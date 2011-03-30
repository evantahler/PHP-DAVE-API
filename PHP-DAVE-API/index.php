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

// Start the timer
require("helper_functions/microtime_float.php");
$ComputationStartTime = microtime_float();

// Start the Output
$OUTPUT = array();

require("ConnectToDatabase.php");
require("CONFIG.php");
require("DAVE.php");
require("CACHE.php");
require("CommonFunctions.php");
require("GetPostVars.php");
date_default_timezone_set($systemTimeZone);

// Get IP (if not provided)
if (empty($IP) || $IP == "")
{
	if (getenv(HTTP_X_FORWARDED_FOR)) {							
   		$IPList = getenv(HTTP_X_FORWARDED_FOR); 
   		$IPArray = explode(",", $IPList);
   		$IP = trim($IPArray[count($IPArray)-1]);
	} else { 
    	$IP = getenv(REMOTE_ADDR);
	}
}

// check if this user has made too many requests this hour
if ($RequestLimitPerHour > 0)
{
	if ($CorrectLimitLockPass != $PARAMS["LimitLockPass"])
	{
		$Status = $DBObj->GetStatus();
		if ($Status === true)
		{		
			$SQL = 'SELECT COUNT(*) as "total" FROM `'.$LogTable.'` WHERE (`IP` = "'.$IP.'" AND `TimeStamp` > "'.date('Y-m-d H:i:s',time()-(60*60)).'") ;';
			$DBObj->Query($SQL);
			$Status = $DBObj->GetStatus();
			if ($Status === true){
				$Results = $DBObj->GetResults();
				if ($Results[0]['total'] > $RequestLimitPerHour)
				{
					$DBObj->close();
					$OUTPUT['ERROR'] = "You have exceeded your allotted ".$RequestLimitPerHour." requests this hour.";
					require('Output.php');
					exit;
				}
				else
				{
					$OUTPUT['api_requests_remaining'] = $RequestLimitPerHour - $Results[0]['total'];
				}
			}
			else{ $ERROR = $Status; }
		}
		else { $ERROR = $Status; } 
	}
}

$ActionPreformed = 0;

// functional Steps
/////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////

$_ActionCounter = 0;
while ($_ActionCounter < count($ACTIONS))
{
	if (0 == strcmp($PARAMS["Action"],$ACTIONS[$_ActionCounter][0]))
	{
		if ($ACTIONS[$_ActionCounter][2] != "Public")
		{
			require("CheckAPIKey.php");
		}
		if ($ERROR == 100)
		{
			$Action = $ACTIONS[$_ActionCounter][0];
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
	if ($ERROR == 100) { $ERROR = "That Action cannot be found.  Did you send the 'Action' parameter?"; }
	$Action = "Unknown Action";
	$OUTPUT["KnownActions"] = humanize_actions();
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
$OUTPUT['ServerName'] = $ServerName;
$OUTPUT['ServerAddress'] = $ServerAddress;

// output
require('Output.php');

// Write to the LOG
/////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////
require("WriteToLog.php");

// close the DB connection (created in CONFIG.php)
$DBObj->close();

?>