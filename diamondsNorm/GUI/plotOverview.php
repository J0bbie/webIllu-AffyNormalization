<?php
/*
Author:					Job van Riet
Date of  creation:		19-2-14
Date of modification:	19-2-14
Version:				1.0
Modifications:			Original version
Known bugs:				None known
Function:				This page will present an overview of files uploaded to a study. It allows the user to select what kind of file each file is.
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
	
	//Make a connection to the DIAMONDSDB (Defined in: functions_dataDB.php)
	require_once('../logic/functions_dataDB.php');
	$connection = makeConnectionToDIAMONDS();

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />

<title>Overview of plots: <?php echo $idStudy; ?></title>
<!--Load CSS for form layout -->
<link rel="stylesheet" type="text/css" href="../css/formLayout.css" media="all" />
<!--Load the CSS for the table layout -->
<link rel="stylesheet" href="../css/tableLayout.css" type="text/css" media="print, projection, screen" />
<!-- Include one of jTable styles. -->
<link href="../css/lightcolor/blue/jtable.css" rel="stylesheet" type="text/css" />
<link href="../css/jQueryUI.css" rel="stylesheet" type="text/css" />

<!--Load main jQuery library-->
<script src="../js/jquery-1.11.0.js" type="text/javascript"></script>
<!--Load jQueryUI-->
<script src="../js/jquery-ui.js" type="text/javascript"></script>

<!-- Include jTable script file. -->
<script src="../js/jquery.jtable.js" type="text/javascript"></script>

<!-- Load Chosen module + CSS -->
<script src="../js/chosen.jquery.js" type="text/javascript"></script>
<link rel="stylesheet" href="../css/chosen.css" />
</head>
	
<div id="navBar"><?php require_once("menu.htm"); ?></div>

<script type="text/javascript">

function getExpressions(){
	var selection = $('#normSelect').val();
};

</script>

<body>
	<!-- Form to show study info -->
	<img id="top" src="../img/top.png" alt="" />
	<div id="form_container">
		<h1>Plot overview</h1>
		<form id="showNormInfo" class="appnitro" method="get" action="">
			<div class="form_description">
				<h2>Overview of plots and statistics</h2>
				<p>Select a plot to see more detail.</p>
			</div>
		</form>
		<!-- End form filesInfo-->
	</div>

	<!--End div form-container-->
	<img id="bottom" src="../img/bottom.png" alt="">
	
	<!--Contains the links where the user can see and download the normalized expressions-->
	<div id="linkContainer"></div>
	
	<!--Give chosen JQuery to selected elements-->
	<script type="text/javascript">
		var config = {
		  '.chosen-select'           : {search_contains:true},
		  '.chosen-select-deselect'  : {allow_single_deselect:true},
		  '.chosen-select-no-single' : {disable_search_threshold:10},
		  '.chosen-select-no-results': {no_results_text:'Oops, nothing found!'},
		  '.chosen-select-width'     : {width:"95%"}
		  
		}
		for (var selector in config) {
		  $(selector).chosen(config[selector]);
		}
  </script>
</body>
</html>

