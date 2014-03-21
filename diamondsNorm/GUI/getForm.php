<?php
/*
Author:	Job van Riet
Date of  creation:	11-2-14
Date of modification:	11-2-14
Version:	1.0
Modifications:	Original version
Known bugs:	None known
Function:	This page is the page that gets called each time a form is submitted. It will then check which form it is and apply the subsequent functionality for this specific form e.g. uploading the data from the form into the DB.
*/
?>

<!--Include the scripts that contain the functions -->
<?php

	//Show PHP errors
	error_reporting(E_ALL);
	ini_set('display_errors', '1');
	
	//Functions for handling uploaded files
	require_once('../logic/functions_fileHandling.php');
	//Functions for normalization
	require_once('../logic/functions_normalization.php');
	
	//Get the idStudy from the session.
	session_start();
	
	if (isset($_SESSION['idStudy']))
	{
		$idStudy = $_SESSION['idStudy'];
	}	
?>

<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
<title>Handling forms for study: <?php echo $idStudy; ?></title>
<!--Load CSS for form layout -->
<link rel="stylesheet" type="text/css" href="../css/formLayout.css" media="all" />
</head>
<body>

	<?php
		//Determine which form is being passed and act accordingly
		function getFormType(){
			//Check whether the form supplied a formType to known what to do with the supplied data (GET request)
			if(isset($_GET['formType'])){
				switch($_GET['formType']){
					case "selectStudyForm":
						session_start();
						$_SESSION['idStudy'] = $_GET['studySelect'];
						$_SESSION['studyTitle'] = $_GET['studyTitle'];
						header('Location: studyOverview');
						break;
					case "createStudyForm":
						$id = insertNewStudy($_GET);
						$_SESSION['idStudy'] = $id;
						$_SESSION['studyTitle'] = $_GET['studyTitle'];
						echo "<font color=green><p>Study has been successfully entered!</p>";
						echo "Redirecting to studyOverview";
						//Redirect to chooseStudy
						header('Refresh: 3; URL=chooseStudy');
						break; //End inserting a study
					case "deleteSamples":
						deleteSamplesFromForm($_GET, $_SESSION['idStudy']);
						echo "<font color=green><p>Samples have been successfully removed!</p></font>";
						echo "Redirecting to sampleOverview in 3 seconds.";
						header('Refresh: 2; URL=sampleOverview');
						break; //End adding samples from a file
					case "normalizeStudy":
						normalizeStudy($_GET, $_SESSION['idStudy'], $_SESSION['studyTitle']);
						echo "<font color=green><p>Samples from study are being normalized!</p>";
						echo "<h3>Showing job overview, when it is done it will be displayed in this overview.</h3>";
						//header('Refresh: 5; URL=jobOverview');
						break; //End normalization
					case "statisticsForm":
						doStatistics($_GET, $_SESSION['idStudy'], $_SESSION['studyTitle']);
						echo "<font color=green><p>Statistics are running!</p>";
						echo "<h3>Showing job overview, when it is done it will be displayed in this overview.</h3>";
						//header('Refresh: 5; URL=jobOverview');
						break; //End statistics on pre-existing normalized data
						
				}
			}
			else{
				//See if a POST request was done
				if(isset($_POST['formType'])){
					switch($_POST['formType']){
						//Add samples to a study, samples originate a provided sample file. The user has also specified the headers
						case "uploadSampleFile":
							uploadSampleFileToDB($_FILES,$_POST, $_SESSION['idStudy'], $_SESSION['studyTitle']);
							echo "<p><font color=green>Samples have been added! <br> Redirecting to samples overview. (5 sec)</font></p>";
							header('Refresh: 5; URL=sampleOverview');
							break; //End adding samples from a file
						//Upload expression data to the DB + Added file with SXS-number to sampleName 
						case "expressionDataSXSForm":
							uploadExpressionDataSXSToDB($_FILES,$_POST, $_SESSION['idStudy'], $_SESSION['studyTitle']);
							echo "<p><font color=green>Files have been added! <br> Redirecting to files overview. (5 sec)</font></p>";
							header('Refresh: 5; URL=fileOverview');
							break; //End uploading expression data.
					}
				}
				//If no POST OR GET is given
				else{
					echo "<p><b><font color='red'>No (hidden) formType is provided! This script does not know what kind of form it is processing! :/</font></b></p>";
				}
			}
		}
		
		//Get what kind of form is submitted and do the subsequent functions on this form.
		getFormType();
	?>
</body>
</html>
