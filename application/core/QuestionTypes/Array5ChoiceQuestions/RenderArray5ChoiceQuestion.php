<?php

/**
 * RenderClass for Array (5 point choice) Question
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
class RenderArray5ChoiceQuestion extends QuestionBaseRenderer
{
    /** @var string Name of the JavaScript function called on change */
    private $sCheckconditionFunction = "checkconditions";

    /** @var bool Whether the "No answer" column is shown */
    private $bShowNoAnswer = false;

    /**
     * Returns the base path of the twig views used to render this question type.
     *
     * @return string
     */
    public function getMainView()
    {
        return '/survey/questions/answer/arrays/5point';
    }

    /**
     * Rows are rendered inside render(), nothing to return here.
     *
     * @return void
     */
    public function getRows()
    {
        return;
    }

    /**
     * Renders the array (5 point choice) question.
     *
     * @param string $sCoreClasses Unused, kept for signature compatibility
     * @return array{0: string, 1: string[]} Rendered answer HTML and the list of input names
     */
    public function render($sCoreClasses = '')
    {
        $this->registerAssets();

        $aLastMoveResult         = LimeExpressionManager::GetLastMoveResult();
        $sMandatory              = $this->aFieldArray[6];
        $aMandatoryViolationSubQ = ($aLastMoveResult['mandViolation'] && ($sMandatory == 'Y' || $sMandatory == 'S')) ? explode("|", (string) $aLastMoveResult['unansweredSQs']) : [];
        $coreClass               = "ls-answers subquestion-list questions-list radio-array";
        $inputnames              = [];

        if (trim((string) $this->getQuestionAttribute('answer_width')) != '') {
            $answerwidth = $this->getQuestionAttribute('answer_width');
            $defaultWidth = false;
        } else {
            $answerwidth = 33;
            $defaultWidth = true;
        }
        $columnswidth = 100 - $answerwidth;
        $colCount = 5; // number of columns

        $this->bShowNoAnswer = ($sMandatory !== 'Y' && $sMandatory !== 'S') && SHOW_NO_ANSWER;
        if ($this->bShowNoAnswer) {
            //Question is not mandatory
            ++$colCount; // add another column
        }
        $sSurveyLanguage = $_SESSION['responses_' . Yii::app()->getConfig('surveyID')]['s_lang'];

        // Get questions and answers by defined order
        $aSubquestions = $this->questionOrderingService->getOrderedSubQuestions($this->oQuestion, 0, $sSurveyLanguage);

        // Check if any subquestion use suffix/prefix
        $right_exists = false;
        foreach ($aSubquestions as $ansrow) {
            $answertext2 = $ansrow->questionl10ns[$sSurveyLanguage]->question;
            if (strpos((string) $answertext2, '|')) {
                $right_exists = true;
            }
        }
        if ($right_exists) {
            /* put the right answer to same width : take place in answer width only if it's not default */
            if ($defaultWidth) {
                $columnswidth -= $answerwidth;
            } else {
                $answerwidth = $answerwidth / 2;
            }
            // Add a class so we can style the left side text differently when there is a right side text
            $coreClass .= " semantic-differential-list";
        }
        $cellwidth = $columnswidth / $colCount;

        $sColumns = $this->renderColumns($cellwidth, $answerwidth, $right_exists);
        $sHeaders = $this->renderHeaders($right_exists);

        $sRows = '';
        foreach ($aSubquestions as $j => $ansrow) {
            $myfname = $this->sSGQA . "_S" . $ansrow['qid'];
            $sRows .= $this->renderRow(
                $ansrow,
                $j,
                $myfname,
                $sSurveyLanguage,
                $answerwidth,
                $right_exists,
                ($sMandatory == 'Y' || $sMandatory == 'S') && in_array($myfname, $aMandatoryViolationSubQ)
            );
            $inputnames[] = $myfname;
        }

        $answer = Yii::app()->twigRenderer->renderQuestion($this->getMainView() . '/answer', array(
            'coreClass'   => $coreClass,
            'sColumns'    => $sColumns,
            'answerwidth' => $answerwidth,
            'sHeaders'    => $sHeaders,
            'sRows'       => $sRows,
            'basename'    => $this->sSGQA,
        ));

        return array($answer, $inputnames);
    }

    /**
     * Renders the column definitions (5 points, optional "No answer" and optional suffix column).
     *
     * @param float|int $cellwidth Width of each answer column in percent
     * @param float|int|string $answerwidth Width of the answer text column in percent
     * @param bool $right_exists Whether any subquestion has a right side (suffix) text
     * @return string
     */
    private function renderColumns($cellwidth, $answerwidth, bool $right_exists): string
    {
        $sColumns = '';
        for ($xc = 1; $xc <= 5; $xc++) {
            $sColumns .= Yii::app()->twigRenderer->renderQuestion($this->getMainView() . '/columns/col', array('cellwidth' => $cellwidth));
        }

        if ($this->bShowNoAnswer) {
            //Question is not mandatory
            $sColumns .= Yii::app()->twigRenderer->renderQuestion($this->getMainView() . '/columns/col', array('cellwidth' => $cellwidth));
        }

        // Column for suffix
        if ($right_exists) {
            $sColumns .= Yii::app()->twigRenderer->renderQuestion($this->getMainView() . '/columns/col', array('cellwidth' => $answerwidth));
        }
        return $sColumns;
    }

    /**
     * Renders the header row cells (empty cell, 5 points, optional suffix and optional "No answer" header).
     *
     * @param bool $right_exists Whether any subquestion has a right side (suffix) text
     * @return string
     */
    private function renderHeaders(bool $right_exists): string
    {
        $sHeaders = Yii::app()->twigRenderer->renderQuestion($this->getMainView() . '/rows/cells/header_information', array(
            'class' => '',
            'content' => '',
        ));
        for ($xc = 1; $xc <= 5; $xc++) {
            $sHeaders .= Yii::app()->twigRenderer->renderQuestion($this->getMainView() . '/rows/cells/header_answer', array(
                'class' => 'answer-text',
                'content' => " " . $xc,
                'basename' => $this->sSGQA,
                'code' => $xc,
            ));
        }

        // Header for suffix
        if ($right_exists) {
            $sHeaders .= Yii::app()->twigRenderer->renderQuestion($this->getMainView() . '/rows/cells/header_information', array(
                'class' => 'answertextright',
                'content' => '',
            ));
        }

        if ($this->bShowNoAnswer) {
            //Question is not mandatory
            $sHeaders .= Yii::app()->twigRenderer->renderQuestion($this->getMainView() . '/rows/cells/header_answer', array(
                'class' => 'answer-text noanswer-text',
                'content' => gT('No answer'),
                'basename' => $this->sSGQA,
                'code' => '',
            ));
        }
        return $sHeaders;
    }

    /**
     * Renders one subquestion row.
     *
     * @param Question $ansrow The subquestion
     * @param int|string $j Index of the subquestion in the ordered list (used for odd/even)
     * @param string $myfname Field name of the subquestion
     * @param string $sSurveyLanguage Language used for the texts
     * @param float|int|string $answerwidth Width of the answer text column in percent
     * @param bool $right_exists Whether any subquestion has a right side (suffix) text
     * @param bool $error Whether this subquestion violates the mandatory rule
     * @return string
     */
    private function renderRow(
        Question $ansrow,
        $j,
        string $myfname,
        $sSurveyLanguage,
        $answerwidth,
        bool $right_exists,
        bool $error
    ): string {
        $sSessionKey = 'responses_' . Yii::app()->getConfig('surveyID');
        $answertext = $ansrow->questionl10ns[$sSurveyLanguage]->question;
        if (strpos((string) $answertext, '|') !== false) {
            $answertext = substr((string) $answertext, 0, strpos((string) $answertext, '|'));
        }

        // Value
        $value = $_SESSION[$sSessionKey][$myfname] ?? '';

        $answer_tds = '';
        for ($i = 1; $i <= 5; $i++) {
            $CHECKED = (isset($_SESSION[$sSessionKey][$myfname]) && $_SESSION[$sSessionKey][$myfname] == $i) ? 'CHECKED' : '';
            $answer_tds .= Yii::app()->twigRenderer->renderQuestion($this->getMainView() . '/rows/cells/answer_td_input', array(
                'i' => $i,
                'labelText' => (string) $i,
                'myfname' => $myfname,
                'basename' => $this->sSGQA,
                'code' => $i,
                'CHECKED' => $CHECKED,
                'checkconditionFunction' => $this->sCheckconditionFunction,
                'value' => $i,
            ));
        }

        // Suffix
        $answertext2 = $ansrow->questionl10ns[$sSurveyLanguage]->question;
        $hasPipeInAnswerText2 = strpos((string) $answertext2, '|');

        if ($hasPipeInAnswerText2) {
            $answertext2 = substr((string) $answertext2, strpos((string) $answertext2, '|') + 1);
            $answer_tds .= Yii::app()->twigRenderer->renderQuestion($this->getMainView() . '/rows/cells/answer_td_answertext', array(
                'class' => 'answertextright',
                'style' => 'text-align:left',
                'answertext2' => $answertext2,
            ));
        } elseif ($right_exists) {
            $answer_tds .= Yii::app()->twigRenderer->renderQuestion($this->getMainView() . '/rows/cells/answer_td_answertext', array(
                'answerwidth' => $answerwidth,
                'answertext2' => '',
            ));
        }

        // ==>tds
        if ($this->bShowNoAnswer) {
            $CHECKED = (
                PRESELECT_NO_ANSWER
                && (
                    !isset($_SESSION[$sSessionKey][$myfname])
                    || $_SESSION[$sSessionKey][$myfname] == ''
                )
            ) ? 'CHECKED' : '';
            $answer_tds .= Yii::app()->twigRenderer->renderQuestion($this->getMainView() . '/rows/cells/answer_td_input', array(
                'i' => "",
                'labelText' => gT('No answer'),
                'myfname' => $myfname,
                'basename' => $this->sSGQA,
                'code' => '',
                'CHECKED' => $CHECKED,
                'checkconditionFunction' => $this->sCheckconditionFunction,
                'value' => '',
            ));
        }

        return Yii::app()->twigRenderer->renderQuestion($this->getMainView() . '/rows/answer_row', array(
            'answer_tds'    => $answer_tds,
            'odd'           => ($j % 2),
            'myfname'       => $myfname,
            'answertext'    => $answertext,
            'answerwidth'   => $answerwidth,
            'value'         => $value,
            'error'         => $error,
            'sDisplayStyle' => '', // return_display_style() is disabled and always returned an empty string
        ));
    }
}
