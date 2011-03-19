<?php

/***********************************************
DAVE PHP API
https://github.com/evantahler/PHP-DAVE-API
Evan Tahler | 2011

This page will attempt to check that the API key in use is OK for the function in qusetion.  I use the APIKey and DeveloperID pair to authenticate the action.  I am used for Private functions
***********************************************/

if ($ERROR == 100){ require ("CheckSafetyString.php"); }
// This page will check to see that the API Key used 1: exists, and 2: has access to the function and game in qusetion

// Check that there is an APIKey
if ($ERROR == 100){ if($APIKey == "") { $ERROR = "You need to provide an APIKey"; } }

// Check that the API Key is in the DB
if ($ERROR == 100)
{
	$CacheKey = $APIKey."_CheckAPIKey";
	$result = GetCache($CacheKey);
	if ($result === false)
	{
		$SQL = 'SELECT * FROM `Developers` WHERE (`APIKey` = "'.$APIKey.'") LIMIT 1;';
		$Status = $DBObj->GetStatus();
		if ($Status === true)
		{
			$DBObj->Query($SQL);
			$Status = $DBObj->GetStatus();
			if ($Status === true){ $Results = $DBObj->GetResults();}
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
if ($SafeMode == true)
{
	if ($ERROR == 100)
	{
		if ($Rand == "")
		{
			$ERROR = "You need to provide a RAND to authenticate with";
		}
		if ($Hash == "")
		{
			$ERROR = "You need to provide a HASH to authenticate with";
		}
		if ($Hash != "" && $Rand == ""){ $Rand = "0"; } // check to see if 0 was sent as the Rand... try it anyway
	}
	if ($ERROR == 100)
	{
		$TestHash1 = md5(($DeveloperID_.$APIKey_.$Rand));
		//$TestHash2 = md5(($DeveloperID_.strtolower($APIKey_).$Rand));
		//$TestHash3 = md5((strtolower($DeveloperID_).$APIKey_.$Rand));
		//$TestHash4 = md5((strtolower($DeveloperID_.$APIKey_).$Rand));
		if (!($TestHash1 == $Hash))
		{
			$ERROR = "Developer Authentication Error";
		}
	}
}

?>