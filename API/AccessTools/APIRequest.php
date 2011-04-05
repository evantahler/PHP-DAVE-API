<?php

/***********************************************
DAVE PHP API
https://github.com/evantahler/PHP-DAVE-API
Evan Tahler | 2011

I am an example class that can be used by another PHP application (perhaps the application that renders the consumer-facing website) to connect to the DAVE API

EXAMPLE USE:
	$PostArray = array( 
		"Action" => "A_DUMMY_ACTION", 
		"OutputType" => "PHP"
	);

	$API_URL = "127.0.0.1/API/"; // local host
	$APIRequest = new APIRequest($IP, $API_URL, $PostArray);
	$APIDATA = $APIRequest->DoRequest();
	if ($APIDATA != false)
	{
		echo "Your request came from ".$APIDATA['IP']." and took ".$APIDATA['ComputationTime']." seconds.";
	}
	else
	{
		echo 'Something is wrong with your URL or DAVE API configuration';
	}
	echo "\r\n\r\n";

***********************************************/
class APIRequest
{
	protected $PostArray, $response, $API_URL;
	
	public function __construct($API_URL="", $PostArray=array(), $IP="")
	{
		if (!is_array($PostArray)){ $PostArray = array(); }
		$this->PostArray = $PostArray;
		$this->IP = $IP;
		$this->API_URL = $API_URL;
	}
	
	private function httpsPost($Url, $PostRequest, $HTTP_headers)
	{
	   $ch=curl_init();
	   curl_setopt($ch, CURLOPT_URL, $Url);
	   curl_setopt($ch, CURLOPT_HEADER, 0);
	   curl_setopt($ch, CURLOPT_HTTPHEADER, array($HTTP_headers));
	   curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	   curl_setopt($ch, CURLOPT_POST, 1) ;
	   curl_setopt($ch, CURLOPT_POSTFIELDS, $PostRequest);
	   curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	   $result = curl_exec($ch);
	   curl_close($ch);
	   return $result;
	} 
		
	// I make the actual request
	public function DoRequest()
	{
		$PostString = "";
		foreach ($this->PostArray as $var => $val)
		{
			$PostString .= $var."=".$val."&";
		}
		
		$return = array();
		
		$PostRequest = "";
		$PostRequest .= ("IP=".$this->IP."&");
		$PostRequest .= $PostString;
		$PostRequest = utf8_encode($PostRequest);
		$Response = $this->httpsPost($this->API_URL, $PostRequest, "");
		$this->response = $Response;
		$return = unserialize($Response);
		return $return;
	}
	
	// return again
	public function ShowResponse()
	{
        return unserialize($this->response);
    }

	public function ShowRawResponse()
	{
        return $this->response;
    }
}

?>