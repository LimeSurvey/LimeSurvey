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

class SurveyRuntimeHelper {

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

    // Template datas
    private $oTemplate;                                                         // Template configuration object (set in model TemplateConfiguration)
    private $sTemplateViewPath;                                                 // Path of the layout files in template

    // LEM Datas
    private $LEMsessid;
    private $LEMdebugLevel          = 0;                                        // customizable debugging for Lime Expression Manager ; LEM_DEBUG_TIMING;   (LEM_DEBUG_TIMING + LEM_DEBUG_VALIDATION_SUMMARY + LEM_DEBUG_VALIDATION_DETAIL);
    private $LEMskipReprocessing    = false;                                    // true if used GetLastMoveResult to avoid generation of unneeded extra JavaScript

    // Survey settings:
    // TODO: To respect object oriented design, all those "states" should be move to SurveyDynamic model, or its related models via relations.
    // The only private variable here should be $oSurvey.
    private $aSurveyInfo;                                                       // Array returned by common_helper::getSurveyInfo(); (survey row + language settings );
    private $iSurveyid              = null;                                     // The survey id
    private $bShowEmptyGroup        = false;                                    // True only when $_SESSION[$this->LEMsessid]['step'] == 0 ; Just a variable for a logic step ==> should not be a Class variable (for now, only here for the redata== get_defined_vars mess)
    private $sSurveyMode;                                                       // {Group By Group,  All in one, Question by question}
    private $aSurveyOptions;                                                    // Few options comming from thissurvey, App->getConfig, LEM. Could be replaced by $oSurvey + relations ; the one coming from LEM and getConfig should be public variable on the surveyModel, set via public methods (active, allowsave, anonymized, assessments, datestamp, deletenonvalues, ipaddr, radix, refurl, savetimings, surveyls_dateformat, startlanguage, target, tempdir,timeadjust)
    private $sLangCode;                                                         // Current language code

    // moves
    private $aMoveResult            = false;                                     // Contains the result of LimeExpressionManager::JumpTo() OR LimeExpressionManager::NavigateBackwards() OR NavigateForwards::LimeExpressionManager(). TODO: create a function LimeExpressionManager::MoveTo that call the right method
    private $move                   = null;                                     // The move requested by user. Set by frontend_helper::getMove() from the POST request.
    private $invalidLastPage        = false;                                    // Just a variable used to check if user submitted a survey while it's not finished. Just a variable for a logic step ==> should not be a Class variable (for now, only here for the redata== get_defined_vars mess)
    private $stepInfo;

    // Popups: HTML of popus. If they are null, no popup. If they contains a string, a popup will be shown to participant.
    // They could probably be merged.
    private $backpopup              = false;                                    // "Please use the LimeSurvey navigation buttons or index.  It appears you attempted to use the browser back button to re-submit a page."
    private $popup                  = false;                                    // savedcontrol, mandatory_popup
    private $notvalidated;                                                      // question validation error

    // response
    // TODO:  To respect object oriented design, all those "states" should be move to Response model, or its related models via relations.
    private $oResponse;                                                         // An instance of the response model.
    private $notanswered;                                                       // A global variable...Should be $oResponse->notanswered
    private $unansweredSQList;                                                  // A list of the unanswered responses created via the global variable $notanswered. Should be $oResponse->unanswereds
    private $invalidSQList;                                                     // Invalid answered, fed from $moveResult(LEM). Its logic should be in Response model.
    private $filenotvalidated       = false;                                    // Same, but specific to file question type. (seems to be problematic by the past)

    // strings
    private $completed;                                                         // The string containing the completed message
    private $blocks;                                                            // Divs containing the HTML generated by plugins trying to override quanda_helper

