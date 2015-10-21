<?php
namespace ls\models\questions;


class DropDownListQuestion extends SingleChoiceQuestion
{
    /**
     * This function renders the object.
     * It MUST NOT produce any output.
     * It should return a string or an object that can be converted to string.
     * @param \ls\interfaces\Response $response
     * @param \ls\components\SurveySession $session
     * @return \ls\components\RenderedQuestion
     */
    public function render(\ls\interfaces\ResponseInterface $response, \ls\components\SurveySession $session)
    {
        $result = parent::render($response, $session);

        $em = $this->getExpressionManager($response);

        $createOption = function(\ls\models\Answer $answer) use ($em, $response) {
            // Get the parts for the answer, we construct one EM expression from the whole.
            $parts = $em->asSplitStringOnExpressions($answer->answer);
            $expressionParts = [];
            $text = '';
            $hasExpression = false;
            foreach ($parts as $part) {
                switch ($part[2]) {
                    case 'STRING':
                        $expressionParts[] = "'{$part[0]}'";
                        $text .= $part[0];
                        break;
                    case 'EXPRESSION':
                        $hasExpression = true;
                        if ($em->RDP_Evaluate(substr($part[0], 1, -1))) {
                            $value = $em->GetResult();
                        } else {
                            $value = '';
                        }
                        $text .= $value;
                        $expressionParts[] = substr($part[0], 1, -1);
                        $text .= $value;
                }
            }
            $result = \CHtml::tag('option', [
                'value' => $answer->code,
                'selected' => $response->{$this->sgqa} == $answer->code,
                'data-expression' => $hasExpression ? $em->getJavascript('join(' . implode(',', $expressionParts) . ')') : null
            ], $text);
            return $result;
        };

        $html = \CHtml::openTag('select');
        foreach($this->answers as $answer) {
            $html .= $createOption($answer);
        }
        $html .= \CHtml::closeTag('select');

        $result->setHtml($html);
        return $result;
        //------------------------------------------------------------------------------------------------------------//
        //------------------------------------------------------------------------------------------------------------//
        // OLD IMPLEMENTATION BELOW.
        //------------------------------------------------------------------------------------------------------------//
        //------------------------------------------------------------------------------------------------------------//
        $columns = is_numeric($this->display_columns) ? $this->display_columns : 1;
        if (!empty($this->other_replace_text)) {
            $otherText=$this->other_replace_text;
        } else {
            $otherText=gT('Other:');
        }

        if ($this->bool_other) {$anscount++;} //Count up for the Other answer
        if (!$this->bool_mandatory && $this->survey->bool_shownoanswer) {$anscount++;} //Count up if "No answer" is showing

        $wrapper = setupColumns($dcols , $anscount,"answers-list radio-list","answer-item radio-item");
        $html = $wrapper['whole-start'];

        //Time Limit Code
        if (trim($this->time_limit)!='')
        {
            $html .= return_timer_script($this, $ia);
        }
        //End Time Limit Code

        // Get array_filter stuff

        $rowcounter = 0;
        $colcounter = 1;
        $trbc='';

        foreach ($ansresult as $key=>$subQuestion)
        {
            $myfname = $this->sgqa . $subQuestion['code'];
            $check_ans = '';
            if (App()->surveySessionManager->current->response->{$this->sgqa} == $subQuestion['code'])
            {
                $check_ans = CHECKED;
            }

            list($htmltbody2, $hiddenfield) = return_array_filter_strings($this, null, $myfname, $trbc, $myfname, "li",
                "answer-item radio-item");
            if(substr($wrapper['item-start'],0,4) == "\t<li")
            {
                $startitem = "\t$htmltbody2\n";
            } else {
                $startitem = $wrapper['item-start'];
            }

            $html .= $startitem;
            $html .= "\t$hiddenfield\n";
            $html .='        <input class="radio" type="radio" value="'.$subQuestion['code'].'" name="'. $this->sgqa .'" id="answer'.$this->sgqa.$subQuestion['code'].'"'.$check_ans.' onclick="$(this).closest(\'ul\').find(\'input[type=text]\').val(\'\').trigger(\'change\');'.$checkconditionFunction.'(this.value, this.name, this.type)" />
        <label for="answer'.$this->sgqa.$subQuestion['code'].'" class="answertext">'.$subQuestion['answer'].'</label>
        '.$wrapper['item-end'];

            ++$rowcounter;
            if ($rowcounter == $wrapper['maxrows'] && $colcounter < $wrapper['cols'] || (count($ansresult)-$key)==$wrapper['cols']-$colcounter)
            {
                if($colcounter == $wrapper['cols'] - 1 )
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

        if ($this->bool_other) {

            $sSeparator = \ls\helpers\SurveyTranslator::getRadixPointData($session->survey->getLocalizedNumberFormat());
            $sSeparator = $sSeparator['separator'];

            if ($this->other_numbers_only==1)
            {
                $oth_checkconditionFunction = 'fixnum_checkconditions';
            }
            else
            {
                $oth_checkconditionFunction = 'checkconditions';
            }

            $check_ans = App()->surveySessionManager->current->response->{$this->sgqa} == '-oth-' ? CHECKED : '';


            $thisfieldname=$this->sgqa.'other';
            if (isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$thisfieldname]))
            {
                $dispVal = $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$thisfieldname];
                if ($this->other_numbers_only==1)
                {
                    $dispVal = str_replace('.',$sSeparator,$dispVal);
                }
                $answer_other = ' value="'.htmlspecialchars($dispVal,ENT_QUOTES).'"';
            }
            else
            {
                $answer_other = ' value=""';
            }

            list($htmltbody2, $hiddenfield)=return_array_filter_strings($this, ['sid' => $session->surveyId],
                $thisfieldname, $trbc, $myfname, "li", "answer-item radio-item other-item other");

            if(substr($wrapper['item-start-other'],0,4) == "\t<li")
            {
                $startitem = "\t$htmltbody2\n";
            } else {
                $startitem = $wrapper['item-start-other'];
            }
            $html .= $startitem;
            $html .= "\t$hiddenfield\n";
            $html .= '        <input class="radio" type="radio" value="-oth-" name="'.$this->sgqa.'" id="SOTH'.$this->sgqa.'"'.$check_ans.' onclick="'.$checkconditionFunction.'(this.value, this.name, this.type)" />
        <label for="SOTH'.$this->sgqa.'" class="answertext">'.$otherText.'</label>
        <label for="answer'.$this->sgqa.'othertext">
        <input type="text" class="text '.$kpclass.'" id="answer'.$this->sgqa.'othertext" name="'.$this->sgqa.'other" title="'.gT('Other').'"'.$answer_other.' onkeyup="if($.trim($(this).val())!=\'\'){ $(\'#SOTH'.$this->sgqa.'\').click(); }; '.$oth_checkconditionFunction.'(this.value, this.name, this.type);" />
        </label>
        '.$wrapper['item-end'];

            $inputnames[]=$thisfieldname;

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

        if ($this->bool_mandatory && $this->survey->bool_shownoanswer)
        {
            $html .= $wrapper['item-start-noanswer'].'        <input class="radio" type="radio" name="'.$this->sgqa.'" id="answer'.$this->sgqa.'NANS" value=""'.$check_ans.' onclick="\'$(this).closest(\'ul\').find(\'input[type=text]\').val(\'\').trigger(\'change\');'.$checkconditionFunction.'(this.value, this.name, this.type)" />
        <label for="answer'.$this->sgqa.'NANS" class="answertext">'.gT('No answer').'</label>
        '.$wrapper['item-end'];
            // --> END NEW FEATURE - SAVE

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
        //END OF ITEMS
        $html .= $wrapper['whole-end'].


            $result->setHtml($html);
        return $result;
    }

    /**
     * Return the classes to be added to the question wrapper.
     * @return []
     */
    public function getClasses()
    {
        $result = parent::getClasses();
        $result[] = 'list-dropdown';
        return $result;
    }


}