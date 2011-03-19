<?php

/***********************************************
DAVE PHP API
https://github.com/evantahler/PHP-DAVE-API
Evan Tahler | 2011

I define the functions and classes to handle the general-case database actions of Delete, Add, View, and Edit (DAVE). 
The 4 major functions will return an array.  The first value is true or false indicating if the SQL action worked.  The second object is either an array of results, or an error message.

***********************************************/

function _ADD($Table, $VARS = null)
{
	Global $TABLES, $DBObj, $Connection, $PARAMS; 
	
	if ($VARS == null){$VARS = $PARAMS;}
	
	if(_tableCheck($Table))
	{
		$UniqueVars = _getUniqueTableVars($Table);
		$RequiredVars = _getRequiredTableVars($Table);
		$AllTableVars = _getAllTableCols($Table);
		
		$SQLKeys = array();
		$SQLValues= array();
		
		$Status = $DBObj->GetStatus();
		if ($Status !== true)
		{
			return array(false,$Status);
		}
		
		foreach($RequiredVars as $req)
		{
			if(strlen($VARS[$req]) == 0)
			{
				return array(false,$req." is a required value and you must provide a value");
			}
		}
		
		foreach($VARS as $var => $val)
		{ 
			if (in_array($var,$AllTableVars))
			{
				if (in_array($var, $UniqueVars) && strlen($val) > 0)  // unique
				{
					$SQL = 'SELECT COUNT(*) FROM `'.$Table.'` WHERE (`'.$var.'` = "'.$val.'") ;';
					$DBObj->Query($SQL);
					$Status = $DBObj->GetStatus();
					if ($Status === true){
						$results = $DBObj->GetResults();
						if ($results[0]['COUNT(*)'] > 0)
						{
							return array(false,"There is already an entry of '".$val."' for ".$var);
						}
						else // var OK!
						{
							$SQLKeys[] = $var;
							$SQLValues[] = $val;
						}
					}
				}
				elseif (strlen($val) > 0) // non-unique
				{
					$SQLKeys[] = $var;
					$SQLValues[] = $val;
				}
			}
		}
		//
		$SQL = "INSERT INTO `".$Table."` ( ";
		$i = 0;
		$needComma = false;
		while ($i < count($SQLKeys))
		{
			if ($needComma) { $SQL .= ", "; } 
			$SQL .= ' `'.$SQLKeys[$i].'` '; 
			$needComma = true;
			$i++;
		}
		$SQL .= " ) VALUES ( ";
		$i = 0;
		$needComma = false;
		while ($i < count($SQLValues))
		{
			if ($needComma) { $SQL .= ", "; } 
			$SQL .= ' "'.mysql_real_escape_string($SQLValues[$i],$Connection).'" '; 
			$needComma = true;
			$i++;
		}
		$SQL .= " ); ";

		$DBObj->Query($SQL);
		$Status = $DBObj->GetStatus();
		if ($Status === true)
		{
			$NewKey = $DBObj->GetLastInsert();
			return array(true,array( $TABLES[$Table]['META']['KEY'] => $NewKey)); 
		}
		else{return array(false,$Status); }
	}
	else
	{
		return array(false,"This table cannot be found");
	}
}

/***********************************************/

