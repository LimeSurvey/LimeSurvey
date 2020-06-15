<?php

/**
 * abstract Class QuestionTypeRoot
 * The aFieldArray Array contains the following
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
 * {@inheritdoc}
 */
abstract class QuestionBaseRenderer extends StaticModel
{
    public $oQuestion;
    public $sSGQA;
    public $sHtml;
    public $bRenderDirect;
    public $bPreview;
    public $sCoreClass;
    public $checkconditionFunction = "checkconditions";

    protected $aFieldArray;
    protected $aQuestionAttributes;
    protected $aSurveySessionArray;
    protected $mSessionValue;
    protected $sLanguage;

    protected $aSubQuestions = [];
    protected $aAnswerOptions = [];

    protected $aPackages = [];
    protected $aScripts = [];
    protected $aScriptFiles = [];
    protected $aStyles = [];

    public function __construct($aFieldArray, $bRenderDirect = false)
    {
        $this->aFieldArray = $aFieldArray;
        $this->sSGQA = $this->aFieldArray[1];
        $this->oQuestion = Question::model()->findByPk($aFieldArray[0]);
        $this->bRenderDirect = $bRenderDirect;
        $this->sLanguage = $this->setDefaultIfEmpty(@$aFieldArray['language'], @$_SESSION['survey_'.$this->oQuestion->sid]['s_lang']);
        if(!$this->sLanguage) {
                $this->sLanguage = $this->oQuestion->survey->language;
        }

        $this->aQuestionAttributes = QuestionAttribute::model()->getQuestionAttributes($this->oQuestion->qid);
        $this->aSurveySessionArray = @$_SESSION['survey_'.$this->oQuestion->sid];
        $this->mSessionValue = @$this->setDefaultIfEmpty($this->aSurveySessionArray[$this->sSGQA], '');

        $oQuestionTemplate = QuestionTemplate::getNewInstance($this->oQuestion);
        $oQuestionTemplate->registerAssets(); // Register the custom assets of the question template, if needed

        if(!empty($this->oQuestion->questionl10ns[$this->sLanguage]->script)){
            $sScriptRendered = LimeExpressionManager::ProcessString($this->oQuestion->questionl10ns[$this->sLanguage]->script,$this->oQuestion->qid, ['QID' => $this->oQuestion->qid]);
            $this->addScript('QuestionStoredScript-'.$this->oQuestion->qid, $sScriptRendered, LSYii_ClientScript::POS_POSTSCRIPT);
        }
    }

    protected function getTimeSettingRender()
    {
        $oQuestion = $this->oQuestion;
        $oSurvey = $this->oQuestion->survey;

        Yii::app()->getClientScript()->registerScriptFile(Yii::app()->getConfig("generalscripts").'coookies.js', CClientScript::POS_BEGIN);
        Yii::app()->getClientScript()->registerPackage('timer-addition');

        $langTimer = array(
            'hours'=>gT("hours"),
            'mins'=>gT("mins"),
            'seconds'=>gT("seconds"),
        );
        /* Registering script : don't go to EM : no need usage of ls_json_encode */
        App()->getClientScript()->registerScript("LSVarLangTimer", "LSvar.lang.timer=".json_encode($langTimer).";", CClientScript::POS_BEGIN);
        /**
         * The following lines cover for previewing questions, because no $_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['fieldarray'] exists.
         * This just stops error messages occuring
         */
        if (!isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['fieldarray'])) {
            $_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['fieldarray'] = [];
        }
        /* End */

        //Used to count how many timer questions in a page, and ensure scripts only load once
        $_SESSION['survey_'.$oSurvey->sid]['timercount'] = (isset($_SESSION['survey_'.$oSurvey->sid]['timercount'])) ? $_SESSION['survey_'.$oSurvey->sid]['timercount']++ : 1;

        /* Work in all mode system : why disable it ? */
        //~ if ($thissurvey['format'] != "S")
        //~ {
        //~ if ($thissurvey['format'] != "G")
        //~ {
        //~ return "\n\n<!-- TIMER MODE DISABLED DUE TO INCORRECT SURVEY FORMAT -->\n\n";
        //~ //We don't do the timer in any format other than question-by-question
        //~ }
        //~ }

        //Render timer
        $timer_html = Yii::app()->twigRenderer->renderQuestion('/survey/questions/question_timer/timer', array('iQid'=>$oQuestion->qid, 'sWarnId'=>''), true);

