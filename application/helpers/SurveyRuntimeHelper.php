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

class SurveyRuntimeHelper
{
    /**
     * In the 2.x version of LimeSurvey and priors, the main run method  was using a variable called redata fed via get_defined_vars. It was making hard to move piece of code to subfuntions.
     * Those private variables are just a step to make easier refactorisation of this file, to have a global overview about what is set in this helper, and to move easely piece of code to new methods:
     * The methods get/set the private variables, and defore calling get_defined_vars, variables are created from those private variables.
     * It's just a first step. get_defined_vars should be removed, and most of the private variables here should be moved to the correct object:
     * i.e: all the private variable concerning the survey should be moved to the survey model and replaced by a $oSurvey
     */

    // parameters
    private $param;

    // Preview datas
    private $previewquestion     = false;
    private $previewgrp          = false;
    private $preview             = false;

    // Template datas
    private $oTemplate; // Template configuration object (set in model TemplateConfiguration)
    private $sTemplateViewPath; // Path of the layout files in template

    // LEM Datas
    private $LEMsessid;
    private $LEMdebugLevel          = 0; // customizable debugging for Lime Expression Manager ; LEM_DEBUG_TIMING;   (LEM_DEBUG_TIMING + LEM_DEBUG_VALIDATION_SUMMARY + LEM_DEBUG_VALIDATION_DETAIL);
    private $LEMskipReprocessing    = false; // true if used GetLastMoveResult to avoid generation of unneeded extra JavaScript

    // Survey settings:
    // TODO: To respect object oriented design, all those "states" should be move to SurveyDynamic model, or its related models via relations.
    // The only private variable here should be $oSurvey.
    private $aSurveyInfo; // Array returned by common_helper::getSurveyInfo(); (survey row + language settings );
    private $iSurveyid              = null; // The survey id
    private $bShowEmptyGroup        = false; // True only when $_SESSION[$this->LEMsessid]['step'] == 0 ; Just a variable for a logic step ==> should not be a Class variable (for now, only here for the redata== get_defined_vars mess)
    private $sSurveyMode; // {Group By Group,  All in one, Question by question}
    private $aSurveyOptions; // Few options comming from thissurvey, App->getConfig, LEM. Could be replaced by $oSurvey + relations ; the one coming from LEM and getConfig should be public variable on the surveyModel, set via public methods (active, allowsave, anonymized, assessments, datestamp, deletenonvalues, ipaddr, radix, refurl, savetimings, surveyls_dateformat, startlanguage, target, tempdir,timeadjust)
    private $sLangCode; // Current language code

    // moves
    private $aMoveResult            = false; // Contains the result of LimeExpressionManager::JumpTo() OR LimeExpressionManager::NavigateBackwards() OR NavigateForwards::LimeExpressionManager(). TODO: create a function LimeExpressionManager::MoveTo that call the right method
    private $sMove = null; // The move requested by user. Set by frontend_helper::getMove() from the POST request.
    private $bInvalidLastPage       = false; // Just a variable used to check if user submitted a survey while it's not finished. Just a variable for a logic step ==> should not be a Class variable (for now, only here for the redata== get_defined_vars mess)
    private $aStepInfo;

    // Popups: HTML of popus. If they are null, no popup. If they contains a string, a popup will be shown to participant.
    // They could probably be merged.
    private $backpopup              = false; // "Please use the survey  navigation buttons or index.  It appears you attempted to use the browser back button to re-submit a page."
    private $popup                  = false; // savedcontrol, mandatory_popup
    private $notvalidated; // question validation error

    // response
    // TODO:  To respect object oriented design, all those "states" should be move to Response model, or its related models via relations.
    private $notanswered; // A global variable...Should be $oResponse->notanswered
    private $filenotvalidated = false; // Same, but specific to file question type. (seems to be problematic by the past)

    // strings
    private $completed; // The string containing the completed message

    // Boolean helpers
    private $okToShowErrors; // true if we must show error in page : it's a submited ($_POST) page and show the same page again for some reason

    // Group
    private $gid;
    private $groupname;
    private $groupdescription;

