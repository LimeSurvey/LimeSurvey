<?php
class RadioArrayQuestion extends ArrayQuestion
{
    public function getAnswerHTML()
    {
        global $thissurvey;
        global $notanswered;
        $repeatheadings = Yii::app()->getConfig("repeatheadings");
        $minrepeatheadings = Yii::app()->getConfig("minrepeatheadings");
        $extraclass ="";
        $clang = Yii::app()->lang;

        $checkconditionFunction = "checkconditions";
        $qquery = "SELECT other FROM {{questions}} WHERE qid={$this->id} AND language='".$_SESSION['survey_'.$this->surveyid]['s_lang']."'";
        $qresult = dbExecuteAssoc($qquery);     //Checked
        $qrow = $qresult->read(); $other = $qrow['other'];
        $lquery = "SELECT * FROM {{answers}} WHERE qid={$this->id} AND language='".$_SESSION['survey_'.$this->surveyid]['s_lang']."' and scale_id=0 ORDER BY sortorder, code";

        $aQuestionAttributes = $this->getAttributeValues();
        if (trim($aQuestionAttributes['answer_width'])!='')
        {
            $answerwidth=$aQuestionAttributes['answer_width'];
        }
        else
        {
            $answerwidth=20;
        }
        $columnswidth=100-$answerwidth;

        if ($aQuestionAttributes['use_dropdown'] == 1)
        {
            $useDropdownLayout = true;
            $extraclass .=" dropdown-list";
        }
        else
        {
            $useDropdownLayout = false;
        }
        if(ctype_digit(trim($aQuestionAttributes['repeat_headings'])) && trim($aQuestionAttributes['repeat_headings']!=""))
        {
            $repeatheadings = intval($aQuestionAttributes['repeat_headings']);
            $minrepeatheadings = 0;
        }
        $lresult = dbExecuteAssoc($lquery);   //Checked
        if ($useDropdownLayout === false && $lresult->count() > 0)
        {
            foreach ($lresult->readAll() as $lrow)
            {
                $labelans[]=$lrow['answer'];
                $labelcode[]=$lrow['code'];
            }

            //		$cellwidth=sprintf('%02d', $cellwidth);

            $ansquery = "SELECT question FROM {{questions}} WHERE parent_qid={$this->id} AND question like '%|%' ";
            $ansresult = dbExecuteAssoc($ansquery);  //Checked
            if ($ansresult->count()>0) {$right_exists=true;$answerwidth=$answerwidth/2;} else {$right_exists=false;}
            // $right_exists is a flag to find out if there are any right hand answer parts. If there arent we can leave out the right td column
            $ansresult = $this->getChildren();
            $anscount = count($this->getChildren());
            $fn=1;

            $numrows = count($labelans);
            if ($this->mandatory != 'Y' && SHOW_NO_ANSWER == 1)
            {
                ++$numrows;
            }
            if ($right_exists)
            {
                ++$numrows;
            }
            $cellwidth = round( ($columnswidth / $numrows ) , 1 );

            $answer_start = "\n<table class=\"question subquestions-list questions-list {$extraclass}\" summary=\"".str_replace('"','' ,strip_tags($this->text))." - an array type question\" >\n";
            $answer_head_line= "\t<td>&nbsp;</td>\n";
            foreach ($labelans as $ld)
            {
                $answer_head_line .= "\t<th>".$ld."</th>\n";
            }
            if ($right_exists) {$answer_head_line .= "\t<td>&nbsp;</td>\n";}
            if ($this->mandatory != 'Y' && SHOW_NO_ANSWER == 1) //Question is not mandatory and we can show "no answer"
            {
                $answer_head_line .= "\t<th>".$clang->gT('No answer')."</th>\n";
            }
            $answer_head = "\t<thead><tr>\n".$answer_head_line."</thead></tr>\n\t\n";

            $answer = '<tbody>';
            $trbc = '';

            foreach($ansresult as $ansrow)
            {
                if (isset($repeatheadings) && $repeatheadings > 0 && ($fn-1) > 0 && ($fn-1) % $repeatheadings == 0)
                {
                    if ( ($anscount - $fn + 1) >= $minrepeatheadings )
                    {
                        $answer .= "</tbody>\n<tbody>";// Close actual body and open another one
                        $answer .= "<tr class=\"repeat headings\">{$answer_head_line}</tr>";
                    }
                }
                $myfname = $this->fieldname.$ansrow['title'];
                $answertext = dTexts__run($ansrow['question']);
                $answertextsave=$answertext;
                if (strpos($answertext,'|'))
                {
                    $answertext=substr($answertext,0, strpos($answertext,'|'));
                }
                /* Check if this item has not been answered: the 'notanswered' variable must be an array,
                containing a list of unanswered questions, the current question must be in the array,
                and there must be no answer available for the item in this session. */

                if (strpos($answertext,'|')) {$answerwidth=$answerwidth/2;}

                if ($this->mandatory=='Y' && (is_array($notanswered)) && (array_search($myfname, $notanswered) !== FALSE) && ($_SESSION['survey_'.$this->surveyid][$myfname] == '') ) {
                    $answertext = '<span class="errormandatory">'.$answertext.'</span>';
                }
                // Get array_filter stuff
                //
                // TMSW - is this correct?
                $trbc = alternation($trbc , 'row');
                list($htmltbody2, $hiddenfield)=return_array_filter_strings($this, $aQuestionAttributes, $thissurvey, $ansrow, $myfname, $trbc, $myfname,"tr","$trbc answers-list radio-list");
                $fn++;
                $answer .= $htmltbody2;

                $answer .= "\t<th class=\"answertext\">\n$answertext"
                . $hiddenfield
                . "<input type=\"hidden\" name=\"java$myfname\" id=\"java$myfname\" value=\"";
                if (isset($_SESSION['survey_'.$this->surveyid][$myfname]))
                {
                    $answer .= $_SESSION['survey_'.$this->surveyid][$myfname];
                }
                $answer .= "\" />\n\t</th>\n";

                $thiskey=0;
                foreach ($labelcode as $ld)
                {
                    $answer .= "\t\t\t<td class=\"answer_cell_00$ld answer-item radio-item\">\n"
                    . "<label for=\"answer$myfname-$ld\">\n"
                    . "\t<input class=\"radio\" type=\"radio\" name=\"$myfname\" value=\"$ld\" id=\"answer$myfname-$ld\" title=\""
                    . HTMLEscape(strip_tags($labelans[$thiskey])).'"';
                    if (isset($_SESSION['survey_'.$this->surveyid][$myfname]) && $_SESSION['survey_'.$this->surveyid][$myfname] == $ld)
                    {
                        $answer .= CHECKED;
                    }
                    // --> START NEW FEATURE - SAVE
                    $answer .= " onclick=\"$checkconditionFunction(this.value, this.name, this.type)\" />\n"
                    . "</label>\n"
                    . "\t</td>\n";
                    // --> END NEW FEATURE - SAVE

                    $thiskey++;
                }
                if (strpos($answertextsave,'|'))
                {
                    $answertext=substr($answertextsave,strpos($answertextsave,'|')+1);
                    $answer .= "\t<th class=\"answertextright\">$answertext</th>\n";
                }
                elseif ($right_exists)
                {
                    $answer .= "\t<td class=\"answertextright\">&nbsp;</td>\n";
                }

                if ($this->mandatory != 'Y' && SHOW_NO_ANSWER == 1)
                {
                    $answer .= "\t<td class=\"answer-item radio-item noanswer-item\">\n<label for=\"answer$myfname-\">\n"
                    ."\t<input class=\"radio\" type=\"radio\" name=\"$myfname\" value=\"\" id=\"answer$myfname-\" title=\"".$clang->gT('No answer').'"';
                    if (!isset($_SESSION['survey_'.$this->surveyid][$myfname]) || $_SESSION['survey_'.$this->surveyid][$myfname] == '')
                    {
                        $answer .= CHECKED;
                    }
                    // --> START NEW FEATURE - SAVE
                    $answer .= " onclick=\"$checkconditionFunction(this.value, this.name, this.type)\"  />\n</label>\n\t</td>\n";
                    // --> END NEW FEATURE - SAVE
                }

                $answer .= "</tr>\n";
                //IF a MULTIPLE of flexi-redisplay figure, repeat the headings
            }
            $answer .= "</tbody>\n";
            $answer_cols = "\t<colgroup class=\"col-responses\">\n"
            ."\t<col class=\"col-answers\" width=\"$answerwidth%\" />\n" ;

            $odd_even = '';
            foreach ($labelans as $c)
            {
                $odd_even = alternation($odd_even);
                $answer_cols .= "<col class=\"$odd_even\" width=\"$cellwidth%\" />\n";
            }
            if ($right_exists)
            {
                $odd_even = alternation($odd_even);
                $answer_cols .= "<col class=\"answertextright $odd_even\" width=\"$answerwidth%\" />\n";
            }
            if ($this->mandatory != 'Y' && SHOW_NO_ANSWER == 1) //Question is not mandatory
            {
                $odd_even = alternation($odd_even);
                $answer_cols .= "<col class=\"col-no-answer $odd_even\" width=\"$cellwidth%\" />\n";
            }
            $answer_cols .= "\t</colgroup>\n";

            $answer = $answer_start . $answer_cols . $answer_head .$answer ."</table>\n";
        }
        elseif ($useDropdownLayout === true && $lresult->count() > 0)
        {
            foreach($lresult->readAll() as $lrow)
                $labels[]=Array('code' => $lrow['code'],
                'answer' => $lrow['answer']);
            $ansquery = "SELECT question FROM {{questions}} WHERE parent_qid={$this->id} AND question like '%|%' ";
            $ansresult = dbExecuteAssoc($ansquery);  //Checked
            if ($ansresult->count()>0) {$right_exists=true;$answerwidth=$answerwidth/2;} else {$right_exists=false;}
            // $right_exists is a flag to find out if there are any right hand answer parts. If there arent we can leave out the right td column
            $ansresult = $this->getChildren(); //Checked
            $anscount = count($ansresult);
            $fn=1;

            $numrows = count($labels);
            if ($this->mandatory != 'Y' && SHOW_NO_ANSWER == 1)
            {
                ++$numrows;
            }
            if ($right_exists)
            {
                ++$numrows;
            }
            $cellwidth = round( ($columnswidth / $numrows ) , 1 );

            $answer_start = "\n<table class=\"question subquestions-list questions-list {$extraclass}\" summary=\"".str_replace('"','' ,strip_tags($this->text))." - an array type question\" >\n";

            $answer = "\t<tbody>\n";
            $trbc = '';

            foreach ($ansresult as $ansrow)
            {
                $myfname = $this->fieldname.$ansrow['title'];
                $trbc = alternation($trbc , 'row');
                $answertext=$ansrow['question'];
                $answertextsave=$answertext;
                if (strpos($answertext,'|'))
                {
                    $answertext=substr($answertext,0, strpos($answertext,'|'));
                }
                /* Check if this item has not been answered: the 'notanswered' variable must be an array,
                containing a list of unanswered questions, the current question must be in the array,
                and there must be no answer available for the item in this session. */

                if (strpos($answertext,'|')) {$answerwidth=$answerwidth/2;}

                if ($this->mandatory=='Y' && (is_array($notanswered)) && (array_search($myfname, $notanswered) !== FALSE) && ($_SESSION['survey_'.$this->surveyid][$myfname] == '') ) {
                    $answertext = '<span class="errormandatory">'.$answertext.'</span>';
                }
                // Get array_filter stuff
                list($htmltbody2, $hiddenfield)=return_array_filter_strings($this, $aQuestionAttributes, $thissurvey, $ansrow, $myfname, $trbc, $myfname,"tr","$trbc question-item answer-item dropdown-item");
                $answer .= $htmltbody2;

                $answer .= "\t<th class=\"answertext\">\n$answertext"
                . $hiddenfield
                . "<input type=\"hidden\" name=\"java$myfname\" id=\"java$myfname\" value=\"";
                if (isset($_SESSION['survey_'.$this->surveyid][$myfname]))
                {
                    $answer .= $_SESSION['survey_'.$this->surveyid][$myfname];
                }
                $answer .= "\" />\n\t</th>\n";

                $answer .= "\t<td >\n"
                . "<select name=\"$myfname\" id=\"answer$myfname\" onchange=\"$checkconditionFunction(this.value, this.name, this.type);\">\n";

                if (!isset($_SESSION['survey_'.$this->surveyid][$myfname]) || $_SESSION['survey_'.$this->surveyid][$myfname] =='')
                {
                    $answer .= "\t<option value=\"\" ".SELECTED.'>'.$clang->gT('Please choose')."...</option>\n";
                }

                foreach ($labels as $lrow)
                {
                    $answer .= "\t<option value=\"".$lrow['code'].'" ';
                    if (isset($_SESSION['survey_'.$this->surveyid][$myfname]) && $_SESSION['survey_'.$this->surveyid][$myfname] == $lrow['code'])
                    {
                        $answer .= SELECTED;
                    }
                    $answer .= '>'.flattenText($lrow['answer'])."</option>\n";
                }
                // If not mandatory and showanswer, show no ans
                if ($this->mandatory != 'Y' && SHOW_NO_ANSWER == 1)
                {
                    $answer .= "\t<option value=\"\" ";
                    if (!isset($_SESSION['survey_'.$this->surveyid][$myfname]) || $_SESSION['survey_'.$this->surveyid][$myfname] == '')
                    {
                        $answer .= SELECTED;
                    }
                    $answer .= '>'.$clang->gT('No answer')."</option>\n";
                }
                $answer .= "</select>\n";

                if (strpos($answertextsave,'|'))
                {
                    $answertext=substr($answertextsave,strpos($answertextsave,'|')+1);
                    $answer .= "\t<th class=\"answertextright\">$answertext</th>\n";
                }
                elseif ($right_exists)
                {
                    $answer .= "\t<td class=\"answertextright\">&nbsp;</td>\n";
                }

                $answer .= "</tr>\n";
                //IF a MULTIPLE of flexi-redisplay figure, repeat the headings
                $fn++;
            }
            $answer .= "\t</tbody>";
            $answer = $answer_start . $answer . "\n</table>\n";
        }
        else
        {
            $answer = "\n<p class=\"error\">".$clang->gT("Error: There are no answer options for this question and/or they don't exist in this language.")."</p>\n";
        }
        return $answer;
    }

