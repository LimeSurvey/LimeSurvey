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
        . '     <input class="radio" type="radio" name="'.$this->fieldname.'" id="answer'.$this->fieldname.'F" value="F"';
        if ($_SESSION['survey_'.$this->surveyid][$this->fieldname] == 'F')
        {
            $answer .= CHECKED;
        }
        $answer .= " onclick=\"$checkconditionFunction(this.value, this.name, this.type)\" />\n"
        . '     <label for="answer'.$this->fieldname.'F" class="answertext">'.$clang->gT('Female')."</label>\n\t</li>\n";

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

    public function getDataEntry($idrow, &$fnames, $language)
    {
        $clang = Yii::app()->lang;
        $select_options = array(
        '' => $clang->gT("Please choose").'...',
        'F' => $clang->gT("Female"),
        'M' => $clang->gT("Male")
        );
        return CHtml::listBox($this->fieldname, $idrow[$this->fieldname], $select_options);
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

    public function getDBField()
    {
        return 'VARCHAR(1)';
    }

    public function getFullAnswer($answerCode, $export, $survey)
    {
        switch ($answerCode)
        {
            case 'M':
                return $export->translator->translate('Male', $export->languageCode);
            case 'F':
                return $export->translator->translate('Female', $export->languageCode);
            default:
                return $export->translator->translate('N/A', $export->languageCode);
        }
    }

    public function getSPSSAnswers()
    {
        $answers[] = array('code'=>1, 'value'=>$clang->gT('Female'));
        $answers[] = array('code'=>2, 'value'=>$clang->gT('Male'));
        return $answers;
    }

    public function getSPSSData($data, $iLength, $na, $qs)
    {
        if ($data == 'F')
        {
            return "'1'";
        } else if ($data == 'M'){
            return "'2'";
        } else {
            return $na;
        }
    }

    public function getAnswerArray($em)
    {
        $clang = Yii::app()->lang;
        return array('M' => $clang->gT("Male"), 'F' => $clang->gT("Female"));
    }

    public function jsVarNameOn()
    {
        return 'java'.$this->fieldname;
    }

    public function getVarAttributeShown($name, $default, $gseq, $qseq, $ansArray)
    {
        $code = LimeExpressionManager::GetVarAttribute($name,'code',$default,$gseq,$qseq);

        if (is_null($ansArray))
        {
            return $default;
        }
        else
        {
            if (isset($ansArray[$code])) {
                $answer = $ansArray[$code];
            }
            else {
                $answer = $default;
            }
            return $answer;
        }
    }

    public function getShownJS()
    {
        return 'return (typeof attr.answers[value] === "undefined") ? "" : attr.answers[value];';
    }

    public function getQuotaAnswers($iQuotaId)
    {
        $clang = Yii::app()->lang;
        $aAnswerList = array('M' => array('Title' => $this->id, 'Display' => $clang->gT("Male"), 'code' => 'M'),
            'F' => array('Title' => $this->title, 'Display' => $clang->gT("Female"), 'code' => 'F'));

        $aResults = Quota_members::model()->findAllByAttributes(array('sid' => $this->surveyid, 'qid' => $this->id, 'quota_id' => $iQuotaId));
        foreach ($aResults as $aQuotaList)
        {
            $aAnswerList[$aQuotaList['code']]['rowexists'] = '1';
        }

        return $aAnswerList;
    }

    public function getDataEntryView($language)
    {
        $output = "<select name='{$this->fieldname}'>";
        $output .= "<option selected='selected' value=''>{$language->gT("Please choose")}..</option>";
        $output .= "<option value='F'>{$language->gT("Female")}</option>";
        $output .= "<option value='M'>{$language->gT("Male")}</option>";
        $output .= "</select>";
        return $output;
    }

    public function getTypeHelp($language)
    {
        return $language->gT('Please choose *only one* of the following:');
    }

    public function getPrintAnswers($language)
    {
        $output = "\n\t<ul>\n";
        $output .= "\t\t<li>\n\t\t\t".printablesurvey::input_type_image('radio',$language->gT("Female"))."\n\t\t\t".$language->gT("Female")." ".(Yii::app()->getConfig('showsgqacode') ? '(F)' : '')."\n\t\t</li>\n";
        $output .= "\t\t<li>\n\t\t\t".printablesurvey::input_type_image('radio',$language->gT("Male"))."\n\t\t\t".$language->gT("Male")." ".(Yii::app()->getConfig('showsgqacode') ? '(M)' : '')."\n\t\t</li>\n";
        $output .= "\t</ul>\n";
        return $output;
    }

    public function getPrintPDF($language)
    {
        return " o ".$language->gT("Female")." | o ".$language->gT("Male");
    }

    public function getConditionAnswers()
    {
        $clang = Yii::app()->lang;
        $canswers = array();

        $canswers[]=array($this->surveyid.'X'.$this->gid.'X'.$this->id, "F", $clang->gT("Female"));
        $canswers[]=array($this->surveyid.'X'.$this->gid.'X'.$this->id, "M", $clang->gT("Male"));
        // Only Show No-Answer if question is not mandatory
        if ($this->mandatory != 'Y')
        {
            $canswers[]=array($this->surveyid.'X'.$this->gid.'X'.$this->id, " ", $clang->gT("No answer"));
        }

        return $canswers;
    }

    public function QueXMLAppendAnswers(&$question)
    {
        global $dom, $quexmllang;
        $qlang = new limesurvey_lang($quexmllang);
        $response = $dom->createElement("response");
        $response->setAttribute("varName", $this->surveyid . 'X' . $this->gid . 'X' . $this->id);
        $response->appendChild(QueXMLFixedArray(array($qlang->gT("Female") => 'F',$qlang->gT("Male") => 'M')));
        $question->appendChild($response);
    }

    public function availableAttributes($attr = false)
    {
        $attrs=array("statistics_showgraph","statistics_graphtype","hide_tip","hidden","page_break","public_statistics","scale_export","random_group");
        return $attr?in_array($attr,$attrs):$attrs;
    }

    public function questionProperties($prop = false)
    {
        $clang=Yii::app()->lang;
        $props=array('description' => $clang->gT("Gender"),'group' => $clang->gT("Mask questions"),'subquestions' => 0,'class' => 'gender','hasdefaultvalues' => 0,'assessable' => 0,'answerscales' => 0,'enum' => 0);
        return $prop?$props[$prop]:$props;
    }
}
?>