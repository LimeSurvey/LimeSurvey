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
 * Condition  Controller
 *
 * This controller performs token actions
 *
 * @package        LimeSurvey
 * @subpackage    Backend
 */
class ConditionsAction extends SurveyCommonAction
{
    /**
     * @var array
     */
    private $stringComparisonOperators;

    /**
     * @var array
     */
    private $nonStringComparisonOperators;

    /**
     * @var int
     */
    private $iSurveyID;

    /**
     * @var string
     */
    private $language;

    /**
     * True if there exists a survey participants table for this survey
     * @var boolean
     */
    private $tokenTableExists;

    /**
     * @var array
     */
    private $tokenFieldsAndNames;

    private $surveyCondition;

    /**
     * Init some stuff
     * @param null $controller
     * @param null $id
     */
    public function __construct($controller = null, $id = null)
    {
        parent::__construct($controller, $id);

        $this->stringComparisonOperators = array(
            "<"      => gT("Less than"),
            "<="     => gT("Less than or equal to"),
            "=="     => gT("Equals"),
            "!="     => gT("Not equal to"),
            ">="     => gT("Greater than or equal to"),
            ">"      => gT("Greater than"),
            "RX"     => gT("Regular expression"),
            "a<b"    => gT("Less than (Strings)"),
            "a<=b"   => gT("Less than or equal to (Strings)"),
            "a>=b"   => gT("Greater than or equal to (Strings)"),
            "a>b"    => gT("Greater than (Strings)")
        );

        $this->nonStringComparisonOperators = array(
            "<"  => gT("Less than"),
            "<=" => gT("Less than or equal to"),
            "==" => gT("equals"),
            "!=" => gT("Not equal to"),
            ">=" => gT("Greater than or equal to"),
            ">"  => gT("Greater than"),
            "RX" => gT("Regular expression")
        );

        $diContainer = \LimeSurvey\DI::getContainer();

        $this->surveyCondition = $diContainer->get(
            LimeSurvey\Models\Services\SurveyCondition::class
        );
    }

