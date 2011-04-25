<?php
/***********************************************
DAVE PHP API
https://github.com/evantahler/PHP-DAVE-API
Evan Tahler | 2011

I load in the enviorment.
***********************************************/

ini_set("display_errors","1");
error_reporting (E_ALL ^ E_NOTICE);

// $parts = explode("/",__FILE__);
// $ThisFile = $parts[count($parts) - 1];
// chdir(substr(__FILE__,0,(strlen(__FILE__) - strlen($ThisFile))));

if (file_exists("CONFIG.php")) 
{
	require_once("CONFIG.php"); 
	require_once("CACHE.php");
	require_once("CommonFunctions.php");
	require_once("GetPostVars.php");
	require_once("Actions.php");
	
	date_default_timezone_set($CONFIG['SystemTimeZone']);
}
else 
{
	echo "Please create CONFIG.php from CONFIG.php.example\r\n"; 
	exit;
}

?>