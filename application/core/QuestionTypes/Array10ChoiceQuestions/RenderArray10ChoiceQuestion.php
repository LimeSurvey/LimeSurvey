<?php

/**
 * RenderClass for Array (10 point choice) Question
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
class RenderArray10ChoiceQuestion extends QuestionBaseRenderer
{
    /**
     * Returns the base path of the twig views used to render this question type.
     *
     * @return string
     */
    public function getMainView()
    {
        return '/survey/questions/answer/arrays/10point';
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
     * Renders the array (10 point choice) question.
     *
     * @param string $sCoreClasses Unused, kept for signature compatibility
     * @return array{0: string, 1: string[]} Rendered answer HTML and the list of input names
     */
    public function render($sCoreClasses = '')
    {
        $this->registerAssets();

        $aLastMoveResult = LimeExpressionManager::GetLastMoveResult();
        $sMandatory = $this->aFieldArray[6];
        $aMandatoryViolationSubQ = ($aLastMoveResult['mandViolation'] && ($sMandatory == 'Y' || $sMandatory == 'S')) ? explode("|", (string) $aLastMoveResult['unansweredSQs']) : [];
        $coreClass = "ls-answers subquestion-list questions-list radio-array";

        if (ctype_digit(trim((string) $this->getQuestionAttribute('answer_width')))) {
            $answerwidth = trim((string) $this->getQuestionAttribute('answer_width'));
        } else {
            $answerwidth = 33;
        }
        // "No answer" column (and header) is shown only if the question is neither mandatory nor soft mandatory
        $bShowNoAnswerColumn = ($sMandatory != 'Y' && $sMandatory != 'S') && SHOW_NO_ANSWER == 1;
        $cellwidth = 10; // number of columns
        if ($bShowNoAnswerColumn) {
            //Question is not mandatory
            ++$cellwidth; // add another column
        }
        $cellwidth = round(((100 - $answerwidth) / $cellwidth), 1); // convert number of columns to percentage of table width

        // Get subquestions using ordering service so keep_codes_order is respected
        $iSurveyId = $this->oQuestion->sid;
        $sSurveyLanguage = isset($_SESSION['responses_' . $iSurveyId]) ? $_SESSION['responses_' . $iSurveyId]['s_lang'] : $this->oQuestion->survey->language;
        $aSubquestions = $this->questionOrderingService->getOrderedSubQuestions($this->oQuestion, 0, $sSurveyLanguage);

        $sColumns = $this->renderColumns($cellwidth, $bShowNoAnswerColumn);
        $sHeaders = $this->renderHeaders($bShowNoAnswerColumn);

        $sRows = '';
        $inputnames = [];
        foreach ($aSubquestions as $j => $ansrow) {
            $myfname = $this->sSGQA . "_S" . $ansrow['qid'];
            $sRows .= $this->renderRow(
                $ansrow,
                $j,
                $myfname,
                $sSurveyLanguage,
                $answerwidth,
                ($sMandatory == 'Y' || $sMandatory == 'S') && in_array($myfname, $aMandatoryViolationSubQ)
            );
            $inputnames[] = $myfname;
        }

        $answer = Yii::app()->twigRenderer->renderQuestion(
            $this->getMainView() . '/answer',
            array(
                'coreClass'     => $coreClass,
                'answerwidth'   => $answerwidth,
                'sColumns'      => $sColumns,
                'sHeaders'      => $sHeaders,
                'sRows'         => $sRows,
                'basename' => $this->sSGQA,
            )
        );
        return array($answer, $inputnames);
    }

    /**
     * Renders the column definitions (10 points and optional "No answer" column).
     *
     * @param float $cellwidth Width of each answer column in percent
     * @param bool $bShowNoAnswerColumn Whether the "No answer" column is shown
     * @return string
     */
    private function renderColumns($cellwidth, bool $bShowNoAnswerColumn): string
    {
        $odd_even = '';
        $sColumns = '';
        for ($xc = 1; $xc <= 10; $xc++) {
            $odd_even = alternation($odd_even);
            $sColumns .= Yii::app()->twigRenderer->renderQuestion($this->getMainView() . '/columns/col', array('odd_even' => $odd_even, 'cellwidth' => $cellwidth));
        }

        if ($bShowNoAnswerColumn) {
            //Question is not mandatory
            $odd_even = alternation($odd_even);
            $sColumns .= Yii::app()->twigRenderer->renderQuestion($this->getMainView() . '/columns/col', array('odd_even' => $odd_even, 'cellwidth' => $cellwidth));
        }
        return $sColumns;
    }

    /**
     * Renders the header row cells (empty cell, 10 points and optional "No answer" header).
     *
     * @param bool $bShowNoAnswerColumn Whether the "No answer" header is shown
     * @return string
     */
    private function renderHeaders(bool $bShowNoAnswerColumn): string
    {
        $sHeaders = Yii::app()->twigRenderer->renderQuestion(
            $this->getMainView() . '/rows/cells/header_information',
            array(
                'class' => '',
                'content' => '',
                'basename' => $this->sSGQA,
                'code' => '',
            )
        );
        for ($xc = 1; $xc <= 10; $xc++) {
            $sHeaders .= Yii::app()->twigRenderer->renderQuestion(
                $this->getMainView() . '/rows/cells/header_answer',
                array(
                    'class' => 'answer-text',
                    'content' => " " . $xc,
                    'basename' => $this->sSGQA,
                    'code' => $xc,
                )
            );
        }

        if ($bShowNoAnswerColumn) {
            //Question is not mandatory
            $sHeaders .= Yii::app()->twigRenderer->renderQuestion(
                $this->getMainView() . '/rows/cells/header_answer',
                array(
                    'class' => 'answer-text noanswer-text',
                    'content' => gT('No answer'),
                    'basename' => $this->sSGQA,
                    'code' => '',
                )
            );
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
     * @param int|string $answerwidth Width of the answer text column in percent
     * @param bool $error Whether this subquestion violates the mandatory rule
     * @return string
     */
    private function renderRow(
        Question $ansrow,
        $j,
        string $myfname,
        $sSurveyLanguage,
        $answerwidth,
        bool $error
    ): string {
        $answertext = $ansrow->questionl10ns[$sSurveyLanguage]->question;

        // Value (null if not set in session)
        $sessionValue = $this->getFromSurveySession($myfname, null);
        $value = $sessionValue ?? '';

        $answer_tds = '';
        for ($i = 1; $i <= 10; $i++) {
            $CHECKED = ($sessionValue !== null && $sessionValue == $i) ? 'CHECKED' : '';

            $answer_tds .= Yii::app()->twigRenderer->renderQuestion(
                $this->getMainView() . '/rows/cells/answer_td_input',
                array(
                    'i' => $i,
                    'labelText' => (string) $i,
                    'myfname' => $myfname,
                    'basename' => $this->sSGQA,
                    'code' => $i,
                    'CHECKED' => $CHECKED,
                    'value' => $i,
                )
            );
        }

        // Legacy quirk: unlike the column and header, the "No answer" cell is only hidden for mandatory 'Y',
        // so it is still rendered for soft mandatory ('S') questions.
        if ($this->aFieldArray[6] != "Y" && SHOW_NO_ANSWER == 1) {
            $CHECKED = (
                PRESELECT_NO_ANSWER
                && (
                    $sessionValue === null
                    || $sessionValue == ''
                )
            ) ? 'CHECKED' : '';
            $answer_tds .= Yii::app()->twigRenderer->renderQuestion(
                $this->getMainView() . '/rows/cells/answer_td_input',
                array(
                    'i' => '',
                    'labelText' => gT('No answer'),
                    'myfname' => $myfname,
                    'basename' => $this->sSGQA,
                    'code' => '',
                    'CHECKED' => $CHECKED,
                    'value' => '',
                )
            );
        }

        return Yii::app()->twigRenderer->renderQuestion(
            $this->getMainView() . '/rows/answer_row',
            array(
                'myfname'       => $myfname,
                'answerwidth'   => $answerwidth,
                'answertext'    => $answertext,
                'value'         => $value,
                'error'         => $error,
                'sDisplayStyle' => '', // return_display_style() is disabled and always returned an empty string
                'odd'           => ($j % 2),
                'answer_tds'    => $answer_tds,
            )
        );
    }
}
