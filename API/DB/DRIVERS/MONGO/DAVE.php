<?php

/***********************************************
DAVE PHP API
https://github.com/evantahler/PHP-DAVE-API
Evan Tahler | 2011

I define the functions and classes to handle the general-case database actions of Delete, Add, View, and Edit (DAVE). 
The 4 major functions will return an array.  The first value is true or false indicating if the SQL action worked.  The second object is either an array of results, or an error message.

See additional notes below.
***********************************************/

/*
Table should be defned in $TABLES
$VARS will be the params of the row to be added.  If unspecified, $PARAMS is used by default)
*/
function _ADD($Table, $VARS = null)
{
	Global $TABLES, $DBOBJ, $Connection, $PARAMS; 
	
	if ($VARS == null){$VARS = $PARAMS;}

	$UniqueVars = _getUniqueTableVars($Table);
	$RequiredVars = _getRequiredTableVars($Table);
	$attrs = array();
	
	$Status = $DBOBJ->GetStatus();
	if ($Status !== true)
	{
		return array(false,$Status);
	}
	
	$MongoDB = $DBOBJ->GetMongoDB();
	$Collection = $MongoDB->$Table;
	
	foreach($RequiredVars as $req)
	{
		if(strlen($VARS[$req]) == 0)
		{
			return array(false,$req." is a required value and you must provide a value");
		}
	}
	
	foreach($VARS as $var => $val)
	{ 
		if (in_array($var, $UniqueVars) && strlen($val) > 0)  // unique
		{	
			$FIND = array( $var  => $val);
			$count = $Collection->count($FIND);
			if ($count > 0)
			{
				return array(false,"There is already an entry of '".$val."' for ".$var);
			}
			else // var OK!
			{
				$attrs[$var] = $val;
			}

		}
		elseif (strlen($val) > 0) // non-unique
		{
			$attrs[$var] = $val;
		}
	}
	
	$Collection->insert($attrs);
	$new_obj = $Collection->findOne($attrs);
	$new_id = (string)$new_obj['_id'];
	
	return array(true,array( $TABLES['users']['META']['KEY'] => $new_id)); 
}

/***********************************************/

/*
Table should be defned in $TABLES
$VARS will be the params of the row to be added.  VARS should include a key/value pair which includes the primary key for the DB.  If unspecified, $PARAMS is used by default)
*/
function _EDIT($Table, $VARS = null)
{
	Global $TABLES, $DBOBJ, $Connection, $PARAMS;
	
	if ($VARS == null){$VARS = $PARAMS;}
	
	$UniqueVars = _getUniqueTableVars($Table);
	$RequiredVars = _getRequiredTableVars($Table);
	$attrs = array();
	
	$Status = $DBOBJ->GetStatus();
	if ($Status !== true)
	{
		return array(false,$Status);
	}
	$MongoDB = $DBOBJ->GetMongoDB();
	$Collection = $MongoDB->$Table;
	
	$resp = _VIEW($Table, $VARS);
	if($resp[0] == false){ return array(false,$resp[1]) ;}
	if (count($resp[1]) > 1){return array(false,"You need to supply the META KEY for this table, ".$TABLES[$Table]['META']['KEY']) ;}
	if (count($resp[1]) == 0)
	{
		$msg = "You have supplied none of the required parameters to make this edit.  At least one of the following is required: ";
		foreach($UniqueVars as $var)
		{
			$msg .= $var." ";
		}
		return array(false,$msg);
	}
	if ($VARS[$TABLES[$Table]['META']['KEY']] == "")
	{
		$VARS[$TABLES[$Table]['META']['KEY']] = $resp[1][0][$TABLES[$Table]['META']['KEY']];
	}
	$current_values = $resp[1][0];

	$new_data = false;
	foreach($VARS as $var => $val)
	{
		if ($var != $TABLES[$Table]['META']['KEY'])
		{
			if (in_array($var, $UniqueVars) && strlen($val) > 0 && $val != $current_values[$var])  // unique
			{
				$count = $Collection->count(array($var => $val));
				if ($count > 0)
				{
					return array(false,"There is already an entry of '".$val."' for ".$var);
				}
				else // var OK!
				{
					$attrs[$var] = $val;
				}
			}
			elseif (strlen($val) > 0) // non-unique
			{
				$attrs[$var] = $val;
			}
			if($attrs[$var] != $current_values[$var] && $var != $TABLES[$Table]['META']['KEY'])
			{
				$new_data = true;
			}
		}
	}
	
	// fill in old values
	foreach($current_values as $var=>$val)
	{
		if(empty($attrs[$var]))
		{
			if(is_object($val) == false)
			$attrs[$var] = $val;
		}
	}
	if (count($attrs) > 0 && $new_data)
	{			
		$MongoId = new MongoID($VARS[$TABLES[$Table]['META']['KEY']]);
		$resp = $Collection->update(array("_id" => $MongoId), $attrs);
		if ($resp === true)
		{
			return _VIEW($Table, $VARS); // do a view again to return fresh data
		}
		else{ return array(false,$Status); }
	}
	else
	{
		return array(false,"There is nothing to change");
	}
}

/***********************************************/

