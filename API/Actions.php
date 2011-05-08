<?php
/***********************************************
DAVE PHP API
https://github.com/evantahler/PHP-DAVE-API
Evan Tahler | 2011

I an the list of $Actions this API will handle, the path to them, and thier permission structure
Actions, defined as "verb",  then "page location", then "Public" or "Private" indicatiing if an APIKey is needed to access the function. Finally, an optional restful URL path can be given as a proxy way to access the action.
array(ActionName, Path-to-action, public/private, restful-url-path)
***********************************************/
$ACTIONS = array();

// default actions
$ACTIONS[] = array('DescribeActions', 'Actions/examples/DescribeActions.php', 'Public', '/DescribeActions');
$ACTIONS[] = array('DescribeTables', 'Actions/examples/DescribeTables.php', 'Public', '/DescribeTables');

// some basic actions
$ACTIONS[] = array('PrivateAction', 'Actions/examples/PrivateAction.php', 'Private', '/PrivateAction');
$ACTIONS[] = array('CacheTest', 'Actions/examples/CacheTest.php', 'Public', '/CacheTest');
$ACTIONS[] = array('ObjectTest', 'Actions/examples/ObjectTest.php', 'Public', '/ObjectTest');
$ACTIONS[] = array('CookieTest', 'Actions/examples/CookieTest.php', 'Public', '/CookieTest');
$ACTIONS[] = array('SlowAction', 'Actions/examples/SlowAction.php', 'Public', '/SlowAction');

// Demo actions for building a user system
$ACTIONS[] = array('UserAdd', 'Actions/examples/UserAdd.php', 'Public', '/User/Add');
$ACTIONS[] = array('UserView', 'Actions/examples/UserView.php', 'Public', '/User/View');
$ACTIONS[] = array('UserEdit', 'Actions/examples/UserEdit.php', 'Public', '/User/Edit');
$ACTIONS[] = array('UserDelete', 'Actions/examples/UserDelete.php', 'Public', '/User/View');
$ACTIONS[] = array('LogIn', 'Actions/examples/LogIn.php', 'Public', '/LogIn');

// Demo actions for a simple chat protocol 
$ACTIONS[] = array('LobbyAdd', 'Actions/examples/LobbyAdd.php', 'Public', '/Lobby/Add');
$ACTIONS[] = array('LobbyView', 'Actions/examples/LobbyView.php', 'Public', '/Lobby/View');
$ACTIONS[] = array('LobbyAuthenticate', 'Actions/examples/LobbyAuthenticate.php', 'Public', '/Lobby/Authenticate');

$ACTIONS[] = array('MessageAdd', 'Actions/examples/MessageAdd.php', 'Public', '/Message/Add');
$ACTIONS[] = array('MessageView', 'Actions/examples/MessageView.php', 'Public', '/Message/View');

?>