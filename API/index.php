<?php
/***********************************************
DAVE PHP API
https://github.com/evantahler/PHP-DAVE-API
Evan Tahler | 2011

This is the main page that does all the work

***********************************************/

// Init Steps
/////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////

$parts = explode("/",__FILE__);
$ThisFile = $parts[count($parts) - 1];
chdir(substr(__FILE__,0,(strlen(__FILE__) - strlen($ThisFile))));
require_once("LoadEnv.php"); unset($parts); unset($ThisFile);

// don't cache these API pages
if (!headers_sent()) { header("Cache-Control: no-cache, must-revalidate"); }

// Start the timer
require("helper_functions/microtime_float.php");
$ComputationStartTime = microtime_float();

// Start the Output
$OUTPUT = array();
$OUTPUT["APIVersion"] = $CONFIG["APIVersion"];

// Get IP (if not provided)
if (empty($PARAMS["IP"]) || $PARAMS["IP"] == "")
{
	if (getenv(HTTP_X_FORWARDED_FOR)) {							
   		$IPList = getenv(HTTP_X_FORWARDED_FOR); 
   		$IPArray = explode(",", $IPList);
   		$IP = trim($IPArray[count($IPArray)-1]);
	} else { 
    	$IP = getenv(REMOTE_ADDR);
	}
	if ($IP == ""){
		$IP = $_SERVER["REMOTE_ADDR"];
	}
}
else {$IP = $PARAMS["IP"];}

// check if this user has made too many requests this hour
if ($CONFIG['Logging'] == true && $CONFIG['RequestLimitPerHour'] > 0)
{
	if ($CONFIG['CorrectLimitLockPass'] != $PARAMS["LimitLockPass"])
	{ 
		$_api_requests_so_far = _GetAPIRequestsCount();
		if (!is_int($_api_requests_so_far)){ $OUTPUT['ERROR'] = $_api_requests_so_far; require('Output.php'); exit;}
		$OUTPUT['APIRequestsRemaining'] = $CONFIG['RequestLimitPerHour'] - $_api_requests_so_far;
		if ($OUTPUT['APIRequestsRemaining'] <= 0)
		{
			$DBOBJ->close();
			$OUTPUT['ERROR'] = "You have exceeded your allotted ".$CONFIG['RequestLimitPerHour']." requests this hour.";
			require('Output.php');
			exit;
		}
	}
}

// start transaction for connection if needed
if (isset($PARAMS['Rollback']))
	{
	if ($PARAMS['Rollback'] == $CONFIG['RollbackPhrase'])
	{
		_StartTransaction();
	}
	else
	{
		$Action = "HALTED";
		$ERROR = "That is not the correct RollbackPhrase";
	}
}

// functional Steps
/////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////

$ActionPreformed = 0;
$_ActionCounter = 0;

// check for restful requests
if(empty($PARAMS["Action"]) && count(explode(".",$_SERVER["REQUEST_URI"])) == 1)
{
	$parts = explode("?",$_SERVER["REQUEST_URI"]);
	$path = $parts[0];
	foreach ($ACTIONS as $action)
	{
		if (strlen($action[3]) > 0 && strstr($path,$action[3]) !== false)
		{
			$PARAMS["Action"] = $action[0]; 
			break;
		}
	}
}

while ($_ActionCounter < count($ACTIONS))
{
	if (0 == strcmp($PARAMS["Action"],$ACTIONS[$_ActionCounter][0]))
	{
		$Action = $ACTIONS[$_ActionCounter][0];
		if ($ACTIONS[$_ActionCounter][2] != "Public")
		{
			require("CheckAPIKey.php");
		}
		if ($ERROR == 100)
		{
			require($ACTIONS[$_ActionCounter][1]);
		}
		$ActionPreformed = 1;
		break;
	}
	$_ActionCounter++;
}


// Cleanup Steps
/////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////

// Check to make sure the action happened... 
if ($ActionPreformed == 0 || $PARAMS["Action"] == "" || strlen($PARAMS["Action"]) == 0)
{
	if ($ERROR == 100) { 
		$ERROR = "That Action cannot be found.  Did you send the 'Action' parameter?  List Actions with Action=DescribeActions"; 
		$Action = "Unknown Action";
	}
}

if ($ERROR == 100)
{
	$ERROR = "OK";
}

// end the timer
$ComputationEndTime = microtime_float();
$ComputationElapsedTime = $ComputationEndTime - $ComputationStartTime;

$OUTPUT['Action'] = $Action;
$OUTPUT['Params'] = $PARAMS;
$OUTPUT['ComputationTime'] = $ComputationElapsedTime;
$OUTPUT['IP'] = $IP;
$OUTPUT['ERROR'] = $ERROR;
$OUTPUT['ServerName'] = $CONFIG['ServerName'];
$OUTPUT['ServerAddress'] = $CONFIG['ServerAddress'];

// end transaction for connection if needed
if ($PARAMS['Rollback'] == $CONFIG['RollbackPhrase'] && ($DBOBJ instanceof DBConnection) == true)
{
	if ($DBOBJ->GetStatus() == true)
	{
		$DBOBJ->Query("ROLLBACK;");
		$OUTPUT["Rollback"] = "true";
	}
}

// output and cleanup
require('Output.php');
if ($CONFIG['Logging'] == true){ _LogAPIRequest(); }
@$DBOBJ->close();

?>