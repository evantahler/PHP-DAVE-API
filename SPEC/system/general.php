<?php
/***********************************************
DAVE PHP API
https://github.com/evantahler/PHP-DAVE-API
Evan Tahler | 2011

I check general API functionality
***********************************************/
require("../spec_helper.php");
$T = new DaveTest("Output Tests");

// api_requests_remaining should decrement on subsequent loads
$PostArray = array("OutputType" => "PHP");
$APIRequest = new APIRequest($TestURL, $PostArray);
$APIDATA = $APIRequest->DoRequest();
$first = $APIDATA["api_requests_remaining"];
$T->assert(">",$first,0);
$APIRequest = new APIRequest($TestURL, $PostArray);
$APIDATA = $APIRequest->DoRequest();
$second = $APIDATA["api_requests_remaining"];
$T->assert(">",$second,0);
$T->assert("<",$second,$first);

// computation time should be > 0 but less than 10 seconds
$PostArray = array("OutputType" => "PHP", "LimitLockPass" => $CONFIG['CorrectLimitLockPass']);
$APIRequest = new APIRequest($TestURL, $PostArray);
$APIDATA = $APIRequest->DoRequest();
$ComputationTime = $APIDATA["ComputationTime"];
$T->assert(">",$ComputationTime,0);
$T->assert("<",$ComputationTime,10);

//I should have an IP address
$PostArray = array("OutputType" => "PHP", "LimitLockPass" => $CONFIG['CorrectLimitLockPass']);
$APIRequest = new APIRequest($TestURL, $PostArray);
$APIDATA = $APIRequest->DoRequest();
$IP = $APIDATA["IP"];
$T->assert("true",IP_check($IP));

//The sever should have an IP address and a ServerName
$PostArray = array("OutputType" => "PHP", "LimitLockPass" => $CONFIG['CorrectLimitLockPass']);
$APIRequest = new APIRequest($TestURL, $PostArray);
$APIDATA = $APIRequest->DoRequest();
$ServerAddress = $APIDATA["IP"];
$T->assert("true",IP_check($ServerAddress));
$T->assert(">",strlen($APIDATA["ServerName"]),0);


$T->end();

function IP_check($IP)
{
	if (preg_match('/^(?:25[0-5]|2[0-4]\d|1\d\d|[1-9]\d|\d)(?:[.](?:25[0-5]|2[0-4]\d|1\d\d|[1-9]\d|\d)){3}$/',$IP)) { 
		return true;
	}
	else {return false;} 
}

?>