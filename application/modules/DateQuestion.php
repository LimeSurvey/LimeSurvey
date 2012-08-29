<?php
class DateQuestion extends QuestionModule
{
    public function getAnswerHTML()
    {
        global $thissurvey;

        $clang=Yii::app()->lang;

        $aQuestionAttributes=$this->getAttributeValues();

        $checkconditionFunction = "checkconditions";

        $dateformatdetails = getDateFormatData($thissurvey['surveyls_dateformat']);
        $numberformatdatat = getRadixPointData($thissurvey['surveyls_numberformat']);

        if (trim($aQuestionAttributes['dropdown_dates'])==1) {
            if (!empty($_SESSION['survey_'.$this->surveyid][$this->fieldname]))
            {
                $datetimeobj = getdate(DateTime::createFromFormat("Y-m-d H:i:s", $_SESSION['survey_'.$this->surveyid][$this->fieldname])->getTimeStamp());
                $currentyear = $datetimeobj['year'];
                $currentmonth = $datetimeobj['mon'];
                $currentdate = $datetimeobj['mday'];
                $currenthour = $datetimeobj['hours'];
                $currentminute = $datetimeobj['minutes'];
            } else {
                $currentdate='';
                $currentmonth='';
                $currentyear='';            
                $currenthour = '';
                $currentminute = '';
            }

            $dateorder = preg_split('/([-\.\/ :])/', $dateformatdetails['phpdate'],-1,PREG_SPLIT_DELIM_CAPTURE);
            $answer='<p class="question answer-item dropdown-item date-item">';
            foreach($dateorder as $datepart)
            {
                switch($datepart)
                {
                    // Show day select box
                    case 'j':
                    case 'd':   $answer .= '<label for="day'.$this->fieldname.'" class="hide">'.$clang->gT('Day').'</label><select id="day'.$this->fieldname.'" name="day'.$this->fieldname.'" class="day">
                        <option value="">'.$clang->gT('Day')."</option>\n";
                        for ($i=1; $i<=31; $i++) {
                            if ($i == $currentdate)
                            {
                                $i_date_selected = SELECTED;
                            }
                            else
                            {
                                $i_date_selected = '';
                            }
                            $answer .= '<option value="'.sprintf('%02d', $i).'"'.$i_date_selected.'>'.sprintf('%02d', $i)."</option>\n";
                        }
                        $answer .='</select>';
                        break;
                        // Show month select box
                    case 'n':
                    case 'm':   $answer .= '<label for="month'.$this->fieldname.'" class="hide">'.$clang->gT('Month').'</label><select id="month'.$this->fieldname.'" name="month'.$this->fieldname.'" class="month">
                        <option value="">'.$clang->gT('Month')."</option>\n";
                        $montharray=array(
                        $clang->gT('Jan'),
                        $clang->gT('Feb'),
                        $clang->gT('Mar'),
                        $clang->gT('Apr'),
                        $clang->gT('May'),
                        $clang->gT('Jun'),
                        $clang->gT('Jul'),
                        $clang->gT('Aug'),
                        $clang->gT('Sep'),
                        $clang->gT('Oct'),
                        $clang->gT('Nov'),
                        $clang->gT('Dec'));
                        for ($i=1; $i<=12; $i++) {
                            if ($i == $currentmonth)
                            {
                                $i_date_selected = SELECTED;
                            }
                            else
                            {
                                $i_date_selected = '';
                            }

                            $answer .= '<option value="'.sprintf('%02d', $i).'"'.$i_date_selected.'>'.$montharray[$i-1].'</option>';
                        }
                        $answer .= '</select>';
                        break;
                        // Show year select box
                    case 'Y':   $answer .= '<label for="year'.$this->fieldname.'" class="hide">'.$clang->gT('Year').'</label><select id="year'.$this->fieldname.'" name="year'.$this->fieldname.'" class="year">
                        <option value="">'.$clang->gT('Year').'</option>';

                        /*
                        *  New question attributes used only if question attribute
                        * "dropdown_dates" is used (see IF(...) above).
                        *
                        * yearmin = Minimum year value for dropdown list, if not set default is 1900
                        * yearmax = Maximum year value for dropdown list, if not set default is 2020
                        */
                        if (trim($aQuestionAttributes['dropdown_dates_year_min'])!='')
                        {
                            $yearmin = $aQuestionAttributes['dropdown_dates_year_min'];
                        }
                        else
                        {
                            $yearmin = 1900;
                        }

                        if (trim($aQuestionAttributes['dropdown_dates_year_max'])!='')
                        {
                            $yearmax = $aQuestionAttributes['dropdown_dates_year_max'];
                        }
                        else
                        {
                            $yearmax = 2020;
                        }

                        if ($yearmin > $yearmax)
                        {
                            $yearmin = 1900;
                            $yearmax = 2020;
                        }

                        if ($aQuestionAttributes['reverse']==1)
                        {
                            $tmp = $yearmin;
                            $yearmin = $yearmax;
                            $yearmax = $tmp;
                            $step = 1;
                            $reverse = true;
                        }
                        else
                        {
                            $step = -1;
                            $reverse = false;
                        }

                        for ($i=$yearmax; ($reverse? $i<=$yearmin: $i>=$yearmin); $i+=$step) {
                            if ($i == $currentyear)
                            {
                                $i_date_selected = SELECTED;
                            }
                            else
                            {
                                $i_date_selected = '';
                            }
                            $answer .= '  <option value="'.$i.'"'.$i_date_selected.'>'.$i.'</option>';
                        }
                        $answer .= '</select>';

                        break;
                    case 'H':
                    case 'h':
                    case 'g':
                    case 'G':
                        $answer .= '<label for="hour'.$ia[1].'" class="hide">'.$clang->gT('Hour').'</label><select id="hour'.$ia[1].'" name="hour'.$ia[1].'" class="hour"><option value="">'.$clang->gT('Hour').'</option>';
                        for ($i=0; $i<24; $i++) {
                            if ($i === $currenthour)
                            {
                                $i_date_selected = SELECTED;
                            }
                            else
                            {
                                $i_date_selected = '';
                            }
                            if ($datepart=='H')
                            {
                                $answer .= '<option value="'.$i.'"'.$i_date_selected.'>'.sprintf('%02d', $i).'</option>';
                            }
                            else
                            {
                                $answer .= '<option value="'.$i.'"'.$i_date_selected.'>'.$i.'</option>';

                            }
                        }
                        $answer .= '</select>';

                        break;
                    case 'i':   $answer .= '<label for="minute'.$ia[1].'" class="hide">'.$clang->gT('Minute').'</label><select id="minute'.$ia[1].'" name="minute'.$ia[1].'" class="minute">
                        <option value="">'.$clang->gT('Minute').'</option>';

                        for ($i=0; $i<60; $i+=$aQuestionAttributes['dropdown_dates_minute_step']) {
                            if ($i === $currentminute)
                            {
                                $i_date_selected = SELECTED;
                            }
                            else
                            {
                                $i_date_selected = '';
                            }
                            if ($datepart=='i')
                            {
                                $answer .= '<option value="'.$i.'"'.$i_date_selected.'>'.sprintf('%02d', $i).'</option>';
                            }
                            else
                            {
                                $answer .= '<option value="'.$i.'"'.$i_date_selected.'>'.$i.'</option>';

                            }
                        }
                        $answer .= '</select>';

                        break;
                    default:  $answer .= $datepart;
                }
            }

            $answer .= '<input class="text" type="text" size="10" name="'.$this->fieldname.'" style="display: none" id="answer'.$this->fieldname.'" value="'.$_SESSION['survey_'.$this->surveyid][$this->fieldname].'" maxlength="10" alt="'.$clang->gT('Answer').'" onchange="'.$checkconditionFunction.'(this.value, this.name, this.type)" />
            </p>';
            $answer .= '<input type="hidden" name="qattribute_answer[]" value="'.$this->fieldname.'" />
            <input type="hidden" id="qattribute_answer'.$this->fieldname.'" name="qattribute_answer'.$this->fieldname.'" />
            <input type="hidden" id="dateformat'.$this->fieldname.'" value="'.$dateformatdetails['jsdate'].'"/>';


        }
        else
        {
            // Format the date  for output
            if (trim($_SESSION['survey_'.$this->surveyid][$this->fieldname])!='')
            {
                $datetimeobj = new Date_Time_Converter($_SESSION['survey_'.$this->surveyid][$this->fieldname] , "Y-m-d");
                $dateoutput = $datetimeobj->convert($dateformatdetails['phpdate']);
            }
            else
            {
                $dateoutput='';
            }

            if (trim($aQuestionAttributes['dropdown_dates_year_min'])!='') {
                $minyear=$aQuestionAttributes['dropdown_dates_year_min'];
            }
            else
            {
                $minyear='1980';
            }

            if (trim($aQuestionAttributes['dropdown_dates_year_max'])!='') {
                $maxyear=$aQuestionAttributes['dropdown_dates_year_max'];
            }
            else
            {
                $maxyear='2020';
            }

            $goodchars = str_replace( array("m","d","y"), "", $dateformatdetails['jsdate']);
            $goodchars = "0123456789".$goodchars[0];

            $answer ="<p class='question answer-item text-item date-item'><label for='answer{$this->fieldname}' class='hide label'>{$clang->gT('Date picker')}</label>
            <input class='popupdate' type=\"text\" size=\"10\" name=\"{$this->fieldname}\" title='".sprintf($clang->gT('Format: %s'),$dateformatdetails['dateformat'])."' id=\"answer{$this->fieldname}\" value=\"$dateoutput\" maxlength=\"10\" onkeypress=\"return goodchars(event,'".$goodchars."')\" onchange=\"$checkconditionFunction(this.value, this.name, this.type)\" />
            <input  type='hidden' name='dateformat{$this->fieldname}' id='dateformat{$this->fieldname}' value='{$dateformatdetails['jsdate']}'  />
            <input  type='hidden' name='datelanguage{$this->fieldname}' id='datelanguage{$this->fieldname}' value='{$clang->langcode}'  />
            <input  type='hidden' name='dateyearrange{$this->fieldname}' id='dateyearrange{$this->fieldname}' value='{$minyear}:{$maxyear}'  />

            </p>";
            if (trim($aQuestionAttributes['hide_tip'])==1) {
                $answer.="<p class=\"tip\">".sprintf($clang->gT('Format: %s'),$dateformatdetails['dateformat'])."</p>";
            }
        }

        return $answer;
    }

