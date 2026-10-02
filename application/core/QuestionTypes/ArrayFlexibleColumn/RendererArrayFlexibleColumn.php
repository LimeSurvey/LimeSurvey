<?php

/**
 * RenderClass for Array by column Question
 *  * The ia Array contains the following
 *  0 => string qid
 *  1 => string sgqa
 *  2 => string questioncode
 *  3 => string question
 *  4 => string type
 *  5 => string gid
 *  6 => string mandatory,
 *  7 => string conditionsexist,
 *  8 => string usedinconditions
 *  0 => string used in group.php for question count
 * 10 => string new group id for question in randomization group (GroupbyGroup Mode)
 *
 */
class RendererArrayFlexibleColumn extends QuestionBaseRenderer
{
    /**
     * Returns the twig view used to render the answer part of the question.
     *
     * @return string
     */
    public function getMainView()
    {
        return '/survey/questions/answer/arrays/column/answer';
    }

    /**
     * Rows are rendered by the main view, nothing to return here.
     *
     * @return void
     */
    public function getRows()
    {
        return;
    }

    /**
     * Renders the array by column question.
     *
     * Answer options become the table rows and subquestions become the table columns.
     *
     * @param string $sCoreClasses Unused, kept for signature compatibility
     * @return array{0: string, 1: string[]|string} Rendered answer HTML and the list of input names
     *                                             (an empty string if there are no answer options or subquestions)
     * @throws CException
     */
    public function render($sCoreClasses = '')
    {
        $this->registerAssets();

        $aLastMoveResult = LimeExpressionManager::GetLastMoveResult();
        $YorNorSvalue = $this->aFieldArray[6];
        $isYes = ($YorNorSvalue == 'Y');
        $isS   = ($YorNorSvalue == 'S');

        if ($aLastMoveResult['mandViolation'] && ($isYes || $isS)) {
            $aMandatoryViolationSubQ = explode('|', (string) $aLastMoveResult['unansweredSQs']);
        } else {
            $aMandatoryViolationSubQ = [];
        }

        $coreClass = "ls-answers subquestion-list questions-list array-radio";
        $checkconditionFunction = "checkconditions";

        $sSessionKey = 'responses_' . Yii::app()->getConfig('surveyID');
        $sSurveyLanguage = $_SESSION[$sSessionKey]['s_lang'];
        // Answer options are always ordered by sortorder and code, the ordering service is not used here
        $aAnswers = Answer::model()->findAll(array('order' => 'sortorder, code', 'condition' => 'qid=:qid AND scale_id=0', 'params' => array(':qid' => $this->oQuestion->qid)));

        $labelans = [];
        $labelcode = [];
        $labels = [];

        foreach ($aAnswers as $lrow) {
            $labelans[] = $lrow->answerl10ns[$sSurveyLanguage]->answer;
            $labelcode[] = $lrow['code'];
            $labels[] = array("answer" => $lrow->answerl10ns[$sSurveyLanguage]->answer, "code" => $lrow['code'], "aid" => $lrow->aid);
        }

        if (count($labelans) == 0) {
            $answer = "<p class='error'>" . gT("Error: There are no answer options for this question and/or they don't exist in this language.") . "</p>\n";
            return array($answer, '');
        }

        if (($this->aFieldArray[6] != 'Y' && $this->aFieldArray[6] != 'S') && SHOW_NO_ANSWER == 1) {
            $labelcode[] = '';
            $labelans[] = gT('No answer');
            $labels[] = array('answer' => gT('No answer'), 'code' => '');
        }

        $aQuestions = $this->questionOrderingService->getOrderedSubQuestions($this->oQuestion, 0, $sSurveyLanguage);
        $anscount = count($aQuestions);

        $aData = [];
        $aData['labelans']  = $labelans;
        $aData['labelcode'] = $labelcode;

        if ($anscount == 0) {
            $answer = '<p class="error">' . gT('Error: There are no answers defined for this question.') . "</p>";
            return array($answer, '');
        }

        if (ctype_digit(trim((string) $this->getQuestionAttribute('answer_width_bycolumn')))) {
            $answerwidth = trim((string) $this->getQuestionAttribute('answer_width_bycolumn'));
        } else {
            $answerwidth = 33;
        }
        $cellwidth = (100 - $answerwidth) / $anscount;

        $aData['anscount']    = $anscount;
        $aData['cellwidth']   = $cellwidth;
        $aData['answerwidth'] = $answerwidth;
        $aData['aQuestions']  = [];

        foreach ($aQuestions as $aQuestion) {
            $aData['aQuestions'][] = array_merge($aQuestion->attributes, $aQuestion->questionl10ns[$sSurveyLanguage]->attributes);
        }

        $anscode = [];
        $answers = [];

        foreach ($aQuestions as $ansrow) {
            $anscode[] = $ansrow['qid'];
            $answers[] = $ansrow->questionl10ns[$sSurveyLanguage]->question;
        }

        $aData['anscode'] = $anscode;
        $aData['answers'] = $answers;

        $iAnswerCount = count($answers);
        for ($_i = 0; $_i < $iAnswerCount; ++$_i) {
            $myfname = $this->sSGQA . "_S" . $aQuestions[$_i]->qid;
            /* Check the Sub Q mandatory violation */
            if (($this->aFieldArray[6] == 'Y' || $this->aFieldArray[6] == 'S') && in_array($myfname, $aMandatoryViolationSubQ)) {
                $aData['aQuestions'][$_i]['errormandatory'] = true;
            } else {
                $aData['aQuestions'][$_i]['errormandatory'] = false;
            }
        }

        $aData['labels'] = $labels;
        $aData['checkconditionFunction'] = $checkconditionFunction;

        foreach ($labels as $labelIdx => $ansrow) {
            // create the html ids for the table rows, which are
            // the answer options for this question type
            $aData['labels'][$labelIdx]['myfname'] = $this->sSGQA;
            if (isset($ansrow['aid'])) {
                $aData['labels'][$labelIdx]['myfname'] .= "_S" . $ansrow['aid'];
            }

            // Checked state of every subquestion (column) for this answer option (row)
            foreach ($anscode as $j => $ld) {
                $myfname = $this->sSGQA . "_S" . $ld;
                $aData['aQuestions'][$j]['myfname'] = $myfname;
                if (
                    isset($_SESSION[$sSessionKey][$myfname]) &&
                    $_SESSION[$sSessionKey][$myfname] === $ansrow['code'] &&
                    ($ansrow['code'] !== '' || PRESELECT_NO_ANSWER)
                ) {
                    $aData['checked'][$ansrow['code']][$ld] = CHECKED;
                } elseif (
                    !isset($_SESSION[$sSessionKey][$myfname]) &&
                    $ansrow['code'] == '' &&
                    PRESELECT_NO_ANSWER
                ) {
                    $aData['checked'][$ansrow['code']][$ld] = CHECKED;
                    // Humm.. (by lemeur), not sure this section can be reached
                    // because I think $_SESSION['responses_'.Yii::app()->getConfig('surveyID')][$myfname] is always set (by save.php ??) !
                    // should remove the !isset part I think !!
                } else {
                    $aData['checked'][$ansrow['code']][$ld] = "";
                }
            }
        }

        // Current value of every subquestion and the input names
        $inputnames = [];
        foreach ($anscode as $j => $ld) {
            $myfname = $this->sSGQA . "_S" . $ld;

            if (isset($_SESSION[$sSessionKey][$myfname])) {
                $aData['aQuestions'][$j]['myfname_value'] = $_SESSION[$sSessionKey][$myfname];
            } else {
                $aData['aQuestions'][$j]['myfname_value'] = '';
            }

            $inputnames[] = $myfname;
        }

        $aData['coreClass'] = $coreClass;
        $aData['basename'] = $this->sSGQA;

        // Render question
        $answer = Yii::app()->twigRenderer->renderQuestion(
            $this->getMainView(),
            $aData
        );
        return array($answer, $inputnames);
    }
}
