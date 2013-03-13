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
 *	$Id$
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

    /* This function is run when opting out of an individual token table. The other function /optout/participants
     * opts the user out of ALL survey invitations from the system
     *
     *
     * */
    function actiontokens()
    {
        $surveyid=Yii::app()->request->getQuery('surveyid');
        $langcode=Yii::app()->request->getQuery('langcode');
        $token=Yii::app()->request->getQuery('token');

        Yii::app()->loadHelper('database');
        Yii::app()->loadHelper('sanitize');
        $sLanguageCode = $langcode;

        $iSurveyID = $surveyid;
        $sToken = $token;
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

        if ($thissurvey==false || !tableExists("{{tokens_{$iSurveyID}}}")){
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
                if ($usresult == 'OK')
                {
                    $usresult = Tokens_dynamic::model($iSurveyID)->updateEmailStatus($sToken, 'OptOut');
                    $html = $clang->gT('You have been successfully removed from this survey.');
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

        $this->_renderHtml($html,$thistpl,$thissurvey);
    }

    /* This function is run when opting out of the participants system. The other function /optout/token
     * opts the user out of just a single token/survey invite list
     *
     *
     * */
    function actionparticipants()
    {
        $surveyid=Yii::app()->request->getQuery('surveyid');
        $langcode=Yii::app()->request->getQuery('langcode');
        $token=Yii::app()->request->getQuery('token');

        Yii::app()->loadHelper('database');
        Yii::app()->loadHelper('sanitize');
        $sLanguageCode = $langcode;

        $iSurveyID = $surveyid;
        $sToken = $token;
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

        if ($thissurvey==false || !tableExists("{{tokens_{$iSurveyID}}}")){
            $html = $clang->gT('This survey does not seem to exist.');
        }
        else
        {
            $row = Tokens_dynamic::model($iSurveyID)->getEmailStatus($sToken);
            $datas = Tokens_dynamic::model($iSurveyID)->find('token = :token', array(":token"=>$sToken));

            if ($row == false)
            {
                $html = $clang->gT('You are not a participant in this survey.');
            }
            else
            {
                $usresult = $row['emailstatus'];
                if ($usresult == 'OK')
                {
                    $usresult = Tokens_dynamic::model($iSurveyID)->updateEmailStatus($sToken, 'OptOut');
                    $html = $clang->gT('You have been successfully removed from this survey.');
                }
                else
                {
                    $html = $clang->gT('You have been already removed from this survey.');
                }
                if(!empty($datas->participant_id) && $datas->participant_id != "")
                {
                    //Participant also exists in central db
                    $cpdb = Participants::model()->find('participant_id = :participant_id', array(":participant_id"=>$datas->participant_id));
                    if($cpdb->blacklisted=="Y")
                    {
                        $html .= "<br />";
                        $html .= $clang->gt("You have already been removed from the central participants list for this site");
                    } else
                    {
                        $cpdb->blacklisted='Y';
                        $cpdb->save();
                        $html .= "<br />";
                        $html .= $clang->gT("You have been removed from the central participants list for this site");
                    }
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

        $this->_renderHtml($html,$thistpl, $thissurvey);
    }

    private function _renderHtml($html, $thistpl, $aSurveyInfo)
    {
        sendCacheHeaders();
        doHeader();
        $aSupportData=array('thissurvey'=>$aSurveyInfo);
        echo templatereplace(file_get_contents($thistpl.DIRECTORY_SEPARATOR.'startpage.pstpl'),array(), $aSupportData);
        $data['html'] = $html;
        $data['thistpl'] = $thistpl;
        $this->render('/opt_view',$data);
        echo templatereplace(file_get_contents($thistpl.DIRECTORY_SEPARATOR.'endpage.pstpl'),array(), $aSupportData);
        doFooter();
    }

}
