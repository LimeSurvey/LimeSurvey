<?php

/*
 * LimeSurvey
 * Copyright (C) 2007-2011 The LimeSurvey Project Team / Carsten Schmitz
 * All rights reserved.
 * License: GNU/GPL License v2 or later, see LICENSE.php
 * LimeSurvey is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 *
 *	$Id: Admin_Controller.php 11256 2011-10-25 13:52:18Z c_schmitz $
 */

/**
 * Survey Common Action
 *
 * This controller contains common functions for survey related views.
 *
 * @package		LimeSurvey
 * @subpackage	Backend
 * @author		Shitiz Garg
 */
class Survey_Common_Action extends CAction
{

    /**
     * Routes the action into correct subaction
     *
     * @access protected
     * @param string $sa
     * @param array $get_vars
     * @return void
     */
    protected function route($sa, array $get_vars)
    {
        $func_args = array();
        foreach ($get_vars as $k => $var)
            if (isset($_GET[$var]))
                $func_args[$k] = $_GET[$var];

        return call_user_func_array(array($this, $sa), $func_args);
    }

    /**
     * Renders template(s) wrapped in header and footer
     *
     * @param string $sAction Current action, the folder to fetch views from
     * @param string|array $aViewUrls View url(s)
     * @param array $aData Data to be passed on. Optional.
     */
    function _renderWrappedTemplate($sAction = '', $aViewUrls = array(), $aData = array())
    {
        // Gather the data
        $aData['clang'] = $clang = $this->getController()->lang;
        $aViewUrls = (array) $aViewUrls;
        $sViewPath = '/admin/';

        if (!empty($sAction))
        {
            $sViewPath .= $sAction . '/';
        }

        // Header
        $this->getController()->_getAdminHeader();

        // Menu bars
        if (!isset($aData['display']['menu_bars']) || $aData['display']['menu_bars'] !== false)
        {
            $this->getController()->_showadminmenu(!empty($aData['surveyid']) ? $aData['surveyid'] : null);

            if (!empty($aData['surveyid']))
            {
                $this->_surveybar($aData['surveyid'], !empty($aData['gid']) ? $aData['gid'] : null);

                if (!empty($aData['display']['menu_bars']['surveysummary']))
                {
                    $this->_surveysummary($aData['surveyid'], $aData['display']['menu_bars']['surveysummary']);
                }

                if (!empty($aData['gid']))
                {
                    $this->_questiongroupbar($aData['surveyid'], $aData['gid'], !empty($aData['qid']) ? $aData['qid'] : null, $aData['display']['menu_bars']['gid_action']);
                }
            }
        }

        unset($aData['display']);

        // Load views
        foreach ($aViewUrls as $sViewKey => $viewUrl)
        {
            if (empty($sViewKey) || is_numeric($sViewKey))
            {
                $this->getController()->render($sViewPath . $viewUrl, $aData);
            }
            else
            {
                switch ($sViewKey)
                {
                    // Message
                    case 'message' :
                        if (empty($viewUrl['class']))
                        {
                            $this->getController()->_showMessageBox($viewUrl['title'], $viewUrl['message']);
                        }
                        else
                        {
                            $this->getController()->_showMessageBox($viewUrl['title'], $viewUrl['message'], $viewUrl['class']);
                        }
                        break;

                     // Output
                    case 'output' :
                        echo $viewUrl;
                        break;
                }
            }
        }

        // Footer
        $this->getController()->_getAdminFooter('http://docs.limesurvey.org', $clang->gT('LimeSurvey online manual'));
    }

