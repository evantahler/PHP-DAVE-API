<?php
/***********************************************
DAVE PHP API
https://github.com/evantahler/PHP-DAVE-API
Evan Tahler | 2011

I will run all tests within these spec folders.  Add to your folder list
***********************************************/

$TEST_SUITE_RESULTS = array();
$TEST_SUITE_RESULTS["StartTime"] = time();

///////////////////////////////////////////////////////////

$sub_folders = array(); // list of folders
$path = substr(__FILE__.$folder,0,(strlen(__FILE__) - strlen("TEST.php")));
chdir($path);
$dir = scandir(".");
foreach($dir as $path)
{
	$bad_paths = array("LOG", ".", "..");
	if (!in_array($path,$bad_paths) && strpos($path,".") === false)
	{
		$sub_folders[] = $path."/";
	}
}

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
$T->log(($TEST_SUITE_RESULTS["Successes"] + $TEST_SUITE_RESULTS["Failures"])." total tests in ".(time() - $TEST_SUITE_RESULTS["StartTime"])." seconds");
$T->log($TEST_SUITE_RESULTS["Successes"]." passing tests");
$T->log($TEST_SUITE_RESULTS["Failures"]." failing tests");

?>