    /**
     * Main function
     *
     * @param mixed $surveyid
     * @param mixed $args
     */
    public function run($surveyid, $args)
    {
        // Survey settings
        $this->setSurveySettings($surveyid, $args);

        // Start rendering
        $this->makeLanguageChanger(); //  language changer can be used on any entry screen, so it must be set first
        extract($args);

        ///////////////////////////////////////////////////////////
        // 1: We check if token and/or captcha form shouls be shown
        if (!isset($_SESSION[$this->LEMsessid]['step'])) {
            $this->showTokenOrCaptchaFormsIfNeeded();
        }

        if (!$this->previewgrp && !$this->previewquestion) {
            $this->initMove(); // main methods to init session, LEM, moves, errors, etc
            $this->checkForDataSecurityAccepted(); // must be called after initMove to allow LEM to be initialized
            $this->checkQuotas(); // check quotas (then the process will stop here)
            $this->displayFirstPageIfNeeded();
            $this->saveAllIfNeeded();
            $this->saveSubmitIfNeeded();
            // TODO: move somewhere else
            $this->setNotAnsweredAndNotValidated();
        } else {
            $this->setPreview();
        }
        $this->moveSubmitIfNeeded();
        $this->setGroup();
        $this->fixMaxStep();

        //******************************************************************************************************
        //PRESENT SURVEY
        //******************************************************************************************************

        $this->okToShowErrors = $okToShowErrors = (!($this->previewgrp || $this->previewquestion) && ($this->bInvalidLastPage || $_SESSION[$this->LEMsessid]['prevstep'] == $_SESSION[$this->LEMsessid]['step']));

        Yii::app()->loadHelper('qanda');
        setNoAnswerMode($this->aSurveyInfo);

        //Iterate through the questions about to be displayed:
        $inputnames = array();
        $vpopup     = $fpopup = false;
        $upload_file = null;

        $qanda = array();
        foreach ($_SESSION[$this->LEMsessid]['grouplist'] as $gl) {
            $gid     = $gl['gid'];
            $qnumber = 0;

            if ($this->sSurveyMode != 'survey') {
                $onlyThisGID = $this->aStepInfo['gid'];
                if ($onlyThisGID != $gid) {
                    continue;
                }
            }

            $upload_file = false;
            if (isset($_SESSION[$this->LEMsessid]['fieldarray'])) {
                foreach ($_SESSION[$this->LEMsessid]['fieldarray'] as $key => $ia) {
                    ++$qnumber;
                    $ia[9] = $qnumber; // incremental question count;

                    // Make $qanda only for needed question $ia[10] is the randomGroup and $ia[5] the real group
                    if ((isset($ia[10]) && $ia[10] == $gid) || (!isset($ia[10]) && $ia[5] == $gid)) {

                        // In question by question mode, we only procceed current question
                        if ($this->sSurveyMode == 'question' && $ia[0] != $this->aStepInfo['qid']) {
                            continue;
                        }

                        // In group by group mode, we only procceed current group
                        if ($this->sSurveyMode == 'group' && $ia[5] != $this->aStepInfo['gid']) {
                            if (isset($_SESSION[$this->LEMsessid]['fieldmap-'.$this->iSurveyid.'-randMaster'])) {
                                // This is a randomized survey, don't continue.
                            } else {
                                continue;
                            }
                        }

                        $qidattributes = QuestionAttribute::model()->getQuestionAttributes($ia[0]);

                        if ($ia[4] != '*' && ($qidattributes === false || !isset($qidattributes['hidden']) || $qidattributes['hidden'] == 1)) {
                            continue;
                        }

                        //Get the answers/inputnames
                        // TMSW - can content of retrieveAnswers() be provided by LEM?  Review scope of what it provides.
                        // TODO - retrieveAnswers is slow - queries database separately for each question. May be fixed in _CI or _YII ports, so ignore for now
                        list($plus_qanda, $plus_inputnames) = retrieveAnswers($ia);

                        if ($plus_qanda) {
                            $plus_qanda[] = $ia[4];
                            $plus_qanda[] = $ia[6]; // adds madatory identifyer for adding mandatory class to question wrapping div

                            // Add a finalgroup in qa array , needed for random attribute : TODO: find a way to have it in new quanda_helper in 2.1
                            if (isset($ia[10])) {
                                                        $plus_qanda['finalgroup'] = $ia[10];
                            } else {
                                                        $plus_qanda['finalgroup'] = $ia[5];
                            }

                            $qanda[] = $plus_qanda;
                        }
                        if ($plus_inputnames) {
                            $inputnames = addtoarray_single($inputnames, $plus_inputnames);
                        }

                        //Display the "mandatory" popup if necessary
                        // TMSW - get question-level error messages - don't call **_popup() directly
                        if ($okToShowErrors && $this->aStepInfo['mandViolation']) {
                            list($mandatorypopup, $this->popup) = mandatory_popup($ia, $this->notanswered);
                        }

                        //Display the "validation" popup if necessary
                        if ($okToShowErrors && !$this->aStepInfo['valid']) {
                            list($validationpopup, $vpopup) = validation_popup($ia, $this->notvalidated);
                        }

                        // Display the "file validation" popup if necessary
                        if ($okToShowErrors && ($this->filenotvalidated !== false)) {
                            list($filevalidationpopup, $fpopup) = file_validation_popup($ia, $this->filenotvalidated);
                        }
                    }

                    if ($ia[4] == "|") {
                                        $upload_file = true;
                    }
                } //end iteration
            }
        }

        if ($this->sSurveyMode != 'survey' && isset($this->aSurveyInfo['showprogress']) && $this->aSurveyInfo['showprogress'] == 'Y') {

            if ($this->bShowEmptyGroup) {
                $this->aSurveyInfo['progress']['currentstep'] = $_SESSION[$this->LEMsessid]['totalsteps'] + 1;
                $this->aSurveyInfo['progress']['total']       = $_SESSION[$this->LEMsessid]['totalsteps'];
            } else {
                $this->aSurveyInfo['progress']['currentstep'] = $_SESSION[$this->LEMsessid]['step'];
                $this->aSurveyInfo['progress']['total']       = isset($_SESSION[$this->LEMsessid]['totalsteps']) ? $_SESSION[$this->LEMsessid]['totalsteps'] : 1;
            }
        }

        $this->aSurveyInfo['yiiflashmessages'] = Yii::app()->user->getFlashes();

        /**
         * create question index only in SurveyRuntime, not needed elsewhere, add it to GlobalVar : must be always set even if empty
         *
         */
        $this->aSurveyInfo['aQuestionIndex']['bShow'] = false;

        if ($this->aSurveyInfo['questionindex']) {
            if (!$this->previewquestion && !$this->previewgrp) {
                $this->aSurveyInfo['aQuestionIndex']['items'] = LimeSurvey\Helpers\questionIndexHelper::getInstance()->getIndexItems();

                if ($this->aSurveyInfo['questionindex'] > 1) {
                    $this->aSurveyInfo['aQuestionIndex']['type'] = 'full';
                } else {
                    $this->aSurveyInfo['aQuestionIndex']['type'] = 'incremental';
                }
            }

            if (isset($this->aSurveyInfo['aQuestionIndex']['items']) && count($this->aSurveyInfo['aQuestionIndex']['items']) > 0) {
                $this->aSurveyInfo['aQuestionIndex']['bShow'] = true;
            }
        }

        sendCacheHeaders();

        Yii::app()->loadHelper('surveytranslator');

        $this->aSurveyInfo['upload_file'] = $upload_file;
        $hiddenfieldnames = $this->aSurveyInfo['hiddenfieldnames'] = implode("|", $inputnames);

        App()->clientScript->registerScriptFile(App()->getConfig("generalscripts").'nojs.js', CClientScript::POS_BEGIN);

        // Show question code/number
        $this->aSurveyInfo['aShow'] = $this->getShowNumAndCode();

        $aPopup = array(); // We can move this part where we want now

        if ($this->backpopup != false) {
            $aPopup[] = $this->backpopup; // If user click reload: no need other popup
        } else {

            if ($this->popup != false) {
                $aPopup[] = $this->popup;
            }

            if ($vpopup != false) {
                $aPopup[] = $vpopup;
            }

            if ($fpopup != false) {
                $aPopup[] = $fpopup;
            }
        }

        $this->aSurveyInfo['jPopup'] = json_encode($aPopup);

        $aErrorHtmlMessage                             = $this->getErrorHtmlMessage();
        $this->aSurveyInfo['errorHtml']['show']        = !empty($aErrorHtmlMessage) && $this->oTemplate->showpopups==0;
        $this->aSurveyInfo['errorHtml']['hiddenClass'] = $this->oTemplate->showpopups==1 ? "ls-js-hidden " : "";
        $this->aSurveyInfo['errorHtml']['messages']    = $aErrorHtmlMessage;

        $_gseq = -1;
        foreach ($_SESSION[$this->LEMsessid]['grouplist'] as $gl) {

            ++$_gseq;

            $gid              = $gl['gid'];
            $aGroup           = array();
            if ($this->sSurveyMode != 'survey') {
                $onlyThisGID = $this->aStepInfo['gid'];
                if ($onlyThisGID != $gid) {
                    continue;
                }
            }

            Yii::app()->setConfig('gid', $gid); // To be used in templaterplace in whole group. Attention : it's the actual GID (not the GID of the question)

            $aGroup['class'] = "";
            $gnoshow         = LimeExpressionManager::GroupIsIrrelevantOrHidden($_gseq);
            $redata          = compact(array_keys(get_defined_vars()));

            if ($gnoshow && !$this->previewgrp) {
                $aGroup['class'] = ' ls-hidden';
            }

            $aGroup['name']        = $gl['group_name'];
            $aGroup['gseq']        = $_gseq;
            $showgroupinfo_global_ = getGlobalSetting('showgroupinfo');
            $aSurveyinfo           = getSurveyInfo($this->iSurveyid, App()->getLanguage());

            // Look up if there is a global Setting to hide/show the Questiongroup => In that case Globals will override Local Settings
            if (($aSurveyinfo['showgroupinfo'] == $showgroupinfo_global_) || ($showgroupinfo_global_ == 'choose')) {
                $showgroupinfo_ = $aSurveyinfo['showgroupinfo'];
            } else {
                $showgroupinfo_ = $showgroupinfo_global_;
            }

            $showgroupdesc_ = $showgroupinfo_ == 'B' /* both */ || $showgroupinfo_ == 'D'; /* (group-) description */

            $aGroup['showgroupinfo'] = $showgroupinfo_;
            $aGroup['showdescription']  = (!$this->previewquestion && trim($gl['description']) != "" && $showgroupdesc_);
            $aGroup['description']      = $gl['description'];

            // one entry per QID
            foreach ($qanda as $qa) {

                if ($gid == $qa[6] || ( isset($_SESSION[$this->LEMsessid]['fieldmap-'.$this->iSurveyid.'-randMaster']) && $this->sSurveyMode != 'survey' ) ) {
                    $qid             = $qa[4];
                    $qinfo           = LimeExpressionManager::GetQuestionStatus($qid);
                    $lemQuestionInfo = LimeExpressionManager::GetQuestionStatus($qid);
                    $lastgrouparray  = explode("X", $qa[7]);
                    $lastgroup       = $lastgrouparray[0]."X".$lastgrouparray[1]; // id of the last group, derived from question id
                    $lastanswer      = $qa[7];

                    if ($qinfo['hidden'] && $qinfo['info']['type'] != '*') {
                        continue; // skip this one
                    }

                    $question = $qa[0];

                    //===================================================================
                    // The following four variables offer the templating system the
                    // capacity to fully control the HTML output for questions making the
                    // above echo redundant if desired.
                    $question['sgq']  = $qa[7];
                    $question['aid']  = !empty($qinfo['info']['aid']) ? $qinfo['info']['aid'] : 0;
                    $question['sqid'] = !empty($qinfo['info']['sqid']) ? $qinfo['info']['sqid'] : 0;
                    //===================================================================


                    $aStandardsReplacementFields = array();
                    $this->aSurveyInfo['surveyls_url']               = $this->processString($this->aSurveyInfo['surveyls_url']);

                    if ( strpos( $qa[0]['text'], '{' ) || strpos( $lemQuestionInfo['info']['help'], '{' ) )   {

                        // process string anyway so that it can be pretty-printed
                        $aStandardsReplacementFields = getStandardsReplacementFields($this->aSurveyInfo);
                        $aStandardsReplacementFields['QID'] = $qid;
                        $aStandardsReplacementFields['SGQ'] = $qa[7];
                        $aStandardsReplacementFields['GROUPNAME'] = $this->groupname;
                        $aStandardsReplacementFields['QUESTION_CODE'] = $qa[0]['code'];
                        $aStandardsReplacementFields['GID'] = $qinfo['info']['gid'];
                    }

                    // easier to understand for survey maker
                    $aGroup['aQuestions'][$qid]['qid']                  = $qa[4];
                    $aGroup['aQuestions'][$qid]['gid']                  = $qinfo['info']['gid'];
                    $aGroup['aQuestions'][$qid]['code']                 = $qa[5];
                    $aGroup['aQuestions'][$qid]['type']                 = $qinfo['info']['type'];
                    $aGroup['aQuestions'][$qid]['number']               = $qa[0]['number'];
                    $aGroup['aQuestions'][$qid]['text']                 = LimeExpressionManager::ProcessString($qa[0]['text'], $qa[4], $aStandardsReplacementFields, 3, 1, false, true, false);
                    $aGroup['aQuestions'][$qid]['SGQ']                  = $qa[7];
                    $aGroup['aQuestions'][$qid]['mandatory']            = $qa[0]['mandatory'];
                    $aGroup['aQuestions'][$qid]['class']                = $this->getCurrentQuestionClasses($qid);
                    $aGroup['aQuestions'][$qid]['input_error_class']    = $qa[0]['input_error_class'];
                    $aGroup['aQuestions'][$qid]['valid_message']        = LimeExpressionManager::ProcessString( $qa[0]['valid_message'] );
                    $aGroup['aQuestions'][$qid]['file_valid_message']   = $qa[0]['file_valid_message'];
                    $aGroup['aQuestions'][$qid]['man_message']          = $qa[0]['man_message'];
                    $aGroup['aQuestions'][$qid]['answer']               = LimeExpressionManager::ProcessString($qa[1], $qa[4], null, 3, 1, false, true, false);
                    $aGroup['aQuestions'][$qid]['help']['show']         = (flattenText($lemQuestionInfo['info']['help'], true, true) != '');
                    $aGroup['aQuestions'][$qid]['help']['text']         = LimeExpressionManager::ProcessString($lemQuestionInfo['info']['help'], $qa[4], null, 3, 1, false, true, false);
                    $aGroup['aQuestions'][$qid] = $this->doBeforeQuestionRenderEvent($aGroup['aQuestions'][$qid]);
                }
                $aGroup['show_last_group']   = $aGroup['show_last_answer']  = false;
                $aGroup['lastgroup']         = $aGroup['lastanswer']        = '';

                if (!empty($qanda)) {

                    if ($this->sSurveyMode == 'group') {
                        $aGroup['show_last_group']   = true;
                        $aGroup['lastgroup']         = $lastgroup;
                    }

                    if ($this->sSurveyMode == 'question') {
                        $aGroup['show_last_answer']   = true;
                        $aGroup['lastanswer']         = $lastanswer;
                    }
                }
                Yii::app()->setConfig('gid', '');
                $this->aSurveyInfo['aGroups'][$gid] = $aGroup;
            }
        }

        /**
         *  Expression Manager Scrips and inputs
         */
        $step = isset($_SESSION[$this->LEMsessid]['step']) ? $_SESSION[$this->LEMsessid]['step'] : '';
        $this->aSurveyInfo['EM']['ScriptsAndHiddenInputs'] = "<!-- emScriptsAndHiddenInputs -->";
        /**
         * Navigator
         */
        $this->aSurveyInfo['aNavigator']         = array();
        $this->aSurveyInfo['aNavigator']['show'] = $aNavigator['show'] = $this->aSurveyInfo['aNavigator']['save']['show'] = $this->aSurveyInfo['aNavigator']['load']['show'] = false;

        if (!$this->previewgrp && !$this->previewquestion) {
            $this->aSurveyInfo['aNavigator']            = getNavigatorDatas();
            $this->aSurveyInfo['hiddenInputs']          = \CHtml::hiddenField('thisstep', $_SESSION[$this->LEMsessid]['step'], array('id'=>'thisstep'));
            $this->aSurveyInfo['hiddenInputs']         .= \CHtml::hiddenField('sid', $this->iSurveyid, array('id'=>'sid'));
            $this->aSurveyInfo['hiddenInputs']         .= \CHtml::hiddenField('start_time', time(), array('id'=>'start_time'));
            $_SESSION[$this->LEMsessid]['LEMpostKey'] = mt_rand();
            $this->aSurveyInfo['hiddenInputs']         .= \CHtml::hiddenField('LEMpostKey', $_SESSION[$this->LEMsessid]['LEMpostKey'], array('id'=>'LEMpostKey'));
            if (!empty($_SESSION[$this->LEMsessid]['token'])) {
                $this->aSurveyInfo['hiddenInputs']     .= \CHtml::hiddenField('token', $_SESSION[$this->LEMsessid]['token'], array('id'=>'token'));
            }
        }

        // For "clear all" buttons
        $this->aSurveyInfo['jYesNo'] = ls_json_encode(array('yes'=>gT("Yes"), 'no'=>gT("No")));

        $this->aSurveyInfo['aLEM']['debugtimming']['show'] = false;

        if (($this->LEMdebugLevel & LEM_DEBUG_TIMING) == LEM_DEBUG_TIMING) {
            $this->aSurveyInfo['aLEM']['debugtimming']['show']   = true;
            $this->aSurveyInfo['aLEM']['debugtimming']['script'] = LimeExpressionManager::GetDebugTimingMessage();
        }

        $this->aSurveyInfo['aLEM']['debugvalidation']['show'] = false;

        if (($this->LEMdebugLevel & LEM_DEBUG_VALIDATION_SUMMARY) == LEM_DEBUG_VALIDATION_SUMMARY) {
            $this->aSurveyInfo['aLEM']['debugvalidation']['show']    = true;
            $this->aSurveyInfo['aLEM']['debugvalidation']['message'] = $this->aMoveResult['message'];
        }

        $this->aSurveyInfo['include_content'] = 'main';
        Yii::app()->twigRenderer->renderTemplateFromFile("layout_global.twig", array(
            'oSurvey'=> Survey::model()->findByPk($this->iSurveyid),
            'aSurveyInfo'=>$this->aSurveyInfo,
            'step'=>$step,
            'LEMskipReprocessing'=>$this->LEMskipReprocessing,
        ), false);
    }

