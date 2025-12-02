<?php

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
 * Class Expressions
 **/
class Expressions extends SurveyCommonAction
{
    /**
     * Index
     **/
    public function index()
    {
        $aData = array();
        $needpermission = false;

        $iSurveyID = sanitize_int(Yii::app()->request->getQuery('surveyid', false));
        if (!$iSurveyID) {
            $iSurveyID = sanitize_int(Yii::app()->request->getQuery('sid'));
        }

        $aData['sa'] = $sa = sanitize_paranoid_string(Yii::app()->request->getQuery('sa', 'index'));

        $aData['pagetitle'] = "ExpressionManager:  {$aData['sa']}";
        $aData['topbar']['title'] = $aData['pagetitle'];
        $aData['topbar']['rightButtons'] = Yii::app()->getController()->renderPartial(
            '/layouts/partial_topbar/right_close_saveclose_save',
            [
                'isCloseBtn' => true,
                'isSaveBtn' => false,
                'isSaveAndCloseBtn' => false,
            ],
            true
        );

        if (($aData['sa'] == 'survey_logic_file' || $aData['sa'] == 'navigation_test') && $iSurveyID) {
            $needpermission = true;
        }

        if ($needpermission && !Permission::model()->hasSurveyPermission($iSurveyID, 'surveycontent', 'read')) {
            $message['title'] = gT('Access denied!');
            $message['message'] = gT('You do not have permission to access this page.');
            $message['class'] = "error";
            $this->renderWrappedTemplate('survey', array("message" => $message), $aData);
        } else {
            App()->getClientScript()->registerPackage('jqueryui');
            App()->getClientScript()->registerPackage('expressions');/* Why we need it ? */
            App()->getClientScript()->registerPackage('decimal');
            App()->getClientScript()->registerScriptFile(App()->getConfig('generalscripts') . 'survey_runtime.js');
            App()->getClientScript()->registerPackage('expression-extend');

            $this->printOnLoad(Yii::app()->request->getQuery('sa', 'index'));
            $aData['subaction'] = $this->printTitle($aData['sa']);


            //header("Content-type: text/html; charset=UTF-8"); // needed for correct UTF-8 encoding
            $sAction = Yii::app()->request->getQuery('sa', false);
            if ($sAction) {
                $this->test($sAction, $aData);
            } else {
                $this->renderWrappedTemplate('expressions', 'test_view', $aData);
            }
        }
    }

    /**
     * Returns Replacements
     * @param $replacementClass
     * @todo Remove it - does nothing
     **/
    public function getReplacements($replacementClass)
    {
        die('TEST');
    }

