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

include_once("login_check.php");  //Login Check dies also if the script is started directly

if (!isset($limit)) {$limit=returnglobal('limit');}
if (!isset($surveyid)) {$surveyid=returnglobal('sid');}
if (!isset($id)) {$id=returnglobal('id');}
if (!isset($order)) {$order=returnglobal('order');}
if (!isset($browselang)) {$browselang=returnglobal('browselang');}

//Ensure script is not run directly, avoid path disclosure
if (!isset($dbprefix) || isset($_REQUEST['dbprefix'])) {die("Cannot run this script directly");}

//Check if results table exists
if (tableExists('survey_'.$surveyid)==false) die("Your results table is missing!");

$surveyinfo=getSurveyInfo($surveyid);
require_once(dirname(__FILE__).'/sessioncontrol.php');

// Set language for questions and labels to base language of this survey

if (isset($browselang) && $browselang!='')
{
    $_SESSION['browselang']=$browselang;
    $language=$_SESSION['browselang'];
}
elseif (isset($_SESSION['browselang']))
{
    $language=$_SESSION['browselang'];
    $languagelist = GetAdditionalLanguagesFromSurveyID($surveyid);
    $languagelist[]=GetBaseLanguageFromSurveyID($surveyid);
    if (!in_array($language,$languagelist))
    {
        $language = GetBaseLanguageFromSurveyID($surveyid);
    }
}
else
{
    $language = GetBaseLanguageFromSurveyID($surveyid);
}

$surveyoptions = browsemenubar($clang->gT("Browse Responses"));
$browseoutput = "";

if (!$database_exists) //DATABASE DOESN'T EXIST OR CAN'T CONNECT
{
    $browseoutput .= "\t<div class='messagebox'><div class='header'>"
            . $clang->gT("Browse Responses")."</div><div class='warningheader'>"
            .$clang->gT("Error")."\t</div>\n"
            . $clang->gT("The defined LimeSurvey database does not exist")."<br />\n"
            . $clang->gT("Either your selected database has not yet been created or there is a problem accessing it.")."<br /><br />\n"
            ."<input type='submit' value='".$clang->gT("Main Admin Screen")."' onclick=\"window.open('$scriptname', '_top')\" /><br />\n"
            ."</div>";
    return;
}
if (!$surveyid && !$subaction) //NO SID OR ACTION PROVIDED
{
    $browseoutput .= "\t<tr ><td colspan='2' height='4'><strong>"
            . $clang->gT("Browse Responses")."</strong></td></tr>\n"
            ."\t<tr><td align='center'>\n"
            ."<strong><font color='red'>".$clang->gT("Error")."</font></strong><br />\n"
            . $clang->gT("You have not selected a survey to browse.")."<br /><br />\n"
            ."<input type='submit' value='"
            . $clang->gT("Main Admin Screen")."' onclick=\"window.open('$scriptname', '_top')\" /><br />\n"
            ."</td></tr></table>\n";
    return;
}

$js_admin_includes[]='scripts/browse.js';


//CHECK IF SURVEY IS ACTIVATED AND EXISTS
$actquery = "SELECT * FROM ".db_table_name('surveys')." as a inner join ".db_table_name('surveys_languagesettings')." as b on (b.surveyls_survey_id=a.sid and b.surveyls_language=a.language) WHERE a.sid=$surveyid";

$actresult = db_execute_assoc($actquery);
$actcount = $actresult->RecordCount();
if ($actcount > 0)
{
    while ($actrow = $actresult->FetchRow())
    {
        $surveytable = db_table_name("survey_".$actrow['sid']);
        $surveytimingstable = db_table_name("survey_".$actrow['sid']."_timings");
        $tokentable = $dbprefix."tokens_".$actrow['sid'];
        $grouptokentable = $dbprefix."grouptokens_".$actrow['sid'];
        $usedtokenstable = $dbprefix."usedtokens_".$actrow['sid'];
        /*
         * DO NEVER EVER PUT VARIABLES AND FUNCTIONS WHICH GIVE BACK DIFFERENT QUOTES
         * IN DOUBLE QUOTED(' and " and \" is used) JAVASCRIPT/HTML CODE!!! (except for: you know what you are doing)
         *
         * Used for deleting a record, fix quote bugs..
         */
        $surveytableNq = db_table_name_nq("survey_".$surveyid);

        $surveyname = "{$actrow['surveyls_title']}";
        if ($actrow['active'] == "N") //SURVEY IS NOT ACTIVE YET
        {
            $browseoutput .= "\t<tr><td colspan='2' height='4'><strong>"
                    . $clang->gT("Browse Responses").":</strong> $surveyname</td></tr>\n"
                    ."\t<tr><td align='center'>\n"
                    ."<strong><font color='red'>".$clang->gT("Error")."</font></strong><br />\n"
                    . $clang->gT("This survey has not been activated. There are no results to browse.")."<br /><br />\n"
                    ."<input type='submit' value='"
                    . $clang->gT("Main Admin Screen")."' onclick=\"window.open('$scriptname?sid=$surveyid', '_top')\" /><br />\n"
                    ."</td></tr></table>\n"
                    ."</body>\n</html>";
            return;
        }
    }
}
else //SURVEY MATCHING $surveyid DOESN'T EXIST
{
    $browseoutput .= "\t<tr><td colspan='2' height='4'><strong>"
            . $clang->gT("Browse Responses")."</strong></td></tr>\n"
            ."\t<tr><td align='center'>\n"
            ."<strong><font color='red'>".$clang->gT("Error")."</font></strong><br />\n"
            . $clang->gT("There is no matching survey.")." ($surveyid)<br /><br />\n"
            ."<input type='submit' value='".$clang->gT("Main Admin Screen")."' onclick=\"window.open('$scriptname', '_top')\" /><br />\n"
            ."</td></tr></table>\n"
            ."</body>\n</html>";
    return;
}