    /**
     * Shows admin menu for question
     * @param int Survey id
     * @param int Group id
     * @param int Question id
     * @param string action
     */
    function _questionbar($surveyid, $gid, $qid, $action)
    {
        $clang = $this->getController()->lang;


        $baselang = GetBaseLanguageFromSurveyID($surveyid);

        //Show Question Details
        //Count answer-options for this question
        $qrr = Answers::model()->findAllByAttributes(array('qid' => $qid, 'language' => $baselang));

        $data['qct'] = $qct = count($qrr);

        //Count sub-questions for this question
        $sqrq = Questions::model()->findAllByAttributes(array('qid' => $qid, 'language' => $baselang));
        $data['sqct'] = $sqct = count($sqrq);

        $qrresult = Questions::model()->findAllByAttributes(array('qid' => $qid, 'gid' => $gid, 'sid' => $surveyid, 'language' => $baselang));

        $questionsummary = "<div class='menubar'>\n";

        // Check if other questions in the Survey are dependent upon this question
        $condarray = GetQuestDepsForConditions($surveyid, "all", "all", $qid, "by-targqid", "outsidegroup");

        $sumresult1 = Survey::model()->findByPk($surveyid);
        if (is_null($sumresult1))
        {
            die('Invalid survey id');
        } //  if surveyid is invalid then die to prevent errors at a later time
        $surveyinfo = $sumresult1->attributes;

        $surveyinfo = array_map('FlattenText', $surveyinfo);
        $data['activated'] = $surveyinfo['active'];

        foreach ($qrresult as $qrrow)
        {
            $qrrow = $qrrow->attributes;
            $qrrow = array_map('FlattenText', $qrrow);
            if (bHasSurveyPermission($surveyid, 'surveycontent', 'read'))
            {
                if (count(GetAdditionalLanguagesFromSurveyID($surveyid)) == 0)
                {

                }
                else
                {
                    Yii::app()->loadHelper('surveytranslator');
                    $tmp_survlangs = GetAdditionalLanguagesFromSurveyID($surveyid);
                    $baselang = GetBaseLanguageFromSurveyID($surveyid);
                    $tmp_survlangs[] = $baselang;
                    rsort($tmp_survlangs);
                    $data['tmp_survlangs'] = $tmp_survlangs;
                }
            }
            $data['qtypes'] = $qtypes = getqtypelist('', 'array');
            if ($action == 'editansweroptions' || $action == "editsubquestions" || $action == "editquestion" || $action == "editdefaultvalues" || $action == "copyquestion")
            {
                $qshowstyle = "style='display: none'";
            }
            else
            {
                $qshowstyle = "";
            }
            $data['qshowstyle'] = $qshowstyle;
            $data['action'] = $action;
            $data['surveyid'] = $surveyid;
            $data['qid'] = $qid;
            $data['gid'] = $gid;
            $data['clang'] = $clang;
            $data['qrrow'] = $qrrow;
            $data['baselang'] = $baselang;
            $aAttributesWithValues = Questions::model()->getAdvancedSettingsWithValues($qid, $qrrow['type'], $surveyid, $baselang);
            $DisplayArray = array();
            foreach ($aAttributesWithValues as $aAttribute)
            {
                if (($aAttribute['i18n'] == false && isset($aAttribute['value']) && $aAttribute['value'] != $aAttribute['default']) || ($aAttribute['i18n'] == true && isset($aAttribute['value'][$baselang]) && $aAttribute['value'][$baselang] != $aAttribute['default']))
                {
                    if ($aAttribute['inputtype'] == 'singleselect')
                    {
                        $aAttribute['value'] = $aAttribute['options'][$aAttribute['value']];
                    }
                    /*
                      if ($aAttribute['name']=='relevance')
                      {
                      $sRelevance = $aAttribute['value'];
                      if ($sRelevance !== '' && $sRelevance !== '1' && $sRelevance !== '0')
                      {
                      LimeExpressionManager::ProcessString("{" . $sRelevance . "}");    // tests Relevance equation so can pretty-print it
                      $aAttribute['value']= LimeExpressionManager::GetLastPrettyPrintExpression();
                      }
                      }
                     */
                    $DisplayArray[] = $aAttribute;
                }
            }
            if (is_null($qrrow['relevance']) || trim($qrrow['relevance']) == '')
            {
                $data['relevance'] = 1;
            }
            else
            {
                LimeExpressionManager::ProcessString("{" . $qrrow['relevance'] . "}", $data['qid']);    // tests Relevance equation so can pretty-print it
                $data['relevance'] = LimeExpressionManager::GetLastPrettyPrintExpression();
            }
            $data['advancedsettings'] = $DisplayArray;
            $data['condarray'] = $condarray;
            $questionsummary .= $this->getController()->render("/admin/survey/Question/questionbar_view", $data, true);
        }
        $finaldata['display'] = $questionsummary;

        $this->getController()->render('/survey_view', $finaldata);
    }

