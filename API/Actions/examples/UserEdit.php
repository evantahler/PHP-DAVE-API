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
	if ($AuthResp !== true)
	{
		$ERROR = $AuthResp;
	}
	else
	{
		$UserData = only_table_columns($PARAMS, "users");
		// convert supplied password to PasswordHash if set
		if (!empty($PARAMS["Password"])){ $PARAMS["PasswordHash"] = md5($PARAMS["Password"].$result[0]['Salt']); }
		if ($PARAMS["PasswordHash"] == $result[0]['PasswordHash']) // THIS user
		{
			if(strlen($PARAMS["NewPassword"]) > 0) // user is trying to change password
			{
				$NewPasswordHash = md5($UserData["NewPassword"].$result[0]['Salt']);
			}
			else
			{
				$NewPasswordHash = $result[0]["PasswordHash"]; // no change
			}
			if (count($result) == 1)
			{
				$UserData["PasswordHash"] = $NewPasswordHash;
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
		else
		{
			$ERROR = "Passwords do not match or PasswordHash was not provided";
		}
	}
	else
	{
		$ERROR = "That user is not found";
	}
}

?>