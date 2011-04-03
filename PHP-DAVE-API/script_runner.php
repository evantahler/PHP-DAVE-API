<?php
/***********************************************
DAVE PHP API
https://github.com/evantahler/PHP-DAVE-API
Evan Tahler | 2011

I am the slave thread to the SERVER which will run the request.  I exist to catch exceptions and sandbox the running script from the main SERVER

*** Due to metaprogramming limitations in the default PHP installs on most servers/machines, it is impossible to modify the behavior of header() and setcookie().  To remedy this, please use _header() and _setcookie() in your DAVE projects.  You can see below that they will first attempt to use the default versions of these functions, and if they fail (AKA when using the StandAlone server), will emulate thier behavior in other ways. ***

Due to the implamention of this server captuing all script output in a buffer, setting headers and cookies should always cause that output to render to the end user before traditional output.
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
        echo "<b>My ERROR</b> [$errno] $errstr<br />\r\n";
        echo "  Fatal error on line $errline in file $errfile";
        echo ", PHP " . PHP_VERSION . " (" . PHP_OS . ")<br />\r\n";
        echo "Aborting...<br />\r\n";
        exit(1);
        break;

    case E_USER_WARNING:
        echo "<b>My WARNING</b> [$errno] $errstr<br />\r\n";
        break;

    case E_USER_NOTICE:
        echo "<b>My NOTICE</b> [$errno] $errstr<br />\r\n";
        break;

    default:
        echo "Unknown error type: [$errno] $errstr<br />\r\n";
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

// header
$_HEADER = array();
function _header($string)
{
	global $_HEADER;
	$out = @header($string);
	if ($out === false || $out === NULL)
	{
		$_HEADER[] = $string;
		return true;
	}
}

// cookie
function _setcookie($name, $value = null, $expire = null, $path = null, $domain = null, $secure = null, $httponly = null)
{
	$out = @setcookie($name, $value, $expire, $path, $domain, $secure, $httponly);
	if ($out === false)
	{
		// TODO: Handle $domain, $secure and $httponly
		
		if (!($expire > 0)){$expire = time() + 60*60;} // 1 hour default cookie duration
		$datetime = new DateTime(date("Y-m-d H:i:s",$expire));
		$cookie_time = $datetime->format(DATE_COOKIE);
		if ($path == null){$path = "/";}
		$ret .= "Set-Cookie: ".urlencode($name)."=".urlencode($value)."; expires=".$cookie_time."; path=".$path.";";
		_header($ret);
		return true;
	}
}

// send the empty buffer to force all header and cookie functions to fail
ob_start();ob_end_flush(); 

// output buffer
ob_start();
require($_FILE);
echo "<<HEADER_BREAK>>";
foreach($_HEADER as $header)
{
	echo $header."<<HEADER_LINE_BREAK>>";
}
ob_end_flush();
?>