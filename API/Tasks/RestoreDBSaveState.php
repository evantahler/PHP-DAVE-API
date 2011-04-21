<?php
/***********************************************
DAVE PHP API
https://github.com/evantahler/PHP-DAVE-API
Evan Tahler | 2011

TASK
***********************************************/

class RestoreDBSaveState extends task
{		
	protected static $description = "I will restore saved tables to their saved state.  I will restore all saved tables within CONFIG/DB/ unless --table is provided.";
	
	public function run($PARAMS = array())
	{
		global $CONFIG, $TABLES, $DBObj;
		
		$TablesToRestore = array();
		if (strlen($PARAMS['table']) > 0)
		{
			$TablesToRestore[] = "~~".$PARAMS['table'];
		}
		else
		{
			foreach($TABLES as $table => $data)
			{
				if (substr($table,0,2) == "~~")
				{
					$TablesToRestore[] = $table;
				}
			}
		}
		
		foreach($TablesToRestore as $table)
		{
			$Status = $DBObj->GetStatus();
			if ($Status === true)
			{
				$this->task_log("restoring `".$table."` to `".substr($table,2)."`");
				$DBObj->Query("DROP TABLE IF EXISTS `".substr($table,2)."`;");
				$DBObj->Query("CREATE TABLE `".substr($table,2)."` LIKE `".$table."`;");
				$DBObj->Query("INSERT INTO `".substr($table,2)."` SELECT * FROM `".$table."`;");
				$DBObj->Query("DROP TABLE `".$table."`;");
			}
			else
			{
				$this->task_log("DB Error: ".$Status);
				break;
			}
		}
	}
}

?>