    public function getDataEntry($idrow, &$fnames, $language)
    {
        $clang = Yii::app()->lang;
        $output = "<table>\n";
        $q = $this;
        while ($q->id == $this->id)
        {
            $output .= "\t<tr>\n"
            ."<td>{$fname['subquestion']}";
            if (isset($fname['scale']))
            {
                $output .= " (".$fname['scale'].')';
            }
            $output .="</td>\n";
            $scale_id=0;
            if (isset($q->scale)) $scale_id=$q->scale;
            $fquery = "SELECT * FROM {{answers}} WHERE qid='{$q->id}' and scale_id={$scale_id} and language='$sDataEntryLanguage' order by sortorder, answer";
            $fresult = dbExecuteAssoc($fquery);
            $output .= "<td>\n";
            foreach ($fresult->readAll() as $frow)
            {
                $output .= "\t<input type='radio' class='radiobtn' name='{$q->fieldname}' value='{$frow['code']}'";
                if ($idrow[$q->fieldname] == $frow['code']) {$output .= " checked";}
                $output .= " />".$frow['answer']."&nbsp;\n";
            }
            //Add 'No Answer'
            $output .= "\t<input type='radio' class='radiobtn' name='{$q->fieldname}' value=''";
            if ($idrow[$q->fieldname] == '') {$output .= " checked";}
            $output .= " />".$clang->gT("No answer")."&nbsp;\n";

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
        if ($value == "-oth-")
        {
            return $language->gT("Other"). "[-oth-]";
        }
        $scale=isset($this->scale)?$this->scale:0;
        $result = Answers::model()->getAnswerFromCode($this->id,$value,$language->langcode,$scale) or die ("Couldn't get answer type."); //Checked
        if($result->count())
        {
            $result =array_values($result->readAll());
            return $result[count($result)-1]." [$value]";
        }
        return $value;
    }

    public function setAssessment()
    {
        $this->assessment_value = 0;
        if (isset($_SESSION['survey_'.$this->surveyid][$this->fieldname]))
        {
            $usquery = "SELECT assessment_value FROM {{answers}} where qid=".$this->id." and language='$baselang' and code=".dbQuoteAll($_SESSION['survey_'.$this->surveyid][$this->fieldname]);
            $usresult = dbExecuteAssoc($usquery);          //Checked
            if ($usresult)
            {
                $usrow = $usresult->read();
                $this->assessment_value=(int) $usrow['assessment_value'];
            }
        }
        return true;
    }

    public function getFullAnswer($answerCode, $export, $survey)
    {
        $answers = $survey->getAnswers($this->id, 0);
        return (isset($answers[$answerCode])) ? $answers[$answerCode]['answer'] : "";
    }

    public function getSPSSAnswers()
    {
        global $language, $length_vallabel;
        $query = "SELECT {{answers}}.code, {{answers}}.answer,
        {{questions}}.type FROM {{answers}}, {{questions}} WHERE";

        if (isset($this->scale)) $query .= " {{answers}}.scale_id = " . (int) $this->scale . " AND";

        $query .= " {{answers}}.qid = '".$this->id."' and {{questions}}.language='".$language."' and  {{answers}}.language='".$language."'
        and {{questions}}.qid='".$this->id."' ORDER BY sortorder ASC";
        $result= Yii::app()->db->createCommand($query)->query(); //Checked
        foreach ($result->readAll() as $row)
        {
            $answers[] = array('code'=>$row['code'], 'value'=>mb_substr(stripTagsFull($row["answer"]),0,$length_vallabel));
        }
        return $answers;
    }

    public function getAnswerArray($em)
    {
        return (isset($em->qans[$this->id]) ? $em->qans[$this->id] : NULL);
    }

    public function getVarAttributeValueNAOK($name, $default, $gseq, $qseq, $ansArray)
    {
        $scale_id = LimeExpressionManager::GetVarAttribute($name,'scale_id','0',$gseq,$qseq);
        $which_ans = $scale_id . '~' . $code;
        if (is_null($ansArray))
        {
            return $default;
        }
        else
        {
            if (isset($ansArray[$which_ans])) {
                $answerInfo = explode('|',$ansArray[$which_ans]);
                $answer = $answerInfo[0];
            }
            else {
                $answer = $default;
            }
            return $answer;
        }
    }

    public function availableAttributes($attr = false)
    {
        $attrs=array("answer_width","repeat_headings","array_filter","array_filter_exclude","array_filter_style","em_validation_q","em_validation_q_tip","exclude_all_others","statistics_showgraph","statistics_graphtype","hide_tip","hidden","max_answers","min_answers","page_break","public_statistics","random_order","parent_order","use_dropdown","scale_export","random_group");
        return $attr?array_key_exists($attr,$attrs):$attrs;
    }

    public function questionProperties($prop = false)
    {
        $clang=Yii::app()->lang;
        $props=array('description' => $clang->gT("Array"),'group' => $clang->gT('Arrays'),'subquestions' => 1,'class' => 'array-flexible-row','hasdefaultvalues' => 0,'assessable' => 1,'answerscales' => 1);
        return $prop?$props[$prop]:$props;
    }
}
?>