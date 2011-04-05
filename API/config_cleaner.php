<?php

/***********************************************
DAVE PHP API
https://github.com/evantahler/PHP-DAVE-API
Evan Tahler | 2011

After reading and parsing the Config files, I do some cleanup on the defined input variables and ensure uniqueness
***********************************************/

// Add Table keys as POST_VARIABLES
$i = 0;
if (count($TABLES) > 0)
{
	$TableNames = array_keys($TABLES);
	while ($i < count($TABLES))
	{
		$j = 0;
		while ($j < count($TABLES[$TableNames[$i]]))
		{
			$POST_VARIABLES[] = $TABLES[$TableNames[$i]][$j][0];
			$j++;
		}
		$i++;
	}
}

// do a doubles check on the POST_VARIABLES 
$POST_VARIABLES = array_unique($POST_VARIABLES);

?>