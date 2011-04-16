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
		global $CONFIG, $DBObj;
		
		if (self::check_DBObj())
		{
			$SQL= 'DELETE FROM `'.$CONFIG['LOG_DB'].'`.`'.$CONFIG['LogTable'].'` WHERE (`TimeStamp` < "'.date('Y-m-d H:i:s',(time() - $CONFIG['LogAge'])).'") ;'; 	
			$DBObj->Query($SQL);
			$this->task_log('Deleted '.$DBObj->NumRowsEffected()." entries from the LOG Table in the ".$CONFIG['LOG_DB']." DB");
		}
	}
}

?>