<?php
class CheckQuestion extends QuestionModule
{
    protected $children;
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

        // Find out if any questions have attributes which reference this questions
        // based on value of attribute. This could be array_filter and array_filter_exclude

        $attribute_ref=false;

        $qaquery = "SELECT qid,attribute FROM {{question_attributes}} WHERE value LIKE '".strtolower($this->title)."' and (attribute='array_filter' or attribute='array_filter_exclude')";
        $qaresult = Yii::app()->db->createCommand($qaquery)->query();     //Checked
        foreach ($qaresult->readAll() as $qarow)
        {
            $qquery = "SELECT qid FROM {{questions}} WHERE sid=".$thissurvey['sid']." AND scale_id=0 AND qid=".$qarow['qid'];
            $qresult = Yii::app()->db->createCommand($qquery)->query();     //Checked
            if ($qresult->getRowCount() > 0)
            {
                $attribute_ref = true;
            }
        }

        $checkconditionFunction = "checkconditions";

        $aQuestionAttributes = $this->getAttributeValues();

        if (trim($aQuestionAttributes['other_replace_text'][$_SESSION['survey_'.$this->surveyid]['s_lang']])!='')
        {
            $othertext=$aQuestionAttributes['other_replace_text'][$_SESSION['survey_'.$this->surveyid]['s_lang']];
        }
        else
        {
            $othertext=$clang->gT('Other:');
        }

        if (trim($aQuestionAttributes['display_columns'])!='')
        {
            $dcols = $aQuestionAttributes['display_columns'];
        }
        else
        {
            $dcols = 1;
        }

        if ($aQuestionAttributes['other_numbers_only']==1)
        {
            $sSeperator = getRadixPointData($thissurvey['surveyls_numberformat']);
            $sSeperator= $sSeperator['seperator'];
            $numbersonly = " onkeypress='return goodchars(event,\"-0123456789$sSeperator\")'";
            $oth_checkconditionFunction = "fixnum_checkconditions";
        }
        else
        {
            $numbersonly = '';
            $oth_checkconditionFunction = "checkconditions";
        }

        $ansresult = $this->getChildren();
        $anscount = count($ansresult);

        if (trim($aQuestionAttributes['exclude_all_others'])!='' && $aQuestionAttributes['random_order']==1)
        {
            //if  exclude_all_others is set then the related answer should keep its position at all times
            //thats why we have to re-position it if it has been randomized
            $position=0;
            foreach ($ansresult as $answer)
            {
                if ((trim($aQuestionAttributes['exclude_all_others']) != '')  &&    ($answer['title']==trim($aQuestionAttributes['exclude_all_others'])))
                {
                    if ($position==$answer['question_order']-1) break; //already in the right position
                    $tmp  = array_splice($ansresult, $position, 1);
                    array_splice($ansresult, $answer['question_order']-1, 0, $tmp);
                    break;
                }
                $position++;
            }
        }

        if ($this->getOther() == 'Y')
        {
            $anscount++; //COUNT OTHER AS AN ANSWER FOR MANDATORY CHECKING!
        }

        $wrapper = setupColumns($dcols, $anscount,"subquestions-list questions-list checkbox-list","question-item answer-item checkbox-item");

        $answer = '<input type="hidden" name="MULTI'.$this->fieldname.'" value="'.$anscount."\" />\n\n".$wrapper['whole-start'];

        $fn = 1;
        if (!isset($multifields))
        {
            $multifields = '';
        }

