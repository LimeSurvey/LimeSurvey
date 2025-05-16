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
class RenderMultipleChoice extends QuestionBaseRenderer
{
    private $sCoreClasses = 'ls-answers checkbox-list answers-list';
    private $inputnames = [];

    private $iColumnWidth;
    private $iMaxRowsByColumn;
    private $iNbCols;

    /** @var boolean indicates if the question has the 'Other' option enabled */
    protected $hasOther;

    /** @var int the position where the 'Other' option should be placed. Possible values: 0 (At end), 1 (At beginning), 3 (After specific subquestion)*/
    protected $otherPosition;

    /** @var string the title of the subquestion after which the 'Other' option should be placed (if $otherPosition == 3) */
    protected $subquestionBeforeOther;

    /** @var string the text for the "Other" option */
    protected $otherText;

    const OTHER_POS_END = 'end';
    const OTHER_POS_START = 'beginning';
    const OTHER_POS_AFTER_SUBQUESTION = 'specific';

    public function __construct($aFieldArray, $bRenderDirect = false)
    {
        parent::__construct($aFieldArray, $bRenderDirect);
        $this->setSubquestions();

        $this->iNbCols = max(1, $this->setDefaultIfEmpty($this->getQuestionAttribute('display_columns'), 1));
        $this->iColumnWidth = round(12 / $this->iNbCols);
        $this->iColumnWidth = ($this->iColumnWidth >= 1) ? $this->iColumnWidth : 1;
        $this->iColumnWidth = ($this->iColumnWidth <= 12) ? $this->iColumnWidth : 12;
        $this->iMaxRowsByColumn = ceil($this->getQuestionCount() / $this->iNbCols);
    
        if ($this->iNbCols > 1) {
            $this->sCoreClasses .= " multiple-list nbcol-{$this->iNbCols}";
        }

        $this->hasOther = $this->oQuestion->other == 'Y';
        $this->otherPosition = $this->setDefaultIfEmpty($this->getQuestionAttribute('other_position'), self::OTHER_POS_END);
        $this->subquestionBeforeOther = '';
        if ($this->hasOther && $this->otherPosition == self::OTHER_POS_AFTER_SUBQUESTION) {
            $this->subquestionBeforeOther = $this->getQuestionAttribute('other_position_code');
        }
        $this->otherText = $this->setDefaultIfEmpty($this->getQuestionAttribute('other_replace_text', $this->sLanguage), gT('Other:'));
    }

    public function getMainView()
    {
        return '/survey/questions/answer/multiplechoice';
    }
    
    public function getRows()
    {
        $otherAdded = false;

        $aRows = [];

        if ($this->getQuestionCount() == 0) {
            return $aRows;
        }

        if ($this->hasOther && $this->otherPosition == self::OTHER_POS_START) {
            $aRows[] = $this->getOtherRow();
            $otherAdded = true;
        }

        $checkconditionFunction = "checkconditions";
        /// Generate answer rows
        foreach ($this->aSubQuestions[0] as $oQuestion) {
            $myfname = $this->sSGQA . $oQuestion->title;
            $this->inputnames[] = $myfname;
            ////
            // Insert row
            // Display the answer row
            $aRows[] = array(
                'myfname'                 => $myfname,
                'name'                    => $this->sSGQA, // field name
                'title'                   => $oQuestion->title,
                'question'                => $oQuestion->questionl10ns[$this->sLanguage]->question,
                'ansrow'                  => array_merge($oQuestion->attributes, $oQuestion->questionl10ns[$this->sLanguage]->attributes),
                'checkedState'            => ($this->setDefaultIfEmpty($this->aSurveySessionArray[$myfname], '') == 'Y' ? CHECKED : ''),
                'sCheckconditionFunction' => $checkconditionFunction . '(this.value, this.name, this.type)',
                'sValue'                  => $this->setDefaultIfEmpty($this->aSurveySessionArray[$myfname], ''),
                'relevanceClass'          => $this->getCurrentRelevecanceClass($myfname)
            );
            if ($this->hasOther && $this->otherPosition == self::OTHER_POS_AFTER_SUBQUESTION && $this->subquestionBeforeOther == $oQuestion->title) {
                $aRows[] = $this->getOtherRow();
                $otherAdded = true;
            }
        }

        if ($this->hasOther && !$otherAdded) {
            $aRows[] = $this->getOtherRow();
        }

        return $aRows;
    }

    public function getOtherRow()
    {
        $sSeparator = (getRadixPointData($this->oQuestion->survey->correct_relation_defaultlanguage->surveyls_numberformat))['separator'];
        $oth_checkconditionFunction = ($this->getQuestionAttribute('other_numbers_only') == 1) ? "fixnum_checkconditions" : "checkconditions";

        $myfname = $this->sSGQA . 'other';
        $mSessionValue = $this->setDefaultIfEmpty($_SESSION['survey_' . Yii::app()->getConfig('surveyID')][$myfname], '');
        $this->inputnames[] = $myfname;

        $sValue = '';
        if (!empty($mSessionValue)) {
            $dispVal = $mSessionValue;
            if ($this->getQuestionAttribute('other_numbers_only') == 1) {
                $dispVal = str_replace('.', $sSeparator, (string) $dispVal);
            }
            $sValue .= htmlspecialchars((string) $dispVal, ENT_QUOTES);
        }

        // TODO : check if $sValueHidden === $sValue
        $sValueHidden = '';
        if (!empty($mSessionValue)) {
            $dispVal = $mSessionValue;
            if ($this->getQuestionAttribute('other_numbers_only') == 1) {
                $dispVal = str_replace('.', $sSeparator, (string) $dispVal);
            }
            $sValueHidden = htmlspecialchars((string) $dispVal, ENT_QUOTES);
        }

        ////
        // Insert row
        // Display the answer row
        return array(
            'myfname'                    => $myfname,
            'othertext'                  => $this->otherText,
            'sValue'                     => $sValue,
            'oth_checkconditionFunction' => $oth_checkconditionFunction,
            'checkconditionFunction'     => "checkconditions",
            'sValueHidden'               => $sValueHidden,
            'checkedState'               => ($mSessionValue != '' ? CHECKED : ''),
            'relevanceClass'             => $this->getCurrentRelevecanceClass($myfname),
            'other'                      => true
        );
    }


    public function render($sCoreClasses = '')
    {
        $answer = '';
        $inputnames = [];
        $this->sCoreClasses .= " " . $sCoreClasses;

        $answer .=  Yii::app()->twigRenderer->renderQuestion($this->getMainView() . '/answer', array(
            'aRows'            => $this->getRows(),
            'name'             => $this->sSGQA,
            'basename'         => $this->sSGQA,
            'anscount'         => $this->getQuestionCount(),
            'iColumnWidth'     => $this->iColumnWidth,
            'iMaxRowsByColumn' => $this->iMaxRowsByColumn,
            'iNbCols'          => $this->iNbCols,
            'coreClass'        => $this->sCoreClasses,
            'othertext'        => $this->otherText,
        ), true);

        $this->registerAssets();
        $this->inputnames[] = $this->sSGQA;
        return array($answer, $this->inputnames);
    }

    protected function getQuestionCount($iScaleId = 0)
    {
        if (!empty($this->aSubQuestions)) {
            $counter = count($this->aSubQuestions[$iScaleId]);
            if ($this->oQuestion->other == 'Y') {
                $counter++;
            }
            return $counter;
        }
        return 0;
    }
}
