<?php
/***********************************************
DAVE PHP API
https://github.com/evantahler/PHP-DAVE-API
Evan Tahler | 2011

Set this page to be fired off every minute by your cron process, and then put the logic inside this page.
The output of this page will be added to the CRON_LOG.txt file

***********************************************/
// Cron example: */1 * * * * /usr/bin/php /path/to/CRON.php > /path/to/CRON_LOG.txt

$parts = explode("/",__FILE__);
$ThisFile = $parts[count($parts) - 1];
chdir(substr(__FILE__,0,(strlen(__FILE__) - strlen($ThisFile))));
require_once("load_enviorment.php"); unset($parts); unset($ThisFile);

load_tasks();

$CRON_OUTPUT = "STARTING CRON @ ".date("m-d-Y H:i:s")."\r\n\r\n";

/////////////////////////////////////////////////////////////////////////
// Do Tasks

$CRON_OUTPUT .= run_task("CleanCache", $ARGS);
$CRON_OUTPUT .= run_task("CleanLog", $ARGS);
$CRON_OUTPUT .= run_task("CleanSessions", $ARGS);
$CRON_OUTPUT .= run_task("RemoveLargeLogs", $ARGS);

/////////////////////////////////////////////////////////////////////////
// End the log output
echo $CRON_OUTPUT;
$fh = fopen($CONFIG['CronLogFile'], 'a');
fwrite($fh, $CRON_OUTPUT);
fclose($fh);

exit;
?>