//OK. IF WE GOT THIS FAR, THEN THE SURVEY EXISTS AND IT IS ACTIVE, SO LETS GET TO WORK.
$qulanguage = GetBaseLanguageFromSurveyID($surveyid);
if ($subaction == "id") // Looking at a SINGLE entry
{
    $dateformatdetails=getDateFormatData($_SESSION['dateformat']);

    //SHOW HEADER
    if (!isset($_POST['sql']) || !$_POST['sql']) {$browseoutput .= $surveyoptions;} // Don't show options if coming from tokens script
    //FIRST LETS GET THE NAMES OF THE QUESTIONS AND MATCH THEM TO THE FIELD NAMES FOR THE DATABASE

    $fncount = 0;


    $fieldmap=createFieldMap($surveyid,'full',false,false,$language);
    $position=0;
    foreach ($fieldmap as $field)
    {
        $question=$field['question'];
        if (isset($field['subquestion']) && $field['subquestion']!='')
        {
            $question .=' ('.$field['subquestion'].')';
        }
        if (isset($field['subquestion1']) && isset($field['subquestion2']))
        {
            $question .=' ('.$field['subquestion1'].':'.$field['subquestion2'].')';
        }
        if (isset($field['scale_id']))
        {
            $question .='['.$field['scale'].']';
        }
        $fnames[]=array($field['fieldname'],$question);
        if ($position==1)
        {
            $fnames[] = array("completed", $clang->gT("Completed"), "0");
            if ($surveyinfo['private'] == "N" && db_tables_exist($tokentable)) //add token to top of list if survey is not private
            {
                $fnames[] = array("firstname", $clang->gT("First Name"));
                $fnames[] = array("lastname", $clang->gT("Last Name"));
                $fnames[] = array("email", $clang->gT("Email"));
            }
        }
        $position++;
    }

    $nfncount = count($fnames)-1;
    //SHOW INDIVIDUAL RECORD
    $idquery = "SELECT * FROM $surveytable ";
    if ($surveyinfo['private'] == "N" && db_tables_exist($tokentable))
        $idquery .= "LEFT JOIN $tokentable ON $surveytable.token = $tokentable.token ";
    if (incompleteAnsFilterstate() == "inc")
        $idquery .= " WHERE (submitdate = ".$connect->DBDate('1980-01-01'). " OR submitdate IS NULL) AND ";
    elseif (incompleteAnsFilterstate() == "filter")
        $idquery .= " WHERE submitdate >= ".$connect->DBDate('1980-01-01'). " AND ";
    else
        $idquery .= " WHERE ";
    if ($id < 1) { $id = 1; }
    if (isset($_POST['sql']) && $_POST['sql'])
    {
        if (get_magic_quotes_gpc()) {$idquery .= stripslashes($_POST['sql']);}
        else {$idquery .= "{$_POST['sql']}";}
    }
    else {$idquery .= "$surveytable.id = $id";}
    $idresult = db_execute_assoc($idquery) or safe_die ("Couldn't get entry<br />\n$idquery<br />\n".$connect->ErrorMsg());
    while ($idrow = $idresult->FetchRow())
    {
        $id=$idrow['id'];
        $rlanguage=$idrow['startlanguage'];
    }
    $next=$id+1;
    $last=$id-1;
    $browseoutput .= "<div class='menubar'>\n"
            ."<div class='menubar-title'>"
            ."<strong>".$clang->gT("View Response").":</strong> $id\n"
            ."\t</div><div class='menubar-main'>\n"
            ."<img src='$imagefiles/blank.gif' width='31' height='20' border='0' hspace='0' align='left' alt='' />\n"
            ."<img src='$imagefiles/seperator.gif' border='0' hspace='0' align='left' alt='' />\n";
    if (isset($rlanguage))
    {
        $browseoutput .="<a href='$scriptname?action=dataentry&amp;subaction=edit&amp;id=$id&amp;sid=$surveyid&amp;language=$rlanguage' "
                ."title='".$clang->gTview("Edit this entry")."'>"
                ."<img align='left' src='$imagefiles/edit.png' alt='".$clang->gT("Edit this entry")."' /></a>\n";
    }
    if (bHasRight($surveyid,'delete_survey') && isset($rlanguage))
    {

        $browseoutput .= "<a href='#' title='".$clang->gTview("Delete this entry")."' onclick=\"if (confirm('".$clang->gT("Are you sure you want to delete this entry?","js")."')) {".get2post($scriptname.'?action=dataentry&amp;subaction=delete&amp;id='.$id.'&amp;sid='.$surveyid)."}\" >"
                ."<img align='left' hspace='0' border='0' src='$imagefiles/delete.png' alt='".$clang->gT("Delete this entry")."' /></a>\n";
    }
    else
    {
        $browseoutput .=  "<img align='left' hspace='0' border='0' src='$imagefiles/delete_disabled.png' alt='".$clang->gT("You don't have permission to delete this entry.")."'/>";
    }
    //Export this response
    $browseoutput .= "<a href='$scriptname?action=exportresults&amp;sid=$surveyid&amp;id=$id'" .
            "title='".$clang->gTview("Export this Response")."' >" .
            "<img name='ExportAnswer' src='$imagefiles/export.png' alt='". $clang->gT("Export this Response")."' align='left' /></a>\n"
            ."<img src='$imagefiles/seperator.gif' border='0' hspace='0' align='left' alt='' />\n"
            ."<img src='$imagefiles/blank.gif' width='20' height='20' border='0' hspace='0' align='left' alt='' />\n"
            ."<a href='$scriptname?action=browse&amp;subaction=id&amp;id=$last&amp;sid=$surveyid' "
            ."title='".$clang->gTview("Show previous...")."' >"
            ."<img name='DataBack' align='left' src='$imagefiles/databack.png' alt='".$clang->gT("Show previous...")."' /></a>\n"
            ."<img src='$imagefiles/blank.gif' width='13' height='20' border='0' hspace='0' align='left' alt='' />\n"
            ."<a href='$scriptname?action=browse&amp;subaction=id&amp;id=$next&amp;sid=$surveyid' title='".$clang->gTview("Show next...")."'>"
            ."<img name='DataForward' align='left' src='$imagefiles/dataforward.png' alt='".$clang->gT("Show next...")."' /></a>\n"
            ."</div>\n"
            ."\t</div>\n";

    $browseoutput .= "<table class='detailbrowsetable' width='99%'>\n";
    $idresult = db_execute_assoc($idquery) or safe_die ("Couldn't get entry<br />$idquery<br />".$connect->ErrorMsg());
    while ($idrow = $idresult->FetchRow())
    {
        $i=0;
        $highlight=false;
        for ($i; $i<$nfncount+1; $i++)
        {
            $inserthighlight='';
            if ($highlight)
            {
                $inserthighlight="class='highlight'";
            }
            $browseoutput .= "\t<tr $inserthighlight>\n"
                    ."<th align='right' width='50%'>"
                    .strip_tags(strip_javascript($fnames[$i][1]))."</th>\n"
                    ."<td align='left' >";
            if ($i == 2)
            {
                if ($idrow['submitdate'] == NULL || $idrow['submitdate'] == "N") { $browseoutput .= "N"; }
                else { $browseoutput .= "Y"; }
            }
            else
            {
                $browseoutput .= htmlspecialchars(strip_tags(strip_javascript(getextendedanswer($fnames[$i][0], $idrow[$fnames[$i][0]], '', $dateformatdetails['phpdate']))), ENT_QUOTES);
            }
            $browseoutput .= "</td>\n\t</tr>\n";
            $highlight=!$highlight;
        }
    }
    $browseoutput .= "</table>\n";

}

