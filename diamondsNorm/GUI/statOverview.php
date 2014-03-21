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

<?php

error_reporting ( E_ALL );
ini_set ( 'display_errors', '1' );

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
<link href="../css/lightcolor/orange/jtable.css" rel="stylesheet" type="text/css" />
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

<!-- If a user has selected a normalization, show the overview etc, else hide. -->
function getPlots(){
	if(<?php echo (isset($_GET['statSelect']) ? '1' : '0') ?> == '1'){
		var selection = <?php echo (isset($_GET['statSelect']) ? $_GET['statSelect'] : '0') ?>;
		$('#filesTableContainer').show();
		$('#statChoser').hide();
		getFiles(selection);
	}else{
		$('#statChoser').show();
		$('#filesTableContainer').hide();
	}
};
</script>

<body onload ="getPlots()">
	<div id="statChoser">
		<!-- Form to show study info -->
		<img id="top" src="../img/top.png" alt="" />
		<div id="form_container">
			<h1>Normalization overview</h1>
			<form id="showNormInfo" class="appnitro" method="get" action="statOverview">
				<div class="form_description">
					<h2>Overview of run normalizations</h2>
					<p>Select a normalization to see the files containing the normalized expression data of that normalization</p>
				</div>
					<ol>
						<li id="li_1" >
							<div>
							<label class="description" for="statSelect">Choose a statistics run:</label>
							<select data-placeholder="Choose a statistics run." style="width:100%" class="chosen-select" name="statSelect" id="statSelect">
								<?php
									if ($result =  mysqli_query($connection, "SELECT idStatistics, tNormAnalysis.idNormAnalysis as idNorm, groupedOn, tStatistics.description as descStat FROM tStatistics JOIN tNormAnalysis ON tStatistics.idNormAnalysis =  tNormAnalysis.idNormAnalysis JOIN tStudy ON tNormAnalysis.idStudy = tStudy.idStudy WHERE tStudy.idStudy =". $idStudy . (isset($_GET['normSelect']) ? " AND tNormAnalysis.idNormAnalysis = ".$_GET['normSelect']  : '') )) {
										while ($row = mysqli_fetch_assoc($result)) {
											echo "<option value=".$row['idStatistics'].">Performed on normalization: ".$row['idNorm'].". Grouped on: ".$row['groupedOn']." Desc: ".$row['descStat']."</option>";										
										}
									}
								?>
							</select>
							</div><p class="guidelines" id="guide_1"><small>Choose which statistics to show.</small></p>
							<input type="submit" value="Retrieve information">
						</li>
					</ol>
			</form>
			<!-- End form filesInfo-->
		</div>
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
		function getFiles(idStat) {
			//Prepare jTable
			$('#filesTableContainer').jtable({
				title: 'Files of study',
				paging: true,
				pageSize: 10,
				sorting: true,
				defaultSorting: 'idFile ASC',
				actions: {
					listAction: '../logic/optionsCRUD.php?action=list_tFilesStat',
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
			$('#filesTableContainer').jtable('load',{ idStat: idStat});
		}; //End function
	</script>
	
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

