<?php
/***********************************************
DAVE PHP API
https://github.com/evantahler/PHP-DAVE-API
Evan Tahler | 2011

I check an API Action
***********************************************/
require_once("../spec_helper.php");
$T = new DaveTest("Describe Actions");

$PostArray = array(
	"OutputType" => "PHP",
	"Action" => "DescribeActions",
	"LimitLockPass" => $CONFIG['CorrectLimitLockPass']
);

$APIDATA = $T->api_request($PostArray);

$ExpectedActions = array(
	"DescribeActions", 
	"DescribeTables", 
	"ObjectTest", 
	"SlowAction", 
	"CacheTest", 
	"UserAdd", 
	"UserView", 
	"UserEdit", 
	"UserEdit", 
	"LogIn"
);

$T->context("There should be at least one Action returned");
	$T->assert(">",count($APIDATA["Actions"]),0);
	$Actions = array();
	foreach($APIDATA["Actions"] as $Action)
	{
		$Actions[] = $Action["Name"];
	}

$T->context("Certain Actions should be in the server results");
	foreach($ExpectedActions as $Action)
	{
		$T->assert("in_array",$Action,$Actions);
	}


$T->end();

?>