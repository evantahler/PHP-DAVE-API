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

// show errors on scrern
ini_set("display_errors","1");
error_reporting (E_ALL ^ E_NOTICE);

// don't cache these API pages
if (!headers_sent()) { header("Cache-Control: no-cache, must-revalidate"); }

// Start the timer
require("helper_functions/microtime_float.php");
$ComputationStartTime = microtime_float();

// working directory
$path = substr(__FILE__,0,(strlen(__FILE__) - strlen("index.php")));
chdir($path); unset($path);

// Start the Output
$OUTPUT = array();

if (file_exists("CONFIG.php")) 
{ 
	require("ConnectToDatabase.php");
	require("CONFIG.php"); 
	require("DAVE.php");
	require("CACHE.php");
	require("CommonFunctions.php");
	require("GetPostVars.php");

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
		if ($IP == ""){
			$IP = $_SERVER["REMOTE_ADDR"];
		}
	}

	// check if this user has made too many requests this hour
	if ($CONFIG['RequestLimitPerHour'] > 0)
	{
		if ($CONFIG['CorrectLimitLockPass'] != $PARAMS["LimitLockPass"])
		{
			$Status = $DBObj->GetStatus();
			if ($Status === true)
			{		
				$SQL = 'SELECT COUNT(*) as "total" FROM `'.$CONFIG['LOG_DB'].'`.`'.$CONFIG['LogTable'].'` WHERE (`IP` = "'.$IP.'" AND `TimeStamp` > "'.date('Y-m-d H:i:s',time()-(60*60)).'") ;';
				$DBObj->Query($SQL);
				$Status = $DBObj->GetStatus();
				if ($Status === true){
					$Results = $DBObj->GetResults();
					if ($Results[0]['total'] > $CONFIG['RequestLimitPerHour'])
					{
						$DBObj->close();
						$OUTPUT['ERROR'] = "You have exceeded your allotted ".$CONFIG['RequestLimitPerHour']." requests this hour.";
						require('Output.php');
						exit;
					}
					else
					{
						$OUTPUT['api_requests_remaining'] = $CONFIG['RequestLimitPerHour'] - $Results[0]['total'];
					}
				}
				else{ $ERROR = $Status; }
			}
			else { $ERROR = $Status; } 
		}
	}
	
	// start transaction for connection if needed
	if (isset($PARAMS['Rollback']))
		{
		if ($PARAMS['Rollback'] == $CONFIG['RollbackPhrase'] && ($DBObj instanceof DBConnection) == true)
		{
			if ($DBObj->GetStatus() == true)
			{
				$DBObj->Query("START TRANSACTION;");
			}
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
		if ($ERROR == 100) { 
			$ERROR = "That Action cannot be found.  Did you send the 'Action' parameter?"; 
			$Action = "Unknown Action";
			$OUTPUT["KnownActions"] = humanize_actions();
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
	if ($PARAMS['Rollback'] == $CONFIG['RollbackPhrase'] && ($DBObj instanceof DBConnection) == true)
	{
		if ($DBObj->GetStatus() == true)
		{
			$DBObj->Query("ROLLBACK;");
			$OUTPUT["Rollback"] = "true";
		}
	}

	// output
	require('Output.php');

	// Write to the LOG
	/////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////
	require("WriteToLog.php");

	// close the DB connection (created in CONFIG.php)
	@$DBObj->close();
}
else 
{
	echo "Please create CONFIG.php from CONFIG.php.example\r\n"; 
}

?>