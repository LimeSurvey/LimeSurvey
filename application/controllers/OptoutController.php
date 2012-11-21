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
 *  $Id$
 */

/**
 * optout
 *
 * @package LimeSurvey
 * @copyright 2011
 * @version $Id$
 * @access public
 */
class OptoutController extends LSYii_Controller {

    /**
    *  This function is run when opting out of an individual token table. The other function /optout/participants
    *  opts the user out of ALL survey invitations from the system
    */
    function actiontokens()
    {
        $iSurveyID=Yii::app()->request->getQuery('surveyid');
        $sLanguageCode=Yii::app()->request->getQuery('langcode');
        $sToken=Yii::app()->request->getQuery('token');

        Yii::app()->loadHelper('database');
        Yii::app()->loadHelper('sanitize');

        $sToken = sanitize_token($sToken);

        if (!$iSurveyID) //IF there is no survey id, redirect back to the default public page
        {
            $this->redirect(Yii::app()->getController()->createUrl('/'));
        }
        $iSurveyID = (int)$iSurveyID; //Make sure it's an integer (protect from SQL injects)
        //Check that there is a SID
        //Get passed language from form, so that we dont lose this!
        if (!isset($sLanguageCode) || $sLanguageCode == "" || !$sLanguageCode)
        {
            $sBaseLanguage = Survey::model()->findByPk($iSurveyID)->language;
            Yii::import('application.libraries.Limesurvey_lang', true);
            $clang = new Limesurvey_lang($sBaseLanguage);
        }
        else
        {
            $sLanguageCode = sanitize_languagecode($sLanguageCode);
            Yii::import('application.libraries.Limesurvey_lang', true);
            $clang = new Limesurvey_lang($sLanguageCode);
            $sBaseLanguage = $sLanguageCode;
        }

        Yii::app()->lang = $clang;

        $aSurveyInfo=getSurveyInfo($iSurveyID,$sBaseLanguage);

        if ($aSurveyInfo==false || !tableExists("{{tokens_{$iSurveyID}}}")){
            $sHTML = $clang->gT('This survey does not seem to exist.');
        }
        else
        {
            $aRow = Tokens_dynamic::model($iSurveyID)->getEmailStatus($sToken);

            if ($aRow == false)
            {
                $sHTML = $clang->gT('You are not a participant in this survey.');
            }
            else
            {
                if ($aRow['emailstatus'] == 'OK')
                {
                    Tokens_dynamic::model($iSurveyID)->updateEmailStatus($sToken, 'OptOut');
                    $sHTML = $clang->gT('You have been successfully removed from this survey.');
                }
                else
                {
                    $sHTML = $clang->gT('You have been already removed from this survey.');
                }
            }
        }

        //PRINT COMPLETED PAGE
        if (!$aSurveyInfo['templatedir'])
        {
            $sTemplate=getTemplatePath(Yii::app()->getConfig("defaulttemplate"));
        }
        else
        {
            $sTemplate=getTemplatePath($aSurveyInfo['templatedir']);
        }

        $this->_renderHtml($sHTML,$sTemplate);
    }

    /** 
    * This function is run when opting out of the participants system. The other function /optout/token
    * opts the user out of just a single token/survey invite list
    */
    function actionparticipants()
    {
        $iSurveyID=Yii::app()->request->getQuery('surveyid');
        $sLanguageCode=Yii::app()->request->getQuery('langcode');
        $sToken=Yii::app()->request->getQuery('token');

        Yii::app()->loadHelper('database');
        Yii::app()->loadHelper('sanitize');

        $sToken = sanitize_token($sToken);

        if (!$iSurveyID) //IF there is no survey id, redirect back to the default public page
        {
            $this->redirect(Yii::app()->getController()->createUrl('/'));
        }
        $iSurveyID = (int)$iSurveyID; //Make sure it's an integer (protect from SQL injects)
        //Check that there is a SID
        // Get passed language from form, so that we dont lose this!
        if (!isset($sLanguageCode) || $sLanguageCode == "" || !$sLanguageCode)
        {
            $sBaseLanguage = Survey::model()->findByPk($iSurveyID)->language;
            Yii::import('application.libraries.Limesurvey_lang', true);
            $clang = new Limesurvey_lang($sBaseLanguage);
        }
        else
        {
            $sLanguageCode = sanitize_languagecode($sLanguageCode);
            Yii::import('application.libraries.Limesurvey_lang', true);
            $clang = new Limesurvey_lang($sLanguageCode);
            $sBaseLanguage = $sLanguageCode;
        }

        Yii::app()->lang = $clang;

        $aSurveyInfo=getSurveyInfo($iSurveyID,$sBaseLanguage);

        if ($aSurveyInfo==false || !tableExists("{{tokens_{$iSurveyID}}}")){
            $sHTML = $clang->gT('This survey does not seem to exist.');
        }
        else
        {
            $aRow = Tokens_dynamic::model($iSurveyID)->getEmailStatus($sToken);
            $oToken = Tokens_dynamic::model($iSurveyID)->find('token = :token', array(":token"=>$sToken));

            if ($aRow == false)
            {
                $sHTML = $clang->gT('You are not a participant in this survey.');
            }
            else
            {
                if ($aRow['emailstatus'] == 'OK')
                {
                    Tokens_dynamic::model($iSurveyID)->updateEmailStatus($sToken, 'OptOut');
                    $sHTML = $clang->gT('You have been successfully removed from this survey.');
                }
                else
                {
                    $sHTML = $clang->gT('You have been already removed from this survey.');
                }
                if(!empty($oToken->participant_id) && $oToken->participant_id != "")
                {
                    //Participant also exists in central db
                    $cpdb = Participants::model()->find('participant_id = :participant_id', array(":participant_id"=>$oToken->participant_id));
                    if($cpdb->blacklisted=="Y")
                    {
                        $sHTML .= "<br />";
                        $sHTML .= $clang->gt("You have already been removed from the central participants list for this site");
                    } else
                    {
                        $cpdb->blacklisted='Y';
                        $cpdb->save();
                        $sHTML .= "<br />";
                        $sHTML .= $clang->gT("You have been removed from the central participants list for this site");
                    }
                }
            }
        }

        //PRINT COMPLETED PAGE
        if (!$aSurveyInfo['templatedir'])
        {
            $sTemplate=getTemplatePath(Yii::app()->getConfig("defaulttemplate"));
        }
        else
        {
            $sTemplate=getTemplatePath($aSurveyInfo['templatedir']);
        }

        $this->_renderHtml($sHTML,$sTemplate);
    }

    private function _renderHtml($sHTML, $sTemplate)
    {
        sendCacheHeaders();
        doHeader();
        $data['html'] = $sHTML;
        $data['thistpl'] = $sTemplate;
        $this->render('/opt_view',$data);
        doFooter();
    }

}