    public function getShowNumAndCode()
    {

        $showqnumcode_global_ = getGlobalSetting('showqnumcode');
        $showqnumcode_survey_ = $this->aSurveyInfo['showqnumcode'];

        // Check global setting to see if survey level setting should be applied
        if ($showqnumcode_global_ == 'choose') {
// Use survey level settings
            $showqnumcode_ = $showqnumcode_survey_; //B, N, C, or X
        } else {
            // Use global setting
            $showqnumcode_ = $showqnumcode_global_; //both, number, code, or none
        }

        switch ($showqnumcode_) {
            // Both
            case 'both':
            case 'B':
                $aShow['question_code']   = true;
                $aShow['question_number'] = true;
                break;

            // Number only
            case 'number':
            case 'N':
                $aShow['question_code']   = false;
                $aShow['question_number'] = true;
                break;

            // Code only
            case 'code':
            case 'C':
                $aShow['question_code']   = true;
                $aShow['question_number'] = false;
                break;

            // Neither
            case 'none':
            case 'X':
            default:
                $aShow['question_code']   = false;
                $aShow['question_number'] = false;
                break;
        }

        return $aShow;
    }

    /**
     * Init session/params values depending of user moves
     *
     * - It init the needed variables for navigation: initFirstStep, initTotalAndMaxSteps, setMoveResult
     * - Then perform all the needed checks before moving:
     *   + did the participant used browser navigation?
     *   + did he pressed clear cancel, is he a confirmed quota?
     *   + Is the previous step set?
     *   + Is the survey finished?
     *   + Are all the answer validated? (like: participant didn't answered to a mandatory question)
     */
    private function initMove()
    {
        $this->initFirstStep(); // If it's the first time user load this survey, will init session and LEM
        $this->initTotalAndMaxSteps();
        $this->checkIfUseBrowserNav(); // Check if user used browser navigation, or relaoded page
        if ($this->sMove != 'clearcancel' && $this->sMove != 'confirmquota') {
            $this->checkPrevStep(); // Check if prev step is set, else set it
            $this->setMoveResult();
            $this->checkClearCancel();
            $this->setPrevStep();
            $this->checkIfFinished();
            $this->setStep();

            // CHECK UPLOADED FILES
            // TMSW - Move this into LEM::NavigateForwards?
            $this->filenotvalidated = checkUploadedFileValidity($this->iSurveyid, $this->sMove);

            //SEE IF THIS GROUP SHOULD DISPLAY
            if ($_SESSION[$this->LEMsessid]['step'] == 0) {
                $this->bShowEmptyGroup = true;
            }

        }

    }


    /**
     * Now it's ok ^^
     */
    private function setArgs()
    {
        if ($this->sMove == "movesubmit") {
            $aSurveyInfo = getSurveyInfo($this->iSurveyid, App()->getLanguage());
            $this->aSurveyInfo = $aSurveyInfo;

            if ($this->aSurveyInfo['refurl'] == "Y") {
                //Only add this if it doesn't already exist
                if ($this->LEMsessid && !in_array("refurl", $_SESSION[$this->LEMsessid]['insertarray'])) {
                    $_SESSION[$this->LEMsessid]['insertarray'][] = "refurl";
                }
            }
            resetTimers();

            //Before doing the "templatereplace()" function, check the $this->aSurveyInfo['url']
            //field for limereplace stuff, and do transformations!
            $this->aSurveyInfo['surveyls_url'] = passthruReplace($this->aSurveyInfo['surveyls_url'], $this->aSurveyInfo);
            $this->aSurveyInfo['surveyls_url'] = templatereplace($this->aSurveyInfo['surveyls_url'], array(), $redata, 'URLReplace', false, null, array(), true); // to do INSERTANS substitutions
        }
    }


    /**
     * Retreive the survey format (mode?)
     * TODO: move to survey model
     *
     * @return string
     */
    private function setSurveyMode()
    {
        switch ($this->aSurveyInfo['format']) {
            case "A": //All in one
                $this->sSurveyMode = 'survey';
                break;
            default:
            case "S": //One at a time
                $this->sSurveyMode = 'question';
                break;
            case "G": //Group at a time
                $this->sSurveyMode = 'group';
                break;
        }
    }

    /**
     * Retreive the radix
     * @return string
     */
    private function getRadix()
    {
        $radix = getRadixPointData($this->aSurveyInfo['surveyls_numberformat']);
        $radix = $radix['separator'];
        return $radix;
    }

    /**
     * Retreives dew options comming from thissurvey, App->getConfig, LEM.
     * TODO: move to survey model
     *
     */
    private function setSurveyOptions()
    {
        global $clienttoken;

        $radix         = $this->getRadix();
        $timeadjust    = Yii::app()->getConfig("timeadjust");

        $this->aSurveyOptions = array(
            'active'                      => ($this->aSurveyInfo['active'] == 'Y'),
            'allowsave'                   => ($this->aSurveyInfo['allowsave'] == 'Y'),
            'anonymized'                  => ($this->aSurveyInfo['anonymized'] != 'N'),
            'assessments'                 => ($this->aSurveyInfo['assessments'] == 'Y'),
            'datestamp'                   => ($this->aSurveyInfo['datestamp'] == 'Y'),
            'deletenonvalues'             => Yii::app()->getConfig('deletenonvalues'),
            'hyperlinkSyntaxHighlighting' => (($this->LEMdebugLevel & LEM_DEBUG_VALIDATION_SUMMARY) == LEM_DEBUG_VALIDATION_SUMMARY), // TODO set this to true if in admin mode but not if running a survey
            'ipaddr'                      => ($this->aSurveyInfo['ipaddr'] == 'Y'),
            'radix'                       => $radix,
            'refurl'                      => (($this->aSurveyInfo['refurl'] == "Y" && isset($_SESSION[$this->LEMsessid]['refurl'])) ? $_SESSION[$this->LEMsessid]['refurl'] : null),
            'savetimings'                 => ($this->aSurveyInfo['savetimings'] == "Y"),
            'surveyls_dateformat'         => isset($this->aSurveyInfo['surveyls_dateformat']) ? $this->aSurveyInfo['surveyls_dateformat'] : 1,
            'startlanguage'               => (isset(App()->language) ? App()->language : $this->aSurveyInfo['language']),
            'target'                      => Yii::app()->getConfig('uploaddir').DIRECTORY_SEPARATOR.'surveys'.DIRECTORY_SEPARATOR.$this->aSurveyInfo['sid'].DIRECTORY_SEPARATOR.'files'.DIRECTORY_SEPARATOR,
            'tempdir'                     => Yii::app()->getConfig('tempdir').DIRECTORY_SEPARATOR,
            'timeadjust'                  => $timeadjust,
            'token'                       => $clienttoken,
        );

    }

    /**
     * If it's the first time the survey is loaded:
     * - Init session, randomization and filed array
     * - Check surveyid coherence
     * - Init $LEM states.
     * - Decide if Welcome page should be shown
     */
    private function initFirstStep()
    {
        // First time the survey is loaded
        if (!isset($_SESSION[$this->LEMsessid]['step']) ) {
            // Init session, randomization and filed array
            buildsurveysession($this->iSurveyid);
            $fieldmap = randomizationGroupsAndQuestions($this->iSurveyid);
            initFieldArray($this->iSurveyid, $fieldmap);

            // Check surveyid coherence
            if ($this->iSurveyid != LimeExpressionManager::getLEMsurveyId()) {
                LimeExpressionManager::SetDirtyFlag();
            }

            // Init $LEM states.
            LimeExpressionManager::StartSurvey($this->iSurveyid, $this->sSurveyMode, $this->aSurveyOptions, false, $this->LEMdebugLevel);
            $_SESSION[$this->LEMsessid]['step'] = 0;

            // Welcome page.
            if ($this->sSurveyMode == 'survey') {
                LimeExpressionManager::JumpTo(1, false, false, true);
            } elseif (isset($this->aSurveyInfo['showwelcome']) && $this->aSurveyInfo['showwelcome'] == 'N') {
                $this->aMoveResult = LimeExpressionManager::NavigateForwards();
                $_SESSION[$this->LEMsessid]['step'] = 1;
            }
        } elseif ($this->iSurveyid != LimeExpressionManager::getLEMsurveyId()) {
            $this->initDirtyStep();
        }

    }

    /**
     * If a step is requested, but the survey id in the session is different from the requested one
     * It reload the needed infos for the requested survey and jump to the requested step.
     */
    private function initDirtyStep()
    {

        //$_SESSION[$this->LEMsessid]['step'] can not be less than 0, fix it always #09772
        $_SESSION[$this->LEMsessid]['step'] = $_SESSION[$this->LEMsessid]['step'] < 0 ? 0 : $_SESSION[$this->LEMsessid]['step'];
        LimeExpressionManager::StartSurvey($this->iSurveyid, $this->sSurveyMode, $this->aSurveyOptions, false, $this->LEMdebugLevel);
        LimeExpressionManager::JumpTo($_SESSION[$this->LEMsessid]['step'], false, false);
    }

