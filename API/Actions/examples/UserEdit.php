<?php
/***********************************************
DAVE PHP API
https://github.com/evantahler/PHP-DAVE-API
Evan Tahler | 2011

I am an example function to Edit a user
***********************************************/
// do some special input filtering
if (strlen($PARAMS["EMail"]) > 0 && $ERROR == 100)
{
	$func_out = validate_EMail($PARAMS["EMail"]);
	if ($func_out != 100){ $ERROR = $func_out; }
}

if ($ERROR == 100 && strlen($PARAMS["PhoneNumber"]) > 0)
{
	list($func_out, $PARAMS["PhoneNumber"]) = validate_PhoneNumber($PARAMS["PhoneNumber"]);
	if ($func_out != 100){ $ERROR = $func_out; }
}

if ($ERROR == 100)
{
	$AuthResp = AuthenticateUser();
	if ($AuthResp[0] !== true)
	{
		$ERROR = $AuthResp[1];
	}
	else
	{
		list($msg, $ReturnedUsers) = _VIEW("users",array(
			"UserID" => $PARAMS['UserID'],
			"ScreenName" => $PARAMS['ScreenName'],
			"EMail" => $PARAMS['EMail'],
		));
		if ($msg == false)
		{
			$ERROR = $ReturnedUsers;
		}
		elseif(count($ReturnedUsers) == 1)
		{
			$UserData = only_table_columns($PARAMS, "users");
			$UserData["PasswordHash"] = $ReturnedUsers[0]["PasswordHash"]; // no change
			if(strlen($PARAMS["NewPassword"]) > 0) // user is trying to change password
			{
				$UserData["PasswordHash"] = md5($PARAMS["NewPassword"].$ReturnedUsers[0]['Salt']);
			}
		
			list($pass,$result) = _EDIT("users", $UserData);
			if (!$pass){ $ERROR = $result; }
			elseif (count($result) == 1)
			{
				foreach( $result[0] as $key => $val)
				{
					$OUTPUT["User"][$key] = $val;
				}
			}
		}
	}
}

?>