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
	$CacheKey = $UserID."_".$ScreenName."_".$EMail."_".$PhoneNumber."_"."_UserView";
	$result = GetCache($CacheKey);
	if ($result === false)
	{
		list($pass,$result) = _VIEW("Users");
		if (!$pass){ $ERROR = $result; }
		else{ SetCache($CacheKey,$result); $OUTPUT['CACHE'] = "FALSE"; }
	}
	else
	{
		$OUTPUT['CACHE'] = "TRUE"; 
	}
}

if ($ERROR == 100)
{
	if ($PasswordHash == $result[0]['PasswordHash']) // THIS user
	{
		if (count($result) == 1)
		{
			foreach( $result[0] as $key => $val)
			{
				$OUTPUT[$key] = $val;
			}
		}
		else
		{
			$ERROR = "That User cannot be found";
		}
	}
	else // another user
	{
		$OUTPUT['ScreenName'] = $result[0]['ScreenName'];
		$OUTPUT['Gender'] = $result[0]['Gender'];
		$OUTPUT['Joined'] = $result[0]['Joined'];
	}
}


?>