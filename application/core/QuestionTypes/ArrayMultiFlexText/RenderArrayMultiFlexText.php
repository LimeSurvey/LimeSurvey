<?php

/**
 * RenderClass for Array (Texts) Question
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
class RenderArrayMultiFlexText extends QuestionBaseRenderer
{
    /** @var string Language used for the subquestion texts and the ordering */
    private $sSurveyLanguage = '';
    /** @var string Radix point separator of the survey number format */
    private $sSeparator = '.';
    /** @var string[] Field names of the subquestions violating the mandatory rule on last move */
    private $aMandatoryViolationSubQ = [];
    /** @var mixed Repeat the headings every x rows (null or <= 0: never) */
    private $repeatheadings;
    /** @var mixed Minimum number of remaining rows needed to repeat the headings */
    private $minrepeatheadings;

    /** @var string Classes of the answer container */
    private $coreClass = "ls-answers subquestion-list questions-list text-array";
    /** @var string Extra classes of the answer table */
    private $extraclass = "";
    /** @var string Classes of each answer row */
    private $coreRowClass = "subquestion-list questions-list";
    /** @var string Javascript function called on input change */
    private $sCheckconditionFunction = "checkconditions";

    /** @var mixed Value of the show_grand_total attribute, forced to true when totals are shown */
    private $showGrand;
    /** @var string Classes added to the table when totals are shown */
    private $totalsClass = '';
    /** @var string Totals mode : '', 'rowTotals', 'col' or 'both' */
    private $showTotals = '';
    /** @var string Rendered column total cell template */
    private $colTotal = '';
    /** @var string Rendered row total cell template */
    private $rowTotal = '';
    /** @var string Rendered header of the row total column */
    private $colHead = '';
    /** @var string Rendered header of the column total row */
    private $rowHead = '';
    /** @var string Rendered grand total cell */
    private $grandTotal = '';
    /** @var string Id of the table, used by the totals javascript */
    private $qTableId = '';
    /** @var string Id attribute of the table, used by the totals javascript */
    private $qTableIdHtml = '';

    /** @var int 1 if only numbers are allowed, 0 otherwise */
    private $isNumber = 0;
    /** @var int Always 0, kept for question theme backward compatibility */
    private $isInteger = 0;
    /** @var int|string Maximum number of characters, empty string if not set */
    private $maxlength = "";
    /** @var string|null Size of the inputs, null if not set */
    private $inputsize = null;
    /** @var string Placeholder of the inputs */
    private $placeholder = '';

    /** @var int|float|string Width of the subquestion text column */
    private $answerwidth = 33;
    /** @var bool Whether answer_width attribute is not set */
    private $defaultWidth = true;
    /** @var bool Whether at least one Y-scale subquestion has a right text part */
    private $rightExists = false;

    /**
     * Title of the last X-scale subquestion used when rendering the answer cells.
     * Kept as in legacy code: it is used for [[ROW_CODE]] and [[COL_CODE]] replacements.
     * @var string|null
     */
    private $lastCellTitle = null;

    /** @var string[] Names of the inputs */
    private $inputnames = [];

    /**
     * Returns the twig view used to render the answer part of the question.
     *
     * @return string
     */
    public function getMainView()
    {
        return '/survey/questions/answer/arrays/texts/answer';
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
     * Renders the array (texts) question.
     *
     * @param string $sCoreClasses Unused, kept for signature compatibility
     * @return array{0: string, 1: string[]|string} Rendered answer HTML and the list of input names (empty string if there are no subquestions)
     */
    public function render($sCoreClasses = '')
    {
        global $thissurvey;

        $this->registerAssets();

        $aLastMoveResult = LimeExpressionManager::GetLastMoveResult();
        $this->aMandatoryViolationSubQ = ($aLastMoveResult['mandViolation'] && ($this->aFieldArray[6] == 'Y' || $this->aFieldArray[6] == 'S')) ? explode("|", (string) $aLastMoveResult['unansweredSQs']) : [];
        $this->repeatheadings = Yii::app()->getConfig("repeatheadings");
        $this->minrepeatheadings = Yii::app()->getConfig("minrepeatheadings");
        $this->sSeparator = getRadixPointData($thissurvey['surveyls_numberformat'])['separator'];
        $this->sSurveyLanguage = $this->getFromSurveySession('s_lang');
        $this->showGrand = $this->getQuestionAttribute('show_grand_total');
        $this->isNumber = intval($this->getQuestionAttribute('numbers_only') == 1);

        $this->setInputSettings();
        if ($this->getQuestionAttribute('numbers_only') == 1) {
            $this->setTotals();
        }
        $this->setAnswerWidth();
        $columnswidth = 100 - ($this->answerwidth);

        // Get questions and answers by defined order via ordering service (respects keep_codes_order)
        $aSubquestionsX = $this->questionOrderingService->getOrderedSubQuestions($this->oQuestion, 1, $this->sSurveyLanguage);
        $labelans = [];
        $labelans2 = [];
        foreach ($aSubquestionsX as $oSubquestion) {
            $labelans[$oSubquestion->qid] = [
                'label' => $oSubquestion->questionl10ns[$this->sSurveyLanguage]->question,
                'title' => $oSubquestion->title
            ];
            $labelans2[$oSubquestion->title] = $oSubquestion->questionl10ns[$this->sSurveyLanguage]->question;
        }

        if (!($numrows = count($labelans))) {
            $answer = Yii::app()->twigRenderer->renderQuestion('/survey/questions/answer/arrays/texts/empty_error', []);
            return array($answer, '');
        }

        $showGrandTotal = (($this->showGrand == true && $this->showTotals == 'col') || $this->showTotals == 'rowTotals' || $this->showTotals == 'both') ? true : false;
        // There are no "No answer" column
        if ($showGrandTotal) {
            ++$numrows;
        }
        $cellwidth = $columnswidth / $numrows;

        $iCount = Question::model()->with(array('questionl10ns' => array('condition' => "question like '%|%'")))->count('parent_qid=:parent_qid AND scale_id=0', array(':parent_qid' => $this->oQuestion->qid));
        if ($iCount > 0) {
            $this->rightExists = true;
            if (!$this->defaultWidth) {
                $this->answerwidth = $this->answerwidth / 2;
            }
        } else {
            $this->rightExists = false;
        }

        // Get questions and answers by defined order via ordering service (respects keep_codes_order)
        $aQuestionsY = $this->questionOrderingService->getOrderedSubQuestions($this->oQuestion, 0, $this->sSurveyLanguage);
        $sRows = $this->renderRows($aQuestionsY, $labelans, $labelans2);

        $showtotals = false;
        $total = '';
        if ($this->showTotals == 'col' || $this->showTotals == 'both' || $this->grandTotal !== '') {
            $showtotals = true;
            $total = $this->renderColumnTotals($labelans);
        }

        $radix = '';
        if (!empty($this->qTableId)) {
            if ($this->getQuestionAttribute('numbers_only') == 1) {
                $radix = $this->sSeparator;
            } else {
                $radix = 'X'; // to indicate that should not try to change entered values
            }
        }

        $answer = Yii::app()->twigRenderer->renderQuestion($this->getMainView(), array(
            'basename'                  => $this->sSGQA,
            'answerwidth'               => $this->answerwidth,
            'col_head'                  => $this->colHead,
            'cellwidth'                 => $cellwidth,
            'labelans'                  => $labelans2,
            'right_exists'              => $this->rightExists,
            'showGrandTotal'            => $showGrandTotal,
            'q_table_id_HTML'           => $this->qTableIdHtml,
            'coreClass'                 => $this->coreClass,
            'extraclass'                => $this->extraclass,
            'totals_class'              => $this->totalsClass,
            'showtotals'                => $showtotals,
            'row_head'                  => $this->rowHead,
            'total'                     => $total,
            'q_table_id'                => $this->qTableId,
            'radix'                     => $radix,
            'name'                      => $this->oQuestion->qid,
            'sRows'                     => $sRows,
            'checkconditionFunction'    => $this->sCheckconditionFunction
        ));
        return array($answer, $this->inputnames);
    }

    /**
     * Sets the repeat headings, maximum chars, input size and placeholder settings from the question attributes.
     *
     * @return void
     */
    private function setInputSettings()
    {
        if (ctype_digit(trim((string) $this->getQuestionAttribute('repeat_headings'))) && trim((string) $this->getQuestionAttribute('repeat_headings')) != "") {
            $this->repeatheadings = intval($this->getQuestionAttribute('repeat_headings'));
            $this->minrepeatheadings = 0;
        }
        if (intval(trim((string) $this->getQuestionAttribute('maximum_chars'))) > 0) {
            // Only maxlength attribute, use textarea[maxlength] jquery selector for textarea
            $this->maxlength = intval(trim((string) $this->getQuestionAttribute('maximum_chars')));
            $this->extraclass .= " ls-input-maxchars";
        } else {
            $this->maxlength = "";
        }
        if (ctype_digit(trim((string) $this->getQuestionAttribute('input_size')))) {
            $this->inputsize = trim((string) $this->getQuestionAttribute('input_size'));
            $this->extraclass .= " ls-input-sized";
        } else {
            $this->inputsize = null;
        }
        $placeholder = $this->getQuestionAttribute('placeholder', $this->sSurveyLanguage);
        if (trim((string) $placeholder) != '') {
            $this->placeholder = $placeholder;
        } else {
            $this->placeholder = '';
        }
    }

    /**
     * Sets the classes, the javascript function and renders the totals cells for a numbers only question.
     *
     * @return void
     */
    private function setTotals()
    {
        $this->sCheckconditionFunction = "fixnum_checkconditions";

        if (in_array($this->getQuestionAttribute('show_totals'), array("R", "C", "B"))) {
            $this->qTableId = 'totals_' . $this->oQuestion->qid;
            $this->qTableIdHtml = ' id="' . $this->qTableId . '"';
        }

        $this->coreClass .= " number-array";
        $this->coreRowClass .= " number-list";
        $this->colHead = '';
        switch ($this->getQuestionAttribute('show_totals')) {
            case 'R':
                $this->totalsClass = $this->showTotals = 'rowTotals';
                $this->rowTotal = $this->renderTotalCell('/rows/cells/td_total', false);
                $this->colHead = $this->renderTotalHeader(gT('Total'), '');
                if ($this->showGrand == true) {
                    $this->rowHead = $this->renderTotalHeader(gT('Grand total'), 'answertext');
                    $this->colTotal = $this->renderTotalCell('/columns/col_total', true);
                    $this->grandTotal = $this->renderTotalCell('/rows/cells/td_grand_total', false);
                }
                break;

            case 'C':
                $this->totalsClass = $this->showTotals = 'col';
                $this->colTotal = $this->renderTotalCell('/columns/col_total', false, true);
                $this->rowHead = $this->renderTotalHeader(gT('Total'), 'answertext');
                if ($this->showGrand == true) {
                    $this->rowTotal = $this->renderTotalCell('/rows/cells/td_total', true);
                    $this->colHead = $this->renderTotalHeader(gT('Grand total'), '');
                    $this->grandTotal = $this->renderTotalCell('/rows/cells/td_grand_total', false);
                }
                break;

            case 'B':
                $this->totalsClass = $this->showTotals = 'both';
                $this->rowTotal = $this->renderTotalCell('/rows/cells/td_total', false);
                $this->colTotal = $this->renderTotalCell('/columns/col_total', false, false);
                $this->colHead = $this->renderTotalHeader(gT('Total'), '');
                $this->rowHead = $this->renderTotalHeader(gT('Total'), 'answertext');
                if ($this->showGrand == true) {
                    $this->grandTotal = $this->renderTotalCell('/rows/cells/td_grand_total', false);
                } else {
                    $this->grandTotal = $this->renderTotalCell('/rows/cells/td_grand_total', true);
                }
                break;
        }

        if (!empty($this->totalsClass)) {
            $this->totalsClass = ' show-totals ' . $this->totalsClass;
            if ($this->getQuestionAttribute('show_grand_total')) {
                $this->totalsClass .= ' grand';
                $this->showGrand = true;
            }
        }
    }

    /**
     * Renders a total cell (row total, column total or grand total).
     *
     * @param string $sView View path relative to the texts array view directory
     * @param bool $bEmpty Whether the cell is rendered empty
     * @param bool|null $bLabel Value of the 'label' view variable, null to not pass it
     * @return string
     */
    private function renderTotalCell(string $sView, bool $bEmpty, ?bool $bLabel = null): string
    {
        $aData = array('empty' => $bEmpty, 'inputsize' => $this->inputsize);
        if ($bLabel !== null) {
            $aData['label'] = $bLabel;
        }
        $aData['basename'] = $this->sSGQA;
        return Yii::app()->twigRenderer->renderQuestion('/survey/questions/answer/arrays/texts' . $sView, $aData);
    }

    /**
     * Renders a totals header cell.
     *
     * @param string $sTotalText Text of the header
     * @param string $sClasses Classes of the header
     * @return string
     */
    private function renderTotalHeader(string $sTotalText, string $sClasses): string
    {
        return Yii::app()->twigRenderer->renderQuestion(
            '/survey/questions/answer/arrays/texts/rows/cells/thead',
            array('totalText' => $sTotalText, 'classes' => $sClasses, 'basename' => $this->sSGQA)
        );
    }

    /**
     * Sets the width of the subquestion text column from the answer_width attribute.
     *
     * @return void
     */
    private function setAnswerWidth()
    {
        if (ctype_digit(trim((string) $this->getQuestionAttribute('answer_width')))) {
            $this->answerwidth = trim((string) $this->getQuestionAttribute('answer_width'));
            $this->defaultWidth = false;
        } else {
            $this->answerwidth = 33;
            $this->defaultWidth = true;
        }
    }

    /**
     * Renders all the answer rows (one by Y-scale subquestion), including the repeated headers.
     *
     * @param Question[] $aQuestionsY Ordered Y-scale (scale 0) subquestions
     * @param array<int, array{label: string, title: string}> $labelans X-scale subquestions by qid
     * @param array<string, string> $labelans2 X-scale subquestion texts by code
     * @return string
     */
    private function renderRows(array $aQuestionsY, array $labelans, array $labelans2): string
    {
        $anscount = count($aQuestionsY);
        $fn = 1;
        $sRows = '';
        foreach ($aQuestionsY as $j => $ansrow) {
            if (isset($this->repeatheadings) && $this->repeatheadings > 0 && ($fn - 1) > 0 && ($fn - 1) % $this->repeatheadings == 0) {
                if (($anscount - $fn + 1) >= $this->minrepeatheadings) {
                    // Close actual body and open another one
                    $sRows .= Yii::app()->twigRenderer->renderQuestion('/survey/questions/answer/arrays/texts/rows/repeat_header', array(
                        'basename'     => $this->sSGQA,
                        'answerwidth'  => $this->answerwidth,
                        'labelans'     => $labelans2,
                        'right_exists' => $this->rightExists,
                        'col_head'     => $this->colHead,
                    ));
                }
            }
            $sRows .= $this->renderRow($ansrow, $j, $labelans);
            $fn++;
        }
        return $sRows;
    }

    /**
     * Renders one answer row.
     *
     * @param Question $ansrow Y-scale subquestion of the row
     * @param int $j Index of the row
     * @param array<int, array{label: string, title: string}> $labelans X-scale subquestions by qid
     * @return string
     */
    private function renderRow(Question $ansrow, $j, array $labelans): string
    {
        $myfname = $this->sSGQA . "_S" . $ansrow['qid'];
        $answertext = $ansrow->questionl10ns[$this->sSurveyLanguage]->question;
        $answertextsave = $answertext;

        $error = false;
        if (($this->aFieldArray[6] == 'Y' || $this->aFieldArray[6] == 'S') && !empty($this->aMandatoryViolationSubQ)) {
            //Go through each labelcode and check for a missing answer! If any are found, highlight this line
            foreach ($labelans as $qid => $aLabel) {
                if (in_array($myfname . '_S' . $qid, $this->aMandatoryViolationSubQ)) {
                    $error = true;
                }
            }
        }

        if (strpos((string) $answertext, '|') !== false) {
            $answertext = (string) substr((string) $answertext, 0, strpos((string) $answertext, '|'));
        }

        [$answer_tds, $value] = $this->renderCells($myfname, $labelans, $error);

        $rightTd = $rightTdEmpty = false;
        if (strpos((string) $answertextsave, '|') !== false) {
            $answertext = (string) substr((string) $answertextsave, strpos((string) $answertextsave, '|') + 1);
            $rightTd = true;
            $rightTdEmpty = false;
        } elseif ($this->rightExists) {
            $rightTd = true;
            $rightTdEmpty = true;
        }
        // Legacy behaviour: [[ROW_CODE]] is replaced by the code of the last column
        $formatedRowTotal = str_replace(
            array('[[ROW_CODE]]', '[[ROW_NAME]]'),
            array($this->lastCellTitle, LimeExpressionManager::ProcessString($answertext, $this->oQuestion->qid)),
            strval($this->rowTotal)
        );
        return Yii::app()->twigRenderer->renderQuestion('/survey/questions/answer/arrays/texts/rows/answer_row', array(
            'myfname'           =>  $myfname,
            'basename'          => $this->sSGQA,
            'coreRowClass'      => $this->coreRowClass,
            'answertext'        => $answertext,
            'error'             => $error,
            'value'             => $value,
            'placeholder'       => $this->placeholder,
            'answer_tds'        => $answer_tds,
            'rightTd'           => $rightTd,
            'rightTdEmpty'      => $rightTdEmpty,
            'answerwidth'       => $this->answerwidth,
            'formatedRowTotal'  => $formatedRowTotal,
            'odd'               => ($j % 2),
        ));
    }

    /**
     * Renders the answer cells of one row and registers their input names.
     *
     * @param string $myfname Field name of the row
     * @param array<int, array{label: string, title: string}> $labelans X-scale subquestions by qid
     * @param bool $error Whether the row violates the mandatory rule
     * @return array{0: string, 1: string} Rendered cells and the escaped value of the last cell (legacy row value)
     */
    private function renderCells(string $myfname, array $labelans, bool $error): array
    {
        $answer_tds = '';
        $value = '';
        foreach ($labelans as $qid => $aLabel) {
            $this->lastCellTitle = $aLabel['title'];
            $myfname2 = $myfname . "_S$qid";
            $myfname2value = $this->getFromSurveySession($myfname2);

            if ($this->getQuestionAttribute('numbers_only') == 1) {
                $myfname2value = str_replace('.', $this->sSeparator, (string) $myfname2value);
            }

            $this->inputnames[] = $myfname2;
            $value = str_replace('"', "'", str_replace('\\', '', (string) $myfname2value));
            $answer_tds .= Yii::app()->twigRenderer->renderQuestion('/survey/questions/answer/arrays/texts/rows/cells/answer_td', array(
                'ld'         => $aLabel['title'],
                'basename'   => $this->sSGQA,
                'myfname2'   => $myfname2,
                'labelText'  => $aLabel['label'],
                'kpclass'    => '', // Old class of removed keypad functionality, kept for question theme backward compatibility
                'maxlength'  => $this->maxlength,
                'inputsize'  => $this->inputsize,
                'value'      => $myfname2value,
                'placeholder' => $this->placeholder,
                'isNumber'   => $this->isNumber,
                'isInteger'  => $this->isInteger,
                'error'      => ($error && $myfname2value === ''),
            ));
        }
        return array($answer_tds, $value);
    }

    /**
     * Renders the column totals cells followed by the grand total cell.
     *
     * @param array<int, array{label: string, title: string}> $labelans X-scale subquestions by qid
     * @return string
     */
    private function renderColumnTotals(array $labelans): string
    {
        $total = '';
        foreach ($labelans as $aLabel) {
            // Legacy behaviour: [[COL_CODE]] is replaced by the code of the last column of the last row
            $total .= str_replace(
                array('[[COL_CODE]]', '[[COL_NAME]]'),
                array($this->lastCellTitle, LimeExpressionManager::ProcessString($aLabel['label'], $this->oQuestion->qid)),
                strval($this->colTotal)
            );
        }
        return $total . $this->grandTotal;
    }
}
