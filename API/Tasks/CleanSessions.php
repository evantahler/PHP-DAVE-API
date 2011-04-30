<?php
/***********************************************
DAVE PHP API
https://github.com/evantahler/PHP-DAVE-API
Evan Tahler | 2011

TASK
***********************************************/

class CleanSessions extends task
{		
	protected static $description = "I will remove old entries from the SESSIONS table in the DB";
	
	public function run($PARAMS = array())
	{
		$resp = _CleanSessions($PARAMS); // I am defined in DirectDBFunctions in the DB Driver. 
		$this->task_log($resp);
	}
}

?>