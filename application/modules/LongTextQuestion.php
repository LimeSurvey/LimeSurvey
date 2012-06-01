<?php
class LongTextQuestion extends TextQuestion
{
    public function getAnswerHTML()
    {
        global $js_header_includes, $thissurvey;
        $extraclass ="";


        $clang=Yii::app()->lang;

        if ($thissurvey['nokeyboard']=='Y')
        {
            includeKeypad();
            $kpclass = "text-keypad";
            $extraclass .=" inputkeypad";
        }
        else
        {
            $kpclass = "";
        }

        $checkconditionFunction = "checkconditions";

        $aQuestionAttributes = $this->getAttributeValues();

        if (intval(trim($aQuestionAttributes['maximum_chars']))>0)
        {
            // Only maxlength attribute, use textarea[maxlength] jquery selector for textarea
            $maximum_chars= intval(trim($aQuestionAttributes['maximum_chars']));
            $maxlength= "maxlength='{$maximum_chars}' ";
            $extraclass .=" maxchars maxchars-".$maximum_chars;
        }
        else
        {
            $maxlength= "";
        }

        // --> START ENHANCEMENT - DISPLAY ROWS
        if (trim($aQuestionAttributes['display_rows'])!='')
        {
            $drows=$aQuestionAttributes['display_rows'];
        }
        else
        {
            $drows=5;
        }
        // <-- END ENHANCEMENT - DISPLAY ROWS

        // --> START ENHANCEMENT - TEXT INPUT WIDTH
        if (trim($aQuestionAttributes['text_input_width'])!='')
        {
            $tiwidth=$aQuestionAttributes['text_input_width'];
            $extraclass .=" inputwidth-".trim($aQuestionAttributes['text_input_width']);
        }
        else
        {
            $tiwidth=40;
        }
        // <-- END ENHANCEMENT - TEXT INPUT WIDTH

        // --> START NEW FEATURE - SAVE
        $answer = "<p class='question answer-item text-item {$extraclass}'><label for='answer{$this->fieldname}' class='hide label'>{$clang->gT('Answer')}</label>";
        $answer .='<textarea class="textarea '.$kpclass.'" name="'.$this->fieldname.'" id="answer'.$this->fieldname.'" alt="'.$clang->gT('Answer').'" '
        .'rows="'.$drows.'" cols="'.$tiwidth.'" '.$maxlength.' onchange="'.$checkconditionFunction.'(this.value, this.name, this.type)">';
        // --> END NEW FEATURE - SAVE

        if ($_SESSION['survey_'.$this->surveyid][$this->fieldname]) {$answer .= str_replace("\\", "", $_SESSION['survey_'.$this->surveyid][$this->fieldname]);}

        $answer .= "</textarea></p>\n";

        if (trim($aQuestionAttributes['time_limit'])!='')
        {
            $js_header_includes[] = '/scripts/coookies.js';
            $answer .= return_timer_script($aQuestionAttributes, $this, "answer".$this->fieldname);
        }

        return $answer;
    }
    
    public function availableAttributes($attr = false)
    {
        $attrs=array("display_rows","em_validation_q","em_validation_q_tip","em_validation_sq","em_validation_sq_tip","statistics_showgraph","statistics_graphtype","hide_tip","hidden","maximum_chars","page_break","text_input_width","time_limit","time_limit_action","time_limit_disable_next","time_limit_disable_prev","time_limit_countdown_message","time_limit_timer_style","time_limit_message_delay","time_limit_message","time_limit_message_style","time_limit_warning","time_limit_warning_display_time","time_limit_warning_message","time_limit_warning_style","time_limit_warning_2","time_limit_warning_2_display_time","time_limit_warning_2_message","time_limit_warning_2_style","random_group");
        return $attr?array_key_exists($attr,$attrs):$attrs;
    }

    public function questionProperties($prop = false)
    {
        $clang=Yii::app()->lang;
        $props=array('description' => $clang->gT("Long Free Text"),'group' => $clang->gT("Text questions"),'subquestions' => 0,'class' => 'text-long','hasdefaultvalues' => 1,'assessable' => 0,'answerscales' => 0);
        return $prop?$props[$prop]:$props;
    }
}
?>