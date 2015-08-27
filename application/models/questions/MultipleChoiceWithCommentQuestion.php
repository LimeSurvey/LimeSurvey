<?php
namespace ls\models\questions;


use ls\interfaces\iResponse;

class MultipleChoiceWithCommentQuestion extends MultipleChoiceQuestion
{
    /**
     * Returns an array of EM expression that validate this question.
     * @return string[]
     */
    public function getValidationExpressions()
    {
        $result = parent::getValidationExpressions();
        switch ($this->commented_checkbox) {
            case 'checked':
                $sq_eqn_commented_checkbox = [];
                foreach ($this->subQuestions as $subQuestion) {
                    $sq_eqn_commented_checkbox[] = "(is_empty({$subQuestion->varName}.NAOK) and !is_empty({$subQuestion->varName}comment.NAOK))";
                }
                $result[] = [
                    'type' => 'commented_checkbox',
                    'class' => 'commented_checkbox',
                    'eqn' => "sum(" . implode(",", $sq_eqn_commented_checkbox) . ")==0",
                ];
                break;
            case 'unchecked':
                $sq_eqn_commented_checkbox = array();
                foreach ($this->subQuestions as $subQuestion) {
                    $sq_eqn_commented_checkbox[] = "(!is_empty({$subQuestion->varName}.NAOK) and !is_empty({$subQuestion->varName}comment.NAOK))";
                }
                $result[] = [
                    'type' => 'commented_checkbox',
                    'class' => 'commented_checkbox',
                    'eqn' => "sum(" . implode(",", $sq_eqn_commented_checkbox) . ")==0",
                ];
                break;
            case 'allways':
            default:
                break;
        }
        if ($this->commented_checkbox != "always") {

        }
        return $result;
    }

    /**
     * This function return the class by question type
     * @param string question type
     * @return string Question class to be added to the container
     */
    public function getClasses()
    {
        return ['multiple-opt-comments'];
    }

