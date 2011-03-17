<?php

/***********************************************
Evan Tahler
evantahler@gmail.com
2010

Set this page to be fired off every minute by your cron process, and then put the logic inside this page.
The output of this page will be added to the CRON_LOG.txt file

***********************************************/
// Cron example: */1 * * * * /usr/bin/php /path/to/CRON.php > /path/to/CRON_LOG.txt

// setup
require("CONFIG.php");
require("ConnectToDatabase.php");
require("DAVE.php");
require("CACHE.php");
require("CommonFunctions.php");
date_default_timezone_set($systemTimeZone);

$CRON_OUTPUT = "";

$CRON_OUTPUT .= date("m-d-Y H:i:s")." \r\n";

/////////////////////////////////////////////////////////////////////////
// Check the CACHE DB table for old entries, and remove them
if($CacheType == "DB")
{
	$SQL= 'DELETE FROM `'.$DB.'`.`'.$CacheTable.'` WHERE (`ExpireTime` < "'.(time() - $CacheTime).'") ;';
	$DBObj = new DBConnection();
	$Status = $DBObj->GetStatus();
	if ($Status === true)
	{
		$DBObj->Query($SQL);
		$CRON_OUTPUT .= 'Deleted '.$DBObj->NumRowsEffected()." entries from the CACHE DB. \r\n";
	}
	$DBObj->close();
}
/////////////////////////////////////////////////////////////////////////
// Check the CACHE Folder table for old entries, and remove them
if($CacheType == "FlatFile")
{
	$files = scandir($CacheFolder);
	$counter = 0;
	foreach ($files as $num => $fname)
	{
		$ThisFile = $CacheFolder.$fname;
		if (file_exists($ThisFile) && ((time() - filemtime($ThisFile)) > $CacheTime) && $fname != "." && $fname != ".." && $fname != ".svn") 
		{
			unlink($ThisFile);
			$counter++;
		}
	}
	$CRON_OUTPUT .= 'Deleted '.$counter." files from the CACHE direcotry. \r\n";
}

/////////////////////////////////////////////////////////////////////////
// Clear the LOG of old LOG entries, acording to $LogAge
$SQL= 'DELETE FROM `'.$LogTable.'` WHERE (`TimeStamp` < "'.date('Y-m-d H:i:s',(time() - $LogAge)).'") ;'; 	
$DBObj = new DBConnection();
$Status = $DBObj->GetStatus();
if ($Status === true)
{
	$DBObj->Query($SQL);
	$CRON_OUTPUT .= 'Deleted '.$DBObj->NumRowsEffected()." entries from the LOG. \r\n";
}
$DBObj->close();

/////////////////////////////////////////////////////////////////////////
// Clear the LOG of old LOG entries, acording to $SessionAge
$SQL= 'DELETE FROM `SESSIONS` WHERE (`created_at` < "'.date('Y-m-d H:i:s',(time() - $SessionAge)).'") ;'; 	
$DBObj = new DBConnection();
$Status = $DBObj->GetStatus();
if ($Status === true)
{
	$DBObj->Query($SQL);
	$CRON_OUTPUT .= 'Deleted '.$DBObj->NumRowsEffected()." expired Sessions. \r\n";
}
$DBObj->close();

/////////////////////////////////////////////////////////////////////////
// Delete Big Log Files, list set in CONFIG
clearstatcache();
$i = 0;
while ($i < count($LogsToCheck))
{
	if (filesize($LogsToCheck[$i]) > $MaxLogFileSize)
	{
		$CRON_OUTPUT .= 'Log: '.$LogsToCheck[$i].'is too big, killing'."\r\n";
		unlink($LogsToCheck[$i]);
		$fh = fopen($LogsToCheck[$i], 'w');
		fclose($fh);
		chmod($Logs[$i], 0777);
	}
	$i++;
}

/////////////////////////////////////////////////////////////////////////
// Do something else.....


/////////////////////////////////////////////////////////////////////////
// End the log output
$CRON_OUTPUT .= "\r\n\r\n";
echo $CRON_OUTPUT;
$fh = fopen($App_dir.$CronLogFile, 'a');
fwrite($fh, $CRON_OUTPUT);
fclose($fh);

exit;
?>