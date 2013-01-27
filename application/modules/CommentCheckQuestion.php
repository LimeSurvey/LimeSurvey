<?php
class CommentCheckQuestion extends CheckQuestion
{
    public function getAnswerHTML()
    {
        global $thissurvey;

        $clang = Yii::app()->lang;
        if ($thissurvey['nokeyboard']=='Y')
        {
            includeKeypad();
            $kpclass = "text-keypad";
        }
        else
        {
            $kpclass = "";
        }

        $attribute_ref=false;
        $qaquery = "SELECT qid,attribute FROM {{question_attributes}} WHERE value LIKE '".strtolower($this->title)."'";
        $qaresult = Yii::app()->db->createCommand($qaquery)->query();     //Checked

        $attribute_ref=false;
        foreach($qaresult->readAll() as $qarow)
        {
            $qquery = "SELECT qid FROM {{questions}} WHERE sid=".$thissurvey['sid']." AND qid=".$qarow['qid'];
            $qresult = Yii::app()->db->createCommand($qquery)->query(); //Checked
            if (count($qresult)> 0)
            {
                $attribute_ref = true;
            }
        }

        $checkconditionFunction = "checkconditions";

        $aQuestionAttributes = $this->getAttributeValues();

        if ($aQuestionAttributes['other_numbers_only']==1)
        {
            $sSeperator = getRadixPointData($thissurvey['surveyls_numberformat']);
            $sSeperator = $sSeperator['seperator'];
            $oth_checkconditionFunction = "fixnum_checkconditions";
        }
        else
        {
            $oth_checkconditionFunction = "checkconditions";
        }

        if (trim($aQuestionAttributes['other_replace_text'][$_SESSION['survey_'.$this->surveyid]['s_lang']])!='')
        {
            $othertext=$aQuestionAttributes['other_replace_text'][$_SESSION['survey_'.$this->surveyid]['s_lang']];
        }
        else
        {
            $othertext=$clang->gT('Other:');
        }

        $callmaxanswscriptother = '';

        $ansresult = $this->getChildren();
        $anscount = count($ansresult)*2;

        $answer = "<input type='hidden' name='MULTI$this->fieldname' value='$anscount' />\n";
        $answer_main = '';

        $fn = 1;

        if($this->isother == 'Y')
        {
            $label_width = 25;
        }
        else
        {
            $label_width = 0;
        }

        foreach ($ansresult as $ansrow)
        {
            $myfname = $this->fieldname.$ansrow['title'];
            $trbc='';
            /* Check for array_filter */

            list($htmltbody2, $hiddenfield)=return_array_filter_strings($this, $aQuestionAttributes, $thissurvey, $ansrow, $myfname, $trbc, $myfname, "li","question-item answer-item checkbox-text-item");

            if($label_width < strlen(trim(strip_tags($ansrow['question']))))
            {
                $label_width = strlen(trim(strip_tags($ansrow['question'])));
            }

            $myfname2 = $myfname."comment";
            $startitem = "\t$htmltbody2\n";
            /* Print out the checkbox */
            $answer_main .= $startitem;
            $answer_main .= "\t$hiddenfield\n";
            $answer_main .= "<span class=\"option\">\n"
            . "\t<input class=\"checkbox\" type=\"checkbox\" name=\"$myfname\" id=\"answer$myfname\" value=\"Y\"";

            /* If the question has already been ticked, check the checkbox */
            if (isset($_SESSION['survey_'.$this->surveyid][$myfname]))
            {
                if ($_SESSION['survey_'.$this->surveyid][$myfname] == 'Y')
                {
                    $answer_main .= CHECKED;
                }
            }
            $answer_main .=" onclick='cancelBubbleThis(event);$checkconditionFunction(this.value, this.name, this.type);if (!$(this).attr(\"checked\")) { $(\"#answer$myfname2\").val(\"\"); $checkconditionFunction(document.getElementById(\"answer{$myfname2}\").value,\"$myfname2\",\"checkbox\");}' />\n"
            . "\t<label for=\"answer$myfname\" class=\"answertext\">\n"
            . $ansrow['question']."</label>\n";

            //        if ($maxansw > 0) {$maxanswscript .= "\tif (document.getElementById('answer".$myfname."').checked) { count += 1; }\n";}
            //        if ($minansw > 0) {$minanswscript .= "\tif (document.getElementById('answer".$myfname."').checked) { count += 1; }\n";}

            $answer_main .= "<input type='hidden' name='java$myfname' id='java$myfname' value='";
            if (isset($_SESSION['survey_'.$this->surveyid][$myfname]))
            {
                $answer_main .= $_SESSION['survey_'.$this->surveyid][$myfname];
            }
            $answer_main .= "' />\n";
            $fn++;
            $answer_main .= "</span>\n<span class=\"comment\">\n\t<label for='answer$myfname2' class=\"answer-comment hide \">".$clang->gT('Make a comment on your choice here:')."</label>\n"
            ."<input class='text ".$kpclass."' type='text' size='40' id='answer$myfname2' name='$myfname2' title='".$clang->gT('Make a comment on your choice here:')."' value='";
            if (isset($_SESSION['survey_'.$this->surveyid][$myfname2])) {$answer_main .= htmlspecialchars($_SESSION['survey_'.$this->surveyid][$myfname2],ENT_QUOTES);}
            // --> START NEW FEATURE - SAVE
            $answer_main .= "' onkeyup='if (jQuery.trim($(\"#answer{$myfname2}\").val())!=\"\") { document.getElementById(\"answer{$myfname}\").checked=true;$checkconditionFunction(document.getElementById(\"answer{$myfname2}\").value,\"$myfname2\",\"text\");$checkconditionFunction(document.getElementById(\"answer{$myfname}\").value,document.getElementById(\"answer{$myfname}\").name, document.getElementById(\"answer{$myfname}\").type);}' />\n</span>\n"
            . "\t</li>\n";
            // --> END NEW FEATURE - SAVE

            $fn++;
        }
        if ($this->isother == 'Y')
        {
            $myfname = $this->fieldname.'other';
            $myfname2 = $myfname.'comment';
            $anscount = $anscount + 2;
            $answer_main .= "\t<li class=\"other question-item answer-item checkbox-text-item other-item\" id=\"javatbd$myfname\">\n<span class=\"option\">\n"
            . "\t<label for=\"answer$myfname\" class=\"answertext\">\n".$othertext."\n<input class=\"text other ".$kpclass."\" type=\"text\" name=\"$myfname\" id=\"answer$myfname\" title=\"".$clang->gT('Other').'" size="10"';
            $answer_main .= " onkeyup='$oth_checkconditionFunction(this.value, this.name, this.type); if($.trim(this.value)==\"\") { $(\"#answer$myfname2\").val(\"\"); $checkconditionFunction(\"\",\"$myfname2\",\"text\"); }'";
            if (isset($_SESSION['survey_'.$this->surveyid][$myfname]) && $_SESSION['survey_'.$this->surveyid][$myfname])
            {
                $dispVal = $_SESSION['survey_'.$this->surveyid][$myfname];
                if ($aQuestionAttributes['other_numbers_only']==1)
                {
                    $dispVal = str_replace('.',$sSeperator,$dispVal);
                }
                $answer_main .= ' value="'.htmlspecialchars($dispVal,ENT_QUOTES).'"';
            }
            $fn++;
            // --> START NEW FEATURE - SAVE
            $answer_main .= "  $callmaxanswscriptother />\n\t</label>\n</span>\n"
            . "<span class=\"comment\">\n\t<label for=\"answer$myfname2\" class=\"answer-comment hide\">".$clang->gT('Make a comment on your choice here:')."\t</label>\n"
            . '<input class="text '.$kpclass.'" type="text" size="40" name="'.$myfname2.'" id="answer'.$myfname2.'"'
            . " onkeyup='$checkconditionFunction(this.value,this.name,this.type);'"
            . ' title="'.$clang->gT('Make a comment on your choice here:').'" value="';
            // --> END NEW FEATURE - SAVE

            if (isset($_SESSION['survey_'.$this->surveyid][$myfname2])) {$answer_main .= htmlspecialchars($_SESSION['survey_'.$this->surveyid][$myfname2],ENT_QUOTES);}
            // --> START NEW FEATURE - SAVE
            $answer_main .= "\"/>\n";

            $answer_main .= "</span>\n\t</li>\n";
            // --> END NEW FEATURE - SAVE
        }
        $answer .= "<ul class=\"subquestions-list questions-list checkbox-text-list\">\n".$answer_main."</ul>\n";

        return $answer;
    }

