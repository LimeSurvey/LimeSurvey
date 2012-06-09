<?php
class GenderQuestion extends QuestionModule
{
    public function getAnswerHTML()
    {
        $clang = Yii::app()->lang;

        $checkconditionFunction = "checkconditions";

        $aQuestionAttributes = $this->getAttributeValues();

        $answer = "<ul class=\"answers-list radio-list\">\n"
        . "\t<li class=\"answer-item radio-item\">\n"
        . '		<input class="radio" type="radio" name="'.$this->fieldname.'" id="answer'.$this->fieldname.'F" value="F"';
        if ($_SESSION['survey_'.$this->surveyid][$this->fieldname] == 'F')
        {
            $answer .= CHECKED;
        }
        $answer .= " onclick=\"$checkconditionFunction(this.value, this.name, this.type)\" />\n"
        . '		<label for="answer'.$this->fieldname.'F" class="answertext">'.$clang->gT('Female')."</label>\n\t</li>\n";

        $answer .= "\t<li class=\"answer-item radio-item\">\n<input class=\"radio\" type=\"radio\" name=\"$this->fieldname\" id=\"answer".$this->fieldname.'M" value="M"';

        if ($_SESSION['survey_'.$this->surveyid][$this->fieldname] == 'M')
        {
            $answer .= CHECKED;
        }
        $answer .= " onclick=\"$checkconditionFunction(this.value, this.name, this.type)\" />\n<label for=\"answer".$this->fieldname."M\" class=\"answertext\">".$clang->gT('Male')."</label>\n\t</li>\n";

        if ($this->mandatory != 'Y' && SHOW_NO_ANSWER == 1)
        {
            $answer .= "\t<li class=\"answer-item radio-item noanswer-item\">\n<input class=\"radio\" type=\"radio\" name=\"$this->fieldname\" id=\"answer".$this->fieldname.'" value=""';
            if ($_SESSION['survey_'.$this->surveyid][$this->fieldname] == '')
            {
                $answer .= CHECKED;
            }
            // --> START NEW FEATURE - SAVE
            $answer .= " onclick=\"$checkconditionFunction(this.value, this.name, this.type)\" />\n<label for=\"answer$this->fieldname\" class=\"answertext\">".$clang->gT('No answer')."</label>\n\t</li>\n";
            // --> END NEW FEATURE - SAVE

        }
        $answer .= "</ul>\n\n<input type=\"hidden\" name=\"java$this->fieldname\" id=\"java$this->fieldname\" value=\"".$_SESSION['survey_'.$this->surveyid][$this->fieldname]."\" />\n";

        return $answer;
    }
            
    public function getExtendedAnswer($value, $language)
    {
        switch($value)
        {
            case "M": return $language->gT("Male")." [$value]";
            case "F": return $language->gT("Female")." [$value]";
            default: return $language->gT("No answer")." [$value]";
        }
    }
    
    public function getQuotaValue($value)
    {
        return array($this->surveyid.'X'.$this->gid.'X'.$this->id => $value);
    }
    
    public function availableAttributes($attr = false)
    {
        $attrs=array("display_columns","statistics_showgraph","statistics_graphtype","hide_tip","hidden","page_break","public_statistics","scale_export","random_group");
        return $attr?array_key_exists($attr,$attrs):$attrs;
    }

    public function questionProperties($prop = false)
    {
        $clang=Yii::app()->lang;
        $props=array('description' => $clang->gT("Gender"),'group' => $clang->gT("Mask questions"),'subquestions' => 0,'class' => 'gender','hasdefaultvalues' => 0,'assessable' => 0,'answerscales' => 0);
        return $prop?$props[$prop]:$props;
    }
}
?>