function _EDIT($Table, $VARS = null)
{
	Global $TABLES, $DBObj, $Connection, $PARAMS;
	
	if ($VARS == null){$VARS = $PARAMS;}
	
	if(_tableCheck($Table))
	{
		$UniqueVars = _getUniqueTableVars($Table);
		$RequiredVars = _getRequiredTableVars($Table);
		$AllTableVars = _getAllTableCols($Table);
		
		$SQLKeys = array();
		$SQLValues= array();
		
		$Status = $DBObj->GetStatus();
		if ($Status !== true)
		{
			return array(false,$Status);
		}
		// get the META KEY if it wasn't provided explicitly
		if ($VARS[$TABLES[$Table]['META']['KEY']] == "")
		{
			$SQL = 'SELECT '.$TABLES[$Table]['META']['KEY'].' FROM `'.$Table.'` WHERE ( ';
			$NeedAnd = false;
			foreach($VARS as $var => $val)
			{
				if (in_array($var,$UniqueVars) && $val != "")
				{
					if ($NeedAnd) { $SQL .= " AND "; }
					$SQL .= ' `'.$var.'` = "'.$val.'" ';
					$NeedAnd = true;
				}
			}
			$SQL .= ' ) ;';
			$DBObj->Query($SQL);
			$Status = $DBObj->GetStatus();
			if ($Status === true)
			{
				$results = $DBObj->GetResults();
				if (count($results) == 1)
				{
					$VARS[$TABLES[$Table]['META']['KEY']] = $results[0][$TABLES[$Table]['META']['KEY']];
				}
				else // var OK!
				{
					return array(false,"You need to supply the META KEY for this table, ".$TABLES[$Table]['META']['KEY']);
				}
			}
			else
			{
				return array(false,"You need to supply the META KEY for this table, ".$TABLES[$Table]['META']['KEY'].", or one of the unique keys.");
			}
		}
		//loop
		foreach($VARS as $var => $val)
		{
			if ($var != $TABLES[$Table]['META']['KEY'])
			{
				if (in_array($var, $RequiredVars) && _isSpecialString($val)) // required
				{
						return array(false,$var." is a required value and you must provide a value");
				}
				elseif (in_array($var,$AllTableVars)) // optional
				{
					if (in_array($var, $UniqueVars) && strlen($val) > 0)  // unique
					{
						$SQL = 'SELECT COUNT(*) FROM `'.$Table.'` WHERE (`'.$var.'` = "'.$val.'" AND `'.$TABLES[$Table]['META']['KEY'].'` != "'.$VARS[$TABLES[$Table]['META']['KEY']].'") ;'; 
						$DBObj->Query($SQL);
						$Status = $DBObj->GetStatus();
						if ($Status === true){
							$results = $DBObj->GetResults();
							if ($results[0]['COUNT(*)'] > 0)
							{
								return array(false,"There is already an entry of '".$val."' for ".$var);
							}
							else // var OK!
							{
								$SQLKeys[] = $var;
								$SQLValues[] = $val;
							}
						}
					}
					elseif (strlen($val) > 0) // non-unique
					{
						$SQLKeys[] = $var;
						$SQLValues[] = $val;
					}
				}
			}
		}
		//
		if(strlen($VARS[$TABLES[$Table]['META']['KEY']]) > 0)
		{
			if (count($SQLKeys) > 0)
			{			
				$SQL = "UPDATE `".$Table."` SET ";
				$i = 0;
				$needComma = false;
				while ($i < count($SQLKeys))
				{
					if ($needComma) { $SQL .= ", "; } 
					$SQL .= ' `'.$SQLKeys[$i].'` = "'.mysql_real_escape_string($SQLValues[$i],$Connection).'" '; 
					$needComma = true;
					$i++;
				}
				
				$SQL .= ' WHERE ( `'.$TABLES[$Table]['META']['KEY'].'` = "'.$VARS[$TABLES[$Table]['META']['KEY']].'" ); ';
				$DBObj->Query($SQL);
				$Status = $DBObj->GetStatus();
				if ($Status === true)
				{
					$NewKey = $DBObj->GetLastInsert();
					return _VIEW($Table); // do a view again to return fresh data
				}
				else{ return array(false,$Status); }
			}
			else
			{
				return array(false,"There is nothing to change");
			}
		}
		else
		{
			return array(false,"You need to provide a parameter for the KEY of this table, ".$VARS[$TABLES[$Table]['META']['KEY']]);
		}
	}
	else
	{
		return array(false,"This table cannot be found");
	}
}

/***********************************************/

