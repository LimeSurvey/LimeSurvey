<?php
class MultitextQuestion extends QuestionModule
{
    protected $children;
    public function getAnswerHTML()
    {
        global $thissurvey;

        $clang = Yii::app()->lang;
        $extraclass ="";
        $answer='';
        $aQuestionAttributes = $this->getAttributeValues();

        if ($aQuestionAttributes['numbers_only']==1)
        {
            $sSeperator = getRadixPointData($thissurvey['surveyls_numberformat']);
            $sSeperator = $sSeperator['seperator'];
            $extraclass .=" numberonly";
            $checkconditionFunction = "fixnum_checkconditions";
        }
        else
        {
            $checkconditionFunction = "checkconditions";
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
        if (trim($aQuestionAttributes['text_input_width'])!='')
        {
            $tiwidth=$aQuestionAttributes['text_input_width'];
            $extraclass .=" inputwidth".trim($aQuestionAttributes['text_input_width']);
        }
        else
        {
            $tiwidth=20;
        }

        if (trim($aQuestionAttributes['prefix'][$_SESSION['survey_'.$this->surveyid]['s_lang']])!='') {
            $prefix=$aQuestionAttributes['prefix'][$_SESSION['survey_'.$this->surveyid]['s_lang']];
            $extraclass .=" withprefix";
        }
        else
        {
            $prefix = '';
        }

        if (trim($aQuestionAttributes['suffix'][$_SESSION['survey_'.$this->surveyid]['s_lang']])!='') {
            $suffix=$aQuestionAttributes['suffix'][$_SESSION['survey_'.$this->surveyid]['s_lang']];
            $extraclass .=" withsuffix";
        }
        else
        {
            $suffix = '';
        }

        if ($thissurvey['nokeyboard']=='Y')
        {
            includeKeypad();
            $kpclass = "text-keypad";
            $extraclass .=" inputkeypad";
        }
        else
        {
            $kpclass = "";
        }

        $ansresult = $this->getChildren();
        $anscount = count($ansresult)*2;
        //$answer .= "\t<input type='hidden' name='MULTI$this->fieldname' value='$anscount'>\n";
        $fn = 1;

        $answer_main = '';

        $label_width = 0;

        if ($anscount==0)
        {
            $answer_main .= '	<li>'.$clang->gT('Error: This question has no answers.')."</li>\n";
        }
        else
        {
            if (trim($aQuestionAttributes['display_rows'])!='')
            {
                //question attribute "display_rows" is set -> we need a textarea to be able to show several rows
                $drows=$aQuestionAttributes['display_rows'];

                foreach ($ansresult as $ansrow)
                {
                    $myfname = $this->fieldname.$ansrow['title'];
                    if ($ansrow['question'] == "")
                    {
                        $ansrow['question'] = "&nbsp;";
                    }

                    //NEW: textarea instead of input=text field
                    list($htmltbody2, $hiddenfield)=return_array_filter_strings($this, $aQuestionAttributes, $thissurvey, $ansrow, $myfname, '', $myfname, "li","question-item answer-item text-item".$extraclass);

                    $answer_main .= "\t$htmltbody2\n"
                    . "<label for=\"answer$myfname\">{$ansrow['question']}</label>\n"
                    . "\t<span>\n".$prefix."\n".'
                    <textarea class="textarea '.$kpclass.'" name="'.$myfname.'" id="answer'.$myfname.'"
                    rows="'.$drows.'" cols="'.$tiwidth.'" '.$maxlength.' onkeyup="'.$checkconditionFunction.'(this.value, this.name, this.type);">';

                    if($label_width < strlen(trim(strip_tags($ansrow['question']))))
                    {
                        $label_width = strlen(trim(strip_tags($ansrow['question'])));
                    }

                    if (isset($_SESSION['survey_'.$this->surveyid][$myfname]))
                    {
                        $dispVal = $_SESSION['survey_'.$this->surveyid][$myfname];
                        if ($aQuestionAttributes['numbers_only']==1)
                        {
                            $dispVal = str_replace('.',$sSeperator,$dispVal);
                        }
                        $answer_main .= $dispVal;
                    }

                    $answer_main .= "</textarea>\n".$suffix."\n\t</span>\n"
                    . "\t</li>\n";

                    $fn++;
                }

            }
            else
            {
                foreach ($ansresult as $ansrow)
                {
                    $myfname = $this->fieldname.$ansrow['title'];
                    if ($ansrow['question'] == "") {$ansrow['question'] = "&nbsp;";}
                    
                    // color code missing mandatory questions red
                    if ($this->mandatory=='Y' && (($_SESSION['survey_'.$this->surveyid]['step'] == $_SESSION['survey_'.$this->surveyid]['prevstep'])
                            || ($_SESSION['survey_'.$this->surveyid]['maxstep'] > $_SESSION['survey_'.$this->surveyid]['step']))
                            && $_SESSION['survey_'.$this->surveyid][$myfname] == '') {
                        $ansrow['question'] = "<span class='errormandatory'>{$ansrow['question']}</span>";
                    }

                    list($htmltbody2, $hiddenfield)=return_array_filter_strings($this, $aQuestionAttributes, $thissurvey, $ansrow, $myfname, '', $myfname, "li","question-item answer-item text-item".$extraclass);
                    $answer_main .= "\t$htmltbody2\n"
                    . "<label for=\"answer$myfname\">{$ansrow['question']}</label>\n"
                    . "\t<span>\n".$prefix."\n".'<input class="text '.$kpclass.'" type="text" size="'.$tiwidth.'" name="'.$myfname.'" id="answer'.$myfname.'" value="';

                    if($label_width < strlen(trim(strip_tags($ansrow['question']))))
                    {
                        $label_width = strlen(trim(strip_tags($ansrow['question'])));
                    }

                    if (isset($_SESSION['survey_'.$this->surveyid][$myfname]))
                    {
                        $dispVal = $_SESSION['survey_'.$this->surveyid][$myfname];
                        if ($aQuestionAttributes['numbers_only']==1)
                        {
                            $dispVal = str_replace('.',$sSeperator,$dispVal);
                        }
                        $answer_main .= $dispVal;
                    }

                    // --> START NEW FEATURE - SAVE
                    $answer_main .= '" onkeyup="'.$checkconditionFunction.'(this.value, this.name, this.type);" '.$maxlength.' />'."\n".$suffix."\n\t</span>\n"
                    . "\t</li>\n";
                    // --> END NEW FEATURE - SAVE

                    $fn++;
                }

            }
        }

        $answer = "<ul class=\"subquestions-list questions-list text-list\">\n".$answer_main."</ul>\n";

        return $answer;
    }

    public function getDataEntry($idrow, &$fnames, $language)
    {
        $output = $this->sq.'&nbsp;';
        $output .= CHtml::textField($this->fieldname, $idrow[$this->fieldname]);
        return $output;
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
        return $this->children = dbExecuteAssoc($ansquery)->readAll();  //Checked
    }
    
    public function createFieldmap($type=null)
    {
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
            $q = clone $this;
            if(isset($this->defaults) && isset($this->defaults[$abrow['qid']])) $q->default=$field['defaultvalue']=$this->defaults[$abrow['qid']];
            $q->fieldname = $fieldname;
            $q->aid=$field['aid'];
            $q->question=$abrow['question'];
            $q->sq=$abrow['question'];
            $q->sqid=$abrow['qid'];
            $field['q']=$q;
            $map[$fieldname]=$field;
        }
        return $map;
    }
    
    public function getDBField()
    {
        return 'text';
    }
    
    public function availableAttributes($attr = false)
    {
        $attrs=array("array_filter","array_filter_exclude","array_filter_style","display_rows","em_validation_q","em_validation_q_tip","em_validation_sq","em_validation_sq_tip","exclude_all_others","statistics_showgraph","statistics_graphtype","hide_tip","hidden","max_answers","maximum_chars","min_answers","numbers_only","page_break","prefix","random_order","parent_order","suffix","text_input_width","random_group");
        return $attr?array_key_exists($attr,$attrs):$attrs;
    }

    public function questionProperties($prop = false)
    {
        $clang=Yii::app()->lang;
        $props=array('description' => $clang->gT("Multiple Short Text"),'group' => $clang->gT("Text questions"),'subquestions' => 1,'class' => 'multiple-short-txt','hasdefaultvalues' => 1,'assessable' => 0,'answerscales' => 0);
        return $prop?$props[$prop]:$props;
    }
}
?>