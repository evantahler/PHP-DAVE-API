<?php
/***********************************************
DAVE PHP API
https://github.com/evantahler/PHP-DAVE-API
Evan Tahler | 2011

I am the slave thread to the SERVER which will run the request.  I exist to catch exceptions and sandbox the running script from the main SERVER

*** Due to metaprogramming limitations in the default PHP installs on most servers/machines, it is impossible to modify the behavior of header() and setcookie().  To remedy this, please use _header() and _setcookie() in your DAVE projects.  You can see below that they will first attempt to use the default versions of these functions, and if they fail (AKA when using the StandAlone server), will emulate thier behavior in other ways. ***

Due to the implamention of this server captuing all script output in a buffer, setting headers and cookies should always cause that output to render to the end user before traditional output.

Here's an example of the collection of params how I might be called from SERVER:

/usr/bin/php /PROJECTS/php-dave-api/SERVER/script_runner.php --FILE=s:45:\"/PROJECTS/php-dave-api/API/index.php\"\; --SERVER=a:5:\{s:8:\"PHP_SELF\"\;s:9:\"index.php\"\;s:11:\"SERVER_ADDR\"\;s:9:\"localhost\"\;s:11:\"SERVER_NAME\"\;s:9:\"localhost\"\;s:15:\"SERVER_PROTOCOL\"\;s:8:\"HTTP/1.0\"\;s:11:\"REMOTE_ADDR\"\;s:9:\"127.0.0.1\"\;\} --GET=a:0:\{\} --POST=a:3:\{s:13:\"LimitLockPass\"\;s:6:\"Sekret\"\;s:10:\"OutputType\"\;s:3:\"XML\"\;s:6:\"Action\"\;s:0:\"\"\;\} --COOKIE=a:0:\{\} --CLIENT_ID=i:0\; --PARENT_PORT=i:3001\; --PARENT_URL=s:9:\"localhost\"\;

***********************************************/

// FROM PHP.NET
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

function __SendToParent($string)
{
	global $__PARENT_URL, $__PARENT_PORT, $__CLIENT_ID;
	$string = $string."<<CLIENT_ID_BREAK>>".$__CLIENT_ID;
	if (strlen($__PARENT_URL) > 0)
	{
		$socket = stream_socket_client("tcp://".$__PARENT_URL.":".$__PARENT_PORT, $errno, $errstr);
		if ($socket)
		{
			fwrite($socket, serialize($string));
		}
		@fclose($socket);
	}
}

function __ErrorHandler($errno, $errstr, $errfile, $errline)
{
	$error_string = "<<PHP_ERROR>>ERROR @ ";
	$deets = debug_backtrace();
	$error_string .= "line ".$deets[1]["line"]." > ";
	
    if (!(error_reporting() & $errno)) {
        return;
    }

    switch ($errno) {
    case E_USER_ERROR:
        $error_string .= "<b>ERROR</b> [$errno] $errstr<br />\r\n";
        $error_string .= "  Fatal error on line $errline in file $errfile";
        $error_string .= ", PHP " . PHP_VERSION . " (" . PHP_OS . ")<br />\r\n";
        $error_string .= "Aborting...<br />\r\n";
        break;

    case E_USER_WARNING:
        $error_string .= "<b>WARNING</b> [$errno] $errstr<br />\r\n";
        break;

    case E_USER_NOTICE:
        $error_string .= "<b>NOTICE</b> [$errno] $errstr<br />\r\n";
        break;

    default:
        $error_string .= "Unknown error type: [$errno] $errstr<br />\r\n";
        break;
    }
	
	echo $error_string;
	__SendToParent($error_string);

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
	elsle {return true;}
}

// cookie
function _setcookie($name, $value = null, $expire = null, $path = null, $domain = null, $secure = null, $httponly = null)
{
	global $SERVER;
	$out = @setcookie($name, $value, $expire, $path, $domain, $secure, $httponly);
	if ($out === false)
	{
		// TODO: Handle $domain, $secure and $httponly
		
		if (!($expire > 0)){$expire = time() + 60*60*24;} // 1 day default cookie duration
		$datetime = new DateTime(date("Y-m-d H:i:s",$expire));
		$cookie_time = $datetime->format(DATE_COOKIE);
		if ($path == null){$path = "/";}
		if ($domain == null){$domain = $SERVER['domain'];}
		$ret .= "Set-Cookie: ".urlencode($name)."=".urlencode($value)."; expires=".$cookie_time."; path=".$path."; domain=".$domain.";";
		_header($ret);
		return true;
	}
	elsle {return true;}
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
$__OUT = ob_get_contents(); 
ob_end_flush();

__SendToParent($__OUT);

?>