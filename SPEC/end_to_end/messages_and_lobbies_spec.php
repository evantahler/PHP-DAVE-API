<?php
/***********************************************
DAVE PHP API
https://github.com/evantahler/PHP-DAVE-API
Evan Tahler | 2011

I check a combination of API actions
***********************************************/
require_once("../spec_helper.php");

$T = new DaveTest("End to End Test: Messages and Lobbies");

// set some random values to ensure that this user doesn't exist already
$TestValues = array(
	"LobbyName" => rand().time()."_name",
);

$T->context("I should be able to create a lobby");
	$PostArray = array(
		"OutputType" => "PHP",
		"Action" => "LobbyAdd",
		"LobbyName" => $TestValues["LobbyName"]
	);

	$APIDATA = $T->api_request($PostArray);
	$T->assert("==",$APIDATA["ERROR"],"OK");
	$T->assert("==",$APIDATA["LobbyName"],$TestValues["LobbyName"]);
	$T->assert("==",strlen($APIDATA["LobbyKey"]),32);
	$TestValues["LobbyKey"] = $APIDATA["LobbyKey"]; // save for later tests

$T->context("I should be able to list lobbies, and my new lobby should be included");
	$PostArray = array(
		"OutputType" => "PHP",
		"Action" => "LobbyView",
	);

	$APIDATA = $T->api_request($PostArray);
	$T->assert("==",$APIDATA["ERROR"],"OK");
	$found = false;
	foreach($APIDATA["Lobbies"] as $Lobby)
	{
		if ($Lobby["LobbyName"] == $TestValues["LobbyName"]){$found = true;}
	}
	$T->assert("true",$found);
	
$T->context("I should be able to authenticate to my new lobby");
	$PostArray = array(
		"OutputType" => "PHP",
		"Action" => "LobbyAuthenticate",
		"LobbyName" => $TestValues["LobbyName"],
		"LobbyKey" => $TestValues["LobbyKey"],
	);

	$APIDATA = $T->api_request($PostArray);
	$T->assert("==",$APIDATA["ERROR"],"OK");
	$T->assert("==",$APIDATA["LobbyAuthentication"],"TRUE");
	
$T->context("I should be able to post a message to my lobby");
	$PostArray = array(
		"OutputType" => "PHP",
		"Action" => "MessageAdd",
		"Message" => "A test message",
		"Speaker" => "DemoTestMan",
		"LobbyKey" => $TestValues["LobbyKey"],
	);

	$APIDATA = $T->api_request($PostArray);
	$T->assert("==",$APIDATA["ERROR"],"OK");
	$T->assert(">=",$APIDATA["MessageID"],1);	
	
$T->context("I should be able to see messages posted to the lobby");
	$PostArray = array(
		"OutputType" => "PHP",
		"Action" => "MessageView",
		"LobbyKey" => $TestValues["LobbyKey"],
	);

	$APIDATA = $T->api_request($PostArray);
	$T->assert("==",$APIDATA["ERROR"],"OK");
	$found = false;
	foreach($APIDATA["Messages"] as $Message)
	{
		if ($Message["Message"] == "A test message"){$found = true;}
	}
	$T->assert("true",$found);
	
$T->end();
?>