    /**
     * Survey Logic file
     * NB: To apply PSR-12 to function name, database must be changed for menu entries.
     **/
    // phpcs:ignore
    public function survey_logic_file()
    {
        $aData = array();

        $sid = (int) Yii::app()->request->getParam('sid', 0);
        $hasSurveyContentReadPermission = Permission::model()->hasSurveyPermission($sid, 'surveycontent', 'read');
        if (!$hasSurveyContentReadPermission) {
            $message['title'] = gT('Access denied!');
            $message['message'] = gT('You do not have permission to access this page.');
            $message['class'] = "error";
            $this->renderWrappedTemplate('survey', array("message" => $message), $aData);
            return;
        }

        $gid = Yii::app()->request->getParam('gid', null);
        $qid = Yii::app()->request->getParam('qid', null);

        $oSurvey = Survey::model()->findByPk($sid);

        $language = Yii::app()->request->getParam('lang', null);

        if ($language !== null) {
            $language = \LSYii_Validators::languageCodeFilter($language);
        }

        $aData['lang'] = $language;

        $aData['sid'] = $sid;
        $aData['gid'] = $gid;
        $aData['qid'] = $qid;
        $aData['title_bar']['title'] = gT("Survey logic file");
        $aData['subaction'] = gT("Survey logic file");
        $aData['sidemenu']['state'] = false;
        $aData['survey'] = $oSurvey;
        $LEM_DEBUG_TIMING = Yii::app()->request->getParam('LEM_DEBUG_TIMING', (App()->getConfig('debug') > 0) ? LEM_DEBUG_TIMING : 0);
        $LEM_DEBUG_VALIDATION_SUMMARY = Yii::app()->request->getParam('LEM_DEBUG_VALIDATION_SUMMARY', LEM_DEBUG_VALIDATION_SUMMARY);
        $LEM_DEBUG_VALIDATION_DETAIL = Yii::app()->request->getParam('LEM_DEBUG_VALIDATION_DETAIL', LEM_DEBUG_VALIDATION_DETAIL);
        $LEM_PRETTY_PRINT_ALL_SYNTAX = Yii::app()->request->getParam('LEM_PRETTY_PRINT_ALL_SYNTAX', LEM_PRETTY_PRINT_ALL_SYNTAX);

        $LEMdebugLevel = (
            ((int) $LEM_DEBUG_TIMING) +
            ((int) $LEM_DEBUG_VALIDATION_SUMMARY) +
            ((int) $LEM_DEBUG_VALIDATION_DETAIL) +
            ((int) $LEM_PRETTY_PRINT_ALL_SYNTAX)
        );

        $assessments = Yii::app()->request->getParam('assessments', $oSurvey->getIsAssessments()) == 'Y';


        $aData['title_bar']['title'] = $oSurvey->getLocalizedTitle() . " (" . gT("ID") . ":" . $sid . ")";

        $aData['topBar']['showBackButton'] = true;
        $aData['topBar']['returnUrl'] = Yii::app()->createUrl('surveyAdministration/view/surveyid/' . $sid);

        if ($gid !== null && $qid === null) {
            $gid = sanitize_int($gid);
            $aData['returnUrl'] = Yii::app()->createUrl('questionGroupsAdministration/view/surveyid/' . $sid . '/gid/' . $gid);
            $aData['gid'] = $gid;
        }

        if ($qid !== null) {
            $qid = sanitize_int($qid);
            $aData['returnUrl'] = Yii::app()->createUrl('questionAdministration/view/surveyid/' . $sid . '/gid/' . $gid . '/qid/' . $qid);
            $aData['qid'] = $qid;
        }

        $aData['topbar']['rightButtons'] = Yii::app()->getController()->renderPartial(
            '/admin/expressions/partial/topbarBtns/rightSideButtons',
            ['aData' => $aData],
            true
        );
        App()->getClientScript()->registerPackage('decimal');
        App()->getClientScript()->registerScriptFile('SCRIPT_PATH', 'survey_runtime.js');
        App()->getClientScript()->registerPackage('expressions');/* Why we need it ? */
        App()->getClientScript()->registerPackage('expression-extend');
        App()->getClientScript()->registerCssFile(Yii::app()->getConfig('publicstyleurl') . 'expressionlogicfile.css');

        SetSurveyLanguage($sid, $language);

        killSurveySession($sid);

        Yii::app()->setLanguage(Yii::app()->session['adminlang']);

        $aData['result'] = LimeExpressionManager::ShowSurveyLogicFile($sid, $gid, $qid, $LEMdebugLevel, $assessments);

        if (Yii::app()->request->getParam('printable', 0) == 1) {
            $html = "<html><body>";
            $html .= "<style>
           @page { 
               size: landscape;
           }
            @media print { 
                html {width: 670px; white-space: pre-wrap; overflow: visible;}
                body {width: 100%; white-space: pre-wrap; overflow: visible; font-size: 12pt;}
                table { overflow: visible !important; }
                div { overflow: visible !important; }
                * { overflow: visible !important; }
                
            }
           </style>";
            $html .= $aData['result']['html'];
            $html .= "</body></html>";
            App()->getClientScript()->render($html);
            echo $html;

            Yii::app()->end();
        }

        $this->renderWrappedTemplate('expressions', 'test/survey_logic_file', $aData);
    }

    /**
     * Survey Logic Form
     **/
    public function surveyLogicForm()
    {
        $aData['surveylist'] = getSurveyList();

        App()->getClientScript()->registerPackage('decimal');
        App()->getClientScript()->registerScriptFile('SCRIPT_PATH', 'survey_runtime.js');
        App()->getClientScript()->registerPackage('expressions');
        App()->getClientScript()->registerPackage('expression-extend');

        $this->renderWrappedTemplate('expressions', 'test/surveyLogicForm', $aData);
    }

    protected function test($which, $aData)
    {
        if ($which == 'survey_logic_file') {
            $which = 'surveyLogicForm';
        }
        $this->renderWrappedTemplate('expressions', 'test/' . $which, $aData);
        //$this->getController()->render('/admin/expressions/test/'.$which);
    }

    /**
     * Print on load
     * @param string $which
     **/
    private function printOnLoad(string $which)
    {
        switch ($which) {
            case 'relevance':
                App()->getClientScript()->registerScript("emscript", "ExprMgr_process_relevance_and_tailoring();", LSYii_ClientScript::POS_POSTSCRIPT);
                break;
            case 'unit':
                App()->getClientScript()->registerScript("emscript", "recompute();", LSYii_ClientScript::POS_POSTSCRIPT);
                break;
        }
    }

    /**
     * Print Title
     * @param string $which
     * @return string
     **/
    private function printTitle(string $which): string
    {
        switch ($which) {
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
    protected function renderWrappedTemplate($sAction = 'expressions', $aViewUrls = array(), $aData = array(), $sRenderFile = false)
    {
        $aData['imageurl'] = Yii::app()->getConfig('adminimageurl');
        header("Content-type: text/html; charset=UTF-8"); // needed for correct UTF-8 encoding
        parent::renderWrappedTemplate($sAction, $aViewUrls, $aData, $sRenderFile);
    }
}
