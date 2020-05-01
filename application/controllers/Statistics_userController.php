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

class Statistics_userController extends SurveyController
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
     */
    public function _remap($method, $params = array())
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
            // CHANGE JSW_NZ - let's get the survey title for display
            $thisSurveyTitle = $surveyinfo["name"];
            // CHANGE JSW_NZ - let's get css from individual template.css - so define path
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
            require_once(APPPATH . 'third_party/pchart/pChart.class.php');
            require_once(APPPATH . 'third_party/pchart/pData.class.php');
            require_once(APPPATH . 'third_party/pchart/pCache.class.php');

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

        //SET THE TEMPLATE DIRECTORY
        //---------- CREATE STATISTICS ----------
        //some progress bar stuff

        // Create progress bar which is shown while creating the results
        //~ $prb = new ProgressBar();
        //~ $prb->pedding = 2;    // Bar Pedding
        //~ $prb->brd_color = "#404040 #dfdfdf #dfdfdf #404040";    // Bar Border Color

        //~ $prb->setFrame();    // set ProgressBar Frame
        //~ $prb->frame['left'] = 50;    // Frame position from left
        //~ $prb->frame['top'] =     80;    // Frame position from top
        //~ $prb->addLabel('text','txt1',gT("Please wait ..."));    // add Text as Label 'txt1' and value 'Please wait'
        //~ $prb->addLabel('percent','pct1');    // add Percent as Label 'pct1'
        //~ $prb->addButton('btn1',gT('Go back'),'?action=statistics&amp;sid='.$iSurveyID);    // add Button as Label 'btn1' and action '?restart=1'

        //~ $prb->show();    // show the ProgressBar

        //~ // 1: Get list of questions with answers chosen
        //~ //"Getting Questions and Answer ..." is shown above the bar
        //~ $prb->setLabelValue('txt1',gT('Getting questions and answers ...'));
        //~ $prb->moveStep(5);

        // creates array of post variable names
        $postvars = array();
        for (reset($_POST); $key = key($_POST); next($_POST)) {
            $postvars[] = $key;
        }
        $data['thisSurveyTitle'] = $thisSurveyTitle;
        $data['totalrecords'] = $totalrecords;
        $data['summary'] = $summary;
        //show some main data at the beginnung
        // CHANGE JSW_NZ - let's allow html formatted questions to show

        //push progress bar from 35 to 40
        $process_status = 40;

        //Show Summary results
        if (isset($summary) && !empty($summary)) {
            //"Generating Summaries ..." is shown above the progress bar
            //~ $prb->setLabelValue('txt1',gT('Generating summaries ...'));
            //~ $prb->moveStep($process_status);

            //let's run through the survey // Fixed bug 3053 with array_unique
            $runthrough = array_unique($summary);

            //loop through all selected questions
            foreach ($runthrough as $rt) {

                //update progress bar
                if ($process_status < 100) {
                    $process_status++;
                }
                //~ $prb->moveStep($process_status);

            }    // end foreach -> loop through all questions

            $helper = new userstatistics_helper();
            $statisticsoutput .= $helper->generate_statistics($iSurveyID, $summary, $summary, $publicgraphs, 'html', null, $sLanguage, false);

        }    //end if -> show summary results

        $data['statisticsoutput'] = $statisticsoutput;
        //done! set progress bar to 100%
        if (isset($prb)) {
            //~ $prb->setLabelValue('txt1',gT('Completed'));
            //~ $prb->moveStep(100);
            //~ $prb->hide();
        }

        Yii::app()->getClientScript()->registerScriptFile(Yii::app()->getConfig('generalscripts') . 'statistics_user.js');
        $this->layout = "public";
        $this->render('/statistics_user_view', $data);

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
                case Question::QT_K_MULTIPLE_NUMERICAL_QUESTION: // Multiple Numerical
                case Question::QT_Q_MULTIPLE_SHORT_TEXT: // Multiple Short Text
                    $results = Question::model()->with('questionl10ns')->findAll([
                        'condition' => 'language=:language AND parent_qid=:parent_qid',
                        'params'    => [':language' => $this->sLanguage, ':parent_qid' => $flt->qid],
                        'order'     => 'question_order'
                    ]);
                    foreach ($results as $row) {
                        $allfields[] = $flt->type . $SGQidentifier . $row->title;
                    }
                    break;
                case Question::QT_A_ARRAY_5_CHOICE_QUESTIONS: // ARRAY OF 5 POINT CHOICE QUESTIONS
                case Question::QT_B_ARRAY_10_CHOICE_QUESTIONS: // ARRAY OF 10 POINT CHOICE QUESTIONS
                case Question::QT_C_ARRAY_YES_UNCERTAIN_NO: // ARRAY OF YES\No\gT("Uncertain") QUESTIONS
                case Question::QT_E_ARRAY_OF_INC_SAME_DEC_QUESTIONS: // ARRAY OF Increase/Same/Decrease QUESTIONS
                case Question::QT_F_ARRAY_FLEXIBLE_ROW: // FlEXIBLE ARRAY
                case Question::QT_H_ARRAY_FLEXIBLE_COLUMN: // ARRAY (By Column)
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
                case Question::QT_T_LONG_FREE_TEXT: // Long free text
                case Question::QT_U_HUGE_FREE_TEXT: // Huge free text
                case Question::QT_S_SHORT_FREE_TEXT: // Short free text
                    $allfields = "T" . $SGQidentifier;
                    break;
                case Question::QT_SEMICOLON_ARRAY_MULTI_FLEX_TEXT:  //ARRAY (Multi Flex) (Text)
                case Question::QT_COLON_ARRAY_MULTI_FLEX_NUMBERS:  //ARRAY (Multi Flex) (Numbers)
                    $resultsScale0 = Question::model()->with('questionl10ns')->findAll([
                        'condition' => 'language=:language AND parent_qid=:parent_qid AND scale_id=:scale:id',
                        'params'    => [':language' => $this->sLanguage, ':parent_qid' => $flt->qid, ':scale_id' => 0],
                        'order'     => 'question_order'
                    ]);
                    $resultsScale1 = Question::model()->with('questionl10ns')->findAll([
                        'condition' => 'language=:language AND parent_qid=:parent_qid AND scale_id=:scale:id',
                        'params'    => [':language' => $this->sLanguage, ':parent_qid' => $flt->qid, ':scale_id' => 1],
                        'order'     => 'question_order'
                    ]);
                    foreach ($resultsScale0 as $rowScale0) {
                        foreach ($resultsScale1 as $rowScale1) {
                            $allfields[] = $SGQidentifier . reset($rowScale0) . "_" . $rowScale1['title'];
                        }
                    }
                    break;
                case Question::QT_R_RANKING_STYLE: //RANKING
                    $results = Question::model()->with('questionl10ns')->findAll([
                        'condition' => 'language=:language AND parent_qid=:parent_qid',
                        'params'    => [':language' => $this->sLanguage, ':parent_qid' => $flt->qid],
                        'order'     => 'question_order'
                    ]);
                    $count = count($results);
                    //loop through all answers. if there are 3 items to rate there will be 3 statistics
                    for ($i = 1; $i <= $count; $i++) {
                        $allfields[] = $flt->type . $SGQidentifier . $i . "-" . strlen($i);
                    }
                    break;
                //Boilerplate questions are only used to put some text between other questions -> no analysis needed
                case Question::QT_X_BOILERPLATE_QUESTION:  //This is a boilerplate question and it has no business in this script
                    break;
                case Question::QT_1_ARRAY_MULTISCALE: // MULTI SCALE
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
