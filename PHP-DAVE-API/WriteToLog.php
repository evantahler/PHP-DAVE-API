<?php

/***********************************************
Evan Tahler
evantahler@gmail.com
2010

Even when an action fails, we should log that it happened.  This is importnat to bloc IPs that access the IP too much, etc.  This page should not use the safety string or do error checks, because we want it to always happen even if there is an error.

***********************************************/

if (!(in_array($Action,$NoLogActions)) && !(in_array($APIKey,$NoLogAPIKeys)))
{
	$DBObj = new DBConnection();
	$Status = $DBObj->GetStatus();
	if ($Status === true)
	{
		$Connection = $DBObj->GetConnection();
		$SQL= 'INSERT INTO `'.$LogTable.'` (`Action`, `APIKey`, `DeveloperID`, `ERROR`, `IP`) VALUES ("'.mysql_real_escape_string($Action,$Connection).'", "'.mysql_real_escape_string($APIKey,$Connection).'", "'.mysql_real_escape_string($DeveloperID,$Connection).'", "'.mysql_real_escape_string($ERROR,$Connection).'", "'.mysql_real_escape_string($IP,$Connection).'");'; 
		$DBObj->Query($SQL);
	}
	$DBObj->close();
}

?>