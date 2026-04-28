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
class RenderFivePointChoice extends QuestionBaseRenderer
{
    protected $aPackages = [];
    protected $aScripts = [];

    public function getMainView()
    {
        return '/survey/questions/answer/5pointchoice/answer';
    }

    /**
     * Build the list of option rows for the 5-point choice question (and an optional "No answer" row).
     *
     * Each row is an associative array representing a single radio option for values 1 through 5.
     * If the question is not mandatory and SHOW_NO_ANSWER is enabled, an additional "No answer" row is appended.
     *
     * @return array<int, array{name: string, value: mixed, id: string, labelText: mixed, itemExtraClass: string, checkedState: string, checkconditionFunction: mixed}>
     *         An array of option rows. Each row contains:
     *         - `name`: form field name (SGQA).
     *         - `value`: option value (1–5 or empty string for "No answer").
     *         - `id`: element id (SGQA with numeric suffix for 1–5; SGQA for "No answer").
     *         - `labelText`: label shown for the option (numeric label for 1–5 or localized "No answer").
     *         - `itemExtraClass`: additional CSS class for the item (empty or 'noanswer-item').
     *         - `checkedState`: `' CHECKED '` if the option should be selected, otherwise an empty string.
     *         - `checkconditionFunction`: condition function used by frontend logic.
     */
    public function getRows()
    {
        $aRows = [];
        $sessionValue = $this->mSessionValue;
        for ($fp = 1; $fp <= 5; $fp++) {
            $aRows[] = array(
                'name'                   => $this->sSGQA,
                'value'                  => $fp,
                'id'                     => $this->sSGQA . $fp,
                'labelText'              => $fp,
                'itemExtraClass'         => '',
                'checkedState'           => ($this->mSessionValue == $fp ? ' CHECKED ' : ''),
                'checkconditionFunction' => $this->checkconditionFunction,
                );
        }

        if ($this->oQuestion->mandatory != "Y" && SHOW_NO_ANSWER == 1) {
            // Add "No Answer" option if question is not mandatory
            $aRows[] = array(
                'name'                   => $this->sSGQA,
                'value'                  => "",
                'id'                     => $this->sSGQA,
                'labelText'              => gT('No answer'),
                'itemExtraClass'         => 'noanswer-item',
                'checkedState'           => $this->isNoAnswerChecked() ? ' CHECKED ' : '',
                'checkconditionFunction' => $this->checkconditionFunction,
            );
        }

        return $aRows;
    }

    public function render($sCoreClasses = '')
    {
        $inputnames = [];

        $aRows = array();

        $inputnames[] = $this->aFieldArray[1];

        $slider_rating = 0;

        if ($this->getQuestionAttribute('slider_rating') == 1) {
            $slider_rating = 1;
            $this->aPackages[] = 'question-5pointchoice-star';
            $this->addScript(
                'doRatingStar',
                "doRatingStar('" . $this->oQuestion->qid . "');",
                LSYii_ClientScript::POS_POSTSCRIPT,
                true
            );
        }
        
        if ($this->getQuestionAttribute('slider_rating') == 2) {
            $slider_rating = 2;
            $this->aPackages[] = 'question-5pointchoice-slider';
            $this->addScript(
                'doRatingSlider',
                "
                    var doRatingSlider_" . $this->aFieldArray[1] . "= new getRatingSlider('" . $this->aFieldArray[0] . "');
                    doRatingSlider_" . $this->aFieldArray[1] . "();
                ",
                LSYii_ClientScript::POS_POSTSCRIPT,
                true
            );
        }


        $answer = Yii::app()->twigRenderer->renderQuestion($this->getMainView(), array(
            'coreClass'     => "ls-answers answers-list radio-list",
            'sliderId'      => $this->aFieldArray[0],
            'name'          => $this->aFieldArray[1],
            'basename'      => $this->aFieldArray[1],
            'sessionValue'  => $this->mSessionValue,
            'aRows'         => $this->getRows(),
            'slider_rating' => $slider_rating,

            ), true);

        $this->registerAssets();
        return array($answer, $inputnames);
    }
}
