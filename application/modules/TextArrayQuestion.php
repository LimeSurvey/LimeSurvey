<?php
class TextArrayQuestion extends ArrayQuestion
{
    public function getAnswerHTML()
    {
        global $thissurvey;
        global $notanswered;
        $repeatheadings = Yii::app()->getConfig("repeatheadings");
        $minrepeatheadings = Yii::app()->getConfig("minrepeatheadings");
        $extraclass ="";
        $clang = Yii::app()->lang;
        $caption=$clang->gT("An array of sub-question on each cell. The sub-question text are in the table header and concerns line header. ");
        if ($thissurvey['nokeyboard']=='Y')
        {
            includeKeypad();
            $kpclass = "text-keypad";
        }
        else
        {
            $kpclass = "";
        }

        $checkconditionFunction = "checkconditions";
        $sSeperator = getRadixPointData($thissurvey['surveyls_numberformat']);
        $sSeperator = $sSeperator['seperator'];

        $defaultvaluescript = "";
        $qquery = "SELECT other FROM {{questions}} WHERE qid={$this->id} AND language='".$_SESSION['survey_'.$this->surveyid]['s_lang']."'";

        $qresult = Yii::app()->db->createCommand($qquery)->query();
        $qrow = $qresult->read(); $other = $qrow['other'];

        $aQuestionAttributes = $this->getAttributeValues();

        $show_grand = $aQuestionAttributes['show_grand_total'];
        $totals_class = '';
        $num_class = '';
        $show_totals = '';
        $col_total = '';
        $row_total = '';
        $total_col = '';
        $col_head = '';
        $row_head = '';
        $grand_total = '';
        $q_table_id = '';
        $q_table_id_HTML = '';

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
        if ($aQuestionAttributes['numbers_only']==1)
        {
            $checkconditionFunction = "fixnum_checkconditions";
            $q_table_id = 'totals_'.$this->id;
            $q_table_id_HTML = ' id="'.$q_table_id.'"';
            $num_class = ' numbers-only';
            $extraclass.=" numberonly";
            $caption.=$clang->gT("Each answers are number. ");
            switch ($aQuestionAttributes['show_totals'])
            {
                case 'R':
                    $totals_class = $show_totals = 'row';
                    $row_total = '<td class="total information-item">
                    <label>
                    <input name="[[ROW_NAME]]_total" title="[[ROW_NAME]] total" size="[[INPUT_WIDTH]]" value="" type="text" disabled="disabled" class="disabled" />
                    </label>
                    </td>';
                    $col_head = '            <th class="total">Total</th>';
                    if($show_grand == true)
                    {
                        $row_head = '
                        <th class="answertext total">Grand total</th>';
                        $col_total = '
                        <td>&nbsp;</td>';
                        $grand_total = '
                        <td class="total grand information-item">
                        <input type="text" size="[[INPUT_WIDTH]]" value="" disabled="disabled" class="disabled" />
                        </td>';
                    };
                    $caption.=$clang->gT("The last row show the total for the column. ");
                    break;
                case 'C':
                    $totals_class = $show_totals = 'col';
                    $col_total = '
                    <td class="total information-item">
                    <input type="text" size="[[INPUT_WIDTH]]" value="" disabled="disabled" class="disabled" />
                    </td>';
                    $row_head = '
                    <th class="answertext total">Total</th>';
                    if($show_grand == true)
                    {
                        $row_total = '
                        <td class="total information-item">&nbsp;</td>';
                        $col_head = '            <th class="total">Grand Total</th>';
                        $grand_total = '
                        <td class="total grand">
                        <input type="text" size="[[INPUT_WIDTH]]" value="" disabled="disabled" class="disabled" />
                        </td>';
                    };
                    $caption.=$clang->gT("The last column show the total for the row. ");
                    break;
                case 'B':
                    $totals_class = $show_totals = 'both';
                    $row_total = '            <td class="total information-item">
                    <label>
                    <input name="[[ROW_NAME]]_total" title="[[ROW_NAME]] total" size="[[INPUT_WIDTH]]" value="" type="text" disabled="disabled" class="disabled" />
                    </label>
                    </td>';
                    $col_total = '
                    <td  class="total information-item">
                    <input type="text" size="[[INPUT_WIDTH]]" value="" disabled="disabled" class="disabled" />
                    </td>';
                    $col_head = '            <th class="total">Total</th>';
                    $row_head = '
                    <th class="answertext">Total</th>';
                    if($show_grand == true)
                    {
                        $grand_total = '
                        <td class="total grand information-item">
                        <input type="text" size="[[INPUT_WIDTH]]" value="" disabled="disabled"/>
                        </td>';
                    }
                    else
                    {
                        $grand_total = '
                        <td>&nbsp;</td>';
                    };
                    $caption.=$clang->gT("The last row show the total for the column and the last column show the total for the row. ");
                    break;
            };
            if(!empty($totals_class))
            {
                $totals_class = ' show-totals '.$totals_class;
                if($aQuestionAttributes['show_grand_total'])
                {
                    $totals_class .= ' grand';
                    $show_grand = true;
                };
            };
        }
        else
        {
        };
        if (trim($aQuestionAttributes['answer_width'])!='')
        {
            $answerwidth=$aQuestionAttributes['answer_width'];
        }
        else
        {
            $answerwidth=20;
        };
        if (trim($aQuestionAttributes['text_input_width'])!='')
        {
            $inputwidth=$aQuestionAttributes['text_input_width'];
            $extraclass .=" inputwidth-".trim($aQuestionAttributes['text_input_width']);
        }
        else
        {
            $inputwidth = 20;
        }
        $columnswidth=100-($answerwidth*2);

        $lquery = "SELECT * FROM {{questions}} WHERE parent_qid={$this->id}  AND language='".$_SESSION['survey_'.$this->surveyid]['s_lang']."' and scale_id=1 ORDER BY question_order";
        $lresult = Yii::app()->db->createCommand($lquery)->query();
        if (count($lresult)> 0)
        {
            foreach($lresult->readAll() as $lrow)
            {
                $labelans[]=$lrow['question'];
                $labelcode[]=$lrow['title'];
            }
            $numrows=count($labelans);
            if ($this->mandatory != 'Y' && SHOW_NO_ANSWER == 1) {$numrows++;}
            if( ($show_grand == true &&  $show_totals == 'col' ) || $show_totals == 'row' ||  $show_totals == 'both' )
            {
                ++$numrows;
            };
            $cellwidth=$columnswidth/$numrows;

            $cellwidth=sprintf('%02d', $cellwidth);

            $ansquery = "SELECT count(question) FROM {{questions}} WHERE parent_qid={$this->id} and scale_id=0 AND question like '%|%'";
            $ansresult = dbExecuteAssoc($ansquery)->read();
            if ($ansresult['count(question)']>0)
            {
                $right_exists=true;
                $answerwidth=$answerwidth/2;
                $caption.=$clang->gT("The last cell give some information. ");
            }
            else
            {
                $right_exists=false;
            }
            // $right_exists is a flag to find out if there are any right hand answer parts. If there arent we can leave out the right td column
            $ansresult = $this->getChildren();
            $anscount = count($ansresult);
            $fn=1;

            $answer_cols = "\t<colgroup class=\"col-responses\">\n"
            ."\n\t\t<col class=\"answertext\" width=\"$answerwidth%\" />\n";

            $answer_head_line= "\t\t\t<td width='$answerwidth%'>&nbsp;</td>\n";

            $odd_even = '';
            foreach ($labelans as $ld)
            {
                $answer_head_line .= "\t<th class=\"answertext\">".$ld."</th>\n";
                $odd_even = alternation($odd_even);
                $answer_cols .= "<col class=\"$odd_even\" width=\"$cellwidth%\" />\n";
            }
            if ($right_exists)
            {
                $answer_head_line .= "\t<td>&nbsp;</td>\n";// class=\"answertextright\"
                $odd_even = alternation($odd_even);
                $answer_cols .= "<col class=\"answertextright $odd_even\" width=\"$cellwidth%\" />\n";
            }

            if( ($show_grand == true &&  $show_totals == 'col' ) || $show_totals == 'row' ||  $show_totals == 'both' )
            {
                $answer_head_line .= $col_head;
                $odd_even = alternation($odd_even);
                $answer_cols .= "\t\t<col class=\"$odd_even\" width=\"$cellwidth%\" />\n";
            }
            $answer_cols .= "\t</colgroup>\n";

            $answer_head = "\n\t<thead>\n\t\t<tr>\n"
            . $answer_head_line
            . "</tr>\n\t</thead>\n";

            $answer = "\n<table$q_table_id_HTML class=\"question subquestions-list questions-list{$extraclass}$num_class"."$totals_class\" >" 
            . "<caption class=\"hide screenreader\">{$caption}</caption>\n"
            . $answer_cols . $answer_head;

            $answer .= "<tbody>";
            $trbc = '';
            foreach ($ansresult as $ansrow)
            {
                if (isset($repeatheadings) && $repeatheadings > 0 && ($fn-1) > 0 && ($fn-1) % $repeatheadings == 0)
                {
                    if ( ($anscount - $fn + 1) >= $minrepeatheadings )
                    {
                        $answer .= "</tbody>\n<tbody>";// Close actual body and open another one
                        $answer .= "<tr class=\"repeat headings\">\n"
                        . $answer_head_line
                        . "</tr>\n";
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
                        if((array_search($myfname2, $notanswered) !== FALSE) && $_SESSION['survey_'.$this->surveyid][$myfname2] == '')
                        {
                            $emptyresult=1;
                        }
                    }
                    if ($emptyresult == 1)
                    {
                        $answertext = "<span class=\"errormandatory\">{$answertext}</span>";
                    }
                }

                // Get array_filter stuff
                $trbc = alternation($trbc , 'row');
                list($htmltbody2, $hiddenfield)=return_array_filter_strings($this, $aQuestionAttributes, $thissurvey, $ansrow, $myfname, $trbc, $myfname,"tr","$trbc subquestion-list questions-list");

                $answer .= $htmltbody2;

                if (strpos($answertext,'|')) {$answertext=substr($answertext,0, strpos($answertext,'|'));}
                $answer .= "\t\t\t<th class=\"answertext\">\n"
                . "\t\t\t\t".$hiddenfield
                . "$answertext\n"
                . "\t\t\t\t<input type=\"hidden\" name=\"java$myfname\" id=\"java$myfname\" value=\"";
                if (isset($_SESSION['survey_'.$this->surveyid][$myfname])) {$answer .= $_SESSION['survey_'.$this->surveyid][$myfname];}
                $answer .= "\" />\n\t\t\t</th>\n";
                $thiskey=0;
                foreach ($labelcode as $ld)
                {

                    $myfname2=$myfname."_$ld";
                    $myfname2value = isset($_SESSION['survey_'.$this->surveyid][$myfname2]) ? $_SESSION['survey_'.$this->surveyid][$myfname2] : "";
                    $answer .= "\t<td class=\"answer_cell_00$ld answer-item text-item\">\n"
                    . "\t\t\t\t<label for=\"answer{$myfname2}\" class=\"hide\">{$labelans[$thiskey]}</label\n"
                    . "\t\t\t\t<input type=\"hidden\" name=\"java{$myfname2}\" id=\"java{$myfname2}\" />\n"
                    . "\t\t\t\t<input type=\"text\" name=\"$myfname2\" id=\"answer{$myfname2}\" class=\"text {$kpclass}\" {$maxlength} "
                    . 'size="'.$inputwidth.'" '
                    . ' value="'.str_replace ('"', "'", str_replace('\\', '', $myfname2value))."\" />\n";
                    $answer .= "\n\t\t\t</td>\n";
                    $thiskey += 1;
                }
                if (strpos($answertextsave,'|'))
                {
                    $answertext=substr($answertextsave,strpos($answertextsave,'|')+1);
                    $answer .= "\t\t\t<td class=\"answertextright\" style=\"text-align:left;\" width=\"$answerwidth%\">$answertext</td>\n";
                }
                elseif ($right_exists)
                {
                    $answer .= "\t\t\t<td class=\"answertextright\" style='text-align:left;' width='$answerwidth%'>&nbsp;</td>\n";
                }

                $answer .= str_replace(array('[[ROW_NAME]]','[[INPUT_WIDTH]]') , array(strip_tags($answertext),$inputwidth) , $row_total);
                $answer .= "\n\t\t</tr>\n";
                //IF a MULTIPLE of flexi-redisplay figure, repeat the headings
                $fn++;
            }
            if($show_totals == 'col' || $show_totals == 'both' || $grand_total == true)
            {
                $answer .= "\t\t<tr class=\"total\">$row_head";
                for( $a = 0; $a < count($labelcode) ; ++$a )
                {
                    $answer .= str_replace(array('[[ROW_NAME]]','[[INPUT_WIDTH]]') , array(strip_tags($answertext),$inputwidth) , $col_total);
                };
                $answer .= str_replace(array('[[ROW_NAME]]','[[INPUT_WIDTH]]') , array(strip_tags($answertext),$inputwidth) , $grand_total)."\n\t\t</tr>\n";
            }
            $answer .= "\t</tbody>\n</table>\n";
            if(!empty($q_table_id))
            {
                if ($aQuestionAttributes['numbers_only']==1)
                {
                    $radix = $sSeperator;
                }
                else {
                    $radix = 'X';   // to indicate that should not try to change entered values
                }
                $answer .= "\n<script type=\"text/javascript\">new multi_set('$q_table_id','$radix');</script>\n";
            }
            else
            {
                $addcheckcond = <<< EOD
<script type="text/javascript">
<!--
$(document).ready(function()
{
    $('#question{$this->id} :input:visible:enabled').each(function(index){
        $(this).bind('keyup',function(e) {
            checkconditions($(this).attr('value'), $(this).attr('name'), $(this).attr('type'));
            return true;
        })
    })
})
// -->
</script>
EOD;
                $answer .= $addcheckcond;
            }
        }
        else
        {
            $answer = "\n<p class=\"error\">".$clang->gT("Error: There are no answer options for this question and/or they don't exist in this language.")."</p>\n";
        }
        return $answer;
    }

