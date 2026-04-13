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

/*
 * Created 12-2008 by Maziminke (maziminke@web.de)
 *
 * This file handles the "Show results to users" option:
 * Survey Settings -> Presentation & navigation -> "Public statistics?"
 *
 * The admin can set a question attribute "public_statistics" for each question
 * to determine whether the results of a certain question should be shown to the user
 * after he/she has submitted the survey.
 *
 */
class StatisticsUserController extends SurveyController
{
    /**
     * @var int
     */
    public $iSurveyID;

    /**
     * @var string
     */
    public $sLanguage;

    /**
     * @param mixed $method
     * @param array $params
     * @return array
     * @todo Not used?
     */
    public function remap($method, $params = array())
    {
        array_unshift($params, $method);
        return call_user_func_array(array($this, "action"), $params);
    }

    /**
     * @param int    $surveyid
     * @param string $language
     *
     * @throws CHttpException
     * @throws CException
     */
    public function actionAction($surveyid, $language = null)
    {
        $sLanguage = $language;
        $survey = Survey::model()->findByPk($surveyid);

        $this->sLanguage = $language;

        $iSurveyID = (int)$survey->sid;
        $this->iSurveyID = $survey->sid;

        //$postlang = returnglobal('lang');
        //~ Yii::import('application.libraries.admin.progressbar',true);
        Yii::app()->loadHelper("userstatistics");
        Yii::app()->loadHelper('database');
        Yii::app()->loadHelper('surveytranslator');
        $data = array();

        if (!isset($iSurveyID)) {
            $iSurveyID = returnGlobal('sid');
        } else {
            $iSurveyID = (int)$iSurveyID;
        }
        if (!$iSurveyID) {
            //This next line ensures that the $iSurveyID value is never anything but a number.
            throw new CHttpException(404, 'You have to provide a valid survey ID.');
        }


        $actresult = Survey::model()->findAll('sid = :sid AND active = :active', array(':sid' => $iSurveyID, ':active' => 'Y')); //Checked
        if (count($actresult) == 0) {
            throw new CHttpException(404, 'You have to provide a valid survey ID.');
        } else {
            $surveyinfo = getSurveyInfo($iSurveyID);
            // let's get the survey title for display
            $thisSurveyTitle = $surveyinfo["name"];
            // let's get css from individual template.css - so define path
            $thisSurveyCssPath = getTemplateURL($surveyinfo["template"]);
            if ($surveyinfo['publicstatistics'] != 'Y') {
                throw new CHttpException(404, 'The public statistics for this survey are deactivated.');
            }

            //check if graphs should be shown for this survey
            if ($survey->isPublicGraphs) {
                $publicgraphs = 1;
            } else {
                $publicgraphs = 0;
            }
        }

        //we collect all the output within this variable
        $statisticsoutput = '';

        //for creating graphs we need some more scripts which are included here
        //True -> include
        //False -> forget about charts
        if (isset($publicgraphs) && $publicgraphs == 1) {
            require_once(APPPATH . '../vendor/pchart/pChart.class.php');
            require_once(APPPATH . '../vendor/pchart/pData.class.php');
            require_once(APPPATH . '../vendor/pchart/pCache.class.php');

            $MyCache = new pCache(Yii::app()->getConfig("tempdir") . DIRECTORY_SEPARATOR);
            //$currentuser is created as prefix for pchart files
            if (isset($_SERVER['REDIRECT_REMOTE_USER'])) {
                $currentuser = $_SERVER['REDIRECT_REMOTE_USER'];
            } else {
                if (session_id()) {
                    $currentuser = substr(session_id(), 0, 15);
                } else {
                    $currentuser = "standard";
                }
            }
        }
        // Set language for questions and labels to base language of this survey
        if ($sLanguage == null || !in_array($sLanguage, $survey->allLanguages)) {
            $sLanguage = $survey->language;
        } else {
            $sLanguage = sanitize_languagecode($sLanguage);
        }
        //set survey language for translations
        SetSurveyLanguage($iSurveyID, $sLanguage);
        //Create header
        $condition = false;
        $sitename = Yii::app()->getConfig("sitename");

        $data['surveylanguage'] = $sLanguage;
        $data['sitename'] = $sitename;
        $data['condition'] = $condition;
        $data['thisSurveyCssPath'] = $thisSurveyCssPath;


        // ---------- CREATE SGQA OF ALL QUESTIONS WHICH USE "PUBLIC_STATISTICS" ----------
        // only show questions where question attribute "public_statistics" is set to "1"
        $questions = Question::model()->with(['group' => ['alias' => 'g'], 'questionattributes' => ['alias' => 'qa']])->findAll([
            'condition' => 't.sid = :surveyid AND t.parent_qid = :parent_qid AND qa.attribute = :attribute AND qa.value = :value',
            'params'    => [':surveyid' => $iSurveyID, ':parent_qid' => 0, ':attribute' => 'public_statistics', ':value' => '1'],
            'order'     => 'g.group_order, t.question_order'
        ]);

        //...while this is the array from copy/paste which we don't want to replace because this is a nasty source of error
        $allfields = [];
        // check if there are any question with public statistics
        if (isset($questions)) {
            $allfields = $this->createSGQA($questions);
        }// end if -> for removing the error message in case there are no filters
        $summary = $allfields;

        //number of records for this survey
        $totalrecords = 0;
        //count number of answers
        $query = "SELECT count(*) FROM " . $survey->responsesTableName;
        //if incompleted answers should be filtert submitdate has to be not null
        //this setting is taken from config-defaults.php
        if (Yii::app()->getConfig("filterout_incomplete_answers") == 'complete') {
            $query .= " WHERE " . $survey->responsesTableName . ".submitdate is not null";
        }
        $result = Yii::app()->db->createCommand($query)->queryAll();

        //$totalrecords = total number of answers
        foreach ($result as $row) {
            $totalrecords = reset($row);
        }

        // creates array of post variable names
        $postvars = array();
        for (reset($_POST); $key = key($_POST); next($_POST)) {
            $postvars[] = $key;
        }
        $data['thisSurveyTitle'] = $thisSurveyTitle;
        $data['totalrecords'] = $totalrecords;
        $data['summary'] = $summary;
        //show some main data at the beginnung
        // let's allow html formatted questions to show

        //push progress bar from 35 to 40
        $process_status = 40;

        //Show Summary results
        if (isset($summary) && !empty($summary)) {
            //let's run through the survey // Fixed bug 3053 with array_unique
            $runthrough = array_unique($summary);

            //loop through all selected questions
            foreach ($runthrough as $rt) {
                //update progress bar
                if ($process_status < 100) {
                    $process_status++;
                }
            }    // end foreach -> loop through all questions

            $helper = new userstatistics_helper();
            $statisticsoutput .= $helper->generate_statistics($iSurveyID, $summary, $summary, $publicgraphs, 'html', null, $sLanguage, false);
        }    //end if -> show summary results

        $data['statisticsoutput'] = $statisticsoutput;
        $data['aSurveyInfo'] = getSurveyInfo($iSurveyID);
        $data['graphUrl'] = Yii::app()->getController()->createUrl("admin/statistics/sa/graph");

        Yii::app()->getClientScript()->registerScriptFile(Yii::app()->getConfig('generalscripts') . 'statistics_user.js');
        $data['aSurveyInfo']['include_content'] = 'statistics_user';
        $data['aSurveyInfo']['trackUrlPageName'] = 'statistics_user';
        // Set template into last instance. Will be picked up later by the renderer
        $oTemplate = Template::model()->getInstance('', $iSurveyID);
        Yii::app()->twigRenderer->renderTemplateFromFile('layout_statistics_user.twig', $data, false);

        //Delete all Session Data
        Yii::app()->session['finished'] = true;
    }

