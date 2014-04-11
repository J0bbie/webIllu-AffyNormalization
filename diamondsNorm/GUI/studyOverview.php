<?php
/*
Author:					Job van Riet
Date of  creation:		11-2-14
Date of modification:	21-2-14
Version:				1.1
Modifications:			Original version
Known bugs:				None known
Function:				This page will present an overview of the study and also allows for the uploading of a file containing the samples and additional attributes.
						If a file is uploaded with the samples used in the study, only two columns are needed to be supplied: compoundName, sampleType (control/neg control/pos control etc.) and CAS-Number.
						All columns which are not compound name, sampleType and/or CAS-number will be stored in the tAttributes table as additional info for that given sample.
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
	
	// Get the idStudy from the session, if no session is made, let the user select a study.
	session_start ();
	
	if (isset ( $_SESSION ['idStudy'] )) {
		$idStudy = $_SESSION ['idStudy'];
	} else {
		// Redirect to studyOverview of this study
		header('Location: chooseStudy');
	}
?>


<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>Overview of study: <?php echo $idStudy; ?></title>
<!--Load CSS for form layout -->
<link rel="stylesheet" type="text/css" href="../css/formLayout.css" media="all" />

<!-- Include one of jTable styles. -->
<link href="../css/lightcolor/orange/jtable.css" rel="stylesheet" type="text/css" />
<link href="../css/jQueryUI.css" rel="stylesheet" type="text/css" />

<!--Load main jQuery library-->
<script src="../js/jquery-1.11.0.js" type="text/javascript"></script>
<!--Load jQueryUI-->
<script src="../js/jquery-ui.js" type="text/javascript"></script>

<!-- Include jTable script file. -->
<script src="../js/jquery.jtable.js" type="text/javascript"></script>

</head>

<!-- Include the menu -->
<div id="navBar">
	<?php require_once("menu.htm"); ?>
</div>


<body id="main_body">
	<!-- Form to show study info -->
	<img id="top" src="../img/top.png" alt="" />
	<div id="form_container">
		<h1>Create a new study</h1>
		<form id="showStudyInfo" class="appnitro" method="get" action="">
			<div class="form_description">
				<h2>Study overview</h2>
				<p>On this page you can see information about the selected study. Using the table you can alter information about this specific study.</p>
			</div>
			<ol>
				<li id="li_1"><label class="description" for="studyStatus">Operations performed on this study:</label>
					<div>
						<?php 
						//Make a connection to the DIAMONDSDB (Defined in: functions_dataDB.php)
						require_once('../logic/functions_dataDB.php');
						$connection = makeConnectionToDIAMONDS();
						
						/////////////////////////////////////////
						//		Check for running job			/
						/////////////////////////////////////////
						
						if ($result =  mysqli_query($connection, "SELECT count(idStudy) as count FROM tJobStatus WHERE idStudy = $idStudy AND status = 0;")) {
							while ($row = mysqli_fetch_assoc($result)) {
								if($row['count'] != 0){
									echo "<input type='checkbox' checked disabled /><font color='#04B431' >Has a running job? (".$row['count']." job running)</font>";
									}
								else{
									echo "<input type='checkbox' disabled /><font color='orange' >Has a running job?</font>";
								}
							}
						}
						
						//Check for failed job
						if ($result =  mysqli_query($connection, "SELECT count(idStudy) as count FROM tJobStatus WHERE idStudy = $idStudy AND status = 2;")) {
							while ($row = mysqli_fetch_assoc($result)) {
								if($row['count'] != 0){
									echo "<font color='red' > (".$row['count']." job failed) </font>";
								}
							}
						}
							
						/////////////////////////////////////////
						//		Check samples + assay name		/
						/////////////////////////////////////////
						
						if ($result =  mysqli_query($connection, "SELECT count(idStudy) as count FROM tSamples WHERE idStudy = $idStudy;")) {
							while ($row = mysqli_fetch_assoc($result)) {
								if($row['count'] != 0){
									echo "<br><input type='checkbox' checked disabled /><font color='green'>Have samples been added? (".$row['count']." samples) </font> <br>";
								}
								else{
									echo "<br><input type='checkbox' disabled /><font color='red'>Have samples been added?</font> <br>";
								}
							}
						}

						//Check if samples have assay names attached
						if ($result =  mysqli_query($connection, "SELECT count(idStudy) as count FROM tSamples WHERE idStudy = $idStudy AND assayName != 0 ;")) {
							while ($row = mysqli_fetch_assoc($result)) {
								if($row['count'] != 0){
									echo "<input type='checkbox' checked disabled /><font color='green'>Samples have assay names? (".$row['count']." samples) </font> <br>";
								}
								else{
									echo "<input type='checkbox' disabled /><font color='red'>Samples have assay names?</font> <br>";
								}
							}
						}
						
						/////////////////////////////////////////
						//		Check raw expression data		/
						/////////////////////////////////////////
						
						// If study has been run on Affymetrix
						if ($result =  mysqli_query($connection, "SELECT idStudy FROM tStudy WHERE idStudy = $idStudy AND platformType = 'affy' LIMIT 1;")) {
							while ($row = mysqli_fetch_assoc($result)) {
								$affyStudy = $row['idStudy'];
							}
						}
						
						if(isset($affyStudy)){
							
							// Get the count of cell files
							if ($result =  mysqli_query($connection, "SELECT count(idStudy) as count FROM tFiles WHERE idStudy = $idStudy AND idFileType = "SET THIS";")) {
								while ($row = mysqli_fetch_assoc($result)) {
									if($row['count'] != 0){
										echo "<input type='checkbox' checked disabled /><font color='green'>Uploaded CELL files: ".$row['count']." </font> <br>";
									}else{
										echo "<input type='checkbox' disabled /><font color='red'>No raw .CELL files uploaded!</font> <br>";
									}
								}
							}							
						}
						
						// If study has been run on Illumina
						if ($result =  mysqli_query($connection, "SELECT idStudy FROM tStudy WHERE idStudy = $idStudy AND platformType = 'illu' LIMIT 1;")) {
							while ($row = mysqli_fetch_assoc($result)) {
								$illuStudy = $row['idStudy'];
							}
						}
						
						if(isset($illuStudy)){
							if ($result =  mysqli_query($connection, "SELECT idStudy FROM tFiles WHERE idStudy = $idStudy AND idFileType = 4 LIMIT 1;" )) {
								while ($row = mysqli_fetch_assoc($result)) {
									$controlProbeProfile = $row['idStudy'];
								}
							}
							if ($result =  mysqli_query($connection, "SELECT idStudy as count FROM tFiles WHERE idStudy = $idStudy AND idFileType = 7 LIMIT 1;" )) {
								while ($row = mysqli_fetch_assoc($result)) {
									$sampleProbeProfile = $row['idStudy'];
								}
							}
							if(isset($controlProbeProfile) AND isset($sampleProbeProfile)){
								echo "<input type='checkbox' checked disabled /><font color='green'>Raw expression data from SXS uploaded? </font> <br>";
							}
							//If files are missing
							else{
								echo "<input type='checkbox' disabled /><font color='red'>Raw expression data from SXS uploaded?";
								//If no control probe profile
								if(isset($controlProbeProfile)){
									echo "<br>(No Control Probe Profile)";
								}
								//If no sample probe profile
								if(isset($sampleProbeProfile)){
									echo "<br>(No Sample Probe Profile)";
								}
								echo "</font> <br>";
							}
						}
		
						/////////////////////////////////////////
						//		Check custom assay annotation	/
						/////////////////////////////////////////
						
						if ($result =  mysqli_query($connection, "SELECT count(idStudy) as count FROM tFiles WHERE idStudy = $idStudy AND idFileType = "SET THIS";")) {
							while ($row = mysqli_fetch_assoc($result)) {
								if($row['count'] != 0){
									echo "<input type='checkbox' checked disabled /><font color='green'>Study has custom annotation file uploaded</font> <br>";
								}
							}
						}
						
						/////////////////////////////////////////
						//			Check normAnalysis			/
						/////////////////////////////////////////
						
						//Check if a normAnalyses has been run.
						if ($result =  mysqli_query($connection, "SELECT count(idStudy) as count FROM tNormAnalysis WHERE idStudy = $idStudy;")) {
							while ($row = mysqli_fetch_assoc($result)) {
								if($row['count'] != 0){
									echo "<input type='checkbox' checked disabled /><font color='green'>Normalization of expressions has been run?</font> <br>";
								}
								else{
									echo "<input type='checkbox' disabled /><font color='red'>Normalization of expressions has been run?</font> <br>";
								}
							}
						}
						
						//Check if normed Expression data is available
						if ($result =  mysqli_query($connection, "SELECT count(idStudy) as count FROM tFiles WHERE idStudy = $idStudy AND idFileType = 12;")) {
							while ($row = mysqli_fetch_assoc($result)) {
								if($row['count'] != 0){
									echo "<input type='checkbox' checked disabled /><font color='green'>Normalized expression data available?</font> <br>";
								}
								else{
									echo "<input type='checkbox' disabled /><font color='red'>Normalized expression data available?</font> <br>";
								}
							}
						}	
						
						//Check if normed Expression data is merged with Entrez Genes
						if ($result =  mysqli_query($connection, "SELECT count(idStudy) as count FROM tFiles WHERE idStudy = $idStudy AND idFileType = 13;")) {
							while ($row = mysqli_fetch_assoc($result)) {
								if($row['count'] != 0){
									echo "<input type='checkbox' checked disabled /><font color='green'>Normalized expression data available? (Mergen Entrez)</font> <br>";
								}
								else{
									echo "<input type='checkbox' disabled /><font color='red'>Normalized expression data available (Merged Entrez)?</font> <br>";
								}
							}
						}

						/////////////////////////////////////////
						//			Check statistics runs		/
						/////////////////////////////////////////
						
						if ($result =  mysqli_query($connection, "SELECT count(idStudy) as count FROM tFiles WHERE idStudy = $idStudy AND idStatistics != NULL;")) {
							while ($row = mysqli_fetch_assoc($result)) {
								if($row['count'] != 0){
									echo "<input type='checkbox' checked disabled /><font color='green'>QC been run on normalized data?</font> <br>";
								}
								else{
									echo "<input type='checkbox' disabled /><font color='red'>QC been run on normalized data?</font> <br>";
								}
							}
						}
						?>
						
					</div>
					<p class="guidelines" id="guide_1">
						<small>View the status/operations performed on this study.</small>
					</p>
				</li>
				<li id="li_2"><label class="description" for="viewSamples">Click to view samples to this study</label>
					<div>
						<a href='sampleOverview'>View samples of study.</a>
					</div>
					<p class="guidelines" id="guide_2">
						<small>View samples of this study</small>
					</p>
				</li>
				<li id="li_3"><label class="description" for="viewNormed">Click to view normalized expression data.</label>
					<div>
						<a href='normOverview'>View normalized expression data.</a>
					</div>
					<p class="guidelines" id="guide_3">
						<small>View normalized expression data of these study samples</small>
					</p>
				</li>
			</ol>
		</form>
		<!-- End form studyInfo-->
	</div>
	<!--End of form div-->
	<img id="bottom" src="../img/bottom.png" alt="">

	<!--CRUD Tables containing the data of the requested table-->
	<div id="studyContainer"></div>

	<script type="text/javascript">
		//Function to load a CRUD table for tStudyTypes
		$(document).ready(function () {
			//Prepare jTable
			$('#studyContainer').jtable({
				title: 'Information about this study. <em>Use this table to edit information.</em>',
				paging: true,
				pageSize: 10,
				sorting: true,
				defaultSorting: 'idStudy ASC',
				actions: {
					listAction: '../logic/optionsCRUD.php?action=list_tStudy',
					updateAction: '../logic/optionsCRUD.php?action=update_tStudy',
				},
				fields: {
					idStudy: {
						key: true,
						title: 'ID of this study',
						create: false,
						edit: false,
						list: true
					},
					title: {
						title: 'Title'
					},
					curator: {
						title: 'Curator of this study'
					},
					description: {
						title: 'Description of study'
					},
					source: {
						title: 'Source'
					},
					submissionDate: {
						title: 'Date of submission',
						edit: false
					},
					idStudyType: {
						title: 'type of study',
						options:  '../logic/optionsCRUD.php?action=getStudyTypes'
					},
					idMainSpecies: {
						title: 'Species',
						options:  '../logic/optionsCRUD.php?action=getSpecies'
					},
					idAssayType: {
						title: 'Assay',
						options:  '../logic/optionsCRUD.php?action=getAssayTypes'
					},
					idDomain: {
						title: 'Domain of study',
						options:  '../logic/optionsCRUD.php?action=getDomainTypes'
					},
					idArrayPlatform: {
						title: 'Arrayplatform',
						options:  '../logic/optionsCRUD.php?action=getArrays'
					}				
				}
			});

			//Load person list from server
			$('#studyContainer').jtable('load',{ idStudy: <?php echo $idStudy; ?>});
		}); //End function to show CRUDtable for tStudy
	</script>

</body>
</html>