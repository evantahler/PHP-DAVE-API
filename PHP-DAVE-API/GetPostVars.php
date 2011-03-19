<?php

/***********************************************
DAVE PHP API
https://github.com/evantahler/PHP-DAVE-API
Evan Tahler | 2011

This page will grab all the possible variables sent to the system and make them avaialbe for use.  This will also check certain variables for bad chars

I should be called uppon opperating SQL commands to look for special commands that would have been posted for empty sets and 0s

***********************************************/

function CleanInput($string,$Connection=null) 
{
	if ($Connection){ $string = mysql_real_escape_string($string,$Connection); }
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

// Use "REQUEST" so that both POST and GET will work, along with cookie data
foreach($POST_VARIABLES as $var)
{
	$value = CleanInput($_REQUEST[$var],$Connection);
	if ($value) { 
		// $$var = $value;
		$PARAMS[$var] = $value; 
	}
}
		
// Special Checks
if ($PARAMS["Rand"] == "") { $PARAMS["Rand"] = CleanInput($_REQUEST['Rand'],$Connection); }
if ($PARAMS["Rand"] == "") { $PARAMS["Rand"] = CleanInput($_REQUEST['rand'],$Connection); }
if ($PARAMS["Hash"] == "") { $PARAMS["Hash"] = CleanInput($_REQUEST['Hash'],$Connection); }
if ($PARAMS["Hash"] == "") { $PARAMS["Hash"] = CleanInput($_REQUEST['hash'],$Connection); }	
if ($PARAMS["Hash"] == "" && $PARAMS["DeveloperID"] != "" && $PARAMS["Rand"] != "")
{
	$PARAMS["Hash"] = md5($PARAMS["DeveloperID"].$PARAMS["APIKey"].$PARAMS["Rand"]);
}

?>