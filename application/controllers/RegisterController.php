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
*/

/**
* register
*
* @package LimeSurvey
* @copyright 2012
* @access public
*/
class RegisterController extends LSYii_Controller {

    /**
    * put your comment there...
    * 
    * @param integer $surveyid
    */
    function actionAJAXRegisterForm($surveyid)
    {
        Yii::app()->loadHelper('database');
        Yii::app()->loadHelper('replacements');
        $redata = compact(array_keys(get_defined_vars()));
        $iSurveyID = sanitize_int($surveyid);
        $oSurvey = Survey::model()->find('sid=:sid',array(':sid' => $iSurveyID)) or show_error("Can't find survey data");
        $sTemplate=getTemplatePath(validateTemplateDir($oSurvey->template));
        $aData['sid'] = $iSurveyID;
        $aData['startdate'] = $oSurvey->startdate;
        $aData['enddate'] = $oSurvey->expires;
        Yii::import('application.libraries.Limesurvey_lang');
        Yii::app()->lang = new Limesurvey_lang($baselang);
        echo templatereplace(file_get_contents("$sTemplate/register.pstpl"),array(),$redata,'register.php',false,NULL,$aData);
        unset($_SESSIOn['survey_'.$iSurveyID]['register_errormsg']);
    }

    /**
    * Process register form data and take appropriate action
    * 
    * @param integer $surveyid
    * @return
    */
    function actionIndex($surveyid = null)
    {
        $iSurveyID=$surveyid;
        Yii::app()->loadHelper('database');
        Yii::app()->loadHelper('replacements');
        $sLanguage = Yii::app()->request->getPost('lang');
        if($iSurveyID == null)
        {
            $iSurveyID = Yii::app()->request->getPost('sid');
        }
        if (!$iSurveyID)
        {
            Yii::app()->request->redirect(Yii::app()->baseUrl);
        }

        getGlobalSetting('SessionName');
        if (getGlobalSetting('SessionName'))
        {
            $stg_SessionName=$usrow['stg_value'];
            Yii::app()->session->setSessionName("$stg_SessionName-runtime-$iSurveyID");
        }
        else
        {
            Yii::app()->setSessionName("LimeSurveyRuntime-$iSurveyID");
        }

        Yii::app()->session->setCookieParams(array(0, Yii::app()->getConfig('publicurl')));

        // Get passed language from form, so that we dont loose this!
        if (!isset($sLanguage) || $sLanguage == "" || !$sLanguage )
        {
            $sLanguage = Survey::model()->findByPk($iSurveyID)->language;
            Yii::import('application.libraries.Limesurvey_lang');
            Yii::app()->lang = new Limesurvey_lang($sLanguage);
            $clang = Yii::app()->lang;
        } else {
            Yii::import('application.libraries.Limesurvey_lang');
            Yii::app()->lang = new Limesurvey_lang($sLanguage);
            $clang = Yii::app()->lang;
        }

        $aSurveyInfo=getSurveyInfo($iSurveyID,$sLanguage);

        $sRegisterErrorMessage = "";
        // Check the security question's answer
        if (function_exists("ImageCreate") && isCaptchaEnabled('registrationscreen',$aSurveyInfo['usecaptcha']) )
        {
            if (!isset($_POST['loadsecurity']) ||
            !isset($_SESSION['survey_'.$iSurveyID]['secanswer']) ||
            Yii::app()->request->getPost('loadsecurity') != $_SESSION['survey_'.$iSurveyID]['secanswer'])
            {
                $sRegisterErrorMessage .= $clang->gT("The answer to the security question is incorrect.")."<br />\n";
            }
        }

        //Check that the email is a valid style address
        if (!validateEmailAddress(Yii::app()->request->getPost('register_email')))
        {
            $sRegisterErrorMessage .= $clang->gT("The email you used is not valid. Please try again.");
        }

        // Check for additional fields
        $aAttributeInsertData = array();
        foreach (GetParticipantAttributes($iSurveyID) as $sAttributeField => $aAttributes)
        {
            if (empty($aAttributes['show_register']) || $aAttributes['show_register'] != 'Y')
                continue;

            $sValue = sanitize_xss_string(Yii::app()->request->getPost('register_' . $sAttributeField));
            if (trim($sValue) == '' && $aAttributes['mandatory'] == 'Y')
                $sRegisterErrorMessage .= sprintf($clang->gT("%s cannot be left empty"), $aSurveyInfo['attributecaptions'][$sAttributeField]);
            $aAttributeInsertData[$sAttributeField] = $sValue;
        }
        if ($sRegisterErrorMessage != "")
        {
            $_SESSION['survey_'.$iSurveyID]['register_errormsg']=$sRegisterErrorMessage;
            Yii::app()->request->redirect(Yii::app()->createUrl('survey/index/sid/'.$iSurveyID));
        }

        //Check if this email already exists in token database
        $oToken = Tokens_dynamic::model($iSurveyID)->find('email = :email', array(":email"=>Yii::app()->request->getPost('register_email')));
        if (!is_null($oToken))
        {
            $sRegisterErrorMessage=$clang->gT("The email you used has already been registered.");
            $_SESSION['survey_'.$iSurveyID]['register_errormsg']=$sRegisterErrorMessage;
            Yii::app()->request->redirect(Yii::app()->createUrl('survey/index/sid/'.$iSurveyID));
        }

        $bMayInsert = false;

        $iTokenLength = $aSurveyInfo['tokenlength'];
        //if tokenlength is not set or there are other problems use the default value (15)
        if(!$iTokenLength || trim($iTokenLength) == '')
        {
            $iTokenLength = 15;
        }

        while ($bMayInsert != true)
        {
            $sNewToken = randomChars($iTokenLength);
            $sQuery = "SELECT * FROM {{tokens_$iSurveyID}} WHERE token='$sNewToken'";
            $aRow = Yii::app()->db->createCommand($sQuery)->queryRow();
            if (!$aRow) {$bMayInsert = true;}
        }

        $sFirstName=sanitize_xss_string(strip_tags(Yii::app()->request->getPost('register_firstname')));
        $sLastName=sanitize_xss_string(strip_tags(Yii::app()->request->getPost('register_lastname')));
        $sStartDateTime = sanitize_xss_string(Yii::app()->request->getPost('startdate'));
        $sEndDateTime = sanitize_xss_string(Yii::app()->request->getPost('enddate'));

        // Insert new entry into tokens db
        Tokens_dynamic::sid($aSurveyInfo['sid']);
        $oToken = new Tokens_dynamic;
        $oToken->firstname = $sFirstName;
        $oToken->lastname = $sLastName;
        $oToken->email = Yii::app()->request->getPost('register_email');
        $oToken->emailstatus = 'OK';
        $oToken->token = $sNewToken;
        if ($sStartDateTime && $sEndDateTime)
        {
            $oToken->validfrom = $sStartDateTime;
            $oToken->validuntil = $sEndDateTime;
        }
        foreach ($aAttributeInsertData as $k => $v)
            $oToken->$k = $v;
        $oToken->save();

        $iTokenID = getLastInsertID($oToken->tableName());;


        $aReplacementFields["{ADMINNAME}"]=$aSurveyInfo['adminname'];
        $aReplacementFields["{ADMINEMAIL}"]=$aSurveyInfo['adminemail'];
        $aReplacementFields["{SURVEYNAME}"]=$aSurveyInfo['name'];
        $aReplacementFields["{SURVEYDESCRIPTION}"]=$aSurveyInfo['description'];
        $aReplacementFields["{FIRSTNAME}"]=$sFirstName;
        $aReplacementFields["{LASTNAME}"]=$sLastName;
        $aReplacementFields["{EXPIRY}"]=$aSurveyInfo["expiry"];

        $sMessage=$aSurveyInfo['email_register'];
        $sSubject=$aSurveyInfo['email_register_subj'];


        $sFrom = "{$aSurveyInfo['adminname']} <{$aSurveyInfo['adminemail']}>";

        if (getEmailFormat($iSurveyID) == 'html')
        {
            $bUseHTMLEmail = true;
            $surveylink = $this->createAbsoluteUrl($iSurveyID.'/lang-'.$sLanguage.'/tk-'.$sNewToken);
            $optoutlink = $this->createAbsoluteUrl('optout/local/'.$iSurveyID.'/'.$sLanguage.'/'.$sNewToken);
            $optinlink = $this->createAbsoluteUrl('optin/local/'.$iSurveyID.'/'.$sLanguage.'/'.$sNewToken);
            $aReplacementFields["{SURVEYURL}"]="<a href='$surveylink'>".$surveylink."</a>";
            $aReplacementFields["{OPTOUTURL}"]="<a href='$optoutlink'>".$optoutlink."</a>";
            $aReplacementFields["{OPTINURL}"]="<a href='$optinlink'>".$optinlink."</a>";
        }
        else
        {
            $bUseHTMLEmail = false;
            $aReplacementFields["{SURVEYURL}"]= $this->createAbsoluteUrl(''.$iSurveyID.'/lang-'.$sLanguage.'/tk-'.$sNewToken);
            $aReplacementFields["{OPTOUTURL}"]= $this->createAbsoluteUrl('optout/local/'.$iSurveyID.'/'.$sLanguage.'/'.$sNewToken);
            $aReplacementFields["{OPTINURL}"]= $this->createAbsoluteUrl('optin/local/'.$iSurveyID.'/'.$sLanguage.'/'.$sNewToken);
        }

        $sMessage=ReplaceFields($sMessage, $aReplacementFields);
        $sSubject=ReplaceFields($sSubject, $aReplacementFields);

        if (SendEmailMessage($sMessage, $sSubject, Yii::app()->request->getPost('register_email'), $sFrom, Yii::app()->getConfig('sitename'), $bUseHTMLEmail, getBounceEmail($iSurveyID)))
        {
            $dNow = dateShift(date("Y-m-d H:i:s"), "Y-m-d H:i", Yii::app()->getConfig('timeadjust'));
            $query = "UPDATE {{tokens_$iSurveyID}}\n"
            ."SET sent='$dNow' WHERE tid=$iTokenID";
            dbExecuteAssoc($query) or show_error("Unable to execute this query : $query<br />");     //Checked
            $sHTML = "<center>".$clang->gT("Thank you for registering to participate in this survey.")."<br /><br />\n".$clang->gT("An email has been sent to the address you provided with access details for this survey. Please follow the link in that email to proceed.")."<br /><br />\n".$clang->gT("Survey administrator")." {ADMINNAME} ({ADMINEMAIL})";
            $sHTML = ReplaceFields($sHTML, $aReplacementFields);
            $sHTML .= "<br /><br /></center>\n";
        }
        else
        {
            $sHTML = "Email Error";
        }

        //PRINT COMPLETED PAGE
        if (!$aSurveyInfo['template'])
        {
            $sTemplate=getTemplatePath(validateTemplateDir('default'));
        }
        else
        {
            $sTemplate=getTemplatePath(validateTemplateDir($aSurveyInfo['template']));
        }

        sendCacheHeaders();
        doHeader();
        Yii::app()->lang = $clang;
        // fetch the defined variables and pass it to the header footer templates.
        $redata = compact(array_keys(get_defined_vars()));
        $this->_printTemplateContent($sTemplate.'/startpage.pstpl', $redata, __LINE__);
        $this->_printTemplateContent($sTemplate.'/survey.pstpl', $redata, __LINE__);
        echo $html;
        $this->_printTemplateContent($sTemplate.'/endpage.pstpl', $redata, __LINE__);
        
        doFooter();
    }
    
    /**
    * function will parse the templates data
    * @return displays the requested template
    */
    function _printTemplateContent($sTemplateFile, &$redata, $iDebugLine = -1)
    {
        echo templatereplace(file_get_contents($sTemplateFile),array(),$redata,'survey['.$iDebugLine.']');
    }

}
