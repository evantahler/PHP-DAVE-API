<?php

function is_bad_word($word)
{
	$result = false;
	
	// check for whole words
	$i = 0;
	$file = fopen("BadWords/badexact.txt","r");
	while(! feof($file))
	{
	  	$words[$i] = fgets($file);
	  	$words[$i] = substr($words[$i],0,-1);
	  	if(strcmp(strtolower($word),$words[$i]) == 0)
	  	{
	  		$result = true;
	  	}
	  	$i++;
	}
	fclose($file);		
	$size = count($words);	
			
	// check for containing words
	$i = 0;
	$file = fopen("BadWords/badpartial.txt","r");
	while(! feof($file))
	{
	  	$words[$i] = fgets($file);
	  	$words[$i] = substr($words[$i],0,-1);
		
	  	if ((strpos(strtolower($word),$words[$i])) > 0)
	  	{
	  		$result = true;
	  	}
	  	$i++;
	}
	fclose($file);		
	$size = count($words);

	return $result;
}

?>