    public function getDataEntry($idrow, &$fnames, $language)
    {
        $output .= "<table>\n";
        $q = $this;
        while (get_class($q) == get_class($this))
        {
            if (substr($q->fieldname, -7) == "comment")
            {
                $output .= "<td><input type='text' name='{$q->fieldname}' size='50' value='"
                .htmlspecialchars($idrow[$q->fieldname], ENT_QUOTES) . "' /></td>\n"
                ."\t</tr>\n";
            }
            elseif (substr($q->fieldname, -5) == "other")
            {
                $output .= "\t<tr>\n"
                ."<td>\n"
                ."\t<input type='text' name='{$q->fieldname}' size='30' value='"
                .htmlspecialchars($idrow[$q->fieldname], ENT_QUOTES) . "' />\n"
                ."</td>\n"
                ."<td>\n";
                $q=next($fnames);
                $output .= "\t<input type='text' name='{$q->fieldname}' size='50' value='"
                .htmlspecialchars($idrow[$q->fieldname], ENT_QUOTES) . "' />\n"
                ."</td>\n"
                ."\t</tr>\n";
            }
            else
            {
                $output .= "\t<tr>\n"
                ."<td><input type='checkbox' class='checkboxbtn' name=\"{$q->fieldname}\" value='Y'";
                if ($idrow[$q->fieldname] == "Y") {$output .= " checked";}
                $output .= " />{$q->sq}</td>\n";
            }
            if(!$fname=next($fnames)) break;
            $q=$fname['q'];
        }
        $output .= "</table>\n";
        prev($fnames);
        return $output;
    }

