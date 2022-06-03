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
     * @var string the icon to display when selecting times
     */
    public $iconTime = 'icon-time';

    /**
     * @var string the icon to display when selecting dates
     */
    public $iconDate = 'icon-calendar';

    /**
     * @var string id for the parent div
     */
    public $mainId = '';

    /**
     * @var string name for the inputfield
     */
    public $name = '';

    /**
     * @var array pluginOptions to be passed to datetimepicker plugin. Defaults are:
     *
     * - minDate: undefined, Prevents the user from selecting a date/time before this value
     * - maxDate: undefined, Prevents the user from selecting a date/time after this value
     * - enabledDates: undefined, Allows the user to select only from the provided days. Setting this takes precedence over options.minDate, options.maxDate configuration.
     * - disabledDates: undefined, Disallows the user to select any of the provided days.
     * - enabledHours: undefined, Allows the user to select only from the provided hours.
     * - disabledHours: undefined, Disallows the user to select any of the provided hours.
     * - disabledTimeIntervals: undefined, Disables time selection between the given DateTimes.
     * - daysOfWeekDisabled, undefined, Disallow the user to select weekdays that exist in this array. This has lower priority over the options.minDate, options.maxDate, options.disabledDates and options.enabledDates configuration settings.
     *
     *
     * OLD:
     * - maskInput: true, disables the text input mask
     * - pickDate: true,  disables the date picker
     * - pickTime: true,  disables de time picker
     * - pick12HourFormat: false, enables the 12-hour format time picker
     * - pickSeconds: true, disables seconds in the time picker
     * - startDate: -Infinity, set a minimum date
     * - endDate: Infinity, set a maximum date
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
        foreach ($this->pluginOptions as $key => $pluginOption) {
            if (is_array($pluginOption)) {
                continue;
            }
            $this->htmlOptions['data-' . $key] = $pluginOption;
        }
        $this->htmlOptions['data-td-target'] = '#' . $this->mainId;
        $this->htmlOptions['data-bs-placement'] = "right"; // @todo problem
        $this->htmlOptions['class'] = 'form-control';
//        $this->htmlOptions['data-widgetPositioning'] = '["vertical": "bottom", "horizontal": "right"]';
//        echo '<pre>';var_dump($this->pluginOptions);echo '</pre>';exit;
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
        $allowInputToggle = $this->getValue('data-allowInputToggle', $this->htmlOptions, false);
        $config = $this->getTempusConfigString();
        $script = "const picker = new tempusDominus.TempusDominus(document.getElementById('$this->mainId'), $config);
        ";
        $script .= $this->getMomentJsOverrideString();

        // bug workaround allowInputToggle
        if ($allowInputToggle) {
            $script .= "
            document.getElementById('$id').onfocus = function () {
                picker.show();
                // reposition();
            };";
        }
        $script .= "
        function reposition() {
            var popperWidgets = document.getElementsByClassName('tempus-dominus-widget');
            for (let widget of popperWidgets) {
                widget.setAttribute('data-popper-placement', 'bottom-end');
                console.log(widget.dataset);
            }
        }"; // @todo problem


        Yii::app()->clientScript->registerScript('datetimepicker', $script, CClientScript::POS_END);
    }

    /**
     * If id contains brackets, we need to double escape it with \\
     * @return string
     */
    protected function getEscapedId()
    {
        $id = str_replace('[', '\\\\[', $this->getId());
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
    private function getValue(string $key, array $array, $defaultValue = null)
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
        $tooltips = $this->getConvertedTempusOptions($this->getTranslatedTooltips());
        foreach ($tooltips as $key => $tooltip) {
            $localeScript .= "$key: '$tooltip',
          ";
        }
        $localeScript .= "dayViewHeaderFormat: { month: 'long', year: 'numeric' },
          locale: '$locale'";

        return "{
          $localeScript
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
     * Exchanges old bootstrap datepicker options which are named different with this Tempus Dominus datepicker.
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
    private function getTempusConfigString()
    {
        $clear = $this->getValue('data-showClear', $this->htmlOptions, 'false') == 1 ? 'true' : 'false';
        $today = $this->getValue('data-showToday', $this->htmlOptions, 'false') == 1 ? 'true' : 'false';
        $close = $this->getValue('data-showClose', $this->htmlOptions, 'false') == 1 ? 'true' : 'false';
        $localization = $this->getLocalizationOptionsString();
        $icons = $this->getCustomIconsString();
        return "
        {
            localization: $localization,
            display: {
                $icons
                components: {
                    useTwentyfourHour: true,    
                },
                buttons: {
                    clear: $clear,
                    today: $today,
                    close: $close,
                },
            },
        }";
    }

    /**
     * to be able to use the custom buttons of future designs, we need this function
     * to set those. By default this datepicker uses Font Awesome 6, with this function we use FA4 icons as before.
     * @return string
     */
    private function getCustomIconsString() {
        return "icons: {
                   time: 'fa fa-clock-o text-success',
                   date: 'fa fa-calendar text-success',
                   up: 'fa fa-caret-up',
                   down: 'fa fa-caret-down',
                   previous: 'fa fa-caret-left',
                   next: 'fa fa-caret-right',
                   today: 'fa fa-today text-success',
                   clear: 'fa fa-trash text-success',
                   close: 'fa fa-close text-success',
                },";
    }

    /**
     * Returns function overrides for correct date format.
     * @return string
     */
    private function getMomentJsOverrideString()
    {
        $date = $this->value;
        $dateFormat = $this->getValue('data-format', $this->htmlOptions, 'DD.MM.YYYY HH:mm');
        return "
        //formatting when selected via datepicker
        picker.dates.formatInput = function(date) { 
            if(date !== null) {
                return moment(date).format('$dateFormat');
            }
            return null;     
        };

        //converting with moment.js
        picker.dates.setFromInput = function(value, index) {
            let converted = moment(value, '$dateFormat');
            if (converted.isValid()) {
                let date = tempusDominus.DateTime.convert(converted.toDate(), this.optionsStore.options.localization.locale);
                this.setValue(date, index);
            }
            else {
                console.log('Momentjs failed to parse the input date.');
            }
        };
        //workaround: formatting when value is loaded on pageload
        picker.dates.setFromInput('$date');
         ";
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
}
