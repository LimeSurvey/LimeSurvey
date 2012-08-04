<?php
class YNQuestion extends QuestionModule
{
    public function getAnswerHTML()
    {
        $clang = Yii::app()->lang;

        $checkconditionFunction = "checkconditions";

        $answer = "<ul class=\"answers-list radio-list\">\n"
        . "\t<li class=\"answer-item radio-item\">\n<input class=\"radio\" type=\"radio\" name=\"{$this->fieldname}\" id=\"answer{$this->fieldname}Y\" value=\"Y\"";

        if ($_SESSION['survey_'.$this->surveyid][$this->fieldname] == 'Y')
        {
            $answer .= CHECKED;
        }
        // --> START NEW FEATURE - SAVE
        $answer .= " onclick=\"$checkconditionFunction(this.value, this.name, this.type)\" />\n<label for=\"answer{$this->fieldname}Y\" class=\"answertext\">\n\t".$clang->gT('Yes')."\n</label>\n\t</li>\n"
        . "\t<li class=\"answer-item radio-item\">\n<input class=\"radio\" type=\"radio\" name=\"{$this->fieldname}\" id=\"answer{$this->fieldname}N\" value=\"N\"";
        // --> END NEW FEATURE - SAVE

        if ($_SESSION['survey_'.$this->surveyid][$this->fieldname] == 'N')
        {
            $answer .= CHECKED;
        }
        // --> START NEW FEATURE - SAVE
        $answer .= " onclick=\"$checkconditionFunction(this.value, this.name, this.type)\" />\n<label for=\"answer{$this->fieldname}N\" class=\"answertext\" >\n\t".$clang->gT('No')."\n</label>\n\t</li>\n";
        // --> END NEW FEATURE - SAVE

        if ($this->mandatory != 'Y' && SHOW_NO_ANSWER == 1)
        {
            $answer .= "\t<li class=\"answer-item radio-item noanswer-item\">\n<input class=\"radio\" type=\"radio\" name=\"{$this->fieldname}\" id=\"answer{$this->fieldname}\" value=\"\"";
            if ($_SESSION['survey_'.$this->surveyid][$this->fieldname] == '')
            {
                $answer .= CHECKED;
            }
            // --> START NEW FEATURE - SAVE
            $answer .= " onclick=\"$checkconditionFunction(this.value, this.name, this.type)\" />\n<label for=\"answer{$this->fieldname}\" class=\"answertext\">\n\t".$clang->gT('No answer')."\n</label>\n\t</li>\n";
            // --> END NEW FEATURE - SAVE
        }

        $answer .= "</ul>\n\n<input type=\"hidden\" name=\"java{$this->fieldname}\" id=\"java{$this->fieldname}\" value=\"{ ".$_SESSION['survey_'.$this->surveyid][$this->fieldname]."}\" />\n";
        return $answer;
    }

    public function getDataEntry($idrow, &$fnames, $language)
    {
        $clang = Yii::app()->lang;
        $output .= "\t<select name='{$this->fieldname}'>\n"
        ."<option value=''";
        if ($idrow[$this->fieldname] == "") {$output .= " selected='selected'";}
        $output .= ">".$clang->gT("Please choose")."..</option>\n"
        ."<option value='Y'";
        if ($idrow[$this->fieldname] == "Y") {$output .= " selected='selected'";}
        $output .= ">".$clang->gT("Yes")."</option>\n"
        ."<option value='N'";
        if ($idrow[$this->fieldname] == "N") {$output .= " selected='selected'";}
        $output .= ">".$clang->gT("No")."</option>\n"
        ."\t</select>\n";
        return $output;
    }

    public function getExtendedAnswer($value, $language)
    {
        switch($value)
        {
            case "Y": return $language->gT("Yes")." [$value]";
            case "N": return $language->gT("No")." [$value]";
            default: return $language->gT("No answer")." [$value]";
        }
    }

    public function getQuotaValue($value)
    {
        return array($this->surveyid.'X'.$this->gid.'X'.$this->id => $value);
    }

    public function getDBField()
    {
        return 'VARCHAR(1)';
    }

    public function transformResponseValues($value, $options)
    {
        if ($value == 'N' && $options->convertN)
        {
            //echo "Transforming 'N' to ".$options->nValue.PHP_EOL;
            return $options->nValue;
        }
        else if ($value == 'Y' && $options->convertY)
        {
            //echo "Transforming 'Y' to ".$options->yValue.PHP_EOL;
            return $options->yValue;
        }
        return parent::transformResponseValues($value, $options);
    }

    public function getFullAnswer($answerCode, $export, $survey)
    {
        switch ($answerCode)
        {
            case 'Y':
                return $export->translator->translate('Yes', $export->languageCode);
            case 'N':
                return $export->translator->translate('No', $export->languageCode);
            default:
                return $export->translator->translate('N/A', $export->languageCode);
        }
    }

    public function getSPSSAnswers()
    {
        $answers[] = array('code'=>1, 'value'=>$clang->gT('Yes'));
        $answers[] = array('code'=>2, 'value'=>$clang->gT('No'));
        return $answers;
    }

    public function getSPSSData($data, $iLength, $na)
    {
        if ($data == 'Y')
        {
            return "'1'";
        } else if ($data == 'N'){
            return "'2'";
        } else {
            return $na;
        }
    }

    public function getAnswerArray($em)
    {
        $clang = Yii::app()->lang;
        return array('Y' => $clang->gT("Yes"), 'N' => $clang->gT("No"));
    }

    public function jsVarNameOn()
    {
        return 'java'.$this->fieldname;
    }

    public function availableAttributes($attr = false)
    {
        $attrs=array("statistics_showgraph","statistics_graphtype","hide_tip","hidden","page_break","public_statistics","scale_export","random_group");
        return $attr?in_array($attr,$attrs):$attrs;
    }

    public function questionProperties($prop = false)
    {
        $clang=Yii::app()->lang;
        $props=array('description' => $clang->gT("Yes/No"),'group' => $clang->gT("Mask questions"),'subquestions' => 0,'class' => 'yes-no','hasdefaultvalues' => 0,'assessable' => 0,'answerscales' => 0);
        return $prop?$props[$prop]:$props;
    }
}
?>