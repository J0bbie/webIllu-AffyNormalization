<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>Create a new study</title>
<!--Load CSS for form layout -->
<link rel="stylesheet" type="text/css" href="../css/formLayout.css" media="all">
<!--Load CSS for autocomplete box-->
<link rel="stylesheet" href="../css/chosen.css" />
<!--Load jQueryUI for autocomplete-->
<script type="text/javascript" src="../js/jquery-1.11.0.js"></script>
<script src="../js/chosen.jquery.js" type="text/javascript"></script>

</head>

<!--Include the scripts that contain the functions -->
<?php
	//Include the scripts containing the config variables
	require_once('../logic/config.php');

	// Show PHP errors if config has this enabled
	if(CONFIG_ERRORREPORTING){
		error_reporting(E_ALL);
		ini_set('display_errors', '1');
	}
	
	 require_once('../logic/functions_dataDB.php');
	 //Initialize DIAMONDSDBClass
	 $connection = makeConnectionToDIAMONDS();
?>

<div id="navBar">
	<?php require_once("menu.htm"); ?>
</div>

<body id="main_body">
	<!-- Form to create a new study -->
	<img id="top" src="../img/top.png" alt="" />
	<div id="form_container">
		<h1>
			<a>Create a new study</a>
		</h1>
		<form id="createStudyForm" class="appnitro" method="get" action="getForm.php">
			<!--Add hidden value to keep track of which form this is-->
			<input id="formType" name="formType" class="element text large" type="hidden" value="createStudyForm" />
			<div class="form_description">
				<h2>Create a new study</h2>
				<p>This form can be used to create a new study in DIAMONDS. After a study is made, samples can be added etc.</p>
			</div>
			<ul>
				<li id="li_1"><label class="description" for="studyTitle">Title of the study: </label>
					<div>
						<input id="studyTitle" name="studyTitle" class="element text large" type="text" maxlength="255" value="" required />
					</div>
					<p class="guidelines" id="guide_1">
						<small>Give a title to the study by which it can easily be recognized. E.g. &lt;Consortium&gt;_&lt;SubGroup&gt;_&lt;StudyNr&gt;</small>
					</p></li>
				<li id="li_2"><label class="description" for="studyDescription">Description of the study: (Optional) </label>
					<div>
						<textarea id="studyDescription" name="studyDescription" class="element textarea medium" cols="" rows=""></textarea>
					</div>
					<p class="guidelines" id="guide_2">
						<small>Optional: Give a brief description of the study. E.g. single-dose experiment for determining X in Y.</small>
					</p></li>
				<li id="li_3"><label class="description" for="studyType">Select a type of study: </label>
					<div>
						<select data-placeholder="Choose the correct type of study" id="studyType" name="studyType" class="chosen-select" style="width: 360px;" tabindex="2">
							<option value="" selected="selected"></option>
							<?php
							if ($result =  mysqli_query($connection, "SELECT * FROM tStudyType")) {
								while ($row = mysqli_fetch_assoc($result)) {
									echo "<option value=".$row['idStudyType'].">".$row['name']."</option>";
								}
							}
						?>
						</select> <input type="button" class="button_text" value="Add a new studyType" onClick="window.open('dataOverview?crudType=studyType');" />
					</div>
					<p class="guidelines" id="guide_5">
						<small>Select the type of study, e.g. Single-Dose Toxicity/Genotoxicity. <br> <br> 
						<em>If a new type is needed, use the "Add new study type" button to add this and reload	the page.</em></small>
					</p></li>
				<li id="li_4"><label class="description" for="assayType">Select the type of assay used: </label>
					<div>
						<select data-placeholder="Choose the correct assay" name="assayType" id="assayType" class="chosen-select" style="width: 360px;" tabindex="2">
							<option value="" selected="selected"></option>
							<?php
							if ($result =  mysqli_query($connection, "SELECT * FROM tAssayType")) {
								while ($row = mysqli_fetch_assoc($result)) {
									echo "<option value=".$row['idAssayType'].">".$row['name']."</option>";
								}
							}
						?>
						</select>
					</div>
					<p class="guidelines" id="guide_6">
						<small>Select the type of array which is used. E.g. in vitro/vivo etc.</small>
					</p></li>
				<li id="li_5"><label class="description" for="species">Select the type of species used: </label>
					<div>
						<select data-placeholder="Choose the main species of the study" name="species" id="species" class="chosen-select" style="width: 360px;" tabindex="2">
							<option value="" selected="selected"></option>
							<?php
							if ($result =  mysqli_query($connection, "SELECT * FROM tSpecies")) {
								while ($row = mysqli_fetch_assoc($result)) {
									echo "<option value=".$row['idSpecies'].">".$row['name']."</option>";
								}
							}
						?>
						</select>
					</div>
					<p class="guidelines" id="guide_8">
						<small>Select the species that was in which the study is performed.</small>
					</p></li>
				<li id="li_6"><label class="description" for="domainType">Select the type of domain: </label>
					<div>
						<select data-placeholder="Choose the correct domain" name="domainType" id="domainType" class="chosen-select" style="width: 360px;" tabindex="2">
							<option value="" selected="selected"></option>
							<?php
							if ($result =  mysqli_query($connection, "SELECT * FROM tDomains")) {
								while ($row = mysqli_fetch_assoc($result)) {
									echo "<option value=".$row['idDomain'].">".$row['name']."</option>";
								}
							}
						?>
						</select> <input type="button" class="button_text" value="Add a new domain" onClick="window.open('dataOverview?crudType=domains');" />
					</div>
					<p class="guidelines" id="guide_7">
						<small>Select the type of domain of the study. E.g. Liver toxicity/kidney toxicity/developmental toxicity. <br> <br> <i>If a new domain is needed, use the "Add new domain
								type" button to add this and reload the page.</i></small>
					</p></li>
				<li id="li_7"><label class="description" for="studyCurator">Curator </label>
					<div>
						<input id="studyCurator" name="studyCurator" class="element text medium" type="text" maxlength="255" value="" required />
					</div>
					<p class="guidelines" id="guide_3">
						<small>Type in the curator for this study.</small>
					</p></li>
				<li id="li_8"><label class="description" for="studySource">Source </label>
					<div>
						<input id="studySource" name="studySource" class="element text medium" type="text" maxlength="255" value="" required />
					</div>
					<p class="guidelines" id="guide_4">
						<small>Type in the source for this study. E.g. TNO/NTC etc.</small>
					</p></li>
				<li ><input id="submit" class="button_text" type="submit" value="Submit new study" /></li>
			</ul>
		</form>
	</div>
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
	  </script>

</body>
</html>