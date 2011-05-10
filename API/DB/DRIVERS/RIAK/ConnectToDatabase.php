<?php

/***********************************************
DAVE PHP API
https://github.com/evantahler/PHP-DAVE-API
Evan Tahler | 2011

I am the RIAK connection class.  To create a simmilar class for other DB types, please note the functions that that DBConnection class should contain and thier implamentation:

- __construct(): 
  - Can be passsed an additional $DB than the default.  
  - Returns true on sucess
  - Returns false on failure, logs error to $This->Status
- riak_log(): Will log "queries" to file
- CheckForSpecialStrings(): Will inspect queries for special strings ($CONFIG['SpecialStrings']) and replace.  This is to fix situations where user might post "0" as input, etc
- GetConnection(): returns the connction object if applicable 
- GetStatus(): returns the last status message
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
	protected $Connection, $Status, $OUT, $DataBase, $BaseURL, $Port;
	
	public function __construct($OtherDB = "")
	{
		global $CONFIG;
		$this->Status = true;
		
		if ($OtherDB != "") { $DataBase = $this->DataBase = $OtherDB ; } 
		else { $DataBase = $this->DataBase = $CONFIG['DB']; }
		$tmp = explode(":",$this->DataBase);
		if (count($tmp) == 2)
		{
			$this->BaseURL = $tmp[0];
			$this->Port = $tmp[1];
		}
		elseif(count($tmp) == 1)
		{
			$this->BaseURL = $this->DataBase;
			$this->Port = 8098;
		}
		else
		{
			$this->Status = "URL definition error.  Use host:port";
		}
		
		$this->Connection = new RiakClient($this->BaseURL, $this->Port);
		
		if(!empty($this->Connection))
		{
			return true;
		}
		else
		{
			$this->Status = "Connection Error (Riak) | Connection Access or permission error";
			return false;
		}		
	}
	
	private function riak_log($line)
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
	
	public function GetConnection()
	{
		return $this->Connection;
	}
	
	public function GetStatus()
	{
		return $this->Status;
	}
	
	public function close()
	{
		// @mysql_close($this->Connection);
		unset($this->Connection);
		$this->Status = "Disconnected. (Mongo)";
	}
}

?>