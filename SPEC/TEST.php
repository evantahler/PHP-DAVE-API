<?php
/***********************************************
DAVE PHP API
https://github.com/evantahler/PHP-DAVE-API
Evan Tahler | 2011

I will run all tests within these spec folders.  Add to your folder list
***********************************************/

$__TEST_SUITE_RESULTS = array();
$__TEST_SUITE_RESULTS["StartTime"] = time();

///////////////////////////////////////////////////////////

$sub_folders = array();
$path = substr(__FILE__,0,(strlen(__FILE__) - strlen("TEST.php")));
chdir($path);
$dir = scandir(".");

function find_folders($path)
{
	global $sub_folders;
	$bad_paths = array("LOG", ".", "..");
	if (!in_array($path,$bad_paths) && strpos($path,".") === false)
	{
		$sub_folders[] = $path."/";
		$sub_sub_folders = scandir($path);
		foreach ($sub_sub_folders as $sub_sub){ find_folders($path."/".$sub_sub); }		
	}
}

foreach($dir as $path) { find_folders($path); }

foreach ($sub_folders as $folder)
{
	$path = substr(__FILE__.$folder,0,(strlen(__FILE__) - strlen("TEST.php")));
	chdir($path);
	foreach (glob($folder."*.php") as $filename) 
	{
		$parts = explode("/",$filename);
		$num_parts = count($parts);
		$just_name = $parts[($num_parts - 1)];
		$path = substr(__FILE__.$folder,0,(strlen(__FILE__) - strlen("TEST.php"))).$folder;
		chdir($path);
		require($filename);
	}
}

///////////////////////////////////////////////////////////

$T = new DaveTest("Test Suite Results");
$T->log("------------ TEST SUITE RESLTS ------------");
$T->log((($__TEST_SUITE_RESULTS["Successes"] + $__TEST_SUITE_RESULTS["Failures"]))." total tests in ".(time() - $__TEST_SUITE_RESULTS["StartTime"])." seconds");
if ($__TEST_SUITE_RESULTS["Successes"] > 0){ $T->log($__TEST_SUITE_RESULTS["Successes"]." passing tests"); }
else { $T->log("0 passing tests"); }

if ($__TEST_SUITE_RESULTS["Failures"] > 0){ $T->log($__TEST_SUITE_RESULTS["Failures"]." failing tests"); }
else { $T->log("0 failing tests"); }

?>