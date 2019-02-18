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
class RenderMultipleShortText extends QuestionBaseRenderer
{
    private $sCoreClasses = 'ls-answers subquestion-list questions-list text-list ';
    private $inputnames = [];
    private $widthArray = [];
    private $sSeparator = '';
    private $extraclass = '';
    private $maxlength = '';
    private $inputsize = null;
    private $numbersonly = false;
    private $prefix = '';
    private $suffix = '';

    public function __construct($aFieldArray, $bRenderDirect = false)
    {
        parent::__construct($aFieldArray, $bRenderDirect);
        $this->setSubquestions();
        $this->setPrefixAndSuffix();
        
        $this->widthArray = $this->getLabelInputWidth();
        $this->numbersonly = ($this->aQuestionAttributes['numbers_only'] == 1);
        

        if ($this->aQuestionAttributes['numbers_only'] == 1) {
            $this->sSeparator   = (getRadixPointData($this->oQuestion->survey->correct_relation_defaultlanguage->surveyls_numberformat))['separator'];
            $this->extraclass   .= " numberonly";
            $this->sCoreClasses .= " number-list ";
        } 

        if (intval($this->setDefaultIfEmpty($this->aQuestionAttributes['maximum_chars'], 0)) > 0) {
            // Only maxlength attribute, use textarea[maxlength] jquery selector for textarea
            $this->maxlength = intval(trim($this->aQuestionAttributes['maximum_chars']));
            $this->extraclass .= " ls-input-maxchars";
        }

        if (ctype_digit(trim($this->aQuestionAttributes['input_size']))) {
            $this->inputsize = trim($this->aQuestionAttributes['input_size']);
            $this->extraclass .= " ls-input-sized";
        }
    }

    public function setPrefixAndSuffix(){
        $sPrefix = $this->setDefaultIfEmpty($this->aQuestionAttributes['prefix'][$this->sLanguage], '');
        if ($sPrefix != '') {
            $this->prefix = $sPrefix;
            $this->extraclass .= " withprefix";
        }
        
        $sSuffix = $this->setDefaultIfEmpty($this->aQuestionAttributes['suffix'][$this->sLanguage], '');
        if ($sSuffix != '') {
            $this->suffix = $sSuffix;
            $this->extraclass .= " withsuffix";
        }
    }

    public function getMainView()
    {
        return '/survey/questions/answer/multipleshorttext';
    }
    
    public function getRows()
    {
        $aRows = [];
        foreach ($this->aSubQuestions[0] as $oSubquestion) {
            $myfname = $this->sSGQA.$oSubquestion->title;
            $sSubquestionText = $this->setDefaultIfEmpty($oSubquestion->questionL10ns[$this->sLanguage]->question, "&nbsp;");

            // color code missing mandatory questions red
            $alert = (
                (($this->aSurveySessionArray['step'] != $this->aSurveySessionArray['maxstep']) 
                || ($this->aSurveySessionArray['step'] == $this->aSurveySessionArray['prevstep']))
                && (($this->oQuestion->mandatory == 'Y' || $this->oQuestion->mandatory == 'S') && $this->aSurveySessionArray[$myfname] === '')
            );

            $sDisplayStyle = '';

            $dispVal       = $this->setDefaultIfEmpty($this->aSurveySessionArray[$myfname],'');
            if ($this->numbersonly === true) {
                $dispVal = str_replace('.', $this->sSeparator, $dispVal);
            }
            $dispVal = htmlspecialchars($dispVal, ENT_QUOTES, 'UTF-8');

            if (trim($this->aQuestionAttributes['display_rows']) != '') {
                $aRows[] = array(
                    'textarea'               => true,
                    'sDisplayStyle'          => '',
                    'alert'                  => $alert,
                    'myfname'                => $myfname,
                    'labelname'              => 'answer'.$myfname,
                    'dispVal'                => $dispVal,
                    'question'               => $sSubquestionText,
                    'numbersonly'            => $this->numbersonly,
                    'maxlength'              => $this->maxlength,
                    'rows'                   => $this->aQuestionAttributes['display_rows'],
                    'inputsize'              => $this->inputsize,
                    'extraclass'             => $this->extraclass,
                    'prefix'                 => $this->prefix,
                    'suffix'                 => $this->suffix,
                    'sInputContainerWidth'   => $this->widthArray['sInputContainerWidth'],
                    'sLabelWidth'            => $this->widthArray['sLabelWidth'],
                    );

                    //sLabelWidth
                    //sInputContainerWidth
                    //defaultWidth
            } else {
                $aRows[] = array(
                    'textarea'               => false,
                    'sDisplayStyle'          => '',
                    'alert'                  => $alert,
                    'myfname'                => $myfname,
                    'labelname'              => 'answer'.$myfname,
                    'dispVal'                => $dispVal,
                    'question'               => $sSubquestionText,
                    'numbersonly'            => $this->numbersonly,
                    'maxlength'              => $this->maxlength,
                    'inputsize'              => $this->inputsize,
                    'extraclass'             => $this->extraclass,
                    'prefix'                 => $this->prefix,
                    'suffix'                 => $this->suffix,
                    'sInputContainerWidth'   => $this->widthArray['sInputContainerWidth'],
                    'sLabelWidth'            => $this->widthArray['sLabelWidth'],
                    );
            }

            $this->inputnames[] = $myfname;
        }

        return  $aRows;
    }

