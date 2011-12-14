// $Id: tokens.js 8633 2010-04-25 12:57:33Z c_schmitz
var idexternal=parseInt(3);
function addcondition() 
{
    id=2;
    html = "<tr name='joincondition_"+idexternal+"' id='joincondition_"+idexternal+"'><td><select name='join_"+idexternal+"' id='join_"+idexternal+"'>\n\
    <option value='and'>AND</option><option value='or'>OR</option></td></tr>";
    html2 = "<tr><td><select name='field_"+idexternal+"' \n\
    id='field_"+idexternal+"'><option value='firstname'>"+colNames[2]+"</option><option value='lastname'>"+colNames[3]+"</option><option value='email'>"+colNames[4]+"</option><option value='emailstatus'>"+colNames[5]+"</option><option value='token'>"+colNames[6]+"</option><option value='sent'>"+colNames[7]+"</option><option value='remindersent'>"+colNames[8]+"</option><option value='remindercount'>"+colNames[9]+"</option><option value='completed'>"+colNames[10]+"</option><option value='usesleft'>"+colNames[11]+"</option><option value='Valid from'>"+colNames[12]+"</option><option value='validuntil'>"+colNames[13]+"</option></select></td><td>\n\
    <select name='condition_"+idexternal+"' id='condition_"+idexternal+"'><option value='equal'>"+searchtypes[0]+"</option><option value='contains'>"+searchtypes[1]+"</option>\n\
    <option value='notequal'>"+searchtypes[2]+"</option><option value='notcontains'>"+searchtypes[3]+"</option><option value='greaterthan'>"+searchtypes[4]+"</option>\n\
    <option value='lessthan'>"+searchtypes[5]+"</option></select></td>\n\<td><input type='text' id='conditiontext_"+idexternal+"' style='margin-left:10px;' /></td>\n\
    <td><img src="+minusbutton+" onClick= $(this).parent().parent().remove();$('#joincondition_"+idexternal+"').remove() id='removebutton'"+idexternal+">\n\
    <img src="+addbutton+" id='addbutton'  onclick='addcondition();' style='margin-bottom:4px'></td></tr>";
    //$('#searchtable > tbody > tr').eq(id).after(html);
    $('#searchtable > tbody > tr').eq(idexternal).after(html);
    idexternal++;
    $('#searchtable > tbody > tr').eq(idexternal).after(html2);
    idexternal++;
}
$(document).ready(function(){
    
    $("#bounceprocessing").change(turnoff);
    turnoff();	
    $('img#bounceprocessing').bind('click',function(){
        $("#dialog-modal").dialog({
                                    title: "Summary",
                                    modal: true,
                                    autoOpen: false,
                                    height: 200,
                                    width: 400,
                                    show: 'blind',
                                    hide: 'blind'
	                                }); 	  
 	    checkbounces(surveyid);
    }); 
    $("#filterduplicatetoken").change(function(){
        if ($("#filterduplicatetoken").attr('checked')==true)
        {
            $("#lifilterduplicatefields").slideDown(); 
        }
        else
        {
            $("#lifilterduplicatefields").slideUp(); 
        }
    }) 


function turnoff(ui,evt)
{
  bounce_disabled=($("#bounceprocessing").val()=='N' || $("#bounceprocessing").val()=='G');
  if (bounce_disabled==true) {bounce_disabled='disabled';}
  else {bounce_disabled='';}
  $("#bounceaccounttype").attr('disabled',bounce_disabled);
  $("#bounceaccounthost").attr('disabled',bounce_disabled);
  $("#bounceaccountuser").attr('disabled',bounce_disabled);
  $("#bounceaccountpass").attr('disabled',bounce_disabled);
  $("#bounceencryption").attr('disabled',bounce_disabled);
  $("#bounceaccountencryption").attr('disabled',bounce_disabled);
}

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
var id=1;
$("#addbutton").click(function(){
id=2;
html = "<tr name='joincondition_"+id+"' id='joincondition_"+id+"'><td><select name='join_"+id+"' id='join_"+id+"'><option value='and'>AND</option><option value='or'>OR</option></td><td></td></tr><tr><td><select name='field_"+id+"' id='field_"+id+"'>\n\
<option value='firstname'>"+colNames[2]+"</option>\n\
<option value='lastname'>"+colNames[3]+"</option>\n\
<option value='email'>"+colNames[4]+"</option>\n\
<option value='emailstatus'>"+colNames[5]+"</option>\n\
<option value='token'>"+colNames[6]+"</option>\n\
<option value='sent'>"+colNames[7]+"</option>\n\
<option value='remindersent'>"+colNames[8]+"</option>\n\
<option value='remindercount'>"+colNames[9]+"</option>\n\
<option value='completed'>"+colNames[10]+"</option>\n\
<option value='usesleft'>"+colNames[11]+"</option>\n\
<option value='validfrom'>"+colNames[12]+"</option>\n\
<option value='validuntil'>"+colNames[13]+"</option>\n\
</select>\n\</td>\n\<td>\n\
<select name='condition_"+id+"' id='condition_"+id+"'>\n\
<option value='equal'>"+searchtypes[0]+"</option>\n\
<option value='contains'>"+searchtypes[1]+"</option>\n\
<option value='notequal'>"+searchtypes[2]+"</option>\n\
<option value='notcontains'>"+searchtypes[3]+"</option>\n\
<option value='greaterthan'>"+searchtypes[4]+"</option>\n\
<option value='lessthan'>"+searchtypes[5]+"</option>\n\
</select></td>\n\<td><input type='text' id='conditiontext_"+id+"' style='margin-left:10px;' /></td>\n\
<td><img src="+minusbutton+" onClick= $(this).parent().parent().remove();$('#joincondition_"+id+"').remove() id='removebutton'"+id+">\n\
<img src="+addbutton+" id='addbutton'  onclick='addcondition();' style='margin-bottom:4px'></td></tr><tr></tr>";
$('#searchtable tr:last').after(html);
});
var searchconditions = {};
var field;
$('#searchbutton').click(function(){
        
});
var lastSel,lastSel2;
jQuery("#displaytokens").jqGrid({
    align:"center",
    url: jsonUrl,
    editurl: editUrl,
    datatype: "json",
    mtype: "post",
    colNames : colNames,
    colModel: colModels,
    toppager: true,
    height: "100%",
    width: "100%",
    rowNum: 25,
    editable:true,
    scrollOffset:0,
    autowidth: true,
    sortable : true,
    sortname: 'id',
    sortorder: 'asc',
    viewrecords : true,
    rowList: [25,50,100,250,500,1000,5000,10000],
    multiselect: true,
    loadonce : false,
    /*ondblClickRow: function(id)
        {
            if(id && id!==lastSel)
            {   
                jQuery('#displaytokens').saveRow(lastSel);
                lastSel=id;
            }
            jQuery('#displaytokens').editRow(id,true);
        },*/
    pager: "#pager",
    caption: "Tokens",
    });
jQuery("#displaytokens").jqGrid('navGrid','#pager',{ add:false,del:true,edit:false,refresh: true,search: false},{},{ width : 400},{ msg:delmsg, width : 700,
                     afterShowForm: function($form) {
                               var dialog = $form.closest('div.ui-jqdialog'),
                                   selRowId = jQuery("#displaytokens").jqGrid('getGridParam', 'selrow'),
                                   selRowCoordinates = $('#'+selRowId).offset();
                               dialog.offset(selRowCoordinates);
                           },
      

 beforeSubmit : function(postdata, formid) {
         $.post(delUrl, { tid : postdata }, function(data) {});
        success = "dummy";
         message = "dummy";
         return[success,message]; 
},

beforeShowForm:function(form) {$('#selectable').bind("mousedown", function (e) { e.metaKey = false;}).selectable({ tolerance: 'fit'})}},{ multipleSearch:true, multipleGroup:true});
$("#displaytokens").navButtonAdd('#pager',{ caption:"",title:"Full Search", buttonicon:'searchicon', onClickButton:function(){
                var dialog_buttons={};
                dialog_buttons[searchBtn]=function(){
                searchconditions="";
                var dialog_buttons={};
                if($('#field_1').val() == '')
               {
                dialog_buttons[okBtn]=function(){
		$( this ).dialog( "close" );};
                /* End of building array for button functions */
                 $('#fieldnotselected').dialog({
			    modal: true,
                            title: error,
 			    buttons: dialog_buttons
                        });
            }
        else if($('#condition_1').val()=="")
        {
                dialog_buttons[okBtn]=function(){
		$( this ).dialog( "close" );};
                /* End of building array for button functions */
                 $('#conditionnotselected').dialog({
			    modal: true,
                            title: error,
 			    buttons: dialog_buttons
                        });
         }
        else
        {
        
        if(id == 1)
         {
                searchconditions = searchconditions + $('#field_1').val()+"||"+$('#condition_1').val()+"||"+$('#conditiontext_1').val();      
                jQuery("#displaytokens").jqGrid('setGridParam',{ url:jsonSearchUrl+'/'+searchconditions}).trigger("reloadGrid");
         }
        else
         {
             searchconditions = $('#field_1').val()+"||"+$('#condition_1').val()+"||"+$('#conditiontext_1').val();      
             for( i=2 ; i<=idexternal; i++)
                {
                    
                   if($('#field_'+i).val())
                    {
                        searchconditions = searchconditions + "||"+ $('#join_'+(i)).val()+"||"+$('#field_'+i).val()+"||"+$('#condition_'+i).val()+"||"+$('#conditiontext_'+i).val();                          
                    }
                }
            jQuery("#displaytokens").jqGrid('setGridParam',{ url:jsonSearchUrl+'/'+searchconditions}).trigger("reloadGrid");
        }
        $(this).dialog("close");
        }
      };
			dialog_buttons[cancelBtn]=function(){
                        $(this).dialog("close");
			};
			/* End of building array for button functions */
	        $("#search").dialog({
                                height: 300,
				width: 750,
				modal: true,
                                title : 'Full Search',
	            buttons: dialog_buttons
	        });    
 
}});
$("#displaytokens").navButtonAdd('#pager',{  caption:"",title:invitemsg, buttonicon:'ui-icon-mail-closed', onClickButton:function(){window.open(inviteurl+$("#displaytokens").getGridParam("selarrrow").join("|"), "_blank")}});
$("#displaytokens").navButtonAdd('#pager',{  caption:"",title:remindmsg, buttonicon:'ui-icon-mail-open', onClickButton:function(){window.open(remindurl+$("#displaytokens").getGridParam("selarrrow").join("|"), "_blank")}});
$.extend(jQuery.jgrid.edit,{ closeAfterAdd: true,reloadAfterSubmit: true,closeOnEspace:true});
});
//ui-icon-newwin