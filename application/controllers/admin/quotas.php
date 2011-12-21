<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
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
 * Quotas Controller
 *
 * This controller performs quota actions
 *
 * @package		LimeSurvey
 * @subpackage	Backend
 */
class quotas extends Survey_Common_Action
{

    /**
     * Base function
     *
     * @access public
     * @return void
     */
    public function run($subaction = 'index', $surveyid = 0)
    {
        if (!bHasSurveyPermission($surveyid, 'quotas', 'read'))
        {
            die();
        }

        // Load helpers
        Yii::app()->loadHelper('surveytranslator');
        // Sanitize/get globals/variables
        $_POST['quotamax'] = sanitize_int(CHttpRequest::getPost('quotamax'));
        if (empty($_POST['autoload_url']))
        {
            $_POST['autoload_url'] = 0;
        }
        if (empty($_POST['quota_limit']) || !is_numeric(CHttpRequest::getPost('quota_limit')) || CHttpRequest::getPost('quota_limit') < 0)
        {
            $_POST['quota_limit'] = 0;
        }

        switch ($subaction)
        {
            case 'index' :
                $this->route('index', array('surveyid', 'quickreport'));
                break;
            case 'insertquota' :
            case 'modifyquota' :
            case 'insertquotaanswer' :
            case 'quota_delans' :
            case 'quota_delquota' :
            case 'quota_editquota' :
            case 'new_quota' :
                $this->route($subaction, array('surveyid'));
                break;
            case 'new_answer' :
            case 'new_answer_two' :
                $this->route('new_answer', array('surveyid', 'subaction'));
                break;
        }
    }

    private function _getData($iSurveyId)
    {
        // Set the variables in an array
        $aData['iSurveyId'] = $iSurveyId;
        $aData['clang'] = $this->getController()->lang;
        $aData['aLangs'] = GetAdditionalLanguagesFromSurveyID($iSurveyId);
        $aData['sBaseLang'] = GetBaseLanguageFromSurveyID($iSurveyId);
        array_push($aData['aLangs'], $aData['sBaseLang']);

        $aData['action'] = $action = CHttpRequest::getParam('action');
        if (!isset($action))
            $aData['action'] = 'quotas';

        return $aData;
    }

    private function _checkPermissions($iSurveyId, $sPermission)
    {
        if (!empty($sPermission) && !bHasSurveyPermission($iSurveyId, 'quotas', $sPermission)) {
            die();
        }
    }

    /**
     * Pre Quota
     *
     * @access publlic
     * @param int $iSurveyId
     * @return void
     */
    public function _displayHeader($iSurveyId)
    {
        // Insert scripts and styles
        $this->getController()->_js_admin_includes(Yii::app()->getConfig('generalscripts') . '/jquery/jquery.tablesorter.min.js');
        $this->getController()->_js_admin_includes(Yii::app()->getConfig('adminscripts') . '/quotas.js');
        $this->getController()->_css_admin_includes(Yii::app()->getConfig('styleurl') . 'admin/default/superfish.css');

        // Show the common head of the page
        $this->getController()->_getAdminHeader();
        $this->getController()->_showadminmenu();
        $this->_surveybar($iSurveyId);
    }

    function _redirectToIndex($iSurveyId)
    {
        $this->getController()->redirect($this->getController()->createUrl("/admin/quotas/surveyid/$iSurveyId"));
    }

