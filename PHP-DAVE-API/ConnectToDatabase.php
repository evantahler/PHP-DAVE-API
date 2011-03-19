<?php

/***********************************************
DAVE PHP API
https://github.com/evantahler/PHP-DAVE-API
Evan Tahler | 2011

I am the mySQL connection class

Here's an example:

	$DBObj = new DBConnection();
	$Status = $DBObj->GetStatus();
	if ($Status === true)
	{
		$DBObj->Query($SQL);
		$Status = $DBObj->GetStatus();
		if ($Status === true){ $Results = $DBObj->GetResults();}
		// Do stuff with the $Results array
		else{ $ERROR = $Status; }
	}
	else { $ERROR = $Status; } 
	$DBObj->close();

use the GetLastInsert() function to get the deatils of an entry you just added.

***********************************************/

class DBConnection
{
	protected $Connection, $Status, $OUT, $DataBase;
	
	public function __construct($OtherDB = "")
	{
		global $dbhost, $dbuser, $dbpass, $DB;
		$this->Status = true;
		
		if ($OtherDB != "") { $this->DataBase = $OtherDB ; } 
		else { $this->DataBase = $DB; }
				
		$this->Connection = @mysql_connect($dbhost, $dbuser, $dbpass);
		if(!empty($this->Connection))
		{
			$DatabaseSelected=mysql_select_db($this->DataBase);
			if (!empty($DatabaseSelected))
			{
				return true;
			}
			else
			{
				$this->Status = "Database Selection Error (mySQL) | ".mysql_errno($this->Connection) . ": " . mysql_error($this->Connection);
				return false;
			}
		}
		else
		{
			$this->Status = "Connection Error (mySQL) | Connection or Access permission error";
			return false;
		}		
	}
	
	private function CheckForSpecialStrings($string)
	{	
		global $SpecialStrings;
		foreach ($SpecialStrings as $term)
		{
			$string = str_replace($term[0],$term[1],$string);
		}
		return $string;
	}
	
	public function Query($SQL)
	{
		if($this->Status != true)
		{
			return false;
		}
		elseif(strlen($SQL) < 1)
		{
			return false;
		}
		else
		{
			$SQL = $this->CheckForSpecialStrings($SQL);
			$Query=mysql_query($SQL);
			if (empty($Query))
			{
				$this->Status = "MYSQL Query Error: ".mysql_errno($this->Connection) . ": " . mysql_error($this->Connection);
				return false;
			}
			else
			{
				$this->OUT = array();
				$tmp = array();
				$NumRows=@mysql_num_rows($Query);
				if ($NumRows > 0)
				{
					while($row = mysql_fetch_assoc($Query))
					{
						$tmp[] = $row;
					}
					$this->OUT = $tmp;
					unset($tmp);
					return true;
				}
				else
				{
					return true; // it worked, but there is no data retruned.  Perhaps this wasn't a SELECT
				}
			}
		}
	}
	
	public function GetLastInsert()
	{
		return mysql_insert_id($this->Connection);
	}
	
	public function NumRowsEffected()
	{
		return mysql_affected_rows($this->Connection);
	}
	
	public function GetConnection()
	{
		return $this->Connection;
	}
	
	public function GetStatus()
	{
		return $this->Status;
	}
	
	public function GetResults()
	{
		return $this->OUT;
	}
	
	public function close()
	{
		@mysql_close($this->Connection);
		$this->Status = "Disconnected. (mySQL)";
	}
}

?>