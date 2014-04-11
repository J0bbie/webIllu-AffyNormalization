<?php
/*
Author:					Job van Riet + ArrayAnalysis.org
Date of  creation:		10-4-14
Date of modification:	10-4-14
Version:				1.0
Modifications:			Original version
Known bugs:				None known
Function:				This page will present the user with options to perform a normilazation of the uploaded Affymetrix expression data.
						The normalization is done by an R scripts which runs a background deamon.
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
<title>Affymetrix normalization on study: <?php echo $idStudy; ?></title>
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
		$("#qcPlots").show();
		$("#normQCsignalPlots").show();
		$("#normQCarrayPlots").show();
		$("#descStatDiv").show();		
	} else {
		$("#qcPlots").hide();
		$("#normQCsignalPlots").hide();
		$("#normQCarrayPlots").hide();
		$("#descStatDiv").hide();
	}
};

function showSampleSelection() {
	if($('#sampleFiltering').is(':checked')) {
		$("#sampleSelection").show();
		$("#searchSamples").show();
		showSampleSelectTable();
	} else {
		$("#sampleSelection").hide();
		$("#searchSamples").hide();
	}
};

function filterSamples(){
	 $('#sampleSelection').jtable('load', {
		sampleName : $('#searchSampleName').val(),
		compoundName : $('#searchCompoundName').val(),
		sampleType : $('#searchSampleType').val(),
		attrValue : $('#searchAttributes').val(),
		attrFilter : $('#attrFilter').val(),
		dataTypeFilter : $('#dataTypeFilter').val(),
		idStudy : <?php echo $idStudy; ?>
     });
}

//Get the selected sample IDs and put them in a , separated string
function getSampleSelection(){
	var $selectedRows = $('#sampleSelection').jtable('selectedRows');
	var $line = "";
    $selectedRows.each(function () {
        var record = $(this).data('record').idSample;
       	if($line != ""){
       		$line += ","+record;
       	}else{
       		$line += record;
       	}
    });
    $('#selectedSamples').val($line);
    alert("Samples subset has been selected.");
}
</script>

<body onload="showSampleSelection()">
	<!-- Form to show normalization options -->
	<img id="top" src="../img/top.png" alt="" />
	<div id="form_container">
		<h1>Perform Affymetrix Normalization</h1>
		<!--Form to normalize samples-->
		<form action="getForm.php" method="GET" name="normalizeStudyForm" id="normalizeStudyForm">
			<!--Add hidden value to keep track of which form this is-->
			<input id="formType" name="formType" type="hidden" value="normalizeAffyStudy" /> 
			<input id="selectedAttributes" name="selectedAttributes" type="hidden" /> 
			<input id="selectedSamples"	name="selectedSamples" value=0 type="hidden" />
			<div class="form_description">
				<h2>Normalize the Affymetrix samples from this study.</h2>
				<p>This form can be used to normalize the Affymetrix omics data from the samples originating from this study.</p>
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
				<!-- Checkbox/filtering selection for sample filtering during normalization and statistics-->
				<li>
					<input type="checkbox" id="sampleFiltering" name="sampleFiltering" onchange="showSampleSelection()"/>Want to perform statistics on a subset only?
					<div id="searchSamples">
						<label class="description" for="searchSampleName">Filter on sample name:</label>
						<input id="searchSampleName" name="searchSampleName" class="element text large" type="text" maxlength="255" value="" />
						<label class="description" for="searchCompoundName">Filter on compound name:</label>
						<input id="searchCompoundName" name="searchCompoundName" class="element text large" type="text" maxlength="255" value="" />
						<label class="description" for="searchSampleType">Filter on sampleType:</label>
						<input id="searchSampleType" name="searchSampleType" class="element text large" type="text" maxlength="255" value="" />
						<label class="description" for="searchAttributes">Filter on attributes (Organ/Noel etc.):</label>
						<input id="searchAttributes" name="searchAttributes" class="element text large" type="text" maxlength="255" value="" />
						<select name="attrFilter" id="attrFilter">
							<option value="L">LIKE</option>
							<option value="NL">NOT LIKE</option>
							<option value="GT">&gt;=</option>
							<option value="LT">&lt;=</option>
						</select>
						<select name="dataTypeFilter" id="dataTypeFilter">
							<?php 
							if ($result =  mysqli_query($connection, "SELECT DISTINCT idDataType, dataTypeName FROM vSamplesWithAttributes WHERE idStudy = ".$idStudy)) {
								while ($row = mysqli_fetch_assoc($result)) {
									echo "<option value=".$row['idDataType'].">".$row['dataTypeName']."</option>";
								}
							}
							?>
						</select><br>
						<button type=button onclick="filterSamples()">Search through records.</button>
						<button type=button onclick="getSampleSelection()">Select these samples.</button>
					</div>
					<div id="sampleSelection"></div>
					<p class="guidelines" id="guide_filtering">
						<small>Select the samples you want to use when performing the statistics. When selecting the filter of attributes, also select how and on what attribute it should be filtered. 
						<br> When the samples are selected that should be used in the subsetting of the statistics, click the "Select these samples" button.</small>
					</p>
				</li>
				<li>
					
					<!-- Checkboxes for status of samples and statistics options -->
					<div>
						<input type="checkbox" id="performStatistics" name="performStatistics" checked onchange="showStatisticsOptions()"/>Perform statistics on raw/norm data? <small>(Define which, below)</small><br>
															
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
						
						//Check if samples have array names attached
						if ($result =  mysqli_query($connection, "SELECT count(idStudy) as count FROM tSamples WHERE idStudy = $idStudy AND sxsName != 0 ;")) {
							while ($row = mysqli_fetch_assoc($result)) {
								if($row['count'] != 0){
									echo "<input type='checkbox' checked disabled /><font color='green'>Samples have assay names? (".$row['count']." samples) </font> <br>";
									echo "<input type='checkbox' id='skipNoSXS' name='skipNoSXS' checked />Skip samples without assay names?<br>";
								}
								else{
									echo "<input type='checkbox' disabled /><font color='red'>Samples have assay names?</font> <br>";
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
				</ol>
				<!-- End basic info -->
			
				<!-- Start plotting options (From ArrayAnalysis.org)-->
				<ul id = qcPlots>
					
					<!-- Options sample Quality plots -->
					<li>
						<h3 style="text-align: center;">Select the plots of the raw data</h3>
						<div>
							<table class="form" style="border: solid 1px #80FF80; background: #D9FFD9; width: 100%;">
								<tbody>
									<tr>
										<td colspan="3" class="sectionTitle" style="text-align:center">Sample Quality</td>
									</tr>
									<tr>
										<td width='33%' style='text-align: center'>
											Plot sample prep controls <br>
											<input type="checkbox" name="SampleQuality" checked style="margin: 0 auto;">
										</td>
										<td width='33%' style='text-align: center'>
											Plot 3'/5' ratio <br>
											<input type="checkbox" name="35Ratio" checked style="margin: 0 auto;">
										</td>
										<td width='33%' style='text-align: center'>
											Plot RNA degradation <br>
											<input type="checkbox" name="rnaDegradation" style="margin: 0 auto;">
										</td>
									</tr>
								</tbody>
							</table>
							<p class="guidelines" id="guide_1">
								<small>
								If the (Dap, Phe, Lys, Trp) controls were spiked on your arrays, check the sample prep control box<br>
								<br>If the 3'/5' ratio for beta-actin and GAPDH gene controls should be plotted, check the 3'/5' ratio box<br>
								<br>Note: RNA degradation plot is not relevant for new generation PM-only arrays as there is no more 3' bias for probe design
								</small>
							</p>
						</div>
					</li>
					<!-- End option sample Quality plots -->
					
					<!-- Options signal quality plots -->
					<li>
						<div>
							<table class="form" style="border: solid 1px #FFCC80; background: #FFE6BF; width:100%;">
								<tbody>
									<tr>
										<td colspan="2" class="sectionTitle" style="text-align:center">Hybridization and overall signal quality</td>
									</tr>
									<tr style='text-align: center'>
										<td>Hybridization controls <input name="plotHybrid" checked style="float:right" type="checkbox"></td>
										<td>Background intensity <input name="plotBackIntens" checked style="float:right" type="checkbox"></td>
									</tr>
									<tr style='text-align: center'>
										<td>Percent present <input name="plotPercPresPlot" checked style="float:right" type="checkbox"></td>
										<td>Present/Marginal/Absent calls <input name="plotCalls" checked style="float:right" type="checkbox"></td>
									</tr>
									<tr style='text-align: center'>
										<td>Pos/Neg controls <input name="plotPosNegControls" checked style="float:right" type="checkbox"></td>
										<td>Profile & boxplot of all controls <input name="plotAllControls" checked style="float:right" type="checkbox"></td>
									</tr>
								</tbody>
							</table>
							<p class="guidelines" id="guide_2">
								<small>
								If the hybridization (bioB, bioC, bioD, creX) controls were spiked on your arrays, check the <b>hybridization controls</b> box<br>
								<br>If the minimal, maximal and average background intensity should be plotted, check the <b>background intensity</b> box<br>
								<br>If the percent of present calls should be plotted, check the <b>percent present</b> box<br>
								<br>If the percent of Marginal/Absent calls should be plotted, check the <b>Marginal/Absent calls</b> box<br>
								<br>If the pos/neg control intensity distribution should be plotted, check the <b>Pos/Neg control </b> box<br>
								<br>If the expression of all controls (and exon/intron controls) should be plotted, check the <b>Profile & boxplot of all controls </b> box<br>
								</small>
							</p>
						</div>
					</li>
					<!-- End Options signal quality plots -->
					
					<!-- Start signal comparability and bias diagnostic plots -->
					<li>
						<div>
						    <table class="form" style="border: solid 1px #80D9FF; background: #BFECFF; width:100%;">
						        <tbody>
						            <tr>
						                <td class="sectionTitle" colspan="3" style="text-align:center">Signal comparability and bias diagnostic</td>
						            </tr>
						            <!--  Signal distribution plots -->
						           	<tr>
						                <td>
						                    <table style="border: solid 1px #80D9FF; background: #E6F7FF; width:100%; ">
						                        <tbody>
						                            <tr>
														<td colspan="3" class="sectionTitle" style="text-align:center">Signal distribution</td>
						                            </tr>
						                            <tr style='text-align: center'>
														<td width="33%">Plot scale Factors<br> <input name="plotScaleFactors" checked type="checkbox" style="margin: 0 auto;"></td>
														<td width="33%">Plot boxplot Raw Log Intensity<br> <input name="plotBoxRawLogIntensity" checked type="checkbox" style="margin: 0 auto;"></td>
														<td width="33%">Plot density Raw Log Intensity<br> <input name="plotDensityRawLogIntensity" checked type="checkbox" style="margin: 0 auto;"></td>
						                            </tr>
						                        </tbody>
						                    </table>
						                </td>
						            </tr>
						       		<!-- End Signal distribution plots -->
						       		
						            <!--  Intensity-dependent bias plots -->
						           	<tr>
						                <td>
						                    <table style="border: solid 1px #80D9FF; background: #E6F7FF; width:100%; ">
						                        <tbody>
						                            <tr>
														<td colspan="3" class="sectionTitle" style="text-align:center">Intensity-dependent bias</td>
						                            </tr>
						                            <tr style='text-align: center'>
														<td width="50%">Plot MA-plot raw-log intensities<br> <input name="plotRawMA" checked type="checkbox" style="margin: 0 auto;"></td>
														<td width="50%">Plot MA per experimental group (if given), else on all arrays<br> <input name="plotRawMAOnGroup" checked type="checkbox" style="margin: 0 auto;">
						                            </tr>
						                        </tbody>
						                    </table>
						                </td>
						            </tr>
						       		<!-- End Intensity-dependent bias plots -->
						       		
						            <!--  Spatial bias plots -->
						           	<tr>
						                <td>
						                    <table style="border: solid 1px #80D9FF; background: #E6F7FF; width:100%; ">
						                        <tbody>
						                            <tr>
														<td colspan="2" class="sectionTitle" style="text-align:center">Spatial bias</td>
						                            </tr>
						                            <tr style='text-align: center'>
														<td width="50%">Plot array reference layout <br> <input name="plotArrayReferceLayout" checked type="checkbox" style="margin: 0 auto;"></td>
														<td width="50%">Plot Pos/Neg Center of Intensity<br> <input name="plotPosNegCenterOfIntensity" checked type="checkbox" style="margin: 0 auto;"></td>
													</tr>
													<tr style='text-align: center'>
														<td width="50%">Plot 2D images <br> <input name="plot2DImages" checked type="checkbox" style="margin: 0 auto;"></td>
														<td width="50%">Plot all PLM-based images  <br> <input name="plotPLM" checked type="checkbox" style="margin: 0 auto;"></td>
													</tr>
						                        </tbody>
						                    </table>
						                </td>
						            </tr>
						       		<!-- End Spatial bias plots -->
						       		
						            <!--  Probe-set homogeneity plots -->
						           	<tr>
						                <td>
						                    <table style="border: solid 1px #80D9FF; background: #E6F7FF; width:100%; ">
						                        <tbody>
						                            <tr>
														<td colspan="2" class="sectionTitle" style="text-align:center">Probe-set homogeneity</td>
						                            </tr>
													<tr style='text-align: center'>
														<td width="50%">Plot NUSE <br> <input name="plotNUSE" checked type="checkbox" style="margin: 0 auto;"></td>
														<td width="50%">Plot RLE <br> <input name="plotRLE" checked type="checkbox" style="margin: 0 auto;"></td>
													</tr>
						                        </tbody>
						                    </table>
						                </td>
						            </tr>
						       		<!-- End Probe-set homogeneity plots -->
						       		
						  		</tbody>
						    </table>
						    <!-- End signal comparability and bias diagnostic table -->
							<p class="guidelines" id="guide_3">
								<small>
								Select the plots that needs to be run for signal comparability and bias diagnostic.
								</small>
							</p>
						</div>
					</li>
					<!-- End signal comparability and bias diagnostic plots -->	
					
					<!-- Options Clustering -->
					<li id="clusterTable">
						<div>
							<table class="form" style="border: solid 1px #FFE152; background: #FFF3BC; width:100%;">
								<tbody>
									<tr>
										<td colspan="3" class="sectionTitle" style="text-align:center">Clustering options</td>
									</tr>
									<tr style='text-align: center'>
										<td width="33%">Plot raw array-array correlation<br> <input name="plotRawArrayCorrelation" checked type="checkbox" style="margin: 0 auto;"></td>
										<td width="33%">Plot raw two-axes PCA<br> <input name="plotRawPCA" checked type="checkbox" style="margin: 0 auto;"></td>
										<td width="33%">Plot raw hierarchical clustering<br> <input name="plotDensityRawLogIntensity" checked type="checkbox" style="margin: 0 auto;"></td>
		                            </tr>
									<tr style='text-align: center'>
										<td>
											Distance calculation method<br>
											<select name="clustoption1" id="clustoption1" onchange="" size="1">
													<option value="Pearson">Pearson</option>
													<option value="Spearman">Spearman</option>
													<option value="Euclidean">Euclidean</option>
											</select>
										</td>
										<td></td>
										<td>
										Clustering method<br>
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
					</li>
				</ul>
				<ul>
					<h3 style="text-align: center;">Pre-processing and QC</h3>
					
					<!-- Options normalization and QC -->
					<li>
						<div>
							<!-- Normalization/annotation options -->
							<table class="form" style="border: solid 1px #BDBDBD; background: #F2F2F2; width: 100%;">
								<tbody>
									<tr>
										<td colspan="3" class="sectionTitle" style="text-align:center">Pre-processing: normalization method and annotation</td>
									</tr>
									<tr>
										<td>
											<table style="border: solid 1px #FF1800; background: #FF8073; width:100%; ">
												<tr>
													<td colspan="3" class="sectionTitle" style="text-align:center">Normalization options</td>
												</tr>
						                        <tbody>
							                        <tr>
														<td width='33%' style='text-align: center'>
															Normalization method <br>
															<input type="checkbox" name="normMethod" checked style="margin: 0 auto;">
														</td>
														<td width='33%' style='text-align: center'>
															Normalize per group <br>
															<input type="checkbox" name="normPerGroup" checked style="margin: 0 auto;">
														</td>
														<td width='33%' style='text-align: center'>
															Use custom annotation? <br>
															<input type="checkbox" name="customAnnotation" style="margin: 0 auto;">
														</td>
													</tr>
													<tr>
														<td width='33%' style='text-align: center'>
															Annotation type <br>
															<select id="annotationType" name="annotationType" onchange="" size="1">
																<option value="ENSG">
																	Ensembl Gene (ENSG)
																</option>
																<option value="ENTREZG">
																	ENTREZ Gene
																</option>
																<option value="REFSEQ">
																	RefSeq
																</option>
																<option value="UG">
																	UniGene
																</option>
																<option value="ENST">
																	Ensembl Transcript
																</option>
																<option value="VEGAG">
																	Vega Gene
																</option>
																<option value="VEGAT">
																	Vega Transcript
																</option>
																<option value="TAIRG">
																	TAIR Gene
																</option>
																<option value="TAIRT">
																	TAIR transcript
																</option>
																<option value="MIRBASEG">
																	miRBase Gene
																</option>
																<option value="MIRBASEF">
																	miRBase Function
																</option>
															</select>
														</td>
														<td></td>
														<td width='33%' style='text-align: center'>
															Species <br>
															<select id="species" name="species" onchange="" size="1">
																<option value="Ag">
																	Anopheles gambiae
																</option>
																<option value="At">
																	Arabidopsis thaliana
																</option>
																<option value="Bt">
																	Bos taurus
																</option>
																<option value="Ce">
																	Caenorhabditis elegans
																</option>
																<option value="Cf">
																	Canis familiaris
																</option>
																<option value="Dr">
																	Danio rerio
																</option>
																<option value="Dm">
																	Drosophila melanogaster
																</option>
																<option value="Gg">
																	Gallus gallus
																</option>
																<option value="Hs">
																	Homo sapiens
																</option>
																<option value="MAmu">
																	Macaca mulatta
																</option>
																<option value="Mm">
																	Mus musculus
																</option>
																<option value="Os">
																	Oryza sativa
																</option>
																<option value="Rn">
																	Rattus norvegicus
																</option>
																<option value="Sc">
																	Saccharomyces cerevisiae
																</option>
																<option value="Sp">
																	Schizosaccharomyces pombe
																</option>
																<option value="Ss">
																	Sus scrofa
																</option>
															</select>
														</td>
													</tr>
												</tbody>
											</table>
										</td>
									</tr>
									<!-- Signal comparability and bias of normalized intensities -->
									<tr id = normQCsignalPlots >
										<td>
											<table style="border: solid 1px #F9F9F9; background: #FFFFFF; width:100%; ">
												<tr>
													<td colspan="3" class="sectionTitle" style="text-align:center">Signal comparability and bias of normalized intensities</td>
												</tr>
						                        <tbody>
						                        	<tr>
														<td width='33%' style='text-align: center'>
															Plot boxplot of normalized log intensities <br>
															<input type="checkbox" name="plotBoxNormLogIntensity" checked style="margin: 0 auto;">
														</td>
														<td width='33%' style='text-align: center'>
															Plot density histogram of normalized log intensities <br>
															<input type="checkbox" name="plotDensityNormLogIntensity" checked style="margin: 0 auto;">
														</td>
														<td width='33%' style='text-align: center'>
															Plot MA-plot of normalized log intensities <br>
															<input type="checkbox" name="plotNormMA" checked style="margin: 0 auto;">
														</td>
													</tr>
												</tbody>
											</table>
										</td>
									</tr>
									<!-- Normalized array correlation plost -->
									<tr id = normQCarrayPlots>
										<td>
											<table style="border: solid 1px #80D9FF; background: #E6F7FF; width:100%; ">
												<tr>
													<td colspan="3" class="sectionTitle" style="text-align:center">Plots of normalized arrays</td>
												</tr>
						                        <tbody>
													<tr style='text-align: center'>
														<td width="33%">Plot norm array-array correlation<br> <input name="plotNormArrayCorrelation" checked type="checkbox" style="margin: 0 auto;"></td>
														<td width="33%">Plot norm two-axes PCA<br> <input name="plotNormPCA" checked type="checkbox" style="margin: 0 auto;"></td>
														<td width="33%">Plot norm hierarchical clustering<br> <input name="plotDensityNormLogIntensity" checked type="checkbox" style="margin: 0 auto;"></td>
						                            </tr>
												</tbody>
											</table>
										</td>
									</tr>
								</tbody>
							</table>
							<p class="guidelines" id="guide_1">
								<small>
								Select the normalization method and the annotation setting which need to be used for this normalization <br> <br>
								Also select the plots that need to be made of the normalized intensities and array correlations.
								</small>
							</p>
						</div>
						<!-- Input field for the description of the statistics run -->
						<div id = descStatDiv>
							<label class="description" for="description">Provide a description for the statistics:</label>
							<input id="descStat" name="descStat" class="element textarea large" type="text"  value="" />
						</div>
					</li>
					<!-- End option sample Quality plots -->
				</ul> <!-- End plot options -->
				<li>
					<button type="button" onclick="checkAttributes()">Normalize study-samples.</button>
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
	  
	  	//Function to load a CRUD table for tStudyTypes
		function showSampleSelectTable() {
			//Prepare jTable
			$('#sampleSelection').jtable({
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
					sxsName: {
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
			$('#sampleSelection').jtable('load', {idStudy: <?php echo $idStudy; ?>});
		}; //End function samples

	</script>
</body>
</html>