        $time_limit = $oQuestion->questionattributes['time_limit']['value'];
        $disable_next = $this->setDefaultIfEmpty($oQuestion->questionattributes['time_limit_disable_next']['value'], 0);
        $disable_prev = $this->setDefaultIfEmpty($oQuestion->questionattributes['time_limit_disable_prev']['value'], 0);
        $time_limit_action = $this->setDefaultIfEmpty($oQuestion->questionattributes['time_limit_action']['value'], 1);
        $time_limit_message = $this->setDefaultIfEmpty($oQuestion->questionattributes['time_limit_message']['value'], gT("Your time to answer this question has expired"));
        $time_limit_warning = $this->setDefaultIfEmpty($oQuestion->questionattributes['time_limit_warning']['value'], 0);
        $time_limit_warning_2 = $this->setDefaultIfEmpty($oQuestion->questionattributes['time_limit_warning_2']['value'], 0);
        $time_limit_countdown_message = $this->setDefaultIfEmpty($oQuestion->questionattributes['time_limit_countdown_message']['value'], gT("Time remaining"));
        $time_limit_warning_message = $this->setDefaultIfEmpty($oQuestion->questionattributes['time_limit_warning_message']['value'], gT("Your time to answer this question has nearly expired. You have {TIME} remaining."));
        $time_limit_warning_message = str_replace("{TIME}", $timer_html, $time_limit_warning_message);
        $time_limit_warning_display_time = $this->setDefaultIfEmpty($oQuestion->questionattributes['time_limit_warning_display_time'], 0);
        $time_limit_warning_2_message = $this->setDefaultIfEmpty($oQuestion->questionattributes['time_limit_warning_2_message'], gT("Your time to answer this question has nearly expired. You have {TIME} remaining."));


        //Render timer 2
        $timer_html = Yii::app()->twigRenderer->renderQuestion(
            '/survey/questions/question_timer/timer',
            array('iQid'=>$ia[0], 'sWarnId'=>'_Warning_2'),
            true
        );

        $time_limit_message_delay = $this->setDefaultIfEmpty($oQuestion->questionattributes['time_limit_message_delay'], 1000);
        $time_limit_warning_2_message = str_replace("{TIME}", $timer_html, $time_limit_warning_2_message);
        $time_limit_warning_2_display_time = $this->setDefaultIfEmpty($oQuestion->questionattributes['time_limit_warning_2_display_time'], 0);
        $time_limit_message_style = $this->setDefaultIfEmpty($oQuestion->questionattributes['time_limit_message_style'], '');
        $time_limit_message_class = "hidden ls-timer-content ls-timer-message ls-no-js-hidden";
        $time_limit_warning_style = $this->setDefaultIfEmpty($oQuestion->questionattributes['time_limit_warning_style'], '');
        $time_limit_warning_class = "hidden ls-timer-content ls-timer-warning ls-no-js-hidden";
        $time_limit_warning_2_style = $this->setDefaultIfEmpty($oQuestion->questionattributes['time_limit_warning_2_style'], '');
        $time_limit_warning_2_class = "hidden ls-timer-content ls-timer-warning2 ls-no-js-hidden";
        $time_limit_timer_style = $this->setDefaultIfEmpty($oQuestion->questionattributes['time_limit_timer_style'], '');
        $time_limit_timer_class = "ls-timer-content ls-timer-countdown ls-no-js-hidden";