elseif ($subaction == "all")
{
    /**
     * fnames is used as informational array
     * it containts
     *             $fnames[] = array(<dbfieldname>, <some strange title>, <questiontext>, <group_id>, <questiontype>);
     */

    $browseoutput .= "\n<script type='text/javascript'>
                          var strdeleteconfirm='".$clang->gT('Do you really want to delete this response?','js')."'; 
                          var strDeleteAllConfirm='".$clang->gT('Do you really want to delete all marked responses?','js')."'; 
                        </script>\n";    
    if (!isset($_POST['sql']))
    {$browseoutput .= $surveyoptions;} //don't show options when called from another script with a filter on
    else
    {
        $browseoutput .= "\t<tr><td colspan='2' height='4'><strong>".$clang->gT("Browse Responses").":</strong> $surveyname</td></tr>\n"
                ."\n<tr><td><table width='100%' align='center' border='0' bgcolor='#EFEFEF'>\n"
                ."\t<tr>\n"
                ."<td align='center'>\n"
                ."".$clang->gT("Showing Filtered Results")."<br />\n"
                ."&nbsp;[<a href=\"javascript:window.close()\">".$clang->gT("Close")."</a>]"
                ."</font></td>\n"
                ."\t</tr>\n"
                ."</table></td></tr>\n";

    }

    if (isset($_POST['deleteanswer']) && $_POST['deleteanswer']!='' && bHasRight($surveyid,'delete_survey'))
    {
        $iResponseID=(int) $_POST['deleteanswer']; // sanitize the value     
        $query="delete FROM $surveytable where id={$iResponseID}";
        $connect->execute($query) or safe_die("Could not delete response<br />$dtquery<br />".$connect->ErrorMsg()); // checked
    }

    if (isset($_POST['markedresponses']) && count($_POST['markedresponses'])>0 && bHasRight($surveyid,'delete_survey'))        
    {
        foreach ($_POST['markedresponses'] as $iResponseID)
        {
            $iResponseID=(int)$iResponseID; // sanitize the value
            $query="delete FROM {$surveytable} where id={$iResponseID}";
            $connect->execute($query) or safe_die("Could not delete response<br />{$dtquery}<br />".$connect->ErrorMsg());  // checked  
        }
    }
    
    
    $fields=createFieldMap($surveyid, 'full', false, false, $language);

    //add token to top of list if survey is not private
    if ($surveyinfo['private'] == "N" && db_tables_exist($tokentable)) //add token to top of list if survey is not private
    {
        $fnames[] = array("token", "Token", $clang->gT("Token ID"), 0);
        $fnames[] = array("firstname", "First Name", $clang->gT("First Name"), 0);
        $fnames[] = array("lastname", "Last Name", $clang->gT("Last Name"), 0);
        $fnames[] = array("email", "Email", $clang->gT("Email"), 0);
    }
    if (db_tables_exist($grouptokentable)) {
        $fnames[] = array("grouptoken", "Group Token", $clang->gT("Group Token"), 0);
    }
    $fnames[] = array("submitdate", "Completed", $clang->gT("Completed"), "0", 'D');

    foreach ($fields as $fielddetails)
    {
        if ($fielddetails['fieldname']=='lastpage' || $fielddetails['fieldname'] == 'submitdate' || $fielddetails['fieldname'] == 'token')
            continue;
        $question=$fielddetails['question'];
        
        // no headers for time data and group tokens
        if ($fielddetails['type']=='interview_time')
			continue;
        if ($fielddetails['type']=='page_time')
			continue;
		if ($fielddetails['type']=='answer_time')
			continue;
		if ($fielddetails['type']=='grouptoken')
            continue;
			
        if (isset($fielddetails['subquestion']) && $fielddetails['subquestion']!='')
            $question .=' ('.$fielddetails['subquestion'].')';
        if (isset($fielddetails['subquestion1']) && isset($fielddetails['subquestion2']))
            $question .=' ('.$fielddetails['subquestion1'].':'.$fielddetails['subquestion2'].')';
        if (isset($fielddetails['scale_id']))
            $question .='['.$fielddetails['scale'].']';
        $fnames[]=array($fielddetails['fieldname'],$question);
    }


    $fncount = count($fnames);

    //NOW LETS CREATE A TABLE WITH THOSE HEADINGS

    $tableheader = "<!-- DATA TABLE -->";
    if ($fncount < 10) {$tableheader .= "<table class='browsetable' width='100%'>\n";}
    else {$tableheader .= "<table class='browsetable'>\n";}
    $tableheader .= "\t<thead><tr valign='top'>\n"
            . "<th><input type='checkbox' id='selectall'></th>\n"
            . "<th>".$clang->gT('Actions')."</th>\n";
    foreach ($fnames as $fn)
    {
        if (!isset($currentgroup))  {$currentgroup = $fn[1]; $gbc = "oddrow";}
        if ($currentgroup != $fn[1])
        {
            $currentgroup = $fn[1];
            if ($gbc == "oddrow") {$gbc = "evenrow";}
            else {$gbc = "oddrow";}
            }
        $tableheader .= "<th class='$gbc'><strong>"
                . strip_javascript("$fn[1]")
                . "</strong></th>\n";
    }
    $tableheader .= "\t</tr></thead>\n\n";
    $tableheader .= "\t<tfoot><tr><td colspan=".($fncount+3).">"
    if (bHasRight($surveyid,'delete_survey'))
    {
        $tableheader .= "\t<tfoot><tr><td colspan=".($fncount+2).">"
                   ."<img id='imgDeleteMarkedResponses' src='$imagefiles/token_delete.png' alt='".$clang->gT('Delete marked responses')."' />"
                   ."\t</tr></tfoot>\n\n";
    }

    $start=returnglobal('start');
    $limit=returnglobal('limit');
    if (!isset($limit) || $limit== '') {$limit = 50;}
    if (!isset($start) || $start =='') {$start = 0;}
    
    //Create the query
    if ($surveyinfo['private'] == "N" && db_tables_exist($tokentable))
    {
        $sql_from = "$surveytable LEFT JOIN $tokentable ON $surveytable.token = $tokentable.token";
    } else {
        $sql_from = $surveytable;
    }
    
    $selectedgroup = returnglobal('selectgroup'); // group token id
    
    $sql_where = "";
    if (incompleteAnsFilterstate() == "inc")
    {
        $sql_where .= "submitdate IS NULL";
        
        // filter group token
        if (db_tables_exist($tokentable) && $selectedgroup != "")
        {
            $selectedgrouptoken = $connect->getOne("SELECT token FROM $grouptokentable WHERE gtid='{$selectedgroup}'");
            $dtquery .= " AND grouptoken='{$selectedgrouptoken}'";
        }
    }
    elseif (incompleteAnsFilterstate() == "filter")
    {
        $sql_where .= "submitdate IS NOT NULL";
        
        // filter group token
        if (db_tables_exist($tokentable) && $selectedgroup != "")
        {
            $selectedgrouptoken = $connect->getOne("SELECT token FROM $grouptokentable WHERE gtid='{$selectedgroup}'");
            $dtquery .= " AND grouptoken='{$selectedgrouptoken}'";
        }
    if (isset($_POST['sql']) && stripcslashes($_POST['sql']) !== "" && $_POST['sql'] !== "NULL")
    {
        if (!empty($sql_where)) $sql_where .= " AND ";
        $sql_where .= stripcslashes($_POST['sql']);
    }
    if (!empty($sql_where)) $sql_where = " WHERE " . $sql_where;

    //LETS COUNT THE DATA
    $dtquery = "SELECT count(*) FROM $sql_from $sql_where";
    }
    // filter group token
    elseif (db_tables_exist($tokentable) && $selectedgroup != "")
    {
        $selectedgrouptoken = $connect->getOne("SELECT token FROM $grouptokentable WHERE gtid='{$selectedgroup}'");
        $dtquery .= " WHERE grouptoken='{$selectedgrouptoken}'";
    }
    
    $dtresult=db_execute_num($dtquery) or safe_die("Couldn't get response data<br />$dtquery<br />".$connect->ErrorMsg());
    while ($dtrow=$dtresult->FetchRow()) {$dtcount=$dtrow[0];}

    if ($limit > $dtcount) {$limit=$dtcount;}

    //NOW LETS SHOW THE DATA
    if (isset($_POST['sql']))
    {
        if ($_POST['sql'] == "NULL" )
        {
            if ($surveyinfo['private'] == "N" && db_tables_exist($tokentable))
                $dtquery = "SELECT * FROM $surveytable LEFT JOIN $tokentable ON $surveytable.token = $tokentable.token ";
            else
                $dtquery = "SELECT * FROM $surveytable ";
            // group token id
            $selectedgroup = returnglobal('selectgroup');
            if (incompleteAnsFilterstate() == "inc")
            {
                $dtquery .= "WHERE submitdate IS NULL ";
                // filter group token
                if (db_tables_exist($tokentable) && $selectedgroup != "")
                {
                    $selectedgrouptoken = $connect->getOne("SELECT token FROM $grouptokentable WHERE gtid='{$selectedgroup}'");
                    $dtquery .= " AND grouptoken='{$selectedgrouptoken}'";
                }
            }
            elseif (incompleteAnsFilterstate() == "filter")
            {
                $dtquery .= " WHERE submitdate IS NOT NULL ";
                // filter group token
                if (db_tables_exist($tokentable) && $selectedgroup != "")
                {
                    $selectedgrouptoken = $connect->getOne("SELECT token FROM $grouptokentable WHERE gtid='{$selectedgroup}'");
                    $dtquery .= " AND grouptoken='{$selectedgrouptoken}'";
                }
            }
            // filter group token
            elseif (db_tables_exist($tokentable) && $selectedgroup != "")
            {
                $selectedgrouptoken = $connect->getOne("SELECT token FROM $grouptokentable WHERE gtid='{$selectedgroup}'");
                $dtquery .= " WHERE grouptoken='{$selectedgrouptoken}'";
            }
            $dtquery .= " ORDER BY {$surveytable}.id";
        }
        else
        {
            if ($surveytable['private'] == "N" && db_tables_exist($tokentable))
                $dtquery = "SELECT * FROM $surveytable LEFT JOIN $tokentable ON $surveytable.token = $tokentable.token ";
            else
                $dtquery = "SELECT * FROM $surveytable ";
            $selectedgroup = returnglobal('selectgroup');
            if (incompleteAnsFilterstate() == "inc")
            {
                $dtquery .= "submitdate IS NULL ";
                // filter group token
                if (db_tables_exist($tokentable) && $selectedgroup != "")
                {
                    $selectedgrouptoken = $connect->getOne("SELECT token FROM $grouptokentable WHERE gtid='{$selectedgroup}'");
                    $dtquery .= " AND grouptoken='{$selectedgrouptoken}'";
                }
                if (stripcslashes($_POST['sql']) !== "")
                {
                    $dtquery .= " AND ";
                }
            }
            elseif (incompleteAnsFilterstate() == "filter")
            {
                $dtquery .= " submitdate IS NOT NULL ";
                // filter group token
                if (db_tables_exist($tokentable) && $selectedgroup != "")
                {
                    $selectedgrouptoken = $connect->getOne("SELECT token FROM $grouptokentable WHERE gtid='{$selectedgroup}'");
                    $dtquery .= " AND grouptoken='{$selectedgrouptoken}'";
                }
                if (stripcslashes($_POST['sql']) !== "")
                {
                    $dtquery .= " AND ";
                }
            }
            // filter group token
            elseif (db_tables_exist($tokentable) && $selectedgroup != "")
            {
                $selectedgrouptoken = $connect->getOne("SELECT token FROM $grouptokentable WHERE gtid='{$selectedgroup}'");
                $dtquery .= " grouptoken='{$selectedgrouptoken}'";
            }
            if (stripcslashes($_POST['sql']) !== "")
            {
                $dtquery .= stripcslashes($_POST['sql'])." ";
            }
            $dtquery .= " ORDER BY {$surveytable}.id";
        }
    }
    else
    {
        if ($surveyinfo['private'] == "N" && db_tables_exist($tokentable))
            $dtquery = "SELECT * FROM $surveytable LEFT JOIN $tokentable ON $surveytable.token = $tokentable.token ";
        else
            $dtquery = "SELECT * FROM $surveytable ";
        if (incompleteAnsFilterstate() == "inc")
        {
            $dtquery .= " WHERE submitdate IS NULL ";
            // filter group token
            if (db_tables_exist($tokentable) && $selectedgroup != "")
            {
                $selectedgrouptoken = $connect->getOne("SELECT token FROM $grouptokentable WHERE gtid='{$selectedgroup}'");
                $dtquery .= " AND grouptoken='{$selectedgrouptoken}'";
            }
        }
        elseif (incompleteAnsFilterstate() == "filter")
        {
            $dtquery .= " WHERE submitdate IS NOT NULL ";
            // filter group token
            if (db_tables_exist($tokentable) && $selectedgroup != "")
            {
                $selectedgrouptoken = $connect->getOne("SELECT token FROM $grouptokentable WHERE gtid='{$selectedgroup}'");
                $dtquery .= " AND grouptoken='{$selectedgrouptoken}'";
            }
        }
        // filter group token
        elseif (db_tables_exist($tokentable) && $selectedgroup != "")
        {
            $selectedgrouptoken = $connect->getOne("SELECT token FROM $grouptokentable WHERE gtid='{$selectedgroup}'");
            $dtquery .= " WHERE grouptoken='{$selectedgrouptoken}'";
        }
        $dtquery .= " ORDER BY {$surveytable}.id";
    }
    if ($order == "desc") {$dtquery .= " DESC";}

    if (isset($limit))
    {
        if (!isset($start)) {$start = 0;}
        $dtresult = db_select_limit_assoc($dtquery, $limit, $start) or safe_die("Couldn't get surveys<br />$dtquery<br />".$connect->ErrorMsg());
    }
    else
    {
        $dtresult = db_execute_assoc($dtquery) or safe_die("Couldn't get surveys<br />$dtquery<br />".$connect->ErrorMsg());
    }
    $dtcount2 = $dtresult->RecordCount();
    $cells = $fncount+1;


    //CONTROL MENUBAR
    $last=$start-$limit;
    $next=$start+$limit;
    $end=$dtcount-$limit;
    if ($end < 0) {$end=0;}
    if ($last <0) {$last=0;}
    if ($next >= $dtcount) {$next=$dtcount-$limit;}
    if ($end < 0) {$end=0;}

    $browseoutput .= "<div class='menubar'>\n"
            . "\t<div class='menubar-title'>\n"
            . "<strong>".$clang->gT("Data view control")."</strong></div>\n"
            . "\t<div class='menubar-main'>\n";
    if (!isset($_POST['sql']))
    {
        $browseoutput .= "<a href='$scriptname?action=browse&amp;subaction=all&amp;sid=$surveyid&amp;start=0&amp;limit=$limit' "
                ."title='".$clang->gTview("Show start...")."' >"
                ."<img name='DataBegin' align='left' src='$imagefiles/databegin.png' alt='".$clang->gT("Show start...")."' /></a>\n"
                ."<a href='$scriptname?action=browse&amp;subaction=all&amp;sid=$surveyid&amp;start=$last&amp;limit=$limit' "
                ."title='".$clang->gTview("Show previous..")."' >"
                ."<img name='DataBack' align='left'  src='$imagefiles/databack.png' alt='".$clang->gT("Show previous..")."' /></a>\n"
                ."<img src='$imagefiles/blank.gif' width='13' height='20' border='0' hspace='0' align='left' alt='' />\n"

                ."<a href='$scriptname?action=browse&amp;subaction=all&amp;sid=$surveyid&amp;start=$next&amp;limit=$limit' " .
                "title='".$clang->gT("Show next...")."' >".
                "<img name='DataForward' align='left' src='$imagefiles/dataforward.png' alt='".$clang->gT("Show next..")."' /></a>\n"
                ."<a href='$scriptname?action=browse&amp;subaction=all&amp;sid=$surveyid&amp;start=$end&amp;limit=$limit' " .
                "title='".$clang->gT("Show last...")."' >" .
                "<img name='DataEnd' align='left' src='$imagefiles/dataend.png' alt='".$clang->gT("Show last..")."' /></a>\n"
                ."<img src='$imagefiles/seperator.gif' border='0' hspace='0' align='left' alt='' />\n";
    }
    $selectshow='';
    $selectinc='';
    $selecthide='';

    if(incompleteAnsFilterstate() == "inc") { $selectinc="selected='selected'"; }
    elseif (incompleteAnsFilterstate() == "filter") { $selecthide="selected='selected'"; }
    else { $selectshow="selected='selected'"; }

    $browseoutput .="<form action='$scriptname?action=browse' id='browseresults' method='post'><font size='1' face='verdana'>\n"
            ."<img src='$imagefiles/blank.gif' width='31' height='20' border='0' hspace='0' align='right' alt='' />\n"
            ."".$clang->gT("Records Displayed:")."<input type='text' size='4' value='$dtcount2' name='limit' id='limit' />\n"
            ."&nbsp;&nbsp; ".$clang->gT("Starting From:")."<input type='text' size='4' value='$start' name='start' id='start' />\n"
            ."&nbsp;&nbsp; <input type='submit' value='".$clang->gT("Show")."' />\n"
            ."&nbsp;&nbsp; ".$clang->gT("Display:")."<select name='filterinc' onchange='javascript:document.getElementById(\"limit\").value=\"\";submit();'>\n"
            ."\t<option value='show' $selectshow>".$clang->gT("All responses")."</option>\n"
            ."\t<option value='filter' $selecthide>".$clang->gT("Completed responses only")."</option>\n"
            ."\t<option value='incomplete' $selectinc>".$clang->gT("Incomplete responses only")."</option>\n"
            ."</select>\n";

    // a dropdown for group selection (group tokens)
    if (db_tables_exist($grouptokentable))
    {
        $selectall = "selected='selected'";
        $gtid = returnglobal('selectgroup');
        if (!$gtid) {
            $selectall = "";
        }
        
        $browseoutput .= "&nbsp;&nbsp; <font size='1' face='verdana'>\n"
        .$clang->gT("Group:")."<select name='selectgroup' onchange='javascript:document.getElementById(\"limit\").value=\"\";submit();'>\n"
        ."\t<option value='' $selectall>".$clang->gT("All groups")."</option>\n";
        
        // get groups from table
        $gtquery = "SELECT * FROM $grouptokentable";
        $gtresult = db_execute_assoc($gtquery) or safe_die("Couldn't get group tokens:<br />$dtquery<br />".$connect->ErrorMsg());
        while ($gtrow = $gtresult->FetchRow())
        {
            $browseoutput .= "\t<option value='{$gtrow['gtid']}'";
            if ($gtid == $gtrow['gtid'])
                $browseoutput .= "selected='selected'";
            $browseoutput .= ">{$gtrow['name']}</option>\n";
        }
        $browseoutput .= "</select>\n";
    }
    
    $browseoutput .= "</font>\n"
                    ."<input type='hidden' name='sid' value='$surveyid' />\n"
                    ."<input type='hidden' name='action' value='browse' />\n"
                    ."<input type='hidden' name='subaction' value='all' />\n";

    if (isset($_POST['sql']))
    {
        $browseoutput .= "<input type='hidden' name='sql' value='".html_escape($_POST['sql'])."' />\n";
    }
    $browseoutput .= 	 "</form></div>\n"
            ."\t</div><form action='$scriptname?action=browse' id='resulttableform' method='post'>\n";

    $browseoutput .= $tableheader;
    $dateformatdetails=getDateFormatData($_SESSION['dateformat']);

    while ($dtrow = $dtresult->FetchRow())
    {
        if (!isset($bgcc)) {$bgcc="evenrow";}
        else
        {
            if ($bgcc == "evenrow") {$bgcc = "oddrow";}
            else {$bgcc = "evenrow";}
            }
        $browseoutput .= "\t<tr class='$bgcc' valign='top'>\n"
                ."<td align='center'><input type='checkbox' class='cbResponseMarker' value='{$dtrow['id']}' name='markedresponses[]' /></td>\n"
                ."<td align='center'>
        <a href='$scriptname?action=browse&amp;sid=$surveyid&amp;subaction=id&amp;id={$dtrow['id']}'><img src='$imagefiles/token_viewanswer.png' alt='".$clang->gT('View response details')."'/></a>
        <a href='$scriptname?action=dataentry&amp;sid=$surveyid&amp;subaction=edit&amp;id={$dtrow['id']}&amp;lang={$language}'><img src='$imagefiles/token_edit.png' alt='".$clang->gT('Edit this response')."'/></a>";
		
        if (bHasRight($surveyid,'delete_survey'))
        {
            $browseoutput .= "<a><img id='deleteresponse_{$dtrow['id']}' src='$imagefiles/token_delete.png' alt='".$clang->gT('Delete this response')."' class='deleteresponse'/></a>\n";
        }
        $browseoutput .= "</td>";
        $i = 0;
        //If not private, display the token info and link to the token screen
        if ($surveyinfo['private'] == "N" && $dtrow['token'] && db_tables_exist($tokentable))
        {
            if (isset($dtrow['tid']) && !empty($dtrow['tid']))
            {
                //If we have a token, create a link to edit it
                $browsedatafield = "<a href='$scriptname?action=tokens&amp;sid=$surveyid&amp;subaction=edit&amp;tid={$dtrow['tid']}' title='".$clang->gT("Edit this token")."'>";
                $browsedatafield .= "{$dtrow['token']}";
                $browsedatafield .= "</a>";
            } else {
                //No corresponding token in the token tabel, just display the token
                $browsedatafield .= "{$dtrow['token']}";
            }
            $browseoutput .= "<td align='center'>$browsedatafield</td>\n";
            $i++;   //We skip the first record (=token) as we just outputted that one
            }
        
        for ($i; $i<$fncount; $i++)
        {
            $browsedatafield=htmlspecialchars($dtrow[$fnames[$i][0]]);
            
            if ( isset($fnames[$i][4]) && $fnames[$i][4] == 'D' && $fnames[$i][0] != '')
            {
                if ($dtrow[$fnames[$i][0]] == NULL)
                    $browsedatafield = "N";
                else
                    $browsedatafield = "Y";
            }
            // display group token as hyperlink
            if ($fnames[$i][0] == 'grouptoken' && db_tables_exist($grouptokentable))
            {
                $browsedatafield = "";
                $SQL = "Select * FROM ".db_table_name('grouptokens_'.$surveyid)." WHERE token='{$dtrow['grouptoken']}'";
                if (db_tables_exist(db_table_name_nq('grouptokens_'.$surveyid)) &&
                        $SQLResult = db_execute_assoc($SQL))
                {
                    $TokenRow = $SQLResult->FetchRow();
                }
                if (isset($TokenRow) && $TokenRow)
                {
                    $browsedatafield .= "<a href='$scriptname?action=tokens&amp;sid=$surveyid&amp;subaction=editgroup&amp;gtid={$TokenRow['gtid']}' title='".$clang->gT("Edit this group token")."'>";
                }
                $browsedatafield .= "{$dtrow['grouptoken']}";
                if (isset($TokenRow) && $TokenRow)
                {
                    $browsedatafield .= "</a>";
                }
            }
            $browseoutput .= "<td align='center'>$browsedatafield</td>\n";
        }
        $browseoutput .= "\t</tr>\n";
    }
    $browseoutput .= "</table>
    <input type='hidden' name='sid' value='$surveyid' />
    <input type='hidden' name='subaction' value='all' />
    <input id='deleteanswer' name='deleteanswer' value='' type='hidden' />
    </form>\n<br />\n";
}
elseif ($surveyinfo['savetimings']=="Y" && $subaction == "time"){
	$browseoutput .= $surveyoptions;
	$browseoutput .= '<div class="header ui-widget-header">'.$clang->gT('Time statistics').'</div>';
	
	// table of time statistics - only display completed surveys
    $browseoutput .= "\n<script type='text/javascript'>
                          var strdeleteconfirm='".$clang->gT('Do you really want to delete this response?','js')."'; 
                          var strDeleteAllConfirm='".$clang->gT('Do you really want to delete all marked responses?','js')."'; 
                        </script>\n";

    if (isset($_POST['deleteanswer']) && $_POST['deleteanswer']!='')
    {
        $_POST['deleteanswer']=(int) $_POST['deleteanswer']; // sanitize the value     
        $query="delete FROM $surveytable where id={$_POST['deleteanswer']}";
        $connect->execute($query) or safe_die("Could not delete response<br />$dtquery<br />".$connect->ErrorMsg()); // checked
    }

    if (isset($_POST['markedresponses']) && count($_POST['markedresponses'])>0)
    {
        foreach ($_POST['markedresponses'] as $iResponseID)
        {
            $iResponseID=(int)$iResponseID; // sanitize the value
            $query="delete FROM $surveytable where id={$iResponseID}";
            $connect->execute($query) or safe_die("Could not delete response<br />$dtquery<br />".$connect->ErrorMsg());  // checked  
        }
    }
    
    $fields=createFieldMap($surveyid,'full');

    foreach ($fields as $fielddetails)
    {
        // headers for answer id and time data
		if ($fielddetails['type']=='id')
			$fnames[]=array($fielddetails['fieldname'],$fielddetails['question']);
        if ($fielddetails['type']=='interview_time')
			$fnames[]=array($fielddetails['fieldname'],"Interview time");
        if ($fielddetails['type']=='page_time')
			$fnames[]=array($fielddetails['fieldname'],"Page ".$fielddetails['gid']." time");
		if ($fielddetails['type']=='answer_time')
			$fnames[]=array($fielddetails['fieldname'],"Question ".$fielddetails['qid']." time");
    }
    $fncount = count($fnames);

    //NOW LETS CREATE A TABLE WITH THOSE HEADINGS
    $tableheader = "<!-- DATA TABLE -->";
    if ($fncount < 10) {$tableheader .= "<table class='browsetable' width='100%'>\n";}
    else {$tableheader .= "<table class='browsetable'>\n";}
    $tableheader .= "\t<thead><tr valign='top'>\n"
            . "<th><input type='checkbox' id='selectall'></th>\n"
            . "<th>Actions</th>\n";
    foreach ($fnames as $fn)
    {
        if (!isset($currentgroup))  {$currentgroup = $fn[1]; $gbc = "oddrow";}
        if ($currentgroup != $fn[1])
        {
            $currentgroup = $fn[1];
            if ($gbc == "oddrow") {$gbc = "evenrow";}
            else {$gbc = "oddrow";}
            }
        $tableheader .= "<th class='$gbc'><strong>"
                . strip_javascript("$fn[1]")
                . "</strong></th>\n";
    }
    $tableheader .= "\t</tr></thead>\n\n";
    $tableheader .= "\t<tfoot><tr><td colspan=".($fncount+2).">"
                   ."<img id='imgDeleteMarkedResponses' src='$imagefiles/token_delete.png' alt='".$clang->gT('Delete marked responses')."' />"
                   ."\t</tr></tfoot>\n\n";

    $start=returnglobal('start');
    $limit=returnglobal('limit');
    if (!isset($limit) || $limit== '') {$limit = 50;}
    if (!isset($start) || $start =='') {$start = 0;}
    
    //LETS COUNT THE DATA
    $dtquery = "SELECT count(*) FROM $surveytimingstable NATURAL JOIN $surveytable WHERE submitdate IS NOT NULL ";
    
    $dtresult=db_execute_num($dtquery) or safe_die("Couldn't get response data<br />$dtquery<br />".$connect->ErrorMsg());
    while ($dtrow=$dtresult->FetchRow()) {$dtcount=$dtrow[0];}

    if ($limit > $dtcount) {$limit=$dtcount;}

    //NOW LETS SHOW THE DATA
    $dtquery = "SELECT * FROM $surveytimingstable NATURAL JOIN $surveytable WHERE submitdate IS NOT NULL ORDER BY $surveytable.id";
    
    if ($order == "desc") {$dtquery .= " DESC";}

    if (isset($limit))
    {
        if (!isset($start)) {$start = 0;}
        $dtresult = db_select_limit_assoc($dtquery, $limit, $start) or safe_die("Couldn't get surveys<br />$dtquery<br />".$connect->ErrorMsg());
    }
    else
    {
        $dtresult = db_execute_assoc($dtquery) or safe_die("Couldn't get surveys<br />$dtquery<br />".$connect->ErrorMsg());
    }
    $dtcount2 = $dtresult->RecordCount();
    $cells = $fncount+1;

    //CONTROL MENUBAR
    $last=$start-$limit;
    $next=$start+$limit;
    $end=$dtcount-$limit;
    if ($end < 0) {$end=0;}
    if ($last <0) {$last=0;}
    if ($next >= $dtcount) {$next=$dtcount-$limit;}
    if ($end < 0) {$end=0;}

    $browseoutput .= "<div class='menubar'>\n"
            . "\t<div class='menubar-title'>\n"
            . "<strong>".$clang->gT("Data view control")."</strong></div>\n"
            . "\t<div class='menubar-main'>\n";
    if (!isset($_POST['sql']))
    {
        $browseoutput .= "<a href='$scriptname?action=browse&amp;subaction=time&amp;sid=$surveyid&amp;start=0&amp;limit=$limit' "
                ."title='".$clang->gTview("Show start...")."' >"
                ."<img name='DataBegin' align='left' src='$imagefiles/databegin.png' alt='".$clang->gT("Show start...")."' /></a>\n"
                ."<a href='$scriptname?action=browse&amp;subaction=time&amp;sid=$surveyid&amp;start=$last&amp;limit=$limit' "
                ."title='".$clang->gTview("Show previous..")."' >"
                ."<img name='DataBack' align='left'  src='$imagefiles/databack.png' alt='".$clang->gT("Show previous..")."' /></a>\n"
                ."<img src='$imagefiles/blank.gif' width='13' height='20' border='0' hspace='0' align='left' alt='' />\n"

                ."<a href='$scriptname?action=browse&amp;subaction=time&amp;sid=$surveyid&amp;start=$next&amp;limit=$limit' " .
                "title='".$clang->gT("Show next...")."' >".
                "<img name='DataForward' align='left' src='$imagefiles/dataforward.png' alt='".$clang->gT("Show next..")."' /></a>\n"
                ."<a href='$scriptname?action=browse&amp;subaction=time&amp;sid=$surveyid&amp;start=$end&amp;limit=$limit' " .
                "title='".$clang->gT("Show last...")."' >" .
                "<img name='DataEnd' align='left' src='$imagefiles/dataend.png' alt='".$clang->gT("Show last..")."' /></a>\n"
                ."<img src='$imagefiles/seperator.gif' border='0' hspace='0' align='left' alt='' />\n";
    }
    $selectshow='';
    $selectinc='';
    $selecthide='';

    if(incompleteAnsFilterstate() == "inc") { $selectinc="selected='selected'"; }
    elseif (incompleteAnsFilterstate() == "filter") { $selecthide="selected='selected'"; }
    else { $selectshow="selected='selected'"; }

    $browseoutput .="<form action='$scriptname?action=browse' id='browseresults' method='post'><font size='1' face='verdana'>\n"
            ."<img src='$imagefiles/blank.gif' width='31' height='20' border='0' hspace='0' align='right' alt='' />\n"
            ."".$clang->gT("Records Displayed:")."<input type='text' size='4' value='$dtcount2' name='limit' id='limit' />\n"
            ."&nbsp;&nbsp; ".$clang->gT("Starting From:")."<input type='text' size='4' value='$start' name='start' id='start' />\n"
            ."&nbsp;&nbsp;&nbsp;&nbsp;<input type='submit' value='".$clang->gT("Show")."' />\n"
            ."</font>\n"
            ."<input type='hidden' name='sid' value='$surveyid' />\n"
            ."<input type='hidden' name='action' value='browse' />\n"
            ."<input type='hidden' name='subaction' value='time' />\n";
    if (isset($_POST['sql']))
    {
        $browseoutput .= "<input type='hidden' name='sql' value='".html_escape($_POST['sql'])."' />\n";
    }
    $browseoutput .= 	 "</form></div>\n"
            ."\t</div><form action='$scriptname?action=browse' id='resulttableform' method='post'>\n";

    $browseoutput .= $tableheader;
    $dateformatdetails=getDateFormatData($_SESSION['dateformat']);

    while ($dtrow = $dtresult->FetchRow())
    {
        if (!isset($bgcc)) {$bgcc="evenrow";}
        else
        {
            if ($bgcc == "evenrow") {$bgcc = "oddrow";}
            else {$bgcc = "evenrow";}
            }
        $browseoutput .= "\t<tr class='$bgcc' valign='top'>\n"
                ."<td align='center'><input type='checkbox' class='cbResponseMarker' value='{$dtrow['id']}' name='markedresponses[]' /></td>\n"
                ."<td align='center'>
        <a href='$scriptname?action=browse&amp;sid=$surveyid&amp;subaction=id&amp;id={$dtrow['id']}'><img src='$imagefiles/token_viewanswer.png' alt='".$clang->gT('View response details')."'/></a>
        <a href='$scriptname?action=dataentry&amp;sid=$surveyid&amp;subaction=edit&amp;id={$dtrow['id']}'><img src='$imagefiles/token_edit.png' alt='".$clang->gT('Edit this response')."'/></a>
        <a><img id='deleteresponse_{$dtrow['id']}' src='$imagefiles/token_delete.png' alt='".$clang->gT('Delete this response')."' class='deleteresponse'/></a></td>\n";
		
        for ($i = 0; $i<$fncount; $i++)
        {
            $browsedatafield=htmlspecialchars($dtrow[$fnames[$i][0]]);
            
            // seconds -> minutes & seconds
			if (strtolower(substr($fnames[$i][0],-4)) == "time")
			{
                $minutes = (int)($browsedatafield/60);
                $seconds = $browsedatafield%60;
                $browsedatafield = '';
                if ($minutes > 0)
				    $browsedatafield .= "$minutes min ";
                $browsedatafield .= "$seconds s";
            }
            $browseoutput .= "<td align='center'>$browsedatafield</td>\n";
        }
        $browseoutput .= "\t</tr>\n";
    }
    $browseoutput .= "</table>
    <input type='hidden' name='sid' value='$surveyid' />
    <input type='hidden' name='subaction' value='time' />
    <input id='deleteanswer' name='deleteanswer' value='' type='hidden' />
    </form>\n<br />\n";
	
	// Interview time
	$browseoutput .= '<div class="header ui-widget-header">'.$clang->gT('Interview Time').'</div>';
	
	//interview Time statistics
	$count=false;
	//$survstats=substr($surveytableNq);
	$queryAvg="SELECT AVG(timings.interviewTime) AS avg, COUNT(timings.id) AS count FROM {$surveytableNq}_timings AS timings JOIN {$surveytable} AS surv ON timings.id=surv.id WHERE surv.submitdate IS NOT NULL";
	$queryAll="SELECT timings.interviewTime FROM {$surveytableNq}_timings AS timings JOIN {$surveytable} AS surv ON timings.id=surv.id WHERE surv.submitdate IS NOT NULL ORDER BY timings.interviewTime";
	$browseoutput .= '<table class="statisticssummary">';
	if($result=db_execute_assoc($queryAvg)){

		$row=$result->FetchRow();
		$min = (int)($row['avg']/60);
		$sec = $row['avg']%60;
		$count=$row['count'];
		$browseoutput .= '<tr><Th>'.$clang->gT('Average interview time: ')."</th><td>{$min} min. {$sec} sec.</td></tr>";
	}
	
	if($count && $result=db_execute_assoc($queryAll)){
		
		$middleval = floor(($count-1)/2);
		$i=0;
		if($count%2){
			while($row=$result->FetchRow()){
				
				if($i==$middleval){
					$median=$row['interviewTime'];
					break;
				}
				$i++;		
			}
		}else{
			while($row=$result->FetchRow()){
				if($i==$middleval){
					$nextrow=$result->FetchRow();
					$median=($row['interviewTime']+$nextrow['interviewTime'])/2;
					break;
				}
				$i++;		
			}
		}
		$min = (int)($median/60);
		$sec = $median%60;
		$browseoutput.='<tr><Th>'.$clang->gT('Median: ')."</th><td>{$min} min. {$sec} sec.</td></tr>";
	}
	$browseoutput .= '</table>';
}
elseif ($subaction=="time")
{
    $browseoutput .= $surveyoptions;
    $browseoutput .= "<div class='header'>".$clang->gT("Timings")."</div>";
	$browseoutput .= "Timing saving is disabled or the timing table does not exist. Try to reactivate survey.\n";
}
else
{
    $browseoutput .= $surveyoptions;
    $num_total_answers=0;
    $num_completed_answers=0;
    $gnquery = "SELECT count(id) FROM $surveytable";
    $gnquery2 = "SELECT count(id) FROM $surveytable WHERE submitdate IS NOT NULL";
    $gnresult = db_execute_num($gnquery);
    $gnresult2 = db_execute_num($gnquery2);

    while ($gnrow=$gnresult->FetchRow()) {$num_total_answers=$gnrow[0];}
    while ($gnrow2=$gnresult2->FetchRow()) {$num_completed_answers=$gnrow2[0];}
    $browseoutput .= "<div class='header'>".$clang->gT("Response summary")."</div>"
            ."<p><table class='statisticssummary'>\n"
            ."<tfoot><tr><th>".$clang->gT("Total responses:")."</th><td>".$num_total_answers."</td></tr></tfoot>"
            ."\t<tbody>"
            ."<tr><th>".$clang->gT("Full responses:")."</th><td>".$num_completed_answers."</td></tr>"
            ."<tr><th>".$clang->gT("Incomplete responses:")."</th><td>".($num_total_answers-$num_completed_answers)."</td></tr></tbody>"
            ."</table>";

}
