<?php
/***********************************************
DAVE PHP API
https://github.com/evantahler/PHP-DAVE-API
Evan Tahler | 2011

I am a collection of common API functions that will be re-used, such as special-case input validation.  
CommonFunctions that are tied to a specific DB implementation will be found in DB/DRIVERS/{DB}/DirectDBFunctions.php
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

function load_tasks()
{
	global $CONFIG;
	require_once($CONFIG['App_dir']."Tasks/_BASE.php");
	$TaskFiles = glob($CONFIG['App_dir']."Tasks/*.php");
	foreach($TaskFiles as $task_file){require_once($task_file); }
	
	$TaskNames = array();
	foreach($TaskFiles as $class_name)
	{
		$parts = explode("/",$class_name);
		$parts = explode(".",$parts[(count($parts) - 1)]);
		$class_name = $parts[0];
		if ($class_name != "task" && class_exists($class_name))
		{
		    $TaskNames[] = $class_name;
		}
	}
	
	return $TaskNames;
}

function run_task($TaskName, $PARAMS = array())
{
	// assumes that tasks have been properly loaded in with load_tasks()
	$TaskLog = "Running Task: ".$TaskName."\r\n\r\n";
	if (!(is_string($TaskName)) || strlen($TaskName) == 0){return "No task provided.\r\n";}
	$_TASK = new $TaskName(true, $PARAMS);
	$TaskLog .= $_TASK->get_task_log();
	return $TaskLog."\r\n";
}

function reload_tables()
{
	global $ERROR, $DBOBJ, $CONFIG, $TABLES;
		
	$Status = $DBOBJ->GetStatus();
	if ($Status === true)
	{
		$TABLES = array();
		$ToReloadTables = true;
		require($CONFIG['App_dir']."DB/DRIVERS/".$CONFIG["DBType"]."/TableConfig.php"); // requiring again will force a re-load
	}
	else
	{
		$ERROR = "DB Cannot be reached: ".$Status;
	}
}

function _tableCheck($Table)
{
	global $TABLES;
	// does this table exist?
	$Keys = array_keys($TABLES);
	if( in_array($Table, $Keys))
	{
		return true;
	}
	else
	{
		return false;
	}
}

function _getAllTableCols($Table)
{
	global $TABLES;
	$Vars = array();
	$i = 0;
	while ($i < count($TABLES[$Table]))
	{
		$Vars[] = $TABLES[$Table][$i][0];
		//
		$i++;
	}
	return $Vars;
}

function _getRequiredTableVars($Table)
{
	global $TABLES;
	$RequiredVars = array();
	$i = 0;
	while ($i < count($TABLES[$Table]))
	{
		if ($TABLES[$Table][$i][2] == true && $TABLES[$Table]["META"]["KEY"] != $TABLES[$Table][$i][0])
		{
			$RequiredVars[] = $TABLES[$Table][$i][0];
		}
		//
		$i++;
	}
	return $RequiredVars;
}

function _getUniqueTableVars($Table)
{
	global $TABLES;
	$UniqueVars = array();
	$i = 0;
	while ($i < count($TABLES[$Table]))
	{
		if ($TABLES[$Table][$i][1] == true)
		{
			$UniqueVars[] = $TABLES[$Table][$i][0];
		}
		//
		$i++;
	}
	return $UniqueVars;
}

function _isSpecialString($string)
{
	global $CONFIG;
	$found = false;
	foreach ($CONFIG['SpecialStrings'] as $term)
	{
		if (stristr($string,$term[0]) !== false)
		{
			$found = true;
			break;
		}
	}
	return $found;
}

function create_session()
{
	$key = md5( uniqid() );
	_ADD("sessions", array(
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
	$resp = _EDIT("sessions",array(
		"KEY" => $SessionKey,
		"updated_at" => date("Y-m-d H:i:s"),
		"DATA" => serialize($SessionData)
	));	
	return($resp[0]);
}

function get_session_data($SessionKey)
{
	global $OUTPUT;
	$results = _VIEW("sessions", 
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

function only_table_columns($DATA, $Table)
{
	$CleanData = array();
	foreach($DATA as $param=>$val)
	{
		if(in_array($param,_getAllTableCols($Table))) { $CleanData[$param] = $val ;}
	}
	return $CleanData;
}

function AuthenticateUser($DATA = null)
{
	// (UserID || ScreenName || EMail) + (Password || PasswordHash) || (Hash + Rand + UserID)
	// Hash = md5(UserID.Password.Rand)
	
	global $PARAMS;
	$OUT = false;
	if ($DATA == null){$DATA = $PARAMS;}
	
	if (empty($DATA['UserID']) && empty($DATA['EMail']) && empty($DATA['ScreenName']))
	{
		$OUT = "Authentication: Provide either UserID, EMail, or ScreenName";
	}
	
	list($msg, $ReturnedUsers) = _VIEW("users",array(
		"UserID" => $DATA['UserID'],
		"ScreenName" => $DATA['ScreenName'],
		"EMail" => $DATA['EMail']
	));
	
	if($msg != true)
	{
		$OUT = $ReturnedUsers;
	}
	else
	{
		if (count($ReturnedUsers) != 1)
		{
			$OUT = "Authentication: User not found";
		}
		elseif (!empty($DATA['Hash']))
		{
			if (empty($DATA['Rand'])){$OUT = "Authentication: Rand is required";}
			else
			{
				$LocalHash = md5($ReturnedUsers[0]['UserID'].$ReturnedUsers[0]['Password'].$DATA['Rand']);
				if ($DATA['Hash'] == $LocalHash){$OUT = true;}
				else{$OUT = "Authentication: Hash does not match expected";}
			}
		}
		elseif(!empty($DATA['Password']) || !empty($DATA['PasswordHash']))
		{
			if(empty($DATA['PasswordHash'])){$DATA['PasswordHash'] = md5($DATA['Password'].$ReturnedUsers[0]['Salt']);}
			if ($DATA['PasswordHash'] == $ReturnedUsers[0]['PasswordHash']){$OUT = true;}
			else{$OUT = "Authentication: Password or PasswordHash does not match";}
		}
		else
		{
			$OUT = "Authentication: Send either Hash [ md5(UserID.Password.Rand) ], Password, or PasswordHash ";
		}
	}
	return $OUT;
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