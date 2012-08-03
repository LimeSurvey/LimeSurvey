<?php
class CommentListQuestion extends ListQuestion
{
    public function getAnswerHTML()
    {
        global $maxoptionsize, $thissurvey;
        $clang=Yii::app()->lang;
        $dropdownthreshold = Yii::app()->getConfig("dropdownthreshold");

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

        $answer = '';

        $aQuestionAttributes = $this->getAttributeValues();
        if (!isset($maxoptionsize)) {$maxoptionsize=35;}

        //question attribute random order set?
        if ($aQuestionAttributes['random_order']==1) {
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

        $ansresult=Yii::app()->db->createCommand($ansquery)->query();
        $anscount = $ansresult->getRowCount();


        $hint_comment = $clang->gT('Please enter your comment here');
        if ($aQuestionAttributes['use_dropdown']!=1 && $anscount <= $dropdownthreshold)
        {
            $answer .= '<div class="list">
            <ul class="answers-list radio-list">
            ';

            foreach ($ansresult->readAll() as $ansrow)
            {
                $check_ans = '';
                if ($_SESSION['survey_'.$this->surveyid][$this->fieldname] == $ansrow['code'])
                {
                    $check_ans = CHECKED;
                }
                $answer .= '		<li class="answer-item radio-item">
                <input type="radio" name="'.$this->fieldname.'" id="answer'.$this->fieldname.$ansrow['code'].'" value="'.$ansrow['code'].'" class="radio" '.$check_ans.' onclick="'.$checkconditionFunction.'(this.value, this.name, this.type)" />
                <label for="answer'.$this->fieldname.$ansrow['code'].'" class="answertext">'.$ansrow['answer'].'</label>
                </li>
                ';
            }

            if ($this->mandatory != 'Y' && SHOW_NO_ANSWER == 1)
            {
                if ((!$_SESSION['survey_'.$this->surveyid][$this->fieldname] || $_SESSION['survey_'.$this->surveyid][$this->fieldname] == '') ||($_SESSION['survey_'.$this->surveyid][$this->fieldname] == ' ' ))
                {
                    $check_ans = CHECKED;
                }
                elseif (($_SESSION['survey_'.$this->surveyid][$this->fieldname] || $_SESSION['survey_'.$this->surveyid][$this->fieldname] != ''))
                {
                    $check_ans = '';
                }
                $answer .= '		<li class="answer-item radio-item noanswer-item">
                <input class="radio" type="radio" name="'.$this->fieldname.'" id="answer'.$this->fieldname.'" value=" " onclick="'.$checkconditionFunction.'(this.value, this.name, this.type)"'.$check_ans.' />
                <label for="answer'.$this->fieldname.'" class="answertext">'.$clang->gT('No answer').'</label>
                </li>
                ';
            }

            $fname2 = $this->fieldname.'comment';
            if ($anscount > 8) {$tarows = $anscount/1.2;} else {$tarows = 4;}
            // --> START NEW FEATURE - SAVE
            //    --> START ORIGINAL
            //        $answer .= "\t<td valign='top'>\n"
            //                 . "<textarea class='textarea' name='$this->fieldnamecomment' id='answer$this->fieldnamecomment' rows='$tarows' cols='30'>";
            //    --> END ORIGINAL
            $answer .= '	</ul>
            </div>

            <p class="comment answer-item text-item">
            <label for="answer'.$this->fieldname.'comment">'.$hint_comment.':</label>

            <textarea class="textarea '.$kpclass.'" name="'.$this->fieldname.'comment" id="answer'.$this->fieldname.'comment" rows="'.floor($tarows).'" cols="30" >';
            // --> END NEW FEATURE - SAVE
            if (isset($_SESSION['survey_'.$this->surveyid][$fname2]) && $_SESSION['survey_'.$this->surveyid][$fname2])
            {
                $answer .= str_replace("\\", "", $_SESSION['survey_'.$this->surveyid][$fname2]);
            }
            $answer .= '</textarea>
            </p>

            <input class="radio" type="hidden" name="java'.$this->fieldname.'" id="java'.$this->fieldname.'" value="'.$_SESSION['survey_'.$this->surveyid][$this->fieldname].'" />
            ';
        }
        else //Dropdown list
        {
            // --> START NEW FEATURE - SAVE
            $answer .= '<p class="select answer-item dropdown-item">
            <select class="select" name="'.$this->fieldname.'" id="answer'.$this->fieldname.'" onchange="'.$checkconditionFunction.'(this.value, this.name, this.type)" >
            ';
            // --> END NEW FEATURE - SAVE
            foreach ($ansresult->readAll() as $ansrow)
            {
                $check_ans = '';
                if ($_SESSION['survey_'.$this->surveyid][$this->fieldname] == $ansrow['code'])
                {
                    $check_ans = SELECTED;
                }
                $answer .= '		<option value="'.$ansrow['code'].'"'.$check_ans.'>'.$ansrow['answer']."</option>\n";

                if (strlen($ansrow['answer']) > $maxoptionsize)
                {
                    $maxoptionsize = strlen($ansrow['answer']);
                }
            }
            if ($this->mandatory != 'Y' && SHOW_NO_ANSWER == 1)
            {
                if ((!$_SESSION['survey_'.$this->surveyid][$this->fieldname] || $_SESSION['survey_'.$this->surveyid][$this->fieldname] == '') ||($_SESSION['survey_'.$this->surveyid][$this->fieldname] == ' '))
                {
                    $check_ans = SELECTED;
                }
                elseif ($_SESSION['survey_'.$this->surveyid][$this->fieldname] || $_SESSION['survey_'.$this->surveyid][$this->fieldname] != '')
                {
                    $check_ans = '';
                }
                $answer .= '<option class="noanswer-item" value=""'.$check_ans.'>'.$clang->gT('No answer')."</option>\n";
            }
            $answer .= '	</select>
            </p>
            ';
            $fname2 = $this->fieldname.'comment';
            if ($anscount > 8) {$tarows = $anscount/1.2;} else {$tarows = 4;}
            if ($tarows > 15) {$tarows=15;}
            $maxoptionsize=$maxoptionsize*0.72;
            if ($maxoptionsize < 33) {$maxoptionsize=33;}
            if ($maxoptionsize > 70) {$maxoptionsize=70;}
            $answer .= '<p class="comment answer-item text-item">
            <label for="answer'.$this->fieldname.'comment">'.$hint_comment.':</label>
            <textarea class="textarea '.$kpclass.'" name="'.$this->fieldname.'comment" id="answer'.$this->fieldname.'comment" rows="'.$tarows.'" cols="'.$maxoptionsize.'" >';
            // --> END NEW FEATURE - SAVE
            if (isset($_SESSION['survey_'.$this->surveyid][$fname2]) && $_SESSION['survey_'.$this->surveyid][$fname2])
            {
                $answer .= str_replace("\\", "", $_SESSION['survey_'.$this->surveyid][$fname2]);
            }
            $answer .= '</textarea>
            <input class="radio" type="hidden" name="java'.$this->fieldname.'" id="java'.$this->fieldname.'" value="'.$_SESSION['survey_'.$this->surveyid][$this->fieldname].'" /></p>';
        }
        return $answer;
    }

    public function getDataEntry($idrow, &$fnames, $language)
    {
        $lquery = "SELECT * FROM {{answers}} WHERE qid={$this->id} AND language = '{$language}' ORDER BY sortorder, answer";
        $lresult = dbExecuteAssoc($lquery);
        $output = "\t<select name='{$this->fieldname}'>\n"
        ."<option value=''";
        if ($idrow[$this->fieldname] == "") {$output .= " selected='selected'";}
        $output .= ">".$clang->gT("Please choose")."..</option>\n";

        foreach ($lresult->readAll() as $llrow)
        {
            $output .= "<option value='{$llrow['code']}'";
            if ($idrow[$this->fieldname] == $llrow['code']) {$output .= " selected='selected'";}
            $output .= ">{$llrow['answer']}</option>\n";
        }
        $q=next($fnames);
        $output .= "\t</select>\n"
        ."\t<br />\n"
        ."\t<textarea cols='45' rows='5' name='{$q->fieldname}'>"
        .htmlspecialchars($idrow[$q->fieldname]) . "</textarea>\n";
        return $output;
    }

    public function createFieldmap($type=null)
    {
        $clang = Yii::app()->lang;
        $map = QuestionModule::createFieldmap($type);
        $comment = $map[$this->fieldname];
        $comment['fieldname'].='comment';
        $comment['aid']='comment';
        $comment['subquestion']=$clang->gT("Comment");
        unset($comment['defaultvalue']);
        $q = clone $comment['q'];
        unset($q->default);
        $q->fieldname .= 'comment';
        $q->aid='comment';
        $q->sq=$clang->gT("Comment");
        $comment['q']=$q;
        $map[$comment['fieldname']]=$comment;
        return $map;
    }

    public function getFullAnswer($answerCode, $export, $survey)
    {
        $answers = $survey->getAnswers($this->id);
        if (array_key_exists($answerCode, $answers))
        {
            //This is one of the dropdown list options.
            return $answers[$answerCode]['answer'];
        }
        else
        {
            //This is a comment.
            return $answerCode;
        }
    }

    public function getFieldSubHeading($survey, $export, $code)
    {
        if ($this->aid == 'comment')
        {
            return ' '.$export->getCommentSubHeading();
        }
        return '';
    }

    public function jsVarNameOn()
    {
        if ($this->aid == 'comment')
        {
            return 'answer' . $this->fieldname;
        }
        else
        {
            return 'java' . $this->fieldname;
        }
    }

    public function generateQuestionInfo()
    {
        return QuestionModule::generateQuestionInfo();
    }

    public function generateSQInfo($ansArray)
    {
        return QuestionModule::generateSQInfo($ansArray);
    }

    public function compareField($sgqa, $sq)
    {
        return false;
    }

    public function includeList()
    {
        return false;
    }

    public function getVarAttributeValueNAOK($name, $default, $gseq, $qseq, $ansArray)
    {
        if (preg_match('/comment\.value/',$name))
        {
            return LimeExpressionManager::GetVarAttribute($name,'code',$default,$gseq,$qseq);
        }
        else
        {
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
    }

    public function getVarAttributeShown($name, $default, $gseq, $qseq, $ansArray)
    {
        $code = LimeExpressionManager::GetVarAttribute($name,'code',$default,$gseq,$qseq);
        if (preg_match('/comment$/',$name))
        {
            return $code;
        }
        else
        {
            $scale_id = LimeExpressionManager::GetVarAttribute($name,'scale_id','0',$gseq,$qseq);
            $which_ans = $scale_id . '~' . $code;
            if (is_null($ansArray))
            {
                return $code;
            }
            else
            {
                if (isset($ansArray[$which_ans])) {
                    $answerInfo = explode('|',$ansArray[$which_ans]);
                    array_shift($answerInfo);
                    $answer = join('|',$answerInfo);
                }
                else {
                    $answer = $code;
                }
                return $answer;
            }
        }
    }

    public function availableAttributes($attr = false)
    {
        $attrs=array("alphasort","statistics_showgraph","statistics_graphtype","hide_tip","hidden","page_break","public_statistics","random_order","parent_order","use_dropdown","scale_export","random_group");
        return $attr?in_array($attr,$attrs):$attrs;
    }

    public function questionProperties($prop = false)
    {
        $clang=Yii::app()->lang;
        $props=array('description' => $clang->gT("List with comment"),'group' => $clang->gT("Single choice questions"),'subquestions' => 0,'class' => 'list-with-comment','hasdefaultvalues' => 1,'assessable' => 1,'answerscales' => 1);
        return $prop?$props[$prop]:$props;
    }
}
?>