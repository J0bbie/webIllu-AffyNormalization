<?php
/*
Author:					Job van Riet
Date of  creation:		23-2-14
Date of modification:	23-2-14
Version:				1.0
Modifications:			Original version
Known bugs:				None known
Function:				This page houses all the CRUD functionality for the normDB, used by the jTable Jquery plugin to provide dynamic CRUD tables.
*/
?>

<?php

// Include the scripts containing the config variables
// Contains user setting such as the path of the main folder and how to connect to the DB and such
require_once('../logic/config.php');

try
{
	//Open database connection
	$con = mysql_connect(CONFIG_HOST, CONFIG_DATABASEUSER, CONFIG_DATABASEUSERPASSWORD);
	mysql_select_db(CONFIG_DATABASESCHEMA, $con);
	/*
	 ######################
	#### CRUD tStudy ###
	######################
	*/
	
	//Getting records (listAction)
	if($_GET["action"] == "list_tStudy")
	{
		$idStudy = $_POST['idStudy'];
		
		//Get record count
		$result = mysql_query("SELECT COUNT(*) AS RecordCount FROM tStudy WHERE idStudy = $idStudy;");
		$row = mysql_fetch_array($result);
		$recordCount = $row['RecordCount'];
	
		//Get records from database
		$result = mysql_query("SELECT * FROM tStudy WHERE idStudy = $idStudy  ORDER BY " . $_GET["jtSorting"] . " LIMIT " . $_GET["jtStartIndex"] . "," . $_GET["jtPageSize"] . ";");
	
		//Add all records to an array
		$rows = array();
		while($row = mysql_fetch_array($result))
		{
			$rows[] = $row;
		}
	
		//Return result to jTable
		$jTableResult = array();
		$jTableResult['Result'] = "OK";
		$jTableResult['TotalRecordCount'] = $recordCount;
		$jTableResult['Records'] = $rows;
		print json_encode($jTableResult);
	}
	
	//Updating a record (updateAction)
	else if($_GET["action"] == "update_tStudy")
	{
		$idStudy = $_POST['idStudy'];
		$title = $_POST['title'];
		$curator = $_POST['curator'];
		$description = $_POST['description'];
		$source = $_POST['source'];
		$idStudyType = $_POST['idStudyType'];
		$idMainSpecies = $_POST['idMainSpecies'];
		$idAssayType = $_POST['idAssayType'];
		$idDomain = $_POST['idDomain'];
		$idArrayPlatform = $_POST['idArrayPlatform'];
		
		//Update record in database
		$result = mysql_query("UPDATE tStudy 
				SET title = '$title', 
				curator = '$curator',
				description = '$description',
				source = '$source',
				idStudyType = '$idStudyType',
				idAssayType = '$idAssayType',
				idDomain = '$idDomain',
				idMainSpecies = '$idMainSpecies',
				idArrayPlatform = '$idArrayPlatform'	
				WHERE idStudy = $idStudy");
	
		//Return result to jTable
		$jTableResult = array();
		$jTableResult['Result'] = "OK";
		print json_encode($jTableResult);
	}

	/*
	 ######################
	#### CRUD tSamples ###
	######################
	*/
	
	//Getting records (listAction)
	if($_GET["action"] == "list_tSamples")
	{
		$sampleName = $_POST['sampleName'];
		$compoundName = $_POST['compoundName'];
		$sampleType = $_POST['sampleType'];
		$idStudy = $_POST['idStudy'];
		$attrValue = $_POST['attrValue'];
		$attrFilter = $_POST['attrFilter'];
		$dataTypeFilter = $_POST['dataTypeFilter'];
			
		$queryCount = "SELECT DISTINCT COUNT(idSample) AS RecordCount FROM vSamplesWithInfoNames.idSample, name, arrayName, compoundName, casNumber, typeName FROM normdb.vSamplesWithInfoNames JOIN vSamplesWithAttributes ON vSamplesWithInfoNames.idSample = vSamplesWithAttributes.idSample 
				WHERE vSamplesWithInfoNames.idStudy = $idStudy 
				".(isset($sampleName) ? " AND name LIKE '%$sampleName%'" : '')."
				".(isset($compoundName) ? " AND compoundName LIKE '%$compoundName%'" : '')."
				".(isset($sampleType) ? " AND typeName LIKE '%$sampleType%'" : '');
		
		$queryGet = "SELECT DISTINCT vSamplesWithInfoNames.idSample as idSample, name, arrayName, compoundName, casNumber, typeName FROM normdb.vSamplesWithInfoNames JOIN vSamplesWithAttributes ON vSamplesWithInfoNames.idSample = vSamplesWithAttributes.idSample 
				WHERE vSamplesWithInfoNames.idStudy = $idStudy 
				".(isset($sampleName) ? " AND name LIKE '%$sampleName%'" : '')."
				".(isset($compoundName) ? " AND compoundName LIKE '%$compoundName%'" : '')."
				".(isset($sampleType) ? " AND typeName LIKE '%$sampleType%'" : '');
		
		if(isset($attrFilter)){
			if($attrFilter == "L"){
				$queryCount .= " AND idDataType = $dataTypeFilter AND attrValue LIKE '%$attrValue%';";
				$queryGet .= " AND idDataType = $dataTypeFilter AND attrValue LIKE '%$attrValue%'";
			}
			else if($attrFilter == "NL"){
				$queryCount .= " AND idDataType = $dataTypeFilter AND attrValue NOT LIKE '%$attrValue%';";
				$queryGet .= " AND idDataType = $dataTypeFilter AND attrValue NOT LIKE '%$attrValue%'";
			}
			else if($attrFilter == "GT"){
				$queryCount .= " AND idDataType = $dataTypeFilter AND attrValue >= ". (int)$attrValue;
				$queryGet .= " AND idDataType = $dataTypeFilter AND attrValue >= ". (int)$attrValue;
			}
			else if($attrFilter == "LT"){
				$queryCount .= " AND idDataType = $dataTypeFilter AND attrValue <= ". (int)$attrValue;
				$queryGet .= " AND idDataType = $dataTypeFilter AND attrValue <= ". (int)$attrValue;
			}
		}
		else{
			$queryCount .= ";";
		}
		
		//Get record count
		$result = mysql_query($queryCount);
		$row = mysql_fetch_array($result);
		$recordCount = $row['RecordCount'];
	
		//Get records from database
		$result = mysql_query($queryGet." ORDER BY " . $_GET["jtSorting"] . " LIMIT " . $_GET["jtStartIndex"] . "," . $_GET["jtPageSize"] . ";");
			
		//Add all records to an array
		$rows = array();
		while($row = mysql_fetch_array($result))
		{
			$rows[] = $row;
		}

		//Return result to jTable
		$jTableResult = array();
		$jTableResult['Result'] = "OK";
		$jTableResult['TotalRecordCount'] = $recordCount;
		$jTableResult['Records'] = $rows;
		print json_encode($jTableResult);
	}

	
	/*
	######################
	#### CRUD tDomains ###
	######################
	*/
	
	//Getting records (listAction)
	if($_GET["action"] == "list_tDomains")
	{
		$name = $_POST['name'];
		
		//Get record count
		$result = mysql_query("SELECT COUNT(*) AS RecordCount FROM tDomains ".(isset($name) ? "WHERE name LIKE '%$name%'" : '').";");
		$row = mysql_fetch_array($result);
		$recordCount = $row['RecordCount'];

		//Get records from database
		$result = mysql_query("SELECT * FROM tDomains ".(isset($name) ? "WHERE name LIKE '%$name%'" : '')." ORDER BY " . $_GET["jtSorting"] . " LIMIT " . $_GET["jtStartIndex"] . "," . $_GET["jtPageSize"] . ";");
		
		//Add all records to an array
		$rows = array();
		while($row = mysql_fetch_array($result))
		{
		    $rows[] = $row;
		}

		//Return result to jTable
		$jTableResult = array();
		$jTableResult['Result'] = "OK";
		$jTableResult['TotalRecordCount'] = $recordCount;
		$jTableResult['Records'] = $rows;
		print json_encode($jTableResult);
	}
	
	//Creating a new record (createAction)
	else if($_GET["action"] == "create_tDomains")
	{
		$name = $_POST['name'];
		$description = $_POST['description'];
		
		//Insert record into database
		$result = mysql_query("INSERT INTO tDomains(name, description) VALUES( '$name', '$description');");
		
		//Get last inserted record (to return to jTable)
		$result = mysql_query("SELECT * FROM tDomains WHERE idDomain = LAST_INSERT_ID();");
		$row = mysql_fetch_array($result);

		//Return result to jTable
		$jTableResult = array();
		$jTableResult['Result'] = "OK";
		$jTableResult['Record'] = $row;
		print json_encode($jTableResult);
	}
	//Updating a record (updateAction)
	else if($_GET["action"] == "update_tDomains")
	{
		$idDomain = $_POST['idDomain'];
		$name = $_POST['name'];
		$description = $_POST['description'];
		
		//Update record in database
		$result = mysql_query("UPDATE tDomains SET name = '$name', description= $description WHERE idDomain = $idDomain");

		//Return result to jTable
		$jTableResult = array();
		$jTableResult['Result'] = "OK";
		print json_encode($jTableResult);
	}
	
	//Deleting a record (deleteAction)
	else if($_GET["action"] == "delete_tDomains")
	{
		$idDomain = $_POST['idDomain'];
		
		//Delete from database
		$result = mysql_query("DELETE FROM tDomains WHERE idDomain = $idDomain ;");
		
		//Return result to jTable
		$jTableResult = array();
		$jTableResult['Result'] = "OK";
		print json_encode($jTableResult);
	}

	/*
	 ######################
	#### CRUD tSampleType ###
	######################
	*/
	
	//Getting records (listAction)
	if($_GET["action"] == "list_tSampleType")
	{
		$name = $_POST['name'];
		
		//Get record count
		$result = mysql_query("SELECT COUNT(*) AS RecordCount FROM tSampleType ".(isset($name) ? "WHERE name LIKE '%$name%'" : '').";");
		$row = mysql_fetch_array($result);
		$recordCount = $row['RecordCount'];
	
		//Get records from database
		$result = mysql_query("SELECT * FROM tSampleType ".(isset($name) ? "WHERE name LIKE '%$name%'" : '')." ORDER BY " . $_GET["jtSorting"] . " LIMIT " . $_GET["jtStartIndex"] . "," . $_GET["jtPageSize"] . ";");
	
		//Add all records to an array
		$rows = array();
		while($row = mysql_fetch_array($result))
		{
			$rows[] = $row;
		}
	
		//Return result to jTable
		$jTableResult = array();
		$jTableResult['Result'] = "OK";
		$jTableResult['TotalRecordCount'] = $recordCount;
		$jTableResult['Records'] = $rows;
		print json_encode($jTableResult);
	}
	
	//Creating a new record (createAction)
	else if($_GET["action"] == "create_tSampleType")
	{
		$name = $_POST['name'];
		$description = $_POST['description'];
	
		//Insert record into database
		$result = mysql_query("INSERT INTO tSampleType(name, description) VALUES( '$name', '$description');");
	
		//Get last inserted record (to return to jTable)
		$result = mysql_query("SELECT * FROM tSampleType WHERE idSampleType = LAST_INSERT_ID();");
		$row = mysql_fetch_array($result);
	
		//Return result to jTable
		$jTableResult = array();
		$jTableResult['Result'] = "OK";
		$jTableResult['Record'] = $row;
		print json_encode($jTableResult);
	}
	//Updating a record (updateAction)
	else if($_GET["action"] == "update_tSampleType")
	{
		$idSampleType = $_POST['idSampleType'];
		$name = $_POST['name'];
		$description = $_POST['description'];
	
		//Update record in database
		$result = mysql_query("UPDATE tSampleType SET name = '$name', description= $description WHERE idSampleType = $idSampleType");
	
		//Return result to jTable
		$jTableResult = array();
		$jTableResult['Result'] = "OK";
		print json_encode($jTableResult);
	}
	
	//Deleting a record (deleteAction)
	else if($_GET["action"] == "delete_tSampleType")
	{
		$idSampleType = $_POST['idSampleType'];
	
		//Delete from database
		$result = mysql_query("DELETE FROM tSampleType WHERE idSampleType = $idSampleType ;");
	
		//Return result to jTable
		$jTableResult = array();
		$jTableResult['Result'] = "OK";
		print json_encode($jTableResult);
	}
		
	/*
	########################
	#### CRUD tJobStatus ###
	########################
	*/
	
	//Getting records (listAction)
	if($_GET["action"] == "list_tJobStatus")
	{
		$idStudy = $_POST['idStudy'];
		
		//Get record count
		$result = mysql_query("SELECT COUNT(*) AS RecordCount FROM tJobStatus WHERE idStudy = $idStudy;");
		$row = mysql_fetch_array($result);
		$recordCount = $row['RecordCount'];
	
		//Get records from database
		$result = mysql_query("SELECT * FROM tJobStatus WHERE idStudy = $idStudy ORDER BY " . $_GET["jtSorting"] . " LIMIT " . $_GET["jtStartIndex"] . "," . $_GET["jtPageSize"] . ";");
	
		//Add all records to an array
		$rows = array();
		while($row = mysql_fetch_array($result))
		{
			$rows[] = $row;
		}
	
		//Return result to jTable
		$jTableResult = array();
		$jTableResult['Result'] = "OK";
		$jTableResult['TotalRecordCount'] = $recordCount;
		$jTableResult['Records'] = $rows;
		print json_encode($jTableResult);
	}
	
	//Updating a record (updateAction)
	else if($_GET["action"] == "update_tJobStatus")
	{
		$idJob = $_POST['idJob'];
		$name = $_POST['name'];
		$status = $_POST['status'];
		$description = $_POST['description'];
		$statusMessage = $_POST['statusMessage'];
	
		//Update record in database
		$result = mysql_query("UPDATE tJobStatus SET name = '$name', status = '$status', statusMessage = '$statusMessage', description= '$description' WHERE idJob = $idJob");
	
		//Return result to jTable
		$jTableResult = array();
		$jTableResult['Result'] = "OK";
		print json_encode($jTableResult);
	}
	
	//Deleting a record (deleteAction)
	else if($_GET["action"] == "delete_tJobStatus")
	{
		$idJob = $_POST['idJob'];
	
		//Delete from database
		$result = mysql_query("DELETE FROM tJobStatus WHERE idJob = $idJob ;");
	
		//Return result to jTable
		$jTableResult = array();
		$jTableResult['Result'] = "OK";
		print json_encode($jTableResult);
	}
	
	/*
	 ######################
	#### CRUD tDataType ###
	######################
	*/
	
	//Getting records (listAction)
	if($_GET["action"] == "list_tDataType")
	{
		$name = $_POST['name'];
		
		//Get record count
		$result = mysql_query("SELECT COUNT(*) AS RecordCount FROM tDataType ".(isset($name) ? "WHERE name LIKE '%$name%'" : '').";");
		$row = mysql_fetch_array($result);
		$recordCount = $row['RecordCount'];
	
		//Get records from database
		$result = mysql_query("SELECT * FROM tDataType ".(isset($name) ? "WHERE name LIKE '%$name%'" : '')." ORDER BY " . $_GET["jtSorting"] . " LIMIT " . $_GET["jtStartIndex"] . "," . $_GET["jtPageSize"] . ";");
	
		//Add all records to an array
		$rows = array();
		while($row = mysql_fetch_array($result))
		{
			$rows[] = $row;
		}
	
		//Return result to jTable
		$jTableResult = array();
		$jTableResult['Result'] = "OK";
		$jTableResult['TotalRecordCount'] = $recordCount;
		$jTableResult['Records'] = $rows;
		print json_encode($jTableResult);
	}
	
	//Creating a new record (createAction)
	else if($_GET["action"] == "create_tDataType")
	{
		$name = $_POST['name'];
		$description = $_POST['description'];
	
		//Insert record into database
		$result = mysql_query("INSERT INTO tDataType(name, description) VALUES( '$name', '$description');");
	
		//Get last inserted record (to return to jTable)
		$result = mysql_query("SELECT * FROM tDataType WHERE idDataType = LAST_INSERT_ID();");
		$row = mysql_fetch_array($result);
	
		//Return result to jTable
		$jTableResult = array();
		$jTableResult['Result'] = "OK";
		$jTableResult['Record'] = $row;
		print json_encode($jTableResult);
	}
	//Updating a record (updateAction)
	else if($_GET["action"] == "update_tDataType")
	{
		$idDataType = $_POST['idDataType'];
		$name = $_POST['name'];
		$description = $_POST['description'];
	
		//Update record in database
		$result = mysql_query("UPDATE tDataType SET name = '$name', description= $description WHERE idDataType = $idDataType");
	
		//Return result to jTable
		$jTableResult = array();
		$jTableResult['Result'] = "OK";
		print json_encode($jTableResult);
	}
	
	//Deleting a record (deleteAction)
	else if($_GET["action"] == "delete_tDataType")
	{
		$idDataType = $_POST['idDataType'];
	
		//Delete from database
		$result = mysql_query("DELETE FROM tDataType WHERE idDataType = $idDataType ;");
	
		//Return result to jTable
		$jTableResult = array();
		$jTableResult['Result'] = "OK";
		print json_encode($jTableResult);
	}
	/*
	######################
	#### CRUD tStudyType ###
	######################
	*/
	
	//Getting records (listAction)
	if($_GET["action"] == "list_tStudyType")
	{
		$name = $_POST['name'];
		
		//Get record count
		$result = mysql_query("SELECT COUNT(*) AS RecordCount FROM tStudyType ".(isset($name) ? "WHERE name LIKE '%$name%'" : '').";");
		$row = mysql_fetch_array($result);
		$recordCount = $row['RecordCount'];

		//Get records from database
		$result = mysql_query("SELECT * FROM tStudyType ".(isset($name) ? "WHERE name LIKE '%$name%'" : '')." ORDER BY " . $_GET["jtSorting"] . " LIMIT " . $_GET["jtStartIndex"] . "," . $_GET["jtPageSize"] . ";");
		
		//Add all records to an array
		$rows = array();
		while($row = mysql_fetch_array($result))
		{
		    $rows[] = $row;
		}

		//Return result to jTable
		$jTableResult = array();
		$jTableResult['Result'] = "OK";
		$jTableResult['TotalRecordCount'] = $recordCount;
		$jTableResult['Records'] = $rows;
		print json_encode($jTableResult);
	}
	
	//Creating a new record (createAction)
	else if($_GET["action"] == "create_tStudyType")
	{
		$name = $_POST['name'];
		$description = $_POST['description'];
		
		//Insert record into database
		$result = mysql_query("INSERT INTO tStudyType(name, description) VALUES( '$name', '$description');");
		
		//Get last inserted record (to return to jTable)
		$result = mysql_query("SELECT * FROM tStudyType WHERE idStudyType = LAST_INSERT_ID();");
		$row = mysql_fetch_array($result);

		//Return result to jTable
		$jTableResult = array();
		$jTableResult['Result'] = "OK";
		$jTableResult['Record'] = $row;
		print json_encode($jTableResult);
	}
	//Updating a record (updateAction)
	else if($_GET["action"] == "update_tStudyType")
	{
		$idStudyType = $_POST['idStudyType'];
		$name = $_POST['name'];
		$description = $_POST['description'];
		
		//Update record in database
		$result = mysql_query("UPDATE tStudyType SET name = '$name', description= '$description' WHERE idStudyType = $idStudyType");

		//Return result to jTable
		$jTableResult = array();
		$jTableResult['Result'] = "OK";
		print json_encode($jTableResult);
	}
	
	//Deleting a record (deleteAction)
	else if($_GET["action"] == "delete_tStudyType")
	{
		$idStudyType = $_POST['idStudyType'];
		
		//Delete from database
		$result = mysql_query("DELETE FROM tStudyType WHERE idStudyType = $idStudyType ;");
		
		//Return result to jTable
		$jTableResult = array();
		$jTableResult['Result'] = "OK";
		print json_encode($jTableResult);
	}
	
	
	/*
	######################
	#### CRUD tAssayType ###
	######################
	*/
	
	//Getting records (listAction)
	if($_GET["action"] == "list_tAssayType")
	{
		$name = $_POST['name'];
		
		//Get record count
		$result = mysql_query("SELECT COUNT(*) AS RecordCount FROM tAssayType ".(isset($name) ? "WHERE name LIKE '%$name%'" : '').";");
		$row = mysql_fetch_array($result);
		$recordCount = $row['RecordCount'];

		//Get records from database
		$result = mysql_query("SELECT * FROM tAssayType ".(isset($name) ? "WHERE name LIKE '%$name%'" : '')." ORDER BY " . $_GET["jtSorting"] . " LIMIT " . $_GET["jtStartIndex"] . "," . $_GET["jtPageSize"] . ";");
		
		//Add all records to an array
		$rows = array();
		while($row = mysql_fetch_array($result))
		{
		    $rows[] = $row;
		}

		//Return result to jTable
		$jTableResult = array();
		$jTableResult['Result'] = "OK";
		$jTableResult['TotalRecordCount'] = $recordCount;
		$jTableResult['Records'] = $rows;
		print json_encode($jTableResult);
	}
	
	//Creating a new record (createAction)
	else if($_GET["action"] == "create_tAssayType")
	{
		$name = $_POST['name'];
		$description = $_POST['description'];
		
		//Insert record into database
		$result = mysql_query("INSERT INTO tAssayType(name, description) VALUES( '$name', '$description');");
		
		//Get last inserted record (to return to jTable)
		$result = mysql_query("SELECT * FROM tAssayType WHERE idAssayType = LAST_INSERT_ID();");
		$row = mysql_fetch_array($result);

		//Return result to jTable
		$jTableResult = array();
		$jTableResult['Result'] = "OK";
		$jTableResult['Record'] = $row;
		print json_encode($jTableResult);
	}
	//Updating a record (updateAction)
	else if($_GET["action"] == "update_tAssayType")
	{
		$idAssayType = $_POST['idAssayType'];
		$name = $_POST['name'];
		$description = $_POST['description'];
		
		//Update record in database
		$result = mysql_query("UPDATE tAssayType SET name = '$name', description= '$description' WHERE idAssayType = $idAssayType");

		//Return result to jTable
		$jTableResult = array();
		$jTableResult['Result'] = "OK";
		print json_encode($jTableResult);
	}
	
	//Deleting a record (deleteAction)
	else if($_GET["action"] == "delete_tAssayType")
	{
		$idAssayType = $_POST['idAssayType'];
		
		//Delete from database
		$result = mysql_query("DELETE FROM tAssayType WHERE idAssayType = $idAssayType ;");
		
		//Return result to jTable
		$jTableResult = array();
		$jTableResult['Result'] = "OK";
		print json_encode($jTableResult);
	}
	
	/*
	###########################
	#### CRUD tStatistics 	###
	###########################
	*/
	
	//Getting records (listAction)
	if($_GET["action"] == "list_tStatistics")
	{
		$idStatistics = $_POST['idStatistics'];
	
		//Get record count
		$result = mysql_query("SELECT COUNT(*) AS RecordCount FROM tStatistics WHERE idStatistics = $idStatistics;");
		$row = mysql_fetch_array($result);
		$recordCount = $row['RecordCount'];
	
		//Get records from database
		$result = mysql_query("SELECT * FROM tStatistics WHERE idStatistics = $idStatistics ORDER BY " . $_GET["jtSorting"] . " LIMIT " . $_GET["jtStartIndex"] . "," . $_GET["jtPageSize"] . ";");
	
		//Add all records to an array
		$rows = array();
		while($row = mysql_fetch_array($result))
		{
			$rows[] = $row;
		}
	
		//Return result to jTable
		$jTableResult = array();
		$jTableResult['Result'] = "OK";
		$jTableResult['TotalRecordCount'] = $recordCount;
		$jTableResult['Records'] = $rows;
		print json_encode($jTableResult);
	}
	
	//Updating a record (updateAction)
	else if($_GET["action"] == "update_tStatistics")
	{
		$idStatistics = $_POST['idStatistics'];
		$description = $_POST['description'];
		$groupedOn = $_POST['groupedOn'];
	
		//Update record in database
		$result = mysql_query("UPDATE tStatistics SET description = '$description', groupedOn= '$groupedOn' WHERE idStatistics = $idStatistics");
	
		//Return result to jTable
		$jTableResult = array();
		$jTableResult['Result'] = "OK";
		print json_encode($jTableResult);
	}
	
	//Deleting a record (deleteAction)
	else if($_GET["action"] == "delete_tStatistics")
	{
		$idStatistics = $_POST['idStatistics'];
			
		//Delete from database
		
		//Files first
		$result = mysql_query("DELETE FROM tFiles WHERE idStatistics = $idStatistics ;");
		
		//Statistics run next
		$result = mysql_query("DELETE FROM tStatistics WHERE idStatistics = $idStatistics ;");
	
		//Return result to jTable
		$jTableResult = array();
		$jTableResult['Result'] = "OK";
		print json_encode($jTableResult);
	}
	
	/*
	 ######################
	#### CRUD tNormAnalysis ###
	######################
	*/
	
	//Getting records (listAction)
	if($_GET["action"] == "list_tNormAnalysis")
	{
		$idNorm = $_POST['idNormAnalysis'];
	
		//Get record count
		$result = mysql_query("SELECT COUNT(*) AS RecordCount FROM tNormAnalysis WHERE idNormAnalysis = $idNorm;");
		$row = mysql_fetch_array($result);
		$recordCount = $row['RecordCount'];
	
		//Get records from database
		$result = mysql_query("SELECT * FROM tNormAnalysis WHERE idNormAnalysis = $idNorm ORDER BY " . $_GET["jtSorting"] . " LIMIT " . $_GET["jtStartIndex"] . "," . $_GET["jtPageSize"] . ";");
	
		//Add all records to an array
		$rows = array();
		while($row = mysql_fetch_array($result))
		{
			$rows[] = $row;
		}
	
		//Return result to jTable
		$jTableResult = array();
		$jTableResult['Result'] = "OK";
		$jTableResult['TotalRecordCount'] = $recordCount;
		$jTableResult['Records'] = $rows;
		print json_encode($jTableResult);
	}
	
	//Updating a record (updateAction)
	else if($_GET["action"] == "update_tNormAnalysis")
	{
		$idNorm = $_POST['idNormAnalysis'];
		$description = $_POST['description'];
	
		//Update record in database
		$result = mysql_query("UPDATE tNormAnalysis SET description = '$description' WHERE idNormAnalysis = $idNorm");
	
		//Return result to jTable
		$jTableResult = array();
		$jTableResult['Result'] = "OK";
		print json_encode($jTableResult);
	}
	
	/*
	 ######################
	#### CRUD tNormedExpression ###
	######################
	*/
	
	//Getting records (listAction)
	if($_GET["action"] == "list_tNormedExpression")
	{
		$idNorm = $_POST['idNormAnalysis'];
		$geneName = $_POST['geneName'];
		$sampleName = $_POST['sampleName'];
	
		//Get record count
		$result = mysql_query("SELECT COUNT(*) AS RecordCount FROM vExpressionWithInfo WHERE idNormAnalysis = $idNorm ".(isset($geneName) ? "AND geneName LIKE '%$geneName%'" : '')." ".(isset($sampleName) ? "AND sampleName LIKE '%$sampleName%'" : '').";");
		$row = mysql_fetch_array($result);
		$recordCount = $row['RecordCount'];
		
		//Get records from database
		$result = mysql_query("SELECT * FROM vExpressionWithInfo WHERE idNormAnalysis = $idNorm ".(isset($geneName) ? "AND geneName LIKE '%$geneName%'" : '')." ".(isset($sampleName) ? "AND sampleName LIKE '%$sampleName%'" : '')." ORDER BY " . $_GET["jtSorting"] . " LIMIT " . $_GET["jtStartIndex"] . "," . $_GET["jtPageSize"] . ";");
		
		//Add all records to an array
		$rows = array();
		while($row = mysql_fetch_array($result))
		{
			$rows[] = $row;
		}
	
		//Return result to jTable
		$jTableResult = array();
		$jTableResult['Result'] = "OK";
		$jTableResult['TotalRecordCount'] = $recordCount;
		$jTableResult['Records'] = $rows;
		print json_encode($jTableResult);
	}
	
	/*
	######################
	#### CRUD tFileType ###
	######################
	*/
	
	//Getting records (listAction)
	if($_GET["action"] == "list_tFileType")
	{
		
		$name = $_POST['name'];
		
		//Get record count
		$result = mysql_query("SELECT COUNT(*) AS RecordCount FROM tFileType ".(isset($name) ? "WHERE name LIKE '%$name%'" : '').";");
		$row = mysql_fetch_array($result);
		$recordCount = $row['RecordCount'];

		//Get records from database
		$result = mysql_query("SELECT * FROM tFileType ".(isset($name) ? "WHERE name LIKE '%$name%'" : '')." ORDER BY " . $_GET["jtSorting"] . " LIMIT " . $_GET["jtStartIndex"] . "," . $_GET["jtPageSize"] . ";");
		
		//Add all records to an array
		$rows = array();
		while($row = mysql_fetch_array($result))
		{
		    $rows[] = $row;
		}

		//Return result to jTable
		$jTableResult = array();
		$jTableResult['Result'] = "OK";
		$jTableResult['TotalRecordCount'] = $recordCount;
		$jTableResult['Records'] = $rows;
		print json_encode($jTableResult);
	}
	
	//Creating a new record (createAction)
	else if($_GET["action"] == "create_tFileType")
	{
		$name = $_POST['name'];
		$description = $_POST['description'];
		$idDirectory = $_POST['idDirectory'];
		$searchOn = $_POST['searchOn'];
		
		//Insert record into database
		$result = mysql_query("INSERT INTO tFileType(name, description, idDirectory, searchOn) VALUES( '$name', '$description', $idDirectory, '$searchOn');");
		
		//Get last inserted record (to return to jTable)
		$result = mysql_query("SELECT * FROM tFileType WHERE idFileType = LAST_INSERT_ID();");
		$row = mysql_fetch_array($result);

		//Return result to jTable
		$jTableResult = array();
		$jTableResult['Result'] = "OK";
		$jTableResult['Record'] = $row;
		print json_encode($jTableResult);
	}
	//Updating a record (updateAction)
	else if($_GET["action"] == "update_tFileType")
	{
		$idFileType = $_POST['idFileType'];
		$name = $_POST['name'];
		$description = $_POST['description'];
		$idDirectory = $_POST['idDirectory'];
		$searchOn = $_POST['searchOn'];
		
		//Update record in database
		$result = mysql_query("UPDATE tFileType SET name = '$name', description= '$description', idDirectory = $idDirectory, searchOn = '$searchOn' WHERE idFileType = $idFileType");

		//Return result to jTable
		$jTableResult = array();
		$jTableResult['Result'] = "OK";
		print json_encode($jTableResult);
	}
	
	//Deleting a record (deleteAction)
	else if($_GET["action"] == "delete_tFileType")
	{
		$idFileType = $_POST['idFileType'];
		
		//Delete from database
		$result = mysql_query("DELETE FROM tFileType WHERE idFileType = $idFileType ;");
		
		//Return result to jTable
		$jTableResult = array();
		$jTableResult['Result'] = "OK";
		print json_encode($jTableResult);
	}
	
	/*
	#######################
	#### CRUD tCompound ###
	#######################
	*/
	
	//Getting records (listAction)
	if($_GET["action"] == "list_tCompound")
	{
		
		$name = $_POST['name'];
		
		//Get record count
		$result = mysql_query("SELECT COUNT(*) AS RecordCount FROM tCompound ".(isset($name) ? "WHERE name LIKE '%$name%'" : '').";");
		$row = mysql_fetch_array($result);
		$recordCount = $row['RecordCount'];
	
		//Get records from database
		$result = mysql_query("SELECT * FROM tCompound ".(isset($name) ? "WHERE name LIKE '%$name%'" : '')." ORDER BY " . $_GET["jtSorting"] . " LIMIT " . $_GET["jtStartIndex"] . "," . $_GET["jtPageSize"] . ";");
	
		//Add all records to an array
		$rows = array();
		while($row = mysql_fetch_array($result))
		{
			$rows[] = $row;
		}
	
		//Return result to jTable
		$jTableResult = array();
		$jTableResult['Result'] = "OK";
		$jTableResult['TotalRecordCount'] = $recordCount;
		$jTableResult['Records'] = $rows;
		print json_encode($jTableResult);
	}
	
	//Creating a new record (createAction)
	else if($_GET["action"] == "create_tCompound")
	{
		$name = $_POST['name'];
		$casNumber = $_POST['casNumber'];
		$abbreviation = $_POST['abbreviation'];
		$officialName = $_POST['officialName'];
	
		//Insert record into database
		$result = mysql_query("INSERT INTO tCompound(name, casNumber, abbreviation, officialName) VALUES( '$name', '$casNumber', '$abbreviation', '$officialName' );");
	
		//Get last inserted record (to return to jTable)
		$result = mysql_query("SELECT * FROM tCompound WHERE idCompound = LAST_INSERT_ID();");
		$row = mysql_fetch_array($result);
	
		//Return result to jTable
		$jTableResult = array();
		$jTableResult['Result'] = "OK";
		$jTableResult['Record'] = $row;
		print json_encode($jTableResult);
	}
	//Updating a record (updateAction)
	else if($_GET["action"] == "update_tCompound")
	{
		$idCompound = $_POST['idCompound'];
		$name = $_POST['name'];
		$casNumber = $_POST['casNumber'];
		$abbreviation = $_POST['abbreviation'];
		$officialName = $_POST['officialName'];
	
		//Update record in database
		$result = mysql_query("UPDATE tCompound SET name = '$name', casNumber= '$casNumber', abbreviation = '$abbreviation', officialName= '$officialName'  WHERE idCompound = $idCompound");
	
		//Return result to jTable
		$jTableResult = array();
		$jTableResult['Result'] = "OK";
		print json_encode($jTableResult);
	}
	
	//Deleting a record (deleteAction)
	else if($_GET["action"] == "delete_tCompound")
	{

		//Delete from database
		$result = mysql_query("DELETE FROM tCompound WHERE idCompound = " . $_POST['idCompound'] . ";");
	
		//Return result to jTable
		$jTableResult = array();
		$jTableResult['Result'] = "OK";
		print json_encode($jTableResult);
	}
	
	/*
	####################
	#### CRUD tFiles ###
	####################
	*/
		
	//Getting records (listAction)
	if($_GET["action"] == "list_tFilesStat")
	{
		$idStat = $_POST['idStat'];
	
		//Get record count
		$result = mysql_query("SELECT COUNT(*) AS RecordCount FROM vFilesWithInfo WHERE idStatistics = $idStat;");
		$row = mysql_fetch_array($result);
		$recordCount = $row['RecordCount'];
	
		//Get records from database
		$result = mysql_query("SELECT * FROM vFilesWithInfo WHERE idStatistics = $idStat ORDER BY " . $_GET["jtSorting"] . " LIMIT " . $_GET["jtStartIndex"] . "," . $_GET["jtPageSize"] . ";");
	
		//Add all records to an array
		$rows = array();
		while($row = mysql_fetch_array($result))
		{
			$rows[] = $row;
		}
	
		//Return result to jTable
		$jTableResult = array();
		$jTableResult['Result'] = "OK";
		$jTableResult['TotalRecordCount'] = $recordCount;
		$jTableResult['Records'] = $rows;
		print json_encode($jTableResult);
	}
	
	//Getting records (listAction)
	if($_GET["action"] == "list_tFilesNorm")
	{
		$idNorm = $_POST['idNormAnalysis'];
	
		//Get record count
		$result = mysql_query("SELECT COUNT(*) AS RecordCount FROM vFilesWithInfo WHERE idNorm = $idNorm;");
		$row = mysql_fetch_array($result);
		$recordCount = $row['RecordCount'];
	
		//Get records from database
		$result = mysql_query("SELECT * FROM vFilesWithInfo WHERE idNorm = $idNorm ORDER BY " . $_GET["jtSorting"] . " LIMIT " . $_GET["jtStartIndex"] . "," . $_GET["jtPageSize"] . ";");
	
		//Add all records to an array
		$rows = array();
		while($row = mysql_fetch_array($result))
		{
			$rows[] = $row;
		}
	
		//Return result to jTable
		$jTableResult = array();
		$jTableResult['Result'] = "OK";
		$jTableResult['TotalRecordCount'] = $recordCount;
		$jTableResult['Records'] = $rows;
		print json_encode($jTableResult);
	}
	
	//Getting records (listAction)
	if($_GET["action"] == "list_tFiles")
	{
		$idStudy = $_POST['idStudy'];
		$fileName = $_POST['fileName'];

		//Get record count
		$result = mysql_query("SELECT COUNT(*) AS RecordCount FROM vFilesWithInfo WHERE idStudy = $idStudy;");
		$row = mysql_fetch_array($result);
		$recordCount = $row['RecordCount'];

		//Get records from database
		$result = mysql_query("SELECT * FROM vFilesWithInfo WHERE idStudy = $idStudy ".(isset($fileName) ? "AND fileName LIKE '%$fileName%'" : '')." ORDER BY " . $_GET["jtSorting"] . " LIMIT " . $_GET["jtStartIndex"] . "," . $_GET["jtPageSize"] . ";");
		
		//Add all records to an array
		$rows = array();
		while($row = mysql_fetch_array($result))
		{
		    $rows[] = $row;
		}
		
		//Return result to jTable
		$jTableResult = array();
		$jTableResult['Result'] = "OK";
		$jTableResult['TotalRecordCount'] = $recordCount;
		$jTableResult['Records'] = $rows;
		print json_encode($jTableResult);
	}
	
	//Creating a new record (createAction)
	else if($_GET["action"] == "create_tFiles")
	{
		$idStudy = $_POST['idStudy'];
		$idFileType = $_POST['idFileType'];
		$fileName = $_POST['fileName'];
		
		//Insert record into database
		$result = mysql_query("INSERT INTO tFiles(idStudy, idFileType, fileName) VALUES( $idStudy, $idFileType, '$fileName');");
		
		//Get last inserted record (to return to jTable)
		$result = mysql_query("SELECT idStudy FROM tFiles WHERE idFile = LAST_INSERT_ID();");
		$row = mysql_fetch_array($result);

		//Return result to jTable
		$jTableResult = array();
		$jTableResult['Result'] = "OK";
		$jTableResult['Record'] = $row;
		print json_encode($jTableResult);
	}
	//Updating a record (updateAction)
	else if($_GET["action"] == "update_tFiles")
	{
		$idFile = $_POST['idFile'];
		$idFileType = $_POST['idFileType'];
		$fileName = $_POST['fileName'];
		
		//Get the old filepath of the file
		$result = mysql_query("SELECT DISTINCT CONCAT(folderName, '/',fileName)  as filePath, tStudy.idStudy, tStudy.title FROM vFilesWithInfo JOIN tStudy on vFilesWithInfo.idStudy = tStudy.idStudy WHERE idFile = $idFile;");
		while($row = mysql_fetch_array($result)) {
			$oldFilePath = $row['filePath'];
			$idStudy = $row['idStudy'];
			$title = $row['title'];
		}
		
		$dataFolder = CONFIG_MAINFOLDER."/data/";
		$studyMap = $idStudy."_".$title;
		//Unique title of the main folder
		$mainFolder = $dataFolder.$studyMap;
		
		$oldFilePath = $mainFolder."/".$oldFilePath;
		
		//Move the file to the new filepath
		$result = mysql_query("SELECT * FROM normdb.vFileTypesWithInfo WHERE idFileType = $idFileType");
		while($row = mysql_fetch_array($result)) {
			$newFolder = $row['folderName'];
		}
		//Set the new filePath
		$newFilePath = $mainFolder."/".$newFolder."/".$fileName;
		//Move file
		rename($oldFilePath,$newFilePath);
		
		//Update record in database
		$result = mysql_query("UPDATE vFilesWithInfo SET idFileType= $idFileType, fileName = $fileName WHERE idFile = $idFile");

		//Return result to jTable
		$jTableResult = array();
		$jTableResult['Result'] = "OK";
		print json_encode($jTableResult);
	}
	
	//Deleting a record (deleteAction)
	else if($_GET["action"] == "delete_tFiles")
	{
		$idStudy = $_POST['idStudy'];
		
		//Delete the file from the server
		$result = mysql_query("SELECT CONCAT(folderName, '/',fileName)  as filePath, idStudy FROM vFilesWithInfo WHERE idFile = " . $_POST['idFile'] . ";");
				
		while($row = mysql_fetch_array($result)) {
			$file = $row['filePath'];
			$idStudy = $row['idStudy'];
		}
		
		$result = mysql_query("SELECT title FROM tStudy WHERE idStudy = ".$idStudy);
				
		while ($row = mysql_fetch_array($result)) {
			$title = $row['title'];
		}
		
		$dataFolder = CONFIG_MAINFOLDER."/data/";
		$studyMap = $idStudy."_".$title;
		//Unique title of the main folder
		$mainFolder = $dataFolder.$studyMap;
		
		$filePath = $mainFolder."/".$file;
		
		unlink($filePath);
		
		//Debug: Check filepath by saving to DB since echo does not work -_-
		//$result = mysql_query("INSERT INTO vFilesWithInfo(idStudy, idfileType, fileName) VALUES( 1, 1, '$filePath');");
		
		//Delete from database
		$result = mysql_query("DELETE FROM tFiles WHERE idFile = " . $_POST['idFile'] . ";");
		
		//Return result to jTable
		$jTableResult = array();
		$jTableResult['Result'] = "OK";
		print json_encode($jTableResult);
	}
	
	/*
	#################################
	###	Additional functions ########
	#################################
	*/
	
	//Get a dropdown menu of the possible fileTypes
	if($_GET["action"] == "getFileTypes") {
		$result = mysql_query("SELECT * FROM tFileType ORDER BY idFileType ASC;");
		$rows = array();
		while ($row = mysql_fetch_array($result)) {
			$eil = array();
			$eil["DisplayText"] = $row['name'];
			$eil["Value"] = $row['idFileType'];
			$rows[] = $eil;
		}
		$jTableResult = array();
		$jTableResult['Result'] = "OK";
		$jTableResult['Options'] = $rows;  
		print json_encode($jTableResult);    
	}
	
	//Get a dropdown menu of the possible directories
	if($_GET["action"] == "getDirectories") {
		$result = mysql_query("SELECT * FROM tDirectory ORDER BY idDirectory ASC;");
		$rows = array();
		while ($row = mysql_fetch_array($result)) {
			$eil = array();
			$eil["DisplayText"] = $row['folderName'];
			$eil["Value"] = $row['idDirectory'];
			$rows[] = $eil;
		}
		$jTableResult = array();
		$jTableResult['Result'] = "OK";
		$jTableResult['Options'] = $rows;  
		print json_encode($jTableResult);    
	}
	
	//Get a dropdown menu of the possible directories
	if($_GET["action"] == "getAssayTypes") {
		$result = mysql_query("SELECT * FROM tAssayType ORDER BY idAssayType ASC;");
		$rows = array();
		while ($row = mysql_fetch_array($result)) {
			$eil = array();
			$eil["DisplayText"] = $row['name'];
			$eil["Value"] = $row['idAssayType'];
			$rows[] = $eil;
		}
		$jTableResult = array();
		$jTableResult['Result'] = "OK";
		$jTableResult['Options'] = $rows;
		print json_encode($jTableResult);
	}
	
	//Get a dropdown menu of the possible directories
	if($_GET["action"] == "getDomainTypes") {
		$result = mysql_query("SELECT * FROM tDomains ORDER BY idDomain ASC;");
		$rows = array();
		while ($row = mysql_fetch_array($result)) {
			$eil = array();
			$eil["DisplayText"] = $row['name'];
			$eil["Value"] = $row['idDomain'];
			$rows[] = $eil;
		}
		$jTableResult = array();
		$jTableResult['Result'] = "OK";
		$jTableResult['Options'] = $rows;
		print json_encode($jTableResult);
	}
	
	//Get a dropdown menu of the possible directories
	if($_GET["action"] == "getSpecies") {
		$result = mysql_query("SELECT * FROM tSpecies ORDER BY idSpecies ASC;");
		$rows = array();
		while ($row = mysql_fetch_array($result)) {
			$eil = array();
			$eil["DisplayText"] = $row['genericName'];
			$eil["Value"] = $row['idSpecies'];
			$rows[] = $eil;
		}
		$jTableResult = array();
		$jTableResult['Result'] = "OK";
		$jTableResult['Options'] = $rows;
		print json_encode($jTableResult);
	}
	
	//Get a dropdown menu of the possible arrays
	if($_GET["action"] == "getArrays") {
		$result = mysql_query("SELECT * FROM tArrayPlatform ORDER BY idArrayPlatform ASC;");
		$rows = array();
		while ($row = mysql_fetch_array($result)) {
			$eil = array();
			$eil["DisplayText"] = $row['name'];
			$eil["Value"] = $row['idArrayPlatform'];
			$rows[] = $eil;
		}
		$jTableResult = array();
		$jTableResult['Result'] = "OK";
		$jTableResult['Options'] = $rows;
		print json_encode($jTableResult);
	}
	
	//Get a dropdown menu of the possible directories
	if($_GET["action"] == "getStudyTypes") {
		$result = mysql_query("SELECT * FROM tStudyType ORDER BY idStudyType ASC;");
		$rows = array();
		while ($row = mysql_fetch_array($result)) {
			$eil = array();
			$eil["DisplayText"] = $row['name'];
			$eil["Value"] = $row['idStudyType'];
			$rows[] = $eil;
		}
		$jTableResult = array();
		$jTableResult['Result'] = "OK";
		$jTableResult['Options'] = $rows;
		print json_encode($jTableResult);
	}
	
	//Close database connection
	mysql_close($con);

}
catch(Exception $ex)
{
    //Return error message
	$jTableResult = array();
	$jTableResult['Result'] = "ERROR";
	$jTableResult['Message'] = $ex->getMessage();
	print json_encode($jTableResult);
}
	
?>