<?PHP
/*
 * LimeSurvey
 * Copyright (C) 2007 The LimeSurvey Project Team / Carsten Schmitz
 * All rights reserved.
 * License: http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * LimeSurvey is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 *
 * $Id: upgrade-mssql.php 7556 2009-09-01 23:48:37Z c_schmitz $
 */

// There will be a file for each database (accordingly named to the dbADO scheme)
// where based on the current database version the database is upgraded
// For this there will be a settings table which holds the last time the database was upgraded

function db_upgrade_all($oldversion) {
    /// This function does anything necessary to upgrade
    /// older versions to match current functionality
    global $modifyoutput, $dbprefix, $usertemplaterootdir, $standardtemplaterootdir;
    $CI =& get_instance();
    $CI->load->dbforge();
    $clang = $CI->limesurvey_lang;
    echo str_pad($clang->gT('The LimeSurvey database is being upgraded').' ('.date('Y-m-d H:i:s').')',14096).".". $clang->gT('Please be patient...')."<br /><br />\n";

    if ($oldversion < 143)
    {
        // Move all user templates to the new user template directory
        echo sprintf($clang->gT("Moving user templates to new location at %s..."),$usertemplaterootdir)."<br />";
        $myDirectory = opendir($standardtemplaterootdir);
        $aFailedTemplates=array();
        // get each entry
        while($entryName = readdir($myDirectory)) {
            if (!in_array($entryName,array('.','..','.svn')) && is_dir($standardtemplaterootdir.DIRECTORY_SEPARATOR.$entryName) && !isStandardTemplate($entryName))
            {
               if (!rename($standardtemplaterootdir.DIRECTORY_SEPARATOR.$entryName,$usertemplaterootdir.DIRECTORY_SEPARATOR.$entryName))
               {
                  $aFailedTemplates[]=$entryName;
               };
            }
        }
        if (count($aFailedTemplates)>0)
        {
            echo "The following templates at {$standardtemplaterootdir} could not be moved to the new location at {$usertemplaterootdir}:<br /><ul>";
            foreach ($aFailedTemplates as $sFailedTemplate)
            {
              echo "<li>{$sFailedTemplate}</li>";
            }
            echo "</ul>Please move these templates manually after the upgrade has finished.<br />";
        }
        // close directory
        closedir($myDirectory);

    }
    if ($oldversion < 149)
    {
        $CI->dbforge->add_field('id');
        $fields = array(
            'sid' => array(
                             'type' => 'INT',
                          ),
            'parameter' => array(
                             'type' => 'VARCHAR',
                             'constraint' => '50',
                              ),
            'targetsqid' => array(
                             'type' => 'INT',
                             'null' => TRUE,
                              ),
            'targetsqid' => array(
                             'type' => 'INT',
                             'null' => TRUE,
                              ),
        );
        $CI->dbforge->add_field($fields);
        $CI->dbforge->create_table('survey_url_parameters',TRUE);
    }
}

function upgrade_question_attributes148()
{
    global $modifyoutput, $connect, $dbprefix;
    $sSurveyQuery = "SELECT sid FROM {$dbprefix}surveys";
    $oSurveyResult = db_execute_assoc($sSurveyQuery);
    foreach ( $oSurveyResult->result_array()  as $aSurveyRow)
    {
        $surveyid=$aSurveyRow['sid'];
        $languages=array_merge(array(GetBaseLanguageFromSurveyID($surveyid)), GetAdditionalLanguagesFromSurveyID($surveyid));

        $sAttributeQuery = "select q.qid,attribute,value from {$dbprefix}question_attributes qa , {$dbprefix}questions q where q.qid=qa.qid and sid={$surveyid}";
        $oAttributeResult = db_execute_assoc($sAttributeQuery);
        $aAllAttributes=questionAttributes(true);
        foreach ( $oAttributeResult->result_array() as $aAttributeRow)
        {
            if (isset($aAllAttributes[$aAttributeRow['attribute']]['i18n']) && $aAllAttributes[$aAttributeRow['attribute']]['i18n'])
            {
                $connect->query("delete from {$dbprefix}question_attributes where qid={$aAttributeRow['qid']} and attribute='{$aAttributeRow['attribute']}'");
                foreach ($languages as $language)
                {
                    $sAttributeInsertQuery="insert into {$dbprefix}question_attributes (qid,attribute,value,language) VALUES({$aAttributeRow['qid']},'{$aAttributeRow['attribute']}','{$aAttributeRow['value']}','{$language}' )";
                    modify_database("",$sAttributeInsertQuery); echo $modifyoutput; flush();@ob_flush();
                }
            }
        }
    }
}