    // Boolean helpers
    private $okToShowErrors;                                                    // true if we must show error in page : it's a submited ($_POST) page and show the same page again for some reason


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
    public function run($surveyid,$args)
    {
        // Survey settings
        $this->setSurveySettings( $surveyid, $args);                            // All the results of those settings will be available in this function later with $this->getArgs();

        // Start rendering
        $this->makeLanguageChanger();                                           //  language changer can be used on any entry screen, so it must be set first

        extract($args);                                                         // TODO: Check if still needed at this level

        $this->param = $param;

        ///////////////////////////////////////////////////////////
        // 1: We check if token and/or captcha form shouls be shown
        if (!isset($_SESSION[$this->LEMsessid]['step'])){
            $this->showTokenOrCaptchaFormsIfNeeded();
        }

        if ( !$this->previewgrp && !$this->previewquestion){
            $this->initMove();                                                  // main methods to init session, LEM, moves, errors, etc
            $this->checkQuotas();                                               // check quotas (then the process will stop here)
            $this->displayFirstPageIfNeeded();
            $this->saveAllIfNeeded();
            $this->saveSubmitIfNeeded();

            // TODO: move somewhere else
            $this->setNotAnsweredAndNotValidated();

        }else{
            $this->setPreview();
        }

        $this->moveSubmitIfNeeded();
        $this->setGroup();

        $this->fixMaxStep();

        // IF GOT THIS FAR, THEN DISPLAY THE ACTIVE GROUP OF QUESTIONSs
        $aPrivateVariables = $this->getArgs();
        extract($aPrivateVariables);

        //******************************************************************************************************
        //PRESENT SURVEY
        //******************************************************************************************************

        $this->okToShowErrors = $okToShowErrors = (!($this->previewgrp || $this->previewquestion) && ($this->invalidLastPage || $_SESSION[$this->LEMsessid]['prevstep'] == $_SESSION[$this->LEMsessid]['step']));

        Yii::app()->getController()->loadHelper('qanda');
        setNoAnswerMode($this->aSurveyInfo);

        //Iterate through the questions about to be displayed:
        $inputnames = array();
        $vpopup     = $fpopup = false;

        foreach ($_SESSION[$this->LEMsessid]['grouplist'] as $gl){
            $gid     = $gl['gid'];
            $qnumber = 0;

            if ($this->sSurveyMode != 'survey'){
                $onlyThisGID = $stepInfo['gid'];
                if ($onlyThisGID != $gid){
                    continue;
                }
            }

            //// To diplay one question, all the questions are processed ?
            $qanda = array();
            $upload_file = false;
            foreach ($_SESSION[$this->LEMsessid]['fieldarray'] as $key => $ia){
                ++$qnumber;
                $ia[9] = $qnumber; // incremental question count;

                // Make $qanda only for needed question $ia[10] is the randomGroup and $ia[5] the real group
                if ((isset($ia[10]) && $ia[10] == $gid) || (!isset($ia[10]) && $ia[5] == $gid)){

                    if ($this->sSurveyMode == 'question' && $ia[0] != $stepInfo['qid']){
                        continue;
                    }

                    $qidattributes = getQuestionAttributeValues($ia[0]);

                    if ($ia[4] != '*' && ($qidattributes === false || !isset($qidattributes['hidden']) || $qidattributes['hidden'] == 1)){
                        continue;
                    }

                    //Get the answers/inputnames
                    // TMSW - can content of retrieveAnswers() be provided by LEM?  Review scope of what it provides.
                    // TODO - retrieveAnswers is slow - queries database separately for each question. May be fixed in _CI or _YII ports, so ignore for now
                    list($plus_qanda, $plus_inputnames) = retrieveAnswers($ia, $this->iSurveyid);

                    if ($plus_qanda){
                        $plus_qanda[] = $ia[4];
                        $plus_qanda[] = $ia[6]; // adds madatory identifyer for adding mandatory class to question wrapping div

                        // Add a finalgroup in qa array , needed for random attribute : TODO: find a way to have it in new quanda_helper in 2.1
                        if(isset($ia[10]))
                            $plus_qanda['finalgroup']=$ia[10];
                        else
                            $plus_qanda['finalgroup']=$ia[5];

                        $qanda[] = $plus_qanda;
                    }
                    if ($plus_inputnames){
                        $inputnames = addtoarray_single($inputnames, $plus_inputnames);
                    }

                    //Display the "mandatory" popup if necessary
                    // TMSW - get question-level error messages - don't call **_popup() directly
                    if ($okToShowErrors && $stepInfo['mandViolation']){
                        list($mandatorypopup, $this->popup) = mandatory_popup($ia, $notanswered);
                    }

                    //Display the "validation" popup if necessary
                    if ($okToShowErrors && !$stepInfo['valid']){
                        list($validationpopup, $vpopup) = validation_popup($ia, $notvalidated);
                    }

                    // Display the "file validation" popup if necessary
                    if ($okToShowErrors && ($this->filenotvalidated !== false) ){
                        list($filevalidationpopup, $fpopup) = file_validation_popup($ia, $this->filenotvalidated);
                    }
                }

                if ($ia[4] == "|")
                    $upload_file = true;
            } //end iteration
        }

        if ($this->sSurveyMode != 'survey' && isset($this->aSurveyInfo['showprogress']) && $this->aSurveyInfo['showprogress'] == 'Y'){

            if ($this->bShowEmptyGroup){
                $this->aSurveyInfo['progress']['currentstep'] = $_SESSION[$this->LEMsessid]['totalsteps'] + 1;
                $this->aSurveyInfo['progress']['total']       = $_SESSION[$this->LEMsessid]['totalsteps'];
            }else{
                $this->aSurveyInfo['progress']['currentstep'] = $_SESSION[$this->LEMsessid]['step'];
                $this->aSurveyInfo['progress']['total']       = $_SESSION[$this->LEMsessid]['totalsteps'];
            }
        }

        $this->aSurveyInfo['yiiflashmessages'] = Yii::app()->user->getFlashes();

        /**
         * create question index only in SurveyRuntime, not needed elsewhere, add it to GlobalVar : must be always set even if empty
         *
         */
        $this->aSurveyInfo['aQuestionIndex']['bShow'] = false;

        if ($this->aSurveyInfo['questionindex']){
            if(!$this->previewquestion && !$this->previewgrp){
                $this->aSurveyInfo['aQuestionIndex']['items'] = ls\helpers\questionIndexHelper::getInstance()->getIndexItems();

                if($this->aSurveyInfo['questionindex'] > 1){
                    $this->aSurveyInfo['aQuestionIndex']['type'] = 'full';
                }else{
                    $this->aSurveyInfo['aQuestionIndex']['type'] = 'incremental';
                }
            }

            if ( count($this->aSurveyInfo['aQuestionIndex']['items']) > 0){
                $this->aSurveyInfo['aQuestionIndex']['bShow'] = true;
            }
        }

        sendCacheHeaders();

        Yii::app()->loadHelper('surveytranslator');

        // Set Langage // TODO remove one of the Yii::app()->session see bug #5901
        if (Yii::app()->session['survey_'.$this->iSurveyid]['s_lang'] ){
            $languagecode =  Yii::app()->session['survey_'.$this->iSurveyid]['s_lang'];
        }elseif ($this->iSurveyid  && Survey::model()->findByPk($this->iSurveyid)){
            $languagecode = Survey::model()->findByPk($this->iSurveyid)->language;
        }else{
            $languagecode = Yii::app()->getConfig('defaultlang');
        }

        $this->aSurveyInfo['languagecode'] = $languagecode;
        $this->aSurveyInfo['dir']          = (getLanguageRTL($languagecode))?"rtl":"ltr";
        $this->aSurveyInfo['upload_file']  = $upload_file;
        $hiddenfieldnames           = $this->aSurveyInfo['hiddenfieldnames']  = implode("|", $inputnames);

        Yii::app()->clientScript->registerScriptFile(Yii::app()->getConfig("generalscripts").'nojs.js',CClientScript::POS_HEAD);

        // Show question code/number
        $this->aSurveyInfo['aShow'] = $this->getShowNumAndCode();

        $aPopup=array(); // We can move this part where we want now

        if ( $this->backpopup != false ){
            $aPopup[]=$this->backpopup;// If user click reload: no need other popup
        }else{

            if ( $this->popup != false ){
                $aPopup[] = $this->popup;
            }

            if ( $vpopup != false ){
                $aPopup[]=$vpopup;
            }

            if ( $fpopup != false ){
                $aPopup[]=$fpopup;
            }
        }

        $this->aSurveyInfo['jPopup'] = json_encode($aPopup);

        $bShowpopups                            = Yii::app()->getConfig('showpopups');
        $aErrorHtmlMessage                      = $this->getErrorHtmlMessage();
        $this->aSurveyInfo['errorHtml']['show']        = !empty($aErrorHtmlMessage);
        $this->aSurveyInfo['errorHtml']['hiddenClass'] = $bShowpopups ? "ls-js-hidden ":"";
        $this->aSurveyInfo['errorHtml']['messages']    = $aErrorHtmlMessage;

        $_gseq = -1;

        foreach ($_SESSION[$this->LEMsessid]['grouplist'] as $gl){

            ++$_gseq;

            $gid              = $gl['gid'];
            $aGroup           = array();
            $groupname        = $gl['group_name'];
            $groupdescription = $gl['description'];

            if ($this->sSurveyMode != 'survey' && $gid != $onlyThisGID){
                continue;
            }

            Yii::app()->setConfig('gid',$gid);// To be used in templaterplace in whole group. Attention : it's the actual GID (not the GID of the question)

            $aGroup['class'] = "";
            $gnoshow         = LimeExpressionManager::GroupIsIrrelevantOrHidden($_gseq);
            $redata          = compact(array_keys(get_defined_vars()));

            if  ($gnoshow && !$this->previewgrp){
                $aGroup['class'] = ' ls-hidden';
            }

            $aGroup['name']        = $gl['group_name'];
            $aGroup['gseq']        = $_gseq;
            $showgroupinfo_global_ = getGlobalSetting('showgroupinfo');
            $aSurveyinfo           = getSurveyInfo($this->iSurveyid);

            // Look up if there is a global Setting to hide/show the Questiongroup => In that case Globals will override Local Settings
            if(($aSurveyinfo['showgroupinfo'] == $showgroupinfo_global_) || ($showgroupinfo_global_ == 'choose')){
                $showgroupinfo_ = $aSurveyinfo['showgroupinfo'];
            } else {
                $showgroupinfo_ = $showgroupinfo_global_;
            }

            $showgroupdesc_ = $showgroupinfo_ == 'B' /* both */ || $showgroupinfo_ == 'D'; /* (group-) description */

            $aGroup['showdescription']  = (!$this->previewquestion && trim($redata['groupdescription'])!="" && $showgroupdesc_);
            $aGroup['description']      = $redata['groupdescription'];

            // one entry per QID
            foreach ($qanda as $qa) {

                // Test if finalgroup is in this qid (for all in one survey, else we do only qanda for needed question (in one by one or group by goup)
                if ($gid != $qa['finalgroup']) {
                    continue;
                }

                $qid             = $qa[4];
                $qinfo           = LimeExpressionManager::GetQuestionStatus($qid);
                $lemQuestionInfo = LimeExpressionManager::GetQuestionStatus($qid);
                $lastgrouparray  = explode("X", $qa[7]);
                $lastgroup       = $lastgrouparray[0] . "X" . $lastgrouparray[1]; // id of the last group, derived from question id
                $lastanswer      = $qa[7];
                $n_q_display     = '';

                if ($qinfo['hidden'] && $qinfo['info']['type'] != '*'){
                    continue; // skip this one
                }

                $aReplacement = array();
                $question     = $qa[0];

                //===================================================================
                // The following four variables offer the templating system the
                // capacity to fully control the HTML output for questions making the
                // above echo redundant if desired.
                $question['sgq']  = $qa[7];
                $question['aid']  = !empty($qinfo['info']['aid']) ? $qinfo['info']['aid'] : 0;
                $question['sqid'] = !empty($qinfo['info']['sqid']) ? $qinfo['info']['sqid'] : 0;
                //===================================================================

                // easier to understand for survey maker
                $aGroup['aQuestions'][$qid]['qid']                  = $qa[4];
                $aGroup['aQuestions'][$qid]['code']                 = $qa[5];
                $aGroup['aQuestions'][$qid]['number']               = $qa[0]['number'];
                $aGroup['aQuestions'][$qid]['text']                 = LimeExpressionManager::ProcessString($qa[0]['text'], $qa[4], NULL, false, 3, 1, false, true, false);
                $aGroup['aQuestions'][$qid]['SGQ']                  = $qa[7];
                $aGroup['aQuestions'][$qid]['mandatory']            = $qa[0]['mandatory'];
                $aGroup['aQuestions'][$qid]['input_error_class']    = $qa[0]['input_error_class'];
                $aGroup['aQuestions'][$qid]['valid_message']        = $qa[0]['valid_message'];
                $aGroup['aQuestions'][$qid]['file_valid_message']   = $qa[0]['file_valid_message'];
                $aGroup['aQuestions'][$qid]['man_message']          = $qa[0]['man_message'];
                $aGroup['aQuestions'][$qid]['answer']               = $qa[1];
                $aGroup['aQuestions'][$qid]['help']['show']         = (flattenText( $lemQuestionInfo['info']['help'], true,true) != '');
                $aGroup['aQuestions'][$qid]['help']['text']         = LimeExpressionManager::ProcessString($lemQuestionInfo['info']['help'], $qa[4], NULL, false, 3, 1, false, true, false);
            }

            $aGroup['show_last_group']   = $aGroup['show_last_answer']  = false;
            $aGroup['lastgroup']         = $aGroup['lastanswer']        = '';

            if (!empty($qanda)){

                if ($this->sSurveyMode == 'group') {
                    $aGroup['show_last_group']   = true;
                    $aGroup['lastgroup']         = $lastgroup;
                }

                if ($this->sSurveyMode == 'question') {
                    $aGroup['show_last_answer']   = true;
                    $aGroup['lastanswer']         = $lastanswer;
                }
            }

            Yii::app()->setConfig('gid','');

            $this->aSurveyInfo['aGroups'][$gid] = $aGroup;
        }


        LimeExpressionManager::FinishProcessingGroup($this->LEMskipReprocessing);
        $this->aSurveyInfo['EM']['ScriptsAndHiddenInputs'] = LimeExpressionManager::GetRelevanceAndTailoringJavaScript();
        Yii::app()->clientScript->registerScript('triggerEmRelevance',"triggerEmRelevance();",CClientScript::POS_END);
        /* Maybe only if we have mandatory error ?*/
        Yii::app()->clientScript->registerScript('updateMandatoryErrorClass',"updateMandatoryErrorClass();",CClientScript::POS_END);
        LimeExpressionManager::FinishProcessingPage();

        /**
        * Navigator
        */
        $this->aSurveyInfo['aNavigator']         = array();
        $this->aSurveyInfo['aNavigator']['show'] = $aNavigator['show'] = $this->aSurveyInfo['aNavigator']['save']['show'] = $this->aSurveyInfo['aNavigator']['load']['show'] = false;

        if (!$this->previewgrp && !$this->previewquestion){
            $this->aSurveyInfo['aNavigator']            = getNavigatorDatas();
            $this->aSurveyInfo['hiddenInputs']          = "<input type='hidden' name='thisstep' value='{$_SESSION[$this->LEMsessid]['step']}' id='thisstep' />\n";
            $this->aSurveyInfo['hiddenInputs']         .= "<input type='hidden' name='sid' value='$this->iSurveyid' id='sid' />\n";
            $this->aSurveyInfo['hiddenInputs']         .= "<input type='hidden' name='start_time' value='" . time() . "' id='start_time' />\n";
            $_SESSION[$this->LEMsessid]['LEMpostKey']  = mt_rand();
            $this->aSurveyInfo['hiddenInputs']         .= "<input type='hidden' name='LEMpostKey' value='{$_SESSION[$this->LEMsessid]['LEMpostKey']}' id='LEMpostKey' />\n";

            global $token;
            if ($token){
                $this->aSurveyInfo['hiddenInputs'] .=  "\n<input type='hidden' name='token' value='$token' id='token' />\n";
            }
        }

        // For "clear all" buttons
        $this->aSurveyInfo['jYesNo'] = ls_json_encode(array('yes'=>gT("Yes"),'no'=>gT("No")));

        $this->aSurveyInfo['aLEM']['debugtimming']['show'] = false;

        if (($this->LEMdebugLevel & LEM_DEBUG_TIMING) == LEM_DEBUG_TIMING){
            $this->aSurveyInfo['aLEM']['debugtimming']['show']   = true;
            $this->aSurveyInfo['aLEM']['debugtimming']['script'] = LimeExpressionManager::GetDebugTimingMessage();
        }

        $this->aSurveyInfo['aLEM']['debugvalidation']['show'] = false;

        if (($this->LEMdebugLevel & LEM_DEBUG_VALIDATION_SUMMARY) == LEM_DEBUG_VALIDATION_SUMMARY){
            $this->aSurveyInfo['aLEM']['debugvalidation']['show']    = true;
            $this->aSurveyInfo['aLEM']['debugvalidation']['message'] = $this->aMoveResult['message'];
        }

        Yii::app()->twigRenderer->renderTemplateFromString( file_get_contents($this->sTemplateViewPath."layout_main.twig"), array('aSurveyInfo'=>$this->aSurveyInfo), false);
    }


