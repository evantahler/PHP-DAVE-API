<?php

/***********************************************
DAVE PHP API
https://github.com/evantahler/PHP-DAVE-API
Evan Tahler | 2011

I inspect the state of your mySQL database and build the array descirbing all the tables (MONGO DON'T DO COLS!).  I will also log this to a file for quicked access next time.  If /API/DB/SCHEMA.php doesn't exist, I'll create it for other parts of the API to use.

***********************************************/
// I AM NOT NEEDED FOR MONGO.  YOU NEED TO WRITE SCHEMA.php BY HAND

/*
// I do work for finding all collections in your mongoDB, not cols or attributes

$TABLES = array();
$ToReload = false;

if (!file_exists($CONFIG['TableConfigFile'])) {
	$ToReload = true;
}
else
{
	require_once($CONFIG['TableConfigFile']);
	if ($CONFIG['TableConfigRefreshTime'] > 0) 
	{
		if ($TableBuildTime + $CONFIG['TableConfigRefreshTime'] < time()) {
			$ToReload = true;
			$TABLES = array(); // clear it, just to be safe
		}
	}
}

if ($ToReload)
{
	$OUTPUT["TableRelaod"] = "true";
	$Status = $DBOBJ->GetStatus();
	if ($Status === true)
	{
		$MongoDB = $DBOBJ->GetMongoDB();
		$list = $MongoDB->listCollections();
		$TableList = array();
		foreach ($list as $sub)
		{
			$name = $sub->getName();
			if ($name != "cache" && $name != "log") { $TableList[] = $name; }
		}
	}
	$TABLES["TableBuildTime"] = time();
	@unlink($CONFIG['TableConfigFile']);
	$TableStringOutput = "";
	$TableStringOutput .= "<?php \r\n";
	
	// $TableStringOutput .= "// TABLE DESCRIPTION GENERATED AT ".date("Y-m-d H:i:s")."\r\n";
	// $TableStringOutput .= "// the KEY meta param is used to define which table is used to look up and edit information with.  This should be a unique column \r\n";
	// $TableStringOutput .= "// every column in a table (that you care to access) is defined as [[ array( ColName, Unique? (true or false), Required? (true or false)) ]].  Any and all unique variables will be used to sort/select SQL lookups \r\n\r\n";
	
	$TableStringOutput .= '$TableBuildTime = "'.$TABLES['TableBuildTime']."\"; \r\n\r\n";
	foreach ($TableList as $ThisTable)
	{
		$TableStringOutput .= '$TABLES["'.$ThisTable.'"]["META"]["KEY"] = "'."_ID"."\"; \r\n";
		$TableStringOutput .= "\r\n";
	}
	$TableStringOutput .= "// END \r\n";
	$TableStringOutput .= "?>";
	
	if (is_dir($CONFIG['App_dir']))
	{
		$fh = fopen($CONFIG['TableConfigFile'], 'w');
		fwrite($fh, $TableStringOutput);
		fclose($fh);
		chmod($CONFIG['TableConfigFile'],0777);
	}
	else
	{
		$ERROR = "CONFIGURATION ERROR: ".$CONFIG['App_dir']." doesn't seem to be a directory.  Please check your CONFIG.";
	}
	
	unset($TABLES["TableBuildTime"]);
}
else
{
	$OUTPUT["TableRelaod"] = "false";
}
*/

?>