function upgrade_survey_table145()
{
    $CI =& get_instance();
    global $modifyoutput, $connect;
    $sSurveyQuery = "SELECT * FROM {$CI->db->dbprefix}surveys where notification<>'0'";
    $oSurveyResult = db_execute_assoc($sSurveyQuery);
    foreach ( $oSurveyResult->result_array() as $aSurveyRow )
    {
        if ($aSurveyRow['notification']=='1' && trim($aSurveyRow['adminemail'])!='')
        {
            $aEmailAddresses=explode(';',$aSurveyRow['adminemail']);
            $sAdminEmailAddress=$aEmailAddresses[0];
            $sEmailnNotificationAddresses=implode(';',$aEmailAddresses);
            $sSurveyUpdateQuery= "update ".db_table_name('surveys')." set adminemail='{$sAdminEmailAddress}', emailnotificationto='{$sEmailnNotificationAddresses}' where sid=".$aSurveyRow['sid'];
            $connect->execute($sSurveyUpdateQuery);
        }
        else
        {
            $aEmailAddresses=explode(';',$aSurveyRow['adminemail']);
            $sAdminEmailAddress=$aEmailAddresses[0];
            $sEmailDetailedNotificationAddresses=implode(';',$aEmailAddresses);
            if (trim($aSurveyRow['emailresponseto'])!='')
            {
                $sEmailDetailedNotificationAddresses=$sEmailDetailedNotificationAddresses.';'.trim($aSurveyRow['emailresponseto']);
            }
            $sSurveyUpdateQuery= "update {$CI->db->dbprefix}surveys set adminemail='{$sAdminEmailAddress}', emailnotificationto='{$sEmailDetailedNotificationAddresses}' where sid=".$aSurveyRow['sid'];
            $connect->execute($sSurveyUpdateQuery);
        }
    }
    $sSurveyQuery = "SELECT * FROM {$CI->db->dbprefix}surveys_languagesettings";
    $oSurveyResult = db_execute_assoc($sSurveyQuery);
    foreach ( $oSurveyResult->result_array() as $aSurveyRow )
    {
        $CI->load->library('Limesurvey_lang',array("langcode"=>$aSurveyRow['surveyls_language']));
        $oLanguage = $CI->limesurvey_lang;
        $aDefaultTexts=aTemplateDefaultTexts($oLanguage,'unescaped');
        unset($oLanguage);
        $aDefaultTexts['admin_detailed_notification_subject']=$aDefaultTexts['admin_detailed_notification'].$aDefaultTexts['admin_detailed_notification_css'];
        $sSurveyUpdateQuery= "update {$CI->db->dbprefix}surveys_languagesettings set
                              email_admin_responses_subj=".$CI->db->escape($aDefaultTexts['admin_detailed_notification_subject']).",
                              email_admin_responses=".$CI->db->escape($aDefaultTexts['admin_detailed_notification']).",
                              email_admin_notification_subj=".$CI->db->escape($aDefaultTexts['admin_notification_subject']).",
                              email_admin_notification=".$CI->db->escape($aDefaultTexts['admin_notification'])."
                              where surveyls_survey_id=".$aSurveyRow['surveyls_survey_id'];
        $connect->execute($sSurveyUpdateQuery);
    }

}


