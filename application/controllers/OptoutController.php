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

    /**
     * Display the confirmation for individual survey opt out
     */
    public function actiontokens()
    {
        $iSurveyID     = Yii::app()->request->getQuery('surveyid');
        $sLanguageCode = Yii::app()->request->getQuery('langcode');
        $sToken        = Token::sanitizeToken(Yii::app()->request->getQuery('token'));

        Yii::app()->loadHelper('database');
        Yii::app()->loadHelper('sanitize');

        if (!$iSurveyID || intval($iSurveyID) !== $iSurveyID || !$sToken) {
            throw new CHttpException(400, gT('Invalid request.'));
        }

        $oSurvey = Survey::model()->findByPk($iSurveyID);
        if (empty($oSurvey) || !$oSurvey->hasTokensTable) {
            throw new CHttpException(404, "The survey in which you are trying to participate does not seem to exist. It may have been deleted or the link you were given is outdated or incorrect.");
        }

        // Get passed language from form, so that we dont lose this!
        if (!isset($sLanguageCode) || $sLanguageCode == "" || !$sLanguageCode) {
            $sBaseLanguage = $oSurvey->language;
        } else {
            $sBaseLanguage = sanitize_languagecode($sLanguageCode);
        }

        Yii::app()->setLanguage($sBaseLanguage);

        $link = '';
        $tokenAttributes = [];
        $oToken = Token::model($iSurveyID)->findByAttributes(array('token' => $sToken));
        if (!isset($oToken)) {
            $sMessage = gT('You are not a participant of this survey.');
        } else {
            if (substr((string) $oToken->emailstatus, 0, strlen('OptOut')) == 'OptOut') {
                $sMessage = gT('You have already been removed from this survey.');
            } else {
                $sMessage = gT('Please confirm that you want to opt out of this survey by clicking the button below.') . '<br>' . gT("After confirmation you won't receive any invitations or reminders for this survey anymore.");
                $link = Yii::app()->createUrl('optout/removetokens', array('surveyid' => $iSurveyID, 'langcode' => $sBaseLanguage, 'token' => $sToken));
            }
            $tokenAttributes = $oToken->getAttributes();
        }
        $this->renderHtml($sMessage, $oSurvey, $link, $tokenAttributes);
    }

    /**
     * Display the confirmation for global opt out
     */
    public function actionparticipants()
    {
        $surveyId = Yii::app()->request->getQuery('surveyid');
        $languageCode = Yii::app()->request->getQuery('langcode');
        $accessToken = Token::sanitizeToken(Yii::app()->request->getQuery('token'));

        Yii::app()->loadHelper('database');
        Yii::app()->loadHelper('sanitize');

        //IF there is no survey ID, redirect back to the default public page
        if (!$surveyId) {
            $this->redirect(array('/'));
        }

        $survey = Survey::model()->findByPk($surveyId);
        if (empty($survey) || !$survey->hasTokensTable) {
            throw new CHttpException(404, "This survey does not seem to exist. It may have been deleted or the link you were given is outdated or incorrect.");
        }

        // Get passed language from form, so that we dont lose this!
        if (!isset($languageCode) || $languageCode == "" || !$languageCode) {
            $baseLanguage = $survey->language;
        } else {
            $baseLanguage = sanitize_languagecode($languageCode);
        }

        Yii::app()->setLanguage($baseLanguage);

        $token = Token::model($surveyId)->findByAttributes(array('token' => $accessToken));

        $link = '';
        $tokenAttributes = [];
        $participantAttributes = [];
        if (!isset($token)) {
            $message = gT('You are not a participant of this survey.');
        } else {
            $optedOutFromSurvey = substr((string) $token->emailstatus, 0, strlen('OptOut')) == 'OptOut';

            $blacklistHandler = new LimeSurvey\Models\Services\ParticipantBlacklistHandler();
            $participant = $blacklistHandler->getCentralParticipantFromToken($token);

            if (!empty($participant) && $participant->blacklisted != 'Y') {
                $message = gT('Please confirm that you want to be removed from the central participants list for this site.');
                $link = Yii::app()->createUrl('optout/removetokens', array('surveyid' => $surveyId, 'langcode' => $baseLanguage, 'token' => $accessToken, 'global' => true));
            } elseif (!$optedOutFromSurvey) {
                $message = gT('Please confirm that you want to opt out of this survey by clicking the button below.') . '<br>' . gT("After confirmation you won't receive any invitations or reminders for this survey anymore.");
                $link = Yii::app()->createUrl('optout/removetokens', array('surveyid' => $surveyId, 'langcode' => $baseLanguage, 'token' => $accessToken));
            } else {
                $message = gT('You have already been removed from the central participants list for this site.');
            }
            $tokenAttributes = $token->getAttributes();
            if (!empty($participant)) {
                $participantAttributes = $participant->getAttributes();
            }
        }

        $this->renderHtml($message, $survey, $link, $tokenAttributes, $participantAttributes);
    }

    /**
     * This function is run when opting out of an individual survey participants table. The other function /optout/participants
     * opts the user out of ALL survey invitations from the system
     */
    public function actionremovetokens()
    {
        $surveyId = Yii::app()->request->getQuery('surveyid');
        $language = Yii::app()->request->getQuery('langcode');
        $accessToken = Token::sanitizeToken(Yii::app()->request->getQuery('token'));
        $global = Yii::app()->request->getQuery('global');

        Yii::app()->loadHelper('database');
        Yii::app()->loadHelper('sanitize');

        // If there is no survey ID, redirect back to the default public page
        if (!$surveyId) {
            $this->redirect(['/']);
        }

        $survey = Survey::model()->findByPk($surveyId);
        if (empty($survey) || !$survey->hasTokensTable) {
            throw new CHttpException(404, "This survey does not seem to exist. It may have been deleted or the link you were given is outdated or incorrect.");
        }

        // Get passed language from form, so that we dont lose this!
        if (!isset($language) || $language == "" || !$language) {
            $baseLanguage = $survey->language;
        } else {
            $baseLanguage = sanitize_languagecode($language);
        }

        Yii::app()->setLanguage($baseLanguage);

        LimeExpressionManager::singleton()->loadTokenInformation($surveyId, $accessToken, false);
        $token = Token::model($surveyId)->findByAttributes(['token' => $accessToken]);

        $tokenAttributes = [];
        $participantAttributes = [];
        if (!isset($token)) {
            $message = gT('You are not a participant in this survey.');
        } else {
            if (substr((string) $token->emailstatus, 0, strlen('OptOut')) !== 'OptOut') {
                $token->emailstatus = 'OptOut';
                $token->save();
                $message = gT('You have been successfully removed from this survey.');
            } else {
                $message = gT('You have already been removed from this survey.');
            }
            if ($global) {
                $blacklistHandler = new LimeSurvey\Models\Services\ParticipantBlacklistHandler();
                $blacklistResult = $blacklistHandler->addToBlacklist($token);
                if ($blacklistResult->isBlacklisted()) {
                    foreach ($blacklistResult->getMessages() as $blacklistMessage) {
                        $message .= "<br>" . $blacklistMessage;
                    }
                }
                $participant = $blacklistHandler->getCentralParticipantFromToken($token);
                if (!empty($participant)) {
                    $participantAttributes = $participant->getAttributes();
                }
            }
            $tokenAttributes = $token->getAttributes();
        }

        $this->renderHtml($message, $survey, '', $tokenAttributes, $participantAttributes);
    }

    /**
     * Render something
     *
     * @param string $message
     * @param Survey $survey
     * @param string $link
     * @param array<string,mixed> $token
     * @param array<string,mixed> $participant
     * @return void
     */
    private function renderHtml($message, $survey, $link = '', $token = [], $participant = [])
    {
        $aSurveyInfo = getSurveyInfo($survey->primaryKey);

        $aSurveyInfo['include_content'] = 'optout';
        $aSurveyInfo['optin_message'] = $message;
        $aSurveyInfo['optin_link'] = $link;
        $aSurveyInfo['aCompleted'] = true;  // Avoid showing the progress bar
        $aSurveyInfo['token'] = $token;
        $aSurveyInfo['participant'] = $participant;
        Template::model()->getInstance('', $survey->primaryKey);

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
