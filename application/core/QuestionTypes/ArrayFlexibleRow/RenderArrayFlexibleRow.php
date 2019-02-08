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
    private $sHeaders;
    
    private $rightExists;
    private $bUseDropdownLayout = false;

    private $inputnames = [];

    public $sCoreClass = "ls-answers subquestion-list questions-list";

    public function __construct($aFieldArray, $bRenderDirect = false) {
        parent::__construct($aFieldArray, $bRenderDirect);

        $aLastMoveResult         = LimeExpressionManager::GetLastMoveResult();
        $this->aMandatoryViolationSubQ = ($aLastMoveResult['mandViolation'] && $ia[6] == 'Y') ? explode("|", $aLastMoveResult['unansweredSQs']) : [];
        
        $this->repeatheadings    = Yii::app()->getConfig("repeatheadings");
        $this->minrepeatheadings = Yii::app()->getConfig("minrepeatheadings");

        if (ctype_digit($this->repeatheadings) && !empty($this->repeatheadings)) {
            $this->repeatheadings    = intval($this->getQuestionAttribute('repeat_headings'));
            $this->minrepeatheadings = 0;
        }
    
    
        if ($this->getQuestionAttribute('use_dropdown') == 1) {
            $this->bUseDropdownLayout = true;
            $this->sCoreClass .= " dropdown-array";
            // I suppose this is irrelevant and if not, why t** f*** is there hardcoded text in the renderer function?
            //$caption           = gT("A table with a subquestion on each row. You have to select your answer.");
        } else {
            $this->bUseDropdownLayout = false;
            $this->sCoreClass .= " radio-array";
            // I suppose this is irrelevant and if not, why t** f*** is there hardcoded text in the renderer function?
            //$caption           = gT("A table with a subquestion on each row. The answer options are contained in the table header.");
        }
        
        $this->setSubquestions();
        $this->setAnsweroptions();

        $iCount = array_reduce($this->aSubQuestions[0], function($combined, $oSubquestion){
            if(preg_match("/^[^|]+\|[^|]+$/",$this->oQuestion->questionL10ns[$this->sLanguage]->question)) { 
                $combined++; 
            } 
            return $combined;
        }, 0);
        // $right_exists is a flag to find out if there are any right hand answer parts. 
        // If there arent we can leave out the right td column
        $this->rightExists = ($iCount > 0);

        $this->answerwidth = $this->setDefaultIfEmpty($this->getQuestionAttribute('answer_width'), 33);
        $this->defaultWidth = ($this->answerwidth===33);
        
        $this->columnswidth = 100 - $this->answerwidth;

        if($this->rightExists) {
        /* put the right answer to same width : take place in answer width only if it's not default */
            if ($this->defaultWidth) {
                $this->columnswidth -= $this->answerwidth;
            } else {
                $this->answerwidth = $this->answerwidth / 2;
            }
        }

        $this->cellwidth = round(($this->columnswidth / $this->getQuestionCount()), 1);
        $this->setHeaders();

    }

    public function getMainView($forTwig = false)
    {
        return $this->bUseDropdownLayout
            ? '/survey/questions/answer/arrays/array/dropdown'
            : '/survey/questions/answer/arrays/array/no_dropdown';
    }

    public function setHeaders(){
        $sHeader = '';
        if($this->bUseDropdownLayout) {
            $this->sHeaders =  $sHeader;
            return;
        }

        $sHeader  .= Yii::app()->twigRenderer->renderQuestion(
            $this->getMainView().'/rows/cells/header_information', 
            [
                'class'   => '',
                'content' => '',
            ]
        );

        foreach ($this->aAnswerOptions[0] as $oAnswer) {
            $sHeader  .= Yii::app()->twigRenderer->renderQuestion(
                $this->getMainView().'/rows/cells/header_answer', 
                [
                    'class'   => "answer-text",
                    'content' => $oAnswer->answerL10ns[$this->sLanguage]->answer,
                ]
            );
        }

        if ($this->rightExists) {
            $sHeader  .= Yii::app()->twigRenderer->renderQuestion(
                $this->getMainView().'/rows/cells/header_information', 
                [
                    'class'   => '',
                    'content' => '',
                ]
            );
        }

        if (($this->oQuestion->mandatory != 'Y' && SHOW_NO_ANSWER == 1)) {
            //Question is not mandatory and we can show "no answer"
            $sHeader  .= Yii::app()->twigRenderer->renderQuestion(
                $this->getMainView().'/rows/cells/header_answer', 
                [
                    'class'   => 'answer-text noanswer-text',
                    'content' => gT('No answer'),
                ]
            );
        }

        $this->sHeaders =  $sHeader;
    }

    public function getDropdownRows() {
        
        // $labels[] = array(
        //     'code'   => $aAnswer->code,
        //     'answer' => $aAnswer->answerL10ns[$sSurveyLanguage]->answer
        // );

        //$aAnswer->answerL10ns[$sSurveyLanguage]->answer
        $aRows = [];
        foreach ($this->aSubQuestions[0] as $i => $oQuestion) {
            $myfname        = $this->sSGQA.$oQuestion->title;
            $answertext     = $oQuestion->questionL10ns[$this->sLanguage]['question'];
            // Check the mandatory sub Q violation 
            $error = (in_array($myfname, $this->aMandatoryViolationSubQ)); 
            $value = $this->getFromSurveySession($myfname);

            if ($this->rightExists && (strpos($oQuestion->questionL10ns[$sSurveyLanguage]['question'], '|') !== false)) {
                $aAnswertextArray = explode('|', $oQuestion->questionL10ns[$sSurveyLanguage]['question']);
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
                    'text'=> gT('Please choose...'),
                    'value'=> '',
                    'selected'=>''
                );
                $showNoAnswer = false;
            }
            // Real options
            foreach ($this->aAnswerOptions[0] as $i=>$oAnswer) {
                $options[] = array(
                    'value'=>$oAnswer->code,
                    'selected'=>($value == $oAnswer->code) ? SELECTED :'',
                    'text'=> $oAnswer->answerL10ns[$this->sLanguage]->answer
                );
            }
            // Add the now answer if needed 
            if ($showNoAnswer) {
                $options[] = array(
                    'text'=> gT('No answer'),
                    'value'=> '',
                    'selected'=> ($value == '') ?  SELECTED :'',
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
            if ($this->repeatheadings > 0 && ($i - 1) > 0 && ($i - 1) % $this->repeatheadings == 0) {
                if (($this->getQuestionCount() - $i + 1) >= $this->minrepeatheadings) {
                    // Close actual body and open another one
                    $aRows[] = [
                        'template' => '/survey/questions/answer/arrays/array/no_dropdown/rows/repeat_header.twig', 
                        'content' => array(
                            'sHeaders' => $this->sHeaders
                        )
                    ];
                }
            }

            $myfname        = $this->sSGQA.$oQuestion->title;
            $answertext     = $oQuestion->questionL10ns[$this->sLanguage]->question;
            $answertext     = (strpos($answertext, '|') !== false) ? substr($answertext, 0, strpos($answertext, '|')) : $answertext;

            if ($this->rightExists && strpos($oQuestion->questionL10ns[$this->sLanguage]->question, '|') !== false) {
                $answertextright = substr($oQuestion->questionL10ns[$this->sLanguage]->question, strpos($oQuestion->questionL10ns[$this->sLanguage]->question, '|') + 1);
            } else {
                $answertextright = '';
            }

            $error          = (in_array($myfname, $this->aMandatoryViolationSubQ)); /* Check the mandatory sub Q violation */
            $value          = $this->getFromSurveySession($myfname);
            $aAnswerColumns = [];

            foreach ($this->aAnswerOptions[0] as $oAnswer) {
                $aAnswerColumns[] = array(
                    'myfname'=>$myfname,
                    'ld'=>$oAnswer->code,
                    'label'=>$oAnswer->answerL10ns[$this->sLanguage]->answer,
                    'CHECKED'=>($this->getFromSurveySession($myfname) == $oAnswer->code) ? 'CHECKED' : '',
                    'checkconditionFunction'=>'checkconditions',
                    );
            }

            // NB: $ia[6] = mandatory
            $aNoAnswerColumn = [];
            if (($this->oQuestion->mandatory != 'Y' && SHOW_NO_ANSWER == 1)) {
                $CHECKED = (!isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname]) || $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname] == '') ? 'CHECKED' : '';
                $aNoAnswerColumn = array(
                    'myfname'                => $myfname,
                    'ld'                     => '',
                    'label'                  => gT('No answer'),
                    'CHECKED'                => $CHECKED,
                    'checkconditionFunction' => 'checkconditions',
                );
            }

            $aRows[] = [
                "template" => "survey/questions/answer/arrays/array/no_dropdown/rows/answer_row.twig",
                "content" => array(
                    'aAnswerColumns' => $aAnswerColumns,
                    'aNoAnswerColumn' => $aNoAnswerColumn,
                    'myfname'    => $myfname,
                    'answertext' => $answertext,
                    'answerwidth'=> $this->answerwidth,
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
                'class'     => 'answertextright '.($oddEven ? 'ls-col-even' : 'ls-col-odd'),
                'cellwidth' => $this->answerwidth,
            );
            $oddEven = !$oddEven;
        }

        if (($this->oQuestion->mandatory != 'Y' && SHOW_NO_ANSWER == 1)) {
            //Question is not mandatory
            $aColumns[] = array(
                'class'     => 'col-no-answer '.($oddEven ? 'ls-col-even' : 'ls-col-odd'),
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

        $answer .=  Yii::app()->twigRenderer->renderQuestion($this->getMainView().'/answer', array(
            'anscount'   => $this->getQuestionCount(),
            'aRows'      => $this->getRows(),
            'aColumns'   => $this->getColumns(),
            'basename'   => $this->sSGQA,
            'answerwidth'=> $this->answerwidth,
            'columnswidth'=> $this->columnswidth,
            'right_exists'=> $this->rightExists,
            'coreClass'  => $this->sCoreClass,
            'sHeaders'   => $this->sHeaders,
            ), true);

        
        return array($answer, $this->inputnames);
    }

    
    protected function getAnswerCount($iScaleId=0)
    {
        // Getting answerrcount
        $anscount  = count($this->aAnswerOptions[0]);
        $anscount  = ($this->oQuestion->other == 'Y') ? $anscount + 1 : $anscount; //COUNT OTHER AS AN ANSWER FOR MANDATORY CHECKING!
        $anscount  = ($this->oQuestion->mandatory != 'Y' && SHOW_NO_ANSWER == 1) ? $anscount + 1 : $anscount; //Count up if "No answer" is showing
        return $anscount;
    }
  
}
/*
function do_array($ia)
{
    $aLastMoveResult         = LimeExpressionManager::GetLastMoveResult();
    $aMandatoryViolationSubQ = ($aLastMoveResult['mandViolation'] && $ia[6] == 'Y') ? explode("|", $aLastMoveResult['unansweredSQs']) : [];
    
    $repeatheadings          = Yii::app()->getConfig("repeatheadings");
    $minrepeatheadings       = Yii::app()->getConfig("minrepeatheadings");

    $coreClass = "ls-answers subquestion-list questions-list";

    $checkconditionFunction  = "checkconditions";


    if ($aQuestionAttributes['use_dropdown'] == 1) {
        $useDropdownLayout = true;
        $coreClass .= " dropdown-array";
        $caption           = gT("A table with a subquestion on each row. You have to select your answer.");
    } else {
        $useDropdownLayout = false;
        $coreClass .= " radio-array";
        $caption           = gT("A table with a subquestion on each row. The answer options are contained in the table header.");
    }

    if (ctype_digit(trim($aQuestionAttributes['repeat_headings'])) && trim($aQuestionAttributes['repeat_headings'] != "")) {
        $repeatheadings    = intval($aQuestionAttributes['repeat_headings']);
        $minrepeatheadings = 0;
    }

    $aAnswers = Answer::model()->findAll(array('order'=>'sortorder, code', 'condition'=>'qid=:qid AND scale_id=0', 'params'=>array(':qid'=>$ia[0])));
    $labelans = [];
    $labelcode = [];

    foreach ($aAnswers as $aAnswer) {
        $labelans[]  = $aAnswer->answerL10ns[$sSurveyLanguage]->answer;
        $labelcode[] = $aAnswer->code;
    }

    // No-dropdown layout
    if ($useDropdownLayout === false && count($aAnswers) > 0) {
        if (trim($aQuestionAttributes['answer_width']) != '') {
            $answerwidth = trim($aQuestionAttributes['answer_width']);
            $defaultWidth = false;
        } else {
            $answerwidth = 33;
            $defaultWidth = true;
        }
        $columnswidth = 100 - $answerwidth;
        $iCount = (int) Question::model()->with(array('questionL10ns'=>array('condition'=>"question like :separator")))->count('parent_qid=:parent_qid AND scale_id=0', array(':parent_qid'=>$ia[0], ":separator"=>'%|%'));
        // $right_exists is a flag to find out if there are any right hand answer parts. If there arent we can leave out the right td column
        if ($iCount > 0) {
            $right_exists = true;
            /* put the right answer to same width : take place in answer width only if it's not default 
            if ($defaultWidth) {
                $columnswidth -= $answerwidth;
            } else {
                $answerwidth = $answerwidth / 2;
            }
        } else {
            $right_exists = false;
        }

        // Get questions and answers by defined order
        if ($aQuestionAttributes['random_order'] == 1) {
            $sOrder = dbRandom();
        } else {
            $sOrder = 'question_order';
        }
        $aQuestions = Question::model()->findAll(array('order'=>$sOrder, 'condition'=>'parent_qid=:parent_qid', 'params'=>array(':parent_qid'=>$ia[0])));
        $iQuestionCount = count($aQuestions);
        $fn         = 1;
        $numrows    = count($labelans);

        if ($right_exists) {
            ++$numrows;
            $caption .= gT("After the answer options a cell does give some information.");
        }
        if ($ia[6] != 'Y' && SHOW_NO_ANSWER == 1) {
            ++$numrows;
        }

        $cellwidth = round(($columnswidth / $numrows), 1);

        $sHeaders = doRender('/survey/questions/answer/arrays/array/no_dropdown/rows/cells/header_information', array(
            'class'   => '',
            'content' => '',
            ), true);

        foreach ($labelans as $ld) {
            $sHeaders .= doRender('/survey/questions/answer/arrays/array/no_dropdown/rows/cells/header_answer', array(
                'class'   => "answer-text",
                'content' => $ld,
                ), true);
        }

        if ($right_exists) {
            $sHeaders .= doRender('/survey/questions/answer/arrays/array/no_dropdown/rows/cells/header_information', array(
                'class'     => '',
                'content'   => '',
                ), true);
        }

        if ($ia[6] != 'Y' && SHOW_NO_ANSWER == 1) {
            //Question is not mandatory and we can show "no answer"
            $sHeaders .= doRender('/survey/questions/answer/arrays/array/no_dropdown/rows/cells/header_answer', array(
                'class'   => 'answer-text noanswer-text',
                'content' => gT('No answer'),
                ), true);
        }

        $inputnames = [];

        $sRows = '';
        foreach ($aQuestions as $i => $ansrow) {
            if (isset($repeatheadings) && $repeatheadings > 0 && ($fn - 1) > 0 && ($fn - 1) % $repeatheadings == 0) {
                if (($iQuestionCount - $fn + 1) >= $minrepeatheadings) {
                    // Close actual body and open another one
                    $sRows .= doRender('/survey/questions/answer/arrays/array/no_dropdown/rows/repeat_header', array(
                        'sHeaders'=>$sHeaders
                        ), true);
                }
            }

            $myfname        = $this->sSGQA.$ansrow['title'];
            $answertext     = $ansrow->questionL10ns[$sSurveyLanguage]->question;
            $answertext     = (strpos($answertext, '|') !== false) ? substr($answertext, 0, strpos($answertext, '|')) : $answertext;

            if ($right_exists && strpos($ansrow->questionL10ns[$sSurveyLanguage]->question, '|') !== false) {
                $answertextright = substr($ansrow->questionL10ns[$sSurveyLanguage]->question, strpos($ansrow->questionL10ns[$sSurveyLanguage]->question, '|') + 1);
            } else {
                $answertextright = '';
            }

            $error          = (in_array($myfname, $aMandatoryViolationSubQ)) ?true:false; /* Check the mandatory sub Q violation 
            $value          = (isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname])) ? $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname] : '';
            $thiskey        = 0;
            $answer_tds     = '';
            $fn++;

            foreach ($labelcode as $ld) {
                $CHECKED     = (isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname]) && $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname] == $ld) ? 'CHECKED' : '';
                $answer_tds .= doRender('/survey/questions/answer/arrays/array/no_dropdown/rows/cells/answer_td', array(
                    'myfname'=>$myfname,
                    'ld'=>$ld,
                    'label'=>$labelans[$thiskey],
                    'CHECKED'=>$CHECKED,
                    'checkconditionFunction'=>'checkconditions',
                    ), true);
                $thiskey++;
            }

            // NB: $ia[6] = mandatory
            $no_answer_td = '';
            if ($ia[6] != 'Y' && SHOW_NO_ANSWER == 1) {
                $CHECKED = (!isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname]) || $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname] == '') ? 'CHECKED' : '';
                $no_answer_td .= doRender('/survey/questions/answer/arrays/array/no_dropdown/rows/cells/answer_td', array(
                    'myfname'                => $myfname,
                    'ld'                     => '',
                    'label'                  => gT('No answer'),
                    'CHECKED'                => $CHECKED,
                    'checkconditionFunction' => $checkconditionFunction,
                    ), true);
            }
            $sRows .= doRender('/survey/questions/answer/arrays/array/no_dropdown/rows/answer_row', array(
                'answer_tds' => $answer_tds,
                'no_answer_td' => $no_answer_td,
                'myfname'    => $myfname,
                'answertext' => $answertext,
                'answerwidth'=>$answerwidth,
                'answertextright' => $answertextright,
                'right_exists' => $right_exists,
                'value'      => $value,
                'error'      => $error,
                'odd'        => ($i % 2), // true for odd, false for even
                ), true);
            $inputnames[] = $myfname;
        }


        $odd_even = '';
        $sColumns = '';
        foreach ($labelans as $c) {
            $odd_even = alternation($odd_even);
            $sColumns .= doRender('/survey/questions/answer/arrays/array/no_dropdown/columns/col', array(
                'class'     => $odd_even,
                'cellwidth' => $cellwidth,
                ), true);
        }

        if ($right_exists) {
            $odd_even = alternation($odd_even);
            $sColumns .= doRender('/survey/questions/answer/arrays/array/no_dropdown/columns/col', array(
                'class'     => 'answertextright '.$odd_even,
                'cellwidth' => $answerwidth,
                ), true);
        }

        if ($ia[6] != 'Y' && SHOW_NO_ANSWER == 1) {
            //Question is not mandatory
            $odd_even = alternation($odd_even);
            $sColumns .= doRender('/survey/questions/answer/arrays/array/no_dropdown/columns/col', array(
                'class'     => 'col-no-answer '.$odd_even,
                'cellwidth' => $cellwidth,
                ), true);
        }

        $answer = doRender('/survey/questions/answer/arrays/array/no_dropdown/answer', array(
            'answerwidth'=> $answerwidth,
            'anscount'   => $iQuestionCount,
            'sRows'      => $sRows,
            'coreClass'  => $coreClass,
            'sHeaders'   => $sHeaders,
            'sColumns'   => $sColumns,
            'basename' => $this->sSGQA,
            ), true);
    }

    // Dropdown layout
    elseif ($useDropdownLayout === true && count($aAnswers) > 0) {
        if (trim($aQuestionAttributes['answer_width']) != '') {
            $answerwidth = trim($aQuestionAttributes['answer_width']);
            $defaultWidth = false;
        } else {
            $answerwidth = 33;
            $defaultWidth = true;
        }
        $columnswidth = 100 - $answerwidth;
        $labels = [];
        foreach ($aAnswers as $aAnswer) {
            $labels[] = array(
                'code'   => $aAnswer->code,
                'answer' => $aAnswer->answerL10ns[$sSurveyLanguage]->answer
            );
        }

        $sQuery = "SELECT count(qid) FROM {{questions}} WHERE parent_qid={$ia[0]} AND question like '%|%' ";
        $iCount = Yii::app()->db->createCommand($sQuery)->queryScalar();

        if ($iCount > 0) {
            $right_exists = true;
            /* put the right answer to same width : take place in answer width only if it's not default 
            if ($defaultWidth) {
                $columnswidth -= $answerwidth;
            } else {
                $answerwidth = $answerwidth / 2;
            }
        } else {
            $right_exists = false;
        }
        // $right_exists is a flag to find out if there are any right hand answer parts. If there arent we can leave out the right td column

        if ($aQuestionAttributes['random_order'] == 1) {
            $sOrder = dbRandom();
        } else {
            $sOrder = 'question_order';
        }
        $aQuestions = Question::model()->findAll(array('order'=>$sOrder, 'condition'=>'parent_qid=:parent_qid', 'params'=>array(':parent_qid'=>$ia[0])));

        $fn         = 1;
        $inputnames = [];
        //$aAnswer->answerL10ns[$sSurveyLanguage]->answer
        $sRows = "";
        foreach ($aQuestions as $j => $ansrow) {
            $myfname        = $this->sSGQA.$ansrow['title'];
            $answertext     = $ansrow->questionL10ns[$sSurveyLanguage]['question'];
            $answertext     = (strpos($answertext, '|') !== false) ? substr($answertext, 0, strpos($answertext, '|')) : $answertext;
            $error          = (in_array($myfname, $aMandatoryViolationSubQ)) ?true:false; /* Check the mandatory sub Q violation 
            $value          = (isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname])) ? $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname] : '';

            if ($right_exists && (strpos($ansrow->questionL10ns[$sSurveyLanguage]['question'], '|') !== false)) {
                $answertextright = substr($ansrow->questionL10ns[$sSurveyLanguage]['question'], strpos($ansrow['question'], '|') + 1);
            } else {
                $answertextright = null;
            }

            $options = [];

            /* Dropdown representation : first choice (activated) must be Please choose... if there are no actual answer 
            $showNoAnswer = $ia[6] != 'Y' && SHOW_NO_ANSWER == 1; // Tag if we must show no-answer
            if (!isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname]) || $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname] === '') {
                $options[] = array(
                    'text'=> gT('Please choose...'),
                    'value'=> '',
                    'selected'=>''
                );
                $showNoAnswer = false;
            }
            // Real options
            foreach ($labels as $i=>$aAnswer) {
                $options[] = array(
                    'value'=>$aAnswer['code'],
                    'selected'=>($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname] == $aAnswer['code']) ? SELECTED :'',
                    'text'=> $aAnswer['answer']
                );
            }
            /* Add the now answer if needed 
            if ($showNoAnswer) {
                $options[] = array(
                    'text'=> gT('No answer'),
                    'value'=> '',
                    'selected'=> ($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname] === '') ?  SELECTED :'',
                );
            }
            unset($showNoAnswer);
            $sRows .= doRender('/survey/questions/answer/arrays/array/dropdown/rows/answer_row', array(
                'myfname'                => $myfname,
                'answertext'             => $answertext,
                'answerwidth'=>$answerwidth,
                'value'                  => $value,
                'error'                  => $error,
                'checkconditionFunction' => $checkconditionFunction,
                'right_exists'           => $right_exists,
                'answertextright'        => $answertextright,
                'options'                => $options,
                'odd'                    => ($j % 2), // true for odd, false for even
                ), true);

            $inputnames[] = $myfname;
            $fn++;
        }

        $answer = doRender('/survey/questions/answer/arrays/array/dropdown/answer', array(
                'coreClass' => $coreClass,
                'basename' => $this->sSGQA,
                'sRows'      => $sRows,
                'answerwidth'=> $answerwidth,
                'columnswidth'=> $columnswidth,
                'right_exists'=> $right_exists,
            ), true);
    } else {
        $answer = doRender('/survey/questions/answer/arrays/array/dropdown/empty', [], true);
        $inputnames = '';
    }
    return array($answer, $inputnames);
} 
*/