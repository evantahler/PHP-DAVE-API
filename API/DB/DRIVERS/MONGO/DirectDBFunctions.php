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

***********************************************/

/*
I querey the LOG table within the database for a given $IP and lookup how many requests they have made so far within this timeframe

$SQL = 'SELECT COUNT(*) as "total" FROM `'.$CONFIG['LOG_DB'].'`.`'.$CONFIG['LogTable'].'` WHERE (`IP` = "'.$IP.'" AND `TimeStamp` > "'.date('Y-m-d H:i:s',time()-(60*60)).'") ;';
*/
function _GetAPIRequestsCount()
{
	global $IP, $CONFIG, $DBOBJ;

	$Status = $DBOBJ->GetStatus();
	if($Status === true)
	{
		$Connection = $DBOBJ->GetConnection();
		$LogsDB = $Connection->$CONFIG['LOG_DB'];
		$Logs = $LogsDB->$CONFIG['LogTable'];		
		$count = $Logs->count(array(
			'IP' => $IP,
			'TimeStamp' => array('$gt' => date('Y-m-d H:i:s',time()-(60*60)))
			));
		return $count;
	}
	else{ return $Status; }
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
	if (count($CONFIG) > 0)
	{
		$Status = $DBOBJ->GetStatus();
		if($Status === true)
		{
			$Connection = $DBOBJ->GetConnection();
			$LogsDB = $Connection->$CONFIG['LOG_DB'];
			$Logs = $LogsDB->$CONFIG['LogTable'];
			$log = array( 
				"Action" => $PARAMS["Action"], 
				"APIKey" => $PARAMS["APIKey"],
				"DeveloperID" => $PARAMS["DeveloperID"],
				"ERROR" => $ERROR,
				"IP" => $IP,
				"Params" => json_encode($PARAMS),
				"TimeStamp" => date('Y-m-d H:i:s',time())
			);
			return $Logs->insert($log);
		}
		else{ return $Status; }
	}
	else{ return false; }	
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
	if($Status === true)
	{
		$MongoDB = $DBOBJ->GetMongoDB();
		$Caches = $MongoDB->$CONFIG['CacheTable'];
		$entry = array( 
			"Key" => $Key, 
			"Value" => $Value,
			"ExpireTime" => $ExpireTime
		);
		return $Caches->insert($entry);
	}
	else{ return false; }
}

/*
I am the DB-based method of getting back CACHE objects.  I will be used in definitions found in CACHE.php
*/
function _DBGetCache($Key)
{
	global $CONFIG, $DBOBJ;
				
	$Status = $DBOBJ->GetStatus();
	if($Status === true)
	{
		$MongoDB = $DBOBJ->GetMongoDB();
		$Caches = $MongoDB->$CONFIG['CacheTable'];
		$cursor = $Caches->find(array(
			'Key' => $Key,
			'ExpireTime' => array('$gt' => time())
			));
		$newest_obj = array("ExpireTime" => 0);
		foreach ($cursor as $obj) 
		{
			if ($obj['ExpireTime'] > $newest_obj["ExpireTime"])
			{
				$newest_obj = $obj;
			}
		}
		
		return $newest_obj['Value'];
	}
	else{ return $Status; }
}

/*
If your database type supports it, start a transaction for this connection
*/
function _StartTransaction()
{
	return true;
}

/*
I create a restorable copy of the entire working database.  This may be the creation of "backup" tables, a file-based dump of the database, etc.  This backup will be restored with RestoreDBSveState.  This backup should leave the current state of the data and schema (if applicable) available in the "normal" tables, as well as copy it to the backup.  For mySQL, we create backup tables denoted with ~~ before the table name. 
*/
function _CreateDBSaveState($PARAMS = array())
{
	global $CONFIG, $TABLES, $DBOBJ;
	
	reload_tables();
	$MongoDB = $DBOBJ->GetMongoDB();
		
	$output = array();
	$TablesToSave = array();
	if (strlen($PARAMS['table']) > 0)
	{
		$TablesToSave[] = $PARAMS['table'];
	}
	else
	{
		$list = $MongoDB->listCollections();
		$TableList = array();
		foreach ($list as $sub)
		{
			$name = $sub->getName();
			if ($name != "cache" && $name != "log" && substr($name,0,2) != "~~") { $TablesToSave[] = $name; }
		}
	}
	
	foreach($TablesToSave as $table)
	{
		$Status = $DBOBJ->GetStatus();
		if ($Status === true)
		{
			$Connection = $DBOBJ->GetConnection();
			$adminMongoDB = $Connection->admin; //connect to admin DB to force authentication
			
			$output[] = "saving `".$table."` to `~~".$table."`";
			$oldName = "~~".$table;
			$adminMongoDB->$oldName->drop();
			$adminMongoDB->command(array( "renameCollection" => $CONFIG["DB"].".".$table, "to" => $CONFIG["DB"].".".$oldName ));
		}
		else
		{
			$output[] = "DB Error: ".$Status;
			break;
		}
	}
	
	return $output;
} 

