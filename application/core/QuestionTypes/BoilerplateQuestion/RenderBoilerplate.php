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
class RenderBoilerplate extends QuestionBaseRenderer
{
    public function getMainView()
    {
        return '/survey/questions/answer/boilerplate/answer';
    }
    
    public function getRows()
    {
        return;
    }

    public function render($sCoreClasses = '')
    {
        $answer = '';
        $inputnames = [];

        if (!empty($this->getQuestionAttribute('time_limit'))) {
            $answer .= $this->getTimeSettingRender();
        }

        $answer .=  Yii::app()->twigRenderer->renderQuestion($this->getMainView(), array(
            'ia' => $this->aFieldArray,
            'name' => $this->sSGQA,
            'basename' => $this->sSGQA, /* is this needed ? */
            'coreClass' => 'ls-answers d-none ' . $sCoreClasses,
            ), true);

        $inputnames[] = $this->sSGQA;
        $this->registerAssets();
        return array($answer, $inputnames);
    }
}
