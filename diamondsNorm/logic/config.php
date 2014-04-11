<?php
///////////////////////////////////////////////////////////////////
// 	Change the variables to serve your server configuration		///
///////////////////////////////////////////////////////////////////

// Define the connection to the DB

// Server of MySQL database
define("CONFIG_HOST", "<Ip address/name of server hosting the MySQL database>");	//Example: 127.0.0.1

// Schema responsible for storing the data
define("CONFIG_DATABASESCHEMA", "<Name of schema for created database (normdb)>");	//Example: normdb

// MySQL user with permissions for the schema
define("CONFIG_DATABASEUSER", "<Name user for accessing schema>");	//Example: dbUser

// MySQL user password with permissions for the schema
define("CONFIG_DATABASEUSERPASSWORD", "<Password of user for accessing schema>");	//Example: dbPass

// Folder where the application has been saved (In the original folder structure, GUI, logic, R, css etc)
define("CONFIG_MAINFOLDER", "<Full path to directory of application>");	//Example: /var/www/illuNorm/

///////////////////////////////////////////////////////////////////
// 							Debug options						///
///////////////////////////////////////////////////////////////////

// Should PHP produce errors (could show potentially vulnerable data)
define("CONFIG_ERRORREPORTING", TRUE);	//TRUE or FALSE

// Should the normalization/statistics pipeline be run or simply print the exec statement and not run the actual pipelines
define("CONFIG_RUNPIPELINES", FALSE);	//TRUE or FALSE, TRUE runs the pipelines, FALSE prints the exec commands

// Should the normalized expressions be saved to the DB or not
define("CONFIG_SAVENORMEDEXPRESSIONS", FALSE);	//TRUE or FALSE, TRUE saves the normalized expressions from the pipeline, FALSE does not save these

?>

