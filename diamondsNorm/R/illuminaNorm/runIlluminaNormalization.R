#Author:                      Job van Riet + ArrayAnalysis.org
#Date of  creation:           26-2-14
#Date of modification:        16-4-14
#Version:                     1.1
#Modifications:               Added configuration file
#Known bugs:                  None known
#Function:                    This script functions as the main script, calling the functions of the other scripts.
#                             This pipeline is used to normalize Illumina microarray data. (From ArrayAnalysis.org)
#                             Run this script using Rscript <script> arg1 arg2 etc. 
#                             This script pipeline is used in conjuction with normDB/DIAMONDS

#####################################################################################################
#                                       Main Process flow                                          #
#####################################################################################################

#####################################################################################################
#                             Load additional scripts, packages and parameters                      #
#####################################################################################################

#Keep track of the running time of this script.
ptm <- proc.time()

#Set working directory to path of execute
#setwd(dirname(sys.frame(1)$ofile))

#Get the configuration options
source("/var/www/normdb/R/config.R")

#Path to folder where the R scripts are found for the normalization of Illumina expression data.
#The main folder is defined in the config.R file
SCRIPT.DIR <- paste(configMainFolder,"R","illuminaNorm",sep="/")

#Functions to access the normDB/DIAMONDS
source(paste(SCRIPT.DIR,"functions_myDB.R",sep="/"))

#Make a connection to the DB
con <- makeConnection()

#Functions to get the passed parameters and set the default values of all parameters used in this pipeline
source(paste(SCRIPT.DIR,"getArguments.R",sep="/"))

#Get the command-line parameters that were given to this script (Parameters defined in getArguments.R)
#Also check the validity of these parameters and directories
userParameters <- getArguments(commandArgs(trailingOnly = TRUE))
#userParameters <- getArguments(c("--rawDataQC" ,FALSE ,  "--performStatistics", FALSE, "--normalize", TRUE, "--createLog", FALSE, "--normSubset", FALSE, "--statSubset", FALSE, "--statFile", "statSubsetFile.txt", "--studyName", "AIMT2_LiverKidney", "-O", "/var/www/normdb/data/1_AIMT2/statistics/8/", "-S", "8", "-j", "4", "--idNorm","2", "-o", "/var/www/normdb//data/1_asd/expressionData/normed/2", "-i","/var/www/normdb//data/1_asd/expressionData/raw/","-s","Sample_Probe_Profile_102259-2.txt","-c", "Control_Probe_Profile_102259-2.txt","-d","descriptionFile.txt", "--saveToDB", FALSE))

#Function to install missing libraries
source(paste(userParameters$scriptDir,"functions_loadPackages.R",sep="/"))

#Functions for the creation of the plots
source(paste(userParameters$scriptDir,"functions_makeImages.R",sep="/"))

#Functions for QCing the raw and normalized data
source(paste(userParameters$scriptDir,"functions_qualityControl.R",sep="/"))

cat("\nLoading required packages.\n")

#Create a list of the mandatory packages needed for this pipeline.
pkgs <- c( "limma", "ALL","bioDist", "gplots",
           "annotate", "arrayQualityMetrics",
           switch(userParameters$species,
                  Human = pkgs <- c("org.Hs.eg.db"),
                  Mouse = pkgs <- c("org.Mm.eg.db"),
                  Rat =   pkgs <- c("org.Rn.eg.db") 
           ), "lumi",
           userParameters$lib.mapping,
           userParameters$lib.All.mapping
)

#Install any missing R libraries if needed
loadPackages(pkgs)

cat("\nRequired packages succesfully loaded.\n")

##################################################################################
##        Load description file (arrayNames | sampleNames | sampleGroup)        ##
##################################################################################
descFile = paste(userParameters$outputDir, userParameters$descFile, sep = "")

cat("\nReading the description file:", descFile, "\n", sep="")

description <- read.table(descFile,
                          header=T,  
                          stringsAsFactors = F,
                          sep='\t',
                          quote="")

#Create new column with format sampleNames as read-in with make.names
description$arraySampleNames = make.names(description[,1])

