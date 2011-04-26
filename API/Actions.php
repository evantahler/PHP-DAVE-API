<?php
/***********************************************
DAVE PHP API
https://github.com/evantahler/PHP-DAVE-API
Evan Tahler | 2011

I an the list of $Actions this API will handle, the path to them, and thier permission structure
Actions, defined as "verb",  then "page location", then "Public" or "Private" indicatiing if an APIKey is needed to access the function
***********************************************/
$ACTIONS = array();

// default actions
$ACTIONS[] = array('DescribeActions', 'Actions/examples/DescribeActions.php', 'Public');
$ACTIONS[] = array('DescribeTables', 'Actions/examples/DescribeTables.php', 'Public');

// some basic actions
$ACTIONS[] = array('PrivateAction', 'Actions/examples/PrivateAction.php', 'Private');
$ACTIONS[] = array('CacheTest', 'Actions/examples/CacheTest.php', 'Public');
$ACTIONS[] = array('ObjectTest', 'Actions/examples/ObjectTest.php', 'Public');
$ACTIONS[] = array('CookieTest', 'Actions/examples/CookieTest.php', 'Public');
$ACTIONS[] = array('SlowAction', 'Actions/examples/SlowAction.php', 'Public');

// Demo actions for building a user system
$ACTIONS[] = array('UserAdd', 'Actions/examples/UserAdd.php', 'Public');
$ACTIONS[] = array('UserView', 'Actions/examples/UserView.php', 'Public');
$ACTIONS[] = array('UserEdit', 'Actions/examples/UserEdit.php', 'Public');
$ACTIONS[] = array('UserDelete', 'Actions/examples/UserDelete.php', 'Public');
$ACTIONS[] = array('LogIn', 'Actions/examples/LogIn.php', 'Public');

?>