<?php
/***********************************************
DAVE PHP API
https://github.com/evantahler/PHP-DAVE-API
Evan Tahler | 2011

I ensure that the various output types of the daveAPI work
***********************************************/
require("../spec_helper.php");
$T = new DaveTest("Output Tests");

// PHP
$PostArray = array("OutputType" => "PHP");
$APIRequest = new APIRequest($PublicURL, $PostArray);
$APIDATA = $APIRequest->DoRequest();
$T->assert(">",count($APIDATA),0);
$T->assert(">",strlen($APIDATA["ERROR"]),0);

// JSON
$PostArray = array("OutputType" => "JSON");
$APIRequest = new APIRequest($PublicURL, $PostArray);
$APIRequest->DoRequest();
$JSON_resp = json_decode($APIRequest->ShowRawResponse(), true);
$T->assert(">",count($JSON_resp),0);
$T->assert(">",strlen($JSON_resp["ERROR"]),0);

// XML
$PostArray = array("OutputType" => "XML");
$APIRequest = new APIRequest($PublicURL, $PostArray);
$APIRequest->DoRequest();
$XML_resp = simplexml_load_string($APIRequest->ShowRawResponse());
$T->assert(">",count($XML_resp),0);
$T->assert(">",strlen($XML_resp->ERROR),0);

$T->end();

?>