<?php

/*
 * LimeSurvey
 * Copyright (C) 2007-2026 The LimeSurvey Project Team
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
     * True if there exists a survey participant list for this survey
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
            "==" => gT("Equals"),
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
        $returnUrl = Yii::app()->createUrl('questionAdministration/view/surveyid/' . $iSurveyID . '/gid/' . $gid . '/qid/' . $qid);

        $aData['returnUrl'] = $returnUrl;
        $aData['conditionsoutput_action_error'] = '';

        $results = $this->surveyCondition->index($args, $aData, $subaction, $method, $gid, $qid, $imageurl, $extraGetParams, $this);
        $results['aData']['returnUrl'] = $returnUrl;
        $this->renderWrappedTemplate('conditions', $results['aViewUrls'], $results['aData']);

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
        $this->surveyCondition->insertCondition($args, $editSourceTab, $editTargetTab, Yii::app(), Yii::app()->request->getPost('ConditionConst', ''), Yii::app()->request->getPost('prevQuestionSGQA', ''), Yii::app()->request->getPost('tokenAttr', ''), Yii::app()->request->getPost('ConditionRegexp', ''));

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
        $this->surveyCondition->updateCondition($args, $request->getPost('editTargetTab'), Yii::app(), Yii::app()->request->getPost('ConditionConst', ''), Yii::app()->request->getPost('prevQuestionSGQA', ''), Yii::app()->request->getPost('tokenAttr', ''), Yii::app()->request->getPost('ConditionRegexp', ''), $request->getPost('editSourceTab'));
        $this->redirectToConditionStart($args['qid'], $args['gid']);
    }

    /**
     * @param int $qid the id of the question
     * @return void
     * @throws CException
     */
    protected function renumberScenarios(int $qid)
    {
        $this->surveyCondition->renumberScenarios($qid, Yii::app());
    }

    /**
     * @param array $args
     * @return void
     * @throws CException
     */
    protected function copyConditions(array $args)
    {
        $this->surveyCondition->copyConditions(returnGlobal('copyconditionsfrom'), returnGlobal('copyconditionsto'), Yii::app());
    }

    /**
     * Gets the condition text based on a qid
     * @param int $qid the id of the question
     * @return void
     */
    protected function getConditionText(int $qid)
    {
        $this->surveyCondition->getConditionText(\Question::model()->findByPk($qid));
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
                $this->surveyCondition->updateScenario($p_newscenarionum, $qid, $p_scenario, Yii::app());
                break;
            // Delete all conditions for this question
            case "deleteallconditions":
                $this->surveyCondition->deleteAllConditions($qid, Yii::app());
                $this->redirectToConditionStart($qid, $gid);
                break;
            // Renumber scenarios
            case "renumberscenarios":
                $this->renumberScenarios($qid);
                $this->redirectToConditionStart($qid, $gid);
                break;
            // Copy conditions if this is copy
            case "copyconditions":
                $this->copyConditions($args);
                break;
            case "getconditiontext":
                $this->getConditionText($qid);
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
     * @return boolean True if anonymized == 'Y' for this survey
     */
    protected function getSurveyIsAnonymized()
    {
        return $this->surveyCondition->getSurveyIsAnonymized($this->iSurveyID);
    }


    /**
     * @param int $qid
     * @param int $gid
     * @param array $conditionsList
     * @param array $pquestions
     * @return string html
     * @throws CException
     */
    public function getCopyForm(int $qid, int $gid, array $conditionsList, array $pquestions): string
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
    public function getEditConditionForm(array $args): string
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
     * Maps keywords with paths so we can ensure that the service is agnostic to where the paths are which gives us flexibility
     * @param string $what the keyname of the view to be rendered
     * @param array $data data array to be passed to the renderPartial method
     * @param bool $return return to be passed to the renderPartial method
     * @param bool $processOutput processOutput to be passed to the renderPartial method
     * @return string
     */
    public function renderPartialView(string $what, array $data = null, bool $return = false, bool $processOutput = false)
    {
        switch ($what) {
            case 'navigator':
                $view = '/admin/conditions/includes/navigator';
                break;
            case 'quickAddConditionForm':
                $view = '/admin/conditions/includes/quickAddConditionForm';
                break;
            case 'conditions_edit':
                $view = '/admin/conditions/includes/conditions_edit';
                break;
            case 'condition':
                $view = '/admin/conditions/includes/condition';
                break;
            case 'conditions_scenario':
                $view = '/admin/conditions/includes/conditions_scenario';
                break;
            case 'customized_conditions':
                $view = '/admin/conditions/customized_conditions';
                break;
            case 'no_condition':
                $view = '/admin/conditions/no_condition';
                break;
            case 'leftSideButtons':
                $view = '/admin/conditions/partial/topbarBtns/leftSideButtons';
                break;
            case 'rightSideButtons':
                $view = '/admin/conditions/partial/topbarBtns/rightSideButtons';
                break;
            default:
                $view = '';
        }
        return $this->getController()->renderPartial($view, $data, $return, $processOutput);
    }

    /**
     * @param int $gid Group id
     * @param int $qid Questino id
     * @return string url
     */
    public function createNavigatorUrl(int $gid, int $qid): string
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
     * Maps $pathKey with $path
     * @param string $pathKey the keyword of the path
     * @param array $params an array to be passed to createUrl
     * @return string
     */
    public function myCreateUrl(string $pathKey, array $params = [])
    {
        switch ($pathKey) {
            case 'quickAddCondition':
                $path = '/admin/conditions/sa/quickAddCondition';
                break;
            case 'index':
                $path = '/admin/conditions/sa/index/';
                break;
            default:
                $path = '';
        }
        return $this->getController()->createUrl(
            $path,
            $params
        );
    }

    /**
     * Adds a script to the view so the service can be agnostic to paths and scripts
     * @param string $key the config
     * @param string $which the filename
     * @param integer $how the way
     * @return void
     */
    public function addScript($key, $which, $how)
    {
        App()->getClientScript()->registerScriptFile(App()->getConfig($key) . $which . '.js', $how);
    }

    /**
     * Javascript to match question with answer
     * @param array $canswers
     * @param array $cquestions
     * @param boolean $surveyIsAnonymized
     * @return string js
     */
    public function getJavascriptForMatching(array $canswers, array $cquestions, bool $surveyIsAnonymized): string
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
}
