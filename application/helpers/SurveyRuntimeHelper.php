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


    protected function createFullQuestionIndex(SurveySession $session)
    {
        if ($session->format == Survey::FORMAT_GROUP)
        {
            $this->createFullQuestionIndexByGroup($session);
        }
        else
        {
            $this->createFullQuestionIndexByQuestion($session);
        }

    }

    protected function createFullQuestionIndexByGroup(SurveySession $session)
    {
        $ssm = App()->surveySessionManager;
        echo "\n\n<!-- PRESENT THE INDEX -->\n";
        echo CHtml::openTag('div', array('id' => 'index'));
        echo CHtml::openTag('div', array('class' => 'container'));
        echo CHtml::tag('h2', array(), gT("Question index"));
        echo CHtml::openTag('ol');
        /**
         * @var int $key
         * @var QuestionGroup $group
         */
        foreach ($session->getGroups() as $key => $group)
        {
            // Better to use tracevar /
            if (LimeExpressionManager::GroupIsRelevant($group->primaryKey))
            {
                $step = $key + 1;
                $stepInfo = LimeExpressionManager::singleton()->validateGroup($key);
                $classes = implode(' ', array(
                    'row',
                    $stepInfo['anyUnanswered'] ? 'missing' : '',
                    $session->getStep() == $step ? 'current' : ''

                ));
                $sButtonSubmit=CHtml::htmlButton(gT('Go to this group'), ['type'=>'submit','value'=> $step, 'name'=>'move','class'=>'jshide']);
                echo CHtml::tag('li', array(
                    'data-id' => $group->primaryKey,
                    'title' => $group->description,
                    'class' => $classes,
                    ), $group->group_name .$sButtonSubmit);
            }
        }
        echo CHtml::closeTag('ol');
        echo CHtml::closeTag('div');
        echo CHtml::closeTag('div');

        App()->getClientScript()->registerScript('manageIndex',"manageIndex()\n",CClientScript::POS_END);
    }

    protected function createFullQuestionIndexByQuestion(SurveySession $session)
    {
        echo CHtml::openTag('div', array('id' => 'index'));
        echo CHtml::openTag('div', array('class' => 'container'));
        echo CHtml::tag('h2', array(), gT("Question index"));
        echo 'Question by question not yet supported, use incremental index.';
        echo CHtml::closeTag('div');
        echo CHtml::closeTag('div');

        App()->getClientScript()->registerScript('manageIndex',"manageIndex()\n",CClientScript::POS_END);
    }

    protected function createIncrementalQuestionIndex(SurveySession $session)
    {
        echo "\n\n<!-- PRESENT THE INDEX -->\n";

        echo '<div id="index"><div class="container"><h2>' . gT("Question index") . '</h2>';

        $lastGseq=-1;
        $gseq = -1;
        $grel = true;
        for($v = 0, $n = 0; $n != $session->getMaxStep(); ++$n)
        {
            $stepInfo = LimeExpressionManager::GetStepIndexInfo($n);

            if ($session->getFormat() == Survey::FORMAT_QUESTION)
            {
                if ($lastGseq != $stepInfo['gseq']) {
                    // show the group label
                    ++$gseq;
                    $g = $session->getGroups()[$gseq];
                    if ($g->isRelevant($session->response))
                    {
                        $gtitle = LimeExpressionManager::ProcessString($g['group_name']);
                        echo '<h3>' . flattenText($gtitle) . "</h3>";
                    }
                    $lastGseq = $stepInfo['gseq'];
                }
                if (!$grel || !$stepInfo['show'])
                {
                    continue;
                }
                $q = $session->getFieldArray()[$n];
            }
            else
            {
                ++$gseq;
                if (!$stepInfo['show'])
                {
                    continue;
                }
                $g = $session->getGroups()[$gseq];
            }

            if ($session->getFormat() == Survey::FORMAT_GROUP)
            {
                $indexlabel = LimeExpressionManager::ProcessString($g['group_name']);
                $sButtonText=gT('Go to this group');
            }
            else
            {
                $indexlabel = LimeExpressionManager::ProcessString($q[3]);
                $sButtonText=gT('Go to this question');
            }

            $sText = (($session->getFormat() == Survey::FORMAT_GROUP) ? flattenText($indexlabel) : flattenText($indexlabel));

            ++$v;

            $class = ($n == $session->getStep() - 1 ? 'current' : (!$stepInfo['anyUnanswered'] ? 'answer' : 'missing'));
            if ($v % 2)
                $class .= " odd";

            $s = $n + 1;
            echo "<div class=\"row $class\">";
            echo "<span class=\"hdr\">$v</span>";
            echo "<span title=\"$sText\">$sText</span>";
            echo CHtml::htmlButton($sButtonText,array('type'=>'submit','value'=>$s,'name'=>'move','class'=>'jshide'));
            echo "</div>";
        }

        if ($session->maxStep == $session->stepCount)
        {
            echo CHtml::htmlButton(gT('Submit'),array('type'=>'submit','value'=>'movesubmit','name'=>'move','class'=>'submit button'));
        }

        echo '</div></div>';
        App()->getClientScript()->registerScript('manageIndex',"manageIndex()\n",CClientScript::POS_END);

    }

    /**
     * Main function
     *
     * @param SurveySession $session
     * @param $move
     * @throws CException
     * @throws CHttpException
     * @throws Exception
     * @internal param mixed $surveyid
     * @internal param mixed $args
     */
    function run(SurveySession $session, $move)
    {
        /** @var Survey $survey */
        $survey = $session->survey;

        $this->setJavascriptVar($survey);

        $templatePath = $session->templateDir;

        $radix = getRadixPointData($survey->getLocalizedNumberFormat())['separator'];

        //        if (isset($param['newtest']) && $param['newtest'] == "Y")
        //            setcookie("limesurvey_timers", "0");   //@todo fix - sometimes results in headers already sent error
        $show_empty_group = false;


        //RUN THIS IF THIS IS THE FIRST TIME , OR THE FIRST PAGE ########################################
        if ($session->step == 0) {
            LimeExpressionManager::StartSurvey($session->surveyId, false);
            $moveResult = LimeExpressionManager::JumpTo(0, false, false, true);
        }


        if (isset($_POST['LEMpostKey']) && $_POST['LEMpostKey'] != $session->postKey) {
            throw new \Exception("CSRF Protection triggered.");
        }

        if (isset($move) && $move == "clearcancel") {
            $moveResult = LimeExpressionManager::JumpTo($session->step, false, true, false, true);
        }


        //Move current step ###########################################################################
        if (isset($move) && $move == 'moveprev' && ($session->survey->bool_allowprev || $session->survey->questionindex > 0)) {
            $moveResult = LimeExpressionManager::NavigateBackwards();
        }
        if (isset($move) && $move == "movenext") {
            $moveResult = LimeExpressionManager::NavigateForwards();
        }
        if (isset($move) && ($move == 'movesubmit')) {
            if ($session->format == Survey::FORMAT_ALL_IN_ONE) {
                $moveResult = LimeExpressionManager::NavigateForwards();
            } else {
                // may be submitting from the navigation bar, in which case need to process all intervening questions
                // in order to update equations and ensure there are no intervening relevant mandatory or relevant invalid questions
                $moveResult = LimeExpressionManager::JumpTo($session->getStepCount() + 1, false);
            }
        }
        if (isset($move) && $move == 'changelang') {
            // jump to current step using new language, processing POST values
            $moveResult = LimeExpressionManager::JumpTo($session->getStep(), false, true, true,
                true);  // do process the POST data
        }
        if (isset($move) && isNumericInt($move) && $session->survey->questionindex == Survey::INDEX_INCREMENTAL) {
            $move = (int)$move;
            if ($move > 0 && ($move <= $session->getStep() || $move <= $session->getMaxStep())) {
                $moveResult = LimeExpressionManager::JumpTo($move, false);
            }
        } elseif (isset($move) && isNumericInt($move) && $session->survey->questionindex == Survey::INDEX_FULL) {
            $moveResult = LimeExpressionManager::JumpTo($move, false, true, true);
            $session->setStep($moveResult['seq'] + 1);
        }
        if (!isset($moveResult) && !($session->format != Survey::FORMAT_ALL_IN_ONE && $session->getStep() == 0)) {
            // Just in case not set via any other means, but don't do this if it is the welcome page
            $moveResult = LimeExpressionManager::GetLastMoveResult(true);
            $LEMskipReprocessing = true;
        }

        if (isset($moveResult) && isset($moveResult['seq']))// Reload at first page (welcome after click previous fill an empty $moveResult array
        {
            // With complete index, we need to revalidate whole group bug #08806. It's actually the only mode where we JumpTo with force
            if ($moveResult['finished']) {
                if ($session->survey->questionindex == Survey::INDEX_FULL) {
                    //LimeExpressionManager::JumpTo(-1, false, false, true);
                    LimeExpressionManager::StartSurvey($session->surveyId);
                    $moveResult = LimeExpressionManager::JumpTo($session->getStepCount() + 1, false, false,
                        false);// no preview, no save data and NO force
                    if (!$moveResult['mandViolation'] && $moveResult['valid'] && empty($moveResult['invalidSQs'])) {
                        $moveResult['finished'] = true;
                    }
                }
                $move = 'movesubmit';
            } else {
                $session->setStep($moveResult['seq']);
            }
            if ($move == "movesubmit" && $moveResult['finished'] == false) {
                // then there are errors, so don't finalize the survey
                $move = "movenext"; // so will re-display the survey
                $invalidLastPage = true;
            }
        }

        //SEE IF THIS GROUP SHOULD DISPLAY
        $show_empty_group = ($session->getStep() == 0);

        $redata = compact(array_keys(get_defined_vars()));

        //SUBMIT ###############################################################################
        if ((isset($move) && $move == "movesubmit")) {
            resetTimers();


            //END PAGE - COMMIT CHANGES TO DATABASE
            if (!$survey->bool_active) //If survey is not active, don't really commit
            {
                if ($survey->bool_assessments) {
                    $assessments = doAssessment($survey);
                }
                sendCacheHeaders();
                doHeader();

                renderOldTemplate($templatePath . "startpage.pstpl", array(), $redata,
                    'SubmitStartpageI', null, true);

                //Check for assessments
                if ($survey->bool_assessments && $assessments) {
                    renderOldTemplate($templatePath . "assessment.pstpl", array(), $redata,
                        'SubmitAssessmentI', null, true);
                }

                // fetch all filenames from $_SESSIONS['files'] and delete them all
                // from the /upload/tmp/ directory
                /* echo "<pre>";print_r($_SESSION);echo "</pre>";
                for($i = 1; isset($_SESSION[$LEMsessid]['files'][$i]); $i++)
                {
                unlink('upload/tmp/'.$_SESSION[$LEMsessid]['files'][$i]['filename']);
                }
                */
                // can't kill session before end message, otherwise INSERTANS doesn't work.
                $completed = templatereplace($survey->getLocalizedEndText(), array(), $redata, 'SubmitEndtextI', null,
                    true);
                $completed .= "<br /><strong><font size='2' color='red'>" . gT("Did Not Save") . "</font></strong><br /><br />\n\n";
                $completed .= gT("Your survey responses have not been recorded. This survey is not yet active.") . "<br /><br />\n";
                if ($thissurvey['printanswers'] == 'Y') {
                    // 'Clear all' link is only relevant for survey with printanswers enabled
                    // in other cases the session is cleared at submit time
                    $completed .= "<a href='" . Yii::app()->getController()->createUrl("survey/index/sid/{$surveyid}/move/clearall") . "'>" . gT("Clear Responses") . "</a><br /><br />\n";
                }


            } else //THE FOLLOWING DEALS WITH SUBMITTING ANSWERS AND COMPLETING AN ACTIVE SURVEY
            {
                if ($session->survey->bool_usetokens && $session->survey->bool_usecookie) //don't use cookies if tokens are being used
                {
                    setcookie("LS_" . $surveyid . "_STATUS", "COMPLETE",
                        time() + 31536000); //Cookie will expire in 365 days
                }


                $content = '';
                $content .= templatereplace(file_get_contents($templatePath . "startpage.pstpl"), array(), $redata,
                    'SubmitStartpage', null, true);

                //Check for assessments
                if ($survey->bool_assessments) {
                    $assessments = doAssessment($survey->primaryKey);
                    if ($assessments) {
                        $content .= templatereplace(file_get_contents($templatePath . "assessment.pstpl"), array(),
                            $redata, 'SubmitAssessment', null, true);
                    }
                }

                //Update the token if needed and send a confirmation email
                set_error_handler(function ($errno, $errstr, $errfile, $errline) {
                    throw new \ErrorException($errstr, $errno, 1, $errfile, $errline);
                });
                if (isset($session->response->token)) {
                    submittokens();
                }

                //Send notifications

                sendSubmitNotifications($session->surveyId);


                $content = '';

                $content .= templatereplace(file_get_contents($templatePath . "startpage.pstpl"), [], $redata,
                    'SubmitStartpage', null, true);

                //echo $thissurvey['url'];
                //Check for assessments
                if ($session->survey->bool_assessments) {
                    $assessments = doAssessment($session->surveyId);
                    if ($assessments) {
                        $content .= templatereplace(file_get_contents($templatePath . "assessment.pstpl"), array(),
                            $redata, 'SubmitAssessment', null, true);
                    }
                }


                if (trim(str_replace(array('<p>', '</p>'), '', $survey->getLocalizedEndText())) == '') {
                    $completed = "<br /><span class='success'>" . gT("Thank you!") . "</span><br /><br />\n\n"
                        . gT("Your survey responses have been recorded.") . "<br /><br />\n";
                } else {
                    $completed = templatereplace($survey->getLocalizedEndText(), array(), $redata, 'SubmitAssessment',
                        null, true);
                }

                // Link to Print Answer Preview  **********
                if ($session->survey->bool_printanswers) {
                    $url = App()->createUrl("/printanswers/view", ['surveyid' => $surveyid]);
                    $completed .= "<br /><br />"
                        . "<a class='printlink' href='$url'  target='_blank'>"
                        . gT("Print your answers.")
                        . "</a><br />\n";

                    if ($session->survey->bool_publicstatistics) {
                        $completed .= '<br />' . gT("or");
                    }
                }

                // Link to Public statistics  **********
                if ($session->survey->bool_publicstatistics) {
                    $url = App()->createUrl("statistics_user/action",
                        ['surveyid' => $surveyid, 'language' => $session->language]);
                    $completed .= "<br /><br />"
                        . "<a class='publicstatisticslink' href='$url' target='_blank'>"
                        . gT("View the statistics for this survey.")
                        . "</a><br />\n";
                }
                //*****************************************

                $session->isFinished = true;
                sendCacheHeaders();
                if ($session->survey->bool_autoredirect && $thissurvey['surveyls_url']) {
                    //Automatically redirect the page to the "url" setting for the survey
                    header("Location: {$thissurvey['surveyls_url']}");
                }

                doHeader();
                echo $content;
            }
            $redata['completed'] = $completed;

            $event = new PluginEvent('afterSurveyComplete');
            $event->set('responseId', $session->getResponseId());
            $event->set('surveyId', $session->getSurveyId());
            App()->getPluginManager()->dispatchEvent($event);
            $blocks = array();

            foreach ($event->getAllContent() as $blockData) {
                /* @var $blockData PluginEventContent */
                $blocks[] = CHtml::tag('div',
                    array('id' => $blockData->getCssId(), 'class' => $blockData->getCssClass()),
                    $blockData->getContent());
            }

            $redata['completed'] = implode("\n", $blocks) . "\n" . $redata['completed'];

            renderOldTemplate($templatePath . "completed.pstpl", array('completed' => $completed),
                $redata, 'SubmitCompleted', null, true);
            echo "\n";
            renderOldTemplate($templatePath . "endpage.pstpl", array(), $redata, 'SubmitEndpage',
                null, true);
            doFooter();

            exit;
        }

        $redata = compact(array_keys(get_defined_vars()));


        //******************************************************************************************************
        //PRESENT SURVEY
        //******************************************************************************************************
        bP('Present Survey');
        App()->loadHelper('qanda');


        //Iterate through the questions about to be displayed:
        $inputNames = array();

        $popups = [];
        $validationResults = [];
        if ($session->format != Survey::FORMAT_ALL_IN_ONE && $session->survey->bool_showprogress) {
            $percentcomplete = makegraph($session->step, $session->stepCount);
        }


        //READ TEMPLATES, INSERT DATA AND PRESENT PAGE
        sendCacheHeaders();
        doHeader();
        $survey = $session->survey;
        renderOldTemplate($session->templateDir . "/startpage.pstpl", array(),
            compact(array_keys(get_defined_vars())));

        if (isset($backpopup)) {
            $popups = [$backpopup];// If user click reload: no need other popup
        }
        Yii::app()->clientScript->registerScript("showpopup",
            "showpopup=" . (int)SettingGlobal::get('showpopups', true) . ";", CClientScript::POS_HEAD);
        //if(count($aPopup))
        Yii::app()->clientScript->registerScript('startPopup', "startPopups=" . json_encode($popups) . ";",
            CClientScript::POS_HEAD);
        //ALTER PAGE CLASS TO PROVIDE WHOLE-PAGE ALTERNATION
        if ($session->format != Survey::FORMAT_ALL_IN_ONE
            && $session->step != $session->prevStep
            || $session->step % 2
        ) {
            echo "<script type=\"text/javascript\">$(\"body\").addClass(\"page-odd\");</script>\n";
        }

        $formParams = [
            'id' => 'limesurvey',
            'name' => 'limesurvey',
            'autocomplete' => 'off'
        ];

        /**
         * @Todo Check if any question on the current page is an upload question.
         */
//        if ($question->type == Question::TYPE_UPLOAD) {
            $formParams['enctype'] = 'multipart/form-data';
//        }

        echo CHtml::beginForm('', 'post', $formParams);
        echo "<!-- INPUT NAMES -->";
        echo CHtml::hiddenField('fieldnames', implode("|", $inputNames), ['id' => 'fieldnames']);




        // The default submit button
        echo CHtml::htmlButton("default", [
            'type' => 'submit',
            'id' => "defaultbtn",
            'value' => "default",
            'name' => 'move',
            'class' => "submit noview",
            'style' => 'display:none'
        ]);

        if ($session->format == Survey::FORMAT_ALL_IN_ONE)
        {
            if (!$survey->bool_showwelcome) {
                //Hide the welcome screen if explicitly set
            } else {
                renderOldTemplate($templatePath . "welcome.pstpl", array(), $redata) . "\n";
            }

            if ($survey->bool_anonymized) {
                renderOldTemplate($templatePath . "privacy.pstpl", array(), $redata) . "\n";
            }
        } else {
            // <-- START THE SURVEY -->
            renderOldTemplate($templatePath . "survey.pstpl", array(), $redata);
        }

        $showpopups= SettingGlobal::get('showpopups', false);
        //Display the "mandatory" message on page if necessary

        if (!$showpopups
            && $session->getViewCount() > 1
            && count(array_filter($validationResults, function(QuestionValidationResult $result) {
                // Count fields that do not pass mandatory criteria.
                return !$result->getPassedMandatory();
            })) > 0
        ) {
            echo "<p class='errormandatory'>" . gT("One or more mandatory questions have not been answered. You cannot proceed until these have been completed.") . "</p>";
        }

        //Display the "validation" message on page if necessary
        if (!$showpopups
            && $session->getViewCount() > 1
            && count(array_filter($validationResults, function(QuestionValidationResult $result) {
                // Count fields that do not pass validation but do pass mandatory validation.
                return $result->getPassedMandatory() && !$result->getSuccess();
            })) > 0
        ) {
            echo "<p class='errormandatory'>" . gT("One or more questions have not been answered in a valid manner. You cannot proceed until these answers are valid.") . "</p>";
        }

        //Display the "file validation" message on page if necessary
        if (!$showpopups && isset($filenotvalidated) && $filenotvalidated == true && $session->getViewCount() > 1)
        {
            echo "<p class='errormandatory'>" . gT("One or more uploaded files are not in proper format/size. You cannot proceed until these files are valid.") . "</p>";
        }

        LimeExpressionManager::registerScripts($session);

        if ($session->format == Survey::FORMAT_ALL_IN_ONE) {
            foreach ($session->groups as $group) {
                $this->renderGroup($session, $group);
            }
        } else {
            $this->renderGroup($session, $session->getCurrentGroup());
        }




        LimeExpressionManager::FinishProcessingPage();

        $navigator = surveymover(); //This gets globalised in the templatereplace function
        $redata = compact(array_keys(get_defined_vars()));

        echo "\n\n<!-- PRESENT THE NAVIGATOR -->\n";
        renderOldTemplate($templatePath . "navigator.pstpl", array(), $redata);
        echo "\n";

        if (!$session->survey->bool_active)
        {
            echo "<p style='text-align:center' class='error'>" . gT("This survey is currently not active. You will not be able to save your responses.") . "</p>\n";
        }


        if ($session->getFormat() != Survey::FORMAT_ALL_IN_ONE && $session->survey->questionindex == Survey::INDEX_INCREMENTAL)
        {
            $this->createIncrementalQuestionIndex($session);
        }
        elseif ($session->getFormat() != Survey::FORMAT_ALL_IN_ONE && $session->survey->questionindex == Survey::INDEX_FULL)
        {
            $this->createFullQuestionIndex($session);
        }

        $step = $session->getStep();
        echo TbHtml::hiddenField('thisstep', $step, ['id' => 'thisstep']);
        echo TbHtml::hiddenField('sid', $session->surveyId, ['id' => 'sid']);
        echo TbHtml::hiddenField('SSM', $session->getId());
        echo TbHtml::hiddenField('start_time', time(), ['id' => 'start_time']);
        echo TbHtml::hiddenField('LEMpostKey', $session->postKey, ['id' => 'LEMpostKey']);

        echo "</form>\n";

        renderOldTemplate($templatePath . "endpage.pstpl", array(), $redata);

        echo "\n";

        doFooter();
        eP('Present Survey');
    }
    /**
    * setJavascriptVar
    *
    * @return @void
    * @param integer $iSurveyId : the survey id for the script
    */
    public function setJavascriptVar(Survey $survey)
    {
        if(isset($survey->localizedNumberFormat))
        {
            $aLSJavascriptVar=array();
            $aLSJavascriptVar['bFixNumAuto']=(int)(bool)Yii::app()->getConfig('bFixNumAuto',1);
            $aLSJavascriptVar['bNumRealValue']=(int)(bool)Yii::app()->getConfig('bNumRealValue',0);
            $aRadix=getRadixPointData($survey->localizedNumberFormat);
            $aLSJavascriptVar['sLEMradix']=$aRadix['separator'];
            $sLSJavascriptVar="LSvar=".json_encode($aLSJavascriptVar) . ';';
            App()->clientScript->registerScript('sLSJavascriptVar',$sLSJavascriptVar,CClientScript::POS_HEAD);
        }
        // Maybe remove one from index and allow empty $surveyid here.
    }

    /**
    * Construction of replacement array, actually doing it with redata
    * 
    * @param $aQuestionQanda : array from qanda helper
    * @return aray of replacement for question.psptl
    **/
    public function getQuestionReplacement(array $details, RenderedQuestion $renderedQuestion, Question $question, Response $response)
    {
        bP();

        $session = App()->surveySessionManager->current;
        if (!isset($session)) {
            return [];
        }
        $survey = $session->survey;

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
            "QUESTION_MAN_CLASS"=>"",
            "QUESTION_INPUT_ERROR_CLASS"=>"",
            "ANSWER"=>"",
            "QUESTION_HELP"=>"", // Core help
            "QUESTION_VALID_MESSAGE"=>"",
            "QUESTION_FILE_VALID_MESSAGE"=>"",
            "QUESTION_MAN_MESSAGE"=>"",
            "QUESTION_MANDATORY"=>"",
        );
        if(empty($details))
        {
            return $aReplacement;
        }
        // Core value : not replaced
        $aReplacement['QID'] = $question->primaryKey;
        $aReplacement['GID'] = $question->gid;
        $aReplacement['SGQ']= $question->sgqa;

        $aReplacement['AID']=isset($details['aid']) ? $details['aid'] : "" ;


        $iNumber = $details['number'];

        switch (SettingGlobal::get('showqnumcode', 'choose'))
        {
            case 'both':
                $aReplacement['QUESTION_CODE'] = $question->title;
                $aReplacement['QUESTION_NUMBER']=$iNumber;
                break;
            case 'number':
                $aReplacement['QUESTION_NUMBER']=$iNumber;
                $aReplacement['QUESTION_CODE'] = $question->title;
                break;
            case 'choose':
                switch($survey->showqnumcode) {
                    case 'B': // Both
                        $aReplacement['QUESTION_CODE'] = $question->title;
                        $aReplacement['QUESTION_NUMBER']=$iNumber;
                        break;
                    case 'N':
                        $aReplacement['QUESTION_NUMBER']=$iNumber;
                        break;
                    case 'C':
                        $aReplacement['QUESTION_CODE'] = $question->title;
                        break;
                    case 'X':
                    default:
                        break;
                }
                break;
        }
        // Core value : user text
        $aReplacement['QUESTION_TEXT'] = $renderedQuestion['text'];
        $aReplacement['QUESTIONHELP']= $question->help;// User help
        // To be moved in a extra plugin : QUESTIONHELP img adding
        $sTemplateDir = $session->templateDir;
        $sTemplateUrl = $session->templateUrl;
        if(flattenText($aReplacement['QUESTIONHELP'], true,true) != '')
        {
            if (file_exists($sTemplateDir . '/help.gif')) {
                $helpicon = $sTemplateUrl . '/help.gif';
            }
            elseif (file_exists($sTemplateDir . '/help.png')) {
                $helpicon = $sTemplateUrl . '/help.png';
            } else {
                $helpicon = Yii::app()->getConfig('imageurl')."/help.gif";
            }
            $aReplacement['QUESTIONHELP']="<img src='{$helpicon}' alt='Help' align='left' />".$aReplacement['QUESTIONHELP'];
        }
        // Core value :the classes
        $classes = $question->classes;
        if (!$question->isRelevant($response)) {
            $classes[] = 'irrelevant';
        }
        $aReplacement['QUESTION_CLASS'] = implode(' ', $classes);
        $aMandatoryClass = [];
        if ($question->bool_mandatory) {
            $aMandatoryClass[]= 'mandatory';
        }
        $session = App()->surveySessionManager->current;
        if ($session->maxStep != $session->step) {
            $aMandatoryClass[]= 'missing';
        }
        $aReplacement['QUESTION_MAN_CLASS'] = " ".implode(" ",$aMandatoryClass);
        $aReplacement['QUESTION_INPUT_ERROR_CLASS']=$details['input_error_class'];
        // Core value : LS text : EM and not
        $aReplacement['ANSWER'] = $renderedQuestion['html'];
        $aReplacement['QUESTION_HELP'] = $details['help'];// Core help only, not EM
        $aReplacement['QUESTION_VALID_MESSAGE'] = $renderedQuestion->getMessages();
        $aReplacement['QUESTION_MANDATORY'] = $details['mandatory'];
        // For QUESTION_ESSENTIALS
        $aHtmlOptions = [];
        if (true !== $relevance = $question->getRelevanceScript()) {
            $aHtmlOptions['data-relevance-expression'] = $relevance;
        }

        // Launch the event
        $event = new PluginEvent('beforeQuestionRender');
        // Some helper
        $event->set('question', $question);
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
        $aReplacement['QUESTION_VALID_MESSAGE'] = $event->get('valid_message');
        $aReplacement['QUESTION_MANDATORY'] = $event->get('mandatory',$aReplacement['QUESTION_MANDATORY']);
        // Always add id for QUESTION_ESSENTIALS
        $aHtmlOptions['id'] = "question{$question->primaryKey}";
        $aReplacement['QUESTION_ESSENTIALS']= CHtml::renderAttributes($aHtmlOptions);
        eP();
        return $aReplacement;
    }

    protected function renderGroup(SurveySession $session, QuestionGroup $group) {
        echo "\n\n<!-- START THE GROUP -->\n";
        echo "\n\n<div id='group-{$session->getGroupIndex($group->primaryKey)}'";
        if  (!$group->isRelevant($session->response)) {
            echo " style='display: none;'";
        }
        echo ">\n";
        renderOldTemplate($session->templateDir . "startgroup.pstpl");
        echo "\n";

        renderOldTemplate($session->templateDir . "groupdescription.pstpl");
        echo "\n";

        echo "\n\n<!-- PRESENT THE QUESTIONS -->\n";
        if ($session->format != Survey::FORMAT_QUESTION) {
            foreach ($group->questions as $question) {
                $this->renderQuestion($session, $question);
            }
        } else {
            $this->renderQuestion($session, $session->getQuestionByIndex($session->step));
        }

        echo "\n\n<!-- END THE GROUP -->\n";
        renderOldTemplate($session->templateDir . "endgroup.pstpl");
        echo "\n\n</div>\n";
    }

    protected function renderQuestion(SurveySession $session, Question $question) {
        bP();
        if ($question->bool_hidden || $question->type == Question::TYPE_EQUATION) {
            return;
        }

        $n_q_display = '';

        $aReplacement = [];
        $question_template = file_get_contents($session->templateDir .'question.pstpl');

        list($details, $html) = retrieveAnswers($question);
//        vdd($details);
        $aQuestionReplacement = $this->getQuestionReplacement($details, $html, $question, $session->response);
        echo templatereplace($question_template, $aQuestionReplacement, compact(array_keys(get_defined_vars())),
            false, $question->primaryKey);
        eP();
    }
}
