var conditionid=1;
function addcondition(newcid)
{
    conditionid++;
    if(typeof optionstring === "undefined") {
        optionstring = "";
    }
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
    <option value='owner_name'>"+ownernameTxt+"</option>"+optionstring+"\n\
    </select>\n\</td>\n\<td>\n\
    <select name='condition_"+conditionid+"' id='condition_"+conditionid+"'>\n\
    <option>"+selectTxt+"</option>\n\
    <option value='equal'>"+equalsTxt+"</option>\n\
    <option value='contains'>"+containsTxt+"</option>\n\
    <option value='beginswith'>"+beginswithTxt+"</option>\n\
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
    $("#addbutton").click(function(){
        conditionid++;
        if(typeof optionstring === "undefined") {
            optionstring = "";
        }
        html = "<tr name='joincondition_"+conditionid+"' id='joincondition_"+conditionid+"'><td>\n\
        <select name='join_"+conditionid+"' id='join_"+conditionid+"'>\n\
        <option value='and'>"+andTxt+"</option>\n\
        <option value='or'>"+orTxt+"</option>\n\
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
        <option value='beginswith'>"+beginswithTxt+"</option>\n\
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


    if(typeof searchconditions === "undefined") {
        searchconditions = {};
    }

    var field;
    $('#searchbutton').click(function(){
    });

    var lastSel,lastSel2;

    /* The main jqGrid, displaying Participants */
    jQuery("#displayparticipants").jqGrid({
        loadtext : sLoadText,
        align:"center",
        headertitles: true,
        url: jsonUrl,
        editurl: editUrl,
        datatype: "json",
        mtype: "post",
        postData: {searchcondition:searchconditions},
        colNames : jQuery.parseJSON(colNames),
        colModel: jQuery.parseJSON(colModels),
        direction: $('html').attr('dir'),
        height: "100%",
        width: "100%",
        rowNum: 25,
        editable:true,
        scrollOffset:0,
        autowidth: autowidth,
        sortable : true,
        sortname: 'firstname',
        sortorder: 'asc',
        viewrecords : true,
        rowList: [25,50,100,250,500,1000,2500,5000],
        multiselect: true,
        loadComplete : function() {
            /* Sneaky way of adding custom icons to jqGrid pager buttons */
            $("#pager").find(".ui-share-icon")
            .css({"background-image":"url("+imageurl+"share_12.png)", "background-position":"0", "color":"black"});
            $("#pager").find(".ui-addtosurvey-icon")
            .css({"background-image":"url("+imageurl+"tokens_12.png)", "background-position":"0", "color":"black"});
        },
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
            if($('tr#'+id).closest('tr.ui-subgrid').length == 0) { // Only want this fired on main grid rows, subgrid rows use editModifier()
                var can_edit = ($('#displayparticipants').getCell(id, 'can_edit')=='true') && bEditPermission;
                if(!can_edit) {
                    var dialog_buttons={};
                    dialog_buttons[okBtn]=function() {
                        $( this ).dialog( "close" );
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
			}
        },
        pager: "#pager",
        pgtext: pageViewTxt,
        emptyrecords: emptyRecordsTxt,
        recordtext: viewRecordTxt,
        caption: participantsTxt,
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
                direction: $('html').attr('dir'),
                datatype: "json",
                url: surveylinkUrl+'/'+row_id,
                height: "100%",
                width: "100%",
                loadonce: true,
                sortable: true,
                colNames:[surveyNameColTxt,surveyIdColTxt,tokenIdColTxt,dateAddedColTxt,dateInvitedColTxt,dateCompletedColTxt],
                colModel:[{ name:'survey_name',index:'survey_name', width:400,align:'center', sorttype:"string", sortable: true},
                    { name:'survey_id',index:'survey_id', width:90,align:'center', sorttype:"int", sortable: true},
                    { name:'token_id',index:'token_id', width:80, align:'center', sorttype:"int", sortable: true},
                    { name:'date_created',index:'date_created', width:100,align:'center', sorttype:"string", sortable: true},
                    { name:'date_invited',index:'date_invited', width:100,align:'center', sorttype:"string", sortable: true},
                    { name:'date_completed',index:'date_invited', width:100,align:'center', sorttype:"string", sortable: true}],
                caption: linksHeadingTxt,
                gridComplete: function () {
                    var recs = $("#"+second_subgrid_table_id).jqGrid('getGridParam','reccount');
                    if (recs == 0 || recs == null) {
                        //$("#"+second_subgrid_table_id).setGridHeight(40);
                        $("#hide_"+second_subgrid_table_id).hide();
                        //$("#NoRecordContact").show();
                    } else {
                        $("#hide_"+second_subgrid_table_id).css("margin-bottom", "20px"); //Some spacing after the subgrid
                    }
                }
            });
            /* Subgrid that displays user attributes */
            jQuery("#"+subgrid_table_id).jqGrid( {
                url: getAttribute_json+'/'+row_id,
                direction: $('html').attr('dir'),
                editurl:editAttributevalue,
                datatype: "json",
                mtype: "post",
                caption: attributesHeadingTxt,
                ignoreCase: true,
                editable: true,
                loadonce : true,
                colNames: [actionsColTxt,participantIdColTxt,attributeTypeColTxt,attributeIdColTxt,attributeNameColTxt,attributeValueColTxt,attributePosValColTxt],
                colModel: [ { name:'act',index:'act',width:65,align:'center',search: false,sortable:false, formatter:'actions',
                    formatoptions : { keys:true,onEdit:function(id){
                        var iRow = $('#' + $.jgrid.jqID(id))[0].rowIndex;
                        editModifier(id,iRow, method='edit');
                    }}},
                    { name:'participant_id',index:'participant_id', width:10, sorttype:"string",align:"center",editable:true,hidden:true},
                    { name:'atttype',index:'atttype', width:170, sorttype:"string",align:"center",editable:false,hidden:true},
                    { name:'attid',index:'attid', width:170, sorttype:"string",align:"center",editable:true,hidden:true},
                    { name:'attname',index:'attname', width:150, sorttype:"string",align:"center",editable:false},
                    { name:'attvalue',index:'attvalue', width:170, sorttype:"string",align:"center",editable:true},
                    { name:'attpvalues',index:'attpvalues', width:10, sorttype:"string",align:"center",editable:false,hidden:true}],
                sortname: attributeNameColTxt,
                sortorder: 'asc',
                sortable: true,
                pager: pager_id,
                viewrecords: true,
                pgbuttons: true,
                pginput: true,
                recordtext:'',
                pgtext:'',
                rowNum:10,
                rowList:[10,25,50,100,250,500,1000,2500,5000],  /* start with 10 to keep it smaller */
                gridComplete: function () {
                    /* Removes the delete icon from the actions bar */
                    $('div.ui-inline-del').html('');
                    $("#gview_"+subgrid_table_id).css("margin-top", "20px"); //Some spacing after the subgrid
                    $(".ui-inline-edit").attr('title',sEditAttributeValueMsg);
                    $(".ui-inline-save").attr('title',sSubmit);
                    $(".ui-inline-cancel").attr('title',sCancel);
                },
                ondblClickRow: function(id,subgrid_id) {
                    editModifier(id, subgrid_id, method='click');
                },
                height: '100%'
            });

            /* Pager for attribute subgrid */
            jQuery("#"+subgrid_table_id).jqGrid('navGrid',"#"+pager_id,{
                refresh: false,
                edit:false,
                add:false,
                del:false,
                search:false});
            jQuery("#"+subgrid_table_id).jqGrid('filterToolbar', {searchOnEnter : false, defaultSearch: 'cn'});
        }
    });

    $.jgrid.formatter.integer.thousandsSeparator=''; //Removes the default spacing as a thousands separator
    //Todo - global setting for all jqGrids to match language/regional number formats

    /* Set up default buttons in the main jqGrid Pager */
    jQuery("#displayparticipants").jqGrid(
        'navGrid',
        '#pager',
        {add:true,
            del:true,
            edit:false,
            refresh: true,
            search: false,
            alertcap: sWarningMsg,
            alerttext: sSelectRowMsg,
            addtitle: createParticipantTxt,
            deltitle: deleteParticipantTxt,
            refreshtitle: refreshListTxt},
        {}, //Default settings for edit
        {
            width : 500,
            addCaption: sAddCaption,
            bSubmit: sAddButtonCaption,
            bCancel: sCancel,
            afterShowForm: function(form) {
                form.closest('div.ui-jqdialog').center();
            }
        }, //default settings for add
        {msg:deleteMsg,
            bCancel: sCancel,
            caption: sDeleteDialogCaption,
            bSubmit: sDeleteButtonCaption,
            width : 900,
            afterShowForm: function($form) {
                /* This code sets the position of the delete dialog to just below the last selected item */
                /* Unless this would put the delete dialog off the page, in which case it will be pushed up a bit */
                var dialog = $form.closest('div.ui-jqdialog'),
                selRowId = jQuery("#displayparticipants").jqGrid('getGridParam', 'selrow'),
                selRowCoordinates = $('#'+selRowId).offset();
                selRowCoordinates.top=selRowCoordinates.top+25;
                selRowCoordinates.left=100;
                if(selRowCoordinates.top+325 > $(window).height()) {
                    selRowCoordinates.top=selRowCoordinates.top-325;
                }
                dialog.offset(selRowCoordinates);
            },
            beforeSubmit : function(postdata, formid) {
                if(!$('#deleteMode input[type=\'radio\']:checked').val()) {
                    alert(nooptionselected);
                    message = "dummy";
                } else {
                    $.post(delparticipantUrl, {
                        participant_id : postdata,
                        selectedoption : $('#deleteMode input[type=\'radio\']:checked' ).val()
                        }, function(data) {
                    });
                    success = "dummy";
                    message = "dummy";
                    return[success,message];
                }
            }
        },
        {multipleSearch:true, multipleGroup:true}
    );

    /* Add the full Search Button to the main jqGrid Pager */
    $("#displayparticipants").navButtonAdd('#pager',
        {
            caption:"",
            title: fullSearchTitle,
            buttonicon:'ui-icon-search',
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
                            mtype:'POST',
                            postData:{searchcondition:searchconditions},
                            url:jsonSearchUrl,
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
                                    jQuery("#displayparticipants").jqGrid('setGridParam',{mtype:'GET'});
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
    // Add separator
    $("#displayparticipants").navSeparatorAdd('#pager');
    /* Add the CSV Export Button to the main jqGrid Pager */
    $("#displayparticipants").navButtonAdd('#pager',
        {
            caption:"",
            title:exportToCSVTitle,
            buttonicon:'ui-icon-extlink',
            onClickButton:function() {
                $.post(
                    exporttocsvcount,
                    { searchcondition: searchconditions,
                        searchURL: jQuery('#displayparticipants').jqGrid('getGridParam', 'url')
                    },
                    function(data) {
                        titlemsg = data;
                        var dialog_buttons={};
                        dialog_buttons[exportBtn]=function(){
                            $.download(exportToCSVURL,{ searchcondition: searchconditions, attributes: $('#attributes').val().join(' ') },"POST");
                            $(this).dialog("close");
                        };
                        dialog_buttons[cancelBtn]=function(){
                            $(this).dialog("close");
                        };
                        /* End of building array for button functions */
                        $('#exportcsv').dialog({
                            modal: true,
                            title: titlemsg,
                            buttons: dialog_buttons,
                            width : 600,
                            height : 300,
                            open: function(event, ui) {
                                $('#attributes').multiselect({ includeSelectAllOption:true,
                                    selectAllValue: '0',
                                    selectAllText: sSelectAllText,
                                    nonSelectedText: sNonSelectedText,
                                    nSelectedText: sNSelectedText,
                                    maxHeight: 140 });
                            }
                        });
                    }
                );
            }
        }
    );
    $("#displayparticipants").navButtonAdd('#pager',
        {
            caption: "",
            title: shareParticipantTxt,
            buttonicon: "ui-share-icon",
            onClickButton:function(){
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
                        if ($('#shareuser').val()=='')
                        {
                            alert(sSelectUserAlert);
                            return;
                        }
                        var row = myGrid .getGridParam('selarrrow');
                        shareParticipants(row);
                    };
                    dialog_buttons[cancelBtn]=function(){
                        $(this).dialog("close");
                    };
                    /* End of building array for button functions */

                    $("#shareform").dialog({
                        height: 400,
                        width: 400,
                        modal: true,
                        buttons: dialog_buttons
                    });
                }
                if (!($("#shareuser").length > 0)) {
                    $('#shareform').html(sfNoUser);
                }
            }
        }
    );
    $("#displayparticipants").navButtonAdd('#pager',
        {
            caption: "",
            title: addToSurveyTxt,
            buttonicon: "ui-addtosurvey-icon",
            onClickButton:function(){
                var selected = "";
                var myGrid = $("#displayparticipants").jqGrid();
                /* the rows variable will contain the UUID of individual items that been ticked in the jqGrid */
                /* if it is empty, then no items have been ticked */
                var rows = myGrid.getGridParam('selarrrow');

                /* Show summary of how many participants will be added to the survey */
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
                                {
                                    searchcondition: searchconditions,
                                    searchURL: jQuery('#displayparticipants').jqGrid('getGridParam', 'url')
                                },
                                function(data) {
                                    $('#count').val(totalitems);
                                    $('#participant_id').val(data);
                                    $("#addsurvey").submit();
                            });
                        } else { /* Add selected (checked) jqGrid items only */
                            rows = myGrid.getGridParam('selarrrow');
                            $('#count').val(totalitems);
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
                    $('div[aria-describedby="addsurvey"] div.ui-dialog-buttonset button:first-child').hide();
                }
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
            can_edit:$('#can_edit').prop('checked')
            }, function(msg){
                $(this).dialog("close");
                alert(msg+"."+shareMsg);
                $(location).attr('href',redUrl);
        });
    }
    //End of Script for sharing

    function addtoSurvey(participant_id,survey_id,redirect) {
        $("#addsurvey").load(postUrl,
            {participantid:participant_id},
            function(){
                $(location).attr('href',attMapUrl+'/'+survey_id+'/'+redirect);
        });
    }

    function basename(path) {
        return path.replace(/\\/g,'/').replace( /.*\//, '' );
    }

    function editModifier(id, subgrid_id, method) {
        var parid = id.split('_');
        var participant_id = $("#displayparticipants_"+parid[0]+"_t").getCell(id,'participant_id');
        var can_edit = ($('#displayparticipants').getCell(parid[0],'can_edit')=='true' && bEditPermission);
        if(!can_edit) {
            var dialog_buttons={};
            dialog_buttons[okBtn]=function(){
                $( this ).dialog( "close" );
            };
            /* End of building array for button functions */
            $('#notauthorised').dialog({
                modal: true,
                title: accessDeniedTxt,
                buttons: dialog_buttons
            });
        } else {
            if(id && id!==lastSel2) { //If there was already another row open for editin save it before editing this one
                $('tr#'+lastSel2+' div.ui-inline-save').click();
                lastSel2=id;
            }
            var att_type = $("#displayparticipants_"+parid[0]+"_t").getCell(id,'atttype');
            if(att_type=="DP") { //Date
                $("#displayparticipants_"+parid[0]+"_t").setColProp('attvalue',{ edittype:'text', editoptions:{ dataInit:function (elem) {$(elem).datepicker();}}});
            }
            if(att_type=="DD") { //Dropdown
                var att_p_values = $("#displayparticipants_"+parid[0]+"_t").getCell(id,'attpvalues');
                $("#displayparticipants_"+parid[0]+"_t").setColProp('attvalue',{ edittype:'select',editoptions:{ value:":;"+att_p_values}});
            }
            if(att_type=="TB") { //Textbox
                $("#displayparticipants_"+parid[0]+"_t").setColProp('attvalue',{ edittype:'text'});
                $("#displayparticipants_"+parid[0]+"_t").setColProp('attvalue',{ editoptions:''});
            }
            if(method=='edit') {
             //   jQuery("#displayparticipants_"+parid[0]+"_t").jqGrid('restoreRow',id);
            }
            if(method=='click') {
                jQuery("#displayparticipants_"+parid[0]+"_t").jqGrid('restoreRow',id);
				jQuery("tr#"+id+" .ui-inline-edit").hide();
				jQuery("tr#"+id+" .ui-inline-save, tr#"+id+" .ui-inline-cancel").show();
            }
            jQuery("#displayparticipants_"+parid[0]+"_t").jqGrid('editRow',id,true);
        }
    }

});