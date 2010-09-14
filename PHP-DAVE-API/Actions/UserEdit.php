<?php

/***********************************************
Evan Tahler
evantahler@gmail.com
2010

I am an example function to Edit a user
***********************************************/

if ($ERROR == 100){ require("CheckSafetyString.php"); }

// do some special input filtering
if (strlen($EMail) > 0 && $ERROR == 100)
{
	$func_out = validate_EMail($EMail);
	if ($func_out != 100){ $ERROR = $func_out; }
}

if ($ERROR == 100 && strlen($PhoneNumber) > 0)
{
	list($fun_out, $PhoneNumber) = validate_PhoneNumber($PhoneNumber);  // I may update the PhoneNumber variable with some formatting.
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
			if(strlen($Password) > 0) // user is trying to change password
			{
				$Salt = md5(rand(1,999).(microtime()/rand(1,999)).rand(1,999));
				$PasswordHash = md5($Password.$Salt);
			}
			if (count($result) == 1)
			{
				list($pass,$result) = _EDIT("Users");
				if (!$pass){ $ERROR = $result; }
				elseif (count($result) == 1)
				{
					foreach( $result[0] as $key => $val)
					{
						$OUTPUT[$key] = $val;
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