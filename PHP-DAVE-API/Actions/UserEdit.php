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
	list($fun_out, $PARAMS["PhoneNumber"]) = validate_PhoneNumber($PARAMS["PhoneNumber"]);
	if ($func_out != 100){ $ERROR = $func_out; }
}

if ($ERROR == 100)
{
	// look up the user info
	list($pass,$result) = _VIEW("Users");
	if (!$pass){ $ERROR = $result; }
}
if ($ERROR == 100)
{
	if (count($result) == 1)
	{
		if ($PasswordHash == $result[0]['PasswordHash']) // THIS user
		{
			if(strlen($PARAMS["Password"]) > 0) // user is trying to change password
			{
				$Salt = md5(rand(1,999).(microtime()/rand(1,999)).rand(1,999));
				$PasswordHash = md5($PARAMS["Password"].$Salt);
			}
			if (count($result) == 1)
			{
				$UserData = $PARAMS;
				$UserData["PasswordHash"] = $PasswordHash;
				$UserData["Salt"] = $Salt;
				
				list($pass,$result) = _EDIT("Users", $UserData);
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
		else // another user
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