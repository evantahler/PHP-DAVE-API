
<?php

/***********************************************
DAVE PHP API
https://github.com/evantahler/PHP-DAVE-API
Evan Tahler | 2011

This page will attempt to check that the API key in use is OK for the function in qusetion.  I use the APIKey and DeveloperID pair to authenticate the action.  I am used for Private functions
***********************************************/

// Check that there is an APIKey
if ($ERROR == 100){ if($PARAMS["APIKey"] == "") { $ERROR = "You need to provide an APIKey"; } }

// Check that the API Key is in the DB
if ($ERROR == 100)
{
	$CacheKey = $PARAMS["APIKey"]."_CheckAPIKey";
	$result = GetCache($CacheKey);
	if ($result === false)
	{
		$SQL = 'SELECT * FROM `developers` WHERE (`APIKey` = "'.$PARAMS["APIKey"].'") LIMIT 1;';
		$Status = $DBOBJ->GetStatus();
		if ($Status === true)
		{
			$DBOBJ->Query($SQL);
			$Status = $DBOBJ->GetStatus();
			if ($Status === true){ $Results = $DBOBJ->GetResults();}
			else{ $ERROR = $Status; }
		}
		else { $ERROR = $Status; } 
	}
	
	$DeveloperID_ = $Results[0]['DeveloperID_'];
	$APIKey_ = $Results[0]['APIKey_'];
	$UserActions = $Results[0]['UserActions'];
	$IsAdmin = $Results[0]['IsAdmin'];
	$ERROR = $Results[0]['ERROR'];
}
// Check that the API Key has admin rights for user Actions
if ($ERROR == 100)
{
	if($UserActions == 1){ $UserAction = true; }
	else if($UserActions == 0){ $UserAction = false; }
}

// Check that the sequrity HASH worked out
// the hash should be md5($DeveloperID{secret}.$APIKey.$Rand), IN THIS ORDER!!!!
if ($CONFIG['SafeMode'] == true)
{
	if ($ERROR == 100)
	{
		if ($PARAMS["Hash"] != "" && $PARAMS["Rand"] == ""){ $PARAMS["Rand"] = "0"; } // check to see if 0 was sent as the Rand... try it anyway
		if ($PARAMS["Rand"] == ""){ $ERROR = "You need to provide a RAND to authenticate with"; }
		if ($PARAMS["Hash"] == ""){ $ERROR = "You need to provide a HASH to authenticate with"; }
	}
	if ($ERROR == 100)
	{
		$TestHash = md5(($DeveloperID_.$APIKey_.$PARAMS["Rand"]));
		if (!($TestHash == $PARAMS["Hash"]))
		{
			$ERROR = "Developer Authentication Failed";
		}
	}
}

?>