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
class RenderListDropdown extends QuestionBaseRenderer
{

    protected $othertext;
    protected $iNbCols;
    protected $sTimer;
    protected $iColumnWidth;

    public function __construct($aFieldArray, $bRenderDirect = false)
    {
        parent::__construct($aFieldArray, $bRenderDirect);
        $this->othertext = $this->setDefaultIfEmpty($this->aQuestionAttributes['other_replace_text'][$this->sLanguage], gT('Other:')); // text for 'other'
        $this->iNbCols   = $this->aQuestionAttributes['display_columns']; // number of columns
        $this->sTimer    = $this->setDefaultIfEmpty($this->aQuestionAttributes['time_limit'], ''); //Time Limit
        $this->setAnsweroptions(null,$this->aQuestionAttributes['alphasort']==1);
        
        if ($this->iNbCols > 1) {
            // Add a class on the wrapper
            $coreClass .= " multiple-list nbcol-{$iNbCols}";
            // First we calculate the width of each column
            // Max number of column is 12 http://getbootstrap.com/css/#grid
            $this->iColumnWidth = round(12 / $iNbCols);
            $this->iColumnWidth = ($iColumnWidth >= 1) ? $iColumnWidth : 1;
            $this->iColumnWidth = ($iColumnWidth <= 12) ? $iColumnWidth : 12;

            // Then, we calculate how many answer rows in each column
            $iMaxRowsByColumn = ceil($anscount / $iNbCols);
        } else {
            $this->iColumnWidth = 12;
            $iMaxRowsByColumn = $anscount + 3; // No max : anscount + no answer + other + 1 by security
        }

    }

    public function getMainView()
    {
        return '/survey/questions/answer/listradio';
    }

    public function getRows() {
        foreach ($this->aAnswerOptions[0] as $iterator=>$oAnswer) {
            $i++; // general count of loop, to check if the item is the last one for column process. Never reset.
            $iRowCount++; // counter of number of row by column. Is reset to zero each time a column is full.
            $myfname = $this->sSGQA.$ansrow['code'];
    
            $checkedState = '';
            if ($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$ia[1]] == $ansrow['code']) {
                $checkedState = 'CHECKED';
            }

            ////
            // Open Column
            // The column is opened if user set more than one column in question attribute
            // and if this is the first answer row, or if the column has been closed and the row count reset before.
            if ($iRowCount == 1) {
                $sRows  .= doRender('/survey/questions/answer/listradio/columns/column_header', array('iColumnWidth' => $iColumnWidth), true);
                $isOpen  = true; // If a column is not closed, it will be closed at the end of the process
            }
    
    
            ////
            // Insert row
            // Display the answer row
            $sRows .= Yii::app()->twigRenderer->renderQuestion($this->getMainView().'/rows/answer_row', array(
                'sDisplayStyle' => $sDisplayStyle,
                'name'          => $this->sSGQA,
                'code'          => $ansrow['code'],
                'answer'        => $ansrow->answerL10ns[$sSurveyLang]->answer,
                'checkedState'  => $checkedState,
                'myfname'       => $myfname,
                ), true);
    
            ////
            // Close column
            // The column is closed if the user set more than one column in question attribute
            // and if the max answer rows by column is reached.
            // If max answer rows by column is not reached while there is no more answer,
            // the column will remain opened, and it will be closed by 'other' answer row if set or at the end of the process
            if ($iRowCount == $iMaxRowsByColumn) {
                $last      = ($i == $anscount) ?true:false; // If this loop count equal to the number of answers, then this answer is the last one.
                $sRows    .= doRender('/survey/questions/answer/listradio/columns/column_footer', array('last'=>$last), true);
                $iRowCount = 0;
                $isOpen    = false;
            }
        }
    }

    public function render($sCoreClasses = '')
    {
        $answer = '';
        $coreClass = "ls-answers answers-list radio-list";
        $inputnames = [];

        if (!empty($this->aQuestionAttributes['time_limit']['value'])) {
            $answer .= $this->getTimeSettingRender();
        }

        $answer .=  Yii::app()->twigRenderer->renderQuestion($this->getMainView(), array(
            'ia'=>$this->aFieldArray,
            'name'=>$this->sSGQA,
            'basename'=>$this->sSGQA, /* is this needed ? */
            'coreClass'=> 'ls-answers hidden '.$sCoreClasses,
            ), true);

        $inputnames[] = $this->sSGQA;
        return array($answer, $inputnames);
    }


    private function getAnswerCount() {
        // Getting answerrcount
        $anscount  = count($this->aAnswerOptions[0]);
        $anscount  = ($this->oQuestion->other == 'Y') ? $anscount + 1 : $anscount; //COUNT OTHER AS AN ANSWER FOR MANDATORY CHECKING!
        $anscount  = ($this->oQuestion->mandatory != 'Y' && SHOW_NO_ANSWER == 1) ? $anscount + 1 : $anscount; //Count up if "No answer" is showing
        return $anscount;
    }
}


