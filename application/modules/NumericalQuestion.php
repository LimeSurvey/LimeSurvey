<?php
class NumericalQuestion extends QuestionModule
{
    public function getAnswerHTML()
    {
        global $thissurvey;

        $clang = Yii::app()->lang;
        $extraclass ="";
        $answertypeclass = "numeric";
        $checkconditionFunction = "fixnum_checkconditions";
        $aQuestionAttributes = $this->getAttributeValues();
        if (trim($aQuestionAttributes['prefix'][$_SESSION['survey_'.$this->surveyid]['s_lang']])!='') {
            $prefix=$aQuestionAttributes['prefix'][$_SESSION['survey_'.$this->surveyid]['s_lang']];
            $extraclass .=" withprefix";
        }
        else
        {
            $prefix = '';
        }
        if (trim($aQuestionAttributes['suffix'][$_SESSION['survey_'.$this->surveyid]['s_lang']])!='') {
            $suffix=$aQuestionAttributes['suffix'][$_SESSION['survey_'.$this->surveyid]['s_lang']];
            $extraclass .=" withsuffix";
        }
        else
        {
            $suffix = '';
        }
        if (intval(trim($aQuestionAttributes['maximum_chars']))>0 && intval(trim($aQuestionAttributes['maximum_chars']))<20)
        {
            // Only maxlength attribute, use textarea[maxlength] jquery selector for textarea
            $maximum_chars= intval(trim($aQuestionAttributes['maximum_chars']));
            $maxlength= "maxlength='{$maximum_chars}' ";
            $extraclass .=" maxchars maxchars-".$maximum_chars;
        }
        else
        {
            $maxlength= "maxlength='20' ";
        }
        if (trim($aQuestionAttributes['text_input_width'])!='')
        {
            $tiwidth=$aQuestionAttributes['text_input_width'];
            $extraclass .=" inputwidth-".trim($aQuestionAttributes['text_input_width']);
        }
        else
        {
            $tiwidth=10;
        }

        if (trim($aQuestionAttributes['num_value_int_only'])==1)
        {
            $acomma="";
            $extraclass .=" integeronly";
            $answertypeclass = " integeronly";
        }
        else
        {
            $acomma=getRadixPointData($thissurvey['surveyls_numberformat']);
            $acomma = $acomma['seperator'];

        }
        $sSeperator = getRadixPointData($thissurvey['surveyls_numberformat']);
        $sSeperator = $sSeperator['seperator'];
        $dispVal = str_replace('.',$sSeperator,$_SESSION['survey_'.$this->surveyid][$this->fieldname]);

        if ($thissurvey['nokeyboard']=='Y')
        {
            includeKeypad();
            $extraclass .=" inputkeypad";
            $answertypeclass = "num-keypad";
        }
        else
        {
            $kpclass = "";
        }
        // --> START NEW FEATURE - SAVE
        $answer = "<p class='question answer-item text-item numeric-item {$extraclass}'>"
        . " <label for='answer{$this->fieldname}' class='hide label'>{$clang->gT('Answer')}</label>\n$prefix\t"
        . "<input class='text {$answertypeclass}' type=\"text\" size=\"$tiwidth\" name=\"$this->fieldname\"  title=\"".$clang->gT('Only numbers may be entered in this field')."\" "
        . "id=\"answer{$this->fieldname}\" value=\"{$dispVal}\" title=\"".$clang->gT('Only numbers may be entered in this field')."\" onkeypress=\"return goodchars(event,'-0123456789{$acomma}')\" onchange='$checkconditionFunction(this.value, this.name, this.type)' "
        . " {$maxlength} />\t{$suffix}\n</p>\n";
        if ($aQuestionAttributes['hide_tip']==0)
        {
            $answer .= "<p class=\"tip\">".$clang->gT('Only numbers may be entered in this field')."</p>\n";
        }

        // --> END NEW FEATURE - SAVE

        return $answer;
    }
    
    public function availableAttributes($attr = false)
    {
        $attrs=array("em_validation_q","em_validation_q_tip","em_validation_sq","em_validation_sq_tip","statistics_showgraph","statistics_graphtype","hide_tip","hidden","max_num_value_n","maximum_chars","min_num_value_n","num_value_int_only","page_break","prefix","public_statistics","suffix","text_input_width","random_group");
        return $attr?array_key_exists($attr,$attrs):$attrs;
    }

    public function questionProperties($prop = false)
    {
        $clang=Yii::app()->lang;
        $props=array('description' => $clang->gT("Numerical Input"),'group' => $clang->gT("Mask questions"),'subquestions' => 0,'class' => 'numeric','hasdefaultvalues' => 1,'assessable' => 0,'answerscales' => 0);
        return $prop?$props[$prop]:$props;
    }
}
?>