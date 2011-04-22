<?php
/***********************************************
DAVE PHP API
https://github.com/evantahler/PHP-DAVE-API
Evan Tahler | 2011

There are certain required global functions for DAVE that are very related to the DB type you are using.  Those are defined here.  If they make no sense for your DB type, then return true.  They still need to be defined.
***********************************************/

function reload_tables()
{
	global $ERROR, $DBObj, $CONFIG, $TABLES;
		
	$Status = $DBObj->GetStatus();
	if ($Status === true)
	{
		$TABLES = array();
		@unlink($CONFIG['TableConfigFile']);
		require($CONFIG['App_dir']."DB/DRIVERS/".$CONFIG["DBType"]."/TableConfig.php"); // requiring again will force a re-load
	}
	else
	{
		$ERROR = "DB Cannot be reached: ".$Status;
	}
}

function get_api_requests_count()
{
	global $IP, $CONFIG, $DBObj;
	
	$Status = $DBObj->GetStatus();
	if ($Status === true)
	{		
		$SQL = 'SELECT COUNT(*) as "total" FROM `'.$CONFIG['LOG_DB'].'`.`'.$CONFIG['LogTable'].'` WHERE (`IP` = "'.$IP.'" AND `TimeStamp` > "'.date('Y-m-d H:i:s',time()-(60*60)).'") ;';
		$DBObj->Query($SQL);
		$Status = $DBObj->GetStatus();
		if ($Status === true){
			$Results = $DBObj->GetResults();
			return (int)$Results[0]['total'];
		}
		else{ return $Status; }
	}
	else { return $Status; }
}

function log_api_request()
{
	global $CONFIG, $PARAMS, $DBObj, $IP;
	
	$Connection = $DBObj->GetConnection();
	
	if (count($CONFIG) > 0)
	{
		if (!(in_array($PARAMS["Action"],$CONFIG['NoLogActions'])) && !(in_array($PARAMS["APIKey"],$CONFIG['NoLogAPIKeys'])))
		{
			$Status = $DBObj->GetStatus();
			if ($Status === true)
			{
				$SQL= 'INSERT INTO `'.$CONFIG['LOG_DB'].'`.`'.$CONFIG['LogTable'].'` (`Action`, `APIKey`, `DeveloperID`, `ERROR`, `IP`, `Params`) VALUES ("'.mysql_real_escape_string($PARAMS["Action"],$Connection).'", "'.mysql_real_escape_string($PARAMS["APIKey"],$Connection).'", "'.mysql_real_escape_string($PARAMS["DeveloperID"],$Connection).'", "'.mysql_real_escape_string($ERROR,$Connection).'", "'.mysql_real_escape_string($IP,$Connection).'" , "'.mysql_real_escape_string(json_encode($PARAMS),$Connection).'");'; 
				$a = $DBObj->Query($SQL);
			}
		}
	}
}

?>