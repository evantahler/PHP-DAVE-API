<?php
/***********************************************
DAVE PHP API
https://github.com/evantahler/PHP-DAVE-API
Evan Tahler | 2011

I am an example of how to set cookies using the new modified functions for the DAVE server (which will still work on a traditional fastCGI server as well)
***********************************************/
$NewUpperLimit = rand();
$NewLowerLimit = rand();
_setcookie("UpperLimit", $NewUpperLimit);
_setcookie("LowerLimit", $NewLowerLimit);

$OUTPUT["COOKIE_TEST"]["OldUpperLimit"] = $PARAMS['UpperLimit'];
$OUTPUT["COOKIE_TEST"]["NewUpperLimit"] = $NewUpperLimit;
$OUTPUT["COOKIE_TEST"]["OldLowerLimit"] = $PARAMS['LowerLimit'];
$OUTPUT["COOKIE_TEST"]["NewLowerLimit"] = $NewLowerLimit;
$OUTPUT["COOKIE_TEST"]["NOTE"] = "The New limits should be set in your cookies.  Load this page again to see the change as read by the API.";

// _header("Location: /PHP-DAVE-API/not_a_folder/");

?>