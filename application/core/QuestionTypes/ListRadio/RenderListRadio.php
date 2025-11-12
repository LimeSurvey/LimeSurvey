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
class RenderListRadio extends QuestionBaseRenderer
{
    public $sCoreClass = "ls-answers answers-list radio-list";
    
    protected $sOthertext;
    protected $iNbCols;
    protected $iCountAnswers;

    private $inputnames = [];

    /** @var boolean indicates if the question has the 'Other' option enabled */
    protected $hasOther;

    /** @var int the position where the 'Other' option should be placed. Possible values: 0 (Before no answer), 1 (At beginning), 2 (At end), 3 (After specific option)*/
    protected $otherPosition;

    /** @var string the code of the answer after which the 'Other' option should be placed (if $otherPosition == 3) */
    protected $answerBeforeOther;

    const OTHER_POS_BEFORE_NOANSWER = 'default';
    const OTHER_POS_START = 'beginning';
    const OTHER_POS_END = 'end';
    const OTHER_POS_AFTER_OPTION = 'specific';
    
    public function __construct($aFieldArray, $bRenderDirect = false)
    {
        parent::__construct($aFieldArray, $bRenderDirect);
        $this->sOthertext = $this->setDefaultIfEmpty($this->getQuestionAttribute('other_replace_text', $this->sLanguage), gT('Other:')); // text for 'other'
        $this->iNbCols   = $this->setDefaultIfEmpty($this->getQuestionAttribute('display_columns'), ""); // number of columns
        $this->hasOther = $this->oQuestion->other == 'Y';
        $this->otherPosition = $this->setDefaultIfEmpty($this->getQuestionAttribute('other_position'), self::OTHER_POS_BEFORE_NOANSWER);
        $this->answerBeforeOther = '';
        if ($this->hasOther && $this->otherPosition == self::OTHER_POS_AFTER_OPTION) {
            $this->answerBeforeOther = $this->getQuestionAttribute('other_position_code');
        }
        $this->setAnsweroptions();

        if ($this->iNbCols) {
            $this->sCoreClass .= " multiple-list nbcol-{$this->iNbCols}";
        }
    }

    public function getMainView()
    {
        return '/survey/questions/answer/listradio';
    }

    public function renderRowsArray()
    {
        $otherRendered = false;

        $aRows = [];
        if ($this->hasOther && $this->otherPosition == self::OTHER_POS_START) {
            $aRows[] = $this->addOtherRow();
            $otherRendered = true;
        }

        foreach ($this->aAnswerOptions[0] as $iterator => $oAnswer) {
            $aRows[] = Yii::app()->twigRenderer->renderQuestion($this->getMainView() . '/rows/answer_row', array(
                'name'          => $this->sSGQA,
                'code'          => $oAnswer->code,
                'answer'        => $oAnswer->answerl10ns[$this->sLanguage]->answer,
                'checkedState'  => ($this->mSessionValue == $oAnswer->code ? 'CHECKED' : ''),
                'myfname'       => $this->sSGQA . $oAnswer->code,
                'iNbCols' => $this->iNbCols,
                'iCountAnswers' => $this->iCountAnswers,
                'hasOther' => $this->hasOther,
                'otherPosition' => $this->otherPosition,
                'answerBeforeOther' => $this->answerBeforeOther,
                ), true);
            if ($this->hasOther && $this->otherPosition == self::OTHER_POS_AFTER_OPTION && $this->answerBeforeOther == $oAnswer->code) {
                $aRows[] = $this->addOtherRow();
                $otherRendered = true;
            }
        }

        if (($this->oQuestion->mandatory != 'Y' && $this->oQuestion->mandatory != 'S') && SHOW_NO_ANSWER == 1) {
            if ($this->hasOther && $this->otherPosition == self::OTHER_POS_BEFORE_NOANSWER) {
                $aRows[] = $this->addOtherRow();
                $otherRendered = true;
            }
            $aRows[] = $this->addNoAnswerRow();
        }

        if ($this->hasOther && !$otherRendered) {
            $aRows[] = $this->addOtherRow();
        }

        return $aRows;
    }

    public function getRows()
    {
        $sRows = "";
        
        foreach ($this->renderRowsArray() as $iterator => $sRow) {
            // Insert row
            $sRows .= $sRow;
        }
        return $sRows;
    }