    /**
     * Main Entry Method.
     *
     * @param string $subaction Given Subaction
     * @param int $iSurveyID Given Survey ID
     * @param int $gid Given Group ID
     * @param int $qid Given Question ID
     *
     * @return void
     * @throws CException
     * @throws CHttpException
     */
    public function index($subaction, $iSurveyID = null, $gid = null, $qid = null)
    {
        $request = Yii::app()->request;
        $iSurveyID = (int) $iSurveyID;
        $imageurl = Yii::app()->getConfig("adminimageurl");
        list($this->iSurveyID, $this->tokenTableExists, $this->tokenFieldsAndNames) = $this->surveyCondition->initialize([
            'iSurveyID' => $iSurveyID
        ]);

        $aData = [];
        $aData['sidemenu']['state'] = false;
        $aData['sidemenu']['landOnSideMenuTab'] = 'structure';
        $aData['title_bar']['title'] = gT("Conditions designer");

        $aData['subaction'] = gT("Conditions designer");
        $aData['currentMode'] = ($subaction == 'conditions' || $subaction == 'copyconditionsform') ? $subaction : 'edit';

        $postSubaction = $request->getPost('subaction');
        if (!empty($postSubaction)) {
            $subaction = $postSubaction;
        }

        //BEGIN Sanitizing POSTed data
        if (!isset($iSurveyID)) {
            $iSurveyID = returnGlobal('sid');
        }
        if (!isset($qid)) {
            $qid = returnGlobal('qid');
        }
        if (!isset($gid)) {
            $gid = returnGlobal('gid');
        }
        $gid = (int) $gid;
        $qid = (int) $qid;

        $p_scenario = returnGlobal('scenario');
        $p_cqid = returnGlobal('cqid');
        if ($p_cqid == '') {
            // we are not using another question as source of condition
            $p_cqid = 0;
        }

        $p_cid = returnGlobal('cid');
        $p_subaction = $subaction;
        $p_cquestions = returnGlobal('cquestions');
        $p_csrctoken = returnGlobal('csrctoken');
        $p_prevquestionsgqa = returnGlobal('prevQuestionSGQA');

        $p_canswers = [];
        if (is_array($request->getPost('canswers'))) {
            foreach ($request->getPost('canswers') as $key => $val) {
                $p_canswers[$key] = preg_replace("/[^_.a-zA-Z0-9]@/", "", (string) $val);
            }
        }

        $method = $this->getMethod();

        if ($request->getPost('method') != '') {
            if (!in_array($request->getPost('method'), array_keys($method))) {
                $p_method = "==";
            } else {
                $p_method = trim($request->getPost('method', ''));
            }
        } else {
            $p_method = null;
        }

        $postNewScenarioNum = $request->getPost('newscenarionum');
        if (!empty($postNewScenarioNum)) {
            $p_newscenarionum = sanitize_int($postNewScenarioNum);
        } else {
            $p_newscenarionum = null;
        }
        //END Sanitizing POSTed data

        // Make sure that there is a sid
        if (!isset($iSurveyID) || !$iSurveyID) {
            Yii::app()->setFlashMessage(gT('You have not selected a survey'), 'error');
            $this->getController()->redirect(array('admin'));
        }

        // This will redirect after logic is reset
        if ($p_subaction == "resetsurveylogic") {
            $this->resetSurveyLogic($iSurveyID);
        }

        // Make sure that there is a qid
        if (!isset($qid) || !$qid) {
            Yii::app()->setFlashMessage(gT('You have not selected a question'), 'error');
            Yii::app()->getController()->redirect(Yii::app()->request->urlReferrer);
        }

        // If we made it this far, then lets develop the menu items
        // add the conditions container table
        $extraGetParams = "";
        if (isset($qid) && isset($gid)) {
            $extraGetParams = "/gid/{$gid}/qid/{$qid}";
        }

        $conditionsoutput_action_error = ""; // defined during the actions

        // Begin process actions
        $args = array(
            'p_scenario'    => $p_scenario,
            'p_cquestions'  => $p_cquestions,
            'p_csrctoken'   => $p_csrctoken,
            'p_canswers'    => $p_canswers,
            'p_cqid'        => $p_cqid,
            'p_cid'         => $p_cid,
            'p_subaction'   => $p_subaction,
            'p_prevquestionsgqa' => $p_prevquestionsgqa,
            'p_newscenarionum' => $p_newscenarionum,
            'p_method'      => $p_method,
            'qid'           => $qid,
            'gid'           => $gid,
            'request'       => $request
        );

        // Subaction = form submission
        $this->applySubaction($p_subaction, $args);

        $cquestions = array();
        $canswers   = array();
        $pquestions = array();

        $language = Survey::model()->findByPk($iSurveyID)->language;
        $this->language = $language;

        //BEGIN: GATHER INFORMATION
        // 1: Get information for this question
        // @todo : use viewHelper::getFieldText and getFieldCode for 2.06 for string show to user
        $aData['surveyIsAnonymized'] = $surveyIsAnonymized = $this->getSurveyIsAnonymized();

        list($questiontitle, $sCurrentFullQuestionText) = $this->getQuestionTitleAndText($qid);

        // 2: Get all other questions that occur before this question that are pre-determined answer types

        // To avoid natural sort order issues,
        // first get all questions in natural sort order
        // , and find out which number in that order this question is
        // Then, using the same array which is now properly sorted by group then question
        // Create an array of all the questions that appear AFTER the current one
        $questionRows = $this->getQuestionRows();
        $questionlist = $this->getQuestionList($qid, $questionRows);
        $postquestionlist = $this->getPostQuestionList($qid, $questionRows);

        $theserows = $this->getTheseRows($questionlist);
        $postrows  = $this->getPostRows($postquestionlist);

        $questionscount = count($theserows);
        $postquestionscount = count($postrows);

        if (isset($postquestionscount) && $postquestionscount > 0) {
            //Build the array used for the questionNav and copyTo select boxes
            foreach ($postrows as $pr) {
                $pquestions[] = array("text" => $pr['title'] . ": " . (string) substr(strip_tags((string) $pr['question']), 0, 80),
                    "fieldname" => $pr['sid'] . "X" . $pr['gid'] . "X" . $pr['qid']);
            }
        }


        // Previous question parsing ==> building cquestions[] and canswers[]
        if ($questionscount > 0) {
            $qids = [];
            for ($index = 0; $index < count($theserows); $index++) {
                if ($theserows[$index]['type'] == "1") {
                    $qids[] = $theserows[$index]['qid'];
                }
            }
            if (count($qids)) {
                $rawQuestions = Question::model()->findAllByPk($qids);
                $questions = [];
                foreach ($rawQuestions as $rawQuestion) {
                    $questions[$rawQuestion->qid] = $rawQuestion;
                }
                for ($index = 0; $index < count($theserows); $index++) {
                    if ($theserows[$index]['type'] == "1") {
                        $theserows[$index]['qObject'] = $questions[$theserows[$index]['qid']];
                    }
                }
            }

            list($cquestions, $canswers) = $this->getCAnswersAndCQuestions($theserows);
        } //if questionscount > 0
        //END Gather Information for this question

        $args['sCurrentFullQuestionText'] = $sCurrentFullQuestionText;
        $args['questiontitle'] = $questiontitle;
        $args['gid'] = $gid;
        $questionNavOptions = $this->getQuestionNavOptions($theserows, $postrows, $args);

        //Now display the information and forms

        $javascriptpre = $this->getJavascriptForMatching($canswers, $cquestions, $surveyIsAnonymized);

        $aViewUrls = array();

        $oQuestion = Question::model()->find('qid=:qid', array(':qid' => $qid));
        $aData['oQuestion'] = $oQuestion;

        // @todo why surveyid and iSurveyID will be used? Only use one!
        $aData['surveyid'] = $iSurveyID;
        $aData['qid'] = $qid;
        $aData['gid'] = $gid;
        $aData['imageurl'] = $imageurl;
        $aData['extraGetParams'] = $extraGetParams;
        $aData['questionNavOptions'] = $questionNavOptions;
        $aData['conditionsoutput_action_error'] = $conditionsoutput_action_error;
        $aData['javascriptpre'] = $javascriptpre;


        // Back Button
        $aData['showBackButton'] = true;

        $scenarios = $this->getAllScenarios($qid);

        // Some extra args to getEditConditionForm
        $args['subaction'] = $subaction;
        // @todo why surveyid and iSurveyID will be used? Only use one!
        $args['iSurveyID'] = $this->iSurveyID;
        $args['gid'] = $gid;
        $args['qcount'] = $this->getQCount($cquestions);
        $args['method'] = $method;
        $args['cquestions'] = $cquestions;
        $args['scenariocount'] = count($scenarios);

        $aData['quickAddConditionForm'] = $this->getQuickAddConditionForm($args);

        $aData['quickAddConditionURL'] = $this->getController()->createUrl(
            '/admin/conditions/sa/quickAddCondition',
            array(
                'surveyId' => $this->iSurveyID,
                'gid'      => $gid,
                'qid'      => $qid
            )
        );

        $aViewUrls['conditionshead_view'][] = $aData;

        $conditionsList = array();

        //BEGIN DISPLAY CONDITIONS FOR THIS QUESTION
        if (
            $subaction == 'index' ||
            $subaction == 'editconditionsform' || $subaction == 'insertcondition' ||
            $subaction == "editthiscondition" || $subaction == "delete" ||
            $subaction == "updatecondition" || $subaction == "deletescenario" ||
            $subaction == "renumberscenarios" || $subaction == "deleteallconditions" ||
            $subaction == "updatescenario" ||
            $subaction == 'copyconditionsform' || $subaction == 'copyconditions' || $subaction == 'conditions'
        ) {
            //3: Get other conditions currently set for this question
            $s = 0;

            $scenariocount = count($scenarios);

            $aData['conditionsoutput'] = '';
            $aData['extraGetParams'] = $extraGetParams;
            $aData['questionNavOptions'] = $questionNavOptions;
            $aData['conditionsoutput_action_error'] = $conditionsoutput_action_error;
            $aData['javascriptpre'] = $javascriptpre;
            $aData['sCurrentQuestionText'] = $questiontitle . ': ' . viewHelper::flatEllipsizeText($sCurrentFullQuestionText, true, '120');

            $aData['scenariocount'] = $scenariocount;
            if (empty(trim((string) $oQuestion->relevance)) || !empty($oQuestion->conditions)) {
                $aViewUrls['conditionslist_view'][] = $aData;
            }

            if ($scenariocount > 0) {
                App()->getClientScript()->registerScriptFile(App()->getConfig('adminscripts') . 'checkgroup.js', LSYii_ClientScript::POS_BEGIN);
                foreach ($scenarios as $scenarionr) {
                    if ($s == 0 && $scenariocount > 1) {
                        $aData['showScenarioText'] = 'normal';
                    } elseif ($s > 0) {
                        $aData['showScenarioText'] = 'withOr';
                    } else {
                        $aData['showScenarioText'] = null;
                    }

                    if (
                        !empty($aData['showScenarioText']) &&
                        ($subaction == "editconditionsform" ||
                        $subaction == "insertcondition" ||
                        $subaction == "updatecondition" ||
                        $subaction == "editthiscondition" ||
                        $subaction == "renumberscenarios" ||
                        $subaction == "updatescenario" ||
                        $subaction == "deletescenario" ||
                        $subaction == "delete")
                    ) {
                        $aData['showScenarioButtons'] = true;
                    } else {
                        $aData['showScenarioButtons'] = false;
                    }

                    $aData['scenarionr'] = $scenarionr;

                    // Used when click on button to add condition to scenario
                    $aData['addConditionToScenarioURL'] = $this->getController()->createUrl(
                        '/admin/conditions/sa/index/',
                        array(
                            'subaction' => 'editconditionsform',
                            'surveyid' => $this->iSurveyID,
                            'gid' => $gid,
                            'qid' => $qid,
                            'scenarioNr' => $scenarionr['scenario']
                        )
                    );

                    if (!isset($aViewUrls['output'])) {
                        $aViewUrls['output'] = '';
                    }

                    $aData['conditionHtml'] = '';

                    unset($currentfield);

                    $conditionscount = Condition::model()->getConditionCount($qid, $this->language, $scenarionr);
                    $conditions = Condition::model()->getConditions($qid, $this->language, $scenarionr);
                    $conditionscounttoken = Condition::model()->getConditionCountToken($qid, $scenarionr);
                    $resulttoken = Condition::model()->getConditionsToken($qid, $scenarionr);

                    $conditionscount = $conditionscount + $conditionscounttoken;

                    ////////////////// BUILD CONDITIONS DISPLAY
                    if ($conditionscount > 0) {
                        $aConditionsMerged = array();
                        foreach ($resulttoken->readAll() as $arow) {
                            $aConditionsMerged[] = $arow;
                        }
                        foreach ($conditions as $arow) {
                            $aConditionsMerged[] = $arow;
                        }

                        foreach ($aConditionsMerged as $rows) {
                            if ($rows['method'] == "") {
                                $rows['method'] = "==";
                            } //Fill in the empty method from previous versions

                            // This variable is used for condition.php view; $aData is used for other view
                            $data = array();

                            if (isset($currentfield) && $currentfield != $rows['cfieldname']) {
                                $data['andOrOr'] = gT('and');
                            } elseif (isset($currentfield)) {
                                $data['andOrOr'] = gT('or');
                            } else {
                                $data['andOrOr'] = '';
                            }

                            $data['formAction'] = $this->getController()->createUrl(
                                '/admin/conditions/sa/index/',
                                array(
                                    'subaction' => $subaction,
                                    'surveyid' => $this->iSurveyID,
                                    'gid' => $gid,
                                    'qid' => $qid
                                )
                            );
                            $data['row'] = $rows;
                            $data['subaction'] = $subaction;
                            $data['scenarionr'] = $scenarionr;
                            $data['method'] = $method;

                            $leftOperandType = 'unknown'; // prevquestion, tokenattr
                            if (preg_match('/^{TOKEN:([^}]*)}$/', (string) $rows['cfieldname'], $extractedTokenAttr) > 0) {
                                if ($surveyIsAnonymized) {
                                    $data['name'] = sprintf(gT("Unable to use %s in anonymized survey."), trim((string) $rows['cfieldname'], "{}"));
                                } else {
                                    $leftOperandType = 'tokenattr';
                                    $thisAttrName = $this->getAttributeName($extractedTokenAttr);
                                    $data['name'] = $thisAttrName;
                                    // TIBO not sure this is used anymore !!
                                    $conditionsList[] = array(
                                        "cid"  => $rows['cid'],
                                        "text" => $thisAttrName
                                    );
                                }
                            } else {
                                $leftOperandType = 'prevquestion';
                                foreach ($cquestions as $cqn) {
                                    if ($cqn[3] == $rows['cfieldname']) {
                                        $data['name'] = $cqn[0] . "(qid{$rows['cqid']})";
                                        $conditionsList[] = array(
                                            "cid"  => $rows['cid'],
                                            "text" => $cqn[0] . " ({$rows['value']})"
                                        );
                                    }
                                }
                            }
                            if (!isset($data['name'])) {
                                $data['name'] = sprintf(gT("Variable not found: %s"), $rows['cfieldname']);
                            }

                            // let's read the condition's right operand
                            // determine its type and display it
                            $rightOperandType = 'unknown'; // predefinedAnsw,constantVal, prevQsgqa, tokenAttr, regexp
                            if ($rows['method'] == 'RX') {
                                $rightOperandType = 'regexp';
                                $data['target'] = HTMLEscape($rows['value']);
                            } elseif (preg_match('/^@([0-9]+X[0-9]+X[^@]*)@$/', (string) $rows['value'], $matchedSGQA) > 0) {
                                // SGQA
                                $rightOperandType = 'prevQsgqa';
                                $textfound = false;
                                $matchedSGQAText = '';
                                foreach ($cquestions as $cqn) {
                                    if ($cqn[3] == $matchedSGQA[1]) {
                                        $matchedSGQAText = $cqn[0];
                                        $textfound = true;
                                        break;
                                    }
                                }

                                if ($textfound === false) {
                                    $matchedSGQAText = $rows['value'] . ' (' . gT("Not found") . ')';
                                }

                                $data['target'] = HTMLEscape($matchedSGQAText);
                            } elseif (!$surveyIsAnonymized && preg_match('/^{TOKEN:([^}]*)}$/', (string) $rows['value'], $extractedTokenAttr) > 0) {
                                $rightOperandType = 'tokenAttr';
                                $aTokenAttrNames = $this->tokenFieldsAndNames;
                                if ($this->tokenTableExists) {
                                    $thisAttrName = HTMLEscape($aTokenAttrNames[strtolower($extractedTokenAttr[1])]['description']) . " [" . gT("From survey participants table") . "]";
                                } else {
                                    $thisAttrName = HTMLEscape($extractedTokenAttr[1]) . " [" . gT("Non-existing survey participants table") . "]";
                                }
                                $data['target'] = $thisAttrName;
                            } elseif (isset($canswers)) {
                                foreach ($canswers as $can) {
                                    if ($can[0] == $rows['cfieldname'] && $can[1] == $rows['value']) {
                                        $data['target'] = "$can[2] ($can[1])\n";
                                        $rightOperandType = 'predefinedAnsw';
                                    }
                                }
                            }

                            // if $rightOperandType is still unknown then it is a simple constant
                            if ($rightOperandType == 'unknown') {
                                $rightOperandType = 'constantVal';
                                if ($rows['value'] == ' ' || $rows['value'] == '') {
                                    $data['target'] = gT("No answer");
                                } else {
                                    $data['target'] = HTMLEscape($rows['value']);
                                }
                            }

                            if (
                                $subaction == "editconditionsform"
                                || $subaction == "insertcondition"
                                || $subaction == "updatecondition"
                                || $subaction == "editthiscondition"
                                || $subaction == "renumberscenarios"
                                || $subaction == "deleteallconditions"
                                || $subaction == "updatescenario"
                                || $subaction == "deletescenario"
                                || $subaction == "delete"
                            ) {
                                // show single condition action buttons in edit mode

                                $aData['rows'] = $rows;
                                $aData['sImageURL'] = Yii::app()->getConfig('adminimageurl');

                                $data['editButtons'] = $this->getController()->renderPartial('/admin/conditions/includes/conditions_edit', $aData, true);
                                $data['hiddenFields'] = $this->getHiddenFields($rows, $leftOperandType, $rightOperandType);
                            } else {
                                $data['editButtons'] = '';
                                $data['hiddenFields'] = '';
                            }

                            $aData['conditionHtml'] .= $this->getController()->renderPartial(
                                '/admin/conditions/includes/condition',
                                $data,
                                true
                            );

                            $currentfield = $rows['cfieldname'];
                        }
                    }

                    $s++;

                    $aViewUrls['output'] .= $this->getController()->renderPartial(
                        '/admin/conditions/includes/conditions_scenario',
                        $aData,
                        true
                    );
                }
                // If we have a condition, all ways reset the condition, this can fix old import (see #09344)
                // LimeExpressionManager::UpgradeConditionsToRelevance(NULL,$qid);
            } elseif (!empty(trim((string) $oQuestion->relevance)) ||  trim((string) $oQuestion->relevance) == '1') {
                $aViewUrls['output'] = $this->getController()->renderPartial('/admin/conditions/customized_conditions', $aData, true);
            } else {
                // no condition ==> disable delete all conditions button, and display a simple comment
                // no_conditions
                $aViewUrls['output'] = $this->getController()->renderPartial('/admin/conditions/no_condition', $aData, true);
            }

            //// To close the div opened in condition header....  see : https://goo.gl/BY7gUJ
            $aViewUrls['afteroutput'] = '</div></div></div>';
        }
        //END DISPLAY CONDITIONS FOR THIS QUESTION

        // Display the copy conditions form
        if (
            $subaction == "copyconditionsform"
            || $subaction == "copyconditions"
        ) {
            $aViewUrls['output'] .= $this->getCopyForm($qid, $gid, $conditionsList, $pquestions);
        }

        if (
            $subaction == "editconditionsform"
            || $subaction == "insertcondition"
            || $subaction == "updatecondition"
            || $subaction == "deletescenario"
            || $subaction == "renumberscenarios"
            || $subaction == "deleteallconditions"
            || $subaction == "updatescenario"
            || $subaction == "editthiscondition"
            || $subaction == "delete"
        ) {
            $aViewUrls['output'] .= $this->getEditConditionForm($args);
        }

        $returnUrl = Yii::app()->createUrl('questionAdministration/view/surveyid/' . $iSurveyID . '/gid/' . $gid . '/qid/' . $qid);
        $aData['returnUrl'] = $returnUrl;
        // Top Bar
        $aData['topbar']['middleButtons'] = Yii::app()->getController()->renderPartial(
            '/admin/conditions/partial/topbarBtns/leftSideButtons',
            ['aData' => $aData],
            true
        );
        $aData['topbar']['rightButtons'] = Yii::app()->getController()->renderPartial(
            '/admin/conditions/partial/topbarBtns/rightSideButtons',
            ['aData' => $aData],
            true
        );
        $aData['conditionsoutput'] = $aViewUrls['output'];
        $this->renderWrappedTemplate('conditions', $aViewUrls, $aData);

        // TMSW Condition->Relevance:  Must call LEM->ConvertConditionsToRelevance() whenever Condition is added or updated - what is best location for that action?
    }

