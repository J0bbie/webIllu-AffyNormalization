<?php
	/*
	 Author:				Job van Riet + Jordy Coolen + ArrayAnalysis.org (Affy module)
	Date of  creation:		10-4-14
	Date of modification:	10-4-14
	Version:				1.0
	Modifications:			Original version
	Known bugs:				None known
	Function:				This file houses the functions to perform Affymetrix normalizations on samples uploaded to the DIAMONDS DB.
							The actual normalization is done by a slightly altered ArrayAnalysis.org R script for affymetrix normalisation, this script is run as a background deamon.
	*/

	//Include the scripts containing the config variables
	require_once('../logic/config.php');

	// Show PHP errors if config has this enabled
	if(CONFIG_ERRORREPORTING){
		error_reporting(E_ALL);
		ini_set('display_errors', '1');
	}
	
	//Include script with added functionality (Connecting to DIAMONDS DB)
	require_once('../logic/functions_dataDB.php');
	
	//Include script with added functionality (Handling the files)
	require_once('../logic/functions_fileHandling.php');
	
	///////////////////////////////////////////////////////////////////
	// 		Main function to produce the exec to normalize study	///
	///////////////////////////////////////////////////////////////////
	
	//Main function for the normalization of Illumina Beadchip data, this function will call other functions
	function normalizeAffyStudy($GET, $idStudy, $studyTitle){
		//Make a connection to the DIAMONDSDB (Defined in: functions_dataDB.php)
		$connection = makeConnectionToDIAMONDS();
		
		//Get the grouping options and put them in an array for easier handling
		$groupAttributes = explode(",",$GET['selectedAttributes']);
		
		//Convert the idDataTypes into their respective names for easier identification to the user
		//Except when compound and Sampletype are given as these are no idDataTypes
		$groupedOn = "";
		foreach($groupAttributes as $attr){
			if($attr != "compound" && $attr != "sampleType"){
				$query = ("SELECT name FROM tDataType WHERE idDataType = $attr");
		
				if ($result =  mysqli_query($connection, $query)) {
					while ($row = mysqli_fetch_assoc($result)) {
						$groupedOn.=$row['name'].'_';
					}
				}
			}
			else{
				$groupedOn.=$attr.'_';
			}
		}
		
		//Delete the last _ symbol
		if($groupedOn != ""){
			$groupedOn = substr($groupedOn, 0, -1);
		}else{
			$groupedOn = "None";
		}
			
		//Make a jobStatus in the DB
		$connection->query("INSERT INTO tJobStatus (`idStudy`, `name`, `description`, status, statusMessage) VALUES ($idStudy, 'Normalizing samples', 'Normalization of expression data.', 0, 'Running');");
		$idJob = mysqli_insert_id($connection);
		
		//Make a normAnalysis record in the DB
		$connection->query("INSERT INTO tNormAnalysis (`idStudy`, `description`, normType, bgCorrectionMethod, varStabMethod, normMethod)
				VALUES ($idStudy, '".$GET['descNorm'] ."', 'affyMetrix', 'PMM', 'None', '".$GET['normMethod'] ."');");

		$idNorm = mysqli_insert_id($connection);

		///////////////////////////////////////////////////////////////////
		// 		Check if the all required raw files are present in DB	///
		///////////////////////////////////////////////////////////////////
		
		// Get the correct folder in which the raw output has been stored
		$queryFiles = ("SELECT idFileType, folderName FROM vFilesWithInfo WHERE idStudy = $idStudy AND idFileType = 14;");
		
		if ($resultFiles =  mysqli_query($connection, $queryFiles)) {
			while ($row = mysqli_fetch_assoc($resultFiles)) {
				$dataFolder = CONFIG_MAINFOLDER."/data/";
				$mainFolder = $dataFolder.$idStudy."_".$studyTitle;
				$inputCELLFolder = $mainFolder."/".$row['folderName']."/";
			}
		}
		
		//If file not in DB
		if(!isset($inputCELLFolder)){
			$connection->query("UPDATE tJobStatus SET status = 2, statusMessage = 'Failed: Could not find the directory in which the raw .cell files were stored!' WHERE idJob = '$idJob'");
			exit("<p><font color=red>Could not find the .CELL files in the DB!</font></p>");
		}
		//If file not on fileserver
		elseif(!is_dir($inputCELLFolder)){
			$connection->query("UPDATE tJobStatus SET status = 2, statusMessage = 'Failed: Could not find the input directory of the .CELL files on the fileserver on: $inputCELLFolder!' WHERE idJob = '$idJob'");
			exit("<p><font color=red>Could not find the directory on the server where the .CELL files are stored on the fileserver: $inputCELLFolder!</font></p>");
		}else{
			echo "<p><font color=green>Input directory of .CELL files are found in both the DB and fileserver!</font></p>";
		}
		
		///////////////////////////////////////////////////////////////////
		// 			Create/set folders to store norm/stat data			///
		///////////////////////////////////////////////////////////////////
		
		//Get the correct folder in which to store the normalized data output from the DB
		$query = ("SELECT folderName FROM tDirectory WHERE idDirectory = 4");
		
		if ($result =  mysqli_query($connection, $query)) {
			while ($row = mysqli_fetch_assoc($result)) {
				$directoryName = $row['folderName'];
			}
		}
		if(!isset($directoryName)){
			$connection->query("UPDATE tJobStatus SET status = 2, statusMessage = 'Failed: Could not retrieve the folder definition  the output of normalized expression data. Probably not filled in the DB' WHERE idJob = '$idJob'");
			exit("<p><font color=red>Could not retrieve the folder definition for the output of normalized expression data. Probably not filled in the DB.</font></p>");
		}
		
		#Add the idNorm to the output directory (expression/normed/idNorm/)
		$directoryName = $directoryName."/".$idNorm;
		
		//Make the folderName for the output of normalized data.
		//Create the folder if not created yet (Defined in: functions_filehandling.php)
		$normFolder = checkFolderStructure($connection, $idStudy, $studyTitle, $directoryName, $idJob);

		///////////////////////////////////////////////////////////////////
		// 						Make description file					///
		///////////////////////////////////////////////////////////////////
		
		//Make a description file
		makeDescriptionFile($connection ,$normFolder, $groupAttributes, $idStudy, $idJob, (isset($GET['skipNoArrayName']) ? $GET['skipNoArrayName'] : 'off') , FALSE, (isset($GET['selectedNormalizationSamples']) AND $GET['selectedNormalizationSamples'] != 0  ? $GET['selectedNormalizationSamples'] : '0'));
				
		///////////////////////////////////////////////////////////////////
		// 	Build all the arguments which are supplied to pipeline		///
		///////////////////////////////////////////////////////////////////
			
		//Make a string of all the possible arguments a user can manipulate.
		$scriptFolder = CONFIG_MAINFOLDER."/R/affymetrixNorm/";
		
		//Check if some of the options should not be performed.

		//Make a record in the tStatistics table if statistics should be run
		$performStat = "FALSE";
		
		//Does this study have a custom annotation file
		$customAnnotation = "FALSE";
		$customAnnotationFile = "none";
		
		if(isset($GET['customAnnotation']) && $GET['customAnnotation'] == "on"){
			$customAnnotation = "TRUE";
			$query = ("SELECT fileName FROM tFiles WHERE idFileType = 13 LIMIT 1");
			if ($result =  mysqli_query($connection, $query)) {
				while ($row = mysqli_fetch_assoc($result)) {
					$customAnnotationFile = $row['fileName'];
				}
			}	
		}
		
		//Should statistics be skipped?
		if(isset($GET['performStatistics']) && $GET['performStatistics'] == "on"){
			$connection->query("INSERT INTO tStatistics (`idNormAnalysis`, `groupedOn`, description) VALUES ($idNorm, '$groupedOn', '".$GET['descStat']."');");
			$idStat = mysqli_insert_id($connection);
		
			//Get the correct folder in which to store the statistics output from the DB
			$query = ("SELECT folderName FROM tDirectory WHERE idDirectory = 5 LIMIT 1");
		
			if ($result =  mysqli_query($connection, $query)) {
				while ($row = mysqli_fetch_assoc($result)) {
					$directoryName = $row['folderName'];
				}
			}
		
			#Make a new normQCResult/idStat folder
			$directoryName = $directoryName."/".$idStat."/";
		
			$statFolder = checkFolderStructure($connection, $idStudy, $studyTitle, $directoryName, $idJob);
		}else{
			echo "<p><font color=orange>Skipping statistics.</font><p>";
		}
		
		//Check if statistics should only be performed on a smaller subset of samples, if so, create a statFile.txt containing these sampleNames.
		$statSubset = "FALSE";
		//Should subsetting be skipped?
		if(isset($GET['selectedStatisticsSamples']) && $GET['selectedStatisticsSamples'] != "0"){
			$statSubset = "TRUE";
			echo "<p><font color=orange>Creating statSubsetFile.txt.</font><p>";
			$sampleIDList = explode(",", $GET['selectedStatisticsSamples']);
			
			//Open a file + fileHandler to make the statSubsetFile, save the file in the statistics folder and DB also.
			$statFile = "statSubsetFile.txt";
			$statFilePath = $statFolder."/".$statFile;
			$fileHandlerStat = fopen($statFolder."/statSubsetFile.txt", "w");
			if(!$fileHandlerStat){
				$connection->query("UPDATE tJobStatus SET status = 2, statusMessage = 'Failed: Could not make/write a statSubsetFile file in folder: ".$statFolder."' WHERE idJob = '$idJob'");
				exit("<p><font color=red>Could not make/write a description file in folder: $statFolder.</font></p>");
			}
				
			//Get the sampleNames
			foreach ($sampleIDList as &$idSample) {
				if ($result =  mysqli_query($connection, "SELECT name FROM tSamples WHERE idSample = $idSample")) {
					while ($row = mysqli_fetch_assoc($result)) {
					fwrite($fileHandlerStat, $row['name']."\n");
					}
				}
			}
							
			//Close the file
			fclose($fileHandlerStat);
				
			//Add the file to the DB
			$connection->query("INSERT INTO tFiles (`idStudy`, `idFileType`, `fileName`, idStatistics) VALUES ($idStudy, '71', 'statSubsetFile.txt', $idStat);");
			echo "<p><font color=green>Succesfully written a statSubsetFile file in folder: ".$statFolder."</font><p>";
		}
		
		$reOrderGroup = "FALSE";
		//Should the plots be ordered based on the groups?
		if(isset($GET['reorderSamples']) && $GET['reorderSamples'] == "on"){
			$reOrderGroup = "TRUE";
		}
		
		$arguments = ("--inputDir $inputCELLFolder
				--outputDir $normFolder
				--scriptDir $scriptFolder
				--statisticsDir ". (isset($statFolder) ? $statFolder : '/normFolder/')."
				--species ".$GET['species']."
				--studyName $studyTitle
				--idStudy $idStudy
				--idJob $idJob

				--descFile descriptionFile.txt

				--useCustomAnnotation $customAnnotation
				--customAnnotation $customAnnotationFile

				--idNorm $idNorm
				--normMeth ".$GET['normMethod']."
				--normSubset ".(($GET['selectedNormalizationSamples'] != 0) ? 'TRUE' : 'FALSE')."
				
				--normOption1 ".(isset($GET['normPerGroup']) ? 'group' : 'dataset')."
				--CDFtype ".(isset($GET['annotationType']) ? 'TRUE' : 'FALSE')."
				
				--performStatistics $performStat
				--perGroup $reOrderGroup
				--statSubset $statSubset
				--statFile ". (isset($statFile) ? $statFile : 'none')."
				--idStatistics ". (isset($idStat) ? $idStat : '0')."
				
				--layoutPlot ".(isset($GET['plotArrayReferceLayout']) ? 'TRUE' : 'FALSE')."
				--controlPlot ".(isset($GET['SampleQualityPlot']) ? 'TRUE' : 'FALSE')."
				--samplePrep ".(isset($GET['SampleQualityPlot']) ? 'TRUE' : 'FALSE')."
				--ratioPlot ".(isset($GET['35RatioPlot']) ? 'TRUE' : 'FALSE')."
				--degPlot ".(isset($GET['rnaDegradationPlot']) ? 'TRUE' : 'FALSE')."
				--hybridPlot ".(isset($GET['plotHybrid']) ? 'TRUE' : 'FALSE')."
				--percPres ".(isset($GET['plotPercPres']) ? 'TRUE' : 'FALSE')."
				--posnegDistrib ".(isset($GET['plotPosNegControls']) ? 'TRUE' : 'FALSE')."
				--bgPlot ".(isset($GET['plotBackIntens']) ? 'TRUE' : 'FALSE')."
				--scaleFact ".(isset($GET['plotScaleFactors']) ? 'TRUE' : 'FALSE')."
				--boxplotRaw ".(isset($GET['plotBoxRawLogIntensity']) ? 'TRUE' : 'FALSE')."
				--boxplotNorm ".(isset($GET['plotBoxNormLogIntensity']) ? 'TRUE' : 'FALSE')."
				--densityRaw ".(isset($GET['plotDensityRawLogIntensity']) ? 'TRUE' : 'FALSE')."
				--densityNorm ".(isset($GET['plotDensityRawLogIntensity']) ? 'TRUE' : 'FALSE')."
				--MARaw ".(isset($GET['plotRawMA']) ? 'TRUE' : 'FALSE')."
				--MANorm ".(isset($GET['plotNormMA']) ? 'TRUE' : 'FALSE')."
				--MAOption1 ".(isset($GET['normPerGroup']) ? 'group' : 'dataset')."
				--spatialImage ".(isset($GET['plot2DImages']) ? 'TRUE' : 'FALSE')."
				--PLMimage ".(isset($GET['plotPLM']) ? 'TRUE' : 'FALSE')."
				--posnegCOI ".(isset($GET['plotPosNegCenterOfIntensity']) ? 'TRUE' : 'FALSE')."
				--Nuse ".(isset($GET['plotNUSE']) ? 'TRUE' : 'FALSE')."
				--Rle ".(isset($GET['plotRLE']) ? 'TRUE' : 'FALSE')."
				--correlRaw ".(isset($GET['plotRawArrayCorrelation']) ? 'TRUE' : 'FALSE')."
				--correlNorm ".(isset($GET['plotNormArrayCorrelation']) ? 'TRUE' : 'FALSE')."
				--clusterRaw ".(isset($GET['"plotRawCluster"']) ? 'TRUE' : 'FALSE')."
				--clusterNorm ".(isset($GET['"plotNormCluster"']) ? 'TRUE' : 'FALSE')."
				--clusterOption1 ".$GET['clustoption1']."
				--clusterOption2 ".$GET['clustoption2']."
				--PCARaw ".(isset($GET['plotRawPCA']) ? 'TRUE' : 'FALSE')."
				--PCANorm ".(isset($GET['plotNormPCA']) ? 'TRUE' : 'FALSE')."
				--PMAcalls ".(isset($GET['plotCalls']) ? 'TRUE' : 'FALSE')."
				--saveToDB ".((CONFIG_SAVENORMEDEXPRESSIONS) ? 'TRUE' : 'FALSE')."
		");
		
		///////////////////////////////////////////////////////////////////
		// 	Run or print the exec statement with the supplied arguments	///
		///////////////////////////////////////////////////////////////////
		
		// Perform the R script with as a -19 nice job and in as a background deamon/thread.
		// This prevents the server from allocating all the resources to the normalization pipeline
		echo ("<p><font color=orange>Running normalization on samples using a background process and using a limited amount of CPU power.</font></p>");
		
		if(!CONFIG_SAVENORMEDEXPRESSIONS){
			echo("<p>Debugging is on, not saving the normalized expressions into the DB! <br>Change this in the config.php<p>");
		}
		
		// Print or exec the pipeline arguments
		if(CONFIG_RUNPIPELINES){
			$execString = "nice -n 19 Rscript ".CONFIG_MAINFOLDER."/R/affymetrixNorm/runAffymetrixNormalization.R ".$arguments." > /dev/null 2>/dev/null &";
			$execString = str_replace("\n", " ", $execString);
			print $execString;
			//shell_exec($execString);
		}
		else{
			echo("<p>Debugging is on, printing the exec statement and NOT actually running the statement! <br>Change this in the config.php<p>");
			echo("nice -n 19 Rscript ".CONFIG_MAINFOLDER."/R/affymetrixNorm/runAffymetrixNormalization.R ".$arguments." > /dev/null 2>/dev/null &");
		}
	}

?>