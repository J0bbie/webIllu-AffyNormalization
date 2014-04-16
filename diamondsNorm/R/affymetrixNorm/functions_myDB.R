#Author:                      Job van Riet
#Date of  creation:           16-4-14
#Date of modification:        16-4-14
#Version:                     1.0
#Modifications:               Original version
#Known bugs:                  None known
#Function:                    This script houses the functions to connect to the normDB/DIAMONDS and transfer data.

#####################################################################################################
#                   Load RMySQL package, if not found, try to install                               #
#####################################################################################################
inst<-installed.packages()

notInst <- "RMySQL" %in% inst

#If installed, simply load
if(notInst){
          library("RMySQL")
}else{
          #Try to install package and see if it was succesfull
          install.packages('RMySQL',type='source')
          #Get a new list of all the installed packages
          inst<-installed.packages()
          notInst <- "RMySQL" %in% inst
          if(notInst){
                    stop("\nCould not install RMySQL package, is MySQL installed on this server or do I have correct permissions to install?!")
          }
}

#####################################################################################################
#                                       Functions to interact with the DB                           #
#####################################################################################################

#Make a connection to the normDB/DIAMONDS database.
#Change the user/pass if appropiate.
makeConnection <- function(){
          con <- dbConnect(MySQL(), user=configDbUser, password=configDbUserPass, dbname=configDbSchema, host=configDbHost)
          return(con)
}

#Close the connection
closeConnection <- function(con){
          dbDisconnect(con)
          
}

changeJobStatus <- function(con, idJob, statusCode, message){
          dbSendQuery(con, paste("UPDATE tJobStatus SET status = '",statusCode,"', statusMessage = '", message, "' WHERE idJob = ", idJob, sep=""))            
}

#Retrieve the sample ID based on the sampleName and idStudy
getSampleID <- function (con, idStudy, sampleName){
          res <- dbSendQuery(con, paste("SELECT idSample FROM tSamples WHERE idStudy =", idStudy," AND name = '", sampleName , "'", sep=""))
          x <- fetch(res)
          dbClearResult(dbListResults(con)[[1]])
          return(x)
}

#Adds a norm file to the tFiles table
addNormFile <- function(idStudy, idFileType, idNorm, fileName){
          dbSendQuery(con, paste("INSERT INTO tFiles(idStudy, idFileType, idNorm, fileName) VALUES('",idStudy,"', '",idFileType,"', '",idNorm,"', '",fileName,"')", sep=""))
          dbClearResult(dbListResults(con)[[1]])
}

#Adds a stat file to the tFiles table
addStatFile <- function(idStudy, idFileType, idStatistics, fileName){
          dbSendQuery(con, paste("INSERT INTO tFiles(idStudy, idFileType, idStatistics, fileName) VALUES('",idStudy,"', '",idFileType,"', '",idStatistics,"', '",fileName,"');", sep=""))
          dbClearResult(dbListResults(con)[[1]])
}

#Adds the sample summaries to the DB
addSampleSummary <- function(con, idNorm, idSample, summaryData, normed){
          dbSendQuery(con, paste("INSERT INTO tSampleSummary(idNormAnalysis, idSample, meanSample, standardError, detectionRate_01, distanceToMeanSample, normed)
                      VALUES('",idNorm,"', '",idSample,"', '",summaryData[[1]],"', '",summaryData[[2]],"', '",summaryData[[3]],"', '",summaryData[[4]],"', '",normed,"')", sep=""))
          dbClearResult(dbListResults(con)[[1]])
}

#Add each expression value to the DB
addNormedExpressionPerGene <- function(con, idNorm, idVector, expressionData){
          #Get the probe ID, create if not yet exist
          probeData <- expressionData[c("nuIDs", "ILMN_GENE", "geneSymbol", "PROBE_ID", "ENTREZ_GENE_ID", "GENE_NAME", "ACCESSION")]
          idProbe <- getCreateProbe(con, probeData)
           #############################
          # Concatanate does not work!#
          #############################
          
          #Vectorize the expression values
          #values <- c(t(expressionData[9:length(eset.anno.normData)]))
          #Concatenate the sample Ids and expression values (sam1:exp1 sam2:exp2 sam3:exp3 etc.)
          #sampleValues <- paste(idVector, values, sep=";")
          #Collapse into string (sam1:exp1|sam2:exp2|sam3:exp3 etc.)
          #expressionString <- paste(sampleValues, collapse="|")
          
          #Loop over all genes
          for(i in 9:length(names(expressionData))){
            intensity <- expressionData[,i]
            idSample <- names(expressionData)[i]
            #Save the intensity to the DB
            dbSendQuery(con, paste("INSERT INTO tNormedExpression(expressionValue, idProbe, idSample, idNormAnalysis) VALUES('",intensity,"', '",idProbe,"', '",idSample,"', '",idNorm,"')", sep=""))
          }          
}

#Find a probe if it already exist, else create
getCreateProbe <- function (con, probeData){
          #Remove quotes
          probeData['GENE_NAME'] <- sub("'", "", probeData['GENE_NAME'])
          #Find a probe
          res <- dbSendQuery(con, paste("SELECT idProbe FROM tProbes WHERE nuID ='", probeData['nuIDs'],"'", sep=""))
          x <- fetch(res)
          idProbe <- x$idProbe
          dbClearResult(dbListResults(con)[[1]])
                    
          #If not found, create
          if(is.null(idProbe)){
                    dbSendQuery(con, paste("INSERT INTO tProbes(nuID, ilmnGene, probeID, entrezGeneID, geneSymbol, geneName, accessionName) 
                                VALUES('",probeData['nuIDs'],"', '",probeData['ILMN_GENE'],"', '",probeData['PROBE_ID'],"', '",probeData['ENTREZ_GENE_ID'],"',
                                '",probeData['geneSymbol'],"', '",probeData['GENE_NAME'],"', '",probeData['ACCESSION'],"');", sep=""))
                    res <- dbGetQuery(con, "select max(idProbe) as idProbe FROM tProbes;")
                    idProbe <- res$idProbe
                    dbClearResult(dbListResults(con)[[1]])
          }
          return(idProbe)          
}

#Update tNormAnalysis and show that this normalisation is complete.
updateNormDescription <- function(con, idNorm, message){
          dbSendQuery(con, paste("UPDATE tNormAnalysis SET description = '", message ,"' WHERE idNormAnalysis = ", idNorm, sep=""))  
}