/*
I restore a copy of the entire working database (creted with CreateDBSaveState). I will erase any modifications (D's,A's,V's, or E's) since the backup state was created.  For mySQL, we look for any tables with the ~~ name indicating that they are a backup.  We then drop the existing table, and rename the backup. 
*/
function _RestoreDBSaveState($PARAMS = array())
{
	global $CONFIG, $TABLES, $DBOBJ;
	
	reload_tables();
	$MongoDB = $DBOBJ->GetMongoDB();
	
	$output = array();
	$TablesToRestore = array();
	if (strlen($PARAMS['table']) > 0)
	{
		$TablesToRestore[] = "~~".$PARAMS['table'];
	}
	else
	{
		$list = $MongoDB->listCollections();
		$TableList = array();
		foreach ($list as $sub)
		{
			$name = $sub->getName();
			if ($name != "cache" && $name != "log" && substr($name,0,2) == "~~") { $TablesToRestore[] = $name; }
		}
	}
			
	foreach($TablesToRestore as $table)
	{
		$Status = $DBOBJ->GetStatus();
		if ($Status === true)
		{
			$Connection = $DBOBJ->GetConnection();
			$adminMongoDB = $Connection->admin; //connect to admin DB to force authentication
			
			$output[] = "restoring `".$table."` to `".substr($table,2)."`";
			$origName = substr($table,2);
			$adminMongoDB->$origName->drop();
			$adminMongoDB->command(array( "renameCollection" => $CONFIG["DB"].".".$table, "to" => $CONFIG["DB"].".".$origName ));
		}
		else
		{
			$output[] = "DB Error: ".$Status;
			break;
		}
	}
	
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
		$Connection = $DBOBJ->GetConnection();
		$MongoDB = $Connection->$ThisDB;
		$Collection = $MongoDB->$PARAMS['table'];
		$FIND = array();
		$count = $Collection->count($FIND);
		$Collection->remove($FIND);
		$resp = $PARAMS['table']." table truncated from the ".$ThisDB." DB";
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
		$MongoDB = $DBOBJ->GetMongoDB();
		$Sessions = $MongoDB->sessions;
		$FIND = array("created_at" => array('$lt' => date('Y-m-d H:i:s',(time() - $CONFIG['SessionAge']))));
		$count = $Sessions->count($FIND);
		$Sessions->remove($FIND);
		$resp = 'Deleted '.$count." entries from the sessions DB";
	}
	else
	{
		$resp = "cannot connect to DB";
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
		$Connection = $DBOBJ->GetConnection();
		$LogsDB = $Connection->$CONFIG['LOG_DB'];
		$Logs = $LogsDB->$CONFIG['LogTable'];
		$FIND = array("TimeStamp" => array('$lt' => date('Y-m-d H:i:s',(time() - $CONFIG['LogAge']))));
		$count = $Logs->count($FIND);
		$Logs->remove($FIND);
		$resp = 'Deleted '.$count." entries from ".$CONFIG['LOG_DB'].".".$CONFIG['LogTable'];
	}
	else
	{
		$resp = "cannot connect to ".$CONFIG['LOG_DB'].".".$CONFIG['LogTable'];
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
		$MongoDB = $DBOBJ->GetMongoDB();
		$Caches = $MongoDB->$CONFIG['CacheTable'];
		$FIND = array("ExpireTime" => array('$lt' => (time() - $CONFIG['CacheTime'])));
		$count = $Caches->count($FIND);
		$Caches->remove($FIND);
		$resp = 'Deleted '.$count." entries from the CACHE DB";
	}
	else
	{
		$resp = "cannot connect to DB";
	}
	return $resp;
}


?>