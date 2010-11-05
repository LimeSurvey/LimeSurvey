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

$postsid=returnglobal('sid');

$date = date('YmdHis'); //'Hi' adds 24hours+minutes to name to allow multiple deactiviations in a day
$deactivateoutput='';
if (!isset($_POST['ok']) || !$_POST['ok'])
{
    $deactivateoutput .= "<br />\n<div class='messagebox ui-corner-all'>\n";
    $deactivateoutput .= "<div class='header ui-widget-header'>".$clang->gT("Deactivate Survey")." ($surveyid)</div>\n";
    $deactivateoutput .= "\t<div class='warningheader'>\n";
    $deactivateoutput .= $clang->gT("Warning")."<br />".$clang->gT("READ THIS CAREFULLY BEFORE PROCEEDING");
    $deactivateoutput .= "</div>\n";
    $deactivateoutput .= "\t".$clang->gT("In an active survey, a table is created to store all the data-entry records.")."\n";
    $deactivateoutput .= "\t<p>".$clang->gT("When you deactivate a survey all the data entered in the original table will be moved elsewhere, and when you activate the survey again, the table will be empty. You will not be able to access this data using LimeSurvey any more.")."</p>\n";
    $deactivateoutput .= "\t<p>".$clang->gT("Deactivated survey data can only be accessed by system administrators using a Database data access tool like phpmyadmin. If your survey uses tokens, this table will also be renamed and will only be accessible by system administrators.")."</p>\n";
    $deactivateoutput .= "\t<p>".$clang->gT("Your responses table will be renamed to:")." {$dbprefix}old_{$_GET['sid']}_{$date}</p>\n";
    $deactivateoutput .= "\t<p>".$clang->gT("Also you should export your responses before deactivating.")."</p>\n";
    $deactivateoutput .= "\t<input type='submit' value='".$clang->gT("Deactivate Survey")."' onclick=\"".get2post("$scriptname?action=deactivate&amp;ok=Y&amp;sid={$_GET['sid']}")."\" />\n";
    $deactivateoutput .= "</div><br />\n";
}

