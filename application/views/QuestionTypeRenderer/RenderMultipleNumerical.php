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
class RenderMultipleNumerical extends QuestionBaseRenderer
{
    private $handleOptions = [
        'round',
        'square',
        'triangle',
        'custom',
    ];
    
    
    private $sSeparator;
    private $useSliderLayout;

    private $sCoreClasses = "ls-answers subquestion-list questions-list ";
    private $inputnames = [];
    private $widthArray = [];
    private $sliderOptionsArray = [];
    private $extraclass = '';
    private $maxlength = '';
    private $inputsize = null;
    private $numbersonly = true;
    private $prefix = '';
    private $suffix = '';

    public function __construct($aFieldArray, $bRenderDirect = false)
    {
        parent::__construct($aFieldArray, $bRenderDirect);
        $this->setSubquestions();
        $this->setPrefixAndSuffix();
        
        $this->sSeparator   = (getRadixPointData($this->oQuestion->survey->correct_relation_defaultlanguage->surveyls_numberformat))['separator'];
        $this->useSliderLayout = $this->aQuestionAttributes['slider_layout'] == 1; 
        
        $this->widthArray = $this->getLabelInputWidth();
        $this->extraclass   .= " numberonly";

        if (intval($this->setDefaultIfEmpty($this->aQuestionAttributes['maximum_chars'], 0)) > 0) {
            // Only maxlength attribute, use textarea[maxlength] jquery selector for textarea
            $this->maxlength = intval(trim($this->aQuestionAttributes['maximum_chars']));
            $this->extraclass .= " ls-input-maxchars";
        }

        if (ctype_digit(trim($this->aQuestionAttributes['input_size']))) {
            $this->inputsize = trim($this->aQuestionAttributes['input_size']);
            $this->extraclass .= " ls-input-sized";
        }

        if ($this->useSliderLayout) {
            $this->sCoreClasses  .= " slider-list";
            $this->extraclass   .= " withslider";
            $this->sliderOptionsArray = [
                'slider_step'          => trim(LimeExpressionManager::ProcessString("{{$this->aQuestionAttributes['slider_accuracy']}}", $this->oQuestion->qid, [], 1, 1, false, false, true)),
                'slider_min'           => trim(LimeExpressionManager::ProcessString("{{$this->aQuestionAttributes['slider_min']}}", $this->oQuestion->qid, [], 1, 1, false, false, true)),
                'slider_max'           => trim(LimeExpressionManager::ProcessString("{{$this->aQuestionAttributes['slider_max']}}", $this->oQuestion->qid, [], 1, 1, false, false, true)),
                'slider_default'       => trim(LimeExpressionManager::ProcessString("{{$this->aQuestionAttributes['slider_default']}}", $this->oQuestion->qid, [], 1, 1, false, false, true)),
                'slider_orientation'   => (trim($this->aQuestionAttributes['slider_orientation']) == 0) ? 'horizontal' : 'vertical',
                'slider_custom_handle' => (trim($this->aQuestionAttributes['slider_custom_handle'])),
            ];
            
            $this->sliderOptionsArray['slider_min'] = (is_numeric($this->sliderOptionsArray['slider_min'])) ? $this->sliderOptionsArray['slider_min'] : 0;
            $this->sliderOptionsArray['slider_mintext'] = $this->sliderOptionsArray['slider_min'];
            $this->sliderOptionsArray['slider_max'] = (is_numeric($this->sliderOptionsArray['slider_max'])) ? $this->sliderOptionsArray['slider_max'] : 100;
            $this->sliderOptionsArray['slider_maxtext'] = $this->sliderOptionsArray['slider_max'];
            
            //Eventually reset numbers with wrong decimal separator
            if($this->sSeparator != '.') {
                $this->sliderOptionsArray['slider_step']    = preg_replace('/'.$this->sSeparator.'/','.',$this->sliderOptionsArray['slider_step']);
            }

            $this->sliderOptionsArray['slider_step']    = (is_numeric($this->sliderOptionsArray['slider_step'])) ? $this->sliderOptionsArray['slider_step'] : 1;
            $this->sliderOptionsArray['slider_default'] = (is_numeric($this->sliderOptionsArray['slider_default'])) ? $this->sliderOptionsArray['slider_default'] : "";
            $this->sliderOptionsArray['slider_handle']  = $this->handleOptions[(trim($this->aQuestionAttributes['slider_handle']))];
            $this->sliderOptionsArray['slider_default_set'] = (bool) ($this->aQuestionAttributes['slider_default_set'] && $this->sliderOptionsArray['slider_default'] !== '');

            // Put the slider init to initial state (when no click is set or when 'reset') 
            if ($this->sliderOptionsArray['slider_default'] !== '') {
                $this->sliderOptionsArray['slider_position'] = $this->sliderOptionsArray['slider_default'];
            } elseif ($this->aQuestionAttributes['slider_middlestart'] == 1) {
                $this->sliderOptionsArray['slider_position'] = intval(($this->sliderOptionsArray['slider_max'] + $this->sliderOptionsArray['slider_min']) / 2);
            }
            
            $this->sliderOptionsArray['slider_separator'] = $this->setDefaultIfEmpty($this->aQuestionAttributes['slider_separator'],"");
            $this->sliderOptionsArray['slider_reset'] = ($this->aQuestionAttributes['slider_reset']) ? 1 : 0;
    
            // Slider reversed value 
            if ($this->aQuestionAttributes['slider_reversed'] == 1) {
                $this->sliderOptionsArray['slider_reversed'] = 'true';
            } else {
                $this->sliderOptionsArray['slider_reversed'] = 'false';
            }


        } else {
            $this->sCoreClasses .= " text-list number-list";
            $this->sliderOptionsArray = [
                'slider_layout' => false,
                'slider_step'  => '',
                'slider_min'  => '',
                'slider_mintext'  => '',
                'slider_max'  => '',
                'slider_maxtext'  => '',
                'slider_default'  => null,
                'slider_orientation'  => '',
                'slider_handle'  => '',
                'slider_custom_handle'  => '',
                'slider_separator'  => '',
                'slider_reset'  => 0,
                'slider_reversed'  => 'false',
            ];
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
        return '/survey/questions/answer/multiplenumeric';
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
            $dispVal = str_replace('.', $this->sSeparator, $dispVal);

            if (!$this->useSliderLayout) {
                $aRows[] = array(
                    'sDisplayStyle'          => '',
                    'prefixclass'            => 'numeric',
                    'alert'                  => $alert,
                    'theanswer'              => $sSubquestionText,
                    'labelText'              => $sSubquestionText,
                    'labelname'              => 'answer'.$myfname,
                    'myfname'                => $myfname,
                    'dispVal'                => $dispVal,
                    'extraclass'             => $this->extraclass,
                    'qid'                    => $this->oQuestion->qid,
                    'answertypeclass'        => $this->aQuestionAttributes['num_value_int_only'] ? 'integeronly' : '',
                    'prefix'                 => $this->prefix,
                    'suffix'                 => $this->suffix,
                    'sInputContainerWidth'   => $this->widthArray['sInputContainerWidth'],
                    'sLabelWidth'            => $this->widthArray['sLabelWidth'],
                    'inputsize'              => $this->inputsize,
                    'maxlength'              => $this->maxlength,
                    'integeronly'            => $this->aQuestionAttributes['num_value_int_only'],
                );

            } else {
                $sliderWidth = 12;

                if ($this->sliderOptionsArray['slider_separator'] != '') {
                
                    $aAnswer     = explode($this->sliderOptionsArray['slider_separator'], $sSubquestionText);
                    $theanswer   = (isset($aAnswer[0])) ? $aAnswer[0] : "";
                    $labelText   = $theanswer;
                    $sliderleft  = (isset($aAnswer[1])) ? $aAnswer[1] : null;
                    $sliderright = (isset($aAnswer[2])) ? $aAnswer[2] : null;

                    /* sliderleft and sliderright is in input, but is part of answers then take label width */
                    if (!empty($sliderleft)) {
                        $sliderWidth = $sliderWidth-2;
                    }
                    
                    if (!empty($sliderright)) {
                        $sliderWidth = $sliderWidth-2;
                    }

                } else {
                    $theanswer = $sQuestionText;
                    $sliders   = false;
                }

                $aAnswer     = (isset($aAnswer)) ? $aAnswer : '';
                $sliderleft  = (isset($sliderleft)) ? $sliderleft : null;
                $sliderright = (isset($sliderright)) ? $sliderright : null;

                // The value of the slider depends on many possible different parameters, by order of priority :
                // 1. The value stored in the session
                if (isset($this->aSurveySessionArray[$myfname])) {
                    $sValue                = $this->aSurveySessionArray[$myfname];
                // 2. Else the default Answer   (set by EM and stored in session, so same case than 1)
                } elseif ($this->sliderOptionsArray['slider_default'] !== "" && $this->sliderOptionsArray['slider_default_set']) {
                    $sValue                = $this->sliderOptionsArray['slider_default'];
                // 3. Else the slider_default value : if slider_default_set set the value here
                } else {
                    $sValue                = null;
                }

                // 4. Else the middle start or slider_default or nothing : leave the value to "" for the input, show slider pos at this position
                $sUnformatedValue = $sValue ? $sValue : '';

                if (strpos($sValue, ".")) {
                    $sValue = rtrim(rtrim($sValue, "0"), ".");
                    $sValue = str_replace('.', $sSeparator, $sValue);
                }

                $aRows[] = array_merge(
                    array(
                        'sDisplayStyle'          => '',
                        'prefixclass'            => 'numeric',
                        'sliders'                => true,
                        'labelname'              => 'answer'.$myfname,
                        'alert'                  => $alert,
                        'theanswer'              => $theanswer,
                        'labelText'              => $sSubquestionText,
                        'myfname'                => $myfname,
                        'dispVal'                => $dispVal,
                        'sliderleft'             => $sliderleft,
                        'sliderright'            => $sliderright,
                        'sliderWidth'            => $sliderWidth,
                        'sUnformatedValue'       => $sUnformatedValue,
                        'extraclass'             => $this->extraclass,
                        'qid'                    => $this->oQuestion->qid,
                        'prefix'                 => $this->prefix,
                        'suffix'                 => $this->suffix,
                        'sInputContainerWidth'   => $this->widthArray['sInputContainerWidth'],
                        'sLabelWidth'            => $this->widthArray['sLabelWidth'],
                        'inputsize'              => $this->inputsize,
                        'maxlength'              => $this->maxlength,
                        'integeronly'            => $this->aQuestionAttributes['num_value_int_only'],
                        'basename'               => $this->sSGQA,
                        'sSeparator'             => $this->sSeparator,
                    ), $this->sliderOptionsArray);
                // array(
                //     'textarea'               => false,
                //     'sDisplayStyle'          => '',
                //     'alert'                  => $alert,
                //     'myfname'                => $myfname,
                //     'labelname'              => 'answer'.$myfname,
                //     'dispVal'                => $dispVal,
                //     'question'               => $sSubquestionText,
                //     'numbersonly'            => $this->numbersonly,
                //     'maxlength'              => $this->maxlength,
                //     'inputsize'              => $this->inputsize,
                //     'extraclass'             => $this->extraclass,
                //     'prefix'                 => $this->prefix,
                //     'suffix'                 => $this->suffix,
                //     'sInputContainerWidth'   => $this->widthArray['sInputContainerWidth'],
                //     'sLabelWidth'            => $this->widthArray['sLabelWidth'],
                //     );
            }

            $this->inputnames[] = $myfname;
        }

        return  $aRows;
    }

