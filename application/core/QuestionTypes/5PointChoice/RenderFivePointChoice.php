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

    public function getRows()
    {
        $aRows = [];
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
                'checkedState'           => '',
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
