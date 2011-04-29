<?php

/***********************************************
DAVE PHP API
https://github.com/evantahler/PHP-DAVE-API
Evan Tahler | 2011

I am the Riak connection class. 

- __construct(): 
  - Can be passsed an additional $DB than the default.  
  - Returns true on sucess
  - Returns false on failure, logs error to $This->Status
- riak_log(): Will log "queries" to file
- CheckForSpecialStrings(): Will inspect queries for special strings ($CONFIG['SpecialStrings']) and replace.  This is to fix situations where user might post "0" as input, etc

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
	protected $Connection, $Status, $OUT, $DataBase, $Bucket;
	
	public function __construct($OtherDB = "")
	{
		global $CONFIG;
		$this->Status = true;
		
		if ($OtherDB != "") { $this->DataBase = $OtherDB ; } 
		else { $this->DataBase = $CONFIG['DB']; }
		
		$url_parts = explode(":",$CONFIG['dbhost']);
		if(!is_int($url_parts[1])){$url_parts[1] = 8098;}
		$this->Connection = new RiakClient($url_parts[0], $url_parts[1]);
		
		if(!empty($this->Connection))
		{
			$this->Bucket = $this->Connection->bucket($this->DataBase);
			if (!empty($this->Bucket))
			{
				return true;
			}
			else
			{
				$this->Status = "Bucket Selection Error (riak)";
				return false;
			}
		}
		else
		{
			$this->Status = "Connection Error (riak) | Connection Access or permission error";
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
	
	public function GetLastInsert()
	{

	}
	
	public function NumRowsEffected()
	{

	}
	
	public function GetConnection()
	{
		return $this->Connection;
	}
	
	public function GetBucket()
	{
		return $this->Bucket;
	}
	
	public function GetStatus()
	{
		return $this->Status;
	}
	
	public function GetResults()
	{

	}
	
	public function close()
	{
		unset($this->Connection);
		$this->Status = "Disconnected. (riak)";
	}
}

?>