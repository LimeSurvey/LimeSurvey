<?php

/**
 * RenderClass for MultipleChoiceWithComments Question
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
class RenderMultipleChoiceWithComments extends QuestionBaseRenderer
{
    private $sCoreClasses = 'ls-answers checkbox-list checkbox-text-list';
    private $inputnames = [];
    private $iLabelWidth = 0;

    private $attributeInputContainerWidth;
    private $attributeLabelWidth;
    private $sLabelWidth;
    private $sInputContainerWidth;

    /** @var boolean indicates if the question has the 'Other' option enabled */
    protected $hasOther;

    /** @var int the position where the 'Other' option should be placed. Possible values: 0 (At end), 1 (At beginning), 3 (After specific subquestion)*/
    protected $otherPosition;

    /** @var string the title of the subquestion after which the 'Other' option should be placed (if $otherPosition == 3) */
    protected $subquestionBeforeOther;

    const OTHER_POS_END = 'end';
    const OTHER_POS_START = 'beginning';
    const OTHER_POS_AFTER_SUBQUESTION = 'specific';

    public function __construct($aFieldArray, $bRenderDirect = false)
    {
        parent::__construct($aFieldArray, $bRenderDirect);

        $this->setSubquestions();

        if ($this->oQuestion->other == 'Y') {
            $this->iLabelWidth = 25;
        }
        
        /*Find the col-sm width : if none is set : default, if one is set, set another one to be 12, if two is set : no change */

        $this->attributeInputContainerWidth = intval(trim((string) $this->getQuestionAttribute('text_input_columns')));
        if ($this->attributeInputContainerWidth < 1 || $this->attributeInputContainerWidth > 12) {
            $this->attributeInputContainerWidth = null;
        }
        $this->attributeLabelWidth = intval(trim((string) $this->getQuestionAttribute('choice_input_columns')));
        if ($this->attributeLabelWidth < 1 || $this->attributeLabelWidth > 12) {
            /* old system or imported */
            $this->attributeLabelWidth = null;
        }

        if ($this->attributeInputContainerWidth === null && $this->attributeLabelWidth === null) {
            $this->sInputContainerWidth = 8;
            $this->sLabelWidth = 4;
        } else {
            if ($this->attributeInputContainerWidth !== null) {
                $this->sInputContainerWidth = $this->attributeInputContainerWidth;
            } elseif ($this->attributeLabelWidth == 12) {
                $this->sInputContainerWidth = 12;
            } else {
                $this->sInputContainerWidth = 12 - $this->attributeLabelWidth;
            }
            if ($this->attributeLabelWidth !== null) {
                $this->sLabelWidth = $this->attributeLabelWidth;
            } elseif ($this->attributeInputContainerWidth == 12) {
                $this->sLabelWidth = 12;
            } else {
                $this->sLabelWidth = 12 - $this->attributeInputContainerWidth;
            }
        }

        $this->hasOther = $this->oQuestion->other == 'Y';
        $this->otherPosition = $this->setDefaultIfEmpty($this->getQuestionAttribute('other_position'), self::OTHER_POS_END);
        $this->subquestionBeforeOther = '';
        if ($this->hasOther && $this->otherPosition == self::OTHER_POS_AFTER_SUBQUESTION) {
            $this->subquestionBeforeOther = $this->getQuestionAttribute('other_position_code');
        }
    }

    public function getMainView()
    {
        return '/survey/questions/answer/multiplechoice_with_comments';
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
        foreach ($this->aSubQuestions[0] as $oQuestion) {
            $myfname = $this->sSGQA . $oQuestion->title;
            $myfname2 = $myfname . "comment";
            $mSessionValue = $this->setDefaultIfEmpty($_SESSION['survey_' . Yii::app()->getConfig('surveyID')][$myfname], '');
            $mSessionValue2 = $this->setDefaultIfEmpty($_SESSION['survey_' . Yii::app()->getConfig('surveyID')][$myfname2], '');
            
            if ($this->iLabelWidth < strlen(trim(strip_tags((string) $oQuestion->questionl10ns[$this->sLanguage]->question)))) {
                $this->iLabelWidth = strlen(trim(strip_tags((string) $oQuestion->questionl10ns[$this->sLanguage]->question)));
            }

            $this->inputnames[] = $myfname;
            $this->inputnames[] = $myfname2;

            ////
            // Insert row
            // Display the answer row
            $aRows[] = array(
                'title'                => '',
                'liclasses'            => 'responsive-content question-item answer-item checkbox-text-item',
                'name'                 => $myfname,
                'id'                   => 'answer' . $myfname,
                'value'                => 'Y', // TODO : check if it should be the same than javavalue
                'classes'              => '',
                'otherNumber'          => $this->getQuestionAttribute('other_numbers_only'),
                'labeltext'            => $oQuestion->questionl10ns[$this->sLanguage]->question,
                'javainput'            => true,
                'javaname'             => 'java' . $myfname,
                'javavalue'            => $mSessionValue,
                'checked'              => ($mSessionValue == 'Y' ? CHECKED : ''),
                'inputCommentId'       => 'answer' . $myfname2,
                'commentLabelText'     => gT('Make a comment on your choice here:'),
                'inputCommentName'     => $myfname2,
                'inputCOmmentValue'    => CHtml::encode($mSessionValue2),
                'sInputContainerWidth' => $this->sInputContainerWidth,
                'sLabelWidth'          => $this->sLabelWidth,
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

        $myfname = $this->sSGQA . 'other';
        $myfname2 = $myfname . "comment";

        $mSessionValue = $this->setDefaultIfEmpty($_SESSION['survey_' . Yii::app()->getConfig('surveyID')][$myfname], '');
        $mSessionValue2 = $this->setDefaultIfEmpty($_SESSION['survey_' . Yii::app()->getConfig('surveyID')][$myfname2], '');

        $this->inputnames[] = $myfname;
        $this->inputnames[] = $myfname2;

        $sValue = '';
        if (!empty($mSessionValue)) {
            $dispVal = $mSessionValue;
            if ($this->getQuestionAttribute('other_numbers_only') == 1) {
                $dispVal = str_replace('.', $sSeparator, (string) $dispVal);
            }
            $sValue .= CHtml::encode($dispVal);
        }

        ////
        // Insert row
        // Display the answer row
        return [
            'other'                => true,
            'liid'                 => 'javatbd' . $myfname,
            'title'                => gT('Other'),
            'name'                 => $myfname,
            'id'                   => 'answer' . $myfname,
            'value'                => $sValue, // TODO : check if it should be the same than javavalue
            'classes'              => '',
            'otherNumber'          => $this->getQuestionAttribute('other_numbers_only'),
            'labeltext'            => $this->setDefaultIfEmpty($this->getQuestionAttribute('other_replace_text', $this->sLanguage), gT('Other:')),
            'inputCommentId'       => 'answer' . $myfname2,
            'commentLabelText'     => gT('Make a comment on your choice here:'),
            'inputCommentName'     => $myfname2,
            'inputCOmmentValue'    => CHtml::encode($mSessionValue2),
            'checked'              => ($mSessionValue == 'Y' ? CHECKED : ''),
            'javainput'            => false,
            'javaname'             => '',
            'javavalue'            => '',
            'sInputContainerWidth' => $this->sInputContainerWidth,
            'sLabelWidth'          => $this->sLabelWidth,
            'liclasses'            => 'other question-item answer-item checkbox-text-item other-item',
        ];
    }


    public function render($sCoreClasses = '')
    {
        $answer = '';
        $inputnames = [];
        $this->sCoreClasses .= " " . $sCoreClasses;

        if ($this->getQuestionAttribute('commented_checkbox') != "allways" && $this->getQuestionAttribute('commented_checkbox_auto')) {
            $this->aScriptFiles[] = [
                'path' => Yii::app()->getConfig('generalscripts') . "multiplechoice_withcomments.js",
                'position' => LSYii_ClientScript::POS_BEGIN
            ];
            $this->addScript(
                'doMultipleChoiceWithComments',
                "doMultipleChoiceWithComments({$this->oQuestion->qid},'{$this->getQuestionAttribute("commented_checkbox")}');",
                LSYii_ClientScript::POS_POSTSCRIPT,
                true
            );
        }
        $this->registerAssets();

        $answer .=  Yii::app()->twigRenderer->renderQuestion($this->getMainView() . '/answer', array(
            'aRows' => $this->getRows(),
            'coreClass' => $this->sCoreClasses,
            'name' => 'MULTI' . $this->sSGQA,
            'basename' => $this->sSGQA,
            'value' => $this->getQuestionCount()
           ), true);

        $this->inputnames[] = $this->sSGQA;
        return array($answer, $this->inputnames);
    }

    protected function getQuestionCount($iScaleId = 0)
    {
        if (!empty($this->aSubQuestions)) {
            $counter = count($this->aSubQuestions[$iScaleId]);
            if ($this->oQuestion->other == 'Y') {
                $counter++;
                $counter++;
            }
            return $counter;
        }
        return 0;
    }
}
