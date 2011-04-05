<?php
/***********************************************
DAVE PHP API
https://github.com/evantahler/PHP-DAVE-API
Evan Tahler | 2011

I am the base class for a DAVE object.  Many of the ideas here are inspired by the Rails ActiveRecord framework.

***********************************************/

class DaveTableObject
{
	protected $Table, $Columns, $Status;
	
	public function __construct($Table)
	{
		global $TABLES, $OUTPUT, $Status;
		$this->Status = false;
		$this->Columns = array();
		if(count($TABLES[$Table]) > 0)	
		{
			$this->Columns = $TABLES[$Table];
			$this->Table = $Table;
			$this->Status = true;
			return $this->Status;
		}	
		else
		{
			return $this->Status;
		}
	}
	
	public function status()
	{
		return $this->Status;
	}
	
	public function table()
	{
		if (!$this->Status){return false;}
		return $this->Table;
	}
	
	public function columns()
	{
		if (!$this->Status){return false;}
		return $this->Columns;
	}
	
	public function column_names()
	{
		if (!$this->Status){return false;}
		$out = array();
		foreach ($this->Columns as $Cols)
		{
			if (strlen($Cols[0]) > 0)
			{
				$out[] = $Cols[0];
			}
		}
		return $out;
	}
	
	public function all()
	{
		if (!$this->Status){return false;}
		return $this->find(null, $Conditions);
	}
	
	public function count($Conditions = null)
	{
		if (!$this->Status){return false;}
		
		if($Conditions == null){$Conditions = array();}
		$Conditions["SQL_Override"] = true;
		$Conditions["select"] = " COUNT(1) AS 'total' FROM ".$this->Table." ";
		
		$results = _VIEW($this->Table, array(), $Conditions);
		if (count($results[1]) > 0){
			return (int)$results[1][0]['total'];
		}
		else
		{
			return 0;
		}
	}
	
	public function find($Params = null, $Conditions = null)
	{
		if (!$this->Status){return false;}
		
		if($Conditions == null){$Conditions = array();}
		if($Params == null){$Params = array();}
		
		$Conditions["SQL_Override"] = true;
		$Conditions["UpperLimit"] = 4294967296; //32-bit max
		$Conditions["LowerLimit"] = 0;
						
		$results = _VIEW($this->Table, $Params, $Conditions);
		if (count($results[1]) > 0 && is_array($results[1])){
			$objs = array();
			foreach ($results[1] as $row)
			{
				$this_obj = new DaveRowObject($this, $row);
				$objs[] = $this_obj;
			}
			return $objs;
		}
		elseif(is_string($results[1]))
		{
			$this->Status = $results[1];
			return false; // indicates a selection error
		}
		else
		{
			return array(); // empty array rather than false indicates no found, but query was OK
		}
	}
	
}

////////////////////////////////////////////////////////////////////////////////

class DaveRowObject
{
	protected $DATA, $DaveTableObject;
	
	public function __construct($DaveTableObject,$params = null)
	{
		global $OUTPUT;
		$this->DATA = array();
		$this->DaveTableObject = $DaveTableObject;
		foreach($this->DaveTableObject->column_names() as $cols)
		{
			$this->DATA[$cols] = null;
		}
		foreach($params as $k => $v)
		{
			$this->DATA[$k] = $v;
		}
	}
	
	public function __set($key, $value) 
	{
        $this->DATA[$key] = $value;
    }

	public function __get($key) 
	{
        if (array_key_exists($key, $this->DATA)) 
		{
	    	return $this->DATA[$key];
        }
		else
		{
			return false;
		}
    }

	public function __isset($key) 
	{
        return isset($this->DATA[$key]);
    }

    public function __unset($key) 
	{
        unset($this->DATA[$key]);
    }
	
	///////////////////////////////////////////////////
	
	public function ADD()
	{
		$resp = _ADD($this->DaveTableObject->table(),$this->DATA);
		$this->DATA = $this->VIEW(); //load in generated data and remove non-col data
		return $resp[1];
	}
	
	public function VIEW()
	{
		$resp = _VIEW($this->DaveTableObject->table(),$this->DATA);
		return $resp[1][0];
	}
	
	public function EDIT($params = null)
	{
		if ($params != null)
		{
			foreach($params as $k => $v)
			{
				if (in_array($k,array_keys($this->DATA)))
				{
					$this->DATA[$k] = $v;
				}
			}
			$resp = _EDIT($this->DaveTableObject->table(),$this->DATA);
			$this->clean_data();
			return $resp[1][0];
		}
		else{return true;}
	}
	
	public function DELETE()
	{
		$resp = _DELETE($this->DaveTableObject->table(),$this->DATA);
		return $resp[1];
	}
	
	public function DATA($request = null)
	{
		if ($request == null) {$request = array();}
		if (is_string($request)) {
			$single_key = $request;
			$request = array($single_key);
		} 
		if (!is_array($request)){ return false; }
		
		if (count($request) == 0){ return $this->DATA; }
		else
		{
			$out = array();
			foreach($request as $var)
			{
				if($this->DATA[$var] != null)
				{
					$out[$var] = $this->DATA[$var];
				}
				if (count($out) == 1){
					return $out[$single_key];
				} 
				else{ return $out; }
			}
		}
	}
	
	private function clean_data()
	{
		foreach ($this->DATA as $key => $val)
		{
			if (!in_array($key, $this->DaveTableObject->column_names()))
			{
				unset($this->DATA[$key]);
			}
		}
	}
}

?>