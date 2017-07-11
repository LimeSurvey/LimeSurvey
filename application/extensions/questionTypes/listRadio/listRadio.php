<?php

class listRadio extends CApplicationComponent
{
    public function doQuestion($ia)
    {
        global $thissurvey;
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

        $aQuestionAttributes = QuestionAttribute::model()->getQuestionAttributes($ia[0]);

        $query = "SELECT other FROM {{questions}} WHERE qid=".$ia[0]." AND language='".$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']."' ";
        $result = Yii::app()->db->createCommand($query)->query();
        foreach ($result->readAll() as $row)
        {
            $other = $row['other'];
        }

        //question attribute random order set?
        if ($aQuestionAttributes['random_order']==1) {
            $ansquery = "SELECT * FROM {{answers}} WHERE qid=$ia[0] AND language='".$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']."' and scale_id=0 ORDER BY ".dbRandom();
        }

        //question attribute alphasort set?
        elseif ($aQuestionAttributes['alphasort']==1)
        {
            $ansquery = "SELECT * FROM {{answers}} WHERE qid=$ia[0] AND language='".$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']."' and scale_id=0 ORDER BY answer";
        }

        //no question attributes -> order by sortorder
        else
        {
            $ansquery = "SELECT * FROM {{answers}} WHERE qid=$ia[0] AND language='".$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']."' and scale_id=0 ORDER BY sortorder, answer";
        }

        $ansresult = dbExecuteAssoc($ansquery)->readAll();  //Checked
        $anscount = count($ansresult);

        if (trim($aQuestionAttributes['display_columns'])!='')
        {
            $dcols = $aQuestionAttributes['display_columns'];
        }
        else
        {
            $dcols= 1;
        }

        if (trim($aQuestionAttributes['other_replace_text'][$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']])!='')
        {
            $othertext=$aQuestionAttributes['other_replace_text'][$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']];
        }
        else
        {
            $othertext=gT('Other:');
        }

        if (isset($other) && $other=='Y') {$anscount++;} //Count up for the Other answer
        if ($ia[6] != 'Y' && SHOW_NO_ANSWER == 1) {$anscount++;} //Count up if "No answer" is showing

        $wrapper = setupColumns($dcols , $anscount,"answers-list radio-list","answer-item radio-item");

        $iBootCols = round(12/$dcols);
        $ansByCol = round($anscount/$dcols); $ansByCol = ($ansByCol > 0)?$ansByCol:1;

        //$answer = 'IKI: '.$iBootCols.' '.$ansByCol.' '.$wrapper['whole-start'];
        $answer = '<div class="row">';
        $answer .= '    <div class="col-xs-'.$iBootCols.'">AAAAAAAAAAAAAA';

        //Time Limit Code
        if (trim($aQuestionAttributes['time_limit'])!='')
        {
            $answer .= return_timer_script($aQuestionAttributes, $ia);
        }
        //End Time Limit Code

        // Get array_filter stuff

        $rowcounter = 0;
        $colcounter = 1;
        $trbc='';

        foreach ($ansresult as $key=>$ansrow)
        {
            $myfname = $ia[1].$ansrow['code'];
            $check_ans = '';
            if ($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$ia[1]] == $ansrow['code'])
            {
                $check_ans = CHECKED;
            }
            list($htmltbody2, $hiddenfield)=return_array_filter_strings($ia, $aQuestionAttributes, $thissurvey, $ansrow, $myfname, '', $myfname, "div","form-group answer-item radio-item");
        /*    if(substr($wrapper['item-start'],0,4) == "\t<li")
            {
                $startitem = "\t$htmltbody2\n";
            } else {
                $startitem = $wrapper['item-start'];
            }

            $answer .= $startitem;*/
            $answer .= "\t$hiddenfield\n";

            $answer .= '<div  class="form-group">';
            $answer .= '    <label for="answer'.$ia[1].$ansrow['code'].'" class="answertext control-label">'.$ansrow['answer'].'</label>';
            $answer .= '        <input class="radio" type="radio" value="'.$ansrow['code'].'" name="'.$ia[1].'" id="answer'.$ia[1].$ansrow['code'].'"'.$check_ans.' onclick="if (document.getElementById(\'answer'.$ia[1].'othertext\') != null) document.getElementById(\'answer'.$ia[1].'othertext\').value=\'\';'.$checkconditionFunction.'(this.value, this.name, this.type)" />';
            $answer .=          $wrapper['item-end'];
            $answer .= '</div>';

            ++$rowcounter;
            //if ($rowcounter == $wrapper['maxrows'] && $colcounter < $wrapper['cols'] || (count($ansresult)-$key)==$wrapper['cols']-$colcounter)
            if ($rowcounter == $ansByCol && $colcounter < $wrapper['cols'])
            {
                if($colcounter == $wrapper['cols'] )
                {
                    //$answer .= 'là '.$wrapper['col-devide-last'];
                    $answer .= '    </div><!-- last -->';
                }
                else
                {
                    //$answer .= 'et là '.$wrapper['col-devide'];
                    $answer .= '    </div><!-- devide --> ';
                    $answer .= '    <div class="col-xs-'.$iBootCols.'">';
                }
                $rowcounter = 0;
                ++$colcounter;
            }
        }

        if (isset($other) && $other=='Y')
        {

            $sSeparator = getRadixPointData($thissurvey['surveyls_numberformat']);
            $sSeparator = $sSeparator['separator'];

            if ($aQuestionAttributes['other_numbers_only']==1)
            {
                $oth_checkconditionFunction = 'fixnum_checkconditions';
            }
            else
            {
                $oth_checkconditionFunction = 'checkconditions';
            }


            if ($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$ia[1]] == '-oth-')
            {
                $check_ans = CHECKED;
            }
            else
            {
                $check_ans = '';
            }

            $thisfieldname=$ia[1].'other';
            if (isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$thisfieldname]))
            {
                $dispVal = $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$thisfieldname];
                if ($aQuestionAttributes['other_numbers_only']==1)
                {
                    $dispVal = str_replace('.',$sSeparator,$dispVal);
                }
                $answer_other = ' value="'.htmlspecialchars($dispVal,ENT_QUOTES).'"';
            }
            else
            {
                $answer_other = ' value=""';
            }

            list($htmltbody2, $hiddenfield)=return_array_filter_strings($ia, $aQuestionAttributes, $thissurvey, array("code"=>"other"), $thisfieldname, $trbc, $myfname, "div", "form-group answer-item radio-item other-item other");

            $answer .= "\t$hiddenfield\n";
            $answer .= '<div  class="form-group">';
            $answer .= '    <label for="SOTH'.$ia[1].'" class="answertext control-label">'.$othertext.'</label>';
            $answer .= '    <input class="radio" type="radio" value="-oth-" name="'.$ia[1].'" id="SOTH'.$ia[1].'"'.$check_ans.' onclick="'.$checkconditionFunction.'(this.value, this.name, this.type)" />';
            $answer .= '    <input type="text" class="text '.$kpclass.'" id="answer'.$ia[1].'othertext" name="'.$ia[1].'other" title="'.gT('Other').'"'.$answer_other.' onkeyup="if($.trim($(this).val())!=\'\'){ $(\'#SOTH'.$ia[1].'\').click(); }; '.$oth_checkconditionFunction.'(this.value, this.name, this.type);" />';
            $answer .=      $wrapper['item-end'];
            $answer .= '</div>';
            $inputnames[]=$thisfieldname;

            ++$rowcounter;
            if ($rowcounter == $ansByCol && $colcounter < $wrapper['cols'])
            {
                if($colcounter == $wrapper['cols'] )
                {
                    $answer .= '    </div><!-- last -->';
                }
                else
                {
                    $answer .= '    </div><!-- devide -->';
                    $answer .= '    <div class="col-xs-'.$iBootCols.'">';
                }
                $rowcounter = 0;
                ++$colcounter;
            }
        }

