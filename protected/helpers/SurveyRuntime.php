<?php

namespace ls\helpers;
use ls\components\SurveySession;
use ls\models\Survey;
use \Yii;
use \CClientScript;
use \TbHtml;
use LimeExpressionManager;
use ls\models\QuestionGroup;
use ls\models\Question;
use PluginEvent;

class SurveyRuntime {


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
        echo TbHtml::openTag('div', array('id' => 'index'));
        echo TbHtml::openTag('div', array('class' => 'container'));
        echo TbHtml::tag('h2', array(), gT("Question index"));
        echo TbHtml::openTag('ol');
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
                $valid = LimeExpressionManager::singleton()->validateGroup($key);
                $classes = implode(' ', array(
                    'row',
                    $stepInfo['anyUnanswered'] ? 'missing' : '',
                    $session->getStep() == $step ? 'current' : ''

                ));
                $sButtonSubmit=TbHtml::htmlButton(gT('Go to this group'), ['type'=>'submit','value'=> $step, 'name'=>'move','class'=>'jshide']);
                echo TbHtml::tag('li', array(
                    'data-id' => $group->primaryKey,
                    'title' => $group->description,
                    'class' => $classes,
                    ), $group->group_name .$sButtonSubmit);
            }
        }
        echo TbHtml::closeTag('ol');
        echo TbHtml::closeTag('div');
        echo TbHtml::closeTag('div');

        App()->getClientScript()->registerScript('manageIndex',"manageIndex()\n",CClientScript::POS_END);
    }

    protected function createFullQuestionIndexByQuestion(SurveySession $session)
    {
        echo TbHtml::openTag('div', array('id' => 'index'));
        echo TbHtml::openTag('div', array('class' => 'container'));
        echo TbHtml::tag('h2', array(), gT("Question index"));
        echo 'ls\models\Question by question not yet supported, use incremental index.';
        echo TbHtml::closeTag('div');
        echo TbHtml::closeTag('div');

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
                        $gtitle = LimeExpressionManager::ProcessString($g['group_name'], $session);
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
                $indexlabel = LimeExpressionManager::ProcessString($g['group_name'], $session);
                $sButtonText=gT('Go to this group');
            }
            else
            {
                $indexlabel = LimeExpressionManager::ProcessString($q[3], $session);
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
            echo TbHtml::htmlButton($sButtonText,array('type'=>'submit','value'=>$s,'name'=>'move','class'=>'jshide'));
            echo "</div>";
        }

        if ($session->maxStep == $session->stepCount)
        {
            echo TbHtml::htmlButton(gT('Submit'),array('type'=>'submit','value'=>'submit','name'=>'move','class'=>'submit button'));
        }

        echo '</div></div>';
        App()->getClientScript()->registerScript('manageIndex',"manageIndex()\n",CClientScript::POS_END);

    }

    /**
     * Main function
     *
     * @param \ls\components\SurveySession $session
     * @param $move
     * @throws CException
     * @throws CHttpException
     * @throws Exception
     */
    function run(SurveySession $session, $move)
    {
        /** @var \ls\models\Survey $survey */
        $survey = $session->survey;

        $this->setJavascriptVar($survey);

        $radix = \ls\helpers\SurveyTranslator::getRadixPointData($survey->getLocalizedNumberFormat())['separator'];

        $show_empty_group = false;


         if ( $move == "clearcancel") {
            $moveResult = LimeExpressionManager::JumpTo($session->step, true, false, true);
        }


        //Move current step ###########################################################################
        switch ($move) {
            case 'prev':
                $moveResult = LimeExpressionManager::NavigateBackwards();
                break;
            case 'next':
                $moveResult = LimeExpressionManager::NavigateForwards();
                break;
            case 'submit':
                if ($session->format == Survey::FORMAT_ALL_IN_ONE) {
                    $moveResult = LimeExpressionManager::NavigateForwards();
                } else {
                    // may be submitting from the navigation bar, in which case need to process all intervening questions
                    // in order to update equations and ensure there are no intervening relevant mandatory or relevant invalid questions
                    $moveResult = LimeExpressionManager::JumpTo($session->getStepCount() + 1);
                }
                break;
            case 'changelang':
                // jump to current step using new language, processing POST values
                $moveResult = LimeExpressionManager::JumpTo($session->getStep(), true, true, true);  // do process the POST data
                break;
            default:
                if (is_numeric($move) && $session->survey->questionindex == Survey::INDEX_INCREMENTAL) {
                    $move = (int)$move;
                    if ($move > 0 && ($move <= $session->getStep() || $move <= $session->getMaxStep())) {
                        $moveResult = LimeExpressionManager::JumpTo($move);
                    }
                } elseif (is_numeric($move) && $session->survey->questionindex == Survey::INDEX_FULL) {
                    $moveResult = LimeExpressionManager::JumpTo($move, true, true);
                    $session->setStep($moveResult['seq'] + 1);
                }

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
                    LimeExpressionManager::StartSurvey($session->surveyId);
                    $moveResult = LimeExpressionManager::JumpTo($session->getStepCount() + 1, false, false);// no preview, no save data and NO force
                    if (!$moveResult['mandViolation'] && $moveResult['valid'] && empty($moveResult['invalidSQs'])) {
                        $moveResult['finished'] = true;
                    }
                }
                $move = 'submit';
            } else {
                $session->setStep($moveResult['seq']);
            }
            if ($move == "submit" && $moveResult['finished'] == false) {
                // then there are errors, so don't finalize the survey
                $move = "next"; // so will re-display the survey
                $invalidLastPage = true;
            }
        }

        //SEE IF THIS GROUP SHOULD DISPLAY
        $show_empty_group = ($session->getStep() == 0);

        //SUBMIT ###############################################################################

        if (($move == "submit")) {
            $this->handleSubmit($session);

        }

        $redata = compact(array_keys(get_defined_vars()));


        //******************************************************************************************************
        //PRESENT SURVEY
        //******************************************************************************************************
        bP('Present Survey');

        //Iterate through the questions about to be displayed:

        //READ TEMPLATES, INSERT DATA AND PRESENT PAGE
        sendCacheHeaders();
        doHeader();
        $survey = $session->survey;
        renderOldTemplate($session->templateDir . "/startpage.pstpl", [], [], $session);

        $formParams = [
            'id' => 'limesurvey',
            'name' => 'limesurvey',
            'autocomplete' => 'off'
        ];

        /**
         * @Todo Check if any question on the current page is an upload question.
         */
        if (Question::model()->findByAttributes([
            'type' => Question::TYPE_UPLOAD,
            'sid' => $survey->primaryKey
        ]) != null) {
            $formParams['enctype'] = 'multipart/form-data';
        }
        echo \TbHtml::well("View count for this page: {$session->getViewCount()}");
        echo \TbHtml::well("Current step: {$session->getStep()}");
        if ($session->getViewCount() > 1) {
            $formParams['class'] = 'touched';
        }
        echo TbHtml::beginForm('', 'post', $formParams);

        // The default submit button
        echo TbHtml::htmlButton("default", [
            'type' => 'submit',
            'id' => "defaultbtn",
            'value' => "default",
            'name' => 'move',
            'class' => "submit noview",
            'style' => 'display:none'
        ]);

        if ($session->format == Survey::FORMAT_ALL_IN_ONE)
        {
            if ($survey->bool_showwelcome) {
                renderOldTemplate($session->templateDir . "welcome.pstpl", [], [], $session);
            }

            if ($survey->bool_anonymized) {
                renderOldTemplate($session->templateDir . "privacy.pstpl", [], [], $session);
            }
        } else {
            renderOldTemplate($session->templateDir . "survey.pstpl", [], [], $session);
        }

        LimeExpressionManager::registerScripts($session);
        if ($session->format == Survey::FORMAT_ALL_IN_ONE) {
            foreach ($session->groups as $group) {
                $this->renderGroup($session, $group);
            }
        } else {
            $this->renderGroup($session, $session->getCurrentGroup());
        }





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
        echo TbHtml::hiddenField('sid', $session->surveyId, ['id' => 'sid']);
        echo TbHtml::hiddenField('SSM', $session->getId());
        echo TbHtml::hiddenField('start_time', time(), ['id' => 'start_time']);
        echo TbHtml::hiddenField('csrfToken', $session->postKey);

        $this->renderNavigator($session);
        echo "</form>\n";

        renderOldTemplate($session->templateDir . "endpage.pstpl", array(), $redata);

        echo "\n";

        doFooter();
        eP('Present Survey');
    }

    protected function renderNavigator(SurveySession $session) {
        echo "\n\n<!-- PRESENT THE NAVIGATOR -->\n";
        renderOldTemplate($session->templateDir . "navigator.pstpl", [], [], $session);
        echo "\n";

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
            $aRadix=\ls\helpers\SurveyTranslator::getRadixPointData($survey->localizedNumberFormat);
            $aLSJavascriptVar['sLEMradix']=$aRadix['separator'];
            $sLSJavascriptVar="LSvar=".json_encode($aLSJavascriptVar) . ';';
            App()->clientScript->registerScript('sLSJavascriptVar',$sLSJavascriptVar,CClientScript::POS_HEAD);
        }
        // Maybe remove one from index and allow empty $surveyid here.
    }



    protected function renderGroup(SurveySession $session, QuestionGroup $group) {
        bP();
        echo "\n\n<!-- START THE GROUP -->\n";
        $replacements = $group->getReplacements();
        echo "\n\n<div id='group-{$session->getGroupIndex($group->primaryKey)}'";
        if  (!$group->isRelevant($session->response)) {
            echo " style='display: none;'";
        }
        echo ">\n";

        renderOldTemplate($session->templateDir . "startgroup.pstpl", [], $replacements);
        echo "\n";

        renderOldTemplate($session->templateDir . "groupdescription.pstpl", [], $replacements);
        echo "\n";

        echo "\n\n<!-- PRESENT THE QUESTIONS -->\n";
        if ($session->format != Survey::FORMAT_QUESTION) {
            foreach ($group->questions as $question) {
                if (!$question->bool_hidden) {
                    echo $this->renderQuestion($session, $question);
                }
            }
        } else {
            echo $this->renderQuestion($session, $session->getQuestionByIndex($session->step));
        }

        echo "\n\n<!-- END THE GROUP -->\n";
        renderOldTemplate($session->templateDir . "endgroup.pstpl", [], $replacements);
        echo "\n\n</div>\n";
        eP();
    }

    /**
     * Render a question.
     * @param SurveySession $session
     * @param Question $question
     * @return RenderedQuestion
     * @throws Exception
     */

    protected function renderQuestion(SurveySession $session, Question $question) {
        static $template;
        bP();
        if ($question->getRelevanceScript() !== false) {
            if (!isset($template)) {
                $template = file_get_contents($session->templateDir . 'question.pstpl');
            }
            /** @var RenderedQuestion $renderedQuestion */
            bP(get_class($question));
            $renderedQuestion = $question->render($session->response, $session);
            eP(get_class($question));
            $renderedQuestion->setTemplate($template);

            $result = $renderedQuestion->render($session);
        } else {
            // If the relevance script === false then this question is never relevant.
            $result = '';
        }
        eP();
        return $result;
    }

    protected function handleSubmit(SurveySession $session) {
        $survey = $session->survey;
        FrontEnd::resetTimers();
        //If survey is not active, don't really commit
        if (!$survey->bool_active) {
            if ($survey->bool_assessments) {
                $assessments = \ls\helpers\FrontEnd::doAssessment($survey);
            }
            sendCacheHeaders();
            doHeader();

            renderOldTemplate($session->templateDir . "startpage.pstpl", [], [], $session);

            //Check for assessments
            if ($survey->bool_assessments && $assessments) {
                renderOldTemplate($session->templateDir . "assessment.pstpl", [], [], $session);
            }

            $completed = Replacements::templatereplace($survey->getLocalizedEndText(), [], [], null, $session);
            $completed .= "<br /><strong><font size='2' color='red'>" . gT("Did Not Save") . "</font></strong><br /><br />\n\n";
            $completed .= gT("Your survey responses have not been recorded. This survey is not yet active.") . "<br /><br />\n";
        } else {
            //THE FOLLOWING DEALS WITH SUBMITTING ANSWERS AND COMPLETING AN ACTIVE SURVEY
            if (!$session->survey->bool_usetokens && $session->survey->bool_usecookie) {
                $cookieName = "LS_" . $survey->primaryKey . "_STATUS";
                App()->request->getCookies()->add($cookieName, new \CHttpCookie($cookieName, "COMPLETED", [
                    //Cookie will expire in 365 days
                    'expire' => time() + 365 * 24 * 3600
                ]));

            }


            $content = Replacements::templatereplace(file_get_contents($session->templateDir . "startpage.pstpl"), [], [], null, $session);

            //Check for assessments
            if ($survey->bool_assessments) {
                $assessments = \ls\helpers\FrontEnd::doAssessment($survey->primaryKey);
                $content .= Replacements::templatereplace(file_get_contents($session->templateDir . "assessment.pstpl"), [
                    'ASSESSMENTS' => $assessments
                ], [], null, $session);
            }

            //Update the token if needed and send a confirmation email
            if (isset($session->response->token)) {
                FrontEnd::submitToken($session);
            }

            //Send notifications

            sendSubmitNotifications($session->surveyId);


            $content = '';

            $content .= Replacements::templatereplace(file_get_contents($session->templateDir . "startpage.pstpl"), [], $redata, null, $session);

            //Check for assessments
            if ($session->survey->bool_assessments) {
                $assessments = \ls\helpers\FrontEnd::doAssessment($session->surveyId);
                if ($assessments) {
                    $content .= Replacements::templatereplace(file_get_contents($session->templateDir . "assessment.pstpl"), array(),
                        $redata, null);
                }
            }


            if (trim(str_replace(array('<p>', '</p>'), '', $survey->getLocalizedEndText())) == '') {
                $completed = "<br /><span class='success'>" . gT("Thank you!") . "</span><br /><br />\n\n"
                    . gT("Your survey responses have been recorded.") . "<br /><br />\n";
            } else {
                $completed = Replacements::templatereplace($survey->getLocalizedEndText(), array(), $redata, null);
            }

            // Link to Print ls\models\Answer Preview  **********
            if ($session->survey->bool_printanswers) {
                $url = App()->createUrl("/printanswers/view", ['surveyid' => $survey->primaryKey]);
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
                    ['surveyid' => $session->surveyId, 'language' => $session->language]);
                $completed .= "<br /><br />"
                    . "<a class='publicstatisticslink' href='$url' target='_blank'>"
                    . gT("View the statistics for this survey.")
                    . "</a><br />\n";
            }
            //*****************************************

            $session->isFinished = true;
            sendCacheHeaders();
            if ($session->survey->bool_autoredirect && isset($survey->localizedEndUrl)) {
                //Automatically redirect the page to the "url" setting for the survey
                header("Location: {$survey->localizedEndUrl}");
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
            $blocks[] = TbHtml::tag('div',
                array('id' => $blockData->getCssId(), 'class' => $blockData->getCssClass()),
                $blockData->getContent());
        }

        $redata['completed'] = implode("\n", $blocks) . "\n" . $redata['completed'];

        renderOldTemplate($session->templateDir . "completed.pstpl", [], ['completed' => $completed], $session);
        renderOldTemplate($session->templateDir . "endpage.pstpl", [], [], $session);
        doFooter();

        exit;
    }
}
