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
class RenderMultipleChoiceWithComments extends QuestionBaseRenderer
{
    private $sCoreClasses = 'ls-answers checkbox-list checkbox-text-list';
    private $inputnames = [];
    private $iLabelWidth = 0;

    private $attributeInputContainerWidth;
    private $attributeLabelWidth;
    private $sLabelWidth;
    private $sInputContainerWidth;



    public function __construct($aFieldArray, $bRenderDirect = false)
    {
        parent::__construct($aFieldArray, $bRenderDirect);

        $this->setSubquestions();

        if ($this->oQuestion->other == 'Y') {
            $this->iLabelWidth = 25;
        }
        
        /*Find the col-sm width : if none is set : default, if one is set, set another one to be 12, if two is set : no change */

        $this->attributeInputContainerWidth = intval(trim($this->getQuestionAttribute('text_input_columns')));
        if ($this->attributeInputContainerWidth < 1 || $this->attributeInputContainerWidth > 12) {
            $this->attributeInputContainerWidth = null;
        }
        $this->attributeLabelWidth = intval(trim($this->getQuestionAttribute('choice_input_columns')));
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
    }

    public function getMainView()
    {
        return '/survey/questions/answer/multiplechoice_with_comments';
    }
    
    public function getRows()
    {
        $aRows = [];
        if($this->getQuestionCount() == 0) {
            return $aRows;
        }

        $checkconditionFunction = "checkconditions"; 
        foreach ($this->aSubQuestions[0] as $oQuestion) {

            $myfname = $this->sSGQA.$oQuestion->title;
            $myfname2 = $myfname."comment";
            $mSessionValue = $this->setDefaultIfEmpty($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname], '');
            $mSessionValue2 = $this->setDefaultIfEmpty($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname2], '');
            
