<?php if (!defined('BASEPATH')) {
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
                $sBaseLanguage = Survey::model()->findByPk($iSurveyID)->language;
            } else {
                $sBaseLanguage = sanitize_languagecode($sLanguageCode);
            }

            Yii::app()->setLanguage($sBaseLanguage);

            $aSurveyInfo = getSurveyInfo($iSurveyID, $sBaseLanguage);

            if ($aSurveyInfo == false || !tableExists("{{tokens_{$iSurveyID}}}")) {
                throw new CHttpException(404, "The survey in which you are trying to participate does not seem to exist. It may have been deleted or the link you were given is outdated or incorrect.");
            } else {
                $sMessage = "<p>".gT('Please confirm that you want to opt out of this survey by clicking the button below.').'<br>'.gT("After confirmation you won't receive any invitations or reminders for this survey anymore.")."</p>";
                $sMessage .= '<p><a href="'.Yii::app()->createUrl('optout/removetokens', array('surveyid'=>$iSurveyID, 'langcode'=> $sBaseLanguage, 'token' => $sToken)).'" class="btn btn-default btn-lg">'.gT("I confirm").'</a><p>';
                $this->renderHtml($sMessage, $aSurveyInfo, $iSurveyID);
            }


        }

    /**
     * This function is run when opting out of an individual survey participants table. The other function /optout/participants
     * opts the user out of ALL survey invitations from the system
     */
    function actionremovetokens()
    {
        $iSurveyID = Yii::app()->request->getQuery('surveyid');
        $sLanguageCode = Yii::app()->request->getQuery('langcode');
        $sToken = Token::sanitizeToken(Yii::app()->request->getQuery('token'));
        Yii::app()->loadHelper('database');
        Yii::app()->loadHelper('sanitize');

        if (!$iSurveyID) {
//IF there is no survey id, redirect back to the default public page
            $this->redirect(array('/'));
        }
        $iSurveyID = (int) $iSurveyID; //Make sure it's an integer (protect from SQL injects)
        //Check that there is a SID
        // Get passed language from form, so that we dont lose this!
        if (!isset($sLanguageCode) || $sLanguageCode == "" || !$sLanguageCode) {
            $sBaseLanguage = Survey::model()->findByPk($iSurveyID)->language;
        } else {
            $sBaseLanguage = sanitize_languagecode($sLanguageCode);
        }

        Yii::app()->setLanguage($sBaseLanguage);

        $aSurveyInfo = getSurveyInfo($iSurveyID, $sBaseLanguage);

        if ($aSurveyInfo == false || !tableExists("{{tokens_{$iSurveyID}}}")) {
            throw new CHttpException(404, "The survey in which you are trying to participate does not seem to exist. It may have been deleted or the link you were given is outdated or incorrect.");
        } else {
            LimeExpressionManager::singleton()->loadTokenInformation($iSurveyID, $sToken, false);
            $oToken = Token::model($iSurveyID)->findByAttributes(array('token'=>$sToken));

            if (!isset($oToken)) {
                $sMessage = gT('You are not a participant in this survey.');
                //throw new CHttpException(404, "You are not a participant in this survey.");
            } else {
                if (substr($oToken->emailstatus, 0, strlen('OptOut')) !== 'OptOut') {
                    $oToken->emailstatus = 'OptOut';
                    $oToken->save();
                    $sMessage = gT('You have been successfully removed from this survey.');
                } else {
                    $sMessage = gT('You have already been removed from this survey.');
                }
            }
        }

        $this->renderHtml($sMessage, $aSurveyInfo, $iSurveyID);
    }

    /**
     * This function is run when opting out of the participants system. The other function /optout/token
     * opts the user out of just a single token/survey invite list
     */
    function actionparticipants()
    {
        $iSurveyID = Yii::app()->request->getQuery('surveyid');
        $sLanguageCode = Yii::app()->request->getQuery('langcode');
        $sToken = Token::sanitizeToken(Yii::app()->request->getQuery('token'));
        Yii::app()->loadHelper('database');
        Yii::app()->loadHelper('sanitize');
        if (!$iSurveyID) {
//IF there is no survey id, redirect back to the default public page
            $this->redirect(array('/'));
        }
        $iSurveyID = (int) $iSurveyID; //Make sure it's an integer (protect from SQL injects)
        //Check that there is a SID
        // Get passed language from form, so that we dont lose this!
        if (!isset($sLanguageCode) || $sLanguageCode == "" || !$sLanguageCode) {
            $sBaseLanguage = Survey::model()->findByPk($iSurveyID)->language;
        } else {
            $sBaseLanguage = sanitize_languagecode($sLanguageCode);
        }
        Yii::app()->setLanguage($sBaseLanguage);

        $aSurveyInfo = getSurveyInfo($iSurveyID, $sBaseLanguage);

        if ($aSurveyInfo == false || !tableExists("{{tokens_{$iSurveyID}}}")) {
            throw new CHttpException(404, "The survey in which you are trying to participate does not seem to exist. It may have been deleted or the link you were given is outdated or incorrect.");
        } else {
            LimeExpressionManager::singleton()->loadTokenInformation($iSurveyID, $sToken, false);
            $oToken = Token::model($iSurveyID)->findByAttributes(array('token' => $sToken));
            if (!isset($oToken)) {
                $sMessage = gT('You are not a participant in this survey.');
            } else {
                if (substr($oToken->emailstatus, 0, strlen('OptOut')) !== 'OptOut') {
                    $oToken->emailstatus = 'OptOut';
                    $oToken->save();
                    $sMessage = gT('You have been successfully removed from this survey.');
                } else {
                    $sMessage = gT('You have been already removed from this survey.');
                }
                if (!empty($oToken->participant_id)) {
                    //Participant also exists in central db
                    $oParticipant = Participant::model()->findByPk($oToken->participant_id);
                    if ($oParticipant->blacklisted == "Y") {
                        $sMessage .= "<br />";
                        $sMessage .= gT("You have already been removed from the central participants list for this site");
                    } else {
                        $oParticipant->blacklisted = 'Y';
                        $oParticipant->save();
                        $sMessage .= "<br />";
                        $sMessage .= gT("You have been removed from the central participants list for this site");
                    }
                }
            }
        }

        $this->renderHtml($sMessage, $aSurveyInfo, $iSurveyID);
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
