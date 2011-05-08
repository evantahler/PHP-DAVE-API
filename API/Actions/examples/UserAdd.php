<?php
/***********************************************
DAVE PHP API
https://github.com/evantahler/PHP-DAVE-API
Evan Tahler | 2011

I am an example function to Add a user
***********************************************/
// do some special input filtering
if (strlen($PARAMS["EMail"]) > 0 && $ERROR == 100)
{
	$func_out = validate_EMail($PARAMS["EMail"]);
	if ($func_out != 100){ $ERROR = $func_out; }
}

if ($ERROR == 100 && strlen($PARAMS["PhoneNumber"]) > 0)
{
	list($fun_out, $PARAMS["PhoneNumber"]) = validate_PhoneNumber($PARAMS["PhoneNumber"]); // baisc format checking
	if ($func_out != 100){ $ERROR = $func_out; }
}

if ($ERROR == 100)
{
	if (strlen($PARAMS["Password"]) > 0)
	{
		$Salt = md5(rand(1,999).(microtime()/rand(1,999)).rand(1,999));
		$PasswordHash = md5($PARAMS["Password"].$Salt);
	}
	else
	{
		$ERROR = "Please provide a Password";
	}
}

// use the DAVE Add to take care of the actual DB checks and adding
if ($ERROR == 100)
{
	$UserData = array();
	foreach($PARAMS as $param=>$val)
	{
		if(in_array($param,_getAllTableCols("users"))) { $UserData[$param] = $val ;}
	}
	$UserData["PasswordHash"] = $PasswordHash;
	$UserData["Salt"] = $Salt;
	
	list($pass,$result) = _ADD("users", $UserData);
	if (!$pass)
	{
		$ERROR = $result; 
	}
	else
	{
		$OUTPUT[$TABLES['users']['META']['KEY']] = $result[$TABLES['users']['META']['KEY']];
	}
}


?>