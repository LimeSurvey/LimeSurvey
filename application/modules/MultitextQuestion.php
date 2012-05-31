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
            $numbersonly = 'onkeypress="return goodchars(event,\'-0123456789'.$sSeperator.'\')"';
            $extraclass .=" numberonly";
            $checkconditionFunction = "fixnum_checkconditions";
        }
        else
        {
            $numbersonly = '';
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

        if (trim($aQuestionAttributes['prefix'][$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']])!='') {
            $prefix=$aQuestionAttributes['prefix'][$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']];
            $extraclass .=" withprefix";
        }
        else
        {
            $prefix = '';
        }

        if (trim($aQuestionAttributes['suffix'][$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']])!='') {
            $suffix=$aQuestionAttributes['suffix'][$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']];
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
                    rows="'.$drows.'" cols="'.$tiwidth.'" '.$maxlength.' onchange="'.$checkconditionFunction.'(this.value, this.name, this.type);" '.$numbersonly.'>';

                    if($label_width < strlen(trim(strip_tags($ansrow['question']))))
                    {
                        $label_width = strlen(trim(strip_tags($ansrow['question'])));
                    }

                    if (isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname]))
                    {
                        $dispVal = $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname];
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

                    list($htmltbody2, $hiddenfield)=return_array_filter_strings($this, $aQuestionAttributes, $thissurvey, $ansrow, $myfname, '', $myfname, "li","question-item answer-item text-item".$extraclass);
                    $answer_main .= "\t$htmltbody2\n"
                    . "<label for=\"answer$myfname\">{$ansrow['question']}</label>\n"
                    . "\t<span>\n".$prefix."\n".'<input class="text '.$kpclass.'" type="text" size="'.$tiwidth.'" name="'.$myfname.'" id="answer'.$myfname.'" value="';

                    if($label_width < strlen(trim(strip_tags($ansrow['question']))))
                    {
                        $label_width = strlen(trim(strip_tags($ansrow['question'])));
                    }

                    if (isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname]))
                    {
                        $dispVal = $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname];
                        if ($aQuestionAttributes['numbers_only']==1)
                        {
                            $dispVal = str_replace('.',$sSeperator,$dispVal);
                        }
                        $answer_main .= $dispVal;
                    }

                    // --> START NEW FEATURE - SAVE
                    $answer_main .= '" onchange="'.$checkconditionFunction.'(this.value, this.name, this.type);" '.$numbersonly.' '.$maxlength.' />'."\n".$suffix."\n\t</span>\n"
                    . "\t</li>\n";
                    // --> END NEW FEATURE - SAVE

                    $fn++;
                }

            }
        }

        $answer = "<ul class=\"subquestions-list questions-list text-list\">\n".$answer_main."</ul>\n";

        return $answer;
    }
    
    public function getInputNames()
    {
        foreach ($this->getChildren() as $ansrow)
        {
            $inputnames[] = $this->fieldname.$ansrow['title'];
        }
        
        return $inputnames;
    }
    
    protected function getChildren()
    {
        if ($this->children) return $this->children;
        $aQuestionAttributes = $this->getAttributeValues();
        if ($aQuestionAttributes['random_order']==1) {
            $ansquery = "SELECT * FROM {{questions}} WHERE parent_qid=$this->id AND scale_id=0 AND language='".$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']."' ORDER BY ".dbRandom();
        }
        else
        {
            $ansquery = "SELECT * FROM {{questions}} WHERE parent_qid=$this->id AND scale_id=0 AND language='".$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']."' ORDER BY question_order";
        }
        return $this->children = dbExecuteAssoc($ansquery)->readAll();  //Checked
    }

    public function availableAttributes()
    {
        return array("array_filter","array_filter_exclude","display_rows","em_validation_q","em_validation_q_tip","em_validation_sq","em_validation_sq_tip","statistics_showgraph","statistics_graphtype","hide_tip","hidden","max_answers","maximum_chars","min_answers","numbers_only","page_break","prefix","random_order","parent_order","suffix","text_input_width","random_group");
    }
}
?>