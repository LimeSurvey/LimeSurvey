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

    // Preview datas
    private $previewquestion     = false;
    private $previewgrp          = false;

    // Template datas
    private $oTemplate;                                                         // Template configuration object (set in model TemplateConfiguration)
    private $sTemplateViewPath;                                                 // Path of the PSTPL files in template

    // LEM Datas
    private $LEMsessid;
    private $LEMdebugLevel          = 0;                                        // customizable debugging for Lime Expression Manager ; LEM_DEBUG_TIMING;   (LEM_DEBUG_TIMING + LEM_DEBUG_VALIDATION_SUMMARY + LEM_DEBUG_VALIDATION_DETAIL);
    private $LEMskipReprocessing    = false;                                    // true if used GetLastMoveResult to avoid generation of unneeded extra JavaScript

    // Survey settings:
    // TODO: To respect object oriented design, all those "states" should be move to SurveyDynamic model, or its related models via relations.
    // The only private variable here should be $oSurvey.
    private $thissurvey;                                                        // Array returned by common_helper::getSurveyInfo(); (survey row + language settings );
    private $surveyid               = null;                                     // The survey id
    private $show_empty_group       = false;                                    // True only when $_SESSION[$LEMsessid]['step'] == 0 ; Just a variable for a logic step ==> should not be a Class variable (for now, only here for the redata== get_defined_vars mess)
    private $surveyMode;                                                        // {Group By Group,  All in one, Question by question}
    private $surveyOptions;                                                     // Few options comming from thissurvey, App->getConfig, LEM. Could be replaced by $oSurvey + relations ; the one coming from LEM and getConfig should be public variable on the surveyModel, set via public methods (active, allowsave, anonymized, assessments, datestamp, deletenonvalues, ipaddr, radix, refurl, savetimings, surveyls_dateformat, startlanguage, target, tempdir,timeadjust)
    private $totalquestions;                                                    // Number of question in the survey. Same, should be moved to survey model.
    private $bTokenAnswerPersitance;                                            // Are token used? Same...
    private $assessments;                                                       // Is assement used? Same...
    private $sLangCode;                                                         // Current language code

    // moves
    private $moveResult             = null;                                     // Contains the result of LimeExpressionManager::JumpTo() OR LimeExpressionManager::NavigateBackwards() OR NavigateForwards::LimeExpressionManager(). TODO: create a function LimeExpressionManager::MoveTo that call the right method
    private $move                   = null;                                     // The move requested by user. Set by frontend_helper::getMove() from the POST request.
    private $invalidLastPage;                                                   // Just a variable used to check if user submitted a survey while it's not finished. Just a variable for a logic step ==> should not be a Class variable (for now, only here for the redata== get_defined_vars mess)
    private $stepInfo;

    // Popups: HTML of popus. If they are null, no popup. If they contains a string, a popup will be shown to participant.
    // They could probably be merged.
    private $backpopup;                                                         // "Please use the LimeSurvey navigation buttons or index.  It appears you attempted to use the browser back button to re-submit a page."
    private $popup;                                                             // savedcontrol, mandatory_popup
    private $notvalidated;                                                      // question validation error

    // response
    // TODO:  To respect object oriented design, all those "states" should be move to Response model, or its related models via relations.
    private $oResponse;                                                         // An instance of the response model.
    private $notanswered;                                                       // A global variable...Should be $oResponse->notanswered
    private $unansweredSQList;                                                  // A list of the unanswered responses created via the global variable $notanswered. Should be $oResponse->unanswereds
    private $invalidSQList;                                                     // Invalid answered, fed from $moveResult(LEM). Its logic should be in Response model.
    private $filenotvalidated;                                                  // Same, but specific to file question type. (seems to be problematic by the past)

    // strings
    private $completed;                                                         // The string containing the completed message
    private $content;                                                           // The content... It contains the result of the different templatereplace (so all the replacement of the pstpl files)
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
        $thissurvey = $this->thissurvey;

        extract($args);                                                         // TODO: Check if still needed at this level

        ///////////////////////////////////////////////////////////
        // 1: We check if token and/or captcha form shouls be shown
        if (!isset($_SESSION[$this->LEMsessid]['step'])){
            $this->showTokenOrCaptchaFormsIfNeeded();
        }

        if ( !$this->previewgrp && !$this->previewquestion){
            $this->initMove();                                                  // main methods to init session, LEM, moves, errors, etc
            $this->checkQuotas();                                               // check quotas (then the process will stop here)
            $this->checkClearCancel();
            $this->displayFirstPageIfNeeded();
            $this->saveAllIfNeeded();
            $this->saveSubmitIfNeeded();

            // TODO: move somewhere else
            $this->setNotAnsweredAndNotValidated();

            $this->setGroup();

        }else{
            $this->setPreview();
        }

        $this->moveSubmitIfNeeded();

        $this->fixMaxStep();

        // IF GOT THIS FAR, THEN DISPLAY THE ACTIVE GROUP OF QUESTIONSs
        $aPrivateVariables = $this->getArgs();
        extract($aPrivateVariables);

        //******************************************************************************************************
        //PRESENT SURVEY
        //******************************************************************************************************

        $this->okToShowErrors = $okToShowErrors = (!($previewgrp || $previewquestion) && (isset($invalidLastPage) || $_SESSION[$LEMsessid]['prevstep'] == $_SESSION[$LEMsessid]['step']));

        Yii::app()->getController()->loadHelper('qanda');
        setNoAnswerMode($thissurvey);

        //Iterate through the questions about to be displayed:
        $inputnames = array();

        foreach ($_SESSION[$LEMsessid]['grouplist'] as $gl)
        {
            $gid = $gl['gid'];
            $qnumber = 0;

            if ($surveyMode != 'survey')
            {
                $onlyThisGID = $stepInfo['gid'];
                if ($onlyThisGID != $gid)
                {
                    continue;
                }
            }

            // TMSW - could iterate through LEM::currentQset instead

            //// To diplay one question, all the questions are processed ?
            if (!isset($qanda))
            {
                $qanda=array();
            }
            foreach ($_SESSION[$LEMsessid]['fieldarray'] as $key => $ia)
            {
                ++$qnumber;
                $ia[9] = $qnumber; // incremental question count;

                if ((isset($ia[10]) && $ia[10] == $gid) || (!isset($ia[10]) && $ia[5] == $gid))// Make $qanda only for needed question $ia[10] is the randomGroup and $ia[5] the real group
                {
                    if ($surveyMode == 'question' && $ia[0] != $stepInfo['qid'])
                    {
                        continue;
                    }
                    $qidattributes = getQuestionAttributeValues($ia[0]);
                    if ($ia[4] != '*' && ($qidattributes === false || !isset($qidattributes['hidden']) || $qidattributes['hidden'] == 1))
                    {
                        continue;
                    }

                    //Get the answers/inputnames
                    // TMSW - can content of retrieveAnswers() be provided by LEM?  Review scope of what it provides.
                    // TODO - retrieveAnswers is slow - queries database separately for each question. May be fixed in _CI or _YII ports, so ignore for now
                    list($plus_qanda, $plus_inputnames) = retrieveAnswers($ia, $surveyid);
                    if ($plus_qanda)
                    {
                        $plus_qanda[] = $ia[4];
                        $plus_qanda[] = $ia[6]; // adds madatory identifyer for adding mandatory class to question wrapping div
                        // Add a finalgroup in qa array , needed for random attribute : TODO: find a way to have it in new quanda_helper in 2.1
                        if(isset($ia[10]))
                            $plus_qanda['finalgroup']=$ia[10];
                        else
                            $plus_qanda['finalgroup']=$ia[5];
                        $qanda[] = $plus_qanda;
                    }
                    if ($plus_inputnames)
                    {
                        $inputnames = addtoarray_single($inputnames, $plus_inputnames);
                    }

                    //Display the "mandatory" popup if necessary
                    // TMSW - get question-level error messages - don't call **_popup() directly
                    if ($okToShowErrors && $stepInfo['mandViolation'])
                    {
                        list($mandatorypopup, $popup) = mandatory_popup($ia, $notanswered);
                    }

                    //Display the "validation" popup if necessary
                    if ($okToShowErrors && !$stepInfo['valid'])
                    {
                        list($validationpopup, $vpopup) = validation_popup($ia, $notvalidated);
                    }

                    // Display the "file validation" popup if necessary
                    if ($okToShowErrors && isset($filenotvalidated))
                    {
                        list($filevalidationpopup, $fpopup) = file_validation_popup($ia, $filenotvalidated);
                    }
                }
                if ($ia[4] == "|")
                    $upload_file = TRUE;
            } //end iteration
        }

        if ($surveyMode != 'survey' && isset($thissurvey['showprogress']) && $thissurvey['showprogress'] == 'Y'){
            if ($show_empty_group){
                // $percentcomplete = makegraph($_SESSION[$LEMsessid]['totalsteps'] + 1, $_SESSION[$LEMsessid]['totalsteps']);
                $thissurvey['progress']['currentstep'] = $_SESSION[$LEMsessid]['totalsteps'] + 1;
                $thissurvey['progress']['total']       = $_SESSION[$LEMsessid]['totalsteps'];
            }else{
                // $percentcomplete = makegraph($_SESSION[$LEMsessid]['step'], $_SESSION[$LEMsessid]['totalsteps']);
                $thissurvey['progress']['currentstep'] = $_SESSION[$LEMsessid]['step'];
                $thissurvey['progress']['total']       = $_SESSION[$LEMsessid]['totalsteps'];

            }
        }

        $thissurvey['yiiflashmessages'] = Yii::app()->user->getFlashes();

        //READ TEMPLATES, INSERT DATA AND PRESENT PAGE

        /**
         * create question index only in SurveyRuntime, not needed elsewhere, add it to GlobalVar : must be always set even if empty
         *
         */
        if(!$previewquestion && !$previewgrp){
            $questionindex            = ls\helpers\questionIndexHelper::getInstance()->getIndexButton();
            $questionindexmenu        = ls\helpers\questionIndexHelper::getInstance()->getIndexLink();
            $thissurvey['aQuestionIndex']['items'] = ls\helpers\questionIndexHelper::getInstance()->getIndexItems();

            if($thissurvey['questionindex'] > 1){
                $thissurvey['aQuestionIndex']['type'] = 'full';
            }else{
                $thissurvey['aQuestionIndex']['type'] = 'incremental';
            }
        }


        sendCacheHeaders();

        Yii::app()->loadHelper('surveytranslator');

        // Set Langage // TODO remove one of the Yii::app()->session see bug #5901
        if (Yii::app()->session['survey_'.$surveyid]['s_lang'] ){
            $languagecode =  Yii::app()->session['survey_'.$surveyid]['s_lang'];
        }elseif (isset($surveyid) && $surveyid  && Survey::model()->findByPk($surveyid)){
            $languagecode = Survey::model()->findByPk($surveyid)->language;
        }else{
            $languagecode = Yii::app()->getConfig('defaultlang');
        }
        $thissurvey['languagecode'] = $languagecode;
        $thissurvey['dir']          = (getLanguageRTL($languagecode))?"rtl":"ltr";

        Yii::app()->clientScript->registerScriptFile(Yii::app()->getConfig("generalscripts").'nojs.js',CClientScript::POS_HEAD);

        $thissurvey['upload_file']      = (isset($upload_file) && $upload_file)?true:false;
        $hiddenfieldnames               = $thissurvey['hiddenfieldnames']  = implode("|", $inputnames);


        // Show question code/number
        $thissurvey['aShow']             = $this->getShowNumAndCode($thissurvey);

        $redata                         = compact(array_keys(get_defined_vars()));

        $popup = $this->popup;
        $aPopup=array(); // We can move this part where we want now
        if (isset($backpopup))
        {
            $aPopup[]=$backpopup;// If user click reload: no need other popup
        }
        else
        {
            if (isset($popup))
            {
                $aPopup[]=$popup;
            }
            if (isset($vpopup))
            {
                $aPopup[]=$vpopup;
            }
            if (isset($fpopup))
            {
                $aPopup[]=$fpopup;
            }
        }
        Yii::app()->clientScript->registerScript('startPopup',"LSvar.startPopups=".json_encode($aPopup).";",CClientScript::POS_HEAD);
        Yii::app()->clientScript->registerScript('showStartPopups',"showStartPopups();",CClientScript::POS_END);

        $bShowpopups                            = Yii::app()->getConfig('showpopups');
        $aErrorHtmlMessage                      = $this->getErrorHtmlMessage();
        $thissurvey['errorHtml']['show']        = !empty($aErrorHtmlMessage);
        $thissurvey['errorHtml']['hiddenClass'] = $bShowpopups ? "ls-js-hidden ":"";
        $thissurvey['errorHtml']['messages']    = $aErrorHtmlMessage;

        $_gseq = -1;
        foreach ($_SESSION[$LEMsessid]['grouplist'] as $gl){

            $gid = $gl['gid'];
            ++$_gseq;
            $aGroup    = array();

            $groupname        = $gl['group_name'];
            $groupdescription = $gl['description'];

            if ($surveyMode != 'survey' && $gid != $onlyThisGID)
            {
                continue;
            }

            $redata = compact(array_keys(get_defined_vars()));
            Yii::app()->setConfig('gid',$gid);// To be used in templaterplace in whole group. Attention : it's the actual GID (not the GID of the question)

            $aGroup['class'] = "";

            $gnoshow = LimeExpressionManager::GroupIsIrrelevantOrHidden($_gseq);
            if  ($gnoshow && !$previewgrp)
            {
                $aGroup['class'] = ' ls-hidden';
            }

            $aGroup['name']             = $gl['group_name'];
            $aGroup['gseq']             = $_gseq;

            $showgroupinfo_global_ = getGlobalSetting('showgroupinfo');
            $aSurveyinfo           = getSurveyInfo($surveyid);

            // Look up if there is a global Setting to hide/show the Questiongroup => In that case Globals will override Local Settings
            if(($aSurveyinfo['showgroupinfo'] == $showgroupinfo_global_) || ($showgroupinfo_global_ == 'choose')){
                $showgroupinfo_ = $aSurveyinfo['showgroupinfo'];
            } else {
                $showgroupinfo_ = $showgroupinfo_global_;
            }

            $showgroupdesc_ = $showgroupinfo_ == 'B' /* both */ || $showgroupinfo_ == 'D'; /* (group-) description */

            $aGroup['showdescription']  = (!$previewquestion && trim($redata['groupdescription'])!="" && $showgroupdesc_);
            $aGroup['description']      = $redata['groupdescription'];

            foreach ($qanda as $qa) // one entry per QID
            {
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

                $n_q_display = '';
                if ($qinfo['hidden'] && $qinfo['info']['type'] != '*')
                {
                    continue; // skip this one
                }


                $aReplacement=array();
                $question = $qa[0];
                //===================================================================
                // The following four variables offer the templating system the
                // capacity to fully control the HTML output for questions making the
                // above echo redundant if desired.
                $question['sgq']  = $qa[7];
                $question['aid']  = !empty($qinfo['info']['aid']) ? $qinfo['info']['aid'] : 0;
                $question['sqid'] = !empty($qinfo['info']['sqid']) ? $qinfo['info']['sqid'] : 0;
                //===================================================================



                // question.twig

                // easier to understand for survey maker
                $aGroup['aQuestions'][$qid]['qid']                  = $qa[4];
                $aGroup['aQuestions'][$qid]['code']                 = $qa[5];
                $aGroup['aQuestions'][$qid]['number']               = $qa[0]['number'];
                $aGroup['aQuestions'][$qid]['text']                 = $qa[0]['text'];
                $aGroup['aQuestions'][$qid]['SGQ']                  = $qa[7];
                $aGroup['aQuestions'][$qid]['mandatory']            = $qa[0]['mandatory'];
                $aGroup['aQuestions'][$qid]['input_error_class']    = $qa[0]['input_error_class'];
                $aGroup['aQuestions'][$qid]['valid_message']        = $qa[0]['valid_message'];
                $aGroup['aQuestions'][$qid]['file_valid_message']   = $qa[0]['file_valid_message'];
                $aGroup['aQuestions'][$qid]['man_message']          = $qa[0]['man_message'];
                $aGroup['aQuestions'][$qid]['answer']               = $qa[1];
                $aGroup['aQuestions'][$qid]['help']['show']         = (flattenText( $lemQuestionInfo['info']['help'], true,true) != '');
                $aGroup['aQuestions'][$qid]['help']['text']         = $lemQuestionInfo['info']['help'];
            }

            $aGroup['show_last_group']   = $aGroup['show_last_answer']  = false;
            $aGroup['lastgroup']         = $aGroup['lastanswer']        = '';

            if (!empty($qanda)){
                if ($surveyMode == 'group') {
                    $aGroup['show_last_group']   = true;
                    $aGroup['lastgroup']         = $lastgroup;
                }
                if ($surveyMode == 'question') {
                    $aGroup['show_last_answer']   = true;
                    $aGroup['lastanswer']         = $lastanswer;
                }
            }

            /*
            $thissurvey['aGroup'] = $aGroup;
            $redata  = compact(array_keys(get_defined_vars()));
            echo templatereplace(file_get_contents($sTemplateViewPath."group.twig"), array(), $redata, false, false);
            */
            Yii::app()->setConfig('gid','');

            $thissurvey['aGroups'][$gid] = $aGroup;
        }


        LimeExpressionManager::FinishProcessingGroup($this->LEMskipReprocessing);
        $thissurvey['EM']['ScriptsAndHiddenInputs'] = LimeExpressionManager::GetRelevanceAndTailoringJavaScript();
        Yii::app()->clientScript->registerScript('triggerEmRelevance',"triggerEmRelevance();",CClientScript::POS_END);
        /* Maybe only if we have mandatory error ?*/
        Yii::app()->clientScript->registerScript('updateMandatoryErrorClass',"updateMandatoryErrorClass();",CClientScript::POS_END);
        LimeExpressionManager::FinishProcessingPage();

        /**
        * Navigator
        */
        $thissurvey['aNavigator'] = array();
        $thissurvey['aNavigator']['show'] = $aNavigator['show'] = $thissurvey['aNavigator']['save']['show'] = $thissurvey['aNavigator']['load']['show'] = false;

        if (!$previewgrp && !$previewquestion){
            $thissurvey['aNavigator']    = getNavigatorDatas();
            $thissurvey['hiddenInputs']  =  "<input type='hidden' name='thisstep' value='{$_SESSION[$LEMsessid]['step']}' id='thisstep' />\n";
            $thissurvey['hiddenInputs'] .=  "<input type='hidden' name='sid' value='$surveyid' id='sid' />\n";
            $thissurvey['hiddenInputs'] .= "<input type='hidden' name='start_time' value='" . time() . "' id='start_time' />\n";
            $_SESSION[$LEMsessid]['LEMpostKey'] = mt_rand();
            $thissurvey['hiddenInputs'] .= "<input type='hidden' name='LEMpostKey' value='{$_SESSION[$LEMsessid]['LEMpostKey']}' id='LEMpostKey' />\n";
            if (isset($token) && !empty($token))
            {
                $thissurvey['hiddenInputs'] .=  "\n<input type='hidden' name='token' value='$token' id='token' />\n";
            }
        }

        // For "clear all" buttons
        App()->getClientScript()->registerScript("activateConfirmLanguage","$.extend(LSvar.lang,".ls_json_encode(array('yes'=>gT("Yes"),'no'=>gT("No"))).")",CClientScript::POS_BEGIN);
        App()->getClientScript()->registerScript("activateActionLink","activateActionLink();\n",CClientScript::POS_END);
        App()->getClientScript()->registerScript("activateConfirmButton","activateConfirmButton();\n",CClientScript::POS_END);


        $thissurvey['aLEM']['debugtimming']['show'] = false;
        if (($this->LEMdebugLevel & LEM_DEBUG_TIMING) == LEM_DEBUG_TIMING){
            $thissurvey['aLEM']['debugtimming']['show'] = true;
            $thissurvey['aLEM']['debugtimming']['script'] = LimeExpressionManager::GetDebugTimingMessage();
        }

        $thissurvey['aLEM']['debugvalidation']['show'] = false;
        if (($this->LEMdebugLevel & LEM_DEBUG_VALIDATION_SUMMARY) == LEM_DEBUG_VALIDATION_SUMMARY){
            $thissurvey['aLEM']['debugvalidation']['show']   = true;
            $thissurvey['aLEM']['debugvalidation']['message'] = $moveResult['message'];
        }

        $redata  = compact(array_keys(get_defined_vars()));
        echo templatereplace(file_get_contents($sTemplateViewPath."layout.twig"), array(), $redata);
    }


    public function getShowNumAndCode($thissurvey)
    {

        $showqnumcode_global_ = getGlobalSetting('showqnumcode');
        $showqnumcode_survey_ = $thissurvey['showqnumcode'];

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
    //    $this->moveFirstChecks();                                               // If the move is clearcancel, or confirmquota, then the process will stop here

        if ($this->move != 'clearcancel' && $this->move != 'confirmquota'){
            $this->checkPrevStep();                                                 // Check if prev step is set, else set it
            $this->setMoveResult();
            $this->checkIfFinished();                                               // If $moveResult == finished, or not, various things to set

            // CHECK UPLOADED FILES
            // TMSW - Move this into LEM::NavigateForwards?
            $filenotvalidated = $this->filenotvalidated = checkUploadedFileValidity($this->surveyid, $this->move);

            //SEE IF THIS GROUP SHOULD DISPLAY
            if ($_SESSION[$this->LEMsessid]['step'] == 0)
                $show_empty_group = $this->show_empty_group = true;

            $move           = $this->move;
            $moveResult     = $this->moveResult;
            $totalquestions = $this->totalquestions = $_SESSION['survey_'.$this->surveyid]['totalquestions'];


            // For welcome screen
            $this->thissurvey['iTotalquestions']   = $totalquestions;
            $showxquestions                        = Yii::app()->getConfig('showxquestions');
            $this->thissurvey['bShowxquestions']   = ( $showxquestions == 'show' || ($showxquestions == 'choose' && !isset($thissurvey['showxquestions'])) || ($showxquestions == 'choose' && $thissurvey['showxquestions'] == 'Y'));
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
            'LEMdebugLevel'          => $this->LEMdebugLevel          ,
            'LEMskipReprocessing'    => $this->LEMskipReprocessing    ,
            'thissurvey'             => $this->thissurvey             ,
            'surveyid '              => $this->surveyid               ,
            'show_empty_group'       => $this->show_empty_group       ,
            'surveyMode'             => $this->surveyMode             ,
            'surveyOptions'          => $this->surveyOptions          ,
            'totalquestions'         => $this->totalquestions         ,
            'bTokenAnswerPersitance' => $this->bTokenAnswerPersitance ,
            'assessments'            => $this->assessments            ,
            'moveResult'             => $this->moveResult             ,
            'move'                   => $this->move                   ,
            'stepInfo'               => $this->stepInfo               ,
            'invalidLastPage'        => $this->invalidLastPage        ,
            'backpopup'              => $this->backpopup              ,
            'popup'                  => $this->popup                  ,
            'oResponse'              => $this->oResponse              ,
            'unansweredSQList'       => $this->unansweredSQList       ,
            'notanswered'            => $this->notanswered            ,
            'invalidSQList'          => $this->invalidSQList          ,
            'filenotvalidated'       => $this->filenotvalidated       ,
            'completed'              => $this->completed              ,
            'content'                => $this->content                ,
            'blocks'                 => $this->blocks                 ,
            'notvalidated'           => $this->notvalidated           ,
            'LEMsessid'              => $this->LEMsessid              ,
            'gid'                    => $this->gid                    ,
            'groupname'              => $this->groupname              ,
            'groupdescription'       => $this->groupdescription       ,
            'previewgrp'             => $this->previewgrp             ,
            'previewquestion'        => $this->previewquestion        ,
            'sTemplateViewPath'      => $this->sTemplateViewPath      ,
        );
        return $aPrivateVariables;
    }

    /**
     * Now it's ok ^^
     */
    private function setArgs()
    {

        $move              = $this->move;
        $moveResult        = $this->moveResult;
        $LEMsessid         = $this->LEMsessid;
        $thissurvey        = $this->thissurvey;
        $sTemplateViewPath = $this->sTemplateViewPath;

        if ($move == "movesubmit"){

            if ($thissurvey['refurl'] == "Y"){
                //Only add this if it doesn't already exist
                if (!in_array("refurl", $_SESSION[$LEMsessid]['insertarray'])){
                    $_SESSION[$LEMsessid]['insertarray'][] = "refurl";
                }
            }

            resetTimers();

            //Before doing the "templatereplace()" function, check the $thissurvey['url']
            //field for limereplace stuff, and do transformations!
            $thissurvey['surveyls_url'] = passthruReplace($thissurvey['surveyls_url'], $thissurvey);
            $thissurvey['surveyls_url'] = templatereplace($thissurvey['surveyls_url'], array(), $redata, 'URLReplace', false, NULL, array(), true );   // to do INSERTANS substitutions

            $this->thissurvey = $thissurvey;

            //THE FOLLOWING DEALS WITH SUBMITTING ANSWERS AND COMPLETING AN ACTIVE SURVEY
            //don't use cookies if tokens are being used
            if ($thissurvey['active'] == "Y"){
                if ($thissurvey['usecookie'] == "Y" && $tokensexist != 1) {
                    setcookie("LS_" . $surveyid . "_STATUS", "COMPLETE", time() + 31536000); //Cookie will expire in 365 days
                }
            }
        }
    }


    /**
     * Retreive the survey format (mode?)
     * TODO: move to survey model
     *
     * @param  array   $thissurvey (an array containg the datas of the dynamic survey model and its related language model )
     * @return string
     */
    private function getSurveyMode($thissurvey)
    {
        switch ($thissurvey['format'])
        {
            case "A": //All in one
                $surveyMode = 'survey';
                break;
            default:
            case "S": //One at a time
                $surveyMode = 'question';
                break;
            case "G": //Group at a time
                $surveyMode = 'group';
                break;
        }

        return $surveyMode;
    }

    /**
     * Retreive the radix
     * @param  array   $thissurvey (an array containg the datas of the dynamic survey model and its related language model )
     * @return string
     */
    private function getRadix($thissurvey)
    {
        $radix = getRadixPointData($thissurvey['surveyls_numberformat']);
        $radix = $radix['separator'];
        return $radix;
    }

    /**
     * Retreives dew options comming from thissurvey, App->getConfig, LEM.
     * TODO: move to survey model
     *
     * @param array $thissurvey     an array containing all the survey needed infos
     * @param int   $LEMdebugLevel  customizable debugging for Lime Expression Manager
     */
    private function getSurveyOptions($thissurvey, $LEMdebugLevel, $timeadjust, $clienttoken )
    {
        $LEMsessid  = $this->LEMsessid;
        $radix      = $this->getRadix($thissurvey);
        $surveyOptions = array(
            'active'                      => ($thissurvey['active'] == 'Y'),
            'allowsave'                   => ($thissurvey['allowsave'] == 'Y'),
            'anonymized'                  => ($thissurvey['anonymized'] != 'N'),
            'assessments'                 => ($thissurvey['assessments'] == 'Y'),
            'datestamp'                   => ($thissurvey['datestamp'] == 'Y'),
            'deletenonvalues'             => Yii::app()->getConfig('deletenonvalues'),
            'hyperlinkSyntaxHighlighting' => (($LEMdebugLevel & LEM_DEBUG_VALIDATION_SUMMARY) == LEM_DEBUG_VALIDATION_SUMMARY), // TODO set this to true if in admin mode but not if running a survey
            'ipaddr'                      => ($thissurvey['ipaddr'] == 'Y'),
            'radix'                       => $radix,
            'refurl'                      => (($thissurvey['refurl'] == "Y" && isset($_SESSION[$LEMsessid]['refurl'])) ? $_SESSION[$LEMsessid]['refurl'] : NULL),
            'savetimings'                 => ($thissurvey['savetimings'] == "Y"),
            'surveyls_dateformat'         => ( ($timeadjust!=0) ? $thissurvey['surveyls_dateformat'] : 1),
            'startlanguage'               => (isset(App()->language) ? App()->language : $thissurvey['language']),
            'target'                      => Yii::app()->getConfig('uploaddir').DIRECTORY_SEPARATOR.'surveys'.DIRECTORY_SEPARATOR.$thissurvey['sid'].DIRECTORY_SEPARATOR.'files'.DIRECTORY_SEPARATOR,
            'tempdir'                     => Yii::app()->getConfig('tempdir').DIRECTORY_SEPARATOR,
            'timeadjust'                  => $timeadjust,
            'token'                       => $clienttoken,
        );

        return $surveyOptions;
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
        // retrieve datas from local variable
        $surveyid      = $this->surveyid;
        $surveyMode    = $this->surveyMode;
        $surveyOptions = $this->surveyOptions;
        $LEMsessid     = $this->LEMsessid;

        // First time the survey is loaded
        if (!isset($_SESSION[$LEMsessid]['step']))
        {
            // Init session, randomization and filed array
            buildsurveysession($surveyid);
            randomizationGroupsAndQuestions($surveyid);
            initFieldArray($surveyid, $_SESSION['survey_' . $surveyid]['fieldmap']);        // NOTE: allready called in buildsurveysession !!!! TODO: check if can be removed

            // Check surveyid coherence
            if($surveyid != LimeExpressionManager::getLEMsurveyId())
                LimeExpressionManager::SetDirtyFlag();

            // Init $LEM states.
            LimeExpressionManager::StartSurvey($surveyid, $surveyMode, $surveyOptions, false, $this->LEMdebugLevel);
            $_SESSION[$LEMsessid]['step'] = 0;

            // Welcome page.
            if ($surveyMode == 'survey'){
                LimeExpressionManager::JumpTo(1, false, false, true);
            }elseif (isset($thissurvey['showwelcome']) && $thissurvey['showwelcome'] == 'N'){
                $moveResult                   = $this->moveResult = LimeExpressionManager::NavigateForwards();
                $_SESSION[$LEMsessid]['step'] = 1;
            }
        }elseif($surveyid != LimeExpressionManager::getLEMsurveyId()){
            $this->initDirtyStep();
        }

    }

    /**
     * If a step is requested, but the survey id in the session is different from the requested one
     * It reload the needed infos for the requested survey and jump to the requested step.
     */
    private function initDirtyStep()
    {
        // retrieve datas from local variable
        $surveyid      = $this->surveyid;
        $surveyMode    = $this->surveyMode;
        $surveyOptions = $this->surveyOptions;
        $LEMsessid     = $this->LEMsessid;

        //$_SESSION[$LEMsessid]['step'] can not be less than 0, fix it always #09772
        $_SESSION[$LEMsessid]['step']   = $_SESSION[$LEMsessid]['step']<0 ? 0 : $_SESSION[$LEMsessid]['step'];
        LimeExpressionManager::StartSurvey($surveyid, $surveyMode, $surveyOptions, false, $this->LEMdebugLevel);
        LimeExpressionManager::JumpTo($_SESSION[$LEMsessid]['step'], false, false);
    }

    /**
     * Seems to be a quick fix to avoid the total and max steps to be null...
     */
    private function initTotalAndMaxSteps()
    {
        $LEMsessid     = $this->LEMsessid;

        if (!isset($_SESSION[$LEMsessid]['totalsteps'])){
            $_SESSION[$LEMsessid]['totalsteps'] = 0;
        }

        if (!isset($_SESSION[$LEMsessid]['maxstep'])){
            $_SESSION[$LEMsessid]['maxstep'] = 0;
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
        $surveyid      = $this->surveyid;
        $LEMsessid     = $this->LEMsessid;

        if (isset($_SESSION[$LEMsessid]['LEMpostKey']) && App()->request->getPost('LEMpostKey',$_SESSION[$LEMsessid]['LEMpostKey']) != $_SESSION[$LEMsessid]['LEMpostKey']){
            // then trying to resubmit (e.g. Next, Previous, Submit) from a cached copy of the page
            $moveResult = $this->moveResult = LimeExpressionManager::JumpTo($_SESSION[$LEMsessid]['step'], false, false, true);// We JumpTo current step without saving: see bug #11404

            if (isset($moveResult['seq']) &&  App()->request->getPost('thisstep',$moveResult['seq']) == $moveResult['seq']){

                /* then pressing F5 or otherwise refreshing the current page, which is OK
                 * Seems OK only when movenext but not with move by index : same with $moveResult = LimeExpressionManager::GetLastMoveResult(true);
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
        $surveyid      = $this->surveyid;

        /* quota submitted */
        if ( $move=='confirmquota'){
            checkCompletedQuota($surveyid);
        }
    }

    /**
     * Check if the move is clearcancel or confirmquota
     */
    private function checkClearCancel()
    {
        $move          = $this->move;
        $LEMsessid     = $this->LEMsessid;

        if ( $move=="clearcancel"){
            $moveResult = $this->moveResult = LimeExpressionManager::JumpTo($_SESSION[$LEMsessid]['step'], false, false);
        }

        $_SESSION[$LEMsessid]['prevstep'] = (!in_array($move,array("clearall","changelang","saveall","reload")))?$_SESSION[$LEMsessid]['step']:$move; // Accepted $move without error
    }

    /**
     * Define prev step if not set in session.
     */
    private function checkPrevStep()
    {
        $LEMsessid     = $this->LEMsessid;

        if (!isset($_SESSION[$LEMsessid]['prevstep'])){
            $_SESSION[$LEMsessid]['prevstep'] = $_SESSION[$LEMsessid]['step']-1;   // this only happens on re-load
        }
    }

    /**
     * Set the moveResult variable, depending on the user move request
     */
    private function setMoveResult()
    {

        // retrieve datas from local variable
        $thissurvey             = $this->thissurvey;
        $surveyid               = $this->surveyid;
        $surveyMode             = $this->surveyMode;
        $surveyOptions          = $this->surveyOptions;
        $LEMsessid              = $this->LEMsessid;
        $move                   = $this->move;
        $LEMskipReprocessing    = $this->LEMskipReprocessing;

        if (isset($_SESSION[$LEMsessid]['LEMtokenResume'])){

            LimeExpressionManager::StartSurvey($thissurvey['sid'], $surveyMode, $surveyOptions, false, $this->LEMdebugLevel);

            // Do it only if needed : we don't need it if we don't have index
            if(isset($_SESSION[$LEMsessid]['maxstep']) && $_SESSION[$LEMsessid]['maxstep']>$_SESSION[$LEMsessid]['step'] && $thissurvey['questionindex'] ){
                LimeExpressionManager::JumpTo($_SESSION[$LEMsessid]['maxstep'], false, false);
            }

            $moveResult = $this->moveResult =  LimeExpressionManager::JumpTo($_SESSION[$LEMsessid]['step'],false,false);   // if late in the survey, will re-validate contents, which may be overkill

            unset($_SESSION[$LEMsessid]['LEMtokenResume']);
        }else if (!$LEMskipReprocessing){

            //Move current step ###########################################################################
            if ($move == 'moveprev' && ($thissurvey['allowprev'] == 'Y' || $thissurvey['questionindex'] > 0)){
                $moveResult = $this->moveResult = LimeExpressionManager::NavigateBackwards();

                if ($moveResult['at_start']){
                    $_SESSION[$LEMsessid]['step'] = 0;
                    unset($moveResult); // so display welcome page again
                    unset($this->moveResult);
                }
            }

            if ( $move == "movenext"){
                $moveResult = $this->moveResult = LimeExpressionManager::NavigateForwards();
            }

            if (($move == 'movesubmit')){
                if ($surveyMode == 'survey'){
                    $moveResult = $this->moveResult =  LimeExpressionManager::NavigateForwards();
                }else{
                    // may be submitting from the navigation bar, in which case need to process all intervening questions
                    // in order to update equations and ensure there are no intervening relevant mandatory or relevant invalid questions
                    if($thissurvey['questionindex']==2) // Must : save actual page , review whole before set finished to true (see #09906), index==1 seems to don't need it : (don't force move)
                        LimeExpressionManager::StartSurvey($surveyid, $surveyMode, $surveyOptions);

                    $moveResult = $this->moveResult = LimeExpressionManager::JumpTo($_SESSION[$LEMsessid]['totalsteps'] + 1, false);
                }
            }
            if ( $move=='clearall'){
                $this->manageClearAll();
            }
            if ( $move=='changelang'){
                // jump to current step using new language, processing POST values
                $moveResult = $this->moveResult = LimeExpressionManager::JumpTo($_SESSION[$LEMsessid]['step'], false, true, true, true);  // do process the POST data
            }

            if (isNumericInt($move) && $thissurvey['questionindex'] == 1){
                $move = $this->move = (int) $move;

                if ($move > 0 && (($move <= $_SESSION[$LEMsessid]['step']) || (isset($_SESSION[$LEMsessid]['maxstep']) && $move <= $_SESSION[$LEMsessid]['maxstep']))){
                    $moveResult = $this->moveResult = LimeExpressionManager::JumpTo($move, false);
                }
            }
            elseif ( isNumericInt($move) && $thissurvey['questionindex'] == 2){
                $move       = $this->move       = (int) $move;
                $moveResult = $this->moveResult = LimeExpressionManager::JumpTo($move, false, true, true);
            }

            if (!isset($moveResult) && !($surveyMode != 'survey' && $_SESSION[$LEMsessid]['step'] == 0)){
                // Just in case not set via any other means, but don't do this if it is the welcome page
                $moveResult          = $this->moveResult          = LimeExpressionManager::GetLastMoveResult(true);
                $LEMskipReprocessing = $this->LEMskipReprocessing = true;
            }
        }
    }

    /**
     * Test if the the moveresult is finished, to decide to set the new $move value
     */
    private function checkIfFinished()
    {
        // retrieve datas from local variable
        $surveyid      = $this->surveyid;
        $surveyMode    = $this->surveyMode;
        $surveyOptions = $this->surveyOptions;
        $move          = $this->move;
        $moveResult    = $this->moveResult;
        $LEMsessid     = $this->LEMsessid;

        // Reload at first page (welcome after click previous fill an empty $moveResult array
        if (isset($moveResult) && isset($moveResult['seq']) ){
            // With complete index, we need to revalidate whole group bug #08806. It's actually the only mode where we JumpTo with force
            // we already done if move == 'movesubmit', don't do it again
            if($moveResult['finished'] == true && $move != 'movesubmit' && $thissurvey['questionindex']==2){
                //LimeExpressionManager::JumpTo(-1, false, false, true);
                LimeExpressionManager::StartSurvey($surveyid, $surveyMode, $surveyOptions);
                $moveResult = $this->moveResult = LimeExpressionManager::JumpTo($_SESSION[$LEMsessid]['totalsteps']+1, false, false, false);// no preview, no save data and NO force
                if(!$moveResult['mandViolation'] && $moveResult['valid'] && empty($moveResult['invalidSQs'])){
                    $moveResult['finished'] = true;
                    $this->moveResult = $moveResult;
                }
            }

            if ($moveResult['finished'] == true){
                $move = $this->move = 'movesubmit';
            }else{
                $_SESSION[$LEMsessid]['step'] = $moveResult['seq'] + 1;  // step is index base 1
                $stepInfo                     = $this->stepInfo =  LimeExpressionManager::GetStepIndexInfo($moveResult['seq']);
            }

            if ($move == "movesubmit" && $moveResult['finished'] == false){
                // then there are errors, so don't finalize the survey
                $move            = $this->move            = "movenext"; // so will re-display the survey
                $invalidLastPage = $this->invalidLastPage = true;
            }
        }

    }

    /**
     * Display the first page if needed
     */
    private function displayFirstPageIfNeeded()
    {
        // retrieve datas from local variable
        $surveyMode    = $this->surveyMode;
        $LEMsessid     = $this->LEMsessid;

        // We do not keep the participant session anymore when the same browser is used to answer a second time a survey (let's think of a library PC for instance).
        // Previously we used to keep the session and redirect the user to the
        // submit page.
        if ($surveyMode != 'survey' && $_SESSION[$LEMsessid]['step'] == 0){
            $_SESSION[$LEMsessid]['test']=time();
            display_first_page($this->thissurvey);
            Yii::app()->end(); // So we can still see debug messages
        }
    }

    /**
     * Perform save all if user asked for it
     */
    private function saveAllIfNeeded()
    {
        // retrieve datas from local variable
        $thissurvey    = $this->thissurvey;
        $surveyid      = $this->surveyid;
        $LEMsessid     = $this->LEMsessid;

        // TODO FIXME
         // Don't test if save is allowed
        if ($thissurvey['active'] == "Y" && Yii::app()->request->getPost('saveall')){
            $bTokenAnswerPersitance = $this->bTokenAnswerPersitance = $thissurvey['tokenanswerspersistence'] == 'Y' && $surveyid!=null && tableExists('tokens_'.$surveyid);

            // must do this here to process the POSTed values
            $moveResult = $this->moveResult = LimeExpressionManager::JumpTo($_SESSION[$LEMsessid]['step'], false);   // by jumping to current step, saves data so far

            if (!isset($_SESSION[$LEMsessid]['scid']) && !$bTokenAnswerPersitance ){
                Yii::import("application.libraries.Save");
                $cSave = new Save();
                // $cSave->showsaveform($thissurvey['sid']); // generates a form and exits, awaiting input
                $thissurvey['aSaveForm'] = $cSave->getSaveFormDatas($thissurvey['sid']);
                $redata = compact(array_keys(get_defined_vars()));
                echo templatereplace(file_get_contents($this->sTemplateViewPath."layout_save.twig"), array(), $redata);
                Yii::app()->end();
            }else{
                // Intentional retest of all conditions to be true, to make sure we do have tokens and surveyid
                // Now update lastpage to $_SESSION[$LEMsessid]['step'] in SurveyDynamic, otherwise we land on
                // the previous page when we return.
                $iResponseID         = $_SESSION[$LEMsessid]['srid'];
                $oResponse           = SurveyDynamic::model($surveyid)->findByPk($iResponseID);
                $oResponse->lastpage = $_SESSION[$LEMsessid]['step'];
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
        // retrieve datas from local variable
        $thissurvey    = $this->thissurvey;

        if ($thissurvey['active'] == "Y" && Yii::app()->request->getParam('savesubmit') ){
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
                $thissurvey['aSaveForm'] = $cSave->getSaveFormDatas($thissurvey['sid']);
                $redata                  = compact(array_keys(get_defined_vars()));
                echo templatereplace(file_get_contents($this->sTemplateViewPath."layout_save.twig"), array(), $redata);
                Yii::app()->end();
            }

            $moveResult          = $this->moveResult          = LimeExpressionManager::GetLastMoveResult(true);
            $LEMskipReprocessing = $this->LEMskipReprocessing = true;
        }
    }

    /**
     * check mandatory questions if necessary
     * CHECK IF ALL CONDITIONAL MANDATORY QUESTIONS THAT APPLY HAVE BEEN ANSWERED
     */
    private function setNotAnsweredAndNotValidated()
    {
        // retrieve datas from local variable
        $moveResult    = $this->moveResult;

        global $notanswered;
        $this->notvalidated = $notanswered;

        if (!$moveResult['finished']){
            $unansweredSQList = $this->unansweredSQList = $moveResult['unansweredSQs'];
            if (strlen($unansweredSQList) > 0){
                $notanswered = $this->notanswered =explode('|', $unansweredSQList);
            }else{
                $notanswered = $this->notanswered = array();
            }
            //CHECK INPUT
            $invalidSQList = $this->invalidSQList = $moveResult['invalidSQs'];
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
        $surveyid          = $this->surveyid;
        $surveyMode        = $this->surveyMode;
        $surveyOptions     = $this->surveyOptions;
        $move              = $this->move;
        $moveResult        = $this->moveResult;
        $LEMsessid         = $this->LEMsessid;
        $LEMdebugLevel     = $this->LEMdebugLevel;
        $thissurvey        = $this->thissurvey;
        $sTemplateViewPath = $this->sTemplateViewPath;

        if ($move == "movesubmit"){

            if ($thissurvey['active'] != "Y"){

                sendCacheHeaders();
                doHeader();

                echo templatereplace(file_get_contents($sTemplateViewPath."startpage.pstpl"), array(), $redata, 'SubmitStartpageI', false, NULL, array(), true );

                //Check for assessments

                // TODO: TWIG ASSESSMENTS !!!!!
                if ($thissurvey['assessments'] == "Y"){
                    $assessments = $this->assessments = doAssessment($thissurvey['sid']);
                    if ($assessments ) {
                        echo templatereplace(file_get_contents($sTemplateViewPath."assessment.pstpl"), array(), $redata, 'SubmitAssessmentI', false, NULL, array(), true );
                    }
                }

                // can't kill session before end message, otherwise INSERTANS doesn't work.
                $completed  = templatereplace($thissurvey['surveyls_endtext'], array(), $redata, 'SubmitEndtextI', false, NULL, array(), true );
                $completed .= "<br /><strong><font size='2' color='red'>" . gT("Did Not Save") . "</font></strong><br /><br />\n\n";
                $completed .= gT("Your survey responses have not been recorded. This survey is not yet active.") . "<br /><br />\n";

                if ($thissurvey['printanswers'] == 'Y'){
                    // 'Clear all' link is only relevant for survey with printanswers enabled
                    // in other cases the session is cleared at submit time
                    $completed .= "<a href='" . Yii::app()->getController()->createUrl("survey/index/sid/{$surveyid}/move/clearall") . "'>" . gT("Clear Responses") . "</a><br /><br />\n";
                }

                $this->completed = $completed;

            }else{

                //Update the token if needed and send a confirmation email
                if (isset($_SESSION['survey_'.$surveyid]['token'])){
                    submittokens();
                }

                //Send notifications
                sendSubmitNotifications($surveyid);

                //Check for assessments
                $thissurvey['aAssessments']['show'] = false;
                if ($thissurvey['assessments'] == "Y"){
                    $thissurvey['aAssessments']['show'] = true;

                    // TODO : TWIG
                    //$assessments = $this->assessments = doAssessment($surveyid);
                    $thissurvey['aAssessments'] = $this->assessments = doAssessment($surveyid, true);
                }

                // End text
                if (trim(str_replace(array('<p>','</p>'),'',$thissurvey['surveyls_endtext'])) == ''){
                    $thissurvey['aCompleted']['showDefault']=true;
                }else{
                    $thissurvey['aCompleted']['showDefault']=false;
                    // NOTE: this occurence of template replace should stay here. User from backend could use old replacement keyword
                    $thissurvey['aCompleted']['sEndText'] = templatereplace($thissurvey['surveyls_endtext'], array(), $redata, 'SubmitAssessment', false, NULL, array(), true );
                }

                // Link to Print Answer Preview  **********
                $thissurvey['aCompleted']['aPrintAnswers']['show'] = false;
                if ($thissurvey['printanswers'] == 'Y'){
                    $thissurvey['aCompleted']['aPrintAnswers']['show']  = true;
                    $thissurvey['aCompleted']['aPrintAnswers']['sUrl']  = Yii::app()->getController()->createUrl("/printanswers/view",array('surveyid'=>$surveyid));
                    $thissurvey['aCompleted']['aPrintAnswers']['sText'] = "Print your answers.";
                }

                // Link to Public statistics  **********
                $thissurvey['aCompleted']['aPublicStatistics']['show'] = false;
                if ($thissurvey['publicstatistics'] == 'Y'){
                    $thissurvey['aCompleted']['aPublicStatistics']['show']  = true;
                    $thissurvey['aCompleted']['aPublicStatistics']['sUrl']  = Yii::app()->getController()->createUrl("/statistics_user/action/",array('surveyid'=>$surveyid,'language'=>App()->getLanguage()));

                }

                $this->completed = true;

                //*****************************************

                $_SESSION[$LEMsessid]['finished'] = true;
                $_SESSION[$LEMsessid]['sid']      = $surveyid;

                if (isset($thissurvey['autoredirect']) && $thissurvey['autoredirect'] == "Y" && $thissurvey['surveyls_url']){
                    //Automatically redirect the page to the "url" setting for the survey
                    header("Location: {$thissurvey['surveyls_url']}");
                }

            }

            $redata['completed'] = $this->completed;

            // @todo Remove direct session access.
            $event = new PluginEvent('afterSurveyComplete');

            if (isset($_SESSION[$LEMsessid]['srid'])){
                $event->set('responseId', $_SESSION[$LEMsessid]['srid']);
            }

            $event->set('surveyId', $surveyid);
            App()->getPluginManager()->dispatchEvent($event);
            $blocks = array();

            foreach ($event->getAllContent() as $blockData){
                /* @var $blockData PluginEventContent */
                $blocks[] = CHtml::tag('div', array('id' => $blockData->getCssId(), 'class' => $blockData->getCssClass()), $blockData->getContent());
            }


            $this->blocks = $blocks;
            $thissurvey['aCompleted']['sPluginHTML'] = implode("\n", $blocks) ."\n";
            $thissurvey['aCompleted']['sSurveylsUrl'] = $thissurvey['surveyls_url'];


            $thissurvey['aLEM']['debugvalidation']['show'] = false;
            if (($LEMdebugLevel & LEM_DEBUG_VALIDATION_SUMMARY) == LEM_DEBUG_VALIDATION_SUMMARY){
                $thissurvey['aLEM']['debugvalidation']['show']   = true;
                $thissurvey['aLEM']['debugvalidation']['message'] = $moveResult['message'];
            }

            $thissurvey['aLEM']['debugvalidation']['show'] = false; $thissurvey['aLEM']['debugvalidation']['message'] = '';
            if ((($LEMdebugLevel & LEM_DEBUG_TIMING) == LEM_DEBUG_TIMING)){
                $thissurvey['aLEM']['debugvalidation']['show']     = true;
                $thissurvey['aLEM']['debugvalidation']['message'] .= LimeExpressionManager::GetDebugTimingMessage();;
            }

            if ((($LEMdebugLevel & LEM_DEBUG_VALIDATION_SUMMARY) == LEM_DEBUG_VALIDATION_SUMMARY)){
                $thissurvey['aLEM']['debugvalidation']['message'] .= "<table><tr><td align='left'><b>Group/Question Validation Results:</b>" . $moveResult['message'] . "</td></tr></table>\n";
            }

            // The session cannot be killed until the page is completely rendered
            if ($thissurvey['printanswers'] != 'Y'){
                killSurveySession($surveyid);
            }

            $redata  = compact(array_keys(get_defined_vars()));
            echo templatereplace(file_get_contents($sTemplateViewPath."layout-submit.twig"), array(), $redata);
            exit;
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
        $this->LEMdebugLevel          = isset( $LEMdebugLevel          )?$LEMdebugLevel          :null ;
        $this->LEMskipReprocessing    = isset( $LEMskipReprocessing    )?$LEMskipReprocessing    :null ;
        $this->thissurvey             = isset( $thissurvey             )?$thissurvey             :null ;
        $this->surveyid               = isset( $surveyid               )?$surveyid               :null ;
        $this->show_empty_group       = isset( $show_empty_group       )?$show_empty_group       :null ;
        $this->surveyMode             = isset( $surveyMode             )?$surveyMode             :null ;
        $this->surveyOptions          = isset( $surveyOptions          )?$surveyOptions          :null ;
        $this->totalquestions         = isset( $totalquestions         )?$totalquestions         :null ;
        $this->bTokenAnswerPersitance = isset( $bTokenAnswerPersitance )?$bTokenAnswerPersitance :null ;
        $this->assessments            = isset( $assessments            )?$assessments            :null ;
        $this->moveResult             = isset( $moveResult             )?$moveResult             :null ;
        $this->move                   = isset( $move                   )?$move                   :null ;
        $this->invalidLastPage        = isset( $invalidLastPage        )?$invalidLastPage        :null ;
        $this->backpopup              = isset( $backpopup              )?$backpopup              :null ;
        $this->popup                  = isset( $popup                  )?$popup                  :null ;
        $this->oResponse              = isset( $oResponse              )?$oResponse              :null ;
        $this->unansweredSQList       = isset( $unansweredSQList       )?$unansweredSQList       :null ;
        $this->notanswered            = isset( $notanswered            )?$notanswered            :null ;
        $this->invalidSQList          = isset( $invalidSQList          )?$invalidSQList          :null ;
        $this->filenotvalidated       = isset( $filenotvalidated       )?$filenotvalidated       :null ;
        $this->completed              = isset( $completed              )?$completed              :null ;
        $this->content                = isset( $content                )?$content                :null ;
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

            $sLSJavascriptVar="LSvar=".json_encode($aLSJavascriptVar) . ';';
            App()->clientScript->registerScript('sLSJavascriptVar',$sLSJavascriptVar,CClientScript::POS_HEAD);
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
     * @uses $this->surveyid
     * @uses $this->sTemplateViewPath
     * @return void
     */
    private function manageClearAll()
    {
        /* Maybe nest is ro move this in SurveyController */
        $sessionSurvey=Yii::app()->session["survey_{$this->surveyid}"];
        if(App()->request->getPost('confirm-clearall')=='confirm'){ // Previous behaviour (and javascript behaviour)
            // delete the existing response but only if not already completed
            if (
                isset($sessionSurvey['srid'])
                && !SurveyDynamic::model($this->surveyid)->isCompleted($sessionSurvey['srid']) // see bug https://bugs.limesurvey.org/view.php?id=11978
            ){
                $oResponse=Response::model($this->surveyid)->find("id=:srid",array(":srid"=>$sessionSurvey['srid']));
                if($oResponse){
                    $oResponse->delete(true);/* delete response line + files uploaded , warninbg : beforeDelete don't happen with deleteAll */
                }
                if(Survey::model()->findByPk($this->surveyid)->savetimings=="Y"){
                    SurveyTimingDynamic::model($this->surveyid)->deleteAll("id=:srid",array(":srid"=>$sessionSurvey['srid'])); /* delete timings ( @todo must move it to Response )*/
                }
                SavedControl::model()->deleteAll("sid=:sid and srid=:srid",array(":sid"=>$this->surveyid,":srid"=>$sessionSurvey['srid']));/* saved controls (think we can have only one , but maybe ....)( @todo must move it to Response )*/
            }
            killSurveySession($this->surveyid);
            $content=templatereplace(file_get_contents($this->sTemplateViewPath."clearall.pstpl"),array());
            App()->getController()->layout='survey';
            App()->getController()->render("/survey/system/display",array('content'=>$content));
            App()->end();
        }elseif(App()->request->getPost('confirm-clearall')!='cancel'){
            LimeExpressionManager::JumpTo($sessionSurvey['step'], false, true, true, false);  // do process the POST data
            App()->getController()->layout="survey";
            App()->getController()->bStartSurvey=true;

            $aReplacements=array();
            $aReplacements['FORMID'] = 'clearall';
            $aReplacements['FORMHEADING'] = App()->getController()->renderPartial("/survey/frontpage/clearallForm/heading",array(),true);
            $aReplacements['FORMMESSAGE'] = App()->getController()->renderPartial("/survey/frontpage/clearallForm/message",array(),true);
            $aReplacements['FORMERROR'] = "";
            $aReplacements['FORM'] = CHtml::beginForm(array("/survey/index","sid"=>$this->surveyid), 'post',array('id'=>'form-'.$aReplacements['FORMID'],'class'=>'ls-form'));
            $aReplacements['FORM'].= CHtml::hiddenField('move','clearall',array());
            $aReplacements['FORM'].= App()->getController()->renderPartial("/survey/frontpage/clearallForm/form",array(),true);
            $aReplacements['FORM'].= CHtml::hiddenField('thisstep',$sessionSurvey['step']);
            $aReplacements['FORM'].= CHtml::hiddenField('sid',$this->surveyid);
            $aReplacements['FORM'].= CHtml::endForm();
            $content = templatereplace(file_get_contents($this->sTemplateViewPath."form.pstpl"),$aReplacements);
            App()->getController()->render("/survey/system/display",array(
                'content'=>$content,
            ));
            Yii::app()->end();
        }
    }

    /**
     * NOTE: right now, captcha works ONLY if reloaded... need to be debug.
     * NOTE: I bet we have the same problem on 2.6x.x
     * NOTE: when token + captcha: works fine
     */
    private function showTokenOrCaptchaFormsIfNeeded()
    {

        $thissurvey = $this->thissurvey;
        $surveyid   = $thissurvey['sid'];
        $sLangCode  = App()->language;
        $preview    = $this->preview;

        // Template settings
        $oTemplate         = $this->template;
        $sTemplatePath     = $oTemplate->path;
        $sTemplateViewPath = $oTemplate->pstplPath;


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
            "captchaRequired" => (isCaptchaEnabled('surveyaccessscreen',$thissurvey['usecaptcha']) && !isset($_SESSION['survey_'.$surveyid]['captcha_surveyaccessscreen']))
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
            if ($thissurvey['alloweditaftercompletion'] == 'Y' ) {
                $oTokenEntry = Token::model($surveyid)->findByAttributes(array('token'=>$clienttoken));
            } else {
                $oTokenEntry = Token::model($surveyid)->usable()->incomplete()->findByAttributes(array('token' => $clienttoken));
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
        $aEnterTokenData['iSurveyId']       = $surveyid;
        $aEnterTokenData['sLangCode']       = $sLangCode;

        if (isset($_GET['bNewTest']) && $_GET['newtest'] == "Y"){
            $aEnterTokenData['bNewTest'] =  true;
        }

        // If this is a direct Reload previous answers URL, then add hidden fields
        if (isset($loadall) && isset($scid) && isset($loadname) && isset($loadpass)) {
            $aEnterTokenData['bDirectReload'] =  true;
            $aEnterTokenData['sCid'] =  $scid;
            $aEnterTokenData['sLoadname'] =  htmlspecialchars($loadname);
            $aEnterTokenData['sLoadpass'] =  htmlspecialchars($loadpass);
        }

        $aEnterErrors=array();
        // Scenario => Token required
        if ($scenarios['tokenRequired'] && !$preview){
            //Test if token is valid
            list($renderToken, $FlashError) = testIfTokenIsValid($subscenarios, $thissurvey, $aEnterTokenData, $clienttoken);
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
                $_SESSION['survey_'.$surveyid]['captcha_surveyaccessscreen'] = true;
                $renderCaptcha = 'correct';
            }
        }

        // Scenario => Token required
        if ($scenarios['tokenRequired'] && !$preview){
            //Test if token is valid
            list($renderToken, $FlashError, $aEnterTokenData) = testIfTokenIsValid($subscenarios, $thissurvey, $aEnterTokenData, $clienttoken);
        }

        if (isset($FlashError) && $FlashError !== ""){
            $aEnterErrors['flash'] = $FlashError;
        }

        $aEnterTokenData['aEnterErrors']    = $aEnterErrors;
        $renderWay                          = getRenderWay($renderToken, $renderCaptcha);
        $redata                             = compact(array_keys(get_defined_vars()));

        /* This funtion end if an form need to be shown */
        renderRenderWayForm($renderWay, $redata, $scenarios, $sTemplateViewPath, $aEnterTokenData, $surveyid);

    }


    private function initTemplate()
    {
        $oTemplate         = $this->template          = Template::model()->getInstance('', $this->surveyid);
        $sTemplateViewPath = $this->sTemplateViewPath = $oTemplate->pstplPath;
        $oTemplate->registerAssets();
        Yii::app()->twigRenderer->setForcedPath($sTemplateViewPath);
    }

    private function makeLanguageChanger()
    {

        $thissurvey = $this->thissurvey;

        $thissurvey['alanguageChanger']['show']  = false;
        $alanguageChangerDatas                   = getLanguageChangerDatas($this->sLangCode);

        if ($alanguageChangerDatas){
            $thissurvey['alanguageChanger']['show']  = true;
            $thissurvey['alanguageChanger']['datas'] = $alanguageChangerDatas;
        }

        $this->thissurvey = $thissurvey;
    }

    /**
     * This method will set survey values in public property of the class
     * So, any value here set as $this->xxx will be available as $xxx after :
     * $aPrivateVariables = $this->getArgs(); extract($aPrivateVariables);
     * eg: $LEMsessid
     *
     */
    private function setSurveySettings( $surveyid, $args  )
    {
        $this->setVarFromArgs($args);                                           // Set the private variable from $args
        $this->initTemplate();                                                  // Template settings
        $this->setJavascriptVar();
        $this->setArgs();

        extract($args);

        $LEMsessid                  = $this->LEMsessid = 'survey_' . $surveyid;
        $thissurvey                 = (!$thissurvey)?getSurveyInfo($surveyid):$thissurvey;
        $thissurvey['surveyUrl']    = App()->createUrl("/survey/index",array("sid"=>$surveyid));
        $thissurvey['oTemplate']    = (array) $this->template;
        $this->thissurvey           = $thissurvey;
        $surveyMode                 = $this->surveyMode      = $this->getSurveyMode($thissurvey);
        $surveyOptions              = $this->surveyOptions   = $this->getSurveyOptions($thissurvey, $this->LEMdebugLevel, (isset($timeadjust)? $timeadjust : 0), (isset($clienttoken)?$clienttoken : NULL) );
        $previewgrp                 = $this->previewgrp      = ($surveyMode == 'group' && isset($param['action'])    && ($param['action'] == 'previewgroup'))    ? true : false;
        $previewquestion            = $this->previewquestion = ($surveyMode == 'question' && isset($param['action']) && ($param['action'] == 'previewquestion')) ? true : false;
        $preview                    = $this->preview         = ($previewquestion || $previewgrp);
        $sLangCode                  = $this->sLangCode       = App()->language;
        $show_empty_group           = $this->show_empty_group;
    }

    private function setPreview()
    {
        $aPrivateVariables = $this->getArgs();
        extract($aPrivateVariables);


        $_SESSION[$this->LEMsessid]['prevstep'] = 2;
        $_SESSION[$this->LEMsessid]['maxstep']  = 0;

        if ($this->previewquestion){
            $_SESSION[$LEMsessid]['step'] = 0; //maybe unset it after the question has been displayed?
        }

        if ($surveyMode == 'group' && $previewgrp){
            $_gid = sanitize_int($param['gid']);

            LimeExpressionManager::StartSurvey($thissurvey['sid'], 'group', $surveyOptions, false, $this->LEMdebugLevel);
            $gseq = LimeExpressionManager::GetGroupSeq($_gid);

            if ($gseq == -1){
                $sMessage = gT('Invalid group number for this survey: ') . $_gid;
                renderError('', $sMessage, $thissurvey, $sTemplateViewPath );
            }

            $moveResult = $this->moveResult = LimeExpressionManager::JumpTo($gseq + 1, true);
            if (is_null($moveResult)){
                $sMessage = gT('This group contains no questions.  You must add questions to this group before you can preview it');
                renderError('', $sMessage, $thissurvey, $sTemplateViewPath );
            }

            if (isset($moveResult)){
                $_SESSION[$LEMsessid]['step'] = $moveResult['seq'] + 1;  // step is index base 1?
            }

            $stepInfo         = $this->stepInfo         = LimeExpressionManager::GetStepIndexInfo($moveResult['seq']);
            $gid              = $this->gid              = $stepInfo['gid'];
            $groupname        = $this->groupname        = $stepInfo['gname'];
            $groupdescription = $this->groupdescription = $stepInfo['gtext'];

        }elseif($surveyMode == 'question' && $previewquestion){
                $_qid       = sanitize_int($param['qid']);
                LimeExpressionManager::StartSurvey($surveyid, 'question', $surveyOptions, false, $this->LEMdebugLevel);
                $qSec       = LimeExpressionManager::GetQuestionSeq($_qid);
                $moveResult = $this->moveResult= LimeExpressionManager::JumpTo($qSec+1,true,false,true);
                $stepInfo   = $this->stepInfo = LimeExpressionManager::GetStepIndexInfo($moveResult['seq']);
        }
    }


    private function setGroup()
    {
        $aPrivateVariables = $this->getArgs();
        extract($aPrivateVariables);

        if ( !$this->previewgrp && !$this->previewquestion)
        {
            if (($show_empty_group) || !isset($_SESSION[$LEMsessid]['grouplist'])){
                $this->gid              = -1; // Make sure the gid is unused. This will assure that the foreach (fieldarray as ia) has no effect.
                $this->groupname        = gT("Submit your answers");
                $this->groupdescription = gT("There are no more questions. Please press the <Submit> button to finish this survey.");
            }
            else if ($surveyMode != 'survey')
            {
                $stepInfo         = $this->stepInfo = LimeExpressionManager::GetStepIndexInfo($moveResult['seq']);
                $this->gid              = $stepInfo['gid'];
                $this->groupname        = $stepInfo['gname'];
                $this->groupdescription = $stepInfo['gtext'];
            }
        }
    }

    private function fixMaxStep()
    {
        // NOTE: must stay after setPreview  because of ()$surveyMode == 'group' && $previewgrp) condition touching step
        if ($_SESSION[$this->LEMsessid]['step'] > $_SESSION[$this->LEMsessid]['maxstep'])
        {
            $_SESSION[$this->LEMsessid]['maxstep'] = $_SESSION[$this->LEMsessid]['step'];
        }
    }

}
