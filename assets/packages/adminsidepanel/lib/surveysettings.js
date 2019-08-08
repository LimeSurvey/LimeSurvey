$('#copysurveyform').submit(copysurvey);

var defineActions = function (dataArray) {
    var buttonEdit = $('<button><i class="fa fa-edit"></i></button>');
    var buttonDelete = $('<button><i class="fa fa-trash"></i></button>');
    var container = $('<div></div>');
    buttonEdit
        .addClass('btn btn-xs btn-default surveysettings_edit_intparameter')
        .data('id', dataArray.id)
        .data('sid', dataArray.sid)
        .data('qid', (dataArray.qid || null))
        .data('sqid', (dataArray.qid || null))
        .appendTo(container);
    buttonDelete
        .addClass('btn btn-xs btn-danger surveysettings_delete_intparameter')
        .data('id', dataArray.id)
        .data('sid', dataArray.sid)
        .data('qid', (dataArray.qid || null))
        .data('sqid', (dataArray.qid || null))
        .appendTo(container);

    return container.html();
};


$(document).on('click', '[data-copy] :submit', function () {
    $('form :input[value=\'' + $(this).val() + '\']').click();
});
// $(document).on('submit',"#addnewsurvey",function(){
//     $('#addnewsurvey').attr('action',$('#addnewsurvey').attr('action')+location.hash);// Maybe validate before ?
// });
$(document).on('ready  pjax:scriptcomplete', function () {

    $('#template').on('change keyup', function (event) {
        console.ls.log('TEMPLATECHANGE', event);
        templatechange($(this));
    });

    $('[data-copy]').each(function () {
        $(this).html($('#' + $(this).data('copy')).html());
    });

    var jsonUrl = jsonUrl || null;

    $('#tabs').on('tabsactivate', function (event, ui) {
        if (ui.newTab.index() > 4) // Hide on import and copy tab, otherwise show
        {
            $('#btnSave').hide();
        } else {
            $('#btnSave').show();
        }
    });
});
/**
 * Bind to submit event
 */
function PostParameterGrid() {
    var rowsData = [],
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
    $('#allurlparams').val(jsonString);

    // if (($('#allowregister').val()=='Y' || $.trim($('#emailresponseto').val())!='' || $.trim($('#emailnotificationto').val())!='')&& $.trim($('#adminemail').val())=='')
    // {
    //     alert (sAdminEmailAddressNeeded);
    //     $("#tabs").tabs("select", 0);
    //      $('#adminemail').focus();
    //     return false;
    // }

}

/**
 * Save row to table
 *
 * @return void
 */
function saveParameter() {
    var sParamname = $.trim($('#paramname').val());
    if (sParamname == '' || !/^[a-zA-Z_][a-zA-Z0-9_]*$/.test(sParamname) || sParamname == 'sid' || sParamname == 'newtest' || sParamname == 'token' || sParamname == 'lang') {
        $('#dlgEditParameter').prepend('<div class="alert alert-danger alert-dismissible fade in"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>' + sEnterValidParam + '</div>');
        return;
    }
    $('#dlgEditParameter').dialog('close');
    try {
        var rowData = JSON.parse($('#dlgEditParameter').data('rawdata'));
    } catch (e) {
        rowData = {};
    }


    if ($('#dlgEditParameter').data('action') == 'add') {
        var sGUID = guidGenerator();
        $('#urlparams').DataTable().row.add({
            'id': sGUID,
            'actionBtn': defineActions({
                id: sGUID,
                sid: iSurveyId,
                qid: $('#targetquestion').val().split('-').shift() || '',
                sqid: $('#targetquestion').val().split('-').pop() || ''
            }),
            'parameter': sParamname,
            'targetQuestionText': $('#targetquestion option:selected').text() || rowData.targetQuestionText,
            'sid': iSurveyId,
            qid: $('#targetquestion').val().split('-').shift() || '',
            sqid: $('#targetquestion').val().split('-').pop() || ''
        });
    } else {
        var rowData = {
            'id': rowData.id,
            'actionBtn': defineActions({
                id: rowData.id,
                sid: iSurveyId,
                qid: rowData.qid,
                sqid: rowData.sqid
            }),
            'parameter': sParamname,
            'targetQuestionText': $('#targetquestion option:selected').text() || rowData.targetQuestionText,
            sid: iSurveyId,
            qid: $('#targetquestion').val().split('-').shift() || '',
            sqid: $('#targetquestion').val().split('-').pop() || ''
        };
        $($('#urlparams').DataTable().row('#' + rowData.id).node()).data('rawdata', JSON.stringify(rowData));
        $('#urlparams').DataTable().row('#' + rowData.id).data(rowData);

    }
    $('#urlparams').DataTable().draw();
    PostParameterGrid();
}

function newParameter(data) {
    $('#targetquestion').val('');
    $('#paramname').val('');
    $('#dlgEditParameter').data('action', 'add');
    $('#dlgEditParameter').dialog('option', 'title', sAddParam);
    $('#dlgEditParameter').dialog('open');
}

function editParameter(event, aRowData) {

    $('#targetquestion').val(aRowData.qid + '-' + aRowData.sqid);
    $('#paramname').val(aRowData.parameter);
    $('#dlgEditParameter').data('action', 'edit');
    $('#dlgEditParameter').data('rawdata', JSON.stringify(aRowData));
    $('#dlgEditParameter').dialog('option', 'title', sEditParam);
    $('#dlgEditParameter').dialog('open');
}

function deleteParameter(event, aRowData) {
    $('#urlparams').DataTable().row('#' + aRowData.id).remove();
    $('#urlparams').DataTable().draw();
    PostParameterGrid();
}

function templatechange($element) {
    $('#preview-image-container').html(
        '<div style="height:200px;" class="ls-flex ls-flex-column align-content-center align-items-center"><i class="fa fa-spinner fa-spin fa-3x fa-fw"></i></div>'
    );
    $.ajax({
        url: $element.data('updateurl'),
        data: {templatename : $element.val()},
        method: 'POST',
        dataType: 'json',
        success: function(data){
            $('#preview-image-container').html(data.image);
        },
        error: console.ls.error
    });
}

function copysurvey() {
    let sMessage = '';
    if ($('#copysurveylist').val() == '') {
        sMessage = sMessage + sSelectASurveyMessage;
    }
    if ($('#copysurveyname').val() == '') {
        sMessage = sMessage + '\n\r' + sSelectASurveyName;
    }
    if (sMessage != '') {
        alert(sMessage);
        return false;
    }
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
