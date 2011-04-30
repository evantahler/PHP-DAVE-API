<?php
/***********************************************
DAVE PHP API
https://github.com/evantahler/PHP-DAVE-API
Evan Tahler | 2011

I am a task that will clean the LOG db acording to the expire time
***********************************************/

class CleanLog extends task
{		
	protected static $description = "I will remove old entries from the LOG table in the DB";
	
	public function run($PARAMS = array())
	{
		$resp = _CleanLog($PARAMS); // I am defined in DirectDBFunctions in the DB Driver. 
		$this->task_log($resp);
	}
}

?>