    /**
     * Seems to be a quick fix to avoid the total and max steps to be null...
     */
    private function initTotalAndMaxSteps()
    {

        if (!isset($_SESSION[$this->LEMsessid]['totalsteps'])) {
            $_SESSION[$this->LEMsessid]['totalsteps'] = 0;
        }

        if (!isset($_SESSION[$this->LEMsessid]['maxstep'])) {
            $_SESSION[$this->LEMsessid]['maxstep'] = 0;
        }

    }

    /**
     * It checks if user used the browser navigation (prev, next, reload page etc)
     * and feed te backpopup variable if needed
     *
     */
    private function checkIfUseBrowserNav()
    {
        // retrieve datas from local variable
        if (isset($_SESSION[$this->LEMsessid]['LEMpostKey']) && App()->request->getPost('LEMpostKey', $_SESSION[$this->LEMsessid]['LEMpostKey']) != $_SESSION[$this->LEMsessid]['LEMpostKey']) {
            // then trying to resubmit (e.g. Next, Previous, Submit) from a cached copy of the page
            $this->aMoveResult = LimeExpressionManager::JumpTo($_SESSION[$this->LEMsessid]['step'], false, false, true); // We JumpTo current step without saving: see bug #11404

            if (isset($this->aMoveResult['seq']) && App()->request->getPost('thisstep', $this->aMoveResult['seq']) == $this->aMoveResult['seq']) {

                /* then pressing F5 or otherwise refreshing the current page, which is OK
                 * Seems OK only when movenext but not with move by index : same with $this->aMoveResult = LimeExpressionManager::GetLastMoveResult(true);
                 */
                $this->LEMskipReprocessing = true;
                $this->sMove = "movenext"; // so will re-display the survey
            } else {
                // trying to use browser back buttons, which may be disallowed if no 'previous' button is present
                $this->LEMskipReprocessing = true;
                $this->sMove                = "movenext"; // so will re-display the survey
                $this->bInvalidLastPage     = true;
                $this->backpopup           = gT("Please use the survey navigation buttons or index.  It appears you attempted to use the browser back button to re-submit a page."); // TODO: twig
            }
        }
    }

    /**
     * Check quotas
     */
    private function checkQuotas()
    {
        /* quota submitted */
        if ($this->sMove == 'confirmquota') {
            checkCompletedQuota($this->iSurveyid);
        }
        /* quota submitted */
        if ($this->sMove == 'returnfromquota') {
            LimeExpressionManager::JumpTo($this->param['thisstep']);
        }
    }

    /**
     * Check if the move is clearcancel or confirmquota
     */
    private function checkClearCancel()
    {
        if ($this->sMove == "clearcancel") {
            $this->aMoveResult = LimeExpressionManager::JumpTo($_SESSION[$this->LEMsessid]['step'], false, false);
        }
    }

    /**
     * Set prev step in session depending on move type
     * If not in a specific page, prevstep stock the value of step just before it get updated
     */
    private function setPrevStep()
    {
        if (isset($this->sMove)) {
            if (!in_array($this->sMove, array("clearall", "changelang", "saveall", "reload"))) {
                $_SESSION[$this->LEMsessid]['prevstep'] = $_SESSION[$this->LEMsessid]['step'];
            } else {
                // Accepted $move without error
                $_SESSION[$this->LEMsessid]['prevstep'] = $this->sMove;
            }
        } else {
            // $_SESSION[$this->LEMsessid]['prevstep'] = $_SESSION[$LEMsessid]['step']-1; // Is this needed ?
        }
        if (!isset($_SESSION[$this->LEMsessid]['prevstep'])) {
            $_SESSION[$this->LEMsessid]['prevstep'] = $_SESSION[$this->LEMsessid]['prevstep'] - 1; // this only happens on re-load
        }

    }

    /**
     * Define prev step if not set in session.
     */
    private function checkPrevStep()
    {

        if (!isset($_SESSION[$this->LEMsessid]['prevstep'])) {
            $_SESSION[$this->LEMsessid]['prevstep'] = $_SESSION[$this->LEMsessid]['step'] - 1; // this only happens on re-load
        }
    }

    /**
     * Set the moveResult variable, depending on the user move request
     */
    private function setMoveResult()
    {
        // retrieve datas from local variable
        if (isset($_SESSION[$this->LEMsessid]['LEMtokenResume'])) {

            LimeExpressionManager::StartSurvey($this->aSurveyInfo['sid'], $this->sSurveyMode, $this->aSurveyOptions, false, $this->LEMdebugLevel);

            // Do it only if needed : we don't need it if we don't have index
            if (isset($_SESSION[$this->LEMsessid]['maxstep']) && $_SESSION[$this->LEMsessid]['maxstep'] > $_SESSION[$this->LEMsessid]['step'] && $this->aSurveyInfo['questionindex']) {
                LimeExpressionManager::JumpTo($_SESSION[$this->LEMsessid]['maxstep'], false, false);
            }

            $this->aMoveResult = LimeExpressionManager::JumpTo($_SESSION[$this->LEMsessid]['step'], false, false); // if late in the survey, will re-validate contents, which may be overkill

            unset($_SESSION[$this->LEMsessid]['LEMtokenResume']);
        } else if (!$this->LEMskipReprocessing) {

            //Move current step ###########################################################################
            if ($this->sMove == 'moveprev' && ($this->aSurveyInfo['allowprev'] == 'Y' || $this->aSurveyInfo['questionindex'] > 0)) {
                $this->aMoveResult = LimeExpressionManager::NavigateBackwards();

                if ($this->aMoveResult['at_start']) {
                    $_SESSION[$this->LEMsessid]['step'] = 0;
                    $this->aMoveResult = false; // so display welcome page again
                }
            }

            if ($this->sMove == "movenext") {
                $this->aMoveResult = LimeExpressionManager::NavigateForwards();
            }

            if (($this->sMove == 'movesubmit')) {
                if ($this->sSurveyMode == 'survey') {
                    $this->aMoveResult = LimeExpressionManager::NavigateForwards();
                } else {
                    // may be submitting from the navigation bar, in which case need to process all intervening questions
                    // in order to update equations and ensure there are no intervening relevant mandatory or relevant invalid questions
                    if ($this->aSurveyInfo['questionindex'] == 2) {
                        // Save actual page ,
                        LimeExpressionManager::JumpTo($_SESSION[$this->LEMsessid]['step'], false, true, true);
                        // Review whole before set finished to true (see #09906), index==1 don't need it because never force move
                        LimeExpressionManager::JumpTo(0,false,false,true); // no preview, no post and force
                    }
                    $this->aMoveResult = LimeExpressionManager::JumpTo($_SESSION[$this->LEMsessid]['totalsteps'] + 1, false);
                }
            }
            if ($this->sMove == 'clearall') {
                $this->manageClearAll();
            }
            if ($this->sMove == 'changelang') {
                // jump to current step using new language, processing POST values
                $this->aMoveResult = LimeExpressionManager::JumpTo($_SESSION[$this->LEMsessid]['step'], false, true, true, true); // do process the POST data
            }

            if (isNumericInt($this->sMove) && $this->aSurveyInfo['questionindex'] == 1) {
                $this->sMove = (int) $this->sMove;

                if ($this->sMove > 0 && (($this->sMove <= $_SESSION[$this->LEMsessid]['step']) || (isset($_SESSION[$this->LEMsessid]['maxstep']) && $this->sMove <= $_SESSION[$this->LEMsessid]['maxstep']))) {
                    $this->aMoveResult = LimeExpressionManager::JumpTo($this->sMove, false);
                }
            } elseif (isNumericInt($this->sMove) && $this->aSurveyInfo['questionindex'] == 2) {
                $this->sMove       = (int) $this->sMove;
                $this->aMoveResult = LimeExpressionManager::JumpTo($this->sMove, false, true, true);
            }

            if (!$this->aMoveResult && !($this->sSurveyMode != 'survey' && $_SESSION[$this->LEMsessid]['step'] == 0)) {
                // Just in case not set via any other means, but don't do this if it is the welcome page
                /* GetLastMoveResult reset substitutionNum in EM core if param is true, this break in all in one mode (see #13725) */
                /* Then don't reset substitutionNum since seems some LimeExpressionManager::ProcessString already happen*/
                $this->aMoveResult = LimeExpressionManager::GetLastMoveResult(false);
                $this->LEMskipReprocessing = true;
            }
        }
    }

    /**
     * Test if the the moveresult is finished, to decide to set the new $this->sMove value
     */
    private function checkIfFinished()
    {
        // Reload at first page (welcome after click previous fill an empty $this->aMoveResult array
        if ($this->aMoveResult && isset($this->aMoveResult['seq'])) {
            // With complete index, we need to revalidate whole group bug #08806. It's actually the only mode where we JumpTo with force
            // we already done if move == 'movesubmit', don't do it again
            if ($this->aMoveResult['finished'] == true && $this->sMove != 'movesubmit' && $this->thissurvey['questionindex'] == 2) {
                /* Issue #14855 : always reset submitdate of current response to null */
                if(!empty($_SESSION[$this->LEMsessid]['srid'])) {
                    $oSurveyResponse = SurveyDynamic::model($this->iSurveyid)->findByAttributes(['id' => $_SESSION[$this->LEMsessid]['srid']]);
                    $oSurveyResponse->submitdate = null;
                    $oSurveyResponse->save();
                }
                /* Save current page */
                LimeExpressionManager::JumpTo($_SESSION[$this->LEMsessid]['step'], false, true, true);
                /* Move to start */
                LimeExpressionManager::JumpTo(0, false, false, true);
                /* Try to move next again */
                /* This reset $this->aMoveResult['finished'] to false if have an error */
                $this->aMoveResult = LimeExpressionManager::JumpTo($_SESSION[$this->LEMsessid]['totalsteps'] + 1, false, false, false); // no preview, no save data and NO force
                if (!$this->aMoveResult['mandViolation'] && $this->aMoveResult['valid'] && empty($this->aMoveResult['invalidSQs'])) {
                    $this->aMoveResult['finished'] = true;
                }
            }

            if ($this->aMoveResult['finished'] == true) {
                $this->sMove = 'movesubmit';
            }

            if ($this->sMove == "movesubmit" && $this->aMoveResult['finished'] == false) {
                // then there are errors, so don't finalize the survey
                $this->sMove = "movenext"; // so will re-display the survey
                $this->bInvalidLastPage = true;
            }
        }
    }

