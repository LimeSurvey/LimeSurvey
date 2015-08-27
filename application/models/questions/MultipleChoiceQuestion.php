<?php
namespace ls\models\questions;

use ls\interfaces\iResponse;

/**
 * Class MultipleChoiceQuestion
 * @package ls\models\questions
 */
class MultipleChoiceQuestion extends ChoiceQuestion
{
    public function getSubQuestionScales()
    {
        return 1;
    }

    /**
     * This function return the class by question type
     * @param string question type
     * @return string Question class to be added to the container
     */
    public function getClasses()
    {
        $result = parent::getClasses();
        $result[] = 'multiple-opt';
        return $result;
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
     * @return QuestionResponseField[]
     */
    public function getFields() {
        $result = [];
        foreach ($this->subQuestions as $subQuestion) {
            $result[] = $field = new \QuestionResponseField($this->sgqa . $subQuestion->title, "{$this->title}_{$subQuestion->title}", $this);
            /**
             * @todo Include subquestion relevance.
             */
            $field->setRelevanceScript($this->relevanceScript);
            $field->setLabels([
                'Y' => $subQuestion->question
            ]);
        }
        return $result;
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
        // Find out if any questions have attributes which reference this questions
        // based on value of attribute. This could be array_filter and array_filter_exclude
        $othertext = trim($this->other_replace_text)!='' ? $this->other_replace_text : gT('Other:');

        if (trim($this->display_columns)!='')
        {
            $dcols = $this->display_columns;
        }
        else
        {
            $dcols = 1;
        }

        if ($this->other_numbers_only==1)
        {
            $sSeparator = getRadixPointData($this->survey->getLocalizedNumberFormat());
            $sSeparator= $sSeparator['separator'];
            $oth_checkconditionFunction = "fixnum_checkconditions";
        }
        else
        {
            $oth_checkconditionFunction = "checkconditions";
        }

        if (trim($this->exclude_all_others)!='' && $this->random_order==1)
        {
            //if  exclude_all_others is set then the related answer should keep its position at all times
            //thats why we have to re-position it if it has been randomized
            $position=0;
            foreach ($this->subQuestions as $html)
            {
                if ((trim($this->exclude_all_others) != '')  &&    ($html['title']==trim($this->exclude_all_others)))
                {
                    if ($position==$html['question_order']-1) break; //already in the right position
                    $tmp  = array_splice($ansresult, $position, 1);
                    array_splice($ansresult, $html['question_order']-1, 0, $tmp);
                    break;
                }
                $position++;
            }
        }

        $anscount = count($this->subQuestions);
        if ($this->bool_other)
        {
            $anscount++; //COUNT OTHER AS AN ANSWER FOR MANDATORY CHECKING!
        }

        $wrapper = setupColumns($dcols, $anscount,"subquestions-list questions-list checkbox-list","question-item answer-item checkbox-item");

        $html = '<input type="hidden" name="MULTI'.$this->sgqa.'" value="'.$anscount."\" />\n\n".$wrapper['whole-start'];

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
        foreach ($this->subQuestions as $subQuestion)
        {
            $myfname = $this->sgqa.$subQuestion->title;
            $extra_class="";

            $trbc='';
            /* Print out the checkbox */
            $html .= $startitem;
            $html .= \TbHtml::hiddenField($this->sgqa.$subQuestion->title, 'N');
            $html .= '        <input class="checkbox" type="checkbox" name="'.$this->sgqa.$subQuestion->title.'" id="answer'.$this->sgqa.$subQuestion->title.'" value="Y"';

            /* If the question has already been ticked, check the checkbox */
            if (isset($response->$myfname))
            {
                if ($response->$myfname == 'Y')
                {
                    $html .= 'checked="checked"';
                }
            }
            $html .= ''
                .  " />\n"
                .  "<label for=\"answer$this->sgqa{$subQuestion->title}\" class=\"answertext\">"
                .  $subQuestion->question
                .  "</label>\n";


            //        if ($maxansw > 0) {$maxanswscript .= "\tif (document.getElementById('answer".$myfname."').checked) { count += 1; }\n";}
            //        if ($minansw > 0) {$minanswscript .= "\tif (document.getElementById('answer".$myfname."').checked) { count += 1; }\n";}

            ++$fn;
            ++$rowcounter;
            if ($rowcounter == $wrapper['maxrows'] && $colcounter < $wrapper['cols'])
            {
                if($colcounter == $wrapper['cols'] - 1)
                {
                    $html .= $wrapper['col-devide-last'];
                }
                else
                {
                    $html .= $wrapper['col-devide'];
                }
                $rowcounter = 0;
                ++$colcounter;
            }
        }

        if ($this->bool_other)
        {
            $myfname = $this->sgqa.'other';
            list($htmltbody2, $hiddenfield)=return_array_filter_strings($this, array("code" => "other"), $myfname,
                $trbc, $myfname, "li", "question-item answer-item checkbox-item other-item");

            if(substr($wrapper['item-start-other'],0,4) == "\t<li")
            {
                $startitem = "\t$htmltbody2\n";
            } else {
                $startitem = $wrapper['item-start-other'];
            }
            $html .= $startitem;
            $html .= $hiddenfield.'
        <input class="checkbox other-checkbox dontread" style="visibility:hidden" type="checkbox" name="'.$myfname.'cbox" id="answer'.$myfname.'cbox"';
            // othercbox can be not display, because only input text goes to database

            if (isset($response->$myfname) && !empty(trim($response->$myfname)))
            {
                $html .= 'checked="checked"';
            }
            $html .= " />
        <label for=\"answer$myfname\" class=\"answertext\">".$othertext."</label>
        <input class=\"text ".$kpclass."\" type=\"text\" name=\"$myfname\" id=\"answer$myfname\" value=\"";
            if (isset($response->$myfname))
            {
                $dispVal = $response->$myfname;
                if ($this->other_numbers_only==1)
                {
                    $dispVal = str_replace('.',$sSeparator,$dispVal);
                }
                $html .= htmlspecialchars($dispVal,ENT_QUOTES);
            }
            $html .="\" />\n";
            $html .="<script type='text/javascript'>\n/*<![CDATA[*/\n";
            $html .="$('#answer{$myfname}cbox').prop('aria-hidden', 'true').css('visibility','');";
            $html .="$('#answer{$myfname}').bind('keyup focusout',function(event){\n";
            $html .= " if ($.trim($(this).val()).length>0) { $(\"#answer{$myfname}cbox\").prop(\"checked\",true); } else { \$(\"#answer{$myfname}cbox\").prop(\"checked\",false); }; $(\"#java{$myfname}\").val($(this).val());LEMflagMandOther(\"$myfname\",$('#answer{$myfname}cbox').is(\":checked\")); $oth_checkconditionFunction(this.value, this.name, this.type); \n";
            $html .="});\n";
            $html .="$('#answer{$myfname}cbox').click(function(event){\n";
            $html .= " if (($(this)).is(':checked') && $.trim($(\"#answer{$myfname}\").val()).length==0) { $(\"#answer{$myfname}\").focus();LEMflagMandOther(\"$myfname\",true);return false; } else {  $(\"#answer{$myfname}\").val('');{$checkconditionFunction}(\"\", \"{$myfname}\", \"text\");LEMflagMandOther(\"$myfname\",false); return true; }; \n";
            $html .="});\n";
            $html .="/*]]>*/\n</script>\n";
            $html .= '<input type="hidden" name="java'.$myfname.'" id="java'.$myfname.'" value="';


            if (isset($response->$myfname))
            {
                $dispVal = $response->$myfname;
                if ($this->other_numbers_only==1)
                {
                    $dispVal = str_replace('.',$sSeparator,$dispVal);
                }
                $html .= htmlspecialchars($dispVal,ENT_QUOTES);
            }

            $html .= "\" />\n{$wrapper['item-end']}";
            $inputnames[]=$myfname;
            ++$anscount;

            ++$rowcounter;
            if ($rowcounter == $wrapper['maxrows'] && $colcounter < $wrapper['cols'])
            {
                if($colcounter == $wrapper['cols'] - 1)
                {
                    $html .= $wrapper['col-devide-last'];
                }
                else
                {
                    $html .= $wrapper['col-devide'];
                }
                $rowcounter = 0;
                ++$colcounter;
            }
        }
        $html .= $wrapper['whole-end'];


        $html .= $postrow;
        $result->setHtml($html);
        return $result;
    }


}