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
    private $placeholder  = '';

    public function __construct($aFieldArray, $bRenderDirect = false)
    {
        parent::__construct($aFieldArray, $bRenderDirect);
        $this->setSubquestions();
        $this->setPrefixAndSuffix();
        $this->setPlaceholder();
        
        $this->widthArray = $this->getLabelInputWidth();
        $this->numbersonly = ($this->getQuestionAttribute('numbers_only') == 1);
        

        if ($this->getQuestionAttribute('numbers_only') == 1) {
            $this->sSeparator   = (getRadixPointData($this->oQuestion->survey->correct_relation_defaultlanguage->surveyls_numberformat))['separator'];
            $this->extraclass   .= " numberonly";
            $this->sCoreClasses .= " number-list ";
        }

        if (intval($this->setDefaultIfEmpty($this->getQuestionAttribute('maximum_chars'), 0)) > 0) {
            // Only maxlength attribute, use textarea[maxlength] jquery selector for textarea
            $this->maxlength = intval(trim((string) $this->getQuestionAttribute('maximum_chars')));
            $this->extraclass .= " ls-input-maxchars";
        }

        if (ctype_digit(trim((string) $this->getQuestionAttribute('input_size')))) {
            $this->inputsize = trim((string) $this->getQuestionAttribute('input_size'));
            $this->extraclass .= " ls-input-sized";
        }
    }

    public function setPrefixAndSuffix()
    {
        $sPrefix = $this->getQuestionAttribute('prefix', $this->sLanguage);
        if ($sPrefix != '') {
            $this->prefix = $sPrefix;
            $this->extraclass .= " withprefix";
        }
        
        $sSuffix = $this->getQuestionAttribute('suffix', $this->sLanguage);
        if ($sSuffix != '') {
            $this->suffix = $sSuffix;
            $this->extraclass .= " withsuffix";
        }
    }

    public function setPlaceholder()
    {
        $sPlaceholder = $this->getQuestionAttribute('placeholder', $this->sLanguage);
        if ($sPlaceholder != '') {
            $this->placeholder = $sPlaceholder;
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
            $myfname = $this->sSGQA . $oSubquestion->title;
            $sSubquestionText = $this->setDefaultIfEmpty($oSubquestion->questionl10ns[$this->sLanguage]->question, "&nbsp;");

            // color code missing mandatory questions red
            $alert = (
                (($this->aSurveySessionArray['step'] != $this->aSurveySessionArray['maxstep'])
                || ($this->aSurveySessionArray['step'] == $this->aSurveySessionArray['prevstep']))
                && (($this->oQuestion->mandatory == 'Y' || $this->oQuestion->mandatory == 'S') && $this->aSurveySessionArray[$myfname] === '')
            );

            $sDisplayStyle = '';

            $dispVal       = $this->setDefaultIfEmpty($this->aSurveySessionArray[$myfname], '');
            if ($this->numbersonly === true) {
                $dispVal = str_replace('.', $this->sSeparator, (string) $dispVal);
            }
            $dispVal = htmlspecialchars((string) $dispVal, ENT_QUOTES, 'UTF-8');

            if (trim((string) $this->getQuestionAttribute('display_rows')) != '') {
                $aRows[] = array(
                    'textarea'               => true,
                    'sDisplayStyle'          => '',
                    'alert'                  => $alert,
                    'myfname'                => $myfname,
                    'labelname'              => 'answer' . $myfname,
                    'dispVal'                => $dispVal,
                    'question'               => $sSubquestionText,
                    'numbersonly'            => $this->numbersonly,
                    'maxlength'              => $this->maxlength,
                    'rows'                   => $this->getQuestionAttribute('display_rows'),
                    'inputsize'              => $this->inputsize,
                    'extraclass'             => $this->extraclass,
                    'prefix'                 => $this->prefix,
                    'placeholder'            => $this->placeholder,
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
                    'labelname'              => 'answer' . $myfname,
                    'dispVal'                => $dispVal,
                    'question'               => $sSubquestionText,
                    'numbersonly'            => $this->numbersonly,
                    'maxlength'              => $this->maxlength,
                    'inputsize'              => $this->inputsize,
                    'extraclass'             => $this->extraclass,
                    'prefix'                 => $this->prefix,
                    'placeholder'            => $this->placeholder,
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
            $this->getMainView() . '/answer',
            array(
                'aRows' => $this->getRows(),
                'coreClass' => $this->sCoreClasses . ' ' . $sCoreClasses,
                'basename' => $this->sSGQA,
            ),
            true
        );

        $this->registerAssets();
        // $inputnames[] = $this->sSGQA;
        return array($answer, $this->inputnames);
    }
}
