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
$SERVER['root_path'] = "../API/"; // from location of SERVER.php.  Ensure that php user has permissions on this path.
$SERVER['PHP_Path'] = "/usr/bin/php";
$SERVER['SystemTimeZone'] = "America/Los_Angeles";
$SERVER['log_file'] = "log/SERVER_LOG.txt";
$SERVER['timeout'] = 10; //how long to wait (seconds) before returning to the client with a 500 error

@mkdir("log");

function parseArgs(){
	global $argv;
    array_shift($argv);
    $out = array();
    foreach ($argv as $arg){
        if (substr($arg,0,2) == '--'){
            $eqPos = strpos($arg,'=');
            if ($eqPos === false){
                $key = substr($arg,2);
                $out[$key] = isset($out[$key]) ? $out[$key] : true;
            } else {
                $key = substr($arg,2,$eqPos-2);
                $out[$key] = substr($arg,$eqPos+1);
            }
        } else if (substr($arg,0,1) == '-'){
            if (substr($arg,2,1) == '='){
                $key = substr($arg,1,1);
                $out[$key] = substr($arg,3);
            } else {
                $chars = str_split(substr($arg,1));
                foreach ($chars as $char){
                    $key = $char;
                    $out[$key] = isset($out[$key]) ? $out[$key] : true;
                }
            }
        } else {
            $out[] = $arg;
        }
    }
    return $out;
}

$ARGS = parseArgs();

?>