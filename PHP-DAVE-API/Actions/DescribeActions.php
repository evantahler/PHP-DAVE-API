<?php
/***********************************************
DAVE PHP API
https://github.com/evantahler/PHP-DAVE-API
Evan Tahler | 2011

I am a simple action that will return all the Actions that this API can preform.  I'm kind of like a WSDL :p
***********************************************/
if ($ERROR == 100)
{
	$OUTPUT["Actions"] = humanize_actions();
}

?>