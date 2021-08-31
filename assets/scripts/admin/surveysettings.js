
var defineActions = function (dataArray) {
    var iconRow = $('<div class="icon-btn-row"></div>');
    var buttonEdit = $('<button><i class="fa fa-pencil"></i></button>');
    var buttonDelete = $('<button><i class="fa fa-trash text-danger"></i></button>');
    var container = $('<div><div class="icon-btn-row"></div></div>');
    buttonEdit
        .addClass('btn btn-sm btn-default surveysettings_edit_intparameter')
        .data('id', dataArray.id)
        .data('sid', dataArray.sid)
        .data('qid', (dataArray.qid || null))
        .data('sqid', (dataArray.qid || null))
        .appendTo(iconRow);
    buttonDelete
        .addClass('btn btn-sm btn-default surveysettings_delete_intparameter')
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
                sid: window.PanelIntegrationData.surveyid,
                qid: $('#targetquestion').val().split('-').shift() || '',
                sqid: $('#targetquestion').val().split('-').pop() || ''
            }),
            'parameter': sParamname,
            'targetQuestionText': $('#targetquestion option:selected').text() || rowData.targetQuestionText,
            'sid': window.PanelIntegrationData.surveyid,
            qid: $('#targetquestion').val().split('-').shift() || '',
            sqid: $('#targetquestion').val().split('-').pop() || ''
        });
    } else {
        var rowData = {
            'id': rowData.id,
            'actionBtn': defineActions({
                id: rowData.id,
                sid: window.PanelIntegrationData.surveyid,
                qid: rowData.qid,
                sqid: rowData.sqid
            }),
            'parameter': sParamname,
            'targetQuestionText': $('#targetquestion option:selected').text() || rowData.targetQuestionText,
            sid: window.PanelIntegrationData.surveyid,
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
    $('#dlgEditParameter').dialog('option', 'title', window.PanelIntegrationData.i10n['Add URL parameter']);
    $('#dlgEditParameter').dialog('open');
}

function editParameter(event, aRowData) {

    $('#targetquestion').val(aRowData.qid + '-' + aRowData.sqid);
    $('#paramname').val(aRowData.parameter);
    $('#dlgEditParameter').data('action', 'edit');
    $('#dlgEditParameter').data('rawdata', JSON.stringify(aRowData));
    $('#dlgEditParameter').dialog('option', 'title', window.PanelIntegrationData.i10n['Edit URL parameter']);
    $('#dlgEditParameter').dialog('open');
}

function deleteParameter(event, aRowData) {
    $('#urlparams').DataTable().row('#' + aRowData.id).remove();
    $('#urlparams').DataTable().draw();
    PostParameterGrid();
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

$(document).on('ready  pjax:scriptcomplete', function(){
    if (window.PanelIntegrationData) {
        var i10n = window.PanelIntegrationData.i10n;
        $.ajax({
            url : window.PanelIntegrationData.getParametersUrl,
            dataType: 'json',
            method: "GET",
            success: function(results){
                // console.log(results);
                var dataSet = [];
                $.each(results.rows, function(i,row){
                    var rowArray = {
                    "id"                 : row.id,
                    "actionBtn"          : defineActions(row),
                    "parameter"          : row.parameter,
                    "targetQuestionText" : row.questionTitle,
                    "sid"                : row.sid,
                    "qid"                : row.targetqid || "",
                    "sqid"               : row.targetsqid || ""
                    };
                    dataSet.push(rowArray);
                });

                $("#urlparams").DataTable({
                    columns:[
                        {data: 'id', visible: false},
                        {data: 'actionBtn', label: i10n['Action'], orderable: false},
                        {data: 'parameter', label: i10n['Parameter']},
                        {data: 'targetQuestionText', label: i10n['Target question']},
                        {data: 'sid', visible: false},
                        {data: 'qid', visible: false},
                        {data: 'sqid', visible: false}
                    ],
                    "language":{
                        "emptyTable":i10n['No parameters defined'],
                        "search":i10n['Search prompt'],
                        "infoEmpty":'',
                        "info":i10n['Progress']
                    }    
                    ,
                    data: dataSet,
                    createdRow: function(thisRow,data,dataIndex){
                        $(thisRow).data('rawdata',JSON.stringify(data));
                    },
                    rowId: 'id',
                    paging: false,
                    dom: "<'#dt-toolbar'>f<t>i"
                });
                var addParamButton = $('<button class="btn btn-success" id="addParameterButton">'+i10n['Add URL parameter']+'</button>');
                $('#dt-toolbar').addClass('pull-left clearfix').append(addParamButton)
                    .on('click', '#addParameterButton', function(e){
                        e.preventDefault();
                        newParameter(e);
                    });
                $("#urlparams").css('width','100%')
                    .on('click', '.surveysettings_edit_intparameter', function(e){
                        e.preventDefault();
                        // console.log(($(this).closest('tr').data('rawdata')));  
                        editParameter(e,JSON.parse($(this).closest('tr').data('rawdata')));                  
                    })
                    .on('click', '.surveysettings_delete_intparameter', function(e){
                        e.preventDefault();
                    deleteParameter(e,JSON.parse($(this).closest('tr').data('rawdata')));
                    });
                    
            },
            error: console.log
        }   );

        $("#dlgEditParameter").dialog({ 
            autoOpen: false, 
            width: 700 
        }); 
    
        $('#btnCancelParams').click(function(){ 
            $("#dlgEditParameter").dialog("close"); 
        }); 
    
        $('#btnSaveParams').click(saveParameter); 
        $('#addnewsurvey').submit(PostParameterGrid); 
        $('#globalsetting').submit(PostParameterGrid);  // This is the name of survey settings update form
        $('#panelintegration').submit(PostParameterGrid);
    }
});
