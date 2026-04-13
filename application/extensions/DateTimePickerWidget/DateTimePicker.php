<?php

/**
 * DateTimePicker widget class
 * A simple implementation for date range picker for Twitter Bootstrap 5
 * @see <https://getdatepicker.com/>
 */

class DateTimePicker extends CInputWidget
{
    /**
     * @var string $selector if provided, then no input field will be rendered. It will write the JS code for the
     * specified selector.
     */
    public $selector;

    /**
     * @var string the date format.
     */
    public $format = 'dd/MM/yyyy hh:mm:ss';

    /**
     * @var string id for the parent div
     */
    public $mainId = '';

    /**
     * @var string name for the inputfield
     */
    public $name = '';

    /**
     * https://getdatepicker.com/6/options.html
     * @var array pluginOptions to be passed to datetimepicker plugin. Defaults are:
     *
     * - format (custom, introduced by us)
     * - allowInputToggle: false
     * - showClear: false
     * - showToday: false
     * - showClose: false
     * - sideBySide: false
     * - stepping: 1, Controls how much the minutes are changed by
     * - locale: default
     * - minDate: undefined, Prevents the user from selecting a date/time before this value
     * - maxDate: undefined, Prevents the user from selecting a date/time after this value
     *
     * Following options are not yet done, because there are not needed right now:
     * - @TODO enabledDates: undefined
     * - @TODO disabledDates: undefined
     * - @TODO enabledHours: undefined
     * - @TODO disabledHours: undefined
     * - @TODO disabledTimeIntervals: undefined
     * - @TODO daysOfWeekDisabled, undefined
     *
     * Display of components like "calendar", "clock", "years", etc is set dynamically via format setting
     */
    public $pluginOptions = array();

    /**
     * @var string[] the JavaScript event handlers.
     */
    public $events = array();

    /**
     * Initializes the widget.
     */
    public function init()
    {
        list($name, $id) = $this->resolveNameID();
        $this->mainId = $id . '_datetimepicker';
        $this->name = $name;

        $this->htmlOptions['id'] = $this->getValue('id', $this->htmlOptions, $this->getEscapedId());
        $this->htmlOptions['autocomplete'] = 'off';
        foreach ($this->pluginOptions as $key => $pluginOption) {
            if (is_array($pluginOption)) {
                continue;
            }
            $this->htmlOptions['data-' . $key] = $pluginOption;
        }
        $this->htmlOptions['data-td-target'] = '#' . $this->mainId;
        $this->htmlOptions['class'] = 'form-control';
        $this->format = $this->getValue('data-format', $this->htmlOptions, 'DD.MM.YYYY HH:mm');
    }

    /**
     * Runs the widget.
     */
    public function run()
    {
        $this->renderField();
        $this->registerClientScript();
    }

    /**
     * Renders the field if no selector has been provided
     */
    public function renderField()
    {
        if (null === $this->selector) {
            $this->render('datetimepicker', array(
                'name' => $this->name,
                'id' => $this->mainId,
                'hasModel' => $this->hasModel(),
                'model' => $this->model,
                'attribute' => $this->attribute,
                'htmlOptions' => $this->htmlOptions,
                'value' => $this->value,
            ));
        }
    }


    /**
     *
     * Registers required css js files
     */
    public function registerClientScript()
    {
        /* @var $cs CClientScript */
        $cs = Yii::app()->getClientScript();
        $cs->registerPackage('tempus-dominus');

        $id = $this->getId();
        $script = $this->getConfigScript($id);

        Yii::app()->clientScript->registerScript('datetimepicker_' . $id, $script, CClientScript::POS_END);
    }