    public function renderSlider($sCoreClasses){
        
        
        return Yii::app()->twigRenderer->renderQuestion(
            $this->getMainView().'/answer',
            array(
                'aRows' => $this->getRows(),
                'coreClass'=>$this->sCoreClasses.' '.$sCoreClasses,
                'basename'=>$this->sSGQA,
            ), 
            true
        );
    }
    
    public function renderInput($sCoreClasses){
        return Yii::app()->twigRenderer->renderQuestion(
            $this->getMainView().'/answer_input',
            array(
                'aRows' => $this->getRows(),
                'coreClass'=>$this->sCoreClasses.' '.$sCoreClasses,
                'basename'=>$this->sSGQA,
            ), 
            true
        );
    }
    
    public function render($sCoreClasses = '')
    {
        $answer = '';
        $rowTemplate = '/survey/questions/answer/multiplenumeric/rows/input/answer_row.twig';
        $dynamicTemplate = "/survey/questions/answer/multiplenumeric/rows/dynamic.twig";

        if ($this->useSliderLayout) {
            /* Add some data for javascript */
            $sliderTranslation = array(
                'help'=>gT('Please click and drag the slider handles to enter your answer.')
            );
            $this->addScript(
                "sliderTranslation",
                "var sliderTranslation=".json_encode($sliderTranslation).";\n",
                CClientScript::POS_BEGIN,
                false
            );
            $this->aPackages[] = "question-numeric-slider";

            $rowTemplate = '/survey/questions/answer/multiplenumeric/rows/sliders/answer_row.twig';
            $dynamicTemplate = "/survey/questions/answer/multiplenumeric/rows/dynamic_slider.twig";
        }

        $answer .= Yii::app()->twigRenderer->renderQuestion(
            $this->getMainView().'/answer',
            array(
                'aRows' => $this->getRows(),
                'coreClass'=>$this->sCoreClasses.' '.$sCoreClasses,
                'basename'=>$this->sSGQA,
                'rowTemplate' => $rowTemplate,
                'dynamicTemplate' => $dynamicTemplate,
            ), 
            true
        );
        $this->registerAssets();
        // $inputnames[] = $this->sSGQA;
        return array($answer, $this->inputnames);
    }
}

