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
	$SourceUserData = array();
	foreach($PARAMS as $param=>$val)
	{
		if(in_array($param,_getAllTableCols("users"))) { $SourceUserData[$param] = $val ;}
	}
	list($pass,$result) = _VIEW("users",$SourceUserData);
	if (!$pass){ $ERROR = $result; }
}

if ($ERROR == 100)
{
	if (strlen($PARAMS["Password"]) > 0)
	{
		$PARAMS["PasswordHash"] = md5($PARAMS["Password"].$result[0]['Salt']);
	}
	if ($PARAMS["PasswordHash"] == $result[0]['PasswordHash'] && count($result) == 1) // THIS user
	{
		$OUTPUT['LOGIN'] = "TRUE";
		$OUTPUT['SessionKey'] = create_session();
		$SessionData = array();
		$SessionData["login_time"] = time();
		$userData = $result[0];
		foreach ($userData as $k => $v)
		{
			$SessionData[$k] = $v;
		}
		update_session($OUTPUT['SessionKey'], $SessionData);
		$OUTPUT['SESSION'] = get_session_data($OUTPUT['SessionKey']);
	}
	else // another user
	{
		$OUTPUT['LOGIN'] = "FALSE";
		$ERROR = "Password MissMatch or user not found";
	}
}


?>