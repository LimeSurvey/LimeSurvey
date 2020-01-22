<?php



/**
 * RenderClass for Boilerplate Question
 *  * The ia Array contains the following
 *  0 => string qid 
 *  1 => string sgqa
 *  2 => string questioncode
 *  3 => string question
 *  4 => string type 
 *  5 => string gid
 *  6 => string mandatory,
 *  7 => string conditionsexist,
 *  8 => string usedinconditions
 *  0 => string used in group.php for question count
 * 10 => string new group id for question in randomization group (GroupbyGroup Mode)
 *
 */
class RenderDate extends QuestionBaseRenderer
{

    protected $aDateformatDetails;
    protected $minDate;
    protected $maxDate;

    public function getMainView(){
        return '/survey/questions/answer/date/';
    }
    
    public function getRows(){
        return;
    }

    protected function registerAssets() { 
        $this->addScript(
            "sDateLangvarJS",
            "translt={alertInvalidDate:'".gT('Date entered is invalid!', 'js')."'};"
        );

        $this->aScriptFiles[] = [
            'path' => Yii::app()->getConfig("generalscripts").'date.js',
            'position' => LSYii_ClientScript::POS_END
        ];

        $this->aPackages = [
            'moment',
            'bootstrap-datetimepicker'
        ];

        parent::registerAssets();
    }

    public function getTranslatorData() {
        $data = [];
        $data['dateparts'] = [
            'year' => gT('Year'),
            'month' => gT('Month'),
            'day' => gT('Day'),
            'hour' => gT('Hour'),
            'minute' => gT('Minute'),
            'second' => gT('Second'),
            'millisecond' => gT('Millisecond')
        ];

        switch ((int) trim($this->getQuestionAttribute('dropdown_dates_month_style'))) {
            case 0:
                $data['montharray'] = array(
                    gT('Jan'), gT('Feb'), gT('Mar'), gT('Apr'), gT('May'), gT('Jun'), 
                    gT('Jul'),   gT('Aug'), gT('Sep'), gT('Oct'), gT('Nov'), gT('Dec')
                );
                break;
            case 1:
                $data['montharray'] = array( 
                    gT('January'), gT('February'), gT('March'), gT('April'), gT('May'), gT('June'),
                    gT('July'), gT('August'), gT('September'), gT('October'), gT('November'), gT('December')
                );
                break;
            case 2:
                $data['montharray'] = array('01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12');
                break;
        }
        $data['tooltips'] = array(
            'clear'=> gT('Clear selection'),
            'prevMonth'=> gT('Previous month'),
            'nextMonth'=> gT('Next month'),
            'selectYear'=> gT('Select year'),
            'prevYear'=> gT('Previous year'),
            'nextYear'=> gT('Next year'),
            'selectDecade'=> gT('Select decade'),
            'prevDecade'=> gT('Previous decade'),
            'nextDecade'=> gT('Next decade'),
            'prevCentury'=> gT('Previous century'),
            'nextCentury'=> gT('Next century'),
            'selectTime'=> gT('Select time')
        );

        return $data;
    }

