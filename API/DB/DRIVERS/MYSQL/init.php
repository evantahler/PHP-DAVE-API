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

	$DBOBJ = new DBConnection(); // keep this variable name
	$Status = $DBOBJ->GetStatus();
	if ($Status === true)
	{
		$Connection = $DBOBJ->GetConnection();
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