            if ($this->iLabelWidth < strlen(trim(strip_tags($oQuestion->questionL10ns[$this->sLanguage]->question)))) {
                $this->iLabelWidth = strlen(trim(strip_tags($oQuestion->questionL10ns[$this->sLanguage]->question)));
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
                'id'                   => 'answer'.$myfname,
                'value'                => 'Y', // TODO : check if it should be the same than javavalue
                'classes'              => '',
                'otherNumber'          => $this->getQuestionAttribute('other_numbers_only'),
                'labeltext'            => $oQuestion->questionL10ns[$this->sLanguage]->question,
                'javainput'            => true,
                'javaname'             => 'java'.$myfname,
                'javavalue'            => $mSessionValue,
                'checked'              => ($mSessionValue == 'Y' ? CHECKED : ''),
                'inputCommentId'       => 'answer'.$myfname2,
                'commentLabelText'     => gT('Make a comment on your choice here:'),
                'inputCommentName'     => $myfname2,
                'inputCOmmentValue'    => (isset($mSessionValue2)) ? $mSessionValue2 : '',
                'sInputContainerWidth' => $this->sInputContainerWidth,
                'sLabelWidth'          => $this->sLabelWidth,
            );
        }

        if ($this->oQuestion->other == 'Y') {
          $aRows[] = $this->getOtherRow();
        }

        return $aRows;
    }

    public function getOtherRow(){

        $sSeparator = (getRadixPointData($this->oQuestion->survey->correct_relation_defaultlanguage->surveyls_numberformat))['separator'];

        $myfname = $this->sSGQA.'other';
        $myfname2 = $myfname."comment";

        $mSessionValue = $this->setDefaultIfEmpty($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname], '');
        $mSessionValue2 = $this->setDefaultIfEmpty($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname2], '');

        $this->inputnames[] = $myfname;
        $this->inputnames[] = $myfname2;

        $sValue = '';
        if (!empty($mSessionValue)) {
            $dispVal = $mSessionValue;
            if ($this->getQuestionAttribute('other_numbers_only') == 1) {
                $dispVal = str_replace('.', $sSeparator, $dispVal);
            }
            $sValue .= htmlspecialchars($dispVal, ENT_QUOTES);
        }

        // TODO : check if $sValueHidden === $sValue
        $sValueHidden = '';
        if (!empty($mSessionValue)) {
            $dispVal = $mSessionValue;
            if ($this->getQuestionAttribute('other_numbers_only') == 1) {
                $dispVal = str_replace('.', $sSeparator, $dispVal);
            }
            $sValueHidden = htmlspecialchars($dispVal, ENT_QUOTES);
        }

        // TODO: $value is not defined for some execution paths.
        if (!isset($value)) {
            $sValue = '';
        }

        ////
        // Insert row
        // Display the answer row
        return [
            'other'                => true,
            'liid'                 => 'javatbd'.$myfname,
            'title'                => gT('Other'),
            'name'                 => $myfname,
            'id'                   => 'answer'.$myfname,
            'value'                => $sValue, // TODO : check if it should be the same than javavalue
            'classes'              => '',
            'otherNumber'          => $this->getQuestionAttribute('other_numbers_only'),
            'labeltext'            => $this->setDefaultIfEmpty($this->getQuestionAttribute('other_replace_text', $this->sLanguage), gT('Other:')),
            'inputCommentId'       => 'answer'.$myfname2,
            'commentLabelText'     => gT('Make a comment on your choice here:'),
            'inputCommentName'     => $myfname2,
            'inputCOmmentValue'    => $mSessionValue2,
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
        $this->sCoreClasses .= " ".$sCoreClasses;

        if ($this->getQuestionAttribute('commented_checkbox') != "allways" && $this->getQuestionAttribute('commented_checkbox_auto')) {
            $this->aScriptFiles[] = [
                'path' => Yii::app()->getConfig('generalscripts')."multiplechoice_withcomments.js", 
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

        $answer .=  Yii::app()->twigRenderer->renderQuestion($this->getMainView().'/answer', array(
            'aRows' => $this->getRows(),
            'coreClass'=>$this->sCoreClasses,
            'name'=>'MULTI'.$this->sSGQA,
            'basename'=> $this->sSGQA,
            'value'=> $this->getQuestionCount()
           ), true);

        $this->inputnames[] = $this->sSGQA;
        return array($answer, $this->inputnames);
    }

    protected function getQuestionCount($iScaleId=0){
        if(!empty($this->aSubQuestions)) {
            $counter = count($this->aSubQuestions[$iScaleId]);
            if($this->oQuestion->other == 'Y') {
                $counter++;
                $counter++;
            }
            return $counter;
        }
        return 0;
    }
}


/*
function do_multiplechoice_withcomments($ia)
{
    global $thissurvey;
    $kpclass    = testKeypad($thissurvey['nokeyboard']); // Virtual keyboard (probably obsolete today)
    $inputnames = [];
    $coreClass = "ls-answers answers-list checkbox-text-list";
    $aQuestionAttributes = QuestionAttribute::model()->getQuestionAttributes($ia[0]);

    if ($aQuestionAttributes['other_numbers_only'] == 1) {
        $sSeparator                 = getRadixPointData($thissurvey['surveyls_numberformat']);
        $sSeparator                 = $sSeparator['separator'];
        $otherNumber = 1;
    } else {
        $otherNumber = 0;
        $sSeparator = '.';
    }

    if (trim($aQuestionAttributes['other_replace_text'][$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']]) != '') {
        $othertext = $aQuestionAttributes['other_replace_text'][$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']];
    } else {
        $othertext = gT('Other:');
    }

    $aQuestion = Question::model()->findByPk($ia[0]);        
    $sSurveyLanguage = $_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang'];
    // Get questions and answers by defined order
    if ($aQuestionAttributes['random_order'] == 1) {
        $sOrder = dbRandom();
    } else {
        $sOrder = 'question_order';
    }
    $aSubquestions = Question::model()->findAll(array('order'=>$sOrder, 'condition'=>'parent_qid=:parent_qid', 'params'=>array(':parent_qid'=>$ia[0])));        
    $anscount = count($aSubquestions) * 2;

    $fn = 1;
    if ($aQuestion->other == 'Y') {
        $label_width = 25;
    } else {
        $label_width = 0;
    }
    /* Find the col-sm width : if none is set : default, if one is set, set another one to be 12, if two is set : no change
    $attributeInputContainerWidth = intval(trim($aQuestionAttributes['text_input_columns']));
    if ($attributeInputContainerWidth < 1 || $attributeInputContainerWidth > 12) {
        $attributeInputContainerWidth = null;
    }
    $attributeLabelWidth = intval(trim($aQuestionAttributes['choice_input_columns']));
    if ($attributeLabelWidth < 1 || $attributeLabelWidth > 12) {
    /* old system or imported 
        $attributeLabelWidth = null;
    }
    if ($attributeInputContainerWidth === null && $attributeLabelWidth === null) {
        $sInputContainerWidth = 8;
        $sLabelWidth = 4;
    } else {
        if ($attributeInputContainerWidth !== null) {
            $sInputContainerWidth = $attributeInputContainerWidth;
        } elseif ($attributeLabelWidth == 12) {
            $sInputContainerWidth = 12;
        } else {
            $sInputContainerWidth = 12 - $attributeLabelWidth;
        }
        if ($attributeLabelWidth !== null) {
            $sLabelWidth = $attributeLabelWidth;
        } elseif ($attributeInputContainerWidth == 12) {
            $sLabelWidth = 12;
        } else {
            $sLabelWidth = 12 - $attributeInputContainerWidth;
        }
    }

    // Size of elements depends on longest text item
    $longest_question = 0;
    foreach ($aSubquestions as $ansrow) {
        $current_length = round((strlen($ansrow->questionL10ns[$sSurveyLanguage]->question) / 10) + 1);
        $longest_question = ($longest_question > $current_length) ? $longest_question : $current_length;
    }

    $sRows = "";
    $inputCOmmentValue = '';
    $checked = '';
    foreach ($aSubquestions as $ansrow) {
        $myfname = $ia[1].$ansrow['title'];

        if ($label_width < strlen(trim(strip_tags($ansrow->questionL10ns[$sSurveyLanguage]->question)))) {
            $label_width = strlen(trim(strip_tags($ansrow->questionL10ns[$sSurveyLanguage]->question)));
        }

        $myfname2 = $myfname."comment";

        /* If the question has already been ticked, check the checkbox 
        $checked = '';
        if (isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname])) {
            if ($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname] == 'Y') {
                $checked = CHECKED;
            }
        }

        $javavalue = (isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname])) ? $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname] : '';

        $fn++;
        $fn++;
        $inputnames[] = $myfname;
        $inputnames[] = $myfname2;

        $inputCOmmentValue = htmlspecialchars($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname2], ENT_QUOTES);
        $sRows .= doRender('/survey/questions/answer/multiplechoice_with_comments/rows/answer_row', array(
            'kpclass'                       => $kpclass,
            'title'                         => '',
            'liclasses'                     => 'responsive-content question-item answer-item checkbox-text-item',
            'name'                          => $myfname,
            'id'                            => 'answer'.$myfname,
            'value'                         => 'Y', // TODO : check if it should be the same than javavalue
            'classes'                       => '',
            'otherNumber'                   => $otherNumber,
            'labeltext'                     => $ansrow->questionL10ns[$sSurveyLanguage]->question,
            'javainput'                     => true,
            'javaname'                      => 'java'.$myfname,
            'javavalue'                     => $javavalue,
            'checked'                       => $checked,
            'inputCommentId'                => 'answer'.$myfname2,
            'commentLabelText'              => gT('Make a comment on your choice here:'),
            'inputCommentName'              => $myfname2,
            'inputCOmmentValue'             => (isset($inputCOmmentValue)) ? $inputCOmmentValue : '',
            'sInputContainerWidth'          => $sInputContainerWidth,
            'sLabelWidth'                   => $sLabelWidth,
            ), true);

    }
    if ($aQuestion->other == 'Y') {
        $myfname = $ia[1].'other';
        $myfname2 = $myfname.'comment';
        $anscount = $anscount + 2;
        // SPAN LABEL OPTION //////////////////////////
        if (isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname]) && $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname]) {
            $dispVal = $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname];
            if ($aQuestionAttributes['other_numbers_only'] == 1) {
                $dispVal = str_replace('.', $sSeparator, $dispVal);
            }
            $value = htmlspecialchars($dispVal, ENT_QUOTES);
        }

        if (isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname2])) {
            $inputCOmmentValue = htmlspecialchars($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname2], ENT_QUOTES);
        }

        // TODO: $value is not defined for some execution paths.
        if (!isset($value)) {
            $value = '';
        }

        $sRows .= doRender('/survey/questions/answer/multiplechoice_with_comments/rows/answer_row_other', array(
            'liclasses'                     => 'other question-item answer-item checkbox-text-item other-item',
            'liid'                          => 'javatbd'.$myfname,
            'kpclass'                       => $kpclass,
            'title'                         => gT('Other'),
            'name'                          => $myfname,
            'id'                            => 'answer'.$myfname,
            'value'                         => $value, // TODO : check if it should be the same than javavalue
            'classes'                       => '',
            'otherNumber'                   => $otherNumber,
            'labeltext'                     => $othertext,
            'inputCommentId'                => 'answer'.$myfname2,
            'commentLabelText'              => gT('Make a comment on your choice here:'),
            'inputCommentName'              => $myfname2,
            'inputCOmmentValue'             => $inputCOmmentValue,
            'checked'                       => $checked,
            'javainput'                     => false,
            'javaname'                      => '',
            'javavalue'                     => '',
            'sInputContainerWidth'          => $sInputContainerWidth,
            'sLabelWidth'                   => $sLabelWidth
            ), true);
        $inputnames[] = $myfname;
        $inputnames[] = $myfname2;
    }

    $answer = doRender('/survey/questions/answer/multiplechoice_with_comments/answer', array(
        'sRows' => $sRows,
        'coreClass'=>$coreClass,
        'name'=>'MULTI'.$ia[1], /* ? name is not $ia[1] 
        'basename'=> $ia[1],
        'value'=> $anscount
        ), true);


    if ($aQuestionAttributes['commented_checkbox'] != "allways" && $aQuestionAttributes['commented_checkbox_auto']) {
        Yii::app()->getClientScript()->registerScriptFile(Yii::app()->getConfig('generalscripts')."multiplechoice_withcomments.js", LSYii_ClientScript::POS_BEGIN);
        Yii::app()->getClientScript()->registerScript('doMultipleChoiceWithComments'.$ia[0],
        "doMultipleChoiceWithComments({$ia[0]},'{$aQuestionAttributes["commented_checkbox"]}');",
        LSYii_ClientScript::POS_POSTSCRIPT);
    }

    return array($answer, $inputnames);
}

*/
