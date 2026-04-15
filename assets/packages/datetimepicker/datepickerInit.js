var pickers = {};

/**
 * returns a basic config object
 *
 * @param options object with options extracted from elements data-attributes
 * @param locale
 * @param dateFormat
 * @returns {{localization: {locale}, display: {components: {clock: boolean}, icons: {date: string, next: string, previous: string, today: string, clear: string, time: string, up: string, down: string, close: string}}}}
 */
function getConfig(options, locale, dateFormat) {
    let clock = dateFormat.indexOf('HH:mm') !== -1;
    let allowinputtoggle = getValueFromConfigObject(options, 'allowinputtoggle', false);
    let showclear = getValueFromConfigObject(options, 'showclear', false);
    let showtoday = getValueFromConfigObject(options, 'showtoday', false);
    let showclose = getValueFromConfigObject(options, 'showclose', false);
    let sidebyside = getValueFromConfigObject(options, 'sidebyside', false);
    let stepping = getValueFromConfigObject(options, 'stepping', 1);
    let mindate = getValueFromConfigObject(options, 'mindate', undefined);
    let maxdate = getValueFromConfigObject(options, 'maxdate', undefined);

    return {
        allowInputToggle: allowinputtoggle,
        localization: {
            locale: locale
        },
        stepping: stepping,
        restrictions: {
            minDate: mindate,
            maxDate: maxdate,
        },
        display: {
            icons: {
                time: 'ri-time-line text-success',
                date: 'ri-calendar-2-fill text-success',
                up: 'ri-arrow-up-s-fill',
                down: 'ri-arrow-down-s-fill',
                previous: 'ri-arrow-left-s-fill',
                next: 'ri-arrow-right-s-fill',
                today: 'ri-calendar-check-fill text-success',
                clear: 'ri-delete-bin-fill text-danger',
                close: 'ri-close-fill text-danger',
            },
            buttons: {
                clear: showclear,
                today: showtoday,
                close: showclose,
            },
            components: {
                useTwentyfourHour: true,
                clock: clock,
            },
            sideBySide: sidebyside,
        },
    };
}

/**
 * manipulates weak handling of tempus datetimepicker with dateformats
 * @param id
 * @param format
 * @param elemDate
 */
function setDatePickerFormat(id, format, elemDate) {
    // formatting when selected via datepicker
    id.dates.formatInput = function (date) {
        if (date !== null) {
            return moment(date).format(format);
        }
        return null;
    };

    // converting with moment.js
    id.dates.setFromInput = function (value, index) {
        let converted = moment(value, format);
        if (converted.isValid()) {
            let date = tempusDominus.DateTime.convert(converted.toDate(), this.optionsStore.options.localization.locale);
            this.setValue(date, index);
        }
    };
    //workaround: formatting when value is loaded on pageload
    if (elemDate) {
        id.dates.setFromInput(elemDate);
    }
}

/**
 * Inits a tempus dominus datepicker for the case if it is inside a modal.
 * It is assumed, that the datepicker was created by the widget, originally.
 * In case no widget was created before, dateFormat and locale can be passed.
 * Options are interpreted from the elements data-attributes.
 *
 * For normal use via PHP stick to the
 * ext.DateTimePickerWidget.DateTimePicker widget.
 * @param element input field where the datepicker resides
 * @param locale optional
 * @param dateFormat optional
 */
function initDatePicker(element, locale, dateFormat) {
    if (!element) return;
    let options = getOptionsFromElement(element);
    dateFormat = dateFormat !== undefined ? dateFormat : 'YYYY-MM-DD HH:mm';
    dateFormat = getValueFromConfigObject(options, 'format', dateFormat);
    locale = locale !== undefined ? locale : 'en';
    locale = getValueFromConfigObject(options, 'locale', locale);
    let config = getConfig(options, locale,dateFormat);
    let constName = 'picker_' + element.id;
    let elementDate = element.value;
    pickers[constName] = new tempusDominus.TempusDominus(element, config);
    setDatePickerFormat(pickers[constName], dateFormat, elementDate);
    attachCalendarIconToDatepicker(options);
    if(getValueFromConfigObject(options, 'allowinputtoggle', false)) {
        fixAllowInputToggle(element.id);
    }

}

/**
 * Open datepicker via click on calendar icon.
 * This is needed for the datepicker fields in the tokenform.
 * @param options
 */
function attachCalendarIconToDatepicker(options) {
    $(document).off('click', '.datepicker-icon');
    $(document).on('click', '.datepicker-icon', function () {
        if (getValueFromConfigObject(options, 'allowinputtoggle', false)) {
            $(this).prevAll('input').focus();
        } else {
            $(this).prevAll('input').click();
        }
    });
}

/**
 * workaround for buggy allowInputToggle option
 * @param id
 */
function fixAllowInputToggle(id) {
    var constName = 'picker_' + id;
    var picker = pickers[constName]
    var input = document.getElementById(id);
    input.removeEventListener('click', picker._toggleClickEvent);
    if (input != null) {
        if ((id.indexOf('answer') >= 0 && input.value !== '') || id.indexOf('answer') < 0) {
            input.addEventListener("focus", function () {
                picker.show();
            });
        }
    }
}

/**
 * Analyzes elements data atrributes for known options and copies them and their values into returned object.
 * @param element
 * @returns {{}}
 */
function getOptionsFromElement(element) {
    const availableOptions = [
        'format', 'locale', 'allowinputtoggle', 'showclear', 'showtoday', 'showclose', 'sidebyside', 'stepping', 'mindate', 'maxdate'
        ];
    const options = {};

    for (const availableOption of availableOptions) {
        if (availableOption in element.dataset) {
            options[availableOption] = element.dataset[availableOption];
        }
    }

    return options;
}

/**
 * Returns value from given property of given object. If it doesn't exist, the defaultValue is returned.
 * Also translates value 1 to true, 0 to false for boolean config attributes.
 *
 * @param object
 * @param searchedProperty
 * @param defaultValue
 * @returns {(function((Array|string)): (function(*): *)|(function(*=): *))|*|(function((Array|string)): (function(*): *)|(function(*=): *))|(<TObj, TResult>(path: PropertyPath) => (obj: TObj) => TResult)|(<TObj, TResult>() => Function<(obj: TObj) => TResult>)|(<TObj, TResult>() => FunctionChain<(obj: TObj) => TResult>)|_.LodashProperty}
 */
function getValueFromConfigObject(object, searchedProperty, defaultValue) {
    let result = defaultValue;
    const booleanAttributes = ['allowinputtoggle', 'showclear', 'showtoday', 'showclose', 'sidebyside'];
    if (searchedProperty in object) {
        if (booleanAttributes.includes(searchedProperty)) {
            result = object[searchedProperty] === '1';
        } else {
            result = object[searchedProperty];
        }
    }
    return result;
}