    public function setMinDate(){
        // date_min: Determine whether we have an expression, a full date (YYYY-MM-DD) or only a year(YYYY)
        if (trim($this->getQuestionAttribute('date_min')) != '') {
            $date_min      = trim($this->getQuestionAttribute('date_min'));
            $date_time_em  = strtotime(LimeExpressionManager::ProcessString("{".$date_min."}", $this->oQuestion->qid));
        
            if (ctype_digit($date_min) && (strlen($date_min) == 4) && ($date_min >= 1900) && ($date_min <= 2099)) {
                $this->minDate = $date_min.'-01-01'; // backward compatibility: if only a year is given, add month and day
            } elseif (preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])/", $date_min)) {
                // it's a YYYY-MM-DD date (use http://www.yiiframework.com/doc/api/1.1/CDateValidator ?)
                $this->minDate = $date_min;
            } elseif ($date_time_em !== false) {
                $this->minDate = (string) date("Y-m-d", $date_time_em);
            } else {
                $this->minDate = '{'.$this->getQuestionAttribute('date_min').'}';
            }
        } else {
            $this->minDate = '1900-01-01'; // Why 1900 ?
        }
    }

    public function setMaxDate(){
        // date_max: Determine whether we have an expression, a full date (YYYY-MM-DD) or only a year(YYYY)
        if (trim($this->getQuestionAttribute('date_max')) != '') {
            $date_max     = trim($this->getQuestionAttribute('date_max'));
            $date_time_em = strtotime(LimeExpressionManager::ProcessString("{".$date_max."}", $this->oQuestion->qid));
        
            if (ctype_digit($date_max) && (strlen($date_max) == 4) && ($date_max >= 1900) && ($date_max <= 2099)) {
                $this->maxDate = $date_max.'-12-31'; // backward compatibility: if only a year is given, add month and day
            } elseif (preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])/", $date_max)) {
        // it's a YYYY-MM-DD date (use http://www.yiiframework.com/doc/api/1.1/CDateValidator ?)
                $this->maxDate = $date_max;
            } elseif ($date_time_em !== false) {
                $this->maxDate = (string) date("Y-m-d", $date_time_em);
            } else {
                $this->maxDate = '{'.$this->getQuestionAttribute('date_max').'}';
            }
        } else {
            $this->maxDate = '2187-12-31'; // Why 2187 ?
        }
    }

    private function getDaySelect($iCurrent){
        return Yii::app()->twigRenderer->renderQuestion(
            $this->getMainView().'/dropdown/rows/day', 
            array('dayId'=>$this->sSGQA, 'currentday'=>$iCurrent), 
            true
        );
    }

    private function getMonthSelect($iCurrent){
        
        return Yii::app()->twigRenderer->renderQuestion(
            $this->getMainView().'/dropdown/rows/month', 
            array('monthId'=>$this->sSGQA, 'currentmonth'=>$iCurrent, 'montharray' => $this->getTranslatorData()['montharray']), 
            true
        );
    }

    private function getYearSelect($iCurrent){
        /*
        * yearmin = Minimum year value for dropdown list, if not set default is 1900
        * yearmax = Maximum year value for dropdown list, if not set default is 2187
        * if full dates (format: YYYY-MM-DD) are given, only the year is used
        * expressions are not supported because contents of dropbox cannot be easily updated dynamically
        */
        $yearmin = (int) substr($this->minDate, 0, 4);
        if (!isset($yearmin) || $yearmin < 1900 || $yearmin > 2187) {
            $yearmin = 1900;
        }

        $yearmax = (int) substr($this->maxDate, 0, 4);
        if (!isset($yearmax) || $yearmax < 1900 || $yearmax > 2187) {
            $yearmax = 2187;
        }

        if ($yearmin > $yearmax) {
            $yearmin = 1900;
            $yearmax = 2187;
        }

        if ($this->getQuestionAttribute('reverse') == 1) {
            $tmp = $yearmin;
            $yearmin = $yearmax;
            $yearmax = $tmp;
            $step = 1;
            $reverse = true;
        } else {
            $step = -1;
            $reverse = false;
        }
                   
        return Yii::app()->twigRenderer->renderQuestion(
            $this->getMainView().'/dropdown/rows/year', 
            array(
                'yearId'=>$this->sSGQA, 
                'currentyear'=>$iCurrent, 
                'yearmax'=>$yearmax, 
                'reverse'=>$reverse, 
                'yearmin'=>$yearmin, 
                'step'=>$step
            ), 
            true
        );
    }
    
    private function getHourSelect($iCurrent, $datepart){
        return Yii::app()->twigRenderer->renderQuestion(
            $this->getMainView().'/dropdown/rows/hour', 
            array('hourId'=>$this->sSGQA, 'currenthour'=>$iCurrent, 'datepart'=>$datepart), 
            true
        );
    }

    private function getMinuteSelect($iCurrent, $datepart){
        return Yii::app()->twigRenderer->renderQuestion(
            $this->getMainView().'/dropdown/rows/minute', 
            array(
                'minuteId'=>$this->sSGQA, 
                'currentminute'=>$iCurrent, 
                'dropdown_dates_minute_step'=>$this->getQuestionAttribute('dropdown_dates_minute_step'), 
                'datepart'=>$datepart
            ), 
            true
        );
    }


    public function renderDatepicker($dateoutput, $coreClass) {
        // Max length of date : Get the date of 1999-12-30 at 32:59:59 to be sure to have space with non leading 0 format
        // "+1" makes room for a trailing space in date/time values
        $iLength = strlen(date($this->aDateformatDetails['phpdate'], mktime(23, 59, 59, 12, 30, 1999))) + 1;

        // Hide calendar (but show hour/minute) if there's no year, month or day in format
        $hideCalendar = strpos($this->aDateformatDetails['jsdate'], 'Y') === false
                     && strpos($this->aDateformatDetails['jsdate'], 'D') === false
                     && strpos($this->aDateformatDetails['jsdate'], 'M') === false;

        
        $this->aPackages[] = 'bootstrap-datetimepicker';        
        $aDefaultDatePicker = array(
            'locale' => convertLStoDateTimePickerLocale(App()->language),
            'tooltips' => $this->getTranslatorData()['tooltips'],
            'icons' => array(
                'time'=> 'fa fa-clock-o',
                'date'=> 'fa fa-calendar',
                'up'=> 'fa fa-chevron-up',
                'down'=> 'fa fa-chevron-down',
                'previous'=> 'fa fa-chevron-left',
                'next'=> 'fa fa-chevron-right',
                'today'=> 'fa fa-calendar-check-o',
                'clear'=> 'fa fa-trash-o',
                'close'=> 'fa fa-closee'
            ),
            'allowInputToggle' =>true,
            'showClear' => true,
            'sideBySide' => true,
            //~ 'debug'=>true
        );

        $this->addScript(
            "setDatePickerGlobalOption",
            "$.extend( $.fn.datetimepicker.defaults, ".json_encode($aDefaultDatePicker)." )", 
            LSYii_ClientScript::POS_BEGIN
        );

        $this->addScript(
            'doPopupDate', 
            "doPopupDate({$this->oQuestion->qid});", 
            LSYii_ClientScript::POS_POSTSCRIPT,
            true
        );

        // HTML for date question using datepicker
        $answer = Yii::app()->twigRenderer->renderQuestion(
            $this->getMainView().'/selector/answer', array(
            'name'                   => $this->sSGQA,
            'basename'               => $this->sSGQA,
            'coreClass'              => $coreClass,
            'iLength'                => $iLength,
            'mindate'                => $this->minDate,
            'maxdate'                => $this->maxDate,
            'dateformatdetails'      => $this->aDateformatDetails['dateformat'],
            'dateformatdetailsjs'    => $this->aDateformatDetails['jsdate'],
            'dateformatdetailsphp'   => $this->aDateformatDetails['phpdate'],
            'minuteStep'             => $this->getQuestionAttribute('dropdown_dates_minute_step'),
            'goodchars'              => "", // "return window.LS.goodchars(event,'".$goodchars."')", //  This won't work with non-latin keyboards
            'checkconditionFunction' => $this->checkconditionFunction.'(this.value, this.name, this.type)',
            'language'               => App()->language,
            'hidetip'                => trim($this->getQuestionAttribute('hide_tip')) == 0,
            'dateoutput'             => $dateoutput,
            'qid'                    => $this->oQuestion->qid,
            'hideCalendar'           => $hideCalendar
            ), true);

        return $answer;
    }

    public function renderDropdownDates($dateoutput, $coreClass) {
        $coreClass .= " dropdown-item"; // items ?
        if (!empty($this->mSessionValue) && ($this->mSessionValue != 'INVALID')) {
            $datetimeobj   = new Date_Time_Converter($this->mSessionValue, "Y-m-d H:i:s");
            $currentyear   = $datetimeobj->years;
            $currentmonth  = $datetimeobj->months;
            $currentday   = $datetimeobj->days;
            $currenthour   = $datetimeobj->hours;
            $currentminute = $datetimeobj->minutes;
        } else {
            // If date is invalid get the POSTED value
            $currentday   = App()->request->getPost("day{$this->sSGQA}", '');
            $currentmonth  = App()->request->getPost("month{$this->sSGQA}", '');
            $currentyear   = App()->request->getPost("year{$this->sSGQA}", '');
            $currenthour   = App()->request->getPost("hour{$this->sSGQA}", '');
            $currentminute = App()->request->getPost("minute{$this->sSGQA}", '');
        }
        $dateorder = preg_split('/([-\.\/ :])/', $this->aDateformatDetails['phpdate'], -1, PREG_SPLIT_DELIM_CAPTURE);
    
        $sRows = '';
        foreach ($dateorder as $datepart) {
            switch ($datepart) {
                // Show day select box
                case 'j': case 'd':
                    $sRows .= $this->getDaySelect($currentday);
                    break;
                // Show month select box
                case 'n': case 'm':
                    $sRows .= $this->getMonthSelect($currentmonth);
                    break;
                // Show year select box
                case 'y': case 'Y':
                    $sRows .= $this->getYearSelect($currentyear);
                    break;
                case 'H': case 'h': case 'g': case 'G':
                    $sRows .= $this->getHourSelect($currenthour, $datepart);
                    break;
                case 'i':
                    $sRows .= $this->getMinuteSelect($currentminute, $datepart);
                    break;
                default:
                    $sRows .= Yii::app()->twigRenderer->renderQuestion(
                        $this->getMainView().'/dropdown/rows/datepart', 
                        array('datepart'=>$datepart), 
                        true
                    );
            }
        }

        $this->addScript(
            "doDropDownDate",
            "doDropDownDate({$this->oQuestion->qid});",
            LSYii_ClientScript::POS_POSTSCRIPT,
            true
        );
        
        
        // ==> answer
        $answer = Yii::app()->twigRenderer->renderQuestion(
            $this->getMainView().'/dropdown/answer', array(
            'sRows'                  => $sRows,
            'coreClass'              => $coreClass,
            'name'                   => $this->sSGQA,
            'basename'               => $this->sSGQA,
            'dateoutput'             => htmlspecialchars($dateoutput, ENT_QUOTES, 'utf-8'),
            'checkconditionFunction' => $this->checkconditionFunction.'(this.value, this.name, this.type)',
            'dateformatdetails'      => $this->aDateformatDetails['jsdate'],
            'dateformat'             => $this->aDateformatDetails['jsdate'],
            ), true);
        
        return $answer;
    }


    public function render($sCoreClasses = ''){
        $answer = '';
        $inputnames = [];
        $this->aDateformatDetails      = getDateFormatDataForQID($this->aQuestionAttributes, $this->oQuestion->sid);
        $coreClass = "ls-answers answer-item date-item ".$sCoreClasses;
        $this->setMinDate();
        $this->setMaxDate();
        // Format the date  for output
        $dateoutput = trim($this->mSessionValue);
        if ($dateoutput != '' && $dateoutput != 'INVALID') {
            $datetimeobj = DateTime::createFromFormat('!Y-m-d H:i', fillDate(trim($dateoutput)));
            if ($datetimeobj) {
                $dateoutput = $datetimeobj->format($this->aDateformatDetails['phpdate']);
            } else {
                $dateoutput = ''; // Imported value and some old survey can have 0000-00-00 00:00:00
            }
        }
        
        //throw new Error("<pre>HALT!".print_r($this->oQuestion,true)."</pre>");
        if (trim($this->getQuestionAttribute('dropdown_dates')) == 1) {
            $answer = $this->renderDropdownDates($dateoutput, $coreClass);
        } else {
            $answer = $this->renderDatepicker($dateoutput, $coreClass);
            $coreClass .= " text-item";
        }

        $this->registerAssets();
        $inputnames[] = $this->sSGQA;
        
        return array($answer, $inputnames);
    }

}
