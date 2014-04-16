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
source("../config.R")

#Path to folder where the R scripts are found for the normalization of Illumina expression data.
#The main folder is defined in the config.R file
SCRIPT.DIR <- paste(configMainFolder,"R","affymetrixNorm",sep="/")

#Functions to access the normDB/DIAMONDS
#source(paste(SCRIPT.DIR,"functions_myDB.R",sep="/"))

#Make a connection to the DB
#con <- makeConnection()

#Functions to get the passed parameters and set the default values of all parameters used in this pipeline
source(paste(SCRIPT.DIR,"getArguments.R",sep="/"))

#Get the command-line parameters that were given to this script (Parameters defined in getArguments.R)
#Also check the validity of these parameters and directories
#userParameters <- getArguments(commandArgs(trailingOnly = TRUE))
userParameters <- getArguments(c("--samplePrep", TRUE, "--inputDir", "C:/Users/rietjv/AppData/Local/My Local Documents/Affymetrix_testdata1", "--outputDir", "C:/Users/rietjv/AppData/Local/My Local Documents/Affymetrix_testdata1"))

#Function to install missing libraries
source(paste(userParameters$scriptDir,"functions_loadPackages.R",sep="/"))

#Functions for the creation of the plots
source(paste(userParameters$scriptDir,"functions_imagesQC.R",sep="/"))

#Functions for QCing the raw and normalized data
source(paste(userParameters$scriptDir,"functions_processingQC.R",sep="/"))

cat("\nLoading required packages.\n")

