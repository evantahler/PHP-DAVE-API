<?php
/***********************************************
DAVE PHP API
https://github.com/evantahler/PHP-DAVE-API
Evan Tahler | 2011

I am an example function to Delete a user
***********************************************/
if ($ERROR == 100)
{
	$AuthResp = AuthenticateUser();
	if ($AuthResp !== true)
	{
		$ERROR = $AuthResp;
	}
	else
	{
		$resp = _DELETE("users", array(
			"UserID" => $PARAMS['UserID'],
			"ScreenName" => $PARAMS['ScreenName'],
			"EMail" => $PARAMS['EMail']
			));
		if($resp[0] == false){$ERROR = $resp[1];}
	}
}


?>