//function _VIEW($Table, $select = null, $join = null, $where_additions = null, $sort = null, $SQL_Override = false)
function _VIEW($Table, $VARS = null, $Settings = null )
{
	Global $TABLES, $LowerLimit, $UpperLimit, $DBObj, $Connection, $PARAMS; 
	if ($VARS == null){$VARS = $PARAMS;}
	
	// Additonal _VIEW Options and Configurations
	if ($Settings == null){ $Settings = array(); }
	$select = $Settings["select"];
	$join = $Settings["join"];
	$where_additions = $Settings["where_additions"];
	$sort = $Settings["sort"];
	$SQL_Override = $Settings["SQL_Override"];
	
	if(_tableCheck($Table))
	{
		$UniqueVars = _getUniqueTableVars($Table);
		if ($select != null)
		{
			$SQL = "SELECT ". $select . " ";
		}
		else
		{
			$SQL = "SELECT * FROM `".$Table."` ";
		}
		if ($join != null)
		{
			$SQL .= " JOIN ".$join." ";
		}
		$SQL .= " WHERE (";
		$NeedAnd = false;
		if (strlen($VARS[$TABLES[$Table]['META']['KEY']]) > 0 && $SQL_Override != true) // if the primary key is given, use JUST this
		{
			$SQL .= ' `'.$TABLES[$Table]['META']['KEY'].'` = "'.$VARS[$TABLES[$Table]['META']['KEY']].'" ';
			$NeedAnd = true;
		}
		else
		{
			foreach($VARS as $var => $val)
			{ 
				if (in_array($var, $UniqueVars) && strlen($val) > 0)
				{
					if ($NeedAnd) { $SQL .= " AND "; } 
					$SQL .= ' `'.$var.'` = "'.$val.'" ';
					$NeedAnd = true;
				}
			}
		}
		if ($where_additions != null)
		{
			if ($NeedAnd) { $SQL .= " AND "; } 
			$SQL .= " ".$where_additions." ";
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
		$SQL .= " ) ";
		if ($sort != null)
		{
			$SQL .= $sort;
		}
		if ($UpperLimit < $LowerLimit) { $ERROR = "UpperLimit must be greater than LowerLimit"; }
		if ($LowerLimit == "") {$LowerLimit = 0; }
		if ($UpperLimit == "") {$UpperLimit = 100; }
		$SQL .= " LIMIT ".$LowerLimit.",".($UpperLimit - $LowerLimit)." ";
		//
		$Status = $DBObj->GetStatus();
		if ($Status === true)
		{
			$DBObj->Query($SQL);
			$Status = $DBObj->GetStatus();
			if ($Status === true){ return array(true, $DBObj->GetResults()); }
			else{ return array(false,$Status); }
		}
		else { return array(false,$Status); } 
	}
	else
	{
		return array(false,"This table cannot be found");
	}
}

/***********************************************/

function _DELETE($Table, $VARS = null)
{
	Global $TABLES, $DBObj, $Connection, $PARAMS; 
	
	if ($VARS == null){$VARS = $PARAMS;}
	
	if(_tableCheck($Table))
	{
		$UniqueVars = _getUniqueTableVars($Table);
		$SQL = "DELETE FROM `".$Table."` WHERE ( ";
		$SQL2 = "SELECT COUNT(*) FROM `".$Table."` WHERE ( ";
		$NeedAnd = false;
		foreach($VARS as $var => $val)
		{ 
			if (in_array($var, $UniqueVars) && strlen($val) > 0)
			{
				if ($NeedAnd) { $SQL .= " AND "; $SQL2 .= " AND "; } 
				$SQL .= ' `'.$var.'` = "'.$val.'" ';
				$SQL2 .= ' `'.$var.'` = "'.$val.'" ';
				$NeedAnd = true;
			}
		}
		if($NeedAnd == false)
		{
			$msg = "You have supplied none of the required parameters to make this query.  At least one of the following is required: ";
			foreach($UniqueVars as $var)
			{
				$msg .= $var." ";
			}
			return array(false,$msg);
		}
		$SQL .= " ) ;"; // There is no limit to allow more than one removal
		$SQL2 .= " ) ;";
		//
		$Status = $DBObj->GetStatus();
		if ($Status === true)
		{
			$DBObj->Query($SQL2);
			$Status = $DBObj->GetStatus();
			if ($Status === true)
			{
				$results = $DBObj->GetResults();
				if ($results[0]['COUNT(*)'] < 1)
				{
					return array(false,"The item you are requesting to delete is not found");
				}
			}
			else{ return array(false,"The item you are requesting to delete is not found"); }
			
			$DBObj->Query($SQL);
			$Status = $DBObj->GetStatus();
			if ($Status === true){ return array(true, $DBObj->GetResults()); }
			else{ return array(false,$Status); }
		}
		else {return array(false,$Status); } 
	}
	else
	{
		return array(false,"This table cannot be found");
	}
}

/***********************************************/
// helper functions

function _tableCheck($Table)
{
	global $TABLES;
	// does this table exist?
	$Keys = array_keys($TABLES);
	if( in_array($Table, $Keys))
	{
		return true;
	}
	else
	{
		return false;
	}
}

function _getAllTableCols($Table)
{
	global $TABLES;
	$Vars = array();
	$i = 0;
	while ($i < count($TABLES[$Table]))
	{
		$Vars[] = $TABLES[$Table][$i][0];
		//
		$i++;
	}
	return $Vars;
}

function _getRequiredTableVars($Table)
{
	global $TABLES;
	$RequiredVars = array();
	$i = 0;
	while ($i < count($TABLES[$Table]))
	{
		if ($TABLES[$Table][$i][2] == true && $TABLES[$Table]["META"]["KEY"] != $TABLES[$Table][$i][0])
		{
			$RequiredVars[] = $TABLES[$Table][$i][0];
		}
		//
		$i++;
	}
	return $RequiredVars;
}

function _getUniqueTableVars($Table)
{
	global $TABLES;
	$UniqueVars = array();
	$i = 0;
	while ($i < count($TABLES[$Table]))
	{
		if ($TABLES[$Table][$i][1] == true)
		{
			$UniqueVars[] = $TABLES[$Table][$i][0];
		}
		//
		$i++;
	}
	return $UniqueVars;
}

function _isSpecialString($string)
{
	global $SpecialStrings;
	$found = false;
	foreach ($SpecialStrings as $term)
	{
		if (stristr($string,$term[0]) !== false)
		{
			$found = true;
			break;
		}
	}
	return $found;
}

?>