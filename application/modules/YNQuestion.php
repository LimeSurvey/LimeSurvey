<?php
class YNQuestion extends QuestionModule
{
    public function getAnswerHTML()
    {
        $clang = Yii::app()->lang;

        $checkconditionFunction = "checkconditions";

        $answer = "<ul class=\"answers-list radio-list\">\n"
        . "\t<li class=\"answer-item radio-item\">\n<input class=\"radio\" type=\"radio\" name=\"{$this->fieldname}\" id=\"answer{$this->fieldname}Y\" value=\"Y\"";

        if ($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$this->fieldname] == 'Y')
        {
            $answer .= CHECKED;
        }
        // --> START NEW FEATURE - SAVE
        $answer .= " onclick=\"$checkconditionFunction(this.value, this.name, this.type)\" />\n<label for=\"answer{$this->fieldname}Y\" class=\"answertext\">\n\t".$clang->gT('Yes')."\n</label>\n\t</li>\n"
        . "\t<li class=\"answer-item radio-item\">\n<input class=\"radio\" type=\"radio\" name=\"{$this->fieldname}\" id=\"answer{$this->fieldname}N\" value=\"N\"";
        // --> END NEW FEATURE - SAVE

        if ($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$this->fieldname] == 'N')
        {
            $answer .= CHECKED;
        }
        // --> START NEW FEATURE - SAVE
        $answer .= " onclick=\"$checkconditionFunction(this.value, this.name, this.type)\" />\n<label for=\"answer{$this->fieldname}N\" class=\"answertext\" >\n\t".$clang->gT('No')."\n</label>\n\t</li>\n";
        // --> END NEW FEATURE - SAVE

        if ($this->mandatory != 'Y' && SHOW_NO_ANSWER == 1)
        {
            $answer .= "\t<li class=\"answer-item radio-item noanswer-item\">\n<input class=\"radio\" type=\"radio\" name=\"{$this->fieldname}\" id=\"answer{$this->fieldname}\" value=\"\"";
            if ($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$this->fieldname] == '')
            {
                $answer .= CHECKED;
            }
            // --> START NEW FEATURE - SAVE
            $answer .= " onclick=\"$checkconditionFunction(this.value, this.name, this.type)\" />\n<label for=\"answer{$this->fieldname}\" class=\"answertext\">\n\t".$clang->gT('No answer')."\n</label>\n\t</li>\n";
            // --> END NEW FEATURE - SAVE
        }

        $answer .= "</ul>\n\n<input type=\"hidden\" name=\"java{$this->fieldname}\" id=\"java{$this->fieldname}\" value=\"{ ".$_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$this->fieldname]."}\" />\n";
        return $answer;
    }
    
    public function availableAttributes()
    {
        return array("statistics_showgraph","statistics_graphtype","hide_tip","hidden","page_break","public_statistics","scale_export","random_group");
    }
}
?>