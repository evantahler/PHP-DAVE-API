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
	
	if(_tableCheck($Table))
	{
		$UniqueVars = _getUniqueTableVars($Table);
		$RequiredVars = _getRequiredTableVars($Table);
		$AllTableVars = _getAllTableCols($Table);
		
		$SQLKeys = array();
		$SQLValues= array();
		
		$Status = $DBOBJ->GetStatus();
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
					$SQL = 'SELECT COUNT(1) FROM `'.$Table.'` WHERE (`'.$var.'` = "'.$val.'") ;';
					$DBOBJ->Query($SQL);
					$Status = $DBOBJ->GetStatus();
					if ($Status === true){
						$results = $DBOBJ->GetResults();
						if ($results[0]['COUNT(1)'] > 0)
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

		$DBOBJ->Query($SQL);
		$Status = $DBOBJ->GetStatus();
		if ($Status === true)
		{
			$NewKey = $DBOBJ->GetLastInsert();
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

/*
Table should be defned in $TABLES
$VARS will be the params of the row to be added.  VARS should include a key/value pair which includes the primary key for the DB.  If unspecified, $PARAMS is used by default)
*/
function _EDIT($Table, $VARS = null)
{
	Global $TABLES, $DBOBJ, $Connection, $PARAMS;
	
	if ($VARS == null){$VARS = $PARAMS;}
	
	if(_tableCheck($Table))
	{
		$UniqueVars = _getUniqueTableVars($Table);
		$RequiredVars = _getRequiredTableVars($Table);
		$AllTableVars = _getAllTableCols($Table);
		
		$SQLKeys = array();
		$SQLValues= array();
		
		$Status = $DBOBJ->GetStatus();
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
			$DBOBJ->Query($SQL);
			$Status = $DBOBJ->GetStatus();
			if ($Status === true)
			{
				$results = $DBOBJ->GetResults();
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
		if(is_array($VARS))
		{
			foreach($VARS as $var => $val)
			{
				if ($var != $TABLES[$Table]['META']['KEY'])
				{
					// if (in_array($var, $RequiredVars) && _isSpecialString($val)) // required
					// {
					// 		return array(false,$var." is a required value and you must provide a value");
					// }
					if (in_array($var,$AllTableVars))
					{
						if (in_array($var, $UniqueVars) && strlen($val) > 0)  // unique
						{
							$SQL = 'SELECT COUNT(1) FROM `'.$Table.'` WHERE (`'.$var.'` = "'.$val.'" AND `'.$TABLES[$Table]['META']['KEY'].'` != "'.$VARS[$TABLES[$Table]['META']['KEY']].'") ;'; 
							$DBOBJ->Query($SQL);
							$Status = $DBOBJ->GetStatus();
							if ($Status === true){
								$results = $DBOBJ->GetResults();
								if ($results[0]['COUNT(1)'] > 0)
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
				$DBOBJ->Query($SQL);
				$Status = $DBOBJ->GetStatus();
				if ($Status === true)
				{
					$NewKey = $DBOBJ->GetLastInsert();
					return _VIEW($Table, $VARS); // do a view again to return fresh data
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

/*
Table should be defned in $TABLES
$VARS will be the params of the row to be added.  VARS should include a key/value pair which includes either the primary key for the DB or one of the unique cols for the table.  If unspecified, $PARAMS is used by default)
Settins is an array that can contain:
- $Settings["select"]: a replacement select statement (rather than "*").  Example: "FirstName as Name, Address as Addy".  Only Name and Addy will be returned.
- $Settings["join"]: Join statement (first "JOIN" is added automatically).
- $Settings["where_additions"]: Specific where statement.  Example: Birtday = "1984-08-27"
- $Settings["sort"]: sort statment. Example: "Order by Date DESC"
- $Settings["UpperLimit"]: used for LIMIT statement.  Defaults to 100
- $Settings["LowerLimit"]: used for LIMIT statement.  Defaults to 0
- $Settings["SQL_Override"]: normally, DAVE wants to only view a single row, and will error unless that row can be defined properly with unique values.  set this true to bypass these checks, and view many rows at once

*/
function _VIEW($Table, $VARS = null, $Settings = null )
{
	Global $TABLES, $DBOBJ, $Connection, $PARAMS; 
	if ($VARS == null){$VARS = $PARAMS;}
	
	// Additonal _VIEW Options and Configurations
	if ($Settings == null){ $Settings = array(); }
	$select = $Settings["select"];
	$join = $Settings["join"];
	$where_additions = $Settings["where_additions"];
	$sort = $Settings["sort"];
	$UpperLimit = $Settings["UpperLimit"];
	$LowerLimit = $Settings["LowerLimit"];
	$SQL_Override = $Settings["SQL_Override"];
	
	if ($UpperLimit == ""){$UpperLimit = $PARAMS["UpperLimit"];}
	if ($LowerLimit == ""){$LowerLimit = $PARAMS["LowerLimit"];}
		
	if(_tableCheck($Table))
	{
		$UniqueVars = _getUniqueTableVars($Table);
		$AllTableVars = _getAllTableCols($Table);
		
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
				if (in_array($var, $AllTableVars) && strlen($val) > 0)
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
		elseif ($NeedAnd == false && $SQL_Override == true)
		{
			$SQL .= " true ";
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
		$Status = $DBOBJ->GetStatus();
		if ($Status === true)
		{
			$DBOBJ->Query($SQL);
			$Status = $DBOBJ->GetStatus();
			if ($Status === true){ return array(true, $DBOBJ->GetResults()); }
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

/*
Table should be defned in $TABLES
$VARS will be the params of the row to be added.  If unspecified, $PARAMS is used by default)
*/
function _DELETE($Table, $VARS = null)
{
	Global $TABLES, $DBOBJ, $Connection, $PARAMS; 
	
	if ($VARS == null){$VARS = $PARAMS;}
	
	if(_tableCheck($Table))
	{
		$UniqueVars = _getUniqueTableVars($Table);
                $AllTableVars = _getAllTableCols($Table);
		$SQL = "DELETE FROM `".$Table."` WHERE ( ";
		$SQL2 = "SELECT COUNT(1) FROM `".$Table."` WHERE ( ";
		$NeedAnd = false;
		if(is_array($VARS))
		{
			foreach($VARS as $var => $val)
			{ 
				if (in_array($var, $AllTableVars) && strlen($val) > 0)
				{
					if ($NeedAnd) { $SQL .= " AND "; $SQL2 .= " AND "; } 
					$SQL .= ' `'.$var.'` = "'.$val.'" ';
					$SQL2 .= ' `'.$var.'` = "'.$val.'" ';
					$NeedAnd = true;
				}
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
		$Status = $DBOBJ->GetStatus();
		if ($Status === true)
		{
			$DBOBJ->Query($SQL2);
			$Status = $DBOBJ->GetStatus();
			if ($Status === true)
			{
				$results = $DBOBJ->GetResults();
				if ($results[0]['COUNT(1)'] > 1)
				{
					return array(false,"More than one item matches these parameters.  Only one row can be deleted at a time.");
				}
                                elseif($results[0]['COUNT(1)'] < 1)
				{
					return array(false,"The row specified for deletion cannot be found.");
				}
			}
			else{ return array(false,"The item you are requesting to delete is not found"); }
			
			$DBOBJ->Query($SQL);
			$Status = $DBOBJ->GetStatus();
			if ($Status === true){ return array(true, true); }
			else{ return array(false,$Status); }
		}
		else {return array(false,$Status); } 
	}
	else
	{
		return array(false,"This table cannot be found");
	}
}

?>