    /**
     * Shows admin menu for question groups
     * @param int Survey id
     * @param int Group id
     */
    function _questiongroupbar($surveyid, $gid, $qid=null, $action)
    {
        $clang = $this->getController()->lang;
        $baselang = GetBaseLanguageFromSurveyID($surveyid);

        Yii::app()->loadHelper('replacements');
        // TODO: check that surveyid and thus baselang are always set here
        $sumresult4 = Questions::model()->findAllByAttributes(array('sid' => $surveyid, 'gid' => $gid, 'language' => $baselang));
        $sumcount4 = count($sumresult4);

        $grpresult = Groups::model()->findAllByAttributes(array('gid' => $gid, 'language' => $baselang));

        // Check if other questions/groups are dependent upon this group
        $condarray = GetGroupDepsForConditions($surveyid, "all", $gid, "by-targgid");

        $groupsummary = "<div class='menubar'>\n"
        . "<div class='menubar-title ui-widget-header'>\n";

        //$sumquery1 = "SELECT * FROM ".db_table_name('surveys')." inner join ".db_table_name('surveys_languagesettings')." on (surveyls_survey_id=sid and surveyls_language=language) WHERE sid=$surveyid"; //Getting data for this survey
        $sumresult1 = Survey::model()->with('languagesettings')->findByPk($surveyid); //$sumquery1, 1) ; //Checked //  if surveyid is invalid then die to prevent errors at a later time
        $surveyinfo = $sumresult1->attributes;
        $surveyinfo = array_merge($surveyinfo, $sumresult1->languagesettings->attributes);
        $surveyinfo = array_map('FlattenText', $surveyinfo);
        //$surveyinfo = array_map('htmlspecialchars', $surveyinfo);
        $data['activated'] = $activated = $surveyinfo['active'];

        foreach ($grpresult as $grow)
        {
            $grow = $grow->attributes;

            $grow = array_map('FlattenText', $grow);
            $data = array();
            $data['activated'] = $activated;
            $data['qid'] = $qid;
            $data['QidPrev'] = $QidPrev = getQidPrevious($surveyid, $gid, $qid);
            $data['QidNext'] = $QidNext = getQidNext($surveyid, $gid, $qid);

            if ($action == 'editgroup' || $action == 'addquestion' || $action == 'viewquestion' || $action == "editdefaultvalues")
            {
                $gshowstyle = "style='display: none'";
            }
            else
            {
                $gshowstyle = "";
            }

            $data['gshowstyle'] = $gshowstyle;
            $data['surveyid'] = $surveyid;
            $data['gid'] = $gid;
            $data['grow'] = $grow;
            $data['clang'] = $clang;
            $data['condarray'] = $condarray;
            $data['sumcount4'] = $sumcount4;

            $groupsummary .= $this->getController()->render('/admin/survey/QuestionGroups/questiongroupbar_view', $data, true);
        }
        $groupsummary .= "\n</table>\n";

        $finaldata['display'] = $groupsummary;
        $this->getController()->render('/survey_view', $finaldata);
    }