    public function addNoAnswerRow()
    {
        return Yii::app()->twigRenderer->renderQuestion($this->getMainView() . '/rows/answer_row_noanswer', array(
            'name' => $this->sSGQA,
            'check_ans' => $this->getIsNoAnswerChecked() ? CHECKED : '',
            'checkconditionFunction' => $this->checkconditionFunction,
            'iNbCols' => $this->iNbCols,
            'iCountAnswers' => $this->iCountAnswers,
            'hasOther' => $this->hasOther,
            'otherPosition' => $this->otherPosition,
            'answerBeforeOther' => $this->answerBeforeOther,
            ), true);
    }

    public function addOtherRow()
    {
        $sSeparator = getRadixPointData($this->oQuestion->survey->correct_relation_defaultlanguage->surveyls_numberformat);
        $sSeparator = $sSeparator['separator'];
        
        $oth_checkconditionFunction = ($this->getQuestionAttribute('other_numbers_only') == 1) ? 'fixnum_checkconditions' : 'checkconditions';
        $checkedState = ($this->mSessionValue == '-oth-') ? CHECKED : '';

        $myfname = $thisfieldname = $this->sSGQA . 'other';

        if (isset($_SESSION['survey_' . Yii::app()->getConfig('surveyID')][$thisfieldname])) {
            $dispVal = $_SESSION['survey_' . Yii::app()->getConfig('surveyID')][$thisfieldname];
            if ($this->getQuestionAttribute('other_numbers_only') == 1) {
                $dispVal = str_replace('.', $sSeparator, (string) $dispVal);
            }
            $answer_other = ' value="' . htmlspecialchars((string) $dispVal, ENT_QUOTES) . '"';
        } else {
            $answer_other = ' value=""';
        }

        $this->inputnames[] = $thisfieldname;
        
        return Yii::app()->twigRenderer->renderQuestion($this->getMainView() . '/rows/answer_row_other', array(
            'name' => $this->sSGQA,
            'answer_other' => $answer_other,
            'myfname' => $myfname,
            'othertext' => $this->sOthertext,
            'checkedState' => $checkedState,
            'oth_checkconditionFunction' => $oth_checkconditionFunction . '(this.value, this.name, this.type)',
            'checkconditionFunction' => $this->checkconditionFunction,
            'iNbCols' => $this->iNbCols,
            'iCountAnswers' => $this->iCountAnswers,
            'hasOther' => $this->hasOther,
            'otherPosition' => $this->otherPosition,
            'answerBeforeOther' => $this->answerBeforeOther,
            ), true);
    }


    public function render($sCoreClasses = '')
    {
        $answer = '';
        $this->inputnames[] = $this->sSGQA;
        $this->sCoreClass .= " " . $sCoreClasses;
        if (!empty($this->getQuestionAttribute('time_limit'))) {
            $answer .= $this->getTimeSettingRender();
        }
        $answer .=  Yii::app()->twigRenderer->renderQuestion($this->getMainView() . '/answer', array(
            'sRows'     => $this->getRows(),
            'name'      => $this->sSGQA,
            'basename'  => $this->sSGQA,
            'value'     => $this->mSessionValue,
            'coreClass' => $this->sCoreClass,
            'othertext' => $this->sOthertext,
            'iNbCols' => $this->iNbCols,
            /* @deprecated since 6.3.3 : Leave it for old question theme compatibility, be sure to don't add columns */
            'iMaxRowsByColumn' => $this->getAnswerCount() + 3,
            'iCountAnswers' => $this->iCountAnswers,
            'hasOther' => $this->hasOther,
            'otherPosition' => $this->otherPosition,
            'answerBeforeOther' => $this->answerBeforeOther,
        ), true);
        $this->registerAssets();
        return array($answer, $this->inputnames);
    }


    protected function getAnswerCount($iScaleId = 0)
    {
        // Getting answerrcount
        $anscount  = count($this->aAnswerOptions[0]);
        $anscount  = ($this->oQuestion->other == 'Y') ? $anscount + 1 : $anscount; //COUNT OTHER AS AN ANSWER FOR MANDATORY CHECKING!
        $anscount  = (($this->oQuestion->mandatory != 'Y' && $this->oQuestion->mandatory != 'S') && SHOW_NO_ANSWER == 1) ? $anscount + 1 : $anscount; //Count up if "No answer" is showing
        return $anscount;
    }
}