    /**
     * This array will be used to explain which conditions is used to evaluate the question
     * @return array
     */
    protected function getMethod()
    {
        if (Yii::app()->getConfig('stringcomparizonoperators') == 1) {
            $method = $this->stringComparisonOperators;
        } else {
            $method = $this->nonStringComparisonOperators;
        }

        return $method;
    }

    /**
     * @param $iSurveyID
     * @return void
     * @throws CException
     * @throws CHttpException
     */
    protected function resetSurveyLogic($iSurveyID)
    {
        if (empty(Yii::app()->request->getPost('ok'))) {
            $data = array('iSurveyID' => $iSurveyID);
            $content = $this->getController()->renderPartial('/admin/conditions/deleteAllConditions', $data, true);
            $this->renderWrappedTemplate('conditions', array('message' => array(
                'title' => gT("Warning"),
                'message' => $content
            )));
            Yii::app()->end();
        } else {
            $this->surveyCondition->resetSurveyLogic();
            Yii::app()->setFlashMessage(gT("All conditions in this survey have been deleted."));
            $this->getController()->redirect(array('surveyAdministration/view/surveyid/' . $iSurveyID));
        }
    }

    /**
     * Add a new condition
     * @todo Better way than to extract $args
     * @param array $args
     * @return void
     */
    protected function insertCondition(array $args)
    {
        // Extract p_scenario, p_cquestions, ...
        /** @var integer $qid */
        /** @var integer $gid */
        /** @var string $p_scenario */
        /** @var string $p_cqid */
        /** @var string $p_method */
        /** @var array $p_canswers */
        /** @var CHttpRequest $request */
        /** @var string $editSourceTab */

        $request = $args['request'];
        $gid = $args['gid'];
        $qid = $args['qid'];

        $editSourceTab = $request->getPost('editSourceTab');
        $editTargetTab = $request->getPost('editTargetTab');
        $this->surveyCondition->insertCondition($args, $editSourceTab, $editTargetTab, Yii::app()->setFlashMessage(...), Yii::app()->request->getPost('ConditionConst', ''), Yii::app()->request->getPost('prevQuestionSGQA', ''), Yii::app()->request->getPost('tokenAttr', ''), Yii::app()->request->getPost('ConditionRegexp', ''));

        $this->redirectToConditionStart($qid, $gid);
    }

    /**
     * As insertCondition() but using Ajax, called from quickAddCondition
     * @todo Code duplication
     * @return array [message, result], where result = 'success' or 'error'
     */
    protected function insertConditionAjax($args)
    {
        // Extract scenario, cquestions, ...
        /** @var integer $qid */
        /** @var integer $gid */
        /** @var string $scenario */
        /** @var string $cqid */
        /** @var string $method */
        /** @var array $p_canswers */
        /** @var string $editSourceTab */
        /** @var string $editTargetTab */
        /** @var string $ConditionConst */
        /** @var string $prevQuestionSGQA */
        /** @var string $tokenAttr */
        /** @var string $ConditionRegexp */
        extract($args);

        if (isset($cquestions) && $cquestions != '' && $editSourceTab == '#SRCPREVQUEST') {
            $conditionCfieldname = $cquestions;
        } elseif (isset($csrctoken) && $csrctoken != '') {
            $conditionCfieldname = $csrctoken;
        } else {
            return array(gT("The condition could not be added! It did not include the question and/or answer upon which the condition was based. Please ensure you have selected a question and an answer."), 'error');
        }

        $condition_data = array(
            'qid'        => $qid,
            'scenario'   => $scenario,
            'cqid'       => $cqid,
            'cfieldname' => $conditionCfieldname,
            'method'     => $method
        );

        if (!empty($canswers) && $editSourceTab == '#SRCPREVQUEST') {
            $results = array();

            foreach ($canswers as $ca) {
                //First lets make sure there isn't already an exact replica of this condition
                $condition_data['value'] = $ca;

                $result = Condition::model()->findAllByAttributes($condition_data);

                $count_caseinsensitivedupes = count($result);

                if ($count_caseinsensitivedupes == 0) {
                    $results[] = Condition::model()->insertRecords($condition_data);
                    ;
                }
            }

            // Check if any result returned false
            if (in_array(false, $results, true)) {
                return array(gT('Could not insert all conditions.'), 'error');
            } elseif (!empty($results)) {
                return array(gT('Condition added.'), 'success');
            } else {
                return array(
                    gT(
                        "The condition could not be added! It did not include the question and/or answer upon which the condition was based. Please ensure you have selected a question and an answer.",
                        "js"
                    ),
                    'error'
                );
            }
        } else {
            $posted_condition_value = null;

            // Other conditions like constant, other question or token field
            if ($editTargetTab == "#CONST") {
                $posted_condition_value = $ConditionConst;
            } elseif ($editTargetTab == "#PREVQUESTIONS") {
                $posted_condition_value = $prevQuestionSGQA;
            } elseif ($editTargetTab == "#TOKENATTRS") {
                $posted_condition_value = $tokenAttr;
            } elseif ($editTargetTab == "#REGEXP") {
                $posted_condition_value = $ConditionRegexp;
            }

            if ($posted_condition_value) {
                $condition_data['value'] = $posted_condition_value;
                $result = Condition::model()->insertRecords($condition_data);
            } else {
                $result = null;
            }

            if ($result === false) {
                return array(gT('Could not insert all conditions.'), 'error');
            } elseif ($result === true) {
                return array(gT('Condition added.'), 'success');
            } else {
                return array(
                    gT(
                        "The condition could not be added! It did not include the question and/or answer upon which the condition was based. Please ensure you have selected a question and an answer.",
                        "js"
                    ),
                    'error'
                );
            }
        }
    }

    /**
     * Used by quick-add form to add conditions async
     * @return void
     * @throws CException
     */
    public function quickAddCondition()
    {
        Yii::import('application.helpers.admin.ajax_helper', true);
        $request = Yii::app()->request;
        $data = $this->getQuickAddData($request);

        list($message, $status) = $this->insertConditionAjax($data);

        if ($status == 'success') {
            LimeExpressionManager::UpgradeConditionsToRelevance(null, $data['qid']);
            ls\ajax\AjaxHelper::outputSuccess($message);
        } elseif ($status == 'error') {
            ls\ajax\AjaxHelper::outputError($message);
        } else {
            ls\ajax\AjaxHelper::outputError('Internal error: Could not add condition, status unknown: ' . $status);
        }
    }

    /**
     * Get posted data from quick-add modal form
     * @param LSHttpRequest $request
     * @return array
     */
    protected function getQuickAddData(LSHttpRequest $request)
    {
        $result = array();
        $keys = array(
            'scenario',
            'cquestions',
            'method',
            'canswers',
            'ConditionConst',
            'ConditionRegexp',
            'sid',
            'qid',
            'gid',
            'cqid',
            'canswersToSelect',
            'editSourceTab',
            'editTargetTab',
            'csrctoken',
            'prevQuestionSGQA',
            'tokenAttr'
        );
        foreach ($keys as $key) {
            $value = $request->getPost('quick-add-' . $key, '');
            $value = str_replace('QUICKADD-', '', $value); // Remove QUICKADD- from editSourceTab/editTargetTab
            $result[$key] = $value;
        }

        return $result;
    }

