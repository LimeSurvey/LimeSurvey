var idexternal=parseInt(3);
function addcondition() 
{
    id=2;
    html = "<tr name='joincondition_"+idexternal+"' id='joincondition_"+idexternal+"'><td><select name='join_"+idexternal+"' id='join_"+idexternal+"'>\n\
    <option value='and'>AND</option><option value='or'>OR</option></td></tr>";
    html2 = "<tr><td><select name='field_"+idexternal+"' \n\
    id='field_"+idexternal+"'><option value='firstname'>First Name</option><option value='lastname'>Last Name</option><option value='email'>\n\
    E-Main</option><option value='black'>Blacklisted</option><option value='language'>Language</option><option value='owner_uid'>Owner ID\n\
    </option><option value='owner_name'>Owner Name</option>"+optionstring+"</select></td><td>\n\
    <select name='condition_"+idexternal+"' id='condition_"+idexternal+"'><option value='equal'>Equals</option><option value='contains'>Contains</option>\n\
    <option value='notequal'>Not Equal</option><option value='notcontains'>Not Contains</option><option value='greaterthan'>Greater Than</option>\n\
    <option value='lessthan'>Less Than</option></select></td>\n\<td><input type='text' id='conditiontext_"+idexternal+"' style='margin-left:10px;' /></td>\n\
    <td><img src="+minusbutton+" onClick= $(this).parent().parent().remove();$('#joincondition_"+idexternal+"').remove() id='removebutton'"+idexternal+">\n\
    <img src="+addbutton+" id='addbutton'  onclick='addcondition();' style='margin-bottom:4px'></td></tr>";
    //$('#searchtable > tbody > tr').eq(id).after(html);
    $('#searchtable > tbody > tr').eq(idexternal).after(html);
    idexternal++;
    $('#searchtable > tbody > tr').eq(idexternal).after(html2);
    idexternal++;
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
var id=1;
$("#addbutton").click(function(){
id=2;
html = "<tr name='joincondition_"+id+"' id='joincondition_"+id+"'><td><select name='join_"+id+"' id='join_"+id+"'><option value='and'>AND</option><option value='or'>OR</option></td><td></td></tr><tr><td><select name='field_"+id+"' id='field_"+id+"'>\n\
<option value='firstname'>First Name</option>\n\
<option value='lastname'>Last Name</option>\n\
<option value='email'>E-Main</option>\n\
<option value='black'>Blacklisted</option>\n\
<option value='language'>Language</option>\n\
<option value='owner_uid'>Owner ID</option>\n\
<option value='owner_name'>Owner Name</option>"+optionstring+"\n\</select>\n\</td>\n\<td>\n\<select name='condition_"+id+"' id='condition_"+id+"'>\n\
<option value='equal'>Equals</option>\n\
<option value='contains'>Contains</option>\n\
<option value='notequal'>Not Equal</option>\n\
<option value='notcontains'>Not Contains</option>\n\
<option value='greaterthan'>Greater Than</option>\n\
<option value='lessthan'>Less Than</option>\n\
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
jQuery("#displayparticipants").jqGrid({
align:"center",
url: jsonUrl,
editurl: editUrl,
datatype: "json",
mtype: "post",
colNames : jQuery.parseJSON(colNames),
colModel: jQuery.parseJSON(colModels),
height: "100%",
width: "100%",
rowNum: 25,
editable:true,
scrollOffset:0,
autowidth: true,
sortable : true,
sortname: 'firstname',
sortorder: 'asc',
viewrecords : true,
rowList: [25,50,100,250,500,1000,5000,10000],
multiselect: true,
loadonce : false,
ondblClickRow: function(id)
                {
                var can_edit = $('#displayparticipants').getCell(id, 'can_edit');
                if(can_edit == 'false')
                    {
                        var dialog_buttons={};
                        dialog_buttons[okBtn]=function(){
		        $( this ).dialog( "close" );
			};
			/* End of building array for button functions */
                        $('#notauthorised').dialog({
			    modal: true,
                            title: "Access Denied",
 			    buttons: dialog_buttons
                        });
                    }
                else
                    {
                        {
                            if(id && id!==lastSel)
                            {   
                                jQuery('#displayparticipants').saveRow(lastSel);
                                lastSel=id;
                            }
                        }
                        jQuery('#displayparticipants').editRow(id,true);
                    }
                },
                pager: "#pager",
                caption: "Participants",
                subGrid: true, 
                subGridRowExpanded: function(subgrid_id,row_id) {
                subgrid_table_id = subgrid_id+"_t"; 
                pager_id = "p_"+subgrid_table_id; 
                second_subgrid_table_id = subgrid_id+"_tt"; //new name for table selector –> tt
                second_pager_id = "p_"+second_subgrid_table_id;
                $("#"+subgrid_id).html("<table id='"+subgrid_table_id+"' class='scroll'></table><div id='"+pager_id+"' class='scroll'></div>"); 
                $("#"+subgrid_id).append("<div id='hide_"+second_subgrid_table_id+"'><table id='"+second_subgrid_table_id+"' class='scroll'></table><div id='"+second_pager_id+"' class='scroll'></div>");
                jQuery("#"+second_subgrid_table_id).jqGrid({
                        datatype: "json", 
                        url: surveylinkUrl+'/'+row_id,
                        height: "100%",
                        width: "100%",
                        colNames:['Survey Name','Survey ID','Token ID','Date Added'], 
                        colModel:[{ name:'surveyname',index:'surveyname', width:100,align:'center'},
                                  { name:'surveyid',index:'surveyid', width:90,align:'center'},
                                  { name:'tokenid',index:'tokenid', width:100,align:'center'},
                                  { name:'dateadded',index:'added', width:120,align:'center'}],
                                  caption: "Participant's Survey Information",
                              gridComplete: function () {

                        var recs = $("#"+second_subgrid_table_id).jqGrid('getGridParam','reccount');
                        if (recs == 0 || recs == null) {
                            //$("#"+second_subgrid_table_id).setGridHeight(40);
                            $("#hide_"+second_subgrid_table_id).hide();
                            //$("#NoRecordContact").show();
                        }
                    }
                });
                jQuery("#"+subgrid_table_id).jqGrid({ 
                url: getAttribute_json+'/'+row_id,
                editurl:editAttributevalue,
                datatype: "json", 
                mtype: "post",
                pgbuttons:false,
                recordtext:'',
                pgtext:'',
                caption: "Participant's Attribute Information",
                editable:true,
                loadonce : true,
                colNames: ['Actions','Participant ID','Attribute Type','Attribute Name','Attribute Value','Attribute Possible Values'],
                colModel: [ { name:'act',index:'act',width:55,align:'center',sortable:false,formatter:'actions',formatoptions : { keys:true,onEdit:function(id){ }}},
                            { name:'participant_id',index:'participant_id', width:150, sorttype:"string",align:"center",editable:true,hidden:true},
                            { name:'atttype',index:'atttype', width:150, sorttype:"string",align:"center",editable:true,hidden:true},
                            { name:'attname',index:'attname', width:150, sorttype:"string",align:"center",editable:false},
                            { name:'attvalue',index:'attvalue', width:150, sorttype:"string",align:"center",editable:true},
                            { name:'attpvalues',index:'attpvalues', width:150, sorttype:"string",align:"center",editable:true,hidden:true}],
                rowNum:20,
                pager: pager_id, 
                      gridComplete: function () {
                      $('div.ui-inline-del').html('');
                      $('div.ui-inline-edit').html('');
                  },
                     
                ondblClickRow: function(id,subgrid_id){
                var parid = id.split('_');             
                var participant_id = $("#displayparticipants_"+parid[0]+"_t").getCell(id,'participant_id');    
                var lsel = parid[0];
                var can_edit = $('#displayparticipants').getCell(participant_id,'can_edit');
                if(can_edit == 'false')
                    {
                        var dialog_buttons={};
                        dialog_buttons[okBtn]=function(){
                        $( this ).dialog( "close" );
                        };
                        /* End of building array for button functions */
                        $('#notauthorised').dialog({
        			    modal: true,
                        title: "Access Denied",
         			    buttons: dialog_buttons
                        });
                    }
               else
                {
                    
                  var att_type = $("#displayparticipants_"+parid[0]+"_t").getCell(id,'atttype');
                  if(att_type=="DP")
                  {
                     $("#displayparticipants_"+parid[0]+"_t").setColProp('attvalue',{ editoptions:{ dataInit:function (elem) {$(elem).datepicker();}}});
                  }
                if(att_type=="DD")
                 {
                     var att_p_values = $("#displayparticipants_"+parid[0]+"_t").getCell(id,'attpvalues');
                     $("#displayparticipants_"+parid[0]+"_t").setColProp('attvalue',{ edittype:'select',editoptions:{ value:":Select One;"+att_p_values}});
                 }
                 if(att_type=="TB")
                 {
                      $("#displayparticipants_"+parid[0]+"_t").setColProp('attvalue',{ edittype:'text'});
                      $("#displayparticipants_"+parid[0]+"_t").setColProp('attvalue',{ editoptions:''});
                 }
                var attap = $("#displayparticipants_"+parid[0]+"_t").getCell(id,'attap');
                if(id && id!==lastSel2)
                        {
                            jQuery("#displayparticipants_"+parid[0]+"_t").saveRow(lastSel2);
                            lastSel2=id;
                        }
                $.fn.fmatter.rowactions(id,'displayparticipants_'+parid[0]+'_t','edit',0);
                jQuery("#displayparticipants_"+parid[0]+"_t").jqGrid('editRow',id,true);
                jQuery("#displayparticipants_"+parid[0]+"_t").editRow(id,true);
                }
            },
                height: '100%'}); 
      }
   
});
jQuery("#displayparticipants").jqGrid('navGrid','#pager',{ add:true,del:true,edit:false,refresh: true,search: false},{},{ width : 400},{ msg:deleteMsg, width : 700,
                     afterShowForm: function($form) {
                               var dialog = $form.closest('div.ui-jqdialog'),
                                   selRowId = jQuery("#displayparticipants").jqGrid('getGridParam', 'selrow'),
                                   selRowCoordinates = $('#'+selRowId).offset();
                               dialog.offset(selRowCoordinates);
                           },
      

 beforeSubmit : function(postdata, formid) {
     if(!$('#selectable .ui-selected').attr('id'))
     {
             alert(nooptionselected);
              message = "dummy";
     }
     else 
     {
         $.post(delparticipantUrl, { participant_id : postdata ,selectedoption : $('#selectable .ui-selected').attr('id')}, function(data) {});
        success = "dummy";
         message = "dummy";
         return[success,message]; 
     }
     
} ,beforeShowForm:function(form) {$('#selectable').bind("mousedown", function (e) { e.metaKey = false;}).selectable({ tolerance: 'fit'})}},{ multipleSearch:true, multipleGroup:true});
$("#displayparticipants").navButtonAdd('#pager',{  caption:"",title:"Export to CSV", buttonicon:'exporticon', onClickButton:function(){
            $.post(exporttocsvcount, { searchcondition: jQuery('#displayparticipants').jqGrid('getGridParam', 'url')},
                            function(data) {
                            titlemsg = data;
                            
                var dialog_buttons={};
                dialog_buttons[cancelBtn]=function(){
                        $(this).dialog("close");
			};
                dialog_buttons[exportBtn]=function(){
                //$.load(exporttocsv+"/"+$('#attributes').val(),{ } );
                var url = jQuery('#displayparticipants').jqGrid('getGridParam', 'url');
                $.download(exporttocsv+"/"+$('#attributes').val(),'searchcondition='+url );
                    $(this).dialog("close");
            };
            
                /* End of building array for button functions */
                 $('#exportcsv').dialog({
			    modal: true,
                            title: titlemsg,
 			    buttons: dialog_buttons,
                            width : 400,
                            height : 400,
                            open: function(event, ui) {
                            $('#attributes').multiselect({ noneSelectedText: 'Select Attributes',autoOpen:true}).multiselectfilter();
                         }
                        });
                 });
}});

$("#displayparticipants").navButtonAdd('#pager',{ caption:"",title:"Full Search", buttonicon:'searchicon', onClickButton:function(){
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
                jQuery("#displayparticipants").jqGrid('setGridParam',{ url:jsonSearchUrl+'/'+searchconditions}).trigger("reloadGrid");
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
            jQuery("#displayparticipants").jqGrid('setGridParam',{ url:jsonSearchUrl+'/'+searchconditions}).trigger("reloadGrid");
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
$.extend(jQuery.jgrid.edit,{ closeAfterAdd: true,reloadAfterSubmit: true,closeOnEspace:true});
	//script for sharing of participants
	$("#sharingparticipants").dialog({
	               title: spTitle,
	               modal: true,
	               autoOpen: false,
	               height: 200,
	               width: 400,
	               show: 'blind',
	               hide: 'blind'
	});
	function shareParticipants(participant_id) {
               var myGrid = $("#displayparticipants").jqGrid();
               var pid = myGrid .getGridParam('selarrrow');
                $("#shareform").load(shareUrl, {
                participantid:pid,
	        shareuser:$("#shareuser").val(),
    	        can_edit:$('#can_edit').attr('checked')                               
	    }, function(msg){
	        $(this).dialog("close");
	        alert(msg+"."+shareMsg);
                $(location).attr('href',redUrl);
	    });
	}
    //End of Script for sharing
	function addtoSurvey(participant_id,survey_id,redirect) {
                    $("#addsurvey").load(postUrl,{
                    participantid:participant_id},function(){
                            $(location).attr('href',attMapUrl+'/'+survey_id+'/'+redirect);
                    });}

	$('#share').click(function(){
	    var myGrid = $("#displayparticipants").jqGrid();
	    var row = myGrid .getGridParam('selarrrow');
	    if(row=="") {
			/* build an array containing the various button functions */
			/* Needed because it's the only way to label a button with a variable */
            var dialog_buttons={};
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
			/* build an array containing the various button functions */
			/* Needed because it's the only way to label a button with a variable */
                var dialog_buttons={};
                dialog_buttons[spAddBtn]=function(){
                var row = myGrid .getGridParam('selarrrow');
                shareParticipants(row);};
			dialog_buttons[cancelBtn]=function(){
                        $(this).dialog("close");
			};
			/* End of building array for button functions */
	        $("#shareform").dialog({
	            height: 300,
				width: 350,
				modal: true,
	            buttons: dialog_buttons
	        });
	    }
	    if (!($("#shareuser").length > 0)) {
	        $('#shareform').html(sfNoUser);
	    }
	});
        function basename(path) {
    return path.replace(/\\/g,'/').replace( /.*\//, '' );
}

    $('#addtosurvey').click(function() {
            var selected = "";
            var myGrid = $("#displayparticipants").jqGrid();
            var rows = myGrid.getGridParam('selarrrow');
            $('#selectableadd').bind("mousedown", function (e) { e.metaKey = false;}).selectable({ tolerance: 'fit',
            selected: function(event, ui) { 
                  if(ui.selected.id == "all")
                    {
                          $.post(getaddtosurveymsg, { searchcondition: jQuery('#displayparticipants').jqGrid('getGridParam','url')},
                                        function(data) {
                                        $('#addsurvey').dialog('option', 'title', data);

		    });
                                selected = "search";
                }
                    else if(ui.selected.id == "allingrid")
                    {
                        $.post(getaddtosurveymsg, { searchcondition: 'getParticipants_json'},
                                        function(data) {
                                        $('#addsurvey').dialog('option', 'title', data);
    
                                        }   
                                    );
                              selected = "all";
                    }
        else
        {   
                        $('#addsurvey').dialog('option', 'title', rows.length+" participants are to be copied");
                        selected = "rows";
                    }
               }
            });
                if(basename(jQuery('#displayparticipants').jqGrid('getGridParam', 'url'))!='getParticipants_json')
                {
                    $('#selectableadd').selectable("enable");
                    $('#all').show();
                    if(rows == "")
                    {
                        $('#selected').hide();
                    }
                    else
                    {
                        $('#selected').show();
                    }
                }
                else if(rows == "")
                {
                    $('#selected').hide();
                    if(basename(jQuery('#displayparticipants').jqGrid('getGridParam', 'url'))=='getParticipants_json')
                    {
                        $('#selectableadd').selectable("disable");
                        $('ol#selectableadd li').eq(1).addClass('ui-selected');
                        selected = "all";
                    }   
                                        
                }
                else if(rows != "")
                {
                    $('#selectableadd').selectable("enable");
                    if(basename(jQuery('#displayparticipants').jqGrid('getGridParam', 'url'))=='getParticipants_json')
                    {
                            $('#selected').show();
                            $('#all').hide();
                            
                    }
                    if(basename(jQuery('#displayparticipants').jqGrid('getGridParam', 'url'))!='getParticipants_json')
                    {
                            $('#all').show();
                            $('#selected').show();
                            
                    }
                }
                
                    var dialog_buttons={};
                    dialog_buttons[mapButton]=function(){
                    
                
                    var survey_id=$('#survey_id').val();
                    var redirect ="";
                    if(survey_id=="")
                    {
                        alert(selectSurvey);
                    }
        
                    else
                    {
                        if(jQuery('#redirect').is(":checked")) 
                        {
                            redirect = "redirect";                               
                        }
                        else
                        {
                            redirect = "";
                        }
                   if(selected == "search")
                       {
                           $.post(getSearchIDs, { searchcondition: jQuery('#displayparticipants').jqGrid('getGridParam','url')},
                           function(data) {                                  
                                $('#count').val($('#ui-dialog-title-addsurvey').text());
                                $('#participant_id').val(data);
                                $("#addsurvey").submit(); 
                             });
                                        
                    }
                   else if(selected == "all")
                       {
                                $.post(getSearchIDs, { searchcondition: 'getParticipants_json'},
                                function(data) 
                                {
                                    $('#count').val($('#ui-dialog-title-addsurvey').text());
                                    $('#participant_id').val(data);
                                    $("#addsurvey").submit(); 
                                }   
                               );
                       }
                    else
                       {
                               rows = myGrid.getGridParam('selarrrow');
                               $('#count').val($('#ui-dialog-title-addsurvey').text());
                               $('#participant_id').val(rows);
                               $("#addsurvey").submit(); 
                       }
                  
                }
                
                    };
                     
               dialog_buttons[cancelBtn]=function(){    $(this).dialog("close");
                   
                   
                   
                    };
                    /* End of building array containing button functions */
                    $("#addsurvey").dialog({
                        height: 350,
                        width: 450,
                        title : addsurvey,
            			modal: true,
                        open: function(event, ui) {
                              if(selected == "all")
                              {
                                      $.post(getaddtosurveymsg, { searchcondition: jQuery('#displayparticipants').jqGrid('getGridParam', 'url')},
                                        function(data) {
                                        $('#addsurvey').dialog('option', 'title', data);

                                        });
       }
                        
                        },
                        
                        buttons: dialog_buttons
                      });
            
                            
             
  if (!($("#survey_id").length > 0)) {
     $('#addsurvey').html(addpartErrorMsg);
} 
});
});