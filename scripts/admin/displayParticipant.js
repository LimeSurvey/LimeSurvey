var conditionid=1;
function addcondition(newcid)
{
    conditionid++;

    html = "<tr name='joincondition_"+conditionid+"' id='joincondition_"+conditionid+"'><td>\n\
    <select name='join_"+conditionid+"' id='join_"+conditionid+"'>\n\
    <option value='and'>"+andTxt+"</option>\n\
    <option value='or'>"+orTxt+"</option>\n\
    </td><td></td></tr>";
    html2 = "<tr><td><select name='field_"+conditionid+"' id='field_"+conditionid+"'>\n\
    <option>"+selectTxt+"</option>\n\
    <option value='firstname'>"+firstnameTxt+"</option>\n\
    <option value='lastname'>"+lastnameTxt+"</option>\n\
    <option value='email'>"+emailTxt+"</option>\n\
    <option value='blacklisted'>"+blacklistedTxt+"</option>\n\
    <option value='surveys'>"+surveysTxt+"</option>\n\
    <option value='survey'>"+surveyTxt+"</option>\n\
    <option value='language'>"+languageTxt+"</option>\n\
    <option value='owner_uid'>"+owneridTxt+"</option>\n\
    <option value='owner_name'>"+ownernameTxt+"</option>\n\
    </select>\n\</td>\n\<td>\n\
    <select name='condition_"+conditionid+"' id='condition_"+conditionid+"'>\n\
    <option>"+selectTxt+"</option>\n\
    <option value='equal'>"+equalsTxt+"</option>\n\
    <option value='contains'>"+containsTxt+"</option>\n\
    <option value='notequal'>"+notequalTxt+"</option>\n\
    <option value='notcontains'>"+notcontainsTxt+"</option>\n\
    <option value='greaterthan'>"+greaterthanTxt+"</option>\n\
    <option value='lessthan'>"+lessthanTxt+"</option>\n\
    </select></td>\n\
    <td><input type='text' id='conditiontext_"+conditionid+"' style='margin-left:10px;' /></td>\n\
    <td><img src="+minusbutton+" onClick= $(this).parent().parent().remove();$('#joincondition_"+conditionid+"').remove() id='removebutton'"+conditionid+" alt='"+minusbuttonTxt+"' />\n\
    <img src="+addbutton+" id='addbutton' onclick='addcondition();' style='margin-bottom:4px' alt='"+addbuttonTxt+"' /></td></tr>\n\<tr></tr>";
    //$('#searchtable > tbody > tr').eq(id).after(html);
    $('#searchtable > tbody > tr').eq(conditionid).after(html);
    conditionid++;
    $('#searchtable > tbody > tr').eq(conditionid).after(html2);
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
    $("#addbutton").click(function(){
        conditionid++;
        if(typeof optionstring === "undefined") {
            optionstring = "";
        }
        html = "<tr name='joincondition_"+conditionid+"' id='joincondition_"+conditionid+"'><td>\n\
        <select name='join_"+conditionid+"' id='join_"+conditionid+"'>\n\
        <option value='and'>AND</option>\n\
        <option value='or'>OR</option>\n\
        </td><td></td></tr><tr><td>\n\
        <select name='field_"+conditionid+"' id='field_"+conditionid+"'>\n\
        <option>"+selectTxt+"</option>\n\
        <option value='firstname'>"+firstnameTxt+"</option>\n\
        <option value='lastname'>"+lastnameTxt+"</option>\n\
        <option value='email'>"+emailTxt+"</option>\n\
        <option value='blacklisted'>"+blacklistedTxt+"</option>\n\
        <option value='surveys'>"+surveysTxt+"</option>\n\
        <option value='survey'>"+surveyTxt+"</option>\n\
        <option value='language'>"+languageTxt+"</option>\n\
        <option value='owner_uid'>"+owneridTxt+"</option>\n\
        <option value='owner_name'>"+ownernameTxt+"</option>"+optionstring+"\n\
        </select>\n\</td>\n\<td>\n\
        <select name='condition_"+conditionid+"' id='condition_"+conditionid+"'>\n\
        <option>"+selectTxt+"</option>\n\
        <option value='equal'>"+equalsTxt+"</option>\n\
        <option value='contains'>"+containsTxt+"</option>\n\
        <option value='notequal'>"+notequalTxt+"</option>\n\
        <option value='notcontains'>"+notcontainsTxt+"</option>\n\
        <option value='greaterthan'>"+greaterthanTxt+"</option>\n\
        <option value='lessthan'>"+lessthanTxt+"</option>\n\
        </select></td>\n\
        <td><input type='text' id='conditiontext_"+conditionid+"' style='margin-left:10px;' /></td>\n\
        <td><img src="+minusbutton+" onClick= $(this).parent().parent().remove();$('#joincondition_"+conditionid+"').remove() id='removebutton'"+conditionid+" alt='"+minusbuttonTxt+"' />\n\
        <img src="+addbutton+" id='addbutton' onclick='addcondition();' style='margin-bottom:4px' alt='"+addbuttonTxt+"' /></td></tr>\n\<tr></tr>";
        $('#searchtable tr:last').after(html);
    });

    var searchconditions = {};
    var field;
    $('#searchbutton').click(function(){
    });

    var lastSel,lastSel2;

    /* The main jqGrid, displaying Participants */
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
        loadError : function(xhr, st, str) {
            var dialog_buttons={};
            dialog_buttons[okBtn]=function(){
                $( this ).dialog( "close" );
            };
            $("<p><strong>" + str + " (Error " + xhr.status + ")</strong><br/> Could not process your query.</p>").dialog({
                modal: true,
                title: error,
                buttons: dialog_buttons,
                resizable: false
            });
        },
        ondblClickRow: function(id) {
            var can_edit = $('#displayparticipants').getCell(id, 'can_edit');
            if(can_edit == 'false') {
                var dialog_buttons={};
                dialog_buttons[okBtn]=function() {
                    $( this ).dialog( clostTxt );
                };
                /* End of building array for button functions */
                $('#notauthorised').dialog({
                    modal: true,
                    title: accessDeniedTxt,
                    buttons: dialog_buttons
                });
            } else {
                {
                    if(id && id!==lastSel) {
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

            /* Subgrid that displays survey links */
            jQuery("#"+second_subgrid_table_id).jqGrid( {
                datatype: "json",
                url: surveylinkUrl+'/'+row_id,
                height: "100%",
                width: "100%",
                colNames:[surveyNameColTxt,surveyIdColTxt,tokenIdColTxt,dateAddedColTxt],
                colModel:[{ name:'surveyname',index:'surveyname', width:100,align:'center'},
                { name:'surveyid',index:'surveyid', width:90,align:'center'},
                { name:'tokenid',index:'tokenid', width:100,align:'center'},
                { name:'dateadded',index:'added', width:120,align:'center'}],
                caption: linksHeadingTxt,
                gridComplete: function () {
                    var recs = $("#"+second_subgrid_table_id).jqGrid('getGridParam','reccount');
                    if (recs == 0 || recs == null) {
                        //$("#"+second_subgrid_table_id).setGridHeight(40);
                        $("#hide_"+second_subgrid_table_id).hide();
                        //$("#NoRecordContact").show();
                    }
                }
            });
            /* Subgrid that displays user attributes */
            jQuery("#"+subgrid_table_id).jqGrid( {
                url: getAttribute_json+'/'+row_id,
                editurl:editAttributevalue,
                datatype: "json",
                mtype: "post",
                pgbuttons:false,
                recordtext:'',
                pgtext:'',
                caption: attributesHeadingTxt,
                editable:true,
                loadonce : true,
                colNames: [actionsColTxt,participantIdColTxt,attributeTypeColTxt,attributeNameColTxt,attributeValueColTxt,attributePosValColTxt],
                colModel: [ { name:'act',index:'act',width:55,align:'center',sortable:false,formatter:'actions',formatoptions : { keys:true,onEdit:function(id){ }}},
                { name:'participant_id',index:'participant_id', width:150, sorttype:"string",align:"center",editable:true,hidden:true},
                { name:'atttype',index:'atttype', width:150, sorttype:"string",align:"center",editable:true,hidden:true},
                { name:'attname',index:'attname', width:150, sorttype:"string",align:"center",editable:false},
                { name:'attvalue',index:'attvalue', width:150, sorttype:"string",align:"center",editable:true},
                { name:'attpvalues',index:'attpvalues', width:150, sorttype:"string",align:"center",editable:true,hidden:true}],
                rowNum:20,
                pager: pager_id,
                gridComplete: function () {
                    /* Removes the delete icon from the actions bar */
                    $('div.ui-inline-del').html('');
                    /* Removes the edit icon from the actions bar */
                    //$('div.ui-inline-edit').html('');
                },
                ondblClickRow: function(id,subgrid_id) {
                    var parid = id.split('_');
                    var participant_id = $("#displayparticipants_"+parid[0]+"_t").getCell(id,'participant_id');
                    var lsel = parid[0];
                    var can_edit = $('#displayparticipants').getCell(participant_id,'can_edit');
                    if(can_edit == 'false') {
                        var dialog_buttons={};
                        dialog_buttons[okBtn]=function(){
                            $( this ).dialog( closeTxt );
                        };
                        /* End of building array for button functions */
                        $('#notauthorised').dialog({
                            modal: true,
                            title: accessDeniedTxt,
                            buttons: dialog_buttons
                        });
                    } else {
                        var att_type = $("#displayparticipants_"+parid[0]+"_t").getCell(id,'atttype');
                        if(att_type=="DP") {
                            $("#displayparticipants_"+parid[0]+"_t").setColProp('attvalue',{ editoptions:{ dataInit:function (elem) {$(elem).datepicker();}}});
                        }
                        if(att_type=="DD") {
                            var att_p_values = $("#displayparticipants_"+parid[0]+"_t").getCell(id,'attpvalues');
                            $("#displayparticipants_"+parid[0]+"_t").setColProp('attvalue',{ edittype:'select',editoptions:{ value:":Select One;"+att_p_values}});
                        }
                        if(att_type=="TB") {
                            $("#displayparticipants_"+parid[0]+"_t").setColProp('attvalue',{ edittype:'text'});
                            $("#displayparticipants_"+parid[0]+"_t").setColProp('attvalue',{ editoptions:''});
                        }
                        var attap = $("#displayparticipants_"+parid[0]+"_t").getCell(id,'attap');
                        if(id && id!==lastSel2) {
                            jQuery("#displayparticipants_"+parid[0]+"_t").saveRow(lastSel2);
                            lastSel2=id;
                        }
                        $.fn.fmatter.rowactions(id,'displayparticipants_'+parid[0]+'_t','edit',0);
                        jQuery("#displayparticipants_"+parid[0]+"_t").jqGrid('editRow',id,true);
                        jQuery("#displayparticipants_"+parid[0]+"_t").editRow(id,true);
                    }
                },
                height: '100%'
            });
        }
    });
    $.jgrid.formatter.integer.thousandsSeparator=''; //Removes the default spacing as a thousands seperator
    //Todo - global setting for all jqGrids to match language/regional number formats

    /* Set up default buttons in the main jqGrid Pager */
    jQuery("#displayparticipants").jqGrid(
        'navGrid',
        '#pager',
        {add:true,del:true,edit:false,refresh: true,search: false},
        {},
        {width : 400},
        {msg:deleteMsg, width : 700,
            afterShowForm: function($form) {
                /* This code sets the position of the delete dialog to just below the last selected item */
                /* Unless this would put the delete dialog off the page, in which case it will be pushed up a bit */
                var dialog = $form.closest('div.ui-jqdialog'),
                selRowId = jQuery("#displayparticipants").jqGrid('getGridParam', 'selrow'),
                selRowCoordinates = $('#'+selRowId).offset();
                selRowCoordinates.top=selRowCoordinates.top+25;
                selRowCoordinates.left=50;
                if(selRowCoordinates.top+325 > $(window).height()) {
                    selRowCoordinates.top=selRowCoordinates.top-325;
                }
                dialog.offset(selRowCoordinates);
            },
            beforeSubmit : function(postdata, formid) {
                if(!$('#selectable .ui-selected').attr('id')) {
                    alert(nooptionselected);
                    message = "dummy";
                } else {
                    $.post(delparticipantUrl, {
                        participant_id : postdata,
                        selectedoption : $('#selectable .ui-selected').attr('id')
                    }, function(data) {
                    });
                    success = "dummy";
                    message = "dummy";
                    return[success,message];
                }
            }, beforeShowForm:function(form) {
                $('#selectable').bind("mousedown", function (e) {
                    e.metaKey = false;
                }).selectable({
                    tolerance: 'fit'
                })
        }},
        {multipleSearch:true, multipleGroup:true}
    );

    /* Add the full Search Button to the main jqGrid Pager */
    $("#displayparticipants").navButtonAdd(
        '#pager',
        {
            caption:"",
            title: fullSearchTitle,
            buttonicon:'searchicon',
            onClickButton:function(){
                var dialog_buttons={};
                dialog_buttons[searchBtn]=function(){
                    searchconditions="";
                    var dialog_buttons={};
                    if($('#field_1').val() == ''){
                        dialog_buttons[okBtn]=function(){
                            $( this ).dialog( "close" );
                        };
                        /* End of building array for button functions */
                        $('#fieldnotselected').dialog({
                            modal: true,
                            title: error,
                            buttons: dialog_buttons
                        });
                    } else if($('#condition_1').val()=="") {
                        dialog_buttons[okBtn]=function() {
                            $( this ).dialog( "close" );
                        };
                        /* End of building array for button functions */
                        $('#conditionnotselected').dialog({
                            modal: true,
                            title: error,
                            buttons: dialog_buttons
                        });
                    } else {
                        if(conditionid == 1) {
                            searchconditions = searchconditions + $('#field_1').val()+"||"+$('#condition_1').val()+"||"+$('#conditiontext_1').val();
                        } else {
                            searchconditions = $('#field_1').val()+"||"+$('#condition_1').val()+"||"+$('#conditiontext_1').val();
                            for( i=2 ; i<=conditionid; i++) {
                                if($('#field_'+i).val()) {
                                    searchconditions = searchconditions + "||"+ $('#join_'+(i)).val()+"||"+$('#field_'+i).val()+"||"+$('#condition_'+i).val()+"||"+$('#conditiontext_'+i).val();
                                }
                            }
                        }
                        jQuery("#displayparticipants").jqGrid('setGridParam',{
                            url:jsonSearchUrl+'/'+searchconditions,
                            gridComplete: function(){
                                if(jQuery("#displayparticipants").jqGrid('getGridParam', 'records') == 0) {
                                    var dialog_buttons={};
                                    dialog_buttons[okBtn]=function(){
                                        $( this ).dialog( "close" );
                                        };
                                    $("<p>"+noSearchResultsTxt+"</p>").dialog({
                                        modal: true,
                                        buttons: dialog_buttons,
                                        resizable: false
                                    });
                                }
                            }}).trigger("reloadGrid");
                        $(this).dialog("close");
                    }
                };
                dialog_buttons[cancelBtn]=function(){
                $( this ).dialog( "close" );
            };
            dialog_buttons[resetBtn]=function(){
                jQuery("#displayparticipants").jqGrid('setGridParam',{
                    url:jsonUrl,
                    gridComplete: function(){
                        if(jQuery("#displayparticipants").jqGrid('getGridParam', 'records') == 0) {
                            var dialog_buttons={};
                            dialog_buttons[okBtn]=function(){
                                $( this ).dialog( "close" );
                            };
                            $("<p>"+noSearchResultsTxt+"</p>").dialog({
                                modal: true,
                                buttons: dialog_buttons,
                                resizable: false
                            });
                        }
                    }
                });
                $("#displayparticipants").jqGrid('setGridParam', { search: false, postData: { "filters": ""} }).trigger("reloadGrid");
            };
            /* End of building array for button functions */
            $("#search").dialog({
                height: 300,
                width: 750,
                modal: true,
                title : fullSearchTitle,
                buttons: dialog_buttons
            });
        }
    }
    );

    /* Add the CSV Export Button to the main jqGrid Pager */
    $("#displayparticipants").navButtonAdd(
        '#pager',
        {
            caption:"",
            title:exportToCSVTitle,
            buttonicon:'ui-icon-extlink',
            onClickButton:function() {
                $.post(
                    exporttocsvcount,
                    { searchcondition: jQuery('#displayparticipants').jqGrid('getGridParam', 'url')},
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
                    }
                );
            }
        }
    );

    $.extend(jQuery.jgrid.edit,{
        closeAfterAdd: true,
        reloadAfterSubmit: true,
        closeOnEspace:true
    });

    //script for sharing of participants
    $("#sharingparticipants").dialog({
        title: spTitle,
        modal: true,
        autoOpen: false,
        height: 400,
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
        }
        );
    }

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
        } else {
            /* build an array containing the various button functions */
            /* Needed because it's the only way to label a button with a variable */
            var dialog_buttons={};
            dialog_buttons[spAddBtn]=function(){
                var row = myGrid .getGridParam('selarrrow');
                shareParticipants(row);
            };
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
        /* the rows variable will contain the UUID of individual items that been ticked in the jqGrid */
        /* if it is empty, then no items have been ticked */
        var rows = myGrid.getGridParam('selarrrow');

        if(rows=="") {
            var totalitems = myGrid.getGridParam('records');
            $('#allinview').text(addAllInViewTxt.replace('%s', totalitems));
            $('#allinview').show();
            $('#selecteditems').hide();
        } else {
            var totalitems = rows.length;
            $('#selecteditems').text(addSelectedItemsTxt.replace('%s', totalitems));
            $('#selecteditems').show();
            $('#allinview').hide();
        }

        var dialog_buttons={};
        dialog_buttons[mapButton]=function(){
            var survey_id=$('#survey_id').val();
            var redirect ="";
            if(survey_id===null) {
                /* No survey has been selected */
                alert(selectSurvey);
            } else {
                /* Check if user wants to see token table after adding new participants */
                if(jQuery('#redirect').is(":checked")) {
                    redirect = "redirect";
                } else {
                    redirect = "";
                }
                /* Submit the form with appropriate options depending on whether
                individual users are selected, or the whole grid is to be copied */
                if(rows=="") { /* All in grid */
                    $.post(
                    getSearchIDs,
                    { searchcondition: jQuery('#displayparticipants').jqGrid('getGridParam','url')},
                    function(data) {
                        $('#count').val($('#ui-dialog-title-addsurvey').text());
                        $('#participant_id').val(data);
                        $("#addsurvey").submit();
                    });
                } else { /* Add selected (checked) jqGrid items only */
                    rows = myGrid.getGridParam('selarrrow');
                    $('#count').val($('#ui-dialog-title-addsurvey').text());
                    $('#participant_id').val(rows);
                    $("#addsurvey").submit();
                }
            }
        };
        dialog_buttons[cancelBtn]=function(){
            $(this).dialog("close");
        };
        /* End of building array containing button functions */

        $("#addsurvey").dialog({
            height: 500,
            width: 500,
            title : addsurvey,
            modal: true,
            open: function(event, ui) {
                $('#addsurvey').dialog('option', 'title', addsurvey + ' ('+totalitems+')');
            },
            buttons: dialog_buttons
        });

        if (!($("#survey_id").length > 0)) {
            $('#addsurvey').html(addpartErrorMsg);
        }
    });
});