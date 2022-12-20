
var defineActions = function (dataArray) {
    var iconRow = $('<div class="icon-btn-row"></div>');
    var buttonEdit = $('<button><i class="fa fa-pencil"></i></button>');
    var buttonDelete = $('<button><i class="fa fa-trash text-danger"></i></button>');
    var container = $('<div><div class="icon-btn-row"></div></div>');
    buttonEdit
        .addClass('btn btn-sm btn-outline-secondary surveysettings_edit_intparameter')
        .data('id', dataArray.id)
        .data('sid', dataArray.sid)
        .data('qid', (dataArray.qid || null))
        .data('sqid', (dataArray.qid || null))
        .appendTo(iconRow);
    buttonDelete
        .addClass('btn btn-sm btn-outline-secondary surveysettings_delete_intparameter')
        .data('id', dataArray.id)
        .data('sid', dataArray.sid)
        .data('qid', (dataArray.qid || null))
        .data('sqid', (dataArray.qid || null))
        .appendTo(iconRow);
    iconRow.appendTo(container);
    return container.html();
};

/**
 * Bind to submit event
 */
function PostParameterGrid() {
    /*var rowsData = [],
        dt = $('#urlparams').DataTable();
    dt.rows().every(
        function (rowId, tableLoop, rowLoop) {
            rowsData.push(dt.row(rowId).data());
        }
    );
    var jsonString = '{}';
    try {
        jsonString = JSON.stringify(rowsData);
    } catch (e) {}
    $('#allurlparams').val(jsonString);*/

}

/**
 * Save row to table
 *
 * @return void
 */
function saveParameter() {
    var sParamname = $.trim($('#paramname').val());
    if (sParamname == '' || !/^[a-zA-Z_][a-zA-Z0-9_]*$/.test(sParamname) || sParamname == 'sid' || sParamname == 'newtest' || sParamname == 'token' || sParamname == 'lang') {
        $('#dlgEditParameter').prepend('<div class="alert alert-danger alert-dismissible fade in"><button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' + sEnterValidParam + '</div>');
        return;
    }
    $('#dlgEditParameter').dialog('close');
    try {
        var rowData = JSON.parse($('#dlgEditParameter').data('rawdata'));
    } catch (e) {
        rowData = {};
    }

    var URLParam = {};
    var postUrl = $('#dlgEditParameter').data('save-url');

    if ($('#dlgEditParameter').data('action') == 'add') {
        URLParam.parameter = sParamname;
        URLParam.targetqid = $('#targetquestion').val().split('-').shift() || '';
        URLParam.targetsqid = $('#targetquestion').val().split('-').pop() || '';
    } else {
        URLParam.id = rowData.id;
        URLParam.parameter = sParamname;
        URLParam.targetqid = $('#targetquestion').val().split('-').shift() || '';
        URLParam.targetsqid = $('#targetquestion').val().split('-').pop();
    }

    var postDatas = {
        surveyId: window.PanelIntegrationData.surveyid,
        URLParam: URLParam
    };

    sendPostAndUpdate(postUrl, postDatas);
}

function newParameter(data) {
    $('#targetquestion').val('');
    $('#paramname').val('');
    $('#dlgEditParameter').data('action', 'add');
    $('#dlgEditParameter').dialog('option', 'title', window.PanelIntegrationData.i10n['Add URL parameter']);
    $('#dlgEditParameter').dialog('open');
}

function editParameter(event, aRowData) {

    $('#targetquestion').val((aRowData.qid || '') + '-' + (aRowData.sqid || ''));
    $('#paramname').val(aRowData.parameter);
    $('#dlgEditParameter').data('action', 'edit');
    $('#dlgEditParameter').data('rawdata', JSON.stringify(aRowData));
    $('#dlgEditParameter').dialog('option', 'title', window.PanelIntegrationData.i10n['Edit URL parameter']);
    $('#dlgEditParameter').dialog('open');
}

