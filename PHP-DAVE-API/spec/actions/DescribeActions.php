<?php
/***********************************************
DAVE PHP API
https://github.com/evantahler/PHP-DAVE-API
Evan Tahler | 2011

I check an API Action
***********************************************/
require("../spec_helper.php");
$T = new DaveTest("Output Tests");

// I should return an array of actions
$PostArray = array(
	"OutputType" => "PHP",
	"Action" => "DescribeActions",
	"LimitLockPass" => $CorrectLimitLockPass
);
$APIRequest = new APIRequest($PublicURL, $PostArray);
$APIDATA = $APIRequest->DoRequest();

$ExpectedActions = array(
	"DescribeActions", 
	"DescribeTables", 
	"ObjectTest", 
	"GeoCode", 
	"CacheTest", 
	"UserAdd", 
	"UserView", 
	"UserEdit", 
	"UserEdit", 
	"LogIn"
);
$T->assert(">",count($APIDATA["Actions"]),0);
$Actions = array();
foreach($APIDATA["Actions"] as $Action)
{
	$Actions[] = $Action["Name"];
}
foreach($ExpectedActions as $Action)
{
	$T->assert("in_array",$Action,$Actions);
}


$T->end();

?>