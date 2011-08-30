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
 * $Id: globalsettings.php 10760 2011-08-17 19:42:04Z dionet $
 */

/**
 * CheckIntegrity Controller
 *
 *
 * @package       LimeSurvey
 * @subpackage    Backend
 */
class CheckIntegrity extends Admin_Controller {

    /**
     * Constructor
     */
    function __construct()
    {
        parent::__construct();
    }

    function index()
    {
        if($this->session->userdata('USER_RIGHT_CONFIGURATOR') == 1)
        {
            if($this->input->post("action"))
            {
                self::_correctissues();
            }
            else
            {
                self::_display();
            }
        }
    }

    function _display()
    {

        $aDelete=array();
        /****** Plainly delete survey permissions if the survey or user does not exist ***/
        $this->db->query("delete FROM {$this->db->dbprefix('survey_permissions')} where sid not in (select sid from {$this->db->dbprefix('surveys')})");
        $this->db->query("delete FROM {$this->db->dbprefix('survey_permissions')} where uid not in (select uid from {$this->db->dbprefix('users')})");
        $this->load->helper('database');
        $this->load->dbforge();

        /***** Check for activate survey tables with missing survey entry **/
        $sDBPrefix=$this->db->dbprefix;
        $sQuery = db_select_tables_like("{$sDBPrefix}survey\_%");
        $aResult = db_execute_assoc($sQuery) or safe_die("Couldn't get list of conditions from database<br />$query<br />");
        foreach ($aResult->row_array() as $aRow)
        {
           $sTableName=substr($aRow,strlen($sDBPrefix));
           if ($sTableName=='survey_permissions' || $sTableName=='survey_links') continue;
           $iSurveyID=substr($sTableName,strpos($sTableName,'_')+1);
           $sQuery="SELECT sid FROM {$sDBPrefix}surveys WHERE sid='{$iSurveyID}'";
           $oResult=$this->db->query($sQuery) or safe_die ("Couldn't check questions table for qids<br />$qquery<br />");
           $iRowCount=$oResult->num_rows();
           if ($iRowCount==0)
           {
                $sDate = date('YmdHis').rand(1,1000);
                $sOldTable = "survey_{$iSurveyID}";
                $sNewTable = "old_survey_{$iSurveyID}_{$sDate}";
                $sQuery = $this->dbforge->rename_table($sDBPrefix.$sOldTable,$sDBPrefix.$sNewTable);
                $deactivateresult = $connect->Execute($sQuery) or die ("Couldn't make backup of the survey table. Please try again. The database reported the following error:<br />".htmlspecialchars($connect->ErrorMsg())."<br />");
                /* Not sure if that is still necessary if the rename procedure works right on CI
                if ($databasetype=='postgre')
                {
                    // If you deactivate a postgres table you have to rename the according sequence too and alter the id field to point to the changed sequence
                    $deactivatequery = db_rename_table($sOldTable.'_id_seq',$sNewTable.'_id_seq');
                    $deactivateresult = $connect->Execute($deactivatequery) or die ("Couldn't make backup of the survey table. Please try again. The database reported the following error:<br />".htmlspecialchars($connect->ErrorMsg())."<br /><br />Survey was not deactivated either.<br /><br /><a href='$scriptname?sid={$postsid}'>".$clang->gT("Main Admin Screen")."</a>");
                    $setsequence="ALTER TABLE $sNewTable ALTER COLUMN id SET DEFAULT nextval('{$sNewTable}_id_seq'::regclass);";
                    $deactivateresult = $connect->Execute($setsequence) or die ("Couldn't make backup of the survey table. Please try again. The database reported the following error:<br />".htmlspecialchars($connect->ErrorMsg())."<br /><br />Survey was not deactivated either.<br /><br /><a href='$scriptname?sid={$postsid}'>".$clang->gT("Main Admin Screen")."</a>");
                }
                */

           }
        }

        /***** Check for activate survey tables with missing survey entry **/
        $sQuery = db_select_tables_like("{$sDBPrefix}tokens\_%");
        $aResult = db_execute_assoc($sQuery) or safe_die("Couldn't get list of conditions from database<br />$query<br />");
        foreach ($aResult->row_array() as $aRow)
        {
           $sTableName=substr($aRow,strlen($sDBPrefix));
           $iSurveyID=substr($sTableName,strpos($sTableName,'_')+1);
           $sQuery="SELECT sid FROM {$sDBPrefix}surveys WHERE sid='{$iSurveyID}'";
           $oResult=$this->db->query($sQuery) or safe_die ("Couldn't check questions table for qids<br />$qquery<br />");
           $iRowCount=$oResult->num_rows();
           if ($iRowCount==0)
           {
                $sDate = date('YmdHis').rand(1,1000);
                $sOldTable = "tokens_{$iSurveyID}";
                $sNewTable = "old_tokens_{$iSurveyID}_{$sDate}";
                $sQuery = $this->dbforge->rename_table($sDBPrefix.$sOldTable,$sDBPrefix.$sNewTable);
                $deactivateresult = $connect->Execute($sQuery) or die ("Couldn't make backup of the survey table. Please try again. The database reported the following error:<br />".htmlspecialchars($connect->ErrorMsg())."<br />");
                /* Not sure if that is still necessary if the rename procedure works right on CI
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
                */

           }
        }

        /**********************************************************************/
        /*     CHECK CONDITIONS                                               */
        /**********************************************************************/
        $sQuery = "SELECT * FROM {$sDBPrefix}conditions ORDER BY cid";
        $oCResult = db_execute_assoc($sQuery) or safe_die("Couldn't get list of conditions from database<br />$sQuery<br />");
        foreach ($oCResult->row_array() as $aRow)
        {
            $sQuery="SELECT qid FROM {$sDBPrefix}questions WHERE qid='{$aRow['qid']}'";
            $qresult=$connect->Execute($sQuery) or safe_die ("Couldn't check questions table for qids<br />$sQuery<br />");
            $iRowCount=$qresult->num_rows();
            if (!$iRowCount) {$aDelete['cid'][]=array($aRow['cid'], "reason"=>"No matching QID");}

            if ($aRow['cqid'] != 0)
            { // skip case with cqid=0 for codnitions on {TOKEN:EMAIL} for instance
                $sQuery = "SELECT qid FROM {$sDBPrefix}questions WHERE qid='{$aRow['cqid']}'";
                $oQResult=$connect->Execute($sQuery) or safe_die ("Couldn't check questions table for qids<br />$sQuery<br />");
                $iRowCount=$oQResult->num_rows();
                if (!$iRowCount) {$aDelete['cid'][]=array($aRow['cid'], "reason"=>$clang->gT("No matching CQID"));}
            }
            if ($aRow['cfieldname']) //Only do this if there actually is a "cfieldname"
            {
                if (preg_match("/^\+{0,1}[0-9]+X[0-9]+X*$/",$aRow['cfieldname']))
                { // only if cfieldname isn't Tag such as {TOKEN:EMAIL} or any other token
                    list ($surveyid, $gid, $rest) = explode("X", $aRow['cfieldname']);
                    $sQuery = "SELECT gid FROM {$sDBPrefix}groups WHERE gid=$gid";
                    $oGResult = $connect->Execute($sQuery) or safe_die ("Couldn't check conditional group matches<br />$sQuery<br />");
                    $iRowCount=$oGResult->num_rows();
                    if ($iRowCount < 1) $aDelete['cid'][]=array($aRow['cid'], "reason"=>$clang->gT("No matching CFIELDNAME group!")." ($gid) ({$aRow['cfieldname']})");
                }
            }
            elseif (!$aRow['cfieldname'])
            {
                $aDelete['cid'][]=array($aRow['cid'], "reason"=>$clang->gT("No CFIELDNAME field set!")." ({$aRow['cfieldname']})");
            }
        }



        self::_getAdminHeader();
        self::_showadminmenu();
        $this->load->view('admin/checkintegrity_view',$aDelete);
        self::_getAdminFooter("http://docs.limesurvey.org", $this->limesurvey_lang->gT("LimeSurvey online manual"));
    }
}