#Order the groups in ascending order
description2 =  description[order(description[,3], description[,2]), ]

cat("\nDescription file loaded succesfully.\n")

#If normalization is true:
if(userParameters$normalize){
  ###############################################################################
  ## Load RAW data, perform pre-processing and normalization                   ##
  ###############################################################################
  
  expData <- paste(userParameters$inputDir, userParameters$sampleProbeProfilePath, sep="")
  
  cat("\nLoading sample probe profile:", expData,"\n", sep="")
  
  rawData <- import.rawData(expData, userParameters$detectionTh ,userParameters$convertNuID, 
                            userParameters$checkDupId, userParameters$lib.mapping, userParameters$dec, 
                            userParameters$parseColumnName, userParameters$rawDataQC);
  
  #create generic sampleNames with function make.names
  sampleNames(rawData)<- make.names(sampleNames(rawData))
  
  cat("\nSuccesfully loaded the Sample Probe Profile.\n")
  
  ##################################################################################
  ##                Make a subset of the samples for normalization                ##
  ##################################################################################
  
  # If subsetting is not enabled, all samples from the sample_Probe_Profile are used
  # Else ony select the samples found in the descriptionFile
  if(userParameters$normSubset){
    
    cat("\nMaking a subset for the normalization based on the samples in the descriptionFile\n")
    
    #Match sampleNames from datafile with first column from description file
    subsetSamples <- match(description[,4],sampleNames(rawData))
    
    #Make subset of samples
    rawData <- rawData[,subsetSamples]
    
    cat("\nSuccesfully made subset of samples to normalize!\n")
  }
  
  ##################################################################################
  ##                            Check description file                            ##
  ##################################################################################
  
  cat("\nChecking if description data is valid for the given sample probe profile.\n")
  
  #Create new column with format sampleNames as read-in with make.names
  description$arraySampleNames = make.names(description[,1])
  
  #Check if all the arrays in the Sample_Probe_File have been named in the descriptionFile
  if( sum( length(sampleNames(rawData)) - length(description[,1]))  > 0){
    message <- paste("Error: Number of array names in raw data file and number of array names in description file is not of the same size!")
    cat(message)
    changeJobStatus(con, userParameters$idJob, 2, message)
    if(userParameters$createLog) sink()
    stop(message)
  }
  
  #Match sampleNames from datafile with first column from description file
  file_order <- match(description$arraySampleNames,sampleNames(rawData))
  
  #Check on NA values in file_order; if na in file_order stop
  if(sum(is.na(file_order)) > 0){
    message <- paste("\nError: Assigned array names in raw data file and file names in description file do not match!\n")
    cat(message)
    changeJobStatus(con, userParameters$idJob, 2, message)
    if(userParameters$createLog) sink()
    stop(message)
  }
  
  #Check if every sampleName is unique in description file
  if(length(description[,2]) != length(unique(description[,2])) ){
    message <- ("Error: Assigned sampleNames are not unique!")
    cat(message)
    changeJobStatus(con, userParameters$idJob, 2, message)
    if(userParameters$createLog) sink()
    stop(message)
  }
  
  #Change order of rawData in order of file_order
  rawData <- rawData[,file_order]
  
  cat("\nDescription data is valid.\n")   
  
  ##################################################################################
  ##        Reorder rawData lumibatch file on Group and sampleNames               ##
  ##################################################################################
  
  #Reorder the samples per defined group, this makes sure the samples in a group are shown together in the plots.
  if(userParameters$perGroup){
    cat("\nRe-ordering raw Sample Probe Profile per group defined in the description file.\n")
    
    #Match sampleNames from datafile with first column from description file
    file_order2 <- match(description2[,4],sampleNames(rawData))
    

    #If not all the array have a sample name and subsetting is not enabled
    if(sum(is.na(file_order2)) > 0) {
      message <- ("Error: File names in Sample Probe Profile and file names in description file do not match!")
      cat(message)
      changeJobStatus(con, userParameters$idJob, 2, message)
      if(userParameters$createLog) sink()
      stop(message)
    }
    
    #Reorder the raw expression data
    rawData <- rawData[,file_order2]
    
    #Change sampleNames into reordered description file
    sampleNames(rawData)<- as.character(description2[,2]) 
    
    cat("\nRe-ordering succesfull.\n")
  }
  
  ##################################################################################
  ##                Background correction/Normalizing                             ##
  ##################################################################################
  
  #If background corrections has already been performed
  if(userParameters$bgSub) { 
    cat("\nSkipping background correction\n", "\nNormalizing the raw Sample Probe Profiles:", userParameters$sampleProbeProfilePath, "\n", sep="")
    
    #Normalize lumiBatch object of raw data
    normData <- lumi.normData(rawData, 
                              bg.correct=FALSE, userParameters$bgcorrect.m,
                              userParameters$variance.stabilize, userParameters$variance.m,
                              userParameters$normalize, userParameters$normalization.m, 
                              userParameters$normDataQC);
    
    cat("\nNormalization of raw data has been successfull.\n")   
    
  }else{    
    #Background correction has not been done already
    controlData <- paste(userParameters$inputDir, userParameters$controlProbeProfilePath, sep="")
    cat("\nPerforming background correction (", userParameters$bgcorrect.m, ") on the Sample Probe Profile using the Control Probe Profile: ",controlData, "\n", sep="")
    cat("\nLoading Control Probe Profile:", userParameters$controlProbeProfilePath, "\n", sep="")
    
    #Checks headers of controlData file with rawData object
    #Add control data to the rawData lumiBatch object file
    cat("\nCombining Control data with Sample data.\n")
    rawData.ctrl <- addControlData2lumi(controlData, rawData)
    
    #Get control data in a data.frame
    controlData <- as.data.frame(getControlData(rawData.ctrl), row.names = NULL )
    
    #Normalize lumi batch raw data object using 'lumi' or 'neqc' function
    cat("\nNormaling (", userParameters$normType ,") the raw Sample Probe Profile using background correction:", userParameters$controlProbeProfilePath, "\n", sep="")
    
    switch (userParameters$normType,
            lumi = normData <- lumi.normData(rawData.ctrl, 
                                             bg.correct=TRUE, userParameters$bgcorrect.m,
                                             userParameters$variance.stabilize, userParameters$variance.m,
                                             userParameters$normalize, userParameters$normalization.m, 
                                             userParameters$normDataQC),
            neqc = normData <- neqc.normData(rawData.ctrl, controlData)
    )
    
    cat("\nNormalization of raw data with background correction has been successfull.\n")  
  }
  
  #Create rawData exprs eset table
  cat("\nCreating eSets expression matrix of raw data.\n") 
  eset.rawData <- exprs(rawData)
  cat("\nSuccesfully created eSets expression matrix of raw data.\n") 
  
  #Create normData exprs eset table
  cat("\nCreating eSets expression matrix of normalized data.\n") 
  eset.normData <- exprs(normData)
  cat("\nSuccesfully created eSets expression matrix of normalized data.\n") 
  
  ##################################################################################
  ##                            Summary tables                                    ##
  ##################################################################################
  
  #Make a summary of the raw data
  if (userParameters$rawSummary){
    fileName <- paste(userParameters$outputDir,  userParameters$studyName,"_summary_rawData.txt", sep="") 
    cat("\nCreating summary file of the means and SD of the raw data: ", fileName ,"\n", sep="")
    rawSum.table = createSummary(rawData, fileName);
    addNormFile( userParameters$idStudy, 19, userParameters$idNorm ,paste(userParameters$studyName,"_summary_rawData.txt", sep=""))
    cat("\nSuccesfully made summary file of raw data.\n")    
  }
  
  #Make a summary of the normalized data
  if(userParameters$normSummary){
    fileName <- paste(userParameters$outputDir,  userParameters$studyName,"_summary_normData.txt", sep="") 
    cat("\nCreating summary file of the means and SD of the normalized data: ", fileName ,"\n", sep="")
    normSum.table = createSummary(normData, fileName );
    addNormFile( userParameters$idStudy, 20, userParameters$idNorm, paste(userParameters$studyName,"_summary_normData.txt", sep=""))
    cat("\nSuccesfully made summary file of normalized data.\n")          
  }                   
  
  ##################################################################################
  ##                  Save lumiBatch files as a R object                          ##
  ##################################################################################
  
  #Save lumiBatch R object of rawData
  if(userParameters$save.rawData) {
    fileName <- paste(userParameters$outputDir,   userParameters$studyName,"_rawData.Rdata", sep="")
    cat("\nSaving lumiBatch R object of the raw data in: ", fileName ,"\n", sep="")
    save(rawData, file=fileName )
    addNormFile( userParameters$idStudy, 12, userParameters$idNorm, paste(userParameters$studyName,"_rawData.Rdata", sep=""))
    cat("\nSuccesfully saved lumiBatch R object of the raw data\n")
  }
  
  #Save lumiBatch R object of normData
  if(userParameters$save.normData) {
    fileName <- paste(userParameters$outputDir,  userParameters$studyName,"_normData.Rdata", sep="")
    cat("\nSaving lumiBatch R object of the normalized data in: ", fileName ,"\n", sep="")
    save(normData, file=fileName )
    addNormFile( userParameters$idStudy, 16, userParameters$idNorm, paste(userParameters$studyName,"_normData.Rdata", sep=""))
    cat("\nSuccesfully saved lumiBatch R object of the normalized data\n")
  }
} #End background correction/normalization

