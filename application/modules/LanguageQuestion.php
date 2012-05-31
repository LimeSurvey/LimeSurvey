<?php
class LanguageQuestion extends QuestionModule
{
    public function getAnswerHTML()
    {
        $clang = Yii::app()->lang;

        $checkconditionFunction = "checkconditions";

        $answerlangs = Survey::model()->findByPk(Yii::app()->getConfig('surveyID'))->additionalLanguages;
        $answerlangs [] = Survey::model()->findByPk(Yii::app()->getConfig('surveyID'))->language;
        $answer = "\n\t<p class=\"question answer-item dropdown-item langage-item\">\n"
        ."<label for='answer{$this->fieldname}' class='hide label'>{$clang->gT('Choose your language')}</label>"
        ."<select name=\"$this->fieldname\" id=\"answer$this->fieldname\" onchange=\"document.getElementById('lang').value=this.value; $checkconditionFunction(this.value, this.name, this.type);\">\n";
        if (!$_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$this->fieldname]) {$answer .= "\t<option value=\"\" selected=\"selected\">".$clang->gT('Please choose...')."</option>\n";}
        foreach ($answerlangs as $ansrow)
        {
            $answer .= "\t<option value=\"{$ansrow}\"";
            if ($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$this->fieldname] == $ansrow)
            {
                $answer .= SELECTED;
            }
            $answer .= '>'.getLanguageNameFromCode($ansrow, true)."</option>\n";
        }
        $answer .= "</select>\n";
        $answer .= "<input type=\"hidden\" name=\"java$this->fieldname\" id=\"java$this->fieldname\" value=\"".$_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$this->fieldname]."\" />\n";

        $answer .= "\n<input type=\"hidden\" name=\"lang\" id=\"lang\" value=\"\" />\n\t</p>\n";

        return $answer;
    }
    
    public function availableAttributes()
    {
        return array("statistics_showgraph","statistics_graphtype","hide_tip","hidden","random_group");
    }
}
?>