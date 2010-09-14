<?php

/***********************************************
Evan Tahler
evantahler@gmail.com
2010

I define the types of cachign allowed, and how to deal with them.
Note: Allow CRON to deal with the removal of old entries for flat files and within the DB, so that these acessors can work faster

All modes should define the SetCache() and GetCache() functions for use in later functions.  Return false if the cached item cannot be fount for GetCache()

***********************************************/

if($CacheType == "MemCache")
{
	// start memcache if memcache is on
	$memcache = new Memcache;
	$memcache->connect('localhost', 11211);
	
	function SetCache($Key, $Value, $ThisCacheTime = null)
	{
		global $CacheTime;
		if ($ThisCacheTime == null) { $ThisCacheTime = $CacheTime; }
		
		$memcache->set($Key, $Value, false, $ThisCacheTime);
	}
	
	function GetCache($Key)
	{
		$memcache_result = $memcache->get($Key);
		return $memcache_result;
	}
}

/***********************************************/

elseif($CacheType == "DB")
{	
	function SetCache($Key, $Value, $ThisCacheTime = null)
	{
		global $CacheTime, $CacheTable;
		if ($ThisCacheTime == null) { $ThisCacheTime = $CacheTime; }
		
		$ExpireTime = time() + $ThisCacheTime;
		$SQL = 'INSERT INTO `'.$CacheTable.'` (`Key`, `Value`, `ExpireTime`) VALUES ("'.mysql_real_escape_string($Key).'", "'.mysql_real_escape_string(serialize($Value)).'", "'.mysql_real_escape_string($ExpireTime).'");' ;
				
		$DBObj = new DBConnection();
		$Status = $DBObj->GetStatus();
		if ($Status === true)
		{
			$DBObj->Query($SQL);
			$Status = $DBObj->GetStatus();
			if ($Status === true){ $DBObj->close(); return true;}
			else { return false; }
		}
		else { $DBObj->close(); return false; } 
		$DBObj->close();
	}
	
	function GetCache($Key)
	{	
		global $CacheTime, $CacheTable;
		
		$SQL = 'SELECT `Value` FROM `'.$CacheTable.'` WHERE (`Key` = "'.mysql_real_escape_string($Key).'" AND `ExpireTime` >= "'.mysql_real_escape_string(time()).'") LIMIT 1;' ;
		
		$DBObj = new DBConnection();
		$Status = $DBObj->GetStatus();
		if ($Status === true)
		{
			$DBObj->Query($SQL);
			$Status = $DBObj->GetStatus();
			if ($Status === true){
				$Results = $DBObj->GetResults();
				$DBObj->close();
				return unserialize($Results[0]['Value']);
			}
			else { return false; }
		}
		else { $DBObj->close(); return false; } 
		$DBObj->close();
	}
}

/***********************************************/

elseif($CacheType == "FlatFile")
{
	function SetCache($Key, $Value, $ThisCacheTime = null)
	{
		global $CacheTime, $CacheFolder;
		if ($ThisCacheTime == null) { $ThisCacheTime = $CacheTime; }
		
		$COUNTAINER = array((time() + $ThisCacheTime),$Value);
		$TheFile = $CacheFolder.$Key.".cache";
		$fh = fopen($TheFile, 'w') or die("can't open cache file for write");
		fwrite($fh, serialize($COUNTAINER));
		fclose($fh);
		chmod($TheFile,0777);
		
		return true;
	}
	
	function GetCache($Key)
	{
		global $CacheFolder;
		clearstatcache();
		$TheFile = $CacheFolder.$Key.".cache";
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
		global $CacheTime;
		if ($ThisCacheTime == null) { $ThisCacheTime = $CacheTime; }
		
		return true;
	}
	
	function GetCache($Key)
	{
		return false;
	}
}

?>