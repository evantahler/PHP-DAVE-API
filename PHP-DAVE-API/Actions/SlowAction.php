<?php

/***********************************************
DAVE PHP API
https://github.com/evantahler/PHP-DAVE-API
Evan Tahler | 2011

I am a slow action which can be used for profiling or parallelization testing.  You can pass me UpperLimit in seconds to tell me how  long to sleep for, or I'll use 10 sec as a default
***********************************************/

if (!($PARAMS["UpperLimit"] > 0)){
	$sleep_time = 10;
} else {
	$sleep_time = $PARAMS["UpperLimit"];
}

sleep($sleep_time);

$OUTPUT["SLEEP_TIME"] = $sleep_time;

?>