    /**
     * Update a condition
     * @param array $args
     * @return void
     */
    protected function updateCondition(array $args)
    {
        /** @var integer $qid */
        /** @var integer $gid */
        /** @var string $p_scenario */
        /** @var string $p_cqid */
        /** @var string $p_cid */
        /** @var string $p_method */
        /** @var array $p_canswers */
        /** @var CHttpRequest $request */
        /** @var string $editSourceTab */
        $request = $args['request'];
        $this->surveyCondition->updateCondition($args, $request->getPost('editTargetTab'), Yii::app()->setFlashMessage(...), Yii::app()->request->getPost('ConditionConst', ''), Yii::app()->request->getPost('prevQuestionSGQA', ''), Yii::app()->request->getPost('tokenAttr', ''), Yii::app()->request->getPost('ConditionRegexp', ''));
        $this->redirectToConditionStart($args['qid'], $args['gid']);
    }

    /**
     * @param array $args
     * @return void
     * @throws CException
     */
    protected function renumberScenarios(array $args)
    {
        /** @var string $p_cid */
        extract($args);

        $query = "SELECT DISTINCT scenario FROM {{conditions}} WHERE qid=:qid ORDER BY scenario";
        $result = Yii::app()->db->createCommand($query)->bindParam(":qid", $qid, PDO::PARAM_INT)->query() or safeDie("Couldn't select scenario<br />$query<br />");
        $newindex = 1;

        foreach ($result->readAll() as $srow) {
            Condition::model()->insertRecords(array('scenario' => $newindex), true, array('qid' => $qid, 'scenario' => $srow['scenario']));
            $newindex++;
        }
        LimeExpressionManager::UpgradeConditionsToRelevance(null, $qid);
        Yii::app()->setFlashMessage(gT("All conditions scenarios were renumbered."));
    }

    /**
     * @param array $args
     * @return void
     * @throws CException
     */
    protected function copyConditions(array $args)
    {
        extract($args);

        $copyconditionsfrom = returnGlobal('copyconditionsfrom');
        $copyconditionsto = returnGlobal('copyconditionsto');
        if (isset($copyconditionsto) && is_array($copyconditionsto) && isset($copyconditionsfrom) && is_array($copyconditionsfrom)) {
            //Get the conditions we are going to copy and quote them properly
            foreach ($copyconditionsfrom as &$entry) {
                $entry = Yii::app()->db->quoteValue($entry);
            }
            $query = "SELECT * FROM {{conditions}}\n"
                . "WHERE cid in (";
            $query .= implode(", ", $copyconditionsfrom);
            $query .= ")";
            $result = Yii::app()->db->createCommand($query)->query() or
                safeDie("Couldn't get conditions for copy<br />$query<br />");

            foreach ($result->readAll() as $row) {
                $proformaconditions[] = array(
                    "scenario"      =>    $row['scenario'],
                    "cqid"          =>    $row['cqid'],
                    "cfieldname"    =>    $row['cfieldname'],
                    "method"        =>    $row['method'],
                    "value"         =>    $row['value']
                );
            } // while

            foreach ($copyconditionsto as $copyc) {
                list(,, $newqid) = explode("X", (string) $copyc);
                foreach ($proformaconditions as $pfc) {
                    //TIBO

                    //First lets make sure there isn't already an exact replica of this condition
                    $conditions_data = array(
                        'qid'        => (int) $newqid,
                        'scenario'   => $pfc['scenario'],
                        'cqid'       => $pfc['cqid'],
                        'cfieldname' => $pfc['cfieldname'],
                        'method'     => $pfc['method'],
                        'value'      => $pfc['value']
                    );

                    $result = Condition::model()->findAllByAttributes($conditions_data);

                    $count_caseinsensitivedupes = count($result);

                    $countduplicates = 0;
                    if ($count_caseinsensitivedupes != 0) {
                        foreach ($result as $ccrow) {
                            if ($ccrow['value'] == $pfc['value']) {
                                $countduplicates++;
                            }
                        }
                    }

                    if ($countduplicates == 0) {
                        //If there is no match, add the condition.
                        Condition::model()->insertRecords($conditions_data);
                        $conditionCopied = true;
                    } else {
                        $conditionDuplicated = true;
                    }
                }
            }

            if (isset($conditionCopied) && $conditionCopied === true) {
                if (isset($conditionDuplicated) && $conditionDuplicated == true) {
                    Yii::app()->setFlashMessage(gT("Condition successfully copied (some were skipped because they were duplicates)"), 'warning');
                } else {
                    Yii::app()->setFlashMessage(gT("Condition successfully copied"));
                }
            } else {
                Yii::app()->setFlashMessage(gT("No conditions could be copied (due to duplicates)"), 'error');
            }
        }
        LimeExpressionManager::UpgradeConditionsToRelevance($this->iSurveyID); // do for whole survey, since don't know which questions affected.
    }

    /**
     * Switch on action to update/copy/add condition etc
     * @param string $p_subaction
     * @param array $args
     * @return void
     */
    protected function applySubaction($p_subaction, array $args)
    {
        /** @var string $p_cid */
        /** @var string $qid */
        /** @var string $gid */
        /** @var string $p_newscenarionum */
        /** @var string $p_scenario */
        extract($args);
        switch ($p_subaction) {
            // Insert new condition
            case "insertcondition":
                $this->insertCondition($args);
                break;
            // Update entry if this is an edit
            case "updatecondition":
                $this->updateCondition($args);
                break;
            // Delete entry if this is delete
            case "delete":
                $this->surveyCondition->deleteCondition($qid, $p_cid);
                $this->redirectToConditionStart($qid, $gid);
                break;
            // Delete all conditions in this scenario
            case "deletescenario":
                $this->surveyCondition->deleteScenario($qid, $p_scenario);
                $this->redirectToConditionStart($qid, $gid);
                break;
            // Update scenario
            case "updatescenario":
                $this->surveyCondition->updateScenario($p_newscenarionum, $qid, $p_scenario, Yii::app()->setFlashMessage(...));
                break;
            // Delete all conditions for this question
            case "deleteallconditions":
                $this->surveyCondition->deleteAllConditions($qid, Yii::app()->setFlashMessage(...));
                $this->redirectToConditionStart($qid, $gid);
                break;
            // Renumber scenarios
            case "renumberscenarios":
                $this->renumberScenarios($args);
                $this->redirectToConditionStart($qid, $gid);
                break;
            // Copy conditions if this is copy
            case "copyconditions":
                $this->copyConditions($args);
                break;
        }
    }

    /**
     * Renders template(s) wrapped in header and footer
     *
     * @param string $sAction Current action, the folder to fetch views from
     * @param string|array $aViewUrls View url(s)
     * @param array $aData Data to be passed on. Optional.
     * @param bool $sRenderFile
     * @return void
     * @throws CHttpException
     */
    protected function renderWrappedTemplate($sAction = 'conditions', $aViewUrls = array(), $aData = array(), $sRenderFile = false)
    {
        parent::renderWrappedTemplate($sAction, $aViewUrls, $aData, $sRenderFile);
    }

    /**
     * @param array $questionlist
     * @return array
     */
    protected function getTheseRows(array $questionlist)
    {
        $theserows = array();
        foreach ($questionlist as $ql) {
            $result = Question::model()->with(array(
                'group' => array(
                    'condition' => 'questiongroupl10ns.language = :lang',
                    'params' => array(':lang' => $this->language),
                    'alias'  => 'group',
                ),
                'group.questiongroupl10ns' => array('alias' => 'questiongroupl10ns' ),
                'questionl10ns'
            ))->findAllByAttributes(array('qid' => $ql, 'parent_qid' => 0, 'sid' => $this->iSurveyID));

            // And store again these questions in this array...
            foreach ($result as $myrows) {
                //key => value
                $theserows[] = array(
                    "qid"        =>    $myrows['qid'],
                    "sid"        =>    $myrows['sid'],
                    "gid"        =>    $myrows['gid'],
                    "question"    =>    $myrows->questionl10ns[$this->language]['question'],
                    "type"        =>    $myrows['type'],
                    "mandatory"    =>    $myrows['mandatory'],
                    "other"        =>    $myrows['other'],
                    "title"        =>    $myrows['title']
                );
            }
        }
        return $theserows;
    }

    /**
     * @param array $postquestionlist
     * @return array
     */
    protected function getPostRows(array $postquestionlist)
    {
        $postrows = array();
        foreach ($postquestionlist as $pq) {
            $aoQuestions = Question::model()->findAllByAttributes(array('qid' => $pq, 'parent_qid' => 0, 'sid' => $this->iSurveyID));

            foreach ($aoQuestions as $oQuestion) {
                $postrows[] = array(
                    "qid"        =>    $oQuestion['qid'],
                    "sid"        =>    $oQuestion['sid'],
                    "gid"        =>    $oQuestion['gid'],
                    "question"    =>    $oQuestion->questionl10ns[$this->language]->question,
                    "type"        =>    $oQuestion['type'],
                    "mandatory"    =>    $oQuestion['mandatory'],
                    "other"        =>    $oQuestion['other'],
                    "title"        =>    $oQuestion['title']
                );
            }
        }
        return $postrows;
    }

    /**
     * @param int $qid
     * @return array (title, question text)
     */
    protected function getQuestionTitleAndText($qid)
    {
        $oQuestion = Question::model()->findByPk($qid);
        return array($oQuestion->title, $oQuestion->questionl10ns[$this->language]->question);
    }

    /**
     * @return boolean True if anonymized == 'Y' for this survey
     */
    protected function getSurveyIsAnonymized()
    {
        $info = getSurveyInfo($this->iSurveyID);
        return $info['anonymized'] == 'Y';
    }

    /**
     * @param int $qid
     * @return array
     */
    protected function getQuestionRows()
    {
        $qresult = Question::model()->primary()->getQuestionList($this->iSurveyID);

        //'language' => $this->language
        $qrows = array();
        foreach ($qresult as $k => $v) {
            $qrows[$k] = array_merge($v->attributes, $v->group->attributes);
        }

        return $qrows;
    }