        if ($ia[6] != 'Y' && SHOW_NO_ANSWER == 1)
        {
            if ((!isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$ia[1]]) || $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$ia[1]] == '') || ($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$ia[1]] == ' ' ))
            {
                $check_ans = CHECKED; //Check the "no answer" radio button if there is no answer in session.
            }
            else
            {
                $check_ans = '';
            }

            $answer .= '<div  class="form-group">';
            $answer .= '    <label for="answer'.$ia[1].'" class="answertext control-label">'.gT('No answer').'</label>';
            $answer .= '        <input class="radio" type="radio" name="'.$ia[1].'" id="answer'.$ia[1].'" value=""'.$check_ans.' onclick="if (document.getElementById(\'answer'.$ia[1].'othertext\') != null) document.getElementById(\'answer'.$ia[1].'othertext\').value=\'\';'.$checkconditionFunction.'(this.value, this.name, this.type)" />';
            $answer .=          $wrapper['item-end'];
            $answer .= '</div>';

            // --> END NEW FEATURE - SAVE

            ++$rowcounter;
            //if ($rowcounter == $wrapper['maxrows'] && $colcounter < $wrapper['cols'])
            if ($rowcounter == $ansByCol && $colcounter < $wrapper['cols'])
            {
                if($colcounter == $wrapper['cols'] )
                {
                    //$answer .= $wrapper['col-devide-last'];
                    $answer .= '    </div><!-- last -->';
                }
                else
                {
                    //$answer .= $wrapper['col-devide'];
                    $answer .= '    </div><!-- devide -->';
                    $answer .= '    <div class="col-xs-'.$iBootCols.'">';
                }
                $rowcounter = 0;
                ++$colcounter;
            }

        }
        //END OF ITEMS
        //$answer .= $wrapper['whole-end'].'
        $answer .= '    <input type="hidden" name="java'.$ia[1].'" id="java'.$ia[1]."\" value=\"".$_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$ia[1]]."\" />\n";
        $answer .= '</div> <!-- wrapper row -->';

        $inputnames[]=$ia[1];
        return array($answer, $inputnames);

    }
}
