<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}
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
 * optout
 *
 * @package LimeSurvey
 * @copyright 2011
 * @access public
 */
class OptoutController extends LSYii_Controller
{

        public $layout = 'bare';
        public $defaultAction = 'tokens';


    function actiontokens()
    {


        $iSurveyID     = Yii::app()->request->getQuery('surveyid');
        $sLanguageCode = Yii::app()->request->getQuery('langcode');
        $sToken        = Token::sanitizeToken(Yii::app()->request->getQuery('token'));
        $oSurvey       = Survey::model()->findByPk($iSurveyID);

        Yii::app()->loadHelper('database');
        Yii::app()->loadHelper('sanitize');

        //IF there is no survey id, redirect back to the default public page
        if (!$iSurveyID) {
            $this->redirect(array('/'));
        }

        $iSurveyID = (int) $iSurveyID; //Make sure it's an integer (protect from SQL injects)
        //Check that there is a SID
        // Get passed language from form, so that we dont lose this!
        if (!isset($sLanguageCode) || $sLanguageCode == "" || !$sLanguageCode) {
            $sBaseLanguage = $oSurvey->language;
        } else {
            $sBaseLanguage = sanitize_languagecode($sLanguageCode);
        }

        Yii::app()->setLanguage($sBaseLanguage);

        $aSurveyInfo = getSurveyInfo($iSurveyID, $sBaseLanguage);

        if ($aSurveyInfo == false || !tableExists("{{tokens_{$iSurveyID}}}")) {
            throw new CHttpException(404, "The survey in which you are trying to participate does not seem to exist. It may have been deleted or the link you were given is outdated or incorrect.");
        } else {
            $oToken = Token::model($iSurveyID)->findByAttributes(array('token' => $sToken));
            if (substr($oToken->emailstatus, 0, strlen('OptOut')) == 'OptOut') {
                $sMessage = "<p>" . gT('You have already been removed from this survey.') . "</p>";
            } else {
                $sMessage = "<p>" . gT('Please confirm that you want to opt out of this survey by clicking the button below.') . '<br>' . gT("After confirmation you won't receive any invitations or reminders for this survey anymore.") . "</p>";
                $sMessage .= '<p><a href="' . Yii::app()->createUrl('optout/removetokens', array('surveyid' => $iSurveyID, 'langcode' => $sBaseLanguage, 'token' => $sToken)) . '" class="btn btn-default btn-lg">' . gT("I confirm") . '</a><p>';
            }
            $this->renderHtml($sMessage, $aSurveyInfo, $iSurveyID);
        }
    }

    /**
     * This function is run when opting out of an individual survey participants table. The other function /optout/participants
     * opts the user out of ALL survey invitations from the system
     */
    function actionremovetokens()
    {
        $this->optoutToken();
    }

    /**
     * This function is run when opting out of the participants system. The other function /optout/token
     * opts the user out of just a single token/survey invite list
     */
    function actionparticipants()
    {
        $this->optoutToken(true);
    }

    private function optoutToken($blacklistGlobally = false)
    {
        $surveyId = Yii::app()->request->getQuery('surveyid');
        $language = Yii::app()->request->getQuery('langcode');
        $accessToken = Token::sanitizeToken(Yii::app()->request->getQuery('token'));
        $survey = Survey::model()->findByPk($surveyId);
        Yii::app()->loadHelper('database');
        Yii::app()->loadHelper('sanitize');

        // If there is no survey id, redirect back to the default public page
        if (!$surveyId) {
            $this->redirect(array('/'));
        }

        // Make sure it's an integer (protect from SQL injects)
        $surveyId = (int) $surveyId;

        // Get passed language from form, so that we dont lose this!
        if (!isset($language) || $language == "" || !$language) {
            $baseLanguage = $survey->language;
        } else {
            $baseLanguage = sanitize_languagecode($language);
        }

        Yii::app()->setLanguage($baseLanguage);

        $surveyInfo = getSurveyInfo($surveyId, $baseLanguage);

        if ($surveyInfo == false || !tableExists("{{tokens_{$surveyId}}}")) {
            throw new CHttpException(404, "The survey in which you are trying to participate does not seem to exist. It may have been deleted or the link you were given is outdated or incorrect.");
        } else {
            LimeExpressionManager::singleton()->loadTokenInformation($surveyId, $accessToken, false);
            $token = Token::model($surveyId)->findByAttributes(array('token' => $accessToken));

            if (!isset($token)) {
                $message = gT('You are not a participant in this survey.');
            } else {
                if (substr($token->emailstatus, 0, strlen('OptOut')) !== 'OptOut') {
                    $token->emailstatus = 'OptOut';
                    $token->save();
                    $message = gT('You have been successfully removed from this survey.');
                } else {
                    $message = gT('You have already been removed from this survey.');
                }
                $blacklistHandler = new LimeSurvey\Models\Services\ParticipantBlacklistHandler();
                $blacklistResult = $blacklistHandler->addToBlacklist($token);
                if ($blacklistResult->isBlacklisted()) {
                    foreach ($blacklistResult->getMessages() as $blacklistMessage) {
                        $message .= "<br>" . $blacklistMessage;
                    }
                }
            }
        }

        $this->renderHtml($message, $surveyInfo, $surveyId);
    }

    /**
     * Render something
     *
     * @param string $html
     * @param array $aSurveyInfo
     * @param int $iSurveyID
     * @return void
     */
    private function renderHtml($html, $aSurveyInfo, $iSurveyID)
    {
        $survey = Survey::model()->findByPk($iSurveyID);

        $aSurveyInfo['include_content'] = 'optout';
        $aSurveyInfo['optin_message'] = $html;
        Template::model()->getInstance('', $iSurveyID);

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
