<?php
class TenRadioArrayQuestion extends RadioArrayQuestion
{
    public function getAnswerHTML()
    {
        global $notanswered, $thissurvey;
        $extraclass ="";
        $clang = Yii::app()->lang;

        $checkconditionFunction = "checkconditions";

        $qquery = "SELECT other FROM {{questions}} WHERE qid=".$this->id."  AND language='".$_SESSION['survey_'.$this->surveyid]['s_lang']."'";
        $qresult = dbExecuteAssoc($qquery);      //Checked
        $qrow = $qresult->read(); $other = $qrow['other'];

        $aQuestionAttributes = $this->getAttributeValues();
        if (trim($aQuestionAttributes['answer_width'])!='')
        {
            $answerwidth=$aQuestionAttributes['answer_width'];
        }
        else
        {
            $answerwidth = 20;
        }
        $cellwidth  = 10; // number of columns
        if ($this->mandatory != 'Y' && SHOW_NO_ANSWER == 1) //Question is not mandatory
        {
            ++$cellwidth; // add another column
        }
        $cellwidth = round((( 100 - $answerwidth ) / $cellwidth) , 1); // convert number of columns to percentage of table width

        $ansresult = $this->getChildren();
        $anscount = count($ansresult);

        $fn = 1;
        $answer = "\n<table class=\"question subquestion-list questions-list {$extraclass}\" summary=\"".str_replace('"','' ,strip_tags($this->text))." - a ten point Likert scale array\" >\n\n"
        . "\t<colgroup class=\"col-responses\">\n"
        . "\t<col class=\"col-answers\" width=\"$answerwidth%\" />\n";

        $odd_even = '';
        for ($xc=1; $xc<=10; $xc++)
        {
            $odd_even = alternation($odd_even);
            $answer .= "<col class=\"$odd_even\" width=\"$cellwidth%\" />\n";
        }
        if ($this->mandatory != 'Y' && SHOW_NO_ANSWER == 1) //Question is not mandatory
        {
            $odd_even = alternation($odd_even);
            $answer .= "<col class=\"col-no-answer $odd_even\" width=\"$cellwidth$\" />\n";
        }
        $answer .= "\t</colgroup>\n\n"
        . "\t<thead>\n<tr class=\"array1\">\n"
        . "\t<th>&nbsp;</th>\n";
        for ($xc=1; $xc<=10; $xc++)
        {
            $answer .= "\t<th>$xc</th>\n";
        }
        if ($this->mandatory != 'Y' && SHOW_NO_ANSWER == 1) //Question is not mandatory
        {
            $answer .= "\t<th>".$clang->gT('No answer')."</th>\n";
        }
        $answer .= "</tr>\n</thead>";
        $answer_t_content = '<tbody';
        $trbc = '';
        foreach ($ansresult as $ansrow)
        {
            $myfname = $this->fieldname.$ansrow['title'];
            $answertext = dTexts__run($ansrow['question']);
            /* Check if this item has not been answered: the 'notanswered' variable must be an array,
            containing a list of unanswered questions, the current question must be in the array,
            and there must be no answer available for the item in this session. */
            if ($this->mandatory=='Y' && (is_array($notanswered)) && (array_search($myfname, $notanswered) !== FALSE) && ($_SESSION['survey_'.$this->surveyid][$myfname] == "") ) {
                $answertext = "<span class='errormandatory'>{$answertext}</span>";
            }
            $trbc = alternation($trbc , 'row');

            //Get array filter stuff
            list($htmltbody2, $hiddenfield)=return_array_filter_strings($this, $aQuestionAttributes, $thissurvey, $ansrow, $myfname, $trbc, $myfname,"tr","$trbc answers-list radio-list");

            $answer_t_content .= $htmltbody2
            . "\t<th class=\"answertext\">\n$answertext\n"
            . $hiddenfield
            . "<input type=\"hidden\" name=\"java$myfname\" id=\"java$myfname\" value=\"";
            if (isset($_SESSION['survey_'.$this->surveyid][$myfname]))
            {
                $answer_t_content .= $_SESSION['survey_'.$this->surveyid][$myfname];
            }
            $answer_t_content .= "\" />\n\t</th>\n";

            for ($i=1; $i<=10; $i++)
            {
                $answer_t_content .= "\t<td class=\"answer_cell_00$i answer-item radio-item\">\n<label for=\"answer$myfname-$i\">\n"
                ."\t<input class=\"radio\" type=\"radio\" name=\"$myfname\" id=\"answer$myfname-$i\" value=\"$i\" title=\"$i\"";
                if (isset($_SESSION['survey_'.$this->surveyid][$myfname]) && $_SESSION['survey_'.$this->surveyid][$myfname] == $i)
                {
                    $answer_t_content .= CHECKED;
                }
                // --> START NEW FEATURE - SAVE
                $answer_t_content .= " onclick=\"$checkconditionFunction(this.value, this.name, this.type)\" />\n</label>\n\t</td>\n";
                // --> END NEW FEATURE - SAVE
            }
            if ($this->mandatory != "Y" && SHOW_NO_ANSWER == 1)
            {
                $answer_t_content .= "\t<td class=\"answer-item radio-item noanswer-item\">\n<label for=\"answer$myfname-\">\n"
                ."\t<input class=\"radio\" type=\"radio\" name=\"$myfname\" id=\"answer$myfname-\" value=\"\" title=\"".$clang->gT('No answer')."\"";
                if (!isset($_SESSION['survey_'.$this->surveyid][$myfname]) || $_SESSION['survey_'.$this->surveyid][$myfname] == '')
                {
                    $answer_t_content .= CHECKED;
                }
                $answer_t_content .= " onclick=\"$checkconditionFunction(this.value, this.name, this.type)\" />\n</label>\n\t</td>\n";

            }
            $answer_t_content .= "</tr>\n";
            $fn++;
        }
        $answer .=  $answer_t_content . "\t\n</tbody>\n</table>\n";
        return $answer;
    }

