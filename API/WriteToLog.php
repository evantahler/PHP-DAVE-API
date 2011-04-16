<?php

/***********************************************
DAVE PHP API
https://github.com/evantahler/PHP-DAVE-API
Evan Tahler | 2011

Even when an action fails, we should log that it happened.  This is importnat to bloc IPs that access the IP too much, etc.  This page should not use the safety string or do error checks, because we want it to always happen even if there is an error.

***********************************************/

if (count($CONFIG) > 0)
{
	if (!(in_array($PARAMS["Action"],$CONFIG['NoLogActions'])) && !(in_array($PARAMS["APIKey"],$CONFIG['NoLogAPIKeys'])))
	{
		$Status = $DBObj->GetStatus();
		if ($Status === true)
		{
			$SQL= 'INSERT INTO `'.$CONFIG['LOG_DB'].'`.`'.$CONFIG['LogTable'].'` (`Action`, `APIKey`, `DeveloperID`, `ERROR`, `IP`, `Params`) VALUES ("'.mysql_real_escape_string($PARAMS["Action"],$Connection).'", "'.mysql_real_escape_string($PARAMS["APIKey"],$Connection).'", "'.mysql_real_escape_string($PARAMS["DeveloperID"],$Connection).'", "'.mysql_real_escape_string($ERROR,$Connection).'", "'.mysql_real_escape_string($IP,$Connection).'" , "'.mysql_real_escape_string(json_encode($PARAMS),$Connection).'");'; 
			$DBObj->Query($SQL);
		}
	}
}

?>