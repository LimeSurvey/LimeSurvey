<?php
class RankingQuestion extends QuestionModule
{
    protected $answers;
    public function getAnswerHTML()
    {
        global $thissurvey, $showpopups;

        // the future string that goes into the answer segment of templates
        $answer = '';

        $clang=Yii::app()->lang;
        $imageurl = Yii::app()->getConfig("imageurl");

        $checkconditionFunction = "checkconditions";

        $aQuestionAttributes = $this->getAttributeValues();
        $answer = '';
        $ansresult = $this->getAnswers();
        $anscount = count($ansresult);
        if (trim($aQuestionAttributes["max_answers"])!='')
        {
            $max_answers=trim($aQuestionAttributes["max_answers"]);
        } else {
            $max_answers=$anscount;
        }
        $finished=$anscount-$max_answers;
        $answer .= "\t<script type='text/javascript'>\n"
        . "\t<!--\n"
        . "function rankthis_{$this->id}(\$code, \$value)\n"
        . "\t{\n"
        . "\t\$index=document.getElementById('CHOICES_{$this->id}').selectedIndex;\n"
        . "\tfor (i=1; i<=$max_answers; i++)\n"
        . "{\n"
        . "\$b=i;\n"
        . "\$b += '';\n"
        . "\$inputname=\"RANK_{$this->id}\"+\$b;\n"
        . "\$hiddenname=\"fvalue_{$this->id}\"+\$b;\n"
        . "\$cutname=\"cut_{$this->id}\"+i;\n"
        . "document.getElementById(\$cutname).style.display='none';\n"
        . "if (!document.getElementById(\$inputname).value)\n"
        . "\t{\n"
        . "\t\t\t\t\t\t\tdocument.getElementById(\$inputname).value=\$value;\n"
        . "\t\t\t\t\t\t\tdocument.getElementById(\$hiddenname).value=\$code;\n"
        . "\t\t\t\t\t\t\tdocument.getElementById(\$cutname).style.display='';\n"
        . "\t\t\t\t\t\t\tfor (var b=document.getElementById('CHOICES_{$this->id}').options.length-1; b>=0; b--)\n"
        . "\t\t\t\t\t\t\t\t{\n"
        . "\t\t\t\t\t\t\t\tif (document.getElementById('CHOICES_{$this->id}').options[b].value == \$code)\n"
        . "\t\t\t\t\t\t\t\t\t{\n"
        . "\t\t\t\t\t\t\t\t\tdocument.getElementById('CHOICES_{$this->id}').options[b] = null;\n"
        . "\t\t\t\t\t\t\t\t\t}\n"
        . "\t\t\t\t\t\t\t\t}\n"
        . "\t\t\t\t\t\t\ti=$max_answers;\n"
        . "\t\t\t\t\t\t\t}\n"
        . "\t\t\t\t\t\t}\n"
        . "\t\t\t\t\tif (document.getElementById('CHOICES_{$this->id}').options.length == $finished)\n"
        . "\t\t\t\t\t\t{\n"
        . "\t\t\t\t\t\tdocument.getElementById('CHOICES_{$this->id}').disabled=true;\n"
        . "\t\t\t\t\t\t}\n"
        . "\t\t\t\t\tdocument.getElementById('CHOICES_{$this->id}').selectedIndex=-1;\n"
        . "\t\t\t\t\t$checkconditionFunction(\$code);\n"
        . "\t\t\t\t\t}\n"
        . "\t\t\t\tfunction deletethis_{$this->id}(\$text, \$value, \$name, \$thisname)\n"
        . "\t\t\t\t\t{\n"
        . "\t\t\t\t\tvar qid='{$this->id}';\n"
        . "\t\t\t\t\tvar lngth=qid.length+4;\n"
        . "\t\t\t\t\tvar cutindex=\$thisname.substring(lngth, \$thisname.length);\n"
        . "\t\t\t\t\tcutindex=parseFloat(cutindex);\n"
        . "\t\t\t\t\tdocument.getElementById(\$name).value='';\n"
        . "\t\t\t\t\tdocument.getElementById(\$thisname).style.display='none';\n"
        . "\t\t\t\t\tif (cutindex > 1)\n"
        . "\t\t\t\t\t\t{\n"
        . "\t\t\t\t\t\t\$cut1name=\"cut_{$this->id}\"+(cutindex-1);\n"
        . "\t\t\t\t\t\t\$cut2name=\"fvalue_{$this->id}\"+(cutindex);\n"
        . "\t\t\t\t\t\tdocument.getElementById(\$cut1name).style.display='';\n"
        . "\t\t\t\t\t\tdocument.getElementById(\$cut2name).value='';\n"
        . "\t\t\t\t\t\t}\n"
        . "\t\t\t\t\telse\n"
        . "\t\t\t\t\t\t{\n"
        . "\t\t\t\t\t\t\$cut2name=\"fvalue_{$this->id}\"+(cutindex);\n"
        . "\t\t\t\t\t\tdocument.getElementById(\$cut2name).value='';\n"
        . "\t\t\t\t\t\t}\n"
        . "\t\t\t\t\tvar i=document.getElementById('CHOICES_{$this->id}').options.length;\n"
        . "\t\t\t\t\tdocument.getElementById('CHOICES_{$this->id}').options[i] = new Option(\$text, \$value);\n"
        . "\t\t\t\t\tif (document.getElementById('CHOICES_{$this->id}').options.length > 0)\n"
        . "\t\t\t\t\t\t{\n"
        . "\t\t\t\t\t\tdocument.getElementById('CHOICES_{$this->id}').disabled=false;\n"
        . "\t\t\t\t\t\t}\n"
        . "\t\t\t\t\t$checkconditionFunction('');\n"
        . "\t\t\t\t\t}\n"
        . "\t\t\t//-->\n"
        . "\t\t\t</script>\n";
        $ranklist = '';

        foreach ($ansresult as $ansrow)
        {
            $answers[] = array($ansrow['code'], $ansrow['answer']);
        }
        $existing=0;
        for ($i=1; $i<=$anscount; $i++)
        {
            $myfname=$this->fieldname.$i;
            if (isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname]) && $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname])
            {
                $existing++;
            }
        }
        for ($i=1; $i<=$max_answers; $i++)
        {
            $myfname = $this->fieldname.$i;
            if (!empty($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname]))
            {
                foreach ($answers as $ans)
                {
                    if ($ans[0] == $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname])
                    {
                        $thiscode = $ans[0];
                        $thistext = $ans[1];
                    }
                }
            }
            $ranklist .= "\t<tr><td class=\"position\">&nbsp;<label for='RANK_{$this->id}$i'>"
            ."$i:&nbsp;</label></td><td class=\"item\"><input class=\"text\" type=\"text\" name=\"RANK_{$this->id}$i\" id=\"RANK_{$this->id}$i\"";
            if (!empty($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname]))
            {
                $ranklist .= " value='";
                $ranklist .= htmlspecialchars($thistext, ENT_QUOTES);
                $ranklist .= "'";
            }
            $ranklist .= " onfocus=\"this.blur()\" />\n";
            $ranklist .= "<input type=\"hidden\" name=\"$myfname\" id=\"fvalue_{$this->id}$i\" value='";
            $chosen[]=""; //create array
            if (!empty($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname]))
            {
                $ranklist .= $thiscode;
                $chosen[]=array($thiscode, $thistext);
            }
            $ranklist .= "' />\n";
            $ranklist .= "<img src=\"$imageurl/cut.gif\" alt=\"".$clang->gT("Remove this item")."\" title=\"".$clang->gT("Remove this item")."\" ";
            if ($i != $existing)
            {
                $ranklist .= "style=\"display:none\"";
            }
            $ranklist .= " id=\"cut_{$this->id}$i\" onclick=\"deletethis_{$this->id}(document.getElementById('RANK_{$this->id}$i').value, document.getElementById('fvalue_{$this->id}$i').value, document.getElementById('RANK_{$this->id}$i').name, this.id)\" /><br />\n";
            $ranklist .= "</td></tr>\n";
        }

        $maxselectlength=0;
        $choicelist = "<select size=\"$anscount\" name=\"CHOICES_{$this->id}\" ";
        if (isset($choicewidth)) {$choicelist.=$choicewidth;}

        $choicelist .= " id=\"CHOICES_{$this->id}\" onchange=\"if (this.options.length>0 && this.selectedIndex<0) { this.options[this.options.length-1].selected=true;}; rankthis_{$this->id}(this.options[this.selectedIndex].value, this.options[this.selectedIndex].text)\" class=\"select\">\n";

        foreach ($answers as $ans)
        {
            if (!in_array($ans, $chosen))
            {
                $choicelist .= "\t\t\t\t\t\t\t<option value='{$ans[0]}'>{$ans[1]}</option>\n";
            }
            if (strlen($ans[1]) > $maxselectlength) {$maxselectlength = strlen($ans[1]);}
        }
        $choicelist .= "</select>\n";

        $answer .= "\t<table border='0' cellspacing='0' class='rank'>\n"
        . "<tr>\n"
        . "\t<td align='left' valign='top' class='rank label'>\n"
        . "<strong>&nbsp;&nbsp;<label for='CHOICES_{$this->id}'>".$clang->gT("Your Choices").":</label></strong><br />\n"
        . "&nbsp;".$choicelist
        . "\t&nbsp;</td>\n";
        $maxselectlength=$maxselectlength+2;
        if ($maxselectlength > 60)
        {
            $maxselectlength=60;
        }
        $ranklist = str_replace("<input class=\"text\"", "<input size='{$maxselectlength}' class='text'", $ranklist);
        $answer .= "\t<td style=\"text-align:left; white-space:nowrap;\" class='rank output'>\n"
        . "\t<table border='0' cellspacing='1' cellpadding='0'>\n"
        . "\t<tr><td></td><td><strong>".$clang->gT("Your Ranking").":</strong></td></tr>\n";

        $answer .= $ranklist
        . "\t</table>\n"
        . "\t</td>\n"
        . "</tr>\n"
        . "<tr>\n"
        . "\t<td colspan='2' class='rank helptext'>\n"
        . "".$clang->gT("Click on the scissors next to each item on the right to remove the last entry in your ranked list")
        . "\t</td>\n"
        . "</tr>\n"
        . "\t</table>\n";

        if (trim($aQuestionAttributes["min_answers"]) != '')
        {
            $minansw=trim($aQuestionAttributes["min_answers"]);
            if(!isset($showpopups) || $showpopups == 0)
            {
                $answer .= "<div id='rankingminanswarning{$this->id}' style='display: none; color: red' class='errormandatory'>"
                .sprintf($clang->ngT("Please rank at least %d item for question \"%s\"","Please rank at least %d items for question \"%s\".",$minansw),$minansw, trim(str_replace(array("\n", "\r"), "", $this->text)))."</div>";
            }
            $minanswscript = "<script type='text/javascript'>\n"
            . "  <!--\n"
            . "  oldonsubmit_{$this->id} = document.limesurvey.onsubmit;\n"
            . "  function ensureminansw_{$this->id}()\n"
            . "  {\n"
            . "     count={$anscount} - document.getElementById('CHOICES_{$this->id}').options.length;\n"
            . "     if (count < {$minansw} && $('#relevance{$this->id}').val()==1){\n";
            if(!isset($showpopups) || $showpopups == 0)
            {
                $minanswscript .= "\n
                document.getElementById('rankingminanswarning{$this->id}').style.display='';\n";
            } else {
                $minanswscript .="
                alert('".sprintf($clang->ngT("Please rank at least %d item for question \"%s\"","Please rank at least %d items for question \"%s\"",$minansw,'js'),$minansw, trim(javascriptEscape(str_replace(array("\n", "\r"), "",$this->text),true,true)))."');\n";
            }
            $minanswscript .= ""
            . "     return false;\n"
            . "   } else {\n"
            . "     if (oldonsubmit_{$this->id}){\n"
            . "         return oldonsubmit_{$this->id}();\n"
            . "     }\n"
            . "     return true;\n"
            . "     }\n"
            . "  }\n"
            . "  document.limesurvey.onsubmit = ensureminansw_{$this->id}\n"
            . "  -->\n"
            . "  </script>\n";
            $answer = $minanswscript . $answer;
        }

        return $answer;
    }
    
    public function getInputNames()
    {
        $aQuestionAttributes = $this->getAttributeValues();
        $ansresult = $this->getAnswers();
        $anscount = count($ansresult);
        if (trim($aQuestionAttributes["max_answers"])!='')
        {
            $max_answers=trim($aQuestionAttributes["max_answers"]);
        } else {
            $max_answers=$anscount;
        }
        
        for ($i=1; $i<=$max_answers; $i++)
        {
            $names[] = $this->fieldname.$i;
        }
        
        return $names;
    }
    
    protected function getAnswers()
    {
        if ($this->answers) return $this->answers;
        $aQuestionAttributes = $this->getAttributeValues();
        if ($aQuestionAttributes['random_order']==1) {
            $ansquery = "SELECT * FROM {{answers}} WHERE qid=$this->id AND language='".$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']."' and scale_id=0 ORDER BY ".dbRandom();
        } else {
            $ansquery = "SELECT * FROM {{answers}} WHERE qid=$this->id AND language='".$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']."' and scale_id=0 ORDER BY sortorder, answer";
        }
        return $this->children = dbExecuteAssoc($ansquery)->readAll();  //Checked
    }
    
    public function getTitle()
    {
        $clang=Yii::app()->lang;
        $aQuestionAttributes = $this->getAttributeValues();
        if (count($this->getInputNames()) > 1 && $aQuestionAttributes['hide_tip']==0 && trim($aQuestionAttributes['min_answers'])!='')
        {
           return $this->text."<br />\n<span class=\"questionhelp\">".sprintf($clang->ngT("Check at least %d item.","Check at least %d items.",$aQuestionAttributes['min_answers']),$aQuestionAttributes['min_answers'])."</span>";
        }
        return $this->text;
    }
    
    public function getHelp()
    {
        $clang=Yii::app()->lang;
        $aQuestionAttributes = $this->getAttributeValues();
        $help = '';
        if (count($this->getInputNames()) > 1 && $aQuestionAttributes['hide_tip']==0)
        {
            $help = $clang->gT("Click on an item in the list on the left, starting with your highest ranking item, moving through to your lowest ranking item.");
            if (trim($aQuestionAttributes['min_answers'])!='')
            {
                $help .=' '.sprintf($clang->ngT("Check at least %d item.","Check at least %d items.",$aQuestionAttributes['min_answers']),$aQuestionAttributes['min_answers']);
            }
        }
        return $help;
    }
    
    public function availableAttributes()
    {
        return array("statistics_showgraph","statistics_graphtype","hide_tip","hidden","max_answers","min_answers","page_break","public_statistics","random_order","parent_order","random_group");
    }
}
?>