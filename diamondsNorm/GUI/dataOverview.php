<?php
/*
Author:					Job van Riet
Date of  creation:		21-2-14
Date of modification:	21-2-14
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
	} else {
		// Redirect to studyOverview of this study
		header ( 'Location: chooseStudy' );
	}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>Files of study: <?php echo $idStudy; ?></title>
<!--Load CSS for form layout -->
<link rel="stylesheet" type="text/css" href="../css/formLayout.css" media="all" />
<!-- Include one of jTable styles. -->
<link href="../css/lightcolor/orange/jtable.css" rel="stylesheet" type="text/css" />
<link href="../css/jQueryUI.css" rel="stylesheet" type="text/css" />

<!--Load main jQuery library-->
<script src="../js/jquery-1.11.0.js" type="text/javascript"></script>
<!--Load jQueryUI-->
<script src="../js/jquery-ui.js" type="text/javascript"></script>

<!--Load jQuery for chosen-->
<script src="../js/chosen.jquery.js" type="text/javascript"></script>
<!--Load CSS for autocomplete box-->
<link rel="stylesheet" href="../css/chosen.css" />

<!-- Include jTable script file. -->
<script src="../js/jquery.jtable.js" type="text/javascript"></script>
</head>

<div id="navBar">
	<?php require_once("menu.htm"); ?>
</div>

<?php 
	//Get the crudType from the GET request if it was given
	if(isset($_GET['crudType'])){
		$crudType= $_GET['crudType'];
	}
?>

<!-- 
/////////////////////////////////////////
//		Functions to hide containers	/
/////////////////////////////////////////
 -->
<!--Based on the selection from the sample functionality options-->
<script type="text/javascript">
	function showCRUD(){
		var selection = $('#crudTypeSelect').val();
		switch(selection){ 
			case "studyType":
				$('#studyTypeContainer').show();
				$('#domainsContainer').hide();
				$('#fileTypeContainer').hide();
				$('#assayTypeContainer').hide();
				$('#compoundContainer').hide();
				$('#dataTypeContainer').hide();
				$('#sampleTypeContainer').hide();
				$('#searchStudyTypes').show();
				$('#searchDomainTypes').hide();
				$('#searchFileTypes').hide();
				$('#searchAssayTypes').hide();
				$('#searchCompounds').hide();
				$('#searchDataTypes').hide();
				$('#searchSampleTypes').hide();
				showStudyTypeTable();
				break;
			case "domains":
				$('#studyTypeContainer').hide();
				$('#domainsContainer').show();
				$('#fileTypeContainer').hide();
				$('#assayTypeContainer').hide();
				$('#compoundContainer').hide();
				$('#dataTypeContainer').hide();
				$('#sampleTypeContainer').hide();
				$('#searchStudyTypes').hide();
				$('#searchDomainTypes').show();
				$('#searchFileTypes').hide();
				$('#searchAssayTypes').hide();
				$('#searchCompounds').hide();
				$('#searchDataTypes').hide();
				$('#searchSampleTypes').hide();
				showDomainsTable();
				break;
			case "fileType":
				$('#studyTypeContainer').hide();
				$('#domainsContainer').hide();
				$('#fileTypeContainer').show();
				$('#assayTypeContainer').hide();
				$('#compoundContainer').hide();
				$('#dataTypeContainer').hide();
				$('#sampleTypeContainer').hide();
				$('#searchStudyTypes').hide();
				$('#searchDomainTypes').hide();
				$('#searchFileTypes').show();
				$('#searchAssayTypes').hide();
				$('#searchCompounds').hide();
				$('#searchDataTypes').hide();
				$('#searchSampleTypes').hide();
				showFileTypeTable();
				break;
			case "assayType":
				$('#studyTypeContainer').hide();
				$('#domainsContainer').hide();
				$('#fileTypeContainer').hide();
				$('#assayTypeContainer').show();
				$('#compoundContainer').hide();
				$('#dataTypeContainer').hide();
				$('#sampleTypeContainer').hide();
				$('#searchStudyTypes').hide();
				$('#searchDomainTypes').hide();
				$('#searchFileTypes').hide();
				$('#searchAssayTypes').show();
				$('#searchCompounds').hide();
				$('#searchDataTypes').hide();
				$('#searchSampleTypes').hide();
				showAssayTypeTable();
				break;
			case "compound":
				$('#studyTypeContainer').hide();
				$('#domainsContainer').hide();
				$('#fileTypeContainer').hide();
				$('#assayTypeContainer').hide();
				$('#dataTypeContainer').hide();
				$('#sampleTypeContainer').hide();
				$('#compoundContainer').show();
				$('#searchStudyTypes').hide();
				$('#searchDomainTypes').hide();
				$('#searchFileTypes').hide();
				$('#searchAssayTypes').hide();
				$('#searchCompounds').show();
				$('#searchDataTypes').hide();
				$('#searchSampleTypes').hide();
				showCompoundTable();
				break;
			case "dataType":
				$('#studyTypeContainer').hide();
				$('#domainsContainer').hide();
				$('#fileTypeContainer').hide();
				$('#assayTypeContainer').hide();
				$('#compoundContainer').hide();
				$('#dataTypeContainer').show();
				$('#sampleTypeContainer').hide();
				$('#searchStudyTypes').hide();
				$('#searchDomainTypes').hide();
				$('#searchFileTypes').hide();
				$('#searchAssayTypes').hide();
				$('#searchCompounds').hide();
				$('#searchDataTypes').show();
				$('#searchSampleTypes').hide();
				showDataTypeTable();
				break;
			case "sampleType":
				$('#studyTypeContainer').hide();
				$('#domainsContainer').hide();
				$('#fileTypeContainer').hide();
				$('#assayTypeContainer').hide();
				$('#compoundContainer').hide();
				$('#dataTypeContainer').hide();
				$('#sampleTypeContainer').show();
				$('#searchStudyTypes').hide();
				$('#searchDomainTypes').hide();
				$('#searchFileTypes').hide();
				$('#searchAssayTypes').hide();
				$('#searchCompounds').hide();
				$('#searchDataTypes').hide();
				$('#searchSampleTypes').show();
				showSampleTypeTable();
				break;
		default:
			$('#studyTypeContainer').hide();
			$('#domainsContainer').hide();
			$('#fileTypeContainer').hide();
			$('#assayTypeContainer').hide();
			$('#compoundContainer').hide();
			$('#dataTypeContainer').hide();
			$('#sampleTypeContainer').hide();
			$('#searchStudyTypes').hide();
			$('#searchDomainTypes').hide();
			$('#searchFileTypes').hide();
			$('#searchAssayTypes').hide();
			$('#searchCompounds').hide();
			$('#searchDataTypes').hide();
			$('#searchSampleTypes').hide();
			break;
		}
	};
</script>


<body onload="showCRUD()">
	<!-- Form to show study info -->
	<img id="top" src="../img/top.png" alt="" />
	<div id="form_container">
		<h1>Files of this study.</h1>
		<form class="appnitro" method="get" onsubmit="return false">
			<div class="form_description">
				<h2>Data overview.</h2>
				<p>
					This page shows the data as they are stored in the database. <br>CRUD (Create/Read/Update/Delete) functions can be performed on the selected data.
				</p>
			</div>
			<!-- 
			/////////////////////////////////////////
			//		Choose which data to show		/
			/////////////////////////////////////////
			 -->
			<ol>
				<li id="li_1"><label class="description" for="crudTypeSelect">Select the data on which you want to perform CRUD:</label> 
				<select data-placeholder="Choose the table you want to view/edit."
					style="width: 100%" class="chosen-select" name="crudTypeSelect" id="crudTypeSelect" onChange="showCRUD()">
						<option value="" selected></option>
						<option value="studyType" <?php if($crudType == "studyType"){echo "selected";} ?>>Study Types</option>
						<option value="domains" <?php if($crudType == "domains"){echo "selected";} ?>>Domain Types</option>
						<option value="fileType" <?php if($crudType == "fileType"){echo "selected";} ?>>File Types</option>
						<option value="assayType" <?php if($crudType == "assayType"){echo "selected";} ?>>Assay Types</option>
						<option value="compound" <?php if($crudType == "compound"){echo "selected";} ?>>Compounds</option>
						<option value="dataType" <?php if($crudType == "dataType"){echo "selected";} ?>>Data Types</option>
						<option value="sampleType" <?php if($crudType == "sampleType"){echo "selected";} ?>>Sample Types</option>
				</select>
					<p class="guidelines" id="guide_1">
						<small>Select a table to perform CRUD on.</small>
					</p>
				</li>
				<li id="searchStudyTypes"><label class="description" for="studyTypeName">Search on study type name:</label>
					<div>
						<input id="studyTypeName" name="studyTypeName" class="element text large" type="text" maxlength="255" value="" />
					</div>
					<button type=button onclick="searchTable('studyType')">Search through records.</button>
				</li>
				<li id="searchDomainTypes"><label class="description" for="domainName">Search on domain name:</label>
					<div>
						<input id="domainName" name="domainName" class="element text large" type="text" maxlength="255" value="" />
					</div>
					<button type=button onclick="searchTable('domains')">Search through records.</button>
				</li>
				<li id="searchFileTypes"><label class="description" for="fileName">Search on file name:</label>
					<div>
						<input id="fileName" name="fileName" class="element text large" type="text" maxlength="255" value="" />
					</div>
					<button type=button onclick="searchTable('fileType')">Search through records.</button>
				</li>
				<li id="searchAssayTypes"><label class="description" for="arrayName">Search on array name:</label>
					<div>
						<input id="arrayName" name="arrayName" class="element text large" type="text" maxlength="255" value="" />
					</div>
					<button type=button onclick="searchTable('assayType')">Search through records.</button>
				</li>
				<li id="searchCompounds"><label class="description" for="compoundName">Search on compound name:</label>
					<div>
						<input id="compoundName" name="compoundName" class="element text large" type="text" maxlength="255" value="" />
					</div>
					<button type=button onclick="searchTable('compound')">Search through records.</button>
				</li>
				<li id="searchDataTypes"><label class="description" for="dataName">Search on dataType name:</label>
					<div>
						<input id="dataName" name="dataName" class="element text large" type="text" maxlength="255" value="" />
					</div>
					<button type=button onclick="searchTable('dataType')">Search through records.</button>
				</li>
				<li id="searchSampleTypes"><label class="description" for="sampleTypeName">Search on sampleType name:</label>
					<div>
						<input id="sampleTypeName" name="sampleTypeName" class="element text large" type="text" maxlength="255" value="" />
					</div>
					<button type=button onclick="searchTable('sampleType')">Search through records.</button>
				</li>
			</ol>
		</form>
		<!-- End form studyInfo-->
	</div>
	<!--End div form-container-->
	<img id="bottom" src="../img/bottom.png" alt="">

	<!--CRUD Tables containing the data of the requested table-->
	<div id="studyTypeContainer"></div>
	<div id="domainsContainer"></div>
	<div id="fileTypeContainer"></div>
	<div id="assayTypeContainer"></div>
	<div id="compoundContainer"></div>
	<div id="dataTypeContainer"></div>
	<div id="sampleTypeContainer"></div>

	<script type="text/javascript">

	/////////////////////////////////////////
	//		Definitions of the CRUDTables	/
	/////////////////////////////////////////

    //Re-load records when user clicks search button.
    function searchTable(selected) {
        
    	switch(selected){ 
			case "studyType":
		        $('#studyTypeContainer').jtable('load', {
		            name: $('#studyTypeName').val()
		        });
			case "domains":
		        $('#domainsContainer').jtable('load', {
		            name: $('#domainName').val()
		        });
			case "fileType":
		        $('#fileTypeContainer').jtable('load', {
		            name: $('#fileName').val()
		        });
			case "assayType":
		        $('#assayTypeContainer').jtable('load', {
		            name: $('#arrayName').val()
		        });
			case "compound":
		        $('#compoundContainer').jtable('load', {
		            name: $('#compoundName').val()
		        });	
			case "dataType":
		        $('#dataTypeContainer').jtable('load', {
		            name: $('#dataName').val()
		        });
			case "sampleType":
		        $('#sampleTypeContainer').jtable('load', {
		            name: $('#sampleTypeName').val()
		        });
    	}
    };
	
		//Function to load a CRUD table for tStudyTypes
		function showStudyTypeTable() {
			//Prepare jTable
			$('#studyTypeContainer').jtable({
				title: 'StudyTypes',
				paging: true,
				pageSize: 10,
				sorting: true,
				defaultSorting: 'idStudyType ASC',
				actions: {
					listAction: '../logic/optionsCRUD.php?action=list_tStudyType',
					createAction: '../logic/optionsCRUD.php?action=create_tStudyType',
					updateAction: '../logic/optionsCRUD.php?action=update_tStudyType',
					deleteAction: '../logic/optionsCRUD.php?action=delete_tStudyType'
				},
				fields: {
					idStudyType: {
						key: true,
						title: 'idStudyType',
						create: false,
						edit: false,
						list: true
					},
					name: {
						title: 'Name of the type of study'
					},
					description: {
						title: 'Description of study type'
					}					
				}
			});

			//Load list from server
			$('#studyTypeContainer').jtable('load');
		}; //End function studyTypes

		//Function to load a CRUD table for tSampleTypes
		function showSampleTypeTable() {
			//Prepare jTable
			$('#sampleTypeContainer').jtable({
				title: 'Type of samples',
				paging: true,
				pageSize: 10,
				sorting: true,
				defaultSorting: 'idSampleType ASC',
				actions: {
					listAction: '../logic/optionsCRUD.php?action=list_tSampleType',
					createAction: '../logic/optionsCRUD.php?action=create_tSampleType',
					updateAction: '../logic/optionsCRUD.php?action=update_tSampleType',
					deleteAction: '../logic/optionsCRUD.php?action=delete_tSampleType'
				},
				fields: {
					idSampleType: {
						key: true,
						title: 'idSampleType',
						create: false,
						edit: false,
						list: true
					},
					name: {
						title: 'Name of the sample type'
					},
					description: {
						title: 'Description of sample type'
					}
				}
			});

			//Load list from server
			$('#sampleTypeContainer').jtable('load');
		}; //End function sampleTypes

		//Function to load a CRUD table for tDomains
		function showDomainsTable() {
			//Prepare jTable
			$('#domainsContainer').jtable({
				title: 'Domains',
				paging: true,
				pageSize: 10,
				sorting: true,
				defaultSorting: 'idDomain ASC',
				actions: {
					listAction: '../logic/optionsCRUD.php?action=list_tDomains',
					createAction: '../logic/optionsCRUD.php?action=create_tDomains',
					updateAction: '../logic/optionsCRUD.php?action=update_tDomains',
					deleteAction: '../logic/optionsCRUD.php?action=delete_tDomains'
				},
				fields: {
					idDomain: {
						key: true,
						title: 'idDomain',
						create: false,
						edit: false,
						list: true
					},
					name: {
						title: 'Name of the type of domain'
					},
					description: {
						title: 'Description of the domain'
					}
				}
			});

			//Load list from server
			$('#domainsContainer').jtable('load');
		}; //End function domains

		//Function to load a CRUD table for tDataType
		function showDataTypeTable() {
			//Prepare jTable
			$('#dataTypeContainer').jtable({
				title: 'dataTypes',
				paging: true,
				pageSize: 10,
				sorting: true,
				defaultSorting: 'idDataType ASC',
				actions: {
					listAction: '../logic/optionsCRUD.php?action=list_tDataType',
					createAction: '../logic/optionsCRUD.php?action=create_tDataType',
					updateAction: '../logic/optionsCRUD.php?action=update_tDataType',
					deleteAction: '../logic/optionsCRUD.php?action=delete_tDataType'
				},
				fields: {
					idDataType: {
						key: true,
						title: 'idDataType',
						create: false,
						edit: false,
						list: true
					},
					name: {
						title: 'Unique name for this data'
					},
					description: {
						title: 'Description of the type of data'
					}
				}
			});

			//Load list from server
			$('#dataTypeContainer').jtable('load');
		}; //End function dataTypes
		
		//Function to load a CRUD table for tFileType
		function showFileTypeTable() {
			//Prepare jTable
			$('#fileTypeContainer').jtable({
				title: 'Type of files',
				paging: true,
				pageSize: 10,
				sorting: true,
				defaultSorting: 'idFileType ASC',
				actions: {
					listAction: '../logic/optionsCRUD.php?action=list_tFileType',
					createAction: '../logic/optionsCRUD.php?action=create_tFileType',
					updateAction: '../logic/optionsCRUD.php?action=update_tFileType',
					deleteAction: '../logic/optionsCRUD.php?action=delete_tFileType'
				},
				fields: {
					idFileType: {
						key: true,
						title: 'idFileType',
						create: false,
						edit: false,
						list: true
					},
					idDirectory:{
					  title: 'Directory location',
					  options:  '../logic/optionsCRUD.php?action=getDirectories',
					  list: true,
					},
					name: {
						title: 'Name of the file'
					},
					description: {
						title: 'Description of the file'
					},
					searchOn: {
						title: 'Unique part of the file name. (For detection)'
					}
				}
			});

			//Load list from server
			$('#fileTypeContainer').jtable('load');
		}; //End function fileTypes

		//Function to load a CRUD table for tAssays
		function showAssayTypeTable() {
			//Prepare jTable
			$('#assayTypeContainer').jtable({
				title: 'Assay Types',
				paging: true,
				pageSize: 10,
				sorting: true,
				defaultSorting: 'idAssayType ASC',
				actions: {
					listAction: '../logic/optionsCRUD.php?action=list_tAssayType',
					createAction: '../logic/optionsCRUD.php?action=create_tAssayType',
					updateAction: '../logic/optionsCRUD.php?action=update_tAssayType',
					deleteAction: '../logic/optionsCRUD.php?action=delete_tAssayType'
				},
				fields: {
					idAssayType: {
						key: true,
						title: 'idAssayType',
						create: false,
						edit: false,
						list: true
					},
					name: {
						title: 'Name of the array Type'
					},
					description: {
						title: 'Description of array type'
					}
				}
			});

			//Load list from server
			$('#assayTypeContainer').jtable('load');
		}; //End function assayType

		//Function to load a CRUD table for tCompound
		function showCompoundTable() {
			//Prepare jTable
			$('#compoundContainer').jtable({
				title: 'Compounds',
				paging: true,
				pageSize: 10,
				sorting: true,
				defaultSorting: 'idCompound ASC',
				actions: {
					listAction: '../logic/optionsCRUD.php?action=list_tCompound',
					createAction: '../logic/optionsCRUD.php?action=create_tCompound',
					updateAction: '../logic/optionsCRUD.php?action=update_tCompound',
					deleteAction: '../logic/optionsCRUD.php?action=delete_tCompound'
				},
				fields: {
					idCompound: {
						key: true,
						title: 'idCompound',
						create: false,
						edit: false,
						list: true
					},
					name: {
						title: 'Name of the compound'
					},
					casNumber: {
						title: 'CAS number'
					},
					abbreviation: {
						title: 'Abbreviation'
					},
					officialName: {
						title: 'Official name'
					}
				}
			});

			//Load list from server
			$('#compoundContainer').jtable('load');
		}; //End function compound

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