    /**
     * This function renders the object.
     * It MUST NOT produce any output.
     * It should return a string or an object that can be converted to string.
     * @param \ls\interfaces\Response $response
     * @param \SurveySession $session
     * @return \RenderedQuestion
     */
    public function render(iResponse $response, \SurveySession $session)
    {
        $result = parent::render($response, $session);

        if ($this->other_numbers_only == 1) {
            $sSeparator = getRadixPointData($this->survey->getLocalizedNumberFormat())['separator'];

        }

        if (trim($this->other_replace_text)!='')
        {
            $otherText = $this->other_replace_text;
        }
        else
        {
            $otherText = gT('Other:');
        }

        $anscount = count($this->subQuestions) * 2;

        $html = "<input type='hidden' name='MULTI$this->sgqa' value='$anscount' />\n";
        $answer_main = '';

        $fn = 1;
        if (!isset($other)){
            $other = 'N';
        }
        if($other == 'Y')
        {
            $label_width = 25;
        }
        else
        {
            $label_width = 0;
        }

        foreach ($this->subQuestions as $subQuestion)
        {
            $myfname = $this->sgqa.$subQuestion->title;
            $trbc='';
            /* Check for array_filter */

            if($label_width < strlen(trim(strip_tags($subQuestion->question))))
            {
                $label_width = strlen(trim(strip_tags($subQuestion->question)));
            }

            $myfname2 = $myfname."comment";
            /* Print out the checkbox */
            $answer_main .= $startitem;
            $answer_main .= "<span class=\"option\">\n"
                . "\t<input class=\"checkbox\" type=\"checkbox\" name=\"$myfname\" id=\"answer$myfname\" value=\"Y\"";

            /* If the question has already been ticked, check the checkbox */
            if (isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname]))
            {
                if ($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname] == 'Y')
                {
                    $answer_main .= CHECKED;
                }
            }
            $answer_main .=" onclick='$checkconditionFunction(this.value, this.name, this.type);' />\n"
                . "\t<label for=\"answer$myfname\" class=\"answertext\">\n"
                . $subQuestion->question."</label>\n";

            $answer_main .= "<input type='hidden' name='java$myfname' id='java$myfname' value='";
            if (isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname]))
            {
                $answer_main .= $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname];
            }
            $answer_main .= "' />\n";
            $fn++;
            $answer_main .= "</span>\n<span class=\"comment\">\n\t<label for='answer$myfname2' class=\"answer-comment hide \">".gT('Make a comment on your choice here:')."</label>\n"
                ."<input class='text ".$kpclass."' type='text' size='40' id='answer$myfname2' name='$myfname2' value='";
            if (isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname2])) {$answer_main .= htmlspecialchars($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname2],ENT_QUOTES);}
            $answer_main .= "' onkeyup='$checkconditionFunction(this.value,this.name,this.type);' />\n</span>\n"
                . "\t</li>\n";

            $fn++;
            $inputnames[]=$myfname;
            $inputnames[]=$myfname2;
        }
        if ($other == 'Y')
        {
            $myfname = $this->sgqa.'other';
            $myfname2 = $myfname.'comment';
            $anscount = $anscount + 2;
            $answer_main .= "\t<li class=\"other question-item answer-item checkbox-text-item other-item\" id=\"javatbd$myfname\">\n<span class=\"option\">\n"
                . "\t<label for=\"answer$myfname\" class=\"answertext\">\n".$otherText."\n<input class=\"text other ".$kpclass."\" type=\"text\" name=\"$myfname\" id=\"answer$myfname\" title=\"".gT('Other').'" size="10"';
            $answer_main .= " onkeyup='$oth_checkconditionFunction(this.value, this.name, this.type);'";
            if (isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname]) && $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname])
            {
                $dispVal = $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname];
                if ($this->other_numbers_only==1)
                {
                    $dispVal = str_replace('.',$sSeparator,$dispVal);
                }
                $answer_main .= ' value="'.htmlspecialchars($dispVal,ENT_QUOTES).'"';
            }
            $fn++;
            // --> START NEW FEATURE - SAVE
            $answer_main .= " />\n\t</label>\n</span>\n"
                . "<span class=\"comment\">\n\t<label for=\"answer$myfname2\" class=\"answer-comment hide\">".gT('Make a comment on your choice here:')."\t</label>\n"
                . '<input class="text '.$kpclass.'" type="text" size="40" name="'.$myfname2.'" id="answer'.$myfname2.'"'
                . " onkeyup='$checkconditionFunction(this.value,this.name,this.type);'"
                . ' title="'.gT('Make a comment on your choice here:').'" value="';
            // --> END NEW FEATURE - SAVE

            if (isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname2])) {$answer_main .= htmlspecialchars($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname2],ENT_QUOTES);}
            $answer_main .= "\"/>\n";
            $answer_main .= "</span>\n\t</li>\n";

            $inputnames[]=$myfname;
            $inputnames[]=$myfname2;
        }
        $html .= "<ul class=\"subquestions-list questions-list checkbox-text-list\">\n".$answer_main."</ul>\n";
        if($this->commented_checkbox!="allways" && $this->commented_checkbox_auto)
        {
            Yii::app()->getClientScript()->registerScriptFile(Yii::app()->getConfig('generalscripts')."multiplechoice_withcomments.js");
#        $script= " doMultipleChoiceWithComments({$this->primaryKey},'{$this->commented_checkbox}');\n";
#        App()->getClientScript()->registerScript("doMultipleChoiceWithComments",$script,CClientScript::POS_HEAD);// Deactivate now: need to be after question, and just after
            $html .= "<script type='text/javascript'>\n"
                . "  /*<![CDATA[*/\n"
                ." doMultipleChoiceWithComments({$this->primaryKey},'{$this->commented_checkbox}');\n"
                ." /*]]>*/\n"
                ."</script>\n";
        }
        $result->setHtml($html);
        return $result;
    }


}