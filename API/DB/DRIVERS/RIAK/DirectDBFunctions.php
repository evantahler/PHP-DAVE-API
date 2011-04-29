<?php
/***********************************************
DAVE PHP API
https://github.com/evantahler/PHP-DAVE-API
Evan Tahler | 2011

There are certain required global functions for DAVE that are very related to the DB type you are using.  Those are defined here.  If they make no sense for your DB type, then return true.  They still need to be defined.

- GetAPIRequestsCount()
- LogAPIRequest()
- _DBSetCache()
- _DBGetCache()
- StartTransaction()
- CreateDBSaveState()
- RestoreDBSaveState()

***********************************************/

/*
I querey the LOG table within the database for a given $IP and lookup how many requests they have made so far within this timeframe

$SQL = 'SELECT COUNT(*) as "total" FROM `'.$CONFIG['LOG_DB'].'`.`'.$CONFIG['LogTable'].'` WHERE (`IP` = "'.$IP.'" AND `TimeStamp` > "'.date('Y-m-d H:i:s',time()-(60*60)).'") ;';
*/
function GetAPIRequestsCount()
{
	global $IP, $CONFIG, $Riak, $RiakBucket;

	// $results = $Riak->search($CONFIG['LOG_DB'], 'IP:'.$IP.'" AND `TimeStamp` > "'.date('Y-m-d H:i:s',time()-(60*60)).'"')->run();
	$results = $Riak->search($CONFIG['LOG_DB'], 'IP:"'.$IP.'"')->run();
	
	var_dump($results);
	return count($results);
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
function LogAPIRequest()
{
	global $CONFIG, $PARAMS, $Riak, $RiakBucket, $IP;
	
	$log_entry = $RiakBucket->newObject('log', array(
	    'Action' => $PARAMS["Action"],
	    'APIKey' => $PARAMS["APIKey"],
	    'DeveloperID' => $PARAMS["DeveloperID"],
		'ERROR' => $ERROR,
		'IP' => $IP,
		'Params' => json_encode($PARAMS)
	));
}

/*
I am the DB-based method of storing CACHE objects.  I will be used in definitions found in CACHE.php
*/
function _DBSetCache($Key, $Value, $ThisCacheTime = null)
{
	global $CONFIG, $DBOBJ;
}

/*
I am the DB-based method of getting back CACHE objects.  I will be used in definitions found in CACHE.php
*/
function _DBGetCache($Key)
{
	global $CONFIG, $DBOBJ;		
}


/*
If your database type supports it, start a transaction for this connection
*/
function StartTransaction()
{
	global $DBOBJ;
	return true;
}

/*
I create a restorable copy of the entire working database.  This may be the creation of "backup" tables, a file-based dump of the database, etc.  This backup will be restored with RestoreDBSveState.  This backup should leave the current state of the data and schema (if applicable) available in the "normal" tables, as well as copy it to the backup.  For mySQL, we create backup tables denoted with ~~ before the table name. 
*/
function CreateDBSaveState($PARAMS = array())
{
	global $CONFIG, $TABLES, $DBOBJ;
	
	reload_tables();
	
} 

/*
I restore a copy of the entire working database (creted with CreateDBSaveState). I will erase any modifications (D's,A's,V's, or E's) since the backup state was created.  For mySQL, we look for any tables with the ~~ name indicating that they are a backup.  We then drop the existing table, and rename the backup. 
*/
function RestoreDBSveState($PARAMS = array())
{
	global $CONFIG, $TABLES, $DBOBJ;
	
	reload_tables();
}

?>