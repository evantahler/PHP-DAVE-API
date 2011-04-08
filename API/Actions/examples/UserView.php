<?php
/***********************************************
DAVE PHP API
https://github.com/evantahler/PHP-DAVE-API
Evan Tahler | 2011

I am an example function to view a user.
If "this" user is viewing (indicated by propper password hash along with another key, all data is shown), otherwise, just basic info is returned
***********************************************/
if ($ERROR == 100)
{
	list($pass,$result) = _VIEW("Users");
	if (!$pass){ $ERROR = $result; }
}

if ($ERROR == 100)
{
	// convert supplied password to PasswordHash if set
	if (!empty($PARAMS["Password"])){ $PARAMS["PasswordHash"] = md5($PARAMS["Password"].$result[0]['Salt']); }
	
	if ($PARAMS["PasswordHash"] == $result[0]['PasswordHash']) // THIS user
	{
		if (count($result) == 1)
		{
			$OUTPUT["User"]['InformationType'] = "Private";
			foreach( $result[0] as $key => $val)
			{
				$OUTPUT["User"][$key] = $val;
			}
		}
		else
		{
			$ERROR = "That User cannot be found";
		}
	}
	elseif(!empty($PARAMS["Password"]) || !empty($PARAMS["PasswordHash"]))
	{
		$ERROR = "Passwords do not match or PasswordHash was not provided";
	}
	else // Public Data Request
	{
		$OUTPUT["User"]['InformationType'] = "Public";
		$OUTPUT["User"]['ScreenName'] = $result[0]['ScreenName'];
		$OUTPUT["User"]['Joined'] = $result[0]['Joined'];
	}
}


?>