    /**
     * @param int $qid
     * @param array $qrows
     * @return array
     */
    protected function getQuestionList($qid, array $qrows)
    {
        $position = "before";
        $questionlist = array();
        // Go through each question until we reach the current one
        foreach ($qrows as $qrow) {
            if ($qrow["qid"] != $qid && $position == "before") {
                // remember all previous questions
                // all question types are supported.
                $questionlist[] = $qrow["qid"];
            } elseif ($qrow["qid"] == $qid) {
                break;
            }
        }
        return $questionlist;
    }

    /**
     * @param int $qid
     * @param array $qrows
     * @return array
     */
    protected function getPostQuestionList($qid, array $qrows)
    {
        $position = "before";
        $postquestionlist = array();
        foreach ($qrows as $qrow) {
            //Go through each question until we reach the current one
            if ($qrow["qid"] == $qid) {
                $position = "after";
            } elseif ($qrow["qid"] != $qid && $position == "after") {
                $postquestionlist[] = $qrow['qid'];
            }
        }
        return $postquestionlist;
    }

    /**
     * @param array $theserows
     * @return array (cquestion, canswers)
     * @throws CException
     */
    protected function getCAnswersAndCQuestions(array $theserows)
    {
        $X = "X";
        $cquestions = array();
        $canswers = array();

        foreach ($theserows as $rows) {
            $shortquestion = $rows['title'] . ": " . strip_tags((string) $rows['question']);

            if ($rows['type'] == "A" || $rows['type'] == "B" || $rows['type'] == "C" || $rows['type'] == "E" || $rows['type'] == "F" || $rows['type'] == "H") {
                $aresult = Question::model()->with('questionl10ns')->findAllByAttributes(array('parent_qid' => $rows['qid']), array('order' => 'question_order ASC'));

                foreach ($aresult as $arows) {
                    $shortanswer = "{$arows['title']}: [" . flattenText($arows->questionl10ns[$this->language]->question) . "]";
                    $shortquestion = $rows['title'] . ":$shortanswer " . flattenText($rows['question']);
                    $cquestions[] = array($shortquestion, $rows['qid'], $rows['type'],
                        $rows['sid'] . $X . $rows['gid'] . $X . $rows['qid'] . $arows['title']
                    );

                    switch ($rows['type']) {
                        // Array 5 buttons
                        case "A":
                            for ($i = 1; $i <= 5; $i++) {
                                $canswers[] = array($rows['sid'] . $X . $rows['gid'] . $X . $rows['qid'] . $arows['title'], $i, $i);
                            }
                            break;
                        // Array 10 buttons
                        case "B":
                            for ($i = 1; $i <= 10; $i++) {
                                $canswers[] = array($rows['sid'] . $X . $rows['gid'] . $X . $rows['qid'] . $arows['title'], $i, $i);
                            }
                            break;
                        // Array Y/N/NA
                        case "C":
                            $canswers[] = array($rows['sid'] . $X . $rows['gid'] . $X . $rows['qid'] . $arows['title'], "Y", gT("Yes"));
                            $canswers[] = array($rows['sid'] . $X . $rows['gid'] . $X . $rows['qid'] . $arows['title'], "U", gT("Uncertain"));
                            $canswers[] = array($rows['sid'] . $X . $rows['gid'] . $X . $rows['qid'] . $arows['title'], "N", gT("No"));
                            break;
                            // Array >/=/<
                        case "E":
                            $canswers[] = array($rows['sid'] . $X . $rows['gid'] . $X . $rows['qid'] . $arows['title'], "I", gT("Increase"));
                            $canswers[] = array($rows['sid'] . $X . $rows['gid'] . $X . $rows['qid'] . $arows['title'], "S", gT("Same"));
                            $canswers[] = array($rows['sid'] . $X . $rows['gid'] . $X . $rows['qid'] . $arows['title'], "D", gT("Decrease"));
                            break;
                            // Array Flexible Row
                        case "F":
                            // Array Flexible Column
                        case "H":
                            $fresult = Answer::model()->with(array(
                            'answerl10ns' => array(
                                'condition' => 'answerl10ns.language = :lang',
                                'params' => array(':lang' => $this->language),
                                'alias' => 'answerl10ns',
                            )))->findAllByAttributes(
                                array(
                                    'qid' => $rows['qid'],
                                    'scale_id' => 0,
                                )
                            );
                            foreach ($fresult as $frow) {
                                $canswers[] = array($rows['sid'] . $X . $rows['gid'] . $X . $rows['qid'] . $arows['title'], $frow['code'], $frow->answerl10ns[$this->language]->answer);
                            }
                            break;
                    }
                    // Only Show No-Answer if question is not mandatory
                    if ($rows['mandatory'] != 'Y' && $rows['mandatory'] != 'S') {
                        $canswers[] = array($rows['sid'] . $X . $rows['gid'] . $X . $rows['qid'] . $arows['title'], "", gT("No answer"));
                    }
                } //foreach
            } elseif ($rows['type'] == Question::QT_COLON_ARRAY_NUMBERS || $rows['type'] == Question::QT_SEMICOLON_ARRAY_TEXT) {
                // Multiflexi
                // Get the Y-Axis
                $fquery = "SELECT sq.*, q.other, l10ns.question
                    FROM {{questions sq}}, {{questions q}}, {{question_l10ns l10ns}}
                    WHERE sq.sid={$this->iSurveyID}
                    AND sq.parent_qid=q.qid
                    AND sq.qid = l10ns.qid
                    AND l10ns.language=:lang1
                    AND q.qid=:qid
                    AND sq.scale_id=0
                    ORDER BY sq.question_order";
                $sLanguage = $this->language;
                $y_axis_db = Yii::app()->db->createCommand($fquery)
                    ->bindParam(":lang1", $sLanguage, PDO::PARAM_STR)
                    ->bindParam(":qid", $rows['qid'], PDO::PARAM_INT)
                    ->query();

                // Get the X-Axis
                $aquery = "SELECT sq.*, l10ns.question
                    FROM {{questions q}}, {{questions sq}}, {{question_l10ns l10ns}}
                    WHERE q.sid={$this->iSurveyID}
                    AND sq.parent_qid=q.qid
                    AND sq.qid = l10ns.qid
                    AND l10ns.language=:lang1
                    AND q.qid=:qid
                    AND sq.scale_id=1
                    ORDER BY sq.question_order";

                $x_axis_db = Yii::app()->db->createCommand($aquery)
                    ->bindParam(":lang1", $sLanguage, PDO::PARAM_STR)
                    ->bindParam(":qid", $rows['qid'], PDO::PARAM_INT)
                    ->query() or safeDie("Couldn't get answers to Array questions<br />$aquery<br />");

                $x_axis = [];

                foreach ($x_axis_db->readAll() as $frow) {
                    $x_axis[$frow['title']] = $frow['question'];
                }

                foreach ($y_axis_db->readAll() as $yrow) {
                    foreach ($x_axis as $key => $val) {
                        $shortquestion = $rows['title'] . ":{$yrow['title']}:$key: [" . strip_tags((string) $yrow['question']) . "][" . strip_tags((string) $val) . "] " . flattenText($rows['question']);
                        $cquestions[] = array($shortquestion, $rows['qid'], $rows['type'], $rows['sid'] . $X . $rows['gid'] . $X . $rows['qid'] . $yrow['title'] . "_" . $key);
                        if ($rows['mandatory'] != 'Y' && $rows['mandatory'] != 'S') {
                        }
                    }
                }
                unset($x_axis);
            } elseif ($rows['type'] == "1") {
                /* Used to get dualscale_headerA and dualscale_headerB */
                $attr = QuestionAttribute::model()->getQuestionAttributes($rows['qObject']);
                //Dual scale
                $aresult = Question::model()->with(array(
                            'questionl10ns' => array(
                                'condition' => 'questionl10ns.language = :lang',
                                'params' => array(':lang' => $this->language)
                            )))->findAllByAttributes(array('parent_qid' => $rows['qid']), array('order' => 'question_order ASC, scale_id ASC'));
                foreach ($aresult as $arows) {
                    $sLanguage = $this->language;
                    // dualscale_header are always set, but can be empty
                    $label1 = empty($attr['dualscale_headerA'][$sLanguage]) ? gT('Scale 1') : $attr['dualscale_headerA'][$sLanguage];
                    $label2 = empty($attr['dualscale_headerB'][$sLanguage]) ? gT('Scale 2') : $attr['dualscale_headerB'][$sLanguage];
                    $shortanswer = "{$arows['title']}: [" . strip_tags((string) $arows->questionl10ns[$this->language]->question) . "][$label1]";
                    $shortquestion = $rows['title'] . ":$shortanswer " . strip_tags((string) $arows->questionl10ns[$this->language]->question);
                    $cquestions[] = array($shortquestion, $rows['qid'], $rows['type'], $rows['sid'] . $X . $rows['gid'] . $X . $rows['qid'] . $arows['title'] . "#0");

                    $shortanswer = "{$arows['title']}: [" . strip_tags((string) $arows->questionl10ns[$this->language]->question) . "][$label2]";
                    $shortquestion = $rows['title'] . ":$shortanswer " . strip_tags((string) $arows->questionl10ns[$this->language]->question);
                    $cquestions[] = array($shortquestion, $rows['qid'], $rows['type'], $rows['sid'] . $X . $rows['gid'] . $X . $rows['qid'] . $arows['title'] . "#1");

                    // first label
                    $lresult = Answer::model()->with(array(
                            'answerl10ns' => array(
                                'condition' => 'answerl10ns.language = :lang',
                                'params' => array(':lang' => $this->language)
                            )))->findAllByAttributes(array('qid' => $rows['qid'], 'scale_id' => 0));
                    foreach ($lresult as $lrows) {
                        $canswers[] = array($rows['sid'] . $X . $rows['gid'] . $X . $rows['qid'] . $arows['title'] . "#0", "{$lrows['code']}", "{$lrows['code']}");
                    }

                    // second label
                    $lresult = Answer::model()->with(array(
                            'answerl10ns' => array(
                                'condition' => 'answerl10ns.language = :lang',
                                'params' => array(':lang' => $this->language)
                            )))->findAllByAttributes(array(
                                'qid' => $rows['qid'],
                                'scale_id' => 1
                            ));

                    foreach ($lresult as $lrows) {
                        $canswers[] = array($rows['sid'] . $X . $rows['gid'] . $X . $rows['qid'] . $arows['title'] . "#1", "{$lrows['code']}", "{$lrows['code']}");
                    }

                    // Only Show No-Answer if question is not mandatory
                    if ($rows['mandatory'] != 'Y' && $rows['mandatory'] != 'S') {
                        $canswers[] = array($rows['sid'] . $X . $rows['gid'] . $X . $rows['qid'] . $arows['title'] . "#0", "", gT("No answer"));
                        $canswers[] = array($rows['sid'] . $X . $rows['gid'] . $X . $rows['qid'] . $arows['title'] . "#1", "", gT("No answer"));
                    }
                } //foreach
            } elseif ($rows['type'] == Question::QT_K_MULTIPLE_NUMERICAL || $rows['type'] == Question::QT_Q_MULTIPLE_SHORT_TEXT) {
                //Multi shorttext/numerical
                $aresult = Question::model()->with('questionl10ns')->findAllByAttributes(array(
                    "parent_qid" => $rows['qid']
                ), array('order' => 'question_order desc'));

                foreach ($aresult as $arows) {
                    $shortanswer = "{$arows['title']}: [" . strip_tags((string) $arows->questionl10ns[$this->language]->question) . "]";
                    $shortquestion = $rows['title'] . ":$shortanswer " . strip_tags((string) $rows['question']);
                    $cquestions[] = array($shortquestion, $rows['qid'], $rows['type'], $rows['sid'] . $X . $rows['gid'] . $X . $rows['qid'] . $arows['title']);

                    // Only Show No-Answer if question is not mandatory
                    if ($rows['mandatory'] != 'Y' && $rows['mandatory'] != 'S') {
                        $canswers[] = array($rows['sid'] . $X . $rows['gid'] . $X . $rows['qid'] . $arows['title'], "", gT("No answer"));
                    }
                } //foreach
            } elseif ($rows['type'] == Question::QT_R_RANKING) {
                //Answer Ranking
                $aresult = Answer::model()->with(array(
                            'answerl10ns' => array(
                                'condition' => 'answerl10ns.language = :lang',
                                'params' => array(':lang' => $this->language)
                            )))->findAllByAttributes(
                                array(
                                    "qid" => $rows['qid'],
                                    "scale_id" => 0,
                                )
                            );

                $acount = count($aresult);

                $quicky = [];
                foreach ($aresult as $arow) {
                    $theanswer = $arow->answerl10ns[$this->language]->answer;
                    $quicky[] = array($arow['code'], $theanswer);
                }

                for ($i = 1; $i <= $acount; $i++) {
                    $cquestions[] = array("{$rows['title']}: [RANK $i] " . strip_tags((string) $rows['question']), $rows['qid'], $rows['type'], $rows['sid'] . $X . $rows['gid'] . $X . $rows['qid'] . $i);
                    foreach ($quicky as $qck) {
                        $canswers[] = array($rows['sid'] . $X . $rows['gid'] . $X . $rows['qid'] . $i, $qck[0], $qck[1]);
                    }
                    // Only Show No-Answer if question is not mandatory
                    if ($rows['mandatory'] != 'Y' && $rows['mandatory'] != 'S') {
                        $canswers[] = array($rows['sid'] . $X . $rows['gid'] . $X . $rows['qid'] . $i, " ", gT("No answer"));
                    }
                }
                unset($quicky);
                // End if type R
            } elseif ($rows['type'] == Question::QT_M_MULTIPLE_CHOICE || $rows['type'] == Question::QT_P_MULTIPLE_CHOICE_WITH_COMMENTS) {
                $shortanswer = " [" . gT("Group of checkboxes") . "]";
                $shortquestion = $rows['title'] . ":$shortanswer " . strip_tags((string) $rows['question']);
                $cquestions[] = array($shortquestion, $rows['qid'], $rows['type'], $rows['sid'] . $X . $rows['gid'] . $X . $rows['qid']);

                $aresult = Question::model()->with('questionl10ns')->findAllByAttributes(array(
                    "parent_qid" => $rows['qid'],
                ), array('order' => 'question_order desc'));

                foreach ($aresult as $arows) {
                    $theanswer = $arows->questionl10ns[$this->language]->question;
                    $canswers[] = array($rows['sid'] . $X . $rows['gid'] . $X . $rows['qid'], $arows['title'], $theanswer);

                    $shortanswer = "{$arows['title']}: [" . strip_tags((string) $theanswer) . "]";
                    $shortanswer .= "[" . gT("Single checkbox") . "]";
                    $shortquestion = $rows['title'] . ":$shortanswer " . strip_tags((string) $rows['question']);
                    $cquestions[] = array($shortquestion, $rows['qid'], $rows['type'], "+" . $rows['sid'] . $X . $rows['gid'] . $X . $rows['qid'] . $arows['title']);
                    $canswers[] = array("+" . $rows['sid'] . $X . $rows['gid'] . $X . $rows['qid'] . $arows['title'], 'Y', gT("checked"));
                    $canswers[] = array("+" . $rows['sid'] . $X . $rows['gid'] . $X . $rows['qid'] . $arows['title'], '', gT("not checked"));
                }
            } else {
                $cquestions[] = array($shortquestion, $rows['qid'], $rows['type'], $rows['sid'] . $X . $rows['gid'] . $X . $rows['qid']);

                switch ($rows['type']) {
                    case Question::QT_Y_YES_NO_RADIO: // Y/N/NA
                        $canswers[] = array($rows['sid'] . $X . $rows['gid'] . $X . $rows['qid'], "Y", gT("Yes"));
                        $canswers[] = array($rows['sid'] . $X . $rows['gid'] . $X . $rows['qid'], "N", gT("No"));
                        // Only Show No-Answer if question is not mandatory
                        if ($rows['mandatory'] != 'Y' && $rows['mandatory'] != 'S') {
                            $canswers[] = array($rows['sid'] . $X . $rows['gid'] . $X . $rows['qid'], " ", gT("No answer"));
                        }
                        break;
                    case Question::QT_G_GENDER: //Gender
                        $canswers[] = array($rows['sid'] . $X . $rows['gid'] . $X . $rows['qid'], "F", gT("Female"));
                        $canswers[] = array($rows['sid'] . $X . $rows['gid'] . $X . $rows['qid'], "M", gT("Male"));
                        // Only Show No-Answer if question is not mandatory
                        if ($rows['mandatory'] != 'Y' && $rows['mandatory'] != 'S') {
                            $canswers[] = array($rows['sid'] . $X . $rows['gid'] . $X . $rows['qid'], " ", gT("No answer"));
                        }
                        break;
                    case Question::QT_5_POINT_CHOICE: // 5 choice
                        for ($i = 1; $i <= 5; $i++) {
                            $canswers[] = array($rows['sid'] . $X . $rows['gid'] . $X . $rows['qid'], $i, $i);
                        }
                        // Only Show No-Answer if question is not mandatory
                        if ($rows['mandatory'] != 'Y' && $rows['mandatory'] != 'S') {
                            $canswers[] = array($rows['sid'] . $X . $rows['gid'] . $X . $rows['qid'], " ", gT("No answer"));
                        }
                        break;
                    case Question::QT_N_NUMERICAL: // Simple Numerical questions
                        // Only Show No-Answer if question is not mandatory
                        if ($rows['mandatory'] != 'Y' && $rows['mandatory'] != 'S') {
                            $canswers[] = array($rows['sid'] . $X . $rows['gid'] . $X . $rows['qid'], " ", gT("No answer"));
                        }
                        break;

                    default:
                        $aresult = Answer::model()->with(array(
                            'answerl10ns' => array(
                                'condition' => 'answerl10ns.language = :lang',
                                'params' => array(':lang' => $this->language),
                                'alias' => 'answerl10ns',
                            )))->findAllByAttributes(array(
                                'qid' => $rows['qid'],
                                'scale_id' => 0,
                            ));

                        foreach ($aresult as $arows) {
                            $theanswer = $arows->answerl10ns[$this->language]->answer;
                            $canswers[] = array($rows['sid'] . $X . $rows['gid'] . $X . $rows['qid'], $arows['code'], $theanswer);
                        }
                        if ($rows['type'] == Question::QT_D_DATE) {
                            // Only Show No-Answer if question is not mandatory
                            if ($rows['mandatory'] != 'Y' && $rows['mandatory'] != 'S') {
                                $canswers[] = array($rows['sid'] . $X . $rows['gid'] . $X . $rows['qid'], " ", gT("No answer"));
                            }
                        } elseif (
                            $rows['type'] != Question::QT_M_MULTIPLE_CHOICE &&
                            $rows['type'] != Question::QT_P_MULTIPLE_CHOICE_WITH_COMMENTS &&
                            $rows['type'] != Question::QT_I_LANGUAGE
                        ) {
                            // For dropdown questions
                            // optinnaly add the 'Other' answer
                            if (
                                ($rows['type'] == Question::QT_L_LIST ||
                                $rows['type'] == Question::QT_EXCLAMATION_LIST_DROPDOWN) &&
                                $rows['other'] == "Y"
                            ) {
                                $canswers[] = array($rows['sid'] . $X . $rows['gid'] . $X . $rows['qid'], "-oth-", gT("Other"));
                            }

                            // Only Show No-Answer if question is not mandatory
                            if ($rows['mandatory'] != 'Y' && $rows['mandatory'] != 'S') {
                                $canswers[] = array($rows['sid'] . $X . $rows['gid'] . $X . $rows['qid'], " ", gT("No answer"));
                            }
                        }
                        break;
                }//switch row type
            } //else
        } //foreach theserows
        return array($cquestions, $canswers);
    }

