<?php

/**
 * 
 */

class RenderBoilerplate extends QuestionRenderer
{
    public function getMainView(){
        return '/survey/questions/answer/boilerplate/answer';
    }
    public function render($sCoreClasses = ''){
        //$aQuestionAttributes = QuestionAttribute::model()->getQuestionAttributes($ia[0]);
        $aQuestionAttributes = QuestionAttribute::model()->getQuestionAttributes($ia[0]);
        $answer = '';
        $inputnames = [];

        if (trim($aQuestionAttributes['time_limit']) != '') {
            $answer .= return_timer_script($aQuestionAttributes, $ia);
        }

        $answer .= doRender('/survey/questions/answer/boilerplate/answer', array(
            'ia'=>$ia,
            'name'=>$ia[1],
            'basename'=>$ia[1], /* is this needed ? */
            'coreClass'=> 'ls-answers hidden '.$sCoreClasses,
            ), true);
        $inputnames[] = $ia[1];

        return array($answer, $inputnames);
    }

}
