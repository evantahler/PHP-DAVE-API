<?php
/***********************************************
DAVE PHP API
https://github.com/evantahler/PHP-DAVE-API
Evan Tahler | 2011

I ensure that the various output types of the daveAPI work
***********************************************/
require_once("../spec_helper.php");
	$T = new DaveTest("Output Types");
	$T->context("The API should return various OutputTypes");

	$T->context("PHP",2);
		$PostArray = array("OutputType" => "PHP", "LimitLockPass" => $CONFIG['CorrectLimitLockPass']);
		$APIDATA = $T->api_request($PostArray);
		$T->assert(">",count($APIDATA),0);
		$T->assert("==",$APIDATA["ERROR"],"That Action cannot be found.  Did you send the 'Action' parameter?  List Actions with Action=DescribeActions");

	$T->context("JSON",2);
		$PostArray = array("OutputType" => "JSON", "LimitLockPass" => $CONFIG['CorrectLimitLockPass']);
		$APIDATA = $T->api_request($PostArray);
		$JSON_resp = json_decode($T->get_raw_api_respnse(), true);
		$T->assert(">",count($JSON_resp),0);
		$T->assert("==",$JSON_resp["ERROR"],"That Action cannot be found.  Did you send the 'Action' parameter?  List Actions with Action=DescribeActions");

	$T->context("XML",2);
		$PostArray = array("OutputType" => "XML", "LimitLockPass" => $CONFIG['CorrectLimitLockPass']);
		$APIDATA = $T->api_request($PostArray);
		$XML_resp = simplexml_load_string($T->get_raw_api_respnse());
		$T->assert(">",count($XML_resp),0);
		$T->assert("==",$XML_resp->ERROR,"That Action cannot be found.  Did you send the 'Action' parameter?  List Actions with Action=DescribeActions");
		
	$T->context("LINE",2); // CONSOLE and LINE are similar
		$PostArray = array("OutputType" => "LINE", "LimitLockPass" => $CONFIG['CorrectLimitLockPass']);
		$APIDATA = $T->api_request($PostArray);
		$Lines = explode("\r\n",$T->get_raw_api_respnse());
		$T->assert(">",count($Lines),0);
		$T->assert("==",$Lines[2],"Action: Unknown Action");
		$T->assert("==",$Lines[9],"ERROR: That Action cannot be found.  Did you send the 'Action' parameter?  List Actions with Action=DescribeActions");

$T->end();

?>