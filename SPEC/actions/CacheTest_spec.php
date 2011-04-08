<?php
/***********************************************
DAVE PHP API
https://github.com/evantahler/PHP-DAVE-API
Evan Tahler | 2011

I check an API Action
***********************************************/
require_once("../spec_helper.php");
$T = new DaveTest("Cache Test");

$T->context("The Action will error properly when no Hash is provided");
	$PostArray = array(
		"OutputType" => "PHP",
		"Action" => "CacheTest",
		"LimitLockPass" => $CONFIG['CorrectLimitLockPass']
	);

	$APIRequest = new APIRequest($TestURL, $PostArray);
	$APIDATA = $APIRequest->DoRequest();
	$T->assert("==",$APIDATA["ERROR"],"You need to provide a Hash");
	
$T->context("The Action will store the provided value in the cache");
	$PostArray = array(
		"OutputType" => "PHP",
		"Action" => "CacheTest",
		"hash" => "abc123",
		"LimitLockPass" => $CONFIG['CorrectLimitLockPass']
	);

	$APIRequest = new APIRequest($TestURL, $PostArray);
	$APIDATA = $APIRequest->DoRequest();
	$T->assert("==",$APIDATA["ERROR"],"OK");
	$T->assert("==",$APIDATA["CachedResult"],"abc123");


$T->end();

?>