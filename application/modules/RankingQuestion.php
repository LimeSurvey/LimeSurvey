<?php
class RankingQuestion extends QuestionModule
{
    protected $answers;
    public function getAnswerHTML()
    {
        global $thissurvey;

        $clang=Yii::app()->lang;
        $imageurl = Yii::app()->getConfig("imageurl");

        $checkconditionFunction = "checkconditions";

        $aQuestionAttributes = $this->getAttributeValues();
        $answers = $this->getAnswers();
        $anscount = count($answers);
        if (trim($aQuestionAttributes["max_answers"])!='')
        {
            $max_answers=trim($aQuestionAttributes["max_answers"]);
        } else {
            $max_answers=$anscount;
        }
        if (trim($aQuestionAttributes["min_answers"])!='')
        {
            $min_answers=trim($aQuestionAttributes["min_answers"]);
        } else {
            $min_answers=0;
        }

        // First start by a ranking without javascript : just a list of select box
        // construction select box
        $answer = '<div class="ranking-answers">
        <ul class="answers-list select-list">';

        for ($i=1; $i<=$anscount; $i++)
        {
            $myfname=$this->fieldname.$i;
            $answer .= "\n<li class=\"select-item\">";
            $answer .="<label for=\"answer{$myfname}\">";
            if($i==1){
                $answer .=$clang->gT('First choice');
            }else{
                $answer .=$clang->gT('Next choice');
            }
            $answer .= "</label>";
            $answer .= "<select name=\"{$myfname}\" id=\"answer{$myfname}\">\n";
            if (!$_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$this->fieldname])
            {
                $answer .= "\t<option value=\"\"".SELECTED.">".$clang->gT('Please choose...')."</option>\n";
            }
            foreach ($answers as $ansrow)
            {
                $thisvalue="";
                $answer .="\t<option value=\"{$ansrow['code']}\"";
                    if (isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname]) && $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname] == $ansrow['code'])
                    {
                        $answer .= SELECTED;
                        $thisvalue=$_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname];
                    }
                $answer .=">".flattenText($ansrow['answer'])."</option>\n";
            }
            $answer .="</select>";
            // Hidden form: maybe can be replaced with ranking.js
            $answer .="<input type=\"hidden\" id=\"java{$myfname}\" disabled=\"disabled\" value=\"{$thisvalue}\"/>";
            $answer .="</li>";
        }
        $answer .="</ul>"
            . "<div style='display:none' id='ranking-{$this->id}-maxans'>{".$max_answers."}</div>"
            . "<div style='display:none' id='ranking-{$this->id}-minans'>{".$min_answers."}</div>"
            . "<div style='display:none' id='ranking-{$this->id}-name'>javatbd".$this->fieldname."</div>"
            . "</div>";
        // The list with HTML answres
        $answer .="<div style=\"display:none\">";
        foreach ($answers as $ansrow)
        {
            $answer.="<div id=\"htmlblock-{$this->id}-{$ansrow['code']}\">{$ansrow['answer']}</div>";
        }
        $answer .="</div>";

        if(trim($aQuestionAttributes['choice_title'][$clang->langcode]) != '')
        {
            $choice_title=htmlspecialchars(trim($aQuestionAttributes['choice_title'][$clang->langcode]), ENT_QUOTES);
        }
        else
        {
            $choice_title=$clang->gT("Your Choices",'js');
        }
        if(trim($aQuestionAttributes['rank_title'][$clang->langcode]) != '')
        {
            $rank_title=htmlspecialchars(trim($aQuestionAttributes['rank_title'][$clang->langcode]), ENT_QUOTES);
        }
        else
        {
            $rank_title=$clang->gT("Your Ranking",'js');
        }
        $answer .= "<script type='text/javascript'>\n"
        . "  <!--\n"
        . "var translt = {
                choicetitle: '{$choice_title}',
                ranktitle: '{$rank_title}'
            };\n"
        ." doDragDropRank({$this->id},{$aQuestionAttributes["showpopups"]},{$aQuestionAttributes["samechoiceheight"]},{$aQuestionAttributes["samelistheight"]});\n"
        ." -->\n"
        ."</script>\n";
        return $answer;
    }

    public function getHelp()
    {
        $clang = Yii::app()->lang;
        return $clang->gT("Click on an item in the list on the left, starting with your highest ranking item, moving through to your lowest ranking item.");
    }

    public function getDataEntry($idrow, &$fnames, $language)
    {
        $clang = Yii::app()->lang;
        $currentvalues=array();
        $myfname=$this->surveyid.'X'.$this->gid.'X'.$this->id;
        $q = $this;
        while ($q->id==$this->id)
        {
            //Let's get all the existing values into an array
            if ($idrow[$q->fieldname])
            {
                $currentvalues[] = $idrow[$q->fieldname];
            }
            if(!$fname=next($fnames)) break;
            $q=$fname['q'];
        }
        $ansquery = "SELECT * FROM {{answers}} WHERE language = '{$language}' AND qid=$thisqid ORDER BY sortorder, answer";
        $ansresult = dbExecuteAssoc($ansquery);
        $anscount = $ansresult->count();
        $output = "\t<script type='text/javascript'>\n"
        ."\t<!--\n"
        ."function rankthis_$thisqid(\$code, \$value)\n"
        ."\t{\n"
        ."\t\$index=document.editresponse.CHOICES_$thisqid.selectedIndex;\n"
        ."\tfor (i=1; i<=$anscount; i++)\n"
        ."{\n"
        ."\$b=i;\n"
        ."\$b += '';\n"
        ."\$inputname=\"RANK_$thisqid\"+\$b;\n"
        ."\$hiddenname=\"d$myfname\"+\$b;\n"
        ."\$cutname=\"cut_$thisqid\"+i;\n"
        ."document.getElementById(\$cutname).style.display='none';\n"
        ."if (!document.getElementById(\$inputname).value)\n"
        ."\t{\n"
        ."\tdocument.getElementById(\$inputname).value=\$value;\n"
        ."\tdocument.getElementById(\$hiddenname).value=\$code;\n"
        ."\tdocument.getElementById(\$cutname).style.display='';\n"
        ."\tfor (var b=document.getElementById('CHOICES_$thisqid').options.length-1; b>=0; b--)\n"
        ."{\n"
        ."if (document.getElementById('CHOICES_$thisqid').options[b].value == \$code)\n"
        ."\t{\n"
        ."\tdocument.getElementById('CHOICES_$thisqid').options[b] = null;\n"
        ."\t}\n"
        ."}\n"
        ."\ti=$anscount;\n"
        ."\t}\n"
        ."}\n"
        ."\tif (document.getElementById('CHOICES_$thisqid').options.length == 0)\n"
        ."{\n"
        ."document.getElementById('CHOICES_$thisqid').disabled=true;\n"
        ."}\n"
        ."\tdocument.editresponse.CHOICES_$thisqid.selectedIndex=-1;\n"
        ."\t}\n"
        ."function deletethis_$thisqid(\$text, \$value, \$name, \$thisname)\n"
        ."\t{\n"
        ."\tvar qid='$thisqid';\n"
        ."\tvar lngth=qid.length+4;\n"
        ."\tvar cutindex=\$thisname.substring(lngth, \$thisname.length);\n"
        ."\tcutindex=parseFloat(cutindex);\n"
        ."\tdocument.getElementById(\$name).value='';\n"
        ."\tdocument.getElementById(\$thisname).style.display='none';\n"
        ."\tif (cutindex > 1)\n"
        ."{\n"
        ."\$cut1name=\"cut_$thisqid\"+(cutindex-1);\n"
        ."\$cut2name=\"d$myfname\"+(cutindex);\n"
        ."document.getElementById(\$cut1name).style.display='';\n"
        ."document.getElementById(\$cut2name).value='';\n"
        ."}\n"
        ."\telse\n"
        ."{\n"
        ."\$cut2name=\"d$myfname\"+(cutindex);\n"
        ."document.getElementById(\$cut2name).value='';\n"
        ."}\n"
        ."\tvar i=document.getElementById('CHOICES_$thisqid').options.length;\n"
        ."\tdocument.getElementById('CHOICES_$thisqid').options[i] = new Option(\$text, \$value);\n"
        ."\tif (document.getElementById('CHOICES_$thisqid').options.length > 0)\n"
        ."{\n"
        ."document.getElementById('CHOICES_$thisqid').disabled=false;\n"
        ."}\n"
        ."\t}\n"
        ."\t//-->\n"
        ."\t</script>\n";
        foreach ($ansresult->readAll() as $ansrow) //Now we're getting the codes and answers
        {
            $answers[] = array($ansrow['code'], $ansrow['answer']);
        }
        //now find out how many existing values there are

        $chosen[]=""; //create array
        if (!isset($ranklist)) {$ranklist="";}

        if (isset($currentvalues))
        {
            $existing = count($currentvalues);
        }
        else {$existing=0;}
        for ($j=1; $j<=$anscount; $j++) //go through each ranking and check for matching answer
        {
            $k=$j-1;
            if (isset($currentvalues) && isset($currentvalues[$k]) && $currentvalues[$k])
            {
                foreach ($answers as $ans)
                {
                    if ($ans[0] == $currentvalues[$k])
                    {
                        $thiscode=$ans[0];
                        $thistext=$ans[1];
                    }
                }
            }
            $ranklist .= "$j:&nbsp;<input class='ranklist' id='RANK_$thisqid$j'";
            if (isset($currentvalues) && isset($currentvalues[$k]) && $currentvalues[$k])
            {
                $ranklist .= " value='".$thistext."'";
            }
            $ranklist .= " onFocus=\"this.blur()\"  />\n"
            . "<input type='hidden' id='d$myfname$j' name='$myfname$j' value='";
            if (isset($currentvalues) && isset($currentvalues[$k]) && $currentvalues[$k])
            {
                $ranklist .= $thiscode;
                $chosen[]=array($thiscode, $thistext);
            }
            $ranklist .= "' />\n"
            . "<img src='".Yii::app()->getConfig('imageurl')."/cut.gif' alt='".$clang->gT("Remove this item")."' title='".$clang->gT("Remove this item")."' ";
            if ($j != $existing)
            {
                $ranklist .= "style='display:none'";
            }
            $ranklist .= " id='cut_$thisqid$j' onclick=\"deletethis_$thisqid(document.editresponse.RANK_$thisqid$j.value, document.editresponse.d$myfname$j.value, document.editresponse.RANK_$thisqid$j.id, this.id)\" /><br />\n\n";
        }

        if (!isset($choicelist)) {$choicelist="";}
        $choicelist .= "<select class='choicelist' size='$anscount' name='CHOICES' id='CHOICES_$thisqid' onclick=\"rankthis_$thisqid(this.options[this.selectedIndex].value, this.options[this.selectedIndex].text)\" >\n";
        foreach ($answers as $ans)
        {
            if (!in_array($ans, $chosen))
            {
                $choicelist .= "\t<option value='{$ans[0]}'>{$ans[1]}</option>\n";
            }
        }
        $choicelist .= "</select>\n";
        $output .= "\t<table>\n"
        ."<tr>\n"
        ."\t<td>\n"
        ."<strong>"
        .$clang->gT("Your Choices").":</strong><br />\n"
        .$choicelist
        ."\t</td>\n"
        ."\t<td align='left'>\n"
        ."<strong>"
        .$clang->gT("Your Ranking").":</strong><br />\n"
        .$ranklist
        ."\t</td>\n"
        ."</tr>\n"
        ."\t</table>\n"
        ."\t<input type='hidden' name='multi' value='$anscount' />\n"
        ."\t<input type='hidden' name='lastfield' value='";
        if (isset($multifields)) {$output .= $multifields;}
        $output .= "' />\n";
        prev($fnames);
        return $output;
    }

    protected function getAnswers()
    {
        if ($this->answers) return $this->answers;
        $aQuestionAttributes = $this->getAttributeValues();
        if ($aQuestionAttributes['random_order']==1) {
            $ansquery = "SELECT * FROM {{answers}} WHERE qid=$this->id AND language='".$_SESSION['survey_'.$this->surveyid]['s_lang']."' and scale_id=0 ORDER BY ".dbRandom();
        } else {
            $ansquery = "SELECT * FROM {{answers}} WHERE qid=$this->id AND language='".$_SESSION['survey_'.$this->surveyid]['s_lang']."' and scale_id=0 ORDER BY sortorder, answer";
        }
        return $this->answers = dbExecuteAssoc($ansquery)->readAll();  //Checked
    }

    public function getHeaderIncludes()
    {
        
        return array('ranking.js' => 'js', 'jquery/jquery.actual/jquery.actual.min.js' => 'js', 'ranking.css' => 'css');
    }

    public function createFieldmap()
    {
        $clang = Yii::app()->lang;
        $data = Answers::model()->findAllByAttributes(array('qid' => $this->id, 'language' => $this->language));
        $map = array();
        for ($i=1; $i<=count($data); $i++)
        {
            $fieldname="{$this->surveyid}X{$this->gid}X{$this->id}$i";
            $q = clone $this;
            $q->fieldname = $fieldname;
            $q->aid = $i;
            $q->sq=sprintf($clang->gT('Rank %s'),$i);
            $map[$fieldname]=$q;
        }
        return $map;
    }

    public function getCsuffix()
    {
        return $this->aid;
    }

    public function getExtendedAnswer($value, $language)
    {
        if ($value == "-oth-")
        {
            return $language->gT("Other")." [$value]";
        }
        $result = Answers::model()->getAnswerFromCode($this->id,$value,$language->langcode) or die ("Couldn't get answer type."); //Checked
        if($result->count())
        {
            $result =array_values($result->readAll());
            return $result[count($result)-1]['answer']." [$value]";
        }
        return $value;
    }

    public function getFullAnswer($answerCode, $export, $survey)
    {
        $answers = $survey->getAnswers($this->id);
        if (array_key_exists($answerCode, $answers))
        {
            return $answers[$answerCode]['answer'];
        }
        else
        {
            return null;
        }
    }

    public function getFieldSubHeading($survey, $export, $code)
    {
        return ' ['.$export->translate('Ranking', $export->languageCode).' '.$this->aid.']';
    }

    public function getSPSSAnswers()
    {
        global $language, $length_vallabel;
        $query = "SELECT {{answers}}.code, {{answers}}.answer,
        {{questions}}.type FROM {{answers}}, {{questions}} WHERE";

        $query .= " {{answers}}.qid = '".$this->id."' and {{questions}}.language='".$language."' and  {{answers}}.language='".$language."'
        and {{questions}}.qid='".$this->id."' ORDER BY sortorder ASC";
        $result= Yii::app()->db->createCommand($query)->query(); //Checked
        foreach ($result->readAll() as $row)
        {
            $answers[] = array('code'=>$row['code'], 'value'=>mb_substr(stripTagsFull($row["answer"]),0,$length_vallabel));
        }
        return $answers;
    }

    public function getAnswerArray($em)
    {
        return (isset($em->qans[$this->id]) ? $em->qans[$this->id] : NULL);
    }

    public function getQuestion()
    {
        return $this->sq;
    }

    public function getRowDivID()
    {
        return $this->fieldname;
    }

    public function getArrayFilterNames($sgq, $subqs, $qans, $sqsuffix, $equal = true)
    {
        $rankables = array();
        foreach ($qans as $k=>$v)
        {
            $rankable = explode('~',$k);
            $rankables[] = '_' . $rankable[1];
        }
        if (array_search($sqsuffix,$rankables) !== false)
        {
            $fsqs = array();
            foreach ($subqs as $fsq)
            {
                // we know the suffix exists
                $fsqs[] = '(' . $sgq . $fsq['csuffix'] . '.NAOK ' . ($equal ? '==' : '!=') . " '" . substr($sqsuffix,1) . "')";
            }
            if (count($fsqs) > 0)
            {
                return '(' . implode(' ' . ($equal ? 'or' : 'and'). ' ', $fsqs) . ')';
            }
        }
        return null;
    }

    public function includeRanks()
    {
        return true;
    }

    public function getVarAttributeValueNAOK($name, $default, $gseq, $qseq, $ansArray)
    {
        $code = LimeExpressionManager::GetVarAttribute($name,'code',$default,$gseq,$qseq);
        $scale_id = LimeExpressionManager::GetVarAttribute($name,'scale_id','0',$gseq,$qseq);
        $which_ans = $scale_id . '~' . $code;
        if (is_null($ansArray))
        {
            return $default;
        }
        else
        {
            if (isset($ansArray[$which_ans])) {
                $answerInfo = explode('|',$ansArray[$which_ans]);
                $answer = $answerInfo[0];
            }
            else {
                $answer = $default;
            }
            return $answer;
        }
    }

    public function getMandatoryTip()
    {
        $clang=Yii::app()->lang;
        return $clang->gT('Please rank all items').'.';
    }

    public function getShownJS()
    {
        return 'which_ans = "0~" + value;'
                . 'if (typeof attr.answers[which_ans] === "undefined") return value;'
                . 'answerParts = attr.answers[which_ans].split("|");'
                . 'answerParts.shift();'
                . 'return answerParts.join("|");';
    }

    public function getValueJS()
    {
        return 'which_ans = "0~" + value;'
                . 'if (typeof attr.answers[which_ans] === "undefined") return "";'
                . 'answerParts = attr.answers[which_ans].split("|");'
                . 'return answerParts[0];';
    }

    public function getDataEntryView($language)
    {
        $ansquery = "SELECT * FROM {{answers}} WHERE qid={$this->id} AND language='{$language->getlangcode()}' ORDER BY sortorder, answer";
        $ansresult = dbExecuteAssoc($ansquery);
        $anscount = $ansresult->getRowCount();

        foreach ($ansresult->readAll() as $ansrow)
        {
            $answers[] = array($ansrow['code'], $ansrow['answer']);
        }
        for ($i=1; $i<=$anscount; $i++)
        {
            if (isset($fname))
            {
                $myfname=$fname.$i;
            }
            if (isset($myfname) && Yii::app()->session[$myfname])
            {
                $existing++;
            }
        }
        for ($i=1; $i<=$anscount; $i++)
        {
            if (isset($fname))
            {
                $myfname = $fname.$i;
            }
            if (isset($myfname) && Yii::app()->session[$myfname])
            {
                foreach ($answers as $ans)
                {
                    if ($ans[0] == Yii::app()->session[$myfname])
                    {
                        $thiscode=$ans[0];
                        $thistext=$ans[1];
                    }
                }
            }
            if (!isset($ranklist)) {$ranklist="";}
            $ranklist .= "&nbsp;<font color='#000080'>{$i}:&nbsp;<input class='ranklist' type='text' name='RANK{$i}' id='RANK_{$this->id}{$i}'";
            if (isset($myfname) && Yii::app()->session[$myfname])
            {
                $ranklist .= " value='";
                $ranklist .= $thistext;
                $ranklist .= "'";
            }
            $ranklist .= " onFocus=\"this.blur()\"  />\n";
            $ranklist .= "<input type='hidden' id='d{$this->fieldname}{$i}' name='{$this->fieldname}{$i}' value='";
            $chosen[]=""; //create array
            if (isset($myfname) && Yii::app()->session[$myfname])
            {
                $ranklist .= $thiscode;
                $chosen[]=array($thiscode, $thistext);
            }
            $ranklist .= "' /></font>\n";
            $ranklist .= "<img src='".Yii::app()->getConfig('imageurl')."/cut.gif' alt='".$language->gT("Remove this item")."' title='".$language->gT("Remove this item")."' ";
            if (!isset($existing) || $i != $existing)
            {
                $ranklist .= "style='display:none'";
            }
            $ranklist .= " id='cut_{$this->id}{$i}' onclick=\"deletethis_{$this->id}(document.addsurvey.RANK_{$this->id}{$i}.value, document.addsurvey.d{$this->fieldname}{$i}.value, document.addsurvey.RANK_{$this->id}{$i}.id, this.id)\" /><br />\n\n";
        }
        $choicelist = "<select size='{$anscount}' class='choicelist' name='CHOICES' id='CHOICES_{$this->id}' onclick=\"rankthis_{$this->id}(this.options[this.selectedIndex].value, this.options[this.selectedIndex].text)\" >\n";
        foreach ($answers as $ans)
        {

            if (!in_array($ans, $chosen))
            {
                $choicelist .= "\t<option value='{$ans[0]}'>{$ans[1]}</option>\n";
            }
        }
        $choicelist .= "</select>\n";
        $output = <<<OUTPUT
        <script type='text/javascript'>
            <!--
            function rankthis_{$this->id}(\$code, \$value)
            {
                \$index=document.addsurvey.CHOICES_{$this->id}.selectedIndex;
                for (i=1; i<={$anscount}; i++)
                {
                    \$b=i;
                    \$b += '';
                    \$inputname="RANK_{$this->id}"+\$b;
                    \$hiddenname="d{$this->fieldname}"+\$b;
                    \$cutname="cut_{$this->id}"+i;
                    document.getElementById(\$cutname).style.display='none';
                    if (!document.getElementById(\$inputname).value)
                        {
                        document.getElementById(\$inputname).value=\$value;
                        document.getElementById(\$hiddenname).value=\$code;
                        document.getElementById(\$cutname).style.display='';
                        for (var b=document.getElementById('CHOICES_{$this->id}').options.length-1; b>=0; b--)
                            {
                            if (document.getElementById('CHOICES_{$this->id}').options[b].value == \$code)
                                {
                                document.getElementById('CHOICES_{$this->id}').options[b] = null;
                            }
                        }
                        i=$anscount;
                    }
                }
                if (document.getElementById('CHOICES_{$this->id}').options.length == 0)
                    {
                    document.getElementById('CHOICES_{$this->id}').disabled=true;
                }
                document.addsurvey.CHOICES_{$this->id}.selectedIndex=-1;
            }
            function deletethis_{$this->id}(\$text, \$value, \$name, \$thisname)
            {
                var qid='{$this->id}';
                var lngth=qid.length+4;
                var cutindex=\$thisname.substring(lngth, \$thisname.length);
                cutindex=parseFloat(cutindex);
                document.getElementById(\$name).value='';
                document.getElementById(\$thisname).style.display='none';
                if (cutindex > 1)
                    {
                    \$cut1name="cut_{$this->id}"+(cutindex-1);
                    \$cut2name="d{$this->fieldname}"+(cutindex);
                    document.getElementById(\$cut1name).style.display='';
                    document.getElementById(\$cut2name).value='';
                }
                else
                    {
                    \$cut2name="d{$this->fieldname}"+(cutindex);
                    document.getElementById(\$cut2name).value='';
                }
                var i=document.getElementById('CHOICES_{$this->id}').options.length;
                document.getElementById('CHOICES_{$this->id}').options[i] = new Option(\$text, \$value);
                if (document.getElementById('CHOICES_{$this->id}').options.length > 0)
                    {
                    document.getElementById('CHOICES_{$this->id}').disabled=false;
                }
            }
            //-->
        </script>
        <table>
            <tr>
                <td align='left' valign='top' width='200'>
                    <strong>{$language->gT("Your choices")}:</strong><br />
                    {$choicelist}
                </td>
                <td align='left'>
                    <strong>{$language->gT("Your ranking")}:</strong><br />
                    {$ranklist}
                </td>
            </tr>
        </table>
        <input type='hidden' name='multi' value='{$anscount}' />
        <input type='hidden' name='lastfield' value='' />
OUTPUT;
        return $output;
    }

    public function getTypeHelp($language)
    {
        $reacount = Answers::model()->getAllRecords(" qid='{$this->id}' AND language='{$language->getlangcode()}'", array('sortorder', 'answer'))->count();
        return $language->gT("Please number each box in order of preference from 1 to")." $reacount";
    }

    public function getPrintAnswers($language)
    {
        $dearesult=Answers::model()->getAllRecords(" qid='{$this->id}' AND language='{$language->getlangcode()}' ", array('sortorder','answer'));

        $output = "\t<ul>\n";
        foreach ($dearesult->readAll() as $dearow)
        {
            $output .= "\t\t<li>\n\t\t\t".printablesurvey::input_type_image('rank','',4,1);
            $output .= "\n\t&nbsp;".$dearow['answer'];
            $output .= Yii::app()->getConfig('showsgqacode') ? " (".$this->surveyid."X".$this->gid."X".$this->id.$dearow['code'].")" : '';
            $output .= "\n\t</li>\n";
        }
        $output .= "\t</ul>\n";
        return $output;
    }

    public function getPrintPDF($language)
    {
        $dearesult=Answers::model()->getAllRecords(" qid='{$this->id}' AND language='{$language->getlangcode()}' ", array('sortorder','answer'));

        $output = array();

        foreach ($dearesult->readAll() as $dearow)
        {
            $output[] = "__ ".$dearow['answer'];
        }

        return $output;
    }

    public function getConditionAnswers()
    {
        $clang = Yii::app()->lang;
        $canswers = array();

        $aresult = Answers::model()->findAllByAttributes(array(
        "qid" => $this->id,
        "scale_id" => 0,
        "language" => Survey::model()->findByPk($this->surveyid)->language,
        ), array('order' => 'sortorder, answer'));

        $acount = count($aresult);
        foreach ($aresult as $arow)
        {
            $theanswer = addcslashes($arow['answer'], "'");
            $quicky[]=array($arow['code'], $theanswer);
        }
        for ($i=1; $i<=$acount; $i++)
        {
            foreach ($quicky as $qck)
            {
                $canswers[]=array($this->surveyid.'X'.$this->gid.'X'.$this->id.$i, $qck[0], $qck[1]);
            }
            // Only Show No-Answer if question is not mandatory
            if ($this->mandatory != 'Y')
            {
                $canswers[]=array($this->surveyid.'X'.$this->gid.'X'.$this->id.$i, " ", $clang->gT("No answer"));
            }
        }
        
        return $canswers;
    }

    public function getConditionQuestions()
    {
        $cquestions = array();

        $aresult = Answers::model()->findAllByAttributes(array(
        "qid" => $this->id,
        "scale_id" => 0,
        "language" => Survey::model()->findByPk($this->surveyid)->language,
        ), array('order' => 'sortorder, answer'));

        $acount = count($aresult);
        for ($i=1; $i<=$acount; $i++)
        {
            $cquestions[]=array("{$this->title}: [RANK $i] ".strip_tags($this->text), $this->id, false, $this->surveyid.'X'.$this->gid.'X'.$this->id.$i);
        }

        return $cquestions;
    }

    public function QueXMLAppendAnswers(&$question)
    {
        global $dom, $quexmllang;
        $response = $dom->createElement("response");
        $response->setAttribute("varName", $this->surveyid . 'X' . $this->gid . 'X' . $this->id);
        quexml_create_subQuestions($question,$this->id,$this->surveyid . 'X' . $this->gid . 'X' . $this->id,true);
        $Query = "SELECT COUNT(*) as sc FROM {{answers}} WHERE qid = {$this->id} AND language='{$quexmllang}' ";
        $QRE = Yii::app()->db->createCommand($Query)->query();
        $QROW = $QRE->read();
        $response->appendChild(QueXMLCreateFree("integer",strlen($QROW['sc']),""));
        $question->appendChild($response);
    }

    public function availableAttributes($attr = false)
    {
        $attrs=array("array_filter","array_filter_exclude","array_filter_style","statistics_showgraph","statistics_graphtype","hide_tip","hidden","max_answers","min_answers","page_break","public_statistics","random_order","showpopups","samechoiceheight","samelistheight", "parent_order","rank_title","choice_title","random_group");
        return $attr?in_array($attr,$attrs):$attrs;
    }

    public function questionProperties($prop = false)
    {
        $clang=Yii::app()->lang;
        $props=array('description' => $clang->gT("Ranking"),'group' => $clang->gT("Mask questions"),'subquestions' => 0,'class' => 'ranking','hasdefaultvalues' => 0,'assessable' => 1,'answerscales' => 1,'enum' => 0);
        return $prop?$props[$prop]:$props;
    }
}
?>