/*
* JavaScript functions for LimeSurvey response browse
*/

// @license magnet:?xt=urn:btih:cf05388f2679ee054f2beb29a391d25f4e673ac3&dn=gpl-2.0.txt  GNU/GPL License v2 or later
// Namespace
var LS = LS || {onDocumentReady: {}};

/**
 * Needed to calculate correct pager position at RTL language
 * @var {number}
 */
var initialScrollValue = 0;

/**
 * True if admin uses an RTL language
 * @var {boolean}
 */
var useRtl = false;

// Return public functions for this module
LS.resp = {
    /**
     * Scroll the pager and the footer when scrolling horizontally
     * @return
     */
    setListPagerPosition: function (pager) {
        var $elListPager = $('#reponsesListPager');

        if (useRtl) {
            var scrollAmount = Math.abs($(pager).scrollLeft() - initialScrollValue);
            $elListPager.css({
                'position': 'relative',
                'right': scrollAmount
            });
        } else {
            $elListPager.css({
                'position': 'relative',
                'left': $(pager).scrollLeft()
            });
        }
    },
    /**
     * Bind fixing pager position on scroll event
     * @return
     */
    bindScrollWrapper: function () {
        LS.resp.setListPagerPosition();
        $('#bottom-scroller').scroll(function () {
            LS.resp.setListPagerPosition(this);
            $("#top-scroller").scrollLeft($("#bottom-scroller").scrollLeft());
        });
        $('#top-scroller').scroll(function () {
            LS.resp.setListPagerPosition(this);
            $("#bottom-scroller").scrollLeft($("#top-scroller").scrollLeft());
        });

        reinstallResponsesFilterDatePicker();
        bindListItemclick();
    },

    /**
     * Set value of module private variable initialScrollValue
     * @param {number} val
     */
    setInitialScrollValue: function (val) {
        initialScrollValue = val;
    },

    /**
     * @param {boolean} val
     */
    setUseRtl: function (val) {
        useRtl = val;
    }
};

/**
 * When date-picker is used in responses gridview
 * @return
 */
function reinstallResponsesFilterDatePicker() {

    // Since grid view is updated with Ajax, we need to fetch date format each update
    var input = document.getElementById('dateFormatDetails');
    var locale = document.getElementById('locale');
    var startdateElement = document.getElementById('SurveyDynamic_startdate');

    if (input.value && locale.value) {
        var dateFormatDetails = JSON.parse(input.value);
        var config = {
            localization: {
                locale: locale.value
            },
            display: {
                icons: {
                    time: 'fa fa-clock-o text-success',
                    date: 'fa fa-calendar text-success',
                    up: 'fa fa-caret-up',
                    down: 'fa fa-caret-down',
                    previous: 'fa fa-caret-left',
                    next: 'fa fa-caret-right',
                    today: 'fa fa-today text-success',
                    clear: 'fa fa-trash text-success',
                    close: 'fa fa-close text-success',
                },
                components: {
                    clock: false,
                },
            },
        };

        const picker_SurveyDynamic_startdate = new tempusDominus.TempusDominus(startdateElement, config);
        setDatePickerFormat(picker_SurveyDynamic_startdate, dateFormatDetails.jsdate, startdateElement.value);
        startdateElement.addEventListener("change.td", function () {
            var data = document.querySelectorAll('#responses-grid .filters input, #responses-grid .filters select');
            data = $('#responses-grid .filters input, #responses-grid .filters select').serialize();
            $.fn.yiiGridView.update('responses-grid', {data: data});
        });

        // $('#SurveyDynamic_datestamp').on('focusout', function() {
        //     var data = $('#responses-grid .filters input, #responses-grid .filters select').serialize();
        //     $.fn.yiiGridView.update('responses-grid', {data: data});
        // });
    } else {
        console.ls.log('Internal error? Run reinstallResponsesFilterDatePicker, but find no input with name dateFormatDetails.');
    }
}

function onDocumentReadyListresponse() {
    if ($('#bottom-scroller').length > 0)
        $('#fake-content').width($('#bottom-scroller')[0].scrollWidth);

    $('#top-scroller').height('18px');

    LS.resp.setInitialScrollValue($('.scrolling-wrapper').scrollLeft());
    LS.resp.setUseRtl($('input[name="rtl"]').val() === '1');

    LS.resp.bindScrollWrapper();

    $('#displaymode input').on('change.listresponse', function (event) {
        $('#change-display-mode-form').find('input[type=submit]').trigger('click');
    });

}

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
        } else {
            // console.log('Momentjs failed to parse the input date.');
        }
    };
    //workaround: formatting when value is loaded on pageload
    if (elemDate) {
        id.dates.setFromInput(elemDate);
    }
}

$(window).bind("load", function () {
    onDocumentReadyListresponse();
    reinstallResponsesFilterDatePicker();
});

$(document).on('pjax:scriptcomplete', onDocumentReadyListresponse);

$(function () {
    // hide and submit Modal on click for pjax preventDefault submit
    $('#responses-column-filter-modal-submit').on('click', function (e) {
        e.preventDefault();
        var form = $('#responses-column-filter-modal form');
        form.submit();
        form.modal('hide');
    });
});

$(function () {
    // select all columns for the response table
    $('#responses-column-filter-modal-selectall').on('click', function (e) {
        e.preventDefault();
        $(".responses-multiselect-checkboxes .checkbox input").prop('checked', true);
    });
});

$(function () {
    // remove selection fir the response table
    $('#responses-column-filter-modal-clear').on('click', function (e) {
        e.preventDefault();
        $(".responses-multiselect-checkboxes .checkbox input").prop('checked', false);
    });
});

$(function () {
    // cancel current modifications to the selection of columns for the response table
    $('#responses-column-filter-modal-cancel').on('click', function (e) {
        e.preventDefault();
        var form = $('#responses-column-filter-modal form');
        var filteredColumns = form.data('filtered-columns');

        $(".responses-multiselect-checkboxes .checkbox input").prop('checked', false);
        filteredColumns.forEach(function (item) {
            $(".responses-multiselect-checkboxes .checkbox input[value=" + item + "]").prop('checked', true);
        });
        form.modal('hide');
    });
});
