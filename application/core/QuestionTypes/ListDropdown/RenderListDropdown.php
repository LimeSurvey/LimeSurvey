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
    protected $optCategorySeparator;
    protected $bPrefix;
    
    private $iRowNum = 0;

    public function __construct($aFieldArray, $bRenderDirect = false)
    {
        parent::__construct($aFieldArray, $bRenderDirect);
        // Question attribute variables
        $this->othertext              = $this->setDefaultIfEmpty($this->getQuestionAttribute('other_replace_text', $this->sLanguage), gT('Other:'));
        $this->optCategorySeparator   = @$this->setDefaultIfEmpty($this->getQuestionAttribute('category_separator'), false);
        $this->sCoreClass             = "ls-answers answer-item dropdown-item";
        $this->bPrefix                = @(sanitize_int($this->getQuestionAttribute('dropdown_prefix')) == 1);
        $this->setAnsweroptions();
    }

    public function getRows()
    {
        $sOptions = '';

        // If no answer previously selected
        if (!$this->mSessionValue || $this->mSessionValue === '') {
            $sOptions .= Yii::app()->twigRenderer->renderQuestion($this->getMainView().'/rows/option', array(
                'name'=> $this->sSGQA,
                'value'=>'',
                'opt_select'=> SELECTED,
                'answer'=>gT('Please choose...')
                ), true);
        }

        if ($this->optCategorySeparator !== false) {
            return $this->getOptGroupRows($sOptions);
        }

        foreach ($this->aAnswerOptions[0] as $oAnsweroption) {
            $opt_select = $this->mSessionValue == $oAnsweroption->code ? SELECTED : '';

            $_prefix = $this->bPrefix ? ++$this->iRowNum.') ': '';
            
            $sOptions .= Yii::app()->twigRenderer->renderQuestion($this->getMainView().'/rows/option', array(
                'name'=> $this->sSGQA,
                'value'=>$oAnsweroption->code,
                'opt_select'=>$opt_select,
                'answer'=>$_prefix.$oAnsweroption->answerl10ns[$this->sLanguage]->answer,
                ), true);
        }

        return $sOptions;
    }

    public function getOptGroupRows($sOptions)
    {
        $defaultopts = [];
        $optgroups = [];

        foreach ($this->aAnswerOptions[0] as $oAnsweroption) {
            // Let's sort answers in an array indexed by subcategories
            @list($categorytext, $answertext) = explode($this->optCategorySeparator, $oAnsweroption->answerl10ns[$this->sLanguage]->answer);
            // The blank category is left at the end outside optgroups
            if ($categorytext == '' || $answertext == '') {
                $defaultopts[] = array('code' => $oAnsweroption->code, 'answer' => $oAnsweroption->answerl10ns[$this->sLanguage]->answer);
            } else {
                $optgroups[$categorytext][] = array('code' => $oAnsweroption->code, 'answer' => $answertext);
            }
        }

        foreach ($optgroups as $categoryname => $optionlistarray) {
            $sOptGroupOptions = '';
            foreach ($optionlistarray as $optionarray) {
                // ==> rows
                $sOptGroupOptions .= Yii::app()->twigRenderer->renderQuestion($this->getMainView().'/rows/option', array(
                    'name'=> $this->sSGQA,
                    'value'=>$optionarray['code'],
                    'opt_select'=>($this->mSessionValue == $optionarray['code'] ? SELECTED : ''),
                    'answer'=>flattenText($optionarray['answer'])
                    ), true);
            }

            $sOptions .= Yii::app()->twigRenderer->renderQuestion($this->getMainView().'/rows/optgroup', array(
                'categoryname'      => flattenText($categoryname),
                'sOptGroupOptions'  => $sOptGroupOptions,
                ), true);
        }

        foreach ($defaultopts as $optionarray) {
            $sOptions .= Yii::app()->twigRenderer->renderQuestion($this->getMainView().'/rows/option', array(
                'name'=> $this->sSGQA,
                'value'=>$optionarray['code'],
                'opt_select'=>($this->mSessionValue == $optionarray['code'] ? SELECTED : ''),
                'answer'=>flattenText($optionarray['answer'])
                ), true);
        }
        return $sOptions;
    }

    public function getOtherOption()
    {
        $_prefix = $this->bPrefix ? ++$this->iRowNum.') ' : '';
        return Yii::app()->twigRenderer->renderQuestion($this->getMainView().'/rows/option', array(
                'name'=> $this->sSGQA,
                'classes'=>'other-item',
                'value'=>'-oth-',
                'opt_select'=>($this->mSessionValue == '-oth-' ? SELECTED : ''),
                'answer'=>flattenText($_prefix.$this->othertext)
                ), true);
    }

    public function getOtherInput()
    {
        return Yii::app()->twigRenderer->renderQuestion($this->getMainView().'/rows/othertext', [
                'name' => $this->sSGQA,
                'checkconditionFunction' => $this->checkconditionFunction,
                'display' => $this->mSessionValue != '-oth-' ? 'display: none;' : '',
                'label' => $this->othertext,
                'value' => (isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$this->sSGQA."other"]))
                    ? htmlspecialchars($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$this->sSGQA."other"], ENT_QUOTES)
                    : ''
            ], true);
    }

    public function getNoAnswerOption()
    {
        if (!(is_null($this->mSessionValue) || $this->mSessionValue === "") && ($this->oQuestion->mandatory != 'Y' && $this->oQuestion->mandatory != 'S') && SHOW_NO_ANSWER == 1) {
            $_prefix = $this->bPrefix ? ++$this->iRowNum.') ' : '';
            return Yii::app()->twigRenderer->renderQuestion($this->getMainView().'/rows/option', array(
                'name'=> $this->sSGQA,
                'classes'=>'noanswer-item',
                'value'=>'',
                'opt_select'=> '', // Never selected
                'answer'=>$_prefix.gT('No answer')
            ), true);
        }
        return '';
    }

    public function getDropdownSize()
    {
        if ($this->getQuestionAttribute('dropdown_size') !== null && $this->getQuestionAttribute('dropdown_size') > 0) {
            $_height    = sanitize_int($this->getQuestionAttribute('dropdown_size'));
            $_maxHeight = $this->getAnswerCount();
    
            if ((!$this->mSessionValue || $this->mSessionValue === '') && ($this->oQuestion->mandatory != 'Y' && $this->oQuestion->mandatory != 'S') && SHOW_NO_ANSWER == 1) {
                ++$_maxHeight; // for No Answer
            }
    
            if ($this->oQuestion->other == 'Y') {
                ++$_maxHeight; // for Other
            }
    
            if (is_null($this->mSessionValue)) {
                ++$_maxHeight; // for 'Please choose:'
            }
    
            if ($_height > $_maxHeight) {
                $_height = $_maxHeight;
            }
            return $_height;
        }

        return null;
    }

    public function getMainView()
    {
        return '/survey/questions/answer/list_dropdown';
    }
    public function render($sCoreClasses = '')
    {
        $inputnames = [];
        $sOther = '';
        $this->sCoreClass = $this->sCoreClass.' '.$sCoreClasses;
        
        $sOptions = $this->getRows();
        if ($this->oQuestion->other == 'Y') {
            $sOther = $this->getOtherInput();
            ;
            $sOptions .= $this->getOtherOption();
            $inputnames[] = $this->sSGQA.'other';
        }
        
        $sOptions .= $this->getNoAnswerOption();

        $answer =  Yii::app()->twigRenderer->renderQuestion($this->getMainView().'/answer', array(
            'sOptions'               => $sOptions,
            'sOther'                 => $sOther,
            'name'                   => $this->sSGQA,
            'basename'               => $this->sSGQA,
            'dropdownSize'           => $this->getDropdownSize(),
            'checkconditionFunction' => $this->checkconditionFunction,
            'value'                  => $this->mSessionValue,
            'coreClass'              => $this->sCoreClass
            ), true);

        $inputnames[] = $this->sSGQA;

        if (!empty($this->getQuestionAttribute('time_limit'))) {
            $answer .= $this->getTimeSettingRender();
        }
        $this->registerAssets();
        return array($answer, $inputnames);
    }
}
