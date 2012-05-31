<?php
class DisplayQuestion extends QuestionModule
{
    public function getAnswerHTML()
    {
        global $js_header_includes;
        $aQuestionAttributes = $this->getAttributeValues();
        $answer='';

        if (trim($aQuestionAttributes['time_limit'])!='')
        {
            $js_header_includes[] = '/scripts/coookies.js';
            $answer .= return_timer_script($aQuestionAttributes, $this);
        }

        $answer .= '<input type="hidden" name="'.$this->fieldname.'" id="answer'.$this->fieldname.'" value="" />';
        
        return $answer;
    }
    
    public function availableAttributes()
    {
        return array("statistics_showgraph","statistics_graphtype","hide_tip","hidden","page_break","time_limit","time_limit_action","time_limit_disable_next","time_limit_disable_prev","time_limit_countdown_message","time_limit_timer_style","time_limit_message_delay","time_limit_message","time_limit_message_style","time_limit_warning","time_limit_warning_display_time","time_limit_warning_message","time_limit_warning_style","time_limit_warning_2","time_limit_warning_2_display_time","time_limit_warning_2_message","time_limit_warning_2_style","random_group");
    }

    public function questionProperties()
    {
        $clang=Yii::app()->lang;
        return array('description' => $clang->gT("Text display"),'group' => $clang->gT("Mask questions"),'subquestions' => 0,'hasdefaultvalues' => 0,'assessable' => 0,'answerscales' => 0);
    }
}
?>