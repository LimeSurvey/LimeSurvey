<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
 * LimeSurvey
 * Copyright (C) 2007 The LimeSurvey Project Team / Carsten Schmitz
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
 * @version $Id$
 * @access public
 */
class OptinController extends LSYii_Controller {

    /**
    * put your comment there...
    * 
    * @param string $surveyid
    * @param string $token
    * @param string $langcode
    */
    function actiontokens($surveyid, $token, $langcode = '')
    {
        Yii::app()->loadHelper('database');
        Yii::app()->loadHelper('sanitize');

        $sLanguageCode = $langcode;
        $iSurveyID = $surveyid;
        $sToken = sanitize_token($token);


        if (!$iSurveyID)
        {
            $this->redirect($this->getController()->createUrl('/'));
        }
        $iSurveyID = (int)$iSurveyID;

        //Check that there is a SID
        // Get passed language from form, so that we dont loose this!
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

        if ($aSurveyInfo == false || !tableExists("{{tokens_{$iSurveyID}}}"))
        {
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
                if ($aRow['emailstatus']=='OptOut')
                {
                    $usresult = Tokens_dynamic::model($iSurveyID)->updateEmailStatus($sToken, 'OK');
                    $sHTML = $clang->gT('You have been successfully added back to this survey.');
                }
                else if ($aRow['emailstatus']=='OK')
                {
                    $sHTML = $clang->gT('You are already a part of this survey.');
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
        $this->_renderHtml($sHTML,$sTemplate,$clang);
    }
    /**
    * put your comment there...
    * 
    * @param string $sHTML
    * @param string $sTemplate
    * @param object $oLanguage
    */
    private function _renderHtml($sHTML,$sTemplate,$oLanguage)
    {
        sendCacheHeaders();
        doHeader();
        $aData['html'] = $sHTML;
        $aData['thistpl'] = $sTemplate;
        $aData['clang'] = $oLanguage;
        $this->render('/opt_view',$aData);
        doFooter();
    }
}
