<?php
/***********************************************
DAVE PHP API
https://github.com/evantahler/PHP-DAVE-API
Evan Tahler | 2011

I am the task runner.

Tasks can be run with the following syntax: php API/TASK.php --task=name_of_task, or by instantitaiting the class directly
***********************************************/

$parts = explode("/",__FILE__);
$ThisFile = $parts[count($parts) - 1];
chdir(substr(__FILE__,0,(strlen(__FILE__) - strlen($ThisFile))));
require_once("load_enviorment.php"); unset($parts); unset($ThisFile);

require_once("helper_functions/parseArgs.php");

$ARGS = __parseArgs();

$TaskNames = load_tasks();

// help / List
if ($ARGS["h"] == true || $ARGS["help"] == true || $ARGS["l"] == true || $ARGS["list"] == true)
{
	echo "Task List:\r\n\r\n";
	
	$max_name_length = 0;
	foreach($TaskNames as $class_name)
	{
		if (strlen($class_name) > $max_name_length)
		{ 
			$max_name_length = strlen($class_name);
		}
	}
		
	foreach($TaskNames as $class_name)
	{
		echo "- ".$class_name::class_name();
		$i = strlen($class_name);
		while ($i < ($max_name_length + 4)) { echo " "; $i++; }
		echo $class_name::get_description();
		echo "\r\n";
	}
	exit;
}

// which task?
$ThisTask = ($ARGS["t"]);
if (strlen($ThisTask) == 0){ $ThisTask = ($ARGS["T"]); }
if (strlen($ThisTask) == 0){ $ThisTask = ($ARGS["task"]); }
if (strlen($ThisTask) == 0)
{
	echo "No task provided.  Please provide one with -t or --task.  Use --list to show available tasks.\r\n";
	exit;
}
else
{
	if (in_array(($ThisTask), $TaskNames))
	{
		echo run_task($ThisTask, $ARGS);
	}
	else
	{
		echo "That task cannot be found.  Use --list to show available tasks.\r\n";
		exit;	
	}
}


?>