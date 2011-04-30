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
		$resp = _TruncateTable($PARAMS); // I am defined in DirectDBFunctions in the DB Driver. 
		$this->task_log($resp);
	}
}

?>