#Create a list of the mandatory packages needed for this pipeline.
pkgs <- c( "affy", "affycomp","affyPLM", "affypdnn",
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
          coverAndKeyPlot(description, refName,WIDTH=userParameters$img.width,HEIGHT=userParameters$img.height)
          
          #create a table with several QC indicators
          if(userParameters$samplePrep || userParameters$ratioPlot || userParameters$hybridPlot || userParameters$percPres || userParameters$bgPlot || userParameters$scaleFact) {
                    
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
                                WIDTH=userParameters$img.width,HEIGHT=userParameters$img.height ,POINTSIZE=userParameters$img.pointSize)
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
          }
          
          cat("\nCreating QC plots for the raw data.\n")
          
          fileNamePrefix <- paste(userParameters$statisticsDir, "/",  userParameters$studyName , "_RAW" ,sep="")
          
          #################################################################################
          #                             Sample prep controls                              #
          #################################################################################
          
          if(userParameters$samplePrep && !is.null(sprep) && !is.null(lys)) {
                    cat("\n Plotting sample prep controls\n")
                    samplePrepPlot(rawData,sprep,lys,plotColors,
                                   WIDTH=userParameters$img.width,HEIGHT=userParameters$img.height ,POINTSIZE=userParameters$img.pointSize,MAXARRAY=userParameters$img.maxArray)
          }
          
          #################################################################################
          #                             Ratio - only for PM-MM arrays                     #
          #################################################################################
          
          if(userParameters$ratioPlot && !is.null(quality)) {
                    print ("   plot beta-actin & GAPDH 3'/5' ratio")
                    ratioPlot(rawData,quality=quality,experimentFactor,plotColors,legendColors,
                              WIDTH=userParameters$img.width,HEIGHT=userParameters$img.height ,POINTSIZE=userParameters$img.pointSize,MAXARRAY=userParameters$img.maxArray)
          }
          
          #################################################################################
          #                             RNA degradation plot                              #
          #################################################################################
          
          if(userParameters$degPlot) {
                    print ("   plot degradation plot"  )
                    RNAdegPlot(rawData,plotColors=plotColors,
                               WIDTH=userParameters$img.width,HEIGHT=userParameters$img.height ,POINTSIZE=userParameters$img.pointSize,MAXARRAY=userParameters$img.maxArray)
          }
          
          #################################################################################
          #                             Spike-in controls - only for PM-MM arrays         #
          #################################################################################
          
          if(userParameters$hybridPlot && !is.null(quality)) {
                    print ("   plot spike-in hybridization controls"  )
                    hybridPlot(rawData,quality=quality,plotColors,
                               WIDTH=userParameters$img.width,HEIGHT=userParameters$img.height ,POINTSIZE=userParameters$img.pointSize,MAXARRAY=userParameters$img.maxArray)
          }
          
          #################################################################################
          #                             Background intensities - only for PM-MM arrays    #
          #################################################################################
          
          if(userParameters$bgPlot && !is.null(quality)) {
                    print ("   plot background intensities"  )
                    backgroundPlot(rawData,quality=quality,experimentFactor,plotColors,legendColors,
                                   WIDTH=userParameters$img.width,HEIGHT=userParameters$img.height ,POINTSIZE=userParameters$img.pointSize,MAXARRAY=userParameters$img.maxArray)
          }
          
          #################################################################################
          #                             Percent present - only for PM-MM arrays           #
          #################################################################################
          
          if(userParameters$percPres && !is.null(quality)) {
                    print ("   plot percent present"  )
                    percPresPlot(rawData,quality=quality,experimentFactor,plotColors,legendColors,
                                 WIDTH=userParameters$img.width,HEIGHT=userParameters$img.height ,POINTSIZE=userParameters$img.pointSize,MAXARRAY=userParameters$img.maxArray)
          }
          
          #################################################################################
          #     Table of PMA-calls based on the MAS5 algorithm - only for PM-MM arrays    #
          #################################################################################
          
          if(userParameters$PMAcalls) {
                    if(userParameters$useCustomAnnotation) {
                              if(userParameters$species=="") {
                                        warning("Species has not been set and custom cdf requested, attempting to deduce species for chip type")
                                        userParameters$species <- deduceSpecies(rawData@annotation)
                              }
                              if(userParameters$species!=""){
                                        PMAtable <- computePMAtable(rawData,userParameters$customAnnotation,userParameters$species,userParameters$CDFtype)
                              }else{
                                        warning("Could not define species; the CDF will not be changed")
                                        PMAtable <- computePMAtable(rawData,userParameters$customAnnotation)
                              }
                    } else {
                              PMAtable <- computePMAtable(rawData,userParameters$customAnnotation)
                    }
                    if(!is.null(PMAtable)) {
                              write.table(PMAtable, "PMAtable.txt", sep="\t", row.names=FALSE, 
                                          col.names=TRUE, quote=FALSE)
                    }
          }
          
          #################################################################################
          #                   Pos and Neg control distribution                            #
          #################################################################################
          
          if(userParameters$posnegDistrib) {
                    print ("   plot pos & neg control distribution"  )
                    PNdistrPlot(rawData,
                                WIDTH=userParameters$img.width,HEIGHT=userParameters$img.height ,POINTSIZE=userParameters$img.pointSize)
          }
          
          #################################################################################
          #                   affx control profiles and boxplot                           #
          #################################################################################
                    
          if(userParameters$controlPlot) {
                    print ("   plot control profiles and/or boxplots")
                    controlPlots(rawData,plotColors,experimentFactor,legendColors,
                                 WIDTH=userParameters$img.width,HEIGHT=userParameters$img.height ,POINTSIZE=userParameters$img.pointSize,MAXARRAY=userParameters$img.maxArray)
          }
          
          #################################################################################
          #                   Scale factor - only for PM-MM arrays                        #
          #################################################################################
                    
          if(userParameters$scaleFact && !is.null(quality)) {
                    print ("   plot scale factors")
                    scaleFactPlot(rawData,quality=quality,experimentFactor,plotColors,
                                  legendColors,
                                  WIDTH=userParameters$img.width,HEIGHT=userParameters$img.height ,POINTSIZE=userParameters$img.pointSize,MAXARRAY=userParameters$img.maxArray)
          }
          
          #################################################################################
          #                   Boxplot of raw log-intensity                                #
          #################################################################################
          
          if(userParameters$boxplotRaw){
                    print ("   plot boxplot for raw intensities")
                    boxplotFun(Data=rawData, experimentFactor, plotColors, legendColors,
                               WIDTH=userParameters$img.width,HEIGHT=userParameters$img.height ,POINTSIZE=userParameters$img.pointSize,MAXARRAY=userParameters$img.maxArray)
          }
          
          #################################################################################
          #                   3.1.3 Density histogram of raw log-intensities              #
          #################################################################################
          
          if(userParameters$densityRaw){
                    print ("   plot density histogram for raw intensities")
                    densityFun(Data=rawData, plotColors,
                               WIDTH=userParameters$img.width,HEIGHT=userParameters$img.height ,POINTSIZE=userParameters$img.pointSize,MAXARRAY=userParameters$img.maxArray)
          }
          
          #################################################################################
          #                   3.2.1 MA-plot or raw data                                   #
          #################################################################################
          
          if(userParameters$MARaw){
                    print ("   MA-plots for raw intensities")
                    maFun(Data=rawData, experimentFactor, perGroup=(MAOption1=="group"), 
                          aType=aType,
                          WIDTH=userParameters$img.width,HEIGHT=userParameters$img.height,MAXARRAY=userParameters$img.maxArray)
          }
          
          #################################################################################
          #                   3.3.1 Plot of the array layout                              #
          #################################################################################
          
          if(userParameters$layoutPlot) {
                    print ("   plot array reference layout")
                    plotArrayLayout(rawData,aType,
                                    WIDTH=userParameters$img.width,HEIGHT=userParameters$img.height,POINTSIZE=userParameters$img.pointSize)
          }
          
          #################################################################################
          #                   3.3.2 Pos and Neg control Position                          #
          #################################################################################
          
          if(userParameters$posnegCOI){  
                    print ("   Pos/Neg COI")
                    PNposPlot(rawData,
                              WIDTH=userParameters$img.width,HEIGHT=userParameters$img.height,POINTSIZE=userParameters$img.pointSize)
          }
          
          #################################################################################
          #                   3.3.3.1 Create PLM object                                   #
          #################################################################################
          
          # 3.3.3.1 Create PLM object
          #--------------------------
          
          # fit a probe level model on the raw data, used by nuse and rle plot as well
          rawData.pset <- NULL
          if(spatialImage || PLMimage || Nuse || Rle) {
                    print ("   Fit a probe level model (PLM) on the raw data")  
                    rawData.pset <- fitPLM(rawData)                     
          }
          
          
          
          
          
          
          
          
} #End normalization
