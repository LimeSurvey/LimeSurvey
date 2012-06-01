<?php
class DualRadioArrayQuestion extends RadioArrayQuestion
{
    public function getAnswerHTML()
    {
        global $thissurvey;
        global $notanswered;
        $repeatheadings = Yii::app()->getConfig("repeatheadings");
        $minrepeatheadings = Yii::app()->getConfig("minrepeatheadings");
        $extraclass ="";
        $answertypeclass = ""; // Maybe not
        $clang = Yii::app()->lang;

        $checkconditionFunction = "checkconditions";

        $labelans1=array();
        $labelans=array();
        $qquery = "SELECT other FROM {{questions}} WHERE qid=".$this->id." AND language='".$_SESSION['survey_'.$this->surveyid]['s_lang']."'";
        $other = reset(dbExecuteAssoc($qquery)->read());    //Checked
        $lquery =  "SELECT * FROM {{answers}} WHERE scale_id=0 AND qid={$this->id} AND language='".$_SESSION['survey_'.$this->surveyid]['s_lang']."' ORDER BY sortorder, code";
        $lquery1 = "SELECT * FROM {{answers}} WHERE scale_id=1 AND qid={$this->id} AND language='".$_SESSION['survey_'.$this->surveyid]['s_lang']."' ORDER BY sortorder, code";
        $aQuestionAttributes = $this->getAttributeValues();

        if ($aQuestionAttributes['use_dropdown']==1)
        {
            $useDropdownLayout = true;
            $extraclass .=" dropdown-list";
            $answertypeclass .=" dropdown";
        }
        else
        {
            $useDropdownLayout = false;
            $extraclass .=" radio-list";
            $answertypeclass .=" radio";
        }

        if (trim($aQuestionAttributes['dualscale_headerA'][$_SESSION['survey_'.$this->surveyid]['s_lang']])!='') {
            $leftheader= $clang->gT($aQuestionAttributes['dualscale_headerA'][$_SESSION['survey_'.$this->surveyid]['s_lang']]);
        }
        else
        {
            $leftheader ='';
        }

        if (trim($aQuestionAttributes['dualscale_headerB'][$_SESSION['survey_'.$this->surveyid]['s_lang']])!='')
        {
            $rightheader= $clang->gT($aQuestionAttributes['dualscale_headerB'][$_SESSION['survey_'.$this->surveyid]['s_lang']]);
        }
        else
        {
            $rightheader ='';
        }

        $lresult = dbExecuteAssoc($lquery); //Checked
        if ($useDropdownLayout === false && $lresult->count() > 0)
        {
            if (trim($aQuestionAttributes['answer_width'])!='')
            {
                $answerwidth=$aQuestionAttributes['answer_width'];
            }
            else
            {
                $answerwidth=20;
            }
            $columnswidth = 100 - $answerwidth;

            foreach ($lresult->readAll() as $lrow)
            {
                $labelans[]=$lrow['answer'];
                $labelcode[]=$lrow['code'];
            }
            $lresult1 = dbExecuteAssoc($lquery1); //Checked
            if ($lresult1->count() > 0)
            {
                foreach ($lresult1->readAll() as $lrow1)
                {
                    $labelans1[]=$lrow1['answer'];
                    $labelcode1[]=$lrow1['code'];
                }
            }
            $numrows=count($labelans) + count($labelans1);
            if ($this->mandatory != "Y" && SHOW_NO_ANSWER == 1) {$numrows++;}
            $cellwidth=$columnswidth/$numrows;

            $cellwidth=sprintf("%02d", $cellwidth);

            $ansquery = "SELECT question FROM {{questions}} WHERE parent_qid=".$this->id." and scale_id=0 AND question like '%|%'";
            $ansresult = dbExecuteAssoc($ansquery);   //Checked
            if ($ansresult->count()>0)
            {
                $right_exists=true;
            }
            else
            {
                $right_exists=false;
            }
            // $right_exists is a flag to find out if there are any right hand answer parts. If there arent we can leave out the right td column
            if ($aQuestionAttributes['random_order']==1) {
                $ansquery = "SELECT * FROM {{questions}} WHERE parent_qid=$this->id AND language='".$_SESSION['survey_'.$this->surveyid]['s_lang']."' and scale_id=0 ORDER BY ".dbRandom();
            }
            else
            {
                $ansquery = "SELECT * FROM {{questions}} WHERE parent_qid=$this->id AND language='".$_SESSION['survey_'.$this->surveyid]['s_lang']."' and scale_id=0 ORDER BY question_order";
            }
            $ansresult = dbExecuteAssoc($ansquery);   //Checked
            $anscount = $ansresult->count();
            $fn=1;
            // unselect second scale when using "no answer"
            $answer = "<script type='text/javascript'>\n"
            . "<!--\n"
            . "function noanswer_checkconditions(value, name, type)\n"
            . "{\n"
            . "\tvar vname;\n"
            . "\tvname = name.replace(/#.*$/,\"\");\n"
            . "\t$('input[name^=\"' + vname + '\"]').attr('checked',false);\n"
            . "\t$('input[id=\"answer' + vname + '#0-\"]').attr('checked',true);\n"
            . "\t$('input[name^=\"java' + vname + '\"]').val('');\n"
            . "\t$checkconditionFunction(value, name, type);\n"
            . "}\n"
            . "function secondlabel_checkconditions(value, name, type)\n"
            . "{\n"
            . "\tvar vname;\n"
            . "\tvname = \"answer\"+name.replace(/#1/g,\"#0-\");\n"
            . "\tif(document.getElementById(vname))\n"
            . "\t{\n"
            . "\tdocument.getElementById(vname).checked=false;\n"
            . "\t}\n"
            . "\t$checkconditionFunction(value, name, type);\n"
            . "}\n"
            . " //-->\n"
            . " </script>\n";

            // Header row and colgroups
            $mycolumns = "\t<colgroup class=\"col-responses group-1\">\n"
            ."\t<col class=\"col-answers\" width=\"$answerwidth%\" />\n";

            $myheader2 = "\n<tr class=\"array1 header_row\">\n"
            . "\t<th class=\"header_answer_text\">&nbsp;</th>\n\n";
            $odd_even = '';
            foreach ($labelans as $ld)
            {
                $myheader2 .= "\t<th>".$ld."</th>\n";
                $odd_even = alternation($odd_even);
                $mycolumns .= "<col class=\"$odd_even\" width=\"$cellwidth%\" />\n";
            }
            $mycolumns .= "\t</colgroup>\n";

            if (count($labelans1)>0) // if second label set is used
            {
                $mycolumns .= "\t<colgroup class=\"col-responses group-2\">\n"
                . "\t<col class=\"seperator\" />\n";
                $myheader2 .= "\n\t<td class=\"header_separator\">&nbsp;</td>\n\n"; // Separator
                foreach ($labelans1 as $ld)
                {
                    $myheader2 .= "\t<th>".$ld."</th>\n";
                    $odd_even = alternation($odd_even);
                    $mycolumns .= "<col class=\"$odd_even\" width=\"$cellwidth%\" />\n";
                }

            }
            if ($right_exists)
            {
                $myheader2 .= "\t<td class=\"header_answer_text_right\">&nbsp;</td>\n";
                $mycolumns .= "\n\t<col class=\"answertextright\" />\n\n";
            }
            if ($this->mandatory != 'Y' && SHOW_NO_ANSWER == 1) //Question is not mandatory and we can show "no answer"
            {
                $myheader2 .= "\t<td class=\"header_separator\">&nbsp;</td>\n"; // Separator
                $myheader2 .= "\t<th class=\"header_no_answer\">".$clang->gT('No answer')."</th>\n";
                $odd_even = alternation($odd_even);
                $mycolumns .= "\n\t<col class=\"seperator\" />\n\n";
                $mycolumns .= "\t<col class=\"col-no-answer $odd_even\" width=\"$cellwidth%\" />\n";
            }

            $mycolumns .= "\t</colgroup>\n";
            $myheader2 .= "</tr>\n";

            // build first row of header if needed
            if ($leftheader != '' || $rightheader !='')
            {
                $myheader1 = "<tr class=\"array1 groups header_row\">\n"
                . "\t<th class=\"header_answer_text\">&nbsp;</th>\n"
                . "\t<th colspan=\"".count($labelans)."\" class=\"dsheader\">$leftheader</th>\n";

                if (count($labelans1)>0)
                {
                    $myheader1 .= "\t<td class=\"header_separator\">&nbsp;</td>\n" // Separator
                    ."\t<th colspan=\"".count($labelans1)."\" class=\"dsheader\">$rightheader</th>\n";
                }
                if ($right_exists)
                {
                    $myheader1 .= "\t<td class=\"header_answer_text_right\">&nbsp;</td>\n";
                }
                if ($this->mandatory != 'Y' && SHOW_NO_ANSWER == 1)
                {
                    $myheader1 .= "\t<td class=\"header_separator\">&nbsp;</td>\n"; // Separator
                    $myheader1 .= "\t<th class=\"header_no_answer\">&nbsp;</th>\n";
                }
                $myheader1 .= "</tr>\n";
            }
            else
            {
                $myheader1 = '';
            }

            $answer .= "\n<table class=\"question subquestions-list questions-list\" summary=\"".str_replace('"','' ,strip_tags($this->text))." - a dual array type question\">\n"
            . $mycolumns
            . "\n\t<thead>\n"
            . $myheader1
            . $myheader2
            . "\n\t</thead>\n"
            . "<tbody>\n";

            $trbc = '';
            foreach ($ansresult->readAll() as $ansrow)
            {
                // Build repeat headings if needed
                if (isset($repeatheadings) && $repeatheadings > 0 && ($fn-1) > 0 && ($fn-1) % $repeatheadings == 0)
                {
                    if ( ($anscount - $fn + 1) >= $minrepeatheadings )
                    {
                        $answer .= "</tbody>\n<tbody>";// Close actual body and open another one
                        $answer .= "\n<tr  class=\"repeat headings\">\n"
                        . "\t<th class=\"header_answer_text\">&nbsp;</th>\n";
                        foreach ($labelans as $ld)
                        {
                            $answer .= "\t<th>".$ld."</th>\n";
                        }
                        if (count($labelans1)>0) // if second label set is used
                        {
                            $answer .= "<th class=\"header_separator\">&nbsp;</th>\n"; // Separator
                            foreach ($labelans1 as $ld)
                            {
                                $answer .= "\t<th>".$ld."</th>\n";
                            }
                        }
                        if ($right_exists)
                        {
                            $answer .= "\t<td class=\"header_answer_text_right\">&nbsp;</td>\n";
                        }
                        if ($this->mandatory != 'Y' && SHOW_NO_ANSWER == 1) //Question is not mandatory and we can show "no answer"
                        {
                            $answer .= "\t<td class=\"header_separator\">&nbsp;</td>\n"; // Separator
                            $answer .= "\t<th class=\"header_no_answer\">".$clang->gT('No answer')."</th>\n";
                        }
                        $answer .= "</tr>\n";
                    }
                }

                $trbc = alternation($trbc , 'row');
                $answertext=dTexts__run($ansrow['question']);
                $answertextsave=$answertext;

                $dualgroup=0;
                $myfname0= $this->fieldname.$ansrow['title'];
                $myfname = $this->fieldname.$ansrow['title'].'#0';
                $myfname1 = $this->fieldname.$ansrow['title'].'#1'; // new multi-scale-answer
                /* Check if this item has not been answered: the 'notanswered' variable must be an array,
                containing a list of unanswered questions, the current question must be in the array,
                and there must be no answer available for the item in this session. */
                if ($this->mandatory=='Y' && (is_array($notanswered)) && ((array_search($myfname, $notanswered) !== FALSE) || (array_search($myfname1, $notanswered) !== FALSE)) && (($_SESSION['survey_'.$this->surveyid][$myfname] == '') || ($_SESSION['survey_'.$this->surveyid][$myfname1] == '')) )
                {
                    $answertext = "<span class='errormandatory'>{$answertext}</span>";
                }

                // Get array_filter stuff
                list($htmltbody2, $hiddenfield)=return_array_filter_strings($this, $aQuestionAttributes, $thissurvey, $ansrow, $myfname0, $trbc, $myfname,"tr","$trbc answers-list radio-list");

                $answer .= $htmltbody2;

                if (strpos($answertext,'|')) {$answertext=substr($answertext,0, strpos($answertext,'|'));}

                $answer .= "\t<th class=\"answertext\">\n"
                . $hiddenfield
                . "$answertext\n"
                . "<input type=\"hidden\" name=\"java$myfname\" id=\"java$myfname\" value=\"";
                if (isset($_SESSION['survey_'.$this->surveyid][$myfname])) {$answer .= $_SESSION['survey_'.$this->surveyid][$myfname];}
                $answer .= "\" />\n\t</th>\n";
                $hiddenanswers='';
                $thiskey=0;

                foreach ($labelcode as $ld)
                {
                    $answer .= "\t<td class=\"answer_cell_1_00$ld answer-item {$answertypeclass}-item\">\n"
                    . "<label for=\"answer$myfname-$ld\">\n"
                    . "\t<input class=\"radio\" type=\"radio\" name=\"$myfname\" value=\"$ld\" id=\"answer$myfname-$ld\" title=\""
                    . HTMLEscape(strip_tags($labelans[$thiskey])).'"';
                    if (isset($_SESSION['survey_'.$this->surveyid][$myfname]) && $_SESSION['survey_'.$this->surveyid][$myfname] == $ld)
                    {
                        $answer .= CHECKED;
                    }
                    // --> START NEW FEATURE - SAVE
                    $answer .= " onclick=\"$checkconditionFunction(this.value, this.name, this.type)\" />\n</label>\n";
                    // --> END NEW FEATURE - SAVE
                    $answer .= "\n\t</td>\n";
                    $thiskey++;
                }
                if (count($labelans1)>0) // if second label set is used
                {
                    $dualgroup++;
                    $hiddenanswers='';
                    $answer .= "\t<td class=\"dual_scale_separator information-item\">&nbsp;</td>\n";		// separator
                    $hiddenanswers .= "<input type=\"hidden\" name=\"java$myfname1\" id=\"java$myfname1\" value=\"";
                    if (isset($_SESSION['survey_'.$this->surveyid][$myfname1])) {$hiddenanswers .= $_SESSION['survey_'.$this->surveyid][$myfname1];}
                    $hiddenanswers .= "\" />\n";
                    $thiskey=0;
                    foreach ($labelcode1 as $ld) // second label set
                    {
                        $answer .= "\t<td class=\"answer_cell_2_00$ld  answer-item radio-item\">\n";
                        if ($hiddenanswers!='')
                        {
                            $answer .=$hiddenanswers;
                            $hiddenanswers='';
                        }
                        $answer .= "<label for=\"answer$myfname1-$ld\">\n"
                        . "\t<input class=\"radio\" type=\"radio\" name=\"$myfname1\" value=\"$ld\" id=\"answer$myfname1-$ld\" title=\""
                        . HTMLEscape(strip_tags($labelans1[$thiskey])).'"';
                        if (isset($_SESSION['survey_'.$this->surveyid][$myfname1]) && $_SESSION['survey_'.$this->surveyid][$myfname1] == $ld)
                        {
                            $answer .= CHECKED;
                        }
                        // --> START NEW FEATURE - SAVE
                        $answer .= " onclick=\"secondlabel_checkconditions(this.value, this.name, this.type)\" />\n</label>\n";
                        // --> END NEW FEATURE - SAVE

                        $answer .= "\t</td>\n";
                        $thiskey++;
                    }
                }
                if (strpos($answertextsave,'|'))
                {
                    $answertext=substr($answertextsave,strpos($answertextsave,'|')+1);
                    $answer .= "\t<td class=\"answertextright\">$answertext</td>\n";
                    $hiddenanswers = '';
                }
                elseif ($right_exists)
                {
                    $answer .= "\t<td class=\"answertextright\">&nbsp;</td>\n";
                }

                if ($this->mandatory != "Y" && SHOW_NO_ANSWER == 1)
                {
                    $answer .= "\t<td class=\"dual_scale_separator information-item\">&nbsp;</td>\n"; // separator
                    $answer .= "\t<td class=\"dual_scale_no_answer answer-item radio-item noanswer-item\">\n"
                    . "<label for='answer$myfname-'>\n"
                    . "\t<input class='radio' type='radio' name='$myfname' value='' id='answer$myfname-' title='".$clang->gT("No answer")."'";
                    if (!isset($_SESSION['survey_'.$this->surveyid][$myfname]) || $_SESSION['survey_'.$this->surveyid][$myfname] == "")
                    {
                        $answer .= CHECKED;
                    }
                    // --> START NEW FEATURE - SAVE
                    $answer .= " onclick=\"noanswer_checkconditions(this.value, this.name, this.type)\" />\n"
                    . "</label>\n"
                    . "\t</td>\n";
                    // --> END NEW FEATURE - SAVE
                }

                $answer .= "</tr>\n";
                //IF a MULTIPLE of flexi-redisplay figure, repeat the headings
                $fn++;
            }
            $answer .= "\t</tbody>\n";
            $answer .= "</table>\n";
        }
        elseif ($useDropdownLayout === true && $lresult->count() > 0)
        {

            if (trim($aQuestionAttributes['answer_width'])!='')
            {
                $answerwidth=$aQuestionAttributes['answer_width'];
            } else {
                $answerwidth=20;
            }
            $separatorwidth=(100-$answerwidth)/10;
            $columnswidth=100-$answerwidth-($separatorwidth*2);

            $answer = "";

            // Get Answers

            $ansresult = $this->getChildren();
            $anscount = count($ansresult);

            if ($anscount==0)
            {
                $answer .="\n<p class=\"error\">".$clang->gT('Error: This question has no answers.')."</p>\n";
            }
            else
            {

                //already done $lresult = dbExecuteAssoc($lquery);
                foreach ($lresult->readAll() as $lrow)
                {
                    $labels0[]=Array('code' => $lrow['code'],
                    'title' => $lrow['answer']);
                }
                $lresult1 = dbExecuteAssoc($lquery1);   //Checked
                foreach ($lresult1->readAll() as $lrow1)
                {
                    $labels1[]=Array('code' => $lrow1['code'],
                    'title' => $lrow1['answer']);
                }


                // Get attributes for Headers and Prefix/Suffix

                if (trim($aQuestionAttributes['dropdown_prepostfix'][$_SESSION['survey_'.$this->surveyid]['s_lang']])!='') {
                    list ($ddprefix, $ddsuffix) =explode("|",$aQuestionAttributes['dropdown_prepostfix'][$_SESSION['survey_'.$this->surveyid]['s_lang']]);
                    $ddprefix = $ddprefix;
                    $ddsuffix = $ddsuffix;
                }
                else
                {
                    $ddprefix ='';
                    $ddsuffix='';
                }
                if (trim($aQuestionAttributes['dropdown_separators'])!='') {
                    list ($postanswSep, $interddSep) =explode('|',$aQuestionAttributes['dropdown_separators']);
                    $postanswSep = $postanswSep;
                    $interddSep = $interddSep;
                }
                else {
                    $postanswSep = '';
                    $interddSep = '';
                }

                $colspan_1 = '';
                $colspan_2 = '';
                $suffix_cell = '';
                $answer .= "\n<table class=\"question subquestion-list questions-list dropdown-list\" summary=\"".str_replace('"','' ,strip_tags($this->text))." - an dual array type question\">\n\n"
                . "\t<col class=\"answertext\" width=\"$answerwidth%\" />\n";
                if($ddprefix != '')
                {
                    $answer .= "\t<col class=\"ddprefix\" />\n";
                    $colspan_1 = ' colspan="2"';
                }
                $answer .= "\t<col class=\"dsheader\" />\n";
                if($ddsuffix != '')
                {
                    $answer .= "\t<col class=\"ddsuffix\" />\n";
                    if(!empty($colspan_1))
                    {
                        $colspan_2 = ' colspan="3"';
                    }
                    $suffix_cell = "\t<td>&nbsp;</td>\n"; // suffix
                }
                $answer .= "\t<col class=\"ddarrayseparator\" width=\"$separatorwidth%\" />\n";
                if($ddprefix != '')
                {
                    $answer .= "\t<col class=\"ddprefix\" />\n";
                }
                $answer .= "\t<col class=\"dsheader\" />\n";
                if($ddsuffix != '')
                {
                    $answer .= "\t<col class=\"ddsuffix\" />\n";
                };
                // headers
                $answer .= "\n\t<thead>\n"
                . "<tr>\n"
                . "\t<td$colspan_1>&nbsp;</td>\n" // prefix
                . "\n"
                //			. "\t<td align='center' width='$columnswidth%'><span class='dsheader'>$leftheader</span></td>\n"
                . "\t<th>$leftheader</th>\n"
                . "\n"
                . "\t<td$colspan_2>&nbsp;</td>\n" // suffix // Inter DD separator // prefix
                //			. "\t<td align='center' width='$columnswidth%'><span class='dsheader'>$rightheader</span></td>\n"
                . "\t<th>$rightheader</th>\n"
                . $suffix_cell."</tr>\n"
                . "\t</thead>\n\n";
                $answer .= "\n<tbody>\n";
                $trbc = '';
                foreach ($ansresult as $ansrow)
                {
                    $rowname = $this->fieldname.$ansrow['title'];
                    $dualgroup=0;
                    $myfname = $this->fieldname.$ansrow['title']."#".$dualgroup;
                    $dualgroup1=1;
                    $myfname1 = $this->fieldname.$ansrow['title']."#".$dualgroup1;

                    if ($this->mandatory=='Y' && (is_array($notanswered)) && ((array_search($myfname, $notanswered) !== FALSE) || (array_search($myfname1, $notanswered) !== FALSE)) && (($_SESSION['survey_'.$this->surveyid][$myfname] == '') || ($_SESSION['survey_'.$this->surveyid][$myfname1] == '')) )
                    {
                        $answertext="<span class='errormandatory'>".dTexts__run($ansrow['question'])."</span>";
                    }
                    else
                    {
                        $answertext=dTexts__run($ansrow['question']);
                    }

                    $trbc = alternation($trbc , 'row');

                    // Get array_filter stuff
                    list($htmltbody2, $hiddenfield)=return_array_filter_strings($this, $aQuestionAttributes, $thissurvey, $ansrow, $rowname, $trbc, $myfname,"tr","$trbc subquestion-list questions-list dropdown-list");

                    $answer .= $htmltbody2;

                    $answer .= "\t<th class=\"answertext\">\n"
                    . "<label for=\"answer$rowname\">\n"
                    . $hiddenfield
                    . "$answertext\n"
                    . "</label>\n"
                    . "\t</th>\n";

                    // Label0

                    // prefix
                    if($ddprefix != '')
                    {
                        $answer .= "\t<td class=\"ddprefix information-item\">$ddprefix</td>\n";
                    }
                    $answer .= "\t<td class=\"answer-item dropdown-item\">\n"
                    . "<select name=\"$myfname\" id=\"answer$myfname\" onchange=\"array_dual_dd_checkconditions(this.value, this.name, this.type,$dualgroup,$checkconditionFunction);\">\n";

                    if (!isset($_SESSION['survey_'.$this->surveyid][$myfname]) || $_SESSION['survey_'.$this->surveyid][$myfname] =='')
                    {
                        $answer .= "\t<option value=\"\" ".SELECTED.'>'.$clang->gT('Please choose...')."</option>\n";
                    }

                    foreach ($labels0 as $lrow)
                    {
                        $answer .= "\t<option value=\"".$lrow['code'].'" ';
                        if (isset($_SESSION['survey_'.$this->surveyid][$myfname]) && $_SESSION['survey_'.$this->surveyid][$myfname] == $lrow['code'])
                        {
                            $answer .= SELECTED;
                        }
                        $answer .= '>'.$lrow['title']."</option>\n";
                    }
                    // If not mandatory and showanswer, show no ans
                    if ($this->mandatory != 'Y' && SHOW_NO_ANSWER == 1)
                    {
                        $answer .= "\t<option class=\"noanswer-item\" value=\"\" ";
                        if (!isset($_SESSION['survey_'.$this->surveyid][$myfname]) || $_SESSION['survey_'.$this->surveyid][$myfname] == '')
                        {
                            $answer .= SELECTED;
                        }
                        $answer .= '>'.$clang->gT('No answer')."</option>\n";
                    }
                    $answer .= "</select>\n";

                    // suffix
                    if($ddsuffix != '')
                    {
                        $answer .= "\t<td class=\"ddsuffix information-item\">$ddsuffix</td>\n";
                    }
                    $answer .= "<input type=\"hidden\" name=\"java$myfname\" id=\"java$myfname\" value=\"";
                    if (isset($_SESSION['survey_'.$this->surveyid][$myfname]))
                    {
                        $answer .= $_SESSION['survey_'.$this->surveyid][$myfname];
                    }
                    $answer .= "\" />\n"
                    . "\t</td>\n";

                    $answer .= "\t<td class=\"ddarrayseparator information-item\">$interddSep</td>\n"; //Separator

                    // Label1

                    // prefix
                    if($ddprefix != '')
                    {
                        $answer .= "\t<td class='ddprefix information-item'>$ddprefix</td>\n";
                    }
                    //				$answer .= "\t<td align='left' width='$columnswidth%'>\n"
                    $answer .= "\t<td class=\"answer-item dropdown-item\">\n"
                    . "<select name=\"$myfname1\" id=\"answer$myfname1\" onchange=\"array_dual_dd_checkconditions(this.value, this.name, this.type,$dualgroup1,$checkconditionFunction);\">\n";

                    if (empty($_SESSION['survey_'.$this->surveyid][$myfname]))
                    {
                        $answer .= "\t<option value=\"\"".SELECTED.'>'.$clang->gT('Please choose...')."</option>\n";
                    }

                    foreach ($labels1 as $lrow1)
                    {
                        $answer .= "\t<option value=\"".$lrow1['code'].'" ';
                        if (isset($_SESSION['survey_'.$this->surveyid][$myfname1]) && $_SESSION['survey_'.$this->surveyid][$myfname1] == $lrow1['code'])
                        {
                            $answer .= SELECTED;
                        }
                        $answer .= '>'.$lrow1['title']."</option>\n";
                    }
                    // If not mandatory and showanswer, show no ans
                    if ($this->mandatory != 'Y' && SHOW_NO_ANSWER == 1)
                    {
                        $answer .= "\t<option class=\"noanswer-item\" value='' ";
                        if (empty($_SESSION['survey_'.$this->surveyid][$myfname]))
                        {
                            $answer .= SELECTED;
                        }
                        $answer .= ">".$clang->gT('No answer')."</option>\n";
                    }
                    $answer .= "</select>\n";

                    // suffix
                    if($ddsuffix != '')
                    {
                        $answer .= "\t<td class=\"ddsuffix information-item\">$ddsuffix</td>\n";
                    }
                    $answer .= "<input type=\"hidden\" name=\"java$myfname1\" id=\"java$myfname1\" value=\"";
                    if (isset($_SESSION['survey_'.$this->surveyid][$myfname1]))
                    {
                        $answer .= $_SESSION['survey_'.$this->surveyid][$myfname1];
                    }
                    $answer .= "\" />\n"
                    . "\t</td>\n";

                    $answer .= "</tr>\n";
                }
            } // End there are answers
            $answer .= "\t</tbody>\n";
            $answer .= "</table>\n";
        }
        else
        {
            $answer = "<p class='error'>".$clang->gT("Error: There are no answer options for this question and/or they don't exist in this language.")."</p>\n";
        }
        return $answer;
    }
    
    public function availableAttributes($attr = false)
    {
        $attrs=array("answer_width","array_filter","array_filter_exclude","dropdown_prepostfix","dropdown_separators","dualscale_headerA","dualscale_headerB","statistics_showgraph","statistics_graphtype","hide_tip","hidden","max_answers","min_answers","page_break","public_statistics","random_order","parent_order","use_dropdown","scale_export","random_group");
        return $attr?array_key_exists($attr,$attrs):$attrs;
    }

    public function questionProperties($prop = false)
    {
        $clang=Yii::app()->lang;
        $props=array('description' => $clang->gT("Array dual scale"),'group' => $clang->gT('Arrays'),'subquestions' => 1,'assessable' => 1,'class' => 'array-flexible-duel-scale','hasdefaultvalues' => 0,'answerscales' => 2);
        return $prop?$props[$prop]:$props;
    }
}
?>