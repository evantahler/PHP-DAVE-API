<?php

/***********************************************
Evan Tahler
evantahler@gmail.com
2010

I handle formatting the $OUTPUT object into XML, JSON, etc

***********************************************/

if ($OutputType == "")
{
	$OutputType = $DefaultOutputType;
}

if ($OutputType == "VAR")
{
	var_dump($OUTPUT);
}

elseif ($OutputType == "PHP")
{
	echo serialize($OUTPUT);
}

elseif ($OutputType == "XML")
{
	function _DepthArrayPrint($Array,$depth,$container=null)
	{
		if (strlen($container) > 0)
		{
			$j = 0;
			while ($j < ($depth-1)) { echo "\t"; $j++; }
			echo '<'.(string)$container.'>'."\r\n";
		}
		
		$i = 0;
		$keys = array_keys($Array);
		while ($i < count($Array))
		{
			if (is_array($Array[$keys[$i]]))
			{
				_DepthArrayPrint($Array[$keys[$i]],($depth+1),$keys[$i]);
			}
			else
			{
				$j = 0;
				while ($j < $depth) { echo "\t"; $j++; }
				if (strlen($keys[$i]) > 0)
				{
					print "<".(string)$keys[$i].">".(string)$Array[$keys[$i]]."</".(string)$keys[$i].">"."\r\n";
				}
			}
			$i++;
		}
		
		if (strlen($container) > 0)
		{
			$j = 0;
			while ($j < ($depth-1)) { echo "\t"; $j++; }
			echo '</'.(string)$container.'>'."\r\n";
		}
	}
	//
	echo '<?xml version="1.0" encoding="UTF-8"?>'."\r\n";
	echo '<'.$XML_ROOT_NODE.'>'."\r\n";
	_DepthArrayPrint($OUTPUT,1);
	echo '</'.$XML_ROOT_NODE.'>'."\r\n";
}

elseif ($OutputType == "JSON")
{
	$JSON = json_encode($OUTPUT);
	if (strlen($Callback) > 0)
	{
		echo $Callback."([".$JSON."]);";
	}
	else
	{
		echo $JSON;
	}
}

elseif ($OutputType == "SOAP")
{	
	// coming soon
	echo "SOAP support coming soon";
	
	//$server = new SoapServer("tmp.wsdl"); 
	//ini_set("soap.wsdl_cache_enabled", "0"); // disabling WSDL cache 
	//$server->addFunction($Action); 
	//$server->handle(); 

}

else
{
	echo 'I am sorry, but I do not regonize that OutputType.  Leave that parameter blank for the default option.';
}

flush();

?>