<?php
/***********************************************
DAVE PHP API
https://github.com/evantahler/PHP-DAVE-API
Evan Tahler | 2011

I will create a new lobby for folks to chat within
***********************************************/
if ($ERROR == 100)
{
	$LobbyKey = md5($PARAMS['LobbyName'].time().rand());
}
if ($ERROR == 100)
{
	$resp = _ADD("lobbies",array("LobbyName" => $PARAMS['LobbyName'], "LobbyKey" => $LobbyKey));
	if ($resp[0] == false){$ERROR = $resp[1];}
	else
	{
		$LobbyID = $resp[1]["LobbyID"];
		$details = _VIEW("lobbies",array("LobbyID" => $LobbyID));
		$OUTPUT["LobbyID"] = $LobbyID;
		$OUTPUT["LobbyName"] = $details[1][0]["LobbyName"];
		$OUTPUT["LobbyKey"] = $details[1][0]["LobbyKey"];
	}
}

?>