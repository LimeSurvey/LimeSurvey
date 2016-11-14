// $Id: surveysettings.js 9757 2011-02-09 20:52:33Z c_schmitz $

    $("#copysurveyform").submit(copysurvey);
    
var defineActions = function(dataArray){
    var buttonEdit = $('<button><i class="fa fa-edit"></i></button>');
    var buttonDelete = $('<button><i class="fa fa-trash"></i></button>');
    var container = $('<div></div>');
    buttonEdit
        .addClass('btn btn-xs btn-default surveysettings_edit_intparameter')
        .data('id',dataArray.id)
        .data('sid',dataArray.sid)
        .data('qid',(dataArray.qid || null))
        .data('sqid',(dataArray.qid || null))
        .appendTo(container);
    buttonDelete
        .addClass('btn btn-xs btn-danger surveysettings_delete_intparameter')
        .data('id',dataArray.id)
        .data('sid',dataArray.sid)
        .data('qid',(dataArray.qid || null))
        .data('sqid',(dataArray.qid || null))
        .appendTo(container);
    
    return container.html();
};


$(document).on('click',"[data-copy] :submit",function(){
    $("form :input[value='"+$(this).val()+"']").click();
});
$(document).on('submit',"#addnewsurvey",function(){
    $('#addnewsurvey').attr('action',$('#addnewsurvey').attr('action')+location.hash);// Maybe validate before ?
});
$(document).ready(function(){

    $('#template').on('change keyup', function(event){
        templatechange($(this).val());
    });

    $("[data-copy]").each(function(){
        $(this).html($("#"+$(this).data('copy')).html());
    });
    
    $.ajax({
        url : jsonUrl,
        dataType: 'json',
        method: "GET",
        success: function(results){
            console.log(results);
            var dataSet = [];
            $.each(results.rows, function(i,row){
                var rowArray = {
                "id" : row.id,
                "actionBtn" : defineActions(row.datas),
                "parameter" : row.parameter,
                "targetQuestionText" : row.question,
                "sid" : row.datas.sid,
                "qid" : row.datas.qid,
                "sqid" : row.datas.sqid
                };
                dataSet.push(rowArray);
            });

            $("#urlparams").DataTable({
                columns:[
                    {title: 'id', visible: false},
                    {name: 'actionBtn', label: sAction, orderable: false},
                    {name: 'parameter', label: sParameter},
                    {name: 'targetQuestionText', label: sTargetQuestion},
                    {title: 'sid', visible: false},
                    {title: 'qid', visible: false},
                    {title: 'sqid', visible: false}
                    ],
                data: dataSet,
                createdRow: function(thisRow,data,dataIndex){
                    console.log(data);
                    $(thisRow).data('rawdata',JSON.stringify(data));
                },
                rowId: 'id',
                paging: false,
                dom: "<'#dt-toolbar'>f<t>i"
            });
            var addParamButton = $('<button class="btn btn-success" id="addParameterButton">'+sAddParam+'</button>');
            $('#dt-toolbar').addClass('pull-left clearfix').append(addParamButton)
                .on('click', '#addParameterButton', function(e){
                    e.preventDefault();
                    newParameter(e);
                });
            $("#urlparams").css('width','100%')
                .on('click', '.surveysettings_edit_intparameter', function(e){
                    e.preventDefault();
                    console.log(JSON.parse($(this).closest('tr').data('rawdata')));  
                    editParameter(e,JSON.parse($(this).closest('tr').data('rawdata')));                  
                })
                .on('click', '.surveysettings_delete_intparameter', function(e){
                    e.preventDefault();
                    console.log($(this));
                });
                
        },
        error: console.log
}   );
/*
    // $("#urlparams").jqGrid({ url:jsonUrl,
    //     datatype: "json",
    //     colNames:[sAction,'','',sParameter,'','',sTargetQuestion],
    //     colModel:[
    //         {name:'act',index:'act', width:80,sortable:false},
    //         {name:'id',index:'id', hidden:true},
    //         {name:'sid',index:'sid', hidden:true},
    //         {name:'parameter',index:'parameter', width:100},
    //         {name:'targetqid',index:'targetqid', hidden:true},
    //         {name:'targetsqid',index:'targetsqid', hidden:true},
    //         {name:'title',index:'title', width:240}
    //     ],
    //     direction: $('html').attr('dir'),
    //     sortname: 'parameter',
    //     pager: '#pagerurlparams',
    //     loadonce: true,
    //     hidegrid: false,
    //     pginput: false,
    //     pgbuttons: false,
    //     viewrecords: true,
    //     width: 420,
    //     shrinkToFit: true,
    //     rowNum: 100,
    //     sortorder: "asc",
    //     editurl: jsonUrl, // this is dummy existing url
    //     emptyrecords : sNoParametersDefined,
    //     caption: sURLParameters,
    //     gridComplete: function() {
    //         var ids = jQuery("#urlparams").jqGrid('getDataIDs');
    //         for(var i=0;i < ids.length;i++)
    //         {
    //             var cl = ids[i];
    //             be = "<span data-toggle='tooltip' data-placement='top' data-original-title='Edit' title='Edit' style='cursor:pointer;' class='glyphicon glyphicon-edit text-success' value='E' onclick=\"editParameter('"+cl+"');\"></span>";
    //             de = "<span data-toggle='tooltip' data-placement='top' data-original-title='Delete' title='Delete' style='cursor:pointer;' class='glyphicon glyphicon-trash text-warning' value='D' onclick=\"if (confirm(sSureDelete)) jQuery('#urlparams').delRowData('"+cl+"');\"></span>";
    //             jQuery("#urlparams").jqGrid('setRowData',ids[i],{act:be+de});
    //         }
    //         //$('[data-toggle="tooltip"]').tooltip(); // TODO: Does not work - why?
    //     }
    // }).navGrid('#pagerurlparams', {
    //     del:false,
    //     edit:false,
    //     refresh:false,
    //     search:false,
    //     add:false
    // }, {}).jqGrid('navButtonAdd',"#pagerurlparams", {
    //     buttonicon:'ui-icon-plusthick',
    //     caption: sAddParam,
    //     id: 'btnAddParam',
    //     onClickButton: newParameter
    // });
    */

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
    
    $( "#tabs" ).bind( "tabsactivate", function(event, ui) {
        if (ui.newTab.index() > 4)    // Hide on import and copy tab, otherwise show
        {
            $('#btnSave').hide();
        }
        else
        {
            $('#btnSave').show();
        }
    });

});

