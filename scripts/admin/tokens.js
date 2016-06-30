




$(document).ready(function(){

    /**
     * Scroll the pager and the footer when scrolling horizontally
     */
    $('.scrolling-wrapper').scroll(function(){
        $('#tokenListPager').css({
            'left': $(this).scrollLeft() ,
        });
    });

});

var conditionid=1;
function checkbounces() {
    $("#dialog-modal").dialog('open');
    $('#dialog-modal').html('<p><img style="margin-top:42px" src="'+imageurl+'ajax-loader.gif" /></p>');
    $('#dialog-modal').load(sBounceProcessingURL);
}

function addcondition()
{
    // Seems unused
    conditionid++;
    html = "<tr name='joincondition_"+conditionid+"' id='joincondition_"+conditionid+"'><td><select name='join_"+conditionid+"' id='join_"+conditionid+"'>\n\
    <option value='and'>"+andTxt+"</option><option value='or'>"+orTxt+"</option></td></tr>";
    html2 = "<tr><td><select name='field_"+conditionid+"' \n\ id='field_"+conditionid+"'>";
    for(col in colInformation){
        if(colInformation[col]['search'])
            html2 += "<option value='"+col+"'>"+colInformation[col]['description']+"</option>";
    }
    html2 += "</select></td><td>\n\
    <select name='condition_"+conditionid+"' id='condition_"+conditionid+"'><option value='equal'>"+searchtypes[0]+"</option><option value='contains'>"+searchtypes[1]+"</option>\n\
    <option value='notequal'>"+searchtypes[2]+"</option><option value='notcontains'>"+searchtypes[3]+"</option><option value='greaterthan'>"+searchtypes[4]+"</option>\n\
    <option value='lessthan'>"+searchtypes[5]+"</option></select></td>\n\<td><input type='text' id='conditiontext_"+conditionid+"' style='margin-left:10px;' /></td>\n\
    <td><img src="+minusbutton+" onClick= $(this).parent().parent().remove();$('#joincondition_"+conditionid+"').remove() id='removebutton'"+conditionid+">\n\
    <img src="+addbutton+" class='addcondition-button' style='margin-bottom:4px'></td></tr>";
    //$('#searchtable > tbody > tr').eq(id).after(html);
    $('#searchtable > tbody > tr').eq(conditionid).after(html);
    conditionid++;
    $('#searchtable > tbody > tr').eq(conditionid).after(html2);
    //idexternal++;
}


function addSelectedParticipantsToCPDB()
{
    var dialog_buttons={};
    var token = [];

    var token = jQuery('#displaytokens').jqGrid('getGridParam','selarrrow');

    if(token.length==0)
    {        /* build an array containing the various button functions */
        /* Needed because it's the only way to label a button with a variable */

        dialog_buttons[okBtn]=function(){
            $( this ).dialog( "close" );
        };
        /* End of building array for button functions */
        $('#norowselected').dialog({
            modal: true,
            buttons: dialog_buttons
        });
    }
    else
    {
        $("#addcpdb").load(postUrl, {
            participantid:token},function(){
                $(location).attr('href',attMapUrl+'/'+survey_id);
        });
    }

    /*$(":checked").each(function() {
    token.push($(this).attr('name'));
    });*/
}


