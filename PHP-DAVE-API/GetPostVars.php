<?php

/***********************************************
DAVE PHP API
https://github.com/evantahler/PHP-DAVE-API
Evan Tahler | 2011

This page will grab all the possible variables sent to the system and make them avaialbe for use.  This will also check certain variables for bad chars

I should be called uppon opperating SQL commands to look for special commands that would have been posted for empty sets and 0s

***********************************************/

function CleanInput($string,$Connection) 
{
	$string = mysql_real_escape_string($string,$Connection);
	$replace = "";
	
	$search = array(
	    '@<script[^>]*?>.*?</script>@si',   // Strip out javascript
	    '@<[\/\!]*?[^<>]*?>@si',            // Strip out HTML tags
	    '@<style[^>]*?>.*?</style>@siU',    // Strip style tags properly
	);
    $string = preg_replace($search, $replace, $string);
    $string = str_replace("<","(",$string);
	$string = str_replace(">",")",$string);
	$string = str_replace("¨"," ",$string);
    $string = iconv('UTF-8', 'ISO-8859-1//TRANSLIT//IGNORE', $string);
   	$string = htmlspecialchars($string, ENT_QUOTES);
   	$string = htmlentities($string, ENT_QUOTES);

    return $string;
}

if ($ERROR == 100)
{
	require ("CheckSafetyString.php");
}

// Use "REQUEST" so that both POST and GET will work, along with cookie data
if ($ERROR == 100)
{
	$i = 0;
	$DBObj = new DBConnection();
	$Connection = $DBObj->GetConnection();
	
	if ($Connection)
	{
		foreach($POST_VARIABLES as $var)
		{
			$$var = CleanInput($_REQUEST[$var],$Connection);
		}
				
		// Special Checks
		if ($Rand == "") { $Rand = CleanInput($_REQUEST['Rand'],$Connection); }
		if ($Rand == "") { $Rand = CleanInput($_REQUEST['rand'],$Connection); }
		if ($Hash == "") { $Hash = CleanInput($_REQUEST['Hash'],$Connection); }
		if ($Hash == "") { $Hash = CleanInput($_REQUEST['hash'],$Connection); }	
		if ($Hash == "" && $DeveloperID != "" && $Rand != "")
		{
			$Hash = md5($DeveloperID.$APIKey.$Rand);
		}
	}
	$DBObj->close();
}

?>