##################################################################################
##                  Make QC plots of raw and/or normalized data                 ##
##################################################################################

if(userParameters$performStatistics){
  
  #################################################################################
  #                 Open subset file if user selected a subset                    #
  #################################################################################
  
  if(userParameters$statSubset){
    cat(paste("\nReading in the statFile conating the subset of samples on which to perform the statistics: ,",paste(userParameters$outputDir, userParameters$statFile, sep=""), "\n", sep=""))
    statFile <- read.table(paste(userParameters$statisticsDir, userParameters$statFile, sep="/"),
                           header=F,  
                           stringsAsFactors = F,
                           sep='\t',
                           quote="")
    
    #Get the indexes of the matched samples to the samples in the normData object
    #All other samples will not be used in the statistics
    statFile$arraySampleNames = make.names(statFile[,1])
    
    #Get the groups from the description file and add to the statFile dataFrame
    statFile$groups <- description$FactorValue[match(statFile[,1], description$SourceName)]
    
    cat("\nSuccesfully read in the statFile containing the samples for the subset!\n")
    
    if(userParameters$perGroup){
      #Order the groups in ascending order
      statFile =  statFile[order(statFile$groups, statFile$arraySampleNames), ]
    }
  }
  
  ###############################################################################
  # Create array groups, array names and plot variables                         #
  ###############################################################################
  
  cat("\nCreating a plot colorset for each array group.\n")
  
  if(userParameters$statSubset == FALSE){
    #Create colorset for the array groups
    if(userParameters$perGroup){
      #Use reordered  description file ordered per group
      experimentFactor <- as.factor(description2[,3])
      colList          <- colorsByFactor(experimentFactor)
      plotColors       <- colList$plotColors
      legendColors     <- colList$legendColors
      rm(colList)
    } else {
      #Use originaly loaded description file
      experimentFactor <- as.factor(description[,3])
      colList          <- colorsByFactor(experimentFactor)
      plotColors       <- colList$plotColors
      legendColors     <- colList$legendColors
      rm(colList)
    }
  }else{
    #Use the samples and groups from the subset
    experimentFactor <- as.factor(statFile$groups)
    colList          <- colorsByFactor(experimentFactor)
    plotColors       <- colList$plotColors
    legendColors     <- colList$legendColors
    rm(colList)
  }
  
  #Create symbolset for the array groups
  plotSymbols <- 18-as.numeric(experimentFactor)
  legendSymbols <- sort(unique(plotSymbols), decreasing=TRUE)
  
  cat("\nPlot colorset sucesfully made.\n")
  
  #################################################################################
  #                             QC plots of the raw data                          #
  #################################################################################
  
  if(userParameters$rawDataQC){
    
    #Make subset of raw data if needed
    if(userParameters$statSubset){
      cat("\nMaking subset of samples in raw data.!\n")
      
      x <- sampleNames(rawData)[order(sampleNames(rawData))]
      matchedSamples <- match(statFile[,1], x)
      
      #Make subset of samples
      rawData <- rawData[, matchedSamples]
      
      cat("\nSuccesfully made subset of samples in raw data!\n")   
    }  
    
    cat("\nCreating QC plots for the raw data.\n")
    fileNamePrefix <- paste(userParameters$statisticsDir, "/",  userParameters$studyName , "_RAW" ,sep="")
    if(userParameters$raw.boxplot) {
      cat("\nPlot boxplot for raw intensities\n")
      gar <-box.plot(rawData, fileNamePrefix , col=plotColors, maxArray=50)
      addStatFile( userParameters$idStudy, 21, userParameters$idStatistics, paste(userParameters$studyName,"_RAW_boxplot.pdf", sep=""))
    }
    
    if(userParameters$raw.density) {
      cat("\nPlot density histogram for raw intensities\n")
      gar <- density.plot(rawData, fileNamePrefix , col=plotColors, maxArray=16)
      addStatFile( userParameters$idStudy, 23, userParameters$idStatistics, paste(userParameters$studyName,"_RAW_density.pdf", sep=""))
    }
    
    if(userParameters$raw.cv) {
      cat("\nPlot density for coefficient of variance for raw intensities\n")
      gar <- cv.plot(rawData, fileNamePrefix, col=plotColors, maxArray=16)
      addStatFile( userParameters$idStudy, 22, userParameters$idStatistics, paste(userParameters$studyName,"_RAW_cv_plot.pdf", sep=""))
    }
    
    fileNamePrefix <- paste(userParameters$statisticsDir, "/", userParameters$studyName,sep="")
    if(userParameters$raw.sampleRelation) {
      cat("\nHierarchical clustering of raw data\n") 
      gar <- clusterFun(rawData, normalized=FALSE,  experimentFactor=experimentFactor,
                        clusterOption1=userParameters$clusterOption1, clusterOption2=userParameters$clusterOption2,
                        plotColors=plotColors, legendColors=legendColors,
                        plotSymbols=plotSymbols, legendSymbols=legendSymbols,
                        WIDTH=userParameters$img.width,HEIGHT=userParameters$img.heigth,
                        POINTSIZE=userParameters$img.pointSize,MAXARRAY=userParameters$img.maxArray,
                        normalization.m=userParameters$normalization.m, fileName=fileNamePrefix) 
      addStatFile( userParameters$idStudy, 25, userParameters$idStatistics, paste(userParameters$studyName,"_RAW_dataCluster_",userParameters$clusterOption1,"_",userParameters$clusterOption2,".png",sep=""))
    }
    
    if(userParameters$raw.pca) {  
      cat("\nPCA graph for raw data\n")
      groupsInLegend =  !( length(unique(levels(experimentFactor))) ) >=10
      
      gar <- pcaFun(rawData, normalized=FALSE ,experimentFactor=experimentFactor, 
                    plotColors=plotColors, legendColors=legendColors, plotSymbols=plotSymbols,
                    legendSymbols=legendSymbols, groupsInLegend=groupsInLegend,
                    namesInPlot=((max(nchar(sampleNames(rawData)))<=10)&& (length(sampleNames(rawData))<=(userParameters$img.maxArray/2))),
                    WIDTH=userParameters$img.width,HEIGHT=userParameters$img.heigth,
                    POINTSIZE=userParameters$img.pointSize, normalization.m=userParameters$normalization.m, 
                    fileName=fileNamePrefix)
      addStatFile( userParameters$idStudy, 26, userParameters$idStatistics, paste(userParameters$studyName,"_RAW_dataPCA_analysis.png",sep=""))
    }
    
    if(userParameters$raw.correl){  
      cat("\nCorrelation plot for raw data\n")
      gar <- correlFun(rawData, normalized=FALSE, experimentFactor=experimentFactor, 
                       clusterOption1=userParameters$clusterOption1, clusterOption2=userParameters$clusterOption2,
                       legendColors=legendColors,
                       WIDTH=userParameters$img.width,HEIGHT=userParameters$img.heigth,
                       POINTSIZE=userParameters$img.pointSize,MAXARRAY=userParameters$img.maxArray,
                       normalization.m=userParameters$normalization.m, fileName=fileNamePrefix)   
      addStatFile( userParameters$idStudy, 24, userParameters$idStatistics, paste(userParameters$studyName,"_RAW_dataArrayCorrelation.png",sep=""))
    }
  }else{
    cat("\nSkipping QC plots for the raw data.\n")   
  }
  
  if(userParameters$normDataQC){
    
    #################################################################################
    #                   Perform statistics on old normalized data                   #
    #################################################################################
    #Load the "old" normalized data in if there should be new statistics performed
    if(userParameters$loadOldNorm){
      cat("\nLoading old normalized data\n")
      load(paste(userParameters$inputDir, userParameters$normData, sep="/"))
    }
    
    #Reorder the samples per defined group, this makes sure the samples in a group are shown together in the plots.
    if(userParameters$loadOldNorm && userParameters$perGroup){
      cat("\nRe-ordering old normalized data per group defined in the description file.\n")
      
      #create generic sampleNames with function make.names
      sampleNames(normData) <- make.names(sampleNames(normData))
      
      #Match sampleNames from datafile with first column from description file
      matchedSamples <- match(description2[,4],sampleNames(normData))
      
      #Reorder the normed expression data
      normData <- normData[,na.omit(matchedSamples)]
        
      cat("\nRe-ordering succesfull.\n")
    }
    
    #################################################################################
    #                           Make subset for statistics                          #
    #################################################################################
    
    #
    if(userParameters$normDataQC && userParameters$statSubset){
      cat("\nMaking subset of samples in raw data.!\n")
      
      x <- sampleNames(normData)[order(sampleNames(normData))]
      matchedSamples <- match(statFile[,1], x)
      
      #Make subset of samples
      normData <- normData[, matchedSamples]
      
      cat("\nSuccesfully made subset of samples in raw data!\n")   
    }
    
    #################################################################################
    #                             QC plots of the normalized data                   #
    #################################################################################
    
    cat("\nCreating QC plots for the normalized data.\n")
    fileNamePrefix <- paste(userParameters$statisticsDir, "/",  userParameters$studyName , "_NORM" ,sep="")
    if(userParameters$norm.boxplot) {
      cat("\nPlot boxplot for normalized intensities\n")
      gar <- box.plot(normData, fileNamePrefix , col=plotColors, maxArray=50)
      addStatFile( userParameters$idStudy,  27, userParameters$idStatistics, paste(userParameters$studyName, "_NORM_boxplot.pdf", sep=""))
    }
    
    if(userParameters$norm.density) {
      cat("\nPlot density histogram for normalized intensities\n")
      gar <- density.plot(normData, fileNamePrefix , col=plotColors, maxArray=16)
      addStatFile( userParameters$idStudy, 29, userParameters$idStatistics, paste(userParameters$studyName,"_NORM_density.pdf", sep=""))
    }
    
    if(userParameters$norm.cv) {
      cat("\nPlot density for coefficient of variance for normalized intensities\n")
      gar <- cv.plot(normData, fileNamePrefix , col=plotColors, maxArray=16)
      addStatFile( userParameters$idStudy, 28, userParameters$idStatistics, paste(userParameters$studyName,"_NORM_cv_plot.pdf", sep=""))
    }
    
    fileNamePrefix <- paste(userParameters$statisticsDir, "/", userParameters$studyName,sep="")
    if(userParameters$norm.sampleRelation) {
      cat("\nHierarchical clustering of normalized data\n") 
      gar <- clusterFun(normData, normalized=TRUE,  experimentFactor=experimentFactor,
                        clusterOption1=userParameters$clusterOption1, clusterOption2=userParameters$clusterOption2,
                        plotColors=plotColors, legendColors=legendColors,
                        plotSymbols=plotSymbols, legendSymbols=legendSymbols,
                        WIDTH=userParameters$img.width,HEIGHT=userParameters$img.heigth,
                        POINTSIZE=userParameters$img.pointSize,MAXARRAY=userParameters$img.maxArray,
                        normalization.m=userParameters$normalization.m, fileName=fileNamePrefix)
      addStatFile(userParameters$idStudy, 31, userParameters$idStatistics, paste(userParameters$studyName,"_NORM_dataCluster_",userParameters$clusterOption1,"_",userParameters$clusterOption2,".png",sep=""))
      
    }
    
    if(userParameters$norm.pca) {  
      cat("\nPCA graph for normalized data\n")
      groupsInLegend =  !( length(unique(levels(experimentFactor))) ) >=10
      
      gar <- pcaFun(normData, normalized=TRUE ,experimentFactor=experimentFactor, 
                    plotColors=plotColors, legendColors=legendColors, plotSymbols=plotSymbols,
                    legendSymbols=legendSymbols, groupsInLegend=groupsInLegend,
                    namesInPlot=((max(nchar(sampleNames(normData)))<=10)&& (length(sampleNames(normData))<=(userParameters$img.maxArray/2))),
                    WIDTH=userParameters$img.width,HEIGHT=userParameters$img.heigth,
                    POINTSIZE=userParameters$img.pointSize, normalization.m=userParameters$normalization.m, 
                    fileName=fileNamePrefix)
      addStatFile(userParameters$idStudy, 32, userParameters$idStatistics, paste(userParameters$studyName,"_NORM_dataPCA_analysis.png",sep=""))
      
    }
    
    if(userParameters$norm.correl){  
      cat("\nCorrelation plot for normalized data\n")
      gar <- correlFun(normData, normalized=TRUE, experimentFactor=experimentFactor, 
                       clusterOption1=userParameters$clusterOption1, clusterOption2=userParameters$clusterOption2,
                       legendColors=legendColors,
                       WIDTH=userParameters$img.width,HEIGHT=userParameters$img.heigth,
                       POINTSIZE=userParameters$img.pointSize,MAXARRAY=userParameters$img.maxArray,
                       normalization.m=userParameters$normalization.m, fileName=fileNamePrefix)
      addStatFile(userParameters$idStudy, 30, userParameters$idStatistics, paste(userParameters$studyName, "_NORM_dataArrayCorrelation.png", sep=""))
    }
  }else{
    cat("\nSkipping QC plots for the normalized data.\n")
  }
}else{
  cat("\nSkipping QC plots for raw and normalized data.\n")
}


