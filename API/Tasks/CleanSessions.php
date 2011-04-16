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
		global $CONFIG, $DBObj;
		
		if (self::check_DBObj())
		{
			$SQL= 'DELETE FROM `SESSIONS` WHERE (`created_at` < "'.date('Y-m-d H:i:s',(time() - $CONFIG['SessionAge'])).'") ;';
			$DBObj->Query($SQL);
			$this->task_log('Deleted '.$DBObj->NumRowsEffected()." entries from the SESSIONS Table in the DB");
		}
	}
}

?>