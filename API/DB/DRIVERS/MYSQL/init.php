<?php
/***********************************************
DAVE PHP API
https://github.com/evantahler/PHP-DAVE-API
Evan Tahler | 2011

I initilize all the DB specific code, including the core DAVE functions
***********************************************/

$_driver_db_path = $CONFIG['App_dir']."DB/DRIVERS/".$CONFIG["DBType"]."/";
require_once($_driver_db_path."ConnectToDatabase.php");
require_once($_driver_db_path."DirectDBFunctions.php");

// go no further if we don't know about databases
if (class_exists("DBConnection")) {
	
	/*********************************************************/
	// Table information
	$CONFIG['TableConfigFile'] = $CONFIG['App_dir']."DB/TABLES.php";
	$CONFIG['TableConfigRefreshTime'] = 60; // time in seconds for this application to re-poll mySQL for table layout information.  0 will never poll

	$DBObj = new DBConnection();
	$Status = $DBObj->GetStatus();
	if ($Status === true)
	{
		$Connection = $DBObj->GetConnection();
		require_once($_driver_db_path."TableConfig.php");
		require_once($_driver_db_path."DAVE.php");
		
	}
	else
	{
		$ERROR = "DB Cannot be reached: ".$Status;
	}
}
else
{
	$ERROR = "DB Config Error: No Class";	
}

?>