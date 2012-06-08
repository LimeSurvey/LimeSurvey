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
            $numbersonly = 'onkeypress="return goodchars(event,\'-0123456789'.$sSeperator.'\')"';
            $oth_checkconditionFunction = "fixnum_checkconditions";
        }
        else
        {
            $numbersonly = '';
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
        
        if($this->getOther() == 'Y')
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
            $answer_main .=" onclick='cancelBubbleThis(event);$checkconditionFunction(this.value, this.name, this.type);' "
            . " onchange='document.getElementById(\"answer$myfname2\").value=\"\";' />\n"
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
            $answer_main .= "'  onclick='cancelBubbleThis(event);' onchange='if (jQuery.trim($(\"#answer{$myfname2}\").val())!=\"\") { document.getElementById(\"answer{$myfname}\").checked=true;$checkconditionFunction(document.getElementById(\"answer{$myfname}\").value,\"$myfname\",\"checkbox\");}' />\n</span>\n"
            . "\t</li>\n";
            // --> END NEW FEATURE - SAVE

            $fn++;
        }
        if ($this->getOther() == 'Y')
        {
            $myfname = $this->fieldname.'other';
            $myfname2 = $myfname.'comment';
            $anscount = $anscount + 2;
            $answer_main .= "\t<li class=\"other question-item answer-item checkbox-text-item other-item\" id=\"javatbd$myfname\">\n<span class=\"option\">\n"
            . "\t<label for=\"answer$myfname\" class=\"answertext\">\n".$othertext."\n<input class=\"text other ".$kpclass."\" $numbersonly type=\"text\" name=\"$myfname\" id=\"answer$myfname\" title=\"".$clang->gT('Other').'" size="10"';
            $answer_main .= " onchange='$oth_checkconditionFunction(this.value, this.name, this.type)'";
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
            . '
            <input class="text '.$kpclass.'" type="text" size="40" name="'.$myfname2.'" id="answer'.$myfname2.'" title="'.$clang->gT('Make a comment on your choice here:').'" value="';
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
    
    //public function getTitle() - inherited
    
    //public function getHelp() - inherited
    
    public function createFieldmap($type=null)
    {
        $clang = Yii::app()->lang;
        $map = array();
        $tmp = parent::createFieldmap($type);
        foreach($tmp as $field)
        {
            $comment = $field;
            $comment['fieldname'].='comment';
            $comment['subquestion']=$comment['aid']=='other'?$clang->gT("Other comment"):$clang->gT("Comment");
            if ($comment['aid']!='other') unset($comment['other']);
            $comment['aid'].='comment';
            unset($comment['defaultvalues']);
            unset($comment['sqid']);
            unset($comment['preg']);
            $q = clone $this;
            $q->fieldname .= 'comment';
            $q->aid = $comment['aid'];
            unset($q->default);
            $comment['q']=$q;
            $comment['pq']=$this;
            $map[$field['fieldname']]=$field;
            $map[$comment['fieldname']]=$comment;
        }
        return $map;
    }
    
    public function availableAttributes($attr = false)
    {
        $attrs=array("array_filter","array_filter_exclude","array_filter_style","assessment_value","exclude_all_others","statistics_showgraph","hide_tip","hidden","max_answers","min_answers","other_comment_mandatory","other_numbers_only","other_replace_text","page_break","public_statistics","random_order","parent_order","scale_export","random_group");
        return $attr?array_key_exists($attr,$attrs):$attrs;
    }

    public function questionProperties($prop = false)
    {
        $clang=Yii::app()->lang;
        $props=array('description' => $clang->gT("Multiple choice with comments"),'group' => $clang->gT("Multiple choice questions"),'subquestions' => 1,'class' => 'multiple-opt-comments','hasdefaultvalues' => 1,'assessable' => 1,'answerscales' => 0);
        return $prop?$props[$prop]:$props;
    }
}
?>