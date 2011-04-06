<?php
/***********************************************
DAVE PHP API
https://github.com/evantahler/PHP-DAVE-API
Evan Tahler | 2011

I hold all the configuration variables for the API
***********************************************/

$SERVER = array();
$SERVER['public_port'] = 3000;
$SERVER['internal_port'] = 3001;
$SERVER['poll_timeout'] = 10000; // in micro-seconds.
$SERVER['max_clients'] = 100;
$SERVER['domain'] = "localhost";
$SERVER['root_path'] = "../API/"; // from location of SERVER.php
$SERVER['PHP_Path'] = "/usr/bin/php";
$SERVER['systemTimeZone'] = "America/Los_Angeles";
$SERVER['log_file'] = "LOG/SERVER_LOG.txt";
$SERVER['timeout'] = 10; //how long to wait (seconds) before returning to the client with a 500 error

@mkdir("LOG");

?>