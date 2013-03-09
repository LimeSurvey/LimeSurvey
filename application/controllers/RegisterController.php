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
* register
*
* @package LimeSurvey
* @copyright 2011
* @version $Id$
* @access public
*/
class RegisterController extends LSYii_Controller {

    function actionAJAXRegisterForm
    ($surveyid)
    {
        Yii::app()->loadHelper('database');
        Yii::app()->loadHelper('replacements');
        $redata = compact(array_keys(get_defined_vars()));
        $surveyid = sanitize_int($surveyid);
        $row = Survey::model()->find('sid=:sid',array(':sid' => $surveyid)) or show_error("Can't find survey data");
        $thistpl=getTemplatePath(validateTemplateDir($row->template));
        $data['sid'] = $surveyid;
        $data['startdate'] = $row->startdate;
        $data['enddate'] = $row->expires;
        Yii::import('application.libraries.Limesurvey_lang');
        Yii::app()->lang = new Limesurvey_lang($baselang);
        echo templatereplace(file_get_contents("$thistpl/register.pstpl"),array(),$redata,'register.php',false,NULL,$data);
        unset($_SESSIOn['survey_'.$surveyid]['register_errormsg']);

    }

    /**
    * register::index()
    * Process register form data and take appropriate action
    * @return
    */
    function actionIndex($surveyid = null)
    {
        Yii::app()->loadHelper('database');
        Yii::app()->loadHelper('replacements');
        $postlang = Yii::app()->request->getPost('lang');
        if($surveyid == null)
        {
            $surveyid = Yii::app()->request->getPost('sid');
        }
        if (!$surveyid)
        {
            Yii::app()->request->redirect(Yii::app()->baseUrl);
        }

        // Get passed language from form, so that we dont loose this!
        if (!isset($postlang) || $postlang == "" || !$postlang )
        {
            $baselang = Survey::model()->findByPk($surveyid)->language;
            Yii::import('application.libraries.Limesurvey_lang');
            Yii::app()->lang = new Limesurvey_lang($baselang);
            $clang = Yii::app()->lang;
        } else {
            Yii::import('application.libraries.Limesurvey_lang');
            Yii::app()->lang = new Limesurvey_lang($postlang);
            $clang = Yii::app()->lang;
            $baselang = $postlang;
        }

        $thissurvey=getSurveyInfo($surveyid,$baselang);

        $register_errormsg = "";
        // Check the security question's answer
        if (function_exists("ImageCreate") && isCaptchaEnabled('registrationscreen',$thissurvey['usecaptcha']) )
        {
            if (!isset($_POST['loadsecurity']) ||
            !isset($_SESSION['survey_'.$surveyid]['secanswer']) ||
            Yii::app()->request->getPost('loadsecurity') != $_SESSION['survey_'.$surveyid]['secanswer'])
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
        foreach (GetParticipantAttributes($surveyid) as $field => $data)
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
            $_SESSION['survey_'.$surveyid]['register_errormsg']=$register_errormsg;
            Yii::app()->request->redirect(Yii::app()->createUrl('survey/index/sid/'.$surveyid));
        }

        //Check if this email already exists in token database
        $query = "SELECT email FROM {{tokens_$surveyid}}\n"
        . "WHERE email = '".sanitize_email(Yii::app()->request->getPost('register_email'))."'";
        $usrow = Yii::app()->db->createCommand($query)->queryRow();
        if ($usrow)
        {
            $register_errormsg=$clang->gT("The email you used has already been registered.");
            $_SESSION['survey_'.$surveyid]['register_errormsg']=$register_errormsg;
            Yii::app()->request->redirect(Yii::app()->createUrl('survey/index/sid/'.$surveyid));
            //include "index.php";
            //exit;
        }

        $mayinsert = false;

        // Get the survey settings for token length
        //$this->load->model("surveys_model");
        $tlresult = Survey::model()->findAllByAttributes(array("sid"=>$surveyid));
        if (isset($tlresult[0])) {
            $tlrow = $tlresult[0];
        }
        else
        {
            $tlrow = $tlresult;
        }
        $tokenlength = $tlrow['tokenlength'];
        //if tokenlength is not set or there are other problems use the default value (15)
        if(!isset($tokenlength) || $tokenlength == '')
        {
            $tokenlength = 15;
        }

        while ($mayinsert != true)
        {
            $newtoken = randomChars($tokenlength);
            $ntquery = "SELECT * FROM {{tokens_$surveyid}} WHERE token='$newtoken'";
            $usrow = Yii::app()->db->createCommand($ntquery)->queryRow();
            if (!$usrow) {$mayinsert = true;}
        }

        $postfirstname=sanitize_xss_string(strip_tags(Yii::app()->request->getPost('register_firstname')));
        $postlastname=sanitize_xss_string(strip_tags(Yii::app()->request->getPost('register_lastname')));
        $starttime = sanitize_xss_string(Yii::app()->request->getPost('startdate'));
        $endtime = sanitize_xss_string(Yii::app()->request->getPost('enddate'));
        /*$postattribute1=sanitize_xss_string(strip_tags(returnGlobal('register_attribute1')));
        $postattribute2=sanitize_xss_string(strip_tags(returnGlobal('register_attribute2')));   */

        // Insert new entry into tokens db
        Tokens_dynamic::sid($thissurvey['sid']);
        $token = new Tokens_dynamic;
        $token->firstname = $postfirstname;
        $token->lastname = $postlastname;
        $token->email = Yii::app()->request->getPost('register_email');
        $token->emailstatus = 'OK';
        $token->token = $newtoken;
        if ($starttime && $endtime)
        {
            $token->validfrom = $starttime;
            $token->validuntil = $endtime;
        }
        foreach ($attributeinsertdata as $k => $v)
            $token->$k = $v;
        $result = $token->save();

        /**
        $result = $connect->Execute($query, array($postfirstname,
        $postlastname,
        returnGlobal('register_email'),
        'OK',
        $newtoken)

        //                             $postattribute1,   $postattribute2)
        ) or safeDie ($query."<br />".$connect->ErrorMsg());  //Checked - According to adodb docs the bound variables are quoted automatically
        */
        $tid = getLastInsertID($token->tableName());;
        $token=$token->token;

        $fieldsarray["{ADMINNAME}"]=$thissurvey['adminname'];
        $fieldsarray["{ADMINEMAIL}"]=$thissurvey['adminemail'];
        $fieldsarray["{SURVEYNAME}"]=$thissurvey['name'];
        $fieldsarray["{SURVEYDESCRIPTION}"]=$thissurvey['description'];
        $fieldsarray["{FIRSTNAME}"]=$postfirstname;
        $fieldsarray["{LASTNAME}"]=$postlastname;
        $fieldsarray["{EXPIRY}"]=$thissurvey["expiry"];

        $message=$thissurvey['email_register'];
        $subject=$thissurvey['email_register_subj'];


        $from = "{$thissurvey['adminname']} <{$thissurvey['adminemail']}>";

        $surveylink = $this->createAbsoluteUrl("/survey/index/sid/{$surveyid}",array('lang'=>$baselang,'token'=>$newtoken));
        $optoutlink = $this->createAbsoluteUrl("/optout/tokens/surveyid/{$surveyid}",array('langcode'=>'fr','token'=>'newtoken'));
        $optinlink = $this->createAbsoluteUrl("/optin/tokens/surveyid/{$surveyid}",array('langcode'=>'fr','token'=>'newtoken'));
        if (getEmailFormat($surveyid) == 'html')
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

        if (SendEmailMessage($message, $subject, Yii::app()->request->getPost('register_email'), $from, $sitename,$useHtmlEmail,getBounceEmail($surveyid)))
        {
            // TLR change to put date into sent
            $today = dateShift(date("Y-m-d H:i:s"), "Y-m-d H:i", Yii::app()->getConfig('timeadjust'));
            $query = "UPDATE {{tokens_$surveyid}}\n"
            ."SET sent='$today' WHERE tid=$tid";
            $result=dbExecuteAssoc($query) or show_error("Unable to execute this query : $query<br />");     //Checked
            $html="<center>".$clang->gT("Thank you for registering to participate in this survey.")."<br /><br />\n".$clang->gT("An email has been sent to the address you provided with access details for this survey. Please follow the link in that email to proceed.")."<br /><br />\n".$clang->gT("Survey administrator")." {ADMINNAME} ({ADMINEMAIL})";
            $html=ReplaceFields($html, $fieldsarray);
            $html .= "<br /><br /></center>\n";
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
