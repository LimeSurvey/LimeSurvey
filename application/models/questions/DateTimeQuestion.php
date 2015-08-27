<?php
/**
 * Created by PhpStorm.
 * User: sam
 * Date: 8/11/15
 * Time: 1:39 PM
 */

namespace ls\models\questions;


use ls\interfaces\iResponse;

class DateTimeQuestion extends TextQuestion
{
    /**
     * Returns an array of EM expression that validate this question.
     * @return string[]
     */
    public function getValidationExpressions()
    {
        $result = parent::getValidationExpressions();
        // Minimum date allowed in date question
        if (isset($this->date_min) && trim($this->date_min) != '') {
            // date_min: Determine whether we have an expression, a full date (YYYY-MM-DD) or only a year(YYYY)
            if (trim($this->date_min) != '') {
                if ((strlen($this->date_min) == 4) && ($this->date_min >= 1900) && ($this->date_min <= 2099)) {
                    // backward compatibility: if only a year is given, add month and day
                    $date_min = '\'' . $this->date_min . '-01-01' . ' 00:00\'';
                } elseif (preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])/", $this->date_min)) {
                    $date_min = '\'' . $this->date_min . ' 00:00\'';
                } elseif (array_key_exists($this->date_min, $this->qcode2sgqa))  // refers to another question
                {
                    $date_min = $this->date_min . '.NAOK';
                }
            }
            $expression = '(is_empty(' . $this->varName . '.NAOK) || (' . $this->varName . '.NAOK >= date("Y-m-d H:i", strtotime(' . $date_min . ')) ))';
            $result[$expression] = gT("Date is too small.");




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
        return ['date'];
    }

    /**
     * Returns the date format for this question or, if not set, for the survey.
     */
    protected function getDateFormat() {
        if (isset($this->date_format)) {
            $aDateFormatDetails = [];
            $aDateFormatDetails['dateformat'] = $this->date_format;
            $aDateFormatDetails['phpdate'] = getPHPDateFromDateFormat($aDateFormatDetails['dateformat']);
            $aDateFormatDetails['jsdate'] = getJSDateFromDateFormat($aDateFormatDetails['dateformat']);
        }
        else
        {
            $aDateFormatDetails = getDateFormatData(getDateFormatForSID($this->sid));
        }
        return $aDateFormatDetails;
    }
    /**
     * This function renders the object.
     * It MUST NOT produce any output.
     * It should return a string or an object that can be converted to string.
     * @param iResponse $response
     * @param \SurveySession $session
     * @return \RenderedQuestion
     */
    public function render(iResponse $response, \SurveySession $session)
    {
        $result = parent::render($response, $session);

//        if (isset($this->date_min)) {
//            vdd($this->getExpressionManager($response)->RDP_Evaluate($this->date_min));
//        }
        $sDateLangvarJS=" translt = {
         alertInvalidDate: '" . gT('Date entered is invalid!','js') . "',
         infoCompleteAll: '" . gT('Please complete all parts of the date!','js') . "'
        };";
        $cs = App()->clientScript;
        $cs->registerScript("sDateLangvarJS",$sDateLangvarJS,\CClientScript::POS_HEAD);
        $cs->registerScriptFile(App()->getConfig("generalscripts").'date.js');
        $cs->registerScriptFile(App()->getConfig("third_party").'jstoolbox/date.js');

        $dateformatdetails = $this->getDateFormat();
        $numberformatdatat = getRadixPointData($this->survey->getLocalizedNumberFormat());
        $sMindatetailor='';
        $sMaxdatetailor='';

        // date_min: Determine whether we have an expression, a full date (YYYY-MM-DD) or only a year(YYYY)
        if (trim($this->date_min)!='')
        {
            $date_min=$this->date_min;
            if (preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])/",$date_min))
            {
                $mindate=$date_min;
            }
            elseif ((strlen($date_min)==4) && ($date_min>=1900) && ($date_min<=2099))
            {
                // backward compatibility: if only a year is given, add month and day
                $mindate=$date_min.'-01-01';
            }
            else
            {
                $mindate='{'.$this->date_min.'}';
                // get the LEMtailor ID, remove the span tags
                $sMindatespan=LimeExpressionManager::ProcessString($mindate, $this->primaryKey, null, false, 1, 1);
                preg_match("/LEMtailor_Q_[0-9]{1,7}_[0-9]{1,3}/", $sMindatespan, $matches);
                if (isset($matches[0]))
                    $sMindatetailor=$matches[0];
            }
        }
        else
        {
            $mindate='1900-01-01';
        }

        // date_max: Determine whether we have an expression, a full date (YYYY-MM-DD) or only a year(YYYY)
        if (trim($this->date_max)!='')
        {
            $date_max=$this->date_max;
            if (preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])/",$date_max))
            {
                $maxdate=$date_max;
            }
            elseif ((strlen($date_max)==4) && ($date_max>=1900) && ($date_max<=2099))
            {
                // backward compatibility: if only a year is given, add month and day
                $maxdate=$date_max.'-12-31';
            }
            else
            {
                $maxdate='{'.$this->date_max.'}';
                // get the LEMtailor ID, remove the span tags
                $sMaxdatespan=LimeExpressionManager::ProcessString($maxdate, $this->primaryKey, null, false, 1, 1);
                preg_match("/LEMtailor_Q_[0-9]{1,7}_[0-9]{1,3}/", $sMaxdatespan, $matches);
                if (isset($matches[0]))
                    $sMaxdatetailor=$matches[0];
            }
        }
        else
        {
            $maxdate='2037-12-31';
        }

        if (trim($this->dropdown_dates)==1) {
            if (!empty($response->{$this->sgqa}) &
                ($response->{$this->sgqa}!='INVALID'))
            {
                $datetimeobj = new Date_Time_Converter($response->{$this->sgqa}, "Y-m-d H:i:s");
                $currentyear = $datetimeobj->years;
                $currentmonth = $datetimeobj->months;
                $currentdate = $datetimeobj->days;
                $currenthour = $datetimeobj->hours;
                $currentminute = $datetimeobj->minutes;
            } else {
                $currentdate='';
                $currentmonth='';
                $currentyear='';
                $currenthour = '';
                $currentminute = '';
            }

            $dateorder = preg_split('/([-\.\/ :])/', $dateformatdetails['phpdate'],-1,PREG_SPLIT_DELIM_CAPTURE );
            $html='<p class="question answer-item dropdown-item date-item">';
            foreach($dateorder as $datepart)
            {
                switch($datepart)
                {
                    // Show day select box
                    case 'j':
                    case 'd':   $html .= '<label for="day'.$this->sgqa.'" class="hide">'.gT('Day').'</label><select id="day'.$this->sgqa.'" name="day'.$this->sgqa.'" class="day">
                    <option value="">'.gT('Day')."</option>\n";
                        for ($i=1; $i<=31; $i++) {
                            if ($i == $currentdate)
                            {
                                $i_date_selected = SELECTED;
                            }
                            else
                            {
                                $i_date_selected = '';
                            }
                            $html .= '<option value="'.sprintf('%02d', $i).'"'.$i_date_selected.'>'.sprintf('%02d', $i)."</option>\n";
                        }
                        $html .='</select>';
                        break;
                    // Show month select box
                    case 'n':
                    case 'm':   $html .= '<label for="month'.$this->sgqa.'" class="hide">'.gT('Month').'</label><select id="month'.$this->sgqa.'" name="month'.$this->sgqa.'" class="month">
                    <option value="">'.gT('Month')."</option>\n";
                        switch ((int)trim($this->dropdown_dates_month_style))
                        {
                            case 0:
                                $montharray=array(
                                    gT('Jan'),
                                    gT('Feb'),
                                    gT('Mar'),
                                    gT('Apr'),
                                    gT('May'),
                                    gT('Jun'),
                                    gT('Jul'),
                                    gT('Aug'),
                                    gT('Sep'),
                                    gT('Oct'),
                                    gT('Nov'),
                                    gT('Dec'));
                                break;
                            case 1:
                                $montharray=array(
                                    gT('January'),
                                    gT('February'),
                                    gT('March'),
                                    gT('April'),
                                    gT('May'),
                                    gT('June'),
                                    gT('July'),
                                    gT('August'),
                                    gT('September'),
                                    gT('October'),
                                    gT('November'),
                                    gT('December'));
                                break;
                            case 2:
                                $montharray=array('01','02','03','04','05','06','07','08','09','10','11','12');
                                break;
                        }

                        for ($i=1; $i<=12; $i++) {
                            if ($i == $currentmonth)
                            {
                                $i_date_selected = SELECTED;
                            }
                            else
                            {
                                $i_date_selected = '';
                            }
                            $html .= '<option value="'.sprintf('%02d', $i).'"'.$i_date_selected.'>'.$montharray[$i-1].'</option>';
                        }
                        $html .= '</select>';
                        break;
                    // Show year select box
                    case 'y':
                    case 'Y':   $html .= '<label for="year'.$this->sgqa.'" class="hide">'.gT('Year').'</label><select id="year'.$this->sgqa.'" name="year'.$this->sgqa.'" class="year">
                    <option value="">'.gT('Year').'</option>';

                        /*
                        * yearmin = Minimum year value for dropdown list, if not set default is 1900
                        * yearmax = Maximum year value for dropdown list, if not set default is 2037
                        * if full dates (format: YYYY-MM-DD) are given, only the year is used
                        * expressions are not supported because contents of dropbox cannot be easily updated dynamically
                        */
                        $yearmin = (int)substr($mindate,0,4);
                        if (!isset($yearmin) || $yearmin<1900 || $yearmin>2037)
                        {
                            $yearmin = 1900;
                        }

                        $yearmax = (int)substr($maxdate, 0, 4);
                        if (!isset($yearmax) || $yearmax<1900 || $yearmax>2037)
                        {
                            $yearmax = 2037;
                        }

                        if ($yearmin > $yearmax)
                        {
                            $yearmin = 1900;
                            $yearmax = 2037;
                        }

                        if ($this->reverse==1)
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
                            $html .= '<option value="'.$i.'"'.$i_date_selected.'>'.$i.'</option>';
                        }
                        $html .= '</select>';

                        break;
                    case 'H':
                    case 'h':
                    case 'g':
                    case 'G':
                        $html .= '<label for="hour'.$this->sgqa.'" class="hide">'.gT('Hour').'</label><select id="hour'.$this->sgqa.'" name="hour'.$this->sgqa.'" class="hour"><option value="">'.gT('Hour').'</option>';
                        for ($i=0; $i<24; $i++) {
                            if ($i === (int)$currenthour && is_numeric($currenthour))
                            {
                                $i_date_selected = SELECTED;
                            }
                            else
                            {
                                $i_date_selected = '';
                            }
                            if ($datepart=='H')
                            {
                                $html .= '<option value="'.$i.'"'.$i_date_selected.'>'.sprintf('%02d', $i).'</option>';
                            }
                            else
                            {
                                $html .= '<option value="'.$i.'"'.$i_date_selected.'>'.$i.'</option>';

                            }
                        }
                        $html .= '</select>';

                        break;
                    case 'i':   $html .= '<label for="minute'.$this->sgqa.'" class="hide">'.gT('Minute').'</label><select id="minute'.$this->sgqa.'" name="minute'.$this->sgqa.'" class="minute">
                    <option value="">'.gT('Minute').'</option>';

                        for ($i=0; $i<60; $i+=$this->dropdown_dates_minute_step) {
                            if ($i === (int)$currentminute && is_numeric($currentminute))
                            {
                                $i_date_selected = SELECTED;
                            }
                            else
                            {
                                $i_date_selected = '';
                            }
                            if ($datepart=='i')
                            {
                                $html .= '<option value="'.$i.'"'.$i_date_selected.'>'.sprintf('%02d', $i).'</option>';
                            }
                            else
                            {
                                $html .= '<option value="'.$i.'"'.$i_date_selected.'>'.$i.'</option>';

                            }
                        }
                        $html .= '</select>';

                        break;
                    default:  $html .= $datepart;
                }
            }

            // Format the date  for output
            $dateoutput=trim($response->{$this->sgqa});
            if ($dateoutput!='' & $dateoutput!='INVALID')
            {
                $datetimeobj = new Date_Time_Converter($dateoutput , "Y-m-d H:i");
                $dateoutput = $datetimeobj->convert($dateformatdetails['phpdate']);
            }

            $html .= '<input class="text" type="text" size="10" name="'.$this->sgqa.'" style="display: none" id="answer'.$this->sgqa.'" value="'.htmlspecialchars($dateoutput,ENT_QUOTES,'utf-8').'" maxlength="10" alt="'.gT('Answer').'" onchange="'.$checkconditionFunction.'(this.value, this.name, this.type)" title="'.sprintf(gT('Date in the format : %s'),$dateformatdetails['dateformat']).'" />
        </p>';
            $html .= '
        <input type="hidden" id="qattribute_answer'.$this->sgqa.'" name="qattribute_answer'.$this->sgqa.'" value="'.$this->sgqa.'"/>
        <input type="hidden" id="dateformat'.$this->sgqa.'" value="'.$dateformatdetails['jsdate'].'"/>';
            App()->getClientScript()->registerScript("doDropDownDate{$this->primaryKey}","doDropDownDate({$this->primaryKey});",CClientScript::POS_HEAD);
            // MayDo:
            // add js code to
            //        - fill dropdown boxes according to min/max
            //        - if one datefield box is changed update all others
            //        - would need a LOT of JS
        }
        else
        {
            //register timepicker extension
            App()->getClientScript()->registerPackage('jqueryui-timepicker');

            // Locale for datepicker and timpicker extension

            if (App()->language !== 'en')
            {
                App()->getClientScript()->registerScriptFile(App()->getConfig('third_party')."/jqueryui/development-bundle/ui/i18n/jquery.ui.datepicker-{App()->language}.js");
                App()->getClientScript()->registerScriptFile(App()->getConfig('third_party')."/jquery-ui-timepicker-addon/i18n/jquery-ui-timepicker-{App()->language}.js");
            }
            // Format the date  for output
            $dateoutput = $response->{$this->sgqa};
            if ($dateoutput!='' & $dateoutput!='INVALID')
            {
                $datetimeobj = new Date_Time_Converter($dateoutput , "Y-m-d H:i");
                $dateoutput = $datetimeobj->convert($dateformatdetails['phpdate']);
            }

            $goodchars = str_replace( array("m","d","y"), "", $dateformatdetails['jsdate']);
            $goodchars = "0123456789".substr($goodchars,0,1);
            // Max length of date : Get the date of 1999-12-30 at 32:59:59 to be sure to have space with non leading 0 format
            // "+1" makes room for a trailing space in date/time values
            $iLength=strlen(date($dateformatdetails['phpdate'],mktime(23,59,59,12,30,1999)))+1;


            // HTML for date question using datepicker
            $html="<p class='question answer-item text-item date-item'><label for='answer{$this->sgqa}' class='hide label'>".sprintf(gT('Date in the format: %s'),$dateformatdetails['dateformat'])."</label>
        <input class='popupdate' type=\"text\" size=\"{$iLength}\" name=\"{$this->sgqa}\" id=\"answer{$this->sgqa}\" value=\"$dateoutput\" maxlength=\"{$iLength}\"  />
        <input  type='hidden' name='dateformat{$this->sgqa}' id='dateformat{$this->sgqa}' value='{$dateformatdetails['jsdate']}'  />
        <input  type='hidden' name='datelanguage{$this->sgqa}' id='datelanguage{$this->sgqa}' value='".App()->language."'  />
        <input  type='hidden' name='datemin{$this->sgqa}' id='datemin{$this->sgqa}' value=\"{$mindate}\"    />
        <input  type='hidden' name='datemax{$this->sgqa}' id='datemax{$this->sgqa}' value=\"{$maxdate}\"   />
        </p>";

            // adds min and max date as a hidden element to the page so EM creates the needed LEM_tailor_Q_XX sections
            $sHiddenHtml="";
            if (!empty($sMindatetailor))
            {
                $sHiddenHtml.=$sMindatespan;
            }
            if (!empty($sMaxdatetailor))
            {
                $sHiddenHtml.=$sMaxdatespan;
            }
            if (!empty($sHiddenHtml))
            {
                $html.="<div class='hidden nodisplay' style='display:none'>{$sHiddenHtml}</div>";
            }

            // following JS is for setting datepicker limits on-the-fly according to variables given in date_min/max attributes
            // works with full dates (format: YYYY-MM-DD, js not needed), only a year, for backward compatibility (YYYY, js not needed),
            // variable names which refer to another date question or expressions.
            // Actual conversion of date formats is handled in LEMval()


            if (!empty($sMindatetailor) || !empty($sMaxdatetailor))
            {
                $html.="<script>
                $(document).ready(function() {
                        $('.popupdate').change(function() {

                            ";
                if (!empty($sMindatetailor))
                    $html.="
                        $('#datemin{$this->sgqa}').attr('value',
                        document.getElementById('{$sMindatetailor}').innerHTML);
                    ";
                if (!empty($sMaxdatetailor))
                    $html.="
                        $('#datemax{$this->sgqa}').attr('value',
                        document.getElementById('{$sMaxdatetailor}').innerHTML);
                    ";

                $html.="
                        });
                    });
                </script>";
            }

            if (trim($this->hide_tip)==1) {
                $html.="<p class=\"tip\">".sprintf(gT('Format: %s'),$dateformatdetails['dateformat'])."</p>";
            }
            $html .= "<script type='text/javascript'>\n"
                . "  /*<![CDATA[*/\n"
                ." doPopupDate({$this->primaryKey});\n"
                ." /*]]>*/\n"
                ."</script>\n";
        }


        $result->setHtml($html);
        return $result;
    }


}