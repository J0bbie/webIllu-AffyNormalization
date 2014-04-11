<?php
/*
Author:					Job van Riet
Date of  creation:		11-2-14
Date of modification:	11-2-14
Version:				1.0
Modifications:			Original version
Known bugs:				None known
Function:				This file contains the functions to connect to the DIAMONDS database.
						This script could be included into a page which needs these functionality.
*/

//Include the scripts containing the config variables
require_once('../logic/config.php');

// Show PHP errors if config has this enabled
 if(CONFIG_ERRORREPORTING){
	error_reporting(E_ALL);
	ini_set('display_errors', '1');
 }

function makeConnectionToDIAMONDS(){
	$host = CONFIG_HOST;
	$databaseSchema = CONFIG_DATABASESCHEMA;
	$username = CONFIG_DATABASEUSER;
	$password = CONFIG_DATABASEUSERPASSWORD;
	$tryConnect = mysqli_connect($host, $username, $password, $databaseSchema);

	// Check connection
	if (mysqli_connect_errno()){
	  echo "<p><b>Failed to connect to MySQL on $host.$databaseSchema</b></p>" . mysqli_connect_error();
	}
	return $tryConnect;
}

//Close the mySQL connection to the DIAMONDS DB (manually, it also does this when the object is destroyed/unset)
function closeConnectionDB($connection) {
	mysqli_close($connection);
}
	
//##########################################################
//########## Functions for inserting data into DB #################
//##########################################################
	
//Tries to prepare an SQL statement, if this fails. Show the error and stop.
function prepareSQLStatement($connection, $query){
	if (!($sqlPrep = $connection->prepare($query))) {
		echo "Prepare of the statement <b> $query </b> failed: (" . $connection->errno . ") " . $connection->error;
	}
	else{
		return $sqlPrep;
	}
}

//Tries to execute the prepared SQL statement, this must be a statement with the parameters already bound and prepared.
function executeSQLStatementGetID($connection, $sqlStatement){
	//Execute the prepared SQL
	if (!$sqlStatement->execute()) {
		echo "Execute of SQL Statement failed: $sqlStatement->error";
		$connection->rollback();
	}
	//Return the ID of last inserted row
	return mysqli_insert_id($connection);
}

//Does not return an id after insert
function executeSQLStatementGetIDNonID($connection, $sqlStatement){
	//Execute the prepared SQL
	if (!$sqlStatement->execute()) {
		echo "Execute of SQL Statement failed: $sqlStatement->error";
		$connection->rollback();
	}
}

//Get studyInfo for a single Study, also translates the foreign ID to names
function getStudyInfo($connection, $idStudy){
	$query = "SELECT * FROM vStudyWithTypeNames WHERE idStudy =".$idStudy;
	return fetchRowFromDBOnID($connection, $query, $idStudy, "study");
}

//Get all the dataTypes
function getDataType($connection, $idDataType){
	$query = "SELECT * FROM tDataType WHERE idDataType =".$idDataType;
	return fetchRowFromDBOnID($connection, $query, $idDataType, "DataType");
}

function fetchRowFromDBOnID($connection, $query, $id, $nameTable){
	if ($result =  mysqli_query($connection, $query)) {
		while ($row = mysqli_fetch_assoc($result)) {
			return $row;
		}
	}
	else{
		echo "<p>There is no info for this id from $nameTable: $id </p>";
	}
}


//Insert a new study
function insertNewStudy($parameters) {
	$ownConnection = false;
	//Make a connection to the DB if none is given.
	if(isset($parameters['connection'])){
			$connection = $parameters['connection'];
	}
	else{
		$connection = makeConnectionToDIAMONDS();
		$ownConnection = true;
	}

	//Get the parameters
	$title = $parameters['studyTitle'];
	$curator = $parameters['studyCurator'];
	$description = $parameters['studyDescription'];
	$source = $parameters['studySource'];
	$studyType = $parameters['studyType'];
	$mainSpecies = $parameters['species'];
	$assayType = $parameters['assayType'];
	$domainType = $parameters['domainType'];
	
	//Prepare the SQL statement
	$sqlStatement = "INSERT INTO tStudy (title, curator, description, source, idStudyType, idMainSpecies, idAssayType, idDomain)
	VALUES (?,?,?,?,?,?,?,?)";
	
	$sqlQuery = prepareSQLStatement($connection, $sqlStatement);
	
	//Bind the variables to the SQL parameters
	$sqlQuery->bind_param('ssssdddd',
	$title,
	$curator, 
	$description,
	$source,
	$studyType,
	$mainSpecies,
	$assayType,
	$domainType
	);

	//Execute the SQL script, get the ID of the inserted study back
	$id = executeSQLStatementGetID($connection, $sqlQuery);
	
	//If there have been no errors, commit the database
	$connection->commit();
	if($ownConnection == true){
		closeConnectionDB($connection);
	}
	
	return $id;
}

