<?php if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}
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
 * Statistics Controller
 *
 * This controller performs statistics actions
 *
 * @package        LimeSurvey
 * @subpackage    Backend
 */
class statistics extends Survey_Common_Action
{

    function __construct($controller, $id)
    {
        parent::__construct($controller, $id);

        Yii::app()->loadHelper("surveytranslator");
        Yii::app()->loadHelper('admin/statistics'); 
    }

    /**
     * Constructor
     */
    public function run($surveyid = 0, $subaction = null)
    {
        //no survey ID? -> come and get one
        if (!isset($surveyid)) {
            $surveyid = returnGlobal('sid'); 
        }

        //Get posted data
        $usegraph       = (int) Yii::app()->request->getPost('usegraph');
        $outputType     = Yii::app()->request->getPost('outputtype');
        $showtextinline = (int) Yii::app()->request->getPost('showtextinline');
        
        $aData     = array();
        $surveyid  = sanitize_int($surveyid);
        $imageurl  = Yii::app()->getConfig("imageurl");
        $oSurvey   = Survey::model()->findByPk($surveyid);
        $helper    = new statistics_helper();
        $error     = $this->checkGraphLibrary()['error'];
        $language  = $oSurvey->language;
        $survlangs = $oSurvey->allLanguages;
       
        $summary   = returnGlobal('summary');
        //if $summary isn't an array we create one
        if (isset($summary) && !is_array($summary)) {
            $summary = explode("+", $summary);
        }

        $statlang  = returnGlobal('statlang');

        // 1: Get list of questions from survey
        $row       = Survey::model()->find('sid = :sid', array(':sid' => $surveyid));
        $datestamp = $row->datestamp;

        $aData['surveyid']       = $surveyid;
        $aData['language']       = $language;
        $aData['imageurl']       = $imageurl;
        $aData['usegraph']       = $usegraph;
        $aData['survlangs']      = $survlangs;
        $aData['showtextinline'] = $showtextinline;
        
        //split up results to extend statistics -> NOT WORKING YET! DO NOT ENABLE THIS!
        $showcombinedresults = 0;

        /*
         * We need this later:
         *  1 - Array Dual Scale
         *  5 - 5 Point Choice
         *  A - Array (5 Point Choice)
         *  B - Array (10 Point Choice)
         *  C - Array (Yes/No/Uncertain)
         *  D - Date
         *  E - Array (Increase, Same, Decrease)
         *  F - Array (Flexible Labels)
         *  G - Gender
         *  H - Array (Flexible Labels) by Column
         *  I - Language Switch
         *  K - Multiple Numerical Input
         *  L - List (Radio)
         *  M - Multiple choice
         *  N - Numerical Input
         *  O - List With Comment
         *  P - Multiple choice with comments
         *  Q - Multiple Short Text
         *  R - Ranking
         *  S - Short Free Text
         *  T - Long Free Text
         *  U - Huge Free Text
         *  X - Boilerplate Question
         *  Y - Yes/No
         *  ! - List (Dropdown)
         *  : - Array (Flexible Labels) multiple drop down
         *  ; - Array (Flexible Labels) multiple texts
         *  | - File Upload

        /*
         * this variable is used in the function shortencode() which cuts off a question/answer title
         * after $maxchars and shows the rest as tooltip
         */
        $maxchars = 50;

        //we collect all the output within this variable
        $statisticsoutput = '';

        //output for chosing questions to cross query
        $cr_statisticsoutput = '';

        //Call the javascript file
        App()->getClientScript()->registerScriptFile(App()->getConfig('adminscripts').'statistics.js', CClientScript::POS_BEGIN);
        App()->getClientScript()->registerScriptFile(App()->getConfig('adminscripts').'json-js/json2.min.js');
        yii::app()->clientScript->registerPackage('jszip');

        $aData['display']['menu_bars']['browse'] = gT("Quick statistics");

        /*
         * We want to have the following data
         * a) "questions" -> all table namens, e.g.
         * qid
         * sid
         * gid
         * type
         * title
         * question
         * preg
         * help
         * other
         * mandatory
         * lid
         * lid1
         * question_order
         * language
         *
         * b) "groups" -> group_name + group_order *
         */

        //store all the data in $rows
        $rows = Question::model()->with('group')->findAll(array('condition'=>'parent_qid = 0 AND group.sid='.$surveyid, 'order'=>'group_order,question_order'));

        //SORT IN NATURAL ORDER!
        usort($rows, 'groupOrderThenQuestionOrder');
        
        //put the question information into the filter array
        $filters = array();
        $aGroups = array();
        $keyone = 0;

        foreach ($rows as $row) {

            $sGroupName = $row->group->questionGroupL10ns[$language]->group_name;
            //store some column names in $filters array
            $filters[] = array($row['qid'],
            $row['gid'],
            $row['type'],
            $row['title'],
            $sGroupName,
            flattenText($row->questionL10ns[$language]['question']));

            if (!in_array($sGroupName, $aGroups)) {
                //$aGroups[] = $sGroupName;
                $aGroups[$sGroupName]['gid'] = $row['gid'];
                $aGroups[$sGroupName]['name'] = $sGroupName;
            }

            $aGroups[$sGroupName]['questions'][$keyone] = array($row['qid'],
            $row['gid'],
            $row['type'],
            $row['title'],
            $sGroupName,
            flattenText($row->questionL10ns[$language]['question']));
            $keyone = $keyone + 1;
        }

        $aData['filters'] = $filters;
        $aData['aGroups'] = $aGroups;
    
        //pre-selection of filter forms
        if (incompleteAnsFilterState() == "complete") {
            $selecthide = "selected='selected'";
            $selectshow = "";
            $selectinc = "";
        } elseif (incompleteAnsFilterState() == "incomplete") {
            $selecthide = "";
            $selectshow = "";
            $selectinc = "selected='selected'";
        } else {
            $selecthide = "";
            $selectshow = "selected='selected'";
            $selectinc = "";
        }

        $aData['selecthide'] = $selecthide;
        $aData['selectshow'] = $selectshow;
        $aData['selectinc'] = $selectinc;
        $aData['error'] = $error;
        $aData['datestamp'] = $datestamp;

        //second row below options -> filter settings headline
        $filterchoice_state = returnGlobal('filterchoice_state');
        $aData['filterchoice_state'] = $filterchoice_state;

        /*
         * let's go through the filter array which contains
         ['qid'],
         ['gid'],
         ['type'],
         ['title'],
         ['group_name'],
         ['question'],
         ['lid'],
         ['lid1']);
         */

        $currentgroup = '';
        $counter = 0;
        foreach ($filters as $key1 => $flt) {
            //is there a previous question type set?
            /*
             * remember: $flt is structured like this
             ['qid'],
             ['gid'],
             ['type'],
             ['title'],
             ['group_name'],
             ['question'],
             ['lid'],
             ['lid1']);
             */


            /*
             * Check question type: This question types will be used (all others are separated in the if clause)
             *  5 - 5 Point Choice
             G - Gender
             I - Language Switch
             L - List (Radio)
             M - Multiple choice
             N - Numerical Input
             | - File Upload
             O - List With Comment
             P - Multiple choice with comments
             Y - Yes/No
             ! - List (Dropdown) )
             */


            /////////////////////////////////////////////////////////////////////////////////////////////////
            //This section presents the filter list, in various different ways depending on the question type
            /////////////////////////////////////////////////////////////////////////////////////////////////

            //let's switch through the question type for each question
            switch ($flt[2]) {
                case Question::QT_K_MULTIPLE_NUMERICAL_QUESTION: // Multiple Numerical
                    //get answers
                    $result = Question::model()->getQuestionsForStatistics('title, question', "parent_qid=$flt[0] ", 'question_order');
                    $aData['result'][$key1]['key1'] = $result;
                    break;

                case Question::QT_Q_MULTIPLE_SHORT_TEXT: // Multiple Short Text

                    //get subqestions
                    $result = Question::model()->getQuestionsForStatistics('title as code, question as answer', "parent_qid=$flt[0] ", 'question_order');
                    $aData['result'][$key1] = $result;
                    break;

                    //----------------------- ARRAYS --------------------------

                case Question::QT_A_ARRAY_5_CHOICE_QUESTIONS: // ARRAY OF 5 POINT CHOICE QUESTIONS

                    //get answers
                    $result = Question::model()->getQuestionsForStatistics('title, question', "parent_qid=$flt[0] ", 'question_order');
                    $aData['result'][$key1] = $result;
                    break;

                    //just like above only a different loop
                case Question::QT_B_ARRAY_10_CHOICE_QUESTIONS: // ARRAY OF 10 POINT CHOICE QUESTIONS
                    $result = Question::model()->getQuestionsForStatistics('title, question', "parent_qid=$flt[0]", 'question_order');
                    $aData['result'][$key1] = $result;
                    break;

                case Question::QT_C_ARRAY_YES_UNCERTAIN_NO: // ARRAY OF YES\No\gT("Uncertain") QUESTIONS
                    //get answers
                    $result = Question::model()->getQuestionsForStatistics('title, question', "parent_qid=$flt[0] ", 'question_order');
                    $aData['result'][$key1] = $result;
                    break;

                    //similiar to the above one
                case Question::QT_E_ARRAY_OF_INC_SAME_DEC_QUESTIONS: // ARRAY OF Increase/Same/Decrease QUESTIONS
                    $result = Question::model()->getQuestionsForStatistics('title, question', "parent_qid=$flt[0] ", 'question_order');
                    $aData['result'][$key1] = $result;
                    break;

                case Question::QT_SEMICOLON_ARRAY_MULTI_FLEX_TEXT:  //ARRAY (Multi Flex) (Text)
                    $result = Question::model()->getQuestionsForStatistics('title, question', "parent_qid=$flt[0]  AND scale_id = 0", 'question_order');
                    $aData['result'][$key1] = $result;
                    foreach ($result as $key => $row) {
                        $fresult = Question::model()->getQuestionsForStatistics('title, question', "parent_qid=$flt[0] AND scale_id = 1", 'question_order');
                        $aData['fresults'][$key1][$key] = $fresult;
                    }
                    break;

                case Question::QT_COLON_ARRAY_MULTI_FLEX_NUMBERS:  //ARRAY (Multi Flex) (Numbers)
                    $result = Question::model()->getQuestionsForStatistics('title, question', "parent_qid=$flt[0] AND scale_id = 0", 'question_order');
                    $aData['result'][$key1] = $result;
                    foreach ($result as $row) {
                        $fresult = Question::model()->getQuestionsForStatistics('*', "parent_qid=$flt[0] AND scale_id = 1", 'question_order, title');
                        $aData['fresults'][$key1] = $fresult;
                    }
                    break;
                    /*
                     * For question type "F" and "H" you can use labels.
                     * The only difference is that the labels are applied to column heading
                     * or rows respectively
                     */
                case Question::QT_F_ARRAY_FLEXIBLE_ROW: // FlEXIBLE ARRAY
                case Question::QT_H_ARRAY_FLEXIBLE_COLUMN: // ARRAY (By Column)
                    //Get answers. We always use the answer code because the label might be too long elsewise
                    $result = Question::model()->getQuestionsForStatistics('title, question', "parent_qid=$flt[0]", 'question_order');
                    $aData['result'][$key1] = $result;

                    //check all the answers
                    foreach ($result as $row) {
                        $fresult = Answer::model()->getAnswersForStatistics('*', "qid=$flt[0]", 'sortorder, code');
                        $aData['fresults'][$key1] = $fresult;
                    }

                    //$statisticsoutput .= "\t\t\t\t<td>\n";
                    $counter = 0;
                    break;

                case Question::QT_R_RANKING_STYLE: //RANKING
                    //get some answers
                    $result = Answer::model()->getAnswersForStatistics('code, answer', "qid=$flt[0]", 'sortorder, answer');
                    $aData['result'][$key1] = $result;
                    break;

                case Question::QT_1_ARRAY_MULTISCALE: // MULTI SCALE

                    //get answers
                    $result = Question::model()->getQuestionsForStatistics('title, question', "parent_qid=$flt[0] ", 'question_order');
                    $aData['result'][$key1] = $result;
                    //loop through answers
                    foreach ($result as $key => $row) {

                        //check if there is a dualscale_headerA/B
                        $dshresult = QuestionAttribute::model()->getQuestionsForStatistics('value', "qid=$flt[0] AND attribute = 'dualscale_headerA'", '');
                        $aData['dshresults'][$key1][$key] = $dshresult;


                        $fresult = Answer::model()->getAnswersForStatistics('*', "qid=$flt[0]  AND scale_id = 0", 'sortorder, code');

                        $aData['fresults'][$key1][$key] = $fresult;


                        $dshresult2 = QuestionAttribute::model()->getQuestionsForStatistics('value', "qid=$flt[0] AND attribute = 'dualscale_headerB'", '');
                        $aData['dshresults2'][$key1][$key] = $dshresult2;
                    }
                    break;

                case Question::QT_P_MULTIPLE_CHOICE_WITH_COMMENTS:  //P - Multiple choice with comments
                case Question::QT_M_MULTIPLE_CHOICE:  //M - Multiple choice

                    //get answers
                    $result = Question::model()->getQuestionsForStatistics('title, question', "parent_qid = $flt[0]", 'question_order');
                    $aData['result'][$key1] = $result;
                    break;

                    /*
                     * This question types use the default settings:
                     *     L - List (Radio)
                     O - List With Comment
                     P - Multiple choice with comments
                     ! - List (Dropdown)
                     */
                default:

                    //get answers
                    $result = Answer::model()->findAll("qid=".$flt[0]);
                    $aData['result'][$key1] = $result;
                    break;

            }    //end switch -> check question types and create filter forms

            $currentgroup = $flt[1];

            $counter++;

            //temporary save the type of the previous question
            //used to adjust linebreaks
            $previousquestiontype = $flt[2];

        }

        // ----------------------------------- END FILTER FORM ---------------------------------------

        //Show Summary results
        if (isset($summary) && $summary) {
            switch ($outputType) {
                case 'html':
                    $statisticsoutput .= $helper->generate_html_chartjs_statistics($surveyid, $summary, $summary, $usegraph, $outputType, 'DD', $statlang);
                    break;
                case 'pdf':
                    $helper->generate_statistics($surveyid, $summary, $summary, $usegraph, $outputType, 'D', $statlang);
                    exit;
                    break;
                case 'xls':
                    $helper->generate_statistics($surveyid, $summary, $summary, $usegraph, $outputType, 'DD', $statlang);
                    exit;
                    break;
                default:
                    break;
            }

        }    //end if -> show summary results
        $aData['error'] = $error;
        $aData['sStatisticsLanguage'] = $statlang;
        $aData['output'] = $statisticsoutput;
        $aData['summary'] = $summary;
        $aData['oStatisticsHelper'] = $helper;
        $aData['fresults'] = (isset($aData['fresults'])) ? $aData['fresults'] : false;
        $aData['dateformatdetails'] = getDateFormatData(Yii::app()->session['dateformat']);

        if (!isset($aData['result'])) {
            $aData['result'] = null;
        }
       
        $this->_renderWrappedTemplate('export', 'statistics_view', $aData);
    }

