<?php
/***********************************************
DAVE PHP API
https://github.com/evantahler/PHP-DAVE-API
Evan Tahler | 2011

I save a Message to a lobby identified by a LobbyKey
***********************************************/

if ($ERROR == 100)
{
	if (empty($PARAMS["LobbyKey"])){ $ERROR = "Please Provide a LobbyKey"; }
}
if ($ERROR == 100)
{
	if (empty($PARAMS["Speaker"])){ $ERROR = "Please Provide a Speaker.  Who are you?"; }
}
if ($ERROR == 100)
{
	if (empty($PARAMS["Message"])){ $ERROR = "Please Provide a Message"; }
}
if ($ERROR == 100)
{
	$resp = _VIEW("lobbies",array("LobbyKey" => $PARAMS['LobbyKey']));
	if ($resp[0] == false){$ERROR = $resp[1];}
	if (count($resp[1]) != 1){ $ERROR = "That Lobby cannot be found"; }
	else
	{		
		$LobbyID = $resp[1][0]["LobbyID"];
	}
}
if ($ERROR == 100)
{
	$resp = _ADD("messages", array(
		"LobbyID" => $LobbyID,
		"Speaker" => $PARAMS["Speaker"],
		"Message" => $PARAMS["Message"],
	));
	$OUTPUT["MessageID"] = $resp["1"]["MessageID"];
}

?>