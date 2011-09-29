// $Id: surveysettings.js 9757 2011-02-09 20:52:33Z c_schmitz $

$(document).ready(function(){
    $("#template").change(templatechange);
    $("#template").keyup(templatechange);
    $("#copysurveyform").submit(copysurvey);
    $("#urlparams").jqGrid({ url:jsonUrl,
        datatype: "json",
        colNames:['Actions','','','Parameter','','','Target question'],
        colModel:[ {name:'act',index:'act', width:50,sortable:false},
                   {name:'id',index:'id', hidden:true},
                   {name:'sid',index:'sid', hidden:true},
                   {name:'parameter',index:'parameter', width:120},
                   {name:'targetqid',index:'targetqid', hidden:true},
                   {name:'targetsqid',index:'targetsqid', hidden:true},
                   {name:'title',index:'title', width:400}
                   ],
        sortname: 'parameter',
        pager: '#pagerurlparams',
        loadonce: true,
        hidegrid: false,
        pginput: false,
        pgbuttons: false,
        viewrecords: true,
        sortorder: "asc",
        editurl: jsonUrl, // this is dummy existing url
        emptyrecords : "No parameters defined.",
        caption: "URL parameters",
        gridComplete: function(){   var ids = jQuery("#urlparams").jqGrid('getDataIDs');
                                    for(var i=0;i < ids.length;i++)
                                    {
                                        var cl = ids[i];
                                        be = "<image style='cursor:pointer;' src='"+imageUrl+"/token_edit.png' value='E' onclick=\"editParameter('"+cl+"');\" />";
                                        de = "<image style='cursor:pointer;' src='"+imageUrl+"/token_delete.png' value='D' onclick=\"if (confirm('Are you sure you want to delete this URL parameter?')) jQuery('#urlparams').delRowData('"+cl+"');\" />";
                                        jQuery("#urlparams").jqGrid('setRowData',ids[i],{act:be+de});
                                    }
        }
    }).navGrid('#pagerurlparams',{  del:false,
                                    edit:false,
                                    refresh:false,
                                    search:false,
                                    add:false}, {})
    .jqGrid('navButtonAdd',"#pagerurlparams",{buttonicon:'ui-icon-plusthick',
                                              caption: 'Add parameter',
                                              id: 'btnAddParam',
                                              onClickButton: newParameter});
    $("#dlgEditParameter").dialog({ autoOpen: false,
                                    width: 700 });
    $('#btnCancel').click(function(){
        $("#dlgEditParameter").dialog("close");
    });

    $('#btnSave').click(saveParameter);
    $('#addnewsurvey').submit(PostParameterGrid);

});

function PostParameterGrid()
{
    rows= jQuery("#urlparams").jqGrid('getRowData');
    $('#allurlparams').val($.toJSON(rows));
}

function saveParameter()
{
    sParamname=$.trim($('#paramname').val());
    if (sParamname=='' || !/^[a-zA-Z_][a-zA-Z0-9_]*$/.test(sParamname) || sParamname=='sid' || sParamname=='newtest' || sParamname=='token' || sParamname=='lang')
    {
        alert ('You have to enter a valid parameter name.');
        return;
    }
    $("#dlgEditParameter").dialog("close");
    sIDs=$("#targetquestion").val();
    aIDs=sIDs.split('-');
    sTargetQID=aIDs[0];
    sTargetSQID=aIDs[1];
    if ($("#dlgEditParameter").data('action')=='add')
    {
       sGUID=guidGenerator();
       jQuery("#urlparams").addRowData(sGUID, { act: "<image style='cursor:pointer;' src='"+imageUrl+"/token_edit.png' value='E' onclick=\"editParameter('"+sGUID+"');\" />"
                                                    +"<image style='cursor:pointer;' src='"+imageUrl+"/token_delete.png' value='D' onclick=\"if (confirm('Are you sure you want to delete this URL parameter?')) jQuery('#urlparams').delRowData('"+sGUID+"');\" />",
                                                id:sGUID,
                                                sid:$('#id').val(),
                                                parameter:sParamname,
                                                targetqid:sTargetQID,
                                                targetsqid:sTargetSQID,
                                                title:$("#targetquestion option:selected").text()});
    }
    else
    {
        jQuery("#urlparams").setRowData($("#dlgEditParameter").data('rowid'),{  parameter:sParamname,
                                                                                targetqid:sTargetQID,
                                                                                targetsqid:sTargetSQID,
                                                                                title:$("#targetquestion option:selected").text()
                                                                                });

    }
}
function newParameter(event)
{
    dialogTitle='Add URL parameter';
    $("#targetquestion").val('');
    $('#paramname').val('');
    $("#dlgEditParameter").data('action','add');
    $("#dlgEditParameter").dialog("option", "title", dialogTitle);
    $("#dlgEditParameter").dialog("open");
}

function editParameter(rowid)
{
    dialogTitle='Edit URL parameter';
    aRowData=jQuery("#urlparams").getRowData(rowid);
    $("#targetquestion").val(aRowData.targetqid+'-'+aRowData.targetsqid);
    $('#paramname').val(aRowData.parameter);
    $("#dlgEditParameter").data('action','edit');
    $("#dlgEditParameter").data('rowid',rowid);
    $("#dlgEditParameter").dialog("option", "title", dialogTitle);
    $("#dlgEditParameter").dialog("open");
}

function templatechange()
{
    standardtemplates=['basic','bluengrey','business_grey','citronade','clear_logo','default','eirenicon','limespired','mint_idea','sherpa','vallendar'];
    if (in_array(this.value,standardtemplates))
    {
        $("#preview").attr('src',standardtemplaterooturl+'/'+this.value+'/preview.png');
    }
    else
    {
    $("#preview").attr('src',templaterooturl+'/'+this.value+'/preview.png');
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
