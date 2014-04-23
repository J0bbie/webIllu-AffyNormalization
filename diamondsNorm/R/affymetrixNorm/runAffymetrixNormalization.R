#=============================================================================#
# ArrayAnalysis - affyAnalysisQC                                              #
# a tool for quality control and pre-processing of Affymetrix array data      #
#                                                                             #
# Copyright 2010-2011 BiGCaT Bioinformatics                                   #
#                                                                             #
# Licensed under the Apache License, Version 2.0 (the "License");             #
# you may not use this file except in compliance with the License.            #
# You may obtain a copy of the License at                                     #
#                                                                             #
# http://www.apache.org/licenses/LICENSE-2.0                                  #
#                                                                             #
# Unless required by applicable law or agreed to in writing, software         #
# distributed under the License is distributed on an "AS IS" BASIS,           #
# WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.    #
# See the License for the specific language governing permissions and         #
# limitations under the License.                                              #
#=============================================================================#

#####################################################################################################
#                                       Main Process flow                                          #
#####################################################################################################

#####################################################################################################
#                             Load additional scripts, packages and parameters                      #
#####################################################################################################

#Keep track of the running time of this script.
ptm <- proc.time()

#Get the configuration options
source("/var/www/normdb/R/config.R")

#Path to folder where the R scripts are found for the normalization of Illumina expression data.
#The main folder is defined in the config.R file
SCRIPT.DIR <- paste(configMainFolder,"R","affymetrixNorm",sep="/")

#Functions to access the normDB/DIAMONDS
source(paste(SCRIPT.DIR,"functions_myDB.R",sep="/"))

#Make a connection to the DB
con <- makeConnection()

#Functions to get the passed parameters and set the default values of all parameters used in this pipeline
source(paste(SCRIPT.DIR,"getArguments.R",sep="/"))

#Get the command-line parameters that were given to this script (Parameters defined in getArguments.R)
#Also check the validity of these parameters and directories
#userParameters <- getArguments(commandArgs(trailingOnly = TRUE))
userParameters <- getArguments(c("--statisticsDir", "/var/www/normdb/data/2_affyStudy/statistics/9", "--idStudy", 2, "--samplePrep", TRUE, "--inputDir", "/var/www/normdb//data/2_affyStudy/expressionData/raw/", "--outputDir", "/var/www/normdb//data/2_affyStudy/expressionData/normed/10"))

#Function to install missing libraries
source(paste(userParameters$scriptDir,"functions_loadPackages.R",sep="/"))

#Functions for the creation of the plots
source(paste(userParameters$scriptDir,"functions_imagesQC.R",sep="/"))

#Functions for QCing the raw and normalized data
source(paste(userParameters$scriptDir,"functions_processingQC.R",sep="/"))

cat("\nLoading required packages.\n")

#Create a list of the mandatory packages needed for this pipeline.
pkgs <- c( "affy", "affycomp","affyPLM", "affypdnn", "ArrayTools",
           "bioDist", "simpleaffy","affyQCReport", "plier", "gdata", "gplots",
           if(userParameters$samplePrep) ("yaqcaffy")
)

#Install any missing R libraries if needed
loadPackages(pkgs)

cat("\nRequired packages succesfully loaded.\n")

##################################################################################
##                            Read in description file                          ##
##################################################################################

descFile = paste(userParameters$outputDir, userParameters$descFile, sep = "")

cat("\nReading the description file:", descFile, "\n", sep="")

description <- read.table(descFile,
                          header=T,  
                          stringsAsFactors = F,
                          sep='\t',
                          quote="")

if(length(grep(".CEL",toupper(colnames(description)[1]), ignore.case = TRUE))>0) {
  stop(paste("\nThe description file may not contain a header, as the first", "column header seems to be a CEL file name\n"))
}

#Create new column with format sampleNames as read-in with make.names
description$arraySampleNames = make.names(description[,1])

#Order the groups in ascending order
description2 =  description[order(description[,3], description[,2]), ]

cat("\nDescription file loaded succesfully.\n")