    private function checkGraphLibrary()
    {
        $result = array();
        $result['grapherror'] = false;   
        $result['error'] = '';
        
        //check if required libraries for graphs are installed
        if (!function_exists("gd_info")) {
            $result['grapherror'] = true;
            $result['error'] .= '<br />'.gT('You do not have the GD Library installed. Showing charts requires the GD library to function properly.');
            $result['error'] .= '<br />'.gT('visit http://us2.php.net/manual/en/ref.image.php for more information').'<br />';
        } elseif (!function_exists("imageftbbox")) {
            $result['grapherror'] = true;
            $result['error'] .= '<br />'.gT('You do not have the Freetype Library installed. Showing charts requires the Freetype library to function properly.');
            $result['error'] .= '<br />'.gT('visit http://us2.php.net/manual/en/ref.image.php for more information').'<br />';
        }

        return $result;
    }
    /**
     * Render statics with graphs for user 
     * @param int $surveyId Current Survey Id 
     */
    public function simpleStatistics($surveyid)
    {
        $usegraph = 1;
        $iSurveyId = sanitize_int($surveyid);
        $aData['surveyid'] = $iSurveyId;
        $showcombinedresults = 0;
        $maxchars = 50;
        $statisticsoutput = '';
        $cr_statisticsoutput = '';
        $survey = Survey::model()->findByPk($surveyid);

        // Set language for questions and answers to base language of this survey
        $language = $survey->language;
        $summary = array();
        $summary[0] = "datestampE";
        $summary[1] = "datestampG";
        $summary[2] = "datestampL";
        $summary[3] = "idG";
        $summary[4] = "idL";

        // 1: Get list of questions from survey
        $rows = Question::model()->getQuestionList($surveyid);

        //SORT IN NATURAL ORDER!
        usort($rows, 'groupOrderThenQuestionOrder');

        // The questions to display (all question)
        foreach ($rows as $row) {
            $type = $row['type'];
            if ($type == Question::QT_T_LONG_FREE_TEXT || $type == Question::QT_N_NUMERICAL) {
                $summary[] = $type.$iSurveyId.'X'.$row['gid'].'X'.$row['qid'];
            }
            switch ($type) {

                // Double scale cases
                case Question::QT_COLON_ARRAY_MULTI_FLEX_NUMBERS:
                    $qidattributes = QuestionAttribute::model()->getQuestionAttributes($row['qid']);
                    if (!$qidattributes['input_boxes']) {
                        $qid = $row['qid'];
                        $results = Question::model()->getQuestionsForStatistics('*', "parent_qid='$qid'  AND scale_id = 0", 'question_order, title');
                        $fresults = Question::model()->getQuestionsForStatistics('*', "parent_qid='$qid'  AND scale_id = 1", 'question_order, title');
                        foreach ($results as $row1) {
                            foreach ($fresults as $row2) {
                                $summary[] = $iSurveyId.'X'.$row['gid'].'X'.$row['qid'].$row1['title'].'_'.$row2['title'];
                            }
                        }
                    }
                break;

                case Question::QT_1_ARRAY_MULTISCALE:
                    $qid = $row['qid'];
                    $results = Question::model()->getQuestionsForStatistics('*', "parent_qid='$qid' ", 'question_order, title');
                    foreach ($results as $row1) {
                        $summary[] = $iSurveyId.'X'.$row['gid'].'X'.$row['qid'].$row1['title'].'#0';
                        $summary[] = $iSurveyId.'X'.$row['gid'].'X'.$row['qid'].$row1['title'].'#1';
                    }

                break;

                case Question::QT_R_RANKING_STYLE: //RANKING
                    $qid = $row['qid'];
                    $results = Question::model()->getQuestionsForStatistics('title, question', "parent_qid='$qid' ", 'question_order');
                    $count = count($results);
                    //loop through all answers. if there are 3 items to rate there will be 3 statistics
                    for ($i = 1; $i <= $count; $i++) {
                        $summary[] = $type.$iSurveyId.'X'.$row['gid'].'X'.$row['qid'].'-'.$i;
                    }
                break;

                // Cases with subquestions
                case Question::QT_A_ARRAY_5_CHOICE_QUESTIONS:
                case Question::QT_F_ARRAY_FLEXIBLE_ROW: // FlEXIBLE ARRAY
                case Question::QT_H_ARRAY_FLEXIBLE_COLUMN: // ARRAY (By Column)
                case Question::QT_E_ARRAY_OF_INC_SAME_DEC_QUESTIONS:
                case Question::QT_B_ARRAY_10_CHOICE_QUESTIONS:
                case Question::QT_C_ARRAY_YES_UNCERTAIN_NO:
                    //loop through all answers. if there are 3 items to rate there will be 3 statistics
                    $qid = $row['qid'];
                    $results = Question::model()->getQuestionsForStatistics('title, question', "parent_qid='$qid' ", 'question_order');
                    foreach ($results as $row1) {
                        $summary[] = $iSurveyId.'X'.$row['gid'].'X'.$row['qid'].$row1['title'];
                    }
                break;

                // Cases with subanwsers, need a question type as first letter
                case Question::QT_P_MULTIPLE_CHOICE_WITH_COMMENTS:  //P - Multiple choice with comments
                case Question::QT_M_MULTIPLE_CHOICE:  //M - Multiple choice
                case Question::QT_S_SHORT_FREE_TEXT:
                    $summary[] = $type.$iSurveyId.'X'.$row['gid'].'X'.$row['qid'];
                break;

                // Not shown (else would only show 'no answer' )
                case Question::QT_K_MULTIPLE_NUMERICAL_QUESTION:
                case Question::QT_ASTERISK_EQUATION:
                case Question::QT_D_DATE:
                case Question::QT_T_LONG_FREE_TEXT: // Long free text
                case Question::QT_U_HUGE_FREE_TEXT: // Huge free text
                case Question::QT_VERTICAL_FILE_UPLOAD: // File Upload, we don't show it
                case Question::QT_N_NUMERICAL:
                case Question::QT_Q_MULTIPLE_SHORT_TEXT:
                case Question::QT_SEMICOLON_ARRAY_MULTI_FLEX_TEXT:

                    break;


                default:
                    $summary[] = $iSurveyId.'X'.$row['gid'].'X'.$row['qid'];
                break;
            }
        }

        // ----------------------------------- END FILTER FORM ---------------------------------------

        $helper = new statistics_helper();
        $showtextinline = (int) Yii::app()->request->getPost('showtextinline', 0);
        $aData['showtextinline'] = $showtextinline;

        //Show Summary results
        $aData['usegraph'] = $usegraph;
        $outputType = 'html';
        $statlang = returnGlobal('statlang');
        $statisticsoutput .= $helper->generate_simple_statistics($surveyid, $summary, $summary, $usegraph, $outputType, 'DD', $statlang);

        $aData['sStatisticsLanguage'] = $statlang;
        $aData['output'] = $statisticsoutput;
        $aData['summary'] = $summary;
        $aData['oStatisticsHelper'] = $helper;
        $aData['menu']['expertstats'] = true;

        //Call the javascript file
        App()->getClientScript()->registerScriptFile(App()->getConfig('adminscripts').'statistics.js', CClientScript::POS_BEGIN);
        App()->getClientScript()->registerScriptFile(App()->getConfig('adminscripts').'json-js/json2.min.js');
        yii::app()->clientScript->registerPackage('jspdf');
        yii::app()->clientScript->registerPackage('jszip');
        echo $this->_renderWrappedTemplate('export', 'statistics_user_view', $aData);
    }

