<?php
/***********************************************
DAVE PHP API
https://github.com/evantahler/PHP-DAVE-API
Evan Tahler | 2011

There are certain required global functions for DAVE that are very related to the DB type you are using.  Those are defined here.  If they make no sense for your DB type, then return true.  They still need to be defined.

- _GetAPIRequestsCount()
- _LogAPIRequest()
- _DBSetCache()
- _DBGetCache()
- _StartTransaction()
- _CreateDBSaveState()
- _RestoreDBSaveState()
- _TruncateTable()
- _CleanSessions()
- _CleanLog()
- _CleanCache()
- _CountRowsInTable();
- _FindDBMaxValue();
- _FindDBMinValue();

***********************************************/

/*
I querey the LOG table within the database for a given $IP and lookup how many requests they have made so far within this timeframe

$SQL = 'SELECT COUNT(*) as "total" FROM `'.$CONFIG['LOG_DB'].'`.`'.$CONFIG['LogTable'].'` WHERE (`IP` = "'.$IP.'" AND `TimeStamp` > "'.date('Y-m-d H:i:s',time()-(60*60)).'") ;';
*/
function _GetAPIRequestsCount()
{
	global $IP, $CONFIG, $DBOBJ;
	
	$Status = $DBOBJ->GetStatus();
	if ($Status === true)
	{		
		$SQL = 'SELECT COUNT(*) as "total" FROM `'.$CONFIG['LOG_DB'].'`.`'.$CONFIG['LogTable'].'` WHERE (`IP` = "'.$IP.'" AND `TimeStamp` > "'.date('Y-m-d H:i:s',time()-(60*60)).'") ;';
		$DBOBJ->Query($SQL);
		$Status = $DBOBJ->GetStatus();
		if ($Status === true){
			$Results = $DBOBJ->GetResults();
			return (int)$Results[0]['total'];
		}
		else{ return $Status; }
	}
	else { return $Status; }
}

/*
I insert a record for an API requset for a given $IP, and log important information:
- Action
- APIKey
- DeveloperID
- ERROR
- IP
- Params (JSON or PHP-serailized hash)

$SQL= 'INSERT INTO `'.$CONFIG['LOG_DB'].'`.`'.$CONFIG['LogTable'].'` (`Action`, `APIKey`, `DeveloperID`, `ERROR`, `IP`, `Params`) VALUES ("'.mysql_real_escape_string($PARAMS["Action"],$Connection).'", "'.mysql_real_escape_string($PARAMS["APIKey"],$Connection).'", "'.mysql_real_escape_string($PARAMS["DeveloperID"],$Connection).'", "'.mysql_real_escape_string($ERROR,$Connection).'", "'.mysql_real_escape_string($IP,$Connection).'" , "'.mysql_real_escape_string(json_encode($PARAMS),$Connection).'");'; 
*/
function _LogAPIRequest()
{
	global $CONFIG, $PARAMS, $DBOBJ, $IP;
	
	$Connection = $DBOBJ->GetConnection();
	
	if (count($CONFIG) > 0)
	{
		if (!(in_array($PARAMS["Action"],$CONFIG['NoLogActions'])) && !(in_array($PARAMS["APIKey"],$CONFIG['NoLogAPIKeys'])))
		{
			$Status = $DBOBJ->GetStatus();
			if ($Status === true)
			{
				$SQL= 'INSERT INTO `'.$CONFIG['LOG_DB'].'`.`'.$CONFIG['LogTable'].'` (`Action`, `APIKey`, `DeveloperID`, `ERROR`, `IP`, `Params`) VALUES ("'.mysql_real_escape_string($PARAMS["Action"],$Connection).'", "'.mysql_real_escape_string($PARAMS["APIKey"],$Connection).'", "'.mysql_real_escape_string($PARAMS["DeveloperID"],$Connection).'", "'.mysql_real_escape_string($ERROR,$Connection).'", "'.mysql_real_escape_string($IP,$Connection).'" , "'.mysql_real_escape_string(json_encode($PARAMS),$Connection).'");'; 
				$a = $DBOBJ->Query($SQL);
			}
		}
	}
}

/*
I am the DB-based method of storing CACHE objects.  I will be used in definitions found in CACHE.php
*/
function _DBSetCache($Key, $Value, $ThisCacheTime = null)
{
	global $CONFIG, $DBOBJ;
	if ($ThisCacheTime == null) { $ThisCacheTime = $CONFIG['CacheTime']; }
	
	$ExpireTime = time() + $ThisCacheTime;
			
	$Status = $DBOBJ->GetStatus();
	if ($Status === true)
	{
		$Connection = $DBOBJ->GetConnection();
		$SQL = 'INSERT INTO `'.$CONFIG['CacheTable'].'` (`Key`, `Value`, `ExpireTime`) VALUES ("'.mysql_real_escape_string($Key,$Connection).'", "'.mysql_real_escape_string(serialize($Value),$Connection).'", "'.mysql_real_escape_string($ExpireTime,$Connection).'");' ;
		$DBOBJ->Query($SQL);
		$Status = $DBOBJ->GetStatus();
		if ($Status === true){return true;}
		else { return false; }
	}
	else { return false; }
}