/*
// -----------------------------------------------------------------
// @todo: Can remove DB query by passing in answer list from EM
function do_multiplenumeric($ia)
{
    global $thissurvey;
    $extraclass             = "";
    $aQuestionAttributes    = QuestionAttribute::model()->getQuestionAttributes($ia[0]);
    $sSeparator             = getRadixPointData($thissurvey['surveyls_numberformat']);
    $sSeparator             = $sSeparator['separator'];
    $extraclass            .= " numberonly"; //Must turn on the "numbers only javascript"
    $coreClass              = "ls-answers subquestion-list questions-list ";
    if (intval(trim($aQuestionAttributes['maximum_chars'])) > 0) {
        /* must be limited to 32 : -(10 number)dot(20 numbers) ! DECIMAL sql 
        $maxlength = intval(trim($aQuestionAttributes['maximum_chars'])); 
        $extraclass .= " ls-input-maxchars";
    } else {
        $maxlength = 20;
    }
    if (ctype_digit(trim($aQuestionAttributes['input_size']))) {
        $inputsize = trim($aQuestionAttributes['input_size']);
        $extraclass .= " ls-input-sized";
    } else {
        $inputsize = null;
    }

    if ($aQuestionAttributes['prefix'][$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']] != '') {
        $prefix      = $aQuestionAttributes['prefix'][$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']];
        $extraclass .= " withprefix";
    } else {
        $prefix = ''; /* slider js need it 
    }

    if ($aQuestionAttributes['suffix'][$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']] != '') {
        $suffix      = $aQuestionAttributes['suffix'][$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']];
        $extraclass .= " withsuffix";
    } else {
        $suffix = ''; /* slider js need it 
    }

    $kpclass = testKeypad($thissurvey['nokeyboard']); // Virtual keyboard (probably obsolete today)
    
    /* Find the col-sm width : if none is set : default, if one is set, set another one to be 12, if two is set : no change
    list($sLabelWidth, $sInputContainerWidth, $defaultWidth) = getLabelInputWidth($aQuestionAttributes['label_input_columns'], $aQuestionAttributes['text_input_width']);

    $prefixclass = "numeric";
    $sliders = 0;
    $slider_position = '';
    $slider_default_set = false;
    
    if ($aQuestionAttributes['slider_layout'] == 1) {
        $coreClass           .= " slider-list";
        $slider_layout        = true;
        $extraclass          .= " withslider";
        $slider_step          = trim(LimeExpressionManager::ProcessString("{{$aQuestionAttributes['slider_accuracy']}}", $ia[0], [], 1, 1, false, false, true));
        $slider_step          = (is_numeric($slider_step)) ? $slider_step : 1;
        $slider_min           = trim(LimeExpressionManager::ProcessString("{{$aQuestionAttributes['slider_min']}}", $ia[0], [], 1, 1, false, false, true));
        $slider_mintext       = $slider_min = (is_numeric($slider_min)) ? $slider_min : 0;
        $slider_max           = trim(LimeExpressionManager::ProcessString("{{$aQuestionAttributes['slider_max']}}", $ia[0], [], 1, 1, false, false, true));
        $slider_maxtext       = $slider_max = (is_numeric($slider_max)) ? $slider_max : 100;
        $slider_default       = trim(LimeExpressionManager::ProcessString("{{$aQuestionAttributes['slider_default']}}", $ia[0], [], 1, 1, false, false, true));
        $slider_default       = (is_numeric($slider_default)) ? $slider_default : "";
        $slider_default_set   = (bool) ($aQuestionAttributes['slider_default_set'] && $slider_default !== '');
        $slider_orientation   = (trim($aQuestionAttributes['slider_orientation']) == 0) ? 'horizontal' : 'vertical';
        $slider_custom_handle = (trim($aQuestionAttributes['slider_custom_handle']));

        switch (trim($aQuestionAttributes['slider_handle'])) {
            case 0:
                $slider_handle = 'round';
                break;

            case 1:
                $slider_handle = 'square';
                break;

            case 2:
                $slider_handle = 'triangle';
                break;

            case 3:
                $slider_handle = 'custom';
                break;
        }

        /* Put the slider init to initial state (when no click is set or when 'reset') 
        if ($slider_default !== '') {
            /* can be 0 
            $slider_position = $slider_default;
        } elseif ($aQuestionAttributes['slider_middlestart'] == 1) {
            $slider_position = intval(($slider_max + $slider_min) / 2);
        }

        $slider_separator = (trim($aQuestionAttributes['slider_separator']) != '') ? $aQuestionAttributes['slider_separator'] : "";
        $slider_reset = ($aQuestionAttributes['slider_reset']) ? 1 : 0;

        /* Slider reversed value 
        if ($aQuestionAttributes['slider_reversed'] == 1) {
            $slider_reversed = 'true';
        } else {
            $slider_reversed = 'false';
        }
    } else {
        $coreClass .= " text-list number-list";
        $slider_layout  = false;
        $slider_step    = '';
        $slider_min     = '';
        $slider_mintext = '';
        $slider_max     = '';
        $slider_maxtext = '';
        $slider_default = null;
        $slider_orientation = '';
        $slider_handle = '';
        $slider_custom_handle = '';
        $slider_separator = '';
        $slider_reset = 0;
        $slider_reversed = 'false';
    }


    if ($aQuestionAttributes['random_order'] == 1) {
        $sOrder = dbRandom();
    } else {
        $sOrder = 'question_order';
    }
    $aSubquestions = Question::model()->findAll(array('order'=>$sOrder, 'condition'=>'parent_qid=:parent_qid', 'params'=>array(':parent_qid'=>$ia[0])));
    $sSurveyLanguage = $_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang'];
    $anscount      = count($aSubquestions) * 2;
    $fn            = 1;
    $sRows         = "";

    $inputnames = [];

    if ($anscount == 0) {
        $answer = doRender('/survey/questions/answer/multiplenumeric/empty', [], true);
    } else {
        foreach ($aSubquestions as $aSubquestion) {
            $labelText = $sQuestionText = $aSubquestion->questionL10ns[$sSurveyLanguage]->question;
            $myfname   = $ia[1].$aSubquestion['title'];

            if ($sQuestionText == "") {
                $sQuestionText = "&nbsp;";
            }

            if ($slider_layout) {
                $sliderWidth = 12;
                if ($slider_separator != '') {
                    $aAnswer     = explode($slider_separator, $sQuestionText);
                    $theanswer   = (isset($aAnswer[0])) ? $aAnswer[0] : "";
                    $labelText   = $theanswer;
                    $sliderleft  = (isset($aAnswer[1])) ? $aAnswer[1] : null;
                    $sliderright = (isset($aAnswer[2])) ? $aAnswer[2] : null;
                    /* sliderleft and sliderright is in input, but is part of answers then take label width 
                    if (!empty($sliderleft)) {
                        $sliderWidth = 10;
                    }
                    if (!empty($sliderright)) {
                        $sliderWidth = $sliderWidth==10 ? 8 : 10 ;
                    }
                    $sliders   = true; // What is the usage ?
                } else {
                    $theanswer = $sQuestionText;
                    $sliders   = false;
                }
            } else {
                $theanswer = $sQuestionText;
                $sliders   = false;
            }

            $aAnswer     = (isset($aAnswer)) ? $aAnswer : '';
            $sliderleft  = (isset($sliderleft)) ? $sliderleft : null;
            $sliderright = (isset($sliderright)) ? $sliderright : null;

            // color code missing mandatory questions red
            $alert = '';

            if (($_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['step'] != $_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['maxstep']) || ($_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['step'] == $_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['prevstep'])) {
                if ($ia[6] == 'Y' && $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname] === '') {
                    $alert = true;
                }
            }

            //list($htmltbody2, $hiddenfield)=return_array_filter_strings($ia, $aQuestionAttributes, $thissurvey, $ansrow, $myfname, '', $myfname, "div","form-group question-item answer-item text-item numeric-item".$extraclass);
            $sDisplayStyle = return_display_style($ia, $aQuestionAttributes, $thissurvey, $myfname);

            // The value of the slider depends on many possible different parameters, by order of priority :
            // 1. The value stored in the session
            // 2. Else the default Answer   (set by EM and stored in session, so same case than 1)
            // 3. Else the slider_default value : if slider_default_set set the value here
            // 4. Else the middle start or slider_default or nothing : leave the value to "" for the input, show slider pos at this position
            if (isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname])) {
                $sValue                = $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname];
            } elseif ($slider_layout && $slider_default !== "" && $slider_default_set) {
                $sValue                = $slider_default;
            } else {
                $sValue                = null;
            }

            $sUnformatedValue = $sValue ? $sValue : '';

            if (strpos($sValue, ".")) {
                $sValue = rtrim(rtrim($sValue, "0"), ".");
                $sValue = str_replace('.', $sSeparator, $sValue);
            }

            if (trim($aQuestionAttributes['num_value_int_only']) == 1) {
                $extraclass .= " integeronly";
                $answertypeclass = " integeronly";
                $integeronly = 1;
            } else {
                $answertypeclass = "";
                $integeronly = 0;
            }

            if (!$slider_layout) {
                $sRows .= doRender('/survey/questions/answer/multiplenumeric/rows/input/answer_row', array(
                    'qid'                    => $ia[0],
                    'extraclass'             => $extraclass,
                    'answertypeclass'        => $answertypeclass,
                    'sDisplayStyle'          => $sDisplayStyle,
                    'kpclass'                => $kpclass,
                    'alert'                  => $alert,
                    'theanswer'              => $theanswer,
                    'labelname'              => 'answer'.$myfname,
                    'prefixclass'            => $prefixclass,
                    'prefix'                 => $prefix,
                    'suffix'                 => $suffix,
                    'sInputContainerWidth'   => $sInputContainerWidth,
                    'sLabelWidth'            => $sLabelWidth,
                    'inputsize'              => $inputsize,
                    'myfname'                => $myfname,
                    'dispVal'                => $sValue,
                    'maxlength'              => $maxlength,
                    'labelText'              => $labelText,
                    'integeronly'=> $integeronly,
                    ), true);
            } else {
                $sRows .= doRender('/survey/questions/answer/multiplenumeric/rows/sliders/answer_row', array(
                    'qid'                    => $ia[0],
                    'basename'               => $ia[1],
                    'extraclass'             => $extraclass,
                    'sDisplayStyle'          => $sDisplayStyle,
                    'kpclass'                => $kpclass,
                    'alert'                  => $alert,
                    'theanswer'              => $theanswer,
                    'labelname'              => 'answer'.$myfname,
                    'prefixclass'            => $prefixclass,
                    'sliders'                => $sliders,
                    'sliderleft'             => $sliderleft,
                    'sliderright'            => $sliderright,
                    'prefix'                 => $prefix,
                    'suffix'                 => $suffix,
                    'sInputContainerWidth'   => $sInputContainerWidth,
                    'sLabelWidth'            => $sLabelWidth,
                    'sliderWidth'            => $sliderWidth,
                    'inputsize'              => $inputsize,
                    'myfname'                => $myfname,
                    'dispVal'                => $sValue,
                    'maxlength'              => $maxlength,
                    'labelText'              => $labelText,
                    'slider_orientation'     => $slider_orientation,
                    'slider_value'           => $slider_position ?  $slider_position : $sUnformatedValue,
                    'slider_step'            => $slider_step,
                    'slider_min'             => $slider_min,
                    'slider_mintext'         => $slider_mintext,
                    'slider_max'             => $slider_max,
                    'slider_maxtext'         => $slider_maxtext,
                    'slider_position'        => $slider_position,
                    'slider_reset_set'       => $slider_default_set,
                    'slider_handle'          => (isset($slider_handle)) ? $slider_handle : '',
                    'slider_reset'           => $slider_reset,
                    'slider_reversed'        => $slider_reversed,
                    'slider_custom_handle'   => $slider_custom_handle,
                    'slider_showminmax'      => $aQuestionAttributes['slider_showminmax'],
                    'sSeparator'             => $sSeparator,
                    'sUnformatedValue'       => $sUnformatedValue,
                    'integeronly'=> $integeronly,
                    ), true);
            }
            $fn++;
            $inputnames[] = $myfname;

            //~ $aJsData=array(
            //~ 'slider_custom_handle'=>$slider_custom_handle
            //~ );
        }
        $displaytotal     = false;
        $equals_num_value = false;

        if (trim($aQuestionAttributes['equals_num_value']) != ''
        || trim($aQuestionAttributes['min_num_value']) != ''
        || trim($aQuestionAttributes['max_num_value']) != ''
        ) {
            $qinfo = LimeExpressionManager::GetQuestionStatus($ia[0]);

            if (trim($aQuestionAttributes['equals_num_value']) != '') {
                $equals_num_value = true;
            }
            $displaytotal = true;
        }

        // TODO: Slider and multiple-numeric input should really be two different question types
        $templateFile = $sliders ? 'answer' : 'answer_input';
        $answer = doRender('/survey/questions/answer/multiplenumeric/'.$templateFile, array(
            'sRows'            => $sRows,
            'coreClass'        => $coreClass,
            'prefixclass'      => $prefixclass,
            'equals_num_value' => $equals_num_value,
            'id'               => $ia[0],
            'basename'         => $ia[1],
            'suffix'           => $suffix,
            'sumRemainingEqn'  => (isset($qinfo)) ? $qinfo['sumRemainingEqn'] : '',
            'displaytotal'     => $displaytotal,
            'sumEqn'           => (isset($qinfo)) ? $qinfo['sumEqn'] : '',
            'prefix'           => $prefix, // Need to know this to place sum/remaining correctly
            'sInputContainerWidth'   => $sInputContainerWidth,
            'sLabelWidth'            => $sLabelWidth,
            ), true);
    }

    if ($aQuestionAttributes['slider_layout'] == 1) {
        /* Add some data for javascript 
        $sliderTranslation = array(
            'help'=>gT('Please click and drag the slider handles to enter your answer.')
        );
        App()->getClientScript()->registerScript("sliderTranslation", "var sliderTranslation=".json_encode($sliderTranslation).";\n", CClientScript::POS_BEGIN);
        App()->getClientScript()->registerPackage("question-numeric-slider");
    }

    return array($answer, $inputnames);
}*/