    function index($iSurveyId, $bQuickReport = false)
    {
        $iSurveyId = sanitize_int($iSurveyId);
        $aData = $this->_getData($iSurveyId);

        if ($bQuickReport == false)
        {
            $this->_displayHeader($iSurveyId);
            $this->getController()->render("/admin/quotas/viewquotas_view", $aData);
        }

        $clang = $aData['clang'];
        $totalquotas = 0;
        $totalcompleted = 0;
        $csvoutput = array();

        $criteria = new CDbCriteria;
        $criteria->select = '*';
        $criteria->join = 'LEFT JOIN {{quota_languagesettings}} as qls ON (t.id = qls.quotals_quota_id)';
        $criteria->condition = 'sid=:survey AND quotals_language=:lang';
        $criteria->params = array(':survey' => $iSurveyId, ':lang' => $aData['sBaseLang']);
        $criteria->order = 'name';
        $aResult = Quota::model()->findAll($criteria);

        //if there are quotas let's proceed
        if (count($aResult) > 0)
        {
            //loop through all quotas
            foreach ($aResult as $aQuotaListing)
            {
                $totalquotas += $aQuotaListing['qlimit'];
                $completed = get_quotaCompletedCount($iSurveyId, $aQuotaListing['id']);
                $highlight = ($completed >= $aQuotaListing['qlimit']) ? "" : "style='color: red'"; //Incomplete quotas displayed in red
                $totalcompleted = $totalcompleted + $completed;
                $csvoutput[] = $aQuotaListing['name'] . "," . $aQuotaListing['qlimit'] . "," . $completed . "," . ($aQuotaListing['qlimit'] - $completed) . "\r\n";

                if ($bQuickReport != false)
                {
                    continue;
                }

                $aData['quotalisting'] = $aQuotaListing;
                $aData['highlight'] = $highlight;
                $aData['completed'] = $completed;
                $aData['totalquotas'] = $totalquotas;
                $aData['totalcompleted'] = $totalcompleted;
                $this->getController()->render("/admin/quotas/viewquotasrow_view", $aData);

                //check how many sub-elements exist for a certain quota
                $aResults2 = Quota_members::model()->findAllByAttributes(array('quota_id' => $aQuotaListing['id']));

                //loop through all sub-parts
                foreach ($aResults2 as $aQuotaQuestions)
                {
                    $aQuestionAnswers = self::getQuotaAnswers($aQuotaQuestions['qid'], $iSurveyId, $aQuotaListing['id']);
                    $aData['question_answers'] = $aQuestionAnswers;
                    $aData['quota_questions'] = $aQuotaQuestions;
                    $this->getController()->render('/admin/quotas/viewquotasrowsub_view', $aData);
                }
            }
        }
        else
        {
            // No quotas have been set for this survey
            $this->getController()->render('/admin/quotas/viewquotasempty_view', $aData);
        }

        $aData['totalquotas'] = $totalquotas;
        $aData['totalcompleted'] = $totalcompleted;

        if ($bQuickReport == false)
        {
            $this->getController()->render('/admin/quotas/viewquotasfooter_view', $aData);
            $this->getController()->_getAdminFooter('http://docs.limesurvey.org', $clang->gT('LimeSurvey online manual'));
        }
        else
        {
            header("Content-Disposition: attachment; filename=results-survey" . $iSurveyId . ".csv");
            header("Content-type: text/comma-separated-values; charset=UTF-8");
            header("Pragma: public");
            echo $clang->gT("Quota name") . "," . $clang->gT("Limit") . "," . $clang->gT("Completed") . "," . $clang->gT("Remaining") . "\r\n";
            foreach ($csvoutput as $line)
            {
                echo $line;
            }
            die;
        }
    }

    function insertquota($iSurveyId)
    {
        $iSurveyId = sanitize_int($iSurveyId);
        $this->_checkPermissions($iSurveyId, 'create');
        $aData = $this->_getData($iSurveyId);
        $aLangs = $aData['aLangs'];

        $oQuota = new Quota;
        $oQuota->sid = $iSurveyId;
        $oQuota->name = CHttpRequest::getPost('quota_name');
        $oQuota->qlimit = CHttpRequest::getPost('quota_limit');
        $oQuota->action = CHttpRequest::getPost('quota_action');
        $oQuota->autoload_url = CHttpRequest::getPost('autoload_url');
        $oQuota->save();
        $iQuotaId = Yii::app()->db->lastInsertID;

        //Iterate through each language, and make sure there is a quota message for it
        $sError = '';
        foreach ($aLangs as $sLang)
        {
            if (!$_POST['quotals_message_' . $sLang])
            {
                $sError .= GetLanguageNameFromCode($sLang, false) . "\\n";
            }
        }
        if ($sError != '')
        {
            $aData['sShowError'] = $sError;
        }
        else
        //All the required quota messages exist, now we can insert this info into the database
        {

            foreach ($aLangs as $sLang) //Iterate through each language
            {
                //Clean XSS - Automatically provided by CI input class
                $_POST['quotals_message_' . $sLang] = html_entity_decode($_POST['quotals_message_' . $sLang], ENT_QUOTES, "UTF-8");

                // Fix bug with FCKEditor saving strange BR types
                $_POST['quotals_message_' . $sLang] = fix_FCKeditor_text($_POST['quotals_message_' . $sLang]);

                $oQuotaLanguageSettings = new Quota_languagesettings;
                $oQuotaLanguageSettings->quotals_quota_id = $iQuotaId;
                $oQuotaLanguageSettings->quotals_language = $sLang;
                $oQuotaLanguageSettings->quotals_name = CHttpRequest::getPost('quota_name');
                $oQuotaLanguageSettings->quotals_message = $_POST['quotals_message_' . $sLang];
                $oQuotaLanguageSettings->quotals_url = $_POST['quotals_url_' . $sLang];
                $oQuotaLanguageSettings->quotals_urldescrip = $_POST['quotals_urldescrip_' . $sLang];
                $oQuotaLanguageSettings->save();
            }
        }

        self::_redirectToIndex($iSurveyId);
    }

