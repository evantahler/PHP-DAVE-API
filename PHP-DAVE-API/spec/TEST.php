<?php
/***********************************************
DAVE PHP API
https://github.com/evantahler/PHP-DAVE-API
Evan Tahler | 2011

I will run all tests within these spec folders.  Add to your folder list
***********************************************/

$sub_folders = array();
$sub_folders[] = "system/";
$sub_folders[] = "actions/";

///////////////////////////////////////////////////////////

foreach ($sub_folders as $folder)
{
	$path = substr(__FILE__.$folder,0,(strlen(__FILE__) - strlen("TEST.php")));
	chdir($path);
	
	foreach (glob($folder."*.php") as $filename) 
	{
	    echo "\r\nTest File: $filename \r\n";
		$parts = explode("/",$filename);
		$num_parts = count($parts);
		$just_name = $parts[($num_parts - 1)];
		$path = substr(__FILE__.$folder,0,(strlen(__FILE__) - strlen("TEST.php"))).$folder;
		chdir($path);
		require($filename);
	}
}

?>