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
class Expressions extends Survey_Common_Action {
	function index()
	{
	    $aData=array();
        $needpermission=false;
        $aData['surveyid']=$surveyid=sanitize_int(Yii::app()->request->getQuery('sid'));
        $aData['sa']=$sa=sanitize_paranoid_string(Yii::app()->request->getQuery('sa','index'));
	    if (($aData['sa']=='survey_logic_file' || $aData['sa']=='navigation_test') && $surveyid)
	    {
	        $needpermission=true;
	    }
        if($needpermission && !Permission::model()->hasSurveyPermission($surveyid,'surveycontent','read'))
        {
            $clang = $this->getController()->lang;
            App()->getClientScript()->registerPackage('jquery-superfish');
            $message['title']= $clang->gT('Access denied!');
            $message['message']= $clang->gT('You do not have sufficient rights to access this page.');
            $message['class']= "error";
            $this->_renderWrappedTemplate('survey', array("message"=>$message), $aData);
        }
        else
        {
            App()->getClientScript()->registerPackage('jqueryui');
            App()->getClientScript()->registerScriptFile(Yii::app()->getConfig('generalscripts')."survey_runtime.js");
            App()->getClientScript()->registerScriptFile(Yii::app()->getConfig('generalscripts')."expressions/em_javascript.js");
            App()->getClientScript()->registerCssFile(Yii::app()->getConfig('adminstyleurl') . "adminstyle.css" );
            $this->_printOnLoad(Yii::app()->request->getQuery('sa', 'index'));
            $aData['pagetitle']="ExpressionManager:  {$aData['sa']}";
            //header("Content-type: text/html; charset=UTF-8"); // needed for correct UTF-8 encoding
	        if(isset($_GET['sa']))
		        $this->test($aData['sa'],$aData);
	        else 
	            $this->_renderWrappedTemplate('expressions', 'test_view', $aData);
        }
    }

    protected function test($which,$aData)
    {
            $this->_renderWrappedTemplate('expressions', 'test/'.$which, $aData);
        //$this->getController()->render('/admin/expressions/test/'.$which);
    }

    private function _printOnLoad($which)
    {
        switch ($which)
        {
            case 'relevance':
                App()->getClientScript()->registerScript("emscript", "ExprMgr_process_relevance_and_tailoring();", CClientScript::POS_LOAD);
                break;
            case 'unit':
                App()->getClientScript()->registerScript("emscript", "recompute();", CClientScript::POS_LOAD);
                break;
        }
    }

    private function _printTitle($which)
    {
        switch ($which)
        {
            case 'index':
                return 'Test Suite';
                break;
            case 'relevance':
                return 'Unit Test Relevance';
                break;
            case 'stringspilt':
                return 'Unit Test String Splitter';
                break;
            case 'functions':
                return 'Available Functions';
                break;
            case 'data':
                return 'Current Data';
                break;
            case 'reset_syntax_error_log':
                return 'Reset Log of Syntax Errors';
                break;
            case 'tokenizer':
                return 'Unit Test Tokenizer';
                break;
            case 'unit':
                return 'Unit Test Core Evaluator';
                break;
            case 'conditions2relevance':
                return 'Preview Conditions to Relevance';
                break;
            case 'navigation_test':
                return 'Navigation Test';
                break;
            case 'reset_syntax_error_log.php':
                return 'Reset Log of Syntax Errors';
                break;
            case 'revert_upgrade_conditions2relevance':
                return 'Revert Upgrade Conditions to Relevance';
                break;
            case 'strings_with_expressions':
                return 'Test Evaluation of Strings Containing Expressions';
                break;
            case 'survey_logic_file':
                return 'Survey logic file';
                break;
            case 'syntax_errors':
                echo 'Show Log of Syntax Errors';
                break;
            case 'upgrade_conditions2relevance':
                return 'Upgrade Conditions to Relevance';
                break;
            case 'upgrade_relevance_location':
                return 'Upgrade Relevance Location';
                break;
            case 'usage':
                return 'Running Translation Log';
                break;
        }
    }
    /**
    * Renders template(s) wrapped in header and footer
    *
    * @param string $sAction Current action, the folder to fetch views from
    * @param string|array $aViewUrls View url(s)
    * @param array $aData Data to be passed on. Optional.
    */
    protected function _renderWrappedTemplate($sAction = 'expressions', $aViewUrls = array(), $aData = array())
    {
        $aData['imageurl'] = Yii::app()->getConfig('adminimageurl');
        //$aData['display']['header']=false;
        $aData['display']['menu_bars'] = false;
        //$aData['display']['footer']= false;
        header("Content-type: text/html; charset=UTF-8"); // needed for correct UTF-8 encoding
        parent::_renderWrappedTemplate($sAction, $aViewUrls, $aData);
    }
}
/* End of file expressions.php */
/* Location: ./application/controllers/admin/expressions.php */
