<?php

use LimeSurvey\Models\Services\SurveySessionState;

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
class RenderEquation extends QuestionBaseRenderer
{
    public function getRows()
    {
        return;
    }

    public function getMainView()
    {
        return '/survey/questions/answer/equation/answer';
    }
    /**
     * Renders the equation question (hidden input holding the equation result).
     *
     * @param string $sCoreClasses Unused, kept for signature compatibility
     * @return array{0: string, 1: string[]} Rendered answer HTML and the list of input names
     */
    public function render($sCoreClasses = '')
    {
        $inputnames = [];

        $sEquation  = $this->setDefaultIfEmpty($this->getQuestionAttribute('equation'), $this->aFieldArray[3]);
        $sValue     = htmlspecialchars((string) SurveySessionState::forSurvey((int) Yii::app()->getConfig('surveyID'))->getFieldValue($this->sSGQA), ENT_QUOTES);

        $answer =  Yii::app()->twigRenderer->renderQuestion($this->getMainView(), array(
            'ia' => $this->aFieldArray,
            'name' => $this->sSGQA,
            'basename' => $this->sSGQA, /* is this needed ? */
            'sValue'    =>  $sValue,
            'sEquation' => LimeExpressionManager::ProcessString($sEquation, $this->oQuestion->qid),
            'coreClass' => 'ls-answers answer-item hidden-item  ' . $sCoreClasses,
            'insideClass' => 'em_equation',
            ), true);

        $this->registerAssets();
        $inputnames[] = $this->sSGQA;
        return array($answer, $inputnames);
    }
}
