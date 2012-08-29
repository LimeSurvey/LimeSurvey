<?php
class IDRadioArrayQuestion extends RadioArrayQuestion
{
    public function getAnswerHTML()
    {
        global $thissurvey;
        global $notanswered;
        $extraclass ="";
        $clang = Yii::app()->lang;

        $checkconditionFunction = "checkconditions";

        $qquery = "SELECT other FROM {{questions}} WHERE qid=".$this->id." AND language='".$_SESSION['survey_'.$this->surveyid]['s_lang']."'";
        $qresult = dbExecuteAssoc($qquery);   //Checked
        $aQuestionAttributes = $this->getAttributeValues();
        if (trim($aQuestionAttributes['answer_width'])!='')
        {
            $answerwidth=$aQuestionAttributes['answer_width'];
        }
        else
        {
            $answerwidth = 20;
        }
        $cellwidth  = 3; // number of columns
        if ($this->mandatory != 'Y' && SHOW_NO_ANSWER == 1) //Question is not mandatory
        {
            ++$cellwidth; // add another column
        }
        $cellwidth = round((( 100 - $answerwidth ) / $cellwidth) , 1); // convert number of columns to percentage of table width

        foreach($qresult->readAll() as $qrow)
        {
            $other = $qrow['other'];
        }
        $ansresult = $this->getChildren();
        $anscount = count($ansresult);

        $fn = 1;

        $answer = "\n<table class=\"question subquestions-list questions-list {$extraclass}\" summary=\"".str_replace('"','' ,strip_tags($this->text))." - Increase/Same/Decrease Likert scale array\">\n"
        . "\t<colgroup class=\"col-responses\">\n"
        . "\t<col class=\"col-answers\" width=\"$answerwidth%\" />\n";

        $odd_even = '';
        for ($xc=1; $xc<=3; $xc++)
        {
            $odd_even = alternation($odd_even);
            $answer .= "<col class=\"$odd_even\" width=\"$cellwidth%\" />\n";
        }
        if ($this->mandatory != 'Y' && SHOW_NO_ANSWER == 1) //Question is not mandatory
        {
            $odd_even = alternation($odd_even);
            $answer .= "<col class=\"col-no-answer $odd_even\" width=\"$cellwidth%\" />\n";
        }
        $answer .= "\t</colgroup>\n"
        . "\t<thead>\n"
        . "<tr>\n"
        . "\t<td>&nbsp;</td>\n"
        . "\t<th>".$clang->gT('Increase')."</th>\n"
        . "\t<th>".$clang->gT('Same')."</th>\n"
        . "\t<th>".$clang->gT('Decrease')."</th>\n";
        if ($this->mandatory != 'Y' && SHOW_NO_ANSWER == 1) //Question is not mandatory
        {
            $answer .= "\t<th>".$clang->gT('No answer')."</th>\n";
        }
        $answer .= "</tr>\n"
        ."\t</thead>\n";
        $answer_body = '<tbody>';
        $trbc = '';
        foreach($ansresult as $ansrow)
        {
            $myfname = $this->fieldname.$ansrow['title'];
            $answertext = dTexts__run($ansrow['question']);
            /* Check if this item has not been answered: the 'notanswered' variable must be an array,
            containing a list of unanswered questions, the current question must be in the array,
            and there must be no answer available for the item in this session. */
            if ($this->mandatory=='Y' && (is_array($notanswered)) && (array_search($myfname, $notanswered) !== FALSE) && ($_SESSION['survey_'.$this->surveyid][$myfname] == "") )
            {
                $answertext = "<span class=\"errormandatory\">{$answertext}</span>";
            }

            $trbc = alternation($trbc , 'row');

            // Get array_filter stuff
            list($htmltbody2, $hiddenfield)=return_array_filter_strings($this, $aQuestionAttributes, $thissurvey, $ansrow, $myfname, $trbc, $myfname,'tr',"$trbc answers-list radio-list");

            $answer_body .= $htmltbody2;

            $answer_body .= "\t<th class=\"answertext\">\n"
            . "$answertext\n"
            . $hiddenfield
            . "<input type=\"hidden\" name=\"java$myfname\" id=\"java$myfname\" value=\"";
            if (isset($_SESSION['survey_'.$this->surveyid][$myfname]))
            {
                $answer_body .= $_SESSION['survey_'.$this->surveyid][$myfname];
            }
            $answer_body .= "\" />\n\t</th>\n";

            $answer_body .= "\t<td class=\"answer_cell_I answer-item radio-item\">\n"
            . "<label for=\"answer$myfname-I\">\n"
            ."\t<input class=\"radio\" type=\"radio\" name=\"$myfname\" id=\"answer$myfname-I\" value=\"I\" title=\"".$clang->gT('Increase').'"';
            if (isset($_SESSION['survey_'.$this->surveyid][$myfname]) && $_SESSION['survey_'.$this->surveyid][$myfname] == 'I')
            {
                $answer_body .= CHECKED;
            }

            $answer_body .= " onclick=\"$checkconditionFunction(this.value, this.name, this.type)\" />\n"
            . "</label>\n"
            . "\t</td>\n"
            . "\t<td class=\"answer_cell_S answer-item radio-item\">\n"
            . "<label for=\"answer$myfname-S\">\n"
            . "\t<input class=\"radio\" type=\"radio\" name=\"$myfname\" id=\"answer$myfname-S\" value=\"S\" title=\"".$clang->gT('Same').'"';

            if (isset($_SESSION['survey_'.$this->surveyid][$myfname]) && $_SESSION['survey_'.$this->surveyid][$myfname] == 'S')
            {
                $answer_body .= CHECKED;
            }

            $answer_body .= " onclick=\"$checkconditionFunction(this.value, this.name, this.type)\" />\n"
            . "</label>\n"
            . "\t</td>\n"
            . "\t<td class=\"answer_cell_D answer-item radio-item\">\n"
            . "<label for=\"answer$myfname-D\">\n"
            . "\t<input class=\"radio\" type=\"radio\" name=\"$myfname\" id=\"answer$myfname-D\" value=\"D\" title=\"".$clang->gT('Decrease').'"';
            // --> END NEW FEATURE - SAVE
            if (isset($_SESSION['survey_'.$this->surveyid][$myfname]) && $_SESSION['survey_'.$this->surveyid][$myfname] == 'D')
            {
                $answer_body .= CHECKED;
            }

            $answer_body .= " onclick=\"$checkconditionFunction(this.value, this.name, this.type)\" />\n"
            . "</label>\n"
            . "<input type=\"hidden\" name=\"java$myfname\" id=\"java$myfname\" value=\"";

            if (isset($_SESSION['survey_'.$this->surveyid][$myfname])) {$answer_body .= $_SESSION['survey_'.$this->surveyid][$myfname];}
            $answer_body .= "\" />\n\t</td>\n";

            if ($this->mandatory != 'Y' && SHOW_NO_ANSWER == 1)
            {
                $answer_body .= "\t<td class=\"answer-item radio-item noanswer-item\">\n"
                . "<label for=\"answer$myfname-\">\n"
                . "\t<input class=\"radio\" type=\"radio\" name=\"$myfname\" id=\"answer$myfname-\" value=\"\" title=\"".$clang->gT('No answer').'"';
                if (!isset($_SESSION['survey_'.$this->surveyid][$myfname]) || $_SESSION['survey_'.$this->surveyid][$myfname] == '')
                {
                    $answer_body .= CHECKED;
                }
                $answer_body .= " onclick=\"$checkconditionFunction(this.value, this.name, this.type)\" />\n"
                . "</label>\n"
                . "\t</td>\n";
            }
            $answer_body .= "</tr>\n";
            $fn++;
        }
        $answer .=  $answer_body . "\t</tbody>\n</table>\n";
        return $answer;
    }