$(document).ready(function() {

    // Code for AJAX download
    jQuery.download = function(url, data, method){
        //url and data options required
        if( url && data ){
            //data can be string of parameters or array/object
            data = typeof data == 'string' ? data : jQuery.param(data);
            //split params into form inputs
            var inputs = '';
            jQuery.each(data.split('&'), function(){
                var pair = this.split('=');
                inputs+='<input type="hidden" name="'+ pair[0] +'" value="'+ pair[1] +'" />';
            });
            //send request
            jQuery('<form action="'+ url +'" method="'+ (method||'post') +'">'+inputs+'</form>')
            .appendTo('body').submit().remove();
        };
    };
    // Code for AJAX download
    $(document).on("click",".addcondition-button",function(){
        conditionid++;
        html = "<tr name='joincondition_"+conditionid+"' id='joincondition_"+conditionid+"'><td><select class='form-control' name='join_"+conditionid+"' id='join_"+conditionid+"'><option value='and'>"+andTxt+"</option><option value='or'>"+orTxt+"</option></td><td></td></tr><tr><td><select class='form-control' name='field_"+conditionid+"' id='field_"+conditionid+"'>\n";
        for(col in colInformation){
            if(colInformation[col]['search'])
                html += "<option value='"+col+"'>"+colInformation[col]['description']+"</option>";
        }
        html += "</select>\n\</td>\n\<td>\n\
        <select class='form-control' name='condition_"+conditionid+"' id='condition_"+conditionid+"'>\n\
        <option value='equal'>"+searchtypes[0]+"</option>\n\
        <option value='contains'>"+searchtypes[1]+"</option>\n\
        <option value='notequal'>"+searchtypes[2]+"</option>\n\
        <option value='notcontains'>"+searchtypes[3]+"</option>\n\
        <option value='greaterthan'>"+searchtypes[4]+"</option>\n\
        <option value='lessthan'>"+searchtypes[5]+"</option>\n\
        </select></td>\n\<td><input class='form-control' type='text' id='conditiontext_"+conditionid+"' /></td>\n\
        <td><span data-toggle='tooltip' title='" + sDelete + "' class='ui-pg-button glyphicon glyphicon-trash text-danger' onClick= $(this).parent().parent().remove();$('#joincondition_"+conditionid+"').remove() id='ui-icon removebutton'"+conditionid+"></span>\n\
        <span data-toggle='tooltip' title='" + sAdd + "' class='ui-pg-button addcondition-button ui-icon text-success icon-add' style='margin-bottom:4px'></span></td></tr><tr></tr>";
        $('#searchtable tr:last').after(html);
        $('[data-toggle="tooltip"]').tooltip()
    });
    if(typeof searchconditions === "undefined") {
        searchconditions = {};
    }
    var field;
    $('#searchbutton').click(function(){

    });


});

function centerInfoDialog() {
    var infoDialog = $("#info_dialog");
    var dialogparent = infoDialog.parent();
    infoDialog.css({ 'left': Math.round((dialogparent.width() - infoDialog.width()) / 2)+'px' });
}

function updatePageAfterGrid(){
    var oGrid=$("#displaytokens");
    var iLastPage=parseInt(oGrid.jqGrid('getGridParam', 'lastpage'));
    var iPage=parseInt(oGrid.jqGrid('getGridParam', 'page'));
    if(iPage>1)
    {
        iPrevPage=iPage-1;
        $(".databegin").click(function(){
            oGrid.setGridParam({page:1}).trigger("reloadGrid");
        });
        $(".gridcontrol.databegin").removeClass("disabled");
        $(".databack").click(function(){
            oGrid.setGridParam({page:iPrevPage}).trigger("reloadGrid");
        });
        $(".gridcontrol.databack").removeClass("disabled");
    }
    else
    {
        $(".databegin").click(function(){});
        $(".gridcontrol.databegin").addClass("disabled");
        $(".databack").click(function(){});
        $(".gridcontrol.databack").addClass("disabled");
    }
    if(iPage<iLastPage)
    {
        iNextPage=iPage+1;
        $(".dataend").click(function(){
            oGrid.setGridParam({page:iLastPage}).trigger("reloadGrid");
        });
        $(".gridcontrol.dataend").removeClass("disabled");
        $(".dataforward").click(function(){
            oGrid.setGridParam({page:iNextPage}).trigger("reloadGrid");
        });
        $(".gridcontrol.dataforward").removeClass("disabled");
    }
    else
    {
        $(".dataend").click(function(){});
        $(".gridcontrol.dataend").addClass("disabled");
        $(".dataforward").click(function(){});
        $(".gridcontrol.dataforward").addClass("disabled");
    }

}