    public function getDataEntry($idrow, &$fnames, $language)
    {
        $dateformatdetails = getDateFormatDataForQID($this->getAttributeValues(), $this->surveyid);
        if ($idrow[$this->fieldname]!='')
        {
            $thisdate = DateTime::createFromFormat("Y-m-d H:i:s", $idrow[$this->fieldname])->format($dateformatdetails['phpdate']);
        }
        else
        {
            $thisdate = '';
        }

        if(canShowDatePicker($dateformatdetails))
        {
            $goodchars = str_replace( array("m","d","y", "H", "M"), "", $dateformatdetails['dateformat']);
            $goodchars = "0123456789".$goodchars[0];
            $output = CHtml::textField($this->fieldname, $thisdate,
            array(
            'class' => 'popupdate',
            'size' => '12',
            'onkeypress' => 'return goodchars(event,\''.$goodchars.'\')'
            )
            );
            $output .= CHtml::hiddenField('dateformat'.$this->fieldname, $dateformatdetails['jsdate'],
            array( 'id' => "dateformat{$this->fieldname}" )
            );
            return $output;
        }
        else
        {
            return CHtml::textField($this->fieldname, $thisdate);
        }
    }

    public function getHeaderIncludes()
    {
        $clang=Yii::app()->lang;
        $aQuestionAttributes=$this->getAttributeValues();
        
        $includes = array(Yii::app()->getConfig("generalscripts").'date.js' => 'js');
        if (trim($aQuestionAttributes['dropdown_dates'])==0) {
            $includes[Yii::app()->getConfig("generalscripts").'date.js'] = 'js';
            if ($clang->langcode !== 'en')
            {
                $includes[Yii::app()->getConfig("generalscripts").'jquery/locale/jquery.ui.datepicker-'.$clang->langcode.'.js'] = 'js';
            }
        }

        return $includes;
    }

