<?php
/***********************************************
DAVE PHP API
https://github.com/evantahler/PHP-DAVE-API
Evan Tahler | 2011

I am a single-client-at-a-time basic PHP webserver.  I can be used to test PHP-DAVE-API application locally by running "php SERVER.php".
How to test post: curl -d "param1=value1&param2=value2" http://localhost:3000/some/page/php
***********************************************/

// CONFIG
/////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////

require("CONFIG.php");

function _server_log($string)
{
	global $ServerLog;
	$string = date("m-d-Y H:i:s")." | ".$string."\r\n";
    print($string);
    if (!(file_exists($ServerLog)))
    {
		$Logfh = fopen($ServerLog, 'a');
		fwrite($Logfh, "");
		fclose($Logfh);
		chmod($ServerLog, 0777);
    }
    $Logfh = fopen($ServerLog, 'a');
    fwrite($Logfh, $string);
    fclose($Logfh);
}

function SendDataToClient($ClientData, $Message)
{
        if (isset($ClientData['sock']))
        {
                @socket_write($ClientData['sock'], $Message."\r\n");
                ob_end_clean();
        }
}

function EndTransfer($ClientData)
{
	global $client;
	socket_close($ClientData['sock']);
    unset($ClientData);
    $client = array_values($client);
}

function make_headers($error_code = 200, $URL, $cookies = null)
{
	global $domain;
	
	$out = "HTTP/1.0 ";
	if ($error_code == 400){$out .= "400 Bad Request\r\n";}
	elseif ($error_code == 403){$out .= "403 Forbidden\r\n";}
	elseif ($error_code == 404){$out .= "404 Not Found\r\n";}
	elseif ($error_code == 500){$out .= "500 Internal Server Error\r\n";}
	elseif ($error_code == 501){$out .= "501 Not Implemented\r\n";}
	else {$out .= "200 OK\r\n";}
	$out .= "Connection: close\r\n";
	$out .= "Server: DaveMiniTestServer\r\n";
	$out .= "Content-Type: ".get_content_type($URL)."\r\n";
	if ($cookies == null){$cookies = array();}
	foreach ($cookies as $cookie)
	{
		if (!($cookie["expire_timestamp"] > 0)){$cookie["expire_timestamp"] = time() + 60*60;}
		$datetime = new DateTime($cookie["expire_timestamp"]);
		$cookie_time = $datetime->format(DATE_COOKIE);
		$out .= "Set-Cookie: ".$cookie["variable"]."=".$cookie["value"]."; expires=".$cookie_time."; path=/; domain=".$domain."\r\n";
	}
	return $out;
}

function get_content_type($URL)
{
	// http://en.wikipedia.org/wiki/Internet_media_type
	$tmp = explode(".",$URL);
	$extension = $tmp[(count($tmp) - 1)];
	$extension = strtolower($extension);
	$out = "text/html";
	if ($extension == "html"){ $out = "text/html"; }
	elseif ($extension == "htmls"){ $out = "text/html"; }
	elseif ($extension == "php"){ $out = "text/html"; }
	elseif ($extension == "xml"){ $out = "text/xml"; }
	elseif ($extension == "jpg"){ $out = "image/jpeg"; }
	elseif ($extension == "jpeg"){ $out = "image/jpeg"; }
	elseif ($extension == "png"){ $out = "image/png"; }
	elseif ($extension == "gif"){ $out = "image/gif"; }
	elseif ($extension == "css"){ $out = "text/css"; }
	elseif ($extension == "js"){ $out = "text/javascript"; }
	elseif ($extension == "mp4"){ $out = "video/mp4"; }
	return $out;
}

function _run($URL, $remote_ip) 
{
	global $_GET, $_POST, $_COOKIE, $domain;
	
	$_SERVER = array(
		"PHP_SELF" => $URL,
		"SERVER_ADDR" => $domain,
		"SERVER_NAME" => $domain,
		"SERVER_PROTOCOL" => "HTTP/1.0",
		"REMOTE_ADDR" => $remote_ip,
	);
	$_FILE = getcwd()."/".$URL;

	$sys = escapeshellcmd("/usr/bin/php ".getcwd()."/script_runner.php --FILE=".serialize($_FILE)." --SERVER=".serialize($_SERVER)." --GET=".serialize($_GET)." --POST=".serialize($_POST)." --COOKIE=".serialize($_COOKIE));
	$sys = str_replace('"','\"',$sys);
	$script_output = `$sys`;
	return $script_output;
}

// INIT
/////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////

/* Setup */
$ServerStartTime = time();
set_time_limit (0);
ini_set( 'default_socket_timeout', (60*60)); // 60 min keep alive

$client = array();
$sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
socket_set_nonblock($sock);
socket_set_option($sock, SOL_SOCKET, SO_REUSEADDR, 1);
$j = 0;
while (@socket_bind($sock, 0, $ServerPort) == false)
{
        sleep($Sleeper);
        $j++;
        if ($j > 3)
        {
                _server_log('Server already running on port '.$ServerPort);
                exit;
                break;
        }
}
        