function do_list_radio($ia)
{

    //// Retrieving datas

    // Getting question
    $oQuestion = Question::model()->findByPk(array('qid'=>$ia[0], 'language'=>$sSurveyLang));
    $other     = $oQuestion->other;

    // Getting answers
    $ansresult = $oQuestion->getOrderedAnswers($aQuestionAttributes['random_order'], $aQuestionAttributes['alphasort']);
    $anscount  = count($ansresult);
    $anscount  = ($other == 'Y') ? $anscount + 1 : $anscount; //COUNT OTHER AS AN ANSWER FOR MANDATORY CHECKING!
    $anscount  = ($ia[6] != 'Y' && SHOW_NO_ANSWER == 1) ? $anscount + 1 : $anscount; //Count up if "No answer" is showing

    //// Columns containing answer rows, set by user in question attribute
    /// TODO : move to a dedicated function

    // setting variables
    $iRowCount        = 0;
    $isOpen           = false; // Is a column opened

    if ($iNbCols > 1) {
        // Add a class on the wrapper
        $coreClass .= " multiple-list nbcol-{$iNbCols}";
        // First we calculate the width of each column
        // Max number of column is 12 http://getbootstrap.com/css/#grid
        $iColumnWidth = round(12 / $iNbCols);
        $iColumnWidth = ($iColumnWidth >= 1) ? $iColumnWidth : 1;
        $iColumnWidth = ($iColumnWidth <= 12) ? $iColumnWidth : 12;

        // Then, we calculate how many answer rows in each column
        $iMaxRowsByColumn = ceil($anscount / $iNbCols);
    } else {
        $iColumnWidth = 12;
        $iMaxRowsByColumn = $anscount + 3; // No max : anscount + no answer + other + 1 by security
    }

    // Get array_filter stuff

    $i = 0;

    

    if (isset($other) && $other == 'Y') {
        $iRowCount++;
        $i++;
        $sSeparator = getRadixPointData($thissurvey['surveyls_numberformat']);
        $sSeparator = $sSeparator['separator'];

        if ($aQuestionAttributes['other_numbers_only'] == 1) {
            $oth_checkconditionFunction = 'fixnum_checkconditions';
        } else {
            $oth_checkconditionFunction = 'checkconditions';
        }


        if ($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$ia[1]] == '-oth-') {
            $checkedState = CHECKED;
        } else {
            $checkedState = '';
        }

        $myfname = $thisfieldname = $ia[1].'other';

        if (isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$thisfieldname])) {
            $dispVal = $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$thisfieldname];
            if ($aQuestionAttributes['other_numbers_only'] == 1) {
                $dispVal = str_replace('.', $sSeparator, $dispVal);
            }
            $answer_other = ' value="'.htmlspecialchars($dispVal, ENT_QUOTES).'"';
        } else {
            $answer_other = ' value=""';
        }

        ////
        // Open Column
        // The column is opened if user set more than one column in question attribute
        // and if this is the first answer row (should never happen for 'other'),
        // or if the column has been closed and the row count reset before.
        if ($iRowCount == 1) {
            $sRows .= doRender('/survey/questions/answer/listradio/columns/column_header', array('iColumnWidth' => $iColumnWidth, 'first'=>false), true);
        }
        $sDisplayStyle = return_display_style($ia, $aQuestionAttributes, $thissurvey, $myfname);

        ////
        // Insert row
        // Display the answer row
        $sRows .= doRender('/survey/questions/answer/listradio/rows/answer_row_other', array(
            'name' => $ia[1],
            'answer_other'=>$answer_other,
            'myfname'=>$myfname,
            'sDisplayStyle' => $sDisplayStyle,
            'othertext'=>$othertext,
            'checkedState'=>$checkedState,
            'kpclass'=>$kpclass,
            'oth_checkconditionFunction'=>$oth_checkconditionFunction.'(this.value, this.name, this.type)',
            'checkconditionFunction'=>$checkconditionFunction,
            ), true);

        $inputnames[] = $thisfieldname;

        ////
        // Close column
        // The column is closed if the user set more than one column in question attribute
        // We can't be sure it's the last one because of 'no answer' item
        if ($iRowCount == $iMaxRowsByColumn) {
            $sRows .= doRender('/survey/questions/answer/listradio/columns/column_footer', [], true);
            $iRowCount = 0;
            $isOpen = false;
        }
    }

    if ($ia[6] != 'Y' && SHOW_NO_ANSWER == 1) {
        $iRowCount++;

        if ((!isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$ia[1]]) || $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$ia[1]] == '') || ($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$ia[1]] == ' ')) {
            $check_ans = CHECKED; //Check the "no answer" radio button if there is no answer in session.
        } else {
            $check_ans = '';
        }

        if ($iRowCount == 1) {
            $sRows .= doRender('/survey/questions/answer/listradio/columns/column_header', array('iColumnWidth' => $iColumnWidth), true);
        }

        $sRows .= doRender('/survey/questions/answer/listradio/rows/answer_row_noanswer', array(
            'name'=>$ia[1],
            'check_ans'=>$check_ans,
            'checkconditionFunction'=>$checkconditionFunction,
            ), true);


        ////
        // Close column
        // 'No answer' is always the last answer, so it's always closing the col and the bootstrap row containing the columns
        $sRows .= doRender('/survey/questions/answer/listradio/columns/column_footer', array('last'=>true), true);
        $isOpen = false;
    }

    ////
    // Close column
    // if on column has been opened and not closed
    // That can happen only when no 'other' option is set, and the maximum answer rows has not been reached in the last question
    if ($isOpen) {
        $sRows .= doRender('/survey/questions/answer/listradio/columns/column_footer', array('last'=>true), true);
    }

    //END OF ITEMS

    // ==> answer
    $answer = doRender('/survey/questions/answer/listradio/answer', array(
        'sTimer'=>$sTimer,
        'sRows' => $sRows,
        'name'  => $ia[1],
        'basename' => $ia[1],
        'value' => $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$ia[1]],
        'coreClass'=>$coreClass,
        ), true);

    $inputnames[] = $ia[1];
    return array($answer, $inputnames);
}
