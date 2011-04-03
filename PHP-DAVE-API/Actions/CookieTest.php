<?php

/***********************************************
DAVE PHP API
https://github.com/evantahler/PHP-DAVE-API
Evan Tahler | 2011

I am an example of how to set cookies using the new modified functions for the DAVE server (which will still work on a traditional fastCGI server as well)
***********************************************/

_setcookie("UpperLimit", rand(), time() + 60);
_setcookie("LowerLimit", rand(), time() + 60);
// _header("Location: /PHP-DAVE-API/not_a_folder/");

?>