/*
Table should be defned in $TABLES
$VARS will be the params of the row to be added.  VARS should include a key/value pair which includes either the primary key for the DB or one of the unique cols for the table.  If unspecified, $PARAMS is used by default)
Settins is an array that can contain:
- $Settings["where_additions"]: Specific where statement. Array() for mongo.  Example: Birtday = "1984-08-27"
- $Settings["SQL_Override"]: normally, DAVE wants to only view a single row, and will error unless that row can be defined properly with unique values.  set this true to bypass these checks, and view many rows at once
- $Settings["UpperLimit"]: used for LIMIT statement.  Defaults to 100
- $Settings["LowerLimit"]: used for LIMIT statement.  Defaults to 0
*/
function _VIEW($Table, $VARS = null, $Settings = null )
{
	Global $TABLES, $DBOBJ, $Connection, $PARAMS; 
	if ($VARS == null){$VARS = $PARAMS;}
	
	// Additonal _VIEW Options and Configurations
	if ($Settings == null){ $Settings = array(); }
	$where_additions = $Settings["where_additions"];
	$UpperLimit = $Settings["UpperLimit"];
	$LowerLimit = $Settings["LowerLimit"];
	$SQL_Override = $Settings["SQL_Override"];

	if ($UpperLimit == ""){$UpperLimit = $PARAMS["UpperLimit"];}
	if ($LowerLimit == ""){$LowerLimit = $PARAMS["LowerLimit"];}
	
	$UniqueVars = _getUniqueTableVars($Table);
	$attrs = array();
	$NeedAnd = false;
	if (strlen($VARS[$TABLES[$Table]['META']['KEY']]) > 0) // if the primary key is given, use JUST this
	{
		$attrs[$TABLES[$Table]['META']['KEY']] = new MongoID($VARS[$TABLES[$Table]['META']['KEY']]);
		$NeedAnd = true;
	}
	else
	{
		foreach($VARS as $var => $val)
		{ 
			if (strlen($val) > 0 && in_array($var,$UniqueVars))
			{
				$attrs[$var] = $val;
				$NeedAnd = true;
			}
		}
		if ($NeedAnd == false)
		{
			foreach($VARS as $var => $val)
			{ 
				if (strlen($val) > 0)
				{
					$attrs[$var] = $val;
				}
			}
		}
	}
	if (count($where_additions) > 0)
	{
		foreach($where_additions as $var=>$val)
		{
			$attrs[$var] = $val;
		}
        $NeedAnd = true;
	}
	if($NeedAnd == false && $SQL_Override != true)
	{
		$msg = "You have supplied none of the required parameters for this Action.  At least one of the following is required: ";
		foreach($UniqueVars as $var)
		{
			$msg .= $var." ";
		}
		return array(false,$msg);
	}
	if ($UpperLimit < $LowerLimit) { return array(false,"UpperLimit must be greater than LowerLimit"); }
        $limit = null;
        $skip = null;
        if ($UpperLimit != "" && $LowerLimit != "")
        {
            $skip = (int)$LowerLimit;  
            $limit = (int)$UpperLimit - (int)$LowerLimit;
        }
	//
	$Status = $DBOBJ->GetStatus();
	if ($Status === true)
	{
		$MongoDB = $DBOBJ->GetMongoDB();
		$Collection = $MongoDB->$Table;
		
		$cursor = $Collection->find($attrs)->skip($skip)->limit($limit);
		$results = array();
		foreach($cursor as $obj)
		{
			$results[] = $obj;
		}
		return array(true, $results);
	}
	else { return array(false,$Status); } 
}

/***********************************************/

/*
Table should be defned in $TABLES
$VARS will be the params of the row to be added.  If unspecified, $PARAMS is used by default)
*/
function _DELETE($Table, $VARS = null)
{
	Global $TABLES, $DBOBJ, $Connection, $PARAMS; 
	
	if ($VARS == null){$VARS = $PARAMS;}
	
	$MongoDB = $DBOBJ->GetMongoDB();
	$Collection = $MongoDB->$Table;
	
	$UniqueVars = _getUniqueTableVars($Table);
	$attrs = array();
	$NeedAnd = false;
	foreach($VARS as $var => $val)
	{ 
		if($var == $TABLES[$Table]['META']['KEY'])
		{
			$attrs[$TABLES[$Table]['META']['KEY']] = new MongoID($VARS[$TABLES[$Table]['META']['KEY']]);
			$NeedAnd = true;
		}
		elseif (in_array($var, $UniqueVars) && strlen($val) > 0)
		{
			$attrs[$var] = $val;
			$NeedAnd = true;
		}
	}
	if($NeedAnd == false)
	{
		$msg = "You have supplied none of the required parameters to make this delete.  At least one of the following is required: ";
		foreach($UniqueVars as $var)
		{
			$msg .= $var." ";
		}
		return array(false,$msg);
	}

	$Status = $DBOBJ->GetStatus();
	if ($Status === true)
	{
		$count = $Collection->count($attrs);
		if ($count > 1)
		{
			return array(false,"More than one item matches these parameters.  Only one row can be deleted at a time.");
		}
		elseif($count < 1) { return array(false,"The item you are requesting to delete is not found"); }
		
		$resp = $Collection->remove($attrs);
		if ($resp === true){ return array(true, true); }
		else{ return array(false,$resp); }
	}
	else {return array(false,$Status); } 

}

?>