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
SCRIPT.DIR <- paste(configMainFolder,"R","affyNorm",sep="/")

#Functions to access the normDB/DIAMONDS
source(paste(SCRIPT.DIR,"functions_myDB.R",sep="/"))

#Make a connection to the DB
con <- makeConnection()

#Functions to get the passed parameters and set the default values of all parameters used in this pipeline
source(paste(SCRIPT.DIR,"getArguments.R",sep="/"))

#Get the command-line parameters that were given to this script (Parameters defined in getArguments.R)
#Also check the validity of these parameters and directories
userParameters <- getArguments(commandArgs(trailingOnly = TRUE))

#Function to install missing libraries
source(paste(userParameters$scriptDir,"functions_loadPackages.R",sep="/"))

#Functions for the creation of the plots
source(paste(userParameters$scriptDir,"functions_imagesQC.R",sep="/"))

#Functions for QCing the raw and normalized data
source(paste(userParameters$scriptDir,"functions_processingQC.R",sep="/"))