    /**
     * Create SGQA of all questions which use "public_statistics"
     * Assumes this->sLanguage and this->iSurveyID is set.
     *
     * @param array $filters
     *
     * @return array
     * @throws CException
     */
    public function createSGQA(array $filters)
    {
        $allfields = array();

        /**
         * @var $flt Question
         */
        foreach ($filters as $flt) {
            //SGQ identifier
            $type = $flt['type'];
            $SGQidentifier = $this->iSurveyID . 'X' . $flt->gid . 'X' . $flt->qid;

            //let's switch through the question type for each question
            switch ($type) {
                case Question::QT_K_MULTIPLE_NUMERICAL: // Multiple Numerical
                case Question::QT_Q_MULTIPLE_SHORT_TEXT: // Multiple short text
                    $results = Question::model()->with('questionl10ns')->findAll([
                        'condition' => 'language=:language AND parent_qid=:parent_qid',
                        'params'    => [':language' => $this->sLanguage, ':parent_qid' => $flt->qid],
                        'order'     => 'question_order'
                    ]);
                    foreach ($results as $row) {
                        $allfields[] = $flt->type . $SGQidentifier . $row->title;
                    }
                    break;
                case Question::QT_A_ARRAY_5_POINT: // Array of 5 point choice questions
                case Question::QT_B_ARRAY_10_CHOICE_QUESTIONS: // Array of 10 point choice questions
                case Question::QT_C_ARRAY_YES_UNCERTAIN_NO: // Array of Yes\No\Uncertain questions
                case Question::QT_E_ARRAY_INC_SAME_DEC: // Array of Increase/Same/Decrease questions
                case Question::QT_F_ARRAY: // Array
                case Question::QT_H_ARRAY_COLUMN: // Array (By Column)
                    $results = Question::model()->with('questionl10ns')->findAll([
                        'condition' => 'language=:language AND parent_qid=:parent_qid',
                        'params'    => [':language' => $this->sLanguage, ':parent_qid' => $flt->qid],
                        'order'     => 'question_order'
                    ]);
                    foreach ($results as $row) {
                        $allfields[] = $SGQidentifier . $row->title;
                    }
                    break;
                // all "free text" types (T, U, S)  get the same prefix ("T")
                case Question::QT_S_SHORT_FREE_TEXT: // Short free text
                case Question::QT_T_LONG_FREE_TEXT: // Long free text
                case Question::QT_U_HUGE_FREE_TEXT: // Huge free text
                    $allfields[] = "T" . $SGQidentifier;
                    break;
                case Question::QT_SEMICOLON_ARRAY_TEXT:  // Array (Text)
                case Question::QT_COLON_ARRAY_NUMBERS:  // Array (Numbers)
                    $resultsScale0 = Question::model()->with('questionl10ns')->findAll([
                        'condition' => 'language=:language AND parent_qid=:parent_qid AND scale_id=:scale_id',
                        'params'    => [':language' => $this->sLanguage, ':parent_qid' => $flt->qid, ':scale_id' => 0],
                        'order'     => 'question_order'
                    ]);
                    $resultsScale1 = Question::model()->with('questionl10ns')->findAll([
                        'condition' => 'language=:language AND parent_qid=:parent_qid AND scale_id=:scale_id',
                        'params'    => [':language' => $this->sLanguage, ':parent_qid' => $flt->qid, ':scale_id' => 1],
                        'order'     => 'question_order'
                    ]);
                    foreach ($resultsScale0 as $rowScale0) {
                        foreach ($resultsScale1 as $rowScale1) {
                            $allfields[] = $SGQidentifier . $rowScale0['title'] . "_" . $rowScale1['title'];
                        }
                    }
                    break;
                case Question::QT_R_RANKING: // Ranking
                    $results = Answer::model()->with('answerl10ns')->findAll([
                        'condition' => 'language=:language AND qid=:qid',
                        'params'    => [':language' => $this->sLanguage, ':qid' => $flt->qid],
                        'order'     => 'sortorder'
                    ]);
                    $count = count($results);
                    //loop through all answers. if there are 3 items to rate there will be 3 statistics
                    for ($i = 1; $i <= $count; $i++) {
                        $allfields[] = $flt->type . $SGQidentifier . $i . "-" . strlen($i);
                    }
                    break;
                //Boilerplate questions are only used to put some text between other questions -> no analysis needed
                case Question::QT_X_TEXT_DISPLAY:  //This is a boilerplate question and it has no business in this script
                    break;
                case Question::QT_1_ARRAY_DUAL: // Dual scale
                    $results = Question::model()->with('questionl10ns')->findAll([
                        'condition' => 'language=:language AND parent_qid=:parent_qid',
                        'params'    => [':language' => $this->sLanguage, ':parent_qid' => $flt->qid],
                        'order'     => 'question_order'
                    ]);
                    //loop through answers
                    foreach ($results as $row) {
                        $allfields[] = $SGQidentifier . $row['title'] . "#0";
                        $allfields[] = $SGQidentifier . $row['title'] . "#1";
                    }    //end WHILE -> loop through all answers
                    break;

                case Question::QT_P_MULTIPLE_CHOICE_WITH_COMMENTS:  //P - Multiple choice with comments
                case Question::QT_M_MULTIPLE_CHOICE:  //M - Multiple choice
                case Question::QT_N_NUMERICAL:  //N - Numerical input
                case Question::QT_D_DATE:  //D - Date
                    $allfields[] = $flt->type . $SGQidentifier;
                    break;
                default:   //Default settings
                    $allfields[] = $SGQidentifier;
                    break;
            }    //end switch -> check question types and create filter forms
        }

        return $allfields;
    }
}
