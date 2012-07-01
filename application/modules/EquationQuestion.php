<?php
class EquationQuestion extends QuestionModule
{
    public function getAnswerHTML()
    {
        $answer='<input type="hidden" name="'.$this->fieldname.'" id="java'.$this->fieldname.'" value="';
        if (isset($_SESSION['survey_'.$this->surveyid][$this->fieldname]))
        {
            $answer .= htmlspecialchars($_SESSION['survey_'.$this->surveyid][$this->fieldname],ENT_QUOTES);
        }
        $answer .= '">';

        return $answer;
    }

    public function getDataEntry($idrow, &$fnames, $language)
    {
        return "\t<input type='text' name='{$this->fieldname}' value='"
            .$idrow[$this->fieldname] . "' />\n";
    }

    public function retrieveText()
    {
        return '<div class="em_equation">' . $this->text . '</div>';
    }
    
    public function availableAttributes($attr = false)
    {
        $attrs=array("statistics_showgraph","statistics_graphtype","hidden","numbers_only","page_break","public_statistics","scale_export");
        return $attr?array_key_exists($attr,$attrs):$attrs;
    }

    public function questionProperties($prop = false)
    {
        $clang=Yii::app()->lang;
        $props=array('description' => $clang->gT("Equation"),'group' => $clang->gT("Mask questions"),'subquestions' => 0,'class' => 'equation','hasdefaultvalues' => 0,'assessable' => 0,'answerscales' => 0);
        return $prop?$props[$prop]:$props;
    }
}
?>