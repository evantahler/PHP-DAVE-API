<?php
/***********************************************
DAVE PHP API
https://github.com/evantahler/PHP-DAVE-API
Evan Tahler | 2011

I am an example function to view a user.
If "this" user is viewing (indicated by propper password hash along with another key, all data is shown), otherwise, just basic info is returned.
I contain example useage of the session functions
***********************************************/
if ($ERROR == 100)
{
	$AuthResp = AuthenticateUser();
	if ($AuthResp[0] !== true)
	{
		$ERROR = $AuthResp[1];
		$OUTPUT['LOGIN'] = "FALSE";
	}
	else
	{
		$ReturnedUser = $AuthResp[1];
		
		$OUTPUT['LOGIN'] = "TRUE";
		$OUTPUT['SessionKey'] = create_session();
		$SessionData = array();
		$SessionData["login_time"] = time();
		$userData = $ReturnedUser;
		foreach ($userData as $k => $v)
		{
			$SessionData[$k] = $v;
		}
		update_session($OUTPUT['SessionKey'], $SessionData);
		$OUTPUT['SESSION'] = get_session_data($OUTPUT['SessionKey']);
	}
}


?>