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

    public $layout = 'bare';

    function actionAJAXRegisterForm($surveyid)
    {
        Yii::app()->loadHelper('database');
        Yii::app()->loadHelper('replacements');
        $redata = compact(array_keys(get_defined_vars()));
        $iSurveyId = $surveyid;
        $oSurvey = Survey::model()->find('sid=:sid',array(':sid' => $iSurveyId));
        if (!$oSurvey){
            throw new CHttpException(404, "The survey in which you are trying to participate does not seem to exist. It may have been deleted or the link you were given is outdated or incorrect.");
        }
        // Don't test if survey allow registering .....
        $sLanguage = Yii::app()->request->getParam('lang',$oSurvey->language);
        Yii::import('application.libraries.Limesurvey_lang');
        Yii::app()->lang = new Limesurvey_lang($sLanguage);

        $thistpl=getTemplatePath(validateTemplateDir($oSurvey->template));
        $data['sid'] = $iSurveyId;
        $data['startdate'] = $oSurvey->startdate;
        $data['enddate'] = $oSurvey->expires;
        $data['thissurvey'] = getSurveyInfo($iSurveyId , $oSurvey->language);
        echo self::getRegisterForm($iSurveyId);
        Yii::app()->end();
    }

    /**
    * Default action register
    * Process register form data and take appropriate action
    * @param $sid Survey Id to register 
    * @param $aRegisterErrors array of errors when try to register
    * @return
    */
    function actionIndex($sid = null)
    {

        if(!is_null($sid))
            $iSurveyId=$sid;
        else
            $iSurveyId=Yii::app()->request->getPost('sid');
        $oSurvey=Survey::model()->find("sid=:sid",array(':sid'=>$iSurveyId));

        $sLanguage = Yii::app()->request->getParam('lang');
        if (!$sLanguage)
        {
            $sLanguage = Yii::app()->request->getPost('register_lang',Survey::model()->findByPk($iSurveyId)->language);
        }

        if (!$oSurvey){
            throw new CHttpException(404, "The survey in which you are trying to participate does not seem to exist. It may have been deleted or the link you were given is outdated or incorrect.");
        }elseif($oSurvey->allowregister!='Y' || !tableExists("{{tokens_{$iSurveyId}}}")){
            throw new CHttpException(404,"The survey in which you are trying to register don't accept registration. It may have been updated or the link you were given is outdated or incorrect.");
        }
        elseif(!is_null($oSurvey->expires) && $oSurvey->expires < dateShift(date("Y-m-d H:i:s"), "Y-m-d H:i", Yii::app()->getConfig('timeadjust'))){
            $this->redirect(array('survey/index','sid'=>$iSurveyId,'lang'=>$sLanguage));
        }

        Yii::import('application.libraries.Limesurvey_lang');
        Yii::app()->lang = new Limesurvey_lang($sLanguage);
        // Test if we come from register form (and submit)
        
        $aSended=array('sent'=>false,'message'=>'');
        // New event to replace complety register system
        $event = new PluginEvent('beforeRegister');
        $event->set('surveyid', $iSurveyId);
        $event->set('lang', $sLanguage);
        App()->getPluginManager()->dispatchEvent($event);

        $bIsSubmited=$event->get('submited',Yii::app()->request->getPost('register_email',false));
        $aSended=$event->get('aSended',array('sent'=>false,'message'=>''));

        if($bIsSubmited!==false){
            $aRegisterErrors=self::getRegisterErrors($iSurveyId);
            if(empty($aRegisterErrors)){
                $aSended=self::sendRegistrationEmail($iSurveyId);
            }
        }else{
            $aRegisterErrors=null;
        }

        $aData['surveyid']=$surveyid=$iSurveyId;
        $aData['thissurvey']=getSurveyInfo($iSurveyId,$sLanguage);
        $sTemplate=getTemplatePath(validateTemplateDir($aData['thissurvey']['template']));
        Yii::app()->setConfig('surveyID',$iSurveyId);//Needed for languagechanger
        $aData['languagechanger']=makeLanguageChangerSurvey($sLanguage);
        $aData['sitename']=Yii::app()->getConfig('sitename');
        // Work only with controllers survey : did we fix it and use register url for registering ?
        sendCacheHeaders();
        doHeader();
        echo templatereplace(file_get_contents("{$sTemplate}/startpage.pstpl"),array(),$aData);
        echo templatereplace(file_get_contents("{$sTemplate}/survey.pstpl"),array(),$aData);
        if(!$aSended['sent']){
            echo self::getRegisterForm($iSurveyId,array($aSended['message']));
        }else{
            echo templatereplace($aSended['message']);
        }
        echo templatereplace(file_get_contents("{$sTemplate}/endpage.pstpl"),array(),$aData);
        doFooter();
#        $aViewData['sTemplate']=$sTemplate;
#        $aViewData['aData']=$aData;
#        $aViewData['content']=self::getRegisterForm($iSurveyId);
#        $this->render('display',$aViewData);// Need register/display.php view
    }

    /**
    * Validate a register form
    * @param $iSurveyId Survey Id to register 
    * @return array of errors when try to register (empty array => no error)
    */
    public function getRegisterErrors($iSurveyId){
        $aRegisterErrors=array();
        $clang = Yii::app()->lang;
        $aSurveyInfo=getSurveyInfo($iSurveyId,$clang->langcode);

        // Check the security question's answer
        if (function_exists("ImageCreate") && isCaptchaEnabled('registrationscreen',$aSurveyInfo['usecaptcha']) )
        {
            $sLoadsecurity=Yii::app()->request->getPost('loadsecurity','');
            $sSecAnswer=(isset($_SESSION['survey_'.$iSurveyId]['secanswer']))?$_SESSION['survey_'.$iSurveyId]['secanswer']:"";
            if ($sLoadsecurity!=$sSecAnswer)
            {
                $aRegisterErrors[] = $clang->gT("The answer to the security question is incorrect.");
            }
        }


        //Check that the email is a valid style address
        $sRegisterEmail=Yii::app()->request->getPost('register_email','');
        if($sRegisterEmail==""){
            $aRegisterErrors[]= $clang->gT("You must enter a valid email. Please try again.");
        }elseif (!validateEmailAddress($sRegisterEmail)){
            $aRegisterErrors[]= $clang->gT("The email you used is not valid. Please try again.");
        }

        //Check and validate attribute 
        $aRegisterAttributes=$aSurveyInfo['attributedescriptions'];
        foreach ($aSurveyInfo['attributedescriptions'] as $key => $aAttribute)
         {
            if ($aAttribute['show_register'] == 'Y' && $aAttribute['mandatory'] == 'Y')
            {
                $sRegisterAttribute=Yii::app()->request->getPost('register_'.$key);
                if(empty($sRegisterAttribute)){
                    $sAttributeCaption=($aSurveyInfo['attributecaptions'][$key]?$aSurveyInfo['attributecaptions'][$key] : ($aAttribute['description']?$aAttribute['description'] : $key));
                    $aRegisterErrors[]= sprintf($clang->gT("%s cannot be left empty").".", $sAttributeCaption);
                }
            }
        }
        return $aRegisterErrors;
    }

    public function getRegisterForm($iSurveyId,$aRegisterErrors = null){
        $clang = Yii::app()->lang;
        $aSurveyInfo=getSurveyInfo($iSurveyId,$clang->langcode);
        $sTemplate=getTemplatePath(validateTemplateDir($aSurveyInfo['template']));

        // Event to replace register form
        $event = new PluginEvent('beforeRegisterForm');
        $event->set('surveyid', $iSurveyId);
        $event->set('lang', $clang->langcode);
        $event->set('aRegistersErrors', $aRegisterErrors);
        App()->getPluginManager()->dispatchEvent($event);
        if(!is_null($event->get('registerForm')))
            return $event->get('registerForm');
        // Allways keep the value
        $sFirstName=Yii::app()->request->getPost('register_firstname','');
        $sLastName=Yii::app()->request->getPost('register_lastname','');
        $sEmail=Yii::app()->request->getPost('register_email','');
        $aRegisterAttributes=$aSurveyInfo['attributedescriptions'];
        foreach($aRegisterAttributes as $key=>$aRegisterAttribute){
            if($aRegisterAttribute['show_register']!='Y'){
                unset($aRegisterAttributes[$key]);
            }else{
                $aRegisterAttributes[$key]['caption']=($aSurveyInfo['attributecaptions'][$key]?$aSurveyInfo['attributecaptions'][$key] : ($aRegisterAttribute['description']?$aRegisterAttribute['description'] : $key));
                $aRegisterAttributes[$key]['value']=Yii::app()->request->getPost("register_{$key}",'');
            }
        }
        $aData['iSurveyId'] = $iSurveyId;
        $aData['sLanguage'] = $clang->langcode;
        $aData['clang'] = $clang;
        $aData['sFirstName'] = $sFirstName;
        $aData['sLastName'] = $sLastName;
        $aData['sEmail'] = $sEmail;
        $aData['aExtraAttributes']=$aRegisterAttributes;
        $aData['urlAction']=App()->createUrl('register/index',array('sid'=>$iSurveyId));
        $aData['bCaptcha'] = function_exists("ImageCreate") && isCaptchaEnabled('registrationscreen', $aSurveyInfo['usecaptcha']);
        $aReplacement['REGISTERFORM']=$this->renderPartial('registerForm',$aData,true);
        if(is_array($aRegisterErrors))
            $sRegisterError=implode('<br />',$aRegisterErrors);
        else
            $sRegisterError='';
        $aReplacement['REGISTERERROR'] = $sRegisterError;
        $aReplacement['REGISTERMESSAGE1'] = $clang->gT("You must be registered to complete this survey");
        $aReplacement['REGISTERMESSAGE2'] = $clang->gT("You may register for this survey if you wish to take part.")."<br />\n".$clang->gT("Enter your details below, and an email containing the link to participate in this survey will be sent immediately.");
        $aData['thissurvey'] = $aSurveyInfo;
        Yii::app()->setConfig('surveyID',$iSurveyId);//Needed for languagechanger
        $aData['languagechanger'] = makeLanguageChangerSurvey($clang->langcode);
        return templatereplace(file_get_contents("$sTemplate/register.pstpl"),$aReplacement,$aData);
    }

    /**
    * Send the register email with $_POST value
    * @param $iSurveyId Survey Id to register 
    * @return array sent: boolean, message :the message to be shown
    */
    public function sendRegistrationEmail($iSurveyId){
 
        $clang = Yii::app()->lang;
        $aSurveyInfo=getSurveyInfo($iSurveyId,$clang->langcode);
        $sLanguage=$clang->langcode;

        // Fill needed information
        $sFirstName=sanitize_xss_string(Yii::app()->request->getPost('register_firstname',''));
        $sLastName=sanitize_xss_string(Yii::app()->request->getPost('register_lastname',''));
        $sEmail=trim(Yii::app()->request->getPost('register_email',''));
        $aRegisterAttributes=$aSurveyInfo['attributedescriptions'];
        $aAttribute=array();
        foreach($aRegisterAttributes as $key=>$aRegisterAttribute){
            if($aRegisterAttribute['show_register']=='Y'){
                $aAttribute[$key]= sanitize_xss_string(Yii::app()->request->getPost('register_'.$key,''));
            }
        }

        // Now construct the text returned
        $sMessage=$sMailSuccess=$sMailError="";
        $oToken=TokenDynamic::model($iSurveyId)->find('email=:email',array(':email'=>$sEmail));
        if ($oToken)
         {
            if($oToken->usesleft<1 && $aSurveyInfo['alloweditaftercompletion']!='Y')
            {
                $sMailError=$clang->gt("The mail address you have entered is already registered an the survey has been completed.");
            }
            elseif(strtolower(substr(trim($oToken->emailstatus),0,6))==="optout")// And global blacklisting ?
            {
                $sMailError=$clang->gt("This email address is already registered but someone ask to not receive new email again.");
            }
            elseif(!$oToken->emailstatus && $oToken->emailstatus!="OK")
            {
                $sMailError=$clang->gt("This email address is already registered but the email adress was bounced.");
            }
            else
            {
                $iTokenId=$oToken->tid;
                $sMailSuccess=$clang->gt("The address you have entered is already registered. An email has been sent to this address with a link that gives you access to the survey.");
            }
        }
        else
        {
            $oToken= Token::create($iSurveyId);
            $oToken->firstname = $sFirstName;
            $oToken->lastname = $sLastName;
            $oToken->email = $sEmail;
            $oToken->emailstatus = 'OK';
            $oToken->language = $sLanguage;
            $oToken->setAttributes($aAttribute);
            if ($aSurveyInfo['startdate'])
            {
                $oToken->validfrom = $aSurveyInfo['startdate'];
            }
            if ($aSurveyInfo['expires'])
            {
                $oToken->validuntil = $aSurveyInfo['expires'];
            }
            $oToken->save();
            $iTokenId=$oToken->tid;
            TokenDynamic::model($iSurveyId)->createToken($iTokenId);// Review if really create a token
            $sMailSuccess=$clang->gT("An email has been sent to the address you provided with access details for this survey. Please follow the link in that email to proceed.");
        }
        // Now we have a existing token and we can send email (based on sMailSucces)
        if($sMailSuccess && $iTokenId){
            $aMail['subject']=$aSurveyInfo['email_register_subj'];
            $aMail['message']=$aSurveyInfo['email_register'];
            $aReplacementFields=array();
            $aReplacementFields["{ADMINNAME}"]=$aSurveyInfo['adminname'];
            $aReplacementFields["{ADMINEMAIL}"]=$aSurveyInfo['adminemail'];
            $aReplacementFields["{SURVEYNAME}"]=$aSurveyInfo['name'];
            $aReplacementFields["{SURVEYDESCRIPTION}"]=$aSurveyInfo['description'];
            $aReplacementFields["{EXPIRY}"]=$aSurveyInfo["expiry"];
            $oToken=TokenDynamic::model($iSurveyId)->findByPk($iTokenId);
            foreach($oToken->attributes as $attribute=>$value){
                $aReplacementFields["{".strtoupper($attribute)."}"]=$value;
            }
            $sToken=$oToken->token;
            $aMail['subject']=preg_replace("/{TOKEN:([A-Z0-9_]+)}/","{"."$1"."}",$aMail['subject']);
            $aMail['message']=preg_replace("/{TOKEN:([A-Z0-9_]+)}/","{"."$1"."}",$aMail['message']);
            $surveylink = App()->createAbsoluteUrl("/survey/index/sid/{$iSurveyId}",array('lang'=>$sLanguage,'token'=>$sToken));
            $optoutlink = App()->createAbsoluteUrl("/optout/tokens/surveyid/{$iSurveyId}",array('langcode'=>$sLanguage,'token'=>$sToken));
            $optinlink = App()->createAbsoluteUrl("/optin/tokens/surveyid/{$iSurveyId}",array('langcode'=>$sLanguage,'token'=>$sToken));
            if (getEmailFormat($iSurveyId) == 'html')
            {
                $useHtmlEmail = true;
                $aReplacementFields["{SURVEYURL}"]="<a href='$surveylink'>".$surveylink."</a>";
                $aReplacementFields["{OPTOUTURL}"]="<a href='$optoutlink'>".$optoutlink."</a>";
                $aReplacementFields["{OPTINURL}"]="<a href='$optinlink'>".$optinlink."</a>";
            }
            else
            {
                $useHtmlEmail = false;
                $aReplacementFields["{SURVEYURL}"]= $surveylink;
                $aReplacementFields["{OPTOUTURL}"]= $optoutlink;
                $aReplacementFields["{OPTINURL}"]= $optinlink;
            }
            // Allow barebone link for all URL
            $aMail['message'] = str_replace("@@SURVEYURL@@", $surveylink, $aMail['message']);
            $aMail['message'] = str_replace("@@OPTOUTURL@@", $optoutlink, $aMail['message']);
            $aMail['message'] = str_replace("@@OPTINURL@@", $optinlink, $aMail['message']);
            // Replace the fields
            $aMail['subject']=ReplaceFields($aMail['subject'], $aReplacementFields);
            $aMail['message']=ReplaceFields($aMail['message'], $aReplacementFields);
            $sFrom = "{$aSurveyInfo['adminname']} <{$aSurveyInfo['adminemail']}>";
            $sBounce=getBounceEmail($iSurveyId);
            $sTo=$oToken->email;
            $sitename =  Yii::app()->getConfig('sitename');
            // Plugin event for email handling (Same than admin token but with register type)
            $event = new PluginEvent('beforeTokenEmail');
            $event->set('type', 'register');
            $event->set('subject', $aMail['subject']);
            $event->set('to', $sTo);
            $event->set('body', $aMail['message']);
            $event->set('from', $sFrom);
            $event->set('bounce',$sBounce );
            $event->set('token', $oToken->attributes);
            $aMail['subject'] = $event->get('subject');
            $aMail['message'] = $event->get('body');
            $sTo = $event->get('to');
            $sFrom = $event->get('from');
            if ($event->get('send', true) == false)
            {
                $sMessage=$event->get('message', '');
            }
            elseif (SendEmailMessage($aMail['message'], $aMail['subject'], $sTo, $sFrom, $sitename,$useHtmlEmail,$sBounce))
            {
                // TLR change to put date into sent
                $today = dateShift(date("Y-m-d H:i:s"), "Y-m-d H:i", Yii::app()->getConfig('timeadjust'));
                $oToken->sent=$today;
                $oToken->save();
                $sMessage="<div id='wrapper' class='message tokenmessage'>"
                    . "<p>".$clang->gT("Thank you for registering to participate in this survey.")."</p>\n"
                    . "<p>{$sMailSuccess}</p>\n"
                    . "<p>".sprintf($clang->gT("Survey administrator %s (%s)"),$aSurveyInfo['adminname'],$aSurveyInfo['adminemail'])."</p>"
                    . "</div>\n";
            }
            else
            {
                $sMessage="<div id='wrapper' class='message tokenmessage'>"
                    . "<p>".$clang->gT("Thank you for registering to participate in this survey.")."</p>\n"
                    . "<p>".$clang->gT("You are registred but an error happen when trying to send the email, please contact the survey administrator.")."</p>\n"
                    . "<p>".sprintf($clang->gT("Survey administrator %s (%s)"),$aSurveyInfo['adminname'],$aSurveyInfo['adminemail'])."</p>"
                    . "</div>\n";
            }
         }
        return array('sent'=>!empty($sMailSuccess),'message'=>$sMessage.$sMailError);
    }
}