    /**
     * Renders template(s) wrapped in header and footer
     *
     * @param string $sAction Current action, the folder to fetch views from
     * @param string $aViewUrls View url(s)
     * @param array $aData Data to be passed on. Optional.
     */
    protected function _renderWrappedTemplate($sAction = 'export', $aViewUrls = array(), $aData = array(), $sRenderFile = false)
    {
        yii::app()->clientScript->registerPackage('bootstrap-switch');
        yii::app()->clientScript->registerPackage('jspdf');
        $oSurvey = Survey::model()->findByPk($aData['surveyid']);

        $aData['menu']['closeurl'] = Yii::app()->request->getUrlReferrer(Yii::app()->createUrl("/admin/survey/sa/view/surveyid/".$aData['surveyid']));

        $aData['display'] = array();
        $aData['display']['menu_bars'] = false;
        $aData['display']['menu_bars']['browse'] = gT('Browse responses'); // browse is independent of the above
        $aData['menu']['edition'] = true;
        $aData['menu']['stats'] = true;
        $aData['menu']['close'] = true;
        $aData['sidemenu']['state'] = false;
        $iSurveyId = $aData['surveyid'];
        $aData['title_bar']['title'] = gT('Browse responses').': '.$oSurvey->currentLanguageSettings->surveyls_title;
        $aData['subaction'] = gT('Statistics');
        parent::_renderWrappedTemplate($sAction, $aViewUrls, $aData, $sRenderFile);
    }


}