        $rowcounter = 0;
        $colcounter = 1;
        $startitem='';
        $postrow = '';
        $trbc='';
        foreach ($ansresult as $ansrow)
        {
            $myfname = $this->fieldname.$ansrow['title'];
            $extra_class="";

            $trbc='';
            /* Check for array_filter */
            list($htmltbody2, $hiddenfield)=return_array_filter_strings($this, $aQuestionAttributes, $thissurvey, $ansrow, $myfname, $trbc, $myfname, "li","question-item answer-item checkbox-item".$extra_class);

            if(substr($wrapper['item-start'],0,4) == "\t<li")
            {
                $startitem = "\t$htmltbody2\n";
            } else {
                $startitem = $wrapper['item-start'];
            }

            /* Print out the checkbox */
            $answer .= $startitem;
            $answer .= "\t$hiddenfield\n";
            $answer .= '		<input class="checkbox" type="checkbox" name="'.$this->fieldname.$ansrow['title'].'" id="answer'.$this->fieldname.$ansrow['title'].'" value="Y"';

            /* If the question has already been ticked, check the checkbox */
            if (isset($_SESSION['survey_'.$this->surveyid][$myfname]))
            {
                if ($_SESSION['survey_'.$this->surveyid][$myfname] == 'Y')
                {
                    $answer .= CHECKED;
                }
            }
            $answer .= " onclick='cancelBubbleThis(event);";

            $answer .= ''
            .  "$checkconditionFunction(this.value, this.name, this.type)' />\n"
            .  "<label for=\"answer$this->fieldname{$ansrow['title']}\" class=\"answertext\">"
            .  $ansrow['question']
            .  "</label>\n";

            ++$fn;
            /* Now add the hidden field to contain information about this answer */
            $answer .= '		<input type="hidden" name="java'.$myfname.'" id="java'.$myfname.'" value="';
            if (isset($_SESSION['survey_'.$this->surveyid][$myfname]))
            {
                $answer .= $_SESSION['survey_'.$this->surveyid][$myfname];
            }
            $answer .= "\" />\n{$wrapper['item-end']}";

            ++$rowcounter;
            if ($rowcounter == $wrapper['maxrows'] && $colcounter < $wrapper['cols'])
            {
                if($colcounter == $wrapper['cols'] - 1)
                {
                    $answer .= $wrapper['col-devide-last'];
                }
                else
                {
                    $answer .= $wrapper['col-devide'];
                }
                $rowcounter = 0;
                ++$colcounter;
            }
        }

        if ($this->getOther() == 'Y')
        {
            $myfname = $this->fieldname.'other';
            list($htmltbody2, $hiddenfield)=return_array_filter_strings($this, $aQuestionAttributes, $thissurvey, array("code"=>"other"), $myfname, $trbc, $myfname, "li","question-item answer-item checkbox-item other-item");

            if(substr($wrapper['item-start-other'],0,4) == "\t<li")
            {
                $startitem = "\t$htmltbody2\n";
            } else {
                $startitem = $wrapper['item-start-other'];
            }
            $answer .= $startitem;
            $answer .= $hiddenfield.'
            <input class="checkbox" type="checkbox" name="'.$myfname.'cbox" alt="'.$clang->gT('Other').'" id="answer'.$myfname.'cbox"';

            if (isset($_SESSION['survey_'.$this->surveyid][$myfname]) && trim($_SESSION['survey_'.$this->surveyid][$myfname])!='')
            {
                $answer .= CHECKED;
            }
            $answer .= " onclick='cancelBubbleThis(event);if(this.checked===false){ document.getElementById(\"answer$myfname\").value=\"\"; document.getElementById(\"java$myfname\").value=\"\"; $checkconditionFunction(\"\", \"$myfname\", \"text\"); }";
            $answer .= " if(this.checked===true) { document.getElementById(\"answer$myfname\").focus(); }; LEMflagMandOther(\"$myfname\",this.checked);";
            $answer .= "' />
            <label for=\"answer$myfname\" class=\"answertext\">".$othertext."</label>
            <input class=\"text ".$kpclass."\" type=\"text\" name=\"$myfname\" id=\"answer$myfname\" value=\"";
            if (isset($_SESSION['survey_'.$this->surveyid][$myfname]))
            {
                $dispVal = $_SESSION['survey_'.$this->surveyid][$myfname];
                if ($aQuestionAttributes['other_numbers_only']==1)
                {
                    $dispVal = str_replace('.',$sSeperator,$dispVal);
                }
                $answer .= htmlspecialchars($dispVal,ENT_QUOTES);
            }
            $answer .= "\" onchange='$(\"#java{$myfname}\").val(this.value);$oth_checkconditionFunction(this.value, this.name, this.type);if ($.trim($(\"#java{$myfname}\").val())!=\"\") { \$(\"#answer{$myfname}cbox\").attr(\"checked\",\"checked\"); } else { \$(\"#answer{$myfname}cbox\").attr(\"checked\",\"\"); }; LEMflagMandOther(\"$myfname\",this.checked);' $numbersonly />";
            $answer .= '<input type="hidden" name="java'.$myfname.'" id="java'.$myfname.'" value="';

            if (isset($_SESSION['survey_'.$this->surveyid][$myfname]))
            {
                $dispVal = $_SESSION['survey_'.$this->surveyid][$myfname];
                if ($aQuestionAttributes['other_numbers_only']==1)
                {
                    $dispVal = str_replace('.',$sSeperator,$dispVal);
                }
                $answer .= htmlspecialchars($dispVal,ENT_QUOTES);
            }

            $answer .= "\" />\n{$wrapper['item-end']}";
            ++$anscount;

            ++$rowcounter;
            if ($rowcounter == $wrapper['maxrows'] && $colcounter < $wrapper['cols'])
            {
                if($colcounter == $wrapper['cols'] - 1)
                {
                    $answer .= $wrapper['col-devide-last'];
                }
                else
                {
                    $answer .= $wrapper['col-devide'];
                }
                $rowcounter = 0;
                ++$colcounter;
            }
        }
        $answer .= $wrapper['whole-end'];

