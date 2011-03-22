<?php
/***********************************************
DAVE PHP API
https://github.com/evantahler/PHP-DAVE-API
Evan Tahler | 2011

I setup the testing enviorment and include handy functions for the test suite
***********************************************/
$path = substr(__FILE__,0,(strlen(__FILE__) - strlen($_SERVER['SCRIPT_NAME'])));

require_once("../../AccessTools/APIRequest.php");
require_once("../../ConnectToDatabase.php");
require_once("../../CONFIG.php");
date_default_timezone_set($systemTimeZone);

$TestLog = "LOG/test_log.txt"; // from project root

if (!class_exists(DaveTest))
{
class DaveTest
{
	protected $StartTime, $TestLog, $title, $Successes, $Failures, $LastLogMessage;
	
	public function __construct($title = null)
	{
		$this->StartTime = time();
		
		global $TestLog;
		
		if ($title == null){$title == "Unknown Test";}
		$this->title = $title;
		$this->TestLog = $TestLog;
		$this->log("Starting new Test: ".$this->title);
		
		$this->Successes = array();
		$this->Failures = array();
	}
	
	public function __set($key, $value) 
	{
        $this->DATA[$key] = $value;
    }

	public function __get($key) 
	{
        if (array_key_exists($key, $this->DATA)) 
		{
	    	return $this->DATA[$key];
        }
		else
		{
			return false;
		}
    }

	////////////////////////////////////////////
	
	public function assert($eval = null, $a = null, $b = null)
	{
		$deets = debug_backtrace();
		$ds = $deets[0]["file"]." @ line ".$deets[0]["line"];
		
		if ($eval == "==")
		{
			if ($a == $b){
				$this->log(((string)$a)." equals ".((string)$b)." | ".$ds);
				$this->Successes[] = $this->LastLogMessage;
				return true;
			}
			else 
			{
				$this->log("! ".((string)$a)." does not equal ".((string)$b)." | ".$ds);
				$this->Failures[] = $this->LastLogMessage;
				return false;
			}
			
		}
		
		if ($eval == "!=")
		{
			if ($a != $b){
				$this->log(((string)$a)." does not equal ".((string)$b)." | ".$ds);
				$this->Successes[] = $this->LastLogMessage;
				return true;
			}
			else 
			{
				$this->log("! ".((string)$a)." equals ".((string)$b)." | ".$ds);
				$this->Failures[] = $this->LastLogMessage;
				return false;
			}
			
		}
	
		if ($eval == "<")
		{
			if ($a < $b)
			{
				$this->log(((string)$a)." < ".((string)$b)." | ".$ds);
				$this->Successes[] = $this->LastLogMessage;
				return true;
			}
			else 
			{
				$this->log("! ".((string)$a)." < ".((string)$b)." | ".$ds);
				$this->Failures[] = $this->LastLogMessage;
				return false;
			}
		}
	
		if ($eval == ">")
		{
			if ($a > $b)
			{
				$this->log(((string)$a)." > ".((string)$b)." | ".$ds);
				$this->Successes[] = $this->LastLogMessage;
				return true;
			}
			else 
			{
				$this->log("! ".((string)$a)." > ".((string)$b)." | ".$ds);
				$this->Failures[] = $this->LastLogMessage;
				return false;
			}
		}
	
		if ($eval == "<=")
		{
			if ($a <= $b)
			{
				$this->log(((string)$a)." <= ".((string)$b)." | ".$ds);
				$this->Successes[] = $this->LastLogMessage;
				return true;
			}
			else 
			{
				$this->log("! ".((string)$a)." <= ".((string)$b)." | ".$ds);
				$this->Failures[] = $this->LastLogMessage;
				return false;
			}
		}
	
		if ($eval == ">=")
		{
			if ($a <= $b)
			{
				$this->log(((string)$a)." >= ".((string)$b)." | ".$ds);
				$this->Successes[] = $this->LastLogMessage;
				return true;
			}
			else 
			{	
				$this->log("! ".((string)$a)." >= ".((string)$b)." | ".$ds);
				$this->Failures[] = $this->LastLogMessage;
				return false;
			}
		}
	
		if ($eval == "empty")
		{
			if (empty($a))
			{
				$this->log(((string)$a)." is empty"." | ".$ds);
				$this->Successes[] = $this->LastLogMessage;
				return true;
			}
			else 
			{
				$this->log("! ".((string)$a)." is not empty"." | ".$ds);
				$this->Failures[] = $this->LastLogMessage;
				return false;
			}
		}
	
		if (strtolower($eval) == "null")
		{
			if (is_null($a))
			{
				$this->log(((string)$a)." is null"." | ".$ds);
				$this->Successes[] = $this->LastLogMessage;
				return true;
			}
			else 
			{
				$this->log("! ".((string)$a)." is not null"." | ".$ds);
				$this->Failures[] = $this->LastLogMessage;
				return false;
			}
		}
		
		if (strtolower($eval) == "true")
		{
			if ($a)
			{
				$this->log(((string)$a)." is true"." | ".$ds);
				$this->Successes[] = $this->LastLogMessage;
				return true;
			}
			else 
			{
				$this->log("! ".((string)$a)." is false"." | ".$ds);
				$this->Failures[] = $this->LastLogMessage;
				return false;
			}
		}
		
		if (strtolower($eval) == "false")
		{
			if (!$a)
			{
				$this->log(((string)$a)." is false"." | ".$ds);
				$this->Successes[] = $this->LastLogMessage;
				return true;
			}
			else 
			{
				$this->log("! ".((string)$a)." is true"." | ".$ds);
				$this->Failures[] = $this->LastLogMessage;
				return false;
			}
		}
		
		if (strtolower($eval) == "in_array")
		{
			if (in_array($a,$b))
			{
				$this->log(((string)$a)." is within the array"." | ".$ds);
				$this->Successes[] = $this->LastLogMessage;
				return true;
			}
			else 
			{
				$this->log("! ".((string)$a)." is not within the array"." | ".$ds);
				$this->Failures[] = $this->LastLogMessage;
				return false;
			}
		}
		
		else 
		{
			return false;
		}
	}
	
	public function end()
	{
		$this->log("");
		$this->log("Summary: ");
		$duration = (time() - $this->StartTime);
		$timeString = $this->secondsToWords($duration);
		$this->log("Test suite [".($this->Successes + $this->Failures)."] complete in ".$timeString);
		$this->log(count($this->Successes)." successes");
		$this->log(count($this->Failures)." failures");
		foreach($this->Failures as $fail)
		{
			$this->log("Failure: ".$fail);
		}
		$this->log("");
	}
	
	private function log($line)
	{	
		if (strlen($this->TestLog) > 0)
		{	
			$this->LastLogMessage = $line;
			if ($line[0] === "\\")
			{
				$line = $line."\r\n";
			}
			else
			{
				$line = date("Y-m-d H:i:s")." | ".$line."\r\n";
			}
			echo $line;
		
			// $LogFileHandle = fopen($this->TestLog, 'a');
			// if($LogFileHandle)
			// {
			// 	fwrite($LogFileHandle, ($line));
			// }
			// fclose($LogFileHandle);
		}
	}
	
	private function secondsToWords($seconds)
	{
	    /*** return value ***/
	    $ret = "";

	    /*** get the hours ***/
	    $hours = intval(intval($seconds) / 3600);
	    if($hours > 0)
	    {
	        $ret .= "$hours hours ";
	    }
	    /*** get the minutes ***/
	    $minutes = bcmod((intval($seconds) / 60),60);
	    if($hours > 0 || $minutes > 0)
	    {
	        $ret .= "$minutes minutes ";
	    }

	    /*** get the seconds ***/
	    $seconds = bcmod(intval($seconds),60);
	    $ret .= "$seconds seconds";

	    return $ret;
	}	
}
}

?>