    public function getDataEntry($idrow, &$fnames, $language)
    {
        $output = "<table>\n";
        $q = $this;
        while ($q->id == $this->id)
        {
            $output .= "\t<tr>\n"
            ."<td align='right'>{$q->sq}</td>\n"
            ."<td>\n";
            for ($j=1; $j<=10; $j++)
            {
                $output .= "\t<input type='radio' class='radiobtn' name='{$q->fieldname}' value='$j'";
                if ($idrow[$q->fieldname] == $j) {$output .= " checked";}
                $output .= " />$j&nbsp;\n";
            }
            $output .= "</td>\n"
            ."\t</tr>\n";
            if(!$fname=next($fnames)) break;
            $q=$fname['q'];
        }
        prev($fnames);
        $output .= "</table>\n";
        return $output;
    }

    public function getExtendedAnswer($value, $language)
    {
        return $value;
    }

    public function getQuotaValue($value)
    {
        $value = explode('-',$value);
        return array($this->surveyid.'X'.$this->gid.'X'.$value[0] => $value[1]);
    }

    public function setAssessment()
    {
        return false;
    }

    public function getFullAnswer($answerCode, $export, $survey)
    {
        return $answerCode;
    }

    public function getSPSSAnswers()
    {
        return array();
    }

    public function getAnswerArray($em)
    {
        return null;
    }

    public function getVarAttributeValueNAOK($name, $default, $gseq, $qseq, $ansArray)
    {
        return LimeExpressionManager::GetVarAttribute($name,'code',$default,$gseq,$qseq);
    }

    public function availableAttributes($attr = false)
    {
        $attrs=array("answer_width","array_filter","array_filter_exclude","array_filter_style","em_validation_q","em_validation_q_tip","exclude_all_others","statistics_showgraph","statistics_graphtype","hide_tip","hidden","max_answers","min_answers","page_break","public_statistics","random_order","parent_order","random_group");
        return $attr?in_array($attr,$attrs):$attrs;
    }

    public function availableOptions()
    {
        return array('other' => false, 'valid' => false, 'mandatory' => true);
    }

    public function questionProperties($prop = false)
    {
        $clang=Yii::app()->lang;
        $props=array('description' => $clang->gT("Array (10 Point Choice)"),'group' => $clang->gT('Arrays'),'subquestions' => 1,'class' => 'array-10-pt','hasdefaultvalues' => 0,'assessable' => 1,'answerscales' => 0);
        return $prop?$props[$prop]:$props;
    }
}
?>