    public function getDataEntry($idrow, &$fnames, $language)
    {
        $output = "<table>\n";
        $q = $this;
        while ($q->id == $this->id)
        {
            $fieldn = substr($q->fieldname, 0, strlen($q->fieldname));
            $output .= "\t<tr>\n"
            ."<td align='right'>{$q->sq}</td>\n"
            ."<td>\n"
            ."\t<input type='radio' class='radiobtn' name='{$q->fieldname}' value='I'";
            if ($idrow[$q->fieldname] == "I") {$output .= " checked";}
            $output .= " />Increase&nbsp;\n"
            ."\t<input type='radio' class='radiobtn' name='{$q->fieldname}' value='S'";
            if ($idrow[$q->fieldname] == "I") {$output .= " checked";}
            $output .= " />Same&nbsp;\n"
            ."\t<input type='radio' class='radiobtn' name='{$q->fieldname}' value='D'";
            if ($idrow[$q->fieldname] == "D") {$output .= " checked";}
            $output .= " />Decrease&nbsp;\n"
            ."</td>\n"
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
        switch($value)
        {
            case "I": return $language->gT("Increase")." [$value]";
            case "D": return $language->gT("Decrease")." [$value]";
            case "S": return $language->gT("Same")." [$value]";
            default: return $value;
        }
    }

    public function setAssessment()
    {
        return false;
    }

    public function getFullAnswer($answerCode, $export, $survey)
    {
        switch ($answerCode)
        {
            case 'I':
                return $export->translator->translate('Increase', $export->languageCode);
            case 'S':
                return $export->translator->translate('Same', $export->languageCode);
            case 'D':
                return $export->translator->translate('Decrease', $export->languageCode);
        }
    }

    public function getSPSSAnswers()
    {
        $answers[] = array('code'=>1, 'value'=>$clang->gT('Increase'));
        $answers[] = array('code'=>2, 'value'=>$clang->gT('Same'));
        $answers[] = array('code'=>3, 'value'=>$clang->gT('Decrease'));
        return $answers;
    }

    public function getSPSSData($data, $iLength, $na, $qs)
    {
        if ($data == 'I')
        {
            return $sq . "'1'" . $sq;
        } else if ($data == 'S'){
            return $sq . "'2'" . $sq;
        } else if ($data == 'D'){
            return $sq . "'3'" . $sq;
        } else {
            return $na;
        }
    }

    public function getAnswerArray($em)
    {
        $clang = Yii::app()->lang;
        return array('I' => $clang->gT("Increase"), 'S' => $clang->gT("Same"), 'D' => $clang->gT("Decrease"));
    }

    public function getVarAttributeValueNAOK($name, $default, $gseq, $qseq, $ansArray)
    {
        return LimeExpressionManager::GetVarAttribute($name,'code',$default,$gseq,$qseq);
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

    public function getValueJS()
    {
        return 'return value;';
    }

    public function getDataEntryView($language)
    {
        $meaquery = "SELECT title, question FROM {{questions}} WHERE parent_qid={$this->id} AND language='{$language->getlangcode()}' ORDER BY question_order";
        $mearesult = dbExecuteAssoc($meaquery)->readAll();
        $output = "<table>";
        foreach ($mearesult as $mearow)
        {
            $output .= "<tr>";
            $output .= "<td align='right'>{$mearow['question']}</td>";
            $output .= "<td>";
            $output .= "<select name='{$this->fieldname}{$mearow['title']}'>";
            $output .= "<option value=''>{$language->gT("Please choose")}..</option>";
            $output .= "<option value='I'>{$language->gT("Increase")}..</option>";
            $output .= "<option value='S'>{$language->gT("Same")}..</option>";
            $output .= "<option value='D'>{$language->gT("Decrease")}..</option>";
            $output .= "</select>";
            $output .= "</td>";
            $output .= "</tr>";
        }
        $output .= "</table>";
        
        return $output;
    }

    public function getPrintAnswers($language)
    {
        $fieldname = $this->surveyid . 'X' . $this->gid . 'X' . $this->id;
        $condition = "parent_qid = '{$this->id}'  AND language= '{$language->getlangcode()}'";
        $mearesult= Questions::model()->getAllRecords( $condition, array('question_order'));

        $output = '
        <table>
            <thead>
                <tr>
                    <td>&nbsp;</td>
                    <th>'.$language->gT("Increase").(Yii::app()->getConfig('showsgqacode') ? " (I)" : '').'</th>
                    <th>'.$language->gT("Same").(Yii::app()->getConfig('showsgqacode') ? " (S)" : '').'</th>
                    <th>'.$language->gT("Decrease").(Yii::app()->getConfig('showsgqacode') ? " (D)": '').'</th>
                </tr>
            </thead>
            <tbody>
        ';

        $j=0;
        $rowclass = 'array1';
        foreach ($mearesult->readAll() as $mearow)
        {
            $output .= "\t\t<tr class=\"$rowclass\">\n";

            $output .= "\t\t\t<th class=\"answertext\">{$mearow['question']}".(Yii::app()->getConfig('showsgqacode') ? " (".$fieldname.$mearow['title'].")" : '')."</th>\n";
            $output .= "\t\t\t<td>".printablesurvey::input_type_image('radio',$language->gT("Increase"))."</td>\n";
            $output .= "\t\t\t<td>".printablesurvey::input_type_image('radio',$language->gT("Same"))."</td>\n";
            $output .= "\t\t\t<td>".printablesurvey::input_type_image('radio',$language->gT("Decrease"))."</td>\n";
            $output .= "\t\t</tr>\n";
            $j++;
            $rowclass = alternation($rowclass,'row');
        }
        $output .= "\t</tbody>\n</table>\n";
        return $output;
    }

    public function getPrintPDF($language)
    {
        $condition = "parent_qid = '{$this->id}'  AND language= '{$language->getlangcode()}'";
        $mearesult= Questions::model()->getAllRecords( $condition, array('question_order'));

        $pdfoutput = array();
        $j=0;
        foreach ($mearesult->readAll() as $mearow)
        {
            $pdfoutput[$j]=array($mearow['question']," o ".$language->gT("Increase")," o ".$language->gT("Same")," o ".$language->gT("Decrease"));
            $j++;
        }
        return $pdfoutput;
    }

    public function availableAttributes($attr = false)
    {
        $attrs=array("answer_width","array_filter","array_filter_exclude","array_filter_style","em_validation_q","em_validation_q_tip","exclude_all_others","statistics_showgraph","statistics_graphtype","hide_tip","hidden","max_answers","min_answers","page_break","public_statistics","random_order","parent_order","scale_export","random_group");
        return $attr?in_array($attr,$attrs):$attrs;
    }

    public function questionProperties($prop = false)
    {
        $clang=Yii::app()->lang;
        $props=array('description' => $clang->gT("Array (Increase/Same/Decrease)"),'group' => $clang->gT('Arrays'),'subquestions' => 1,'class' => 'array-increase-same-decrease','hasdefaultvalues' => 0,'assessable' => 1,'answerscales' => 0,'enum' => 0);
        return $prop?$props[$prop]:$props;
    }
}
?>