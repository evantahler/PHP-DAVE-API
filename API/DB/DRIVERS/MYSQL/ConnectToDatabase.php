<?php

/***********************************************
DAVE PHP API
https://github.com/evantahler/PHP-DAVE-API
Evan Tahler | 2011

I am the mySQL connection class.  To create a simmilar class for other DB types, please note the functions that that DBConnection class should contain and thier implamentation:

- __construct(): 
  - Can be passsed an additional $DB than the default.  
  - Returns true on sucess
  - Returns false on failure, logs error to $This->Status
- mysql_log(): Will log "queries" to file
- CheckForSpecialStrings(): Will inspect queries for special strings ($CONFIG['SpecialStrings']) and replace.  This is to fix situations where user might post "0" as input, etc
- query(): preforms the DB operation
  - Returns true on sucess
  - Returns false on failure, logs error to $This->Status
- GetLastInsert(): returns the auto incramanet ID of the row added
- NumRowsEffected(): returns a count of the rows effected by the "Edit" command
- GetConnection(): returns the connction object if applicable 
- GetStatus(): returns the last status message
- GetResults(): returns the result of the last query()
- close(): closes the DB connection

//////////////

Example useage:

	$DBOBJ = new DBConnection();
	$Status = $DBOBJ->GetStatus();
	if ($Status === true)
	{
		$DBOBJ->Query($SQL);
		$Status = $DBOBJ->GetStatus();
		if ($Status === true){ 
			$Results = $DBOBJ->GetResults();
			// Do stuff with the $Results array
		}
		else{ $ERROR = $Status; }
	}
	else { $ERROR = $Status; } 
	$DBOBJ->close();

use the GetLastInsert() function to get the deatils of an entry you just added.

***********************************************/

class DBConnection
{
	protected $Connection, $Status, $OUT, $DataBase;
	
	public function __construct($OtherDB = "")
	{
		global $CONFIG;
		$this->Status = true;
		
		if ($OtherDB != "") { $this->DataBase = $OtherDB ; } 
		else { $this->DataBase = $CONFIG['DB']; }
		$this->Connection = @mysql_connect($CONFIG['dbhost'], $CONFIG['dbuser'], $CONFIG['dbpass']);
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
			$this->Status = "Connection Error (mySQL) | Connection Access or permission error";
			return false;
		}		
	}
	
	private function mysql_log($line)
	{
		global $IP, $CONFIG;
		
		$host = $IP;
		if ($host == ""){$host = "local_system";}
		
		$line = date("Y-m-d H:i:s")." | ".$host." | ".$line;
		if (strlen($CONFIG['DBLogFile']) > 0)
		{
			$LogFileHandle = fopen($CONFIG['DBLogFile'], 'a');
			if($LogFileHandle)
			{
				fwrite($LogFileHandle, ($line."\r\n"));
			}
			fclose($LogFileHandle);
		}
	}
	
	private function CheckForSpecialStrings($string)
	{	
		global $CONFIG;
		foreach ($CONFIG['SpecialStrings'] as $term)
		{
			$string = str_replace($term[0],$term[1],$string);
		}
		$string = str_replace("  "," ",$string);
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
			$LogLine = $SQL;
			$Query=mysql_query($SQL);
			if (empty($Query))
			{
				$this->Status = "MYSQL Query Error: ".mysql_errno($this->Connection) . ": " . mysql_error($this->Connection);
				$LogLine .= " | Error->".$this->Status;
				$this->mysql_log($LogLine);
				return false;
			}
			else
			{
				$this->OUT = array();
				$tmp = array();
				$NumRows = 0;
				if(is_resource($Query)){ $NumRows = mysql_num_rows($Query); }
				
				if ($NumRows > 0){ $LogLine .= " | RowsFond -> ".$NumRows; }
				elseif($this->NumRowsEffected > 0){ $LogLine .= " | RowsEffected -> ".$this->NumRowsEffected; } 
				if ($this->GetLastInsert > 0){ $LogLine .= " | InsertID -> ".$this->GetLastInsert; }
				
				$this->mysql_log($LogLine);
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