var filterData = {};

/**
 * reload gridview only when data of filter input has changed
 */

function initColumnFilter()
{
    // hide and submit Modal on click for pjax preventDefault submit
    $('#' + modalId + '-submit').on('click', function (e) {
        e.preventDefault();
        var form = $('#' + modalId);
        var model = $('#' + modalId + ' input.model-name').val();
        var filterData = $('#' + modalId + ' .checkbox input:checked').map(function () {
            if ($(this).val().trim() !== '') {
                return $(this).val();
            }
        }).get();

        if (filterData.length === 0) {
            filterData = [];
        }

        $.fn.yiiGridView.update('survey-grid', {data: {model: model, selectColumns : 'select', columnsSelected: filterData}});
        form.modal('hide');
    });

    // select all columns for the response table
    $('#' + modalId + '-selectall').on('click', function (e) {
        e.preventDefault();
        $(".responses-multiselect-checkboxes .checkbox input").prop('checked', true);
    });

    // remove selection for the response table
    $('#' + modalId + '-clear').on('click', function (e) {
        e.preventDefault();
        $(".responses-multiselect-checkboxes .checkbox input").prop('checked', function () {
            if ($(this).val() !== "") {
                return false
            }
        });
    });

    // cancel current modifications to the selection of columns for the response table
    $('#' + modalId + '-cancel').on('click', function (e) {
        e.preventDefault();
        var form = $('#' + modalId + ' form');
        var filteredColumns = form.data('filtered-columns');

        $(".responses-multiselect-checkboxes .checkbox input").prop('checked', false);
        filteredColumns.forEach(function (item) {
            $(".responses-multiselect-checkboxes .checkbox input[value=" + item + "]").prop('checked', true);
        });
        form.modal('hide');
    });
}

function afterAjaxResponsesReload()
{
    initColumnFilter();
}
$(document).on('ready pjax:scriptcomplete', function () {
    initColumnFilter();
});