    /**
     * @param int $qid
     * @param int $gid
     * @param array $conditionsList
     * @param array $pquestions
     * @return string html
     * @throws CException
     */
    protected function getCopyForm(int $qid, int $gid, array $conditionsList, array $pquestions): string
    {
        App()->getClientScript()->registerScriptFile(App()->getConfig('adminscripts') . 'checkgroup.js', LSYii_ClientScript::POS_BEGIN);

        $url = $this->getcontroller()->createUrl(
            '/admin/conditions/sa/index/subaction/copyconditions/',
            array(
                'surveyid' => $this->iSurveyID,
                'gid' => $gid,
                'qid' => $qid
            )
        );

        $data = array();
        $data['url'] = $url;
        $data['conditionsList'] = $conditionsList;
        $data['pquestions'] = $pquestions;
        $data['qid'] = $qid;
        $data['gid'] = $gid;
        $data['iSurveyID'] = $this->iSurveyID;

        return $this->getController()->renderPartial(
            '/admin/conditions/includes/copyform',
            $data,
            true
        );
    }

    /**
     * Get html for add/edit condition form
     * @param array $args
     * @return string
     * @throws CException
     */
    protected function getEditConditionForm(array $args): string
    {
        /** @var array $cquestions */
        /** @var string $p_cquestions */
        /** @var array $p_canswers */
        /** @var string $subaction */
        /** @var integer $iSurveyID */
        /** @var integer $gid */
        /** @var integer $qid */
        /** @var integer $qcount */
        /** @var string $p_csrctoken */
        /** @var string $p_prevquestionsgqa */
        /** @var string $method */
        /** @var string $scenariocount */
        extract($args);
        $result = '';

        $js_getAnswers_onload = $this->getJsAnswersToSelect($cquestions, $p_cquestions, $p_canswers);

        App()->getClientScript()->registerScriptFile(App()->getConfig('adminscripts') . 'conditions.js', LSYii_ClientScript::POS_BEGIN);

        if ($subaction == "editthiscondition" && isset($p_cid)) {
            $title = gT("Edit condition");
            $submitLabel = gT("Update condition");
            $submitSubaction = "updatecondition";
            $submitcid = sanitize_int($p_cid);
        } else {
            $title = gT("Add condition");
            $submitLabel = $title;
            $submitSubaction = "insertcondition";
            $submitcid = "";
        }

        $data = array(
            'subaction'     => $subaction,
            'iSurveyID'     => $iSurveyID,
            'gid'           => $gid,
            'qid'           => $qid,
            'title'         => $title,
            'showScenario'  => $this->shouldShowScenario($subaction, $scenariocount),
            'qcountI'       => $qcount + 1,
            'cquestions'    => $cquestions,
            'p_csrctoken'   => $p_csrctoken,
            'p_prevquestionsgqa'  => $p_prevquestionsgqa,
            'tokenFieldsAndNames' => $this->tokenFieldsAndNames,
            'method'        => $method,
            'EDITConditionConst'  => $this->getEDITConditionConst($subaction),
            'EDITConditionRegexp' => $this->getEDITConditionRegexp($subaction),
            'submitLabel'   => $submitLabel,
            'submitSubaction'     => $submitSubaction,
            'submitcid'     => $submitcid,
            'editSourceTab' => $this->getEditSourceTab(),
            'editTargetTab' => $this->getEditTargetTab(),
            'addConditionToScenarioNr' => Yii::app()->request->getQuery('scenarioNr'),
            'surveyIsAnonymized' => $this->getSurveyIsAnonymized(),
        );
        $result .= $this->getController()->renderPartial('/admin/conditions/includes/form_editconditions_header', $data, true);

        $scriptResult = ""
            . "\t" . $js_getAnswers_onload . "\n";
        if (isset($p_method)) {
            $scriptResult .= "\tdocument.getElementById('method').value='" . $p_method . "';\n";
        }

        $scriptResult .= $this->getEditFormJavascript($subaction);

        if (isset($p_scenario)) {
            $scriptResult .= "\tdocument.getElementById('scenario').value='" . $p_scenario . "';\n";
        }
        $scriptResult .= "\n";
        App()->getClientScript()->registerScript('conditionsaction_onstartscripts', $scriptResult, LSYii_ClientScript::POS_END);

        return $result;
    }