    public function filter($value, $type)
    {
        if (trim($value)=="")
        {
            return NULL;
        }
        switch ($type)
        {
            case 'get':
            case 'post':
            global $thissurvey;
            $dateformatdatat=getDateFormatData($thissurvey['surveyls_dateformat']);
            $datetimeobj = new Date_Time_Converter($value, $dateformatdatat['phpdate']);
            return $datetimeobj->convert("Y-m-d");
            case 'db':
            return $value;
            case 'dataentry':
            case 'dataentryinsert':
            global $thissurvey;
            $qidattributes = $this->getAttributeValues();
            $dateformatdetails = getDateFormatDataForQID($qidattributes, $thissurvey);

            $items = array($value,$dateformatdetails['phpdate']);
            $this->getController()->loadLibrary('Date_Time_Converter');
            $datetimeobj = new date_time_converter($items) ;
            //need to check if library get initialized with new value of constructor or not.

            //$datetimeobj = new Date_Time_Converter($thisvalue,$dateformatdetails['phpdate']);
            return $datetimeobj->convert("Y-m-d H:i:s");
        }
    }

    public function getExtendedAnswer($value, $language)
    {
        $qidattributes = $this->getAttributeValues();
        $dateformatdetails = getDateFormatDataForQID($qidattributes, $this->surveyid);
        return convertDateTimeFormat($value,"Y-m-d H:i:s",$dateformatdetails['phpdate']);
    }

