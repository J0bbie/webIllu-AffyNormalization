#Author:  Job van Riet + Jordy Coolen
#Date of  creation: 14-4-14
#Date of modification:  14-4-14
#Version: 1.0
#Modifications: Original version
#Known bugs:  None known
#Function:
#This script tries to catch all the options for the normalization used by the ArrayAnalysis scripts for Affymetrix normalization.
#Also checks if the entered parameters are all valid.

#Useful short flags:
#-i       Input directory of .CEL files
#-o       Output directory of normalized data
#-O       Output directory of statistics and plots
#-F       Name of statFile (Located in statistics folder), used to subset
#-f       Whether to load old normalized data
#-m       Filename of normalized data from the input directory
#-A       Filepath of the custom annotation

#Parameters for normDB/DIAMONDS
#-j       idJob (For updating jobstatus)
#-x       idStudy (ID of the study)
#-y       idNorm (ID of the normalization run)
#-S       idStatistics (ID of the statistics run)
#-D       Save data into database
#-n       Name of the study (Used in naming files)

#Makes an optionList of the possible parameters (Using optparse library)
#Also reads the passed command-line arguments and returns these in a list
#Needs commandArgs(trailingOnly = TRUE) as input

getArguments <- function(commandArguments, con){
  #Load optparse package, if not found, try to install
  inst<-installed.packages()
  notInst <- "optparse" %in% inst
  
  #If installed, simply load
  if(notInst){
    library("optparse", character.only = TRUE)    
  }else{
    install.packages("optparse", repos="http://R-Forge.R-project.org")
    #Get a new list of all the installed packages
    inst<-installed.packages()
    notInst <- "optparse" %in% inst
    if(notInst){
      stop("\nCould not install optparse package!")
    }
  }         
  
  option_list <- list(
    
    #####################################################################################################
    #                                 Parameters for normDIAMONDS                                       #
    #####################################################################################################
    
    make_option(c("-j","--idJob"),  type="integer", default=1,
                help="Job ID for updating the job status if failed and done. \ndefault = [%default] "),  
    
    make_option(c("-y","--idNorm"),  type="integer", default=1,
                help="idNorm, keeps track of what normalization run this is. \ndefault = [%default] "),   
    
    make_option(c("-x","--idStudy"),  type="integer", default=1,
                help="idStudy, keeps track of what study this is. \ndefault = [%default] "),
    
    make_option(c("-D","--saveToDB"),  type="logical", default=TRUE,
                help="Whether to save data to DB. \ndefault = [%default] "),
    
    make_option(c("-S","--idStatistics"),  type="integer", default=1,
                help="IdStatistics on which to save the files. \ndefault = [%default] "),
    
    make_option(c("-n","--studyName"),  type="character", default=paste(format(Sys.Date(), "%Y-%m-%d"), sub(":", ".", sub(":", "." ,format(Sys.time(), "%X"))), sep="_" ),
                help="Used to be called ns, Name of the study (Used in naming the output files) \ndefault = [%default] "),
    
    #####################################################################################################
    #                                 Parameters inputfolders & files                                   #
    #####################################################################################################

    make_option(c("-i", "--inputDir"), type="character", default=paste(configMainFolder, "data", sep="/"),
                help="Path to folder where the Control_Probe_Profile, Sample_Probe_Profile and Description file are found \ndefault = [%default] "),
    
    make_option(c("-o","--outputDir"), type="character",  default=paste(configMainFolder, "expressionData", sep="/"),
                help = "Path to folder where the output files will be stored \ndefault = [%default] "),
    
    make_option(c("-O","--statisticsDir"), type="character",  default=paste(configMainFolder, "statistics", sep="/"),
                help = "Path to folder where the output statistics files will be stored \ndefault = [%default] "),
    
    make_option("--scriptDir", type="character",  default=paste(configMainFolder, "R" ,"affyNorm", sep="/"),
                help="Path to folder where the scripts are stored. \ndefault = [%default] "),
    
    make_option(c("-m","--normData"), type="character", default="normData.Rdata",
                help="R Lumibatch object containing the normalized expression values. (If normalization has been run before) \ndefault = [%default] "),
    
    make_option(c("-F","--statFile"), type="character", default="statSubsetFile.txt",
                help="File containing a single column with the sampleNames or ArrayNames on which statistics should be performed. If none given, statistics is performed on all samples in the description file. \ndefault = [%default] "),
    
    make_option(c("-g", "--arrayGroup"), type="character", default="",
                help="description file describing the array names and experimental groups \ndefault = [%default] "),
    
    make_option(c("-A", "--customAnnotation"), type="character", default="",
                help="File containing the custom annotation of the array \ndefault = [%default] "),
    
    make_option("--saveHistory",  type="logical", default=TRUE,
                help="Whether to save the R history to the output directory. \ndefault = [%default] "),
    
    #####################################################################################################
    #                                     Parameters normalization                                      #
    #####################################################################################################
    
    make_option(c("--normalize"), type="logical", default=TRUE,
                help="Whether to normalize or not \ndefault = [%default] "),
    
    make_option("--save.normData", type="logical", default=TRUE,
                help="Whether to save lumi.batch of normalized data as R object in WORK.DIR \ndefault = [%default] "), 
    
    make_option("--save.rawData", type="logical", default=TRUE,
                help="Whether to save lumi.batch of raw data as R object in WORK.DIR \ndefault = [%default] "), 
    
    make_option(c("-f","--loadOldNorm"), type="logical", default=FALSE,
                help="Whether to load the old normalized data (given with -m/--normData). \ndefault = [%default] "),
    
    make_option(c("-z", "--normMeth"), type="character", default="RMA",
                help="possible values for Data pre-processing: (RMA, GCRMA, PLIER, none) \ndefault = [%default] "),
    
    make_option(c("-J", "--normOption1"), type="character", default="dataset",
                help="two possible values: (group, dataset) \ndefault = [%default] "),
    
    make_option(c("-l", "--customCDF"), type="logical", default=TRUE,
                help="boolean for a custom CDF for the pre-processed data \ndefault = [%default] "),
    
    make_option(c("-L", "--CDFtype"), type="character", default="ENSG",
                help="annotation format (default: ENSG), possibilities: (ENTREZG, REFSEQ, ENSG, ENSE, ENST, VEGAG, VEGAE, VEGAT, TAIRG, TAIRT, UG, MIRBASEF, MIRBASEG) \ndefault = [%default] "),
    
    make_option(c("--species"), type="character", default="",
                help="It is required when customCDF is called. Possibilities: abbreviations: (Ag, At, Bt, Ce, Cf, Dr, Dm, Gg, Hs, MAmu,  Mm, Os, Rn, Sc, Sp, Ss or full names: Anopheles gambiae, Arabidopsis thaliana, Bos taurus, Caenorhabditis elegans, Canis familiaris,  Danio rerio, Drosophila melanogaster, Gallus gallus, Homo sapiens, Macaca mulatta, Mus musculus, Oryza sativa, Rattus norvegicus, Saccharomyces cerevisiae, Schizosaccharomyces pombe, Sus scrofa) \ndefault = [%default] "), 
    
    #####################################################################################################
    #                                    Parameters subsetting/groups                                   #
    #####################################################################################################
    
    make_option(c("-B","--statSubset"),  type="logical", default=FALSE,
                help="Whether statistics should be done only on a subset. (defined in --statFile) \ndefault = [%default] "),
    
    make_option(c("-G", "--reOrder"), type="logical", default=TRUE,
                help="boolean for whether the arrays have to be ordered per group in the plots FALSE keeps the order of the description file, TRUE reorders per group \ndefault = [%default] "),

    #####################################################################################################
    #                                         Parameters plots                                          #
    #####################################################################################################
    
    make_option(c("-p", "--performStatistics"), type="logical", default=TRUE,
                help="Should clustering and PCA be done alongside the normalization of the data? (Individual options are below) \ndefault = [%default] "), 
    
    make_option("--rawDataQC", type="logical", default=TRUE,
                help="Determine whether to do QC assessment for the raw data; if false no summary can be computed. \ndefault = [%default] "), 
    
    make_option("--normDataQC", type="logical", default=TRUE,
                help="Determine whether to do QC assessment for the normed data; if false no summary can be computed. \ndefault = [%default] "), 
    
    make_option(c("-F", "--layoutPlot"), type="logical", default=TRUE,
                help="boolean for plot of the array layout \ndefault = [%default] "),
    
    make_option(c("-H", "--controlPlot"), type="logical", default=TRUE,
                help="boolean for plots of the AFFX controls on the arrays \ndefault = [%default] "),
    
    make_option(c("-s", "--samplePrep"), type="logical", default=TRUE,
                help="boolean for Sample prep controls \ndefault = [%default] "),
    
    make_option(c("-r", "--ratioPlot"), type="logical", default=TRUE,
                help="boolean for 3?/5? for b-actin and GAPDH \ndefault = [%default] "),
    
    make_option(c("-e", "--degPlot"), type="logical", default=TRUE,
                help="boolean for DNA degration plot \ndefault = [%default] "),
    
    make_option(c("-h", "--hybridPlot"), type="logical", default=TRUE,
                help="boolean for Spike-in controls \ndefault = [%default] "),
    
    make_option(c("-p", "--percPres"), type="logical", default=TRUE,
                help="boolean for Percent present \ndefault = [%default] "),
    
    make_option(c("-n", "--posnegDistrib"), type="logical", default=TRUE,
                help="boolean for +and - controls distribution \ndefault = [%default] "),
    
    make_option(c("-b", "--bgPlot"), type="logical", default=TRUE,
                help="boolean for Background intensity \ndefault = [%default] "),
    
    make_option(c("-f", "--scaleFact"), type="logical", default=TRUE,
                help="boolean for Scale factor \ndefault = [%default] "),
    
    make_option(c("-x", "--boxplotRaw"), type="logical", default=TRUE,
                help="boolean for Raw boxplot of log-intensity \ndefault = [%default] "),
    
    make_option(c("-X", "--boxplotNorm"), type="logical", default=TRUE,
                help="boolean for Norm boxplot of log-intensity \ndefault = [%default] "),
    
    make_option(c("--densityRaw"), type="logical", default=TRUE,
                help="boolean for Raw density histrogram \ndefault = [%default] "),
    
    make_option(c("--densityNorm"), type="logical", default=TRUE,
                help="boolean for Norm density histrogram \ndefault = [%default] "),
    
    make_option(c("-k", "--MARaw"), type="logical", default=TRUE,
                help="boolean for Raw MA-plot \ndefault = [%default] "),
    
    make_option(c("-K", "--MANorm"), type="logical", default=TRUE,
                help="boolean for Norm MA-plot \ndefault = [%default] "),
    
    make_option(c("--MAOption1"), type="character", default="dataset",
                help="two possible values: group or dataset \ndefault = [%default] "),
    
    make_option(c("-R", "--spatialImage"), type="logical", default=TRUE,
                help="boolean for 2D images \ndefault = [%default] "),
    
    make_option(c("-W", "--PLMimage"), type="logical", default=TRUE,
                help="boolean for 2D PLM plots \ndefault = [%default] "),
    
    make_option(c("-N", "--posnegCOI"), type="logical", default=TRUE,
                help="boolean for + and ? controls COI plot \ndefault = [%default] "),
    
    make_option(c("-u", "--Nuse"), type="logical", default=TRUE,
                help="boolean for NUSE \ndefault = [%default] "),
    
    make_option(c("-a", "--Rle"), type="logical", default=TRUE,
                help="boolean for RLE \ndefault = [%default] "),
    
    make_option(c("-c", "--correlRaw"), type="logical", default=TRUE,
                help="boolean for Raw correlation plot \ndefault = [%default] "),
    
    make_option(c("-C", "--correlNorm"), type="logical", default=TRUE,
                help="boolean for Norm correlation plot \ndefault = [%default] "),
    
    make_option(c("-o", "--clusterRaw"), type="logical", default=TRUE,
                help="boolean for Raw hierarchical clustering \ndefault = [%default] "),
    
    make_option(c("-O", "--clusterNorm"), type="logical", default=TRUE,
                help="boolean for Norm hierarchical clustering \ndefault = [%default] "),
    
    make_option(c("-v", "--clusterOption1"), type="character", default="Spearman",
                help="possible values for Distance: (Spearman, Pearson, Euclidian) \ndefault = [%default] "),
    
    make_option(c("-w", "--clusterOption2"), type="character", default="ward",
                help="possible values for Tree: (ward, singlecomplete, average, mcquitty, median, centroid)  \ndefault = [%default] "),
    
    make_option(c("-t", "--PCARaw"), type="logical", default=TRUE,
                help="boolean for PCA analysis of raw data \ndefault = [%default] "),
    
    make_option(c("-T", "--PCANorm"), type="logical", default=TRUE,
                help="boolean for PCA analysis of normalized data \ndefault = [%default] "),
    
    make_option(c("-P", "--PMAcalls"), type="logical", default=FALSE,
                help="boolean for Present/Marginal/Absent calls using MAS5 \ndefault = [%default] "),
    
    #####################################################################################################
    #                             Display parameters for the images                                     #
    #####################################################################################################
    
    make_option("--img.width", type="numeric", default=1920,
                help="The max. width of the plots. \ndefault = [%default] "), 
    
    make_option("--img.heigth", type="numeric", default=1080,
                help="The max. heigth of the plots. \ndefault = [%default] "), 
    
    make_option("--img.pointSize", type="numeric", default=24,
                help="The size of the points on plots. \ndefault = [%default] "), 
    
    make_option("--img.maxArray", type="numeric", default=41,
                help="The maximum datapoint on each plot per page. \ndefault = [%default] ")
)
  
  #Get a list of the named parameters that were given when running this script
  userParameters <- parse_args(OptionParser(option_list = option_list), commandArguments)
  
  #Check the validity of the parameters and also create the log file if needed
  userParameters <- checkUserInput(userParameters, arrayTypeList, arrayAnnoList)
  
  #Return the parameters if all were valid
  return(userParameters)
}

