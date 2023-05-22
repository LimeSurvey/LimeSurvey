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

    /** @var boolean indicates if the question has the 'Other' option enabled */
    protected $hasOther = false;

    /** @var int the position where the 'Other' option should be placed. Possible values: 0 (Before no answer), 1 (At beginning), 2 (At end), 3 (After specific option)*/
    protected $otherPosition;

    /** @var string the code of the answer after which the 'Other' option should be placed (if $otherPosition == 3) */
    protected $answerBeforeOther;

    /** @var boolean indicates if 'Other' option has already been rendered */
    protected $otherRendered = false;
    
    private $iRowNum = 0;

    const OTHER_POS_BEFORE_NOANSWER = 'default';
    const OTHER_POS_START = 'beginning';
    const OTHER_POS_END = 'end';
    const OTHER_POS_AFTER_OPTION = 'specific';

    public function __construct($aFieldArray, $bRenderDirect = false)
    {
        parent::__construct($aFieldArray, $bRenderDirect);
        // Question attribute variables
        $this->othertext              = $this->setDefaultIfEmpty($this->getQuestionAttribute('other_replace_text', $this->sLanguage), gT('Other:'));
        $this->optCategorySeparator   = @$this->setDefaultIfEmpty($this->getQuestionAttribute('category_separator'), false);
        $this->sCoreClass             = "ls-answers answer-item";
        $this->bPrefix                = @(sanitize_int($this->getQuestionAttribute('dropdown_prefix')) == 1);
        $this->hasOther               = $this->oQuestion->other == 'Y';
        $this->otherPosition          = $this->setDefaultIfEmpty($this->getQuestionAttribute('other_position'), self::OTHER_POS_BEFORE_NOANSWER);
        $this->answerBeforeOther = '';
        if ($this->hasOther && $this->otherPosition == self::OTHER_POS_AFTER_OPTION) {
            $this->answerBeforeOther = $this->getQuestionAttribute('other_position_code');
        }
        $this->setAnsweroptions();
    }

    public function getRows()
    {
        $sOptions = '';
        $this->otherRendered = false;

        // If no answer previously selected
        if (!$this->mSessionValue || $this->mSessionValue === '') {
            $sOptions .= Yii::app()->twigRenderer->renderQuestion($this->getMainView() . '/rows/option', array(
                'name' => $this->sSGQA,
                'value' => '',
                'opt_select' => SELECTED,
                'answer' => gT('Please choose...')
                ), true);
        }

        if ($this->hasOther && $this->otherPosition == self::OTHER_POS_START) {
            $sOptions .= $this->getOtherOption();
            $this->otherRendered = true;
        }

        if ($this->optCategorySeparator !== false) {
            $sOptions .= $this->getOptGroupRows($sOptions);
        } else {
            foreach ($this->aAnswerOptions[0] as $oAnsweroption) {
                $opt_select = $this->mSessionValue == $oAnsweroption->code ? SELECTED : '';

                $_prefix = $this->bPrefix ? ++$this->iRowNum . ') ' : '';
                
                $sOptions .= Yii::app()->twigRenderer->renderQuestion($this->getMainView() . '/rows/option', array(
                    'name' => $this->sSGQA,
                    'value' => $oAnsweroption->code,
                    'opt_select' => $opt_select,
                    'answer' => $_prefix . $oAnsweroption->answerl10ns[$this->sLanguage]->answer,
                    ), true);

                if ($this->hasOther && $this->otherPosition == self::OTHER_POS_AFTER_OPTION && $this->answerBeforeOther == $oAnsweroption->code) {
                    $sOptions .= $this->getOtherOption();
                    $this->otherRendered = true;
                }
            }

            $sOptions .= $this->getNoAnswerOption();
        }

        if ($this->hasOther && !$this->otherRendered) {
            $sOptions .= $this->getOtherOption();
        }

        return $sOptions;
    }

    public function getOptGroupRows()
    {
        $sOptions = '';
        $defaultopts = [];
        $optgroups = [];

        foreach ($this->aAnswerOptions[0] as $oAnsweroption) {
            // Let's sort answers in an array indexed by subcategories
            @list($categorytext, $answertext) = explode($this->optCategorySeparator, (string) $oAnsweroption->answerl10ns[$this->sLanguage]->answer);
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
                $sOptGroupOptions .= Yii::app()->twigRenderer->renderQuestion($this->getMainView() . '/rows/option', array(
                    'name' => $this->sSGQA,
                    'value' => $optionarray['code'],
                    'opt_select' => ($this->mSessionValue == $optionarray['code'] ? SELECTED : ''),
                    'answer' => flattenText($optionarray['answer'])
                    ), true);
                if ($this->hasOther && $this->otherPosition == self::OTHER_POS_AFTER_OPTION && $this->answerBeforeOther == $optionarray['code']) {
                    $sOptGroupOptions .= $this->getOtherOption();
                    $this->otherRendered = true;
                }
            }

            $sOptions .= Yii::app()->twigRenderer->renderQuestion($this->getMainView() . '/rows/optgroup', array(
                'categoryname'      => flattenText($categoryname),
                'sOptGroupOptions'  => $sOptGroupOptions,
                ), true);
        }

        foreach ($defaultopts as $optionarray) {
            $sOptions .= Yii::app()->twigRenderer->renderQuestion($this->getMainView() . '/rows/option', array(
                'name' => $this->sSGQA,
                'value' => $optionarray['code'],
                'opt_select' => ($this->mSessionValue == $optionarray['code'] ? SELECTED : ''),
                'answer' => flattenText($optionarray['answer'])
                ), true);
            if ($this->hasOther && $this->otherPosition == self::OTHER_POS_AFTER_OPTION && $this->answerBeforeOther == $optionarray['code']) {
                $sOptions .= $this->getOtherOption();
                $this->otherRendered = true;
            }
        }
        return $sOptions;
    }

    public function getOtherOption()
    {
        $_prefix = $this->bPrefix ? ++$this->iRowNum . ') ' : '';
        return Yii::app()->twigRenderer->renderQuestion($this->getMainView() . '/rows/option', array(
                'name' => $this->sSGQA,
                'classes' => 'other-item',
                'value' => '-oth-',
                'opt_select' => ($this->mSessionValue == '-oth-' ? SELECTED : ''),
                'answer' => flattenText($_prefix . $this->othertext)
                ), true);
    }

    public function getOtherInput()
    {
        return Yii::app()->twigRenderer->renderQuestion($this->getMainView() . '/rows/othertext', [
                'name' => $this->sSGQA,
                'checkconditionFunction' => $this->checkconditionFunction,
                'display' => $this->mSessionValue != '-oth-' ? 'display: none;' : '',
                'label' => $this->othertext,
                'value' => (isset($_SESSION['survey_' . Yii::app()->getConfig('surveyID')][$this->sSGQA . "other"]))
                    ? htmlspecialchars((string) $_SESSION['survey_' . Yii::app()->getConfig('surveyID')][$this->sSGQA . "other"], ENT_QUOTES)
                    : ''
            ], true);
    }

    public function getNoAnswerOption()
    {
        /** @var string the HTML for the options */
        $options = '';
        if (!(is_null($this->mSessionValue) || $this->mSessionValue === "") && ($this->oQuestion->mandatory != 'Y' && $this->oQuestion->mandatory != 'S') && SHOW_NO_ANSWER == 1) {
            if ($this->hasOther && $this->otherPosition == self::OTHER_POS_BEFORE_NOANSWER) {
                $options .= $this->getOtherOption();
                $this->otherRendered = true;
            }
            $_prefix = $this->bPrefix ? ++$this->iRowNum . ') ' : '';
            $options .= Yii::app()->twigRenderer->renderQuestion($this->getMainView() . '/rows/option', array(
                'name' => $this->sSGQA,
                'classes' => 'noanswer-item',
                'value' => '',
                'opt_select' => '', // Never selected
                'answer' => $_prefix . gT('No answer')
            ), true);
        }
        return $options;
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
        $this->sCoreClass = $this->sCoreClass . ' ' . $sCoreClasses;
        
        $sOptions = $this->getRows();
        if ($this->hasOther == 'Y') {
            $sOther = $this->getOtherInput();
            $inputnames[] = $this->sSGQA . 'other';
        }

        $answer =  Yii::app()->twigRenderer->renderQuestion($this->getMainView() . '/answer', array(
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
