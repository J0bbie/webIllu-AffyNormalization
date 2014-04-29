<?php
/*
Author:					Job van Riet + ArrayAnalysis.org
Date of  creation:		25-2-14
Date of modification:	25-2-14
Version:				1.0
Modifications:			Original version
Known bugs:				None known
Function:				This page will present the user with options to perform a normilazation of the uploaded Illumina Beadchip expression data using Control_Probe_Profile and Sample_Probe_Profile.
						The normalization is done by a slightly altered ArrayAnalysis.org R script on a CPU-limited thread (to prevent 100% CPU uptake). (Unknown author)
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
	
	//Include the script to make a connection to the DIAMONDS DB
	require_once('../logic/functions_dataDB.php');
	//Initialize DIAMONDSDBClass
	$connection = makeConnectionToDIAMONDS();

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>Illumina normalization on study: <?php echo $idStudy; ?></title>
<!--Load CSS for form layout -->
<link rel="stylesheet" type="text/css" href="../css/formLayout.css" media="all" />
<link rel="stylesheet" type="text/css" href="../css/arrayAnalysisOptions" media="all" />

<!-- Include one of jTable styles. -->
<link href="../css/lightcolor/orange/jtable.css" rel="stylesheet" type="text/css" />
<link href="../css/jQueryUI.css" rel="stylesheet" type="text/css" />

<!--Load main jQuery library-->
<script src="../js/jquery-1.11.0.js" type="text/javascript"></script>
<!--Load jQueryUI-->
<script src="../js/jquery-ui.js" type="text/javascript"></script>

<!-- Load Chosen + CSS -->
<link rel="stylesheet" type="text/css" href="../css/chosen.css" media="all" />
<script src="../js/chosen.jquery.js" type="text/javascript"></script>
<script src="../js/chosen.order.js" type="text/javascript"></script>

<!-- Include jTable script file. -->
<script src="../js/jquery.jtable.js" type="text/javascript"></script>

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
		if($('#expressionDataUploaded').val() == 1){
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
			alert("Cannot do a normalization if the expression data is not uploaded!");
			return false;
		}
	};
	
	//Function to display filtering
	function showStatisticsOptions() {
		if($('#performStatistics').is(':checked')) {
			$("#rawPlots").show();
			$("#normPlots").show();
			$("#clusterTable").show();
			$("#reorderSamples").show();
		} else {
			$("#rawPlots").hide();
			$("#normPlots").hide();
			$("#clusterTable").hide();
			$("#reorderSamples").show();
			
		}
	};
	
	//Function to display background correction options
	function showBackgroundCorrectionOptions() {
		if($('#performBackgroundCorrection').is(':checked')) {
			$("#backgroundCorrectionOptions").show();
		} else {
			$("#backgroundCorrectionOptions").hide();
		}
	};
	
	// Hides/shows the variance stabilization options
	function showVarianceStabilizationOptions() {
		if($('#performVarianceStabilization').is(':checked')) {
			$("#varianceStabilizationOptions").show();
		} else {
			$("#varianceStabilizationOptions").hide();
			
		}
	};
	
// Shows the filtering options for the statistics and normalization
function showSampleSelection() {
	// Show/hide statistics subsetting
	if($('#sampleStatisticsFiltering').is(':checked')) {
		$("#sampleStatisticsSelection").show();
		$("#searchStatisticsSamples").show();
		showSampleStatisticsSelectTable();
	} else {
		$("#sampleStatisticsSelection").hide();
		$("#searchStatisticsSamples").hide();
	}

	// Show/hide normalization subsetting
	if($('#sampleNormalizationFiltering').is(':checked')) {
		$("#sampleNormalizationSelection").show();
		$("#searchNormalizationSamples").show();
		showSampleNormalizationSelectTable();
	} else {
		$("#sampleNormalizationSelection").hide();
		$("#searchNormalizationSamples").hide();
	}
};

//Filters the samples for subsetting the statistics on user input
function filterNormalizationSamples(){
	 $('#sampleNormalizationSelection').jtable('load', {
		sampleName : $('#searchSampleNameN').val(),
		compoundName : $('#searchCompoundNameN').val(),
		sampleType : $('#searchSampleTypeN').val(),
		attrValue : $('#searchAttributesN').val(),
		attrFilter : $('#attrFilterN').val(),
		dataTypeFilter : $('#dataTypeFilterN').val(),
		idStudy : <?php echo $idStudy; ?>
     });
}

// Filters the samples for subsetting the statistics on user input
function filterStatisticsSamples(){
	 $('#sampleStatisticsSelection').jtable('load', {
		sampleName : $('#searchSampleNameS').val(),
		compoundName : $('#searchCompoundNameS').val(),
		sampleType : $('#searchSampleTypeS').val(),
		attrValue : $('#searchAttributesS').val(),
		attrFilter : $('#attrFilterS').val(),
		dataTypeFilter : $('#dataTypeFilterS').val(),
		idStudy : <?php echo $idStudy; ?>
     });
}

//Get the selected sample IDs used in the normalization and put them in a , separated string
function getSampleNormalizationSelection(){
	var $selectedRows = $('#sampleNormalizationSelection').jtable('selectedRows');
	var $line = "";
    $selectedRows.each(function () {
        var record = $(this).data('record').idSample;
       	if($line != ""){
       		$line += ","+record;
       	}else{
       		$line += record;
       	}
    });
    $('#selectedNormalizationSamples').val($line);
    alert("Samples subset for normalization has been selected.");
}

//Get the selected sample IDs used in the statistics and put them in a , separated string
function getSampleStatisticsSelection(){
	var $selectedRows = $('#sampleStatisticsSelection').jtable('selectedRows');
	var $line = "";
    $selectedRows.each(function () {
        var record = $(this).data('record').idSample;
       	if($line != ""){
       		$line += ","+record;
       	}else{
       		$line += record;
       	}
    });
    $('#selectedStatisticsSamples').val($line);
    alert("Samples subset for statistics has been selected.");
}
</script>

<body onload="showSampleSelection()">
	<!-- Form to show normalization options -->
	<img id="top" src="../img/top.png" alt="" />
	<div id="form_container">
		<h1>Perform Illumina Beadchip Normalization</h1>
		<!--Form to normalize samples-->
		<form action="getForm.php" method="GET" name="normalizeStudyForm" id="normalizeStudyForm">
			<!--Add hidden value to keep track of which form this is-->
			<input id="formType" name="formType" type="hidden" value="normalizeIlluStudy" /> 
			<input id="selectedAttributes" name="selectedAttributes" type="hidden" />
			<input id="selectedStatisticsSamples" name="selectedStatisticsSamples" value=0 type="hidden" />
			<input id="selectedNormalizationSamples" name="selectedNormalizationSamples" value=0 type="hidden" />
			
			<div class="form_description">
				<h2>Normalize the Illumina Beadchip samples from this study.</h2>
				<p>This form can be used to normalize the Illumina Beadchip omics data from the samples originating from this study.</p>
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
					</div>
				</li>
				
				<!-- 
				##########################################################################
				# Checkbox/filtering selection for sample filtering during normalization #
				##########################################################################
				-->
				<li>
					<input type="checkbox" id="sampleNormalizationFiltering" name="sampleNormalizationFiltering" onchange="showSampleSelection()"/>Want to perform <strong>normalization</strong> on a subset only?
					<div id="searchNormalizationSamples">
						<label class="description" for="searchSampleNameN">Filter on sample name:</label>
						<input id="searchSampleNameN" name="searchSampleNameN" class="element text large" type="text" maxlength="255" value="" />
						<label class="description" for="searchCompoundNameN">Filter on compound name:</label>
						<input id="searchCompoundNameN" name="searchCompoundNameN" class="element text large" type="text" maxlength="255" value="" />
						<label class="description" for="searchSampleTypeN">Filter on sampleType:</label>
						<input id="searchSampleTypeN" name="searchSampleTypeN" class="element text large" type="text" maxlength="255" value="" />
						<label class="description" for="searchAttributesN">Filter on attributes (Organ/Noel etc.):</label>
						<input id="searchAttributesN" name="searchAttributesN" class="element text large" type="text" maxlength="255" value="" />
						<select name="attrFilterN" id="attrFilterN">
							<option value="L">LIKE</option>
							<option value="NL">NOT LIKE</option>
							<option value="GT">&gt;=</option>
							<option value="LT">&lt;=</option>
						</select>
						<select name="dataTypeFilterN" id="dataTypeFilterN">
							<?php 
							if ($result =  mysqli_query($connection, "SELECT DISTINCT idDataType, dataTypeName FROM vSamplesWithAttributes WHERE idStudy = ".$idStudy)) {
								while ($row = mysqli_fetch_assoc($result)) {
									echo "<option value=".$row['idDataType'].">".$row['dataTypeName']."</option>";
								}
							}
							?>
						</select><br>
						<button type=button onclick="filterNormalizationSamples()">Search through records.</button>
						<button type=button onclick="getSampleNormalizationSelection()">Select these samples.</button>
					</div>
					<div id="sampleNormalizationSelection"></div>
					<p class="guidelines" id="guide_filtering">
						<small>Select the samples you want to use when performing the statistics. When selecting the filter of attributes, also select how and on what attribute it should be filtered. 
						<br> When the samples are selected that should be used in the subsetting of the statistics, click the "Select these samples" button.</small>
					</p>
				</li>				
				
				<!-- 
				##########################################################################
				# Checkbox/filtering selection for sample filtering during statistics	 #
				##########################################################################
				-->
				
				<li>
					<input type="checkbox" id="sampleStatisticsFiltering" name="sampleStatisticsFiltering" onchange="showSampleSelection()"/>Want to perform <strong>statistics</strong> on a subset only?
					<div id="searchStatisticsSamples">
						<label class="description" for="searchSampleNameS">Filter on sample name:</label>
						<input id="searchSampleNameS" name="searchSampleNamSe" class="element text large" type="text" maxlength="255" value="" />
						<label class="description" for="searchCompoundNameS">Filter on compound name:</label>
						<input id="searchCompoundNameS" name="searchCompoundNameS" class="element text large" type="text" maxlength="255" value="" />
						<label class="description" for="searchSampleTypeS">Filter on sampleType:</label>
						<input id="searchSampleTypeS" name="searchSampleTypeS" class="element text large" type="text" maxlength="255" value="" />
						<label class="description" for="searchAttributesS">Filter on attributes (Organ/Noel etc.):</label>
						<input id="searchAttributesS" name="searchAttributesS" class="element text large" type="text" maxlength="255" value="" />
						<select name="attrFilterS" id="attrFilterS">
							<option value="L">LIKE</option>
							<option value="NL">NOT LIKE</option>
							<option value="GT">&gt;=</option>
							<option value="LT">&lt;=</option>
						</select>
						<select name="dataTypeFilterS" id="dataTypeFilterS">
							<?php 
							if ($result =  mysqli_query($connection, "SELECT DISTINCT idDataType, dataTypeName FROM vSamplesWithAttributes WHERE idStudy = ".$idStudy)) {
								while ($row = mysqli_fetch_assoc($result)) {
									echo "<option value=".$row['idDataType'].">".$row['dataTypeName']."</option>";
								}
							}
							?>
						</select><br>
						<button type=button onclick="filterStatisticsSamples()">Search through records.</button>
						<button type=button onclick="getSampleStatisticsSelection()">Select these samples.</button>
					</div>
					<div id="sampleStatisticsSelection"></div>
					<p class="guidelines" id="guide_filtering">
						<small>Select the samples you want to use when performing the statistics. When selecting the filter of attributes, also select how and on what attribute it should be filtered. 
						<br> When the samples are selected that should be used in the subsetting of the statistics, click the "Select these samples" button.</small>
					</p>
				</li>
				<li>
					
					<!-- Checkboxes for status of samples and background correction and statistics options -->
					<div>
						<input type="checkbox" id="performBackgroundCorrection" name="performBackgroundCorrection" checked onchange="showBackgroundCorrectionOptions()"/>Perform background correction?<br>
						<input type="checkbox" id="performVarianceStabilization" name="performVarianceStabilization" checked onchange="showVarianceStabilizationOptions()"/>Perform variance stabilization?<br>
						<input type="checkbox" id="performStatistics" name="performStatistics" checked onchange="showStatisticsOptions()"/>Perform statistics on raw/norm data? <small>(Define which, below)</small><br>
						<input type="checkbox" id="reorderSamples" name="reorderSamples" checked/>Reorder samples by experimental group? <small>(Used for the order in plots)</small><br>
						<input type="checkbox" id="nameInPCA" name="nameInPCA" />Label the data points in the PCA plots? <br>
																											
						<?php
						//Check if samples have been added
						if ($result =  mysqli_query($connection, "SELECT count(idStudy) as count FROM tSamples WHERE idStudy = $idStudy;")) {
							while ($row = mysqli_fetch_assoc($result)) {
								if($row['count'] != 0){
									echo "<input type='checkbox' checked disabled /><font color='green'>Have samples been added? (".$row['count']." samples) </font> <br>";
								}
								else{
									echo "<input type='checkbox' disabled /><font color='red'>Have samples been added?</font> <br>";
								}
							}
						}
						
						//Check if samples have arrayName attached
						if ($result =  mysqli_query($connection, "SELECT count(idStudy) as count FROM tSamples WHERE idStudy = $idStudy AND arrayName != 0 ;")) {
							while ($row = mysqli_fetch_assoc($result)) {
								if($row['count'] != 0){
									echo "<input type='checkbox' checked disabled /><font color='green'>Samples have array names? (".$row['count']." samples) </font> <br>";
									echo "<input type='checkbox' id='skipNoArrayName' name='skipNoArrayName' checked />Skip samples without array names?<br>";
								}
								else{
									echo "<input type='checkbox' disabled /><font color='red'>Samples have array names?</font> <br>";
								}
							}
						}
						
						//Check if the raw expression data already has been uploaded for this study. (Sample Gene Profile / Control Probe Profile)
						$checkFiles = 0;
						if ($result =  mysqli_query($connection, "SELECT DISTINCT idStudy FROM tFiles WHERE idStudy = $idStudy AND idFileType= 7 AND 4")) {
							while ($row = mysqli_fetch_assoc($result)) {
								$checkFiles = 1;
							}
						}
						
						if($checkFiles == 1){
							echo "<input type='checkbox' id='expressionDataUploaded' name='expressionDataUploaded' checked value='1' disabled/>This study has expression data?<br>";
						}else{
							echo "<input type='checkbox' id='expressionDataUploaded' name='expressionDataUploaded' value='0' disabled/>This study has expression data?<br>";
						}
						
						//Check if the study already has been normalized.
						$checkNorm = 0;
						if ($result =  mysqli_query($connection, "SELECT DISTINCT idStudy FROM tNormAnalysis WHERE idStudy = ".$idStudy)) {
							while ($row = mysqli_fetch_assoc($result)) {
								$checkNorm = 1;
							}
						}
	
						if($checkNorm == 1){
							echo "<input type='checkbox' name='hasBeenNormalized' checked disabled/>Has this study already been normalized?<br>";
						}else{
							echo "<input type='checkbox' name='hasBeenNormalized' disabled/>Has this study already been normalized?<br>";
						}
						?>
						<p class="guidelines" id="guide_2">
							<small>Is the data background-substracted? Also, is there even expression data added to this study and has there already been a normalization done?</small>
						</p>
					</div>
					<!-- End checkboxes -->
				</li>
				<!-- End basic info -->
				<!-- Start normalization options (From ArrayAnalysis.org)-->
				<li>
					<!-- Options Pre-processing -->
					<div>
						<table class="form" style="border: solid 1px #80FF80; background: #D9FFD9; width:100%;">
							<tbody>
								<tr>
									<td colspan="2" class="sectionTitle">Pre-processing</td>
								</tr>
								<tr>
									<td>Normalization type</td>
									<td><select id="normType" name="normType">
											<option value="lumi" selected>lumi</option>
											<option value="neqc">neqc</option>
									</select></td>
								</tr>
								<tr id="backgroundCorrectionOptions">
									<td>Background correction</td>
									<td><select name="bgCorrect_m">
											<option value="bgAdjust" selected>bgAdjust</option>
											<option value="forcePositive">forcePositive</option>
											<option value="bgAdjust.affy">bgAdjust.affy</option>
									</select></td>
								</tr>
								<tr id="varianceStabilizationOptions">
									<td>Variance stabilization</td>
									<td><select name="variance_Stab_m">
											<option value="vst">vst</option>
											<option value="log2" selected>log2</option>
											<option value="cubicRoot">cubicRoot</option>
									</select></td>
								</tr>
								<tr id="normalizationOptions">
									<td>Normalization</td>
									<td><select name="normalization_m">
											<option value="quantile" selected>quantile</option>
											<option value="rsn">rsn</option>
											<option value="ssn">ssn</option>
											<option value="loess">loess</option>
											<option value="vsn">vsn</option>
											<option value="rankinvariant">rankinvariant</option>
									</select></td>
								</tr>
								<tr>
									<td>P-value:<br> <small>Threshold for expression determination.</small></td>
									<td><input name="detectionTh" value="0.01" size="4" type="text"></td>
								</tr>
							</tbody>
						</table>
						<p class="guidelines" id="guide_3">
							<small> Choose the normalization options.</small>
						</p>
					</div>
					<!-- Input field for the description of the normalization run -->
					<label class="description" for="description">Provide a description for the normalization:</label>
					<input id="descNorm" name="descNorm" class="element textarea large" type="text"  value="" />
				</li>
				<!-- End option Pre-processing -->
				<li>
					<!-- Options Filtering -->
					<div>
						<script type="text/javascript">
								$('#normType').click(function() {
									if($('#normType').val() == "lumi") {
										$(".showLumi").show();
									} else {
										$(".showLumi").hide();
									}
								});
								
								//Function to display filtering
								function showFilterOptions() {
									if($('#filtering').is(':checked')) {
										$(".showFiltering").show();
									} else {
										$(".showFiltering").hide();
									}
								};
							</script>

						<table class="form" style="border: solid 1px #FFCC80; background: #FFE6BF; width:100%;">
							<tbody>
								<tr>
									<td colspan="1" class="sectionTitle">Filtering</td>
								</tr>
								<tr>
									<td>Perform filtering <input id="filtering" name="filtering" onchange="showFilterOptions()" checked style="margin-left: 20px;" type="checkbox" /></td>
								</tr>
								<tr class="showFiltering">
									<td>To speed up the processing and reduce false positives, remove the unexpressed probes.</td>
								</tr>
								<tr class="showFiltering">
									<td>More than <input name="filter_dp" value="0" size="4" type="text"> probes should have p-value &lt; <input name="filter_Th" value="0.01" size="4" type="text">.
									</td>
								</tr>
							</tbody>
						</table>
						<p class="guidelines" id="guide_4">
							<small>Select minimum number of probes to detect and the filtering threshold (default: &gt;0 and &lt;0.01)</small>
						</p>
					</div>
				</li>
				<!-- End Options Filtering -->
				<li>
					<!-- Options Annotation -->
					<div>
						<table class="form" style="border: solid 1px #BDBDBD; background: #F2F2F2; width:100%;">
							<tbody>
								<tr>
									<td class="sectionTitle">Annotation</td>
								</tr>
								<tr>
									<td>Create annotation file <input name="createAnno" checked disabled style="margin-left:20px" type="checkbox">
								</tr>
							</tbody>
						</table>
						<p class="guidelines" id="guide_5">
							<small>An annotation file will be made on the server.</small>
						</p>
					</div>
				</li>
				<!-- End Options Annotation -->
				<!-- Options Raw Plots -->
				<li id="rawPlots">
					<div>
						<table class="form" style="border: solid 1px #80D9FF; background: #BFECFF; width:100%;">
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
						<table class="form" style="border: solid 1px #9F8FBF; background: #cccde0; width:100%;">
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
				<li id="clusterTable">
					<div>
						<table class="form" style="border: solid 1px #FFE152; background: #FFF3BC; width:100%;">
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
					</div>
					<!-- Input field for the description of the statistics run -->
					<label class="description" for="description">Provide a description for the statistics:</label>
					<input id="descStat" name="descStat" class="element textarea large" type="text"  value="" />
				</li>
				<!-- End Clustering options -->
				<li>
					<button type="button" onclick="checkAttributes()">Normalize study-samples. </button>
				</li>
			</ol>
		</form>
		<!--End form normalization-->
	</div>
	<!--End div form-container-->
	<img id="bottom" src="../img/bottom.png" alt="">
	
	
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

		  	//Function to load a CRUD table for tStudyTypes used for subsetting statistics
			function showSampleNormalizationSelectTable() {
				//Prepare jTable
				$('#sampleNormalizationSelection').jtable({
					title: 'Samples of this study',
					paging: true,
					pageSize: 200,
					sorting: true,
					defaultSorting: 'idSample ASC',
		            selecting: true, //Enable selecting
		            multiselect: true, //Allow multiple selecting
		            selectingCheckboxes: true, //Show checkboxes on first column
					actions: {
						listAction: '../logic/optionsCRUD.php?action=list_tSamples'
					},
					fields: {
						idSample: {
							key: true,
							title: 'idSample',
							create: false,
							edit: false,
							list: true
						},
						name: {
							title: 'Sample Name'
						},
						arrayName: {
							title: 'Array ID'
						},
						compoundName:{
							title: 'compoundName'
						},
						casNumber:{
							title: 'casNumber'
						},
						typeName:{
							title: 'sampleType'
						}
					}
				});

				//Load list from server
				$('#sampleNormalizationSelection').jtable('load', {idStudy: <?php echo $idStudy; ?>});
			}; //End function normalization samples

	  	//Function to load a CRUD table for tStudyTypes used for subsetting statistics
		function showSampleStatisticsSelectTable() {
			//Prepare jTable
			$('#sampleStatisticsSelection').jtable({
				title: 'Samples of this study',
				paging: true,
				pageSize: 200,
				sorting: true,
				defaultSorting: 'idSample ASC',
	            selecting: true, //Enable selecting
	            multiselect: true, //Allow multiple selecting
	            selectingCheckboxes: true, //Show checkboxes on first column
				actions: {
					listAction: '../logic/optionsCRUD.php?action=list_tSamples'
				},
				fields: {
					idSample: {
						key: true,
						title: 'idSample',
						create: false,
						edit: false,
						list: true
					},
					name: {
						title: 'Sample Name'
					},
					arrayName: {
						title: 'Array ID'
					},
					compoundName:{
						title: 'compoundName'
					},
					casNumber:{
						title: 'casNumber'
					},
					typeName:{
						title: 'sampleType'
					}
				}
			});

			//Load list from server
			$('#sampleStatisticsSelection').jtable('load', {idStudy: <?php echo $idStudy; ?>});
		}; //End statistics samples

	</script>
</body>
</html>