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
		$studyTitle = $_SESSION ['studyTitle'];
	} else {
		// Redirect to studyOverview of this study
		header('Location: chooseStudy');
	}

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />

<title>Files of study: <?php echo $idStudy; ?></title>
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
		<h1>Show Files</h1>
		<form id="showFilesInfo" class="appnitro" method="get" action="">
			<div class="form_description">
				<h2>File overview</h2>
				<p>Use the table to change the names of files and/or their associated fileType. <br> If a file is deleted using the table, it wil also be deleted from the file-server.</p>
			</div>
			<ol>
				<li id="li_1"><label class="description" for="sampleName">Search on file name:</label>
				<div>
					<input id="fileName" name="fileName" class="element text large" type="text" maxlength="255" value="" />
				</div>
				<button id="SearchFileNames" type=button>Search files.</button>
				</li>
			</ol>
		</form>
		<!-- End form filesInfo-->
	</div>
	<!--End div form-container-->
	<img id="bottom" src="../img/bottom.png" alt="">

	<!--CRUD Table containing the files-->
	<div id="filesTableContainer"></div>

	<script type="text/javascript">

		$('#SearchFileNames').click(function () {
	        $('#filesTableContainer').jtable('load', {
	            fileName: $('#fileName').val(),
	            idStudy: <?php echo $idStudy; ?>
	        });
	    });
		$(document).ready(function () {
			//Prepare jTable
			$('#filesTableContainer').jtable({
				title: 'Files of study',
				paging: true,
				pageSize: 10,
				sorting: true,
				defaultSorting: 'idFile ASC',
				actions: {
					listAction: '../logic/optionsCRUD.php?action=list_tFiles',
					createAction: '../logic/optionsCRUD.php?action=create_tFiles',
					updateAction: '../logic/optionsCRUD.php?action=update_tFiles',
					deleteAction: '../logic/optionsCRUD.php?action=delete_tFiles'
				},
				fields: {
					idFile: {
						key: true,
						title: 'idFile',
						create: false,
						edit: false,
						list: false
					},
					idStudy: {
						title: 'idStudy',
						edit: false,
						type: 'hidden',
						defaultValue: <?php echo $idStudy; ?>,
						list: false
					},
					folderName: {
						title: 'folderName',
						create:false,
						edit: false
					},
					fileName: {
						title: 'fileName',
						edit: true,
						display: function (data) {
					    	link = '<a href="../data/<?php echo $idStudy."_".$studyTitle."/"; ?>'+data.record.folderName+'/';
							if(data.record.idNorm){
								link = link+data.record.idNorm+"/";
							}
							if(data.record.idStatistics){
								link = link+data.record.idStatistics+"/";
							}
							link = link+data.record.fileName+'">'+data.record.fileName+'</a>';
							return link;
						}
					},
					idFileType:{
					  title: 'FileTypes',
					  options:  '../logic/optionsCRUD.php?action=getFileTypes',
					  list: false
					},
					idDirectory:{
					  title: 'idDirectory',
					  options:  '../logic/optionsCRUD.php?action=getDirectories',
					  create:false,
					  list: false,
					  edit:false
					},
					name: {
						title: 'Short description',
						create: false,
						edit: false
					},
					description: {
						title: 'Description',
						create: false,
						edit: false
					}
				}
			});

			//Load person list from server
			$('#filesTableContainer').jtable('load',{ idStudy: <?php echo $idStudy; ?>});
		}); //End function
	</script>

</body>
</html>