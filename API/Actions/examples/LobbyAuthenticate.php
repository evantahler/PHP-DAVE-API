<?php
/***********************************************
DAVE PHP API
https://github.com/evantahler/PHP-DAVE-API
Evan Tahler | 2011

I return true or false letting a user know if they have the right LobbyName and LobbyKey.  I return the LobbyID as well
***********************************************/

if ($ERROR == 100)
{
	if (empty($PARAMS["LobbyName"])){ $ERROR = "Please Provide a LobbyName"; }
}
if ($ERROR == 100)
{
	if (empty($PARAMS["LobbyKey"])){ $ERROR = "Please Provide a LobbyKey"; }
}

if ($ERROR == 100)
{
	$resp = _VIEW("lobbies",array("LobbyName" => $PARAMS['LobbyName'], "LobbyKey" => $PARAMS["LobbyKey"]));
	if ($resp[0] == false){$ERROR = $resp[1];}
	if (count($resp[1]) == 0){ $OUTPUT["LobbyAuthentication"] = "FALSE"; }
	else
	{		
		$OUTPUT["LobbyDetails"] = $resp[1][0];
		$OUTPUT["LobbyAuthentication"] = "TRUE";
	}
}

?>