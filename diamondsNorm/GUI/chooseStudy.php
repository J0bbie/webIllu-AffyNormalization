<?php
/*
Author:					Job van Riet
Date of  creation:		20-2-14
Date of modification:	20-2-14
Version:				1.0
Modifications:			Original version
Known bugs:				None known
Function:				User will be redirected to this page if not logged in or selected a 
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
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
		<title>Choose/create Study</title>
		<!--Load CSS for form layout -->
		<link rel="stylesheet" type="text/css" href="../css/formLayout.css" media="all"/>
		<!--Load CSS for autocomplete box-->
		<link rel="stylesheet" href="../css/chosen.css"/>
		
		<!--Load main jQuery library-->
		<script src="../js/jquery-1.11.0.js" type="text/javascript" ></script>
		<!--Load jQueryUI for autocomplete/chosen-->
		<script src="../js/chosen.jquery.js" type="text/javascript"></script>
		<script src="../js/chosen.order.js" type="text/javascript"></script>
		
	</head>
	
	<?php 
		//Make a connection to the DIAMONDSDB (Defined in: functions_dataDB.php)
		require_once('../logic/functions_dataDB.php');
		$connection = makeConnectionToDIAMONDS();
	?>
	<div id="navBar"><?php require_once("menu.htm"); ?></div>
	
	<!-- 
	/////////////////////////////////////////
	//		Form to choose a study			/
	///////////////////////////////////////// 
	-->
		
	<body id="main_body" >
		<!-- Form to show study info -->
		<img id="top" src="../img/top.png" alt=""/>
		<div id="form_container">
			<h1>Choose a study.</h1>
			<form id="selectStudyForm" class="appnitro"  method="get" action="getForm" onsubmit="getTitle()">
			<!--Add hidden value to keep track of which form this is-->
			<input id="formType" name="formType" class="element text large" type="hidden" value="selectStudyForm"/>
			<input id="studyTitle" name="studyTitle" class="element text large" type="hidden" value=""/> 
				<div class="form_description">
					<p>Select or create a study.</p>
				</div>
				<ol>
					<li id="li_1" >
						<div>
						<label class="description" for="studySelect">Choose a study:</label>
						<select data-placeholder="Choose an existing study." style="width:100%" class="chosen-select" name="studySelect" id="studySelect" required>
							<?php
								if ($result =  mysqli_query($connection, "SELECT * FROM tStudy")) {
									while ($row = mysqli_fetch_assoc($result)) {
										echo "<option value=".$row['idStudy'].">".$row['title']."</option>";
									}
								}
							?>
						</select>
						</div><p class="guidelines" id="guide_1"><small>Choose which study to work with.</small></p>
						 <input type="submit" value="Choose study.">
					</li>
				</ol>
			</form> <!-- End form selectStudyForm-->
		</div>
			
		<div id="form_container">
			<form id="createStudy" class="appnitro"  method="post" action="createStudy">
			<!--Add hidden value to keep track of which form this is-->
			<ol>
				<li id="li_2" >
					<label class="description" for="createStudy">Create a new study.</label>
					<div>
					 <input type="submit" value="Create a new study.">
					</div><p class="guidelines" id="guide_2"><small>Click to go to form to create a new study.</small></p> 
				</li>
			</ol>
			</form> <!-- End button to create form -->
		</div>
		<!--End of form div-->
		<img id="bottom" src="../img/bottom.png" alt="">
		
		<script>
			//Function to get the title of the selected study
			function getTitle()
				{
				var selection = $('#studySelect option:selected').text();
				$('#studyTitle').val(selection);
				}
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