    public function getDataEntry($idrow, &$fnames, $language)
    {
        $output = "<table>\n";
        $q = $this;
        while ($q->id == $this->id)
        {
            $output .= "\t<tr>\n"
            . "<td>{$q->sq1}:{$q->sq2}</td>\n";
            $output .= "<td>\n";
            $output .= "\t<input type='text' name='{$q->fieldname}' value='";
            if(!empty($idrow[$q->fieldname])) {$output .= $idrow[$q->fieldname];}
            $output .= "' /></td>\n"
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

    public function jsVarNameOn()
    {
        return 'answer'.$this->fieldname;
    }

    public function onlyNumeric()
    {
        $attributes = $this->getAttributeValues();
        return array_key_exists('numbers_only', $attributes) && $attributes['numbers_only'] == 1;
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

    public function availableOptions()
    {
        return array('other' => false, 'valid' => true, 'mandatory' => true);
    }

    public function getDataEntryView($language)
    {
        $qidattributes = $this->getAttributeValues();
        $lquery = "SELECT * FROM {{questions}} WHERE scale_id=1 and parent_qid={$this->id} and language='{$language->getlangcode()}' ORDER BY question_order";
        $lresult=dbExecuteAssoc($lquery)->readAll() or die ("Couldn't get labels, Type \":\"<br />$lquery<br />");

        $meaquery = "SELECT * FROM {{questions}} WHERE scale_id=0 and parent_qid={$this->id} and language='{$language->getlangcode()}' ORDER BY question_order";
        $mearesult=dbExecuteAssoc($meaquery)->readAll() or die ("Couldn't get answers, Type \":\"<br />$meaquery<br />");

        $output = "<table>";
        $output .= "<tr><td></td>";
        $labelcodes = array();
        foreach($lresult as $data)
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
                $output .= "<input type='text' name='{$this->fieldname}{$mearow['title']}_{$ld}' size=4 />";
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
                $output .= "\t\t\t\t".printablesurvey::input_type_image('text','',23);
                $output .= (Yii::app()->getConfig('showsgqacode') ? " (".$fieldname.$frow['title']."_".$xaxisarray[$i].") " : '')."\n";
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
                $pdfoutput[$a][$i]="_____________";
            }
            $a++;
        }

        return $pdfoutput;
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

    public function getConditionAnswers()
    {
        return array();
    }

    public function QueXMLAppendAnswers(&$question)
    {
        quexml_create_subQuestions($question,$this->id,$this->surveyid . 'X' . $this->gid . 'X' . $this->id);
        quexml_create_multi($question,$this->id,$this->surveyid . 'X' . $this->gid . 'X' . $this->id,1,array('f' => 'integer', 'len' => 10, 'lab' => ''));
    }

    public function availableAttributes($attr = false)
    {
        $attrs=array("answer_width","repeat_headings","array_filter","array_filter_exclude","array_filter_style","em_validation_q","em_validation_q_tip","em_validation_sq","em_validation_sq_tip","statistics_showgraph","statistics_graphtype","hide_tip","hidden","max_answers","maximum_chars","min_answers","numbers_only","show_totals","show_grand_total","page_break","random_order","parent_order","text_input_width","random_group");
        return $attr?in_array($attr,$attrs):$attrs;
    }

    public function questionProperties($prop = false)
    {
        $clang=Yii::app()->lang;
        $props=array('description' => $clang->gT("Array (Texts)"),'group' => $clang->gT('Arrays'),'subquestions' => 2,'class' => 'array-multi-flexi-text','hasdefaultvalues' => 0,'assessable' => 0,'answerscales' => 0,'enum' => 0);
        return $prop?$props[$prop]:$props;
    }

    public function getVarAttributeLEM($sgqa,$value)
    {
        return htmlspecialchars(parent::getVarAttributeLEM($sgqa,$value),ENT_NOQUOTES);
    }

}
?>
