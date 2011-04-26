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
		$Results = _VIEW("developers",array("APIKey" => $PARAMS["APIKey"]));
	}
	
	if (count($Results[1]) == 1)
	{
		$DeveloperID_ = $Results[1][0]['DeveloperID'];
		$APIKey_ = $Results[1][0]['APIKey'];
		$UserActions = $Results[1][0]['UserActions'];
		$IsAdmin = $Results[1][0]['IsAdmin'];
	}
	else
	{
		$ERROR = "API Key not found";
	}
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
	if ($ERROR == 100) { if ($PARAMS["Rand"] == ""){ $ERROR = "You need to provide a Rand to authenticate with"; } }
	if ($ERROR == 100) { if ($PARAMS["Hash"] == ""){ $ERROR = "You need to provide a Hash to authenticate with"; } }
	if ($ERROR == 100)
	{
		$TestHash = md5(($DeveloperID_.$APIKey_.$PARAMS["Rand"]));
		$OUTPUT["DeveloperID_"] = $DeveloperID_;
		$OUTPUT["APIKey_"] = $APIKey_;
		$OUTPUT["rand_"] = $PARAMS["Rand"];
		if (!($TestHash == $PARAMS["Hash"]))
		{
			$ERROR = "Developer Authentication Failed";
		}
	}
}

if ($ERROR == 100)
{
	$OUTPUT["DeveloperAuthentication"] = "TRUE";
}
else
{
	$OUTPUT["DeveloperAuthentication"] = "FALSE";
}


?>