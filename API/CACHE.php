<?php

/***********************************************
DAVE PHP API
https://github.com/evantahler/PHP-DAVE-API
Evan Tahler | 2011

I define the types of cachign allowed, and how to deal with them.
Note: Allow CRON to deal with the removal of old entries for flat files and within the DB, so that these acessors can work faster

All modes should define the SetCache() and GetCache() functions for use in later functions.  Return false if the cached item cannot be fount for GetCache()

***********************************************/

if($CONFIG['CacheType'] == "MemCache")
{
	// start memcache if memcache is on
	$memcache = new Memcache;
	$memcache->connect($CONFIG['MemCacheHost'], 11211);
	
	function SetCache($Key, $Value, $ThisCacheTime = null)
	{
		global $CONFIG;
		if ($ThisCacheTime == null) { $ThisCacheTime = $CONFIG['CacheTime']; }
		
		$memcache->set($Key, $Value, false, $ThisCacheTime);
	}
	
	function GetCache($Key)
	{
		$memcache_result = $memcache->get($Key);
		return $memcache_result;
	}
}

/***********************************************/

elseif($CONFIG['CacheType'] == "DB")
{	
	function SetCache($Key, $Value, $ThisCacheTime = null)
	{
		global $CONFIG, $DBObj;
		if ($ThisCacheTime == null) { $ThisCacheTime = $CONFIG['CacheTime']; }
		
		$ExpireTime = time() + $ThisCacheTime;
				
		$Status = $DBObj->GetStatus();
		if ($Status === true)
		{
			$Connection = $DBObj->GetConnection();
			$SQL = 'INSERT INTO `'.$CONFIG['CacheTable'].'` (`Key`, `Value`, `ExpireTime`) VALUES ("'.mysql_real_escape_string($Key,$Connection).'", "'.mysql_real_escape_string(serialize($Value),$Connection).'", "'.mysql_real_escape_string($ExpireTime,$Connection).'");' ;
			$DBObj->Query($SQL);
			$Status = $DBObj->GetStatus();
			if ($Status === true){return true;}
			else { return false; }
		}
		else { return false; } 
	}
	
	function GetCache($Key)
	{	
		global $CONFIG, $DBObj;
				
		$Status = $DBObj->GetStatus();
		if ($Status === true)
		{
			$Connection = $DBObj->GetConnection();
			$SQL = 'SELECT `Value` FROM `'.$CONFIG['CacheTable'].'` WHERE (`Key` = "'.mysql_real_escape_string($Key,$Connection).'" AND `ExpireTime` >= "'.mysql_real_escape_string(time(),$Connection).'") LIMIT 1;' ;
			$DBObj->Query($SQL);
			$Status = $DBObj->GetStatus();
			if ($Status === true){
				$Results = $DBObj->GetResults();
				return unserialize($Results[0]['Value']);
			}
			else { return false; }
		}
		else { return false; } 
	}
}

/***********************************************/

elseif($CONFIG['CacheType'] == "FlatFile")
{
	function SetCache($Key, $Value, $ThisCacheTime = null)
	{
		global $CONFIG;
		if ($ThisCacheTime == null) { $ThisCacheTime = $CONFIG['CacheTime']; }
		
		$COUNTAINER = array((time() + $ThisCacheTime),$Value);
		$TheFile = $CONFIG['CacheFolder'].$Key.".cache";
		$fh = fopen($TheFile, 'w') or die("can't open cache file for write");
		fwrite($fh, serialize($COUNTAINER));
		fclose($fh);
		chmod($TheFile,0777);
		
		return true;
	}
	
	function GetCache($Key)
	{
		global $CONFIG;
		clearstatcache();
		$TheFile = $CONFIG['CacheFolder'].$Key.".cache";
		if (!file_exists($TheFile))
		{
			return false;
		}
		else
		{
			$fh = fopen($TheFile, 'r');
			$theData = fread($fh, filesize($TheFile));
			fclose($fh);
			$Result = unserialize($theData);
			if ($Result[0] < time())
			{
				unlink($TheFile);
				return false;
			}
			else
			{
				return $Result[1];
			}
		}
	}
}

/***********************************************/

else
{	
	function SetCache($Key, $Value, $ThisCacheTime = null)
	{
		global $CONFIG;
		if ($ThisCacheTime == null) { $ThisCacheTime = $CONFIG['CacheTime']; }
		
		return true;
	}
	
	function GetCache($Key)
	{
		return false;
	}
}

?>