// $Id: surveysettings.js 9757 2011-02-09 20:52:33Z c_schmitz $

$(document).ready(function(){
    $("#template").change(templatechange);
    $("#template").keyup(templatechange);
    $("#copysurveyform").submit(copysurvey);
  /*  $("#urlparams").jqGrid({ url:jsonUrl,
        datatype: "json",
        colNames:['','Parameter','Destination'],
        colModel:[ {name:'id',index:'id', width:0},
                   {name:'parameter',index:'parameter', width:120},
                   {name:'targetqid',index:'targetqid', width:200} ],
        sortname: 'parameter',
        viewrecords: true,
        sortorder: "desc",
        multiselect: false,
        emptyrecords : 'No params configures',
         autowidth: true,
        caption: "URL parameters" });*/
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
