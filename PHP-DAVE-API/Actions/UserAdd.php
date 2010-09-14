<?php

/***********************************************
Evan Tahler
evantahler@gmail.com
2010

I am an example function to Add a user
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
	list($fun_out, $PhoneNumber) = validate_PhoneNumber($PhoneNumber); // I may update the PhoneNumber variable with some formatting.
	if ($func_out != 100){ $ERROR = $func_out; }
}

if ($ERROR == 100)
{
	if (strlen($Password) > 0)
	{
		$Salt = md5(rand(1,999).(microtime()/rand(1,999)).rand(1,999));
		$PasswordHash = md5($Password.$Salt);
	}
	else
	{
		$ERROR = "Please provide a Password";
	}
}

// use the DAVE Add to take care of the actual DB checks and adding
if ($ERROR == 100)
{
	list($pass,$result) = _ADD("Users");
	if (!$pass)
	{
		$ERROR = $result; 
	}
	else
	{
		$OUTPUT[$TABLES['Users']['META']['KEY']] = $result[$TABLES['Users']['META']['KEY']];
	}
}


?>