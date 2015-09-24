<?php
namespace ls\models\questions;

class MultipleTextQuestion extends TextQuestion
{
    public function getSubQuestionScales()
    {
        return 1;
    }

    public function getColumns()
    {
        $result = call_user_func_array('array_merge', array_map(function (\Question $subQuestion) {
            $subResult = [];
            foreach ($subQuestion->columns as $name => $type) {
                $subResult[$this->sgqa . $name] = $type;
            }
            return $subResult;
        }, $this->subQuestions));
        return $result;
    }


    /**
     * Returns the fields for this question.
     * @return \ls\components\QuestionResponseField[]
     */
    public function getFields() {
        foreach($this->subQuestions as $subQuestion) {
            $fields[] = $field = new \ls\components\QuestionResponseField($this->sgqa . $subQuestion->title, "{$this->title}_{$subQuestion->title}", $this);
            $field->setRelevanceScript($this->getRelevanceScript() . ' && ' .$subQuestion->getRelevanceScript());
        }

        return $fields;


    }

    /**
     * This function return the class by question type
     * @param string question type
     * @return string Question class to be added to the container
     */
    public function getClasses()
    {
        $result = parent::getClasses();
        $result[] = 'multiple-short-txt';
        return $result;
    }

    /**
     * This function renders the object.
     * It MUST NOT produce any output.
     * It should return a string or an object that can be converted to string.
     * @param \ls\interfaces\Response $response
     * @param \ls\components\SurveySession $session
     * @return \ls\components\RenderedQuestion
     */
    public function render(\ls\interfaces\iResponse $response, \ls\components\SurveySession $session)
    {
        $result = parent::render($response, $session);
        $html ='';
        if ($this instanceof MultipleNumberQuestion) {
            $result->setHtml(' ');
            return $result;
        }
        foreach($this->subQuestions as $subQuestion) {
            $html .= $this->renderSubQuestion($subQuestion, $response, $session);
        }
        $result->setHtml($html);
        return $result;
    }

    public function renderSubQuestion(\Question $question, \ls\interfaces\iResponse $response, \ls\components\SurveySession $session) {
        

        $extraclass ="";
        $html='';


        if ($this->numbers_only == 1)
        {
            $sSeparator = \ls\helpers\SurveyTranslator::getRadixPointData($this->survey->localizedNumberFormat)['separator'];
            $extraclass .=" numberonly";
        }
        if (intval(trim($this->maximum_chars))>0)
        {
            // Only maxlength attribute, use textarea[maxlength] jquery selector for textarea
            $maximum_chars= intval(trim($this->maximum_chars));
            $maxlength= "maxlength='{$maximum_chars}' ";
            $extraclass .=" maxchars maxchars-".$maximum_chars;
        }
        else
        {
            $maxlength= "";
        }
        if (trim($this->text_input_width)!='')
        {
            $tiwidth=$this->text_input_width;
            $extraclass .=" inputwidth".trim($this->text_input_width);
        }
        else
        {
            $tiwidth=20;
        }

        if (trim($this->prefix)!='') {
            $prefix=$this->prefix;
            $extraclass .=" withprefix";
        } else {
            $prefix = '';
        }

        if (trim($this->suffix)!='') {
            $suffix=$this->suffix;
            $extraclass .=" withsuffix";
        }
        else
        {
            $suffix = '';
        }

        $fn = 1;

        $answer_main = '';

        $label_width = 0;

        if (trim($this->display_rows)!='')
        {
            //question attribute "display_rows" is set -> we need a textarea to be able to show several rows
            $drows=$this->display_rows;

            foreach ($this->subQuestions as $subQuestion)
            {
                $myfname = $this->sgqa.$subQuestion->title;
                if ($subQuestion->question == "")
                {
                    $subQuestion->question = "&nbsp;";
                }

                //NEW: textarea instead of input=text field
                list($htmltbody2, $hiddenfield)=return_array_filter_strings($this, $subQuestion, $myfname, '', $myfname,
                    "li", "question-item answer-item text-item" . $extraclass);

                $answer_main .= "\t$htmltbody2\n"
                    . "<label for=\"answer$myfname\">{$subQuestion->question}</label>\n"
                    . "\t<span>\n".$prefix."\n".'
            <textarea class="textarea '.$kpclass.'" name="'.$myfname.'" id="answer'.$myfname.'"
            rows="'.$drows.'" cols="'.$tiwidth.'" '.$maxlength.' onkeyup="'.$checkconditionFunction.'(this.value, this.name, this.type);">';

                if($label_width < strlen(trim(strip_tags($subQuestion->question))))
                {
                    $label_width = strlen(trim(strip_tags($subQuestion->question)));
                }

                if (isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname]))
                {
                    $dispVal = $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname];
                    if ($this->numbers_only==1)
                    {
                        $dispVal = str_replace('.',$sSeparator,$dispVal);
                    }
                    $answer_main .= $dispVal;
                }

                $answer_main .= "</textarea>\n".$suffix."\n\t</span>\n"
                    . "\t</li>\n";

                $fn++;
                $inputnames[]=$myfname;
            }

        }
        else
        {
            foreach ($this->subQuestions as $subQuestion)
            {
                $myfname = $this->sgqa.$subQuestion->title;
                // color code missing mandatory questions red
                if ($this->bool_mandatory && !$this->validateResponse($response)->getPassedMandatory()) {
                    $subQuestion->question = "<span class='errormandatory'>{$subQuestion->question}</span>";
                }

                list($htmltbody2, $hiddenfield)=return_array_filter_strings($this, $thissurvey, $myfname, '', $myfname,
                    "li", "question-item answer-item text-item" . $extraclass);
                $answer_main .= "\t$htmltbody2\n"
                    . "<label for=\"answer$myfname\">{$subQuestion->question}</label>\n"
                    . "\t<span>\n".$prefix."\n".'<input class="text '.$kpclass.'" type="text" size="'.$tiwidth.'" name="'.$myfname.'" id="answer'.$myfname.'" value="';

                if($label_width < strlen(trim(strip_tags($subQuestion->question))))
                {
                    $label_width = strlen(trim(strip_tags($subQuestion->question)));
                }

                if (isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname]))
                {
                    $dispVal = $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname];
                    if ($this->numbers_only==1)
                    {
                        $dispVal = str_replace('.',$sSeparator,$dispVal);
                    }
                    $answer_main .= htmlspecialchars($dispVal,ENT_QUOTES,'UTF-8');
                }

                // --> START NEW FEATURE - SAVE
                $answer_main .= '" onkeyup="'.$checkconditionFunction.'(this.value, this.name, this.type);" '.$maxlength.' />'."\n".$suffix."\n\t</span>\n"
                    . "\t</li>\n";
                // --> END NEW FEATURE - SAVE

                $fn++;
                $inputnames[]=$myfname;
            }

        }

        $html = "<ul class=\"subquestions-list questions-list text-list\">\n".$answer_main."</ul>\n";

        return $html;
    }

    /**
     * Does this question support custom subquestions?
     * @return boolean
     */
    public function getHasCustomSubQuestions()
    {
        return true;
    }


}