<?php
class SelectQuestion extends ListQuestion
{
    public function getAnswerHTML()
    {
        global $dropdownthreshold;

        $clang=Yii::app()->lang;

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

        if (trim($aQuestionAttributes['category_separator'])!='')
        {
            $optCategorySeparator = $aQuestionAttributes['category_separator'];
        }

        $answer='';

        $result = $this->getOther();
        $other = $result[0]['other'];

        //question attribute random order set?
        if ($aQuestionAttributes['random_order']==1)
        {
            $ansquery = "SELECT * FROM {{answers}} WHERE qid=$this->id AND language='".$_SESSION['survey_'.$this->surveyid]['s_lang']."' and scale_id=0 ORDER BY ".dbRandom();
        }
        //question attribute alphasort set?
        elseif ($aQuestionAttributes['alphasort']==1)
        {
            $ansquery = "SELECT * FROM {{answers}} WHERE qid=$this->id AND language='".$_SESSION['survey_'.$this->surveyid]['s_lang']."' and scale_id=0 ORDER BY answer";
        }
        //no question attributes -> order by sortorder
        else
        {
            $ansquery = "SELECT * FROM {{answers}} WHERE qid=$this->id AND language='".$_SESSION['survey_'.$this->surveyid]['s_lang']."' and scale_id=0 ORDER BY sortorder, answer";
        }

        $ansresult = Yii::app()->db->createCommand($ansquery)->query() or safeDie('Couldn\'t get answers<br />'.$ansquery.'<br />');    //Checked

        $dropdownSize = '';
        if (isset($aQuestionAttributes['dropdown_size']) && $aQuestionAttributes['dropdown_size'] > 0)
        {
            $_height = sanitize_int($aQuestionAttributes['dropdown_size']) ;
            $_maxHeight = $ansresult->RowCount();
            if ((!empty($_SESSION['survey_'.$this->surveyid][$this->fieldname])) && $this->mandatory != 'Y' && $this->mandatory != 'Y' && SHOW_NO_ANSWER == 1) {
                ++$_maxHeight;  // for No Answer
            }
            if (isset($other) && $other=='Y') {
                ++$_maxHeight;  // for Other
            }
            if (!$_SESSION['survey_'.$this->surveyid][$this->fieldname]) {
                ++$_maxHeight;  // for 'Please choose:'
            }

            if ($_height > $_maxHeight) {
                $_height = $_maxHeight;
            }
            $dropdownSize = ' size="'.$_height.'"';
        }

        $prefixStyle = 0;
        if (isset($aQuestionAttributes['dropdown_prefix']))
        {
            $prefixStyle = sanitize_int($aQuestionAttributes['dropdown_prefix']) ;
        }
        $_rowNum=0;
        $_prefix='';

        if (!isset($optCategorySeparator))
        {
            foreach ($ansresult->readAll() as $ansrow)
            {
                $opt_select = '';
                if ($_SESSION['survey_'.$this->surveyid][$this->fieldname] == $ansrow['code'])
                {
                    $opt_select = SELECTED;
                }
                if ($prefixStyle == 1) {
                    $_prefix = ++$_rowNum . ') ';
                }
                $answer .= "<option value='{$ansrow['code']}' {$opt_select}>{$_prefix}{$ansrow['answer']}</option>\n";
            }
        }
        else
        {
            $defaultopts = Array();
            $optgroups = Array();
            foreach ($ansresult->readAll() as $ansrow)
            {
                // Let's sort answers in an array indexed by subcategories
                @list ($categorytext, $answertext) = explode($optCategorySeparator,$ansrow['answer']);
                // The blank category is left at the end outside optgroups
                if ($categorytext == '')
                {
                    $defaultopts[] = array ( 'code' => $ansrow['code'], 'answer' => $answertext);
                }
                else
                {
                    $optgroups[$categorytext][] = array ( 'code' => $ansrow['code'], 'answer' => $answertext);
                }
            }

            foreach ($optgroups as $categoryname => $optionlistarray)
            {
                $answer .= '                                   <optgroup class="dropdowncategory" label="'.$categoryname.'">
                ';

                foreach ($optionlistarray as $optionarray)
                {
                    if ($_SESSION['survey_'.$this->surveyid][$this->fieldname] == $optionarray['code'])
                    {
                        $opt_select = SELECTED;
                    }
                    else
                    {
                        $opt_select = '';
                    }

                    $answer .= '     					<option value="'.$optionarray['code'].'"'.$opt_select.'>'.$optionarray['answer'].'</option>
                    ';
                }

                $answer .= '                                   </optgroup>';
            }
            $opt_select='';
            foreach ($defaultopts as $optionarray)
            {
                if ($_SESSION['survey_'.$this->surveyid][$this->fieldname] == $optionarray['code'])
                {
                    $opt_select = SELECTED;
                }
                else
                {
                    $opt_select = '';
                }

                $answer .= '     					<option value="'.$optionarray['code'].'"'.$opt_select.'>'.$optionarray['answer'].'</option>
                ';
            }
        }

        if (!$_SESSION['survey_'.$this->surveyid][$this->fieldname])
        {
            $answer = '					<option value=""'.SELECTED.'>'.$clang->gT('Please choose...').'</option>'."\n".$answer;
        }

        if (isset($other) && $other=='Y')
        {
            if ($_SESSION['survey_'.$this->surveyid][$this->fieldname] == '-oth-')
            {
                $opt_select = SELECTED;
            }
            else
            {
                $opt_select = '';
            }
            if ($prefixStyle == 1) {
                $_prefix = ++$_rowNum . ') ';
            }
            $answer .= '					<option value="-oth-"'.$opt_select.'>'.$_prefix.$othertext."</option>\n";
        }

        if (($_SESSION['survey_'.$this->surveyid][$this->fieldname] || $_SESSION['survey_'.$this->surveyid][$this->fieldname] != '') && $this->mandatory != 'Y' && $this->mandatory != 'Y' && SHOW_NO_ANSWER == 1)
        {
            if ($prefixStyle == 1) {
                $_prefix = ++$_rowNum . ') ';
            }
            $answer .= '<option class="noanswer-item" value="">'.$_prefix.$clang->gT('No answer')."</option>\n";
        }
        $answer .= '				</select>
        <input type="hidden" name="java'.$this->fieldname.'" id="java'.$this->fieldname.'" value="'.$_SESSION['survey_'.$this->surveyid][$this->fieldname].'" />';

        if (isset($other) && $other=='Y')
        {
            $sselect_show_hide = ' showhideother(this.name, this.value);';
        }
        else
        {
            $sselect_show_hide = '';
        }
        $sselect = '
        <p class="question answer-item dropdown-item"><label for="answer'.$this->fieldname.'" class="hide label">'.$clang->gT('Please choose').'</label>
        <select name="'.$this->fieldname.'" id="answer'.$this->fieldname.'"'.$dropdownSize.' onchange="'.$checkconditionFunction.'(this.value, this.name, this.type);'.$sselect_show_hide.'">
        ';
        $answer = $sselect.$answer;

        if (isset($other) && $other=='Y')
        {
            $answer = "\n<script type=\"text/javascript\">\n"
            ."<!--\n"
            ."function showhideother(name, value)\n"
            ."\t{\n"
            ."\tvar hiddenothername='othertext'+name;\n"
            ."\tif (value == \"-oth-\")\n"
            ."{\n"
            ."document.getElementById(hiddenothername).style.display='';\n"
            ."document.getElementById(hiddenothername).focus();\n"
            ."}\n"
            ."\telse\n"
            ."{\n"
            ."document.getElementById(hiddenothername).style.display='none';\n"
            ."document.getElementById(hiddenothername).value='';\n" // reset othercomment field
            ."}\n"
            ."\t}\n"
            ."//--></script>\n".$answer;
            $answer .= '				<input type="text" id="othertext'.$this->fieldname.'" name="'.$this->fieldname.'other" style="display:';

            if ($_SESSION['survey_'.$this->surveyid][$this->fieldname] != '-oth-')
            {
                $answer .= 'none';
            }

            //		// --> START BUG FIX - text field for other was not repopulating when returning to page via << PREV
            $answer .= '"';
            //		$thisfieldname=$this->fieldname.'other';
            //		if (isset($_SESSION['survey_'.$this->surveyid][$thisfieldname])) { $answer .= ' value="'.htmlspecialchars($_SESSION['survey_'.$this->surveyid][$thisfieldname],ENT_QUOTES).'" ';}
            //		// --> END BUG FIX

            // --> START NEW FEATURE - SAVE
            $answer .= "  alt='".$clang->gT('Other answer')."' onchange='$checkconditionFunction(this.value, this.name, this.type);'";
            $thisfieldname=$this->fieldname.'other';
            if (isset($_SESSION['survey_'.$this->surveyid][$thisfieldname])) { $answer .= " value='".htmlspecialchars($_SESSION['survey_'.$this->surveyid][$thisfieldname],ENT_QUOTES)."' ";}
            $answer .= ' />';
            $answer .= "</p>";
            // --> END NEW FEATURE - SAVE
        }
        else
        {
            $answer .= "</p>";
        }

        return $answer;
    }
    
