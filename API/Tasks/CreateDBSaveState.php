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
		$resp = CreateDBSaveState($PARAMS); // Defined by DB Driver
		foreach($resp as $line){ $this->task_log($line); }
	}
}

?>