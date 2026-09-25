<?php

/**
 * RenderClass for Array (Increase/Same/Decrease) Question
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
class RenderArrayOfIncSameDecQuestions extends QuestionBaseRenderer
{
    public $sCoreClass = "ls-answers subquestion-list questions-list radio-array";

    /**
     * Returns the twig view used to render the answer part of the question.
     *
     * @return string
     */
    public function getMainView()
    {
        return '/survey/questions/answer/arrays/increasesamedecrease/answer';
    }

    /**
     * Rows are rendered inside render(), see renderRows().
     *
     * @return void
     */
    public function getRows()
    {
        return;
    }

    /**
     * Renders the Increase/Same/Decrease array question.
     *
     * @param string $sCoreClasses Unused, kept for signature compatibility
     * @return array{0: string, 1: string[]} Rendered answer HTML and the list of input names
     */
    public function render($sCoreClasses = '')
    {
        $aLastMoveResult         = LimeExpressionManager::GetLastMoveResult();
        $aMandatoryViolationSubQ = ($aLastMoveResult['mandViolation'] && $this->isMandatory())
            ? explode("|", (string) $aLastMoveResult['unansweredSQs'])
            : [];

        if (ctype_digit(trim((string) $this->getQuestionAttribute('answer_width')))) {
            $answerwidth = trim((string) $this->getQuestionAttribute('answer_width'));
        } else {
            $answerwidth = 33;
        }

        $cellwidth = 3; // number of columns
        if ($this->showNoAnswer()) {
            ++$cellwidth; // add another column
        }
        // convert number of columns to percentage of table width
        $cellwidth = round(((100 - $answerwidth) / $cellwidth), 1);

        // Get subquestions through ordering service so keep_codes_order is respected
        $sSurveyLanguage = $this->getFromSurveySession('s_lang');
        $aSubquestions   = $this->questionOrderingService->getOrderedSubQuestions(
            $this->oQuestion,
            0,
            $sSurveyLanguage
        );
        $anscount = count($aSubquestions);

        $sColumns = $this->renderColumns($cellwidth);
        $sHeaders = Yii::app()->twigRenderer->renderQuestion(
            '/survey/questions/answer/arrays/increasesamedecrease/rows/cells/thead',
            array(
                'basename' => $this->sSGQA,
                'no_answer' => $this->showNoAnswer()
            )
        );

        $inputnames = [];
        $sRows = $this->renderRows(
            $aSubquestions,
            $sSurveyLanguage,
            $answerwidth,
            $aMandatoryViolationSubQ,
            $inputnames
        );

        $answer = Yii::app()->twigRenderer->renderQuestion($this->getMainView(), array(
            'coreClass'   => $this->sCoreClass,
            'answerwidth' => $answerwidth,
            'sColumns'    => $sColumns,
            'sHeaders'    => $sHeaders,
            'sRows'       => $sRows,
            'anscount'    => $anscount,
            'basename'    => $this->sSGQA,
        ));

        return array($answer, $inputnames);
    }

    /**
     * Renders the column definitions: Increase, Same, Decrease and, if shown, the "No answer" column.
     *
     * Unlike the Yes/Uncertain/No array, the "No answer" column is rendered without the no_answer flag.
     *
     * @param float $cellwidth Width of each answer column in percent of the table width
     * @return string
     */
    private function renderColumns($cellwidth): string
    {
        $odd_even = '';
        $sColumns = '';
        for ($xc = 1; $xc <= 3; $xc++) {
            $odd_even  = alternation($odd_even);
            $sColumns .= Yii::app()->twigRenderer->renderQuestion(
                '/survey/questions/answer/arrays/increasesamedecrease/columns/col',
                array('odd_even' => $odd_even, 'cellwidth' => $cellwidth)
            );
        }

        if ($this->showNoAnswer()) {
            $odd_even  = alternation($odd_even);
            $sColumns .= Yii::app()->twigRenderer->renderQuestion(
                '/survey/questions/answer/arrays/increasesamedecrease/columns/col',
                array('odd_even' => $odd_even, 'cellwidth' => $cellwidth)
            );
        }
        return $sColumns;
    }

    /**
     * Renders one table row per subquestion and collects the input names.
     *
     * @param Question[] $aSubquestions Ordered subquestions of scale 0
     * @param string $sSurveyLanguage Current survey language (from the survey session)
     * @param string|int $answerwidth Width of the subquestion text column in percent
     * @param string[] $aMandatoryViolationSubQ Field names of unanswered mandatory subquestions
     * @param string[] $inputnames Collected input names, one per subquestion (by reference)
     * @return string
     */
    private function renderRows(
        array $aSubquestions,
        $sSurveyLanguage,
        $answerwidth,
        array $aMandatoryViolationSubQ,
        array &$inputnames
    ): string {
        $sRows = '';
        foreach ($aSubquestions as $i => $ansrow) {
            $myfname    = $this->sSGQA . "_S" . $ansrow['qid'];
            $answertext = $ansrow->questionl10ns[$sSurveyLanguage]->question;
            /* Check the sub question mandatory violation */
            $error = ($this->isMandatory() && in_array($myfname, $aMandatoryViolationSubQ)) ? true : false;

            $value     = $this->getFromSurveySession($myfname);
            $Ichecked  = ($value == 'I') ? 'CHECKED' : '';
            $Schecked  = ($value == 'S') ? 'CHECKED' : '';
            $Dchecked  = ($value == 'D') ? 'CHECKED' : '';
            $NAchecked = (PRESELECT_NO_ANSWER && $value == '') ? 'CHECKED' : '';

            $sRows .= Yii::app()->twigRenderer->renderQuestion(
                '/survey/questions/answer/arrays/increasesamedecrease/rows/answer_row',
                array(
                    'basename'               => $this->sSGQA,
                    'myfname'                => $myfname,
                    'answertext'             => $answertext,
                    'answerwidth'            => $answerwidth,
                    'Ichecked'               => $Ichecked,
                    'Schecked'               => $Schecked,
                    'Dchecked'               => $Dchecked,
                    'NAchecked'              => $NAchecked,
                    'value'                  => $value,
                    'checkconditionFunction' => $this->checkconditionFunction,
                    'error'                  => $error,
                    'no_answer'              => $this->showNoAnswer(),
                    'odd'                    => ($i % 2)
                )
            );
            $inputnames[] = $myfname;
        }
        return $sRows;
    }

    /**
     * Whether the question is mandatory ('Y') or soft mandatory ('S').
     *
     * @return bool
     */
    private function isMandatory(): bool
    {
        return $this->aFieldArray[6] == 'Y' || $this->aFieldArray[6] == 'S';
    }

    /**
     * Whether the "No answer" column is shown: question is not (soft) mandatory and "No answer" is enabled.
     *
     * @return bool
     */
    private function showNoAnswer(): bool
    {
        return !$this->isMandatory() && SHOW_NO_ANSWER == 1;
    }
}
