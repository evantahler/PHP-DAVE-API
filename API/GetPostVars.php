<?php
/***********************************************
DAVE PHP API
https://github.com/evantahler/PHP-DAVE-API
Evan Tahler | 2011

This page will grab all the possible variables sent to the system and make them avaialbe for use.  This will also check certain variables for bad chars

I should be called uppon opperating SQL commands to look for special commands that would have been posted for empty sets and 0s
***********************************************/

// Variables that might not be in the TABLLES.  List any extra parameters your application might need
$POST_VARIABLES = array();

$POST_VARIABLES[] = "Action";
$POST_VARIABLES[] = "Rollback";
$POST_VARIABLES[] = "APIKey";
$POST_VARIABLES[] = "IP";
$POST_VARIABLES[] = "UpperLimit";
$POST_VARIABLES[] = "LowerLimit";
$POST_VARIABLES[] = "Date";
$POST_VARIABLES[] = "TimeStamp";
$POST_VARIABLES[] = "Rand";
$POST_VARIABLES[] = "Hash";
$POST_VARIABLES[] = "DeveloperID";
$POST_VARIABLES[] = "OutputType";
$POST_VARIABLES[] = "Callback";
$POST_VARIABLES[] = "LimitLockPass";
$POST_VARIABLES[] = "Password";
$POST_VARIABLES[] = "NewPassword";

// Add Table columns as POST_VARIABLES
$i = 0;
if (count($TABLES) > 0)
{
	$TableNames = array_keys($TABLES);
	while ($i < count($TABLES))
	{
		$j = 0;
		while ($j < count($TABLES[$TableNames[$i]]))
		{
			$POST_VARIABLES[] = $TABLES[$TableNames[$i]][$j][0];
			$j++;
		}
		$i++;
	}
}

// Use "REQUEST" so that both POST and GET will work, along with cookie data
foreach($POST_VARIABLES as $var)
{
	$value = _CleanPostVariableInput($_REQUEST[$var],$Connection);
	if ($value) { 
		// $$var = $value;
		$PARAMS[$var] = $value; 
	}
}
		
// Special Checks
if ($PARAMS["Rand"] == "") { $PARAMS["Rand"] = _CleanPostVariableInput($_REQUEST['Rand'],$Connection); }
if ($PARAMS["Rand"] == "") { $PARAMS["Rand"] = _CleanPostVariableInput($_REQUEST['rand'],$Connection); }
if ($PARAMS["Hash"] == "") { $PARAMS["Hash"] = _CleanPostVariableInput($_REQUEST['Hash'],$Connection); }
if ($PARAMS["Hash"] == "") { $PARAMS["Hash"] = _CleanPostVariableInput($_REQUEST['hash'],$Connection); }	
if ($PARAMS["Hash"] == "" && $PARAMS["DeveloperID"] != "" && $PARAMS["Rand"] != "")
{
	$PARAMS["Hash"] = md5($PARAMS["DeveloperID"].$PARAMS["APIKey"].$PARAMS["Rand"]);
}
if ($PARAMS["Rand"] == ""){unset($PARAMS["Rand"]);}
if ($PARAMS["Hash"] == ""){unset($PARAMS["Hash"]);}

// do a doubles check on the uniqueness of POST_VARIABLES 
$POST_VARIABLES = array_unique($POST_VARIABLES);

/////////////////////////////////////////////////////////////////////////////////////////////////

function _CleanPostVariableInput($string,$Connection=null) 
{
	if (is_resource($Connection) == true && get_resource_type($Connection) == "mysql link"){ $string = mysql_real_escape_string($string,$Connection); }
	else{ $string = addslashes($string); }
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

?>