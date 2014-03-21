<?php
/*
Author:					Job van Riet
Date of  creation:		11-2-14
Date of modification:	11-2-14
Version:				1.0
Modifications:			Original version
Known bugs:				None known
Function:				This page will present an overview of the samples of a specific study.
*/
?>

<?php
	//Get the idStudy from the session, if no session is made, let the user select a study.
	session_start();

	if (isset($_SESSION['idStudy']))
	{
		$idStudy = $_SESSION['idStudy'];
	}else{
		//Redirect to studyOverview of this study
		header('Location:chooseStudy' );
	}
?>

<?php
	error_reporting(E_ALL);
	ini_set('display_errors', '1');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>Samples of study: <?php echo $idStudy; ?></title>
<!--Load CSS for form layout -->
<link rel="stylesheet" type="text/css" href="../css/formLayout.css" media="all" />
<!--Load main jQuery library-->
<script src="../js/jquery-1.11.0.js" type="text/javascript"></script>
<!--Load the CSS for the table layout -->
<link rel="stylesheet" href="../css/tableLayout.css" type="text/css" media="print, projection, screen" />
<!--Load jQuery for chosen-->
<script src="../js/chosen.jquery.js" type="text/javascript"></script>
<!--Load CSS for autocomplete box-->
<link rel="stylesheet" href="../css/chosen.css" />

<!--Load tableSorter JQuery for sorting and pagination of tables-->
<script src="../js/jquery.tablesorter.js" type="text/javascript"></script>
<script src="../js/jquery.tablesorter.pager.js" type="text/javascript"></script>

<!--Load the tablesorter functions on the sampleTable (id of table)-->
<script type="text/javascript">
			$(document).ready(function() { 
				$("#sampleTable") 
					.tablesorter({ widthFixed: true, widgets: ['zebra']})
					.tablesorterPager({positionFixed: false, container: $("#pager")}); 
			}); 
</script>
</head>

<!-- Make connection to the DB -->
<?php 
	require_once('../logic/functions_dataDB.php');
	//Make a connection to the DIAMONDSDB (Defined in: functions_dataDB.php)
	$connection = makeConnectionToDIAMONDS();
?>

<div id="navBar">
	<?php require_once("menu.htm"); ?>
</div>

<script type="text/javascript">
//Function to get the samples the user selected to delete
function getSamples()
	{
	var selection = $('#sampleSelector').val();
	$('#selectedSamples').val(selection);
	}

</script>