    /**
     * Returns the whole js config which is needed for init of this datepicker
     * @param $id
     * @return string
     */
    public function getConfigScript($id)
    {
        $allowInputToggle = $this->getValue('data-allowInputToggle', $this->htmlOptions, false);
        $config = $this->getTempusConfigString();
        $script = "var picker_$id = new tempusDominus.TempusDominus(document.getElementById('$this->mainId'), $config);
        ";
        $script .= $this->getMomentJsOverrideString();

        if ($allowInputToggle) {
            $script .= "
            // bug workaround allowInputToggle
            var id_$id = '$id';
            var input_$id = document.getElementById('$id');
            input_$id.removeEventListener('click', picker_$id._toggleClickEvent);
            if(input_$id != null) {
                if((id_$id.indexOf('answer') >= 0 && input_$id.value !== '') || id_$id.indexOf('answer') < 0) {
                        input_$id.onfocus = function () {
                        picker_$id.show();
                    };
                } 
            }    
            ";
        }

        return $script;
    }


    /**
     * If id contains brackets, we need to double escape it with \\
     * @return string
     */
    protected function getEscapedId()
    {
        $id = str_replace('[', '\\\\[', (string) $this->getId());
        $id = str_replace(']', '\\\\]', $id);
        return $id;
    }

    /**
     * Returns a specific value from the given array (or the default value if not set).
     * @param string $key the item key.
     * @param array $array the array to get from.
     * @param mixed $defaultValue the default value.
     * @return mixed the value.
     */
    private function getValue(string $key, array $array, $defaultValue = 'false')
    {
        return array_key_exists($key, $array) ? $array[$key] : $defaultValue;
    }