    function modifyquota($iSurveyId)
    {
        $iSurveyId = sanitize_int($iSurveyId);
        $this->_checkPermissions($iSurveyId, 'update');
        $aData = $this->_getData($iSurveyId);
        $aLangs = $aData['aLangs'];

        $oQuota = Quota::model()->findByPk(CHttpRequest::getPost('quota_id'));
        $oQuota->name = CHttpRequest::getPost('quota_name');
        $oQuota->qlimit = CHttpRequest::getPost('quota_limit');
        $oQuota->action = CHttpRequest::getPost('quota_action');
        $oQuota->autoload_url = CHttpRequest::getPost('autoload_url');
        $oQuota->save();

        //Iterate through each language, and make sure there is a quota message for it
        $sError = '';
        foreach ($aLangs as $sLang)
        {
            if (!$_POST['quotals_message_' . $sLang])
            {
                $sError.= GetLanguageNameFromCode($sLang, false) . "\\n";
            }
        }
        if ($sError != '')
        {
            $aData['sShowError'] = $sError;
        }
        else
        //All the required quota messages exist, now we can insert this info into the database
        {

            foreach ($aLangs as $sLang) //Iterate through each language
            {
                //Clean XSS - Automatically provided by CI
                $_POST['quotals_message_' . $sLang] = html_entity_decode($_POST['quotals_message_' . $sLang], ENT_QUOTES, "UTF-8");

                // Fix bug with FCKEditor saving strange BR types
                $_POST['quotals_message_' . $sLang] = fix_FCKeditor_text($_POST['quotals_message_' . $sLang]);

                $oQuotaLanguageSettings = Quota_languagesettings::model()->findByAttributes(array('quotals_quota_id' => CHttpRequest::getPost('quota_id'), 'quotals_language' => $sLang));
                $oQuotaLanguageSettings->quotals_name = CHttpRequest::getPost('quota_name');
                $oQuotaLanguageSettings->quotals_message = $_POST['quotals_message_' . $sLang];
                $oQuotaLanguageSettings->quotals_url = $_POST['quotals_url_' . $sLang];
                $oQuotaLanguageSettings->quotals_urldescrip = $_POST['quotals_urldescrip_' . $sLang];
                $oQuotaLanguageSettings->save();
            }
        } //End insert language based components

        self::_redirectToIndex($iSurveyId);
    }

    function insertquotaanswer($iSurveyId)
    {
        $iSurveyId = sanitize_int($iSurveyId);
        $this->_checkPermissions($iSurveyId, 'create');

        $oQuotaMembers = new Quota_members;
        $oQuotaMembers->sid = $iSurveyId;
        $oQuotaMembers->qid = CHttpRequest::getPost('quota_qid');
        $oQuotaMembers->quota_id = CHttpRequest::getPost('quota_id');
        $oQuotaMembers->code = CHttpRequest::getPost('quota_anscode');
        $oQuotaMembers->save();

        if (!empty($_POST['createanother']))
        {
            $_POST['action'] = "quotas";
            $_POST['subaction'] = "new_answer";
            $sSubAction = "new_answer";
            self::new_answer($iSurveyId, $sSubAction);
        }
        else
        {
            self::_redirectToIndex($iSurveyId);
        }
    }

    function quota_delans($iSurveyId)
    {
        $iSurveyId = sanitize_int($iSurveyId);
        $this->_checkPermissions($iSurveyId, 'delete');

        Quota_members::model()->deleteAllByAttributes(array(
            'id' => CHttpRequest::getPost('quota_member_id'),
            'qid' => CHttpRequest::getPost('quota_qid'),
            'code' => CHttpRequest::getPost('quota_anscode'),
        ));

        self::_redirectToIndex($iSurveyId);
    }

    function quota_delquota($iSurveyId)
    {
        $iSurveyId = sanitize_int($iSurveyId);
        $this->_checkPermissions($iSurveyId, 'delete');

        Quota::model()->deleteByPk(CHttpRequest::getPost('quota_id'));
        Quota_languagesettings::model()->deleteAllByAttributes(array('quotals_quota_id' => CHttpRequest::getPost('quota_id')));
        Quota_members::model()->deleteAllByAttributes(array('quota_id' => CHttpRequest::getPost('quota_id')));

        self::_redirectToIndex($iSurveyId);
    }

