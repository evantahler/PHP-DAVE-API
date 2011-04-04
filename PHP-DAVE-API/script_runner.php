<?php
/***********************************************
DAVE PHP API
https://github.com/evantahler/PHP-DAVE-API
Evan Tahler | 2011

I am the slave thread to the SERVER which will run the request.  I exist to catch exceptions and sandbox the running script from the main SERVER

*** Due to metaprogramming limitations in the default PHP installs on most servers/machines, it is impossible to modify the behavior of header() and setcookie().  To remedy this, please use _header() and _setcookie() in your DAVE projects.  You can see below that they will first attempt to use the default versions of these functions, and if they fail (AKA when using the StandAlone server), will emulate thier behavior in other ways. ***

Due to the implamention of this server captuing all script output in a buffer, setting headers and cookies should always cause that output to render to the end user before traditional output.

Here's an example of the collection of params how I might be called from SERVER:

/usr/bin/php /PROJECTS/php-dave-api/PHP-DAVE-API/script_runner.php --FILE=s:45:\"/PROJECTS/php-dave-api/PHP-DAVE-API/index.php\"\; --SERVER=a:5:\{s:8:\"PHP_SELF\"\;s:9:\"index.php\"\;s:11:\"SERVER_ADDR\"\;s:9:\"localhost\"\;s:11:\"SERVER_NAME\"\;s:9:\"localhost\"\;s:15:\"SERVER_PROTOCOL\"\;s:8:\"HTTP/1.0\"\;s:11:\"REMOTE_ADDR\"\;s:9:\"127.0.0.1\"\;\} --GET=a:0:\{\} --POST=a:3:\{s:13:\"LimitLockPass\"\;s:6:\"Sekret\"\;s:10:\"OutputType\"\;s:3:\"XML\"\;s:6:\"Action\"\;s:0:\"\"\;\} --COOKIE=a:0:\{\} --CLIENT_ID=i:0\; --PARENT_PORT=i:3001\; --PARENT_URL=s:9:\"localhost\"\;

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
$__FILE = @unserialize($__input["FILE"]);
$__CLIENT_ID = @unserialize($__input["CLIENT_ID"]);
$__PARENT_URL = @unserialize($__input["PARENT_URL"]);
$__PARENT_PORT = @unserialize($__input["PARENT_PORT"]);

echo "-->".$__PARENT_URL."\r\n\r\n";

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
require($__FILE);
echo "<<HEADER_BREAK>>";
foreach($_HEADER as $header)
{
	echo $header."<<HEADER_LINE_BREAK>>";
}
echo "<<CLIENT_ID_BREAK>>".$__CLIENT_ID;
$__OUT = ob_get_contents(); 
ob_end_flush();

// send data to parent
if (strlen($__PARENT_URL) > 0)
{
	$socket = stream_socket_client("tcp://".$__PARENT_URL.":".$__PARENT_PORT, $errno, $errstr);
	if ($socket)
	{
		fwrite($socket, serialize($__OUT));
	}
	fclose($socket);
}

?>