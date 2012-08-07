<?php
/***********************************************
DAVE PHP API
https://github.com/evantahler/PHP-DAVE-API
Evan Tahler | 2011

I hold all the configuration variables for the API

RESERVERD VARIABLES THAT ARE SET BY THIS CONFIG FILE OR IN OTHER PARTS OF THE APP INITILIZATION:
- $PARAMS : To be set with all relevant, sanitized user input to the API
- $CONFIG : Holds configuration options
- $POST_VARIABLES : Collection of names of variables the API should ingest
- $ACTIONS : Hash of API's Actions
- $ERROR : The globabl state variable (100 is the OK state)
- $DBOBJ : The database connection object
- $OUTPUT : The array to be formatted and returned to the user
- $TABLES : The table description object
***********************************************/

$CONFIG = array();

// Note: phpfog may need special cache settings

// working directory
$path = substr(__FILE__,0,(strlen(__FILE__) - strlen("CONFIG.php")));
chdir($path);
unset($path);

// Set the ERROR.  This is used by all steps to ensure that nothing has perviously gone awry, and allows the next step to excecute.  All steps will first check to make sure that ERROR == 100.
$ERROR = 100; 

$CONFIG["APIVersion"] = 0.9;

$CONFIG['SystemTimeZone'] = "America/Los_Angeles";
date_default_timezone_set($CONFIG['SystemTimeZone']);
$CONFIG['DefaultOutputType'] = "JSON" ;  // XML, PHP, VAR, SOAP, LINE, CONSOLE, or JSON
$CONFIG['XML_ROOT_NODE'] = "XML"; // what is the root node of your XML output called?
$CONFIG['RequestLimitPerHour'] = 1000; // limit how many times a specific IP can use the API per hour.  Set it to 0 to have no limit
$CONFIG['CorrectLimitLockPass'] = "Sekret"; // If a user provides the phrase as the param "LimitLockPass", even with a request limit per hour set above, they will not be limited on the requests that they can make.
$CONFIG['RollbackPhrase'] = "Sekret"; // A user can send send the &Rollback param with a request.  This will place the entire request in a mySQL tansaction that will be rolled back uppon completion.  This requset will still get logger however.

//define some things about this API node
$CONFIG['ServerAddress'] = "php-dave-api.phpfogapp.com"; //include the port.  80 is the web's default
$CONFIG['ServerName'] = "DAVE_API_SERVER";

/*********************************************************/
// Files and Folders
$CONFIG['PHP_Path'] = "/usr/bin/php"; // where is the PHP excecutable?
$CONFIG['App_dir'] = "/var/fog/apps/2429/php-dave-api.phpfogapp.com/API/"; // the location of this application

/*********************************************************/
// Log
$CONFIG['Logging'] = true; // log user actions and rate limit?
$CONFIG['LogFolder'] = $CONFIG['App_dir']."log/";
$CONFIG['LogAge'] = 60*60*24; // time to keep log entries in the DB (in seconds)
// If you want to ignore certain Actions, list them here (For logging purposes)
$CONFIG['NoLogActions'] = array();
$CONFIG['NoLogActions'][] = "A_Blocked_Action";
//If you want to ignore certain APIKeys, list them here (For logging purposes)
$CONFIG['NoLogAPIKeys'] = array();
$CONFIG['NoLogAPIKeys'][] = "A_Blocked_APIKey";
$CONFIG['LogTable'] = "log";

/*********************************************************/
// database
$CONFIG["DBType"] = "MYSQL"; // DB types require a 'driver' to be defined within DB/DRIVERS.  Check the MYSQL folder for examples.
$CONFIG['TableConfigFile'] = $CONFIG['App_dir']."DB/SCHEMA.php";  // The DB is periodically read and schema is written here for faster subsequent access
$CONFIG['TableConfigRefreshTime'] = 60; // time in seconds for this application to re-poll mySQL for table layout information.  0 will never poll
$CONFIG['DBLogFile'] = $CONFIG['LogFolder']."SQL.txt"; // comment me out for no logging of mySQL commands

