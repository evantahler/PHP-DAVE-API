<?php
/***********************************************
DAVE PHP API
https://github.com/evantahler/PHP-DAVE-API
Evan Tahler | 2011

TASK
***********************************************/

class TruncateTable extends task
{		
	protected static $description = "I will truncate a table provided with --table, and optionally --DB";
	
	public function run($PARAMS = array())
	{
		global $CONFIG, $DBObj;
		
		$stop = false;
		if (strlen($PARAMS['table']) == 0)
		{
			$this->task_log('Provide a table name with --table');
			$stop = true;
		}
		
		if (strlen($PARAMS['DB']) > 0)
		{
			$ThisDB = $PARAMS['DB'];
		}
		else
		{
			$ThisDB = $CONFIG['DB'];
		}
				
		if (self::check_DBObj() && $stop == false)
		{
			$SQL= 'TRUNCATE TABLE `'.$ThisDB.'`.`'.$PARAMS['table'].'`;'; 	
			$DBObj->Query($SQL);
			if ($DBObj->NumRowsEffected() == 0)
			{
				$this->task_log($PARAMS['table']." table truncated from the ".$ThisDB." DB");
			}
			else
			{
				$this->task_log("Table ".$PARAMS['table']." cannot be found in ".$ThisDB);
			}
		}
	}
}

?>