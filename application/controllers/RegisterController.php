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
*/

/**
* register
*
* @package LimeSurvey
* @copyright 2011
* @access public
*/
class RegisterController extends LSYii_Controller {

    function actionAJAXRegisterForm($surveyid)
    {
        Yii::app()->loadHelper('database');
        Yii::app()->loadHelper('replacements');
        $redata = compact(array_keys(get_defined_vars()));
        $iSurveyID = sanitize_int($surveyid);
        $oSurvey = Survey::model()->find('sid=:sid',array(':sid' => $iSurveyID)) or show_error("Can't find survey data");
        $thistpl=getTemplatePath(validateTemplateDir($oSurvey->template));
        $data['sid'] = $iSurveyID;
        $data['startdate'] = $oSurvey->startdate;
        $data['enddate'] = $oSurvey->expires;
        $data['thissurvey'] = getSurveyInfo($iSurveyID , $oSurvey->language);
        Yii::import('application.libraries.Limesurvey_lang');
        Yii::app()->lang = new Limesurvey_lang($oSurvey->language);
        echo templatereplace(file_get_contents("$thistpl/register.pstpl"),array(),$data,'register.php',false,NULL,$data);
        unset($_SESSION['survey_'.$iSurveyID]['register_errormsg']);

    }

    /**
    * register::index()
    * Process register form data and take appropriate action
    * @return
    */
    function actionIndex($iSurveyID = null)
    {
        Yii::app()->loadHelper('database');
        Yii::app()->loadHelper('replacements');
        $sLanguage = Yii::app()->request->getParam('lang','');
        if($iSurveyID == null)
        {
            $iSurveyID = Yii::app()->request->getPost('sid');
        }
        if (!$iSurveyID)
        {
            $this->redirect(Yii::app()->baseUrl);
        }

        if ($sLanguage=="" )
        {
            $sBaseLanguage = Survey::model()->findByPk($iSurveyID)->language;
        }
        else
        {
            $sBaseLanguage = $sLanguage;
        }
        Yii::import('application.libraries.Limesurvey_lang');
        Yii::app()->lang = new Limesurvey_lang($sBaseLanguage);
        $clang = Yii::app()->lang;
        $thissurvey=getSurveyInfo($iSurveyID,$sBaseLanguage);

        $register_errormsg = "";
        // Check the security question's answer
        if (function_exists("ImageCreate") && isCaptchaEnabled('registrationscreen',$thissurvey['usecaptcha']) )
        {
            if (!isset($_POST['loadsecurity']) ||
            !isset($_SESSION['survey_'.$iSurveyID]['secanswer']) ||
            Yii::app()->request->getPost('loadsecurity') != $_SESSION['survey_'.$iSurveyID]['secanswer'])
            {
                $register_errormsg .= $clang->gT("The answer to the security question is incorrect.")."<br />\n";
            }
        }

        //Check that the email is a valid style address
        if (!validateEmailAddress(Yii::app()->request->getPost('register_email')))
        {
            $register_errormsg .= $clang->gT("The email you used is not valid. Please try again.");
        }

        // Check for additional fields
        $attributeinsertdata = array();
        foreach (GetParticipantAttributes($iSurveyID) as $field => $data)
        {
            if (empty($data['show_register']) || $data['show_register'] != 'Y')
                continue;

            $value = sanitize_xss_string(Yii::app()->request->getPost('register_' . $field));
            if (trim($value) == '' && $data['mandatory'] == 'Y')
                $register_errormsg .= sprintf($clang->gT("%s cannot be left empty"), $thissurvey['attributecaptions'][$field]);
            $attributeinsertdata[$field] = $value;
        }
        if ($register_errormsg != "")
        {
            $_SESSION['survey_'.$iSurveyID]['register_errormsg']=$register_errormsg;
            $this->redirect($this->createUrl("survey/index/sid/{$iSurveyID}",array('lang'=>$sBaseLanguage)));
        }

        //Check if this email already exists in token database
        $oToken=TokenDynamic::model($iSurveyID)->find('email=:email',array(':email'=>Yii::app()->request->getPost('register_email')));
        if ($oToken)
        {
            $register_errormsg=$clang->gT("The email you used has already been registered.");
            $_SESSION['survey_'.$iSurveyID]['register_errormsg']=$register_errormsg;
            $this->redirect($this->createUrl("survey/index/sid/{$iSurveyID}",array('lang'=>$sBaseLanguage)));
            //include "index.php";
            //exit;
        }

        $mayinsert = false;

        // Get the survey settings for token length

        $tokenlength = $thissurvey['tokenlength'];
        //if tokenlength is not set or there are other problems use the default value (15)
        if(!isset($tokenlength) || $tokenlength == '')
        {
            $tokenlength = 15;
        }

        while ($mayinsert != true)
        {
            $newtoken = randomChars($tokenlength);
            $oTokenExist=TokenDynamic::model($iSurveyID)->find('token=:token',array(':token'=>$newtoken));
            if (!$oTokenExist) {$mayinsert = true;}
        }

        $postfirstname=sanitize_xss_string(strip_tags(Yii::app()->request->getPost('register_firstname')));
        $postlastname=sanitize_xss_string(strip_tags(Yii::app()->request->getPost('register_lastname')));
        $starttime = sanitize_xss_string(Yii::app()->request->getPost('startdate'));
        $endtime = sanitize_xss_string(Yii::app()->request->getPost('enddate'));
        /*$postattribute1=sanitize_xss_string(strip_tags(returnGlobal('register_attribute1')));
        $postattribute2=sanitize_xss_string(strip_tags(returnGlobal('register_attribute2')));   */

        // Insert new entry into tokens db
        $oToken = Token::create($thissurvey['sid']);
        $oToken->firstname = $postfirstname;
        $oToken->lastname = $postlastname;
        $oToken->email = Yii::app()->request->getPost('register_email');
        $oToken->emailstatus = 'OK';
        $oToken->token = $newtoken;
        if ($starttime && $endtime)
        {
            $oToken->validfrom = $starttime;
            $oToken->validuntil = $endtime;
        }
        $oToken->setAttributes($attributeinsertdata, false);
        $result = $oToken->save();

        //$tid = $oToken->tid;// Not needed any more
        $fieldsarray["{ADMINNAME}"]=$thissurvey['adminname'];
        $fieldsarray["{ADMINEMAIL}"]=$thissurvey['adminemail'];
        $fieldsarray["{SURVEYNAME}"]=$thissurvey['name'];
        $fieldsarray["{SURVEYDESCRIPTION}"]=$thissurvey['description'];
        $fieldsarray["{FIRSTNAME}"]=$postfirstname;
        $fieldsarray["{LASTNAME}"]=$postlastname;
        $fieldsarray["{EXPIRY}"]=$thissurvey["expiry"];
        $fieldsarray["{TOKEN}"]=$oToken->token;
        $fieldsarray["{EMAIL}"]=$oToken->email;

        $token=$oToken->token;
        
        
        $message=preg_replace("/{TOKEN:([A-Z0-9_]+)}/","{"."$1"."}",$thissurvey['email_register']);
        $subject=preg_replace("/{TOKEN:([A-Z0-9_]+)}/","{"."$1"."}",$thissurvey['email_register_subj']);


        $from = "{$thissurvey['adminname']} <{$thissurvey['adminemail']}>";

        $surveylink = $this->createAbsoluteUrl("/survey/index/sid/{$iSurveyID}",array('lang'=>$sBaseLanguage,'token'=>$newtoken));
        $optoutlink = $this->createAbsoluteUrl("/optout/tokens/surveyid/{$iSurveyID}",array('langcode'=>$sBaseLanguage,'token'=>$newtoken));
        $optinlink = $this->createAbsoluteUrl("/optin/tokens/surveyid/{$iSurveyID}",array('langcode'=>$sBaseLanguage,'token'=>$newtoken));
        if (getEmailFormat($iSurveyID) == 'html')
        {
            $useHtmlEmail = true;
            $fieldsarray["{SURVEYURL}"]="<a href='$surveylink'>".$surveylink."</a>";
            $fieldsarray["{OPTOUTURL}"]="<a href='$optoutlink'>".$optoutlink."</a>";
            $fieldsarray["{OPTINURL}"]="<a href='$optinlink'>".$optinlink."</a>";
        }
        else
        {
            $useHtmlEmail = false;
            $fieldsarray["{SURVEYURL}"]= $surveylink;
            $fieldsarray["{OPTOUTURL}"]= $optoutlink;
            $fieldsarray["{OPTINURL}"]= $optinlink;
        }

        $message=ReplaceFields($message, $fieldsarray);
        $subject=ReplaceFields($subject, $fieldsarray);

        $html = ""; //Set variable
        $sitename =  Yii::app()->getConfig('sitename');

        if (SendEmailMessage($message, $subject, Yii::app()->request->getPost('register_email'), $from, $sitename,$useHtmlEmail,getBounceEmail($iSurveyID)))
        {
            // TLR change to put date into sent
            $today = dateShift(date("Y-m-d H:i:s"), "Y-m-d H:i", Yii::app()->getConfig('timeadjust'));
            $oToken->sent=$today;
            $oToken->save();
            $html="<div id='wrapper' class='message tokenmessage'>"
                . "<p>".$clang->gT("Thank you for registering to participate in this survey.")."</p>\n"
                . "<p>".$clang->gT("An email has been sent to the address you provided with access details for this survey. Please follow the link in that email to proceed.")."</p>\n"
                . "<p>".$clang->gT("Survey administrator")." {ADMINNAME} ({ADMINEMAIL})</p>"
                . "</div>\n";
            $html=ReplaceFields($html, $fieldsarray);
        }
        else
        {
            $html="Email Error";
        }

        //PRINT COMPLETED PAGE
        if (!$thissurvey['template'])
        {
            $thistpl=getTemplatePath(validateTemplateDir('default'));
        }
        else
        {
            $thistpl=getTemplatePath(validateTemplateDir($thissurvey['template']));
        }

        // Same fix than http://bugs.limesurvey.org/view.php?id=8441
        ob_start(function($buffer, $phase) {
            App()->getClientScript()->render($buffer);
            App()->getClientScript()->reset();
            return $buffer;
        });
        ob_implicit_flush(false);
        sendCacheHeaders();
        doHeader();
        Yii::app()->lang = $clang;
        // fetch the defined variables and pass it to the header footer templates.
        $redata = compact(array_keys(get_defined_vars()));
        $this->_printTemplateContent($thistpl.'/startpage.pstpl', $redata, __LINE__);
        $this->_printTemplateContent($thistpl.'/survey.pstpl', $redata, __LINE__);
        echo $html;
        $this->_printTemplateContent($thistpl.'/endpage.pstpl', $redata, __LINE__);
        doFooter();
        ob_flush();
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