    /**
     * Increase step in session
     */
    private function setStep()
    {
        if ($this->aMoveResult && isset($this->aMoveResult['seq'])) {
            if ($this->aMoveResult['finished'] != true) {
                $_SESSION[$this->LEMsessid]['step'] = $this->aMoveResult['seq'] + 1; // step is index base 1
                $this->aStepInfo = LimeExpressionManager::GetStepIndexInfo($this->aMoveResult['seq']);
            }
        }
    }

    /**
     * Display the first page if needed
     */
    private function displayFirstPageIfNeeded()
    {
        $bDisplayFirstPage = ($this->sSurveyMode != 'survey' && $_SESSION[$this->LEMsessid]['step'] == 0);
        $this->aSurveyInfo['move'] = isset($this->sMove) ? $this->sMove : '';

        if ($this->sSurveyMode == 'survey' || $bDisplayFirstPage) {

            //Failsave to have a general standard value
            if (empty($this->aSurveyInfo['datasecurity_notice_label'])) {
                $this->aSurveyInfo['datasecurity_notice_label'] = gT("To continue please first accept our survey data policy.");
            }

            if (empty($this->aSurveyInfo['datasecurity_error'])) {
                $this->aSurveyInfo['datasecurity_error'] = gT("We are sorry but you can't proceed without first agreeing to our survey data policy.");
            }


            $this->aSurveyInfo['datasecurity_notice_label'] = Survey::replacePolicyLink($this->aSurveyInfo['datasecurity_notice_label'],$this->aSurveyInfo['sid']);
        }

        if ($bDisplayFirstPage) {
            $_SESSION[$this->LEMsessid]['test'] = time();
            display_first_page($this->thissurvey, $this->aSurveyInfo);
            Yii::app()->end(); // So we can still see debug messages
        }
    }

    private function checkForDataSecurityAccepted(){
        $this->aSurveyInfo['datasecuritynotaccepted'] = false;
        if($this->param['thisstep'] === '0' && Survey::model()->findByPk($this->aSurveyInfo['sid'])->showsurveypolicynotice>0) {
            $data_security_accepted = App()->request->getPost('datasecurity_accepted', false);
            $move_step = App()->request->getPost('move', false);

            if($data_security_accepted !== 'on' && ($move_step !== 'default')){
                $_SESSION[$this->LEMsessid]['step'] = 0;
                $this->aSurveyInfo['datasecuritynotaccepted'] = true;
                $this->displayFirstPageIfNeeded(true);
                Yii::app()->end(); // So we can still see debug messages
            }
        }
    }

    /**
     * Perform save all if user asked for it
     */
    public function saveAllIfNeeded()
    {
        // save current survey data when clicking on "Load unfinished survey"
        if(Yii::app()->request->getPost('loadall') && Yii::app()->request->getPost('loadall') == 'loadall') {
            if ($this->iSurveyid === null){
                $this->iSurveyid = Yii::app()->request->getPost('sid', 0);
            }
            if ($this->aSurveyInfo === null){
                $this->aSurveyInfo = getSurveyInfo($this->iSurveyid, App()->getLanguage());
            }
            $this->LEMsessid = 'survey_'.$this->iSurveyid;
            if ($this->aSurveyInfo['active'] == "Y" && isset($_SESSION[$this->LEMsessid])) {
                $this->aMoveResult = LimeExpressionManager::JumpTo($_SESSION[$this->LEMsessid]['step'], false); // by jumping to current step, saves data so far
            }
            return;
        }

        if(!Yii::app()->request->getPost('saveall')) {
            return;
        }
        // Don't test if save is allowed … maybe must be done
        if ($this->aSurveyInfo['active'] == "Y") {
            $bAnonymized            = $this->aSurveyInfo["anonymized"] == 'Y';
            $bTokenAnswerPersitance = $this->aSurveyInfo['tokenanswerspersistence'] == 'Y' && $this->iSurveyid != null && tableExists('tokens_'.$this->iSurveyid);

            // must do this here to process the POSTed values
            $this->aMoveResult = LimeExpressionManager::JumpTo($_SESSION[$this->LEMsessid]['step'], false); // by jumping to current step, saves data so far

            if (!isset($_SESSION[$this->LEMsessid]['scid']) && (!$bTokenAnswerPersitance || $bAnonymized)) {
                Yii::import("application.libraries.Save");
                $cSave = new Save();
                // $cSave->showsaveform($this->aSurveyInfo['sid']); // generates a form and exits, awaiting input
                $this->aSurveyInfo['aSaveForm'] = $cSave->getSaveFormDatas($this->aSurveyInfo['sid']);

                $this->aSurveyInfo['include_content'] = 'save';
                Yii::app()->twigRenderer->renderTemplateFromFile("layout_global.twig", array('oSurvey'=> Survey::model()->findByPk($this->iSurveyid), 'aSurveyInfo'=>$this->aSurveyInfo), false);
            } else {
                // Intentional retest of all conditions to be true, to make sure we do have tokens and surveyid
                // Now update lastpage to $_SESSION[$this->LEMsessid]['step'] in SurveyDynamic, otherwise we land on
                // the previous page when we return.
                $iResponseID         = $_SESSION[$this->LEMsessid]['srid'];
                $oResponse           = SurveyDynamic::model($this->iSurveyid)->findByPk($iResponseID);
                $oResponse->lastpage = $_SESSION[$this->LEMsessid]['step'];
                if($oResponse->save()) {
                    $this->aSurveyInfo['saved'] = array(
                        'success'=> true,
                        'title' => gT('Success'),
                        'text' => gT("Your responses were successfully saved.")
                    );
                } else {
                    $this->aSurveyInfo['saved'] = array(
                        'success'=> false,
                        'title' => gT('Error'),
                        'text' => gT("Your responses were not saved. Please contact the survey administrator.")
                    );
                }
                $oResponse->save();
            }
        } else {
            $this->aSurveyInfo['saved'] = array(
                'success'=> false,
                'title' => gT('Warning'),
                'text' => gT("Saving responses is disabled if survey is not activated.")
            );
        }
    }

    /**
     * perform save submit if asked by user
     * called from save survey
     */
    private function saveSubmitIfNeeded()
    {
        if ($this->aSurveyInfo['active'] == "Y" && Yii::app()->request->getParam('savesubmit')) {
            // The response from the save form
            // CREATE SAVED CONTROL RECORD USING SAVE FORM INFORMATION
            Yii::import("application.libraries.Save");
            $cSave = new Save();

            // Try to save survey
            $aResult = $cSave->saveSurvey();
            if (!$aResult['success']) {
                $aPopup  = $this->popup = $aResult['aSaveErrors'];
            } else {
                $aPopup  = $this->popup = array($aResult['message']);
            }

            Yii::app()->clientScript->registerScript('startPopup', "LSvar.startPopups=".json_encode($aPopup).";", LSYii_ClientScript::POS_END);
            Yii::app()->clientScript->registerScript('showStartPopups', "window.templateCore.showStartPopups();", LSYii_ClientScript::POS_POSTSCRIPT);
            // reshow the form if there is an error
            if (!empty($aResult['aSaveErrors'])) {
                $this->aSurveyInfo['aSaveForm'] = $cSave->getSaveFormDatas($this->aSurveyInfo['sid']);
                $this->aSurveyInfo['include_content'] = 'save';
                Yii::app()->twigRenderer->renderTemplateFromFile("layout_global.twig", array('oSurvey'=> Survey::model()->findByPk($this->iSurveyid), 'aSurveyInfo'=>$this->aSurveyInfo), false);
            }

            $this->aMoveResult = LimeExpressionManager::GetLastMoveResult(true);
            $this->LEMskipReprocessing = true;
        }
    }

    /**
     * check mandatory questions if necessary
     * CHECK IF ALL CONDITIONAL MANDATORY QUESTIONS THAT APPLY HAVE BEEN ANSWERED
     */
    private function setNotAnsweredAndNotValidated()
    {
        global $notanswered;
        // TODO: check that line:
        $this->notvalidated = $notanswered;
        $this->notanswered  = $notanswered;

        if (!$this->aMoveResult['finished']) {
            $unansweredSQList = $this->aMoveResult['unansweredSQs']; // A list of the unanswered responses created via the global variable $notanswered. Should be $oResponse->unanswereds
            if (strlen($unansweredSQList) > 0) {
                $this->notanswered = explode('|', $unansweredSQList);
            } else {
                $this->notanswered = array();
            }
            //CHECK INPUT
            $invalidSQList = $this->aMoveResult['invalidSQs']; // Invalid answered, fed from $moveResult(LEM). Its logic should be in Response model.
            if (strlen($invalidSQList) > 0) {
                $this->notvalidated = explode('|', $invalidSQList);
            } else {
                $this->notvalidated = array();
            }
        }
    }