else
{
    //See if there is a tokens table for this survey
    if (tableExists("tokens_{$postsid}"))
    {
        $toldtable="tokens_{$postsid}";
        $tnewtable="old_tokens_{$postsid}_{$date}";
        $tdeactivatequery = db_rename_table(db_table_name_nq($toldtable) ,db_table_name_nq($tnewtable));
        $tdeactivateresult = $connect->Execute($tdeactivatequery) or die ("Couldn't deactivate tokens table because:<br />".htmlspecialchars($connect->ErrorMsg())."<br /><br />Survey was not deactivated either.<br /><br /><a href='$scriptname?sid={$postsid}'>".$clang->gT("Main Admin Screen")."</a>");

        if ($databasetype=='postgres')
        {
            // If you deactivate a postgres table you have to rename the according sequence too and alter the id field to point to the changed sequence
            $deactivatequery = db_rename_table(db_table_name_nq($toldtable).'_tid_seq',db_table_name_nq($tnewtable).'_tid_seq');
            $deactivateresult = $connect->Execute($deactivatequery) or die ("Could not rename the old sequence for this token table. The database reported the following error:<br />".htmlspecialchars($connect->ErrorMsg())."<br /><br /><a href='$scriptname?sid={$postsid}'>".$clang->gT("Main Admin Screen")."</a>");
            $setsequence="ALTER TABLE ".db_table_name_nq($tnewtable)." ALTER COLUMN tid SET DEFAULT nextval('".db_table_name_nq($tnewtable)."_tid_seq'::regclass);";
            $deactivateresult = $connect->Execute($setsequence) or die ("Could not alter the field 'tid' to point to the new sequence name for this token table. The database reported the following error:<br />".htmlspecialchars($connect->ErrorMsg())."<br /><br />Survey was not deactivated either.<br /><br /><a href='$scriptname?sid={$postsid}'>".$clang->gT("Main Admin Screen")."</a>");
            $setidx="ALTER INDEX ".db_table_name_nq($toldtable)."_idx RENAME TO ".db_table_name_nq($tnewtable)."_idx;";
            $deactivateresult = $connect->Execute($setidx) or die ("Could not alter the index for this token table. The database reported the following error:<br />".htmlspecialchars($connect->ErrorMsg())."<br /><br />Survey was not deactivated either.<br /><br /><a href='$scriptname?sid={$_GET['sid']}'>".$clang->gT("Main Admin Screen")."</a>");

        }
    }

    // IF there are any records in the saved_control table related to this survey, they have to be deleted
    $query = "DELETE FROM {$dbprefix}saved_control WHERE sid={$postsid}";
    $result = $connect->Execute($query);

    $oldtable="{$dbprefix}survey_{$postsid}";
    $newtable="{$dbprefix}old_survey_{$postsid}_{$date}";

    //Update the auto_increment value from the table before renaming
    $new_autonumber_start=0;
    $query = "SELECT id FROM $oldtable ORDER BY id desc";
    $result = db_select_limit_assoc($query, 1,-1, false, false);
    if ($result)
    {
        while ($row=$result->FetchRow())
        {
            if (strlen($row['id']) > 12) //Handle very large autonumbers (like those using IP prefixes)
            {
                $part1=substr($row['id'], 0, 12);
                $part2len=strlen($row['id'])-12;
                $part2=sprintf("%0{$part2len}d", substr($row['id'], 12, strlen($row['id'])-12)+1);
                $new_autonumber_start="{$part1}{$part2}";
            }
            else
            {
                $new_autonumber_start=$row['id']+1;
            }
        }
    }
    $query = "UPDATE {$dbprefix}surveys SET autonumber_start=$new_autonumber_start WHERE sid=$surveyid";
    @$result = $connect->Execute($query); //Note this won't die if it fails - that's deliberate.

    $deactivatequery = db_rename_table($oldtable,$newtable);
    $deactivateresult = $connect->Execute($deactivatequery) or die ("Couldn't make backup of the survey table. Please try again. The database reported the following error:<br />".htmlspecialchars($connect->ErrorMsg())."<br /><br />Survey was not deactivated either.<br /><br /><a href='$scriptname?sid={$postsid}'>".$clang->gT("Main Admin Screen")."</a>");

    if ($databasetype=='postgres')
    {
        // If you deactivate a postgres table you have to rename the according sequence too and alter the id field to point to the changed sequence
        $deactivatequery = db_rename_table($oldtable.'_id_seq',$newtable.'_id_seq');
        $deactivateresult = $connect->Execute($deactivatequery) or die ("Couldn't make backup of the survey table. Please try again. The database reported the following error:<br />".htmlspecialchars($connect->ErrorMsg())."<br /><br />Survey was not deactivated either.<br /><br /><a href='$scriptname?sid={$postsid}'>".$clang->gT("Main Admin Screen")."</a>");
        $setsequence="ALTER TABLE $newtable ALTER COLUMN id SET DEFAULT nextval('{$newtable}_id_seq'::regclass);";
        $deactivateresult = $connect->Execute($setsequence) or die ("Couldn't make backup of the survey table. Please try again. The database reported the following error:<br />".htmlspecialchars($connect->ErrorMsg())."<br /><br />Survey was not deactivated either.<br /><br /><a href='$scriptname?sid={$postsid}'>".$clang->gT("Main Admin Screen")."</a>");
    }

    //            $dict = NewDataDictionary($connect);
    //            $dropindexquery=$dict->DropIndexSQL(db_table_name_nq($oldtable).'_idx');
    //            $connect->Execute($dropindexquery[0]);

    $deactivatequery = "UPDATE {$dbprefix}surveys SET active='N' WHERE sid=$surveyid";
    $deactivateresult = $connect->Execute($deactivatequery) or die ("Couldn't deactivate because:<br />".htmlspecialchars($connect->ErrorMsg())."<br /><br /><a href='$scriptname?sid={$postsid}'>Admin</a>");
    $deactivateoutput .= "<br />\n<div class='messagebox ui-corner-all'>\n";
    $deactivateoutput .= "<div class='header ui-widget-header'>".$clang->gT("Deactivate Survey")." ($surveyid)</div>\n";
    $deactivateoutput .= "\t<div class='successheader'>".$clang->gT("Survey Has Been Deactivated")."\n";
    $deactivateoutput .= "</div>\n";
    $deactivateoutput .= "\t<p>\n";
    $deactivateoutput .= "\t".$clang->gT("The responses table has been renamed to: ")." $newtable.\n";
    $deactivateoutput .= "\t".$clang->gT("The responses to this survey are no longer available using LimeSurvey.")."\n";
    $deactivateoutput .= "\t<p>".$clang->gT("You should note the name of this table in case you need to access this information later.")."</p>\n";
    if (isset($toldtable) && $toldtable)
    {
        $deactivateoutput .= "\t".$clang->gT("The tokens table associated with this survey has been renamed to: ")." $tnewtable.\n";
    }
    $deactivateoutput .= "\t<p>".$clang->gT("Note: If you deactivated this survey in error, it is possible to restore this data easily if you do not make any changes to the survey structure. See the LimeSurvey documentation for further details")."</p>";
    $deactivateoutput .= "</div><br/>&nbsp;\n";
    
    $pquery = "SELECT savetimings FROM {$dbprefix}surveys WHERE sid={$postsid}";
    $presult=db_execute_assoc($pquery);
    $prow=$presult->FetchRow(); //fetch savetimings value
    if ($prow['savetimings'] == "Y")
    {
		$oldtable="{$dbprefix}survey_{$postsid}_timings";
		$newtable="{$dbprefix}old_survey_{$postsid}_timings_{$date}";

		$deactivatequery = db_rename_table($oldtable,$newtable);
		$deactivateresult2 = $connect->Execute($deactivatequery) or die ("Couldn't make backup of the survey timings table. Please try again. The database reported the following error:<br />".htmlspecialchars($connect->ErrorMsg())."<br /><br />Survey was deactivated.<br /><br /><a href='$scriptname?sid={$postsid}'>".$clang->gT("Main Admin Screen")."</a>");
		$deactivateresult=($deactivateresult && $deactivateresult2);
}
}

?>
