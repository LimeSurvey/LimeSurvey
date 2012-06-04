<?php
class NumberArrayQuestion extends ArrayQuestion
{
    public function getAnswerHTML()
    {
        global $thissurvey;
        global $notanswered;
        $repeatheadings = Yii::app()->getConfig("repeatheadings");
        $minrepeatheadings = Yii::app()->getConfig("minrepeatheadings");
        $extraclass ="";
        $answertypeclass = "";
        $clang = Yii::app()->lang;

        $checkconditionFunction = "fixnum_checkconditions";
        //echo '<pre>'; print_r($_POST); echo '</pre>';
        $defaultvaluescript = '';
        $qquery = "SELECT other FROM {{questions}} WHERE qid=".$this->id." AND language='".$_SESSION['survey_'.$this->surveyid]['s_lang']."' and parent_qid=0";
        $qresult = dbExecuteAssoc($qquery);
        $qrow = $qresult->read(); $other = $qrow['other'];

        $aQuestionAttributes = $this->getAttributeValues();
        if (trim($aQuestionAttributes['multiflexible_max'])!='' && trim($aQuestionAttributes['multiflexible_min']) ==''){
            $maxvalue=$aQuestionAttributes['multiflexible_max'];
            $extraclass .=" maxvalue maxvalue-".trim($aQuestionAttributes['multiflexible_max']);
            if(isset($minvalue['value']) && $minvalue['value'] == 0) {$minvalue = 0;} else {$minvalue=1;}
        }
        if (trim($aQuestionAttributes['multiflexible_min'])!='' && trim($aQuestionAttributes['multiflexible_max']) ==''){
            $minvalue=$aQuestionAttributes['multiflexible_min'];
            $extraclass .=" minvalue minvalue-".trim($aQuestionAttributes['multiflexible_max']);
            $maxvalue=$aQuestionAttributes['multiflexible_min'] + 10;
        }
        if (trim($aQuestionAttributes['multiflexible_min'])=='' && trim($aQuestionAttributes['multiflexible_max']) ==''){
            if(isset($minvalue['value']) && $minvalue['value'] == 0) {$minvalue = 0;} else {$minvalue=1;}
            $maxvalue=10;
        }
        if (trim($aQuestionAttributes['multiflexible_min']) !='' && trim($aQuestionAttributes['multiflexible_max']) !=''){
            if($aQuestionAttributes['multiflexible_min'] < $aQuestionAttributes['multiflexible_max']){
                $minvalue=$aQuestionAttributes['multiflexible_min'];
                $maxvalue=$aQuestionAttributes['multiflexible_max'];
            }
        }

        if (trim($aQuestionAttributes['multiflexible_step'])!='' && $aQuestionAttributes['multiflexible_step'] > 0)
        {
            $stepvalue=$aQuestionAttributes['multiflexible_step'];
        }
        else
        {
            $stepvalue=1;
        }

        if($aQuestionAttributes['reverse']==1)
        {
            $tmp = $minvalue;
            $minvalue = $maxvalue;
            $maxvalue = $tmp;
            $reverse=true;
            $stepvalue=-$stepvalue;
        }
        else
        {
            $reverse=false;
        }

        $checkboxlayout=false;
        if ($aQuestionAttributes['multiflexible_checkbox']!=0)
        {
            $minvalue=0;
            $maxvalue=1;
            $checkboxlayout=true;
            $answertypeclass .=" checkbox";
        }

        $inputboxlayout=false;
        if ($aQuestionAttributes['input_boxes']!=0 && !$checkboxlayout) // checkboxlayout have the
        {
            $inputboxlayout=true;
            $answertypeclass .=" numberonly text";
        }
        if (!$checkboxlayout && !$inputboxlayout)
        {
            $answertypeclass .=" dropdown";
        }

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

        if ($thissurvey['nokeyboard']=='Y')
        {
            includeKeypad();
            $kpclass = "num-keypad";
            $extraclass .=" inputkeypad";
        }
        else
        {
            $kpclass = "";
        }

        if (trim($aQuestionAttributes['answer_width'])!='')
        {
            $answerwidth=$aQuestionAttributes['answer_width'];
        }
        else
        {
            $answerwidth=20;
        }
        $columnswidth=100-($answerwidth*2);

        $lquery = "SELECT * FROM {{questions}} WHERE parent_qid={$this->id}  AND language='".$_SESSION['survey_'.$this->surveyid]['s_lang']."' and scale_id=1 ORDER BY question_order";
        $lresult = dbExecuteAssoc($lquery);
        if ($lresult->count() > 0)
        {
            foreach ($lresult->readAll() as $lrow)
            {
                $labelans[]=$lrow['question'];
                $labelcode[]=$lrow['title'];
            }
            $numrows=count($labelans);
            if ($this->mandatory != 'Y' && SHOW_NO_ANSWER == 1) {$numrows++;}
            $cellwidth=$columnswidth/$numrows;

            $cellwidth=sprintf('%02d', $cellwidth);

            $ansquery = "SELECT question FROM {{questions}} WHERE parent_qid=".$this->id." AND scale_id=0 AND question like '%|%'";
            $ansresult = dbExecuteAssoc($ansquery);
            if ($ansresult->count()>0) {$right_exists=true;$answerwidth=$answerwidth/2;} else {$right_exists=false;}
            // $right_exists is a flag to find out if there are any right hand answer parts. If there arent we can leave out the right td column
            $ansresult = $this->getChildren();
            if (trim($aQuestionAttributes['parent_order']!=''))
            {
                $iParentQID=(int) $aQuestionAttributes['parent_order'];
                $aResult=array();
                $sessionao = $_SESSION['survey_'.$this->surveyid]['answer_order'];
                foreach ($sessionao[$iParentQID] as $aOrigRow)
                {
                    $sCode=$aOrigRow['title'];
                    foreach ($ansresult as $aRow)
                    {
                        if ($sCode==$aRow['title'])
                        {
                            $aResult[]=$aRow;
                        }
                    }
                }
                $ansresult=$aResult;
            }
            $anscount = count($ansresult);
            $fn=1;

            $mycols = "\t<colgroup class=\"col-responses\">\n"
            . "\n\t<col class=\"answertext\" width=\"$answerwidth%\" />\n";

            $myheader = "\n\t<thead>\n"
            . "<tr>\n"
            . "\t<td >&nbsp;</td>\n";

            $odd_even = '';
            foreach ($labelans as $ld)
            {
                $myheader .= "\t<th>".$ld."</th>\n";
                $odd_even = alternation($odd_even);
                $mycols .= "<col class=\"$odd_even\" width=\"$cellwidth%\" />\n";
            }
            if ($right_exists)
            {
                $myheader .= "\t<td>&nbsp;</td>";
                $odd_even = alternation($odd_even);
                $mycols .= "<col class=\"answertextright $odd_even\" width=\"$answerwidth%\" />\n";
            }
            $myheader .= "</tr>\n"
            . "\t</thead>\n";
            $mycols .= "\t</colgroup>\n";

            $trbc = '';
            $answer = "\n<table class=\"question subquestions-list questions-list {$answertypeclass}-list {$extraclass}\" summary=\"".str_replace('"','' ,strip_tags($this->text))." - an array type question with dropdown responses\">\n" . $mycols . $myheader . "\n";
            $answer .= "<tbody>";
            foreach ($ansresult as $ansrow)
            {
                if (isset($repeatheadings) && $repeatheadings > 0 && ($fn-1) > 0 && ($fn-1) % $repeatheadings == 0)
                {
                    if ( ($anscount - $fn + 1) >= $minrepeatheadings )
                    {
                        $answer .= "</tbody>\n<tbody>";// Close actual body and open another one
                        $answer .= "<tr class=\"repeat headings\">\n"
                        . "\t<td>&nbsp;</td>\n";
                        foreach ($labelans as $ld)
                        {
                            $answer .= "\t<th>".$ld."</th>\n";
                        }
                        $answer .= "</tr>\n\n";
                    }
                }
                $myfname = $this->fieldname.$ansrow['title'];
                $answertext = dTexts__run($ansrow['question']);
                $answertextsave=$answertext;
                /* Check if this item has not been answered: the 'notanswered' variable must be an array,
                containing a list of unanswered questions, the current question must be in the array,
                and there must be no answer available for the item in this session. */
                if ($this->mandatory=='Y' && is_array($notanswered))
                {
                    //Go through each labelcode and check for a missing answer! If any are found, highlight this line
                    $emptyresult=0;
                    foreach($labelcode as $ld)
                    {
                        $myfname2=$myfname.'_'.$ld;
                        if((array_search($myfname2, $notanswered) !== FALSE) && $_SESSION['survey_'.$this->surveyid][$myfname2] == "")
                        {
                            $emptyresult=1;
                        }
                    }
                    if ($emptyresult == 1)
                    {
                        $answertext = '<span class="errormandatory">'.$answertext.'</span>';
                    }
                }

                // Get array_filter stuff
                $trbc = alternation($trbc , 'row');
                list($htmltbody2, $hiddenfield)=return_array_filter_strings($this, $aQuestionAttributes, $thissurvey, $ansrow, $myfname, $trbc, $myfname,"tr","$trbc subquestions-list questions-list {$answertypeclass}-list");

                $answer .= $htmltbody2;

                if (strpos($answertext,'|')) {$answertext=substr($answertext,0, strpos($answertext,'|'));}
                $answer .= "\t<th class=\"answertext\" width=\"$answerwidth%\">\n"
                . "$answertext\n"
                . $hiddenfield
                . "<input type=\"hidden\" name=\"java$myfname\" id=\"java$myfname\" value=\"";
                if (isset($_SESSION['survey_'.$this->surveyid][$myfname]))
                {
                    $answer .= $_SESSION['survey_'.$this->surveyid][$myfname];
                }
                $answer .= "\" />\n\t</th>\n";
                $first_hidden_field = '';
                $thiskey=0;
                foreach ($labelcode as $ld)
                {
                    if ($checkboxlayout == false)
                    {
                        $myfname2=$myfname."_$ld";
                        if(isset($_SESSION['survey_'.$this->surveyid][$myfname2]))
                        {
                            $myfname2_java_value = " value=\"{$_SESSION['survey_'.$this->surveyid][$myfname2]}\" ";
                        }
                        else
                        {
                            $myfname2_java_value = "";
                        }
                        $answer .= "\t<td class=\"answer_cell_00$ld question-item answer-item {$answertypeclass}-item\">\n"
                        . "<label for=\"answer{$myfname2}\">\n"
                        . "\t<input type=\"hidden\" name=\"java{$myfname2}\" id=\"java{$myfname2}\" $myfname2_java_value />\n";

                        if($inputboxlayout == false) {
                            $answer .= "\t<select class=\"multiflexiselect\" name=\"$myfname2\" id=\"answer{$myfname2}\" title=\""
                            . HTMLEscape($labelans[$thiskey]).'"'
                            . " onchange=\"$checkconditionFunction(this.value, this.name, this.type)\">\n"
                            . "<option value=\"\">".$clang->gT('...')."</option>\n";

                            for($ii=$minvalue; ($reverse? $ii>=$maxvalue:$ii<=$maxvalue); $ii+=$stepvalue) {
                                $answer .= "<option value=\"$ii\"";
                                if(isset($_SESSION['survey_'.$this->surveyid][$myfname2]) && $_SESSION['survey_'.$this->surveyid][$myfname2] == $ii) {
                                    $answer .= SELECTED;
                                }
                                $answer .= ">$ii</option>\n";
                            }
                            $answer .= "\t</select>\n";
                        } elseif ($inputboxlayout == true)
                        {
                            $sSeperator = getRadixPointData($thissurvey['surveyls_numberformat']);
                            $sSeperator = $sSeperator['seperator'];
                            $answer .= "\t<input type='text' class=\"multiflexitext $kpclass\" name=\"$myfname2\" id=\"answer{$myfname2}\" {$maxlength} size=5 title=\""
                            . HTMLEscape($labelans[$thiskey]).'"'
                            . " onchange=\"$checkconditionFunction(this.value, this.name, this.type)\" onkeypress=\"return goodchars(event,'-0123456789$sSeperator')\""
                            . " value=\"";
                            if(isset($_SESSION['survey_'.$this->surveyid][$myfname2]) && $_SESSION['survey_'.$this->surveyid][$myfname2]) {
                                $dispVal = str_replace('.',$sSeperator,$_SESSION['survey_'.$this->surveyid][$myfname2]);
                                $answer .= $dispVal;
                            }
                            $answer .= "\" />\n";
                        }
                        $answer .= "</label>\n"
                        . "\t</td>\n";

                        $thiskey++;
                    }
                    else
                    {
                        $myfname2=$myfname."_$ld";
                        if(isset($_SESSION['survey_'.$this->surveyid][$myfname2]) && $_SESSION['survey_'.$this->surveyid][$myfname2] == '1')
                        {
                            $myvalue = '1';
                            $setmyvalue = CHECKED;
                        }
                        else
                        {
                            $myvalue = '';
                            $setmyvalue = '';
                        }
                        $answer .= "\t<td class=\"answer_cell_00$ld question-item answer-item {$answertypeclass}-item\">\n"
                        //					. "<label for=\"answer{$myfname2}\">\n"
                        . "\t<input type=\"hidden\" name=\"java{$myfname2}\" id=\"java{$myfname2}\" value=\"$myvalue\"/>\n"
                        . "\t<input type=\"hidden\" name=\"$myfname2\" id=\"answer{$myfname2}\" value=\"$myvalue\" />\n";
                        $answer .= "\t<input type=\"checkbox\" name=\"cbox_$myfname2\" id=\"cbox_$myfname2\" $setmyvalue "
                        . " onclick=\"cancelBubbleThis(event); "
                        . " aelt=document.getElementById('answer{$myfname2}');"
                        . " jelt=document.getElementById('java{$myfname2}');"
                        . " if(this.checked) {"
                        . "  aelt.value=1;jelt.value=1;$checkconditionFunction(1,'{$myfname2}',aelt.type);"
                        . " } else {"
                        . "  aelt.value=0;jelt.value=0;$checkconditionFunction(0,'{$myfname2}',aelt.type);"
                        . " }; return true;\" "
                        //					. " onchange=\"checkconditions(this.value, this.name, this.type)\" "
                        . " />\n";
                        //					$answer .= "</label>\n"
                        $answer .= ""
                        . "\t</td>\n";
                        $thiskey++;
                    }
                }
                if (strpos($answertextsave,'|'))
                {
                    $answertext=substr($answertextsave,strpos($answertextsave,'|')+1);
                    $answer .= "\t<td class=\"answertextright\" style='text-align:left;' width=\"$answerwidth%\">$answertext</td>\n";
                }
                elseif ($right_exists)
                {
                    $answer .= "\t<td class=\"answertextright\" style='text-align:left;' width=\"$answerwidth%\">&nbsp;</td>\n";
                }

                $answer .= "</tr>\n";
                //IF a MULTIPLE of flexi-redisplay figure, repeat the headings
                $fn++;
            }
            $answer .= "\t</tbody>\n</table>\n";
        }
        else
        {
            $answer = "\n<p class=\"error\">".$clang->gT("Error: There are no answer options for this question and/or they don't exist in this language.")."</p>\n";
        }
        return $answer;
    }
    