    /**
     * Perform submit if asked by user
     */
    private function moveSubmitIfNeeded()
    {
        if ($this->sMove == "movesubmit") {
            /* Put active in var for next part */
            $surveyActive = ($this->aSurveyInfo['active'] == "Y");
            $oSurvey = Survey::model()->findByPk($this->iSurveyid);
            // Parts needed for active and unactive
            //Check for assessments
            $this->aSurveyInfo['aAssessments']['show'] = false;
            if ($this->aSurveyInfo['assessments'] == "Y") {
                $this->aSurveyInfo['aAssessments'] = doAssessment($this->iSurveyid,false);
            }
            // End text
            if (trim(str_replace(array('<p>', '</p>'), '', $this->aSurveyInfo['surveyls_endtext'])) == '') {
                $this->aSurveyInfo['aCompleted']['showDefault'] = true;
            } else {
                $this->aSurveyInfo['aCompleted']['showDefault'] = false;
                // NOTE: If needed : move keywords from templatereplace to getStandardsReplacementFields function
                //$this->aSurveyInfo['aCompleted']['sEndText'] = templatereplace($this->aSurveyInfo['surveyls_endtext'], array(), $redata, 'SubmitAssessment', false, null, array(), true);
                $this->aSurveyInfo['aCompleted']['sEndText'] = $this->processString($this->aSurveyInfo['surveyls_endtext'], 3,1);
            }

            //Update the token if needed and send a confirmation email
            if ($surveyActive && $oSurvey->getHasTokensTable()) {
                submittokens();
            }
            //Send notifications
            if($surveyActive) {
                sendSubmitNotifications($this->iSurveyid);
            }
            // Link to Print Answer Preview  **********
            $this->aSurveyInfo['aCompleted']['aPrintAnswers']['show'] = false;
            if ($this->aSurveyInfo['printanswers'] == 'Y') {
                $this->aSurveyInfo['aCompleted']['aPrintAnswers']['show']  = true;
                $this->aSurveyInfo['aCompleted']['aPrintAnswers']['sUrl']  = $surveyActive ? Yii::app()->getController()->createUrl("/printanswers/view", array('surveyid'=>$this->iSurveyid)) : "#";
                $this->aSurveyInfo['aCompleted']['aPrintAnswers']['sText'] = gT("Print your answers.");
                $this->aSurveyInfo['aCompleted']['aPrintAnswers']['sTitle'] =  $surveyActive ? $this->aSurveyInfo['aCompleted']['aPrintAnswers']['sText'] : gT("Note: This link only works if the survey is activated.");
            }
            // Link to Public statistics
            $this->aSurveyInfo['aCompleted']['aPublicStatistics']['show'] = false;
            if ($this->aSurveyInfo['publicstatistics'] == 'Y') {
                $this->aSurveyInfo['aCompleted']['aPublicStatistics']['show']  = true;
                $this->aSurveyInfo['aCompleted']['aPublicStatistics']['sUrl']  = $surveyActive ? Yii::app()->getController()->createUrl("/statistics_user/action/", array('surveyid'=>$this->iSurveyid, 'language'=>App()->getLanguage())) : "#";
                $this->aSurveyInfo['aCompleted']['aPublicStatistics']['sText'] =  gT("View the statistics for this survey.");
                $this->aSurveyInfo['aCompleted']['aPublicStatistics']['sTitle'] =  $surveyActive ? $this->aSurveyInfo['aCompleted']['aPublicStatistics']['sText'] : gT("Note: This link only works if the survey is activated.");
            }

            $this->completed = true;

            $_SESSION[$this->LEMsessid]['finished'] = true;
            $_SESSION[$this->LEMsessid]['sid']      = $this->iSurveyid;

            // cookies
            if($surveyActive && $this->aSurveyInfo['usecookie'] == "Y") {
                if(!$oSurvey->getHasTokensTable()) {
                    setcookie("LS_".$this->iSurveyid."_STATUS", "COMPLETE", time() + 31536000); //Cookie will expire in 365 days
                }
            }

            $redata['completed'] = $this->completed;
            // event afterSurveyComplete
            $blocks = array();
            if($surveyActive) { // @todo : enable event even when survey is not active, but broke API
                $event = new PluginEvent('afterSurveyComplete');
                if ($surveyActive && isset($_SESSION[$this->LEMsessid]['srid'])) {
                    $event->set('responseId', $_SESSION[$this->LEMsessid]['srid']);
                }
                $event->set('surveyId', $this->iSurveyid);
                App()->getPluginManager()->dispatchEvent($event);
                foreach ($event->getAllContent() as $blockData) {
                    /* @var $blockData PluginEventContent */
                    $blocks[] = CHtml::tag('div', array('id' => $blockData->getCssId(), 'class' => $blockData->getCssClass()), $blockData->getContent());
                }
            }

            $this->aSurveyInfo['aCompleted']['sPluginHTML']  = implode("\n", $blocks)."\n";
            $this->aSurveyInfo['aCompleted']['sSurveylsUrl'] = $this->aSurveyInfo['surveyls_url'];
            $this->aSurveyInfo['surveyls_url']               = passthruReplace($this->aSurveyInfo['surveyls_url'], $this->aSurveyInfo);
            $this->aSurveyInfo['surveyls_url']               = $this->processString($this->aSurveyInfo['surveyls_url'],3,1);
            $this->aSurveyInfo['aCompleted']['sSurveylsUrl'] = $this->aSurveyInfo['surveyls_url'];
            $this->aSurveyInfo['aCompleted']['sSurveylsUrlDescription'] = $this->aSurveyInfo['surveyls_urldescription'];
            if ($this->aSurveyInfo['aCompleted']['sSurveylsUrlDescription'] == "") {
                $this->aSurveyInfo['aCompleted']['sSurveylsUrlDescription'] = $this->aSurveyInfo['surveyls_url'];
            }

            // LEM debug (???? what is this usage …)
            $this->aSurveyInfo['aLEM']['debugvalidation']['show'] = false;
            if (($this->LEMdebugLevel & LEM_DEBUG_VALIDATION_SUMMARY) == LEM_DEBUG_VALIDATION_SUMMARY) {
                $this->aSurveyInfo['aLEM']['debugvalidation']['show'] = true;
                $this->aSurveyInfo['aLEM']['debugvalidation']['message'] = $this->aMoveResult['message'];
            }

            $this->aSurveyInfo['aLEM']['debugvalidation']['show'] = false; $this->aSurveyInfo['aLEM']['debugvalidation']['message'] = '';
            if ((($this->LEMdebugLevel & LEM_DEBUG_TIMING) == LEM_DEBUG_TIMING)) {
                $this->aSurveyInfo['aLEM']['debugvalidation']['show']     = true;
                $this->aSurveyInfo['aLEM']['debugvalidation']['message'] .= LimeExpressionManager::GetDebugTimingMessage(); ;
            }

            if ((($this->LEMdebugLevel & LEM_DEBUG_VALIDATION_SUMMARY) == LEM_DEBUG_VALIDATION_SUMMARY)) {
                $this->aSurveyInfo['aLEM']['debugvalidation']['message'] .= "<table><tr><td align='left'><b>Group/Question Validation Results:</b>".$this->aMoveResult['message']."</td></tr></table>\n";
            }

            if (isset($this->aSurveyInfo['autoredirect']) && $this->aSurveyInfo['autoredirect'] == "Y" && $this->aSurveyInfo['surveyls_url']) {
                // kill survey session before redirecting
                if ($this->aSurveyInfo['printanswers'] != 'Y') {
                    killSurveySession($this->iSurveyid);
                }
                //Automatically redirect the page to the "url" setting for the survey
                $headToSurveyUrl = htmlspecialchars_decode ($this->aSurveyInfo['surveyls_url']);
                $actualRedirect = $headToSurveyUrl;
                if($surveyActive) {
                    header("Access-Control-Allow-Origin: *");
                    if(Yii::app()->request->getParam('ajax') == 'on'){
                        header("X-Redirect: ".$headToSurveyUrl, false, 302);
                    } else {
                        header("Location: ".$actualRedirect, false, 302);
                    }
                }
                $this->aSurveyInfo['aCompleted']['sSurveylsUrlDescriptionExta'] = gT("Note: Automatically loading the end URL works only if the survey is activated.");
            }

            $this->aSurveyInfo['include_content'] = 'submit';
            if(!$surveyActive) {
                $this->aSurveyInfo['include_content'] = 'submit_preview';
            }
            $sHtml = Yii::app()->twigRenderer->renderTemplateFromFile("layout_global.twig", array('oSurvey'=> $oSurvey, 'aSurveyInfo'=>$this->aSurveyInfo), true);
            $oTemplate = Template::getLastInstance();
            // kill survey session after doing template : didn't work for all var, but for EM core var : it's OK.
            if ($this->aSurveyInfo['printanswers'] != 'Y') {
                killSurveySession($this->iSurveyid);
            }
            Yii::app()->twigRenderer->renderHtmlPage($sHtml, $oTemplate);
        }
    }


    /**
     * Check in a string if it uses expressions to replace them
     * @param string $sString the string to evaluate
     * @param integer $numRecursionLevels - the number of times to recursively subtitute values in this string
     * @param boolean $static - return static string
     * @return string
     * @todo : find/get current qid for processing string
     */
    private function processString($sString, $iRecursionLevel = 1, $static =false)
    {
        $sProcessedString = $sString;

        if((strpos($sProcessedString, "{") !== false)){
            // process string anyway so that it can be pretty-printed
            $aStandardsReplacementFields = getStandardsReplacementFields($this->aSurveyInfo);
            $sProcessedString = LimeExpressionManager::ProcessStepString( $sString, $aStandardsReplacementFields, $iRecursionLevel, $static);
        }
        return $sProcessedString;
    }

    /**
     * The run method fed $redata with using get_defined_var(). So it was very hard to move a piece of code from the run method to a new one.
     * To make it easier, private variables has been added to this class:
     * So when a piece of code changes a variable (a variable that originally was finally added to redata get_defined_var()), now, it also changes its private variable version.
     * Then, before performing the get_defined_var, the private variables are used to recreate those variables. So we can move piece of codes to sub methods.
     * setVarFromArgs($args) will set the original state of those private variables using the parameter $args passed to the run() method
     *
     * @param array $args
     */
    private function setVarFromArgs($args)
    {
        extract($args);

        $this->param = $param;

        // Todo: check which ones are really needed
        $this->LEMskipReprocessing    = isset($LEMskipReprocessing) ? $LEMskipReprocessing : null;
        $this->thissurvey             = isset($thissurvey) ? $thissurvey : null;
        $this->iSurveyid              = isset($surveyid) ? $surveyid : null;
        $this->LEMsessid              = $this->iSurveyid ? 'survey_'.$this->iSurveyid: null;
        $this->aSurveyOptions         = isset($surveyOptions) ? $surveyOptions : null;
        $this->aMoveResult            = isset($moveResult) ? $moveResult : null;
        $this->sMove                  = isset($move) ? $move : null;
        $this->bInvalidLastPage       = isset($invalidLastPage) ? $invalidLastPage : null;
        $this->notanswered            = isset($notanswered) ? $notanswered : null;
        $this->filenotvalidated       = isset($filenotvalidated) ? $filenotvalidated : null;
        $this->completed              = isset($completed) ? $completed : null;
        $this->notvalidated           = isset($notvalidated) ? $notvalidated : null;
    }