/**
 * Bind to submit event
 */
function PostParameterGrid()
{
    var rowsData = [];
    jQuery("#urlparams").DataTable().rows().each(
        function(rowId, tableLoop, rowLoop){
            rowsData.push(this.data());
        }
    )
    console.log(rowsData);
    $('#allurlparams').val(JSON.stringify(rows));
    
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
function saveParameter()
{
    sParamname=$.trim($('#paramname').val());
    if (sParamname=='' || !/^[a-zA-Z_][a-zA-Z0-9_]*$/.test(sParamname) || sParamname=='sid' || sParamname=='newtest' || sParamname=='token' || sParamname=='lang')
    {
        $("#dlgEditParameter").prepend('<div class="alert alert-danger alert-dismissible fade in"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>'+sEnterValidParam+'</div>');
        return;
    }
    $("#dlgEditParameter").dialog("close");
    sIDs=$("#targetquestion").val();
    aIDs=sIDs.split('-');
    sTargetQID=aIDs[0];
    sTargetSQID=aIDs[1];
    if ($("#dlgEditParameter").data('action')=='add') {
       sGUID = guidGenerator();
       $("#urlparams").DataTable().row.add([
            sGUID,
            defineActions({
                id  : sGUID,
                sid : $('#id').val(), 
                qid : sTargetQID,
                sqid: sTargetSQID
            }),
            sParamname,
            $("#targetquestion option:selected").text()
        ]);
    } else {
        var rowId = $('#dlgEditParameter').data('rowid');
         $("#urlparams").DataTable().row('#'+rowId).edit([
            rowId,
            sParamname,
            defineActions({
                id: sGUID,
                sid: sTargetQID, 
                qid : sTargetQID,
                sqid: sTargetSQID
            }),
            $("#targetquestion option:selected").text()
         ]);

    }
    $("#urlparams").DataTable().draw();
}

function newParameter(data)
{
    $("#targetquestion").val('');
    $('#paramname').val('');
    $("#dlgEditParameter").data('action','add');
    $("#dlgEditParameter").dialog("option", "title", sAddParam);
    $("#dlgEditParameter").dialog("open");
}

function editParameter(event, aRowData){

    $("#targetquestion").val(aRowData.qid+'-'+aRowData.sqid);
    $('#paramname').val(aRowData.parameter);
    $("#dlgEditParameter").data('action','edit');
    $("#dlgEditParameter").data('rowid',aRowData.id);
    $("#dlgEditParameter").dialog("option", "title", sEditParam);
    $("#dlgEditParameter").dialog("open");
}

function templatechange(template)
{
    standardtemplates=[
        'default',
        'blue_sky',
        'metro_ode',
        'electric_black',
        'night_mode',
        'flat_and_modern',
        'news_paper',
        'light_and_shadow',
        'material_design',
        'readable',
        'sandstone',
        'minimalist',
        'gunmetal',
        'super_blue',
        'ubuntu_orange',
        'yeti'
];
    if (in_array(template,standardtemplates))
    {
        $("#preview").attr('src',standardtemplaterooturl+'/'+template+'/preview.png');
    }
    else
    {
        $("#preview").attr('src',templaterooturl+'/'+template+'/preview.png');
    }
}

function copysurvey()
{
    sMessage='';
    if ($('#copysurveylist').val()=='')
    {
        sMessage = sMessage+sSelectASurveyMessage;
    }
    if ($('#copysurveyname').val()=='')
    {
        sMessage = sMessage+'\n\r'+sSelectASurveyName;
    }
    if (sMessage!='')
    {
       alert(sMessage);
       return false;
    }
}

function in_array (needle, haystack, argStrict) {

    var key = '', strict = !!argStrict;

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
    var S4 = function() {
       return (((1+Math.random())*0x10000)|0).toString(16).substring(1);
    };
    return (S4()+S4()+"-"+S4()+"-"+S4()+"-"+S4()+"-"+S4()+S4()+S4());
}