    public function render($sCoreClasses = '')
    {
        $answer = '';


        $answer .=  Yii::app()->twigRenderer->renderQuestion(
            $this->getMainView().'/answer',
            array(
                'aRows' => $this->getRows(),
                'coreClass'=>$this->sCoreClasses.' '.$sCoreClasses,
                'basename'=>$this->sSGQA,
            ), 
            true
        );

        // $inputnames[] = $this->sSGQA;
        return array($answer, $this->inputnames);
    }
}

/*
function do_multipleshorttext($ia)
{
    global $thissurvey;
    $extraclass          = "";
    $aQuestionAttributes = QuestionAttribute::model()->getQuestionAttributes($ia[0]);
    $coreClass = "ls-answers subquestion-list questions-list text-list";
    if ($aQuestionAttributes['numbers_only'] == 1) {
        $sSeparator             = getRadixPointData($thissurvey['surveyls_numberformat']);
        $sSeparator             = $sSeparator['separator'];
        $extraclass            .= " numberonly";
        $coreClass             .= " number-list";
    } else {
        $sSeparator = '';
    }

    if (intval(trim($aQuestionAttributes['maximum_chars'])) > 0) {
        // Only maxlength attribute, use textarea[maxlength] jquery selector for textarea
        $maxlength = intval(trim($aQuestionAttributes['maximum_chars']));
        $extraclass .= " ls-input-maxchars";
    } else {
        $maxlength = "";
    }
    if (ctype_digit(trim($aQuestionAttributes['input_size']))) {
        $inputsize = trim($aQuestionAttributes['input_size']);
        $extraclass .= " ls-input-sized";
    } else {
        $inputsize = null;
    }

    /* Find the col-sm width : if non is set : default, if one is set, set another one to be 12, if two is set : no change
    /* Find the col-sm width : if none is set : default, if one is set, set another one to be 12, if two is set : no change
    list($sLabelWidth, $sInputContainerWidth, $defaultWidth) = getLabelInputWidth($aQuestionAttributes['label_input_columns'], $aQuestionAttributes['text_input_columns']);


    if (trim($aQuestionAttributes['prefix'][$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']]) != '') {
        $prefix      = $aQuestionAttributes['prefix'][$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']];
        $extraclass .= " withprefix";
    } else {
        $prefix = '';
    }

    if (trim($aQuestionAttributes['suffix'][$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']]) != '') {
        $suffix      = $aQuestionAttributes['suffix'][$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']];
        $extraclass .= " withsuffix";
    } else {
        $suffix = '';
    }
    $kpclass = testKeypad($thissurvey['nokeyboard']); // Virtual keyboard (probably obsolete today)

    $sSurveyLanguage = $_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang'];
    // Get questions and answers by defined order
    if ($aQuestionAttributes['random_order'] == 1) {
        $sOrder = dbRandom();
    } else {
        $sOrder = 'question_order';
    }
    $aSubquestions = Question::model()->findAll(array('order'=>$sOrder, 'condition'=>'parent_qid=:parent_qid', 'params'=>array(':parent_qid'=>$ia[0])));        
    $anscount      = count($aSubquestions) * 2;
    $fn            = 1;
    $sRows         = '';
    $inputnames = [];

    if ($anscount != 0) {
        $alert = false;
        foreach ($aSubquestions as $aSubquestion) {
            $myfname = $ia[1].$aSubquestion['title'];
            $sSubquestionText = ($aSubquestion->questionL10ns[$sSurveyLanguage]->question == "") ? "&nbsp;" : $aSubquestion->questionL10ns[$sSurveyLanguage]->question;

            // color code missing mandatory questions red
            if (($_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['step'] != $_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['maxstep']) || ($_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['step'] == $_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['prevstep'])) {
                if ($ia[6] == 'Y' && $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname] === '') {
                    $alert = true;
                }
            }

            $sDisplayStyle = return_display_style($ia, $aQuestionAttributes, $thissurvey, $myfname);
            $dispVal       = '';

            if (isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname])) {
                $dispVal = $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname];
                if ($aQuestionAttributes['numbers_only'] == 1) {
                    $dispVal = str_replace('.', $sSeparator, $dispVal);
                }
                $dispVal = htmlspecialchars($dispVal, ENT_QUOTES, 'UTF-8');
            }
            $numbersonly = ($aQuestionAttributes['numbers_only'] == 1);
            if (trim($aQuestionAttributes['display_rows']) != '') {
                $sRows .= doRender('/survey/questions/answer/multipleshorttext/rows/answer_row_textarea', array(
                    'alert'                  => $alert,
                    'labelname'              => 'answer'.$myfname,
                    'maxlength'              => $maxlength,
                    'rows'                   => $aQuestionAttributes['display_rows'],
                    'numbersonly'            => $numbersonly,
                    'sInputContainerWidth'   => $sInputContainerWidth,
                    'sLabelWidth'            => $sLabelWidth,
                    'inputsize'              => $inputsize,
                    'extraclass'             => $extraclass,
                    'sDisplayStyle'          => $sDisplayStyle,
                    'prefix'                 => $prefix,
                    'myfname'                => $myfname,
                    'question'               => $sSubquestionText,
                    'kpclass'                => $kpclass,
                    'dispVal'                => $dispVal,
                    'suffix'                 => $suffix,
                    ), true);
            } else {
                $sRows .= doRender('/survey/questions/answer/multipleshorttext/rows/answer_row_inputtext', array(
                    'alert'                  => $alert,
                    'labelname'              => 'answer'.$myfname,
                    'maxlength'              => $maxlength,
                    'numbersonly'            => $numbersonly,
                    'sInputContainerWidth'   => $sInputContainerWidth,
                    'sLabelWidth'            => $sLabelWidth,
                    'inputsize'              => $inputsize,
                    'extraclass'             => $extraclass,
                    'sDisplayStyle'          => $sDisplayStyle,
                    'prefix'                 => $prefix,
                    'myfname'                => $myfname,
                    'question'               => $sSubquestionText,
                    'kpclass'                => $kpclass,
                    'dispVal'                => $dispVal,
                    'suffix'                 => $suffix,
                    ), true);
            }
            $fn++;
            $inputnames[] = $myfname;
        }

        $answer = doRender('/survey/questions/answer/multipleshorttext/answer', array(
            'sRows' => $sRows,
            'coreClass'=>$coreClass,
            'basename'=>$ia[1],
            ), true);

    } else {
        $inputnames   = [];
        $answer       = doRender('/survey/questions/answer/multipleshorttext/empty', [], true);
    }

    return array($answer, $inputnames);
}*/