#################################################################################
#                             Perform filtering                                 #
#################################################################################

#Will create a matrix a filter on expression lower than the user defined expression threshold and bead count
#This table will be later be used to create a filtered annotation matrix
if(userParameters$filtering){
  cat("\nPerforming filtering on low expressions of probes.\n")
  filtered.normData = filterFun(rawData, normData, userParameters$filter.Th, userParameters$filter.dp)
  cat("\nFiltering is done!\n")
}

#################################################################################
#                             Create annotation files                           #
#################################################################################

#Make a annotationfile for the raw data. (Containing gene-expressions per gene for each sample)
if(userParameters$createAnno){
  
  cat("\nMerging annotation file with the raw expression data\n")
  
  #Merge samples to genes
  anno.rawData = createAnnoFun(eset.rawData, userParameters$lib.mapping, userParameters$lib.All.mapping);
  eset.anno.rawData  = cbind(anno.rawData, eset.rawData);
  
  fileName <- paste(userParameters$outputDir,   userParameters$studyName,"_rawData.txt", sep="")
  
  cat("\nMerging done, now saving merged file of raw data to: ", fileName ,"\n")
  
  write.table(eset.anno.rawData, file = fileName, quote= FALSE, sep='\t', row.names= F, col.names= T )
  
  addNormFile( userParameters$idStudy, 73, userParameters$idNorm , paste(userParameters$studyName,"_rawData.txt", sep=""))
  
  cat("\nSaving merged raw data completed.\n")
  
  cat("\nMerging annotation file with the normalized expression data\n")
  
  #Merge norm data with anno
  anno.normData = createAnnoFun(eset.normData, userParameters$lib.mapping, userParameters$lib.All.mapping);
  eset.anno.normData = cbind(anno.normData, eset.normData);
  
  fileName <- paste(userParameters$outputDir,  userParameters$studyName, "_normData_", userParameters$normType, ".txt", sep="")
  
  write.table(eset.anno.normData, file = fileName, quote= FALSE, sep='\t', row.names= F, col.names= T )
  
  addNormFile( userParameters$idStudy, 15, userParameters$idNorm ,paste(userParameters$studyName, "_normData_", userParameters$normType, ".txt", sep=""))
  
  cat("\nSaving merged normalized data to the fileserver completed.\n")
  
}else{
  cat("\nSkipping the output of the merged annotation/samples expression files.\n")
}

