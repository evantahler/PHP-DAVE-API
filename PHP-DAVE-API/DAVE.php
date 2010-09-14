<?php

/***********************************************
Evan Tahler
evantahler@gmail.com
2010

I define the functions and classes to handle the general-case database actions of Delete, Add, View, and Edit (DAVE). 
The 4 major functions will return an array.  The first value is true or false indicating if the SQL action worked.  The second object is either an array of results, or an error message.

***********************************************/

function _ADD($Table)
{
	Global $POST_VARIABLES, $TABLES; 
	if(_tableCheck($Table))
	{
		$UniqueVars = _getUniqueTableVars($Table);
		$RequiredVars = _getRequiredTableVars($Table);
		$AllTableVars = _getAllTableCols($Table);
		
		$SQLKeys = array();
		$SQLValues= array();
		
		$DBObj = new DBConnection();
		$Status = $DBObj->GetStatus();
		if ($Status !== true)
		{
			$DBObj->close(); 
			return array(false,$Status);
		}
		
		foreach($POST_VARIABLES as $var)
		{ 
			Global $$var; 
			if (in_array($var, $RequiredVars)) // required
			{
				if (_isSpecialString($$var))
				{
					return array(false,$var." is a required value and you must provide a value");
				}
				elseif (strlen($$var) > 0)
				{
					if (in_array($var, $UniqueVars))  // unique
					{
						$SQL = 'SELECT COUNT(*) FROM `'.$Table.'` WHERE (`'.$var.'` = "'.$$var.'") ;';
						$DBObj->Query($SQL);
						$Status = $DBObj->GetStatus();
						if ($Status === true){
							$results = $DBObj->GetResults();
							if ($results[0]['COUNT(*)'] > 0)
							{
								return array(false,"There is already an entry of '".$$var."' for ".$var);
							}
							else // var OK!
							{
								$SQLKeys[] = $var;
								$SQLValues[] = $$var;
							}
						}
					}
					else // non-unique
					{
						$SQLKeys[] = $var;
						$SQLValues[] = $$var;
					}
				}
				else
				{
					return array(false,$var." is a required value");
				}
			}
			elseif (in_array($var,$AllTableVars)) // optional
			{
				if (in_array($var, $UniqueVars) && strlen($$var) > 0)  // unique
				{
					$SQL = 'SELECT COUNT(*) FROM `'.$Table.'` WHERE (`'.$var.'` = "'.$$var.'") ;';
					$DBObj->Query($SQL);
					$Status = $DBObj->GetStatus();
					if ($Status === true){
						$results = $DBObj->GetResults();
						if ($results[0]['COUNT(*)'] > 0)
						{
							return array(false,"There is already an entry of '".$$var."' for ".$var);
						}
						else // var OK!
						{
							$SQLKeys[] = $var;
							$SQLValues[] = $$var;
						}
					}
				}
				elseif (strlen($$var) > 0) // non-unique
				{
					$SQLKeys[] = $var;
					$SQLValues[] = $$var;
				}
			}
		}
		//
		$SQL = "INSERT INTO `".$Table."` ( ";
		$i = 0;
		$needComma = false;
		$Connection = $DBObj->GetConnection();
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
			$DBObj->close();
			return array(true,array( $TABLES[$Table]['META']['KEY'] => $NewKey)); 
		}
		else{$DBObj->close(); return array(false,$Status); }
		//
		$DBObj->close();
	}
	else
	{
		return array(false,"This table cannot be found");
	}
}

/***********************************************/