        $checkotherscript = "";
        if ($this->getOther() == 'Y')
        {
            // Multiple choice with 'other' is a specific case as the checkbox isn't recorded into DB
            // this means that if it is cehcked We must force the end-user to enter text in the input
            // box
            $checkotherscript = "<script type='text/javascript'>\n"
            . "\t<!--\n"
            . "oldonsubmitOther_{$this->id} = document.limesurvey.onsubmit;\n"
            . "function ensureOther_{$this->id}()\n"
            . "{\n"
            . "\tothercboxval=document.getElementById('answer".$myfname."cbox').checked;\n"
            . "\totherval=document.getElementById('answer".$myfname."').value;\n"
            . "\tif (otherval != '' || othercboxval != true) {\n"
            . "if(typeof oldonsubmitOther_{$this->id} == 'function') {\n"
            . "\treturn oldonsubmitOther_{$this->id}();\n"
            . "}\n"
            . "\t}\n"
            . "\telse {\n"
            . "alert('".sprintf($clang->gT("You've marked the \"other\" field for question \"%s\". Please also fill in the accompanying \"other comment\" field.","js"),trim(javascriptEscape($this->text,true,true)))."');\n"
            . "return false;\n"
            . "\t}\n"
            . "}\n"
            . "document.limesurvey.onsubmit = ensureOther_{$this->id};\n"
            . "\t-->\n"
            . "</script>\n";
        }

        $answer = $checkotherscript . $answer;

