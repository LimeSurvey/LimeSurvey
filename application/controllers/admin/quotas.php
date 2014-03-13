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

    function __construct($controller, $id)
    {
        parent::__construct($controller, $id);

        // Load helpers
        Yii::app()->loadHelper('surveytranslator');
        // Sanitize/get globals/variables
        $_POST['quotamax'] = sanitize_int(Yii::app()->request->getPost('quotamax'));

        if (empty($_POST['autoload_url']))
        {
            $_POST['autoload_url'] = 0;
        }

        if (empty($_POST['quota_limit']) || !is_numeric(Yii::app()->request->getPost('quota_limit')) || Yii::app()->request->getPost('quota_limit') < 0)
        {
            $_POST['quota_limit'] = 0;
        }
    }

    private function _getData($iSurveyId)
    {
        // Set the variables in an array
        $aData['iSurveyId'] = $aData['surveyid'] = $iSurveyId;
        $aData['clang'] = $this->getController()->lang;
        $aData['sBaseLang'] = Survey::model()->findByPk($iSurveyId)->language;
        $aData['aLangs'] = Survey::model()->findByPk($iSurveyId)->additionalLanguages;
        array_unshift($aData['aLangs'], $aData['sBaseLang']);

        $aData['action'] = $action = Yii::app()->request->getParam('action');
        if (!isset($action))
            $aData['action'] = 'quotas';

        return $aData;
    }

    private function _checkPermissions($iSurveyId, $sPermission)
    {
        $clang=$this->getController()->lang;
        if (!empty($sPermission) && !(Permission::model()->hasSurveyPermission($iSurveyId, 'quotas', $sPermission))) {
            Yii::app()->session['flashmessage'] = $clang->gT('Access denied!');
            $this->_redirectToIndex($iSurveyId);
        }
    }

    function _redirectToIndex($iSurveyId)
    {
        $clang=$this->getController()->lang;
        if(Permission::model()->hasSurveyPermission($iSurveyId, 'quotas','read'))
        {
            $this->getController()->redirect($this->getController()->createUrl("/admin/quotas/sa/index/surveyid/$iSurveyId"));
        }
        else
        {
            Yii::app()->session['flashmessage'] = $clang->gT('Access denied!');
            $this->getController()->redirect($this->getController()->createUrl("admin/survey/sa/view/surveyid/$iSurveyId"));
        }
    }

    function index($iSurveyId, $quickreport = false)
    {
        $iSurveyId = sanitize_int($iSurveyId);
        $this->_checkPermissions($iSurveyId, 'read');
        $aData = $this->_getData($iSurveyId);
        $aViewUrls = array();

        if ($quickreport == false)
        {
            $aViewUrls[] = 'viewquotas_view';
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
            $aViewUrls['output'] = '';
            //loop through all quotas
            foreach ($aResult as $aQuotaListing)
            {
                $totalquotas += $aQuotaListing['qlimit'];
                $completed = getQuotaCompletedCount($iSurveyId, $aQuotaListing['id']);
                $highlight = ($completed >= $aQuotaListing['qlimit']) ? '' : "style='color: orange'"; //Incomplete quotas displayed in red
                $totalcompleted = $totalcompleted + $completed;
                $csvoutput[] = $aQuotaListing['name'] . "," . $aQuotaListing['qlimit'] . "," . $completed . "," . ($aQuotaListing['qlimit'] - $completed) . "\r\n";

                if ($quickreport != false)
                {
                    continue;
                }

                $aData['quotalisting'] = $aQuotaListing;
                $aData['highlight'] = $highlight;
                $aData['completed'] = $completed;
                $aData['totalquotas'] = $totalquotas;
                $aData['totalcompleted'] = $totalcompleted;
                $aViewUrls['output'] .= $this->getController()->renderPartial("/admin/quotas/viewquotasrow_view", $aData, true);

                //check how many sub-elements exist for a certain quota
                $aResults2 = QuotaMember::model()->findAllByAttributes(array('quota_id' => $aQuotaListing['id']));

                //loop through all sub-parts
                foreach ($aResults2 as $aQuotaQuestions)
                {
                    $aQuestionAnswers = self::getQuotaAnswers($aQuotaQuestions['qid'], $iSurveyId, $aQuotaListing['id']);
                    $aData['question_answers'] = $aQuestionAnswers;
                    $aData['quota_questions'] = $aQuotaQuestions;
                    $aViewUrls['output'] .= $this->getController()->renderPartial('/admin/quotas/viewquotasrowsub_view', $aData, true);
                }
            }
        }
        else
        {
            // No quotas have been set for this survey
            $aViewUrls[] = 'viewquotasempty_view';
        }

        $aData['totalquotas'] = $totalquotas;
        $aData['totalcompleted'] = $totalcompleted;

        if ($quickreport == false)
        {
            $aViewUrls[] = 'viewquotasfooter_view';
            $this->_renderWrappedTemplate('quotas', $aViewUrls, $aData);
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
        $oQuota->name = Yii::app()->request->getPost('quota_name');
        $oQuota->qlimit = Yii::app()->request->getPost('quota_limit');
        $oQuota->action = Yii::app()->request->getPost('quota_action');
        $oQuota->autoload_url = Yii::app()->request->getPost('autoload_url');
        $oQuota->save();
        $iQuotaId = $oQuota->id;

        //Iterate through each language, and make sure there is a quota message for it
        $sError = '';
        foreach ($aLangs as $sLang)
        {
            if (!$_POST['quotals_message_' . $sLang])
            {
                $sError .= getLanguageNameFromCode($sLang, false) . "\\n";
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
                $_POST['quotals_message_' . $sLang] = fixCKeditorText($_POST['quotals_message_' . $sLang]);

                $oQuotaLanguageSettings = new QuotaLanguageSetting;
                $oQuotaLanguageSettings->quotals_quota_id = $iQuotaId;
                $oQuotaLanguageSettings->quotals_language = $sLang;
                $oQuotaLanguageSettings->quotals_name = Yii::app()->request->getPost('quota_name');
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

        $oQuota = Quota::model()->findByPk(Yii::app()->request->getPost('quota_id'));
        $oQuota->name = Yii::app()->request->getPost('quota_name');
        $oQuota->qlimit = Yii::app()->request->getPost('quota_limit');
        $oQuota->action = Yii::app()->request->getPost('quota_action');
        $oQuota->autoload_url = Yii::app()->request->getPost('autoload_url');
        $oQuota->save();

        //Iterate through each language posted, and make sure there is a quota message for it
        $sError = '';
        foreach ($aLangs as $sLang)
        {
            if (!$_POST['quotals_message_' . $sLang])
            {
                $sError.= getLanguageNameFromCode($sLang, false) . "\\n";
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
                $_POST['quotals_message_' . $sLang] = fixCKeditorText($_POST['quotals_message_' . $sLang]);

                $oQuotaLanguageSettings = QuotaLanguageSetting::model()->findByAttributes(array('quotals_quota_id' => Yii::app()->request->getPost('quota_id'), 'quotals_language' => $sLang));
                $oQuotaLanguageSettings->quotals_name = Yii::app()->request->getPost('quota_name');
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
        $this->_checkPermissions($iSurveyId, 'update');

        $oQuotaMembers = new QuotaMember('create');  // Trigger the 'create' rules
        $oQuotaMembers->sid = $iSurveyId;
        $oQuotaMembers->qid = Yii::app()->request->getPost('quota_qid');
        $oQuotaMembers->quota_id = Yii::app()->request->getPost('quota_id');
        $oQuotaMembers->code = Yii::app()->request->getPost('quota_anscode');
        if ($oQuotaMembers->save()) {
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
        } else {
            // Save was not successful, redirect back
            $_POST['action'] = "quotas";
            $_POST['subaction'] = "new_answer";
            $sSubAction = "new_answer_two";
            self::new_answer($iSurveyId, $sSubAction);
        }
    }

    function delans($iSurveyId)
    {
        $iSurveyId = sanitize_int($iSurveyId);
        $this->_checkPermissions($iSurveyId, 'update');

        QuotaMember::model()->deleteAllByAttributes(array(
            'id' => Yii::app()->request->getPost('quota_member_id'),
            'qid' => Yii::app()->request->getPost('quota_qid'),
            'code' => Yii::app()->request->getPost('quota_anscode'),
        ));

        self::_redirectToIndex($iSurveyId);
    }

    function delquota($iSurveyId)
    {
        $iSurveyId = sanitize_int($iSurveyId);
        $this->_checkPermissions($iSurveyId, 'delete');

        Quota::model()->deleteByPk(Yii::app()->request->getPost('quota_id'));
        QuotaLanguageSetting::model()->deleteAllByAttributes(array('quotals_quota_id' => Yii::app()->request->getPost('quota_id')));
        QuotaMember::model()->deleteAllByAttributes(array('quota_id' => Yii::app()->request->getPost('quota_id')));

        self::_redirectToIndex($iSurveyId);
    }

    function editquota($iSurveyId)
    {
        $iSurveyId = sanitize_int($iSurveyId);
        $this->_checkPermissions($iSurveyId, 'update');
        $aData = $this->_getData($iSurveyId);
        $aLangs = $aData['aLangs'];
        $clang = $aData['clang'];
        $aViewUrls = array();

        $aQuotaInfo = Quota::model()->findByPk(Yii::app()->request->getPost('quota_id'));
        $aData['quotainfo'] = $aQuotaInfo;

        $first=true;
        foreach ($aLangs as $sLanguage)
        {
            $aTabTitles[$sLanguage] = getLanguageNameFromCode($sLanguage, false);
            if ($first)
            {
                $aTabTitles[$sLanguage].= ' (' . $clang->gT("Base language") . ')';
                $first = false;
            }
            $aData['langquotainfo'] = QuotaLanguageSetting::model()->findByAttributes(array('quotals_quota_id' => Yii::app()->request->getPost('quota_id'), 'quotals_language' => $sLanguage));
            $aData['lang'] = $sLanguage;
            $aTabContents[$sLanguage] = $this->getController()->renderPartial('/admin/quotas/editquotalang_view', $aData, true);
        }
        $aData['aTabTitles']=$aTabTitles;
        $aData['aTabContents']=$aTabContents;

        $aViewUrls[] = 'editquota_view';

        $this->_renderWrappedTemplate('quotas', $aViewUrls, $aData);
    }

    function new_answer($iSurveyId, $sSubAction = 'new_answer')
    {
        $iSurveyId = sanitize_int($iSurveyId);
        $this->_checkPermissions($iSurveyId, 'update');
        $aData = $this->_getData($iSurveyId);
        $sBaseLang = $aData['sBaseLang'];
        $clang = $aData['clang'];
        $aViewUrls = array();

        if (($sSubAction == "new_answer" || ($sSubAction == "new_answer_two" && !isset($_POST['quota_qid']))) && Permission::model()->hasSurveyPermission($iSurveyId, 'quotas', 'create'))
        {
            $result = Quota::model()->findAllByPk(Yii::app()->request->getPost('quota_id'));
            foreach ($result as $aQuotaDetails)
            {
                $quota_name = $aQuotaDetails['name'];
            }

            $result = Question::model()->findAllByAttributes(array('type' => array('G', 'M', 'Y', 'A', 'B', 'I', 'L', 'O', '!'), 'sid' => $iSurveyId, 'language' => $sBaseLang, 'parent_qid' => 0));
            if (empty($result))
            {
                $aViewUrls[] = 'newanswererror_view';
            }
            else
            {
                $aData['newanswer_result'] = $result;
                $aData['quota_name'] = $quota_name;
                $aViewUrls[] = 'newanswer_view';
            }
        }

        if ($sSubAction == "new_answer_two" && isset($_POST['quota_qid']) && Permission::model()->hasSurveyPermission($iSurveyId, 'quotas', 'create'))
        {
            $aResults = Quota::model()->findByPk(Yii::app()->request->getPost('quota_qid'));
            $sQuotaName = $aResults['name'];

            $aQuestionAnswers = self::getQuotaAnswers(Yii::app()->request->getPost('quota_qid'), $iSurveyId, Yii::app()->request->getPost('quota_id'));
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
            $aViewUrls[] = 'newanswertwo_view';
        }

        $this->_renderWrappedTemplate('quotas', $aViewUrls, $aData);
    }

    function newquota($iSurveyId)
    {
        $iSurveyId = sanitize_int($iSurveyId);
        $this->_checkPermissions($iSurveyId, 'create');
        $aData = $this->_getData($iSurveyId);
        $clang = $aData['clang'];

        $aData['thissurvey'] = getSurveyInfo($iSurveyId);
        $aData['langs'] = $aData['aLangs'];
        $aData['baselang'] = $aData['sBaseLang'];

        $this->_renderWrappedTemplate('quotas', 'newquota_view', $aData);
    }

    /**
     * 
     * @param type $iQuestionId
     * @param type $iSurveyId
     * @param type $iQuotaId
     * @return array
     */
    function getQuotaAnswers($iQuestionId, $iSurveyId, $iQuotaId)
    {
        $iQuestionId = sanitize_int($iQuestionId);
        $iSurveyId   = sanitize_int($iSurveyId);
        $iQuotaId    = sanitize_int($iQuotaId);
        $aData       = $this->_getData($iSurveyId);
        $sBaseLang   = $aData['sBaseLang'];
        $clang       = $aData['clang'];
        $this->_checkPermissions($iSurveyId, 'read');
        $aQuestion = Question::model()->findByPk(array('qid' => $iQuestionId, 'language' => $sBaseLang));
        $aQuestionType = $aQuestion['type'];

        if ($aQuestionType == 'M')
        {
            $aResults = Question::model()->findAllByAttributes(array('parent_qid' => $iQuestionId));
            $aAnswerList = array();

            foreach($aResults as $aDbAnsList)
            {
                $tmparrayans = array('Title' => $aQuestion['title'], 'Display' => substr($aDbAnsList['question'], 0, 40), 'code' => $aDbAnsList['title']);
                $aAnswerList[$aDbAnsList['title']] = $tmparrayans;
            }
        } elseif ($aQuestionType == 'G')
        {
            $aAnswerList = array(
                'M' => array('Title' => $aQuestion['title'], 'Display' => $clang->gT("Male"), 'code' => 'M'),
                'F' => array('Title' => $aQuestion['title'], 'Display' => $clang->gT("Female"), 'code' => 'F'));
        } elseif ($aQuestionType == 'L' || $aQuestionType == 'O' || $aQuestionType == '!')
        {
            $aAnsResults = Answer::model()->findAllByAttributes(array('qid' => $iQuestionId));

            $aAnswerList = array();

            foreach ($aAnsResults as $aDbAnsList) 
            {
                $aAnswerList[$aDbAnsList['code']] = array('Title' => $aQuestion['title'], 'Display' => substr($aDbAnsList['answer'], 0, 40), 'code' => $aDbAnsList['code']);
            }
        } elseif ($aQuestionType == 'A')
        {
            $aAnsResults = Question::model()->findAllByAttributes(array('parent_qid' => $iQuestionId));

            $aAnswerList = array();

            foreach ($aAnsResults as $aDbAnsList)
            {
                for ($x = 1; $x < 6; $x++)
                {
                    $tmparrayans = array('Title' => $aQuestion['title'], 'Display' => substr($aDbAnsList['question'], 0, 40) . ' [' . $x . ']', 'code' => $aDbAnsList['title']);
                    $aAnswerList[$aDbAnsList['title'] . "-" . $x] = $tmparrayans;
                }
            }
        } elseif ($aQuestionType == 'B')
        {
            $aAnsResults = Answer::model()->findAllByAttributes(array('qid' => $iQuestionId));

            $aAnswerList = array();

            foreach ($aAnsResults as $aDbAnsList)
            {
                for ($x = 1; $x < 11; $x++)
                {
                    $tmparrayans = array('Title' => $aQuestion['title'], 'Display' => substr($aDbAnsList['answer'], 0, 40) . ' [' . $x . ']', 'code' => $aDbAnsList['code']);
                    $aAnswerList[$aDbAnsList['code'] . "-" . $x] = $tmparrayans;
                }
            }
        } elseif ($aQuestionType == 'Y')
        {
            $aAnswerList = array(
                'Y' => array('Title' => $aQuestion['title'], 'Display' => $clang->gT("Yes"), 'code' => 'Y'),
                'N' => array('Title' => $aQuestion['title'], 'Display' => $clang->gT("No"), 'code' => 'N'));
        } elseif ($aQuestionType == 'I')
        {
            $slangs = Survey::model()->findByPk($iSurveyId)->additionalLanguages;
            array_unshift($slangs, $sBaseLang);

            while (list($key, $value) = each($slangs))
            {
                $tmparrayans = array('Title' => $aQuestion['title'], 'Display' => getLanguageNameFromCode($value, false), $value);
                $aAnswerList[$value] = $tmparrayans;
            }
        }

        if (empty($aAnswerList))
        {
            return array();
        } 
        else
        {
            // Now we mark answers already used in this quota as such
            $aExistsingAnswers = QuotaMember::model()->findAllByAttributes(array('sid' => $iSurveyId, 'qid' => $iQuestionId, 'quota_id' => $iQuotaId));
            foreach ($aExistsingAnswers as $aAnswerRow)
            {
                if (array_key_exists($aAnswerRow['code'], $aAnswerList))
                {
                    $aAnswerList[$aAnswerRow['code']]['rowexists'] = '1';
                }
            }
            return $aAnswerList;
        }
    }

    /**
     * Renders template(s) wrapped in header and footer
     *
     * @param string $sAction Current action, the folder to fetch views from
     * @param string|array $aViewUrls View url(s)
     * @param array $aData Data to be passed on. Optional.
     */
    protected function _renderWrappedTemplate($sAction = 'quotas', $aViewUrls = array(), $aData = array())
    {
        App()->getClientScript()->registerPackage('jquery-superfish');
        App()->getClientScript()->registerScriptFile(Yii::app()->getConfig('adminscripts') . '/quotas.js');
        parent::_renderWrappedTemplate($sAction, $aViewUrls, $aData);
    }

}
