<?php
/***********************************************
DAVE PHP API
https://github.com/evantahler/PHP-DAVE-API
Evan Tahler | 2011

I am the slave thread to the SERVER which will run the request.  I exist to catch exceptions and sandbox the running script from the main SERVER
***********************************************/
function __parseArgs(){
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

function __ErrorHandler($errno, $errstr, $errfile, $errline)
{
    if (!(error_reporting() & $errno)) {
        // This error code is not included in error_reporting
        return;
    }

    switch ($errno) {
    case E_USER_ERROR:
        echo "<b>My ERROR</b> [$errno] $errstr<br />\n";
        echo "  Fatal error on line $errline in file $errfile";
        echo ", PHP " . PHP_VERSION . " (" . PHP_OS . ")<br />\n";
        echo "Aborting...<br />\n";
        exit(1);
        break;

    case E_USER_WARNING:
        echo "<b>My WARNING</b> [$errno] $errstr<br />\n";
        break;

    case E_USER_NOTICE:
        echo "<b>My NOTICE</b> [$errno] $errstr<br />\n";
        break;

    default:
        echo "Unknown error type: [$errno] $errstr<br />\n";
        break;
    }

    /* Don't execute PHP internal error handler */
    return true;
}
$old_error_handler = set_error_handler("__ErrorHandler");

$_GET = array();
$_POST = array();
$_COOKIE = array();
$_REQUEST = array();

$__input = @__parseArgs();
$_GET = @unserialize($__input["GET"]);
$_POST = @unserialize($__input["POST"]);
$_SERVER = @unserialize($__input["SERVER"]);
$_COOKIE = @unserialize($__input["COOKIE"]);
$_FILE = @unserialize($__input["FILE"]);

foreach ($_GET as $k => $v){ $_REQUEST[$k] = $v; }
foreach ($_POST as $k => $v){ $_REQUEST[$k] = $v; }
foreach ($_COOKIE as $k => $v){ $_REQUEST[$k] = $v; }

ob_start();
require($_FILE);
ob_end_flush();
?>