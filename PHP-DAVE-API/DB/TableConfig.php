<?php

// Table Definitions
$TABLES = array();
$ToReload = false;

if (!file_exists($TableConfigFile)) {
	$ToReload = true;
}
else
{
	require_once($TableConfigFile);
	if ($TableConfigRefreshTime > 0) 
	{
		if ($TableBuildTime + $TableConfigRefreshTime < time()) {
			$ToReload = true;
			$TABLES = array(); // clear it, just to be safe
		}
	}
}

if ($ToReload)
{
	$OUTPUT["TableRelaod"] = "true";
	$DBObj = new DBConnection();
	$Status = $DBObj->GetStatus();
	if ($Status === true)
	{
		$Connection = $DBObj->GetConnection();
		$SQL= 'SHOW TABLES;';
		$DBObj->Query($SQL);
		$out = $DBObj->GetResults();
		$TableList = array();
		foreach ($out as $sub){
			$name = $sub["Tables_in_".$DB];
			if ($name != "CACHE" && $name != "LOG") { $TableList[] = $name; }
		}
		foreach ($TableList as $ThisTable)
		{
			$SQL= 'DESCRIBE '.$DB.'.'.$ThisTable.';';
			$DBObj->Query($SQL);
			$Response = $DBObj->GetResults();
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
	$DBObj->close();
	$TABLES["TableBuildTime"] = time();
	@unlink($TableConfigFile);
	$TableStringOutput = "";
	$TableStringOutput .= "<?php \r\n";
	$TableStringOutput .= "// TABLE DESCRIPTION GENERATED AT ".date("Y-m-d H:i:s")."\r\n";
	$TableStringOutput .= "// the KEY meta param is used to define which table is used to look up and edit information with.  This should be a unique column \r\n";
	$TableStringOutput .= "// every column in a table (that you care to access) is defined as [[ array( ColName, Unique? (true or false), Required? (true or false)) ]].  Any and all unique variables will be used to sort/select SQL lookups \r\n\r\n";
	$TableStringOutput .= '$TableBuildTime = "'.$TABLES['TableBuildTime']."\"; \r\n\r\n";
	foreach ($TableList as $ThisTable)
	{
		$TableStringOutput .= '$TABLES["'.$ThisTable.'"]["META"]["KEY"] = '.$TABLES[$ThisTable]['META']['KEY']."; \r\n";
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
	
	$fh = fopen($TableConfigFile, 'w');
	fwrite($fh, $TableStringOutput);
	fclose($fh);
	chmod($TableConfigFile,0777);
}
else
{
	$OUTPUT["TableRelaod"] = "false";
}

?>