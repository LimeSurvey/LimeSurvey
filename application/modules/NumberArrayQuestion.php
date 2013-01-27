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
        $caption=$clang->gT("An array of sub-question on each cell. The sub-question text are in the table header and concerns line header. ");
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
        $inputboxlayout=false;
        if ($aQuestionAttributes['multiflexible_checkbox']!=0)
        {
            $minvalue=0;
            $maxvalue=1;
            $checkboxlayout=true;
            $answertypeclass =" checkbox";
            $caption.=$clang->gT("Check or uncheck the answer for each subquestion. ");
        }
        elseif ($aQuestionAttributes['input_boxes']!=0 )
        {
            $inputboxlayout=true;
            $answertypeclass =" numeric-item text";
            $extraclass.=" numberonly";
            $caption.=$clang->gT("Each answers are a number. ");
        }
        else
        {
            $answertypeclass =" dropdown";
            $caption.=$clang->gT("Select the answer for each subquestion. ");
        }
        if(ctype_digit(trim($aQuestionAttributes['repeat_headings'])) && trim($aQuestionAttributes['repeat_headings']!=""))
        {
            $repeatheadings = intval($aQuestionAttributes['repeat_headings']);
            $minrepeatheadings = 0;
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
            if ($ansresult->count()>0)
            {
                $right_exists=true;
                $answerwidth=$answerwidth/2;
                $caption.=$clang->gT("The last cell give some information. ");
            }
            else
            {
                $right_exists=false;
            }
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
            $answer_head_line = "\t<td >&nbsp;</td>\n";
            $odd_even = '';
            foreach ($labelans as $ld)
            {
                $answer_head_line .= "\t<th>".$ld."</th>\n";
                $odd_even = alternation($odd_even);
                $mycols .= "<col class=\"$odd_even\" width=\"$cellwidth%\" />\n";
            }
            if ($right_exists)
            {
                $answer_head_line .= "\t<td>&nbsp;</td>";
                $odd_even = alternation($odd_even);
                $mycols .= "<col class=\"answertextright $odd_even\" width=\"$answerwidth%\" />\n";
                $caption.=$clang->gT("The last cell give some information. ");
            }
            $answer_head = "\n\t<thead>\n<tr>\n"
            . $answer_head_line
            . "</tr>\n\t</thead>\n";
            $mycols .= "\t</colgroup>\n";

            $trbc = '';
            $answer = "\n<table class=\"question subquestions-list questions-list {$answertypeclass}-list {$extraclass}\" summary=\"".str_replace('"','' ,strip_tags($this->text))." - an array type question with dropdown responses\">\n" 
            . "<caption class=\"hide screenreader\">{$caption}</caption>\n"
            . $mycols . $answer_head . "\n";
            $answer .= "<tbody>";
            foreach ($ansresult as $ansrow)
            {
                if (isset($repeatheadings) && $repeatheadings > 0 && ($fn-1) > 0 && ($fn-1) % $repeatheadings == 0)
                {
                    if ( ($anscount - $fn + 1) >= $minrepeatheadings )
                    {
                        $answer .= "</tbody>\n<tbody>";// Close actual body and open another one
                        $answer .= "<tr class=\"repeat headings\">\n"
                        . $answer_head_line
                        . "</tr>\n\n";
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
                        . "\t<input type=\"hidden\" name=\"java{$myfname2}\" id=\"java{$myfname2}\" $myfname2_java_value />\n"
                        . "<label for=\"answer{$myfname2}\" class=\"hide\">$ld</label>\n";
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
                        }
                        else
                        {
                            $sSeperator = getRadixPointData($thissurvey['surveyls_numberformat']);
                            $sSeperator = $sSeperator['seperator'];
                            $answer .= "\t<input type='text' class=\"multiflexitext $kpclass $extraclass\" name=\"$myfname2\" id=\"answer{$myfname2}\" {$maxlength} size=5 title=\""
                            . HTMLEscape($labelans[$thiskey]).'"'
                            . " onkeyup=\"$checkconditionFunction(this.value, this.name, this.type)\""
                            . " value=\"";
                            if(isset($_SESSION['survey_'.$this->surveyid][$myfname2]) && is_numeric($_SESSION['survey_'.$this->surveyid][$myfname2])) {
                                $dispVal = str_replace('.',$sSeperator,$_SESSION['survey_'.$this->surveyid][$myfname2]);
                                $answer .= $dispVal;
                            }
                            $answer .= "\" />\n";
                        }
                        $answer .= "\t</td>\n";

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
                        . "\t<input type=\"hidden\" name=\"java{$myfname2}\" id=\"java{$myfname2}\" value=\"$myvalue\"/>\n"
                        . "\t<input type=\"hidden\" name=\"$myfname2\" id=\"answer{$myfname2}\" value=\"$myvalue\" />\n";
                        // This one can not be done without javascript, TODO move js to a global js for whole question
                        $answer .= "\t<input type=\"checkbox\" class=\"checkbox\" name=\"cbox_$myfname2\" id=\"cbox_$myfname2\" $setmyvalue "
                        . " onclick=\"cancelBubbleThis(event); "
                        . " aelt=document.getElementById('answer{$myfname2}');"
                        . " jelt=document.getElementById('java{$myfname2}');"
                        . " if(this.checked) {"
                        . "  aelt.value=1;jelt.value=1;$checkconditionFunction(1,'{$myfname2}',aelt.type);"
                        . " } else {"
                        . "  aelt.value=0;jelt.value=0;$checkconditionFunction(0,'{$myfname2}',aelt.type);"
                        . " }; return true;\" "
                        . " />\n";
                        $answer .= "<label for=\"cbox_{$myfname2}\" class=\"hide\">$ld</label>\n"
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

    public function getDataEntry($idrow, &$fnames, $language)
    {
        $qidattributes=$this->getAttributeValues();
        if (trim($qidattributes['multiflexible_max'])!='' && trim($qidattributes['multiflexible_min']) ==''){
            $maxvalue=$qidattributes['multiflexible_max'];
            $minvalue=1;
        }
        if (trim($qidattributes['multiflexible_min'])!='' && trim($qidattributes['multiflexible_max']) ==''){
            $minvalue=$qidattributes['multiflexible_min'];
            $maxvalue=$qidattributes['multiflexible_min'] + 10;
        }
        if (trim($qidattributes['multiflexible_min'])=='' && trim($qidattributes['multiflexible_max']) ==''){
            $minvalue=1;
            $maxvalue=10;
        }
        if (trim($qidattributes['multiflexible_min']) !='' && trim($qidattributes['multiflexible_max']) !=''){
            if($qidattributes['multiflexible_min'] < $qidattributes['multiflexible_max']){
                $minvalue=$qidattributes['multiflexible_min'];
                $maxvalue=$qidattributes['multiflexible_max'];
            }
        }


        if (trim($qidattributes['multiflexible_step'])!='') {
            $stepvalue=$qidattributes['multiflexible_step'];
        } else {
            $stepvalue=1;
        }
        if ($qidattributes['multiflexible_checkbox']!=0) {
            $minvalue=0;
            $maxvalue=1;
            $stepvalue=1;
        }
        $output = "<table>\n";
        $q = $this;
        while ($q->id == $this->id)
        {
            $output .= "\t<tr>\n"
            . "<td>{$q->sq1}:{$q->sq2}</td>\n";
            $output .= "<td>\n";
            if ($qidattributes['input_boxes']!=0) {
                $output .= "\t<input type='text' name='{$q->fieldname}' value='";
                if (!empty($idrow[$q->fieldname])) {$output .= $idrow[$q->fieldname];}
                $output .= "' size=4 />";
            } else {
                $output .= "\t<select name='{$q->fieldname}'>\n";
                $output .= "<option value=''>...</option>\n";
                for($ii=$minvalue;$ii<=$maxvalue;$ii+=$stepvalue)
                {
                    $output .= "<option value='$ii'";
                    if($idrow[$q->fieldname] == $ii) {$output .= " selected";}
                    $output .= ">$ii</option>\n";
                }
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

    public function createFieldmap()
    {
        $map = array();
        $abrows = getSubQuestions($this);
        $answerset=array();
        foreach ($abrows as $key=>$abrow)
        {
            if($abrow['scale_id']==1) {
                $answerset[]=$abrow;
                unset($abrows[$key]);
            }
        }
        foreach ($abrows as $abrow)
        {
            foreach($answerset as $answer)
            {
                $fieldname="{$this->surveyid}X{$this->gid}X{$this->id}{$abrow['title']}_{$answer['title']}";
                $q = clone $this;
                $q->fieldname = $fieldname;
                $q->aid = $abrow['title']."_".$answer['title'];
                $q->sq1=$abrow['question'];
                $q->sq2=$answer['question'];
                $q->sqid=$abrow['qid'];
                $q->preg=$this->haspreg;
                $map[$fieldname]=$q;
            }
        }
        return $map;
    }

    public function statisticsFieldmap()
    {
        return true;
    }

    public function getDBField()
    {
        return 'text';
    }

    public function getFieldSubHeading($survey, $export, $code)
    {
        //The headers created by this section of code are significantly different from
        //the old code.  I believe that they are more accurate. - elameno
        list($scaleZeroTitle, $scaleOneTitle) = explode('_', $this->aid);
        if($code) return' ['.$scaleZeroTitle.']['.$scaleOneTitle.']';

        $sqs = $survey->getSubQuestionArrays($this->id);

        $scaleZeroText = '';
        $scaleOneText = '';
        foreach ($sqs as $sq)
        {
            if ($sq['title'] == $scaleZeroTitle && $sq['scale_id'] == 0)
            {
                $scaleZeroText = $sq['question'];
            }
            elseif ($sq['title'] == $scaleOneTitle && $sq['scale_id'] == 1)
            {
                $scaleOneText = $sq['question'];
            }
        }

        return ' ['.$export->stripTagsFull($scaleZeroText).
               ']['.$export->stripTagsFull($scaleOneText).']';
    }

    public function getSPSSAnswers()
    {
        $answers = array();
        //Get the labels that could apply!
        $qidattributes=$this->getAttributeValues();
        if (trim($qidattributes['multiflexible_max'])!='') {
            $maxvalue=$qidattributes['multiflexible_max'];
        } else {
            $maxvalue=10;
        }
        if (trim($qidattributes['multiflexible_min'])!='')
        {
            $minvalue=$qidattributes['multiflexible_min'];
        } else {
            $minvalue=1;
        }
        if (trim($qidattributes['multiflexible_step'])!='')
        {
            $stepvalue=$qidattributes['multiflexible_step'];
        } else {
            $stepvalue=1;
        }
        if ($qidattributes['multiflexible_checkbox']!=0) {
            $minvalue=0;
            $maxvalue=1;
            $stepvalue=1;
        }
        for ($i=$minvalue; $i<=$maxvalue; $i+=$stepvalue)
        {
            $answers[] = array('code'=>$i, 'value'=>$i);
        }
        return $answers;
    }

    public function jsVarNameOn()
    {
        return 'answer'.$this->fieldname;
    }

    public function onlyNumeric()
    {
        return true;
    }

    public function getSqsuffix()
    {
        return '_' . substr($this->aid,0,strpos($this->aid,'_'));
    }

    public function getExportVarName()
    {
        $sgqa_len = strlen($this->surveyid . 'X'. $this->gid . 'X' . $this->id);
        return $this->title . '_' . substr($this->getRowDivID(),$sgqa_len);
    }

    public function getQuestion()
    {
        return $this->sq1 . '[' . $this->sq2 . ']';
    }

    public function getRowDivID()
    {
        return substr($this->fieldname,0,strpos($this->fieldname,'_'));
    }

    public function getExportRowDivID()
    {
        return $this->getRowDivID();
    }

    public function getArrayFilterNames($sgq, $subqs, $qans, $sqsuffix, $equal = true)
    {
        $fsqs = array();
        foreach ($subqs as $fsq)
        {
            $attributes = $this->getAttributeValues();
            if ($attributes['multiflexible_checkbox']=='1')
            {
                if ($fsq['sqsuffix'] == $sqsuffix)
                {
                    $fsqs[] = $sgq . $fsq['csuffix'] . '.NAOK' . ($equal ? '==' : '!=') . '"1"';
                }
            }
            else
            {
                if ($fsq['sqsuffix'] == $sqsuffix)
                {
                    $fsqs[] = ($equal ? '!' : '') . 'is_empty(' . $sgq . $fsq['csuffix'] . '.NAOK)';
                }
            }
        }
        if (count($fsqs) > 0)
        {
            return '(' . implode(' ' . ($equal ? 'or' : 'and') . ' ', $fsqs) . ')';
        }
        else
        {
            return null;
        }
    }

    public function getPregSQ($sgqaNaming, $sq)
    {
        $sgqa = substr($sq['jsVarName'],4);
        if ($sgqaNaming)
        {
            return '(if(is_empty('.$sgqa.'.NAOK),0,!regexMatch("' . $this->preg . '", ' . $sgqa . '.NAOK)))';
        }
        else
        {
            return '(if(is_empty('.$sq['varName'].'.NAOK),0,!regexMatch("' . $this->preg . '", ' . $sq['varName'] . '.NAOK)))';
        }
    }

    public function getPregEqn($sgqaNaming, $sq)
    {
        $sgqa = substr($sq['jsVarName'],4);
        if ($sgqaNaming)
        {
            return '(is_empty('.$sgqa.'.NAOK) || regexMatch("' . $this->preg . '", ' . $sgqa . '.NAOK))';
        }
        else
        {
            return '(is_empty('.$sq['varName'].'.NAOK) || regexMatch("' . $this->preg . '", ' . $sq['varName'] . '.NAOK))';
        }
    }

    public function compareField($sgqa, $sq)
    {
        return preg_match('/^' . $sq['rowdivid'] . '_/', $sgqa);
    }

    public function includeRelevanceStatus()
    {
        return true;
    }

    public function getMandatoryTip()
    {
        $clang=Yii::app()->lang;
        $attributes = $this->getAttributeValues();
        if ($attributes['multiflexible_checkbox'] == 1)
        {
            return $clang->gT('Please check at least one box per row').'.';
        }
        else
        {
            return $clang->gT('Please complete all parts').'.';
        }
    }


    public function mandatoryViolation($relevantSQs, $unansweredSQs, $subsqs, $sgqas)
    {
        $clang=Yii::app()->lang;
        $attributes = $this->getAttributeValues();
        if ($attributes['multiflexible_checkbox'] == 1)
        {
            foreach ($subqs as $sq)
            {
                if (!isset($_SESSION['survey_' . $this->surveyid]['relevanceStatus'][$sq['rowdivid']]) || $_SESSION['survey_' . $this->surveyid]['relevanceStatus'][$sq['rowdivid']])
                {
                    $rowCount=0;
                    $numUnanswered=0;
                    foreach ($sgqas as $s)
                    {
                        if (strpos($s, $sq['rowdivid']) !== false)
                        {
                            ++$rowCount;
                            if (array_search($s,$unansweredSQs) !== false) {
                                ++$numUnanswered;
                            }
                        }
                    }
                    if ($rowCount > 0 && $rowCount == $numUnanswered)
                    {
                        return true;
                    }
                }
            }
        }
        else
        {
            if (count($unansweredSQs) > 0)
            {
                return true; // TODO - what about 'other'?
            }
        }

        return false;
    }

    public function availableOptions()
    {
        return array('other' => false, 'valid' => true, 'mandatory' => true);
    }

    public function getDataEntryView($language)
    {
        $qidattributes = $this->getAttributeValues();
        $minvalue=1;
        $maxvalue=10;
        if (trim($qidattributes['multiflexible_max'])!='' && trim($qidattributes['multiflexible_min']) =='') {
            $maxvalue=$qidattributes['multiflexible_max'];
            $minvalue=1;
        }
        if (trim($qidattributes['multiflexible_min'])!='' && trim($qidattributes['multiflexible_max']) =='') {
            $minvalue=$qidattributes['multiflexible_min'];
            $maxvalue=$qidattributes['multiflexible_min'] + 10;
        }
        if (trim($qidattributes['multiflexible_min']) !='' && trim($qidattributes['multiflexible_max']) !='') {
            if($qidattributes['multiflexible_min'] < $qidattributes['multiflexible_max']){
                $minvalue=$qidattributes['multiflexible_min'];
                $maxvalue=$qidattributes['multiflexible_max'];
            }
        }

        if (trim($qidattributes['multiflexible_step'])!='') {
            $stepvalue=$qidattributes['multiflexible_step'];
        } else {
            $stepvalue=1;
        }
        if ($qidattributes['multiflexible_checkbox']!=0)
        {
            $minvalue=0;
            $maxvalue=1;
            $stepvalue=1;
        }

        $lquery = "SELECT question, title FROM {{questions}} WHERE parent_qid={$this->id} and scale_id=1 and language='{$language->getlangcode()}' ORDER BY question_order";
        $lresult=dbExecuteAssoc($lquery)->readAll() or die ("Couldn't get labels, Type \":\"<br />$lquery<br />");

        $meaquery = "SELECT question, title FROM {{questions}} WHERE parent_qid={$this->id} and scale_id=0 and language='{$language->getlangcode()}' ORDER BY question_order";
        $mearesult=dbExecuteAssoc($meaquery)->readAll() or die ("Couldn't get answers, Type \":\"<br />$meaquery<br />");
        $output = "<table>";
        $output .= "<tr><td></td>";
        foreach($lresult  as $data)
        {
            $output .= "<th>{$data['question']}</th>";
            $labelcodes[]=$data['title'];
        }

        $output .= "</tr>";
        $i=0;
        foreach ($mearesult as $mearow)
        {
            if (strpos($mearow['question'],'|'))
            {
                $answerleft=substr($mearow['question'],0,strpos($mearow['question'],'|'));
            }
            else
            {
                $answerleft=$mearow['question'];
            }

            $output .= "<tr>";
            $output .= "<td align='right'>{$answerleft}</td>";
            foreach($labelcodes as $ld)
            {
                $output .= "<td>";
                if ($qidattributes['input_boxes']!=0) {
                    $output .= "<input type='text' name='{$this->fieldname}{$mearow['title']}_{$ld}' size=4 />";
                } else {
                    $output .= "<select name='{$this->fieldname}{$mearow['title']}_{$ld}'>";
                    $output .= "<option value=''>...</option>";
                    for($ii=$minvalue;$ii<=$maxvalue;$ii+=$stepvalue)
                    {
                        $output .= "<option value='{$ii}'>{$ii}</option>";
                    }
                    $output .= "</select>";
                }
                $output .= "</td>";
            }
            $output .= "</tr>";
            $i++;
        }
        $output .= "</table>";
        return $output;
    }

    public function getPrintAnswers($language)
    {
        $fieldname = $this->surveyid . 'X' . $this->gid . 'X' . $this->id;
        $qidattributes = $this->getAttributeValues();
        if (trim($qidattributes['multiflexible_max'])!='' && trim($qidattributes['multiflexible_min']) =='') {
            $maxvalue=$qidattributes['multiflexible_max'];
            $minvalue=1;
        }
        if (trim($qidattributes['multiflexible_min'])!='' && trim($qidattributes['multiflexible_max']) =='') {
            $minvalue=$qidattributes['multiflexible_min'];
            $maxvalue=$qidattributes['multiflexible_min'] + 10;
        }
        if (trim($qidattributes['multiflexible_min'])=='' && trim($qidattributes['multiflexible_max']) =='') {
            $minvalue=1;
            $maxvalue=10;
        }
        if (trim($qidattributes['multiflexible_min']) !='' && trim($qidattributes['multiflexible_max']) !='') {
            if($qidattributes['multiflexible_min'] < $qidattributes['multiflexible_max']){
                $minvalue=$qidattributes['multiflexible_min'];
                $maxvalue=$qidattributes['multiflexible_max'];
            }
        }

        if (trim($qidattributes['multiflexible_step'])!='') {
            $stepvalue=$qidattributes['multiflexible_step'];
        }
        else
        {
            $stepvalue=1;
        }
        if ($qidattributes['multiflexible_checkbox']!=0) {
            $checkboxlayout=true;
        } else {
            $checkboxlayout=false;
        }
        $mearesult=Questions::model()->getAllRecords(" parent_qid='{$this->id}' and scale_id=0 AND language='{$language->getlangcode()}' ", array('question_order'));

        $output = "\n<table>\n\t<thead>\n\t\t<tr>\n\t\t\t<td>&nbsp;</td>\n";
        $fresult=Questions::model()->getAllRecords(" parent_qid='{$this->id}' and scale_id=1 AND language='{$language->getlangcode()}' ", array('question_order'));

        $fcount = $fresult->getRowCount();
        $i=0;

        //array to temporary store X axis question codes
        $xaxisarray = array();
        $result = $fresult->readAll();
        foreach ($result as $frow)

        {
            $output .= "\t\t\t<th>{$frow['question']}</th>\n";
            $i++;

            //add current question code
            $xaxisarray[$i] = $frow['title'];
        }
        $output .= "\t\t</tr>\n\t</thead>\n\n\t<tbody>\n";
        $rowclass = 'array1';

        $result = $mearesult->readAll();
        foreach ($result as $frow)
        {
            $output .= "\t<tr class=\"$rowclass\">\n";
            $rowclass = alternation($rowclass,'row');

            $answertext=$frow['question'];
            if (strpos($answertext,'|')) {$answertext=substr($answertext,0, strpos($answertext,'|'));}
            $output .= "\t\t\t\t\t<th class=\"answertext\">$answertext</th>\n";
            for ($i=1; $i<=$fcount; $i++)
            {

                $output .= "\t\t\t<td>\n";
                if ($checkboxlayout === false)
                {
                    $output .= "\t\t\t\t".printablesurvey::input_type_image('text','',4);
                    $output .= (Yii::app()->getConfig('showsgqacode') ? " (".$fieldname.$frow['title']."_".$xaxisarray[$i].") " : '')."\n";
                }
                else
                {
                    $output .= "\t\t\t\t".printablesurvey::input_type_image('checkbox');
                    $output .= (Yii::app()->getConfig('showsgqacode') ? " (".$fieldname.$frow['title']."_".$xaxisarray[$i].") " : '')."\n";
                }
                $output .= "\t\t\t</td>\n";
            }
            $answertext=$frow['question'];
            if (strpos($answertext,'|'))
            {
                $answertext=substr($answertext,strpos($answertext,'|')+1);
                $output .= "\t\t\t<th class=\"answertextright\">$answertext</th>\n";
            }
            $output .= "\t\t</tr>\n";
        }
        $output .= "\t</tbody>\n</table>\n";
        return $output;
    }

    public function getPrintPDF($language)
    {
        $qidattributes = $this->getAttributeValues();
        if ($qidattributes['multiflexible_checkbox']!=0) {
            $checkboxlayout=true;
        } else {
            $checkboxlayout=false;
        }
        $mearesult=Questions::model()->getAllRecords(" parent_qid='{$this->id}' and scale_id=0 AND language='{$language->getlangcode()}' ", array('question_order'));

        $fresult=Questions::model()->getAllRecords(" parent_qid='{$this->id}' and scale_id=1 AND language='{$language->getlangcode()}' ", array('question_order'));
        $fcount = $fresult->getRowCount();

        $i=0;
        $pdfoutput = array();
        $pdfoutput[0][0]=' ';

        $result = $fresult->readAll();
        foreach ($result as $frow)
        {
            $i++;
            $pdfoutput[0][$i]=$frow['question'];
        }
        $a=1; //Counter for pdfoutput

        $result = $mearesult->readAll();
        foreach ($result as $frow)
        {
            $answertext=$frow['question'];
            if (strpos($answertext,'|')) {$answertext=substr($answertext,0, strpos($answertext,'|'));}
            $pdfoutput[$a][0]=$answertext;
            for ($i=1; $i<=$fcount; $i++)
            {
                if ($checkboxlayout === false)
                {
                    $pdfoutput[$a][$i]="__";
                }
                else
                {
                    $pdfoutput[$a][$i]="o";
                }
            }
            $a++;
        }

        return $pdfoutput;
    }

    public function getConditionAnswers()
    {
        $canswers = array();

        $qidattributes=$this->getAttributeValues();
        if (isset($qidattributes['multiflexible_max']) && trim($qidattributes['multiflexible_max'])!='') {
            $maxvalue=floatval($qidattributes['multiflexible_max']);
        } else {
            $maxvalue=10;
        }
        if (isset($qidattributes['multiflexible_min']) && trim($qidattributes['multiflexible_min'])!='') {
            $minvalue=floatval($qidattributes['multiflexible_min']);
        } else {
            $minvalue=1;
        }
        if (isset($qidattributes['multiflexible_step']) && trim($qidattributes['multiflexible_step'])!='') {
            $stepvalue=floatval($qidattributes['multiflexible_step']);
            if ($stepvalue==0) $stepvalue=1;
        } else {
            $stepvalue=1;
        }

        if (isset($qidattributes['multiflexible_checkbox']) && $qidattributes['multiflexible_checkbox']!=0) {
            $minvalue=0;
            $maxvalue=1;
            $stepvalue=1;
        }

        // Get the Y-Axis
        $sLanguage=Survey::model()->findByPk($this->surveyid)->language;
        $y_axis_db = Questions::model()->findAllByAttributes(
            array('sid' => $this->surveyid, 'parent_qid' => $this->id, 'language' => $sLanguage, 'scale_id' => 0),
            array('order' => 'question_order')
        );

        // Get the X-Axis
        $x_axis_db = Questions::model()->findAllByAttributes(
            array('sid' => $this->surveyid, 'parent_qid' => $this->id, 'language' => $sLanguage, 'scale_id' => 1),
            array('order' => 'question_order')
        );

        foreach ($x_axis_db as $frow)
        {
            $x_axis[$frow['title']]=$frow['question'];
        }

        foreach ($y_axis_db as $yrow)
        {
            foreach($x_axis as $key=>$val)
            {

                for($ii=$minvalue; $ii<=$maxvalue; $ii+=$stepvalue)
                {
                    $canswers[]=array($this->surveyid.'X'.$this->gid.'X'.$this->id.$yrow['title']."_".$key, $ii, $ii);
                }
            }
        }

        return $canswers;
    }

    public function getConditionQuestions()
    {
        $cquestions = array();

        // Get the Y-Axis
        $sLanguage=Survey::model()->findByPk($this->surveyid)->language;
        $y_axis_db = Questions::model()->findAllByAttributes(
            array('sid' => $this->surveyid, 'parent_qid' => $this->id, 'language' => $sLanguage, 'scale_id' => 0),
            array('order' => 'question_order')
        );

        // Get the X-Axis
        $x_axis_db = Questions::model()->findAllByAttributes(
            array('sid' => $this->surveyid, 'parent_qid' => $this->id, 'language' => $sLanguage, 'scale_id' => 1),
            array('order' => 'question_order')
        );

        foreach ($x_axis_db as $frow)
        {
            $x_axis[$frow['title']]=$frow['question'];
        }

        foreach ($y_axis_db as $yrow)
        {
            foreach($x_axis as $key=>$val)
            {
                $shortquestion=$this->title.":{$yrow['title']}:$key: [".strip_tags($yrow['question']). "][" .strip_tags($val). "] " . flattenText($this->text);
                $cquestions[]=array($shortquestion, $this->id, false, $this->surveyid.'X'.$this->gid.'X'.$this->id.$yrow['title']."_".$key);
            }
        }
        return $cquestions;
    }

    public function QueXMLAppendAnswers(&$question)
    {
        quexml_create_subQuestions($question,$this->id,$this->surveyid . 'X' . $this->gid . 'X' . $this->id);
        $mcb  = quexml_get_lengthth($this->id,'multiflexible_checkbox',-1);
        if ($mcb != -1)
            quexml_create_multi($question,$this->id,$this->surveyid . 'X' . $this->gid . 'X' . $this->id,1);
        else
        {
            //get multiflexible_max - if set then make boxes of max this width
            $mcm = strlen(quexml_get_lengthth($this->id,'multiflexible_max',1));
            quexml_create_multi($question,$this->id,$this->surveyid . 'X' . $this->gid . 'X' . $this->id,1,array('f' => 'integer', 'len' => $mcm, 'lab' => ''));
        }
    }

    public function availableAttributes($attr = false)
    {
        $attrs=array("answer_width","repeat_headings","array_filter","array_filter_exclude","array_filter_style","em_validation_q","em_validation_q_tip","em_validation_sq","em_validation_sq_tip","statistics_showgraph","statistics_graphtype","hide_tip","hidden","max_answers","maximum_chars","min_answers","multiflexible_max","multiflexible_min","multiflexible_step","multiflexible_checkbox","reverse","input_boxes","page_break","public_statistics","random_order","parent_order","scale_export","random_group");
        return $attr?in_array($attr,$attrs):$attrs;
    }

    public function questionProperties($prop = false)
    {
        $clang=Yii::app()->lang;
        $props=array('description' => $clang->gT("Array (Numbers)"),'group' => $clang->gT('Arrays'),'subquestions' => 2,'class' => 'array-multi-flexi','hasdefaultvalues' => 0,'assessable' => 1,'answerscales' => 0,'enum' => 0);
        return $prop?$props[$prop]:$props;
    }
}
?>
