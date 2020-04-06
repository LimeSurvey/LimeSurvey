<?php if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}
/*
 * LimeSurvey
 * Copyright (C) 2013 The LimeSurvey Project Team / Carsten Schmitz
 * All rights reserved.
 * License: GNU/GPL License v2 or later, see LICENSE.php
 * LimeSurvey is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 *
 *
 *
 */

/**
 * optin
 *
 * @package LimeSurvey
 * @copyright 2011
 * @access public
 */
class OptinController extends LSYii_Controller
{

    public $layout = 'bare';
    public $defaultAction = 'tokens';

    function actiontokens($surveyid, $token, $langcode = '')
    {
        Yii::app()->loadHelper('database');
        Yii::app()->loadHelper('sanitize');
        $sLanguageCode = $langcode;
        $iSurveyID = $surveyid;
        $oSurvey = Survey::model()->findByPk($iSurveyID);
        $sToken = $token;
        $sToken = Token::sanitizeToken($sToken);

        if (!$iSurveyID) {
            $this->redirect(array('/'));
        }
        $iSurveyID = $oSurvey->primaryKey;

        //Check that there is a SID
        // Get passed language from form, so that we dont loose this!
        if (!isset($sLanguageCode) || $sLanguageCode == "" || !$sLanguageCode) {
            $sBaseLanguage = $oSurvey->language;
        } else {
            $sBaseLanguage = sanitize_languagecode($sLanguageCode);
        }

        Yii::app()->setLanguage($sBaseLanguage);

        if (empty($oSurvey) || !$oSurvey->hasTokensTable) {
            throw new CHttpException(404, "This survey does not seem to exist. It may have been deleted or the link you were given is outdated or incorrect.");
        } else {
            LimeExpressionManager::singleton()->loadTokenInformation($iSurveyID, $sToken, false);
            $oToken = Token::model($iSurveyID)->findByAttributes(array('token' => $sToken));

            if (!isset($oToken)) {
                $sMessage = gT('You are not a participant of this survey.');
            } else {
                if ($oToken->emailstatus == 'OptOut') {
                    $oToken->emailstatus = 'OK';
                    $oToken->save();
                    $sMessage = gT('You have been successfully added back to this survey.');
                } elseif ($oToken->emailstatus == 'OK') {
                    $sMessage = gT('You are already a participant of this survey.');
                } else {
                    $sMessage = gT('You have been already removed from this survey.');
                }
            }
        }

        $this->renderHtml($sMessage, $oSurvey);
    }

    /**
     * Render stuff
     *
     * @param string $html
     * @param Survey $survey
     * @return void
     */
    private function renderHtml($html, $survey)
    {
        $aSurveyInfo = getSurveyInfo($survey->primaryKey);

        $aSurveyInfo['include_content'] = 'optin';
        $aSurveyInfo['optin_message'] = $html;
        Template::getInstance('', $survey->primaryKey);

        Yii::app()->twigRenderer->renderTemplateFromFile(
            "layout_global.twig",
            array(
                'oSurvey'     => $survey,
                'aSurveyInfo' => $aSurveyInfo
            ),
            false
        );
        Yii::app()->end();
    }
}
