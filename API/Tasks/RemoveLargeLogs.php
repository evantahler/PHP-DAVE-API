<?php
/***********************************************
DAVE PHP API
https://github.com/evantahler/PHP-DAVE-API
Evan Tahler | 2011

TASK
***********************************************/

class RemoveLargeLogs extends task
{		
	protected static $description = "I will remove large log files the API may have created.";
	
	public function run($PARAMS = array())
	{
		global $CONFIG, $DBObj;
				
		clearstatcache();
		$i = 0;
		while ($i < count($CONFIG['LogsToCheck']))
		{
			$this->task_log("checking: ".$CONFIG['LogsToCheck'][$i]." against ".$CONFIG['MaxLogFileSize']." bytes");
			$bytes = @filesize($CONFIG['LogsToCheck'][$i]);
			if (@filesize($CONFIG['LogsToCheck'][$i]) > $CONFIG['MaxLogFileSize'])
			{
				$this->task_log("	> file size too large @ ".$bytes." bytes, removing.");
				unlink($CONFIG['LogsToCheck'][$i]);
				$fh = fopen($CONFIG['LogsToCheck'][$i], 'w');
				fclose($fh);
				chmod($Logs[$i], 0777);
				$this->task_log("	> recreated with 0 bytes");
			}
			else
			{
				$this->task_log("	> file size OK @ ".$bytes." bytes");
			}
			$i++;
		}
	}
}

?>