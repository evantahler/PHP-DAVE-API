<?php
/***********************************************
DAVE PHP API
https://github.com/evantahler/PHP-DAVE-API
Evan Tahler | 2011

I am a simple action that will return the array of tables and thier column state that I divined from the connected mySQL database.
***********************************************/

if ($ERROR == 100)
{
	$HumanizedTables = array();
	$TableNames = array_keys($TABLES);
	$i = 0;
	while ($i < count($TABLES))
	{
		$CurTable = $TableNames[$i];
		$HumanizedTables[$CurTable]["Key"] = $TABLES[$CurTable]["META"]["KEY"];

		foreach($TABLES[$CurTable] as $row)
		{
			$col_name = $row[0];
			$unique = $row[1];
			$required = $row[2];
			
			if ($unique == 1){$unique = "true";}
			else{$unique = "false";}
			
			if ($required == 1){$required = "true";}
			else{$required = "false";}
			
			if (strlen($col_name) > 0)
			{
				$HumanizedTables[$CurTable]["Columns"][] = array("name" => $col_name, "unique" => $unique, "required" => $required);
			}
		}
		$i++;
	}
	
	$OUTPUT["Tables"] = $HumanizedTables;
}

?>
