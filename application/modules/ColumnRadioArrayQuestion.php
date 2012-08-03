<?php
class ColumnRadioArrayQuestion extends RadioArrayQuestion
{
    public function getAnswerHTML()
    {
        global $notanswered;
        $clang = Yii::app()->lang;
        $extraclass = "";
        $checkconditionFunction = "checkconditions";

        $aQuestionAttributes = $this->getAttributeValues();
        $qquery = "SELECT other FROM {{questions}} WHERE qid=".$this->id." AND language='".$_SESSION['survey_'.$this->surveyid]['s_lang']."'";
        $qresult = dbExecuteAssoc($qquery);    //Checked
        $qrow = $qresult->read(); $other = $qrow['other'];
        $lquery = "SELECT * FROM {{answers}} WHERE qid=".$this->id."  AND language='".$_SESSION['survey_'.$this->surveyid]['s_lang']."' and scale_id=0 ORDER BY sortorder, code";
        $lresult = dbExecuteAssoc($lquery);   //Checked
        if ($lresult->count() > 0)
        {
            foreach ($lresult->readAll() as $lrow)
            {
                $labelans[]=$lrow['answer'];
                $labelcode[]=$lrow['code'];
                $labels[]=array("answer"=>$lrow['answer'], "code"=>$lrow['code']);
            }
            if ($this->mandatory != 'Y' && SHOW_NO_ANSWER == 1)
            {
                $labelcode[]='';
                $labelans[]=$clang->gT('No answer');
                $labels[]=array('answer'=>$clang->gT('No answer'), 'code'=>'');
            }
            $ansresult = $this->getChildren();
            $anscount = count($ansresult);
            if ($anscount>0)
            {
                $fn=1;
                $cellwidth=$anscount;
                $cellwidth=round(( 50 / $cellwidth ) , 1);
                $answer = "\n<table class=\"question subquestions-list questions-list\" summary=\"".str_replace('"','' ,strip_tags($this->text))." - an array type question with a single response per column\">\n\n"
                . "\t<colgroup class=\"col-responses\">\n"
                . "\t<col class=\"col-answers\" width=\"50%\" />\n";
                $odd_even = '';
                for( $c = 0 ; $c < $anscount ; ++$c )
                {
                    $odd_even = alternation($odd_even);
                    $answer .= "<col class=\"$odd_even question-item answers-list radio-list\" width=\"$cellwidth%\" />\n";
                }
                $answer .= "\t</colgroup>\n\n"
                . "\t<thead>\n"
                . "<tr>\n"
                . "\t<td>&nbsp;</td>\n";

                foreach ($ansresult as $ansrow)
                {
                    $anscode[]=$ansrow['title'];
                    $answers[]=dTexts__run($ansrow['question']);
                }
                $trbc = '';
                $odd_even = '';
                for ($_i=0;$_i<count($answers);++$_i)
                {
                    $ld = $answers[$_i];
                    $myfname = $this->fieldname.$anscode[$_i];
                    $trbc = alternation($trbc , 'row');
                    /* Check if this item has not been answered: the 'notanswered' variable must be an array,
                    containing a list of unanswered questions, the current question must be in the array,
                    and there must be no answer available for the item in this session. */
                    if ($this->mandatory=='Y' && (is_array($notanswered)) && (array_search($myfname, $notanswered) !== FALSE) && ($_SESSION['survey_'.$this->surveyid][$myfname] == "") )
                    {
                        $ld = "<span class=\"errormandatory\">{$ld}</span>";
                    }
                    $odd_even = alternation($odd_even);
                    $answer .= "\t<th class=\"$odd_even\">$ld</th>\n";
                }
                unset($trbc);
                $answer .= "</tr>\n\t</thead>\n\n\t<tbody>\n";
                $ansrowcount=0;
                $ansrowtotallength=0;
                foreach($ansresult as $ansrow)
                {
                    $ansrowcount++;
                    $ansrowtotallength=$ansrowtotallength+strlen($ansrow['question']);
                }
                $percwidth=100 - ($cellwidth*$anscount);
                foreach($labels as $ansrow)
                {
                    $answer .= "<tr>\n"
                    . "\t<th class=\"arraycaptionleft\">{$ansrow['answer']}</th>\n";
                    foreach ($anscode as $ld)
                    {
                        //if (!isset($trbc) || $trbc == 'array1') {$trbc = 'array2';} else {$trbc = 'array1';}
                        $myfname=$this->fieldname.$ld;
                        $answer .= "\t<td class=\"answer_cell_00$ld answer-item radio-item\">\n"
                        . "<label for=\"answer".$myfname.'-'.$ansrow['code']."\">\n"
                        . "\t<input class=\"radio\" type=\"radio\" name=\"".$myfname.'" value="'.$ansrow['code'].'" '
                        . 'id="answer'.$myfname.'-'.$ansrow['code'].'" '
                        . 'title="'.HTMLEscape(strip_tags($ansrow['answer'])).'"';
                        if (isset($_SESSION['survey_'.$this->surveyid][$myfname]) && $_SESSION['survey_'.$this->surveyid][$myfname] == $ansrow['code'])
                        {
                            $answer .= CHECKED;
                        }
                        elseif (!isset($_SESSION['survey_'.$this->surveyid][$myfname]) && $ansrow['code'] == '')
                        {
                            $answer .= CHECKED;
                            // Humm.. (by lemeur), not sure this section can be reached
                            // because I think $_SESSION['survey_'.$this->surveyid][$myfname] is always set (by save.php ??) !
                            // should remove the !isset part I think !!
                        }
                        $answer .= " onclick=\"$checkconditionFunction(this.value, this.name, this.type)\" />\n</label>\n\t</td>\n";
                    }
                    unset($trbc);
                    $answer .= "</tr>\n";
                    $fn++;
                }

                $answer .= "\t</tbody>\n</table>\n";
                foreach($anscode as $ld)
                {
                    $myfname=$this->fieldname.$ld;
                    $answer .= '<input type="hidden" name="java'.$myfname.'" id="java'.$myfname.'" value="';
                    if (isset($_SESSION['survey_'.$this->surveyid][$myfname]))
                    {
                        $answer .= $_SESSION['survey_'.$this->surveyid][$myfname];
                    }
                    $answer .= "\" />\n";
                }
            }
            else
            {
                $answer = '<p class="error">'.$clang->gT('Error: There are no answers defined for this question.')."</p>";
            }
        }
        else
        {
            $answer = "<p class='error'>".$clang->gT("Error: There are no answer options for this question and/or they don't exist in this language.")."</p>\n";
        }
        return $answer;
    }

    public function getSqsuffix()
    {
        return '';
    }

    public function getRowDivID()
    {
        return null;
    }

    public function compareField($sgqa, $sq)
    {
        return false;
    }

    public function availableAttributes($attr = false)
    {
        $attrs=array("statistics_showgraph","statistics_graphtype","hide_tip","hidden","page_break","public_statistics","random_order","parent_order","scale_export","random_group");
        return $attr?array_key_exists($attr,$attrs):$attrs;
    }

    public function questionProperties($prop = false)
    {
        $clang=Yii::app()->lang;
        $props=array('description' => $clang->gT("Array by column"),'group' => $clang->gT('Arrays'),'class' => 'array-flexible-column','hasdefaultvalues' => 0,'subquestions' => 1,'assessable' => 1,'answerscales' => 1);
        return $prop?$props[$prop]:$props;
    }
}
?>