    public function getShowNumAndCode()
    {

        $showqnumcode_global_ = getGlobalSetting('showqnumcode');
        $showqnumcode_survey_ = $this->aSurveyInfo['showqnumcode'];

        // Check global setting to see if survey level setting should be applied
        if($showqnumcode_global_ == 'choose') { // Use survey level settings
            $showqnumcode_ = $showqnumcode_survey_; //B, N, C, or X
        } else {
            // Use global setting
            $showqnumcode_ = $showqnumcode_global_; //both, number, code, or none
        }

        switch ($showqnumcode_){
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
        $this->initFirstStep();                                                 // If it's the first time user load this survey, will init session and LEM
        $this->initTotalAndMaxSteps();
        $this->checkIfUseBrowserNav();                                          // Check if user used browser navigation, or relaoded page

        if ($this->move != 'clearcancel' && $this->move != 'confirmquota'){
            $this->checkPrevStep();                                                 // Check if prev step is set, else set it
            $this->setMoveResult();
            $this->checkClearCancel();
            $this->setPrevStep();
            $this->checkIfFinished();
            $this->setStep();

            // CHECK UPLOADED FILES
            // TMSW - Move this into LEM::NavigateForwards?
            $this->filenotvalidated = checkUploadedFileValidity($this->iSurveyid, $this->move);

            //SEE IF THIS GROUP SHOULD DISPLAY
            if ($_SESSION[$this->sTemplateViewPath]['step'] == 0) {
                $this->bShowEmptyGroup = true;
            }

            // For welcome screen
            $this->thissurvey['iTotalquestions']   = $_SESSION['survey_'.$this->iSurveyid]['totalquestions'];
            $showxquestions                        = Yii::app()->getConfig('showxquestions');
            $this->thissurvey['bShowxquestions']   = ( $showxquestions == 'show' || ($showxquestions == 'choose' && !isset($this->aSurveyInfo['showxquestions'])) || ($showxquestions == 'choose' && $this->aSurveyInfo['showxquestions'] == 'Y'));
        }

    }

    /**
     * Return an array containing all the private variable, for easy extraction.
     * It makes easier to move piece of code to methods dispite the use of $redata = compact(array_keys(get_defined_vars()));
     *
     * @return array
     */
    private function getArgs()
    {
        $aPrivateVariables = array(
            'thissurvey'             => $this->thissurvey             ,
            'surveyid '              => $this->iSurveyid               ,
            'show_empty_group'       => $this->bShowEmptyGroup       ,
            'surveyMode'             => $this->sSurveyMode             ,
            'surveyOptions'          => $this->aSurveyOptions          ,
            'moveResult'             => $this->aMoveResult             ,
            'move'                   => $this->move                   ,
            'stepInfo'               => $this->stepInfo               ,
            'invalidLastPage'        => $this->invalidLastPage        ,
            'popup'                  => $this->popup                  ,
            'oResponse'              => $this->oResponse              ,
            'unansweredSQList'       => $this->unansweredSQList       ,
            'notanswered'            => $this->notanswered            ,
            'invalidSQList'          => $this->invalidSQList          ,
            'filenotvalidated'       => $this->filenotvalidated       ,
            'completed'              => $this->completed              ,
            'blocks'                 => $this->blocks                 ,
            'notvalidated'           => $this->notvalidated           ,
            'gid'                    => $this->gid                    ,
            'groupname'              => $this->groupname              ,
            'groupdescription'       => $this->groupdescription       ,
            'previewgrp'             => $this->previewgrp             ,
            'previewquestion'        => $this->previewquestion        ,
            'param'                  => $this->param                  ,
        );
        return $aPrivateVariables;
    }

    /**
     * Now it's ok ^^
     */
    private function setArgs()
    {
        if ($move == "movesubmit"){

            if ($this->aSurveyInfo['refurl'] == "Y"){
                //Only add this if it doesn't already exist
                if (!in_array("refurl", $_SESSION[$this->LEMsessid]['insertarray'])){
                    $_SESSION[$this->LEMsessid]['insertarray'][] = "refurl";
                }
            }

            resetTimers();

            //Before doing the "templatereplace()" function, check the $this->aSurveyInfo['url']
            //field for limereplace stuff, and do transformations!
            $this->aSurveyInfo['surveyls_url'] = passthruReplace($this->aSurveyInfo['surveyls_url'], $this->aSurveyInfo);
            $this->aSurveyInfo['surveyls_url'] = templatereplace($this->aSurveyInfo['surveyls_url'], array(), $redata, 'URLReplace', false, NULL, array(), true );   // to do INSERTANS substitutions


            //THE FOLLOWING DEALS WITH SUBMITTING ANSWERS AND COMPLETING AN ACTIVE SURVEY
            //don't use cookies if tokens are being used
            if ($this->aSurveyInfo['active'] == "Y"){
                global $tokensexist;
                if ($this->aSurveyInfo['usecookie'] == "Y" && $tokensexist != 1) {
                    setcookie("LS_" . $this->iSurveyid . "_STATUS", "COMPLETE", time() + 31536000); //Cookie will expire in 365 days
                }
            }
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
        switch ($this->aSurveyInfo['format'])
        {
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
            'refurl'                      => (($this->aSurveyInfo['refurl'] == "Y" && isset($_SESSION[$this->LEMsessid]['refurl'])) ? $_SESSION[$this->LEMsessid]['refurl'] : NULL),
            'savetimings'                 => ($this->aSurveyInfo['savetimings'] == "Y"),
            'surveyls_dateformat'         => ( ($timeadjust!=0) ? $this->aSurveyInfo['surveyls_dateformat'] : 1),
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
        if (!isset($_SESSION[$this->LEMsessid]['step']))
        {
            // Init session, randomization and filed array
            buildsurveysession($this->iSurveyid);
            randomizationGroupsAndQuestions($this->iSurveyid);

            // Check surveyid coherence
            if($this->iSurveyid != LimeExpressionManager::getLEMsurveyId())
                LimeExpressionManager::SetDirtyFlag();

            // Init $LEM states.
            LimeExpressionManager::StartSurvey($this->iSurveyid, $this->sSurveyMode, $this->aSurveyOptions, false, $this->LEMdebugLevel);
            $_SESSION[$this->LEMsessid]['step'] = 0;

            // Welcome page.
            if ($this->sSurveyMode == 'survey'){
                LimeExpressionManager::JumpTo(1, false, false, true);
            }elseif (isset($this->aSurveyInfo['showwelcome']) && $this->aSurveyInfo['showwelcome'] == 'N'){
                $this->aMoveResult = LimeExpressionManager::NavigateForwards();
                $_SESSION[$this->LEMsessid]['step'] = 1;
            }
        }elseif($this->iSurveyid != LimeExpressionManager::getLEMsurveyId()){
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
        $_SESSION[$this->LEMsessid]['step']   = $_SESSION[$this->LEMsessid]['step']<0 ? 0 : $_SESSION[$this->LEMsessid]['step'];
        LimeExpressionManager::StartSurvey($this->iSurveyid, $this->sSurveyMode, $this->aSurveyOptions, false, $this->LEMdebugLevel);
        LimeExpressionManager::JumpTo($_SESSION[$this->LEMsessid]['step'], false, false);
    }

    /**
     * Seems to be a quick fix to avoid the total and max steps to be null...
     */
    private function initTotalAndMaxSteps()
    {

        if (!isset($_SESSION[$this->LEMsessid]['totalsteps'])){
            $_SESSION[$this->LEMsessid]['totalsteps'] = 0;
        }

        if (!isset($_SESSION[$this->LEMsessid]['maxstep'])){
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
        if (isset($_SESSION[$this->LEMsessid]['LEMpostKey']) && App()->request->getPost('LEMpostKey',$_SESSION[$this->LEMsessid]['LEMpostKey']) != $_SESSION[$this->LEMsessid]['LEMpostKey']){
            // then trying to resubmit (e.g. Next, Previous, Submit) from a cached copy of the page
            $this->aMoveResult = LimeExpressionManager::JumpTo($_SESSION[$this->LEMsessid]['step'], false, false, true);// We JumpTo current step without saving: see bug #11404

            if (isset($this->aMoveResult['seq']) &&  App()->request->getPost('thisstep',$this->aMoveResult['seq']) == $this->aMoveResult['seq']){

                /* then pressing F5 or otherwise refreshing the current page, which is OK
                 * Seems OK only when movenext but not with move by index : same with $this->aMoveResult = LimeExpressionManager::GetLastMoveResult(true);
                 */
                $this->LEMskipReprocessing =  true;
                $this->move                = "movenext"; // so will re-display the survey
            }else{
                // trying to use browser back buttons, which may be disallowed if no 'previous' button is present
                $this->LEMskipReprocessing = true;
                $this->move                = "movenext"; // so will re-display the survey
                $this->invalidLastPage     = true;
                $this->backpopup           =  gT("Please use the LimeSurvey navigation buttons or index.  It appears you attempted to use the browser back button to re-submit a page.");    // TODO: twig
            }
        }
    }

    /**
     * Check quotas
     */
    private function checkQuotas()
    {
        $move          = $this->move;

        /* quota submitted */
        if ( $move=='confirmquota'){
            checkCompletedQuota($this->iSurveyid);
        }
    }

    /**
     * Check if the move is clearcancel or confirmquota
     */
    private function checkClearCancel()
    {
        $move          = $this->move;

        if ( $move=="clearcancel"){
            $this->aMoveResult = LimeExpressionManager::JumpTo($_SESSION[$this->LEMsessid]['step'], false, false);
        }
    }

    /**
     * Set prev step in session depending on move type
     */
    private function setPrevStep()
    {
        $_SESSION[$this->LEMsessid]['prevstep'] = (!in_array($this->move,array("clearall","changelang","saveall","reload")))?$_SESSION[$this->LEMsessid]['step']:$this->move; // Accepted $move without error
    }

    /**
     * Define prev step if not set in session.
     */
    private function checkPrevStep()
    {

        if (!isset($_SESSION[$this->LEMsessid]['prevstep'])){
            $_SESSION[$this->LEMsessid]['prevstep'] = $_SESSION[$this->LEMsessid]['step']-1;   // this only happens on re-load
        }
    }

    /**
     * Set the moveResult variable, depending on the user move request
     */
    private function setMoveResult()
    {

        // retrieve datas from local variable
        $move                   = $this->move;
        $this->aMoveResult       = false;

        if (isset($_SESSION[$this->LEMsessid]['LEMtokenResume'])){

            LimeExpressionManager::StartSurvey($this->aSurveyInfo['sid'], $this->sSurveyMode, $this->aSurveyOptions, false, $this->LEMdebugLevel);

            // Do it only if needed : we don't need it if we don't have index
            if(isset($_SESSION[$this->LEMsessid]['maxstep']) && $_SESSION[$this->LEMsessid]['maxstep']>$_SESSION[$this->LEMsessid]['step'] && $this->aSurveyInfo['questionindex'] ){
                LimeExpressionManager::JumpTo($_SESSION[$this->LEMsessid]['maxstep'], false, false);
            }

            $this->aMoveResult =  LimeExpressionManager::JumpTo($_SESSION[$this->LEMsessid]['step'],false,false);   // if late in the survey, will re-validate contents, which may be overkill

            unset($_SESSION[$this->LEMsessid]['LEMtokenResume']);
        }else if (!$this->LEMskipReprocessing){

            //Move current step ###########################################################################
            if ($move == 'moveprev' && ($this->aSurveyInfo['allowprev'] == 'Y' || $this->aSurveyInfo['questionindex'] > 0)){
                $this->aMoveResult = LimeExpressionManager::NavigateBackwards();

                if ($this->aMoveResult['at_start']){
                    $_SESSION[$this->LEMsessid]['step'] = 0;
                    unset($this->aMoveResult); // so display welcome page again
                }
            }

            if ( $move == "movenext"){
                $this->aMoveResult = LimeExpressionManager::NavigateForwards();
            }

            if (($move == 'movesubmit')){
                if ($this->sSurveyMode == 'survey'){
                    $this->aMoveResult =  LimeExpressionManager::NavigateForwards();
                }else{
                    // may be submitting from the navigation bar, in which case need to process all intervening questions
                    // in order to update equations and ensure there are no intervening relevant mandatory or relevant invalid questions
                    if($this->aSurveyInfo['questionindex']==2) // Must : save actual page , review whole before set finished to true (see #09906), index==1 seems to don't need it : (don't force move)
                        LimeExpressionManager::StartSurvey($this->iSurveyid, $this->sSurveyMode, $this->aSurveyOptions);

                    $this->aMoveResult = LimeExpressionManager::JumpTo($_SESSION[$this->LEMsessid]['totalsteps'] + 1, false);
                }
            }
            if ( $move=='clearall'){
                $this->manageClearAll();
            }
            if ( $move=='changelang'){
                // jump to current step using new language, processing POST values
                $this->aMoveResult = LimeExpressionManager::JumpTo($_SESSION[$this->LEMsessid]['step'], false, true, true, true);  // do process the POST data
            }

            if (isNumericInt($move) && $this->aSurveyInfo['questionindex'] == 1){
                $move = $this->move = (int) $move;

                if ($move > 0 && (($move <= $_SESSION[$this->LEMsessid]['step']) || (isset($_SESSION[$this->LEMsessid]['maxstep']) && $move <= $_SESSION[$this->LEMsessid]['maxstep']))){
                    $this->aMoveResult = LimeExpressionManager::JumpTo($move, false);
                }
            }
            elseif ( isNumericInt($move) && $this->aSurveyInfo['questionindex'] == 2){
                $move       = $this->move       = (int) $move;
                $this->aMoveResult = LimeExpressionManager::JumpTo($move, false, true, true);
            }

            if ( ! $this->aMoveResult && !($this->sSurveyMode != 'survey' && $_SESSION[$this->LEMsessid]['step'] == 0)){
                // Just in case not set via any other means, but don't do this if it is the welcome page
                $this->aMoveResult          = LimeExpressionManager::GetLastMoveResult(true);
                $this->LEMskipReprocessing = true;
            }
        }
    }

    /**
     * Test if the the moveresult is finished, to decide to set the new $move value
     */
    private function checkIfFinished()
    {
        // retrieve datas from local
        $move          = $this->move;

        // Reload at first page (welcome after click previous fill an empty $this->aMoveResult array
        if ( $this->aMoveResult && isset($this->aMoveResult['seq']) ){
            // With complete index, we need to revalidate whole group bug #08806. It's actually the only mode where we JumpTo with force
            // we already done if move == 'movesubmit', don't do it again
            if($this->aMoveResult['finished'] == true && $move != 'movesubmit' && $this->thissurvey['questionindex']==2){
                //LimeExpressionManager::JumpTo(-1, false, false, true);
                LimeExpressionManager::StartSurvey($this->iSurveyid, $this->sSurveyMode, $this->aSurveyOptions);
                $this->aMoveResult = LimeExpressionManager::JumpTo($_SESSION[$this->LEMsessid]['totalsteps']+1, false, false, false);// no preview, no save data and NO force
                if(!$this->aMoveResult['mandViolation'] && $this->aMoveResult['valid'] && empty($this->aMoveResult['invalidSQs'])){
                    $this->aMoveResult['finished'] = true;
                }
            }

            if ($this->aMoveResult['finished'] == true){
                $move = $this->move = 'movesubmit';
            }

            if ($move == "movesubmit" && $this->aMoveResult['finished'] == false){
                // then there are errors, so don't finalize the survey
                $move            = $this->move            = "movenext"; // so will re-display the survey
                $invalidLastPage = $this->invalidLastPage = true;
            }
        }
    }

    /**
     * Increase step in session
     */
    private function setStep()
    {

        if ( $this->aMoveResult && isset($this->aMoveResult['seq']) ){
            if ($this->aMoveResult['finished'] != true){
                $_SESSION[$this->LEMsessid]['step'] = $this->aMoveResult['seq'] + 1;  // step is index base 1
                $stepInfo                     = $this->stepInfo =  LimeExpressionManager::GetStepIndexInfo($this->aMoveResult['seq']);
            }
        }
    }

    /**
     * Display the first page if needed
     */
    private function displayFirstPageIfNeeded()
    {
        // We do not keep the participant session anymore when the same browser is used to answer a second time a survey (let's think of a library PC for instance).
        // Previously we used to keep the session and redirect the user to the
        // submit page.
        if ($this->sSurveyMode != 'survey' && $_SESSION[$this->LEMsessid]['step'] == 0){
            $_SESSION[$this->LEMsessid]['test']=time();
            display_first_page($this->thissurvey);
            Yii::app()->end(); // So we can still see debug messages
        }
    }

    /**
     * Perform save all if user asked for it
     */
    private function saveAllIfNeeded()
    {

        // TODO FIXME
         // Don't test if save is allowed
        if ($this->aSurveyInfo['active'] == "Y" && Yii::app()->request->getPost('saveall')){
            $bTokenAnswerPersitance = $this->aSurveyInfo['tokenanswerspersistence'] == 'Y' && $this->iSurveyid!=null && tableExists('tokens_'.$this->iSurveyid);

            // must do this here to process the POSTed values
            $this->aMoveResult = LimeExpressionManager::JumpTo($_SESSION[$this->LEMsessid]['step'], false);   // by jumping to current step, saves data so far

            if (!isset($_SESSION[$this->LEMsessid]['scid']) && !$bTokenAnswerPersitance ){
                Yii::import("application.libraries.Save");
                $cSave = new Save();
                // $cSave->showsaveform($this->aSurveyInfo['sid']); // generates a form and exits, awaiting input
                $this->aSurveyInfo['aSaveForm'] = $cSave->getSaveFormDatas($this->aSurveyInfo['sid']);
                Yii::app()->twigRenderer->renderTemplateFromString( file_get_contents($this->sTemplateViewPath."layout_save.twig"), array('aSurveyInfo'=>$this->aSurveyInfo), false);
            }else{
                // Intentional retest of all conditions to be true, to make sure we do have tokens and surveyid
                // Now update lastpage to $_SESSION[$this->LEMsessid]['step'] in SurveyDynamic, otherwise we land on
                // the previous page when we return.
                $iResponseID         = $_SESSION[$this->LEMsessid]['srid'];
                $oResponse           = SurveyDynamic::model($this->iSurveyid)->findByPk($iResponseID);
                $oResponse->lastpage = $_SESSION[$this->LEMsessid]['step'];
                $oResponse->save();

                $this->oResponse = $oResponse;
            }
        }
    }

    /**
     * perform save submit if asked by user
     * called from save survey
     */
    private function saveSubmitIfNeeded()
    {
        if ($this->aSurveyInfo['active'] == "Y" && Yii::app()->request->getParam('savesubmit') ){
            // The response from the save form
            // CREATE SAVED CONTROL RECORD USING SAVE FORM INFORMATION
            Yii::import("application.libraries.Save");
            $cSave = new Save();

            // Try to save survey
            $aResult = $cSave->saveSurvey();
            if (!$aResult['success']){
                $aPopup  = $this->popup = $aResult['aSaveErrors'];
            }else{
                $aPopup  = $this->popup = array($aResult['message']);
            }

            Yii::app()->clientScript->registerScript('startPopup',"LSvar.startPopups=".json_encode($aPopup).";",CClientScript::POS_HEAD);
            Yii::app()->clientScript->registerScript('showStartPopups',"showStartPopups();",CClientScript::POS_END);

            // reshow the form if there is an error
            if (!empty($aResult['aSaveErrors'])){
                $this->aSurveyInfo['aSaveForm'] = $cSave->getSaveFormDatas($this->aSurveyInfo['sid']);
                Yii::app()->twigRenderer->renderTemplateFromString( file_get_contents($this->sTemplateViewPath."layout_save.twig"), array('aSurveyInfo'=>$this->aSurveyInfo), false);
            }

            $this->aMoveResult          = LimeExpressionManager::GetLastMoveResult(true);
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
        $this->notvalidated = $notanswered;

        if (!$this->aMoveResult['finished']){
            $unansweredSQList = $this->unansweredSQList = $this->aMoveResult['unansweredSQs'];
            if (strlen($unansweredSQList) > 0){
                $notanswered = $this->notanswered =explode('|', $unansweredSQList);
            }else{
                $notanswered = $this->notanswered = array();
            }
            //CHECK INPUT
            $invalidSQList = $this->invalidSQList = $this->aMoveResult['invalidSQs'];
            if (strlen($invalidSQList) > 0){
                $notvalidated = $this->notvalidated = explode('|', $invalidSQList);
            }else{
                $notvalidated = $this->notvalidated = array();
            }
        }
    }

    /**
     * Perform submit if asked by user
     */
    private function moveSubmitIfNeeded()
    {
        // retrieve datas from local variable
        $move              = $this->move;

        if ($move == "movesubmit"){

            if ($this->aSurveyInfo['active'] != "Y"){

                sendCacheHeaders();

                //Check for assessments
                if ($this->aSurveyInfo['assessments'] == "Y"){
                    $this->aSurveyInfo['aAssessments']['show'] = true;
                    $this->aSurveyInfo['aAssessments'] = doAssessment($this->iSurveyid, true);
                }

                $redata = compact(array_keys(get_defined_vars()));
                // can't kill session before end message, otherwise INSERTANS doesn't work.
                $completed  = templatereplace($this->aSurveyInfo['surveyls_endtext'], array(), $redata, 'SubmitEndtextI', false, NULL, array(), true );
                $this->completed = $completed;

                Yii::app()->twigRenderer->renderTemplateFromString( file_get_contents($this->sTemplateViewPath."layout_submit_preview.twig"), array('aSurveyInfo'=>$this->aSurveyInfo), false);
            }else{

                //Update the token if needed and send a confirmation email
                if (isset($_SESSION['survey_'.$this->iSurveyid]['token'])){
                    submittokens();
                }

                //Send notifications
                sendSubmitNotifications($this->iSurveyid);

                //Check for assessments
                $this->aSurveyInfo['aAssessments']['show'] = false;
                if ($this->aSurveyInfo['assessments'] == "Y"){
                    $this->aSurveyInfo['aAssessments']['show'] = true;
                    $this->aSurveyInfo['aAssessments'] = doAssessment($this->iSurveyid, true);
                }

                // End text
                if (trim(str_replace(array('<p>','</p>'),'',$this->aSurveyInfo['surveyls_endtext'])) == ''){
                    $this->aSurveyInfo['aCompleted']['showDefault']=true;
                }else{
                    $this->aSurveyInfo['aCompleted']['showDefault']=false;
                    // NOTE: this occurence of template replace should stay here. User from backend could use old replacement keyword
                    $this->aSurveyInfo['aCompleted']['sEndText'] = templatereplace($this->aSurveyInfo['surveyls_endtext'], array(), $redata, 'SubmitAssessment', false, NULL, array(), true );
                }

                // Link to Print Answer Preview  **********
                $this->aSurveyInfo['aCompleted']['aPrintAnswers']['show'] = false;
                if ($this->aSurveyInfo['printanswers'] == 'Y'){
                    $this->aSurveyInfo['aCompleted']['aPrintAnswers']['show']  = true;
                    $this->aSurveyInfo['aCompleted']['aPrintAnswers']['sUrl']  = Yii::app()->getController()->createUrl("/printanswers/view",array('surveyid'=>$this->iSurveyid));
                    $this->aSurveyInfo['aCompleted']['aPrintAnswers']['sText'] = "Print your answers.";
                }

                // Link to Public statistics  **********
                $this->aSurveyInfo['aCompleted']['aPublicStatistics']['show'] = false;
                if ($this->aSurveyInfo['publicstatistics'] == 'Y'){
                    $this->aSurveyInfo['aCompleted']['aPublicStatistics']['show']  = true;
                    $this->aSurveyInfo['aCompleted']['aPublicStatistics']['sUrl']  = Yii::app()->getController()->createUrl("/statistics_user/action/",array('surveyid'=>$this->iSurveyid,'language'=>App()->getLanguage()));

                }

                $this->completed = true;

                //*****************************************

                $_SESSION[$this->LEMsessid]['finished'] = true;
                $_SESSION[$this->LEMsessid]['sid']      = $this->iSurveyid;

                if (isset($this->aSurveyInfo['autoredirect']) && $this->aSurveyInfo['autoredirect'] == "Y" && $this->aSurveyInfo['surveyls_url']){
                    //Automatically redirect the page to the "url" setting for the survey
                    header("Location: {$this->aSurveyInfo['surveyls_url']}");
                }

            }

            $redata['completed'] = $this->completed;

            // @todo Remove direct session access.
            $event = new PluginEvent('afterSurveyComplete');

            if (isset($_SESSION[$this->LEMsessid]['srid'])){
                $event->set('responseId', $_SESSION[$this->LEMsessid]['srid']);
            }

            $event->set('surveyId', $this->iSurveyid);
            App()->getPluginManager()->dispatchEvent($event);
            $blocks = array();

            foreach ($event->getAllContent() as $blockData){
                /* @var $blockData PluginEventContent */
                $blocks[] = CHtml::tag('div', array('id' => $blockData->getCssId(), 'class' => $blockData->getCssClass()), $blockData->getContent());
            }


            $this->blocks = $blocks;
            $this->aSurveyInfo['aCompleted']['sPluginHTML'] = implode("\n", $blocks) ."\n";
            $this->aSurveyInfo['aCompleted']['sSurveylsUrl'] = $this->aSurveyInfo['surveyls_url'];


            $this->aSurveyInfo['aLEM']['debugvalidation']['show'] = false;
            if (($this->LEMdebugLevel & LEM_DEBUG_VALIDATION_SUMMARY) == LEM_DEBUG_VALIDATION_SUMMARY){
                $this->aSurveyInfo['aLEM']['debugvalidation']['show']   = true;
                $this->aSurveyInfo['aLEM']['debugvalidation']['message'] = $this->aMoveResult['message'];
            }

            $this->aSurveyInfo['aLEM']['debugvalidation']['show'] = false; $this->aSurveyInfo['aLEM']['debugvalidation']['message'] = '';
            if ((($this->LEMdebugLevel & LEM_DEBUG_TIMING) == LEM_DEBUG_TIMING)){
                $this->aSurveyInfo['aLEM']['debugvalidation']['show']     = true;
                $this->aSurveyInfo['aLEM']['debugvalidation']['message'] .= LimeExpressionManager::GetDebugTimingMessage();;
            }

            if ((($this->LEMdebugLevel & LEM_DEBUG_VALIDATION_SUMMARY) == LEM_DEBUG_VALIDATION_SUMMARY)){
                $this->aSurveyInfo['aLEM']['debugvalidation']['message'] .= "<table><tr><td align='left'><b>Group/Question Validation Results:</b>" . $this->aMoveResult['message'] . "</td></tr></table>\n";
            }

            // The session cannot be killed until the page is completely rendered
            if ($this->aSurveyInfo['printanswers'] != 'Y'){
                killSurveySession($this->iSurveyid);
            }

            Yii::app()->twigRenderer->renderTemplateFromString( file_get_contents($this->sTemplateViewPath."layout_submit.twig"), array('aSurveyInfo'=>$this->aSurveyInfo), false);
        }
    }


    /**
     * The run method fed $redata with using get_defined_var(). So it was very hard to move a piece of code from the run method to a new one.
     * To make it easier, private variables has been added to this class:
     * So when a piece of code changes a variable (a variable that originally was finally added to redata get_defined_var()), now, it also changes its private variable version.
     * Then, before performing the get_defined_var, the private variables are used to recreate those variables. So we can move piece of codes to sub methods.
     * setVarFromArgs($args) will set the original state of those private variables using the parameter $args passed to the run() method
     *
     * @params array $args
     */
    private function setVarFromArgs($args)
    {
        extract($args);

        // Todo: check which ones are really needed
        $this->LEMskipReprocessing    = isset( $LEMskipReprocessing    )?$LEMskipReprocessing    :null ;
        $this->thissurvey             = isset( $thissurvey             )?$thissurvey             :null ;
        $this->iSurveyid              = isset( $surveyid               )?$surveyid               :null ;
        $this->bShowEmptyGroup        = isset( $show_empty_group       )?$show_empty_group       :null ;
        $this->sSurveyMode            = isset( $surveyMode             )?$surveyMode             :null ;
        $this->aSurveyOptions         = isset( $surveyOptions          )?$surveyOptions          :null ;
        $this->aMoveResult             = isset( $moveResult             )?$moveResult             :null ;
        $this->move                   = isset( $move                   )?$move                   :null ;
        $this->invalidLastPage        = isset( $invalidLastPage        )?$invalidLastPage        :null ;
        $this->oResponse              = isset( $oResponse              )?$oResponse              :null ;
        $this->unansweredSQList       = isset( $unansweredSQList       )?$unansweredSQList       :null ;
        $this->notanswered            = isset( $notanswered            )?$notanswered            :null ;
        $this->invalidSQList          = isset( $invalidSQList          )?$invalidSQList          :null ;
        $this->filenotvalidated       = isset( $filenotvalidated       )?$filenotvalidated       :null ;
        $this->completed              = isset( $completed              )?$completed              :null ;
        $this->blocks                 = isset( $blocks                 )?$blocks                 :null ;
        $this->notvalidated           = isset( $notvalidated           )?$notvalidated           :null;
    }

    /**
    * setJavascriptVar
    *
    * @return @void
    * @param integer $iSurveyId : the survey id for the script
    */
    public function setJavascriptVar($iSurveyId='')
    {
        $aSurveyinfo  = ($iSurveyId!='')?getSurveyInfo($iSurveyId, App()->getLanguage()):$this->thissurvey;

        if(isset($aSurveyinfo['surveyls_numberformat'])){
            $aLSJavascriptVar                  = array();
            $aLSJavascriptVar['bFixNumAuto']   = (int)(bool)Yii::app()->getConfig('bFixNumAuto',1);
            $aLSJavascriptVar['bNumRealValue'] = (int)(bool)Yii::app()->getConfig('bNumRealValue',0);
            $aRadix                            = getRadixPointData($aSurveyinfo['surveyls_numberformat']);
            $aLSJavascriptVar['sLEMradix']     = $aRadix['separator'];
            $aLSJavascriptVar['lang']          = new stdClass; // To add more easily some lang string here
            $aLSJavascriptVar['showpopup']     = (int)Yii::app()->getConfig('showpopups');
            $aLSJavascriptVar['startPopups']   = new stdClass;
            $sLSJavascriptVar                  = "LSvar=".json_encode($aLSJavascriptVar) . ';';
            $sLSJavascriptVar                  = "LSvar=".json_encode($aLSJavascriptVar) . ';';
            App()->clientScript->registerScript('sLSJavascriptVar',$sLSJavascriptVar,CClientScript::POS_HEAD);
            App()->clientScript->registerScript('setJsVar',"setJsVar();",CClientScript::POS_BEGIN);                 // Ensure all js var is set before rendering the page (User can click before $.ready)
        }
    }

    /**
    * Construction of replacement array, actually doing it with redata
    *
    * @param $aQuestionQanda : array from qanda helper
    * @return aray of replacement for question.psptl
    **/
    public static function getQuestionReplacement($aQuestionQanda)
    {

        // Get the default replacement and set empty value by default
        $aReplacement=array(
            "QID"=>"",
            //"GID"=>"", // Attention : set in replacement helper too (by gid).
            "SGQ"=>"",
            "AID"=>"",
            "QUESTION_CODE"=>"",
            "QUESTION_NUMBER"=>"",
            "QUESTION"=>"",
            "QUESTION_TEXT"=>"",
            "QUESTIONHELP"=>"", // User help
            "QUESTIONHELPPLAINTEXT"=>"",
            "QUESTION_CLASS"=>"",
            "QUESTION_MAN_CLASS"=>"",
            "QUESTION_INPUT_ERROR_CLASS"=>"",
            "ANSWER"=>"",
            "QUESTION_HELP"=>"", // Core help
            "QUESTION_VALID_MESSAGE"=>"",
            "QUESTION_FILE_VALID_MESSAGE"=>"",
            "QUESTION_MAN_MESSAGE"=>"",
            "QUESTION_MANDATORY"=>"",
            "QUESTION_ESSENTIALS"=>"",
        );
        if(!is_array($aQuestionQanda) || empty($aQuestionQanda[0]))
        {
            return $aReplacement;
        }
        $iQid=$aQuestionQanda[4];
        /* Need actual EM status */
        $lemQuestionInfo = LimeExpressionManager::GetQuestionStatus($iQid);
        /* Allow Question Attribute to update some part */
        $aQuestionAttributes = getQuestionAttributeValues($iQid);

        $iSurveyId=Yii::app()->getConfig('surveyID');// Or : by SGQA of question ? by Question::model($iQid)->sid;
        //~ $oSurveyId=Survey::model()->findByPk($iSurveyId); // Not used since 2.50
        $sType=$lemQuestionInfo['info']['type'];

        // Core value : not replaced
        $aReplacement['QID']=$iQid;
        $aReplacement['GID']=$aQuestionQanda[6];// Not sure for aleatory : it's the real gid or the updated gid ? We need original gid or updated gid ?
        $aReplacement['SGQ']=$aQuestionQanda[7];
        $aReplacement['AID']=isset($aQuestionQanda[0]['aid']) ? $aQuestionQanda[0]['aid'] : "" ;
        $sCode=$aQuestionQanda[5];
        $iNumber=$aQuestionQanda[0]['number'];

        /* QUESTION_CODE + QUESTION_NUMBER */
        $showqnumcode_global_ = getGlobalSetting('showqnumcode');
        $aSurveyinfo = getSurveyInfo($iSurveyId);
        // Check global setting to see if survey level setting should be applied
        if($showqnumcode_global_ == 'choose') { // Use survey level settings
            $showqnumcode_ = $aSurveyinfo['showqnumcode']; //B, N, C, or X
        } else { // Use global setting
            $showqnumcode_ = $showqnumcode_global_; //both, number, code, or none
        }

        switch ($showqnumcode_)
        {
            case 'both':
            case 'B': // Both
                $aReplacement['QUESTION_CODE']=$sCode;
                $aReplacement['QUESTION_NUMBER']=$iNumber;
                break;
            case 'number':
            case 'N': // Number only
                $aReplacement['QUESTION_CODE']="";
                $aReplacement['QUESTION_NUMBER']=$iNumber;
                break;
            case 'code':
            case 'C': // Code only
                $aReplacement['QUESTION_CODE']=$sCode;
                $aReplacement['QUESTION_NUMBER']="";
                break;
            case 'none':
            case 'X':
            default: // Neither
                $aReplacement['QUESTION_CODE']="";
                $aReplacement['QUESTION_NUMBER']="";
                break;
        }

        $aReplacement['QUESTION']=$aQuestionQanda[0]['all'] ; // Deprecated : only used in old template (very old)
        // Core value : user text : add an id for labelled-by and described-by
        $aReplacement['QUESTION_TEXT'] = CHtml::tag("div", array('id'=>"ls-question-text-{$aReplacement['SGQ']}",'class'=>"ls-label-question"),$aQuestionQanda[0]['text']);
        $aReplacement['QUESTIONHELP']=$lemQuestionInfo['info']['help'];// User help
        if(flattenText($aReplacement['QUESTIONHELP'], true,true) != '')
        {
            $aReplacement['QUESTIONHELP']= Yii::app()->getController()->renderPartial('//survey/questions/question_help/questionhelp', array('questionHelp'=>$aReplacement['QUESTIONHELP']), true);;
        }
        // Core value :the classes
        $aQuestionClass=array(
            Question::getQuestionClass($sType),
        );
        /* Add the relevance class */
        if (!$lemQuestionInfo['relevant'])
        {
            $aQuestionClass[]='ls-unrelevant';
            $aQuestionClass[]='ls-hidden';
        }
        if ($lemQuestionInfo['hidden']){ /* Can use aQuestionAttributes too */
            $aQuestionClass[]='ls-hidden-attribute';/* another string ? */
            $aQuestionClass[]='ls-hidden';
        }
        //add additional classes
        if(isset($aQuestionAttributes['cssclass']) && $aQuestionAttributes['cssclass']!=""){
            /* Got to use static expression */
            $emCssClass=trim(LimeExpressionManager::ProcessString($aQuestionAttributes['cssclass'], null, array(), false, 1, 1, false, false, true));/* static var is the lmast one ...*/
            if($emCssClass!=""){
                $aQuestionClass[]=Chtml::encode($emCssClass);
            }
        }
        $aReplacement['QUESTION_CLASS'] =implode(" ",$aQuestionClass);

        $aMandatoryClass = array();
        if ($lemQuestionInfo['info']['mandatory'] == 'Y')// $aQuestionQanda[0]['mandatory']=="*"
        {
            $aMandatoryClass[]= 'mandatory';
        }
        if ($lemQuestionInfo['anyUnanswered'] && $_SESSION['survey_' . $iSurveyId]['maxstep'] != $_SESSION['survey_' . $iSurveyId]['step'])// This is working ?
        {
            $aMandatoryClass[]= 'missing';
        }
        $aReplacement['QUESTION_MAN_CLASS']=!empty($aMandatoryClass) ? " ".implode(" ",$aMandatoryClass) : "";
        $aReplacement['QUESTION_INPUT_ERROR_CLASS']=$aQuestionQanda[0]['input_error_class'];
        // Core value : LS text : EM and not
        $aReplacement['ANSWER']=$aQuestionQanda[1];
        $aReplacement['QUESTION_HELP']=$aQuestionQanda[0]['help'];// Core help only, not EM
        $aReplacement['QUESTION_VALID_MESSAGE']=$aQuestionQanda[0]['valid_message'];// $lemQuestionInfo['validTip']
        $aReplacement['QUESTION_FILE_VALID_MESSAGE']=$aQuestionQanda[0]['file_valid_message'];// $lemQuestionInfo['??']
        $aReplacement['QUESTION_MAN_MESSAGE']=$aQuestionQanda[0]['man_message'];
        $aReplacement['QUESTION_MANDATORY']=$aQuestionQanda[0]['mandatory'];
        // For QUESTION_ESSENTIALS
        $aHtmlOptions=array();

        // Launch the event
        $event = new PluginEvent('beforeQuestionRender');
        // Some helper
        $event->set('surveyId', $iSurveyId);
        $event->set('type', $sType);
        $event->set('code', $sCode);
        $event->set('qid', $iQid);
        $event->set('gid', $aReplacement['GID']);
        $event->set('sgq', $aReplacement['SGQ']);
        // User text
        $event->set('text', $aReplacement['QUESTION_TEXT']);
        $event->set('questionhelp', $aReplacement['QUESTIONHELP']);
        // The classes
        $event->set('class', $aReplacement['QUESTION_CLASS']);
        $event->set('man_class', $aReplacement['QUESTION_MAN_CLASS']);
        $event->set('input_error_class', $aReplacement['QUESTION_INPUT_ERROR_CLASS']);
        // LS core text
        $event->set('answers', $aReplacement['ANSWER']);
        $event->set('help', $aReplacement['QUESTION_HELP']);
        $event->set('man_message', $aReplacement['QUESTION_MAN_MESSAGE']);
        $event->set('valid_message', $aReplacement['QUESTION_VALID_MESSAGE']);
        $event->set('file_valid_message', $aReplacement['QUESTION_FILE_VALID_MESSAGE']);
        // htmlOptions for container
        $event->set('aHtmlOptions', $aHtmlOptions);

        App()->getPluginManager()->dispatchEvent($event);
        // User text
        $aReplacement['QUESTION_TEXT'] = $event->get('text');
        $aReplacement['QUESTIONHELP'] = $event->get('questionhelp');
        $aReplacement['QUESTIONHELPPLAINTEXT']=strip_tags(addslashes($aReplacement['QUESTIONHELP']));
        // The classes
        $aReplacement['QUESTION_CLASS'] = $event->get('class');
        $aReplacement['QUESTION_MAN_CLASS'] = $event->get('man_class');
        $aReplacement['QUESTION_INPUT_ERROR_CLASS'] = $event->get('input_error_class');
        // LS core text
        $aReplacement['ANSWER'] = $event->get('answers');
        $aReplacement['QUESTION_HELP'] = $event->get('help');
        $aReplacement['QUESTION_MAN_MESSAGE'] = $event->get('man_message');
        $aReplacement['QUESTION_VALID_MESSAGE'] = $event->get('valid_message');
        $aReplacement['QUESTION_FILE_VALID_MESSAGE'] = $event->get('file_valid_message');
        $aReplacement['QUESTION_MANDATORY'] = $event->get('mandatory',$aReplacement['QUESTION_MANDATORY']);
        //Another data for QUESTION_ESSENTIALS
        $aHtmlOptions= (array) $event->get('aHtmlOptions');
        unset($aHtmlOptions['class']);// Disallowing update/set class
        $aHtmlOptions['id']="question{$iQid}";// Always add id for QUESTION_ESSENTIALS$

        $aReplacement['QUESTION_ESSENTIALS']=CHtml::renderAttributes($aHtmlOptions);

        return $aReplacement;
    }
    /**
     * Html error message if needed/available in the page
     * @return string (html)
     * @todo : move to coreReplacements ? Can be good.
     */
    private function getErrorHtmlMessage()
    {
        $aErrorsMandatory=array();

        //Mandatory question(s) with unanswered answer
        if ($this->stepInfo['mandViolation'] && $this->okToShowErrors){
            $aErrorsMandatory[]=gT("One or more mandatory questions have not been answered. You cannot proceed until these have been completed.");
        }

        // Question(s) with not valid answer(s)
        if ($this->stepInfo['valid'] && $this->okToShowErrors){
            $aErrorsMandatory[]=gT("One or more questions have not been answered in a valid manner. You cannot proceed until these answers are valid.");
        }

        // Upload question(s) with invalid file(s)
        if ($this->filenotvalidated && $this->okToShowErrors){
            $aErrorsMandatory[]=gT("One or more uploaded files are not in proper format/size. You cannot proceed until these files are valid.");
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
        $sessionSurvey  = Yii::app()->session["survey_{$this->iSurveyid}"];

        if (App()->request->getPost('confirm-clearall') == 'confirm'){

            // Previous behaviour (and javascript behaviour)
            // delete the existing response but only if not already completed
            if (
                isset($sessionSurvey['srid'])
                && !SurveyDynamic::model($this->iSurveyid)->isCompleted($sessionSurvey['srid']) // see bug https://bugs.limesurvey.org/view.php?id=11978
            ){
                $oResponse = Response::model($this->iSurveyid)->find("id = :srid",array(":srid"=>$sessionSurvey['srid']));

                if($oResponse){
                    $oResponse->delete(true);/* delete response line + files uploaded , warninbg : beforeDelete don't happen with deleteAll */
                }

                if(Survey::model()->findByPk($this->iSurveyid)->savetimings == "Y"){
                    SurveyTimingDynamic::model($this->iSurveyid)->deleteAll("id=:srid",array(":srid"=>$sessionSurvey['srid'])); /* delete timings ( @todo must move it to Response )*/
                }

                SavedControl::model()->deleteAll("sid=:sid and srid=:srid",array(":sid"=>$this->iSurveyid,":srid"=>$sessionSurvey['srid']));/* saved controls (think we can have only one , but maybe ....)( @todo must move it to Response )*/
            }

            killSurveySession($this->iSurveyid);

            global $token;
            if($token){
                $restartparam['token'] = sanitize_token($token);
            }

            if (Yii::app()->request->getQuery('lang')){
                $restartparam['lang'] = sanitize_languagecode(Yii::app()->request->getQuery('lang'));
            }else{
                $s_lang = isset(Yii::app()->session['survey_'.$this->iSurveyid]['s_lang']) ? Yii::app()->session['survey_'.$this->iSurveyid]['s_lang'] : 'en';
                $restartparam['lang'] = $s_lang;
            }

            $restartparam['newtest'] = "Y";
            $restarturl = Yii::app()->getController()->createUrl("survey/index/sid/$this->iSurveyid",$restartparam);

            $this->aSurveyInfo['surveyUrl'] = $restarturl;

            Yii::app()->twigRenderer->renderTemplateFromString( file_get_contents($this->sTemplateViewPath."layout_clearall.twig"), array('aSurveyInfo'=>$this->aSurveyInfo), false);
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
        $oTemplate         = $this->template;
        $sTemplatePath     = $oTemplate->path;
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
            "captchaRequired" => (isCaptchaEnabled('surveyaccessscreen',$this->aSurveyInfo['usecaptcha']) && !isset($_SESSION['survey_'.$this->iSurveyid]['captcha_surveyaccessscreen']))
        );

        /**
        *   Set subscenarios depending on scenario outcome
        */
        $subscenarios = array(
            "captchaCorrect" => false,
            "tokenValid"     => false
        );

        //Check the scenario for token required
        if ($scenarios['tokenRequired']){

            //Check for the token-validity
            if ($this->aSurveyInfo['alloweditaftercompletion'] == 'Y' ) {
                $oTokenEntry = Token::model($this->iSurveyid)->findByAttributes(array('token'=>$clienttoken));
            } else {
                $oTokenEntry = Token::model($this->iSurveyid)->usable()->incomplete()->findByAttributes(array('token' => $clienttoken));
            }
            $subscenarios['tokenValid'] = ((!empty($oTokenEntry) && ($clienttoken != "")));
        }else{
            $subscenarios['tokenValid'] = true;
        }

        //Check the scenario for captcha required
        if ($scenarios['captchaRequired']){
            //Check if the Captcha was correct
            $captcha                        = Yii::app()->getController()->createAction('captcha');
            $subscenarios['captchaCorrect'] = $captcha->validate(App()->getRequest()->getPost('loadsecurity'), false);
        }else{
            $subscenarios['captchaCorrect'] = true;
            $loadsecurity                   = false;
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
        $aEnterTokenData['bNewTest']        =  false;
        $aEnterTokenData['bDirectReload']   =  false;
        $aEnterTokenData['iSurveyId']       = $this->iSurveyid;
        $aEnterTokenData['sLangCode']       = App()->language;

        if (isset($_GET['bNewTest']) && $_GET['newtest'] == "Y"){
            $aEnterTokenData['bNewTest'] =  true;
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
        // Scenario => Token required
        if ($scenarios['tokenRequired'] && !$preview){
            //Test if token is valid
            list($renderToken, $FlashError) = testIfTokenIsValid($subscenarios, $this->aSurveyInfo, $aEnterTokenData, $clienttoken);
            if(!empty($FlashError)){
                $aEnterErrors['token']=$FlashError;
            }
        }

        // Scenario => Captcha required
        if ($scenarios['captchaRequired'] && !$preview) {

            //Apply the captcYii::app()->getRequest()->getPost($id);haEnabled flag to the partial
            $aEnterTokenData['bCaptchaEnabled'] = true;
            // IF CAPTCHA ANSWER IS NOT CORRECT OR NOT SET
            if (!$subscenarios['captchaCorrect']) {

                if (App()->getRequest()->getPost('loadsecurity')){
                    $aEnterErrors['captcha'] = gT("Your answer to the security question was not correct - please try again.");

                } elseif (null!==App()->getRequest()->getPost('loadsecurity')) {
                    $aEnterErrors['captcha'] = gT("Your must answer to the security question - please try again.");
                }
                $renderCaptcha = 'main';
            }
            else {
                $_SESSION['survey_'.$this->iSurveyid]['captcha_surveyaccessscreen'] = true;
                $renderCaptcha = 'correct';
            }
        }

        // Scenario => Token required
        if ($scenarios['tokenRequired'] && !$preview){
            //Test if token is valid
            list($renderToken, $FlashError, $aEnterTokenData) = testIfTokenIsValid($subscenarios, $this->aSurveyInfo, $aEnterTokenData, $clienttoken);
        }

        if ($FlashError){
            $aEnterErrors['flash'] = $FlashError;
        }

        $aEnterTokenData['aEnterErrors']    = $aEnterErrors;
        $renderWay                          = getRenderWay($renderToken, $renderCaptcha);

        /* This funtion end if an form need to be shown */
        renderRenderWayForm($renderWay, $scenarios, $this->sTemplateViewPath, $aEnterTokenData, $this->iSurveyid);

    }


    private function initTemplate()
    {
        $oTemplate         = $this->template          = Template::model()->getInstance('', $this->iSurveyid);
        $this->sTemplateViewPath = $oTemplate->viewPath;
        $oTemplate->registerAssets();
        Yii::app()->twigRenderer->setForcedPath($this->sTemplateViewPath);
    }

    private function makeLanguageChanger()
    {

        $this->aSurveyInfo['alanguageChanger']['show']  = false;
        $alanguageChangerDatas                   = getLanguageChangerDatas($this->sLangCode);

        if ($alanguageChangerDatas){
            $this->aSurveyInfo['alanguageChanger']['show']  = true;
            $this->aSurveyInfo['alanguageChanger']['datas'] = $alanguageChangerDatas;
        }
    }

    /**
     * This method will set survey values in public property of the class
     * So, any value here set as $this->xxx will be available as $xxx after :
     * $aPrivateVariables = $this->getArgs(); extract($aPrivateVariables);
     * eg: $this->LEMsessid
     *
     */
    private function setSurveySettings( $surveyid, $args  )
    {
        $this->setVarFromArgs($args);                                           // Set the private variable from $args
        $this->initTemplate();                                                  // Template settings
        $this->setJavascriptVar();
        $this->setArgs();

        extract($args);

        $this->LEMsessid = 'survey_' . $this->iSurveyid;
        $this->aSurveyInfo                 = (!$thissurvey)?getSurveyInfo($this->iSurveyid):$thissurvey;
        $this->aSurveyInfo['surveyUrl']    = App()->createUrl("/survey/index",array("sid"=>$this->iSurveyid));

        // TODO: check this:
        $this->aSurveyInfo['oTemplate']    = (array) $this->template;

        $this->setSurveyMode();
        $this->setSurveyOptions();

        $this->previewgrp      = ($this->sSurveyMode == 'group' && isset($param['action'])    && ($param['action'] == 'previewgroup'))    ? true : false;
        $this->previewquestion = ($this->sSurveyMode == 'question' && isset($param['action']) && ($param['action'] == 'previewquestion')) ? true : false;
        $preview               = $this->preview         = ($this->previewquestion || $this->previewgrp);
        $this->sLangCode       = App()->language;
    }

    private function setPreview()
    {
        $aPrivateVariables = $this->getArgs();
        extract($aPrivateVariables);


        $_SESSION[$this->LEMsessid]['prevstep'] = 2;
        $_SESSION[$this->LEMsessid]['maxstep']  = 0;

        if ($this->previewquestion){
            $_SESSION[$this->LEMsessid]['step'] = 0; //maybe unset it after the question has been displayed?
        }

        if ($this->sSurveyMode == 'group' && $this->previewgrp){
            $_gid = sanitize_int($param['gid']);

            LimeExpressionManager::StartSurvey($this->aSurveyInfo['sid'], 'group', $this->aSurveyOptions, false, $this->LEMdebugLevel);
            $gseq = LimeExpressionManager::GetGroupSeq($_gid);

            if ($gseq == -1){
                $sMessage = gT('Invalid group number for this survey: ') . $_gid;
                renderError('', $sMessage, $this->aSurveyInfo, $this->sTemplateViewPath );
            }

            $this->aMoveResult = LimeExpressionManager::JumpTo($gseq + 1, true);
            if (is_null($this->aMoveResult)){
                $sMessage = gT('This group contains no questions.  You must add questions to this group before you can preview it');
                renderError('', $sMessage, $this->aSurveyInfo, $this->sTemplateViewPath );
            }

            $_SESSION[$this->LEMsessid]['step'] = $this->aMoveResult['seq'] + 1;  // step is index base 1?

            $stepInfo         = $this->stepInfo         = LimeExpressionManager::GetStepIndexInfo($this->aMoveResult['seq']);
            $gid              = $this->gid              = $stepInfo['gid'];
            $groupname        = $this->groupname        = $stepInfo['gname'];
            $groupdescription = $this->groupdescription = $stepInfo['gtext'];

        }elseif($this->sSurveyMode == 'question' && $this->previewquestion){
                $_qid       = sanitize_int($param['qid']);
                LimeExpressionManager::StartSurvey($this->iSurveyid, 'question', $this->aSurveyOptions, false, $this->LEMdebugLevel);
                $qSec       = LimeExpressionManager::GetQuestionSeq($_qid);
                $this->aMoveResult= LimeExpressionManager::JumpTo($qSec+1,true,false,true);
                $stepInfo   = $this->stepInfo = LimeExpressionManager::GetStepIndexInfo($this->aMoveResult['seq']);
        }
    }


    private function setGroup()
    {
        $aPrivateVariables = $this->getArgs();
        extract($aPrivateVariables);

        if ( !$this->previewgrp && !$this->previewquestion)
        {
            if (($this->bShowEmptyGroup) || !isset($_SESSION[$this->LEMsessid]['grouplist'])){
                $this->gid              = -1; // Make sure the gid is unused. This will assure that the foreach (fieldarray as ia) has no effect.
                $this->groupname        = gT("Submit your answers");
                $this->groupdescription = gT("There are no more questions. Please press the <Submit> button to finish this survey.");
            }
            else if ($this->sSurveyMode != 'survey')
            {
                if ($this->sSurveyMode != 'group'){
                    $stepInfo         = $this->stepInfo = LimeExpressionManager::GetStepIndexInfo($this->aMoveResult['seq']);
                }

                $this->gid              = $stepInfo['gid'];
                $this->groupname        = $stepInfo['gname'];
                $this->groupdescription = $stepInfo['gtext'];
            }
        }
    }

    private function fixMaxStep()
    {
        // NOTE: must stay after setPreview  because of ()$this->sSurveyMode == 'group' && $this->previewgrp) condition touching step
        if ($_SESSION[$this->LEMsessid]['step'] > $_SESSION[$this->LEMsessid]['maxstep'])
        {
            $_SESSION[$this->LEMsessid]['maxstep'] = $_SESSION[$this->LEMsessid]['step'];
        }
    }

}