    //public function getInputNames() - inherited
    
    public function createFieldmap($type=null)
    {
        $map = array();
        $abrows = getSubQuestions($this);
        foreach ($abrows as $key=>$abrow)
        {
            if($abrow['scale_id']==1) {
                $answerset[]=$abrow;
                $answerList[] = array(
                'code'=>$abrow['title'],
                'answer'=>$abrow['question'],
                );
                unset($abrows[$key]);
            }
        }
        foreach ($abrows as $abrow)
        {
            foreach($answerset as $answer)
            {
                $fieldname="{$this->surveyid}X{$this->gid}X{$this->id}{$abrow['title']}_{$answer['title']}";
                $field['fieldname']= $fieldname;
                $field['type']=$type;
                $field['sid']=$this->surveyid;
                $field['gid']=$this->gid;
                $field['qid']=$this->id;
                $field['aid']=$abrow['title']."_".$answer['title'];
                $field['sqid']=$abrow['qid'];
                $field['title']=$this->title;
                $field['question']=$this->text;
                $field['subquestion1']=$abrow['question'];
                $field['subquestion2']=$answer['question'];
                $field['group_name']=$this->groupname;
                $field['mandatory']=$this->mandatory;
                $field['hasconditions']=$this->conditionsexist;
                $field['usedinconditions']=$this->usedinconditions;
                $field['questionSeq']=$this->questioncount;
                $field['groupSeq']=$this->groupcount;
                $field['preg']=$this->preg;
                $field['answerList']=$answerList;
                $q = clone $this;
                $q->fieldname = $fieldname;
                $q->aid = $field['aid'];
                $field['q']=$q;
                $field['pq']=$this;
                $map[$fieldname]=$field;
            }
        }
        return $map;
    }
    
    public function availableAttributes($attr = false)
    {
        $attrs=array("answer_width","array_filter","array_filter_exclude","em_validation_q","em_validation_q_tip","em_validation_sq","em_validation_sq_tip","statistics_showgraph","statistics_graphtype","hide_tip","hidden","max_answers","maximum_chars","min_answers","multiflexible_max","multiflexible_min","multiflexible_step","multiflexible_checkbox","reverse","input_boxes","page_break","public_statistics","random_order","parent_order","scale_export","random_group");
        return $attr?array_key_exists($attr,$attrs):$attrs;
    }

    public function questionProperties($prop = false)
    {
        $clang=Yii::app()->lang;
        $props=array('description' => $clang->gT("Array (Numbers)"),'group' => $clang->gT('Arrays'),'subquestions' => 2,'class' => 'array-multi-flexi','hasdefaultvalues' => 0,'assessable' => 1,'answerscales' => 0);
        return $prop?$props[$prop]:$props;
    }
}
?>