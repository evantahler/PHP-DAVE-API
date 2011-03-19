<?php

/***********************************************
DAVE PHP API
https://github.com/evantahler/PHP-DAVE-API
Evan Tahler | 2011

I am a way to test that cahce functions are working
I'll store and return a user provided variable
***********************************************/

// I'll use HASH as the variable to log by IP address;
if ($ERROR == 100){ require("CheckSafetyString.php"); }

if ($ERROR == 100)
{
	if ($CacheType == "")
	{
		$ERROR = "The cache is not configured on this server";
	}
}

if ($ERROR == 100)
{
	if (strlen($Hash) == 0)
	{
		$ERROR = "You need to provide a Hash";
	}
	else
	{
		$CacheKey = $IP."_CacheTest";
		$result = GetCache($CacheKey);
		if ($result != false)
		{
			$OUTPUT['CacheAction'] = "Found in Cache";
			$OUTPUT['CachedResult'] = $result;
		}
		else
		{
			$OUTPUT['CacheAction'] = "Added to Cache";
			SetCache($CacheKey,$Hash);
			$result = GetCache($CacheKey);
			$OUTPUT['CachedResult'] = $result;
		}
	}
}

?>