    /**
     * setJavascriptVar
     *
     * @return @void
     * @param integer $iSurveyId : the survey id for the script
     */
    public function setJavascriptVar($iSurveyId = '')
    {
        $aSurveyinfo = ($iSurveyId != '') ?getSurveyInfo($iSurveyId, App()->getLanguage()) : $this->thissurvey;

        if (isset($aSurveyinfo['surveyls_numberformat'])) {
            $aLSJavascriptVar                  = array();
            $aLSJavascriptVar['bFixNumAuto']   = (int) (bool) Yii::app()->getConfig('bFixNumAuto', 1);
            $aLSJavascriptVar['bNumRealValue'] = (int) (bool) Yii::app()->getConfig('bNumRealValue', 0);
            $aRadix                            = getRadixPointData($aSurveyinfo['surveyls_numberformat']);
            $aLSJavascriptVar['sLEMradix']     = $aRadix['separator'];
            $aLSJavascriptVar['lang']          = [
                "confirm" =>  [
                    "confirm_cancel" =>  gT('Cancel'),
                    "confirm_ok" =>  gT('OK'),
                ],
            ]; // To add more easily some lang string here
            $aLSJavascriptVar['showpopup']     = $this->oTemplate != null ? $this->oTemplate->showpopups : false;
            $aLSJavascriptVar['startPopups']   = new stdClass;
            $aLSJavascriptVar['debugMode']     = Yii::app()->getConfig('debug');
            $sLSJavascriptVar                  = "LSvar=".json_encode($aLSJavascriptVar).';';
            App()->clientScript->registerScript('sLSJavascriptVar', $sLSJavascriptVar, CClientScript::POS_HEAD);
            App()->clientScript->registerScript('setJsVar', "setJsVar();", CClientScript::POS_BEGIN); // Ensure all js var is set before rendering the page (User can click before $.ready)
        }
    }

    /**
     * Html error message if needed/available in the page
     * @return string (html)
     * @todo : move to coreReplacements ? Can be good.
     */
    private function getErrorHtmlMessage()
    {
        $aErrorsMandatory = array();

        //Mandatory question(s) with unanswered answer
        if ($this->aStepInfo['mandViolation'] && $this->okToShowErrors) {
            $aErrorsMandatory[] = gT("One or more mandatory questions have not been answered. You cannot proceed until these have been completed.");
        }

        // Question(s) with not valid answer(s)
        if (!$this->aStepInfo['valid'] && $this->okToShowErrors) {
            $aErrorsMandatory[] = gT("One or more questions have not been answered in a valid manner. You cannot proceed until these answers are valid.");
        }

        // Upload question(s) with invalid file(s)
        if ($this->filenotvalidated && $this->okToShowErrors) {
            $aErrorsMandatory[] = gT("One or more uploaded files are not in proper format/size. You cannot proceed until these files are valid.");
        }

        return $aErrorsMandatory;
    }

    /**
     * clear all system (no js or broken js)
     * @uses $this->iSurveyid
     * @uses $this->sTemplateViewPath
     * @return void
     */
    private function manageClearAll()
    {
        $sessionSurvey = Yii::app()->session["survey_{$this->iSurveyid}"];
        if (App()->request->getPost('confirm-clearall') != 'confirm') {
            /* Save current reponse, and come back to survey if clearll is not confirmed */
            $this->aMoveResult = LimeExpressionManager::JumpTo($_SESSION[$this->LEMsessid]['step'], false, true, true, false);
            /* Todo : add an error in HTML view … */
            //~ $aErrorHtmlMessage                             = array(gT("You need to confirm clear all action"));
            //~ $this->aSurveyInfo['errorHtml']['show']        = true;
            //~ $this->aSurveyInfo['errorHtml']['hiddenClass'] = "ls-js-hidden";
            //~ $this->aSurveyInfo['errorHtml']['messages']    = $aErrorHtmlMessage;
            return;
        }
        if (App()->request->getPost('confirm-clearall') == 'confirm') {
            // Previous behaviour (and javascript behaviour)
            // delete the existing response but only if not already completed
            if (
                isset($sessionSurvey['srid'])
                && !SurveyDynamic::model($this->iSurveyid)->isCompleted($sessionSurvey['srid']) // see bug https://bugs.limesurvey.org/view.php?id=11978
            ) {
                $oResponse = Response::model($this->iSurveyid)->find("id = :srid", array(":srid"=>$sessionSurvey['srid']));

                if ($oResponse) {
                    $oResponse->delete(true); /* delete response line + files uploaded , warninbg : beforeDelete don't happen with deleteAll */
                }

                if (Survey::model()->findByPk($this->iSurveyid)->savetimings == "Y") {
                    SurveyTimingDynamic::model($this->iSurveyid)->deleteAll("id=:srid", array(":srid"=>$sessionSurvey['srid'])); /* delete timings ( @todo must move it to Response )*/
                }

                SavedControl::model()->deleteAll("sid=:sid and srid=:srid", array(":sid"=>$this->iSurveyid, ":srid"=>$sessionSurvey['srid'])); /* saved controls (think we can have only one , but maybe ....)( @todo must move it to Response )*/
            }

            killSurveySession($this->iSurveyid);

            global $token;
            if ($token) {
                $restartparam['token'] = Token::sanitizeToken($token);
            }

            if (!empty(App()->getLanguage())) {
                $restartparam['lang'] = sanitize_languagecode(App()->getLanguage());
            } else {
                $s_lang = isset(Yii::app()->session['survey_'.$this->iSurveyid]['s_lang']) ? Yii::app()->session['survey_'.$this->iSurveyid]['s_lang'] : 'en';
                $restartparam['lang'] = $s_lang;
            }

            $restartparam['newtest'] = "Y";
            $restarturl = Yii::app()->getController()->createUrl("survey/index/sid/$this->iSurveyid", $restartparam);

            $this->aSurveyInfo['surveyUrl'] = $restarturl;
            $this->aSurveyInfo['include_content'] = 'clearall';
            Yii::app()->twigRenderer->renderTemplateFromFile("layout_global.twig", array('oSurvey'=> Survey::model()->findByPk($this->iSurveyid), 'aSurveyInfo'=>$this->aSurveyInfo), false);
        }
    }

    /**
     * NOTE: right now, captcha works ONLY if reloaded... need to be debug.
     * NOTE: I bet we have the same problem on 2.6x.x
     * NOTE: when token + captcha: works fine
     */
    private function showTokenOrCaptchaFormsIfNeeded()
    {
        $this->iSurveyid   = $this->aSurveyInfo['sid'];
        $preview           = $this->preview;

        // Template settings
        $oTemplate         = $this->oTemplate;
        $this->sTemplateViewPath = $oTemplate->viewPath;


        // TODO: find where they are defined before this call
        global $clienttoken;
        global $tokensexist;
        /**
         * This method has multiple outcomes that virtually do the same thing
         * Possible scenarios/subscenarios are =>
         *   - No token required & no captcha required
         *   - No token required & captcha required
         *       > captcha may be wrong
         *   - token required & captcha required
         *       > token may be wrong/used
         *       > captcha may be wrong
         */

        $scenarios = array(
            "tokenRequired"   => ($tokensexist == 1),
            "captchaRequired" => (isCaptchaEnabled('surveyaccessscreen', $this->aSurveyInfo['usecaptcha']) && !isset($_SESSION['survey_'.$this->iSurveyid]['captcha_surveyaccessscreen']))
        );

        /**
         *   Set subscenarios depending on scenario outcome
         */
        $subscenarios = array(
            "captchaCorrect" => false,
            "tokenValid"     => false
        );

        //Check the scenario for token required
        if ($scenarios['tokenRequired']) {

            //Check for the token-validity
            if ($this->aSurveyInfo['alloweditaftercompletion'] == 'Y') {
                $oTokenEntry = Token::model($this->iSurveyid)->findByAttributes(array('token'=>$clienttoken));
            } else {
                $oTokenEntry = Token::model($this->iSurveyid)->usable()->incomplete()->findByAttributes(array('token' => $clienttoken));
            }
            $subscenarios['tokenValid'] = ((!empty($oTokenEntry) && ($clienttoken != "")));
        } else {
            $subscenarios['tokenValid'] = true;
        }

        //Check the scenario for captcha required
        if ($scenarios['captchaRequired']) {
            //Check if the Captcha was correct
            $captcha                        = Yii::app()->getController()->createAction('captcha');
            $subscenarios['captchaCorrect'] = $captcha->validate(App()->getRequest()->getPost('loadsecurity'), false);
        } else {
            $subscenarios['captchaCorrect'] = true;
        }


        //RenderWay defines which html gets rendered to the user_error
        // Possibilities are main,register,correct
        $renderCaptcha = "";
        $renderToken   = "";

        /**
         * @todo : create 2 new function to create and call form
         */
        //Define array to render the partials
        $aEnterTokenData                    = array();
        $aEnterTokenData['bNewTest']        = false;
        $aEnterTokenData['bDirectReload']   = false;
        $aEnterTokenData['iSurveyId']       = $this->iSurveyid;
        $aEnterTokenData['sLangCode']       = App()->language;

        if (isset($_GET['bNewTest']) && $_GET['newtest'] == "Y") {
            $aEnterTokenData['bNewTest'] = true;
        }

        // TODO: check with markus why $loadall, it's never ever defined, even in master branch

        /*
        // If this is a direct Reload previous answers URL, then add hidden fields
        if (isset($loadall) && isset($scid) && isset($loadname) && isset($loadpass)) {
            $aEnterTokenData['bDirectReload'] =  true;
            $aEnterTokenData['sCid'] =  $scid;
            $aEnterTokenData['sLoadname'] =  htmlspecialchars($loadname);
            $aEnterTokenData['sLoadpass'] =  htmlspecialchars($loadpass);
        }
        */

        $aEnterErrors = array();
        $FlashError   = false;

        // Scenario => Captcha required
        if ($scenarios['captchaRequired'] && !$preview) {

            //Apply the captcYii::app()->getRequest()->getPost($id);haEnabled flag to the partial
            $aEnterTokenData['bCaptchaEnabled'] = true;
            // IF CAPTCHA ANSWER IS NOT CORRECT OR NOT SET
            if (!$subscenarios['captchaCorrect']) {

                if (App()->getRequest()->getPost('loadsecurity')) {
                    $aEnterErrors['captcha'] = gT("Your answer to the security question was not correct - please try again.");

                } elseif (null !== App()->getRequest()->getPost('loadsecurity')) {
                    $aEnterErrors['captcha'] = gT("Your have to answer the security question - please try again.");
                }
                $renderCaptcha = 'main';
            } else {
                $_SESSION['survey_'.$this->iSurveyid]['captcha_surveyaccessscreen'] = true;
                $renderCaptcha = 'correct';
            }
        }

        // Scenario => Token required
        if ($scenarios['tokenRequired'] && !$preview) {
            //Test if token is valid
            list($renderToken, $FlashError, $aEnterTokenData) = testIfTokenIsValid($subscenarios, $this->aSurveyInfo, $aEnterTokenData, $clienttoken);
        }

        if ($FlashError) {
            $aEnterErrors['flash'] = $FlashError;
        }

        $aEnterTokenData['aEnterErrors']    = $aEnterErrors;
        $renderWay                          = getRenderWay($renderToken, $renderCaptcha);

        /* This funtion end if an form need to be shown */
        renderRenderWayForm($renderWay, $scenarios, $this->sTemplateViewPath, $aEnterTokenData, $this->iSurveyid, $this->aSurveyInfo);

    }


