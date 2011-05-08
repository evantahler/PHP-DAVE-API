<?php
/***********************************************
DAVE PHP API
https://github.com/evantahler/PHP-DAVE-API
Evan Tahler | 2011

I will get the messages for a lobby defiend by a LobbyKey. I can accept some extra paramiters:
- Speaker
- UpperLimit
- LowerLimit

Newest messages first.
***********************************************/

if ($ERROR == 100)
{
	if (empty($PARAMS["LobbyKey"])){ $ERROR = "Please Provide a LobbyKey"; }
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
	$UpperLimit = $PARAMS["UpperLimit"];
	$LowerLimit = $PARAMS["LowerLimit"];
	if (empty($UpperLimit)){ $UpperLimit = 10; }
	if (empty($LowerLimit)){ $LowerLimit = 0; }
	
	// null values for some of these are OK
	$resp = _VIEW("messages", array(
		"LobbyID" => $LobbyID,
		"Speaker" => $PARAMS["Speaker"],
	),array(
		"SQL_Override" => true,
		"sort" => "ORDER BY Timestamp DESC",
		"UpperLimit" => $UpperLimit,
		"LowerLimit" => $LowerLimit
	));
	
	$OUTPUT["Messages"] = array();
	foreach($resp[1] as $message)
	{
		$OUTPUT["Messages"][] = $message;
	}
}

?>