    /**
     * Generates and returns the localization part of the Tempus Dominus datepicker config
     * @return string
     */
    private function getLocalizationOptionsString()
    {
        $localeScript = '';
        $locale = $this->getValue('locale', $this->pluginOptions, 'en');
        $dateFormat = CHtml::encode($this->format);
        $tooltips = $this->getConvertedTempusOptions($this->getTranslatedTooltips());
        foreach ($tooltips as $key => $tooltip) {
            $localeScript .= "      $key: '$tooltip',\n";
        }
        $localeScript .= "      dayViewHeaderFormat: { month: 'long', year: 'numeric' },\n" .
            "      locale: '$locale',\n" .
            "      format: '$dateFormat',\n";

        // Try to guess the right hour cycle from the format
        // The old datetimepicker did that, but Tempus Dominus does not (it guesses from the locale, not the format)
        if (strpos(strtolower($dateFormat), 'a') === false && strpos($dateFormat, 'h') === false) {
            $localeScript .= "      hourCycle: 'h24',\n";
        } else {
            $localeScript .= "      hourCycle: 'h12',\n";
        }

        return "{
          $localeScript
        }";
    }

    /**
     * Generates and returns the restrictions part of the Tempus Dominus datepicker config
     * @return string
     */
    private function getRestrictionsOptionsString()
    {
        $minDate = $this->getValue('data-minDate', $this->htmlOptions, 'undefined');
        $minDate = $minDate != 'undefined' ? "'$minDate'" : $minDate;
        $maxDate = $this->getValue('data-maxDate', $this->htmlOptions, 'undefined');
        $maxDate = $maxDate != 'undefined' ? "'$maxDate'" : $maxDate;

        return "{
                minDate: $minDate, 
                maxDate: $maxDate,
        }";
    }

    /**
     * Generates and returns the components part of the Tempus Dominus datepicker config
     * @return string
     */
    private function getComponentsOptionsString()
    {
        $clock = $this->getShowComponent('clock') ? 'true' : 'false';
        $date = $this->getShowComponent('date') ? 'true' : 'false';
        $month = $this->getShowComponent('month') ? 'true' : 'false';
        $year = $this->getShowComponent('year') ? 'true' : 'false';
        $decades = $this->getShowComponent('decades') ? 'true' : 'false';
        $hours = $this->getShowComponent('hours') ? 'true' : 'false';
        $minutes = $this->getShowComponent('minutes') ? 'true' : 'false';
        $seconds = $this->getShowComponent('seconds') ? 'true' : 'false';
        return "{
                    date: $date,
                    month: $month,
                    year: $year,
                    decades: $decades,
                    clock: $clock,
                    hours: $hours,
                    minutes: $minutes,
                    seconds: $seconds,    
                }";
    }

    /**
     * Converts keys of $options array to the correct options used by this datepicker
     * @param array $options
     * @return array
     */
    private function getConvertedTempusOptions(array $options)
    {
        $convertedOptions = [];
        foreach ($options as $option => $value) {
            $convertedOptions[$this->getTempusOption($option)] = $value;
        }

        return $convertedOptions;
    }

    /**
     * Exchanges old bootstrap datepicker options which are named differentto the ones used by Tempus Dominus datepicker.
     * If there is nothing found in $tempusConvertOptions array, given $option is returned unchanged.
     * @param string $option
     * @return string
     */
    private function getTempusOption(string $option)
    {
        $tempusConvertOptions = [
            'prevMonth' => 'previousMonth',
            'prevYear' => 'previousYear',
            'prevDecade' => 'previousDecade',
            'prevCentury' => 'previousCentury',
        ];

        return array_key_exists($option, $tempusConvertOptions) ? $tempusConvertOptions[$option] : $option;
    }

    /**
     * Creates and returns the main config object for the Tempus Dominus datepicker
     * @return string
     */
    public function getTempusConfigString()
    {
        $clear = $this->getValue('data-showClear', $this->htmlOptions) == 1 ? 'true' : 'false';
        $today = $this->getValue('data-showToday', $this->htmlOptions) == 1 ? 'true' : 'false';
        $close = $this->getValue('data-showClose', $this->htmlOptions) == 1 ? 'true' : 'false';
        $sideBySide = $this->getValue('data-sideBySide', $this->htmlOptions) == 1 && $this->getShowComponent(
            'clock'
        ) ? 'true' : 'false';
        $stepping = $this->getValue('data-stepping', $this->htmlOptions, 1);
        $stepping = $stepping != 0 ? $stepping : 1;

        $localization = $this->getLocalizationOptionsString();
        $calendarComponents = $this->getComponentsOptionsString();
        $icons = $this->getCustomIconsString();

        return "
        {
            localization: $localization,
            display: {
                $icons
                components: $calendarComponents,
                buttons: {
                    clear: $clear,
                    today: $today,
                    close: $close,
                },
                sideBySide: $sideBySide,
                theme : (document.body.hasAttribute('data-thememode')) ? document.body.getAttribute('data-thememode') : 'auto'
            },
            stepping: $stepping
        }";
    }

    /**
     * to be able to use the custom buttons of future designs, we need this function
     * to set those. By default this datepicker uses Font Awesome 6, with this function we use FA4 icons as before.
     * @return string
     */
    private function getCustomIconsString()
    {
        return "icons: {
                   time: 'ri-time-line text-success',
                   date: 'ri-calendar-2-fill text-success',
                   up: 'ri-arrow-up-s-fill',
                   down: 'ri-arrow-down-s-fill',
                   previous: 'ri-arrow-left-s-fill',
                   next: 'ri-arrow-right-s-fill',
                   today: 'ri-calendar-check-fill text-success',
                   clear: 'ri-delete-bin-fill text-danger',
                   close: 'ri-close-fill text-danger',
                },";
    }

    /**
     * Returns function overrides for correct date format using momentjs.
     * @return string
     */
    private function getMomentJsOverrideString()
    {
        $id = $this->getId();
        $date = $this->value;
        $dateFormat = CHtml::encode($this->format);
        $minDate = $this->getValue('data-minDate', $this->htmlOptions, 'undefined');
        $minDate = $minDate != 'undefined' ? "'$minDate'" : $minDate;
        $maxDate = $this->getValue('data-maxDate', $this->htmlOptions, 'undefined');
        $maxDate = $maxDate != 'undefined' ? "'$maxDate'" : $maxDate;
        $viewDate = $this->getViewDate();
        if (empty($viewDate)) {
            $viewDate = 'undefined';
        }

        return "
        //formatting when selected via datepicker
        picker_$id.dates.formatInput = function(date) { 
            if(typeof date !== 'undefined' && date !== null) {
                return moment(date).format('$dateFormat');
            }
            return null;
        };

        //converting with moment.js
        picker_$id.dates.setFromInput = function(value, index) {
            let converted = moment(value, '$dateFormat');
            if (converted.isValid()) {
                let date = tempusDominus.DateTime.convert(converted.toDate(), this.optionsStore.options.localization.locale);
                this.setValue(date, index);
            }
            else {
                // console.log('Momentjs failed to parse the input date.');
            }
        };
        //workaround: formatting when value is loaded on pageload
        picker_$id.dates.setFromInput('$date');
         
        //workaround for correct minDate, maxDate settings
        var minDate = $minDate;
        var maxDate = $maxDate;
        var locale = picker_$id.optionsStore.options.localization.locale;
        if(minDate) {
           var min = moment(minDate);
           min.set({h: 0, m: 0, s: 0});
           picker_$id.optionsStore.options.restrictions.minDate = tempusDominus.DateTime.convert(min.toDate(), locale);
        }
        if(maxDate) {
           var max = moment(maxDate);
           max.set({h: 23, m: 59, s: 59});
           picker_$id.optionsStore.options.restrictions.maxDate = tempusDominus.DateTime.convert(max.toDate(), locale);
        }
        var viewDate = $viewDate;
        if (viewDate) {
            picker_$id.optionsStore.options.viewdate = tempusDominus.DateTime.convert(moment($viewDate, '$dateFormat').toDate(), locale);
        }";
    }

    /**
     * Returns the default tooltips for the datepicker, using the LS translation.
     * If there are tooltips defined in the widget call as well, they will also be added
     * and even prioritized over the defaults.
     * @return array
     */
    private function getTranslatedTooltips()
    {
        $defaultToolTips = [
            'clear' => gT('Clear selection'),
            'prevMonth' => gT('Previous month'),
            'nextMonth' => gT('Next month'),
            "selectMonth"  => gT('Select month'),
            'selectYear' => gT('Select year'),
            'prevYear' => gT('Previous year'),
            'nextYear' => gT('Next year'),
            'selectDecade' => gT('Select decade'),
            'prevDecade' => gT('Previous decade'),
            'nextDecade' => gT('Next decade'),
            'prevCentury' => gT('Previous century'),
            'nextCentury' => gT('Next century'),
            'selectTime' => gT('Select time'),
            'selectDate' => gT('Select date')
        ];
        $tooltipsFromCall = $this->getValue('tooltips', $this->pluginOptions, []);

        return array_merge($defaultToolTips, $tooltipsFromCall);
    }

    /**
     * Regarding the format of the displayed date, it is determined which calendar components will be shown
     * @return bool
     */
    private function getShowComponent($component)
    {
        switch ($component) {
            case 'clock':
                $formatMatch = preg_match('/[Hhms]/', $this->format);
                break;
            case 'date':
                $formatMatch = preg_match('/[D]/', $this->format);
                break;
            case 'month':
                $formatMatch = preg_match('/[M]/', $this->format);
                break;
            case 'year':
            case 'decades':
                $formatMatch = preg_match('/[yY]/', $this->format);
                break;
            case 'hours':
                $formatMatch = preg_match('/[Hh]/', $this->format);
                break;
            case 'minutes':
                $formatMatch = preg_match('/[m]/', $this->format);
                break;
            case 'seconds':
                $formatMatch = preg_match('/[sS]/', $this->format);
                break;
            default:
                $formatMatch = preg_match('/.*/', $this->format);
        }

        return $formatMatch !== false && $formatMatch !== 0;
    }

    /**
     * Returns the viewDate for the datepicker
     * @return string
     */
    private function getViewDate()
    {
        if (!empty($this->value)) {
            return "'" . $this->value . "'";
        }

        $minDate = $this->getValue('data-minDate', $this->htmlOptions, null);
        if (isset($minDate)) {
            // If min date is in the future, we set the view date to the min date
            if (strtotime($minDate) > time()) {
                return "'$minDate'";
            }
        }

        $maxDate = $this->getValue('data-maxDate', $this->htmlOptions, null);
        if (isset($maxDate)) {
            // If max date is in the past, we set the view date to the max date
            if (strtotime($maxDate) < time()) {
                return "'$maxDate'";
            }
        }

        return null;
    }
}
