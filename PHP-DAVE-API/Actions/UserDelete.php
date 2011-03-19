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
	list($pass,$result) = _VIEW("Users");
	if (!$pass){ $ERROR = $result; }
}
if ($ERROR == 100)
{
	if (count($result) == 1)
	{
		if ($PasswordHash == $result[0]['PasswordHash']) // THIS user
		{
			_DELETE("Users");
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