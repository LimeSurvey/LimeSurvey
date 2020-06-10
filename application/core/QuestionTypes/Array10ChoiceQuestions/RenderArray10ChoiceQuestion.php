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
class RenderArray10ChoiceQuestion extends QuestionBaseRenderer
{
    public function getMainView()
    {
        return '/survey/questions/answer/dummy/answer';
    }
    
    public function getRows()
    {
        return;
    }

    public function render($sCoreClasses = '')
    {
        $this->registerAssets();
        return do_array_10point($this->aFieldArray);
        
        $answer = '';
        $inputnames = [];

        if (!empty($this->getQuestionAttribute('time_limit'))) {
            $answer .= $this->getTimeSettingRender();
        }

        $answer .=  Yii::app()->twigRenderer->renderQuestion($this->getMainView(), array(
            'ia'=>$this->aFieldArray,
            'name'=>$this->sSGQA,
            'basename'=>$this->sSGQA, 
            'content' => $this->oQuestion,
            'coreClass'=> 'ls-answers '.$sCoreClasses,
            ), true);

        $inputnames[] = [];
        return array($answer, $inputnames);
    }
}
