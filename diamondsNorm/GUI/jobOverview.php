<?php
/*
Author:					Job van Riet
Date of  creation:		19-2-14
Date of modification:	19-2-14
Version:				1.0
Modifications:			Original version
Known bugs:				None known
Function:				This page will present an overview of the jobs run by this study.
*/
?>

<?php
// Get the idStudy from the session, if no session is made, let the user select a study.
session_start ();

if (isset ( $_SESSION ['idStudy'] )) {
	$idStudy = $_SESSION ['idStudy'];
} else {
	// Redirect to studyOverview of this study
	header('Location: chooseStudy');
}
?>

<?php

error_reporting ( E_ALL );
ini_set ( 'display_errors', '1' );
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />

<title>Jobs of study: <?php echo $idStudy; ?></title>
<!--Load CSS for form layout -->
<link rel="stylesheet" type="text/css" href="../css/formLayout.css" media="all" />
<!--Load the CSS for the table layout -->
<link rel="stylesheet" href="../css/tableLayout.css" type="text/css" media="print, projection, screen" />
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
	

<div id="navBar"><?php require_once("menu.htm"); ?></div>

<body>
	<!-- Form to show study info -->
	<img id="top" src="../img/top.png" alt="" />
	<div id="form_container">
		<h1>These are the files that are listed for this study.</h1>
		<form id="showJobInfo" class="appnitro" action="">
			<div class="form_description">
				<h2>Job overview</h2>
				<p>This page shows all the jobs performed and currently performing on this study.</p>
			</div>
		</form>
		<!-- End form filesInfo-->
	</div>
	<!--End div form-container-->
	<img id="bottom" src="../img/bottom.png" alt="">

	<!--CRUD Table containing the files-->
	<div id="jobTableContainer"></div>

	<script type="text/javascript">
			$(document).ready(function () {
				//Prepare jTable
				$('#jobTableContainer').jtable({
					title: 'Jobs of study',
					paging: true,
					pageSize: 10,
					sorting: true,
					defaultSorting: 'idJob ASC',
					actions: {
						listAction: '../logic/optionsCRUD.php?action=list_tJobStatus',
						createAction: '../logic/optionsCRUD.php?action=create_tJobStatus',
						updateAction: '../logic/optionsCRUD.php?action=update_tJobStatus',
						deleteAction: '../logic/optionsCRUD.php?action=delete_tJobStatus'
					},
					fields: {
						idJob: {
							key: true,
							title: 'idJob',
							create: false,
							edit: false
						},
						idStudy: {
							title: 'idStudy',
							edit: false,
							type: 'hidden',
							defaultValue: <?php echo $idStudy ?>,
							list: false
						},
						submissionDate:{
							  title: 'Date of Job',
							  edit: false
							},
						name: {
							title: 'Name of Job'
						},
						description: {
							title: 'Description of Job'
						},
						status:{
						  title: 'Status of Job'
						},
						statusMessage:{
						  title: 'Message of Job',
						  display: function (data) {
							  if(data.record.status == 0){
							        return '<b><font color= "orange">'+data.record.statusMessage +'</font></b>';
							  }
							  else if(data.record.status == 1){
							        return '<b><font color = "green">'+data.record.statusMessage +'</font></b>';
							  }
							  else if(data.record.status == 2){
							        return '<b><font color = "red">'+data.record.statusMessage +'</font></b>';
							  }
						  }
						}
					}
				});

			//Load person list from server
			$('#jobTableContainer').jtable('load',{ idStudy: <?php echo $idStudy; ?>});
		}); //End function
		</script>

</body>
</html>