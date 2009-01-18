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
    // THIS FILE CHECKS THE CONSISTENCY OF THE DATABASE, IT LOOKS FOR
    // STRAY QUESTIONS, ANSWERS, CONDITIONS OR GROUPS AND DELETES THEM
    $ok=returnglobal('ok');
    
    $integritycheck=''; 
    if (!isset($ok) || $ok != "Y") // do the check, but don't delete anything
    {
        $integritycheck .= "<table><tr><td height='1'></td></tr></table>\n"
        . "<table align='center' class='menu2columns' style='border: 1px solid #555555' "
        . "cellpadding='1' cellspacing='0' width='450'>\n"
        . "\t<tr>\n"
        . "\t\t<td colspan='2' align='center'>\n"
        . "\t\t\t<strong>".$clang->gT("Data Consistency Check")."<br /><font size='1'>".$clang->gT("If errors are showing up you might have to execute this script repeatedly.")."</font></strong>\n"
        . "\t\t</td>\n"
        . "\t</tr>\n"
        . "\t<tr><td align='center'>"
        . "<br />\n";
        // Check conditions
        //  $query = "SELECT {$dbprefix}questions.sid, {$dbprefix}conditions.* "
        //          ."FROM {$dbprefix}conditions, {$dbprefix}questions "
        //          ."WHERE {$dbprefix}conditions.qid={$dbprefix}questions.qid "
        //          ."ORDER BY qid, scenario, cqid, cfieldname, value";
        $query = "SELECT * FROM {$dbprefix}conditions ORDER BY cid";
        $result = db_execute_assoc($query) or safe_die("Couldn't get list of conditions from database<br />$query<br />".$connect->ErrorMsg());
        while ($row=$result->FetchRow())
        {
            $qquery="SELECT qid FROM {$dbprefix}questions WHERE qid='{$row['qid']}'";
            $qresult=$connect->Execute($qquery) or safe_die ("Couldn't check questions table for qids<br />$qquery<br />".$connect->ErrorMsg());
            $qcount=$qresult->RecordCount();
            if (!$qcount) {$cdelete[]=array("cid"=>$row['cid'], "reason"=>"No matching qid");}
            $qquery = "SELECT qid FROM {$dbprefix}questions WHERE qid='{$row['cqid']}'";
            $qresult=$connect->Execute($qquery) or safe_die ("Couldn't check questions table for qids<br />$qquery<br />".$connect->ErrorMsg());
            $qcount=$qresult->RecordCount();
            if (!$qcount) {$cdelete[]=array("cid"=>$row['cid'], "reason"=>$clang->gT("No matching Cqid"));}
            if ($row['cfieldname']) //Only do this if there actually is a "cfieldname"
            {
                list ($surveyid, $gid, $rest) = explode("X", $row['cfieldname']);
                $qquery = "SELECT gid FROM {$dbprefix}groups WHERE gid=$gid";
                $qresult = $connect->Execute($qquery) or safe_die ("Couldn't check conditional group matches<br />$qquery<br />".$connect->ErrorMsg());
                $qcount=$qresult->RecordCount();
                if ($qcount < 1) {$cdelete[]=array("cid"=>$row['cid'], "reason"=>$clang->gT("No matching CFIELDNAME Group!")." ($gid) ({$row['cfieldname']})");}
            }
            elseif (!$row['cfieldname'])
            {
                $cdelete[]=array("cid"=>$row['cid'], "reason"=>$clang->gT("No \"CFIELDNAME\" field set!")." ({$row['cfieldname']})");
            }
        }
        if (isset($cdelete) && $cdelete)
        {
            $integritycheck .= "<strong>".$clang->gT("The following conditions should be deleted").":</strong><br /><font size='1'>\n";
            foreach ($cdelete as $cd) {
                $integritycheck .= "CID: {$cd['cid']} ".$clang->gT("because")." {$cd['reason']}<br />\n";
            }
            $integritycheck .= "</font><br />\n";
        }
        else
        {
            $integritycheck .= "<strong>".$clang->gT("All conditions meet consistency standards")."</strong><br />\n";
        }
    
        // Check question_attributes to delete
        $query = "SELECT * FROM {$dbprefix}question_attributes ORDER BY qid";
        $result = db_execute_assoc($query) or safe_die($connect->ErrorMsg());
        while($row = $result->FetchRow())
        {
            $aquery = "SELECT * FROM {$dbprefix}questions WHERE qid = {$row['qid']}";
            $aresult = $connect->Execute($aquery) or safe_die($connect->ErrorMsg());
            $qacount = $aresult->RecordCount();
            if (!$qacount) {
                $qadelete[]=array("qaid"=>$row['qaid'], "attribute"=>$row['attribute'], "reason"=>$clang->gT("No matching qid"));
            }
        } // while
        if (isset($qadelete) && $qadelete) {
            $integritycheck .= "<strong>".$clang->gT("The following question attributes should be deleted").":</strong><br /><font size='1'>\n";
            foreach ($qadelete as $qad) {$integritycheck .= "QAID `{$qad['qaid']}` ATTRIBUTE `{$qad['attribute']}` ".$clang->gT("because")." `{$qad['reason']}`<br />\n";}
            $integritycheck .= "</font><br />\n";
        }
        else
        {
            $integritycheck .= "<strong>".$clang->gT("All question attributes meet consistency standards")."</strong><br />\n";
        }
    
        // Check assessments
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
            $integritycheck .= "<strong>".$clang->gT("The following assessments should be deleted").":</strong><br /><font size='1'>\n";
            foreach ($assdelete as $ass) {$integritycheck .= "ID `{$ass['id']}` ASSESSMENT `{$ass['assessment']}` ".$clang->gT("because")." `{$ass['reason']}`<br />\n";}
            $integritycheck .= "</font><br />\n";
        }
        else
        {
            $integritycheck .= "<strong>".$clang->gT("All Survey (Total) assessments meet consistency standards")."</strong><br />\n";
        }
        if (isset($asgdelete) && $asgdelete)
        {
            $integritycheck .= "<strong>".$clang->gT("The following assessments should be deleted").":</strong><br /><font size='1'>\n";
            foreach ($asgdelete as $asg) {$integritycheck .= "ID `{$asg['id']}` ASSESSMENT `{$asg['assessment']}` ".$clang->gT("because")." `{$asg['reason']}`<br />\n";}
            $integritycheck .= "</font><br />\n";
        }
        else
        {
            $integritycheck .= "<strong>".$clang->gT("All Group assessments meet consistency standards")."</strong><br />\n";
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
            $integritycheck .= "<strong>".$clang->gT("The following answers should be deleted").":</strong><br /><font size='1'>\n";
            foreach ($adelete as $ad) {$integritycheck .= "QID `{$ad['qid']}` CODE `{$ad['code']}` ".$clang->gT("because")." `{$ad['reason']}`<br />\n";}
            $integritycheck .= "</font><br />\n";
        }
        else
        {
            $integritycheck .= "<strong>".$clang->gT("All answers meet consistency standards")."</strong><br />\n";
        }
    
        // Check surveys
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
            $integritycheck .= "<strong>".$clang->gT("The following surveys should be deleted").":</strong><br /><font size='1'>\n";
            foreach ($sdelete as $sd) {$integritycheck .= "SID `{$sd['sid']}` ".$clang->gT("because")." `{$sd['reason']}`<br />\n";}
            $integritycheck .= "</font><br />\n";
        }
        else
        {
            $integritycheck .= "<strong>".$clang->gT("All survey settings meet consistency standards")."</strong><br />\n";
        }
    
        //check questions
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
            $integritycheck .= "<strong>".$clang->gT("The following questions should be deleted").":</strong><br /><font size='1'>\n";
            foreach ($qdelete as $qd) {$integritycheck .= "QID `{$qd['qid']}` ".$clang->gT("because")." `{$qd['reason']}`<br />\n";}
            $integritycheck .= "</font><br />\n";
        }
        else
        {
            $integritycheck .= "<strong>".$clang->gT("All questions meet consistency standards")."</strong><br />\n";
        }
        //check groups
        $query = "SELECT * FROM {$dbprefix}groups ORDER BY sid, gid";
        $result=db_execute_assoc($query) or safe_die ("Couldn't get list of groups for checking<br />$query<br />".$connect->ErrorMsg());
        while ($row=$result->FetchRow())
        {
            //make sure survey exists
            $qquery = "SELECT * FROM {$dbprefix}groups WHERE sid={$row['sid']}";
            $qresult=$connect->Execute($qquery) or safe_die("Couldn't check surveys table for gids from groups<br />$qquery<br />".$connect->ErrorMsg());
            $qcount=$qresult->RecordCount();
            if (!$qcount) {$gdelete[]=array($row['gid']);}
        }
        if (isset($gdelete) && $gdelete)
        {
            $integritycheck .= "<strong>".$clang->gT("The following groups should be deleted").":</strong><br /><font size='1'>\n";
            $integritycheck .= implode(", ", $gdelete);
            $integritycheck .= "</font><br />\n";
        }
        else
        {
            $integritycheck .= "<strong>".$clang->gT("All groups meet consistency standards")."</strong><br />\n";
        }
        //NOW CHECK FOR STRAY SURVEY RESPONSE TABLES AND TOKENS TABLES
        if (!isset($cdelete) && !isset($adelete) && !isset($qdelete) && !isset($gdelete) && !isset($asgdelete) && !isset($sdelete) && !isset($assdelete) && !isset($qadelete)) {
            $integritycheck .= "<br />".$clang->gT("No database action required");
        } else {
            $integritycheck .= "<br />".$clang->gT("Should we proceed with the delete?")."<br />\n";
            $integritycheck .= "<form action='{$_SERVER['PHP_SELF']}?action=checkintegrity' method='post'>\n";
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
                    $integritycheck .= "<input type='hidden' name='gdelete[]' value='{$gd['gid']}' />\n";
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
            $integritycheck .= "<input type='hidden' name='ok' value='Y'>\n"
                              ."<input type='submit' value='".$clang->gT("Yes - Delete Them!")."'>\n"
                              ."</form>\n";
        }
        $integritycheck .= "<br /><br />\n"
        ."</td></tr></table>\n"
        ."<table><tr><td height='1'></td></tr></table>\n";
    }
    elseif ($ok == "Y")
    {
        $integritycheck .= "<table><tr><td height='1'></td></tr></table>\n"
        . "<table align='center' style='border: 1px solid #555555' "
        . "cellpadding='1' cellspacing='0' width='450'>\n"
        . "\t<tr>\n"
        . "\t\t<td colspan='2' align='center'>\n"
        . "\t\t\t<strong>".$clang->gT("Data Consistency Check")."<br /><font size='1'>".$clang->gT("If errors are showing up you might have to execute this script repeatedly.")."</strong>\n"
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
    
        if (isset($sdelete)) {
            $integritycheck .= $clang->gT("Deleting Surveys").":<br /><fontsize='1'>\n";
            foreach ($sdelete as $ass) {
                $integritycheck .= $clang->gT("Deleting Survey ID").":".$ass."<br />\n";
                $sql = "DELETE FROM {$dbprefix}surveys WHERE sid=$ass";
                $result = $connect->Execute($sql) or safe_die ("Couldn't delete ($sql)<br />".$connect->ErrorMsg());
            }
        }    
    
        if (isset($assdelete)) {
            $integritycheck .= $clang->gT( "Deleting Assessments").":<br /><fontsize='1'>\n";
            foreach ($assdelete as $ass) {
                $integritycheck .= $clang->gT("Deleting ID").":".$ass."<br />\n";
                $sql = "DELETE FROM {$dbprefix}assessments WHERE id=$ass";
                $result = $connect->Execute($sql) or safe_die ("Couldn't delete ($sql)<br />".$connect->ErrorMsg());
            }
        }
        if (isset($asgdelete)) {
            $integritycheck .= $clang->gT("Deleting Assessments").":<br /><fontsize='1'>\n";
            foreach ($asgdelete as $asg) {
                $integritycheck .= $clang->gT("Deleting ID").":".$asg."<br />\n";
                $sql = "DELETE FROM {$dbprefix}assessments WHERE id=$asg";
                $result = $connect->Execute($sql) or safe_die ("Couldn't delete ($sql)<br />".$connect->ErrorMsg());
            }
        }
        if (isset($qadelete)) {
            $integritycheck .= $clang->gT("Deleting Question_Attributes").":<br /><fontsize='1'>\n";
            foreach ($qadelete as $qad) {
                $integritycheck .= "Deleting QAID:".$qad."<br />\n";
                $sql = "DELETE FROM {$dbprefix}question_attributes WHERE qaid=$qad";
                $result = $connect->Execute($sql) or safe_die ("Couldn't delete ($sql)<br />".$connect->ErrorMsg());
            }
        }
        if (isset($cdelete)) {
            $integritycheck .= $clang->gT("Deleting Conditions").":<br /><font size='1'>\n";
            foreach ($cdelete as $cd) {
                $integritycheck .= $clang->gT("Deleting cid").":".$cd."<br />\n";
                $sql = "DELETE FROM {$dbprefix}conditions WHERE cid=$cd";
                $result=$connect->Execute($sql) or safe_die ("Couldn't Delete ($sql)<br />".$connect->ErrorMsg());
            }
            $integritycheck .= "</font><br />\n";
        }
        if (isset($adelete)) {
            $integritycheck .= $clang->gT("Deleting Answers").":<br /><font size='1'>\n";
            foreach ($adelete as $ad) {
                list($ad1, $ad2)=explode("|", $ad);
                $integritycheck .= $clang->gT("Deleting answer with qid").":".$ad1." and code: ".$ad2."<br />\n";
                $sql = "DELETE FROM {$dbprefix}answers WHERE qid=$ad1 AND code='$ad2'";
                $result=$connect->Execute($sql) or safe_die ("Couldn't Delete ($sql)<br />".$connect->ErrorMsg());
            }
            $integritycheck .= "</font><br />\n";
        }
        if (isset($qdelete)) {
            $integritycheck .= $clang->gT("Deleting Questions").":<br /><font size='1'>\n";
            foreach ($qdelete as $qd) {
                $integritycheck .= $clang->gT("Deleting qid").":".$qd."<br />\n";
                $sql = "DELETE FROM {$dbprefix}questions WHERE qid=$qd";
                $result=$connect->Execute($sql) or safe_die ("Couldn't Delete ($sql)<br />".$connect->ErrorMsg());
            }
            $integritycheck .= "</font><br />\n";
        }
        if (isset($gdelete)) {
            $integritycheck .= $clang->gT("Deleting Groups").":<br /><font size='1'>\n";
            foreach ($gdelete as $gd) {
                $integritycheck .= $clang->gT("Deleting group id").":".$gd."<br />\n";
                $sql = "DELETE FROM {$dbprefix}groups WHERE gid=$gd";
                $result=$connect->Execute($sql) or safe_die ("Couldn't Delete ($sql)<br />".$connect->ErrorMsg());
            }
            $integritycheck .= "</font><br />\n";
        }
        $integritycheck .= $clang->gT("Check database again?")."<br />\n"
                          ."<a href='{$_SERVER['PHP_SELF']}?action=checkintegrity'>".$clang->gT("Check Again")."</a><br />\n"
                          ."</td></tr></table><br />\n";
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
