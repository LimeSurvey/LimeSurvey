<?php
class FiveRadioArrayQuestion extends RadioArrayQuestion
{
    public function getAnswerHTML()
    {
        global $notanswered, $thissurvey;
        $extraclass ="";
        $clang = Yii::app()->lang;

        $checkconditionFunction = "checkconditions";

        $aQuestionAttributes = $this->getAttributeValues();

        if (trim($aQuestionAttributes['answer_width'])!='')
        {
            $answerwidth=$aQuestionAttributes['answer_width'];
            $extraclass .=" answerwidth-".trim($aQuestionAttributes['answer_width']);
        }
        else
        {
            $answerwidth = 20;
        }
        $cellwidth  = 5; // number of columns

        if ($this->mandatory != 'Y' && SHOW_NO_ANSWER == 1) //Question is not mandatory
        {
            ++$cellwidth; // add another column
        }
        $cellwidth = round((( 100 - $answerwidth ) / $cellwidth) , 1); // convert number of columns to percentage of table width

        $ansquery = "SELECT question FROM {{questions}} WHERE parent_qid=".$this->id." AND question like '%|%'";
        $ansresult = dbExecuteAssoc($ansquery);   //Checked

        if ($ansresult->count()>0) {$right_exists=true;$answerwidth=$answerwidth/2;} else {$right_exists=false;}
        // $right_exists is a flag to find out if there are any right hand answer parts. If there arent we can leave out the right td column

        $ansresult = $this->getChildren();
        $anscount = count($ansresult);

        $fn = 1;
        $answer = "\n<table class=\"question subquestion-list questions-list\" summary=\"".str_replace('"','' ,strip_tags($this->text))." - a five point Likert scale array\">\n\n"
        . "\t<colgroup class=\"col-responses\">\n"
        . "\t<col class=\"col-answers\" width=\"$answerwidth%\" />\n";
        $odd_even = '';

        for ($xc=1; $xc<=5; $xc++)
        {
            $odd_even = alternation($odd_even);
            $answer .= "<col class=\"$odd_even\" width=\"$cellwidth%\" />\n";
        }
        if ($this->mandatory != 'Y' && SHOW_NO_ANSWER == 1) //Question is not mandatory
        {
            $odd_even = alternation($odd_even);
            $answer .= "<col class=\"col-no-answer $odd_even\" width=\"$cellwidth%\" />\n";
        }
        $answer .= "\t</colgroup>\n\n"
        . "\t<thead>\n<tr class=\"array1\">\n"
        . "\t<th>&nbsp;</th>\n";
        for ($xc=1; $xc<=5; $xc++)
        {
            $answer .= "\t<th>$xc</th>\n";
        }
        if ($right_exists) {$answer .= "\t<td width='$answerwidth%'>&nbsp;</td>\n";}
        if ($this->mandatory != 'Y' && SHOW_NO_ANSWER == 1) //Question is not mandatory
        {
            $answer .= "\t<th>".$clang->gT('No answer')."</th>\n";
        }
        $answer .= "</tr></thead>\n";

        $answer_t_content = '<tbody>';
        $trbc = '';
        $n=0;
        //return array($answer, $inputnames);
        foreach ($ansresult as $ansrow)
        {
            $myfname = $this->fieldname.$ansrow['title'];

            $answertext = dTexts__run($ansrow['question']);
            if (strpos($answertext,'|')) {$answertext=substr($answertext,0,strpos($answertext,'|'));}

            /* Check if this item has not been answered: the 'notanswered' variable must be an array,
            containing a list of unanswered questions, the current question must be in the array,
            and there must be no answer available for the item in this session. */
            if ($this->mandatory=='Y' && (is_array($notanswered)) && (array_search($myfname, $notanswered) !== FALSE) && ($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname] == '') ) {
                $answertext = "<span class=\"errormandatory\">{$answertext}</span>";
            }

            $trbc = alternation($trbc , 'row');

            // Get array_filter stuff
            list($htmltbody2, $hiddenfield)=return_array_filter_strings($this, $aQuestionAttributes, $thissurvey, $ansrow, $myfname, $trbc, $myfname,"tr","$trbc answers-list radio-list");

            $answer_t_content .= $htmltbody2
            . "\t<th class=\"answertext\" width=\"$answerwidth%\">\n$answertext\n"
            . $hiddenfield
            . "<input type=\"hidden\" name=\"java$myfname\" id=\"java$myfname\" value=\"";
            if (isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname]))
            {
                $answer_t_content .= $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname];
            }
            $answer_t_content .= "\" />\n\t</th>\n";
            for ($i=1; $i<=5; $i++)
            {
                $answer_t_content .= "\t<td class=\"answer_cell_00$i answer-item radio-item\">\n<label for=\"answer$myfname-$i\">"
                ."\n\t<input class=\"radio\" type=\"radio\" name=\"$myfname\" id=\"answer$myfname-$i\" value=\"$i\" title=\"$i\"";
                if (isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname]) && $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname] == $i)
                {
                    $answer_t_content .= CHECKED;
                }
                $answer_t_content .= " onclick=\"$checkconditionFunction(this.value, this.name, this.type)\" />\n</label>\n\t</td>\n";
            }

            $answertext2 = dTexts__run($ansrow['question']);
            if (strpos($answertext2,'|'))
            {
                $answertext2=substr($answertext2,strpos($answertext2,'|')+1);
                $answer_t_content .= "\t<td class=\"answertextright\" style='text-align:left;' width=\"$answerwidth%\">$answertext2</td>\n";
            }
            elseif ($right_exists)
            {
                $answer_t_content .= "\t<td class=\"answertextright\" style='text-align:left;' width=\"$answerwidth%\">&nbsp;</td>\n";
            }


            if ($this->mandatory != 'Y' && SHOW_NO_ANSWER == 1)
            {
                $answer_t_content .= "\t<td class=\"answer-item radio-item noanswer-item\">\n<label for=\"answer$myfname-\">"
                ."\n\t<input class=\"radio\" type=\"radio\" name=\"$myfname\" id=\"answer$myfname-\" value=\"\" title=\"".$clang->gT('No answer').'"';
                if (!isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname]) || $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname] == '')
                {
                    $answer_t_content .= CHECKED;
                }
                $answer_t_content .= " onclick='$checkconditionFunction(this.value, this.name, this.type)'  />\n</label>\n\t</td>\n";
            }

            $answer_t_content .= "</tr>\n";
            $fn++;
        }

        $answer .= $answer_t_content . "\n</tbody>\t</table>\n";
        return $answer;
    }
    
    //public function getInputNames() - inherited
    
    public function availableAttributes()
    {
        return array("answer_width","array_filter","array_filter_exclude","statistics_showgraph","statistics_graphtype","hide_tip","hidden","max_answers","min_answers","page_break","public_statistics","random_order","parent_order","random_group");
    }
}
?>