        $answer .= $postrow;
        return $answer;
    }
    
    protected function getChildren()
    {
        if ($this->children) return $this->children;
        $aQuestionAttributes = $this->getAttributeValues();
        if ($aQuestionAttributes['random_order']==1) {
            $ansquery = "SELECT * FROM {{questions}} WHERE parent_qid=$this->id AND scale_id=0 AND language='".$_SESSION['survey_'.$this->surveyid]['s_lang']."' ORDER BY ".dbRandom();
        }
        else
        {
            $ansquery = "SELECT * FROM {{questions}} WHERE parent_qid=$this->id AND scale_id=0 AND language='".$_SESSION['survey_'.$this->surveyid]['s_lang']."' ORDER BY question_order";
        }
        $ansresult = dbExecuteAssoc($ansquery)->readAll();  //Checked
        
        if (trim($aQuestionAttributes['exclude_all_others'])!='' && $aQuestionAttributes['random_order']==1)
        {
            //if  exclude_all_others is set then the related answer should keep its position at all times
            //thats why we have to re-position it if it has been randomized
            $position=0;
            foreach ($ansresult as $answer)
            {
                if ((trim($aQuestionAttributes['exclude_all_others']) != '')  &&    ($answer['title']==trim($aQuestionAttributes['exclude_all_others'])))
                {
                    if ($position==$answer['question_order']-1) break; //already in the right position
                    $tmp  = array_splice($ansresult, $position, 1);
                    array_splice($ansresult, $answer['question_order']-1, 0, $tmp);
                    break;
                }
                $position++;
            }
        }
        return $this->children  = $ansresult;
    }
    
    protected function getOther()
    {
        if ($this->other) return $this->other;
        $query = "SELECT other FROM {{questions}} WHERE qid=".$this->id." AND language='".$_SESSION['survey_'.$this->surveyid]['s_lang']."' and parent_qid=0";
        $result = Yii::app()->db->createCommand($query)->query()->readAll();
        return $this->other = $result[0]['other'];  //Checked
    }

    public function getTitle()
    {
        $clang=Yii::app()->lang;
        $aQuestionAttributes = $this->getAttributeValues();
        if (count($this->getChildren()) > 0 && $aQuestionAttributes['hide_tip']==0)
        {
            $maxansw=trim($aQuestionAttributes['max_answers']);
            $minansw=trim($aQuestionAttributes['min_answers']);
            if (!($maxansw || $minansw))
            {
                return $this->text."<br />\n<span class=\"questionhelp\">".$clang->gT('Check any that apply').'</span>';
            }
        }
        return $this->text;
    }
    
    public function getHelp()
    {
        $clang=Yii::app()->lang;
        $aQuestionAttributes = $this->getAttributeValues();
        if (count($this->getChildren()) > 0 && $aQuestionAttributes['hide_tip']==0)
        {
            $maxansw=trim($aQuestionAttributes['max_answers']);
            $minansw=trim($aQuestionAttributes['min_answers']);
            if (!($maxansw || $minansw))
            {
                return $clang->gT('Check any that apply');
            }
        }
        return '';
    }
    
    public function createFieldmap($type=null)
    {
        $clang = Yii::app()->lang;
        $map = array();
        $abrows = getSubQuestions($this);
        foreach ($abrows as $abrow)
        {
            $fieldname="{$this->surveyid}X{$this->gid}X{$this->id}{$abrow['title']}";
            $field['fieldname']=$fieldname;
            $field['type']=$type;
            $field['sid']=$this->surveyid;
            $field['gid']=$this->gid;
            $field['qid']=$this->id;
            $field['aid']=$abrow['title'];
            $field['sqid']=$abrow['qid'];
            $field['title']=$this->title;
            $field['question']=$this->text;
            $field['subquestion']=$abrow['question'];
            $field['group_name']=$this->groupname;
            $field['mandatory']=$this->mandatory;
            $field['hasconditions']=$this->conditionsexist;
            $field['usedinconditions']=$this->usedinconditions;
            $field['questionSeq']=$this->questioncount;
            $field['groupSeq']=$this->groupcount;
            $field['preg']=$this->preg;
            if(isset($this->default[$abrow['qid']])) $field['defaultvalue']=$this->default[$abrow['qid']];
            $field['pq']=$this;
            $q = clone $this;
            $q->fieldname = $fieldname;
            $q->aid=$field['aid'];
            $q->question=$abrow['question'];
            $field['q']=$q;
            $map[$fieldname]=$field;
        }
        if ($this->other=='Y')
        {
            $other = parent::createFieldmap($type);
            $other = $other[$this->fieldname];
            $other['fieldname'].='other';
            $other['aid']='other';
            $other['subquestion']=$clang->gT("Other");
            $other['other']=$this->other;
            if (isset($this->default['other'])) $other['defaultvalue']=$this->default['other'];
            else unset($other['defaultvalues']);
            $q = clone $this;
            $q->fieldname .= 'other';
            $q->aid = 'other';
            $q->default = isset($other['defaultvalues'])?$other['defaultvalues']:null;
            $other['q']=$q;
            $other['pq']=$this;
            $map[$other['fieldname']]=$other;
        }
        
        return $map;
    }
        
    public function getExtendedAnswer($value, $language)
    {
        if($value=="Y") return $language->gT("Yes")." [$value]";
        return $value;
    }
    
    public function getQuotaValue($value)
    {
        return array($this->surveyid.'X'.$this->gid.'X'.$this->id.$value => 'Y');
    }
    
    public function availableAttributes($attr = false)
    {
        $attrs=array("array_filter","array_filter_exclude","array_filter_style","assessment_value","display_columns","exclude_all_others","exclude_all_others_auto","statistics_showgraph","hide_tip","hidden","max_answers","min_answers","other_numbers_only","other_replace_text","page_break","public_statistics","random_order","parent_order","scale_export","random_group");
        return $attr?array_key_exists($attr,$attrs):$attrs;
    }

    public function questionProperties($prop = false)
    {
        $clang=Yii::app()->lang;
        $props=array('description' => $clang->gT("Multiple choice"),'group' => $clang->gT("Multiple choice questions"),'subquestions' => 1,'class' => 'multiple-opt','hasdefaultvalues' => 1,'assessable' => 1,'answerscales' => 0);
        return $prop?$props[$prop]:$props;
    }
}
?>