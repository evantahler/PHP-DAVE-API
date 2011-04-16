<?php
/***********************************************
DAVE PHP API
https://github.com/evantahler/PHP-DAVE-API
Evan Tahler | 2011

I am a collection of common API functions that will be re-used, such as special-case input validation.  
/*************************************************************/

function humanize_actions()
{
	global $ACTIONS;
	$HumanActions = array();
	foreach ($ACTIONS as $action)
	{
		$HumanActions[] = array(
			"Name" => $action[0], 
			"Access" => $action[2]
		);
	}
	return $HumanActions;
}

function reload_tables()
{
	global $ERROR, $DBObj, $CONFIG, $TABLES;
	
	$Status = $DBObj->GetStatus();
	if ($Status === true)
	{
		$TABLES = array();
		@unlik($CONFIG['TableConfigFile']);
		require("DB/TableConfig.php");
	}
	else
	{
		$ERROR = "DB Cannot be reached: ".$Status;
	}
}

function load_tasks()
{
	global $CONFIG;
	require_once($CONFIG['App_dir']."Tasks/_BASE.php");
	$TaskFiles = glob($CONFIG['App_dir']."Tasks/*.php");
	foreach($TaskFiles as $task_file){require_once($task_file); }
	
	return $TaskFiles;
}

function run_task($TaskName, $PARAMS = array())
{
	// assumes that tasks have been properly loaded in with load_tasks()
	$TaskLog = "Running Task: ".$TaskName."\r\n\r\n";
	$_TASK = new $TaskName(true, $PARAMS);
	$TaskLog .= $_TASK->get_task_log();
	return $TaskLog."\r\n";
}

function create_session()
{
	$key = md5( uniqid() );
	_ADD("SESSIONS", array(
		"KEY" => $key,
		"DATA" => serialize(array()),
		"created_at" => date("Y-m-d H:i:s"),
		"updated_at" => date("Y-m-d H:i:s")
	));
	return $key;
}

function update_session($SessionKey, $SessionData)
{
	// this function is destructive and will replace the entire array of session data previously stored
	_EDIT("SESSIONS",array(
		"KEY" => $SessionKey,
		"updated_at" => date("Y-m-d H:i:s"),
		"DATA" => serialize($SessionData)
	));
}

function get_session_data($SessionKey)
{
	global $OUTPUT;
	$results = _VIEW("SESSIONS", 
		array('KEY' => $SessionKey)
	);
	if ($results[0] != 1)
	{
		$OUTPUT["SessionError"] = "Session cannot be found by this key";
		return false;
	}
	else
	{
		return unserialize($results[1][0]["DATA"]);
	}
}

function validate_EMail($EMail)
{
	if (preg_match("/^([\w\!\#$\%\&\'\*\+\-\/\=\?\^\`{\|\}\~]+\.)*[\w\!\#$\%\&\'\*\+\-\/\=\?\^\`{\|\}\~]+@((((([a-z0-9]{1}[a-z0-9\-]{0,62}[a-z0-9]{1})|[a-z])\.)+[a-z]{2,6})|(\d{1,3}\.){3}\d{1,3}(\:\d{1,5})?)$/i",$EMail))
	{
	  $OUT = 100;
	} 
	else { 
	  $OUT = "That is not a valid EMail address";
	} 
	return $OUT;
}

function validate_PhoneNumber($PhoneNumber)
{
	$ERROR = 100;
	$PhoneNumber = preg_replace("[^A-Za-z0-9]", "", $PhoneNumber );
	$PhoneNumber = str_replace(".", "", $PhoneNumber);
	$PhoneNumber = str_replace("-", "", $PhoneNumber);
	$PhoneNumber = str_replace(" ", "", $PhoneNumber);
	if (strlen($PhoneNumber) == 10)
	{
		$PhoneNumber = "1".$PhoneNumber;
	}
	if (strlen($PhoneNumber) != 11)
	{
		$ERROR = "This phone number is not formatted properly";
	}
	return array($ERROR,$PhoneNumber);
}


/*************************************************************/

?>