/*
I am the DB-based method of getting back CACHE objects.  I will be used in definitions found in CACHE.php
*/
function _DBGetCache($Key)
{
	global $CONFIG, $DBOBJ;
			
	$Status = $DBOBJ->GetStatus();
	if ($Status === true)
	{
		$Connection = $DBOBJ->GetConnection();
		$SQL = 'SELECT `Value` FROM `'.$CONFIG['CacheTable'].'` WHERE (`Key` = "'.mysql_real_escape_string($Key,$Connection).'" AND `ExpireTime` >= "'.mysql_real_escape_string(time(),$Connection).'") ORDER BY `ExpireTime` DESC LIMIT 1;' ;
		$DBOBJ->Query($SQL);
		$Status = $DBOBJ->GetStatus();
		if ($Status === true){
			$Results = $DBOBJ->GetResults();
			return unserialize($Results[0]['Value']);
		}
		else { return false; }
	}
	else { return false; }
}

/*
If your database type supports it, start a transaction for this connection
*/
function _StartTransaction()
{
	global $DBOBJ;
	if (($DBOBJ instanceof DBConnection) == true && $DBOBJ->GetStatus() == true)
	{
		$DBOBJ->Query("START TRANSACTION;");
		return true;
	}
	else
	{
		return false;
	}
}

/*
I create a restorable copy of the entire working database.  This may be the creation of "backup" tables, a file-based dump of the database, etc.  This backup will be restored with RestoreDBSveState.  This backup should leave the current state of the data and schema (if applicable) available in the "normal" tables, as well as copy it to the backup.  For mySQL, we create backup tables denoted with ~~ before the table name. 
*/
function _CreateDBSaveState($PARAMS = array())
{
	global $CONFIG, $TABLES, $DBOBJ;
	
	reload_tables();
	
	$output = array();
	$TablesToSave = array();
	if (strlen($PARAMS['table']) > 0)
	{
		$TablesToSave[] = $PARAMS['table'];
	}
	else
	{
		foreach($TABLES as $table => $data)
		{
			if (substr($table,0,2) != "~~")
			{
				$TablesToSave[] = $table;
			}
		}
	}
	
	$Status = $DBOBJ->GetStatus();
	if ($Status === true){ $DBOBJ->Query("LOCK TABLES;"); }
	
	foreach($TablesToSave as $table)
	{
		$Status = $DBOBJ->GetStatus();
		if ($Status === true)
		{
			$output[] = "saving `".$table."` to `~~".$table."`";
			$DBOBJ->Query("DROP TABLE IF EXISTS `~~".$table."`;");
			$DBOBJ->Query("RENAME TABLE `".$table."` TO `~~".$table."`;");
			$DBOBJ->Query("CREATE TABLE `".$table."` LIKE `~~".$table."`;");
		}
		else
		{
			$output[] = "DB Error: ".$Status;
			break;
		}
	}
	
	$Status = $DBOBJ->GetStatus();
	if ($Status === true){ $DBOBJ->Query("UNLOCK TABLES;"); }
	
	return $output;
} 

/*
I restore a copy of the entire working database (creted with CreateDBSaveState). I will erase any modifications (D's,A's,V's, or E's) since the backup state was created.  For mySQL, we look for any tables with the ~~ name indicating that they are a backup.  We then drop the existing table, and rename the backup. 
*/
function _RestoreDBSaveState($PARAMS = array())
{
	global $CONFIG, $TABLES, $DBOBJ;
	
	reload_tables();
	
	$output = array();
	$TablesToRestore = array();
	if (strlen($PARAMS['table']) > 0)
	{
		$TablesToRestore[] = "~~".$PARAMS['table'];
	}
	else
	{
		foreach($TABLES as $table => $data)
		{
			if (substr($table,0,2) == "~~")
			{
				$TablesToRestore[] = $table;
			}
		}
	}
	
	$Status = $DBOBJ->GetStatus();
	if ($Status === true){ $DBOBJ->Query("LOCK TABLES;"); }
			
	foreach($TablesToRestore as $table)
	{
		$Status = $DBOBJ->GetStatus();
		if ($Status === true)
		{
			$output[] = "restoring `".$table."` to `".substr($table,2)."`";
			$DBOBJ->Query("DROP TABLE IF EXISTS `".substr($table,2)."`;");
			$DBOBJ->Query("RENAME TABLE `".$table."` TO `".substr($table,2)."`;");
		}
		else
		{
			$output[] = "DB Error: ".$Status;
			break;
		}
	}
	
	$Status = $DBOBJ->GetStatus();
	if ($Status === true){ $DBOBJ->Query("UNLOCK TABLES;"); }
	
	return $output;
}