    public function createFieldmap()
    {
        $clang = Yii::app()->lang;
        $map = array();
        $tmp = parent::createFieldmap();
        foreach($tmp as $q)
        {
            $q2 = clone $q;
            $q2->fieldname .= 'comment';
            $q2->sq=$q->aid=='other'?$clang->gT("Other comment"):$clang->gT("Comment");
            $q2->aid.='comment';
            unset($q2->default);
            unset($q2->sqid);
            unset($q2->preg);
            $map[$q->fieldname]=$q;
            $map[$q2->fieldname]=$q2;
        }
        return $map;
    }

    public function getQuotaValue($value)
    {
        return false;
    }

    public function jsVarNameOn()
    {
        if (preg_match("/(other|comment)$/",$this->fieldname))
        {
            return 'answer' . $this->fieldname;
        }
        else
        {
            return 'java'.$this->fieldname;
        }
    }

    public function getSqsuffix()
    {
        if (preg_match("/comment$/", $this->fieldname))
        {
            return '';
        } else {
            return '_' . $this->aid;
        }
    }

    public function getRowDivID()
    {
        if (preg_match("/comment$/", $this->fieldname))
        {
            return null;
        } else {
            return $this->fieldname;
        }
    }

    public function getExportRowDivID()
    {
        if (preg_match('/comment$/',$this->fieldname))
        {
            return NULL;
        }
        else
        {
            return $this->fieldname;
        }
    }

    public function getAnswerCountSQ($sgqaNaming, $sq, $min = true)
    {
        if (!preg_match('/comment$/',$sq['varName'])) {
            if ($sgqaNaming || $min)
            {
                return $sq['rowdivid'] . '.NAOK';
            }
            else
            {
                return $sq['varName'] . '.NAOK';
            }
        }
        else
        {
            return parent::getAnswerCountSQ();
        }
    }

    public function getCommentMandatorySQ()
    {
        return "(is_empty(trim(" . $this->fieldname . "other.NAOK)) || (!is_empty(trim(" . $this->fieldname . "other.NAOK)) && !is_empty(trim(" . $this->fieldname . "othercomment.NAOK))))";
    }

    public function getAdditionalValParts()
    {
        $othervar = 'answer' . $this->fieldname;
        $valParts[] = "\n  if(isValidOtherComment" . $this->id . "){\n";
        $valParts[] = "    $('#" . $othervar . "').addClass('em_sq_validation').removeClass('error').addClass('good');\n";
        $valParts[] = "  }\n  else {\n";
        $valParts[] = "    $('#" . $othervar . "').addClass('em_sq_validation').removeClass('good').addClass('error');\n";
        $valParts[] = "  }\n";
        return $valParts;
    }

    public function getShownJS()
    {
        return 'if (typeof attr.question === "undefined" || value == "") return "";'
                . 'if (varName.match(/comment$/)) return value;'
                . 'return htmlspecialchars_decode(attr.question);';
    }

    public function getVarAttributeLEM($sgqa,$value)
    {
        if (preg_match('/comment$/',$sgqa))
        {
            return htmlspecialchars(parent::getVarAttributeLEM($sgqa,$value),ENT_NOQUOTES);
        }
        else
        {
            return parent::getVarAttributeLEM($sgqa,$value);// CheckQuestion doing for other
        }
    }

    public function getVarAttributeShown($name, $default, $gseq, $qseq, $ansArray)
    {
        $name=preg_replace('/\.shown$/','',$name);
        if (preg_match('/comment$/',$name)) {
            return LimeExpressionManager::GetVarAttribute($name,'code',$default,$gseq,$qseq);
        }
        else
        {
            return parent::getVarAttributeShown($name, $default, $gseq, $qseq, $ansArray);
        }
    }

