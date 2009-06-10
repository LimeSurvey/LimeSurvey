<?php
/*
* LimeSurvey
* Copyright (C) 2007 The LimeSurvey Project Team / Carsten Schmitz
* All rights reserved.
* License: GNU/GPL License v2 or later, see LICENSE.php
* LimeSurvey is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
* 
* $Id$
*/


//import responses from an old_ survey table into an active survey
include_once("login_check.php");        

if (!isset($oldtable)) {$oldtable=returnglobal('oldtable');}
if (!isset($surveyid)) {$surveyid=returnglobal('sid');}


if (!$subaction == "import")
{
	// show UI for choosing old table

	$query = db_select_tables_like("{$dbprefix}old\_survey\_%");
	$result = db_execute_num($query) or safe_die("Error:<br />$query<br />".$connect->ErrorMsg());
	$optionElements = '';
	$queryCheckColumnsActive = "SELECT * FROM {$dbprefix}survey_{$surveyid} ";
	$resultActive = db_execute_num($queryCheckColumnsActive) or safe_die("Error:<br />$query<br />".$connect->ErrorMsg());
	$countActive = $resultActive->FieldCount(); 
	
	while ($row = $result->FetchRow())
	{
		$queryCheckColumnsOld = "SELECT * FROM {$row[0]} ";
		
		$resultOld = db_execute_num($queryCheckColumnsOld) or safe_die("Error:<br />$query<br />".$connect->ErrorMsg());
		
		if($countActive== $resultOld->FieldCount())
		{
			$optionElements .= "\t\t\t<option value='{$row[0]}'>{$row[0]}</option>\n";
		}
	}

    //Get the menubar
    $importoldresponsesoutput = browsemenubar($clang->gT("Quick Statistics"));
	$importoldresponsesoutput .= "<br />
		<div class='header'>
			".$clang->gT("Import responses from an deactivated survey table")."
		</div>
        <form id='personalsettings' method='post'>        
		<ul>
		 <li><label for='spansurveyid'>".$clang->gT("Target survey ID:")."</label>
		 <span id='spansurveyid'> $surveyid<input type='hidden' value='$surveyid' name='sid'></span>
		</li>
        <li>
		 <label for='oldtable'>
		  ".$clang->gT("Source table:")."
		 </label>
		  <select name='oldtable' >
          {$optionElements}
		  </select>
		</li>
        </ul>
		  <input type='submit' value='".$clang->gT("Import Responses")."' onclick='return confirm(\"".$clang->gT("Are you sure?","js").")'>&nbsp;
 	 	  <input type='hidden' name='subaction' value='import'><br /><br />
			<div class='warningtitle'>".$clang->gT("Warning: You can import all old responses with the same amount of columns as in your active survey. YOU have to make sure, that this responses corresponds to the questions in your active survey.")."</div>
		</form>
        </div>
		<br />";
}
elseif (isset($surveyid) && $surveyid && isset($oldtable))
{
	/*
	 * TODO:
	 * - mysql fit machen
	 * -- quotes für mysql beachten --> ` 
	 * - warnmeldung mehrsprachig
	 * - testen
	 */
//	if($databasetype=="postgres")
//	{
		$activetable = "{$dbprefix}survey_$surveyid";
		
		//Fields we don't want to import
		$dontimportfields = array(
		'id' //,'otherfield'
		);
		
		// fields we can import
		$importablefields = array();
		if($databasetype=="postgres")
		{
			$query = "SELECT column_name as field FROM information_schema.columns WHERE table_name = '{$activetable}' ";
		}
		else
		{
			$query = "SHOW COLUMNS FROM {$activetable}";
		}
		
		$result = db_execute_assoc($query) or safe_die("Error:<br />$query<br />".$connect->ErrorMsg());

		while ($row = $result->FetchRow())
		{
			if($databasetype=="postgres")
			{
				if (!in_array($row['field'],$dontimportfields))
				{
					$importablefields[] = $row['field'];
				}
			}
			else
			{
			if (!in_array($row['Field'],$dontimportfields))
				{
					$importablefields[] = $row['Field'];
				}
			}
		}
		foreach ($importablefields as $field => $value)
		{
            $fields2insert[]=($databasetype=="postgres") ? "\"".$value."\"" : "`".$value."`";
//		    if($databasetype=="postgres") { $fields2insert[] = "\"".$value."\""; }
		}
		
		//fields we can supply //fields in the old database
		$availablefields = array();

		if($databasetype=="postgres")
		{
			$query = "SELECT column_name as field FROM information_schema.columns WHERE table_name = '{$oldtable}' ";
		}
		else
		{
			$query = "SHOW COLUMNS FROM {$oldtable}";
		}
		$result = db_execute_assoc($query) or safe_die("Error:<br />$query<br />".$connect->ErrorMsg());
		
		while ($row = $result->FetchRow())
		{
			if($databasetype=="postgres")
			{
				if (!in_array($row['field'],$dontimportfields))
				{
					$availablefields[] = $row['field'];
				}
			}
			else
			{
				if (!in_array($row['Field'],$dontimportfields))
				{
					$availablefields[] = $row['Field'];
				}
			}

		}
		
		foreach ($availablefields as $field => $value)
		{
			if($databasetype=="postgres")
			{
				$fields2import[] = "\"".$value."\"";
			}
			else
			{
				$fields2import[] = '`'.$value.'`'; 
			}
		}
		
		$queryOldValues = "SELECT ".implode(", ",$fields2import)." "
						. "FROM {$oldtable} ";
		$resultOldValues = db_execute_assoc($queryOldValues) or safe_die("Error:<br />$queryOldValues<br />".$connect->ErrorMsg());
		
		while ($row = $resultOldValues->FetchRow())
		{
			$values2import = array();
			foreach($row as $fieldName => $fieldValue)
			{
				if( $fieldValue=="")
				{
					$values2import[] = "NULL";
				}
				else
				{
					if(!is_numeric($fieldValue))
						$values2import[] = "'".db_quote($fieldValue)."'";
					else
						$values2import[] = "".$fieldValue."";
				}
			}	
	
			$insertOldValues = "INSERT INTO {$activetable} ( ".implode(", ",$fields2insert).") "
							 . "VALUES( ".implode(", ",$values2import)."); ";		 
			$result = $connect->Execute($insertOldValues) or safe_die("Error:<br />$insertOldValues<br />".$connect->ErrorMsg());
		}

//	}
//	else
//	{
//		// options (UI not implemented)
//	
//		$dontimportfields = array(
//		'id' //,'otherfield'
//		);
//		$presetfields = array( // quote all strings so we can allow NULL
//		//'4X13X951'=>"'Y'"
//		//'id' => "NULL"
//		);
//		$importidrange = false; //array('first'=>3,'last'=>10);
//	
//		$activetable = "{$dbprefix}survey_$surveyid";
//	
//		// fields we can import
//		$importablefields = array();
//		$query = "SHOW COLUMNS FROM {$activetable}";
//		$result = db_execute_assoc($query) or safe_die("Error:<br />$query<br />".$connect->ErrorMsg());
//		while ($row = $result->FetchRow())
//		{
//			if (!in_array($row['Field'],$dontimportfields))
//			{
//				$importablefields[] = $row['Field'];
//			}
//		}
//	
//		// fields we can supply
//		$availablefields = array();
//		$query = "SHOW COLUMNS FROM {$oldtable}";
//		$result = db_execute_assoc($query) or safe_die("Error:<br />$query<br />".$connect->ErrorMsg());
//		while ($row = $result->FetchRow())
//		{
//			$availablefields[] = $row['Field'];
//		}
//		foreach ($presetfields as $field => $value)
//		{
//			if (!in_array($field,$availablefields))
//			{
//				$availablefields[] = $field;
//			}
//		}
//	
//		$fieldstoimport = array_intersect($importablefields,$availablefields);
//	
//		// data sources for each field (field of oldtable or preset value)
//		$sourcefields = array();
//		foreach ($fieldstoimport as $field)
//		{
//			$sourcefields[] = array_key_exists($field,$presetfields)?
//			$presetfields[$field]
//			: ($oldtable.'.`'.$field.'`');
//			$fieldstoimport2[] = '`'.$field.'`'; 
//		}
//	
//		$query = "INSERT INTO {$activetable} (\n\t".join("\t, ",$fieldstoimport2)."\n) "
//		."SELECT\n\t".join("\t,",$sourcefields)."\n"
//		."FROM {$oldtable}";
//		if (is_array($importidrange))
//		{
//			$query .= " WHERE {$oldtable}.id >= {$importidrange['first']} "
//			." AND {$oldtable}.id <= {$importidrange['last']}";
//		}
//	
//		$result = $connect->Execute($query) or safe_die("Error:<br />$query<br />".$connect->ErrorMsg());
//	}
	header("Location: $scriptname?action=browse&sid=$surveyid");
}

?>
