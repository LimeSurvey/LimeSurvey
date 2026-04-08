<?php

/**
 * RenderClass for Boilerplate Question
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
class RenderArrayFlexibleRow extends QuestionBaseRenderer
{
    private $aMandatoryViolationSubQ;
    private $repeatheadings;
    private $minrepeatheadings;
    private $defaultWidth;
    private $columnswidth;
    private $answerwidth;
    private $cellwidth;
    private $sHeaders = '';
    private $sRepeatHeaders = '';

    private $rightExists;
    private $bUseDropdownLayout = false;

    private $inputnames = [];

    public $sCoreClass = "ls-answers subquestion-list questions-list";

    public function __construct($aFieldArray, $bRenderDirect = false)
    {
        parent::__construct($aFieldArray, $bRenderDirect);

        $aLastMoveResult         = LimeExpressionManager::GetLastMoveResult();
        $this->aMandatoryViolationSubQ = ($aLastMoveResult['mandViolation'] && $this->oQuestion->mandatory == 'Y') ? explode("|", (string) $aLastMoveResult['unansweredSQs']) : [];
        $this->repeatheadings    = Yii::app()->getConfig("repeatheadings");
        $this->minrepeatheadings = Yii::app()->getConfig("minrepeatheadings");

        if (ctype_digit((string) $this->repeatheadings) && !empty($this->repeatheadings)) {
            $this->repeatheadings    = intval($this->getQuestionAttribute('repeat_headings'));
            $this->minrepeatheadings = 0;
        }

        if ($this->getQuestionAttribute('use_dropdown') == 1) {
            $this->bUseDropdownLayout = true;
            $this->sCoreClass .= " dropdown-array";
        } else {
            $this->bUseDropdownLayout = false;
            $this->sCoreClass .= " radio-array";
        }

        $this->setSubquestions();
        $this->setAnsweroptions();

        $iCount = array_reduce($this->aSubQuestions[0], function ($combined, $oSubQuestions) {
            if (preg_match("/^[^|]+\|[^|]+$/", (string) $oSubQuestions->questionl10ns[$this->sLanguage]->question)) {
                $combined++;
            }
            return $combined;
        }, 0);
        // $right_exists is a flag to find out if there are any right hand answer parts.
        // If there arent we can leave out the right td column
        $this->rightExists = ($iCount > 0);

        if (ctype_digit(trim((string) $this->getQuestionAttribute('answer_width')))) {
            $this->answerwidth  = trim((string) $this->getQuestionAttribute('answer_width'));
            $this->defaultWidth = false;
        } else {
            $this->answerwidth = 33;
            $this->defaultWidth = true;
        }

        $this->columnswidth = 100 - $this->answerwidth;

        if ($this->rightExists) {
        /* put the right answer to same width : take place in answer width only if it's not default */
            if ($this->defaultWidth) {
                $this->columnswidth -= $this->answerwidth;
            } else {
                $this->answerwidth = $this->answerwidth / 2;
            }
            // Add a class so we can style the left side text differently when there is a right side text
            $this->sCoreClass .= " semantic-differential-list";
        }
        if ($this->getQuestionCount() > 0 && $this->getAnswerCount() > 0) {
            $this->cellwidth = round(($this->columnswidth / $this->getAnswerCount()), 1);
        }
        /* set the default header */
        $this->setHeaders();
        /* set the repeat header */
        $this->setHeaders(true);
    }

    public function getMainView($forTwig = false)
    {
        return $this->bUseDropdownLayout
            ? '/survey/questions/answer/arrays/array/dropdown'
            : '/survey/questions/answer/arrays/array/no_dropdown';
    }

    /**
     * set the header
     * @var null|boolean isrepeat
     * @return void
     */
    public function setHeaders($isrepeat = false)
    {
        $sHeader = '';
        if ($this->bUseDropdownLayout) {
            $this->sHeaders =  $sHeader;
            return;
        }

        $sHeader  .= Yii::app()->twigRenderer->renderQuestion(
            $this->getMainView() . '/rows/cells/header_information',
            [
                'class'   => '',
                'content' => '',
                'type' => 'subquestion-header',
                'isrepeat' => $isrepeat,
            ]
        );

        foreach ($this->aAnswerOptions[0] as $oAnswer) {
            $sHeader  .= Yii::app()->twigRenderer->renderQuestion(
                $this->getMainView() . '/rows/cells/header_answer',
                [
                    'class'   => "answer-text",
                    'basename' => $this->sSGQA,
                    'content' => $oAnswer->answerl10ns[$this->sLanguage]->answer,
                    'code' => $oAnswer->code,
                    'isrepeat' => $isrepeat,
                    'oAnswer' => $oAnswer
                ]
            );
        }

        if ($this->rightExists) {
            $sHeader  .= Yii::app()->twigRenderer->renderQuestion(
                $this->getMainView() . '/rows/cells/header_information',
                [
                    'class'   => '',
                    'content' => '',
                    'type' => 'right-header',
                    'isrepeat' => $isrepeat,
                    'role' => null
                ]
            );
        }

        if (($this->oQuestion->mandatory != 'Y' && SHOW_NO_ANSWER == 1)) {
            //Question is not mandatory and we can show "no answer"
            $sHeader  .= Yii::app()->twigRenderer->renderQuestion(
                $this->getMainView() . '/rows/cells/header_answer',
                [
                    'class'   => 'answer-text noanswer-text',
                    'basename' => $this->sSGQA,
                    'content' => gT('No answer'),
                    'isrepeat' => $isrepeat,
                    'code' => '',
                    'oAnswer' => null
                ]
            );
        }
        if ($isrepeat) {
            $this->sRepeatHeaders =  $sHeader;
        } else {
            $this->sHeaders =  $sHeader;
        }
    }

    public function getDropdownRows()
    {
        // $labels[] = array(
        //     'code'   => $aAnswer->code,
        //     'answer' => $aAnswer->answerl10ns[$sSurveyLanguage]->answer
        // );

        //$aAnswer->answerl10ns[$sSurveyLanguage]->answer
        $aRows = [];
        foreach ($this->aSubQuestions[0] as $i => $oQuestion) {
            $myfname        = $this->sSGQA . $oQuestion->title;
            $answertext     = $oQuestion->questionl10ns[$this->sLanguage]['question'];
            // Check the mandatory sub Q violation
            $error = (in_array($myfname, $this->aMandatoryViolationSubQ));
            $value = $this->getFromSurveySession($myfname);

            if ($this->rightExists && (strpos((string) $oQuestion->questionl10ns[$this->sLanguage]['question'], '|') !== false)) {
                $aAnswertextArray = explode('|', (string) $oQuestion->questionl10ns[$this->sLanguage]['question']);
                $answertextright = $aAnswertextArray[1];
                $answertext = $aAnswertextArray[0];
            } else {
                $answertextright = null;
            }

            $options = [];

            // Dropdown representation : first choice (activated) must be Please choose... if there are no actual answer
            $showNoAnswer = ($this->oQuestion->mandatory != 'Y' && SHOW_NO_ANSWER == 1); // Tag if we must show no-answer
            if ($value === '') {
                $options[] = array(
                    'text' => gT('Please choose...'),
                    'value' => '',
                    'selected' => ''
                );
                $showNoAnswer = false;
            }
            // Real options
            foreach ($this->aAnswerOptions[0] as $i => $oAnswer) {
                $options[] = array(
                    'value' => $oAnswer->code,
                    'selected' => ($value == $oAnswer->code) ? SELECTED : '',
                    'text' => $oAnswer->answerl10ns[$this->sLanguage]->answer
                );
            }
            // Add the now answer if needed
            if ($showNoAnswer) {
                $options[] = array(
                    'text' => gT('No answer'),
                    'value' => '',
                    'selected' => ($value == '') ?  SELECTED : '',
                );
            }
            unset($showNoAnswer);
            $aRows[] = array(
                'myfname'                => $myfname,
                'answertext'             => $answertext,
                'answerwidth'            => $this->answerwidth,
                'value'                  => $value,
                'error'                  => $error,
                'checkconditionFunction' => 'checkconditions',
                'right_exists'           => $this->rightExists,
                'answertextright'        => $answertextright,
                'options'                => $options,
                'odd'                    => ($i % 2), // true for odd, false for even
            );

            $this->inputnames[] = $myfname;
        }
        return $aRows;
    }

    public function getNonDropdownRows()
    {
        $aRows = [];
        foreach ($this->aSubQuestions[0] as $i => $oQuestion) {
            if (($this->repeatheadings > 0) && ($i > 0) && ($i % $this->repeatheadings == 0)) {
                if (($this->getQuestionCount() - $i + 1) >= $this->minrepeatheadings) {
                    // Close actual body and open another one
                    $aRows[] = [
                        'template' => '/survey/questions/answer/arrays/array/no_dropdown/rows/repeat_header.twig',
                        'content' => array(
                            'sHeaders' => $this->sRepeatHeaders
                        )
                    ];
                }
            }

            $myfname        = $this->sSGQA . $oQuestion->title;
            $answertext     = $oQuestion->questionl10ns[$this->sLanguage]->question;
            $answertext     = (strpos((string) $answertext, '|') !== false) ? substr((string) $answertext, 0, strpos((string) $answertext, '|')) : $answertext;

            if ($this->rightExists && strpos((string) $oQuestion->questionl10ns[$this->sLanguage]->question, '|') !== false) {
                $answertextright = substr((string) $oQuestion->questionl10ns[$this->sLanguage]->question, strpos((string) $oQuestion->questionl10ns[$this->sLanguage]->question, '|') + 1);
            } else {
                $answertextright = '';
            }

            $error          = (in_array($myfname, $this->aMandatoryViolationSubQ)); /* Check the mandatory sub Q violation */
            $value          = $this->getFromSurveySession($myfname);
            $aAnswerColumns = [];

            foreach ($this->aAnswerOptions[0] as $oAnswer) {
                $aAnswerColumns[] = array(
                    'basename' => $this->sSGQA,
                    'myfname' => $myfname,
                    'ld' => $oAnswer->code,
                    'code' => $oAnswer->code,
                    'label' => $oAnswer->answerl10ns[$this->sLanguage]->answer,
                    'checked' => ($value == $oAnswer->code) ? 'checked' : '',
                    );
            }

            $aNoAnswerColumn = [];
            if (($this->oQuestion->mandatory != 'Y' && SHOW_NO_ANSWER == 1)) {
                $aNoAnswerColumn = array(
                    'basename' => $this->sSGQA,
                    'myfname'                => $myfname,
                    'ld'                     => '',
                    'code' => $oAnswer->code,
                    'label'                  => gT('No answer'),
                    'checked'                => (is_null($value) || $value === '') ? 'checked' : '',
                );
            }

            $aRows[] = [
                "template" => "survey/questions/answer/arrays/array/no_dropdown/rows/answer_row.twig",
                "content" => array(
                    'aAnswerColumns' => $aAnswerColumns,
                    'aNoAnswerColumn' => $aNoAnswerColumn,
                    'sSGQA'    => $this->sSGQA,
                    'myfname'    => $myfname,
                    'answertext' => $answertext,
                    'answerwidth' => $this->answerwidth,
                    'answertextright' => $answertextright,
                    'right_exists' => intval($this->rightExists),
                    'value'      => $value,
                    'error'      => $error,
                    'odd'        => ($i % 2), // true for odd, false for even
                )
                ];

            $this->inputnames[] = $myfname;
        }

        return $aRows;
    }

    public function getColumns()
    {
        $aColumns = [];
        $oddEven = false;
        foreach ($this->aAnswerOptions[0] as $oAnswer) {
            $aColumns[] = array(
                'class'     => $oddEven ? 'ls-col-even' : 'ls-col-odd',
                'cellwidth' => $this->cellwidth,
            );
                $oddEven = !$oddEven;
        }

        if ($this->rightExists) {
            $aColumns[] = array(
                'class'     => 'answertextright ' . ($oddEven ? 'ls-col-even' : 'ls-col-odd'),
                'cellwidth' => $this->answerwidth,
            );
            $oddEven = !$oddEven;
        }

        if (($this->oQuestion->mandatory != 'Y' && SHOW_NO_ANSWER == 1)) {
            //Question is not mandatory
            $aColumns[] = array(
                'class'     => 'col-no-answer ' . ($oddEven ? 'ls-col-even' : 'ls-col-odd'),
                'cellwidth' => $this->cellwidth,
            );
            $oddEven = !$oddEven;
        }
        return $aColumns;
    }


    public function getRows()
    {
        //return;
        return $this->bUseDropdownLayout
            ? $this->getDropdownRows()
            : $this->getNonDropdownRows();
    }

    public function render($sCoreClasses = '')
    {

        //return @do_array($this->aFieldArray);
        $answer = '';
        $answer .=  Yii::app()->twigRenderer->renderQuestion($this->getMainView() . '/answer', array(
            'anscount'   => $this->getQuestionCount(),
            'aRows'      => $this->getRows(),
            'aColumns'   => $this->getColumns(),
            'basename'   => $this->sSGQA,
            'answerwidth' => $this->answerwidth,
            'columnswidth' => $this->columnswidth,
            'right_exists' => $this->rightExists,
            'coreClass'  => $this->sCoreClass,
            'sHeaders'   => $this->sHeaders,
            ), true);

        $this->registerAssets();
        return array($answer, $this->inputnames);
    }

    protected function getAnswerCount($iScaleId = 0)
    {
        // Getting answerrcount
        $anscount  = count($this->aAnswerOptions[0]);
        $anscount  = ($this->oQuestion->other == 'Y') ? $anscount + 1 : $anscount; //COUNT OTHER AS AN ANSWER FOR MANDATORY CHECKING!
        $anscount  = ($this->oQuestion->mandatory != 'Y' && SHOW_NO_ANSWER == 1) ? $anscount + 1 : $anscount; //Count up if "No answer" is showing
        return $anscount;
    }
}