    function quota_editquota($iSurveyId)
    {
        $iSurveyId = sanitize_int($iSurveyId);
        $this->_checkPermissions($iSurveyId, 'update');
        $aData = $this->_getData($iSurveyId);
        $aLangs = $aData['aLangs'];
        $clang = $aData['clang'];

        $aQuotaInfo = Quota::model()->findByPk(CHttpRequest::getPost('quota_id'));
        $aData['quotainfo'] = $aQuotaInfo;

        $this->getController()->render('/admin/quotas/editquota_view', $aData);

        foreach ($aLangs as $sLang)
        {
            $aData['langquotainfo'] = Quota_languagesettings::model()->findByAttributes(array('quotals_quota_id' => CHttpRequest::getPost('quota_id'), 'quotals_language' => $sLang));
            $aData['lang'] = $sLang;
            $this->getController()->render('/admin/quotas/editquotalang_view', $aData);
        }

        $this->getController()->render('/admin/quotas/editquotafooter_view', $aData);
        $this->getController()->_getAdminFooter('http://docs.limesurvey.org', $clang->gT('LimeSurvey online manual'));
    }

    function new_answer($iSurveyId, $sSubAction)
    {
        $iSurveyId = sanitize_int($iSurveyId);
        $this->_checkPermissions($iSurveyId, 'create');
        $aData = $this->_getData($iSurveyId);
        $sBaseLang = $aData['sBaseLang'];
        $clang = $aData['clang'];

        if (($sSubAction == "new_answer" || ($sSubAction == "new_answer_two" && !isset($_POST['quota_qid']))) && bHasSurveyPermission($iSurveyId, 'quotas', 'create'))
        {
            $result = Quota::model()->findAllByPk(CHttpRequest::getPost('quota_id'));
            foreach ($result as $aQuotaDetails)
            {
                $quota_name = $aQuotaDetails['name'];
            }

            $result = Questions::model()->findAllByAttributes(array('type' => array('G', 'M', 'Y', 'A', 'B', 'I', 'L', 'O', '!'), 'sid' => $iSurveyId, 'language' => $sBaseLang));
            if (empty($result))
            {
                $this->getController()->render("/admin/quotas/newanswererror_view", $aData);
            }
            else
            {
                $aData['newanswer_result'] = $result;
                $aData['quota_name'] = $quota_name;
                $this->getController()->render("/admin/quotas/newanswer_view", $aData);
            }
        }

        if ($sSubAction == "new_answer_two" && isset($_POST['quota_qid']) && bHasSurveyPermission($iSurveyId, 'quotas', 'create'))
        {
            $aResults = Quota::model()->findByPk(CHttpRequest::getPost('quota_qid'));
            $sQuotaName = $aResults['name'];

            $aQuestionAnswers = self::getQuotaAnswers(CHttpRequest::getPost('quota_qid'), $iSurveyId, CHttpRequest::getPost('quota_id'));
            $x = 0;

            foreach ($aQuestionAnswers as $aQACheck)
            {
                if (isset($aQACheck['rowexists']))
                    $x++;
            }

            reset($aQuestionAnswers);
            $aData['question_answers'] = $aQuestionAnswers;
            $aData['x'] = $x;
            $aData['quota_name'] = $sQuotaName;
            $this->getController()->render('/admin/quotas/newanswertwo_view', $aData);
        }

        $this->getController()->_getAdminFooter('http://docs.limesurvey.org', $clang->gT('LimeSurvey online manual'));
    }

    function new_quota($iSurveyId)
    {
        $iSurveyId = sanitize_int($iSurveyId);
        $this->_checkPermissions($iSurveyId, 'create');
        $aData = $this->_getData($iSurveyId);
        $clang = $aData['clang'];

        $aData['thissurvey'] = getSurveyInfo($iSurveyId);
        $aData['langs'] = $aData['sLangs'];

        $this->getController()->render('/admin/quotas/newquota_view', $aData);
        $this->getController()->_getAdminFooter('http://docs.limesurvey.org', $clang->gT('LimeSurvey online manual'));
    }