function deleteParameter(event, aRowData) {
    var postUrl = $('#dlgEditParameter').data('delete-url');
    var postDatas = {
        surveyId: window.PanelIntegrationData.surveyid,
        URLParam: {id: aRowData.id}
    };
    sendPostAndUpdate(postUrl, postDatas);
}

function in_array(needle, haystack, argStrict) {

    var key = '',
        strict = !!argStrict;

    if (strict) {
        for (key in haystack) {
            if (haystack[key] === needle) {
                return true;
            }
        }
    } else {
        for (key in haystack) {
            if (haystack[key] == needle) {
                return true;
            }
        }
    }

    return false;
}

function guidGenerator() {
    var S4 = function () {
        return (((1 + Math.random()) * 0x10000) | 0).toString(16).substring(1);
    };
    return (S4() + S4() + '-' + S4() + '-' + S4() + '-' + S4() + '-' + S4() + S4() + S4());
}

/**
 * Validate settings form depending on form name
 */
function validateSettingsForm($form) {
    switch ($form.attr('id')) {
        case 'publication':
            return LS.validateEndDateHigherThanStart(
                $('#startdate_datetimepicker').data('DateTimePicker'),
                $('#expires_datetimepicker').data('DateTimePicker'),
                () => {LS.LsGlobalNotifier.createAlert(expirationLowerThanStartError, 'danger')}
            );
        default:
            return true;
    }
}

/**
 * Validates that an end date is not lower than a start date
 */
function validateEndDateHigherThanStart(startDatePicker, endDatePicker, errorMessage) {
    if (!startDatePicker || !startDatePicker.date()) {
        return true;
    }
    if (!endDatePicker || !endDatePicker.date()) {
        return true;
    }
    const difference = endDatePicker.date().diff(startDatePicker.date());
    if (difference >= 0) {
        return true;
    }
    LS.LsGlobalNotifier.createAlert(errorMessage, 'danger');
    return false;
}

function sendPostAndUpdate(url, data) {
    var postDatas = data || {};
    postDatas[LS.data.csrfTokenName] = LS.data.csrfToken;

    // Ajax request
    $.ajax({
        url: url,
        type: 'POST',
        data: postDatas,

        success : function(result) {
            if (!result.success) {
                var errorMsg = result.message || '';
                if (!errorMsg) errorMsg = "Unexpected error";
                LS.LsGlobalNotifier.createAlert(errorMsg, 'danger');
                return;
            }

            LS.LsGlobalNotifier.createAlert(result.message, 'success');

            try {
                $.fn.yiiGridView.update('urlparams');
            } catch (e){
                if (e) {
                    console.ls.error(e);
                }
            }
        },
        error :  function(result){
            LS.LsGlobalNotifier.createAlert(result.statusText ?? "Unexpected error", 'danger');
        }
    });
}

function searchParameters() {
    var data = {
        search_query: $('#search_query').val()
    };
    $.fn.yiiGridView.update('urlparams', {data: data});
}

$(document).on('click', '#addParameterButton', function(e){
    e.preventDefault();
    newParameter(e);
});
$(document).on('click', '#urlparams .surveysettings_edit_intparameter', function(e){
    e.preventDefault();
    editParameter(e,$(this).closest('tr').data());
});
$(document).on('click', '#urlparams .surveysettings_delete_intparameter', function(e){
    e.preventDefault();
    $(this).prop('disabled', true);
    deleteParameter(e,$(this).closest('tr').data());
});
$(document).on('click', '#btnCancelParams', function(){ 
    $("#dlgEditParameter").dialog("close"); 
});
$(document).on('click', '#btnSaveParams', saveParameter);

$(document).on('ready  pjax:scriptcomplete', function(){
    if (window.PanelIntegrationData) {
        $("#dlgEditParameter").dialog({ 
            autoOpen: false, 
            width: 700 
        });
        $("#dlgEditParameter").removeClass('hide');
    }
});

$(document).on('click', '#searchParameterButton', searchParameters);
$(document).on('change', '#integrationPanelPager #pageSize', function(){
    $.fn.yiiGridView.update('urlparams',{ data:{ pageSize: $(this).val() }});
});