    /**
     * Form used in quick-add modal
     * @param array $args
     * @return string|string[]|null
     * @throws CException
     */
    protected function getQuickAddConditionForm(array $args)
    {
        /** @var integer $iSurveyID */
        /** @var integer $gid */
        /** @var integer $qid */
        /** @var string $subaction */
        /** @var string $method */
        /** @var string $p_csrctoken */
        /** @var string $p_prevquestionsgqa */
        /** @var array $cquestions */
        extract($args);
        $data = array(
            'subaction'     => $subaction,
            'iSurveyID'     => $iSurveyID,
            'gid'           => $gid,
            'qid'           => $qid,
            'cquestions'    => $cquestions,
            'p_csrctoken'   => $p_csrctoken,
            'p_prevquestionsgqa'  => $p_prevquestionsgqa,
            'tokenFieldsAndNames' => $this->tokenFieldsAndNames,
            'method'        => $method,
        );
        $html = $this->getController()->renderPartial('/admin/conditions/includes/quickAddConditionForm', $data, true);
        return $html;
    }

    /**
     * @param array $cquestions
     * @param string $p_cquestions Question SGID
     * @param array $p_canswers E.g. array('A2')
     * @return string JS code
     */
    protected function getJsAnswersToSelect($cquestions, $p_cquestions, $p_canswers): string
    {
        $js_getAnswers_onload = "";
        foreach ($cquestions as $cqn) {
            if ($cqn[3] == $p_cquestions) {
                if (isset($p_canswers)) {
                    $canswersToSelect = "";
                    foreach ($p_canswers as $checkval) {
                        $canswersToSelect .= ";$checkval";
                    }
                    $canswersToSelect = substr($canswersToSelect, 1);
                    $js_getAnswers_onload .= "$('#canswersToSelect').val('$canswersToSelect');\n";
                }
            }
        }
        return $js_getAnswers_onload;
    }

    /**
     * @param string $subaction
     * @return string
     */
    protected function getEDITConditionConst(string $subaction): string
    {
        $request = Yii::app()->request;
        $EDITConditionConst = HTMLEscape($request->getPost('ConditionConst', ''));
        if ($subaction == "editthiscondition" && $request->getPost('EDITConditionConst', '') !== '') {
            $EDITConditionConst = HTMLEscape($request->getPost('EDITConditionConst', ''));
        }
        return $EDITConditionConst;
    }

    /**
     * @param string $subaction
     * @return string
     */
    protected function getEDITConditionRegexp(string $subaction): string
    {
        $request = Yii::app()->request;
        $EDITConditionRegexp = '';
        if ($subaction == "editthiscondition") {
            if ($request->getPost('EDITConditionRegexp') != '') {
                $EDITConditionRegexp = HTMLEscape($request->getPost('EDITConditionRegexp'));
            }
        } else {
            if ($request->getPost('ConditionRegexp') != '') {
                $EDITConditionRegexp = HTMLEscape($request->getPost('ConditionRegexp'));
            }
        }
        return $EDITConditionRegexp;
    }

    /**
     * Generates some JS used by form
     * @param string $subaction
     * @return string JS
     */
    protected function getEditFormJavascript(string $subaction): string
    {
        $request = Yii::app()->request;
        $aViewUrls = array('output' => '');
        if ($subaction == "editthiscondition") {
            // in edit mode we read previous values in order to dusplay them in the corresponding inputs
            if ($request->getPost('EDITConditionConst', '') !== '') {
                // In order to avoid issues with backslash escaping, I don't use javascript to set the value
                // Thus the value is directly set when creating the Textarea element
                $aViewUrls['output'] .= "\tdocument.getElementById('editTargetTab').value='#CONST';\n";
            } elseif ($request->getPost('EDITprevQuestionSGQA') != '') {
                $aViewUrls['output'] .= "\tdocument.getElementById('prevQuestionSGQA').value='" . HTMLEscape($request->getPost('EDITprevQuestionSGQA')) . "';\n";
                $aViewUrls['output'] .= "\tdocument.getElementById('editTargetTab').value='#PREVQUESTIONS';\n";
            } elseif ($request->getPost('EDITtokenAttr') != '') {
                $aViewUrls['output'] .= "\tdocument.getElementById('tokenAttr').value='" . HTMLEscape($request->getPost('EDITtokenAttr')) . "';\n";
                $aViewUrls['output'] .= "\tdocument.getElementById('editTargetTab').value='#TOKENATTRS';\n";
            } elseif ($request->getPost('EDITConditionRegexp') != '') {
                // In order to avoid issues with backslash escaping, I don't use javascript to set the value
                // Thus the value is directly set when creating the Textarea element
                $aViewUrls['output'] .= "\tdocument.getElementById('editTargetTab').value='#REGEXP';\n";
            } elseif (is_array($request->getPost('EDITcanswers'))) {
                // was a predefined answers post
                $aViewUrls['output'] .= "\tdocument.getElementById('editTargetTab').value='#CANSWERSTAB';\n";
                $EDITcanswers = $request->getPost('EDITcanswers');
                $aViewUrls['output'] .= "\t$('#canswersToSelect').val('" . $EDITcanswers[0] . "');\n";
            }

            if ($request->getPost('csrctoken') != '') {
                $aViewUrls['output'] .= "\tdocument.getElementById('csrctoken').value='" . HTMLEscape($request->getPost('csrctoken')) . "';\n";
            } elseif ($request->getPost('cquestions') != '') {
                $aViewUrls['output'] .= "\tdocument.getElementById('cquestions').value='" . HTMLEscape($request->getPost('cquestions')) . "';\n";
            }
        } else {
            // in other modes, for the moment we do the same as for edit mode
            if ($request->getPost('ConditionConst', '') !== '') {
                // In order to avoid issues with backslash escaping, I don't use javascript to set the value
                // Thus the value is directly set when creating the Textarea element
                $aViewUrls['output'] .= "\tdocument.getElementById('editTargetTab').value='#CONST';\n";
            } elseif ($request->getPost('prevQuestionSGQA') != '') {
                $aViewUrls['output'] .= "\tdocument.getElementById('prevQuestionSGQA').value='" . HTMLEscape($request->getPost('prevQuestionSGQA')) . "';\n";
                $aViewUrls['output'] .= "\tdocument.getElementById('editTargetTab').value='#PREVQUESTIONS';\n";
            } elseif ($request->getPost('tokenAttr') != '') {
                $aViewUrls['output'] .= "\tdocument.getElementById('tokenAttr').value='" . HTMLEscape($request->getPost('tokenAttr')) . "';\n";
                $aViewUrls['output'] .= "\tdocument.getElementById('editTargetTab').value='#TOKENATTRS';\n";
            } elseif ($request->getPost('ConditionRegexp') != '') {
                // In order to avoid issues with backslash escaping, I don't use javascript to set the value
                // Thus the value is directly set when creating the Textarea element
                $aViewUrls['output'] .= "\tdocument.getElementById('editTargetTab').value='#REGEXP';\n";
            } else {
                // was a predefined answers post
                if ($request->getPost('cquestions') != '') {
                    $aViewUrls['output'] .= "\tdocument.getElementById('cquestions').value='" . HTMLEscape($request->getPost('cquestions')) . "';\n";
                }
                $aViewUrls['output'] .= "\tdocument.getElementById('editTargetTab').value='#CANSWERSTAB';\n";
            }

            if ($request->getPost('csrctoken') != '') {
                $aViewUrls['output'] .= "\tdocument.getElementById('csrctoken').value='" . HTMLEscape($request->getPost('csrctoken')) . "';\n";
            } else {
                if ($request->getPost('cquestions') != '') {
                    $aViewUrls['output'] .= "\tdocument.getElementById('cquestions').value='" . javascriptEscape($request->getPost('cquestions')) . "';\n";
                }
            }
        }
        return $aViewUrls['output'];
    }