    function getQuotaAnswers($iQuestionId, $iSurveyId, $iQuotaId)
    {
        $iSurveyId = sanitize_int($iSurveyId);
        $aData = $this->_getData($iSurveyId);
        $sBaseLang = $aData['sBaseLang'];
        $clang = $aData['clang'];

        $aQuestionType = Questions::model()->findByPk(array('qid' => $iQuestionId, 'language' => $sBaseLang));
        $aQuestionType = $aQuestionType['type'];

        if ($aQuestionType == 'M')
        {
            $aResults = Questions::model()->findAllByAttributes(array('parent_qid' => $iQuestionId));
            $aAnswerList = array();

            foreach($aResults as $aDbAnsList)
            {
                $tmparrayans = array('Title' => $aQuestionType['title'], 'Display' => substr($aDbAnsList['question'], 0, 40), 'code' => $aDbAnsList['title']);
                $aAnswerList[$aDbAnsList['title']] = $tmparrayans;
            }

            $aResults = Quota_members::model()->findAllByAttributes(array('sid' => $iSurveyId, 'qid' => $iQuestionId, 'quota_id' => $iQuotaId));
            foreach($aResults as $aQuotaList)
            {
                $aAnswerList[$aQuotaList['code']]['rowexists'] = '1';
            }
        }
        else
        {
            $aResults = Quota_members::model()->findAllByAttributes(array('sid' => $iSurveyId, 'qid' => $iQuestionId, 'quota_id' => $iQuotaId));
        }

        if ($aQuestionType == 'G')
        {
            $aAnswerList = array('M' => array('Title' => $aQuestionType['title'], 'Display' => $clang->gT("Male"), 'code' => 'M'),
                'F' => array('Title' => $aQuestionType['title'], 'Display' => $clang->gT("Female"), 'code' => 'F'));

            foreach ($aResults as $aQuotaList)
            {
                $aAnswerList[$aQuotaList['code']]['rowexists'] = '1';
            }
        }

        if ($aQuestionType == 'L' || $aQuestionType == 'O' || $aQuestionType == '!')
        {
            $aAnsResults = Answers::model()->findAllByAttributes(array('qid' => $iQuestionId));

            $aAnswerList = array();

            foreach ($aAnsResults as $aDbAnsList)
            {
                $aAnswerList[$aDbAnsList['code']] = array('Title' => $aQuestionType['title'],
                    'Display' => substr($aDbAnsList['answer'], 0, 40),
                    'code' => $aDbAnsList['code']);
            }
        }

        if ($aQuestionType == 'A')
        {
            $aAnsResults = Questions::model()->findAllByAttributes(array('parent_qid' => $iQuestionId));

            $aAnswerList = array();

            foreach ($aAnsResults as $aDbAnsList)
            {
                for ($x = 1; $x < 6; $x++)
                {
                    $tmparrayans = array('Title' => $aQuestionType['title'], 'Display' => substr($aDbAnsList['question'], 0, 40) . ' [' . $x . ']', 'code' => $aDbAnsList['title']);
                    $aAnswerList[$aDbAnsList['title'] . "-" . $x] = $tmparrayans;
                }
            }

            foreach ($aResults as $aQuotaList)
            {
                $aAnswerList[$aQuotaList['code']]['rowexists'] = '1';
            }
        }

        if ($aQuestionType == 'B')
        {
            $aAnsResults = Answers::model()->findAllByAttributes(array('qid' => $iQuestionId));

            $aAnswerList = array();

            foreach ($aAnsResults as $aDbAnsList)
            {
                for ($x = 1; $x < 11; $x++)
                {
                    $tmparrayans = array('Title' => $aQuestionType['title'], 'Display' => substr($aDbAnsList['answer'], 0, 40) . ' [' . $x . ']', 'code' => $aDbAnsList['code']);
                    $aAnswerList[$aDbAnsList['code'] . "-" . $x] = $tmparrayans;
                }
            }

            foreach ($aResults as $aQuotaList)
            {
                $aAnswerList[$aQuotaList['code']]['rowexists'] = '1';
            }
        }

        if ($aQuestionType == 'Y')
        {
            $aAnswerList = array('Y' => array('Title' => $aQuestionType['title'], 'Display' => $clang->gT("Yes"), 'code' => 'Y'),
                'N' => array('Title' => $aQuestionType['title'], 'Display' => $clang->gT("No"), 'code' => 'N'));

            foreach ($aResults as $aQuotaList)
            {
                $aAnswerList[$aQuotaList['code']]['rowexists'] = '1';
            }
        }

        if ($aQuestionType == 'I')
        {
            $slangs = GetAdditionalLanguagesFromSurveyID($iSurveyId);
            array_unshift($slangs, $sBaseLang);

            while (list($key, $value) = each($slangs))
            {
                $tmparrayans = array('Title' => $aQuestionType['title'], 'Display' => getLanguageNameFromCode($value, false), $value);
                $aAnswerList[$value] = $tmparrayans;
            }

            foreach ($aResults as $aQuotaList)
            {
                $aAnswerList[$aQuotaList['code']]['rowexists'] = '1';
            }
        }

        if (empty($aAnswerList))
        {
            return array();
        }
        else
        {
            return $aAnswerList;
        }
    }

}