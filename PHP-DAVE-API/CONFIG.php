<?php

/***********************************************
Evan Tahler
evantahler@gmail.com
2010

I hold all the configuration variables for the API

***********************************************/

// show errors on scrern
ini_set("display_errors","1");
error_reporting (E_ALL ^ E_NOTICE);

// Set the ERROR.  This is used by all steps to ensure that nothing has perviously gone awry, and allows the next step to excecute.  All steps will first check to make sure that ERROR == 100.
$ERROR = 100; 
$systemTimeZone = "America/New_York";
$DefaultOutputType = "XML" ;  // XML, PHP, VAR, SOAP, or JSON
$XML_ROOT_NODE = "XML"; // what is the root node of your XML output called?
$RequestLimitPerHour = 100; // limit how many times a specific IP can use the API per hour.  Set it to 0 to have no limit
$CorrectLimitLockPass = "Sekret"; // If a user provides the phrase as the param "LimitLockPass", even with a request limit per hour set above, they will not be limited on the requests that they can make.

//define some things about this API node
$ServerAddress = $_SERVER["SERVER_ADDR"];
$ServerName = $_SERVER["SERVER_NAME"];

// mySQL database
$DB = "API";  // the name of the database in use
$dbhost = "localhost";
$dbuser = "MrAPI";
$dbpass = "MrAPIPassword";

/*********************************************************/
// Files and Folders
$PHP_Path = "/usr/bin/php/"; // where is the PHP excecutable?
$App_dir = "/var/www/html/API/"; // the location of this application

/*********************************************************/
// CRON
$CronLogFile = "CRON_LOG.txt"; // I'll be placed in the App_Dir
$MaxLogFileSize = 1048576 * 1;  // 1MB

$LogsToCheck = array(); // log files that might get big
$LogsToCheck[] = $App_dir.$CronLogFile;

/*********************************************************/
// CACHE
$CacheType = "DB"; // Options are "", "MemCache", "FlatFile", or "DB";
$CacheTime = 10; // time to keep a cached value (in seconds)
$CacheTable = "CACHE"; // if using the DB method
$CacheFolder = "/var/www/html/API/CACHE/"; // chmod 777, if using FlatFile mode
$MemCacheHost = 'localhost'; // The Server name or IP address of the memcache host, if CacheType = MemCache

/*********************************************************/
// Log
$LogTable = "LOG";
$LogAge = 60*60*24; // time to keep log entries in the DB (in seconds)
// If you want to ignore certain Actions, list them here (For logging purposes)
$NoLogActions = array();
$NoLogActions[] = "A_Blocked_Action";
//If you want to ignore certain APIKeys, list them here (For logging purposes)
$NoLogAPIKeys = array();
$NoLogActions[] = "A_Blocked_APIKey";

/*********************************************************/
// Safe Mode: Use this to force the MD5 Checks to occur
// md5($DeveloperID{secret}.$APIKey.$Rand).
$SafeMode = true; //can be "true" or "false"

/*********************************************************/
// Special Strings
// In order to ensure input is handled properly, this API required that you use the reserved (definable) special terms below to indicate you mean either an empty string "" or the numeral 0 with input.  HTML GET and POST may treat these values as empty, so the special strings are required.  GET, POST, or COOKIE INPUT OF "0" OR "" WILL BE IGNORED for this reason!
// defined as array(TheTerm, ActualReplaceValue)

$SpecialStrings = array();
$SpecialStrings[] = array('{clear}',"");
$SpecialStrings[] = array('{CLEAR}',"");
$SpecialStrings[] = array('%%CLEAR%%',"");
$SpecialStrings[] = array('%%clear%%',"");
$SpecialStrings[] = array('{zero}',"0");
$SpecialStrings[] = array('{ZERO}',"0");
$SpecialStrings[] = array('%%ZERO%%',"0");
$SpecialStrings[] = array('%%zero%%',"0");

/*********************************************************/
// load object classes.  Assumes each file in the /Objects directory contains object classes
foreach (glob("Objects/*.php") as $filename)
{
	require_once($filename);
}

/*********************************************************/
// Actions, defined as "verb",  then "page location", then "Public" or "Private" indicatiing if an APIKey is needed to access the function

$ACTIONS = array();

// default actions
$ACTIONS[] = array('DescribeActions', 'Actions/DescribeActions.php', 'Public');

// some basic actions
$ACTIONS[] = array('GeoCode', 'Actions/Geocode.php', 'Public');
$ACTIONS[] = array('CacheTest', 'Actions/CacheTest.php', 'Public');
$ACTIONS[] = array('DescribeTables', 'Actions/DescribeTables.php', 'Public');

// Demo actions for building a user system
$ACTIONS[] = array('UserAdd', 'Actions/UserAdd.php', 'Public');
$ACTIONS[] = array('UserView', 'Actions/UserView.php', 'Public');
$ACTIONS[] = array('UserEdit', 'Actions/UserEdit.php', 'Public');
$ACTIONS[] = array('UserDelete', 'Actions/UserDelete.php', 'Public');
$ACTIONS[] = array('LogIn', 'Actions/LogIn.php', 'Public');

/*********************************************************/
// Table information
$TableConfigFile = "DB/TABLES.php";
$TableConfigRefreshTime = 60; // time in seconds for this application to re-poll mySQL for table layout information.  0 will never poll
require("DB/TableConfig.php");

/*********************************************************/

// Variables that might not be in the TABLLES.  List any extra parameters your application might need
$POST_VARIABLES = array();

$POST_VARIABLES[] = "Action";
$POST_VARIABLES[] = "APIKey";
$POST_VARIABLES[] = "IP";
$POST_VARIABLES[] = "UpperLimit";
$POST_VARIABLES[] = "LowerLimit";
$POST_VARIABLES[] = "Date";
$POST_VARIABLES[] = "TimeStamp";
$POST_VARIABLES[] = "Rand";
$POST_VARIABLES[] = "Hash";
$POST_VARIABLES[] = "DeveloperID";
$POST_VARIABLES[] = "OutputType";
$POST_VARIABLES[] = "Callback";
$POST_VARIABLES[] = "LimitLockPass";
$POST_VARIABLES[] = "Password";

/*********************************************************/
require('config_cleaner.php');

?>