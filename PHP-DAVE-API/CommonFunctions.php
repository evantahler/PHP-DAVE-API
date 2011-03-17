<?php

/***********************************************
Evan Tahler
evantahler@gmail.com
2010

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

function create_session()
{
	global $OUTPUT, $KEY, $DATA, $created_at, $updated_at;
	$KEY = md5( uniqid() );
	$DATA = serialize(array());
	$created_at = date("Y-m-d H:i:s");
	$updated_at = date("Y-m-d H:i:s");
	_ADD("SESSIONS");
	return($KEY);
}

function update_session($SessionKey, $SessionData)
{
	// this function is destructive and will replace the entire array of session data previously stored
	global $OUTPUT, $KEY, $DATA;
	$KEY = $SessionKey;
	$updated_at = date("Y-m-d H:i:s");
	$DATA = serialize($SessionData);
	_EDIT("SESSIONS");
}

function get_session_data($SessionKey)
{
	global $OUTPUT, $KEY;
	$KEY = $SessionKey;
	$results = _VIEW("SESSIONS");
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