//Insert a new compound
function getCreateCompound($parameters){
	$ownConnection = false;
	//Make a connection to the DB if none is given.
	if(isset($parameters['connection'])){
			$connection = $parameters['connection'];
	}
	else{
		$connection = makeConnectionToDIAMONDS();
		$ownConnection = true;
	}
	
	//Create variables to allow for non-required fields to be empty when creating the compound
	$compoundName = ''; 
	$compoundCAS = ''; 
	$compoundAbbr ='';
	$compoundOfficialName = '';
	$compoundSynonyms = '';
	
	if(isset($parameters['name'])){
		$compoundName = $parameters['name'];
	}else{ echo "Did not specify a compoundName to search a compound!";}
	
	if(isset($parameters['casNumber'])){
		$compoundCAS = $parameters['casNumber'];
	}else{ echo "Did not specify a compoundCAS to search a compound!";}
	
	if(isset($parameters['abbreviation'])){
		$compoundAbbr = $parameters['abbreviation'];
	}
	
	if(isset($parameters['officialName'])){
		$compoundOfficialName = $parameters['officialName'];
	}
	//If synonyms are submitted, insert them into tcompoundsynonyms if not already present
	if(isset($parameters['synonyms'])){
		$compoundSynonyms = $parameters['synonyms'];
	}

	//Check if compound already exist
	$query = "SELECT idCompound FROM tCompound WHERE casNumber = '$compoundCAS'";
	if ($result =  mysqli_query($connection, $query)) {
		while ($row = mysqli_fetch_assoc($result)) {
			return $row['idCompound'];
		}
	}
	
	//Create compound if it does not already exist
	$sqlStatement  = "INSERT INTO tCompound (name, casNumber, abbreviation, officialName)
	VALUES (?,?,?,?)";
	
	$sqlQuery = prepareSQLStatement($connection, $sqlStatement);
	
	//Bind the variables to the SQL parameters
	$sqlQuery->bind_param('ssss',
	$compoundName,
	$compoundCAS,
	$compoundAbbr,
	$compoundOfficialName
	);
	
	$id = executeSQLStatementGetID($connection, $sqlQuery);
	//Also add the synonyms to this compound if any were given, else just return the id
	if($compoundSynonyms != ''){
		foreach(explode(',',$compoundSynonyms) as $compoundSyn){
			$sqlStatement  = "INSERT INTO tCompoundsynonyms (idCompound, synonym)
		VALUES (?,?)";
		
			$sqlQuery = prepareSQLStatement($connection, $sqlStatement);
			
			//Bind the variables to the SQL parameters
			$sqlQuery->bind_param('ds',
			$id,
			$compoundSyn
			);
			
			executeSQLStatementGetIDNonID($connection, $sqlQuery);
		}
	}
	
	$connection->commit();
	
	if($ownConnection == true){
		closeConnectionDB($connection);
	}
	return $id;
	
}


//If a sample exists in the DB, retrieve this, else create the sample.  If overwrite is true, it deletes the old record and makes a new one.
function getCreateSample($parameters){
	$ownConnection = false;
	$overwrite = false;
	//Make a connection to the DB if none is given.
	if(isset($parameters['connection'])){
			$connection = $parameters['connection'];
	}
	else{
		$connection = makeConnectionToDIAMONDS();
		$ownConnection = true;
	}
	
	//Check if all the required fields are given.
	if(isset($parameters['name'])){
		$sampleName = $parameters['name'];
	}else{ exit( "Cannot create sample, did not provide a sample name!" );}
	
	if(isset($parameters['idStudy'])){
		$idStudy = $parameters['idStudy'];
	}else{ exit( "Cannot create sample, did not provide an idStudy!" );}
	
	if(isset($parameters['idSampleType'])){
		$idSampleType = $parameters['idSampleType'];
	}else{ exit( "Cannot create sample, did not provide an idSampleType!" );}
	
	if(isset($parameters['idCompound'])){
		$idCompound = $parameters['idCompound'];
	}else{ exit( "Cannot create sample, did not provide an idCompound!" );}
	
	//Check whether an overwrite options has been given.
	if(isset($parameters['overwrite'])){
		if($parameters['overwrite'] == true){
			$overwrite = true;
		}
	}
	
	//Get the id of a sample, if not exist -> create the sample and return that id.
	//If overwrite is false, the sample id will be returned or else the old sample will be deleted and a new sample will be created.
	$query = "SELECT idSample FROM tSamples WHERE idStudy = $idStudy AND name = '$sampleName' AND idCompound = '$idCompound'";
	if ($result =  mysqli_query($connection, $query)) {
		while ($row = mysqli_fetch_assoc($result)) {
			if($overwrite == false){
				return $row['idSample'];
			}else{
				$query = "DELETE FROM tSamples WHERE idSample = ".$row['idSample'];
				$connection->query($query);
			}
		}
	}
	//Create sample if it does not already exist
	$sqlStatement  = "INSERT INTO tSamples (idStudy, idSampleType, name, idCompound)
	VALUES (?,?,?,?)";
			
	$sqlQuery = prepareSQLStatement($connection, $sqlStatement);
	
	
	//Bind the variables to the SQL parameters
	$sqlQuery->bind_param('ddsd',
	$idStudy,
	$idSampleType,
	$sampleName,
	$idCompound
	);
	
	$id = executeSQLStatementGetID($connection, $sqlQuery);
	if($ownConnection == true){
		closeConnectionDB($connection);
	}
	return $id;
}

