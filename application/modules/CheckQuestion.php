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
            $oth_checkconditionFunction = "fixnum_checkconditions";
        }
        else
        {
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

        if ($this->isother == 'Y')
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
            $answer .= '        <input class="checkbox" type="checkbox" name="'.$this->fieldname.$ansrow['title'].'" id="answer'.$this->fieldname.$ansrow['title'].'" value="Y"';

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
            $answer .= '        <input type="hidden" name="java'.$myfname.'" id="java'.$myfname.'" value="';
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

        if ($this->isother == 'Y')
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
            <input class="checkbox other-checkbox" style="visibility:hidden" type="checkbox" name="'.$myfname.'cbox" alt="'.$clang->gT('Other').'" id="answer'.$myfname.'cbox"';
            // othercbox can be not display, because only input text goes to database

            if (isset($_SESSION['survey_'.$this->surveyid][$myfname]) && trim($_SESSION['survey_'.$this->surveyid][$myfname])!='')
            {
                $answer .= CHECKED;
            }
            $answer .= " />
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
            $answer .="\" />";
            $answer .="<script type='text/javascript'>\n";
            $answer .="$('#answer{$myfname}cbox').css('visibility','');";
            $answer .="$('#answer{$myfname}').bind('keyup focusout',function(event){\n";
            $answer .= " if ($.trim($(this).val()).length>0) { $(\"#answer{$myfname}cbox\").attr(\"checked\",true); } else { \$(\"#answer{$myfname}cbox\").attr(\"checked\",false); }; $(\"#java{$myfname}\").val($(this).val());LEMflagMandOther(\"$myfname\",$('#answer{$myfname}cbox').is(\":checked\")); $oth_checkconditionFunction(this.value, this.name, this.type); \n";
            $answer .="});\n";
            $answer .="$('#answer{$myfname}cbox').click(function(event){\n";
            $answer .= " //if (($(this)).is(':checked')) { $(\"#answer{$myfname}\").focus(); } else { $(\"#answer{$myfname}\").val('');{$checkconditionFunction}(\"\", \"{$myfname}\", \"text\"); }; return true;\n";
            $answer .= " if (($(this)).is(':checked') && $.trim($(\"#answer{$myfname}\").val()).length==0) { $(\"#answer{$myfname}\").focus();LEMflagMandOther(\"$myfname\",true);return false; } else {  $(\"#answer{$myfname}\").val('');{$checkconditionFunction}(\"\", \"{$myfname}\", \"text\");LEMflagMandOther(\"$myfname\",false); return true; }; \n";
            $answer .="});\n";
            $answer .="</script>\n";
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

        $answer .= $postrow;
        return $answer;
    }

    public function getDataEntry($idrow, &$fnames, $language)
    {
        $q = $this;
        $output="";
        while ($q->id == $this->id)
        {
            if (substr($q->fieldname, -5) == "other")
            {
                $output .= "\t<input type='text' name='{$q->fieldname}' value='"
                .htmlspecialchars($idrow[$q->fieldname], ENT_QUOTES) . "' />\n";
            }
            else
            {
                $output .= "\t<input type='checkbox' class='checkboxbtn' name='{$q->fieldname}' value='Y'";
                if ($idrow[$q->fieldname] == "Y") {$output .= " checked";}
                $output .= " />{$q->sq}<br />\n";
            }

            if(!$q=next($fnames)) break;
            //$q=$fnames['q'];
        }
        prev($fnames);
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

    public function createFieldmap()
    {
        $clang = Yii::app()->lang;
        $map = array();
        $abrows = getSubQuestions($this);
        foreach ($abrows as $abrow)
        {
            $fieldname="{$this->surveyid}X{$this->gid}X{$this->id}{$abrow['title']}";
            $q = clone $this;
            if(isset($this->defaults) && isset($this->defaults[$abrow['qid']])) $q->default=$this->defaults[$abrow['qid']];
            else
            {
                unset($q->default);
            }
            $q->fieldname = $fieldname;
            $q->aid=$abrow['title'];
            $q->sq=$abrow['question'];
            $q->sqid=$abrow['qid'];
            $q->preg=$this->haspreg;
            $map[$fieldname]=$q;
        }
        if ($this->isother=='Y')
        {
            $q = clone $this;
            if (isset($this->defaults) && isset($this->defaults['other'])) $q->default=$this->defaults['other'];
            else
            {
                unset($q->default);
            }
            $q->fieldname .= 'other';
            $q->aid = 'other';
            $q->sq = $clang->gT("Other");
            $q->other = $this->isother;
            $map[$q->fieldname]=$q;
        }

        return $map;
    }

    public function statisticsFieldmap()
    {
        return (strpos('other', $this->fieldname) === false) && (strpos('comment', $this->fieldname) === false);
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

    public function setAssessment()
    {
        if (isset($_SESSION['survey_'.$this->surveyid][$this->fieldname]) && $_SESSION['survey_'.$this->surveyid][$this->fieldname] == "Y")
        {
            $aAttributes=$this->getAttributeValues();
            $this->assessment_value=(int)$aAttributes['assessment_value'];
        } else {
            $this->assessment_value = 0;
        }
        return true;
    }

    public function getDBField()
    {
        if ($this->aid != 'other' && strpos($this->aid,'comment')===false && strpos($this->aid,'othercomment')===false)
        {
            return "VARCHAR(5)";
        }
        else
        {
            return "text";
        }
    }

    public function prepareConditions($row)
    {
        if (preg_match("/^\+(.*)$/",$row['cfieldname'],$cfieldnamematch))
        { // this condition uses a single checkbox as source
            return array("cfieldname"=>$cfieldnamematch[1],
            "value"=>$row['value'],
            "matchfield"=>$row['cfieldname'],
            "matchvalue"=>$row['value'],
            "matchmethod"=>$row['method'],
            "subqid"=>$cfieldnamematch[1].'NAOK'
            );
        }

        return array("cfieldname"=>$rows['cfieldname'].$rows['value'],
        "value"=>$row['value'],
        "matchfield"=>$row['cfieldname'],
        "matchvalue"=>"Y",
        "matchmethod"=>$row['method'],
        "subqid"=>$row['cfieldname']
        );
    }

    public function transformResponseValue($export, $value, $options)
    {
        if ($value == 'N' && $options->convertN)
        {
            //echo "Transforming 'N' to ".$options->nValue.PHP_EOL;
            return $options->nValue;
        }
        else if ($value == 'Y' && $options->convertY)
        {
            //echo "Transforming 'Y' to ".$options->yValue.PHP_EOL;
            return $options->yValue;
        }
        return parent::transformResponseValue($export, $value, $options);
    }

    public function getFullAnswer($answerCode, $export, $survey)
    {
        if (mb_substr($this->fieldname, -5, 5) == 'other' || mb_substr($this->fieldname, -7, 7) == 'comment')
        {
            //echo "\n -- Branch 1 --";
            return $answerCode;
        }
        else
        {
            switch ($answerCode)
            {
                case 'Y':
                    return $export->translator->translate('Yes', $export->languageCode);
                case 'N':
                case '':
                    return $export->translator->translate('No', $export->languageCode);
                default:
                    //echo "\n -- Branch 2 --";
                    return $answerCode;
            }
        }
    }

    public function getFieldSubHeading($survey, $export, $code)
    {
        //This section creates differing output from the old code base, but I do think
        //that it is more correct than the old code.
        $isOther = ($this->aid == 'other');
        $isComment = (mb_substr($this->aid, -7, 7) == 'comment');

        if ($isComment)
        {
            $isOther = (mb_substr($this->aid, 0, -7) == 'other');
        }

        if ($isOther)
        {
            return ' '.$export->getOtherSubHeading();
        }
        else if (!$code)
        {
            $sqs = $survey->getSubQuestionArrays($this->id);
            foreach ($sqs as $sq)
            {
                if (!$isComment && $sq['title'] == $this->aid)
                {
                    $value = $sq['question'];
                }
            }
            if (!empty($value))
            {
                return ' ['.$value.']';
            }
        }
        elseif (!$isComment)
        {
            return ' ['.$this->aid.']';
        }
        else
        {
            return ' '.$export->getCommentSubHeading();
        }
    }

    public function getSPSSAnswers()
    {
        if ($this->aid == 'other' || strpos($this->aid,'comment') !== false) return array();
        $answers[] = array('code'=>1, 'value'=>$clang->gT('Yes'));
        $answers[] = array('code'=>0, 'value'=>$clang->gT('Not Selected'));
        return $answers;
    }

    public function getSPSSData($data, $iLength, $na, $qs)
    {
        if ($this->aid == 'other' || strpos($this->aid,'comment') !== false)
        {
            return parent::getSPSSData($data, $iLength, $na);
        } else if ($data == 'Y'){
            return $sq . "'1'" . $sq;
        } else {
            return $sq . "'0'" . $sq;
        }
    }

    public function jsVarNameOn()
    {
        return 'java'.$this->fieldname;
    }

    public function onlyNumeric()
    {
        $attributes = $this->getAttributeValues();
        return array_key_exists('other_numbers_only', $attributes) && $attributes['other_numbers_only'] == 1 && preg_match('/other$/',$this->fieldname);
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

    public function compareField($sgqa, $sq)
    {
        return $sgqa == $sq['rowdivid'] || $sgqa == ($sq['rowdivid'] . 'comment');
    }

    public function includeRelevanceStatus()
    {
        return true;
    }

    public function getVarAttributeLEM($sgqa,$value)
    {
        if (preg_match('/other$/',$sgqa))
        {
            return htmlspecialchars(parent::getVarAttributeLEM($sgqa,$value),ENT_NOQUOTES);
        }
        else
        {
            return parent::getVarAttributeLEM($sgqa,$value);
        }
    }

    public function getVarAttributeShown($name, $default, $gseq, $qseq, $ansArray)
    {
        $code = parent::getVarAttributeShown($name,'code',$default,$gseq,$qseq);
        if ($code == 'Y' && isset($this->sq))
        {
            return $this->sq;
        }
        else
        {
            return $default;
        }
    }

    public function getMandatoryTip()
    {
        if ($this->isother == 'Y')
        {
            $clang=Yii::app()->lang;
            $attributes = $this->getAttributeValues();
            if (trim($attributes['other_replace_text'][$_SESSION['survey_'.$this->surveyid]['s_lang']]) != '') {
                $othertext = trim($qattr['other_replace_text'][$_SESSION['survey_'.$this->surveyid]['s_lang']]);
            }
            else {
                $othertext = $clang->gT('Other:');
            }
            return "<br />\n".sprintf($clang->gT("If you choose '%s' you must provide a description."), $othertext);
        }
        else
        {
            return '';
        }
    }

    public function getQuotaAnswers($iQuotaId)
    {
        $aAnswerList = array();

        $aResults = Questions::model()->findAllByAttributes(array('parent_qid' => $this->id));
        foreach($aResults as $aDbAnsList)
        {
            $tmparrayans = array('Title' => $this->title, 'Display' => substr($aDbAnsList['question'], 0, 40), 'code' => $aDbAnsList['title']);
            $aAnswerList[$aDbAnsList['title']] = $tmparrayans;
        }

        $aResults = Quota_members::model()->findAllByAttributes(array('sid' => $this->surveyid, 'qid' => $this->id, 'quota_id' => $iQuotaId));
        foreach($aResults as $aQuotaList)
        {
            $aAnswerList[$aQuotaList['code']]['rowexists'] = '1';
        }

        return $aAnswerList;
    }

    public function anyUnanswered($relevantSQs, $unansweredSQs)
    {
        return count($relevantSQs) > 0 && (count($relevantSQs) == count($unansweredSQs));
    }

    public function availableOptions()
    {
        return array('other' => true, 'valid' => false, 'mandatory' => true);
    }

    public function getShownJS()
    {
        return 'if (typeof attr.question === "undefined" || value == "") return "";'
                . 'return htmlspecialchars_decode(attr.question);';
    }

    public function getDataEntryView($language)
    {
        $qidattributes = $this->getAttributeValues();
        if (trim($qidattributes['display_columns'])!='')
        {
            $dcols=$qidattributes['display_columns'];
        }
        else
        {
            $dcols=0;
        }
        $meaquery = "SELECT title, question FROM {{questions}} WHERE parent_qid={$this->id} AND language='{$language->getlangcode()}' ORDER BY question_order";
        $mearesult = dbExecuteAssoc($meaquery);
        $meacount = $mearesult->getRowCount();
        $fieldname="{$this->surveyid}X{$this->gid}X{$this->id}";// $this->fieldname give the last sq fieldname in dataentry
        $output = '';
        if ($this->isother == "Y") $meacount++;
        if ($dcols > 0 && $meacount >= $dcols)
        {
            $width=sprintf("%0d", 100/$dcols);
            $maxrows=ceil(100*($meacount/$dcols)/100); //Always rounds up to nearest whole number
            $divider=" </td> <td valign='top' width='{$width}%' nowrap='nowrap'>";
            $upto=0;
            $output .= "<table class='question'><tr> <td valign='top' width='{$width}%' nowrap='nowrap'>";
            foreach ($mearesult as $mearow)
            {
                if ($upto == $maxrows)
                {
                    echo $divider;
                    $upto=0;
                }
                $output .= "<input type='checkbox' class='checkboxbtn' name='{$fieldname}{$mearow['title']}' id='answer{$fieldname}{$mearow['title']}' value='Y' />";
                $output .= "<label for='answer{$fieldname}{$mearow['title']}'>{$mearow['question']}</label><br />";
                $upto++;
            }
            if ($this->isother == "Y")
            {
                $output .= $language->gT("Other") . "<input type='text' name='{$fieldname}other' />";
            }
            $output .= "</td></tr></table>";
        }
        else
        {
            foreach ($mearesult as $mearow)
            {
                $output .= "<input type='checkbox' class='checkboxbtn' name='{$this->fieldname}{$mearow['code']}' id='answer{$this->fieldname}{$mearow['code']}' value='Y'";
                if ($mearow['default_value'] == "Y") $output .= "checked";
                $output .= "/><label for='{$this->fieldname}{$mearow['code']}'>{$mearow['answer']}</label><br />";
            }
            if ($this->isother == "Y")
            {
                $output .= $language->gT("Other") . "<input type='text' name='{$this->fieldname}other' />";
            }
        }
        return $output;
    }

    public function getTypeHelp($language)
    {
        return $language->gT('Please choose *all* that apply:');
    }

    public function getPrintAnswers($language)
    {
        $qidattributes = $this->getAttributeValues();
        if (trim($qidattributes['display_columns'])!='')
        {
            $dcols=$qidattributes['display_columns'];
        }
        else
        {
            $dcols=0;
        }

        $mearesult=Questions::model()->getAllRecords(" parent_qid='{$this->id}' AND language='{$language->getlangcode()}' ", array('question_order'));
        $meacount = $mearesult->getRowCount();
        if ($this->isother == 'Y') {$meacount++;}

        $wrapper = setupColumns($dcols, $meacount);
        $output = $wrapper['whole-start'];

        $rowcounter = 0;
        $colcounter = 1;

        foreach ($mearesult->readAll() as $mearow)
        {
            $output .= $wrapper['item-start'].printablesurvey::input_type_image('checkbox',$mearow['question'])."\n\t\t";
            $output .= $mearow['question']. (Yii::app()->getConfig('showsgqacode') ? " (".$this->surveyid .'X' . $this->gid . 'X' . $this->id .$mearow['title'].") " : '') . $wrapper['item-end'];

            ++$rowcounter;
            if ($rowcounter == $wrapper['maxrows'] && $colcounter < $wrapper['cols'])
            {
                if($colcounter == $wrapper['cols'] - 1)
                {
                    $output .= $wrapper['col-devide-last'];
                }
                else
                {
                    $output.= $wrapper['col-devide'];
                }
                $rowcounter = 0;
                ++$colcounter;
            }
        }
        if ($this->isother == 'Y')
        {
            if (trim($qidattributes['other_replace_text'][$language->getlangcode()])=='')
            {
                $qidattributes["other_replace_text"][$language->getlangcode()]="Other";
            }
            if(!isset($mearow['answer'])) $mearow['answer']="";
            $output.= $wrapper['item-start-other'].printablesurvey::input_type_image('checkbox',$mearow['answer']);
            $output .= $language->gT($qidattributes["other_replace_text"][$language->getlangcode()]).":\n\t\t";
            $output .= printablesurvey::input_type_image('other'). (Yii::app()->getConfig('showsgqacode') ? " (".$fieldname."other) ": '').$wrapper['item-end'];
        }
        $output .= $wrapper['whole-end'];
        return $output;
    }

    public function getPrintPDF($language)
    {
        $qidattributes = $this->getAttributeValues();
        $output = array();

        $mearesult=Questions::model()->getAllRecords(" parent_qid='{$this->id}' AND language='{$language->getlangcode()}' ", array('question_order'));

        foreach ($mearesult->readAll() as $mearow)
        {
            $output[] = " o ".$mearow['question'];

        }
        if ($this->isother)
        {
            if (trim($qidattributes['other_replace_text'][$language->getlangcode()])=='')
            {
                $qidattributes["other_replace_text"][$language->getlangcode()]="Other";
            }
            $output[] = " o ".$language->gT($qidattributes["other_replace_text"][$language->getlangcode()]).": ________";
        }
        return $output;
    }

    public function getConditionAnswers()
    {
        $clang = Yii::app()->lang;
        $canswers = array();

        $aresult = Questions::model()->findAllByAttributes(array(
        "parent_qid" => $this->id,
        "language" => Survey::model()->findByPk($this->surveyid)->language,
        ), array('order' => 'question_order desc'));

        foreach ($aresult as $arows)
        {
            $theanswer = addcslashes($arows['question'], "'");
            $canswers[]=array($this->surveyid.'X'.$this->gid.'X'.$this->id, $arows['title'], $theanswer);

            $canswers[]=array("+".$this->surveyid.'X'.$this->gid.'X'.$this->id.$arows['title'], 'Y', $clang->gT("checked"));
            $canswers[]=array("+".$this->surveyid.'X'.$this->gid.'X'.$this->id.$arows['title'], '', $clang->gT("not checked"));
        }

        return $canswers;
    }

    public function getConditionQuestions()
    {
        $clang = Yii::app()->lang;
        $cquestions = array();

        $shortanswer = " [".$clang->gT("Group of checkboxes")."]";
        $shortquestion = $this->title.":$shortanswer ".strip_tags($this->text);
        $cquestions[] = array($shortquestion, $this->id, true, $this->surveyid.'X'.$this->gid.'X'.$this->id);

        $aresult = Questions::model()->findAllByAttributes(array(
        "parent_qid" => $this->id,
        "language" => Survey::model()->findByPk($this->surveyid)->language,
        ), array('order' => 'question_order desc'));

        foreach ($aresult as $arows)
        {
            $shortanswer = "{$arows['title']}: [" . strip_tags($arows['question']) . "]";
            $shortanswer .= "[".$clang->gT("Single checkbox")."]";
            $shortquestion=$this->title.":$shortanswer ".strip_tags($this->text);
            $cquestions[]=array($shortquestion, $this->id, true, "+".$this->surveyid.'X'.$this->gid.'X'.$this->id.$arows['title']);
        }

        return $cquestions;
    }

    public function QueXMLAppendAnswers(&$question)
    {
        quexml_create_multi($question,$this->id,$this->surveyid.'X'.$this->gid.'X'.$this->id,false,false,$this->isother == 'Y');
    }

    public function availableAttributes($attr = false)
    {
        $attrs=array("array_filter","array_filter_exclude","array_filter_style","assessment_value","display_columns","em_validation_q","em_validation_q_tip","exclude_all_others","exclude_all_others_auto","statistics_showgraph","hide_tip","hidden","max_answers","min_answers","other_numbers_only","other_replace_text","page_break","public_statistics","random_order","parent_order","scale_export","random_group");
        return $attr?in_array($attr,$attrs):$attrs;
    }

    public function questionProperties($prop = false)
    {
        $clang=Yii::app()->lang;
        $props=array('description' => $clang->gT("Multiple choice"),'group' => $clang->gT("Multiple choice questions"),'subquestions' => 1,'class' => 'multiple-opt','hasdefaultvalues' => 1,'assessable' => 1,'answerscales' => 0,'enum' => 1);
        return $prop?$props[$prop]:$props;
    }
}
?>
