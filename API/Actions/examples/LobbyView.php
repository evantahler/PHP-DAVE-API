<?php
/***********************************************
DAVE PHP API
https://github.com/evantahler/PHP-DAVE-API
Evan Tahler | 2011

I will list lobbies
***********************************************/
if ($ERROR == 100)
{
	$resp = _VIEW("lobbies",null,array("SQL_Override" => true, "sort" => "ORDER BY TimeStamp DESC"));
	if ($resp[0] == false){$ERROR = $resp[1];}
	else
	{
		foreach($resp[1] as $lobby)
		{
			$OUTPUT["Lobbies"][] = array(
				"LobbyID" => $lobby["LobbyID"],
				"LobbyName" => $lobby["LobbyName"],
				"TimeStamp" => $lobby["TimeStamp"]
			);
		}
	}
}

?>