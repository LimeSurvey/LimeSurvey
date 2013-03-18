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
    
    /**
     * Initialises this controller, does some basic checks and setups
     *
     * @access protected
     * @return void
     */
    protected function _init()
    {
        parent::_init();

        $this->_sessioncontrol();

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

        Yii::import('application.libraries.Limesurvey_lang');
        $this->lang = new Limesurvey_lang(Yii::app()->session['adminlang']);
        Yii::app()->setLang($this->lang);
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
            'verification' => 'application.controllers.verification'
        );
    }

}
