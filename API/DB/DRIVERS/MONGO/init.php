<?php
/***********************************************
DAVE PHP API
https://github.com/evantahler/PHP-DAVE-API
Evan Tahler | 2011

I initilize all the DB specific code, including the core DAVE functions.  I will create the $DBOBJ object.
!!! You might need to setup mongo for your os: http://www.mongodb.org/display/DOCS/PHP+Language+Center !!!
!!! http://www.php.net/manual/en/mongo.installation.php !!!
!!! You may need to create the CLI version of your php ini file for local DAVE development to work.  Ensure you have both a /etc/php.ini and /etc/php-cli/ini with extension= mongo.so !!!
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
		// require_once($_driver_db_path."TableConfig.php");
		require_once($CONFIG['TableConfigFile']);
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