#Check if the required parameters are all valid. Create a log file is createLog == TRUE
#Also check if the given species, array type and array annotation combi is valid.
checkUserInput <-function(userParameters, arrayTypeList, arrayAnnoList) {
  #Check if the directories exist, also clean their path if not properly closed of with last /
  userParameters$scriptDir      <- correctDirectory(userParameters$scriptDir)
  userParameters$inputDir       <- correctDirectory(userParameters$inputDir)
  userParameters$outputDir      <- correctDirectory(userParameters$outputDir)      

  if (file.info(userParameters$scriptDir)$isdir == FALSE){
    message <- paste("\nThe script directory does not exist:",userParameters$scriptDir, sep="")
    cat(message)
    changeJobStatus(con, userParameters$idJob, 2, message)
    if(userParameters$createLog) sink()
    stop(message)
  }
  
  if (file.info(userParameters$inputDir)$isdir == FALSE){
    message <- paste("\nThe input directory does not exist:",userParameters$inputDir, sep="")
    cat(message)
    changeJobStatus(con, userParameters$idJob, 2, message)
    if(userParameters$createLog) sink()
    stop(message)
  }
  
  #If statistics should only be performed on a smaller subset of samples, check if the file containing the sampleNames of this subset exist.
  if(userParameters$statSubset){
    if (file.exists(paste(userParameters$statisticsDir, userParameters$statFile, sep="")) == FALSE){
      message <- paste("\nNo statFile in path:", paste(userParameters$statisticsDir, userParameters$statFile, sep=""), sep=" ")
      cat(message)
      changeJobStatus(con, userParameters$idJob, 2, message)
      if(userParameters$createLog) sink()
      stop(message)
    }        
  }
  
  #Make output directory if not yet exist
  if (file.info(userParameters$outputDir)$isdir == FALSE){
    dir.create(userParameters$outputDir)
    if(!file.info(userParameters$outputDir)$isdir){
      if(userParameters$createLog) sink()
      message <- paste("\nThe output directory does not exist and cannot be created:",userParameters$outputDir, sep="")
      cat(message)
      changeJobStatus(con, userParameters$idJob, 2, message)
      if(userParameters$createLog) sink()
      stop(message)
    }
  }
  if(!userParameters$loadOldNorm){
    #Check if the paths to the input files are all valid
    if (file.exists(paste(userParameters$inputDir, userParameters$sampleProbeProfilePath, sep="")) == FALSE){
      message <- paste("\nNo Sample Probe Profile in path:", paste(userParameters$inputDir, userParameters$sampleProbeProfilePath, sep=""), sep=" ")
      cat(message)
      changeJobStatus(con, userParameters$idJob, 2, message)
      if(userParameters$createLog) sink()
      stop(message)
    }
    # Check for any .CEL file
    if (list.files(userParameters$inputDir, pattern="*.cel", ignore.case = TRUE) == 0){
      message <- paste("\nNo .CEL files in path:", userParameters$inputDir, sep=" ")
      cat(message)
      changeJobStatus(con, userParameters$idJob, 2, message)
      if(userParameters$createLog) sink()
      stop(message)
    }
  }else{
    if (file.exists(paste(userParameters$inputDir, userParameters$normData, sep="")) == FALSE){
      message <- paste("\nNo normalized R object:", paste(userParameters$inputDir, userParameters$normData, sep="") , sep=" ")
      cat(message)
      changeJobStatus(con, userParameters$idJob, 2, message)
      if(userParameters$createLog) sink()
      stop(message)
    }         
  }
  return(userParameters)
}

#Amend paths of directories if not started or closed off correctly with /
correctDirectory <- function(dirName) {
  lastChar <- substr(dirName,nchar(dirName)-1,nchar(dirName))
  if(lastChar != "/"){
    dirName <- paste(dirName,"/",sep="")
  }
  return(dirName)
}