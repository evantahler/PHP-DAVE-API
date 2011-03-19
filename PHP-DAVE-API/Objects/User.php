<?php
/***********************************************
DAVE PHP API
https://github.com/evantahler/PHP-DAVE-API
Evan Tahler | 2011

I am the DAVEObject for the User table
***********************************************/

class User extends DaveRowObject
{
	public function validate_and_configure_new_user()
	{
		if (strlen($this->DATA["EMail"]) > 0)
		{
			$func_out = validate_EMail($this->DATA["EMail"]);
			if ($func_out != 100){ return $func_out; }
		}
		
		if (strlen($this->DATA["PhoneNumber"]) > 0)
		{
			list($fun_out, $this->DATA["PhoneNumber"]) = validate_PhoneNumber($this->DATA["PhoneNumber"]);
			if ($func_out != 100){ return $func_out; }
		}
		
		if (strlen($this->DATA["Password"]) > 0)
		{
			$this->DATA["Salt"] = md5(rand(1,999).(microtime()/rand(1,999)).rand(1,999));
			$this->DATA["PasswordHash"] = md5($this->DATA["Password"].$this->DATA["Salt"]);
			return true;
		}
		else { return "Please provide a Password"; }
	} 
}

?>