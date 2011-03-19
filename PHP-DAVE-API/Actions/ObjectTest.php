<?php
/***********************************************
DAVE PHP API
https://github.com/evantahler/PHP-DAVE-API
Evan Tahler | 2011

I am an example of how to use DAVE objects.  The results of the test are output line by line
***********************************************/

if ($ERROR == 100)
{
	// init
	$TestResults = array();
	$UsersTable = new DaveTableObject("Users");
	
	// how many users are there now?
	$TestResults["A_How_Many_Initial_Users"] = $UsersTable->count();
	
	// what are thier screen names and join dates?
	$screen_names = array();
	$Users = $UsersTable->all(); // you can also use find() and pass no params
	foreach($Users as $User)
	{
		$screen_names[$User->DATA("ScreenName")] = $User->DATA("Joined");
	}
	$TestResults["B_User_ScreenNames_and_join_dates"] = $screen_names;
	
	// add a new user
	$OurUserData = array(
		"FirstName" => "Evan",
		"LastName" => "Taler",
		"EMail" => "test_user@test.com",
		"ScreenName" => "DaveAPI",
		"Password" => "password"
	);
	$OurUser = new User($UsersTable, $OurUserData); 
	$OurUser->validate_and_configure_new_user();
	$TestResults["C_Add_User_Response"] = $OurUser->ADD();
	
	// how many users now?
	$TestResults["D_How_Many_Users_After_Create"] = $UsersTable->count();
	
	// try to add the same user again, and note the error about these unique params already existing
	$TestResults["E_Existance_Error_When_Add_Again"] = $OurUser->ADD();

	// view that user by the user object
	$TestResults["F_User_Details_From_User_Object"] = $OurUser->VIEW();
	
	// view that user by a find on the Table Object	
	$FoundUserObjects = $UsersTable->find(array("ScreenName" => "DaveAPI"));
	$FoundUserHashes = array();
	foreach ($FoundUserObjects as $FoundUser)
	{
		$FoundUserHashes[] = $FoundUser->DATA();
	}
	$TestResults["G_User_Details_From_Find_On_Table_Object"] = $FoundUserHashes;

	// edit that user
	$NewUserData = array(
		"ScreenName" => "NewDaveAPI",
		"EMail" => "new_email@test.com"
	);
	$EditResp = $OurUser->EDIT($NewUserData);
	$TestResults["H_Edited_User_Data"] = $EditResp;

	// delete that user
	$delete_resp = $OurUser->DELETE();
	if ($delete_resp == true){ $delete_resp = "OK";}
	$TestResults["I_Delete_User"] = $delete_resp;

	// how many users now?
	$TestResults["J_Original_User_Count"] = $UsersTable->count();

	
	
	
	$OUTPUT["TestResults"] = $TestResults;
}
?>