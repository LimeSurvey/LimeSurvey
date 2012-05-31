<?php
class EquationQuestion extends QuestionModule
{
    public function getAnswerHTML()
    {
        $answer='<input type="hidden" name="'.$this->fieldname.'" id="java'.$this->fieldname.'" value="';
        if (isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$this->fieldname]))
        {
            $answer .= htmlspecialchars($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$this->fieldname],ENT_QUOTES);
        }
        $answer .= '".>';

        return $answer;
    }
    
    public function availableAttributes()
    {
        return array("statistics_showgraph","statistics_graphtype","hidden","numbers_only","page_break","public_statistics","scale_export");
    }

    public function questionProperties()
    {
        $clang=Yii::app()->lang;
        return array('description' => $clang->gT("Equation"),'group' => $clang->gT("Mask questions"),'subquestions' => 0,'hasdefaultvalues' => 0,'assessable' => 0,'answerscales' => 0);
    }
}
?>