<?php

/**
 * RenderClass for Array (Numbers) Question
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
class RenderArrayMultiFlexNumbers extends QuestionBaseRenderer
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
    private $coreClass = "ls-answers subquestion-list questions-list";
    /** @var string Classes of each answer row */
    private $coreRowClass = "subquestion-list questions-list";
    /** @var string Extra classes of the inputs */
    private $extraclass = "";
    /** @var string Classes of each answer item */
    private $answertypeclass = "";
    /** @var string Javascript function called on input change */
    private $sCheckconditionFunction = "fixnum_checkconditions";

    /** @var mixed Minimum value of the dropdown (or first value if reversed) */
    private $minvalue = 1;
    /** @var mixed Maximum value of the dropdown (or last value if reversed) */
    private $maxvalue = 10;
    /** @var mixed Step of the dropdown values, negative if reversed */
    private $stepvalue = 1;
    /** @var bool Whether the dropdown values are reversed */
    private $reverse = false;

    /** @var string Layout of the cells : 'checkbox', 'text' or 'dropdown' */
    private $layout = "dropdown";
    /** @var bool Whether the checkbox layout is used */
    private $checkboxlayout = false;
    /** @var bool Whether the text input layout is used */
    private $inputboxlayout = false;
    /** @var string Alignment of the header texts */
    private $textAlignment = 'right';

    /** @var int|string Maximum number of characters, empty string if not set */
    private $maxlength = "";
    /** @var string|null Size of the inputs, null if not set */
    private $inputsize = null;

    /** @var int|float|string Width of the subquestion text column */
    private $answerwidth = 33;
    /** @var bool Always false (legacy): the answer width is halved when a right text exists */
    private $defaultWidth = false;
    /** @var bool Whether at least one Y-scale subquestion has a right text part */
    private $rightExists = false;
    /** @var int|float Width of each answer column */
    private $cellwidth;

    /** @var string[] Names of the inputs */
    private $inputnames = [];

    /**
     * Returns the twig view used to render the answer part of the question.
     *
     * @return string
     */
    public function getMainView()
    {
        return '/survey/questions/answer/arrays/multiflexi/answer';
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
     * Renders the array (numbers) question.
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

        $this->setMinMaxStep();
        $this->setLayout();
        $this->setInputSettings();

        $columnswidth = 100 - ($this->answerwidth);
        $this->sSurveyLanguage = $this->getFromSurveySession('s_lang');
        // Get questions and answers by defined order via ordering service (respects keep_codes_order)
        $aQuestions = $this->questionOrderingService->getOrderedSubQuestions($this->oQuestion, 1, $this->sSurveyLanguage);
        $labelans = [];
        $labelcode = [];
        $labeltitle = [];
        foreach ($aQuestions as $lrow) {
            $labelans[] = $lrow->questionl10ns[$this->sSurveyLanguage]->question;
            $labelcode[] = [
                'title' => $lrow['title'],
                'qid' => $lrow['qid']
            ];
            $labeltitle[] = $lrow['title'];
        }

        if (!($numrows = count($labelans))) {
            $answer = Yii::app()->twigRenderer->renderQuestion('/survey/questions/answer/arrays/multiflexi/empty_error', []);
            return array($answer, '');
        }

        // There are no "No answer" column
        $this->cellwidth = $columnswidth / $numrows;
        $iCount = Question::model()->with(array('questionl10ns' => array('condition' => "question like '%|%'")))->countByAttributes([], 'parent_qid=:parent_qid AND scale_id=0', array(':parent_qid' => $this->oQuestion->qid));
        // $right_exists is a flag to find out if there are any right hand answer parts. If there aren't we can leave out the right td column
        if ($iCount > 0) {
            $this->rightExists = true;
            if (!$this->defaultWidth) {
                $this->answerwidth = $this->answerwidth / 2;
            }
        } else {
            $this->rightExists = false;
        }

        // Get questions and answers by defined order via ordering service (respects keep_codes_order)
        $aSubquestions = $this->applyParentOrder(
            $this->questionOrderingService->getOrderedSubQuestions($this->oQuestion, 0, $this->sSurveyLanguage)
        );
        if (!empty($aSubquestions)) {
            // Only read when there is at least one row, as in legacy code
            $this->sSeparator = getRadixPointData($thissurvey['surveyls_numberformat'])['separator'];
        }
        $sAnswerRows = $this->renderRows($aSubquestions, $labelans, $labelcode, $labeltitle);

        $answer = Yii::app()->twigRenderer->renderQuestion($this->getMainView(), array(
            'answertypeclass'   => $this->answertypeclass,
            'coreClass'         => $this->coreClass,
            'basename'          => $this->sSGQA,
            'extraclass'        => $this->extraclass,
            'answerwidth'       => $this->answerwidth,
            'labelans'          => $labelans,
            'labelcode'         => $labeltitle,
            'cellwidth'         => $this->cellwidth,
            'right_exists'      => $this->rightExists,
            'sAnswerRows'       => $sAnswerRows,
            'textAlignment'     => $this->textAlignment,
        ));
        return array($answer, $this->inputnames);
    }

    /**
     * Sets the minimum, maximum and step values of the dropdown from the question attributes.
     *
     * @return void
     */
    private function setMinMaxStep()
    {
        $sMin = $this->getQuestionAttribute('multiflexible_min');
        $sMax = $this->getQuestionAttribute('multiflexible_max');
        $this->minvalue = 1;
        $this->maxvalue = 10;
        if (trim((string) $sMax) != '' && trim((string) $sMin) == '') {
            $this->maxvalue = $sMax;
            $this->minvalue = 1;
        }
        if (trim((string) $sMin) != '' && trim((string) $sMax) == '') {
            $this->minvalue = $sMin;
            $this->maxvalue = $sMin + 10;
        }
        if (trim((string) $sMin) != '' && trim((string) $sMax) != '') {
            if ($sMin < $sMax) {
                $this->minvalue = $sMin;
                $this->maxvalue = $sMax;
            }
        }

        $sStep = $this->getQuestionAttribute('multiflexible_step');
        $this->stepvalue = (trim((string) $sStep) != '' && $sStep > 0) ? $sStep : 1;

        if ($this->getQuestionAttribute('reverse') == 1) {
            $tmp = $this->minvalue;
            $this->minvalue = $this->maxvalue;
            $this->maxvalue = $tmp;
            $this->reverse = true;
            $this->stepvalue = -$this->stepvalue;
        } else {
            $this->reverse = false;
        }
    }

    /**
     * Sets the layout (checkbox, text or dropdown) and the related classes, registers the checkbox script if needed.
     *
     * @return void
     */
    private function setLayout()
    {
        $this->checkboxlayout = false;
        $this->inputboxlayout = false;
        $this->textAlignment = 'right';

        if ($this->getQuestionAttribute('multiflexible_checkbox') != 0) {
            $this->layout = "checkbox";
            $this->minvalue = 0;
            $this->maxvalue = 1;
            $this->checkboxlayout = true;
            $this->answertypeclass = " checkbox-item";
            $this->coreClass .= " checkbox-array";
            $this->coreRowClass .= " checkbox-list";
            $this->textAlignment = 'center';
            App()->getClientScript()->registerScriptFile(Yii::app()->getConfig('generalscripts') . "array-number-checkbox.js", CClientScript::POS_BEGIN);
            App()->getClientScript()->registerScript("doArrayNumberCheckbox", "doArrayNumberCheckbox();\n", LSYii_ClientScript::POS_POSTSCRIPT);
        } elseif ($this->getQuestionAttribute('input_boxes') != 0) {
            $this->layout = "text";
            $this->inputboxlayout = true;
            $this->answertypeclass .= " numeric-item text-item";
            $this->coreClass .= " text-array number-array";
            $this->coreRowClass .= " text-list number-list";
            $this->extraclass .= " numberonly";
        } else {
            $this->layout = "dropdown";
            $this->answertypeclass = " ls-dropdown-item";
            $this->coreClass .= " dropdown-array";
            $this->coreRowClass .= " dropdown-list";
        }
    }

    /**
     * Sets the repeat headings, maximum chars, input size and answer width settings from the question attributes.
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
            $this->extraclass .= " ls-input-maxchars"; // @todo : move to data or fix class
        } else {
            $this->maxlength = "";
        }
        if (ctype_digit(trim((string) $this->getQuestionAttribute('input_size')))) {
            $this->inputsize = trim((string) $this->getQuestionAttribute('input_size'));
            $this->extraclass .= " ls-input-sized";
        } else {
            $this->inputsize = null;
        }

        if (ctype_digit(trim((string) $this->getQuestionAttribute('answer_width')))) {
            $this->answerwidth = trim((string) $this->getQuestionAttribute('answer_width'));
        } else {
            $this->answerwidth = 33;
        }
        // Legacy behaviour: never the default width, so the answer width is always halved when a right text exists
        $this->defaultWidth = false;
    }

    /**
     * Reorders the subquestions by the answer order of the question set in the parent_order attribute, if any.
     *
     * @param Question[] $aSubquestions Ordered Y-scale (scale 0) subquestions
     * @return Question[]
     */
    private function applyParentOrder(array $aSubquestions): array
    {
        if (trim((string) $this->getQuestionAttribute('parent_order')) != '') {
            $iParentQID = (int) $this->getQuestionAttribute('parent_order');
            $aResult = [];
            $sessionao = $this->getFromSurveySession('answer_order', []);

            if (isset($sessionao[$iParentQID])) {
                foreach ($sessionao[$iParentQID] as $aOrigRow) {
                    $sCode = $aOrigRow['title'];

                    foreach ($aSubquestions as $aRow) {
                        if ($sCode == $aRow['title']) {
                            $aResult[] = $aRow;
                        }
                    }
                }
                $aSubquestions = $aResult;
            }
        }
        return $aSubquestions;
    }

    /**
     * Renders all the answer rows (one by Y-scale subquestion), including the repeated headers.
     *
     * @param Question[] $aSubquestions Ordered Y-scale (scale 0) subquestions
     * @param string[] $labelans X-scale subquestion texts
     * @param array<int, array{title: string, qid: int}> $labelcode X-scale subquestion codes and ids
     * @param string[] $labeltitle X-scale subquestion codes
     * @return string
     */
    private function renderRows(array $aSubquestions, array $labelans, array $labelcode, array $labeltitle): string
    {
        $anscount = count($aSubquestions);
        $fn = 1;
        $sAnswerRows = '';
        foreach ($aSubquestions as $j => $aSubquestion) {
            if (isset($this->repeatheadings) && $this->repeatheadings > 0 && ($fn - 1) > 0 && ($fn - 1) % $this->repeatheadings == 0) {
                if (($anscount - $fn + 1) >= $this->minrepeatheadings) {
                    $sAnswerRows .= Yii::app()->twigRenderer->renderQuestion('/survey/questions/answer/arrays/multiflexi/rows/repeat_header', array(
                        'basename'      => $this->sSGQA,
                        'labelans'      =>  $labelans,
                        'labelcode'     =>  $labeltitle,
                        'right_exists'  =>  $this->rightExists,
                        'cellwidth'     =>  $this->cellwidth,
                        'answerwidth'   =>  $this->answerwidth,
                        'textAlignment' => $this->textAlignment,
                    ));
                }
            }
            $sAnswerRows .= $this->renderRow($aSubquestion, $j, $labelans, $labelcode, $labeltitle);
            $fn++;
        }
        return $sAnswerRows;
    }

    /**
     * Renders one answer row.
     *
     * @param Question $aSubquestion Y-scale subquestion of the row
     * @param int $j Index of the row
     * @param string[] $labelans X-scale subquestion texts
     * @param array<int, array{title: string, qid: int}> $labelcode X-scale subquestion codes and ids
     * @param string[] $labeltitle X-scale subquestion codes
     * @return string
     */
    private function renderRow(Question $aSubquestion, $j, array $labelans, array $labelcode, array $labeltitle): string
    {
        $myfname = $this->sSGQA . "_S" . $aSubquestion['qid'];
        $answertext = $aSubquestion->questionl10ns[$this->sSurveyLanguage]->question;
        $answertextsave = $answertext;

        /* Check the sub Q mandatory violation */
        $error = false;
        if (($this->aFieldArray[6] == 'Y' || $this->aFieldArray[6] == 'S') && !empty($this->aMandatoryViolationSubQ)) {
            //Go through each labelcode and check for a missing answer! Default :If any are found, highlight this line, checkbox : if one is not found : don't highlight
            // PS : we really need a better system : event for EM !
            $emptyresult = ($this->getQuestionAttribute('multiflexible_checkbox') != 0) ? 1 : 0;
            foreach ($labelcode as $ld) {
                $myfname2 = $myfname . '_S' . $ld['qid'];
                if ($this->getQuestionAttribute('multiflexible_checkbox') != 0) {
                    if (!in_array($myfname2, $this->aMandatoryViolationSubQ)) {
                        $emptyresult = 0;
                    }
                } else {
                    if (in_array($myfname2, $this->aMandatoryViolationSubQ)) {
                        $emptyresult = 1;
                    }
                }
            }
            $error = ($emptyresult == 1) ? true : false;
        }

        if (strpos((string) $answertext, '|') !== false) {
            $answertext = (string) substr((string) $answertext, 0, strpos((string) $answertext, '|'));
        }

        $row_value = $this->getFromSurveySession($myfname);
        $answer_tds = $this->renderCells($myfname, $answertext, $error, $labelans, $labelcode, $labeltitle);

        $rightTd = false;
        $answertextright = '';
        // Legacy behaviour: a "|" at the very start of the text is not considered as a separator
        if (strpos((string) $answertextsave, '|')) {
            $answertextright = substr((string) $answertextsave, strpos((string) $answertextsave, '|') + 1);
            $rightTd = true;
        } elseif ($this->rightExists) {
            $rightTd = true;
        }

        return Yii::app()->twigRenderer->renderQuestion('/survey/questions/answer/arrays/multiflexi/rows/answer_row', array(
            'basename'          => $this->sSGQA,
            'sDisplayStyle'     => '', // return_display_style() is disabled and always returned an empty string
            'coreRowClass'      => $this->coreRowClass,
            'answerwidth'       => $this->answerwidth,
            'myfname'           => $myfname,
            'error'             => $error,
            'row_value'         => $row_value,
            'answertext'        => $answertext,
            'answertextright'   => $answertextright,
            'answer_tds'        => $answer_tds,
            'rightTd'           => $rightTd,
            'odd'               => ($j % 2),
            'layout'            => $this->layout
        ));
    }

    /**
     * Renders the answer cells (inputs, dropdowns or checkboxes) of one row and registers their input names.
     *
     * @param string $myfname Field name of the row
     * @param string|null $answertext Left text of the row subquestion
     * @param bool $error Whether the row violates the mandatory rule
     * @param string[] $labelans X-scale subquestion texts
     * @param array<int, array{title: string, qid: int}> $labelcode X-scale subquestion codes and ids
     * @param string[] $labeltitle X-scale subquestion codes
     * @return string
     */
    private function renderCells(string $myfname, $answertext, bool $error, array $labelans, array $labelcode, array $labeltitle): string
    {
        $answer_tds = '';
        foreach ($labelcode as $i => $ld) {
            $myfname2 = $myfname . "_S{$ld['qid']}";
            $value = $this->getFromSurveySession($myfname2);
            // Possibly replace '.' with ','
            if (is_numeric($value)) {
                $value = str_replace('.', $this->sSeparator, (string) $value);
            }

            if ($this->checkboxlayout === false) {
                $answer_tds .= Yii::app()->twigRenderer->renderQuestion('/survey/questions/answer/arrays/multiflexi/rows/cells/answer_td', array(
                    'basename'                  => $this->sSGQA,
                    'dataTitle'                 => $labelans[$i],
                    'dataCode'                  => $labeltitle[$i],
                    'ld'                        => $ld['title'],
                    'answertypeclass'           => $this->answertypeclass,
                    'answertext'                => $answertext,
                    'stepvalue'                 => $this->stepvalue,
                    'extraclass'                => $this->extraclass,
                    'myfname2'                  => $myfname2,
                    'inputboxlayout'            => $this->inputboxlayout,
                    'checkconditionFunction'    => $this->sCheckconditionFunction,
                    'minvalue'                  => $this->minvalue,
                    'maxvalue'                  => $this->maxvalue,
                    'reverse'                   => $this->reverse,
                    'value'                     => $value,
                    'sSeparator'                => $this->sSeparator,
                    'kpclass'                   => '', // Old class of removed keypad functionality, kept for question theme backward compatibility
                    'maxlength'                 => $this->maxlength,
                    'inputsize'                 => $this->inputsize,
                    'error'                     => ($error && $value === '')
                ));
            } else {
                if ($this->getFromSurveySession($myfname2) == '1') {
                    $myvalue = '1';
                    $setmyvalue = CHECKED;
                } else {
                    $myvalue = '';
                    $setmyvalue = '';
                }

                $answer_tds .= Yii::app()->twigRenderer->renderQuestion('/survey/questions/answer/arrays/multiflexi/rows/cells/answer_td_checkboxes', array(
                    'basename'                  => $this->sSGQA,
                    'dataTitle'                 => $labelans[$i],
                    'dataCode'                  => $labelcode[$i]['title'],
                    'ld'                        => $ld['title'],
                    'answertypeclass'           => $this->answertypeclass,
                    'value'                     => $myvalue,
                    'setmyvalue'                => $setmyvalue,
                    'myfname2'                  => $myfname2,
                    'checkconditionFunction'    => $this->sCheckconditionFunction,
                    'extraclass'                => $this->extraclass,
                    'qid'                       => $ld['qid'],
                ));
            }
            $this->inputnames[] = $myfname2;
        }
        return $answer_tds;
    }
}