<body id="main_body">
	<!--Form to delete (multiple) samples-->
	<img id="top" src="../img/top.png" alt="" />
	<div id="form_container">
		<h1>Delete samples from this study.</h1>
		<form class="appnitro" action="getForm.php" method="GET" name="deleteSamples" id="deleteSamples" onsubmit="getSamples()">
			<div class="form_description">
				<h2>Delete (multiple) samples from this study.</h2>
				<p>This form can be used to delete samples from this specific study.</p>
			</div>
			<!--Add hidden value to keep track of which form this is-->
			<input id="formType" name="formType" class="element text large" type="hidden" value="deleteSamples" />
			<!--Add hidden value to keep track of the selected samples-->
			<input id="selectedSamples" name="selectedSamples" type="hidden" />
			<ol>
				<li id="li_1"><label class="description" for="sampleSelector">Select the samples you want to remove/delete.</label>
					<div>
						<select data-placeholder="Choose the samples" id="sampleSelector" multiple class="chosen-select" style="width: 360px;" tabindex="2">
							<?php
						if ($result =  mysqli_query($connection, "SELECT * FROM tSamples WHERE idStudy = $idStudy")) {
							while ($row = mysqli_fetch_assoc($result)) {
								echo "<option value=".$row['idSample'].">".$row['name']."</option>";
							}
						}
					?>
						</select>
						<br><input type="checkbox" name="deleteAllSamples" id="deleteAllSamples" value="1">Delete all the samples of this study?<br>
					</div>
					<p class="guidelines" id="guide_5">
						<small>Select the samples you want to remove from this study.<br> <em>Multiple samples can be selected, check the box to delete all the samples.</em></small>
					</p> <input type="submit" value="Delete selected samples">
				</li>
			</ol>
		</form>
	</div>
	<!--End form select samples to delete-->

	<!--Show a table with all the samples of the given study-->
	<table id="sampleTable" class="tablesorter">
		<thead>
			<tr>
				<th>idStudy</th>
				<th>idSample</th>
				<th>Name</th>
				<th>sxsNumber</th>
				<th>Compound</th>
				<th>Compound-CAS</th>
				<th>SampleType</th>
				<th>Submission date</th>
				<?php
						if ($result =  mysqli_query($connection, "SELECT DISTINCT dataTypeName FROM vSamplesWithAttributes WHERE idStudy = ".$idStudy)) {
							while ($row = mysqli_fetch_assoc($result)) {
								echo "<th>".$row['dataTypeName']. "</th>";
							}
						}
					?>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<th>idStudy</th>
				<th>idSample</th>
				<th>Name</th>
				<th>sxsNumber</th>
				<th>Compound</th>
				<th>Compound-CAS</th>
				<th>SampleType</th>
				<th>Submission date</th>
				<?php
						//Keeps track of the order of datatypes
						$dataTypeArray = array();
						if ($result =  mysqli_query($connection, "SELECT DISTINCT dataTypeName, idDataType FROM vSamplesWithAttributes WHERE idStudy = ".$idStudy)) {
							while ($row = mysqli_fetch_assoc($result)) {
								echo "<th>".$row['dataTypeName']. "</th>";
								array_push($dataTypeArray, $row['idDataType']);
							}
						}
					?>
			</tr>
		</tfoot>
		<tbody>
			<?php
						if ($result =  mysqli_query($connection, "SELECT * FROM vSamplesWithInfoNames WHERE idStudy = $idStudy")) {
							while ($row = mysqli_fetch_assoc($result)) {
								echo "<tr>";
								echo "<td>".$row['idStudy']. "</td>";
								echo "<td>".$row['idSample']. "</td>";
								echo "<td>".$row['name']. "</td>";
								if($row['sxsName'] == ''){ echo "<td>N/A</td>";} else{echo "<td>".$row['sxsName']."</td>";};
								echo "<td>".$row['compoundName']. "</td>";
								echo "<td>".$row['casNumber']. "</td>";
								echo "<td>".$row['typeName']. "</td>";
								echo "<td>".$row['submissionDate']. "</td>";
								foreach($dataTypeArray as $dataType){
									if ($resultAttr =  mysqli_query($connection, "SELECT * FROM vSamplesWithAttributes WHERE idSample = ".$row['idSample']." AND idDataType = ".$dataType)) {
										while ($rowAttr = mysqli_fetch_assoc($resultAttr)) {
											echo "<td>".$rowAttr['attrValue']."</td>";
										}
									}
								}
								echo "</tr>";
							}
						}
					?>
		</tbody>
	</table>
	<!--End table-->

	<!--Pager for pagination of table-->
	<div id="pager" class="pager">
		<form action="">
			<img src="http://tablesorter.com/addons/pager/icons/first.png" class="first" alt="" /> 
			<img src="http://tablesorter.com/addons/pager/icons/prev.png" class="prev" alt="" /> 
			<input type="text" class="pagedisplay" disabled /> 
			<img src="http://tablesorter.com/addons/pager/icons/next.png" class="next" alt="" />
			<img src="http://tablesorter.com/addons/pager/icons/last.png" class="last"	alt="" />
			<select class="pagesize">
				<option selected="selected" value="10">10</option>
				<option value="20">20</option>
				<option value="30">30</option>
				<option value="40">40</option>
			</select>
		</form>
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