#If normalization is true:
if(userParameters$normalize){
  ##################################################################################
  ##                  Load the .CEL files from the input directory                ##
  ##################################################################################
  
  # Get a list of the .CEL files in the input directory
  rawCelFiles <- list.celfiles(path = userParameters$inputDir, full.names=TRUE)
  
  # Get all the .CEL files from the input directory
  # If a custom annotation file is given, use this for annotation
  if(userParameters$useCustomAnnotation && userParameters$customAnnotation != ""){
    customAnnotationFile <- paste(userParameters$inputDir, userParameters$useCustomAnnotation ,sep="/")
    cat(paste("Using custom annotation file:", customAnnotationFile, sep=""))
    rawData <- ReadAffy(filenames = rawCelFiles, cdfname = customAnnotationFile)
  }else{
    rawData <- ReadAffy(filenames = rawCelFiles)
  }
  
  print("\nRaw data have been loaded in R\n")
  
  # Make sure that the CDF environment works
  rawData <- addStandardCDFenv(rawData)   # if already works, won't be changed
  
  # Verify the array type (PMMM or PMonly)
  aType <- getArrayType(rawData)
  
  # When refName does not exist, use the empty string
  if(!exists("refName")) refName <- ""
  
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
  
  cat("\nChecking if description data is valid for the given CEL files.\n")
  
  # Check if the first column contains the .CEL files present in the input directory
  if(length(grep(".CEL",toupper(colnames(description)[1]), ignore.case = TRUE))>0) {
    stop(paste("The description file may not contain a header, as the first", "column header seems to be a CEL file name"))
  }
  
  #Match sampleNames from datafile with first column from description file
  file_order <- match(description[,4],sampleNames(rawData))
  
  #Check on NA values in file_order; if na in file_order stop
  if(sum(is.na(file_order)) > 0){
    message <- paste("\nError: Assigned array names in raw data file and file names in description file do not match!\n")
    changeJobStatus(con, userParameters$idJob, 2, message)
    stop(message)
  }
  
  #Check if every sampleName is unique in description file
  if(length(description[,2]) != length(unique(description[,2])) ){
    message <- ("Error: Assigned sampleNames are not unique!")
    changeJobStatus(con, userParameters$idJob, 2, message)
    stop(message)
  }
  
  #Change order of rawData in order of file_order
  rawData <- rawData[,file_order]
  
  cat("\nDescription data is valid.\n") 
  
  ##################################################################################
  ##        Reorder rawData affyBatch file on Group and sampleNames               ##
  ##################################################################################
  
  #Reorder the samples per defined group, this makes sure the samples in a group are shown together in the plots.
  if(userParameters$perGroup){
    cat("\nRe-ordering raw .CEL data per group defined in the description file.\n")
    
    #Match sampleNames from datafile with first column from description file
    file_order2 <- match(description2[,4],sampleNames(rawData))
    
    #If not all the array have a sample name
    if(sum(is.na(file_order2)) > 0) {
      message <- ("Error: .CEL filenames and file names in description file do not match!")
      changeJobStatus(con, userParameters$idJob, 2, message)
      stop(message)
    }
    
    #Reorder the raw expression data
    rawData <- rawData[,file_order2]
    
    #Change sampleNames into reordered description file
    sampleNames(rawData)<- as.character(description2[,2]) 
    
    cat("\nRe-ordering succesfull.\n")
  }else {
    #Change sampleNames into loaded description file
    sampleNames(rawData)<- as.character(description[,2])
    cat("\nSample names have been given to the arrays.\n")
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
  
  ###############################################################################
  # Calculate the indicator values and begin the report                         #
  ###############################################################################
  
  # Create a cover sheet for the report to be created later
  # And create a page indicating the naming and grouping used
  ## Not really needed and therefore disabled.
  
  #coverAndKeyPlot(description, refName, WIDTH=userParameters$img.width, HEIGHT=userParameters$img.height, outputDirectory=userParameters$outputDirectory)
  
  #create a table with several QC indicators
  if(userParameters$samplePrep || userParameters$ratioPlot || userParameters$hybridPlot || userParameters$percPres || userParameters$bgPlot || userParameters$scaleFact) {
    
    cat("\nCreating QC indicators tables.\n")
    
    # The indicators are calculated only for PM-MM arrays as the calculation
    # based on MAS5 does not work for PM-only arrays
    
    quality <- NULL
    try(quality <- qc(rawData),TRUE) # calculate Affymetrix quality data for PMMM
    if(is.null(quality)) {
      warning("Plots based on the simpleaffy qc function cannot be created for this chip type")
    }
    
    if(userParameters$samplePrep) {    
      # find the data 
      try(yack <- yaqc(rawData),TRUE)
      if(exists("yack")) {
        spnames<-rownames(yack@morespikes[grep("(lys|phe|thr|dap).*3", # only 3' 
                                               rownames(yack@morespikes), ignore.case = TRUE),])
        sprep<-t(yack@morespikes[spnames,])
      } else {
        sprep <- NULL
        warning("Plots based on the yaqc function cannot be created for this chip type")
      }    
      
      try({calls<-detection.p.val(rawData)$call
           lys<-calls[rownames(calls)[grep("lys.*3",rownames(calls),ignore.case=TRUE)],]
           rm(calls)},TRUE)
      if(!exists("lys")) {
        lys <- NULL
        warning("Plots based on the detection.p.val function cannot be created for this chip type")
      }else{
        if(length(lys) > length(sampleNames(rawData))) { lys<-lys[1,] }
      }
    }
    
    QCtablePlot(rawData,quality,sprep,lys,samplePrep=userParameters$samplePrep,ratio=userParameters$ratioPlot,
                hybrid=userParameters$hybridPlot,percPres=userParameters$percPres,bgPlot=userParameters$bgPlot,scaleFact=userParameters$scaleFact,
                WIDTH=userParameters$img.width, HEIGHT=userParameters$img.height ,POINTSIZE=userParameters$img.pointSize,outputDirectory=userParameters$statisticsDir)
    addStatFile( userParameters$idStudy, 64, userParameters$idStatistics, "QCtable.png")
  }
  
  ###############################################################################
  # Raw data Quality Control graphs                                             #
  ###############################################################################
  
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
        
    #################################################################################
    #                             Sample prep controls                              #
    #################################################################################
    
    if(userParameters$samplePrep && !is.null(sprep) && !is.null(lys)) {
      cat("\nPlotting sample prep controls\n")
      samplePrepPlot(rawData,sprep,lys,plotColors,
                     WIDTH=userParameters$img.width,HEIGHT=userParameters$img.height,POINTSIZE=userParameters$img.pointSize,MAXARRAY=userParameters$img.maxArray, outputDirectory=userParameters$statisticsDir)
      addStatFile( userParameters$idStudy, 41, userParameters$idStatistics, "RawDataSamplePrepControl.png")
      
    }
    
    #################################################################################
    #                             Ratio - only for PM-MM arrays                     #
    #################################################################################
    
    if(userParameters$ratioPlot && !is.null(quality)) {
      print ("\nPlot beta-actin & GAPDH 3'/5' ratio\n")
      ratioPlot(rawData,quality=quality,experimentFactor,plotColors,legendColors,
                WIDTH=userParameters$img.width,HEIGHT=userParameters$img.height ,POINTSIZE=userParameters$img.pointSize,MAXARRAY=userParameters$img.maxArray,outputDirectory=userParameters$statisticsDir)
      addStatFile( userParameters$idStudy, 42, userParameters$idStatistics, "RawData53ratioPlot_beta-actin.png")
      addStatFile( userParameters$idStudy, 43, userParameters$idStatistics, "RawData53ratioPlot_GAPDH.png-actin.png")
    }
    
    #################################################################################
    #                             RNA degradation plot                              #
    #################################################################################
    
    if(userParameters$degPlot) {
      print ("\nPlot degradation plot\n")
      RNAdegPlot(rawData,plotColors=plotColors,
                 WIDTH=userParameters$img.width,HEIGHT=userParameters$img.height ,POINTSIZE=userParameters$img.pointSize,MAXARRAY=userParameters$img.maxArray,outputDirectory=userParameters$statisticsDir)
      addStatFile( userParameters$idStudy, 44, userParameters$idStatistics, "RawDataRNAdegradation.png")
    }
    
    #################################################################################
    #                             Spike-in controls - only for PM-MM arrays         #
    #################################################################################
    
    if(userParameters$hybridPlot && !is.null(quality)) {
      print ("\nPlot spike-in hybridization controls\n")
      hybridPlot(rawData,quality=quality,plotColors,
                 WIDTH=userParameters$img.width,HEIGHT=userParameters$img.height ,POINTSIZE=userParameters$img.pointSize,MAXARRAY=userParameters$img.maxArray,outputDirectory=userParameters$statisticsDir)
      addStatFile( userParameters$idStudy, 60, userParameters$idStatistics, "RawDataSpikeinHybridControl.png")
    }
    
    #################################################################################
    #                             Background intensities - only for PM-MM arrays    #
    #################################################################################
    
    if(userParameters$bgPlot && !is.null(quality)) {
      print ("\nPlot background intensities\n")
      backgroundPlot(rawData,quality=quality,experimentFactor,plotColors,legendColors,
                     WIDTH=userParameters$img.width,HEIGHT=userParameters$img.height ,POINTSIZE=userParameters$img.pointSize,MAXARRAY=userParameters$img.maxArray,outputDirectory=userParameters$statisticsDir)
      addStatFile( userParameters$idStudy, 48, userParameters$idStatistics, "RawDataBackground.png")
    }
    
    #################################################################################
    #                             Percent present - only for PM-MM arrays           #
    #################################################################################
    
    if(userParameters$percPres && !is.null(quality)) {
      print ("\nPlot percent present\n")
      percPresPlot(rawData,quality=quality,experimentFactor,plotColors,legendColors,
                   WIDTH=userParameters$img.width,HEIGHT=userParameters$img.height ,POINTSIZE=userParameters$img.pointSize,MAXARRAY=userParameters$img.maxArray,outputDirectory=userParameters$statisticsDir)
      addStatFile( userParameters$idStudy, 54, userParameters$idStatistics, "RawDataPercentPresent.png")
    }
    
    #################################################################################
    #     Table of PMA-calls based on the MAS5 algorithm - only for PM-MM arrays    #
    #################################################################################
    
    if(userParameters$PMAcalls) {
      if(userParameters$useCustomAnnotation) {
        if(userParameters$species=="") {
          warning("\nSpecies has not been set and custom cdf requested, attempting to deduce species for chip type\n")
          userParameters$species <- deduceSpecies(rawData@annotation)
        }
        if(userParameters$species!=""){
          PMAtable <- computePMAtable(rawData, userParameters$useCustomAnnotation,userParameters$species,userParameters$CDFtype)
        }else{
          warning("\nCould not define species; the CDF will not be changed\n")
          PMAtable <- computePMAtable(rawData,userParameters$useCustomAnnotation)
        }
      } else {
        PMAtable <- computePMAtable(rawData,userParameters$useCustomAnnotation)
      }
      # Write the PMAtable
      if(!is.null(PMAtable)) {
        print ("\nWriting PMA table\n")
        write.table(PMAtable, paste(userParameters$outputDirectory,"PMAtable.txt", sep="/"), sep="\t", row.names=FALSE, 
                    col.names=TRUE, quote=FALSE)
        addStatFile( userParameters$idStudy, 63, userParameters$idStatistics, "PMAtable.txt")
      }
    }
    
    #################################################################################
    #                   Pos and Neg control distribution                            #
    #################################################################################
    
    if(userParameters$posnegDistrib) {
      print ("\nPlot pos & neg control distribution\n")
      PNdistrPlot(rawData,
                  WIDTH=userParameters$img.width,HEIGHT=userParameters$img.height ,POINTSIZE=userParameters$img.pointSize,outputDirectory=userParameters$statisticsDir)
      addStatFile( userParameters$idStudy, 55, userParameters$idStatistics, "RawDataPosNegDistribution.png")
    }
    
    #################################################################################
    #                   affx control profiles and boxplot                           #
    #################################################################################
    
    if(userParameters$controlPlot) {
      print ("\nPlot control profiles and/or boxplots\n")
      controlPlots(rawData,plotColors,experimentFactor,legendColors,
                   WIDTH=userParameters$img.width,HEIGHT=userParameters$img.height ,POINTSIZE=userParameters$img.pointSize,MAXARRAY=userParameters$img.maxArray,outputDirectory=userParameters$statisticsDir)
      addStatFile( userParameters$idStudy, 46, userParameters$idStatistics, "RawDataAFFXControlsProfiles.png")
      addStatFile( userParameters$idStudy, 45, userParameters$idStatistics, "RawDataControlsBoxplot.png")
    }
    
    #################################################################################
    #                   Scale factor - only for PM-MM arrays                        #
    #################################################################################
    
    if(userParameters$scaleFact && !is.null(quality)) {
      print ("\nPlot scale factors\n")
      scaleFactPlot(rawData,quality=quality,experimentFactor,plotColors,
                    legendColors,
                    WIDTH=userParameters$img.width,HEIGHT=userParameters$img.height ,POINTSIZE=userParameters$img.pointSize,MAXARRAY=userParameters$img.maxArray,outputDirectory=userParameters$statisticsDir)
      addStatFile( userParameters$idStudy, 61, userParameters$idStatistics, "RawDataScaleFactors.png")
    }
    
    #################################################################################
    #                   Boxplot of raw log-intensity                                #
    #################################################################################
    
    if(userParameters$boxplotRaw){
      print ("Plot boxplot for raw intensities\n")
      boxplotFun(Data=rawData, experimentFactor, plotColors, legendColors,
                 WIDTH=userParameters$img.width,HEIGHT=userParameters$img.height ,POINTSIZE=userParameters$img.pointSize,MAXARRAY=userParameters$img.maxArray,outputDirectory=userParameters$statisticsDir)
      addStatFile( userParameters$idStudy, 49, userParameters$idStatistics, "RawDataBoxplot.png")
    }
    
    #################################################################################
    #                   3.1.3 Density histogram of raw log-intensities              #
    #################################################################################
    
    if(userParameters$densityRaw){
      print ("\nPlot density histogram for raw intensities\n")
      densityFun(Data=rawData, plotColors,
                 WIDTH=userParameters$img.width,HEIGHT=userParameters$img.height ,POINTSIZE=userParameters$img.pointSize,MAXARRAY=userParameters$img.maxArray,outputDirectory=userParameters$statisticsDir)
      addStatFile( userParameters$idStudy, 62, userParameters$idStatistics, "RawDensityHistogram.png")
    }
    
    #################################################################################
    #                   3.2.1 MA-plot or raw data                                   #
    #################################################################################
    
    if(userParameters$MARaw){
      print ("\nMA-plots for raw intensities\n")
      maFun(Data=rawData, experimentFactor, perGroup=(userParameters$MAOption1=="group"), 
            aType=aType,
            WIDTH=userParameters$img.width,HEIGHT=userParameters$img.height,MAXARRAY=userParameters$img.maxArray,outputDirectory=userParameters$statisticsDir)
      addStatFile( userParameters$idStudy, 51, userParameters$idStatistics, "RawDataMAplot.png")
    }
    
    #################################################################################
    #                   3.3.1 Plot of the array layout                              #
    #################################################################################
    
    if(userParameters$layoutPlot) {
      print ("\nPlot array reference layout\n")
      plotArrayLayout(rawData,aType,
                      WIDTH=userParameters$img.width,HEIGHT=userParameters$img.height,POINTSIZE=userParameters$img.pointSize,outputDirectory=userParameters$statisticsDir)
      addStatFile( userParameters$idStudy, 57, userParameters$idStatistics, "RawDataReferenceArrayLayout.png")
    }
    
    #################################################################################
    #                   3.3.2 Pos and Neg control Position                          #
    #################################################################################
    
    if(userParameters$posnegCOI){  
      print ("\nPos/Neg COI\n")
      PNposPlot(rawData,
                WIDTH=userParameters$img.width,HEIGHT=userParameters$img.height,POINTSIZE=userParameters$img.pointSize,outputDirectory=userParameters$statisticsDir)
      addStatFile( userParameters$idStudy, 56, userParameters$idStatistics, "RawDataPosNegPositions.png")
    }
    
    #################################################################################
    #                   3.3.3.1 Create PLM object                                   #
    #################################################################################
    
    # fit a probe level model on the raw data, used by nuse and rle plot as well
    rawData.pset <- NULL
    if(userParameters$spatialImage || userParameters$PLMimage || userParameters$Nuse || userParameters$Rle) {
      cat ("\nFit a probe level model (PLM) on the raw data\n")  
      rawData.pset <- fitPLM(rawData)                     
    }
    
    #################################################################################
    #                   3.3.3.2 Spatial images                                      #
    #################################################################################
    
    if(userParameters$spatialImage) {  
      cat ("\n2D virtual images\n")
      valtry<-try(spatialImages(rawData, Data.pset=rawData.pset, TRUE,FALSE,FALSE,FALSE, 
                                               WIDTH=userParameters$img.width,HEIGHT=userParameters$img.height,POINTSIZE=userParameters$img.pointSize,outputDirectory=userParameters$statisticsDir),
                  silent=TRUE)
      if(class(valtry)=="try-error") {
        cat("\nUse array.image instead of spatialImages function\n")
        if(length(sampleNames(rawData))>6){
          # Usage of a median array is interesting when there are enough arrays
          array.image(rawData,WIDTH=userParameters$img.width,HEIGHT=userParameters$img.height,POINTSIZE=userParameters$img.pointSize,outputDirectory=userParameters$statisticsDir)
        }else{
          # Usage when few arrays in dataset (one page for 3 arrays -> max: 2 pages)
          array.image(rawData,relative=FALSE,col.mod=4,symm=TRUE,
                      WIDTH=userParameters$img.width,HEIGHT=userParameters$img.height,POINTSIZE=userParameters$img.pointSize,outputDirectory=userParameters$statisticsDir)
        }
      }
      addStatFile( userParameters$idStudy, 65, userParameters$idStatistics, "RawData2DVirtualImage.png")
    }
    
    #################################################################################
    #                             3.3.3.3 PLM images                                #
    #################################################################################
    
    if(userParameters$PLMimage) {  
      print ("\nComplete set of 2D PLM images\n")
      valtry<-try(spatialImages(rawData, Data.pset=rawData.pset, TRUE, TRUE, TRUE, TRUE,
                                WIDTH=userParameters$img.width,HEIGHT=userParameters$img.height ,POINTSIZE=userParameters$img.pointSize,MAXARRAY=userParameters$img.maxArray,outputDirectory=userParameters$statisticsDir),
                  silent=TRUE)
      if(class(valtry)=="try-error") {
        print("      Could not create the PLM images.")
      }
    }
    
    #################################################################################
    #                             3.4.1 NUSE                                        #
    #################################################################################
    
    if(userParameters$Nuse){
      print ("\nNUSE boxplot\n")
      nuseFun(rawData, Data.pset=rawData.pset, experimentFactor, plotColors, 
              legendColors,
              WIDTH=userParameters$img.width,HEIGHT=userParameters$img.height ,POINTSIZE=userParameters$img.pointSize,MAXARRAY=userParameters$img.maxArray,outputDirectory=userParameters$statisticsDir)
      addStatFile( userParameters$idStudy, 52, userParameters$idStatistics, "RawDataNUSE.png")
    }
    
    #################################################################################
    #                             3.4.2 RLE                                         #
    #################################################################################
    
    if(userParameters$Rle){          
      print ("\nRLE boxplot\n")
      rleFun(rawData, Data.pset=rawData.pset, experimentFactor, plotColors, 
             legendColors,
             WIDTH=userParameters$img.width,HEIGHT=userParameters$img.height ,POINTSIZE=userParameters$img.pointSize,MAXARRAY=userParameters$img.maxArray,outputDirectory=userParameters$statisticsDir)
      addStatFile( userParameters$idStudy, 58, userParameters$idStatistics, "RawDataRLE.png")
    }
    
    #################################################################################
    #                             4.1 Correlation Plot  of raw data                 #
    #################################################################################
    
    if(userParameters$correlRaw){
      print ("\nCorrelation plot of raw data\n")
      correlFun(Data=rawData, experimentFactor=experimentFactor, legendColors=legendColors,
                WIDTH=userParameters$img.width,HEIGHT=userParameters$img.height ,POINTSIZE=userParameters$img.pointSize,MAXARRAY=userParameters$img.maxArray,outputDirectory=userParameters$statisticsDir)
      addStatFile( userParameters$idStudy, 47, userParameters$idStatistics, "RawDataArrayCorrelation.png")
    }
    
    #################################################################################
    #                             4.2 PCA analysis of raw data                      #
    #################################################################################
    
    if(userParameters$PCARaw){
      print("\nPCA analysis of raw data\n")
      pcaFun(Data=rawData, experimentFactor=experimentFactor, 
             plotColors=plotColors, legendColors=legendColors, plotSymbols=plotSymbols,
             legendSymbols=legendSymbols, namesInPlot=((max(nchar(sampleNames(rawData)))<=10)&&
                                                         (length(sampleNames(rawData))<=(userParameters$img.maxArray/2))),
             WIDTH=userParameters$img.width, HEIGHT=userParameters$img.height, POINTSIZE=userParameters$img.pointSize,outputDirectory=userParameters$statisticsDir)
      addStatFile( userParameters$idStudy, 53, userParameters$idStatistics, "RawDataPCAanalysis.png")
    }
    
    #################################################################################
    #                             4.3 Hierarchical Clustering of raw data           #
    #################################################################################
    
    if(userParameters$clusterRaw){
      print ("\nHierarchical clustering of raw data\n") 
      clusterFun(Data=rawData, experimentFactor=experimentFactor,
                 clusterOption1=userParameters$clusterOption1, clusterOption2=userParameters$clusterOption2,
                 plotColors=plotColors, legendColors=legendColors,
                 plotSymbols=plotSymbols, legendSymbols=legendSymbols,
                 WIDTH=userParameters$img.width,HEIGHT=userParameters$img.height ,POINTSIZE=userParameters$img.pointSize,MAXARRAY=userParameters$img.maxArray,outputDirectory=userParameters$statisticsDir)
      addStatFile( userParameters$idStudy, 50, userParameters$idStatistics, paste("RawDataCluster_",userParameters$clusterOption1,".png", sep=""))
    }
  }else{
    cat("\nSkipping QC plots for the raw data.\n")   
  } # End raw plots
  
  ##################################################################################
  ##                                      Normalizing                             ##
  ##################################################################################
  
  if(userParameters$normalize){
    
    if (aType == "PMonly") {
      if (userParameters$normMeth == "MAS5") {
        warning("\nMAS5 cannot be applied to PMonly arrays. Changed MAS5 to PLIER\n")
        userParameters$normMeth <- "PLIER"
      }
      if (userParameters$normMeth == "GCRMA") {
        warning("\nGCRMA cannot be applied to PMonly arrays. Changed GCRMA to RMA\n")
        userParameters$normMeth <- "RMA"
      }  
    }
    
    if(userParameters$normMeth!="" && userParameters$normMeth!="none") {
      if(userParameters$useCustomAnnotation) {         
        if(userParameters$species!=""){
          normData <- normalizeData(rawData,userParameters$normMeth,perGroup=(userParameters$normOption1=="group"), 
                                    experimentFactor, aType=aType, userParameters$useCustomAnnotation, userParameters$species, userParameters$CDFtype,
                                    WIDTH=userParameters$img.width,HEIGHT=userParameters$img.height)
        }else{
          warning("\nCould not define species; the CDF will not be changed\n")
          normData <- normalizeData(rawData,userParameters$normMeth,perGroup=(userParameters$normOption1=="group"), 
                                    experimentFactor, aType=aType, userParameters$useCustomAnnotation,
                                    WIDTH=userParameters$img.width,HEIGHT=userParameters$img.height)
        }
        
      } else {
        normData <- normalizeData(rawData,userParameters$normMeth,perGroup=(userParameters$normOption1=="group"), 
                                  experimentFactor, aType=aType, userParameters$useCustomAnnotation,
                                  WIDTH=userParameters$img.width,HEIGHT=userParameters$img.height)
      }
    }         
  }else{
    cat("\nSkipping normalization!\n")   
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
      
      #If not all the array have a sample name
      if(sum(is.na(file_order2)) > 0) {
        message <- ("Error: File names in old normalized data and file names in description file do not match!")
        changeJobStatus(con, userParameters$idJob, 2, message)
        stop(message)
      }
      #Reorder the normed expression data
      normData <- normData[,file_order2]
      
      #Change sampleNames into reordered description file
      sampleNames(normData) <- as.character(description2[,2]) 
      
      cat("\nRe-ordering succesfull.\n")
    }
    
    #Make subset of normed data if needed
    if(userParameters$normDataQC && userParameters$statSubset){
      cat("\nMaking subset of samples in raw data.!\n")
      
      x <- sampleNames(normData)[order(sampleNames(normData))]
      matchedSamples <- match(statFile[,1], x)
      
      #Make subset of samples
      normData <- normData[, matchedSamples]
      
      cat("\nSuccesfully made subset of samples in raw data!\n")   
    }
    
    #################################################################################
    #                             Make boxplot of normalized data                   #
    #################################################################################
    
    if(userParameters$boxplotNorm){
      print ("\nPlot boxplot for normalized intensities\n") 
      boxplotFun(Data=normData, experimentFactor, plotColors, legendColors, 
                 normMeth=userParameters$normMeth,
                 WIDTH=userParameters$img.width,HEIGHT=userParameters$img.height ,POINTSIZE=userParameters$img.pointSize,MAXARRAY=userParameters$img.maxArray,outputDirectory=userParameters$statisticsDir)
      addStatFile( userParameters$idStudy, 67, userParameters$idStatistics, "NormDataBoxplot.png")
    }
    
    #################################################################################
    #                    Make density histogram of normalized data                  #
    #################################################################################
    
    if(userParameters$densityNorm){
      print ("\nPlot density histogram for normalized intensities\n")
      densityFun(Data=normData, plotColors, normMeth=userParameters$normMeth,
                 WIDTH=userParameters$img.width,HEIGHT=userParameters$img.height ,POINTSIZE=userParameters$img.pointSize,MAXARRAY=userParameters$img.maxArray,outputDirectory=userParameters$statisticsDir)
      addStatFile( userParameters$idStudy, 70, userParameters$idStatistics, "NormDensityHistogram.png")
    }
    
    
    #################################################################################
    #         Make separate MA-plots for each group on normalized data              #
    #################################################################################
    
    if(userParameters$MANorm){
      print ("\nMA-plots for normalized intensities\n") 
      maFun(Data=normData, experimentFactor, userParameters$perGroup, 
            normMeth=userParameters$normMeth, WIDTH=userParameters$img.width,HEIGHT=userParameters$img.height ,MAXARRAY=userParameters$img.maxArray,outputDirectory=userParameters$statisticsDir)
      addStatFile( userParameters$idStudy, 70, userParameters$idStatistics, "NormDensityHistogram.png")
    }             
    
    #################################################################################
    #                   Make correlation plots on normalized data                   #
    #################################################################################
    
    if(userParameters$correlNorm){
      print ("\nCorrelation plot of normalized data\n") 
      correlFun(Data=normData, normMeth=userParameters$normMeth, experimentFactor=experimentFactor, legendColors=legendColors,
                WIDTH=userParameters$img.width,HEIGHT=userParameters$img.height ,POINTSIZE=userParameters$img.pointSize,MAXARRAY=userParameters$img.maxArray,outputDirectory=userParameters$statisticsDir)
      addStatFile( userParameters$idStudy, 69, userParameters$idStatistics, "NormDataMAplot")
    }
    
    #################################################################################
    #                             PCA analysis of normalized data                   #
    #################################################################################
    
    if(userParameters$PCANorm){
      print("\nPCA graph for normalized data\n")
      pcaFun(Data=normData, experimentFactor=experimentFactor,normMeth=userParameters$normMeth, 
             plotColors=plotColors, legendColors=legendColors, plotSymbols=plotSymbols,
             legendSymbols=legendSymbols, namesInPlot=((max(nchar(sampleNames(rawData)))<=10)&&
                                                         (length(sampleNames(rawData))<=(userParameters$img.maxArray/2))),
             WIDTH=userParameters$img.width, HEIGHT=userParameters$img.height, POINTSIZE=userParameters$img.pointSize,outputDirectory=userParameters$statisticsDir)
      addStatFile( userParameters$idStudy, 70, userParameters$idStatistics, "NormDataPCAanalysis")
    }
    
    #################################################################################
    #                   Make hierarchical clustering on normalized data             #
    #################################################################################
    
    if(userParameters$clusterNorm){
      print ("\nHierarchical clustering of normalized data\n") 
      clusterFun(Data=normData, experimentFactor=experimentFactor,
                 clusterOption1=userParameters$clusterOption1, clusterOption2=userParameters$clusterOption2,
                 normMeth=userParameters$normMeth, plotColors = plotColors, legendColors = legendColors,
                 plotSymbols=plotSymbols, legendSymbols=legendSymbols,
                 WIDTH=userParameters$img.width,HEIGHT=userParameters$img.height ,POINTSIZE=userParameters$img.pointSize,outputDirectory=userParameters$statisticsDir)
      addStatFile( userParameters$idStudy, 68, userParameters$idStatistics, paste("RawDataCluster_",userParameters$clusterOption1,".png", sep=""))
    }
  }else{
    cat("\nSkipping QC plots of normed data\n")
  }  
  
  ##################################################################################
  ##                  Save lumiBatch files as a R object                          ##
  ##################################################################################
  
  #Save lumiBatch R object of rawData
  if(userParameters$save.rawData) {
    fileName <- paste(userParameters$outputDir,   userParameters$studyName,"_rawData.Rdata", sep="")
    cat("\nSaving lumiBatch R object of the raw data in: ", fileName ,"\n", sep="")
    save(rawData, file=fileName )
    addNormFile( userParameters$idStudy, 35, userParameters$idNorm, paste(userParameters$studyName,"_rawData.Rdata", sep=""))
    cat("\nSuccesfully saved lumiBatch R object of the raw data\n")
  }
  
  #Save lumiBatch R object of normData
  if(userParameters$save.normData) {
    fileName <- paste(userParameters$outputDir,  userParameters$studyName,"_normData.Rdata", sep="")
    cat("\nSaving lumiBatch R object of the normalized data in: ", fileName ,"\n", sep="")
    save(normData, file=fileName )
    addNormFile( userParameters$idStudy, 36, userParameters$idNorm, paste(userParameters$studyName,"_normData.Rdata", sep=""))
    cat("\nSuccesfully saved lumiBatch R object of the normalized data\n")
  }
  
  #################################################################################
  #                         Output the normalized data                            #
  #################################################################################
  
  if(userParameters$normalize) {
    print("\nSaving normalized data table\n")
    
    normDataTable <- createNormDataTable(normData, customCDF=(sum(featureNames(normData)!=featureNames(rawData)[1:length(featureNames(normData))])>0), userParameters$species, userParameters$CDFtype)
    
    # Output normalised expression data to file
    refName <- sub("(_\\d{4}-\\d{2}-\\d{2}_\\d{2}-\\d{2}_\\d{2})", "", refName)  
    normFileName <- paste(userParameters$outputDir, paste(userParameters$normMeth,"NormData_",refName,".txt",sep=""), sep="/")
    print(paste("Normalized data table:", normFileName))
    write.table(normDataTable, normFileName, sep="\t", row.names=FALSE, col.names=TRUE, quote=FALSE)
    addNormFile( userParameters$idStudy, 33, userParameters$idNorm ,normFileName)
  }
  
  #################################################################################
  #                   Save the expressions data to the DB                         #
  #################################################################################
  
  if(userParameters$saveToDB){
    cat("\nSaving data to DB\n")
    
    cat("\nMaking ID vector\n")
    
    #Make a vector of all the sampleIDs, retrieve them based on idStudy and sampleName
    idVector <- character(length(colnames(normDataTable))-1)
    i=0;
    for(sampleName in colnames(normDataTable)){
      if(i != 0){
        sampleName
        #Get the idSample
        x <- getSampleID(con, userParameters$idStudy, sampleName)
        idVector[i] <- x$idSample
      }
      i <- i+1
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
  message = ("\nAffymetrix Beadchip normalisation (and/or QC) has been completed!!\n")
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
  
} #End normalization