    public function loadAnswer($value)
    {
        return $value==null?'':$value;
    }

    public function getDBField()
    {
        return 'datetime';
    }

    public function getDataEntryView($language)
    {
        $dateformatdetails = getDateFormatDataForQID($this->getAttributeValues(), getSurveyInfo($this->surveyid));
        if(canShowDatePicker($dateformatdetails))
        {
            $goodchars = str_replace( array("m","d","y", "H", "M"), "", $dateformatdetails['dateformat']);
            $goodchars = "0123456789".$goodchars[0];
            $output = "<input type='text' class='popupdate' size='12' name='{$this->fieldname}' onkeypress=\"return goodchars(event,'{$goodchars}')\"/>";
            $output .= "<input type='hidden' name='dateformat{$this->fieldname}' id='dateformat{$this->fieldname}' value='{$dateformatdetails['jsdate']}'  />";
        }
        else
        {
            $output = "<input type='text' name='{$this->fieldname}'/>";
        }
        return $output;
    }

    public function getTypeHelp($language)
    {
        return $language->gT('Please enter a date:');
    }

    public function getPrintAnswers($language)
    {
        return "\t".printablesurvey::input_type_image('text',$this->getTypeHelp($language),30,1);
    }

    public function getPrintPDF($language)
    {
        return "___________";
    }

    public function availableAttributes($attr = false)
    {
        $attrs=array("dropdown_dates","dropdown_dates_year_min","dropdown_dates_year_max","statistics_showgraph","statistics_graphtype","hide_tip","hidden","reverse","page_break","date_format","dropdown_dates_minute_step","dropdown_dates_month_style","random_group");
        return $attr?in_array($attr,$attrs):$attrs;
    }

    public function questionProperties($prop = false)
    {
        $clang=Yii::app()->lang;
        $props=array('description' => $clang->gT("Date/Time"),'group' => $clang->gT("Mask questions"),'subquestions' => 0,'class' => 'date','hasdefaultvalues' => 1,'assessable' => 0,'answerscales' => 0,'enum' => 0);
        return $prop?$props[$prop]:$props;
    }
}
?>