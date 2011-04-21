<?php
/***********************************************
DAVE PHP API
https://github.com/evantahler/PHP-DAVE-API
Evan Tahler | 2011

I am an example function to Delete a user
***********************************************/
if ($ERROR == 100)
{
	// look up the user info
	list($pass,$result) = _VIEW("users");
	if (!$pass){ $ERROR = $result; }
}
if ($ERROR == 100)
{
	if (count($result) == 1)
	{
		// convert supplied password to PasswordHash if set
		if (!empty($PARAMS["Password"])){ $PARAMS["PasswordHash"] = md5($PARAMS["Password"].$result[0]['Salt']); }
		
		if ($PARAMS["PasswordHash"] == $result[0]['PasswordHash']) // THIS user
		{
			_DELETE("users", $PARAMS);
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