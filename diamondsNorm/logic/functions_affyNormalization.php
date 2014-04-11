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

?>

<?php 
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
		
		
		$changeThis;
		//Make a normAnalysis record in the DB
		$connection->query("INSERT INTO tNormAnalysis (`idStudy`, `description`, normType, bgCorrectionMethod, varStabMethod, normMethod, filterThreshold)
				VALUES ($idStudy, 'Normalization is running, see idJob: $idJob', '".$GET['normType'] ."', '". (isset($GET['performBackgroundCorrection']) ? $GET['bgCorrect_m'] : 'None') ."', '".(isset($GET['performVarianceStabilization']) ? $GET['variance_Stab_m'] : 'None') ."', '".$GET['normalization_m'] ."', '".(isset($GET['filtering']) ? $GET['detectionTh'] : 'None') ."');");

		$idNorm = mysqli_insert_id($connection);

		///////////////////////////////////////////////////////////////////
		// 		Check if the all required raw files are present in DB	///
		///////////////////////////////////////////////////////////////////
		
		// Get the correct folder in which the raw output has been stored
		$queryFiles = ("SELECT idFileType, folderName FROM vFilesWithInfo WHERE idStudy = $idStudy AND idFileType = $changeThisToFileTypeCELL;");
		
		if ($resultFiles =  mysqli_query($connection, $queryFiles)) {
			while ($row = mysqli_fetch_assoc($resultFiles)) {
				$dataFolder = CONFIG_MAINFOLDER."/data/";
				$mainFolder = $dataFolder.$idStudy."_".$studyTitle;
				$inputCELLFolder = $mainFolder."/".$row['folderName']."/";
			}
		}
		
		//If file not in DB
		if(!isset($inputCELLFolder)){
			$connection->query("UPDATE tJobStatus SET status = 2, statusMessage = 'Failed: Could not find the directory in which the raw .cell files were stored! WHERE idJob = '$idJob'");
			exit("<p><font color=red>Could not find the .CELL files in the DB!</font></p>");
		}
		//If file not on fileserver
		elseif(!is_dir($inputCELLFolder)){
			$connection->query("UPDATE tJobStatus SET status = 2, statusMessage = 'Failed: Could not find the input directory of the .CELL files on the fileserver on: $inputCELLFolder! WHERE idJob = '$idJob'");
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
		makeDescriptionFile($connection ,$normFolder, $groupAttributes, $idStudy, $idJob, (isset($GET['skipNoAssayName']) ? $GET['skipNoAssayName'] : 'off') , FALSE);
		
		///////////////////////////////////////////////////////////////////
		// 	Build all the arguments which are supplied to pipeline		///
		///////////////////////////////////////////////////////////////////
		
		//Get information about the study such as species, array used etc.
		$querySpecies = ("SELECT speciesName FROM vStudyWithTypeNames WHERE idStudy = $idStudy;");
		$species;
		if ($resultSpecies =  mysqli_query($connection, $querySpecies)) {
			while ($row = mysqli_fetch_assoc($resultSpecies)) {
				$species = $row['speciesName'];
			}
		}
		
		//Get the correct arrayType and annoType
		$queryArray = ("SELECT annoType, arrayType FROM vStudyWithTypeNames WHERE idStudy = $idStudy;");
		if ($resultArray =  mysqli_query($connection, $queryArray)) {
			while ($row = mysqli_fetch_assoc($resultArray)) {
				$annoType = $row['annoType'];
				$arrayType = $row['arrayType'];
			}
		}
			
		//Make a string of all the possible arguments a user can manipulate.
		$scriptFolder = CONFIG_MAINFOLDER."/R/";
		
		//Check if some of the options should not be performed.
		//Should background correction be skipped?
		$bgSub = "TRUE";
		
		if(isset($GET['performBackgroundCorrection']) && $GET['performBackgroundCorrection'] == "on"){
			$bgSub = "FALSE";
		}
		
		$filtering = "TRUE";
		if(!$GET['filtering']){
			$filtering = "FALSE";
		}
		
		//Should variance stabilization be skipped?
		$varStab = "FALSE";
		if(isset($GET['performVarianceStabilization']) && $GET['performVarianceStabilization'] == "on"){
			$varStab = "TRUE";
		}
		
		//Make a record in the tStatistics table if statistics should be run
		$performStat = "FALSE";
		
		//Should statistics be skipped?
		if(isset($GET['performStatistics']) && $GET['performStatistics'] == "on"){
			$connection->query("INSERT INTO tStatistics (`idNormAnalysis`, `groupedOn`, description) VALUES ($idNorm, '$groupedOn', '".$GET['descStat']."');");
			$idStat = mysqli_insert_id($connection);
		
		
			//Get the correct folder in which to store the statistics output from the DB
			$query = ("SELECT folderName FROM tDirectory WHERE idDirectory = 5");
		
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
		if(isset($GET['selectedSamples']) && $GET['selectedSamples'] != "0"){
			$statSubset = "TRUE";
			echo "<p><font color=orange>Creating statSubsetFile.txt.</font><p>";
			$sampleIDList = explode(",", $GET['selectedSamples']);
			
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
			$connection->query("INSERT INTO tFiles (`idStudy`, `idFileType`, `fileName`, idStatistics) VALUES ($idStudy, '31', 'statSubsetFile.txt', $idStat);");
			echo "<p><font color=green>Succesfully written a statSubsetFile file in folder: ".$statFolder."</font><p>";
		}
		
		$reOrderGroup = "FALSE";
		//Should the plots be ordered based on the groups?
		if(isset($GET['reorderSamples']) && $GET['reorderSamples'] == "on"){
			$reOrderGroup = "TRUE";
		}
		
		$arguments = ("--inputDir $inputFolder
				--outputDir $normFolder
				--scriptDir $scriptFolder
				--statisticsDir ". (isset($statFolder) ? $statFolder : '/noFolder/')."
				--species $species
				--arrayType $arrayType
				--annoType $annoType
				--studyName $studyTitle
				--idStudy $idStudy
				--idJob $idJob
				--idNorm $idNorm
				--statSubset $statSubset
				--statFile ". (isset($statFile) ? $statFile : 'none')."
				--createLog FALSE
				-S ". (isset($idStat) ? $idStat : '0')."
				-s $sampleProbeProfileName
				-c $controlProbeProfileName
				-d descriptionFile.txt
				--bgSub $bgSub
				--detectionTh ".$GET['detectionTh']."
				--normType ".$GET['normType']."
				--bgcorrect.m ".$GET['bgCorrect_m']."
				--variance.stabilize $varStab
				--variance.m ".$GET['variance_Stab_m']."
				--normalization.m ".$GET['normalization_m']."
				--filtering $filtering
				--filter.Th ".$GET['filter_Th']."
				--filter.dp ".$GET['filter_dp']."
				--performStatistics $performStat
				--perGroup $reOrderGroup
				--raw.boxplot ".(isset($GET['raw_boxplot']) ? 'TRUE' : 'FALSE')."
				--raw.density ".(isset($GET['raw_density']) ? 'TRUE' : 'FALSE')."
				--raw.cv ".(isset($GET['raw_cv']) ? 'TRUE' : 'FALSE')."
				--raw.sampleRelation ".(isset($GET['raw_sampleRelation']) ? 'TRUE' : 'FALSE')."
				--raw.pca ".(isset($GET['raw_pca']) ? 'TRUE' : 'FALSE')."
				--raw.correl ".(isset($GET['raw_correl']) ? 'TRUE' : 'FALSE')."
				--norm.boxplot ".(isset($GET['norm_boxplot']) ? 'TRUE' : 'FALSE')."
				--norm.density ".(isset($GET['norm_density']) ? 'TRUE' : 'FALSE')."
				--norm.cv ".(isset($GET['norm_cv']) ? 'TRUE' : 'FALSE')."
				--norm.sampleRelation ".(isset($GET['norm_sampleRelation']) ? 'TRUE' : 'FALSE')."
				--norm.pca ".(isset($GET['norm_pca']) ? 'TRUE' : 'FALSE')."
				--norm.correl ".(isset($GET['norm_correl']) ? 'TRUE' : 'FALSE')."
				--clusterOption1 ".$GET['clustoption1']."
				--clusterOption2 ".$GET['clustoption2']."
				--saveToDB ".CONFIG_SAVENORMEDEXPRESSIONS."
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
			echo("<p>Debugging is on, printing the exec statement and NOT actually running the statement! <br>Change this in the config.php<p>");
			echo("nice -n 19 Rscript ".CONFIG_MAINFOLDER."/R/runAffymetrixNormalization.R ".$arguments." > ".CONFIG_MAINFOLDER."/log &");
		}
		else{
			exec("nice -n 19 Rscript ".CONFIG_MAINFOLDER."/R/runAffymetrixNormalization.R ".$arguments." > ".CONFIG_MAINFOLDER."/log &");
		}
	}

?>