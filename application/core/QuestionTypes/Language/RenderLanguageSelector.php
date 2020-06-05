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
class RenderLanguageSelector extends QuestionBaseRenderer
{
    public function getMainView()
    {
        return '/survey/questions/answer/language/answer';
    }
    
    public function getRows()
    {
        $answerlangs            = $this->oQuestion->survey->additionalLanguages;
        $answerlangs[]          = $this->oQuestion->survey->language;
        
        return $answerlangs;
    }

    public function render($sCoreClasses = '')
    {
        $answer = '';
        $inputnames = [];

        if (!empty($this->getQuestionAttribute('time_limit'))) {
            $answer .= $this->getTimeSettingRender();
        }
        $sLanguage = $this->sLanguage;
        $aAnswerlangs = $this->getRows();

        if (!in_array($sLanguage, $aAnswerlangs)) {
            $sLanguage = $this->oQuestion->survey->language;
        }


        $answer .=  Yii::app()->twigRenderer->renderQuestion($this->getMainView(), array(
            'name'=>$this->sSGQA,
            'basename'=>$this->sSGQA, 
            'checkconditionFunction'=>'checkconditions(this.value, this.name, this.type)',
            'coreClass'=> 'ls-answers answer-item dropdow-item langage-item '.$sCoreClasses,
            'answerlangs'=> $aAnswerlangs,
            'sLang'=> $sLanguage,
            ), true);
            
        $this->registerAssets();
        $inputnames[] = $this->sSGQA;
        return array($answer, $inputnames);
    }
}
