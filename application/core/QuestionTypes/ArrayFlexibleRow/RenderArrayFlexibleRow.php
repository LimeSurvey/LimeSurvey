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
    
    private $rightExists;
    private $bUseDropdownLayout = false;

    private $sCoreClass = "ls-answers subquestion-list questions-list";

    public function __construct($aFieldArray, $bRenderDirect = false) {
        parent::__construct($aFieldArray, $bRenderDirect);

        $aLastMoveResult         = LimeExpressionManager::GetLastMoveResult();
        $this->aMandatoryViolationSubQ = ($aLastMoveResult['mandViolation'] && $ia[6] == 'Y') ? explode("|", $aLastMoveResult['unansweredSQs']) : [];
        
        $this->repeatheadings    = Yii::app()->getConfig("repeatheadings");
        $this->minrepeatheadings = Yii::app()->getConfig("minrepeatheadings");

        if (ctype_digit($this->repeatheadings) && !empty($this->repeatheadings)) {
            $this->repeatheadings    = intval($aQuestionAttributes['repeat_headings']);
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
        
        $this->setSubquestions(0);
        $this->setAnsweroptions(0);

        $iCount = array_reduce($this->aSubQuestions[0], function($combined, $oSubquestion){
            if(preg_match("/[^|]*|[^|]*/",$oQuestion->questionL10ns[$this->sLanguage]->question)) { $combined++; } 
            return $combined;
        }, 0);
        // $right_exists is a flag to find out if there are any right hand answer parts. 
        // If there arent we can leave out the right td column
        $this->rightExists = ($iCount > 0);

    }

    public function getMainView()
    {
        return $this->bUseDropdownLayout
            ? '/survey/questions/answer/arrays/array/dropdown'
            : '/survey/questions/answer/arrays/array/no_dropdown';
    }

    public function renderDropdownRows()
    {
        
        $answerwidth = $this->setDefaultIfEmpty($this->getQuestionAttribute('answer_width'), 33);
        $defaultWidth = ($answerwidth===33);
        
        $columnswidth = 100 - $answerwidth;

        if($this->rightExists) {
        /* put the right answer to same width : take place in answer width only if it's not default */
            if ($defaultWidth) {
                $columnswidth -= $answerwidth;
            } else {
                $answerwidth = $answerwidth / 2;
            }
        }

        $cellwidth = round(($columnswidth / $this->getQuestionCount()), 1);

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

            $myfname        = $ia[1].$ansrow['title'];
            $answertext     = $ansrow->questionL10ns[$sSurveyLanguage]->question;
            $answertext     = (strpos($answertext, '|') !== false) ? substr($answertext, 0, strpos($answertext, '|')) : $answertext;

            if ($right_exists && strpos($ansrow->questionL10ns[$sSurveyLanguage]->question, '|') !== false) {
                $answertextright = substr($ansrow->questionL10ns[$sSurveyLanguage]->question, strpos($ansrow->questionL10ns[$sSurveyLanguage]->question, '|') + 1);
            } else {
                $answertextright = '';
            }

            $error          = (in_array($myfname, $aMandatoryViolationSubQ)) ?true:false; /* Check the mandatory sub Q violation */
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
                    'checkconditionFunction'=>$checkconditionFunction,
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
            'basename' => $ia[1],
            ), true);
    }


    public function getRows()
    {
        return;
    }

    public function render($sCoreClasses = '')
    {

        return do_array($this->aFieldArray);

        $answer = '';
        $inputnames = [];

        if (!empty($this->getQuestionAttribute('time_limit', 'value'))) {
            $answer .= $this->getTimeSettingRender();
        }

        $answer .=  Yii::app()->twigRenderer->renderQuestion($this->getMainView(), array(
            'ia'=>$this->aFieldArray,
            'name'=>$this->sSGQA,
            'basename'=>$this->sSGQA, 
            'content' => $this->oQuestion,
            'coreClass'=> 'ls-answers '.$sCoreClasses,
            ), true);

        $inputnames[] = [];
        return array($answer, $inputnames);
    }

    
    protected function getAnswerCount($iScaleId=0)
    {
        // Getting answerrcount
        $anscount  = count($this->aAnswerOptions[0]);
        $anscount  = ($this->oQuestion->other == 'Y') ? $anscount + 1 : $anscount; //COUNT OTHER AS AN ANSWER FOR MANDATORY CHECKING!
        $anscount  = ($this->oQuestion->mandatory != 'Y' && SHOW_NO_ANSWER == 1) ? $anscount + 1 : $anscount; //Count up if "No answer" is showing
        return $anscount;
    }
    
    protected function getQuestionCount($iScaleId=0)
    {
        // Getting subquestion count
        $sqcount  = count($this->aSubQuestions[$iScaleId]);
        $sqcount  = ($this->oQuestion->other == 'Y') ? $sqcount + 1 : $sqcount; //COUNT OTHER AS AN ANSWER FOR MANDATORY CHECKING!
        $sqcount  = ($this->oQuestion->mandatory != 'Y' && SHOW_NO_ANSWER == 1) ? $sqcount + 1 : $sqcount; //Count up if "No answer" is showing
        $sqcount  = ($this->rightExists) ? $sqcount + 1 : $sqcount;
        return $sqcount;
    }
}

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
            /* put the right answer to same width : take place in answer width only if it's not default */
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

            $myfname        = $ia[1].$ansrow['title'];
            $answertext     = $ansrow->questionL10ns[$sSurveyLanguage]->question;
            $answertext     = (strpos($answertext, '|') !== false) ? substr($answertext, 0, strpos($answertext, '|')) : $answertext;

            if ($right_exists && strpos($ansrow->questionL10ns[$sSurveyLanguage]->question, '|') !== false) {
                $answertextright = substr($ansrow->questionL10ns[$sSurveyLanguage]->question, strpos($ansrow->questionL10ns[$sSurveyLanguage]->question, '|') + 1);
            } else {
                $answertextright = '';
            }

            $error          = (in_array($myfname, $aMandatoryViolationSubQ)) ?true:false; /* Check the mandatory sub Q violation */
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
                    'checkconditionFunction'=>$checkconditionFunction,
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
            'basename' => $ia[1],
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
            /* put the right answer to same width : take place in answer width only if it's not default */
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
            $myfname        = $ia[1].$ansrow['title'];
            $answertext     = $ansrow->questionL10ns[$sSurveyLanguage]['question'];
            $answertext     = (strpos($answertext, '|') !== false) ? substr($answertext, 0, strpos($answertext, '|')) : $answertext;
            $error          = (in_array($myfname, $aMandatoryViolationSubQ)) ?true:false; /* Check the mandatory sub Q violation */
            $value          = (isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname])) ? $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname] : '';

            if ($right_exists && (strpos($ansrow->questionL10ns[$sSurveyLanguage]['question'], '|') !== false)) {
                $answertextright = substr($ansrow->questionL10ns[$sSurveyLanguage]['question'], strpos($ansrow['question'], '|') + 1);
            } else {
                $answertextright = null;
            }

            $options = [];

            /* Dropdown representation : first choice (activated) must be Please choose... if there are no actual answer */
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
            /* Add the now answer if needed */
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
                'basename' => $ia[1],
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