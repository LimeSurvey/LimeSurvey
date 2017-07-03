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
class SurveyController extends LSYii_Controller
{
    public $lang = null;

    /* @var string : Default layout when using render : leave at bare actually : just send content */
    public $layout= 'bare';
    /* @var string the template name to be used when using layout */
    public $sTemplate= 'default';
    /* @var string[] Replacement data when use templatereplace function in layout, @see templatereplace $replacements */
    public $aReplacementData= array();
    /* @var array Global data when use templatereplace function  in layout, @see templatereplace $redata */
    public $aGlobalData= array();

    /**
     * Initialises this controller, does some basic checks and setups
     *
     * @access protected
     * @return void
     */
    protected function _init()
    {
        parent::_init();

        unset(Yii::app()->session['FileManagerContext']);

        if (!Yii::app()->getConfig("surveyid")) {Yii::app()->setConfig("surveyid", returnGlobal('sid'));}         //SurveyID
        if (!Yii::app()->getConfig("ugid")) {Yii::app()->setConfig("ugid", returnGlobal('ugid'));}                //Usergroup-ID
        if (!Yii::app()->getConfig("gid")) {Yii::app()->setConfig("gid", returnGlobal('gid'));}                   //GroupID
        if (!Yii::app()->getConfig("qid")) {Yii::app()->setConfig("qid", returnGlobal('qid'));}                   //QuestionID
        if (!Yii::app()->getConfig("lid")) {Yii::app()->setConfig("lid", returnGlobal('lid'));}                   //LabelID
        if (!Yii::app()->getConfig("code")) {Yii::app()->setConfig("code", returnGlobal('code'));}                // ??
        if (!Yii::app()->getConfig("action")) {Yii::app()->setConfig("action", returnGlobal('action'));}          //Desired action
        if (!Yii::app()->getConfig("subaction")) {Yii::app()->setConfig("subaction", returnGlobal('subaction'));} //Desired subaction
        if (!Yii::app()->getConfig("editedaction")) {Yii::app()->setConfig("editedaction", returnGlobal('editedaction'));} // for html editor integration
        Yii::app()->clientScript->registerPackage('decimal');                  // decimal
    }

    /**
     * Load and set session vars
     *
     * @access protected
     * @return void
     */
    protected function _sessioncontrol()
    {
        if (!Yii::app()->session["adminlang"] || Yii::app()->session["adminlang"]=='')
            Yii::app()->session["adminlang"] = Yii::app()->getConfig("defaultlang");
        Yii::app()->setLanguage(Yii::app()->session['adminlang']);
    }

    /**
     * Routes all the actions to their respective places
     *
     * @access public
     * @return array
     */
    public function actions()
    {
        return array(
            'index' => 'application.controllers.survey.index',
            'optin' => 'application.controllers.optin',
            'optout' => 'application.controllers.optout',
            'printanswers' => 'application.controllers.printanswers',
            'register' => 'application.controllers.register',
            'statistics_user' => 'application.controllers.statistics_user',
            'tcpdf_check' => 'application.controllers.tcpdf_check',
            'uploader' => 'application.controllers.uploader',
            'verification' => 'application.controllers.verification',
            'captcha' => array(
              'class'=>'CaptchaExtendedAction',
              // if needed, modify settings
              'mode'=>CaptchaExtendedAction::MODE_MATH,
            )
        );
    }

    //~ /**
     //~ * Reset the session
     //~ **/
    //~ function resetSession($iSurveyId)
    //~ {

    //~ }
    /**
     * Show a message and exit
     * @param string $sType : type of message
     * @param string[] $aMessage :  array of message line to be shown
     * @param string[]|null : $aUrl : if url can/must be set
     * @param string[]|null $aErrors : array of errors to be shown
     * @return void
     **/
    function renderExitMessage($iSurveyId,$sType,$aMessages=array(),$aUrl=null,$aErrors=null)
    {
        $this->layout='survey';
        $oTemplate = Template::model()->getInstance('', $iSurveyId);
        $this->sTemplate=$oTemplate->sTemplateName;
        $message=$this->renderPartial("/survey/system/message",array(
            'aMessage'=>$aMessages
        ),true);
        if(!empty($aUrl)){
            $url=$this->renderPartial("/survey/system/url",$aUrl,true);
        }else{
            $url="";
        }
        if(!empty($aErrors)){
            $error=$this->renderPartial("/survey/system/errorWarning",array(
                'aErrors'=>$aErrors
            ),true);
        }else{
            $error="";
        }
        /* Set the data for templatereplace */
        $this->aGlobalData['thissurvey']=getSurveyInfo($iSurveyId); /* Did we need it, or did we just use Yii::app()->getConfig('surveyID'); ? */
        $this->aReplacementData=$aReplacementData['MESSAGEID']=$sType; // Adding this to replacement data : allow to update title (for example)
        $aReplacementData['MESSAGE']=$message;
        $aReplacementData['URL']=$url;
        $this->aReplacementData=$aReplacementData['ERROR']=$error; // Adding this to replacement data : allow to update title (for example) : @see https://bugs.limesurvey.org/view.php?id=9106 (but need more)
        $content=templatereplace(file_get_contents($oTemplate->pstplPath."message.pstpl"),$aReplacementData,$this->aGlobalData);
        $this->render("/survey/system/display",array('content'=>$content));
        App()->end();
    }
}