    /**
     * @return string Either '#SRCTOKENATTRS' or '#SRCPREVQUEST'; defaults to '#SRCPREVQUEST' if nothing is posted
     */
    protected function getEditSourceTab()
    {
        $request = Yii::app()->request;
        if ($request->getPost('csrctoken') != '') {
            return '#SRCTOKENATTRS';
        } elseif ($request->getPost('cquestions') != '') {
            return '#SRCPREVQUEST';
        } else {
            return '#SRCPREVQUEST';
        }
    }

    /**
     * @return string Predfined, constant, questions, token field or regexp; defaults to predefined
     */
    protected function getEditTargetTab()
    {
        $request = Yii::app()->request;
        if ($request->getPost('EDITConditionConst', '') !== '') {
            return '#CONST';
        } elseif ($request->getPost('EDITprevQuestionSGQA') != '') {
            return '#PREVQUESTIONS';
        } elseif ($request->getPost('EDITtokenAttr') != '') {
            return '#TOKENATTRS';
        } elseif ($request->getPost('EDITConditionRegexp') != '') {
            return '#REGEXP';
        } elseif (is_array($request->getPost('EDITcanswers'))) {
            return '#CANSWERSTAB';
        } else {
            return '#PREVQUESTIONS';
        }
    }

    /**
     * The navigator that lets user quickly move to another question within the survey.
     * @param array $theserows
     * @param array $postrows
     * @param array $args
     * @return string html
     * @throws CException
     */
    protected function getQuestionNavOptions(array $theserows, array $postrows, array $args): string
    {
        /** @var integer $gid */
        /** @var integer $qid */
        /** @var string $questiontitle */
        /** @var string $sCurrentFullQuestionText */
        extract($args);

        $theserows2 = array();
        foreach ($theserows as $row) {
            $question = strip_tags((string) $row['question']);
            $questionselecter = viewHelper::flatEllipsizeText($question, true, '40');
            $theserows2[] = array(
                'value' => $this->createNavigatorUrl($row['gid'], $row['qid']),
                'text' => strip_tags((string) $row['title']) . ':' . $questionselecter
            );
        }

        $postrows2 = array();
        foreach ($postrows as $row) {
            $question = strip_tags((string) $row['question']);
            $questionselecter = viewHelper::flatEllipsizeText($question, true, '40');
            $postrows2[] = array(
                'value' => $this->createNavigatorUrl($row['gid'], $row['qid']),
                'text' => strip_tags((string) $row['title']) . ':' . $questionselecter
            );
        }

        $data = array(
            'theserows' => $theserows2,
            'postrows' => $postrows2,
            'currentValue' => $this->createNavigatorUrl($gid, $qid),
            'currentText' => $questiontitle . ':' . viewHelper::flatEllipsizeText(strip_tags((string) $sCurrentFullQuestionText), true, '40')
        );

        return $this->getController()->renderPartial('/admin/conditions/includes/navigator', $data, true);
    }

    /**
     * @param int $gid Group id
     * @param int $qid Questino id
     * @return string url
     */
    protected function createNavigatorUrl(int $gid, int $qid): string
    {
        return $this->getController()->createUrl(
            '/admin/conditions/sa/index/subaction/editconditionsform/',
            array(
                'surveyid' => $this->iSurveyID,
                'gid' => $gid,
                'qid' => $qid
            )
        );
    }

    /**
     * Javascript to match question with answer
     * @param array $canswers
     * @param array $cquestions
     * @param boolean $surveyIsAnonymized
     * @return string js
     */
    protected function getJavascriptForMatching(array $canswers, array $cquestions, bool $surveyIsAnonymized): string
    {
        $javascriptpre = ""
            . "\tvar Fieldnames = new Array();\n"
            . "\tvar Codes = new Array();\n"
            . "\tvar Answers = new Array();\n"
            . "\tvar QFieldnames = new Array();\n"
            . "\tvar Qcqids = new Array();\n"
            . "\tvar Qtypes = new Array();\n";

        $jn = 0;
        foreach ($canswers as $can) {
            $an = ls_json_encode(flattenText($can[2]));
            $javascriptpre .= "Fieldnames[{$jn}]='{$can[0]}';\n"
                . "Codes[{$jn}]='{$can[1]}';\n"
                . "Answers[{$jn}]={$an};\n";
            $jn++;
        }

        $jn = 0;
        foreach ($cquestions as $cqn) {
            $javascriptpre .= "QFieldnames[$jn]='$cqn[3]';\n"
                . "Qcqids[$jn]='$cqn[1]';\n"
                . "Qtypes[$jn]='$cqn[2]';\n";
            $jn++;
        }

        //  record a JS variable to let jQuery know if survey is Anonymous
        if ($surveyIsAnonymized) {
            $javascriptpre .= "isAnonymousSurvey = true;";
        } else {
            $javascriptpre .= "isAnonymousSurvey = false;";
        }

        $javascriptpre .= "\n";

        return $javascriptpre;
    }

    /**
     * @param string[] $extractedTokenAttr
     * @return string
     */
    protected function getAttributeName($extractedTokenAttr): string
    {
        if (isset($this->tokenFieldsAndNames[strtolower($extractedTokenAttr[1])])) {
            $thisAttrName = HTMLEscape($this->tokenFieldsAndNames[strtolower($extractedTokenAttr[1])]['description']);
        } else {
            $thisAttrName = HTMLEscape($extractedTokenAttr[1]);
        }

        if ($this->tokenTableExists) {
            $thisAttrName .= " [" . gT("From survey participants table") . "]";
        } else {
            $thisAttrName .= " [" . gT("Non-existing survey participants table") . "]";
        }

        return $thisAttrName;
    }

    /**
     * @param array $rows
     * @param string $leftOperandType
     * @param string $rightOperandType
     * @return string html
     */
    protected function getHiddenFields(array $rows, string $leftOperandType, string $rightOperandType): string
    {
        $html = '';

        // now sets e corresponding hidden input field
        // depending on the leftOperandType
        if ($leftOperandType == 'tokenattr') {
            $html .= CHtml::hiddenField('csrctoken', HTMLEscape($rows['cfieldname']), array(
                'id' => 'csrctoken' . $rows['cid']
            ));
        } else {
            $html .= CHtml::hiddenField(
                'cquestions',
                HTMLEscape($rows['cfieldname']),
                array(
                    'id' => 'cquestions' . $rows['cid']
                )
            );
        }

        // now set the corresponding hidden input field
        // depending on the rightOperandType
        // This is used when editing a condition
        if ($rightOperandType == 'predefinedAnsw') {
            $html .= CHtml::hiddenField('EDITcanswers[]', HTMLEscape($rows['value']), array(
                'id' => 'editModeTargetVal' . $rows['cid']
            ));
        } elseif ($rightOperandType == 'prevQsgqa') {
            $html .= CHtml::hiddenField(
                'EDITprevQuestionSGQA',
                HTMLEscape($rows['value']),
                array(
                    'id' => 'editModeTargetVal' . $rows['cid']
                )
            );
        } elseif ($rightOperandType == 'tokenAttr') {
            $html .= CHtml::hiddenField('EDITtokenAttr', HTMLEscape($rows['value']), array(
                'id' => 'editModeTargetVal' . $rows['cid']
            ));
        } elseif ($rightOperandType == 'regexp') {
            $html .= CHtml::hiddenField(
                'EDITConditionRegexp',
                HTMLEscape($rows['value']),
                array(
                    'id' => 'editModeTargetVal' . $rows['cid']
                )
            );
        } else {
            $html .= CHtml::hiddenField(
                'EDITConditionConst',
                HTMLEscape($rows['value']),
                array(
                    'id' => 'editModeTargetVal' . $rows['cid']
                )
            );
        }

        return $html;
    }

    /**
     * @param int $qid
     * @return CActiveRecord[] Conditions
     */
    protected function getAllScenarios(int $qid)
    {
        $criteria = new CDbCriteria();
        $criteria->select = 'scenario'; // only select the 'scenario' column
        $criteria->condition = 'qid=:qid';
        $criteria->params = array(':qid' => $qid);
        $criteria->order = 'scenario';
        $criteria->group = 'scenario';

        return Condition::model()->findAll($criteria);
    }

    /**
     * After add/delete/etc, redirect to conditions start page
     * @param int $qid
     * @param int $gid
     * @return void
     */
    protected function redirectToConditionStart(int $qid, int $gid)
    {
        $url = $this->getcontroller()->createUrl(
            '/admin/conditions/sa/index/subaction/editconditionsform/',
            array(
                'surveyid' => $this->iSurveyID,
                'gid' => $gid,
                'qid' => $qid
            )
        );
        $this->getController()->redirect($url);
    }

    /**
     * Decides if "Default scenario" should be shown or not
     * @param string $subaction
     * @param int $scenariocount
     * @return boolean
     */
    protected function shouldShowScenario(string $subaction, int $scenariocount): bool
    {
        return $subaction != "editthiscondition" && ($scenariocount == 1 || $scenariocount == 0);
    }

    /**
     * Used to calculate size of select box
     * @todo Not used
     * @param array $cquestions
     * @return int
     */
    protected function getQCount(array $cquestions): int
    {
        if (count($cquestions) > 0 && count($cquestions) <= 10) {
            $qcount = count($cquestions);
        } else {
            $qcount = 9;
        }

        return $qcount;
    }
}
