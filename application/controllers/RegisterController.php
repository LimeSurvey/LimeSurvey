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

    /**
    * The array of errors to be displayed
    */
    private $aRegisterErrors;
    /**
    * The message to be shown, is not null: default form not shown
    */
    private $sMessage;
    /**
    * The message to diplay after sending the register email
    */
    private $sMailMessage;

    public function actions()
    {
        return array(
            'captcha' => array(
                'class' => 'CCaptchaAction',
                'backColor'=>0xf6f6f6
            )
        );
    }

    public function actionAJAXRegisterForm($surveyid)
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
        Yii::app()->setLanguage($sLanguage);

        $thistpl=getTemplatePath($oSurvey->template);
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
    public function actionIndex($sid = null)
    {

        if(!is_null($sid))
            $iSurveyId=$sid;
        else
            $iSurveyId=Yii::app()->request->getPost('sid');

        $oSurvey=Survey::model()->find("sid=:sid",array(':sid'=>$iSurveyId));

        $sLanguage = Yii::app()->request->getParam('lang');
        if (!$sLanguage)
        {
            $sLanguage = Survey::model()->findByPk($iSurveyId)->language;
        }

        if (!$oSurvey){
            throw new CHttpException(404, "The survey in which you are trying to participate does not seem to exist. It may have been deleted or the link you were given is outdated or incorrect.");
        }elseif($oSurvey->allowregister!='Y' || !tableExists("{{tokens_{$iSurveyId}}}")){
            throw new CHttpException(404,"The survey in which you are trying to register don't accept registration. It may have been updated or the link you were given is outdated or incorrect.");
        }
        elseif(!is_null($oSurvey->expires) && $oSurvey->expires < dateShift(date("Y-m-d H:i:s"), "Y-m-d H:i", Yii::app()->getConfig('timeadjust'))){
            $this->redirect(array('survey/index','sid'=>$iSurveyId,'lang'=>$sLanguage));
        }

        Yii::app()->setLanguage($sLanguage);


        $event = new PluginEvent('beforeRegister');
        $event->set('surveyid', $iSurveyId);
        $event->set('lang', $sLanguage);
        App()->getPluginManager()->dispatchEvent($event);

        $this->sMessage=$event->get('sMessage');
        $this->aRegisterErrors=$event->get('aRegisterErrors');
        $iTokenId=$event->get('iTokenId');
        // Test if we come from register form (and submit)
        if((Yii::app()->request->getPost('register')) && !$iTokenId){
            self::getRegisterErrors($iSurveyId);
            if(empty($this->aRegisterErrors)){
                $iTokenId=self::getTokenId($iSurveyId);
            }
        }
        if(empty($this->aRegisterErrors) && $iTokenId && $this->sMessage===null){
            self::sendRegistrationEmail($iSurveyId,$iTokenId);
        }

        // Display the page
        self::display($iSurveyId);
    }

    /**
    * Validate a register form
    * @param $iSurveyId Survey Id to register
    * @return array of errors when try to register (empty array => no error)
    */
    public function getRegisterErrors($iSurveyId){
        $aSurveyInfo=getSurveyInfo($iSurveyId,App()->language);

        // Check the security question's answer
        if (function_exists("ImageCreate") && isCaptchaEnabled('registrationscreen',$aSurveyInfo['usecaptcha']) )
        {
            $sLoadSecurity=Yii::app()->request->getPost('loadsecurity','');
            $captcha=Yii::app()->getController()->createAction("captcha");
            $captchaCorrect = $captcha->validate( $sLoadSecurity, false);

            if (!$captchaCorrect)
            {
                $this->aRegisterErrors[] = gT("Your answer to the security question was not correct - please try again.");
            }
        }

        $aFieldValue=$this->getFieldValue($iSurveyId);
        $aRegisterAttributes=$this->getExtraAttributeInfo($iSurveyId);

        //Check that the email is a valid style address
        if($aFieldValue['sEmail']==""){
            $this->aRegisterErrors[]= gT("You must enter a valid email. Please try again.");
        }elseif (!validateEmailAddress($aFieldValue['sEmail'])){
            $this->aRegisterErrors[]= gT("The email you used is not valid. Please try again.");
        }
        //Check and validate attribute
        foreach ($aRegisterAttributes as $key => $aAttribute)
        {
            if ($aAttribute['show_register'] == 'Y' && $aAttribute['mandatory'] == 'Y' && empty($aFieldValue['aAttribute'][$key]))
            {
                $this->aRegisterErrors[]= sprintf(gT("%s cannot be left empty").".", $aAttribute['caption']);
            }
        }
    }

    public function getRegisterForm($iSurveyId)
    {
        $aSurveyInfo=getSurveyInfo($iSurveyId,App()->language);

        // Event to replace register form
        $event = new PluginEvent('beforeRegisterForm');
        $event->set('surveyid', $iSurveyId);
        $event->set('lang', App()->language);
        $event->set('aRegistersErrors', $this->aRegisterErrors);
        App()->getPluginManager()->dispatchEvent($event);
        // Allow adding error or replace error with plugin ?
        $this->aRegisterErrors=$event->get('aRegistersErrors');
        if(!is_null($event->get('registerForm')))
            return $event->get('registerForm');

        $aFieldValue=$this->getFieldValue($iSurveyId);
        $aRegisterAttributes=$this->getExtraAttributeInfo($iSurveyId);
        $aData['iSurveyId'] = $iSurveyId;
        $aData['sLanguage'] = App()->language;
        $aData['sFirstName'] = $aFieldValue['sFirstName'];
        $aData['sLastName'] = $aFieldValue['sLastName'];
        $aData['sEmail'] = $aFieldValue['sEmail'];
        $aData['aAttribute'] = $aFieldValue['aAttribute'];
        $aData['aExtraAttributes']=$aRegisterAttributes;
        $aData['urlAction']=App()->createUrl('register/index',array('sid'=>$iSurveyId));
        $aData['bCaptcha'] = function_exists("ImageCreate") && isCaptchaEnabled('registrationscreen', $aSurveyInfo['usecaptcha']);
        $aReplacement['REGISTERFORM']=$this->renderPartial('registerForm',$aData,true);
        if(is_array($this->aRegisterErrors))
        {
            $sRegisterError="<div class='alert alert-danger' role='alert'>"
            .implode('<br />',$this->aRegisterErrors)
            ."</div>";
        }
        else
        {
            $sRegisterError='';
        }

        $aReplacement['REGISTERERROR'] = $sRegisterError;
        $aReplacement['REGISTERMESSAGE1'] = gT("You must be registered to complete this survey");
        if($sStartDate=$this->getStartDate($iSurveyId))
            $aReplacement['REGISTERMESSAGE2'] = sprintf(gT("You may register for this survey but you have to wait for the %s before starting the survey."),$sStartDate)."<br />\n".gT("Enter your details below, and an email containing the link to participate in this survey will be sent immediately.");
        else
            $aReplacement['REGISTERMESSAGE2'] = gT("You may register for this survey if you wish to take part.")."<br />\n".gT("Enter your details below, and an email containing the link to participate in this survey will be sent immediately.");

        $aData['thissurvey'] = $aSurveyInfo;
        Yii::app()->setConfig('surveyID',$iSurveyId);//Needed for languagechanger
        $aData['languagechanger'] = makeLanguageChangerSurvey(App()->language);
        $oTemplate = Template::model()->getInstance(null, $iSurveyId);
        return templatereplace(file_get_contents($oTemplate->viewPath . "/register.pstpl"),$aReplacement,$aData);
    }

    /**
    * Send the register email with $_POST value
    * @param $iSurveyId Survey Id to register
    * @return boolean : if email is set to sent (before SMTP problem)
    */
    public function sendRegistrationEmail($iSurveyId,$iTokenId){

        $sLanguage=App()->language;
        $aSurveyInfo=getSurveyInfo($iSurveyId,$sLanguage);

        $aMail['subject']=$aSurveyInfo['email_register_subj'];
        $aMail['message']=$aSurveyInfo['email_register'];
        $aReplacementFields=array();
        $aReplacementFields["{ADMINNAME}"]=$aSurveyInfo['adminname'];
        $aReplacementFields["{ADMINEMAIL}"]=$aSurveyInfo['adminemail'];
        $aReplacementFields["{SURVEYNAME}"]=$aSurveyInfo['name'];
        $aReplacementFields["{SURVEYDESCRIPTION}"]=$aSurveyInfo['description'];
        $aReplacementFields["{EXPIRY}"]=$aSurveyInfo["expiry"];
        $oToken = Token::model($iSurveyId)->findByPk($iTokenId); // Reload the token (needed if just created)
        foreach($oToken->attributes as $attribute=>$value){
            $aReplacementFields["{".strtoupper($attribute)."}"]=$value;
        }
        $sToken=$oToken->token;
        $useHtmlEmail = (getEmailFormat($iSurveyId) == 'html');
        $aMail['subject']=preg_replace("/{TOKEN:([A-Z0-9_]+)}/","{"."$1"."}",$aMail['subject']);
        $aMail['message']=preg_replace("/{TOKEN:([A-Z0-9_]+)}/","{"."$1"."}",$aMail['message']);
        $aReplacementFields["{SURVEYURL}"] = App()->createAbsoluteUrl("/survey/index/sid/{$iSurveyId}",array('lang'=>$sLanguage,'token'=>$sToken));
        $aReplacementFields["{OPTOUTURL}"] = App()->createAbsoluteUrl("/optout/tokens/surveyid/{$iSurveyId}",array('langcode'=>$sLanguage,'token'=>$sToken));
        $aReplacementFields["{OPTINURL}"] = App()->createAbsoluteUrl("/optin/tokens/surveyid/{$iSurveyId}",array('langcode'=>$sLanguage,'token'=>$sToken));
        foreach(array('OPTOUT', 'OPTIN', 'SURVEY') as $key)
        {
            $url = $aReplacementFields["{{$key}URL}"];
            if ($useHtmlEmail)
                $aReplacementFields["{{$key}URL}"] = "<a href='{$url}'>" . htmlspecialchars($url) . '</a>';
            $aMail['subject'] = str_replace("@@{$key}URL@@", $url, $aMail['subject']);
            $aMail['message'] = str_replace("@@{$key}URL@@", $url, $aMail['message']);
        }
        // Replace the fields
        $aMail['subject']=ReplaceFields($aMail['subject'], $aReplacementFields);
        $aMail['message']=ReplaceFields($aMail['message'], $aReplacementFields);
        $sFrom = "{$aSurveyInfo['adminname']} <{$aSurveyInfo['adminemail']}>";
        $sBounce=getBounceEmail($iSurveyId);
        $sTo=$oToken->email;
        $sitename =  Yii::app()->getConfig('sitename');
        // Plugin event for email handling (Same than admin token but with register type)
        $event = new PluginEvent('beforeTokenEmail');
        $event->set('survey', $iSurveyId);
        $event->set('type', 'register');
        $event->set('model', 'register');
        $event->set('subject', $aMail['subject']);
        $event->set('to', $sTo);
        $event->set('body', $aMail['message']);
        $event->set('from', $sFrom);
        $event->set('bounce',$sBounce );
        $event->set('token', $oToken->attributes);
        App()->getPluginManager()->dispatchEvent($event);
        $aMail['subject'] = $event->get('subject');
        $aMail['message'] = $event->get('body');
        $sTo = $event->get('to');
        $sFrom = $event->get('from');
        $sBounce = $event->get('bounce');

        $aRelevantAttachments = array();
        if (isset($aSurveyInfo['attachments']))
        {
            $aAttachments = unserialize($aSurveyInfo['attachments']);
            if (!empty($aAttachments))
            {
                if (isset($aAttachments['registration']))
                {
                    LimeExpressionManager::singleton()->loadTokenInformation($aSurveyInfo['sid'], $sToken);

                    foreach ($aAttachments['registration'] as $aAttachment)
                    {
                        if (LimeExpressionManager::singleton()->ProcessRelevance($aAttachment['relevance']))
                        {
                            $aRelevantAttachments[] = $aAttachment['url'];
                        }
                    }
                }
            }
        }

        if ($event->get('send', true) == false)
        {
            $this->sMessage=$event->get('message', $this->sMailMessage); // event can send is own message
            if($event->get('error')==null) // mimic core system, set send to today
            {
                $today = dateShift(date("Y-m-d H:i:s"), "Y-m-d H:i", Yii::app()->getConfig('timeadjust'));
                $oToken->sent=$today;
                $oToken->save();
            }
        }
        elseif (SendEmailMessage($aMail['message'], $aMail['subject'], $sTo, $sFrom, $sitename,$useHtmlEmail,$sBounce,$aRelevantAttachments))
        {
            // TLR change to put date into sent
            $today = dateShift(date("Y-m-d H:i:s"), "Y-m-d H:i", Yii::app()->getConfig('timeadjust'));
            $oToken->sent=$today;
            $oToken->save();
            $this->sMessage="<div id='wrapper' class='message tokenmessage'>"
                . "<p>".gT("Thank you for registering to participate in this survey.")."</p>\n"
                . "<p>{$this->sMailMessage}</p>\n"
                . "<p>".sprintf(gT("Survey administrator %s (%s)"),$aSurveyInfo['adminname'],$aSurveyInfo['adminemail'])."</p>"
                . "</div>\n";
        }
        else
        {
            $this->sMessage="<div id='wrapper' class='message tokenmessage'>"
                . "<p>".gT("Thank you for registering to participate in this survey.")."</p>\n"
                . "<p>".gT("You are registered but an error happened when trying to send the email - please contact the survey administrator.")."</p>\n"
                . "<p>".sprintf(gT("Survey administrator %s (%s)"),$aSurveyInfo['adminname'],$aSurveyInfo['adminemail'])."</p>"
                . "</div>\n";
        }
        // Allways return true : if we come here, we allways trye to send an email
        return true;
    }

    /**
    * Get the token id according to filled values
    * @param $iSurveyId
    * @return integer : the token id created
    */
    public function getTokenId($iSurveyId)
    {

        $sLanguage=App()->language;
        $aSurveyInfo=getSurveyInfo($iSurveyId,$sLanguage);

        $aFieldValue=$this->getFieldValue($iSurveyId);
        // Now construct the text returned
        $oToken=Token::model($iSurveyId)->findByAttributes(array(
            'email' => $aFieldValue['sEmail']
        ));
        if ($oToken)
         {
            if($oToken->usesleft<1 && $aSurveyInfo['alloweditaftercompletion']!='Y')
            {
                $this->aRegisterErrors[]=gT("The email address you have entered is already registered and the survey has been completed.");
            }
            elseif(strtolower(substr(trim($oToken->emailstatus),0,6))==="optout")// And global blacklisting ?
            {
                $this->aRegisterErrors[]=gT("This email address cannot be used because it was opted out of this survey.");
            }
            elseif(!$oToken->emailstatus && $oToken->emailstatus!="OK")
            {
                $this->aRegisterErrors[]=gT("This email address is already registered but the email adress was bounced.");
            }
            else
            {
                $this->sMailMessage=gT("The address you have entered is already registered. An email has been sent to this address with a link that gives you access to the survey.");
                return $oToken->tid;
            }
        }
        else
        {
            // TODO : move xss filtering in model
            $oToken= Token::create($iSurveyId);
            $oToken->firstname = sanitize_xss_string($aFieldValue['sFirstName']);
            $oToken->lastname = sanitize_xss_string($aFieldValue['sLastName']);
            $oToken->email = $aFieldValue['sEmail'];
            $oToken->emailstatus = 'OK';
            $oToken->language = $sLanguage;
            $aFieldValue['aAttribute']=array_map('sanitize_xss_string',$aFieldValue['aAttribute']);
            $oToken->setAttributes($aFieldValue['aAttribute']);
            if ($aSurveyInfo['startdate'])
            {
                $oToken->validfrom = $aSurveyInfo['startdate'];
            }
            if ($aSurveyInfo['expires'])
            {
                $oToken->validuntil = $aSurveyInfo['expires'];
            }
            $oToken->generateToken();
            $oToken->save();
            $this->sMailMessage=gT("An email has been sent to the address you provided with access details for this survey. Please follow the link in that email to proceed.");
            return $oToken->tid;
        }
    }
    /**
    * Get the array of fill value from the register form
    * @param $iSurveyId
    * @return array : if email is set to sent (before SMTP problem)
    */
    public function getFieldValue($iSurveyId)
    {
        //static $aFiledValue; ?
        $sLanguage=Yii::app()->language;
        $aSurveyInfo=getSurveyInfo($iSurveyId,$sLanguage);
        $aFieldValue=array();
        $aFieldValue['sFirstName']=Yii::app()->request->getPost('register_firstname','');
        $aFieldValue['sLastName']=Yii::app()->request->getPost('register_lastname','');
        $aFieldValue['sEmail']=Yii::app()->request->getPost('register_email','');
        $aRegisterAttributes=$aSurveyInfo['attributedescriptions'];
        $aFieldValue['aAttribute']=array();
        foreach($aRegisterAttributes as $key=>$aRegisterAttribute){
            if($aRegisterAttribute['show_register']=='Y'){
                $aFieldValue['aAttribute'][$key]= Yii::app()->request->getPost('register_'.$key,'');
            }
        }
        return $aFieldValue;
    }

    /**
    * Get the array of extra attribute with caption
    * @param $iSurveyId
    * @return array
    */
    public function getExtraAttributeInfo($iSurveyId)
    {
        $sLanguage=Yii::app()->language;
        $aSurveyInfo=getSurveyInfo($iSurveyId,$sLanguage);
        $aRegisterAttributes=$aSurveyInfo['attributedescriptions'];
        foreach($aRegisterAttributes as $key=>$aRegisterAttribute){
            if($aRegisterAttribute['show_register']!='Y'){
                unset($aRegisterAttributes[$key]);
            }else{
                $aRegisterAttributes[$key]['caption']=($aSurveyInfo['attributecaptions'][$key]?$aSurveyInfo['attributecaptions'][$key] : ($aRegisterAttribute['description']?$aRegisterAttribute['description'] : $key));
            }
        }
        return $aRegisterAttributes;
    }
    /**
    * Get the date if survey is future
    * @param $iSurveyId
    * @return localized date
    */
    public function getStartDate($iSurveyId){
        $aSurveyInfo=getSurveyInfo($iSurveyId,Yii::app()->language);
        if(empty($aSurveyInfo['startdate']) ||  dateShift(date("Y-m-d H:i:s"), "Y-m-d H:i:s", Yii::app()->getConfig("timeadjust"))>=$aSurveyInfo['startdate'])
            return;
        Yii::app()->loadHelper("surveytranslator");
        $aDateFormat=getDateFormatData(getDateFormatForSID($iSurveyId,Yii::app()->language),Yii::app()->language);
        $datetimeobj = new Date_Time_Converter($aSurveyInfo['startdate'], 'Y-m-d H:i:s');
        return $datetimeobj->convert($aDateFormat['phpdate']);
    }
    /**
    * Display needed public page
    * @param $iSurveyId
    */
    private function display($iSurveyId)
    {
        $sLanguage=Yii::app()->language;
        $aData['surveyid']=$surveyid=$iSurveyId;
        $aData['thissurvey']=getSurveyInfo($iSurveyId,$sLanguage);
        $sTemplate=getTemplatePath($aData['thissurvey']['template']);
        Yii::app()->setConfig('surveyID',$iSurveyId);//Needed for languagechanger
        $aData['sitename']=Yii::app()->getConfig('sitename');
        $aData['aRegisterErrors']=$this->aRegisterErrors;
        $aData['sMessage']=$this->sMessage;

            $oTemplate = Template::model()->getInstance('', $iSurveyId);
            Yii::app()->clientScript->registerPackage( 'survey-template' );
        sendCacheHeaders();
        doHeader();
        $aViewData['sTemplate']=$sTemplate;
        if(!$this->sMessage){
            $aData['languagechanger']=makeLanguageChangerSurvey($sLanguage); // Only show language changer shown the form is shown, not after submission
            $aViewData['content']=self::getRegisterForm($iSurveyId);
        }else{
            $aViewData['content']=templatereplace($this->sMessage);
        }
        $aViewData['aData']=$aData;
        // Test if we come from index or from register
        if(empty(App()->clientScript->scripts)){
            $oTemplate = Template::model()->getInstance('', $iSurveyId);
            Yii::app()->clientScript->registerPackage( 'survey-template' );
            App()->getClientScript()->registerPackage('jqueryui');
            App()->getClientScript()->registerPackage('jquery-touch-punch');
            App()->getClientScript()->registerScriptFile(Yii::app()->getConfig('generalscripts')."survey_runtime.js");
            useFirebug();
            $this->render('/register/display',$aViewData);
        }else{
            // Survey/index need renderPartial
            echo $this->renderPartial('/register/display',$aViewData, true, true);
        }
        doFooter();
    }
}
