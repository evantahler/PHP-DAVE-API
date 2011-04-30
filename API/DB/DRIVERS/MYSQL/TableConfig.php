<?php

/***********************************************
DAVE PHP API
https://github.com/evantahler/PHP-DAVE-API
Evan Tahler | 2011

I inspect the state of your mySQL database and build the array descirbing all the colums and thier attributes.  I will also log this to a file for quicked access next time.  If /API/DB/SCHEMA.php doesn't exist, I'll create it for other parts of the API to use.

***********************************************/

// Table Definitions
$TABLES = array();
$ToReload = false;

if (!file_exists($CONFIG['TableConfigFile'])) {
	$ToReload = true;
}
else
{
	require_once($CONFIG['TableConfigFile']);
	if ($CONFIG['TableConfigRefreshTime'] > 0) 
	{
		if ($TableBuildTime + $CONFIG['TableConfigRefreshTime'] < time()) {
			$ToReload = true;
			$TABLES = array(); // clear it, just to be safe
		}
	}
}

if ($ToReload)
{
	$OUTPUT["TableRelaod"] = "true";
	$Status = $DBOBJ->GetStatus();
	if ($Status === true)
	{
		$SQL= 'SHOW TABLES;';
		$DBOBJ->Query($SQL);
		$out = $DBOBJ->GetResults();
		$TableList = array();
		foreach ($out as $sub){
			$name = $sub["Tables_in_".$CONFIG['DB']];
			if ($name != "cache" && $name != "log") { $TableList[] = $name; }
		}
		foreach ($TableList as $ThisTable)
		{
			$SQL= 'DESCRIBE `'.$CONFIG['DB'].'`.`'.$ThisTable.'`;';
			$DBOBJ->Query($SQL);
			$Response = $DBOBJ->GetResults();
			foreach ($Response as $col)
			{
				if ($col['Key'] == "PRI")
				{
					$TABLES[$ThisTable]['META']['KEY'] = $col['Field'];
				}
				$is_unique = false;
				$is_required = false;
				if ($col['Key'] == "UNI" || $col['Key'] == "PRI") { $is_unique = true; }
				if ($col['Null'] == "NO") { $is_required = true; } 
				$TABLES[$ThisTable][] = array($col['Field'],$is_unique,$is_required);
			}
		}
	}
	$TABLES["TableBuildTime"] = time();
	@unlink($CONFIG['TableConfigFile']);
	$TableStringOutput = "";
	$TableStringOutput .= "<?php \r\n";
	
	// $TableStringOutput .= "// TABLE DESCRIPTION GENERATED AT ".date("Y-m-d H:i:s")."\r\n";
	// $TableStringOutput .= "// the KEY meta param is used to define which table is used to look up and edit information with.  This should be a unique column \r\n";
	// $TableStringOutput .= "// every column in a table (that you care to access) is defined as [[ array( ColName, Unique? (true or false), Required? (true or false)) ]].  Any and all unique variables will be used to sort/select SQL lookups \r\n\r\n";
	
	$TableStringOutput .= '$TableBuildTime = "'.$TABLES['TableBuildTime']."\"; \r\n\r\n";
	foreach ($TableList as $ThisTable)
	{
		if ($TABLES[$ThisTable]['META']['KEY'] != null)
		{
			$TableStringOutput .= '$TABLES["'.$ThisTable.'"]["META"]["KEY"] = '.$TABLES[$ThisTable]['META']['KEY']."; \r\n";
		}
		foreach($TABLES[$ThisTable] as $col)
		{
			if ($col["KEY"] == null)
			{
				if ($col[1] == 1){$col[1] = "true";}
				else {$col[1] = "false";}
				
				if ($col[2] == 1){$col[2] = "true";}
				else {$col[2] = "false";}
				
				$TableStringOutput .= '$TABLES["'.$ThisTable.'"][] = array("'.$col[0].'",'.$col[1].','.$col[2].')'. "; \r\n";
			}
		}
		$TableStringOutput .= "\r\n";
	}
	$TableStringOutput .= "// END \r\n";
	$TableStringOutput .= "?>";
	
	if (is_dir($CONFIG['App_dir']))
	{
		$fh = fopen($CONFIG['TableConfigFile'], 'w');
		fwrite($fh, $TableStringOutput);
		fclose($fh);
		chmod($CONFIG['TableConfigFile'],0777);
	}
	else
	{
		$ERROR = "CONFIGURATION ERROR: ".$CONFIG['App_dir']." doesn't seem to be a directory.  Please check your CONFIG.";
	}
	
	unset($TABLES["TableBuildTime"]);
}
else
{
	$OUTPUT["TableRelaod"] = "false";
}

?>