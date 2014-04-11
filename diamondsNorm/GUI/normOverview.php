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
		$idNorm = 1;
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

<title>Overview of normalization from: <?php echo $idStudy; ?></title>
<!--Load CSS for form layout -->
<link rel="stylesheet" type="text/css" href="../css/formLayout.css" media="all" />
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
function getNormOverview(){
	if(<?php echo (isset($_GET['normSelect']) ? '1' : '0') ?> == '1'){
		var selection = <?php echo (isset($_GET['normSelect']) ? $_GET['normSelect'] : '0') ?>;
		$('#expressionTableContainer').show();
		$('#normChoser').hide();
		showNormOverviewTable(selection);
		showExpressionTable(selection);
	}else{
		$('#normChoser').show();
		$('#expressionTableContainer').hide();
	}
};
</script>

<body onload ="getNormOverview()">
	<div id="normChoser">
		<!-- Form to show study info -->
		<img id="top" src="../img/top.png" alt="" />
		<div id="form_container">
			<h1>Normalization overview</h1>
			<form id="showNormInfo" class="appnitro" method="get" action="normOverview">
				<div class="form_description">
					<h2>Overview of run normalizations</h2>
					<p>Select a normalization to see the files containing the normalized expression data of that normalization</p>
				</div>
					<ol>
						<li id="li_1" >
							<div>
							<label class="description" for="normSelect">Choose a normalization run:</label>
							<select data-placeholder="Choose a normalization run." style="width:100%" class="chosen-select" name="normSelect" id="normSelect">
								<?php
								if ($result =  mysqli_query($connection, "SELECT * FROM tNormAnalysis WHERE idStudy =". $idStudy )) {
									while ($row = mysqli_fetch_assoc($result)) {
										echo "<option value=".$row['idNormAnalysis'].">".$row['idNormAnalysis']." - ".$row['description']."</option>";										
									}
								}
								?>
							</select>
							</div><p class="guidelines" id="guide_1"><small>Choose which normalization to work with.</small></p>
							<input type="submit" value="Retrieve information">
						</li>
					</ol>
			</form>
			<!-- End form filesInfo-->
		</div>
	</div>

	<!--End div form-container-->
	<img id="bottom" src="../img/bottom.png" alt="">
	
	<!--CRUD Table containing the overview of the normalisation-->
	<div id="normOverviewContainer"></div>
	
	<!--CRUD Table containing the expression values-->
	<div id="expressionTableContainer">
		<div id="form_container">
			<form id="searchOptions" class="appnitro">
				<div class="form_description">
					<h3>Search options</h3>
				</div>
					<ol>
						<li id="li_1"><label class="description" for="geneName">Search on gene name:</label>
							<div>
								<input id="geneName" name="geneName" class="element text large" type="text" maxlength="255" value="" />
							</div>
						</li>
						<li id="li_2"><label class="description" for="sampleName">Search on sample name:</label>
							<div>
								<input id="sampleName" name="sampleName" class="element text large" type="text" maxlength="255" value="" />
							</div>
							<button id="LoadRecordsButton" type=button>Search through records</button>
						</li>
						<li>
							<a href="statOverview?normSelect=<?php echo (isset($_GET['normSelect']) ? $_GET['normSelect'] : '0') ?>">Show plots of normalization.</a> <br>
							<a href="doStatistics?normSelect=<?php echo (isset($_GET['normSelect']) ? $_GET['normSelect'] : '0') ?>">Perform new statistics on this normalization.</a>
						</li>
					</ol>
			</form>
		</div>
	</div>

	<script type="text/javascript">
    //Re-load records when user click 'load records' button.
    $('#LoadRecordsButton').click(function () {
        $('#expressionTableContainer').jtable('load', {
            geneName: $('#geneName').val(),
            sampleName: $('#sampleName').val(),
            idNormAnalysis: <?php echo (isset($_GET['normSelect']) ? $_GET['normSelect'] : '0') ?>
        });
    });

	function showNormOverviewTable(idNormSelect) {
		//Prepare jTable
		$('#normOverviewContainer').jtable({
			title: 'Overview of the normalisation',
			paging: true,
			pageSize: 10,
			sorting: true,
			defaultSorting: 'idNormAnalysis ASC',
			actions: {
				listAction: '../logic/optionsCRUD.php?action=list_tNormAnalysis',
				updateAction: '../logic/optionsCRUD.php?action=update_tNormAnalysis'
			},
			fields: {
				idNormAnalysis: {
					key: true,
					title: 'idNormAnalysis',
					create: false,
					edit: false,
					list: false
				},
				description: {
					title: 'Description',
					edit: true
				},
				groupedOn: {
					title: 'Grouped on:',
					edit: true
				},
				normType: {
					title: 'Type of normalization run',
					edit: false
				},
				bgCorrectionMethod: {
					title: 'Method of background correcting',
					edit: false
				},
				varStabMethod: {
					title: 'Method of variance stabilization',
					edit: false
				},
				normMethod: {
					title: 'Method of normalizing',
					edit: false
				},
				filterThreshold: {
					title: 'Threshold of low expression filtering',
					edit: false
				}
			}
		});

		//Load person list from server
		$('#normOverviewContainer').jtable('load',{ idNormAnalysis: idNormSelect});
	}; //End showNormOverview function
	
	function showExpressionTable(idNormSelect) {
		//Prepare jTable
		$('#expressionTableContainer').jtable({
			title: 'Expressions of the normalization',
			paging: true,
			pageSize: 20,
			sorting: true,
			defaultSorting: 'idNormExpression ASC',
			actions: {
				listAction: '../logic/optionsCRUD.php?action=list_tNormedExpression'
			},
			fields: {
				idNormExpression: {
					key: true,
					title: 'idNormExpression',
					create: false,
					edit: false,
					list: false
				},
				sampleName: {
					title: 'Sample Name',
					edit: false
				},
				expression: {
					title: 'Value of expression'
				},
				geneName: {
					title: 'Name of gene'
				},
				entrezGeneID: {
					title: 'entrezGeneID',
					display: function (data) {
				    	return'<a href="http://www.ncbi.nlm.nih.gov/gene/'+data.record.entrezGeneID+'">'+data.record.entrezGeneID+'</a>';
					}
				},
				nuID: {
					title: 'nuID',
				}
			}
		});

		//Load person list from server
		$('#expressionTableContainer').jtable('load',{ idNormAnalysis: idNormSelect});
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

