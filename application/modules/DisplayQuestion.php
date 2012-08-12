<?php
class DisplayQuestion extends QuestionModule
{
    public function getAnswerHTML()
    {
        $aQuestionAttributes = $this->getAttributeValues();
        $answer='';

        if (trim($aQuestionAttributes['time_limit'])!='')
        {
            $answer .= return_timer_script($aQuestionAttributes, $this);
        }

        $answer .= '<input type="hidden" name="'.$this->fieldname.'" id="answer'.$this->fieldname.'" value="" />';

        return $answer;
    }

    public function getDataEntry($idrow, &$fnames, $language)
    {
        return "";
    }

    public function getDBField()
    {
        return 'VARCHAR(1)';
    }

    public function mandatoryViolation($relevantSQs, $unansweredSQs, $subsqs, $sgqas)
    {
        return false;
    }

    public function availableAttributes($attr = false)
    {
        $attrs=array("statistics_showgraph","statistics_graphtype","hide_tip","hidden","page_break","time_limit","time_limit_action","time_limit_disable_next","time_limit_disable_prev","time_limit_countdown_message","time_limit_timer_style","time_limit_message_delay","time_limit_message","time_limit_message_style","time_limit_warning","time_limit_warning_display_time","time_limit_warning_message","time_limit_warning_style","time_limit_warning_2","time_limit_warning_2_display_time","time_limit_warning_2_message","time_limit_warning_2_style","random_group");
        return $attr?in_array($attr,$attrs):$attrs;
    }

    public function questionProperties($prop = false)
    {
        $clang=Yii::app()->lang;
        $props=array('description' => $clang->gT("Text display"),'group' => $clang->gT("Mask questions"),'subquestions' => 0,'class' => 'boilerplate','hasdefaultvalues' => 0,'assessable' => 0,'answerscales' => 0);
        return $prop?$props[$prop]:$props;
    }
}
?>