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


include_once("login_check.php");
if($_SESSION['USER_RIGHT_CONFIGURATOR'] == 1)
{
    // THIS FILE CHECKS THE CONSISTENCY OF THE DATABASE, IT "LOOKS FOR
    // STRAY QUESTIONS, ANSWERS, CONDITIONS OR GROUPS AND DELETES THEM
    $ok=returnglobal('ok');

    $integritycheck='';
    if (!isset($ok) || ($ok != "Y" && $ok != "R")) // do the check, but don't delete anything
    {
        $integritycheck .= "<div class='messagebox ui-corner-all'>"
        . "<div class='header ui-widget-header'>".$clang->gT("Data Consistency Check")."<br />\n"
        . "<span style='font-size:7pt;'>".$clang->gT("If errors are showing up you might have to execute this script repeatedly.")."</span></div>\n"
        . "<ul>\n";

        /****** Plainly delete survey permissions if the survey or user does not exist ***/

        $connect->query("delete FROM {$dbprefix}survey_permissions where sid not in (select sid from {$dbprefix}surveys)");
        $connect->query("delete FROM {$dbprefix}survey_permissions where uid not in (select uid from {$dbprefix}users)");

        // Silently fix subquestions
        fixSubquestions();

        /***** Check for activate survey tables with missing survey entry **/

        $sQuery = db_select_tables_like("{$dbprefix}survey\_%");
        $aResult = db_execute_num($sQuery) or safe_die("Couldn't get list of conditions from database<br />$query<br />".$connect->ErrorMsg());
        while ($aRow=$aResult->FetchRow())
        {
           $tablename=substr($aRow[0],strlen($dbprefix));
           if ($tablename=='survey_permissions') continue;
           $iSurveyID=substr($tablename,strpos($tablename,'_')+1);
           $qquery="SELECT sid FROM {$dbprefix}surveys WHERE sid='{$iSurveyID}'";
           $qresult=$connect->Execute($qquery) or safe_die ("Couldn't check questions table for qids<br />$qquery<br />".$connect->ErrorMsg());
           $qcount=$qresult->RecordCount();
           if ($qcount==0)
           {
                $date = date('YmdHis').rand(1,1000);
                $sOldTable = "survey_{$iSurveyID}";
                $sNewTable = "old_survey_{$iSurveyID}_$date";

                $deactivatequery = db_rename_table(db_table_name_nq($sOldTable),db_table_name_nq($sNewTable));
                $deactivateresult = $connect->Execute($deactivatequery) or die ("Couldn't make backup of the survey table. Please try again. The database reported the following error:<br />".htmlspecialchars($connect->ErrorMsg())."<br />");

                if ($databasetype=='postgres')
                {
                    // If you deactivate a postgres table you have to rename the according sequence too and alter the id field to point to the changed sequence
                    $deactivatequery = db_rename_table($sOldTable.'_id_seq',$sNewTable.'_id_seq');
                    $deactivateresult = $connect->Execute($deactivatequery) or die ("Couldn't make backup of the survey table. Please try again. The database reported the following error:<br />".htmlspecialchars($connect->ErrorMsg())."<br /><br />Survey was not deactivated either.<br /><br /><a href='$scriptname?sid={$postsid}'>".$clang->gT("Main Admin Screen")."</a>");
                    $setsequence="ALTER TABLE $sNewTable ALTER COLUMN id SET DEFAULT nextval('{$sNewTable}_id_seq'::regclass);";
                    $deactivateresult = $connect->Execute($setsequence) or die ("Couldn't make backup of the survey table. Please try again. The database reported the following error:<br />".htmlspecialchars($connect->ErrorMsg())."<br /><br />Survey was not deactivated either.<br /><br /><a href='$scriptname?sid={$postsid}'>".$clang->gT("Main Admin Screen")."</a>");
                }

           }
        }

        /***** Check for activate token tables with missing survey entry **/

        $sQuery = db_select_tables_like("{$dbprefix}tokens\_%");
        $aResult = db_execute_num($sQuery) or safe_die("Couldn't get list of token tables from database<br />$query<br />".$connect->ErrorMsg());
        while ($aRow=$aResult->FetchRow())
        {
           $tablename=substr($aRow[0],strlen($dbprefix));
           $iSurveyID=substr($tablename,strpos($tablename,'_')+1);
           $qquery="SELECT sid FROM {$dbprefix}surveys WHERE sid='{$iSurveyID}'";
           $qresult=$connect->Execute($qquery) or safe_die ("Couldn't check survey table for sid<br />$qquery<br />".$connect->ErrorMsg());
           $qcount=$qresult->RecordCount();
           if ($qcount==0)
           {
                $date = date('YmdHis').rand(1,1000);
                $sOldTable = "tokens_{$iSurveyID}";
                $sNewTable = "old_tokens_{$iSurveyID}_$date";
                $deactivatequery = db_rename_table( db_table_name_nq($sOldTable), db_table_name_nq($sNewTable));

                if ($databasetype=='postgres')
                {
                    // If you deactivate a postgres table you have to rename the according sequence too and alter the id field to point to the changed sequence
                    $sOldTableJur = db_table_name_nq($sOldTable);
                    $deactivatequery = db_rename_table(db_table_name_nq($sOldTable),db_table_name_nq($sNewTable).'_tid_seq');
                    $deactivateresult = $connect->Execute($deactivatequery) or die ("oldtable : ".$sOldTable. " / oldtableJur : ". $sOldTableJur . " / ".htmlspecialchars($deactivatequery)." / Could not rename the old sequence for this token table. The database reported the following error:<br />".htmlspecialchars($connect->ErrorMsg())."<br /><br /><a href='$scriptname?sid={$_GET['sid']}'>".$clang->gT("Main Admin Screen")."</a>");
                    $setsequence="ALTER TABLE ".db_table_name_nq($sNewTable)."_tid_seq ALTER COLUMN tid SET DEFAULT nextval('".db_table_name_nq($sNewTable)."_tid_seq'::regclass);";
                    $deactivateresult = $connect->Execute($setsequence) or die (htmlspecialchars($setsequence)." Could not alter the field tid to point to the new sequence name for this token table. The database reported the following error:<br />".htmlspecialchars($connect->ErrorMsg())."<br /><br />Survey was not deactivated either.<br /><br /><a href='$scriptname?sid={$_GET['sid']}'>".$clang->gT("Main Admin Screen")."</a>");
                    $setidx="ALTER INDEX ".db_table_name_nq($sOldTable)."_idx RENAME TO ".db_table_name_nq($sNewTable)."_idx;";
                    $deactivateresult = $connect->Execute($setidx) or die (htmlspecialchars($setidx)." Could not alter the index for this token table. The database reported the following error:<br />".htmlspecialchars($connect->ErrorMsg())."<br /><br />Survey was not deactivated either.<br /><br /><a href='$scriptname?sid={$_GET['sid']}'>".$clang->gT("Main Admin Screen")."</a>");
                } else {
                    $deactivateresult = $connect->Execute($deactivatequery) or die ("Couldn't deactivate because:<br />\n".htmlspecialchars($connect->ErrorMsg())." - Query: ".htmlspecialchars($deactivatequery)." <br /><br />\n<a href='$scriptname?sid=$surveyid'>Admin</a>\n");
                }

           }
        }


        /**********************************************************************/
        /*     CHECK CONDITIONS                                               */
        /**********************************************************************/
        $query = "SELECT * FROM {$dbprefix}conditions ORDER BY cid";
        $result = db_execute_assoc($query) or safe_die("Couldn't get list of conditions from database<br />$query<br />".$connect->ErrorMsg());
        while ($row=$result->FetchRow())
        {
            $qquery="SELECT qid FROM {$dbprefix}questions WHERE qid='{$row['qid']}'";
            $qresult=$connect->Execute($qquery) or safe_die ("Couldn't check questions table for qids<br />$qquery<br />".$connect->ErrorMsg());
            $qcount=$qresult->RecordCount();
            if (!$qcount) {$cdelete[]=array("cid"=>$row['cid'], "reason"=>"No matching qid");}

            if ($row['cqid'] != 0)
            { // skip case with cqid=0 for codnitions on {TOKEN:EMAIL} for instance
                $qquery = "SELECT qid FROM {$dbprefix}questions WHERE qid='{$row['cqid']}'";
                $qresult=$connect->Execute($qquery) or safe_die ("Couldn't check questions table for qids<br />$qquery<br />".$connect->ErrorMsg());
                $qcount=$qresult->RecordCount();
                if (!$qcount) {$cdelete[]=array("cid"=>$row['cid'], "reason"=>$clang->gT("No matching Cqid"));}
            }
            if ($row['cfieldname']) //Only do this if there actually is a "cfieldname"
            {
                if (preg_match("/^\+{0,1}[0-9]+X[0-9]+X*$/",$row['cfieldname']))
                { // only if cfieldname isn't Tag such as {TOKEN:EMAIL} or any other token
                    list ($surveyid, $gid, $rest) = explode("X", $row['cfieldname']);
                    $qquery = "SELECT gid FROM {$dbprefix}groups WHERE gid=$gid";
                    $qresult = $connect->Execute($qquery) or safe_die ("Couldn't check conditional group matches<br />$qquery<br />".$connect->ErrorMsg());
                    $qcount=$qresult->RecordCount();
                    if ($qcount < 1) {$cdelete[]=array("cid"=>$row['cid'], "reason"=>$clang->gT("No matching CFIELDNAME Group!")." ($gid) ({$row['cfieldname']})");}
                }
            }
            elseif (!$row['cfieldname'])
            {
                $cdelete[]=array("cid"=>$row['cid'], "reason"=>$clang->gT("No \"CFIELDNAME\" field set!")." ({$row['cfieldname']})");
            }
        }
        if (isset($cdelete) && $cdelete)
        {
            $integritycheck .= "<li>".$clang->gT("The following conditions should be deleted").":</li><br /><span style='font-size:7pt;'>\n";
            foreach ($cdelete as $cd) {
                $integritycheck .= "CID: {$cd['cid']} ".$clang->gT("because")." {$cd['reason']}<br />\n";
            }
            $integritycheck .= "<br />\n";
        }
        else
        {
            $integritycheck .= "<li>".$clang->gT("All conditions meet consistency standards")."</li>\n";
        }

        /**********************************************************************/
        /*     CHECK QUESTION ATTRIBUTES                                      */
        /**********************************************************************/
        $query = "SELECT * FROM {$dbprefix}question_attributes ORDER BY qid";
        $result = db_execute_assoc($query) or safe_die($connect->ErrorMsg());
        $countQuestionAttributes=0;
        while($row = $result->FetchRow())
        {
            $aquery = "SELECT * FROM {$dbprefix}questions WHERE qid = {$row['qid']}";
            $aresult = $connect->Execute($aquery) or safe_die($connect->ErrorMsg());
            $qacount = $aresult->RecordCount();
            if (!$qacount) {
                $countQuestionAttributes++;
            }
        } // while
        if (isset($qadelete) && $qadelete) {
            $integritycheck .= "<li>".sprintf($clang->gT("There are %s orphaned question attributes."),$countQuestionAttributes)."</li>";
        }
        else
        {
            $integritycheck .= "<li>".$clang->gT("All question attributes meet consistency standards")."</li>\n";
        }

        /**********************************************************************/
        /*     Check default values                                           */
        /**********************************************************************/
        $sQuery = "SELECT * FROM {$dbprefix}defaultvalues where qid not in (select qid from {$dbprefix}questions)";
        $result = db_execute_assoc($sQuery) or safe_die($connect->ErrorMsg());
        $iCountDefaultValues=$result->RecordCount();

        if ($iCountDefaultValues>0) {
            $integritycheck .= "<li>".sprintf($clang->gT("There are %s orphaned default value entries which can be deleted."),$iCountDefaultValues)."</li>";
        }
        else
        {
            $integritycheck .= "<li>".$clang->gT("All default values meet consistency standards")."</li>\n";
        }

        /**********************************************************************/
        /*     Check quotas                                                   */
        /**********************************************************************/
        $sQuery = "SELECT * FROM {$dbprefix}quota where sid not in (select sid from {$dbprefix}surveys)";
        $result = db_execute_assoc($sQuery) or safe_die($connect->ErrorMsg());
        $iCountQuotas=$result->RecordCount();

        if ($iCountQuotas>0) {
            $integritycheck .= "<li>".sprintf($clang->gT("There are %s orphaned quota entries which can be deleted."),$iCountQuotas)."</li>";
        }
        else
        {
            $integritycheck .= "<li>".$clang->gT("All quotas meet consistency standards")."</li>\n";
        }


        /**********************************************************************/
        /*     Check quota languagesettings                                   */
        /**********************************************************************/
        $sQuery = "SELECT * FROM {$dbprefix}quota_languagesettings where quotals_quota_id not in (select id from {$dbprefix}quota)";
        $result = db_execute_assoc($sQuery) or safe_die($connect->ErrorMsg());
        $iCountQuotaLanguageSettings=$result->RecordCount();

        if ($iCountQuotaLanguageSettings>0) {
            $integritycheck .= "<li>".sprintf($clang->gT("There are %s orphaned quota language settings which can be deleted."),$iCountQuotaLanguageSettings)."</li>";
        }
        else
        {
            $integritycheck .= "<li>".$clang->gT("All quota language settings meet consistency standards")."</li>\n";
        }

        /**********************************************************************/
        /*     Check quota members                                   */
        /**********************************************************************/
        $sQuery = "SELECT * FROM {$dbprefix}quota_members where quota_id not in (select id from {$dbprefix}quota) or qid not in (select qid from {$dbprefix}questions) or sid not in (select sid from {$dbprefix}surveys)";
        $result = db_execute_assoc($sQuery) or safe_die($connect->ErrorMsg());
        $iCountQuotaMembers=$result->RecordCount();


        if ($iCountQuotaMembers>0) {
            $integritycheck .= "<li>".sprintf($clang->gT("There are %s orphaned quota members which can be deleted."),$iCountQuotaMembers)."</li>";
        }
        else
        {
            $integritycheck .= "<li>".$clang->gT("All quota members meet consistency standards")."</li>\n";
        }



        /**********************************************************************/
        /*     CHECK ASSESSMENTS                                              */
        /**********************************************************************/
        $query = "SELECT * FROM {$dbprefix}assessments WHERE scope='T' ORDER BY sid";
        $result = db_execute_assoc($query) or safe_die ("Couldn't get list of assessments<br />$query<br />".$connect->ErrorMsg());
        while($row = $result->FetchRow())
        {
            $aquery = "SELECT * FROM {$dbprefix}surveys WHERE sid = {$row['sid']}";
            $aresult = db_execute_assoc($aquery) or safe_die("Oh dear - died in assessments surveys:".$aquery ."<br />".$connect->ErrorMsg());
            $acount = $aresult->RecordCount();
            if (!$acount) {
                $assdelete[]=array("id"=>$row['id'], "assessment"=>$row['name'], "reason"=>$clang->gT("No matching survey"));
            }
        } // while

        $query = "SELECT * FROM {$dbprefix}assessments WHERE scope='G' ORDER BY gid";
        $result = db_execute_assoc($query) or safe_die ("Couldn't get list of assessments<br />$query<br />".$connect->ErrorMsg());
        while($row = $result->FetchRow())
        {
            $aquery = "SELECT * FROM {$dbprefix}groups WHERE gid = {$row['gid']}";
            $aresult = $connect->Execute($aquery) or safe_die("Oh dear - died:".$aquery ."<br />".$connect->ErrorMsg());
            $acount = $aresult->RecordCount();
            if (!$acount) {
                $asgdelete[]=array("id"=>$row['id'], "assessment"=>$row['name'], "reason"=>$clang->gT("No matching group"));
            }
        }

        if (isset($assdelete) && $assdelete)
        {
            $integritycheck .= "<li>".$clang->gT("The following assessments should be deleted").":</li><span style='font-size:7pt;'>\n";
            foreach ($assdelete as $ass) {$integritycheck .= "ID `{$ass['id']}` ASSESSMENT `{$ass['assessment']}` ".$clang->gT("because")." `{$ass['reason']}`<br />\n";}
            $integritycheck .= "</span><br />\n";
        }
        else
        {
            $integritycheck .= "<li>".$clang->gT("All Survey (Total) assessments meet consistency standards")."</li>\n";
        }
        if (isset($asgdelete) && $asgdelete)
        {
            $integritycheck .= "<strong>".$clang->gT("The following assessments should be deleted").":</strong><br /><span style='font-size:7pt;'>\n";
            foreach ($asgdelete as $asg) {$integritycheck .= "ID `{$asg['id']}` ASSESSMENT `{$asg['assessment']}` ".$clang->gT("because")." `{$asg['reason']}`<br />\n";}
            $integritycheck .= "</span><br />\n";
        }
        else
        {
            $integritycheck .= "<li>".$clang->gT("All Group assessments meet consistency standards")."</li>\n";
        }

        // Check answers
        $query = "SELECT * FROM {$dbprefix}answers ORDER BY qid";
        $result = db_execute_assoc($query) or safe_die ("Couldn't get list of answers from database<br />$query<br />".$connect->ErrorMsg());
        while ($row=$result->FetchRow())
        {
            //$integritycheck .= "Checking answer {$row['code']} to qid {$row['qid']}<br />\n";
            $qquery="SELECT qid FROM {$dbprefix}questions WHERE qid='{$row['qid']}'";
            $qresult=$connect->Execute($qquery) or safe_die ("Couldn't check questions table for qids from answers<br />$qquery<br />".$connect->ErrorMsg());
            $qcount=$qresult->RecordCount();
            if (!$qcount) {
                $adelete[]=array("qid"=>$row['qid'], "code"=>$row['code'], "reason"=>$clang->gT("No matching question"));
            }
            //$integritycheck .= "<br />\n";
        }
        if (isset($adelete) && $adelete)
        {
            $integritycheck .= "<strong>".$clang->gT("The following answers should be deleted").":</strong><br /><span style='font-size:7pt;'>\n";
            foreach ($adelete as $ad) {$integritycheck .= "QID `{$ad['qid']}` CODE `{$ad['code']}` ".$clang->gT("because")." `{$ad['reason']}`<br />\n";}
            $integritycheck .= "</span><br />\n";
        }
        else
        {
            $integritycheck .= "<li>".$clang->gT("All answers meet consistency standards")."</li>\n";
        }

        /**********************************************************************/
        /*     CHECK SURVEYS                                                  */
        /**********************************************************************/
        $query = "SELECT * FROM {$dbprefix}surveys ORDER BY sid";
        $result = db_execute_assoc($query) or safe_die ("Couldn't get list of answers from database<br />$query<br />".$connect->ErrorMsg());
        while ($row=$result->FetchRow())
        {
            $qquery="SELECT surveyls_survey_id FROM {$dbprefix}surveys_languagesettings WHERE surveyls_survey_id='{$row['sid']}'";
            $qresult=$connect->Execute($qquery) or safe_die ("Couldn't check languagesettings table for sids from surveys<br />$qquery<br />".$connect->ErrorMsg());
            $qcount=$qresult->RecordCount();
            if (!$qcount) {
                $sdelete[]=array("sid"=>$row['sid'], "reason"=>$clang->gT("Language specific settings missing"));
            }
        }
        if (isset($sdelete) && $sdelete)
        {
            $integritycheck .= "<strong>".$clang->gT("The following surveys should be deleted").":</strong><br /><span style='font-size:7pt;'>\n";
            foreach ($sdelete as $sd) {$integritycheck .= "SID `{$sd['sid']}` ".$clang->gT("because")." `{$sd['reason']}`<br />\n";}
            $integritycheck .= "</span><br />\n";
        }
        else
        {
            $integritycheck .= "<li>".$clang->gT("All survey settings meet consistency standards")."</li>\n";
        }

        /**********************************************************************/
        /*     CHECK SURVEY LANGUAGE SETTINGS                                 */
        /**********************************************************************/
        $query = "SELECT surveyls_survey_id FROM {$dbprefix}surveys_languagesettings where surveyls_survey_id not in (select sid from {$dbprefix}surveys) group by surveyls_survey_id order by surveyls_survey_id";
        $result = db_execute_assoc($query) or safe_die ("Couldn't get list of answers from database<br />$query<br />".$connect->ErrorMsg());
        while ($row=$result->FetchRow())
        {
                $sldelete[]=$row['surveyls_survey_id'];
        }
        if (isset($sldelete) && $sldelete)
        {
            $integritycheck .= "<strong>".$clang->gT("The following survey language settings should be deleted").":</strong><br /><span style='font-size:7pt;'>\n";
            foreach ($sldelete as $sld) {
                $integritycheck .= sprintf($clang->gT("SLID `%s` because the related survey is missing."),$sld)."<br />\n";
            }
            $integritycheck .= "</span><br />\n";
        }
        else
        {
            $integritycheck .= "<li>".$clang->gT("All survey language settings meet consistency standards")."</li>\n";
        }


        /**********************************************************************/
        /*     CHECK QUESTIONS                                                */
        /**********************************************************************/
        $query = "SELECT * FROM {$dbprefix}questions ORDER BY sid, gid, qid";
        $result = db_execute_assoc($query) or safe_die ("Couldn't get list of questions from database<br />$query<br />".$connect->ErrorMsg());
        while ($row=$result->FetchRow())
        {
            //Make sure group exists
            $qquery="SELECT * FROM {$dbprefix}groups WHERE gid={$row['gid']}";
            $qresult=$connect->Execute($qquery) or safe_die ("Couldn't check groups table for gids from questions<br />$qquery<br />".$connect->ErrorMsg());
            $qcount=$qresult->RecordCount();
            if (!$qcount) {$qdelete[]=array("qid"=>$row['qid'], "reason"=>$clang->gT("No matching group")." ({$row['gid']})");}
            //Make sure survey exists
            $qquery="SELECT * FROM {$dbprefix}surveys WHERE sid={$row['sid']}";
            $qresult=$connect->Execute($qquery) or safe_die ("Couldn't check surveys table for sids from questions<br />$qquery<br />".$connect->ErrorMsg());
            $qcount=$qresult->RecordCount();
            if (!$qcount) {
                if (!isset($qdelete) || !in_array($row['qid'], $qdelete)) {$qdelete[]=array("qid"=>$row['qid'], "reason"=>$clang->gT("No matching survey")." ({$row['sid']})");}
            }
        }
        if (isset($qdelete) && $qdelete)
        {
            $integritycheck .= "<strong>".$clang->gT("The following questions should be deleted").":</strong><br /><span style='font-size:7pt;'>\n";
            foreach ($qdelete as $qd) {$integritycheck .= "QID `{$qd['qid']}` ".$clang->gT("because")." `{$qd['reason']}`<br />\n";}
            $integritycheck .= "</span><br />\n";
        }
        else
        {
            $integritycheck .= "<li>".$clang->gT("All questions meet consistency standards")."</li>\n";
        }
        /**********************************************************************/
        /*     CHECK GROUPS                                                   */
        /**********************************************************************/
        $query = "SELECT gid FROM {$dbprefix}groups where sid not in (select sid from {$dbprefix}surveys) group by gid order by gid";
        $result=db_execute_assoc($query) or safe_die ("Couldn't get list of groups for checking<br />$query<br />".$connect->ErrorMsg());
        while ($row=$result->FetchRow())
        {
           $gdelete[]=$row['gid'];
        }
        if (isset($gdelete) && $gdelete)
        {
            $integritycheck .= "<li>".$clang->gT("The following groups should be deleted").":</li><span style='font-size:7pt;'>\n";
            foreach ($gdelete as $gd) {$integritycheck .= sprintf($clang->gT("GID `%s` because there is no matching survey."),$gd)."<br />\n";}
            $integritycheck .= "</span><br />\n";
        }
        else
        {
            $integritycheck .= "<li>".$clang->gT("All groups meet consistency standards")."</li>\n";
        }
        /**********************************************************************/
        /*     CHECK OLD SURVEY TABLES                                        */
        /**********************************************************************/
        //1: Get list of "old_survey" tables and extract the survey id
        //2: Check if that survey id still exists
        //3: If it doesn't offer it for deletion
        $tables=$connect->MetaTables(false, false, "{$dbprefix}old_survey%");
        $oldsids=array();
        $sids=array();
        foreach($tables as $table)
        {
            list($oldtxt, $surveytxt, $sid, $date)=explode("_", substr($table,strlen($dbprefix)));
            $oldsids[]=$sid;
            $fulloldsids[$sid][]=$table;
        }
        $oldsids=array_unique($oldsids);
        $query = "SELECT sid FROM {$dbprefix}surveys ORDER BY sid";
        $result=$connect->Execute($query) or safe_die("Couldn't get unique survey ids<br />$query<br />");
        while ($row=$result->FetchRow())
        {
            $sids[]=$row['sid'];
        }
        foreach($oldsids as $oldsid)
        {
            if(!in_array($oldsid, $sids))
            {
                foreach($fulloldsids[$oldsid] as $tablename)
                {
                    $oldsdelete[]=$tablename;
                }
            } else {
                foreach($fulloldsids[$oldsid] as $tablename)
                {
                    $tableParts = explode("_", substr($tablename,strlen($dbprefix)));
                    if (count($tableParts) == 4) {
                        $oldtxt     = $tableParts[0];
                        $surveytxt  = $tableParts[1];
                        $sid        = $tableParts[2];
                        $datetime   = $tableParts[3];
                        $type = $clang->gT('responses');
                    } elseif (count($tableParts) == 5) {
                        //This is a timings table (
                        $oldtxt     = $tableParts[0];
                        $surveytxt  = $tableParts[1];
                        $sid        = $tableParts[2];
                        $datetime   = $tableParts[4];
                        $type = $clang->gT('timings');
                    }
                    $year=substr($datetime, 0,4);
                    $month=substr($datetime, 4,2);
                    $day=substr($datetime, 6, 2);
                    $hour=substr($datetime, 8, 2);
                    $minute=substr($datetime, 10, 2);
                    $date=date("D, d M Y  h:i a", mktime($hour, $minute, 0, $month, $day, $year));
                    $jq="SELECT * FROM ".$tablename;
                    $jqresult=$connect->execute($jq) or safe_die($query." failed");
                    $jqcount=$jqresult->RecordCount();
                    if($jqcount == 0) {
                        $oldsoptionaldelete[]=$tablename."| ".sprintf($clang->gT("Survey ID %d saved at %s (%s)"), $sid, $date, $type);
                        //				     $oldsoptionaldelete[]=$tablename."| SID ".$four. " ". $clang->gT("saved at")." $date";
                    } else {
                        $oldsmultidelete[]=$tablename."| ".sprintf($clang->gT("Survey ID %d saved at %s containing %d record(s) (%s)"), $sid, $date, $jqcount, $type);
                        //				     $oldsmultidelete[]=$tablename."| SID ".$four." ". $clang->gT("saved at")." $date ".sprintf($clang->gT("containing %d record(s)"), $jqcount);
                    }
                }
            }
        }
        if (isset($oldsdelete) && $oldsdelete)
        {
            $integritycheck .= "<li>".$clang->gT("The following old survey tables should be deleted because their parent survey no longer exists").":</li><span style='font-size:7pt;'>\n";
            $integritycheck .= implode(", ", $oldsdelete);
            $integritycheck .= "</span><br />\n";
        }
        else
        {
            $integritycheck .= "<li>".$clang->gT("All old survey tables meet consistency standards")."</li>\n";
        }

        /**********************************************************************/
        /*     CHECK OLD TOKEN  TABLES                                        */
        /**********************************************************************/
        //1: Get list of "old_token" tables and extract the survey id
        //2: Check if that survey id still exists
        //3: If it doesn't offer it for deletion
        $tables=$connect->MetaTables(false, false, "{$dbprefix}old_token%");
        $oldtsids=array();
        $tsids=array();
        $folloldtsids=array();

        foreach($tables as $table)
        {
            list($oldtxt, $surveytxt, $sid, $date)=explode("_", substr($table,strlen($dbprefix)));
            $oldtsids[]=$sid;
            $fulloldtsids[$sid][]=$table;
        }
        $oldtsids=array_unique($oldtsids);
        $query = "SELECT sid FROM {$dbprefix}surveys ORDER BY sid";
        $result=$connect->Execute($query) or safe_die("Couldn't get unique survey ids<br />$query<br />");
        while ($row=$result->FetchRow())
        {
            $tsids[]=$row['sid'];
        }
        foreach($oldtsids as $oldtsid)
        {
            if(!in_array($oldtsid, $tsids))
            {
                foreach($fulloldtsids[$oldtsid] as $tablename)
                {
                    $oldtdelete[]=$tablename;
                }
            } else {
                foreach($fulloldtsids[$oldtsid] as $tablename)
                {
                    list($oldtxt, $tokenstxt, $sid, $datetime)=explode("_", substr($tablename,strlen($dbprefix)));
                    $year=substr($datetime, 0,4);
                    $month=substr($datetime, 4,2);
                    $day=substr($datetime, 6, 2);
                    $hour=substr($datetime, 8, 2);
                    $minute=substr($datetime, 10, 2);
                    $date=date("D, d M Y  h:i a", mktime($hour, $minute, 0, $month, $day, $year));
                    $jq="SELECT * FROM ".$tablename;

                    $jqresult=$connect->execute($jq) or safe_die($query." failed");
                    $jqcount=$jqresult->RecordCount();
                    if($jqcount == 0) {
                        //				     $oldtoptionaldelete[]=$tablename."| SID ".$four. " ". $clang->gT("saved at")." $date";
                        $oldtoptionaldelete[]=$tablename."| ".sprintf($clang->gT("Survey ID %d saved at %s"), $sid, $date);
                    } else {
                        $oldtmultidelete[]=$tablename."| ".sprintf($clang->gT("Survey ID %d saved at %s containing %d record(s)"), $sid, $date, $jqcount);
                        //				     $oldtmultidelete[]=$tablename."| SID ".$four." ". $clang->gT("saved at")." $date ".sprintf($clang->gT("containing %d record(s)"), $jqcount);
                    }
                }
            }
        }
        if (isset($oldtdelete) && $oldtdelete)
        {
            $integritycheck .= "<li>".$clang->gT("The following old token tables should be deleted because their parent survey no longer exists").":</li><span style='font-size:7pt;'>\n";
            $integritycheck .= implode(", ", $oldtdelete);
            $integritycheck .= "</span><br />\n";
        }
        else
        {
            $integritycheck .= "<li>".$clang->gT("All old token tables meet consistency standards")."</li>\n";
        }


        //Finish the list
        $integritycheck .='</ul>' ;

        /**********************************************************************/
        /*     CREATE FORM ELEMENTS CONTAINING PROPOSED ALTERATIONS           */
        /**********************************************************************/
        if (!isset($cdelete) && !isset($adelete) && !isset($qdelete) &&
        !isset($gdelete) && !isset($asgdelete) && !isset($sdelete) &&
        !isset($assdelete) && !isset($qadelete) && !isset($oldsdelete) &&
        !isset($oldtdelete) && !isset($sldelete)&& $iCountDefaultValues==0 &&
        $iCountQuotas==0 && $iCountQuotaLanguageSettings==0 && $iCountQuotaMembers==0) {
            $integritycheck .= "<br />".$clang->gT("No database action required");
        } else {
            $integritycheck .= "<br />".$clang->gT("Should we proceed with the delete?")."<br />\n";
            $integritycheck .= "<form action='{$scriptname}?action=checkintegrity' method='post'>\n";

            if ($iCountQuotaMembers>0)
            {
                    $integritycheck .= "<input type='hidden' name='quotamembers_delete'  value='1' />\n";
            }

            if ($iCountQuotaLanguageSettings>0)
            {
                    $integritycheck .= "<input type='hidden' name='quotalanguagesettings_delete'  value='1' />\n";
            }

            if ($iCountQuotas>0)
            {
                    $integritycheck .= "<input type='hidden' name='quota_delete'  value='1' />\n";
            }

            if ($iCountDefaultValues>0)
            {
                    $integritycheck .= "<input type='hidden' name='defaultvalue_delete'  value='1' />\n";
            }

            if (isset($oldsdelete)) {
                foreach($oldsdelete as $olds) {
                    $integritycheck .= "<input type='hidden' name='oldsdelete[]' value='{$olds}' />\n";
                }
            }
            if (isset($oldtdelete)) {
                foreach($oldtdelete as $oldt) {
                    $integritycheck .= "<input type='hidden' name='oldtdelete[]' value='{$oldt}' />\n";
                }
            }
            if (isset($cdelete)) {
                foreach ($cdelete as $cd) {
                    $integritycheck .= "<input type='hidden' name='cdelete[]' value='{$cd['cid']}' />\n";
                }
            }
            if (isset($adelete)) {
                foreach ($adelete as $ad) {
                    $integritycheck .= "<input type='hidden' name='adelete[]' value='{$ad['qid']}|{$ad['code']}' />\n";
                }
            }
            if (isset($qdelete)) {
                foreach($qdelete as $qd) {
                    $integritycheck .= "<input type='hidden' name='qdelete[]' value='{$qd['qid']}' />\n";
                }
            }
            if (isset($gdelete)) {
                foreach ($gdelete as $gd) {
                    $integritycheck .= "<input type='hidden' name='gdelete[]' value='{$gd}' />\n";
                }
            }
            if (isset($qadelete)) {
                foreach ($qadelete as $qad) {
                    $integritycheck .= "<input type='hidden' name='qadelete[]' value='{$qad['qaid']}'/>\n";
                }
            }
            if (isset($assdelete)) {
                foreach ($assdelete as $ass) {
                    $integritycheck .= "<input type='hidden' name='assdelete[]' value='{$ass['id']}'/>\n";
                }
            }
            if (isset($asgdelete)) {
                foreach ($asgdelete as $asg) {
                    $integritycheck .= "<input type='hidden' name='asgdelete[]' value='{$asg['id']}'/>\n";
                }
            }
            if (isset($sdelete)) {
                foreach ($sdelete as $asg) {
                    $integritycheck .= "<input type='hidden' name='sdelete[]' value='{$asg['sid']}'/>\n";
                }
            }
            if (isset($sldelete)) {
                foreach ($sldelete as $sld) {
                    $integritycheck .= "<input type='hidden' name='sldelete[]' value='{$sld}'/>\n";
                }
            }
            $integritycheck .= "<input type='hidden' name='ok' value='Y'>\n"
            ."<input type='submit' value='".$clang->gT("Yes - Delete Them!")."'>\n"
            ."</form>\n";
        }
        $integritycheck .= "</div><br />\n";

        $integritycheck2 = "<div class='messagebox ui-corner-all'>"
        . "<div class='header ui-widget-header'>".$clang->gT("Data redundancy check")."<br />"
        . "<span style='font-size:7pt;'>".$clang->gT("The redundancy check looks for tables leftover after deactivating a survey. You can delete these if you no longer require them.")."</span>\n"
        . "</div>\n";
        if (!isset($oldsoptionaldelete) && !isset($oldsmultidelete) &&
        !isset($oldtoptionaldelete) && !isset($oldtmultidelete) ) {
            $integritycheck2 .= "<br />".$clang->gT("No database action required");
        } else {

            $integritycheck2 .= "<form action='{$scriptname}?action=checkintegrity' method='post'>\n"
            . "<ul>\n";
            if(isset($oldsoptionaldelete)) {
                $integritycheck2 .= "<li>".$clang->gT("The following old survey tables contain no responses and can be deleted:")."<br /><span style='font-size: 7pt'>\n";
                foreach($oldsoptionaldelete as $ood) {
                    list($tablename, $display)=explode("|", $ood);
                    $integritycheck2 .= "<input type='checkbox' value='$tablename' name='oldsoptionaldelete[]' />$display<br />\n";
                }
                $integritycheck2 .= "</span><br /></li>\n";
            }
            if(isset($oldsmultidelete)) {
                $integritycheck2 .= "<li>".$clang->gT("The following old survey response tables exist and may be deleted if no longer required:")."<br /><span style='font-size: 7pt'>\n";
                foreach($oldsmultidelete as $omd) {
                    list($tablename, $display)=explode("|", $omd);
                    $integritycheck2 .= "<input type='checkbox' value='$tablename' name='oldsmultidelete[]' />$display<br />\n";
                }
                $integritycheck2 .= "</span><br /></li>\n";
            }
            if(isset($oldtoptionaldelete)) {
                $integritycheck2 .= "<li>".$clang->gT("The following old token tables contain no tokens and can be deleted:")."<br /><span style='font-size: 7pt'>\n";
                foreach($oldtoptionaldelete as $ood) {
                    list($tablename, $display)=explode("|", $ood);
                    $integritycheck2 .= "<input type='checkbox' value='$tablename' name='oldtoptionaldelete[]' />$display<br />\n";
                }
                $integritycheck2 .= "</span><br /></li>\n";
            }
            if(isset($oldtmultidelete)) {
                $integritycheck2 .= "<li>".$clang->gT("The following old token list tables exist and may be deleted if no longer required:")."<br /><span style='font-size: 7pt'>\n";
                foreach($oldtmultidelete as $omd) {
                    list($tablename, $display)=explode("|", $omd);
                    $integritycheck2 .= "<input type='checkbox' value='$tablename' name='oldtmultidelete[]' />$display<br />\n";
                }
                $integritycheck2 .= "</span></li>\n";
            }
            $integritycheck2 .= "</ul><input type='hidden' name='ok' value='R' />\n"
            ."<center><input type='submit' value='".$clang->gT("Delete checked items!")."' /><br />\n"
            ."<p><span style='color: red; font-size:0.8em;'>".$clang->gT("Note that you cannot undo a delete if you proceed. The data will be gone.")."</span><br /></center>\n"
            ."</form>\n";


        }
        $integritycheck2 .= "</div>";

        $integritycheck .= $integritycheck2;
    }
    elseif ($ok == "Y")
    {
        $integritycheck .= "<table><tr><td height='1'></td></tr></table>\n"
        . "<table align='center' style='border: 1px solid #555555' "
        . "cellpadding='1' cellspacing='0' width='450'>\n"
        . "\t<tr>\n"
        . "\t\t<td colspan='2' align='center'>\n"
        . "\t\t\t<strong>".$clang->gT("Data Consistency Check")."<br /><span style='font-size:7pt;'>".$clang->gT("If errors are showing up you might have to execute this script repeatedly.")."</strong>\n"
        . "\t\t</td>\n"
        . "\t</tr>\n"
        . "\t<tr><td align='center'>";
        $cdelete=returnglobal('cdelete');
        $adelete=returnglobal('adelete');
        $qdelete=returnglobal('qdelete');
        $gdelete=returnglobal('gdelete');
        $assdelete=returnglobal('assdelete');
        $asgdelete=returnglobal('asgdelete');
        $qadelete=returnglobal('qadelete');
        $sdelete=returnglobal('sdelete');
        $sldelete=returnglobal('sldelete');
        $oldsdelete=returnglobal('oldsdelete');
        $oldtdelete=returnglobal('oldtdelete');
        $bDefaultValueDelete=returnglobal('defaultvalue_delete');
        $bQuotaDelete=returnglobal('quota_delete');
        $bQuotaLanguageSettingsDelete=returnglobal('quotalanguagesettings_delete');
        $bQuotaMembersDelete=returnglobal('quotamembers_delete');

        if (isset($bDefaultValueDelete) && $bDefaultValueDelete==1)
        {
            $integritycheck .= $clang->gT("Deleting orphaned default values.")."<br />\n";
            $sSQL = "delete FROM {$dbprefix}defaultvalues where qid not in (select qid from {$dbprefix}questions)";
            $result = $connect->Execute($sSQL) or safe_die ("Couldn't delete default values ($sSQL)<br />".$connect->ErrorMsg());
        }

        if (isset($bQuotaMembersDelete) && $bQuotaMembersDelete==1)
        {
            $integritycheck .= $clang->gT("Deleting orphaned quotas.")."<br />\n";
            $sSQL = "delete FROM {$dbprefix}quota_members where quota_id not in (select id from {$dbprefix}quota) or qid not in (select qid from {$dbprefix}questions) or sid not in (select sid from {$dbprefix}surveys)";
            $result = $connect->Execute($sSQL) or safe_die ("Couldn't delete quota members ($sSQL)<br />".$connect->ErrorMsg());
        }

        if (isset($bQuotaDelete) && $bQuotaDelete==1)
        {
            $integritycheck .= $clang->gT("Deleting orphaned quotas.")."<br />\n";
            $sSQL = "delete FROM {$dbprefix}quota where sid not in (select sid from {$dbprefix}surveys)";
            $result = $connect->Execute($sSQL) or safe_die ("Couldn't delete quotas ($sSQL)<br />".$connect->ErrorMsg());
        }


        if (isset($bQuotaLanguageSettingsDelete) && $bQuotaLanguageSettingsDelete==1)
        {
            $integritycheck .= $clang->gT("Deleting orphaned language settings.")."<br />\n";
            $sSQL = "delete FROM {$dbprefix}quota_languagesettings where quotals_quota_id not in (select id from {$dbprefix}quota)";
            $result = $connect->Execute($sSQL) or safe_die ("Couldn't delete quotas ($sSQL)<br />".$connect->ErrorMsg());
        }

        if (isset($oldsdelete)) {
            $integritycheck .= $clang->gT("Deleting old survey result tables").":<br /><span style='font-size: 7pt;'>\n";
            foreach ($oldsdelete as $olds) {
                $integritycheck .= $clang->gT("Deleting")." $olds<br />\n";
                $sql = "DROP TABLE $olds";
                $result = $connect->Execute($sql) or safe_die ("Couldn't drop table $olds ($sql)<br />".$connect->ErrorMsg());
            }
            $integritycheck .= "</span><br />\n";
        }

        if (isset($oldtdelete)) {
            $integritycheck .= $clang->gT("Deleting old survey result tables").":<br /><span style='font-size: 7pt;'>\n";
            foreach ($oldtdelete as $oldt) {
                $integritycheck .= $clang->gT("Deleting")." $oldt<br />\n";
                $sql = "DROP TABLE $oldt";
                $result = $connect->Execute($sql) or safe_die ("Couldn't drop table $olds ($sql)<br />".$connect->ErrorMsg());
            }
            $integritycheck .= "</span><br />\n";
        }

        if (isset($sdelete)) {
            $integritycheck .= $clang->gT("Deleting Surveys").":<br /><spanstyle='font-size:7pt;'>\n";
            foreach ($sdelete as $ass) {
                $integritycheck .= $clang->gT("Deleting Survey ID").":".$ass."<br />\n";
                $sql = "DELETE FROM {$dbprefix}surveys WHERE sid=$ass";
                $result = $connect->Execute($sql) or safe_die ("Couldn't delete ($sql)<br />".$connect->ErrorMsg());
            }
            $integritycheck .= "</span><br />\n";
        }

        if (isset($sldelete)) {
            $integritycheck .= $clang->gT("Deleting survey language settings").":<br /><spanstyle='font-size:7pt;'>\n";
            foreach ($sldelete as $sld) {
                $integritycheck .= $clang->gT("Deleting survey language setting").":".$sld."<br />\n";
                $sql = "DELETE FROM {$dbprefix}surveys_languagesettings WHERE surveyls_survey_id=$sld";
                $result = $connect->Execute($sql) or safe_die ("Couldn't delete ($sql)<br />".$connect->ErrorMsg());
            }
            $integritycheck .= "</span><br />\n";
        }

        if (isset($assdelete)) {
            $integritycheck .= $clang->gT( "Deleting Assessments").":<br /><spanstyle='font-size:7pt;'>\n";
            foreach ($assdelete as $ass) {
                $integritycheck .= $clang->gT("Deleting ID").":".$ass."<br />\n";
                $sql = "DELETE FROM {$dbprefix}assessments WHERE id=$ass";
                $result = $connect->Execute($sql) or safe_die ("Couldn't delete ($sql)<br />".$connect->ErrorMsg());
            }
            $integritycheck .= "</span><br />\n";
        }

        if (isset($asgdelete)) {
            $integritycheck .= $clang->gT("Deleting Assessments").":<br /><spanstyle='font-size:7pt;'>\n";
            foreach ($asgdelete as $asg) {
                $integritycheck .= $clang->gT("Deleting ID").":".$asg."<br />\n";
                $sql = "DELETE FROM {$dbprefix}assessments WHERE id=$asg";
                $result = $connect->Execute($sql) or safe_die ("Couldn't delete ($sql)<br />".$connect->ErrorMsg());
            }
            $integritycheck .= "</span><br />\n";
        }

        if (isset($qadelete)) {
            $integritycheck .= $clang->gT("Deleting Question_Attributes").":<br /><spanstyle='font-size:7pt;'>\n";
            foreach ($qadelete as $qad) {
                $integritycheck .= "Deleting QAID:".$qad."<br />\n";
                $sql = "DELETE FROM {$dbprefix}question_attributes WHERE qaid=$qad";
                $result = $connect->Execute($sql) or safe_die ("Couldn't delete ($sql)<br />".$connect->ErrorMsg());
            }
            $integritycheck .= "</span><br />\n";
        }

        if (isset($cdelete)) {
            $integritycheck .= $clang->gT("Deleting Conditions").":<br /><span style='font-size:7pt;'>\n";
            foreach ($cdelete as $cd) {
                $integritycheck .= $clang->gT("Deleting cid").":".$cd."<br />\n";
                $sql = "DELETE FROM {$dbprefix}conditions WHERE cid=$cd";
                $result=$connect->Execute($sql) or safe_die ("Couldn't Delete ($sql)<br />".$connect->ErrorMsg());
            }
            $integritycheck .= "</span><br />\n";
        }
        if (isset($adelete)) {
            $integritycheck .= $clang->gT("Deleting Answers").":<br /><span style='font-size:7pt;'>\n";
            foreach ($adelete as $ad) {
                list($ad1, $ad2)=explode("|", $ad);
                $integritycheck .= $clang->gT("Deleting answer with qid").":".$ad1." and code: ".$ad2."<br />\n";
                $sql = "DELETE FROM {$dbprefix}answers WHERE qid=$ad1 AND code='$ad2'";
                $result=$connect->Execute($sql) or safe_die ("Couldn't Delete ($sql)<br />".$connect->ErrorMsg());
            }
            $integritycheck .= "</span><br />\n";
        }
        if (isset($qdelete)) {
            $integritycheck .= $clang->gT("Deleting questions").":<br /><span style='font-size:7pt;'>\n";
            foreach ($qdelete as $qd) {
                $integritycheck .= $clang->gT("Deleting qid").":".$qd."<br />\n";
                $sql = "DELETE FROM {$dbprefix}questions WHERE qid=$qd";
                $result=$connect->Execute($sql) or safe_die ("Couldn't Delete ($sql)<br />".$connect->ErrorMsg());
            }
            $integritycheck .= "</span><br />\n";
        }
        if (isset($gdelete)) {
            $integritycheck .= $clang->gT("Deleting Groups").":<br /><span style='font-size:7pt;'>\n";
            foreach ($gdelete as $gd) {
                $integritycheck .= $clang->gT("Deleting group id").":".$gd."<br />\n";
                $sql = "DELETE FROM {$dbprefix}groups WHERE gid=$gd";
                $result=$connect->Execute($sql) or safe_die ("Couldn't Delete ($sql)<br />".$connect->ErrorMsg());
            }
            $integritycheck .= "</span><br />\n";
        }
        $integritycheck .= $clang->gT("Check database again?")."<br />\n"
        ."<a href='{$scriptname}?action=checkintegrity'>".$clang->gT("Check Again")."</a><br />\n"
        ."</td></tr></table><br />\n";
    } elseif ($ok == "R")
    {
        $integritycheck .= "<div class='messagebox ui-corner-all'>\n"
        . "<div class='header ui-widget-header'>".$clang->gT("Data redundancy check")."<br />\n"
        . "<span style='font-size:7pt;'>".$clang->gT("Deleting old token and response tables leftover from deactivation")."</span></div><p>\n";
        $oldsmultidelete=returnglobal('oldsmultidelete');
        $oldtmultidelete=returnglobal('oldtmultidelete');
        $oldsoptionaldelete=returnglobal('oldsoptionaldelete');
        $oldtoptionaldelete=returnglobal('oldtoptionaldelete');

        if (isset($oldsoptionaldelete)) { //OLD Survey Tables with zero entries
            $integritycheck .= $clang->gT("Deleting old survey result tables").":<br /><span style='font-size: 7pt;'>\n";
            foreach ($oldsoptionaldelete as $olds) {
                $integritycheck .= $clang->gT("Deleting")." $olds<br />\n";
                $sql = "DROP TABLE $olds";
                $result = $connect->Execute($sql) or safe_die ("Couldn't drop table $olds ($sql)<br />".$connect->ErrorMsg());
            }
            $integritycheck .= "</span><br />\n";
        }

        if (isset($oldsmultidelete)) {
            $integritycheck .= $clang->gT("Deleting old survey result tables").":<br /><span style='font-size: 7pt;'>\n";
            foreach ($oldsmultidelete as $olds) {
                $integritycheck .= $clang->gT("Deleting")." $olds<br />\n";
                $sql = "DROP TABLE $olds";
                $result = $connect->Execute($sql) or safe_die ("Couldn't drop table $olds ($sql)<br />".$connect->ErrorMsg());
            }
            $integritycheck .= "</span><br />\n";
        }

        if (isset($oldtmultidelete)) {
            $integritycheck .= $clang->gT("Deleting old token tables").":<br /><span style='font-size: 7pt;'>\n";
            foreach ($oldtmultidelete as $oldt) {
                $integritycheck .= $clang->gT("Deleting")." $oldt<br />\n";
                $sql = "DROP TABLE $oldt";
                $result = $connect->Execute($sql) or safe_die ("Couldn't drop table $oldt ($sql)<br />".$connect->ErrorMsg());
            }
            $integritycheck .= "</span><br />\n";
        }

        if (isset($oldtoptionaldelete)) {
            $integritycheck .= $clang->gT("Deleting old token tables").":<br /><span style='font-size: 7pt;'>\n";
            foreach ($oldtoptionaldelete as $oldt) {
                $integritycheck .= $clang->gT("Deleting")." $oldt<br />\n";
                $sql = "DROP TABLE $oldt";
                $result = $connect->Execute($sql) or safe_die ("Couldn't drop table $oldt ($sql)<br />".$connect->ErrorMsg());
            }
            $integritycheck .= "</span><br />\n";
        }

        $integritycheck .= $clang->gT("Check database again?")."<br />\n"
        ."<a href='{$scriptname}?action=checkintegrity'>".$clang->gT("Check Again")."</a><br />\n"
        ."</div><br />\n";

    }

    $surveyid=false;
}
else
{
    $action = "dbchecker";
    include("access_denied.php");
    include("admin.php");
}
?>
