<?php 
// TABLE DESCRIPTION GENERATED AT 2011-03-16 17:03:29
// the KEY meta param is used to define which table is used to look up and edit information with.  This should be a unique column 
// every column in a table (that you care to access) is defined as [[ array( ColName, Unique? (true or false), Required? (true or false)) ]].  Any and all unique variables will be used to sort/select SQL lookups 

$TableBuildTime = "1300320209"; 

$TABLES["Developers"]["META"]["KEY"] = ID; 
$TABLES["Developers"][] = array("ID",true,true); 
$TABLES["Developers"][] = array("DeveloperID",false,true); 
$TABLES["Developers"][] = array("APIKey",false,true); 
$TABLES["Developers"][] = array("UserActions",false,true); 
$TABLES["Developers"][] = array("IsAdmin",false,true); 

$TABLES["Users"]["META"]["KEY"] = UserID; 
$TABLES["Users"][] = array("UserID",true,true); 
$TABLES["Users"][] = array("FirstName",false,true); 
$TABLES["Users"][] = array("LastName",false,true); 
$TABLES["Users"][] = array("PhoneNumber",false,true); 
$TABLES["Users"][] = array("Gender",false,true); 
$TABLES["Users"][] = array("ScreenName",false,true); 
$TABLES["Users"][] = array("EMail",false,true); 
$TABLES["Users"][] = array("Birthday",false,true); 
$TABLES["Users"][] = array("PasswordHash",false,true); 
$TABLES["Users"][] = array("Salt",false,true); 
$TABLES["Users"][] = array("Joined",false,true); 

// END 
?>