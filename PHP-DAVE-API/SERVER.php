<?php
/***********************************************
DAVE PHP API
https://github.com/evantahler/PHP-DAVE-API
Evan Tahler | 2011

I am a single-client-at-a-time basic PHP webserver.  I can be used to test PHP-DAVE-API application locally by running "php SERVER.php".
How to test post: curl -d "param1=value1&param2=value2" http://localhost:3000/some/page/php

*** Due to metaprogramming limitations in the default PHP installs on most servers/machines, it is impossible to modify the behavior of header() and setcookie().  To remedy this, please use _header() and _setcookie() in your DAVE projects.  You can see below that they will first attempt to use the default versions of these functions, and if they fail (AKA when using the StandAlone server), will emulate thier behavior in other ways. ***

TODO: Overwrite the SetCookie method for setting cookies (probably within the script_runner)
TODO: Currently the _run method will block until a request completes.  USE background exec and PID tracking to solve this
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

function clean_body_output($script_output)
{
	$tmp = explode("<<HEADER_BREAK>>", $script_output);
	return trim($tmp[0]);
}

function make_headers($error_code = 200, $URL, $script_output = "")
{
	global $domain;
	
	$extra_lines = array();
	$code_override = false;
	if ($script_output != null)
	{
		$tmp = explode("<<HEADER_BREAK>>", $script_output);
		$header_lines = explode("<<HEADER_LINE_BREAK>>", $tmp[1]);
		foreach($header_lines as $line)
		{
			if (strlen($line) > 0)
			{
				$extra_lines[] = $line; 
				if (strpos($line,"Location:") !== false){$code_override = "301 Moved Permanently\r\n";}
			}
		}
	}
	
	$out = "HTTP/1.0 ";
	if ($code_override != false) {$out .= $code_override;}
	else
	{
		if ($error_code == 400){$out .= "400 Bad Request\r\n";}
		elseif ($error_code == 403){$out .= "403 Forbidden\r\n";}
		elseif ($error_code == 404){$out .= "404 Not Found\r\n";}
		elseif ($error_code == 500){$out .= "500 Internal Server Error\r\n";}
		elseif ($error_code == 501){$out .= "501 Not Implemented\r\n";}
		else {$out .= "200 OK\r\n";}
	}
	$out .= "Connection: close\r\n";
	$out .= "Server: DaveServer\r\n";
	$out .= "Content-Type: ".get_content_type($URL)."\r\n";

	foreach ($extra_lines as $line){ $out .= $line."\r\n"; }

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
					if (strpos($line,"GET ") !== false)
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
					if (strpos($line,"POST ") !== false)
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
					if (strpos($line,"Cookie: ") !== false)
					{
						$line = substr($line,8);
						$vars = explode("; ",$line);
						foreach($vars as $var)
						{
							$var = trim($var);
							$sub = explode("=",$var);
							$_COOKIE[$sub[0]] = $sub[1];
						}
					}		
				}
				
				_server_log("[".$client[$i]["IP"]."] REQUEST: ".$URL." | ".count($_COOKIE)." cookies | ".count($_GET)." get vars | ".count($_POST)." post vars");
				
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
							if ($script_output == ""){$script_output = "SERVER ERROR.  Please check the server log for more information.";}
							$headers = make_headers(200, $URL, $script_output);
							SendDataToClient($client[$i], $headers);
							if ( strpos($headers,"301 Moved Permanently") !== false)
							{
								$header_lines = explode("\r\n", $headers);
								foreach($header_lines as $line)
								{
									if (strpos($line,"Location:") !== false)
									{
										$new_loc = substr($line,8,(strlen($line) - 1));
									}
								}
								$out = "<!DOCTYPE HTML PUBLIC \"-//IETF//DTD HTML 2.0//EN\">\r\n<html><head>\r\n<title>301 Moved Permanently</title>\r\n</head><body>\r\n<h1>Moved Permanently</h1>\r\n<p>The document has moved <a href=\"".$new_loc."\">here</a>.</p>\r\n</body></html>";
								SendDataToClient($client[$i],$out);
							}
							else
							{
								SendDataToClient($client[$i], clean_body_output($script_output));
							}
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