    protected function getOther()
    {
        if ($this->other) return $this->other;
        $query = "SELECT other FROM {{questions}} WHERE qid=".$this->id." AND language='".$_SESSION['survey_'.$this->surveyid]['s_lang']."' ";
        return $this->other = Yii::app()->db->createCommand($query)->query()->readAll();  //Checked
    }

    //public function getTitle() - inherited
    
    //public function getHelp() - inherited
       
    public function createFieldmap($type=null)
    {
        $clang = Yii::app()->lang;
        $map = parent::createFieldmap($type);
        if($this->other=='Y')
        {
            $other = $map[$this->fieldname];
            $other['fieldname'].='other';
            $other['aid']='other';
            $other['subquestion']=$clang->gT("Other");
            if (isset($this->default['other'])) $other['defaultvalue']=$this->default['other'];
            else unset($other['defaultvalue']);
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
    
    public function availableAttributes($attr = false)
    {
        $attrs=array("alphasort","category_separator","statistics_showgraph","statistics_graphtype","hide_tip","hidden","other_comment_mandatory","other_replace_text","page_break","public_statistics","random_order","parent_order","dropdown_size","dropdown_prefix","scale_export","random_group");
        return $attr?array_key_exists($attr,$attrs):$attrs;
    }

    public function questionProperties($prop = false)
    {
        $clang=Yii::app()->lang;
        $props=array('description' => $clang->gT("List (Dropdown)"),'group' => $clang->gT("Single choice questions"),'subquestions' => 0,'class' => 'list-dropdown','hasdefaultvalues' => 1,'assessable' => 1,'answerscales' => 1);
        return $prop?$props[$prop]:$props;
    }
}
?>