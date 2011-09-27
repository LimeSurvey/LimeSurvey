// $Id: surveysettings.js 9757 2011-02-09 20:52:33Z c_schmitz $

$(document).ready(function(){
    $("#template").change(templatechange);
    $("#template").keyup(templatechange);
    $("#copysurveyform").submit(copysurvey);
    $("#urlparams").jqGrid({ url:jsonUrl,
        datatype: "json",
        colNames:['Actions','','','Parameter','','','Target question'],
        colModel:[ {name:'act',index:'act', width:65,sortable:false},
                   {name:'id',index:'id', hidden:true},
                   {name:'sid',index:'sid', hidden:true},
                   {name:'parameter',index:'parameter', width:120},
                   {name:'targetqid',index:'targetqid', hidden:true},
                   {name:'targetsqid',index:'targetsqid', hidden:true},
                   {name:'title',index:'title', width:300}
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
                                        be = "<image style='cursor:pointer;' src='"+imageUrl+"/token_edit.png' value='E' onclick=\"jQuery('#urlparams').editRow('"+cl+"');\" />";
                                        de = "<image style='cursor:pointer;' src='"+imageUrl+"/token_delete.png' value='D' onclick=\"if (confirm('Are you sure you want to delete this URL parameter?')) jQuery('#urlparams').delRowData("+cl+");\" />";
                                        jQuery("#urlparams").jqGrid('setRowData',ids[i],{act:be+de});
                                    }
        }
    }).navGrid('#pagerurlparams',{del:false,
                                    edit:false,
                                    refresh:false,
                                    search:false}, {});

});

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
