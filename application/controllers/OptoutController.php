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
     * Display the confirmation page for individual survey opt-out.
     * Provides both a legacy GET link (optin_link) for old themes and a
     * secure POST link (optin_post_link) for updated themes.
     *
     * @throws CHttpException
     */
    public function actiontokens()
    {
        $iSurveyID     = Yii::app()->request->getQuery('surveyid');
        $sLanguageCode = Yii::app()->request->getQuery('langcode');
        $sToken        = Token::sanitizeToken(Yii::app()->request->getQuery('token'));

        Yii::app()->loadHelper('database');
        Yii::app()->loadHelper('sanitize');

        if (!filter_var($iSurveyID, FILTER_VALIDATE_INT) || !$sToken) {
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
                $postLink = Yii::app()->createUrl('optout/removetoken', array('surveyid' => $iSurveyID, 'langcode' => $sBaseLanguage, 'token' => $sToken));
            }
            $tokenAttributes = $oToken->getAttributes();
        }
        $this->renderHtml($sMessage, $oSurvey, $link, $tokenAttributes, [], $postLink ?? '');
    }

    /**
     * Display the confirmation page for global opt-out (central participant list).
     * Provides both a legacy GET link (optin_link) for old themes and a
     * secure POST link (optin_post_link) for updated themes.
     *
     * @throws CHttpException
     */
    public function actionparticipants()
    {
        $surveyId = Yii::app()->request->getQuery('surveyid');
        $languageCode = Yii::app()->request->getQuery('langcode');
        $accessToken = Token::sanitizeToken(Yii::app()->request->getQuery('token'));

        Yii::app()->loadHelper('database');
        Yii::app()->loadHelper('sanitize');

        if (!filter_var($surveyId, FILTER_VALIDATE_INT) || !$accessToken) {
            throw new CHttpException(400, gT('Invalid request.'));
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
                $message = gT('Please confirm that you want to be removed from the central participant list for this site.');
                $link = Yii::app()->createUrl('optout/removetokens', array('surveyid' => $surveyId, 'langcode' => $baseLanguage, 'token' => $accessToken, 'global' => true));
                $postLink = Yii::app()->createUrl('optout/removetoken', array('surveyid' => $surveyId, 'langcode' => $baseLanguage, 'token' => $accessToken, 'global' => true));
            } elseif (!$optedOutFromSurvey) {
                $message = gT('Please confirm that you want to opt out of this survey by clicking the button below.') . '<br>' . gT("After confirmation you won't receive any invitations or reminders for this survey anymore.");
                $link = Yii::app()->createUrl('optout/removetokens', array('surveyid' => $surveyId, 'langcode' => $baseLanguage, 'token' => $accessToken));
                $postLink = Yii::app()->createUrl('optout/removetoken', array('surveyid' => $surveyId, 'langcode' => $baseLanguage, 'token' => $accessToken));
            } else {
                $message = gT('You have already been removed from the central participant list for this site.');
            }
            $tokenAttributes = $token->getAttributes();
            if (!empty($participant)) {
                $participantAttributes = $participant->getAttributes();
            }
        }

        $this->renderHtml($message, $survey, $link, $tokenAttributes, $participantAttributes, $postLink ?? '');
    }

    /**
     * Legacy opt-out endpoint (GET-based). Kept for backward compatibility with
     * already-sent emails and custom themes that still use <a href> links.
     * New templates should use actionremovetoken() via POST instead.
     */
    public function actionremovetokens()
    {
        $result = $this->handleOptout();
        $this->renderHtml($result['message'], $result['survey'], '', $result['tokenAttributes'], $result['participantAttributes']);
    }

    /**
     * Secure opt-out endpoint (POST-only). Prevents email security scanners
     * (Microsoft Defender Safe Links, Proofpoint, etc.) from automatically
     * triggering opt-out by following GET links.
     */
    public function actionremovetoken()
    {
        if (!Yii::app()->request->isPostRequest) {
            throw new CHttpException(405, gT('Invalid request method.'));
        }

        $result = $this->handleOptout();
        $this->renderHtml($result['message'], $result['survey'], '', $result['tokenAttributes'], $result['participantAttributes']);
    }

    /**
     * Common opt-out logic shared by actionremovetokens() and actionremovetoken().
     * Validates the survey, resolves the language, loads the token, sets emailstatus
     * to 'OptOut', and optionally blacklists the participant globally.
     *
     * @return array{message: string, survey: Survey, tokenAttributes: array<string,mixed>, participantAttributes: array<string,mixed>}
     * @throws CHttpException
     */
    private function handleOptout()
    {
        $surveyId = Yii::app()->request->getQuery('surveyid');
        $language = Yii::app()->request->getQuery('langcode');
        $accessToken = Token::sanitizeToken(Yii::app()->request->getQuery('token'));
        $global = Yii::app()->request->getQuery('global');

        Yii::app()->loadHelper('database');
        Yii::app()->loadHelper('sanitize');

        if (!filter_var($surveyId, FILTER_VALIDATE_INT) || !$accessToken) {
            throw new CHttpException(400, gT('Invalid request.'));
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

        return [
            'message' => $message,
            'survey' => $survey,
            'tokenAttributes' => $tokenAttributes,
            'participantAttributes' => $participantAttributes,
        ];
    }

    /**
     * Render the opt-out/opt-in themed page via Twig.
     *
     * @param string $message
     * @param Survey $survey
     * @param string $link Legacy GET URL for the opt-out action (used by old themes via optin_link)
     * @param array<string,mixed> $token
     * @param array<string,mixed> $participant
     * @param string $postLink Secure POST-only URL for the opt-out action (used by updated themes via optin_post_link)
     * @return void
     */
    private function renderHtml($message, $survey, $link = '', $token = [], $participant = [], $postLink = '')
    {
        $aSurveyInfo = getSurveyInfo($survey->primaryKey);

        $aSurveyInfo['include_content'] = 'optout';
        $aSurveyInfo['optin_message'] = $message;
        $aSurveyInfo['optin_link'] = $link;
        $aSurveyInfo['optin_post_link'] = $postLink;
        $aSurveyInfo['optin_csrf_token_name'] = Yii::app()->request->csrfTokenName;
        $aSurveyInfo['optin_csrf_token_value'] = Yii::app()->request->csrfToken;
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
