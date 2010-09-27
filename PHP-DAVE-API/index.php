<?php

/***********************************************
Evan Tahler
evantahler@gmail.com
2010

This is the main page that does all the work

***********************************************/

// Init Steps
/////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////

require("helper_functions/microtime_float.php");
// Start the timer
$ComputationStartTime = microtime_float();
// Start the Output
$OUTPUT = array();

require("CONFIG.php");
require("ConnectToDatabase.php");
require("DAVE.php");
require("CACHE.php");
require("CommonFunctions.php");
require("SafetyString.php");
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
	if ($CorrectLimitLockPass != $LimitLockPass)
	{
		$SQL = 'SELECT COUNT(*) FROM `'.$LogTable.'` WHERE (`IP` = "'.$IP.'" AND `TimeStamp` > "'.date('Y-m-d H:i:s',time()-(60*60)).'") ;';
		$DBObj = new DBConnection();
		$Status = $DBObj->GetStatus();
		if ($Status === true)
		{
			$DBObj->Query($SQL);
			$Status = $DBObj->GetStatus();
			if ($Status === true){
				$Results = $DBObj->GetResults();
				if ($Results[0]['COUNT(*)'] > $RequestLimitPerHour)
				{
					$DBObj->close();
					$OUTPUT['ERROR'] = "You have exceded your alloted ".$RequestLimitPerHour." requests this hour.";
					require('Output.php');
					exit;
				}
				else
				{
					$OUTPUT['api_requests_remaining'] = $RequestLimitPerHour - $Results[0]['COUNT(*)'];
				}
			}
			else{ $ERROR = $Status; }
		}
		else { $ERROR = $Status; } 
		$DBObj->close();
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
	if (0 == strcmp($Action,$ACTIONS[$_ActionCounter][0]))
	{
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
if ($ERROR == 100)
{
	if ($ActionPreformed == 0 || $Action == "" || strlen($Action) == 0)
	{
		$ERROR = "That Action cannot be found.  Did you send the 'Action' parameter?";
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

?>