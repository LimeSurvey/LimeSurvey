<?php

/**
 * RenderClass for Numerical Input Question
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
class RenderNumerical extends QuestionBaseRenderer
{
    /**
     * Returns the twig view used to render the answer part of the question.
     *
     * @return string
     */
    public function getMainView()
    {
        return '/survey/questions/answer/numerical/answer';
    }

    /**
     * Numerical input questions have no rows.
     *
     * @return void
     */
    public function getRows()
    {
        return;
    }

    /**
     * Renders the numerical input question.
     *
     * @param string $sCoreClasses Unused, kept for signature compatibility
     * @return array{0: string, 1: string[], 2: null} Rendered answer HTML, the list of input names and the (unused) mandatory flag
     */
    public function render($sCoreClasses = '')
    {
        $this->registerAssets();

        $extraclass             = "";
        $answertypeclass        = "numeric";
        $checkconditionFunction = "fixnum_checkconditions";
        $coreClass              = "ls-answers answer-item text-item numeric-item";
        $sLanguage              = $this->getSessionLanguage();

        if (trim((string) $this->getQuestionAttribute('prefix', $sLanguage)) != '') {
            $prefix      = $this->getQuestionAttribute('prefix', $sLanguage);
            $extraclass .= " withprefix";
        } else {
            $prefix = '';
        }

        if (trim((string) $this->getQuestionAttribute('suffix', $sLanguage)) != '') {
            $suffix      = $this->getQuestionAttribute('suffix', $sLanguage);
            $extraclass .= " withsuffix";
        } else {
            $suffix = '';
        }

        $iMaximumChars = intval(trim((string) $this->getQuestionAttribute('maximum_chars')));
        if ($iMaximumChars > 0 && $iMaximumChars < 20) {
            // Only maxlength attribute, use textarea[maxlength] jquery selector for textarea
            $maxlength   = $iMaximumChars;
            $extraclass .= " ls-input-maxchars";
        } else {
            $maxlength = 20;
        }

        if (trim((string) $this->getQuestionAttribute('text_input_width')) != '') {
            $col         = ($this->getQuestionAttribute('text_input_width') <= 12) ? $this->getQuestionAttribute('text_input_width') : 12;
            $extraclass .= " col-md-" . trim((string) $col);
            $withColumn  = true;
        } else {
            $withColumn = false;
        }

        if (ctype_digit(trim((string) $this->getQuestionAttribute('input_size')))) {
            $inputsize   = trim((string) $this->getQuestionAttribute('input_size'));
            $extraclass .= " ls-input-sized";
        } else {
            $inputsize = null;
        }

        if (trim((string) $this->getQuestionAttribute('num_value_int_only')) == 1) {
            $extraclass      .= " integeronly";
            $answertypeclass .= " integeronly";
            $integeronly      = 1;
        } else {
            $integeronly = 0;
        }

        if (trim((string) $this->getQuestionAttribute('placeholder', $sLanguage)) != '') {
            $placeholder = $this->getQuestionAttribute('placeholder', $sLanguage);
        } else {
            $placeholder = '';
        }

        $answer = Yii::app()->twigRenderer->renderQuestion($this->getMainView(), array(
            'extraclass'             => $extraclass,
            'coreClass'              => $coreClass,
            'withColumn'             => $withColumn,
            'id'                     => $this->sSGQA,
            'basename'               => $this->sSGQA,
            'prefix'                 => $prefix,
            'answertypeclass'        => $answertypeclass,
            'inputsize'              => $inputsize,
            'fValue'                 => $this->getDisplayValue(),
            'checkconditionFunction' => $checkconditionFunction,
            'integeronly'            => $integeronly,
            'maxlength'              => $maxlength,
            'suffix'                 => $suffix,
            'placeholder'            => $placeholder,
        ), true);

        $inputnames = [];
        $inputnames[] = $this->sSGQA;
        $mandatory = null;
        return array($answer, $inputnames, $mandatory);
    }

    /**
     * Returns the stored answer formatted for display: reloaded DECIMAL values are
     * normalised (leading zero added, trailing zeros removed) and the dot is replaced
     * by the survey's decimal separator.
     *
     * @return string
     */
    private function getDisplayValue()
    {
        global $thissurvey;

        $fValue     = $this->getLegacySessionValue($this->sSGQA);
        $sSeparator = getRadixPointData($thissurvey['surveyls_numberformat'] ?? null);
        $sSeparator = $sSeparator['separator'];

        if ($fValue && is_string($fValue)) {
            // Fix reloaded DECIMAL value
            if ($fValue[0] == ".") {
                // issue #15684 mssql SAVE 0.01 AS .0100000000, set it at 0.0100000000
                $fValue = "0" . $fValue;
            }
            if (strpos($fValue, ".")) {
                $fValue = rtrim(rtrim($fValue, "0"), ".");
            }
        }
        return str_replace('.', $sSeparator, (string) $fValue);
    }

    /**
     * Returns the language of the running survey session, looked up the same way the
     * legacy renderer did (session of the survey set in the 'surveyID' config).
     * An empty string is returned when unset, which is the array key PHP used for the
     * legacy null lookup.
     *
     * @return string
     */
    private function getSessionLanguage()
    {
        return $_SESSION['responses_' . Yii::app()->getConfig('surveyID')]['s_lang'] ?? '';
    }

    /**
     * Returns a value from the session of the survey set in the 'surveyID' config,
     * as the legacy renderer did.
     *
     * @param string $sIndex Session key (e.g. the SGQA)
     * @return mixed|null The stored value, or null if it is not set
     */
    private function getLegacySessionValue($sIndex)
    {
        return $_SESSION['responses_' . Yii::app()->getConfig('surveyID')][$sIndex] ?? null;
    }
}