function _EDIT($Table)
{
	Global $POST_VARIABLES, $TABLES, $$TABLES[$Table]['META']['KEY'];
	
	if(_tableCheck($Table))
	{
		$UniqueVars = _getUniqueTableVars($Table);
		$RequiredVars = _getRequiredTableVars($Table);
		$AllTableVars = _getAllTableCols($Table);
		
		$SQLKeys = array();
		$SQLValues= array();
		
		$DBObj = new DBConnection();
		$Status = $DBObj->GetStatus();
		if ($Status !== true)
		{
			$DBObj->close(); 
			return array(false,$Status);
		}
		// get the META KEY if it wasn't provided explicitly
		if ($$TABLES[$Table]['META']['KEY'] == "")
		{
			$SQL = 'SELECT '.$TABLES[$Table]['META']['KEY'].' FROM `'.$Table.'` WHERE ( ';
			$NeedAnd = false;
			foreach($POST_VARIABLES as $var)
			{
				Global $$var; 
				if (in_array($var,$UniqueVars) && $$var != "")
				{
					if ($NeedAnd) { $SQL .= " AND "; }
					$SQL .= ' `'.$var.'` = "'.$$var.'" ';
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
					$$TABLES[$Table]['META']['KEY'] = $results[0][$TABLES[$Table]['META']['KEY']];
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
		foreach($POST_VARIABLES as $var)
		{
			if ($var != $TABLES[$Table]['META']['KEY'])
			{
				Global $$var; 
				if (in_array($var, $RequiredVars) && _isSpecialString($$var)) // required
				{
						return array(false,$var." is a required value and you must provide a value");
				}
				elseif (in_array($var,$AllTableVars)) // optional
				{
					if (in_array($var, $UniqueVars) && strlen($$var) > 0)  // unique
					{
						$SQL = 'SELECT COUNT(*) FROM `'.$Table.'` WHERE (`'.$var.'` = "'.$$var.'" AND `'.$TABLES[$Table]['META']['KEY'].'` != "'.$$TABLES[$Table]['META']['KEY'].'") ;'; 
						$DBObj->Query($SQL);
						$Status = $DBObj->GetStatus();
						if ($Status === true){
							$results = $DBObj->GetResults();
							if ($results[0]['COUNT(*)'] > 0)
							{
								return array(false,"There is already an entry of '".$$var."' for ".$var);
							}
							else // var OK!
							{
								$SQLKeys[] = $var;
								$SQLValues[] = $$var;
							}
						}
					}
					elseif (strlen($$var) > 0) // non-unique
					{
						$SQLKeys[] = $var;
						$SQLValues[] = $$var;
					}
				}
			}
		}
		//
		if(strlen($$TABLES[$Table]['META']['KEY']) > 0)
		{
			if (count($SQLKeys) > 0)
			{			
				$Connection = $DBObj->GetConnection();
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
				
				$SQL .= ' WHERE ( `'.$TABLES[$Table]['META']['KEY'].'` = "'.$$TABLES[$Table]['META']['KEY'].'" ); ';
				$DBObj->Query($SQL);
				$Status = $DBObj->GetStatus();
				if ($Status === true)
				{
					$NewKey = $DBObj->GetLastInsert();
					$DBObj->close();
					return _VIEW($Table); // do a view again to return fresh data
				}
				else{$DBObj->close(); return array(false,$Status); }
				//
				$DBObj->close();
			}
			else
			{
				return array(false,"There is nothing to change");
			}
		}
		else
		{
			return array(false,"You need to provide a parameter for the KEY of this table, ".$$TABLES[$Table]['META']['KEY']);
		}
	}
	else
	{
		return array(false,"This table cannot be found");
	}
}

/***********************************************/

function _VIEW($Table, $select = null, $join = null, $where_additions = null, $sort = null, $SQL_Override = false)
{
	Global $POST_VARIABLES, $TABLES, $LowerLimit, $UpperLimit, $$TABLES[$Table]['META']['KEY']; 
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
		if (strlen($$TABLES[$Table]['META']['KEY']) > 0 && $SQL_Override != true) // if the primary key is given, use JUST this
		{
			$SQL .= ' `'.$TABLES[$Table]['META']['KEY'].'` = "'.$$TABLES[$Table]['META']['KEY'].'" ';
			$NeedAnd = true;
		}
		else
		{
			foreach($POST_VARIABLES as $var)
			{ 
				Global $$var; 
				if (in_array($var, $UniqueVars) && strlen($$var) > 0)
				{
					if ($NeedAnd) { $SQL .= " AND "; } 
					$SQL .= ' `'.$var.'` = "'.$$var.'" ';
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
		$DBObj = new DBConnection();
		$Status = $DBObj->GetStatus();
		if ($Status === true)
		{
			$DBObj->Query($SQL);
			$Status = $DBObj->GetStatus();
			if ($Status === true){$DBObj->close(); return array(true, $DBObj->GetResults()); }
			else{$DBObj->close(); return array(false,$Status); }
		}
		else {$DBObj->close(); return array(false,$Status); } 
		$DBObj->close();
	}
	else
	{
		return array(false,"This table cannot be found");
	}
}

/***********************************************/

function _DELETE($Table)
{
	Global $POST_VARIABLES, $TABLES; 
	if(_tableCheck($Table))
	{
		$UniqueVars = _getUniqueTableVars($Table);
		$SQL = "DELETE FROM `".$Table."` WHERE ( ";
		$SQL2 = "SELECT COUNT(*) FROM `".$Table."` WHERE ( ";
		$NeedAnd = false;
		foreach($POST_VARIABLES as $var)
		{ 
			Global $$var; 
			if (in_array($var, $UniqueVars) && strlen($$var) > 0)
			{
				if ($NeedAnd) { $SQL .= " AND "; $SQL2 .= " AND "; } 
				$SQL .= ' `'.$var.'` = "'.$$var.'" ';
				$SQL2 .= ' `'.$var.'` = "'.$$var.'" ';
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
		$DBObj = new DBConnection();
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
					$DBObj->close();
					return array(false,"The item you are requesting to delete is not found");
				}
			}
			else{$DBObj->close(); return array(false,"The item you are requesting to delete is not found"); }
			
			$DBObj->Query($SQL);
			$Status = $DBObj->GetStatus();
			if ($Status === true){$DBObj->close(); return array(true, $DBObj->GetResults()); }
			else{$DBObj->close(); return array(false,$Status); }
		}
		else {$DBObj->close(); return array(false,$Status); } 
		$DBObj->close();
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
		if ($TABLES[$Table][$i][2] == true)
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