        $timersessionname = "timer_question_".$oQuestion->qid;
        if (isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$timersessionname])) {
            $time_limit = $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$timersessionname];
        }

        $output = Yii::app()->twigRenderer->renderQuestion(
            '/survey/questions/question_timer/timer_header',
            array('timersessionname'=>$timersessionname, 'time_limit'=>$time_limit),
            true
        );

        if ($_SESSION['survey_'.$oSurvey->sid]['timercount'] < 2) {
            $iAction = '';
            if ($oSurvey->format == "G") {
                $qcount = 0;
                foreach ($_SESSION['survey_'.$oSurvey->sid]['fieldarray'] as $ib) {
                    if ($ib[5] == $oQuestion->gid) {
                        $qcount++;
                    }
                }
                // Override all other options and just allow freezing, survey is presented in group by group mode
                // Why don't allow submit in Group by group mode, this surely broke 'mandatory' question, but this remove a great system for user (Denis 140224)
                if ($qcount > 1) {
                    $iAction = '3';
                }
            }

            /* If this is a preview, don't allow the page to submit/reload */
            $thisaction = returnglobal('action');
            if ($thisaction == "previewquestion" || $thisaction == "previewgroup" || $this->bPreview == true) {
                $iAction = '3';
            }

            $output .= Yii::app()->twigRenderer->renderQuestion('/survey/questions/question_timer/timer_javascript', array(
                'timersessionname'=>$timersessionname,
                'time_limit'=>$time_limit,
                'iAction'=>$iAction,
                'disable_next'=>$disable_next,
                'disable_prev'=>$disable_prev,
                'time_limit_countdown_message' =>$time_limit_countdown_message,
                'time_limit_message_delay' => $time_limit_message_delay
                ), true);
        }

        $output .= Yii::app()->twigRenderer->renderQuestion(
            '/survey/questions/question_timer/timer_content',
            array(
                'iQid'=>$oQuestion->qid,
                'time_limit_message_style'=>$time_limit_message_style,
                'time_limit_message_class'=>$time_limit_message_class,
                'time_limit_message'=>$time_limit_message,
                'time_limit_warning_style'=>$time_limit_warning_style,
                'time_limit_warning_class'=>$time_limit_warning_class,
                'time_limit_warning_message'=>$time_limit_warning_message,
                'time_limit_warning_2_style'=>$time_limit_warning_2_style,
                'time_limit_warning_2_class'=>$time_limit_warning_2_class,
                'time_limit_warning_2_message'=>$time_limit_warning_2_message,
                'time_limit_timer_style'=>$time_limit_timer_style,
                'time_limit_timer_class'=>$time_limit_timer_class,
            ),
            true
        );

        $output .= Yii::app()->twigRenderer->renderQuestion(
            '/survey/questions/question_timer/timer_footer',
            array(
                'iQid'=>$oQuestion->qid,
                'time_limit'=>$time_limit,
                'time_limit_action'=>$time_limit_action,
                'time_limit_warning'=>$time_limit_warning,
                'time_limit_warning_2'=>$time_limit_warning_2,
                'time_limit_warning_display_time'=>$time_limit_warning_display_time,
                'time_limit_warning_2_display_time'=>$time_limit_warning_2_display_time,
                'disable'=>$disable,
            ),
            true
        );
        return $output;
    }

    protected function getQuestionAttribute($key1, $key2=null) {
        $result =  isset($this->aQuestionAttributes[$key1]) ? $this->aQuestionAttributes[$key1] : null;
        if($key2 !== null && $result !== null) {
            $result =  isset($result[$key2]) ? $result[$key2] : null;
        }
        return $result;
    }

    protected function setSubquestions($scale_id = null)
    {

        $this->aSubQuestions = $this->oQuestion->getOrderedSubQuestions($scale_id);
    }

    protected function setAnsweroptions($scale_id = null)
    {
        $this->aAnswerOptions = $this->oQuestion->getOrderedAnswers($scale_id);
    }

    protected function getAnswerCount($iScaleId=0)
    {
        return count($this->aAnswerOptions[$iScaleId]);
    }

    protected function getQuestionCount($iScaleId=0)
    {
        return count($this->aSubQuestions[$iScaleId]);
    }

    protected function getFromSurveySession($sIndex, $default="")
    {
        return isset($_SESSION['survey_'.$this->oQuestion->sid][$sIndex])
            ? $_SESSION['survey_'.$this->oQuestion->sid][$sIndex]
            : $default;
    }

    protected function applyPackages()
    {
        foreach ($this->aPackages as $sPackage) {
            Yii::app()->getClientScript()->registerPackage($sPackage);
        }
    }

    protected function addScript($name, $content, $position = LSYii_ClientScript::POS_BEGIN, $appendId = false)
    {
        $this->aScripts[] = [
            'name' => $name.($appendId ? '_'.$this->oQuestion->qid : ''),
            'content' => $content,
            'position' => $position
        ];
    }

    protected function applyScripts()
    {
        foreach ($this->aScripts as $aScript) {
            Yii::app()->getClientScript()->registerScript($aScript['name'], $aScript['content'], $aScript['position']);
        }
    }
    protected function applyScriptfiles()
    {
        foreach ($this->aScriptFiles as $aScriptFile) {
            Yii::app()->getClientScript()->registerScriptFile($aScriptFile['path'], $aScriptFile['position']);
        }
    }

    protected function applyStyles()
    {
        foreach ($this->aStyles as $aStyle) {
            Yii::app()->getClientScript()->registerCss($aStyle['name'], $aStyle['content']);
        }
    }

    protected function setDefaultIfEmpty($value, $default)
    {
        if(!$value) {
            return $default;
        }
        return trim($value) == '' ? $default : $value;
    }

    protected function registerAssets()
    {
        $this->applyPackages();
        $this->applyScripts();
        $this->applyScriptfiles();
        $this->applyStyles();
    }

    /**
    * Return class of a specific row (hidden by relevance)
    * @param string $myfname The name of the question/row to test
    * @return string
    */
    public function getCurrentRelevecanceClass($myfname)
    {
        $aSurveySessionArray = $_SESSION["survey_{$this->oQuestion->sid}"];
        $relevanceStatus = !isset($aSurveySessionArray['relevanceStatus'][$myfname]) || $aSurveySessionArray['relevanceStatus'][$myfname];
        if ($relevanceStatus) {
            return "";
        }

        $sExcludeAllOther = $this->setDefaultIfEmpty($this->getQuestionAttribute('exclude_all_others'), false);
        /* EM don't set difference between relevance in session, if exclude_all_others is set , just ls-disabled */
        if ($sExcludeAllOther !== false) {
            foreach (explode(';', $sExcludeAllOther) as $sExclude) {
                $sExclude = $this->sSGQA.$sExclude;
                if ((!isset($aSurveySessionArray['relevanceStatus'][$sExclude]) || $aSurveySessionArray['relevanceStatus'][$sExclude])
                && (isset($aSurveySessionArray[$sExclude]) && $aSurveySessionArray[$sExclude] == "Y")
                ) {
                    return "ls-irrelevant ls-disabled";
                }
            }
        }

        // Currently null/0/false=> hidden , 1 : disabled
        $filterStyle = !empty($this->aQuestionAttributes['array_filter_style']);
        return ($filterStyle) ?  "ls-irrelevant ls-disabled" : "ls-irrelevant ls-hidden";
    }
    /**
    * Find the label / input width
    * @param string|int $labelAttributeWidth label width from attribute
    * @param string|int $inputAttributeWidth input width from attribute
    * @return array labelWidth as integer,inputWidth as integer,defaultWidth as boolean
    */
    public function getLabelInputWidth()
    {
        $labelAttributeWidth = trim($this->getQuestionAttribute('label_input_columns'));
        $inputAttributeWidth = trim($this->getQuestionAttribute('text_input_columns'));

        $attributeInputContainerWidth = intval($inputAttributeWidth);
        if ($attributeInputContainerWidth < 1 || $attributeInputContainerWidth > 12) {
            $attributeInputContainerWidth = null;
        }

        $attributeLabelWidth =  ($labelAttributeWidth === 'hidden')
            ? 0
            : (
                ($labelAttributeWidth < 1 || $labelAttributeWidth > 12)
                ? null
                : intval($labelAttributeWidth)
            );

        if ($attributeInputContainerWidth === null && $attributeLabelWidth === null) {
            $sInputContainerWidth = 8;
            $sLabelWidth = 4;
            $defaultWidth = true;
        } else {
            if ($attributeInputContainerWidth !== null) {
                $sInputContainerWidth = $attributeInputContainerWidth;
            } elseif ($attributeLabelWidth == 12) {
                $sInputContainerWidth = 12;
            } else {
                $sInputContainerWidth = 12 - $attributeLabelWidth;
            }

            if (!is_null($attributeLabelWidth)) {
                $sLabelWidth = $attributeLabelWidth;
            } elseif ($attributeInputContainerWidth == 12) {
                $sLabelWidth = 12;
            } else {
                $sLabelWidth = 12 - $attributeInputContainerWidth;
            }

            $defaultWidth = false;
        }
        return array(
            'sLabelWidth' => $sLabelWidth,
            'sInputContainerWidth' => $sInputContainerWidth,
            'defaultWidth' => $defaultWidth,
        );
    }

    /**
    * Include Keypad headers
    */
    public function includeKeypad()
    {
        Yii::app()->getClientScript()->registerCssFile(Yii::app()->getConfig('third_party')."jquery-keypad/jquery.keypad.alt.css");

        $this->aScriptFiles[] = ['path' => Yii::app()->getConfig('third_party').'jquery-keypad/jquery.plugin.min.js', 'position' => LSYii_ClientScript::POS_BEGIN];
        $this->aScriptFiles[] = ['path' => Yii::app()->getConfig('third_party').'jquery-keypad/jquery.keypad.min.js', 'position' => LSYii_ClientScript::POS_BEGIN];
        $localefile = Yii::app()->getConfig('rootdir').'/third_party/jquery-keypad/jquery.keypad-'.App()->language.'.js';
        if (App()->language != 'en' && file_exists($localefile)) {
            $this->aScriptFiles[] = ['path' => Yii::app()->getConfig('third_party').'jquery-keypad/jquery.keypad-'.App()->language.'.js', 'position' => LSYii_ClientScript::POS_BEGIN];
        }
    }

    abstract public function getMainView();
    abstract public function getRows();
    abstract public function render();
}
