<?php
/***********************************************
DAVE PHP API
https://github.com/evantahler/PHP-DAVE-API
Evan Tahler | 2011

I am the slave thread to the SERVER which will run the request.  I exist to catch exceptions and sandbox the running script from the main SERVER
***********************************************/
require("helper_functions/parseArgs.php");
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