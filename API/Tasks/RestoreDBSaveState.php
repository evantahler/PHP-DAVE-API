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
		$resp = RestoreDBSveState($PARAMS); // Defined by DB Driver
		foreach($resp as $line){ $this->task_log($line); }
	}
}

?>