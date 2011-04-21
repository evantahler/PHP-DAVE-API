<?php
/***********************************************
DAVE PHP API
https://github.com/evantahler/PHP-DAVE-API
Evan Tahler | 2011

TASK
***********************************************/

class CreateDBSaveState extends task
{		
	protected static $description = "I will create saved copies of mySQL tables.  I will save all of the tables within CONFIG/DB/ unless --table is provided. CACHE and LOG tables will be ignored unless passed explicitly.";
	
	public function run($PARAMS = array())
	{
		global $CONFIG, $TABLES, $DBObj;
		
		reload_tables();
		
		$TablesToSave = array();
		if (strlen($PARAMS['table']) > 0)
		{
			$TablesToSave[] = $PARAMS['table'];
		}
		else
		{
			foreach($TABLES as $table => $data)
			{
				if (substr($table,0,2) != "~~")
				{
					$TablesToSave[] = $table;
				}
			}
		}
		
		foreach($TablesToSave as $table)
		{
			$Status = $DBObj->GetStatus();
			if ($Status === true)
			{
				$this->task_log("saving `".$table."` to `~~".$table."`");
				$DBObj->Query("DROP TABLE IF EXISTS `~~".$table."`;");
				$DBObj->Query("CREATE TABLE `~~".$table."` LIKE `".$table."`;");
				$DBObj->Query("INSERT INTO `~~".$table."` SELECT * FROM `".$table."`;");
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