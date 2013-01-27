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
            $answer_main .= '    <li>'.$clang->gT('Error: This question has no answers.')."</li>\n";
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

    public function createFieldmap()
    {
        $map = array();
        $abrows = getSubQuestions($this);
        foreach ($abrows as $abrow)
        {
            $fieldname="{$this->surveyid}X{$this->gid}X{$this->id}{$abrow['title']}";
            $q = clone $this;
            if(isset($this->defaults) && isset($this->defaults[$abrow['qid']])) $q->default=$this->defaults[$abrow['qid']];
            $q->fieldname = $fieldname;
            $q->aid=$abrow['title'];
            $q->question=$abrow['question'];
            $q->sq=$abrow['question'];
            $q->sqid=$abrow['qid'];
            $q->preg=$this->haspreg;
            $map[$fieldname]=$q;
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

    public function onlyNumeric()
    {
        $attributes = $this->getAttributeValues();
        return array_key_exists('numbers_only', $attributes) && $attributes['numbers_only'] == 1;
    }

    public function getCsuffix()
    {
        return $this->aid;
    }

    public function getSqsuffix()
    {
        return '_' . $this->aid;
    }

    public function getVarName()
    {
        return $this->title . '_' . $this->aid;
    }

    public function getQuestion()
    {
        return $this->sq;
    }

    public function getRowDivID()
    {
        return $this->fieldname;
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
        return $sgqa == $sq['rowdivid'] || $sgqa == ($sq['rowdivid'] . 'comment');
    }

    public function includeRelevanceStatus()
    {
        return true;
    }

    public function availableOptions()
    {
        return array('other' => false, 'valid' => true, 'mandatory' => true);
    }

    public function getDataEntryView($language)
    {
        $deaquery = "SELECT question,title FROM {{questions}} WHERE parent_qid={$this->id} AND language='{$language->getlangcode()}' ORDER BY question_order";
        $dearesult = dbExecuteAssoc($deaquery);
        $dearesult = $dearesult->readAll();

        $output = "<table>";
        foreach ($dearesult as $dearow)
        {
        $output .= "<tr><td align='right'>";
        $output .= "{$dearow['question']}";
        $output .= "</td>";
        $output .= "<td><input type='text' name='{$this->fieldname}{$dearow['title']}' /></td>";
        $output .= "</tr>";
        }
        $output .= "</table>";
        return $output;
    }

    public function getTypeHelp($language)
    {
        return $language->gT("Please write your answer(s) here:");
    }

    public function getPrintAnswers($language)
    {
        $qidattributes = $this->getAttributeValues();
        $mearesult=Questions::model()->getAllRecords("parent_qid='{$this->id}' AND language='{$language->getlangcode()}'", array('question_order'));
        $output = '';

        foreach ($mearesult->readAll() as $mearow)
        {
            if (isset($qidattributes['slider_layout']) && $qidattributes['slider_layout']==1)
            {
              $mearow['question']=explode(':',$mearow['question']);
              $mearow['question']=$mearow['question'][0];
            }
            $output .=  "\t<li>\n\t\t<span>".$mearow['question']."</span>\n\t\t".printablesurvey::input_type_image('text',$mearow['question'],60);
            $output .= (Yii::app()->getConfig('showsgqacode') ? " (".$fieldname.$mearow['title'].") " : '')."\n\t</li>\n";
        }
        $output =  "\n<ul>\n".$output."</ul>\n";
        return $output;
    }

    public function getPrintPDF($language)
    {
        $mearesult=Questions::model()->getAllRecords("parent_qid='{$this->id}' AND language='{$language->getlangcode()}'", array('question_order'));

        $output = array();
        foreach ($mearesult->readAll() as $mearow)
        {
            $output = $mearow['question'].": ____________________";
        }
        return $output;
    }

    public function getConditionAnswers()
    {
        $clang = Yii::app()->lang;
        $canswers = array();

        $aresult = Questions::model()->findAllByAttributes(array('parent_qid' => $this->id, 'language' => Survey::model()->findByPk($this->surveyid)->language), array('order' => 'question_order desc'));

        foreach ($aresult as $arows)
        {
            // Only Show No-Answer if question is not mandatory
            if ($this->mandatory != 'Y')
            {
                $canswers[]=array($this->surveyid.'X'.$this->gid.'X'.$this->id.$arows['title'], "", $clang->gT("No answer"));
            }
        } //while

        return $canswers;
    }

    public function getConditionQuestions()
    {
        $clang = Yii::app()->lang;
        $cquestions = array();

        $aresult = Questions::model()->findAllByAttributes(array('parent_qid' => $this->id, 'language' => Survey::model()->findByPk($this->surveyid)->language), array('order' => 'question_order desc'));

        foreach ($aresult as $arows)
        {
            $shortanswer = "{$arows['title']}: [" . strip_tags($arows['question']) . "]";
            $shortquestion=$this->title.":$shortanswer ".strip_tags($this->text);
            $cquestions[]=array($shortquestion, $this->id, false, $this->surveyid.'X'.$this->gid.'X'.$this->id.$arows['title']);
        } //while

        return $cquestions;
    }

    public function QueXMLAppendAnswers(&$question)
    {
        global $dom;
        $response = $dom->createElement("response");
        $response->setAttribute("varName", $this->surveyid . 'X' . $this->gid . 'X' . $this->id);
        quexml_create_subQuestions($question,$this->id,$this->surveyid.'X'.$this->gid.'X'.$this->id);
        $response->appendChild(QueXMLCreateFree("text",quexml_get_lengthth($this->id,"maximum_chars","10"),""));
        $question->appendChild($response);
    }

    public function availableAttributes($attr = false)
    {
        $attrs=array("array_filter","array_filter_exclude","array_filter_style","display_rows","em_validation_q","em_validation_q_tip","em_validation_sq","em_validation_sq_tip","exclude_all_others","statistics_showgraph","statistics_graphtype","hide_tip","hidden","max_answers","maximum_chars","min_answers","numbers_only","page_break","prefix","random_order","parent_order","suffix","text_input_width","random_group");
        return $attr?in_array($attr,$attrs):$attrs;
    }

    public function questionProperties($prop = false)
    {
        $clang=Yii::app()->lang;
        $props=array('description' => $clang->gT("Multiple Short Text"),'group' => $clang->gT("Text questions"),'subquestions' => 1,'class' => 'multiple-short-txt','hasdefaultvalues' => 1,'assessable' => 0,'answerscales' => 0,'enum' => 0);
        return $prop?$props[$prop]:$props;
    }

    public function getVarAttributeLEM($sgqa,$value)
    {
        return htmlspecialchars(parent::getVarAttributeLEM($sgqa,$value),ENT_NOQUOTES);
    }

}
?>