function getIDSampleByName($connection, $idStudy, $sampleName){
	$query = ("SELECT idSample FROM tSamples WHERE name = '$sampleName' and idStudy = '$idStudy'");
	
	if ($result =  mysqli_query($connection, $query)) {
		while ($row = mysqli_fetch_assoc($result)) {
			return $row['idSample'];
		}
	}
}

//Return an array of sampleTypes [Id+name]
function getCreateSampleTypeID($parameters){
	$idSampleType = '0';
	$sampleTypeName = '';
	
	$ownConnection = false;
	//Make a connection to the DB if none is given.
	if(isset($parameters['connection'])){
			$connection = $parameters['connection'];
	}
	else{
		$connection = makeConnectionToDIAMONDS();
		$ownConnection = true;
	}
	
	//Check if all the required fields are given.
	if(isset($parameters['name'])){
		$sampleTypeName = $parameters['name'];
	}
	
	if(isset($parameters['idSampleType'])){
		$idSampleType = $parameters['idSampleType'];
	}
	
	//Check if sampleType already exist, if yes return that id
	$query = "SELECT idSampleType FROM tSampleType WHERE idSampleType = ".$idSampleType." OR name = '$sampleTypeName'";
	
	$id = '';
	
	if ($result =  mysqli_query($connection, $query)) {
		while ($row = mysqli_fetch_assoc($result)) {
			$id = $row['idSampleType'];
		}
	}
	
	//Create the sampleType if it does not yet exist
	if($id == ''){
	
		$sqlStatement  = "INSERT INTO tSampleType (name) VALUES(?)";
			
		$sqlQuery = prepareSQLStatement($connection, $sqlStatement);
		
		//Bind the variables to the SQL parameters
		$sqlQuery->bind_param('s',
		$sampleTypeName
		);
		
		$id = executeSQLStatementGetID($connection, $sqlQuery);
		$connection->commit();
		
		echo "<p><font color=orange>SampleType: ".$sampleTypeName. " created in DIAMONDS DB</font></p>";
	}
	
	if($ownConnection == true){
		closeConnectionDB($connection);
	}
	
	return $id;
	
}
function insertSampleAttribute($connection, $idSample, $idDataType, $value){
		$sqlStatement  = "INSERT INTO tAttributes (idSample, idDataType, value)
		VALUES (?,?,?)";
		
		$sqlQuery = prepareSQLStatement($connection, $sqlStatement);
		
		//Bind the variables to the SQL parameters
		$sqlQuery->bind_param('dds',
		$idSample,
		$idDataType,
		$value
		);
		
		executeSQLStatementGetIDNonID($connection, $sqlQuery);
}

//Delete samples based on GET request from form.
function deleteSamplesFromForm($GET, $idStudy){
	//Make connection
	$connection = makeConnectionToDIAMONDS();
	//Get the idStudy form the GET request
	if(!isset($idStudy)){
		die("<font color=red><p>idStudy was not given when deleting the samples!</p></font>");
	}
	
	//Check if the option to delete all the samples has been checked
	if(isset($GET['deleteAllSamples'])){
		if($GET['deleteAllSamples'] == 1){
			$query = "DELETE FROM tSamples WHERE idStudy = $idStudy";
			$connection->query($query);
		}
	}else{
		//Delete each individual sample
		foreach(explode(',',$GET['selectedSamples']) as $idSample){
			$query = "DELETE FROM tSamples WHERE idStudy = $idStudy AND idSample = $idSample";
			$connection->query($query);
		}
	}
	
	//Commit database changes
	$connection->commit();
	closeConnectionDB($connection);
}
?>