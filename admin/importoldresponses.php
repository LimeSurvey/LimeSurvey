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
    $importoldresponsesoutput = browsemenubar($clang->gT("Quick statistics"));
    $importoldresponsesoutput .= "
		<div class='header ui-widget-header'>
			".$clang->gT("Import responses from a deactivated survey table")."
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
        <li>
         <label for='importtimings'>
          ".$clang->gT("Import also timings (if exist):")."
         </label>
          <select name='importtimings' >
          <option value='Y' selected='selected'>".$clang->gT("Yes")."</option>
          <option value='N'>".$clang->gT("No")."</option>
          </select>
        </li>
        </ul>
		  <p><input type='submit' value='".$clang->gT("Import Responses")."' onclick='return confirm(\"".$clang->gT("Are you sure?","js").")'>&nbsp;
 	 	  <input type='hidden' name='subaction' value='import'><br /><br />
			<div class='messagebox ui-corner-all'><div class='warningheader'>".$clang->gT("Warning").'</div>'.$clang->gT("You can import all old responses with the same amount of columns as in your active survey. YOU have to make sure, that this responses corresponds to the questions in your active survey.")."</div>
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
		 //,'otherfield'
    );


    $aFieldsOldTable=array_values($connect->MetaColumnNames($oldtable, true)); 
    $aFieldsNewTable=array_values($connect->MetaColumnNames($activetable, true)); 
    
    // Only import fields where the fieldnames are matching
    $aValidFields=array_intersect($aFieldsOldTable,$aFieldsNewTable);

    // Only import fields not being in the $dontimportfields array
    $aValidFields=array_diff($aValidFields,$dontimportfields);


    $queryOldValues = "SELECT ".implode(", ",$aValidFields)." FROM {$oldtable} ";
    $resultOldValues = db_execute_assoc($queryOldValues) or safe_die("Error:<br />$queryOldValues<br />".$connect->ErrorMsg());
    $iRecordCount=$resultOldValues->RecordCount();
    $aSRIDConversions=array();
    while ($row = $resultOldValues->FetchRow())
    {
        $iOldID=$row['id'];
        unset($row['id']);

        $sInsertSQL=$connect->GetInsertSQL($activetable,$row);
        $result = $connect->Execute($sInsertSQL) or safe_die("Error:<br />$sInsertSQL<br />".$connect->ErrorMsg());
        $aSRIDConversions[$iOldID]=$connect->Insert_ID();
    }

    $_SESSION['flashmessage'] = sprintf($clang->gT("%s old response(s) were successfully imported."),$iRecordCount);               

    $sOldTimingsTable=substr($oldtable,0,strrpos($oldtable,'_')).'_timings'.substr($oldtable,strrpos($oldtable,'_'));
    $sNewTimingsTable=db_table_name_nq("survey_{$surveyid}_timings");
    if (tableExists(sStripDBPrefix($sOldTimingsTable)) && tableExists(sStripDBPrefix($sNewTimingsTable)) && returnglobal('importtimings')=='Y')
    {
       // Import timings
        $aFieldsOldTimingTable=array_values($connect->MetaColumnNames($sOldTimingsTable, true)); 
        $aFieldsNewTimingTable=array_values($connect->MetaColumnNames($sNewTimingsTable, true)); 
        $aValidTimingFields=array_intersect($aFieldsOldTimingTable,$aFieldsNewTimingTable);
        
        $queryOldValues = "SELECT ".implode(", ",$aValidTimingFields)." FROM {$sOldTimingsTable} ";
        $resultOldValues = db_execute_assoc($queryOldValues) or safe_die("Error:<br />$queryOldValues<br />".$connect->ErrorMsg());
        $iRecordCountT=$resultOldValues->RecordCount();
        $aSRIDConversions=array();
        while ($row = $resultOldValues->FetchRow())
        {
            if (isset($aSRIDConversions[$row['id']]))
            {
                $row['id']=$aSRIDConversions[$row['id']];   
            }
            else continue;
            $sInsertSQL=$connect->GetInsertSQL($sNewTimingsTable,$row);
            $result = $connect->Execute($sInsertSQL) or safe_die("Error:<br />$sInsertSQL<br />".$connect->ErrorMsg());
        }
        $_SESSION['flashmessage'] = sprintf($clang->gT("%s old response(s) and according timings were successfully imported."),$iRecordCount,$iRecordCountT);               
    }
    $importoldresponsesoutput = browsemenubar($clang->gT("Quick statistics"));
}

?>
