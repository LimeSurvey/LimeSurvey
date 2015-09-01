/*
* LimeSurvey
* Copyright (C) 2007 The LimeSurvey Project Team / Carsten Schmitz
* All rights reserved.
* License: GNU/GPL License v2 or later, see LICENSE.php
* LimeSurvey is free software. This xversion may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*
*/

/* Tooltip only on mouseenter and only if there are no title
 * This allow to set tooltip only when needed
 */
$(document).on("mouseenter",".browsetable thead th:not([title])",function(){
  $(this).attr('title',$(this).find(".questiontext").text());
  $(this).tooltip({ tooltipClass: "tooltip-text" });//,track: true allow to update always tooltip, but seems really annoying
});
$(document).on("mouseenter",".browsetable tbody td:not([title])",function(){
  if($(this).text().length>20)// 20 seem a good value, maybe less (10 ?)
  {
    $(this).attr('title',$(this).text());
    $(this).tooltip({ tooltipClass: "tooltip-text" });
  }
  else
  {
    $(this).attr('title',"");// Don't do this again
  }
});
$(document).ready(function(){
    $('ul.sf-menu').superfish({
        speed:'fast'
    }); 
    $("#selectall").click(function(){
        $('.cbResponseMarker').attr('checked',$(this).is(':checked'));
    });
    // Update the responses table if completionstate change
    $("#browseresults #completionstate").change(function(){
        $("#limit").val('');
        $("#browseresults").submit();
    });

    // Delete individual file
    $(".deleteresponse").click(function(){
        thisid=removechars($(this).attr('id'));
        answer = confirm(strdeleteconfirm);
        if (answer==true)
        {
            $('#deleteanswer').val(thisid);
            $('.cbResponseMarker').attr('checked',false);
            $('#resulttableform').submit();
        }
    });
    // Delete all marked responses
    $("#imgDeleteMarkedResponses").click(function(){
        if ($('.cbResponseMarker:checked').size()>0)
        {
            thisid=removechars($(this).attr('id'));
            answer = confirm(strDeleteAllConfirm);
            if (answer==true)
            {
                $('#deleteanswer').val('marked');
                $('#resulttableform').submit();
            }
        }
        else
            alert(noFilesSelectedForDeletion);
    });

    // Download individual file bundle
    $(".downloadfile").click(function() {
        thisid = removechars($(this).attr('id'));
        $('#downloadfile').val(thisid);
        $('.cbResponseMarker').attr('checked', false);
        $('#resulttableform').submit();
    });

    // Download all marked files
    $("#imgDownloadMarkedFiles").click(function() {
        if ($('.cbResponseMarker:checked').size() > 0)
        {
            $('#downloadfile').val('marked');
            $('#resulttableform').submit();
        }
        else
            alert(noFilesSelectedForDnld);
    });


});



/*    $("#responseTable")
.jqGrid({
url: siteURL + "/admin/responses/" + surveyID + "/grid",
editurl: siteURL + "/admin/responses/" + surveyID + "/grid",
datatype: "json",
mtype: "POST",
caption: "Responses",
width: "100%",
height: "100%",
rowNum: 25,
pager: "#responsePager",
editable: true,
colNames: colNames,
colModel: colModel,
sortname: 'id',
sortorder: 'asc',
viewrecords : true,
gridview: true,
multiselect: true,
loadonce: true,
rowList: [25,50,100,250,500,1000,2500,5000],
ondblClickRow: function(id) {
if (lastSel != id) {
$('#responseTable').saveRow(lastSel);
$('#responseTable').editRow(id, true);
lastSel=id;
}
},
onSelectRow: function(id) {
$('#responseTable').saveRow(lastSel);
}
})
.filterToolbar({
'autosearch': true,
'searchOnEnter': false
})
.navGrid("#responsePager", {
'add': false,
'refresh': false,
'edit': false
})
.navButtonAdd("#responsePager", {
'caption': 'Download marked files',
'onClickButton': function (rows) {
var rows = $("#responseTable").getGridParam('selarrrow');

if (rows.length <= 0) {
alert ("Please select some records first!");
return;
}

sendPost(siteURL + "/admin/responses/" + surveyID + "/grid", {
'oper': 'downloadarchives',
'ids': rows
});
}
})  */

/* Deprecated : use admin_core.js function */
///**
//Send a post request to the server to download a file

//@param myaction     post action
//@param data         parameters for $_POST

//*/
//function sendPost(myaction, checkcode, arrayparam, arrayval)
//{
//    var myform = document.createElement('form');
//    document.body.appendChild(myform);
//    myform.action =myaction;
//    myform.method = 'POST';
//    for (i=0;i<arrayparam.length;i++)
//    {
//        addHiddenElement(myform,arrayparam[i],arrayval[i])
//    }

//    addHiddenElement(myform,'YII_CSRF_TOKEN',LS.data.csrfToken)
//    myform.submit();
//}




/**
Dowload a file from a response
@param id       ID of the response
@param column   Field of the text upload question
@param filename Name of the file
*/
function getFile(id, field, filename)
{
    sendPost(siteURL + "/admin/responses/" + surveyID + "/grid", 
        new Array(
            'id',
            'fieldname',
            'oper',
            'filename'
        ),
        new Array(
            id,
            field,
            'downloadfile',
            filename
    ));
}


/**
Get an archive containing all the file from a response
@param id       ID of the response
*/
function getArchive(id)
{
    sendPost(siteURL + "/admin/responses/" + surveyID + "/grid", 
        new Array(
            'oper',
            'id'),
        new Array(
            'downloadarchive',
            id)        
    );
}

