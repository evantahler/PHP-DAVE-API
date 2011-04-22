<?php
/***********************************************
DAVE PHP API
https://github.com/evantahler/PHP-DAVE-API
Evan Tahler | 2011

I setup the testing enviorment and include handy functions for the test suite
***********************************************/

// show errors on scrern
ini_set("display_errors","1");
error_reporting (E_ALL ^ E_NOTICE);

chdir("../../API/");
require_once("load_enviorment.php");
require_once("helper_functions/colors.php");

if (!class_exists(DaveTest))
{
class DaveTest
{
	protected $StartTime, $TestLog, $title, $Successes, $Failures, $LastLogMessage, $colors, $MessageDepth, $LastAPIResponse, $ToDBSave;
	
	public function __construct($title = null, $ToDBSave = true)
	{
		$this->StartTime = time();
		$this->colors = new Colors();
		$this->MessageDepth = 0;
		$this->ToDBSave = $ToDBSave;
		
		global $TestLog;
		
		if ($title == null){$title == "Unknown Test";}
		$this->title = $title;
		$this->TestLog = $TestLog;
		$this->log($this->colors->getColoredString($this->title,"yellow"));
		
		$this->Successes = array();
		$this->Failures = array();
		
		load_tasks();
		if ($this->ToDBSave) { $this->log_task_output(run_task("CreateDBSaveState")); }
	}
	
	private function log_task_output($string)
	{
		$this->log("");
		$string = str_replace("\r\n\r\n","\r\n",$string);
		$string = substr($string,0,-4);
		$this->log($string);
		$this->log("");
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
	
	public function api_request($PARAMS)
	{
		global $CONFIG;
		
		$exec = "eval \" cd ".$CONFIG['App_dir']."../;";
		$exec .= " script/api ";
		foreach ($PARAMS as $k => $v)
		{
			$exec .= " --".$k."=".$v." ";
		}
		if (!in_array("OutputType",array_keys($PARAMS)))
		{
			$exec .= " --OutputType=PHP ";
		}
		$exec .= " ; \"";
		$resp = `$exec`;
		$this->LastAPIResponse = $resp;
		return unserialize($resp);
	}
	
	public function get_raw_api_respnse()
	{
		return $this->LastAPIResponse;
	}

	////////////////////////////////////////////
	
	public function assert($eval = null, $a = null, $b = null)
	{
		global $_RESULTS;
		
		$deets = debug_backtrace();
		$ds = " @ line ".$deets[0]["line"];
		
		if ($eval == "==")
		{
			if ($a == $b){
				$this->log(((string)$a)." equals ".((string)$b).$ds);
				return $this->do_success();
			}
			else 
			{
				$this->log("! ".((string)$a)." does not equal ".((string)$b).$ds);
				return $this->do_failure();
			}
			
		}
		
		elseif ($eval == "!=")
		{
			if ($a != $b){
				$this->log(((string)$a)." does not equal ".((string)$b).$ds);
				return $this->do_success();
			}
			else 
			{
				$this->log("! ".((string)$a)." equals ".((string)$b).$ds);
				return $this->do_failure();
			}
			
		}
	
		elseif ($eval == "<")
		{
			if ($a < $b)
			{
				$this->log(((string)$a)." < ".((string)$b).$ds);
				return $this->do_success();
			}
			else 
			{
				$this->log("! ".((string)$a)." < ".((string)$b).$ds);
				return $this->do_failure();
			}
		}
	
		elseif ($eval == ">")
		{
			if ($a > $b)
			{
				$this->log(((string)$a)." > ".((string)$b).$ds);
				return $this->do_success();
			}
			else 
			{
				$this->log("! ".((string)$a)." > ".((string)$b).$ds);
				return $this->do_failure();
			}
		}
	
		elseif ($eval == "<=")
		{
			if ($a <= $b)
			{
				$this->log(((string)$a)." <= ".((string)$b).$ds);
				return $this->do_success();
			}
			else 
			{
				$this->log("! ".((string)$a)." <= ".((string)$b).$ds);
				return $this->do_failure();
			}
		}
	
		elseif ($eval == ">=")
		{
			if ($a <= $b)
			{
				$this->log(((string)$a)." >= ".((string)$b).$ds);
				return $this->do_success();
			}
			else 
			{	
				$this->log("! ".((string)$a)." >= ".((string)$b).$ds);
				return $this->do_failure();
			}
		}
	
		elseif (strtolower($eval) == "empty")
		{
			if (empty($a))
			{
				$this->log(((string)$a)." is empty".$ds);
				return $this->do_success();
			}
			else 
			{
				$this->log("! ".((string)$a)." is not empty".$ds);
				return $this->do_failure();
			}
		}
	
		elseif (strtolower($eval) == "null")
		{
			if (is_null($a))
			{
				$this->log(((string)$a)." is null".$ds);
				return $this->do_success();
			}
			else 
			{
				$this->log("! ".((string)$a)." is not null".$ds);
				return $this->do_failure();
			}
		}
		
		elseif (strtolower($eval) == "true")
		{
			if ($a)
			{
				$this->log(((string)$a)." is true".$ds);
				return $this->do_success();
			}
			else 
			{
				$this->log("! ".((string)$a)." is false".$ds);
				return $this->do_failure();
			}
		}
		
		elseif (strtolower($eval) == "false")
		{
			if (!$a)
			{
				$this->log(((string)$a)." is false".$ds);
				return $this->do_success();
			}
			else 
			{
				$this->log("! ".((string)$a)." is true".$ds);
				return $this->do_failure();
			}
		}
		
		elseif (strtolower($eval) == "in_array")
		{
			if (in_array($a,$b))
			{
				$this->log(((string)$a)." is within the array".$ds);
				return $this->do_success();
			}
			else 
			{
				$this->log("! ".((string)$a)." is not within the array".$ds);
				return $this->do_failure();
			}
		}
		
		elseif (strtolower($eval) == "not_in_array")
		{
			if (!in_array($a,$b))
			{
				$this->log(((string)$a)." is not within the array".$ds);
				return $this->do_success();
			}
			else 
			{
				$this->log("! ".((string)$a)." is within the array".$ds);
				return $this->do_failure();
			}
		}
		
		else 
		{
			return false;
		}
	}

	private function do_success()
	{
		global $__TEST_SUITE_RESULTS;
		if (isset($__TEST_SUITE_RESULTS)){
			$__TEST_SUITE_RESULTS["Successes"]++;
			$__TEST_SUITE_RESULTS["Successes_List"][] = array("title" => $title, "message" => $this->LastLogMessage);
		}
		$this->Successes[] = $this->LastLogMessage;
		return true;
	}
	
	private function do_failure()
	{
		global $__TEST_SUITE_RESULTS;
		if (isset($__TEST_SUITE_RESULTS)){
			$__TEST_SUITE_RESULTS["Failures"]++;
			$__TEST_SUITE_RESULTS["Failures_List"][] = array("title" => $title, "message" => $this->LastLogMessage);
		}
		$this->Failures[] = $this->LastLogMessage;
		return false;
	}

	public function end()
	{
		$this->MessageDepth = 0;
		
		if (count($this->Successes) + count($this->Failures) > 0)
		{
			$this->log("");
			$this->log($this->colors->getColoredString("Summary: ", "cyan"));
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
				$this->log($this->colors->getColoredString(count($this->Failures)." failures","red"));
			}
			foreach($this->Failures as $fail)
			{
				$this->log($this->colors->getColoredString("Failure: ".$fail,"red"));
			}
		}

		if ($this->ToDBSave) { $this->log_task_output(run_task("RestoreDBSaveState")); }
	}
	
	public function log($line)
	{	
		global $CONFIG;
		$log_folder = 
		$this->LastLogMessage = $line;
		
		$i = 0;
		while($i < $this->MessageDepth)
		{
			$line = "  ".$line;
			$i++;
		}
		
		if ($line[0] === "\\")
		{
			$line = $line."\r\n";
		}
		else
		{
			$line = date("Y-m-d H:i:s")." | ".$line."\r\n";
		}
		
		echo $line;
		
		if(isset($CONFIG['TestLog']))
		{
			$fh = fopen($CONFIG['TestLog'], 'a');
			fwrite($fh, $line);
			fclose($fh);
		}
		
		return true;
	}
	
	public function context($contect_message, $depth = 1)
	{
		$this->MessageDepth = $depth;
		$this->log($this->colors->getColoredString("Context: ".$contect_message,"purple"));
		$this->MessageDepth = $depth + 1;
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