    public function getDataEntryView($language)
    {
        $meaquery = "SELECT * FROM {{questions}} WHERE parent_qid={$this->id} AND language='{$language->getlangcode()}' ORDER BY question_order, question";
        $mearesult = dbExecuteAssoc($meaquery);

        $output = "<table border='0'>";
        foreach ($mearesult->readAll() as $mearow)
        {
            $output .= "<tr>";
            $output .= "<td>";
            $output .= "<input type='checkbox' class='checkboxbtn' name='{$this->fieldname}{$mearow['title']}' value='Y'";
            $output .= "/>{$mearow['question']}";
            $output .= "</td>";

            $output .= "<td>";
            $output .= "<input type='text' name='{$this->fieldname}{$mearow['title']}comment' size='50' />";
            $output .= "</td>";
            $output .= "</tr>";
        }
        if ($this->isother == "Y")
        {
            $output .= "<tr>";
            $output .= "<td  align='left'><label>{$language->gT("Other")}:</label>";
            $output .= "<input type='text' name='{$this->fieldname}other' size='10'/>";
            $output .= "</td>";
            $output .= "<td align='left'>";
            $output .= "<input type='text' name='{$this->fieldname}othercomment' size='50'/>";
            $output .= "</td>";
            $output .= "</tr>";
        }
        $output .= "</table>";
        return $output;
    }

    public function getTypeHelp($language)
    {
        return $language->gT('Please choose all that apply and provide a comment:');
    }

    public function getPrintAnswers($language)
    {
        $mearesult=Questions::model()->getAllRecords("parent_qid='{$this->id}'  AND language='{$language->getlangcode()}'", array('question_order'));
        $fieldname = $this->surveyid . 'X' . $this->gid . 'X' . $this->id;
        $output = '';
        foreach ($mearesult->readAll() as $mearow)
        {
            $output .= "\t<li><span>\n\t\t".printablesurvey::input_type_image('checkbox',$mearow['question']).$mearow['question'];
            $output .= Yii::app()->getConfig('showsgqacode') ? " (".$fieldname.$mearow['title'].") " : '';
            $output .= "</span>\n\t\t" . printablesurvey::input_type_image('text','comment box',60);
            $output .= (Yii::app()->getConfig('showsgqacode') ? " (".$fieldname.$mearow['title']."comment) " : '')."\n\t</li>\n";
        }
        if ($this->isother == "Y")
        {
            $output .= "\t<li class=\"other\">\n\t\t<div class=\"other-replacetext\">".$language->gT('Other:');
            $output .= printablesurvey::input_type_image('other','',1)."</div>".printablesurvey::input_type_image('othercomment','comment box',50);
            $output .= (Yii::app()->getConfig('showsgqacode') ? " (".$fieldname."other) " : '')."\n\t</li>\n";
        }

        $output = "\n<ul>\n" . $output . "</ul>\n";
        return $output;
    }

    public function getPrintPDF($language)
    {
        $mearesult=Questions::model()->getAllRecords("parent_qid='{$this->id}'  AND language='{$language->getlangcode()}'", array('question_order'));

        $output=array();
        $j=0;
        $longest_string = 0;
        foreach ($mearesult->readAll() as $mearow)
        {
            $output[$j]=array(" o ".$mearow['title']," __________");
            $j++;
        }
        if ($this->isother == "Y")
        {
            $output[$j]=array(" o "."Other"," __________");
            $output[$j+1]=array(" o "."OtherComment"," __________");
        }

        return $output;
    }

    public function availableAttributes($attr = false)
    {
        $attrs=array("array_filter","array_filter_exclude","array_filter_style","assessment_value","em_validation_q","em_validation_q_tip","exclude_all_others","exclude_all_others_auto","statistics_showgraph","hide_tip","hidden","max_answers","min_answers","other_comment_mandatory","other_numbers_only","other_replace_text","page_break","public_statistics","random_order","parent_order","scale_export","random_group");
        return $attr?in_array($attr,$attrs):$attrs;
    }

    public function questionProperties($prop = false)
    {
        $clang=Yii::app()->lang;
        $props=array('description' => $clang->gT("Multiple choice with comments"),'group' => $clang->gT("Multiple choice questions"),'subquestions' => 1,'class' => 'multiple-opt-comments','hasdefaultvalues' => 1,'assessable' => 1,'answerscales' => 0,'enum' => 1);
        return $prop?$props[$prop]:$props;
    }
}
?>