    /**
     * Shows admin menu for surveys
     * @param int Survey id
     */
    function _surveybar($surveyid, $gid=null)
    {
        //$this->load->helper('surveytranslator');
        $clang = $this->getController()->lang;
        //echo Yii::app()->getConfig('gid');
        $baselang = GetBaseLanguageFromSurveyID($surveyid);
        $condition = array('sid' => $surveyid, 'language' => $baselang);

        //$sumquery1 = "SELECT * FROM ".db_table_name('surveys')." inner join ".db_table_name('surveys_languagesettings')." on (surveyls_survey_id=sid and surveyls_language=language) WHERE sid=$surveyid"; //Getting data for this survey
        $sumresult1 = Survey::model()->with('languagesettings')->findByPk($surveyid); //$sumquery1, 1) ; //Checked
        if (is_null($sumresult1))
        {
            die('Invalid survey id');
        } //  if surveyid is invalid then die to prevent errors at a later time
        $surveyinfo = $sumresult1->attributes;
        $surveyinfo = array_merge($surveyinfo, $sumresult1->languagesettings->attributes);
        $surveyinfo = array_map('FlattenText', $surveyinfo);
        //$surveyinfo = array_map('htmlspecialchars', $surveyinfo);
        $activated = ($surveyinfo['active'] == 'Y');

        $js_admin_includes = Yii::app()->getConfig("js_admin_includes");
        $js_admin_includes[] = Yii::app()->getConfig('generalscripts') . 'jquery/jquery.coookie.js';
        $js_admin_includes[] = Yii::app()->getConfig('generalscripts') . 'jquery/superfish.js';
        $js_admin_includes[] = Yii::app()->getConfig('generalscripts') . 'jquery/hoverIntent.js';
        $js_admin_includes[] = Yii::app()->getConfig('adminscripts') . 'surveytoolbar.js';
        $this->controller->_css_admin_includes(Yii::app()->getConfig('styleurl') . "admin/default/superfish.css");

        Yii::app()->setConfig("js_admin_includes", $js_admin_includes);

        //Parse data to send to view
        $data['clang'] = $clang;
        $data['surveyinfo'] = $surveyinfo;
        $data['surveyid'] = $surveyid;

        // ACTIVATE SURVEY BUTTON
        $data['activated'] = $activated;
        $data['imageurl'] = Yii::app()->getConfig('imageurl');

        $condition = array('sid' => $surveyid, 'parent_qid' => 0, 'language' => $baselang);

        //$sumquery3 =  "SELECT * FROM ".db_table_name('questions')." WHERE sid={$surveyid} AND parent_qid=0 AND language='".$baselang."'"; //Getting a count of questions for this survey
        $sumresult3 = Questions::model()->findAllByAttributes($condition); //Checked
        $sumcount3 = count($sumresult3);

        $data['canactivate'] = $sumcount3 > 0 && bHasSurveyPermission($surveyid, 'surveyactivation', 'update');
        $data['candeactivate'] = bHasSurveyPermission($surveyid, 'surveyactivation', 'update');
        $data['expired'] = $surveyinfo['expires'] != '' && ($surveyinfo['expires'] < date_shift(date("Y-m-d H:i:s"), "Y-m-d H:i", Yii::app()->getConfig('timeadjust')));
        $data['notstarted'] = ($surveyinfo['startdate'] != '') && ($surveyinfo['startdate'] > date_shift(date("Y-m-d H:i:s"), "Y-m-d H:i", Yii::app()->getConfig('timeadjust')));

        // Start of suckerfish menu
        // TEST BUTTON
        if (!$activated)
        {
            $data['icontext'] = $clang->gT("Test This Survey");
            $data['icontext2'] = $clang->gTview("Test This Survey");
        }
        else
        {
            $data['icontext'] = $clang->gT("Execute This Survey");
            $data['icontext2'] = $clang->gTview("Execute This Survey");
        }

        $data['baselang'] = GetBaseLanguageFromSurveyID($surveyid);
        $data['onelanguage'] = (count(GetAdditionalLanguagesFromSurveyID($surveyid)) == 0);

        $tmp_survlangs = GetAdditionalLanguagesFromSurveyID($surveyid);
        $data['additionallanguages'] = $tmp_survlangs;
        $tmp_survlangs[] = $data['baselang'];
        rsort($tmp_survlangs);
        $data['languagelist'] = $tmp_survlangs;

        $data['hasadditionallanguages'] = (count($data['additionallanguages']) > 0);

        // EDIT SURVEY TEXT ELEMENTS BUTTON
        $data['surveylocale'] = bHasSurveyPermission($surveyid, 'surveylocale', 'read');
        // EDIT SURVEY SETTINGS BUTTON
        $data['surveysettings'] = bHasSurveyPermission($surveyid, 'surveysettings', 'read');
        // Survey permission item
        $data['surveysecurity'] = (Yii::app()->session['USER_RIGHT_SUPERADMIN'] == 1 || $surveyinfo['owner_id'] == Yii::app()->session['loginID']);
        // CHANGE QUESTION GROUP ORDER BUTTON
        $data['surveycontent'] = bHasSurveyPermission($surveyid, 'surveycontent', 'read');
        $data['groupsum'] = (getGroupSum($surveyid, $surveyinfo['language']) > 1);
        // SET SURVEY QUOTAS BUTTON
        $data['quotas'] = bHasSurveyPermission($surveyid, 'quotas', 'read');
        // Assessment menu item
        $data['assessments'] = bHasSurveyPermission($surveyid, 'assessments', 'read');
        // EDIT SURVEY TEXT ELEMENTS BUTTON
        // End if survey properties
        // Tools menu item
        // Delete survey item
        $data['surveydelete'] = bHasSurveyPermission($surveyid, 'survey', 'delete');
        // Translate survey item
        $data['surveytranslate'] = bHasSurveyPermission($surveyid, 'translations', 'read');
        // RESET SURVEY LOGIC BUTTON
        //$sumquery6 = "SELECT count(*) FROM ".db_table_name('conditions')." as c, ".db_table_name('questions')." as q WHERE c.qid = q.qid AND q.sid=$surveyid"; //Getting a count of conditions for this survey
        // TMSW Conditions->Relevance:  How is conditionscount used?  Should Relevance do the same?

        $query = count(Conditions::model()->findAllByAttributes(array('qid' => $surveyid)));
        $sumcount6 = $query; //Checked
        $data['surveycontent'] = bHasSurveyPermission($surveyid, 'surveycontent', 'update');
        $data['conditionscount'] = ($sumcount6 > 0);
        // Eport menu item
        $data['surveyexport'] = bHasSurveyPermission($surveyid, 'surveycontent', 'export');
        // PRINTABLE VERSION OF SURVEY BUTTON
        // SHOW PRINTABLE AND SCANNABLE VERSION OF SURVEY BUTTON
        //browse responses menu item
        $data['respstatsread'] = bHasSurveyPermission($surveyid, 'responses', 'read') || bHasSurveyPermission($surveyid, 'statistics', 'read') || bHasSurveyPermission($surveyid, 'responses', 'export');
        // Data entry screen menu item
        $data['responsescreate'] = bHasSurveyPermission($surveyid, 'responses', 'create');
        $data['responsesread'] = bHasSurveyPermission($surveyid, 'responses', 'read');
        // TOKEN MANAGEMENT BUTTON
        $data['tokenmanagement'] = bHasSurveyPermission($surveyid, 'surveysettings', 'update') || bHasSurveyPermission($surveyid, 'tokens', 'read');

        $data['gid'] = $gid; // = $this->input->post('gid');

        if (bHasSurveyPermission($surveyid, 'surveycontent', 'read'))
        {
            $data['permission'] = true;
        }
        else
        {
            $data['gid'] = $gid = null;
            $qid = null;
            $data['permission'] = false;
        }

        if (getgrouplistlang($gid, $baselang, $surveyid))
        {
            $data['groups'] = getgrouplistlang($gid, $baselang, $surveyid);
        }
        else
        {
            $data['groups'] = "<option>" . $clang->gT("None") . "</option>";
        }

        $data['GidPrev'] = $GidPrev = getGidPrevious($surveyid, $gid);

        $data['GidNext'] = $GidNext = getGidNext($surveyid, $gid);

        $this->getController()->render("/admin/survey/surveybar_view", $data);
    }

