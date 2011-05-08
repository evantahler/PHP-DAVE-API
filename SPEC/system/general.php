<?php
/***********************************************
DAVE PHP API
https://github.com/evantahler/PHP-DAVE-API
Evan Tahler | 2011

I check general API functionality
***********************************************/
require_once("../spec_helper.php");
$T = new DaveTest("General API Tests");

$T->context("APIRequestsRemaining should decrement on subsequent loads");
	$PostArray = array("OutputType" => "PHP");
	$APIDATA = $T->api_request($PostArray);
	$first = $APIDATA["APIRequestsRemaining"];
	$T->assert(">",$first,0);
	$APIDATA = $T->api_request($PostArray);
	$second = $APIDATA["APIRequestsRemaining"];
	$T->assert(">",$second,0);
	$T->assert("<",$second,$first);

$T->context("computation time should be > 0s but less than 10s for rendering no Action");
	$PostArray = array("OutputType" => "PHP", "LimitLockPass" => $CONFIG['CorrectLimitLockPass']);
	$APIDATA = $T->api_request($PostArray);
	$ComputationTime = $APIDATA["ComputationTime"];
	$T->assert(">",$ComputationTime,0);
	$T->assert("<",$ComputationTime,10);

$T->context("I should have an IP address");
	$PostArray = array("OutputType" => "PHP", "LimitLockPass" => $CONFIG['CorrectLimitLockPass']);
	$APIDATA = $T->api_request($PostArray);
	$IP = $APIDATA["IP"];
	$T->assert("==",$IP,"localhost");

$T->context("The sever should have an IP address and a ServerName");
	$PostArray = array("OutputType" => "PHP", "LimitLockPass" => $CONFIG['CorrectLimitLockPass']);
	$APIDATA = $T->api_request($PostArray);
	$ServerAddress = $APIDATA["ServerAddress"];
	$T->assert(">",strlen($APIDATA["ServerAddress"]),0);
	$T->assert(">",strlen($APIDATA["ServerName"]),0);
	
$T->context("The name of the Action should be returned if a correct action is passed, and not otherwise");
	$PostArray = array("OutputType" => "PHP", "Action" => "DescribeActions", "LimitLockPass" => $CONFIG['CorrectLimitLockPass']);
	$APIDATA = $T->api_request($PostArray);
	$T->assert("==",$APIDATA["Action"],"DescribeActions");
	$PostArray = array("OutputType" => "PHP", "Action" => "NOT_AN_ACTION", "LimitLockPass" => $CONFIG['CorrectLimitLockPass']);
	$APIDATA = $T->api_request($PostArray);
	$T->assert("==",$APIDATA["Action"],"Unknown Action");

$T->context("Only meaningful PARAMS that are passed should be returned");
	$PostArray = array("OutputType" => "PHP", "Action" => "TheAction", "ADumbParam" => "Dumb", "LimitLockPass" => $CONFIG['CorrectLimitLockPass']);
	$APIDATA = $T->api_request($PostArray);
	$Params = $APIDATA["Params"];
	$T->assert("in_array",'TheAction',$Params);
	$T->assert("not_in_array",'Dumb',$Params);


$T->end();

?>