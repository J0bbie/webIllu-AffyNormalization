<?php
/*
 Author:				Job van Riet + Jordy Coolen + ArrayAnalysis.org (Affy module)
Date of  creation:		10-4-14
Date of modification:	10-4-14
Version:				1.0
Modifications:			Original version
Known bugs:				None known
Function:				This file houses the functions to perform Affymetrix normalizations on samples uploaded to the DIAMONDS DB.
						The actual normalization is done by a slightly altered ArrayAnalysis.org R script for affymetrix normalisation, this script is run as a background deamon.
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