function upgrade_surveypermissions_table145()
{
    $CI =& get_instance();
    global $modifyoutput, $connect;
    $sPermissionQuery = "SELECT * FROM {$CI->db->dbprefix}surveys_rights";
    $oPermissionResult = db_execute_assoc($sPermissionQuery);
    if (!$oPermissionResult) {return "Database Error";}
    else
    {
        $tablename=$CI->db->dbprefix.'survey_permissions';
        foreach ( $oPermissionResult->result_array() as $aPermissionRow )
        {

            $sPermissionInsertQuery=$connect->GetInsertSQL($tablename,array('permission'=>'assessments',
                                                                            'create_p'=>$aPermissionRow['define_questions'],
                                                                            'read_p'=>$aPermissionRow['define_questions'],
                                                                            'update_p'=>$aPermissionRow['define_questions'],
                                                                            'delete_p'=>$aPermissionRow['define_questions'],
                                                                            'sid'=>$aPermissionRow['sid'],
                                                                            'uid'=>$aPermissionRow['uid']));
            modify_database("",$sPermissionInsertQuery); echo $modifyoutput; flush();@ob_flush();

            $sPermissionInsertQuery=$connect->GetInsertSQL($tablename,array('permission'=>'quotas',
                                                                            'create_p'=>$aPermissionRow['define_questions'],
                                                                            'read_p'=>$aPermissionRow['define_questions'],
                                                                            'update_p'=>$aPermissionRow['define_questions'],
                                                                            'delete_p'=>$aPermissionRow['define_questions'],
                                                                            'sid'=>$aPermissionRow['sid'],
                                                                            'uid'=>$aPermissionRow['uid']));
            modify_database("",$sPermissionInsertQuery); echo $modifyoutput; flush();@ob_flush();

            $sPermissionInsertQuery=$connect->GetInsertSQL($tablename,array('permission'=>'responses',
                                                                            'create_p'=>$aPermissionRow['browse_response'],
                                                                            'read_p'=>$aPermissionRow['browse_response'],
                                                                            'update_p'=>$aPermissionRow['browse_response'],
                                                                            'delete_p'=>$aPermissionRow['delete_survey'],
                                                                            'export_p'=>$aPermissionRow['export'],
                                                                            'import_p'=>$aPermissionRow['browse_response'],
                                                                            'sid'=>$aPermissionRow['sid'],
                                                                            'uid'=>$aPermissionRow['uid']));
            modify_database("",$sPermissionInsertQuery); echo $modifyoutput; flush();@ob_flush();

            $sPermissionInsertQuery=$connect->GetInsertSQL($tablename,array('permission'=>'statistics',
                                                                            'read_p'=>$aPermissionRow['browse_response'],
                                                                            'sid'=>$aPermissionRow['sid'],
                                                                            'uid'=>$aPermissionRow['uid']));
            modify_database("",$sPermissionInsertQuery); echo $modifyoutput; flush();@ob_flush();

            $sPermissionInsertQuery=$connect->GetInsertSQL($tablename,array('permission'=>'survey',
                                                                            'read_p'=>1,
                                                                            'delete_p'=>$aPermissionRow['delete_survey'],
                                                                            'sid'=>$aPermissionRow['sid'],
                                                                            'uid'=>$aPermissionRow['uid']));
            modify_database("",$sPermissionInsertQuery); echo $modifyoutput; flush();@ob_flush();

            $sPermissionInsertQuery=$connect->GetInsertSQL($tablename,array('permission'=>'surveyactivation',
                                                                            'update_p'=>$aPermissionRow['activate_survey'],
                                                                            'sid'=>$aPermissionRow['sid'],
                                                                            'uid'=>$aPermissionRow['uid']));
            modify_database("",$sPermissionInsertQuery); echo $modifyoutput; flush();@ob_flush();

            $sPermissionInsertQuery=$connect->GetInsertSQL($tablename,array('permission'=>'surveycontent',
                                                                            'create_p'=>$aPermissionRow['define_questions'],
                                                                            'read_p'=>$aPermissionRow['define_questions'],
                                                                            'update_p'=>$aPermissionRow['define_questions'],
                                                                            'delete_p'=>$aPermissionRow['define_questions'],
                                                                            'export_p'=>$aPermissionRow['export'],
                                                                            'import_p'=>$aPermissionRow['define_questions'],
                                                                            'sid'=>$aPermissionRow['sid'],
                                                                            'uid'=>$aPermissionRow['uid']));
            modify_database("",$sPermissionInsertQuery); echo $modifyoutput; flush();@ob_flush();

            $sPermissionInsertQuery=$connect->GetInsertSQL($tablename,array('permission'=>'surveylocale',
                                                                            'read_p'=>$aPermissionRow['edit_survey_property'],
                                                                            'update_p'=>$aPermissionRow['edit_survey_property'],
                                                                            'sid'=>$aPermissionRow['sid'],
                                                                            'uid'=>$aPermissionRow['uid']));
            modify_database("",$sPermissionInsertQuery); echo $modifyoutput; flush();@ob_flush();

            $sPermissionInsertQuery=$connect->GetInsertSQL($tablename,array('permission'=>'surveysettings',
                                                                            'read_p'=>$aPermissionRow['edit_survey_property'],
                                                                            'update_p'=>$aPermissionRow['edit_survey_property'],
                                                                            'sid'=>$aPermissionRow['sid'],
                                                                            'uid'=>$aPermissionRow['uid']));
            modify_database("",$sPermissionInsertQuery); echo $modifyoutput; flush();@ob_flush();

            $sPermissionInsertQuery=$connect->GetInsertSQL($tablename,array('permission'=>'tokens',
                                                                            'create_p'=>$aPermissionRow['activate_survey'],
                                                                            'read_p'=>$aPermissionRow['activate_survey'],
                                                                            'update_p'=>$aPermissionRow['activate_survey'],
                                                                            'delete_p'=>$aPermissionRow['activate_survey'],
                                                                            'export_p'=>$aPermissionRow['export'],
                                                                            'import_p'=>$aPermissionRow['activate_survey'],
                                                                            'sid'=>$aPermissionRow['sid'],
                                                                            'uid'=>$aPermissionRow['uid'])
                                                          );
            modify_database("",$sPermissionInsertQuery); echo $modifyoutput; flush();@ob_flush();
        }
    }
}
