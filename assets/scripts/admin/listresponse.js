/*
* JavaScript functions for LimeSurvey response browse
*/

// @license magnet:?xt=urn:btih:cf05388f2679ee054f2beb29a391d25f4e673ac3&dn=gpl-2.0.txt  GNU/GPL License v2 or later

/**
 *
 * @type {{}}
 */
var filterData = {};



/**
 * reinits the datetimepickers and adds event listener
 * for grid reload
 * @return
 */
function reinstallResponsesFilterDatePicker() {
    // Since grid view is updated with Ajax, we need to fetch date format each update
    var input = document.getElementById('dateFormatDetails');
    var locale = document.getElementById('locale');
    var startdateElement = document.getElementById('SurveyDynamic_startdate');
    var datestampElement = document.getElementById('SurveyDynamic_datestamp');

    if ((input && input.value) && (locale && locale.value)) {
        var dateFormatDetails = JSON.parse(input.value);

        if (startdateElement) {
            initDatePicker(startdateElement, locale.value, dateFormatDetails.jsdate);
            startdateElement.addEventListener("hide.td", function () {
                reloadGrid();
            });
        }

        if (datestampElement) {
            initDatePicker(datestampElement, locale.value, dateFormatDetails.jsdate);
            datestampElement.addEventListener("hide.td", function () {
                reloadGrid();
            });
        }
    } else {
        console.ls.log('Internal error? Run reinstallResponsesFilterDatePicker, but find no input with name dateFormatDetails.');
    }
}

/**
 * reload gridview only when data of filter input has changed
 */
function reloadGrid() {
    var newData = $('#responses-grid .filters input, #responses-grid .filters select').serialize();
    if (filterData !== newData) {
        filterData = newData;
        $.fn.yiiGridView.update('responses-grid', {data: filterData});
    }
}

function onDocumentReadyListresponse() {
    $('#displaymode input').off('change.listresponse').on('change.listresponse', function (event) {
        $('#change-display-mode-form').find('input[type=submit]').trigger('click');
    });

}

$(document).off('pjax:scriptcomplete.listresponse').on('pjax:scriptcomplete.listresponse', onDocumentReadyListresponse);
$(document).off('bindscroll.listresponse').on('bindscroll.listresponse', reinstallResponsesFilterDatePicker);

function initColumnFilter() {
    // hide and submit Modal on click for pjax preventDefault submit
    $('#responses-column-filter-modal-submit').on('click', function (e) {
        e.preventDefault();
        var form = $('#responses-column-filter-modal form');
        form.submit();
        form.modal('hide');
    });

    // select all columns for the response table
    $('#responses-column-filter-modal-selectall').on('click', function (e) {
        e.preventDefault();
        $(".responses-multiselect-checkboxes .checkbox input").prop('checked', true);
    });

    // remove selection for the response table
    $('#responses-column-filter-modal-clear').on('click', function (e) {
        e.preventDefault();
        $(".responses-multiselect-checkboxes .checkbox input").prop('checked', false);
    });

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
}

function afterAjaxResponsesReload() {
    reinstallResponsesFilterDatePicker();
    initColumnFilter();
}
$(document).on('ready pjax:scriptcomplete', function() {
    onDocumentReadyListresponse();
    reinstallResponsesFilterDatePicker();
    initColumnFilter();
});