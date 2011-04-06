<?php
/***********************************************
DAVE PHP API
https://github.com/evantahler/PHP-DAVE-API
Evan Tahler | 2011

I setup the testing enviorment and include handy functions for the test suite

TODO: Handle errrors in testing elegantly so next tests continue
TODO: Aggregate testing scores for whole suite
***********************************************/

// show errors on scrern
ini_set("display_errors","1");
error_reporting (E_ALL ^ E_NOTICE);

require_once("../../API/AccessTools/APIRequest.php");
require_once("../../API/helper_functions/colors.php");
require_once("../../API/CONFIG.php");

// working directory
chdir("../../API/");

$TestURL = $CONFIG['ServerAddress'];  // be sure to include the proper port (default for using included SERVER.php)

if (!class_exists(DaveTest))
{
class DaveTest
{
	protected $StartTime, $TestLog, $title, $Successes, $Failures, $LastLogMessage, $colors;
	
	public function __construct($title = null)
	{
		$this->StartTime = time();
		$this->colors = new Colors();
		
		global $TestLog;
		
		if ($title == null){$title == "Unknown Test";}
		$this->title = $title;
		$this->TestLog = $TestLog;
		$this->log($this->colors->getColoredString("Starting new Test: ".$this->title,"purple","yellow"));
		
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
		$ds = " @ line ".$deets[0]["line"];
		
		if ($eval == "==")
		{
			if ($a == $b){
				$this->log(((string)$a)." equals ".((string)$b).$ds);
				$this->Successes[] = $this->LastLogMessage;
				return true;
			}
			else 
			{
				$this->log("! ".((string)$a)." does not equal ".((string)$b).$ds);
				$this->Failures[] = $this->LastLogMessage;
				return false;
			}
			
		}
		
		if ($eval == "!=")
		{
			if ($a != $b){
				$this->log(((string)$a)." does not equal ".((string)$b).$ds);
				$this->Successes[] = $this->LastLogMessage;
				return true;
			}
			else 
			{
				$this->log("! ".((string)$a)." equals ".((string)$b).$ds);
				$this->Failures[] = $this->LastLogMessage;
				return false;
			}
			
		}
	
		if ($eval == "<")
		{
			if ($a < $b)
			{
				$this->log(((string)$a)." < ".((string)$b).$ds);
				$this->Successes[] = $this->LastLogMessage;
				return true;
			}
			else 
			{
				$this->log("! ".((string)$a)." < ".((string)$b).$ds);
				$this->Failures[] = $this->LastLogMessage;
				return false;
			}
		}
	
		if ($eval == ">")
		{
			if ($a > $b)
			{
				$this->log(((string)$a)." > ".((string)$b).$ds);
				$this->Successes[] = $this->LastLogMessage;
				return true;
			}
			else 
			{
				$this->log("! ".((string)$a)." > ".((string)$b).$ds);
				$this->Failures[] = $this->LastLogMessage;
				return false;
			}
		}
	
		if ($eval == "<=")
		{
			if ($a <= $b)
			{
				$this->log(((string)$a)." <= ".((string)$b).$ds);
				$this->Successes[] = $this->LastLogMessage;
				return true;
			}
			else 
			{
				$this->log("! ".((string)$a)." <= ".((string)$b).$ds);
				$this->Failures[] = $this->LastLogMessage;
				return false;
			}
		}
	
		if ($eval == ">=")
		{
			if ($a <= $b)
			{
				$this->log(((string)$a)." >= ".((string)$b).$ds);
				$this->Successes[] = $this->LastLogMessage;
				return true;
			}
			else 
			{	
				$this->log("! ".((string)$a)." >= ".((string)$b).$ds);
				$this->Failures[] = $this->LastLogMessage;
				return false;
			}
		}
	
		if ($eval == "empty")
		{
			if (empty($a))
			{
				$this->log(((string)$a)." is empty".$ds);
				$this->Successes[] = $this->LastLogMessage;
				return true;
			}
			else 
			{
				$this->log("! ".((string)$a)." is not empty".$ds);
				$this->Failures[] = $this->LastLogMessage;
				return false;
			}
		}
	
		if (strtolower($eval) == "null")
		{
			if (is_null($a))
			{
				$this->log(((string)$a)." is null".$ds);
				$this->Successes[] = $this->LastLogMessage;
				return true;
			}
			else 
			{
				$this->log("! ".((string)$a)." is not null".$ds);
				$this->Failures[] = $this->LastLogMessage;
				return false;
			}
		}
		
		if (strtolower($eval) == "true")
		{
			if ($a)
			{
				$this->log(((string)$a)." is true".$ds);
				$this->Successes[] = $this->LastLogMessage;
				return true;
			}
			else 
			{
				$this->log("! ".((string)$a)." is false".$ds);
				$this->Failures[] = $this->LastLogMessage;
				return false;
			}
		}
		
		if (strtolower($eval) == "false")
		{
			if (!$a)
			{
				$this->log(((string)$a)." is false".$ds);
				$this->Successes[] = $this->LastLogMessage;
				return true;
			}
			else 
			{
				$this->log("! ".((string)$a)." is true".$ds);
				$this->Failures[] = $this->LastLogMessage;
				return false;
			}
		}
		
		if (strtolower($eval) == "in_array")
		{
			if (in_array($a,$b))
			{
				$this->log(((string)$a)." is within the array".$ds);
				$this->Successes[] = $this->LastLogMessage;
				return true;
			}
			else 
			{
				$this->log("! ".((string)$a)." is not within the array".$ds);
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
		$this->log($this->colors->getColoredString("Summary: ", "null", "cyan"));
		$duration = (time() - $this->StartTime);
		$timeString = $this->secondsToWords($duration);
		$this->log("Test suite [".(count($this->Successes) + count($this->Failures))."] complete in ".$timeString);
		$this->log($this->colors->getColoredString(count($this->Successes)." successes","green", null));
		if (count($this->Failures) == 0)
		{
			$this->log($this->colors->getColoredString(count($this->Failures)." failures","green", null));
		}
		else
		{
			$this->log($this->colors->getColoredString(count($this->Failures)." failures","red", "black"));
		}
		foreach($this->Failures as $fail)
		{
			$this->log($this->color->getColoredString("Failure: ".$fail,"red",black));
		}
		$this->log("");	
	}
	
	private function log($line)
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