// Start listening for connections
socket_listen($sock);
_server_log('..........Starting Server @ port '.$ServerPort.'..........');

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
/* LOOP FOREVER! */

while (true) {
    // Setup clients listen socket for reading
    $read[0] = $sock;
    for ($i = 0; $i < $max_clients; $i++)
    {
        if ($client[$i]['sock']  != null)
            $read[$i + 1] = $client[$i]['sock'] ;
    }
    // Set up a blocking call to socket_select()
    $ready = @socket_select($read, $write = NULL, $except = NULL, $tv_sec = 0, $tv_usec = 100000);

    /* if a new connection is being made add it to the client array */
    if (in_array($sock, $read)) {
        for ($i = 0; $i < $max_clients; $i++)
        {
            if ($client[$i]['sock'] == null) 
            {
                $client[$i]['sock'] = socket_accept($sock);
                // set defaults
                socket_getpeername($client[$i]['sock'],$ip,$RemotePort);
                $client[$i]['IP'] = $ip;
                break;
            }
            elseif ($i == $max_clients - 1) { _server_log(("too many clients"), $LogFile); }
        }
        if (--$ready <= 0) 
            continue;
    } 
    
    // If a client is trying to write - handle it now
    for ($i = 0; $i < $max_clients; $i++) // for each client
    {
        if (in_array($client[$i]['sock'] , $read))
        {
            $input = @socket_read($client[$i]['sock'] , 1024*1024, PHP_BINARY_READ);
			$input = trim($input);
            if(strlen($input) < 1){ break; }
			else
			{				
				$_COOKIE = array();
				$_POST = array();
				$_GET = array();
				$URL = "";
				
				$lines = explode("\n", $input);
				foreach($lines as $line)
				{
					// GET
					if (substr($line,0,4) == "GET ")
					{
						$sections = explode(" ",$line);
						$full_url = $sections[1];
						$a = explode("?",$full_url);
						$URL = $a[0];
						$get = $a[1];
						$vars = explode("&",$get);
						foreach($vars as $var)
						{
							$var = trim($var);
							$sub = explode("=",$var);
							if (strlen($sub[0]) > 0) { $_GET[$sub[0]] = $sub[1]; }
						}
					}
					
					// POST
					if (substr($line,0,5) == "POST ")
					{
						$sections = explode(" ",$line);
						$full_url = $sections[1];
						$a = explode("?",$full_url);
						$URL = $a[0];
						$get = $a[1];
						$vars = explode("&",$get);
						foreach($vars as $var)
						{
							$var = trim($var);
							$sub = explode("=",$var);
							if (strlen($sub[0]) > 0) { $_GET[$sub[0]] = $sub[1]; }
						}
						$post = $lines[(count($lines) - 1)];
						$vars = explode("&",$post);
						foreach($vars as $var)
						{
							$var = trim($var);
							$sub = explode("=",$var);
							if (strlen($sub[0]) > 0) { $_POST[$sub[0]] = $sub[1]; }
						}
					}
					
					// COOKIES
					if (substr($line,0,8) == "Cookie: ")
					{
						$line = substr($line,8);
						$vars = explode(";",$line);
						foreach($vars as $var)
						{
							$var = trim($var);
							$sub = explode("=",$var);
							$_COOKIE[$sub[0]] = $sub[1];
						}
					}					
				}
				
				_server_log("[".$client[$i]["IP"]."] REQUEST: ".$URL." | ".count($_COOKIES)." cookies | ".count($_GET)." get vars | ".count($_POST)." post vars");
				
				// Return page content
				if ($URL == "/"){ $URL = "index.php"; }
				else{ $URL = substr($URL,1); }
				$StartTime = time();
				if(file_exists($URL))
				{
					$contents = file_get_contents($URL);
					if ($contents === false){
						$headers = make_headers(404, $URL);
						SendDataToClient($client[$i], $headers);
						SendDataToClient($client[$i], "Error: 404");
						_server_log("[".$client[$i]["IP"]."] -> "."404 [".(time() - $StartTime)."s]");
					}
					else
					{
						if (substr($URL,-4) == ".php")
						{
							$script_output = _run($URL, $client[$i]["IP"]);
							if ($script_output == ""){$script_output = "ERROR.  Please check the server log for more information.";}
							$headers = make_headers(200, $URL);
							SendDataToClient($client[$i], $headers);
							SendDataToClient($client[$i], $script_output);
							_server_log("[".$client[$i]["IP"]."] -> "."200 (php) [".(time() - $StartTime)."s]");
						}
						else
						{
							$headers = make_headers(200, $URL);
							SendDataToClient($client[$i], $headers);
							SendDataToClient($client[$i], $contents);
							_server_log("[".$client[$i]["IP"]."] -> "."200 (not php) [".(time() - $StartTime)."s]");
						}
					}
				}
				else
				{
					$headers = make_headers(404, $URL);
					SendDataToClient($client[$i], $headers);
					SendDataToClient($client[$i], "Error: 404");
					_server_log("[".$client[$i]["IP"]."] -> "."404 [".(time() - $StartTime)."s]");
				}
			}
			EndTransfer($client[$i]);
        }
    }
}
socket_close($sock);

?>