    /**
     * Show survey summary
     * @param int Survey id
     * @param string Action to be performed
     */
    function _surveysummary($surveyid, $action=null)
    {
        $clang = $this->getController()->lang;

        $baselang = GetBaseLanguageFromSurveyID($surveyid);
        $condition = array('sid' => $surveyid, 'language' => $baselang);

        $sumresult1 = Survey::model()->with('languagesettings')->findByPk($surveyid); //$sumquery1, 1) ; //Checked
        if (is_null($sumresult1))
        {
            die('Invalid survey id');
        } //  if surveyid is invalid then die to prevent errors at a later time
        $surveyinfo = $sumresult1->attributes;
        $surveyinfo = array_merge($surveyinfo, $sumresult1->languagesettings->attributes);
        $surveyinfo = array_map('FlattenText', $surveyinfo);
        //$surveyinfo = array_map('htmlspecialchars', $surveyinfo);
        $activated = $surveyinfo['active'];

        $condition = array('sid' => $surveyid, 'parent_qid' => 0, 'language' => $baselang);

        $sumresult3 = Questions::model()->findAllByAttributes($condition); //Checked
        $sumcount3 = count($sumresult3);

        $condition = array('sid' => $surveyid, 'language' => $baselang);

        //$sumquery2 = "SELECT * FROM ".db_table_name('groups')." WHERE sid={$surveyid} AND language='".$baselang."'"; //Getting a count of groups for this survey
        $sumresult2 = Groups::model()->findAllByAttributes($condition); //Checked
        $sumcount2 = count($sumresult2);

        //SURVEY SUMMARY

        $aAdditionalLanguages = GetAdditionalLanguagesFromSurveyID($surveyid);
        $surveysummary2 = "";
        if ($surveyinfo['anonymized'] != "N")
        {
            $surveysummary2 .= $clang->gT("Responses to this survey are anonymized.") . "<br />";
        }
        else
        {
            $surveysummary2 .= $clang->gT("Responses to this survey are NOT anonymized.") . "<br />";
        }
        if ($surveyinfo['format'] == "S")
        {
            $surveysummary2 .= $clang->gT("It is presented question by question.") . "<br />";
        }
        elseif ($surveyinfo['format'] == "G")
        {
            $surveysummary2 .= $clang->gT("It is presented group by group.") . "<br />";
        }
        else
        {
            $surveysummary2 .= $clang->gT("It is presented on one single page.") . "<br />";
        }
        if ($surveyinfo['allowjumps'] == "Y")
        {
            if ($surveyinfo['format'] == 'A')
            {
                $surveysummary2 .= $clang->gT("No question index will be shown with this format.") . "<br />";
            }
            else
            {
                $surveysummary2 .= $clang->gT("A question index will be shown; participants will be able to jump between viewed questions.") . "<br />";
            }
        }
        if ($surveyinfo['datestamp'] == "Y")
        {
            $surveysummary2 .= $clang->gT("Responses will be date stamped.") . "<br />";
        }
        if ($surveyinfo['ipaddr'] == "Y")
        {
            $surveysummary2 .= $clang->gT("IP Addresses will be logged") . "<br />";
        }
        if ($surveyinfo['refurl'] == "Y")
        {
            $surveysummary2 .= $clang->gT("Referrer URL will be saved.") . "<br />";
        }
        if ($surveyinfo['usecookie'] == "Y")
        {
            $surveysummary2 .= $clang->gT("It uses cookies for access control.") . "<br />";
        }
        if ($surveyinfo['allowregister'] == "Y")
        {
            $surveysummary2 .= $clang->gT("If tokens are used, the public may register for this survey") . "<br />";
        }
        if ($surveyinfo['allowsave'] == "Y" && $surveyinfo['tokenanswerspersistence'] == 'N')
        {
            $surveysummary2 .= $clang->gT("Participants can save partially finished surveys") . "<br />\n";
        }
        if ($surveyinfo['emailnotificationto'] != '')
        {
            $surveysummary2 .= $clang->gT("Basic email notification is sent to:") . " {$surveyinfo['emailnotificationto']}<br />\n";
        }
        if ($surveyinfo['emailresponseto'] != '')
        {
            $surveysummary2 .= $clang->gT("Detailed email notification with response data is sent to:") . " {$surveyinfo['emailresponseto']}<br />\n";
        }

        if (bHasSurveyPermission($surveyid, 'surveycontent', 'update'))
        {
            $surveysummary2 .= $clang->gT("Regenerate question codes:")
            . " [<a href='#' "
            . "onclick=\"if (confirm('" . $clang->gT("Are you sure you want regenerate the question codes?", "js") . "')) { " . Yii::app()->baseUrl . "?action=renumberquestions&amp;sid=$surveyid&amp;style=straight" . "}\" "
            . ">" . $clang->gT("Straight") . "</a>] "
            . " [<a href='#' "
            . "onclick=\"if (confirm('" . $clang->gT("Are you sure you want regenerate the question codes?", "js") . "')) { " . Yii::app()->baseUrl . "?action=renumberquestions&amp;sid=$surveyid&amp;style=bygroup" . "}\" "
            . ">" . $clang->gT("By Group") . "</a>]";
        }

        $dateformatdetails = getDateFormatData(Yii::app()->session['dateformat']);
        if (trim($surveyinfo['startdate']) != '')
        {
            Yii::import('application.libraries.Date_Time_Converter');
            $datetimeobj = new Date_Time_Converter($surveyinfo['startdate'], 'Y-m-d H:i:s');
            $data['startdate'] = $datetimeobj->convert($dateformatdetails['phpdate'] . ' H:i');
        }
        else
        {
            $data['startdate'] = "-";
        }

        if (trim($surveyinfo['expires']) != '')
        {
            //$constructoritems = array($surveyinfo['expires'] , "Y-m-d H:i:s");
            Yii::import('application.libraries.Date_Time_Converter');
            $datetimeobj = new Date_Time_Converter($surveyinfo['expires'], 'Y-m-d H:i:s');
            //$datetimeobj = new Date_Time_Converter($surveyinfo['expires'] , "Y-m-d H:i:s");
            $data['expdate'] = $datetimeobj->convert($dateformatdetails['phpdate'] . ' H:i');
        }
        else
        {
            $data['expdate'] = "-";
        }

        if (!$surveyinfo['language'])
        {
            $data['language'] = getLanguageNameFromCode($currentadminlang, false);
        }
        else
        {
            $data['language'] = getLanguageNameFromCode($surveyinfo['language'], false);
        }

        // get the rowspan of the Additionnal languages row
        // is at least 1 even if no additionnal language is present
        $additionnalLanguagesCount = count($aAdditionalLanguages);
        $first = true;
        $data['additionnalLanguages'] = "";
        if ($additionnalLanguagesCount == 0)
        {
            $data['additionnalLanguages'] .= "<td align='left'>-</td>\n";
        }
        else
        {
            foreach ($aAdditionalLanguages as $langname)
            {
                if ($langname)
                {
                    if (!$first)
                    {
                        $data['additionnalLanguages'].= "<tr><td>&nbsp;</td>";
                    }
                    $first = false;
                    $data['additionnalLanguages'] .= "<td align='left'>" . getLanguageNameFromCode($langname, false) . "</td></tr>\n";
                }
            }
        }
        if ($first)
            $data['additionnalLanguages'] .= "</tr>";

        if ($surveyinfo['surveyls_urldescription'] == "")
        {
            $surveyinfo['surveyls_urldescription'] = htmlspecialchars($surveyinfo['surveyls_url']);
        }

        if ($surveyinfo['surveyls_url'] != "")
        {
            $data['endurl'] = " <a target='_blank' href=\"" . htmlspecialchars($surveyinfo['surveyls_url']) . "\" title=\"" . htmlspecialchars($surveyinfo['surveyls_url']) . "\">{$surveyinfo['surveyls_urldescription']}</a>";
        }
        else
        {
            $data['endurl'] = "-";
        }

        $data['sumcount3'] = $sumcount3;
        $data['sumcount2'] = $sumcount2;

        if ($activated == "N")
        {
            $data['activatedlang'] = $clang->gT("No");
        }
        else
        {
            $data['activatedlang'] = $clang->gT("Yes");
        }

        $data['activated'] = $activated;
        if ($activated == "Y")
        {
            $data['surveydb'] = Yii::app()->db->tablePrefix . "survey_" . $surveyid;
        }
        $data['warnings'] = "";
        if ($activated == "N" && $sumcount3 == 0)
        {
            $data['warnings'] = $clang->gT("Survey cannot be activated yet.") . "<br />\n";
            if ($sumcount2 == 0 && bHasSurveyPermission($surveyid, 'surveycontent', 'create'))
            {
                $data['warnings'] .= "<span class='statusentryhighlight'>[" . $clang->gT("You need to add question groups") . "]</span><br />";
            }
            if ($sumcount3 == 0 && bHasSurveyPermission($surveyid, 'surveycontent', 'create'))
            {
                $data['warnings'] .= "<span class='statusentryhighlight'>[" . $clang->gT("You need to add questions") . "]</span><br />";
            }
        }
        $data['hints'] = $surveysummary2;

        //return (array('column'=>array($columns_used,$hard_limit) , 'size' => array($length, $size_limit) ));
//        $data['tableusage'] = get_dbtableusage($surveyid);
// ToDo: Table usage is calculated on every menu display which is too slow with bug surveys.
// Needs to be moved to a database field and only updated if there are question/subquestions added/removed (it's currently also not functional due to the port)
//
        $data['tableusage'] = false;

        //$gid || $qid ||


        if ($action == "deactivate" || $action == "activate" || $action == "surveysecurity" || $action == "editdefaultvalues" || $action == "editemailtemplates"
        || $action == "surveyrights" || $action == "addsurveysecurity" || $action == "addusergroupsurveysecurity"
        || $action == "setsurveysecurity" || $action == "setusergroupsurveysecurity" || $action == "delsurveysecurity"
        || $action == "editsurveysettings" || $action == "editsurveylocalesettings" || $action == "updatesurveysettingsandeditlocalesettings" || $action == "addgroup" || $action == "importgroup"
        || $action == "ordergroups" || $action == "deletesurvey" || $action == "resetsurveylogic"
        || $action == "importsurveyresources" || $action == "translate" || $action == "emailtemplates"
        || $action == "exportstructure" || $action == "quotas" || $action == "copysurvey" || $action == "viewgroup" || $action == "viewquestion")
        {
            $showstyle = "style='display: none'";
        }
        if (!isset($showstyle))
        {
            $showstyle = "";
        }
        /*         * if ($gid) {$showstyle="style='display: none'";}
          if (!isset($showstyle)) {$showstyle="";} */
        $data['showstyle'] = $showstyle;
        $data['aAdditionalLanguages'] = $aAdditionalLanguages;
        $data['clang'] = $clang;
        $data['surveyinfo'] = $surveyinfo;
        $this->getController()->render("/admin/survey/surveySummary_view", $data);
    }

    /**
     * Browse Menu Bar
     */
    function _browsemenubar($surveyid, $title='')
    {
        //BROWSE MENU BAR
        $data['title'] = $title;
        $data['thissurvey'] = getSurveyInfo($surveyid);
        $data['imageurl'] = Yii::app()->getConfig("imageurl");
        $data['clang'] = Yii::app()->lang;
        $data['surveyid'] = $surveyid;

        $tmp_survlangs = GetAdditionalLanguagesFromSurveyID($surveyid);
        $baselang = GetBaseLanguageFromSurveyID($surveyid);
        $tmp_survlangs[] = $baselang;
        rsort($tmp_survlangs);
        $data['tmp_survlangs'] = $tmp_survlangs;

        $this->getController()->render("/admin/browse/browsemenubar_view", $data);
    }

    function _js_admin_includes($include)
    {
        $js_admin_includes = Yii::app()->getConfig("js_admin_includes");
        $js_admin_includes[] = $include;
        Yii::app()->setConfig("js_admin_includes", $js_admin_includes);
    }

}