if(userParameters$createAnno && userParameters$filtering) {
  ##filtered data with anno
  cat("\nMerging annotation file with the filtered normalized expression data\n")
  
  anno.filtData = createAnnoFun(filtered.normData, userParameters$lib.mapping, userParameters$lib.All.mapping);
  eset.anno.filtData = cbind(anno.filtData, filtered.normData);
  
  fileName <- paste(userParameters$outputDir, userParameters$studyName, "_normData_Filtered_", userParameters$normType, ".txt", sep="")
  
  write.table(eset.anno.filtData, file = fileName, quote= FALSE, sep='\t', row.names= F, col.names= T )
  
  addNormFile( userParameters$idStudy, 18, userParameters$idNorm ,paste(userParameters$studyName, "_normData_Filtered_", userParameters$normType, ".txt", sep=""))
  
  cat("\nSaving merged filtered normalized data completed.\n")
}else{
  cat("\nSkipping the output of the merged filtered normalized annotation/samples expression files.\n")
}

#################################################################################
#                   Save the expressions data to the DB                         #
#################################################################################
if(userParameters$saveToDB){
  cat("\nSaving data to DB\n")
  
  cat("\nMaking ID vector\n")
  
  #Make a vector of all the sampleIDs, retrieve them based on idStudy and sampleName
  idVector <- character(length(row.names(rawSum.table)))
  i=0;
  for(sampleName in row.names(rawSum.table)){
    i <- i+1
    #Get the idSample
    x <- getSampleID(con, userParameters$idStudy, sampleName)
    idVector[i] <- x$idSample
  }
  
  cat("\nSaving summary data to DB\n")
  
  #Save all the data from the summary into the DB
  for(x in 1:length(row.names(rawSum.table))){
    addSampleSummary(con, userParameters$idNorm, idVector[x], rawSum.table[x,], 0)
  }
  
  for(y in 1:length(row.names(normSum.table))){
    addSampleSummary(con, userParameters$idNorm, idVector[y], normSum.table[y,], 1)
  }
  
  cat("\nSaving summary data to DB succesfull!\n")
  
  cat("\nSaving expressions of probes to DB\n")
  
  #Loop over the expression values for each probe
  #Replace the array names with the idSample from the idVector
  names(eset.anno.normData)[9:length(names(eset.anno.normData))] <- idVector
  
  #Loop over all the genes
  for(p in 1:length(row.names(eset.anno.normData))){
    g <- addNormedExpressionPerGene(con, userParameters$idNorm, idVector, eset.anno.normData[p,])
  }
  cat("\nSaving data to the database completed.\n")
}

#Yay, everything worked! (Or at least no errors were found ;) ) 
message = ("\nIllumina Beadchip normalisation (and/or QC) has been completed!!\n")
x<- cat(message)
#Set status of job as succesfull
y <- changeJobStatus(con, userParameters$idJob, 1, message)
#Change the description of the normalization run.
if(!userParameters$loadOldNorm){
  message <- paste("Completed in:", ceiling( (proc.time() -ptm)[3]/60) ,"minutes", sep=" ")
  cat(message)
  y <- updateNormDescription(con, userParameters$idNorm, message)
}
#stop sinking the log
if(userParameters$createLog) sink()
#close the connection to the DB
x <- closeConnection(con)
