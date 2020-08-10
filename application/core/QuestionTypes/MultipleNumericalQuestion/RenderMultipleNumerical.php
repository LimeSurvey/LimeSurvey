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
        $this->useSliderLayout = $this->getQuestionAttribute('slider_layout') == 1; 
        
        $this->widthArray = $this->getLabelInputWidth();
        $this->extraclass   .= " numberonly";

        if (intval($this->setDefaultIfEmpty($this->getQuestionAttribute('maximum_chars'), 0)) > 0) {
            // Only maxlength attribute, use textarea[maxlength] jquery selector for textarea
            $this->maxlength = intval(trim($this->getQuestionAttribute('maximum_chars')));
            $this->extraclass .= " ls-input-maxchars";
        }

        if (ctype_digit(trim($this->getQuestionAttribute('input_size')))) {
            $this->inputsize = trim($this->getQuestionAttribute('input_size'));
            $this->extraclass .= " ls-input-sized";
        }

        if ($this->useSliderLayout) {
            $this->sCoreClasses  .= " slider-list";
            $this->extraclass   .= " withslider";
            $this->sliderOptionsArray = [
                'slider_step'          => trim(LimeExpressionManager::ProcessString("{{$this->getQuestionAttribute('slider_accuracy')}}", $this->oQuestion->qid, [], 1, 1, false, false, true)),
                'slider_min'           => trim(LimeExpressionManager::ProcessString("{{$this->getQuestionAttribute('slider_min')}}", $this->oQuestion->qid, [], 1, 1, false, false, true)),
                'slider_max'           => trim(LimeExpressionManager::ProcessString("{{$this->getQuestionAttribute('slider_max')}}", $this->oQuestion->qid, [], 1, 1, false, false, true)),
                'slider_default'       => trim(LimeExpressionManager::ProcessString("{{$this->getQuestionAttribute('slider_default')}}", $this->oQuestion->qid, [], 1, 1, false, false, true)),
                'slider_orientation'   => (trim($this->getQuestionAttribute('slider_orientation')) == 0) ? 'horizontal' : 'vertical',
                'slider_custom_handle' => (trim($this->getQuestionAttribute('slider_custom_handle'))),
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
            $this->sliderOptionsArray['slider_handle']  = $this->handleOptions[(trim($this->getQuestionAttribute('slider_handle')))];
            $this->sliderOptionsArray['slider_default_set'] = (bool) ($this->getQuestionAttribute('slider_default_set') && $this->sliderOptionsArray['slider_default'] !== '');

            // Put the slider init to initial state (when no click is set or when 'reset') 
            if ($this->sliderOptionsArray['slider_default'] !== '') {
                $this->sliderOptionsArray['slider_position'] = $this->sliderOptionsArray['slider_default'];
            } elseif ($this->getQuestionAttribute('slider_middlestart') == 1) {
                $this->sliderOptionsArray['slider_position'] = intval(($this->sliderOptionsArray['slider_max'] + $this->sliderOptionsArray['slider_min']) / 2);
            }
            
            $this->sliderOptionsArray['slider_separator'] = $this->setDefaultIfEmpty($this->getQuestionAttribute('slider_separator'),"");
            $this->sliderOptionsArray['slider_reset'] = ($this->getQuestionAttribute('slider_reset')) ? 1 : 0;
    
            // Slider reversed value 
            if ($this->getQuestionAttribute('slider_reversed') == 1) {
                $this->sliderOptionsArray['slider_reversed'] = 'true';
            } else {
                $this->sliderOptionsArray['slider_reversed'] = 'false';
            }

            $this->sliderOptionsArray['slider_showminmax'] = $this->getQuestionAttribute('slider_showminmax');

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
        $sPrefix = $this->setDefaultIfEmpty($this->getQuestionAttribute('prefix', $this->sLanguage), '');
        if ($sPrefix != '') {
            $this->prefix = $sPrefix;
            $this->extraclass .= " withprefix";
        }
        
        $sSuffix = $this->setDefaultIfEmpty($this->getQuestionAttribute('suffix', $this->sLanguage), '');
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
            $sSubquestionText = $this->setDefaultIfEmpty($oSubquestion->questionl10ns[$this->sLanguage]->question, "&nbsp;");
            $labelText = $sSubquestionText;

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
                    'labelText'              => $labelText,
                    'labelname'              => 'answer'.$myfname,
                    'myfname'                => $myfname,
                    'dispVal'                => $dispVal,
                    'extraclass'             => $this->extraclass,
                    'qid'                    => $this->oQuestion->qid,
                    'answertypeclass'        => $this->getQuestionAttribute('num_value_int_only') ? 'integeronly' : '',
                    'prefix'                 => $this->prefix,
                    'suffix'                 => $this->suffix,
                    'sInputContainerWidth'   => $this->widthArray['sInputContainerWidth'],
                    'sLabelWidth'            => $this->widthArray['sLabelWidth'],
                    'inputsize'              => $this->inputsize,
                    'maxlength'              => $this->maxlength,
                    'integeronly'            => $this->getQuestionAttribute('num_value_int_only'),
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
                    $sValue = str_replace('.', $this->sSeparator, $sValue);
                }

                $aRows[] = array_merge(
                    array(
                        'sDisplayStyle'          => '',
                        'prefixclass'            => 'numeric',
                        'sliders'                => true,
                        'labelname'              => 'answer'.$myfname,
                        'alert'                  => $alert,
                        'theanswer'              => $theanswer,
                        'labelText'              => $labelText,
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
                        'integeronly'            => $this->getQuestionAttribute('num_value_int_only'),
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

        $displaytotal     = false;
        $equals_num_value = false;
        if (trim($this->getQuestionAttribute('equals_num_value')) != ''
        || trim($this->getQuestionAttribute('min_num_value')) != ''
        || trim($this->getQuestionAttribute('max_num_value')) != ''
        ) {
            $qinfo = LimeExpressionManager::GetQuestionStatus($this->oQuestion->qid);

            $sumRemainingEqn = LimeExpressionManager::ProcessString('{'.$qinfo['sumRemainingEqn'].'}', $this->oQuestion->qid);
            $sumEqn = LimeExpressionManager::ProcessString('{'.$qinfo['sumEqn'].'}', $this->oQuestion->qid);

            if (trim($this->getQuestionAttribute('equals_num_value')) != '') {
                $equals_num_value = true;
            }
            $displaytotal = true;
        }

        $answer .= Yii::app()->twigRenderer->renderQuestion(
            $this->getMainView().'/answer',
            array(
                'aRows' => $this->getRows(),
                'coreClass'=>$this->sCoreClasses.' '.$sCoreClasses,
                'basename'=>$this->sSGQA,
                'rowTemplate' => $rowTemplate,
                'dynamicTemplate' => $dynamicTemplate,
                'id' => $this->oQuestion->qid,
                'sumRemainingEqn' => $equals_num_value ? $sumRemainingEqn : '',
                'sumEqn' => $displaytotal ? $sumEqn : '',
                'sLabelWidth' => $this->widthArray['sLabelWidth'],
                'sInputContainerWidth' => $this->widthArray['sInputContainerWidth'],
                'prefix' => $this->prefix,
                'suffix' => $this->suffix,
            ), 
            true
        );
        $this->registerAssets();
        // $inputnames[] = $this->sSGQA;
        return array($answer, $this->inputnames);
    }
}
