<?php
/*
Author:					Job van Riet + ArrayAnalysis.org
Date of  creation:		4-3-14
Date of modification:	4-3-14
Version:				1.0
Modifications:			Original version
Known bugs:				None known
Function:				This page will present the user with options to perform statistics on a already normalized LumiBatch Object
						The normalization is done by a slightly altered ArrayAnalysis.org R script on a CPU-limited thread (to prevent 100% CPU uptake). (Unknown author)
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

//Include the script to make a connection to the DIAMONDS DB
require_once('../logic/functions_dataDB.php');
//Initialize DIAMONDSDBClass
$connection = makeConnectionToDIAMONDS();

error_reporting ( E_ALL );
ini_set ( 'display_errors', '1' );
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>Perform normalization: <?php echo $idStudy; ?></title>
<!--Load CSS for form layout -->
<link rel="stylesheet" type="text/css" href="../css/formLayout.css" media="all" />
<link rel="stylesheet" type="text/css" href="../css/arrayAnalysisOptions" media="all" />

<!--Load main jQuery library-->
<script src="../js/jquery-1.11.0.js" type="text/javascript"></script>
<!-- Load Chosen + CSS -->
<link rel="stylesheet" type="text/css" href="../css/chosen.css" media="all" />
<script src="../js/chosen.jquery.js" type="text/javascript"></script>
<script src="../js/chosen.order.js" type="text/javascript"></script>

</head>

<!-- Include the menu -->
<div id="navBar">
	<?php require_once("menu.htm"); ?>
</div>

<script type="text/javascript">
//Function to get the selected attributes on which the samples should be clustered
function getAttributes(){
 var selection = ChosenOrder.getSelectionOrder(document.getElementById('groupOnSelector'));
	$('#selectedAttributes').val(selection);
};

//Ask if the user if sure if they do not want to cluster the samples and treat each sample independently when QCing
function checkAttributes(){
	var selection = $('#selectedAttributes').val();
	if($('#normDataAvailable').val() == 1){
		if(selection == ""){
			if (confirm('Are you sure you do NOT want to cluster the samples when QCing the normalized/raw data?\nPress cancel to stop.')) {
				document.getElementById("normalizeStudyForm").submit();
			} else {
			    return false;
			}
		}
		else{
			document.getElementById("normalizeStudyForm").submit();
		}
	}
	else{
		alert("Cannot do a rerun of statistics if the normalized lumiBatch Object is not available on the fileserver (and linked in DB)!");
		return false;
	}
};

//If a user has selected a normalization, show the statistics etc, else hide.
function showStatistics(){
	if(<?php echo (isset($_GET['normSelect']) ? '1' : '0') ?> == '1'){
		$('#statisticsOptions').show();
		$('#normChoser').hide();
	}else{
		$('#statisticsOptions').hide();
		$('#normChoser').show();
	}
};

</script>


<body onload ="showStatistics()">
	<div id="normChoser">
		<!-- Form to show study info -->
		<img id="top" src="../img/top.png" alt="" />
		<div id="form_container">
			<h1>Normalization overview</h1>
			<form id="showNormInfo" class="appnitro" method="get" action="doQC">
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
										echo "<option value=".$row['idNormAnalysis'].">".$row['idNormAnalysis']." - ".$row['description']." Grouped on:(".($row['groupedOn'] != "" ? $row['groupedOn'] : 'Nothing').")</option>";										
									}
								}
								?>
							</select>
							</div><p class="guidelines" id="guide_1"><small>Choose which normalization to work with.</small></p>
							<input type="submit" value="Select normalization data.">
						</li>
					</ol>
			</form>
			<!-- End form filesInfo-->
		</div>
	<!--End div form-container-->
	<img id="bottom" src="../img/bottom.png" alt="">
	</div>

	
	<!-- Div containing the statistics options -->
	<div id="statisticsOptions">
		<!-- Form to show normalization options -->
		<img id="top" src="../img/top.png" alt="" />
		<div id="form_container">
			<h1>Perform Normalization</h1>
			<!--Form to normalize samples-->
			<form action="getForm.php" method="GET" name="statisticsForm" id="statisticsForm">
				<!--Add hidden value to keep track of which form this is-->
				<input id="formType" name="formType" class="element text large" type="hidden" value="statisticsForm" /> 
				<input id="selectedAttributes" name="selectedAttributes" class="element text large"	type="hidden" />
				<div class="form_description">
					<h2>Perform statistics on available normalized data.</h2>
					<p>This form can be used perform statistics on already available normalized data. (lumiBatch R Object) <br>This page can not be used to normalize raw data.</p>
				</div>
				<ol>
					<li id="li_1"><label class="description" for="groupOnSelector">Select the attributes on which to cluster. (PCA/QC)</label>
						<div>
							<select data-placeholder="Choose the attribute on which to group on." onchange="getAttributes()" id="groupOnSelector" multiple class="chosen-select" style="width: 360px;" tabindex="2">
								<?php
								//Allow for grouping on attributes
								if ($result =  mysqli_query($connection, "SELECT DISTINCT idDataType, dataTypeName FROM vSamplesWithAttributes WHERE idStudy = ".$idStudy)) {
									while ($row = mysqli_fetch_assoc($result)) {
										echo "<option value=".$row['idDataType'].">".$row['dataTypeName']."</option>";
									}
								}
								//Allow for grouping on compound
								echo "<option value='compound'>On identical compound</option>";
								echo "<option value='sampleType'>On identical sampleType</option>";
							?>
							</select>
							<p class="guidelines" id="guide_1">
								<small>Select the criteria on which the samples will be grouped to check for clusters.<br> <em>Groups can be dynamically made with more than one attribute.</em></small>
							</p>
						</div></li>
					<li>
						<!-- Checkboxes for status of samples and background correction and statistics options -->
						<div>
							<input type="checkbox" id="reorderSamples" name="reorderSamples" checked/>Reorder samples by experimental group? <small>(Used for the order in plots)</small><br>
																
							<?php
							//Check if the raw expression data already has been uploaded for this study. (Sample Gene Profile / Control Probe Profile)
							$checkFiles = 0;
							if ($result =  mysqli_query($connection, "SELECT DISTINCT idStudy FROM tFiles WHERE idNorm = ".(isset($_GET['normSelect']) ? $_GET['normSelect'] : '0')." AND idStudy = $idStudy AND idFileType= 14")) {
								while ($row = mysqli_fetch_assoc($result)) {
									$checkFiles = 1;
								}
							}
							if($checkFiles == 1){
								echo "<input type='checkbox' id='normDataAvailable' name='normDataAvailable' checked value='1' disabled/><font color=green>This study has lumiBatch R Object of normalized data?</font><br>";
							}else{
								echo "<input type='checkbox' id='normDataAvailable' name='normDataAvailable' value='0' disabled/><font color=red>This study has lumiBatch R Object of normalized data</font><br>";
							}
							
							?>
							<p class="guidelines" id="guide_2">
								<small>Is the data background-substracted? Also, is there even expression data added to this study and has there already been a normalization done?</small>
							</p>
						</div>
						<!-- End checkboxes -->
					</li>
					<!-- End basic info -->
					<!-- Options Raw Plots -->
					<li id="rawPlots">
						<div>
							<table class="form" style="border: solid 1px #80D9FF; background: #BFECFF">
								<tbody>
									<tr>
										<td colspan="3" class="sectionTitle">Raw data plots</td>
									</tr>
									<tr>
										<td>Create density plot <input name="raw_density" checked style="float:right" type="checkbox"></td>
										<td>Create CV plot <input name="raw_cv" checked style="float:right" type="checkbox"></td>
										<td>Create sample relation plot <input name="raw_sampleRelation" checked style="float:right" type="checkbox"></td>
									</tr>
									<tr>
										<td>Create PCA plot <input name="raw_pca" checked style="float:right" type="checkbox"></td>
										<td>Create boxplot <input name="raw_boxplot" checked style="float:right" type="checkbox"></td>
										<td>Create correlation plot <input name="raw_correl" checked style="float:right" type="checkbox"></td>
									</tr>
								</tbody>
							</table>
							<p class="guidelines" id="guide_6">
								<small>Choose which plots will be made from the raw data.</small>
							</p>
						</div>
					</li>
					<!-- End Options Raw Plots -->
					<!-- Options Normed Plots -->
					<li id="normPlots">
						<div>
							<table class="form" style="border: solid 1px #9F8FBF; background: #cccde0">
								<tbody>
									<tr>
										<td colspan="3" class="sectionTitle">Normalized data plots</td>
									</tr>
									<tr>
										<td>Create density plot <input name="norm_density" checked style="float:right" type="checkbox"></td>
										<td>Create CV plot <input name="norm_cv" checked style="float:right" type="checkbox"></td>
										<td>Create sample relation plot <input name="norm_sampleRelation" checked style="float:right" type="checkbox"></td>
									</tr>
									<tr>
										<td>Create PCA plot <input name="norm_pca" checked style="float:right" type="checkbox"></td>
										<td>Create boxplot <input name="norm_boxplot" checked style="float:right" type="checkbox"></td>
										<td>Create correlation plot <input name="norm_correl" checked style="float:right" type="checkbox"></td>
									</tr>
								</tbody>
							</table>
							<p class="guidelines" id="guide_7">
								<small>Choose which plots will be made from the normalized data.</small>
							</p>
						</div>
					</li>
					<!-- End Options Normed Plots -->
					<!-- Options Clustering -->
					<li>
						<div>
							<table class="form" style="border: solid 1px #FFE152; background: #FFF3BC">
								<tbody>
									<tr>
										<td colspan="2" class="sectionTitle">Clustering options</td>
									</tr>
									<tr>
										<td>
											<i>Distance calculation method</i><br>
											<select name="clustoption1" id="clustoption1" onchange="" size="1">
													<option value="Pearson">Pearson</option>
													<option value="Spearman">Spearman</option>
													<option value="Euclidean">Euclidean</option>
											</select>
										</td>
										<td>
										<i>Clustering method</i><br>
											<select name="clustoption2" id="clustoption2" onchange="" size="1">
													<option value="ward">Ward</option>
													<option value="mcquitty">Mcquitty</option>
													<option value="average">average</option>
													<option value="median">median</option>
													<option value="single">single</option>
													<option value="complete">complete</option>
													<option value="centroid">centroid</option>
											</select>
										</td>
									</tr>
								</tbody>
							</table>
							<p class="guidelines" id="guide_8">
								<small>Parameters for the hierarchical clustering.</small>
							</p>
							<button type="button" onclick="checkAttributes()">Normalize study-samples. </button>
						</div>
					</li>
					<!-- End Clustering options -->
				</ol>
			</form>
			<!--End form normalization-->
		</div>
		<!--End div form-container-->
		<img id="bottom" src="../img/bottom.png" alt="">
	</div>
	
	
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