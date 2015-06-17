<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
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
class OptinController extends LSYii_Controller {

     public $layout = 'bare';
     public $defaultAction = 'tokens';

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
            $this->redirect(array('/'));
        }
        $iSurveyID = (int)$iSurveyID;

        //Check that there is a SID
        // Get passed language from form, so that we dont loose this!
        if (!isset($sLanguageCode) || $sLanguageCode == "" || !$sLanguageCode)
        {
            $sBaseLanguage = Survey::model()->findByPk($iSurveyID)->language;
        }
        else
        {
            $sBaseLanguage = sanitize_languagecode($sLanguageCode);
        }

        Yii::app()->setLanguage($sBaseLanguage);

        $aSurveyInfo=getSurveyInfo($iSurveyID,$sBaseLanguage);

        if ($aSurveyInfo == false || !tableExists("{{tokens_{$iSurveyID}}}"))
        {
            throw new CHttpException(404, "This survey does not seem to exist. It may have been deleted or the link you were given is outdated or incorrect.");
        }
        else
        {
            LimeExpressionManager::singleton()->loadTokenInformation($iSurveyID,$token,false);
            $oToken = Token::model($iSurveyID)->findByAttributes(array('token' => $token));

            if (!isset($oToken))
            {
                $sMessage = gT('You are not a participant in this survey.');
            }
            else
            {
                if ($oToken->emailstatus =='OptOut')
                {
                    $oToken->emailstatus = 'OK';
                    $oToken->save();
                    $sMessage = gT('You have been successfully added back to this survey.');
                }
                elseif ($oToken->emailstatus == 'OK')
                {
                    $sMessage = gT('You are already a part of this survey.');
                }
                else
                {
                    $sMessage = gT('You have been already removed from this survey.');
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
        $this->_renderHtml($sMessage,$sTemplate,$aSurveyInfo);
    }

    private function _renderHtml($html,$thistpl, $aSurveyInfo)
    {
        sendCacheHeaders();
        doHeader();
        $aSupportData=array('thissurvey'=>$aSurveyInfo);
        echo templatereplace(file_get_contents($thistpl.DIRECTORY_SEPARATOR.'startpage.pstpl'),array(), $aSupportData);
        $aData['html'] = $html;
        $aData['thistpl'] = $thistpl;
        $this->render('/opt_view',$aData);
        echo templatereplace(file_get_contents($thistpl.DIRECTORY_SEPARATOR.'endpage.pstpl'),array(), $aSupportData);
        doFooter();
    }

}