/*
I will clear out all rows/objects from a table.  I will also reset any auto-incrament counters to 0.  In mySQL, this is the truncate command.
*/
function _TruncateTable($PARAMS = array())
{
	global $CONFIG, $DBOBJ;
	
	$resp = "";
	$stop = false;
	if (strlen($PARAMS['table']) == 0)
	{
		$resp = 'Provide a table name with --table';
		$stop = true;
	}
	
	if (strlen($PARAMS['DB']) > 0)
	{
		$ThisDB = $PARAMS['DB'];
	}
	else
	{
		$ThisDB = $CONFIG['DB'];
	}
	
	$Status = $DBOBJ->GetStatus();
	if ($stop == false && !($Status === true))
	{
		$resp = "DB Error: ".$Status;
		$stop = true;
	}
			
	if ($stop == false)
	{
		$SQL= 'TRUNCATE TABLE `'.$ThisDB.'`.`'.$PARAMS['table'].'`;'; 	
		$DBOBJ->Query($SQL);
		if ($DBOBJ->NumRowsEffected() == 0)
		{
			$resp = $PARAMS['table']." table truncated from the ".$ThisDB." DB";
		}
		else
		{
			$resp = "Table ".$PARAMS['table']." cannot be found in ".$ThisDB;
		}
	}
	return $resp;
}

/*
I will remove old sessions from the sessions table
*/
function _CleanSessions($PARAMS = array())
{
	global $CONFIG, $DBOBJ;
	$stop = false;
	$resp = "";
	
	$Status = $DBOBJ->GetStatus();
	if ($stop == false && !($Status === true))
	{
		$resp = "DB Error: ".$Status;
		$stop = true;
	}
	
	if ($stop == false)
	{
		$SQL= 'DELETE FROM `sessions` WHERE (`created_at` < "'.date('Y-m-d H:i:s',(time() - $CONFIG['SessionAge'])).'") ;';
		$DBOBJ->Query($SQL);
		$resp = 'Deleted '.$DBOBJ->NumRowsEffected()." entries from the SESSIONS Table in the DB";
	}
	else
	{
		$resp = "cannot connect to database";
	}
	return $resp;
}

/*
I will remove old log entries from the log table/db
*/
function _CleanLog()
{
	global $CONFIG, $DBOBJ;
	$stop = false;
	$resp = "";
	
	$Status = $DBOBJ->GetStatus();
	if ($stop == false && !($Status === true))
	{
		$resp = "DB Error: ".$Status;
		$stop = true;
	}
	
	if ($stop == false)
	{
		$SQL= 'DELETE FROM `'.$CONFIG['LOG_DB'].'`.`'.$CONFIG['LogTable'].'` WHERE (`TimeStamp` < "'.date('Y-m-d H:i:s',(time() - $CONFIG['LogAge'])).'") ;'; 	
		$DBOBJ->Query($SQL);
		$resp = 'Deleted '.$DBOBJ->NumRowsEffected()." entries from the LOG Table in the ".$CONFIG['LOG_DB']." DB";
	}
	else
	{
		$resp = "cannot connect to ".$CONFIG['LOG_DB'];
	}
	return $resp;
}

/*
I will remove old log entries from the log table/db
*/
function _CleanCache()
{
	global $CONFIG, $DBOBJ;
	$stop = false;
	$resp = "";
	
	$Status = $DBOBJ->GetStatus();
	if ($stop == false && !($Status === true))
	{
		$resp = "DB Error: ".$Status;
		$stop = true;
	}
	
	if ($stop == false)
	{
		$SQL= 'DELETE FROM `'.$CONFIG['DB'].'`.`'.$CONFIG['CacheTable'].'` WHERE (`ExpireTime` < "'.(time() - $CONFIG['CacheTime']).'") ;';
		$DBOBJ->Query($SQL);
		$resp = 'Deleted '.$DBOBJ->NumRowsEffected()." entries from the CACHE DB";
	}
	else
	{
		$resp = "cannot connect to DB";
	}
	return $resp;
}

function _CountRowsInTable($Table)
{
	global $CONFIG, $DBOBJ;
	
	if ($DBOBJ->GetStatus() != true){return false;}
	$SQL = "SELECT COUNT(1) AS 'total' FROM ".$Table.";";
	$DBOBJ->query($SQL);
	$results = $DBOBJ->GetResults();
	if (count($results[0]) > 0){
		return (int)$results[0]['total'];
	}
	else
	{
		return 0;
	}
}

function _FindDBMaxValue($Table, $col)
{
	global $CONFIG, $DBOBJ;
	
	if ($DBOBJ->GetStatus() != true){return false;}
	$SQL = "SELECT MAX(`".$col."`) as 'total' from `".$Table."` ) ";
	$DBOBJ->query($SQL);
	$results = $DBOBJ->GetResults();
	if (count($results[0]) > 0){
		return (int)$results[0]['total'];
	}
	else
	{
		return false;
	}
}

function _FindDBMinValue($Table, $col)
{
	global $CONFIG, $DBOBJ;
	
	if ($DBOBJ->GetStatus() != true){return false;}
	$SQL = "SELECT MIN(`".$col."`) as 'total' from `".$Table."` ) ";
	$DBOBJ->query($SQL);
	$results = $DBOBJ->GetResults();
	if (count($results[0]) > 0){
		return (int)$results[0]['total'];
	}
	else
	{
		return false;
	}
}

?>