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

    function actiontokens($surveyid, $token, $langcode = '')
    {
        Yii::app()->loadHelper('database');
        Yii::app()->loadHelper('sanitize');
        $sLanguageCode = $langcode;
        $iSurveyID = $surveyid;
        $sToken = $token;
        $sToken = sanitize_token($sToken);

        if (!$iSurveyID)
        {
            $this->redirect($this->getController()->createUrl('/'));
        }
        $iSurveyID = (int)$iSurveyID;

        //Check that there is a SID
        // Get passed language from form, so that we dont loose this!
        if (!isset($sLanguageCode) || $sLanguageCode == "" || !$sLanguageCode)
        {
            $baselang = Survey::model()->findByPk($iSurveyID)->language;
            Yii::import('application.libraries.Limesurvey_lang', true);
            $clang = new Limesurvey_lang($baselang);
        }
        else
        {
            $sLanguageCode = sanitize_languagecode($sLanguageCode);
            Yii::import('application.libraries.Limesurvey_lang', true);
            $clang = new Limesurvey_lang($sLanguageCode);
            $baselang = $sLanguageCode;
        }

        Yii::app()->lang = $clang;

        $thissurvey=getSurveyInfo($iSurveyID,$baselang);

        if ($thissurvey == false || !tableExists("{{tokens_{$iSurveyID}}}"))
        {
            $html = $clang->gT('This survey does not seem to exist.');
        }
        else
        {
            $row = Tokens_dynamic::model($iSurveyID)->getEmailStatus($sToken);

            if ($row == false)
            {
                $html = $clang->gT('You are not a participant in this survey.');
            }
            else
            {
                $usresult = $row['emailstatus'];
                if ($usresult=='OptOut')
                {
                    $usresult = Tokens_dynamic::model($iSurveyID)->updateEmailStatus($sToken, 'OK');
                    $html = $clang->gT('You have been successfully added back to this survey.');
                }
                else if ($usresult=='OK')
                {
                    $html = $clang->gT('You are already a part of this survey.');
                }
                else
                {
                    $html = $clang->gT('You have been already removed from this survey.');
                }
            }
        }

        //PRINT COMPLETED PAGE
        if (!$thissurvey['templatedir'])
        {
            $thistpl=getTemplatePath(Yii::app()->getConfig("defaulttemplate"));
        }
        else
        {
            $thistpl=getTemplatePath($thissurvey['templatedir']);
        }
        $this->_renderHtml($html,$thistpl,$clang,$thissurvey);
    }

    private function _renderHtml($html,$thistpl, $oLanguage, $aSurveyInfo)
    {
        sendCacheHeaders();
        doHeader();
        $aSupportData=array('thissurvey'=>$aSurveyInfo, 'clang'=>$oLanguage);
        echo templatereplace(file_get_contents($thistpl.DIRECTORY_SEPARATOR.'startpage.pstpl'),array(), $aSupportData);
        $data['html'] = $html;
        $data['thistpl'] = $thistpl;
        $this->render('/opt_view',$data);
        echo templatereplace(file_get_contents($thistpl.DIRECTORY_SEPARATOR.'endpage.pstpl'),array(), $aSupportData);
        doFooter();
    }

}