    private function initTemplate()
    {
        $oTemplate = $this->oTemplate = Template::model()->getInstance('', $this->iSurveyid);
        $this->sTemplateViewPath = $oTemplate->viewPath;
        //$oTemplate->registerAssets();
    }

    /**
     * Set alanguageChanger.show to true if we need to show
     * the language changer.
     * @return void
     */
    private function makeLanguageChanger()
    {
        $this->aSurveyInfo['alanguageChanger']['show'] = false;
        $alanguageChangerDatas = getLanguageChangerDatas($this->sLangCode);

        if ($alanguageChangerDatas) {
            $this->aSurveyInfo['alanguageChanger']['show']  = true;
            $this->aSurveyInfo['alanguageChanger']['datas'] = $alanguageChangerDatas;
        }
    }

    /**
     * This method will set survey values in public property of the class
     * So, any value here set as $this->xxx will be available as $xxx after :
     * eg: $this->LEMsessid
     * @param integer $surveyid;
     * @param array $args;
     */
    private function setSurveySettings($surveyid, $args)
    {
        $this->setVarFromArgs(array_merge($args, array('surveyid' => $surveyid))); // Set the private variable from $args, be sure to set surveyid
        $this->initTemplate(); // Template settings
        $this->setJavascriptVar();
        $this->setArgs();

        extract($args);

        $this->aSurveyInfo                 = getSurveyInfo($this->iSurveyid, App()->getLanguage());
        $this->aSurveyInfo['surveyUrl']    = App()->createUrl("/survey/index", array("sid"=>$this->iSurveyid));

        // TODO: check this:
        $this->aSurveyInfo['oTemplate']    = (array) $this->oTemplate;

        $this->setSurveyMode();
        $this->setSurveyOptions();

        $this->previewgrp      = (isset($this->param['action']) && $this->param['action'] == 'previewgroup') ?true:false;
        $this->previewquestion = (isset($this->param['action']) && $this->param['action'] == 'previewquestion') ?true:false;

        $this->preview         = ($this->previewquestion || $this->previewgrp);

        if ($this->preview) {
            // When previewing groups or questions, the survey URL must include the
            // respective parameters. Otherwise, changing language doesn't work.
            $surveyUrlParams = array(
                "action" => $this->param['action'],
                "sid" => $this->iSurveyid,
            );
            if (isset($this->param['gid'])) {
                $surveyUrlParams['gid'] = $this->param['gid'];
            }
            if (isset($this->param['qid'])) {
                $surveyUrlParams['qid'] = $this->param['qid'];
            }
            $this->aSurveyInfo['surveyUrl'] = App()->createUrl("/survey/index", $surveyUrlParams);
        }

        $this->sLangCode       = App()->language;
    }

    private function setPreview()
    {
        $this->sSurveyMode = ($this->previewgrp) ? 'group' : 'question'; // Can be great to have a survey here …
        buildsurveysession($this->iSurveyid,true); // Preview part disable SurveyURLParameter , why ? Work without

        /* Set steps for PHP notice */
        $_SESSION[$this->LEMsessid]['prevstep'] = 2;
        $_SESSION[$this->LEMsessid]['maxstep']  = 0;
        $_SESSION[$this->LEMsessid]['step'] = 0;
        if ($this->previewgrp) {
            $_gid = sanitize_int($this->param['gid']);

            LimeExpressionManager::StartSurvey($this->aSurveyInfo['sid'], $this->sSurveyMode, $this->aSurveyOptions, false, $this->LEMdebugLevel);
            $gseq = LimeExpressionManager::GetGroupSeq($_gid);

            if ($gseq == -1) {
                $sMessage = gT('Invalid group number for this survey: ').$_gid;
                renderError('', $sMessage, $this->aSurveyInfo, $this->sTemplateViewPath);
            }

            $this->aMoveResult = LimeExpressionManager::JumpTo($gseq + 1, 'group', false, true);
            if (is_null($this->aMoveResult)) {
                $sMessage = gT('This group contains no questions.  You must add questions to this group before you can preview it');
                renderError('', $sMessage, $this->aSurveyInfo, $this->sTemplateViewPath);
            }

            $_SESSION[$this->LEMsessid]['step'] = $this->aMoveResult['seq'] + 1; // step is index base 1?

            $this->aStepInfo = LimeExpressionManager::GetStepIndexInfo($this->aMoveResult['seq']);

            // #14595
            if(empty($this->aStepInfo)) {
                $sMessage = gT('This group is empty');
                renderError('', $sMessage, $this->aSurveyInfo, $this->sTemplateViewPath);
            }

        } elseif ($this->previewquestion) {
            $_qid       = sanitize_int($this->param['qid']);
            LimeExpressionManager::StartSurvey($this->iSurveyid, $this->sSurveyMode, $this->aSurveyOptions, true, $this->LEMdebugLevel);
            $qSec = LimeExpressionManager::GetQuestionSeq($_qid);
            $this->aMoveResult = LimeExpressionManager::JumpTo($qSec + 1, 'question', false, true);
            $this->aStepInfo = LimeExpressionManager::GetStepIndexInfo($this->aMoveResult['seq']);
        }
    }


    private function setGroup()
    {
        if (!$this->previewgrp && !$this->previewquestion) {
            if (($this->bShowEmptyGroup) || !isset($_SESSION[$this->LEMsessid]['grouplist'])) {
                $this->gid              = -1; // Make sure the gid is unused. This will assure that the foreach (fieldarray as ia) has no effect.
                $this->groupname        = gT("Submit your answers");
                $this->groupdescription = gT("There are no more questions. Please press the <Submit> button to finish this survey.");
            } else if ($this->sSurveyMode != 'survey') {
                if ($this->sSurveyMode != 'group') {
                    $this->aStepInfo = LimeExpressionManager::GetStepIndexInfo($this->aMoveResult['seq']);
                }
                $this->gid              = $this->aStepInfo['gid'];
                $this->groupname        = $this->aStepInfo['gname'];
                $this->groupdescription = $this->aStepInfo['gtext'];
                $this->groupname        = LimeExpressionManager::ProcessString($this->groupname, null, null, 3, 1, false, true, false);
                $this->groupdescription = LimeExpressionManager::ProcessString($this->groupdescription, null, null, 3, 1, false, true, false);
            }
        }
    }

    private function fixMaxStep()
    {
        // NOTE: must stay after setPreview  because of ()$this->sSurveyMode == 'group' && $this->previewgrp) condition touching step
        if ($_SESSION[$this->LEMsessid]['step'] > $_SESSION[$this->LEMsessid]['maxstep']) {
            $_SESSION[$this->LEMsessid]['maxstep'] = $_SESSION[$this->LEMsessid]['step'];
        }
    }

    /**
     * Apply the plugin even beforeQuestionRender to
     * question data.
     *
     * @see https://manual.limesurvey.org/BeforeQuestionRender
     *
     * @param array $data Question data
     * @return array Question data modified by plugin
     */
    protected function doBeforeQuestionRenderEvent($data)
    {
        $event = new PluginEvent('beforeQuestionRender');
        $event->set('surveyId', $this->iSurveyid);
        $event->set('type', $data['type']);
        $event->set('code', $data['code']);
        $event->set('qid', $data['qid']);
        $event->set('gid', $data['gid']);
        $event->set('text', $data['text']);
        $event->set('class', $data['class']);
        $event->set('input_error_class', $data['input_error_class']);
        $event->set('answers', $data['answer']);  // NB: "answers" in plugin, "answer" in $data.
        $event->set('help', $data['help']['text']);
        $event->set('man_message', $data['man_message']);
        $event->set('valid_message', $data['valid_message']);
        $event->set('file_valid_message', $data['file_valid_message']);
        $event->set('aHtmlOptions', array()); // Set as empty array, not needed. Before 3.0 usage for EM style
        App()->getPluginManager()->dispatchEvent($event);

        $data['text']               = $event->get('text');
        $data['mandatory']          = $event->get('mandatory',$data['mandatory']);
        $data['class']              = $event->get('class');
        $data['input_error_class']  = $event->get('input_error_class');
        $data['valid_message']      = $event->get('valid_message');
        $data['file_valid_message'] = $event->get('file_valid_message');
        $data['man_message']        = $event->get('man_message');
        $data['answer']             = $event->get('answers');
        $data['help']['text']       = $event->get('help');
        $data['help']['show']       = flattenText($data['help']['text'], true, true) != '';
        $data['attributes']         = CHtml::renderAttributes(array_merge((array) $event->get('aHtmlOptions'), ['id' => "question{$data['qid']}"]));

        return $data;
    }
    /**
     * Retreive the question classes for a given question id
     *
     * @param  int      $iQid the question id
     * @return string   the classes
     */
    public function getCurrentQuestionClasses($iQid)
    {
        $lemQuestionInfo = LimeExpressionManager::GetQuestionStatus($iQid);
        $sType           = $lemQuestionInfo['info']['type'];
        $aQuestionClass  = Question::getQuestionClass($sType);

        /* Add the relevance class */
        if (!$lemQuestionInfo['relevant']) {
            $aQuestionClass .= ' ls-irrelevant';
            $aQuestionClass .= ' ls-hidden';
        }

        /* Can use aQuestionAttributes too */
        if ($lemQuestionInfo['hidden']) {
            $aQuestionClass .= ' ls-hidden-attribute'; /* another string ? */
            $aQuestionClass .= ' ls-hidden';
        }

        if ($lemQuestionInfo['info']['mandatory'] == 'Y') {
            $aQuestionClass .= ' mandatory';
        }

        if ($lemQuestionInfo['anyUnanswered'] && $_SESSION[$this->LEMsessid]['maxstep'] != $_SESSION[$this->LEMsessid]['step']) {
            $aQuestionClass .= ' missing';
        }



        $aQuestionAttributes = QuestionAttribute::model()->getQuestionAttributes($iQid);

        //add additional classes
        if (isset($aQuestionAttributes['cssclass']) && $aQuestionAttributes['cssclass'] != "") {
            /* Got to use static expression */
            $emCssClass = trim(LimeExpressionManager::ProcessString($aQuestionAttributes['cssclass'], null, array(), 1, 1, false, false, true)); /* static var is the last one ...*/
            if ($emCssClass != "") {
                $aQuestionClass .= " ".CHtml::encode($emCssClass);
            }
        }

        return $aQuestionClass;
    }
}