$CONFIG['DB'] = getenv("MYSQL_DB_NAME");  // the name of the database in use
$CONFIG['LOG_DB'] = $CONFIG['DB']; // There can be a seperate DB in use for logging.  The LogTable table is expected to be within this database.
$CONFIG['dbhost'] = getenv("MYSQL_DB_HOST");
$CONFIG['dbuser'] = getenv("MYSQL_USERNAME");
$CONFIG['dbpass'] = getenv("MYSQL_PASSWORD");

/*********************************************************/
// CRON
$CONFIG['CronLogFile'] = $CONFIG['LogFolder']."CRON_LOG.txt";
$CONFIG['MaxLogFileSize'] = 1048576 * 1;  // 1MB

$CONFIG['LogsToCheck'] = array(); // log files that might get big that you want to automatically truncate
$CONFIG['LogsToCheck'][] = $CONFIG['CronLogFile'];
$CONFIG['LogsToCheck'][] = $CONFIG['DBLogFile'];

/*********************************************************/
// CACHE
$CONFIG['CacheType'] = "DB"; // Options are "", "MemCache", "FlatFile", or "DB";
$CONFIG['CacheTime'] = 10; // time to keep a cached value (in seconds)
$CONFIG['CacheTable'] = "cache"; // if using the DB method
$CONFIG['CacheFolder'] = $CONFIG['App_dir']."cache/"; // chmod 777, if using FlatFile mode
$CONFIG['MemCacheHost'] = '127.0.0.1'; // The Server name or IP address of the memcache host, if CacheType = MemCache

/*********************************************************/
// Sessions
$CONFIG['SessionAge'] = 60*60*24; // how long to keep session information in the DB (in seconds)

/*********************************************************/
// Safe Mode: Use this to force the MD5 Checks to occur
// md5($DeveloperID{secret}.$APIKey.$Rand).
$CONFIG['SafeMode'] = true; //can be "true" or "false"

/*********************************************************/
// load object classes.  Assumes each file in the /Objects directory contains object classes
require_once("Objects/_BASE.php");
foreach (glob("Objects/*.php") as $filename)
{
	require_once($filename);
}

/*********************************************************/
// Tests and Specs
$CONFIG['TestRootFolder'] = $CONFIG['App_dir']."../SPEC/";
$CONFIG['TestLogFodler'] = $CONFIG['App_dir']."../SPEC/log/";
$CONFIG['TestLog'] = $CONFIG['TestLogFodler']."test_log.txt";

/*********************************************************/
// Create needed folders
@mkdir($CONFIG['TestLogFodler']);
@mkdir($CONFIG['LogFolder']);
@mkdir($CONFIG['CacheFolder']);

/*********************************************************/
// Special Strings
// In order to ensure input is handled properly, this API required that you use the reserved (definable) special terms below to indicate you mean either an empty string "" or the numeral 0 with input.  HTML GET and POST may treat these values as empty, so the special strings are required.  GET, POST, or COOKIE INPUT OF "0" OR "" WILL BE IGNORED for this reason!
// defined as array(TheTerm, ActualReplaceValue)

$CONFIG['SpecialStrings'] = array();
$CONFIG['SpecialStrings'][] = array('{clear}',"");
$CONFIG['SpecialStrings'][] = array('{CLEAR}',"");
$CONFIG['SpecialStrings'][] = array('%%CLEAR%%',"");
$CONFIG['SpecialStrings'][] = array('%%clear%%',"");
$CONFIG['SpecialStrings'][] = array('{zero}',"0");
$CONFIG['SpecialStrings'][] = array('{ZERO}',"0");
$CONFIG['SpecialStrings'][] = array('%%ZERO%%',"0");
$CONFIG['SpecialStrings'][] = array('%%zero%%',"0");

// move on to DB config from the Driver
require($CONFIG['App_dir']."DB/DRIVERS/".$CONFIG["DBType"]."/init.php");

?>