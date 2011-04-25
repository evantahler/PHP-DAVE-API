<?php
/***********************************************
DAVE PHP API
https://github.com/evantahler/PHP-DAVE-API
Evan Tahler | 2011

I am the base class for API Tasks.  I will create the basic task object which can be extended.  Tasks can be run at the end of certain actions, by CRON, or even manually.

The basic layout of a task class is:

---------------------------------------------------------------------------------------

class CleanCache extends task
{		
	protected static $description = "Use me to clean both the DB and file-based cache";
	
	public function run($PARAMS = array())
	{
		global $CONFIG, $DBOBJ;
		
		// PUT YOUR ACTION CODE HERE
		
		// collect output with the task_log method
		// $this->task_log('Task Complete!');
	}
}

***********************************************/

if (empty($TASKS)) { $TASKS = array(); } // a list of tasks to be populated by name

class task 
{
	protected $DATA = array();
	protected static $description = "empty"; // I will be overwritten by child
	
	public function __construct($ToRun = false, $PARAMS = array())
	{
		if ($ToRun)
		{
			$this->run($PARAMS); // I will run automatically.  BE SURE TO MAKE A run() CLASS IN YOUR task
		}
	}
	
	public function task_log($message)
	{
		$line = date("Y-m-d H:i:s")." | ".$message."\r\n";
		$this->DATA["log"] .= $line;
	}
	
	public static function class_name() 
	{
	    return get_called_class();
	}
	
	public static function get_description()
	{
		$ThisClass = get_called_class();
		return $ThisClass::$description;
	}
	
	public function get_task_log()
	{
		return $this->DATA["log"];
	}
	
	public function check_DBObj()
	{
		global $DBOBJ;
		$Status = $DBOBJ->GetStatus();
		if (!($Status === true))
		{
			$this->task_log("DB Error: ".$Status);
		}
		return $Status;
	}
}

?>