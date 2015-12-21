$(document).ready(function(){

    var old_owner = '';

    $(document).on('click', ".ownername_edit", function(){
        var oldThis = this;
        var ownername_edit_id = $(this).attr('id');
        var survey_id = ownername_edit_id.slice(15);
        var translate_to = $(this).attr('translate_to');
        var initial_text = $(this).html();
        $.post( getuserurl,function( oData ) {
            if(typeof oData=="object")
            {
                old_owner =  $($(oldThis).parent()).html();
                old_owner = (old_owner.split("("))[0];
                $($(oldThis).parent()).html('<select class="ownername_select" id="ownername_select_'+survey_id+'"></select>\n'
                + '<input class="ownername_button" id="ownername_button_'+survey_id+'" type="button" initial_text="'+initial_text+'" value="'+delBtnCaption+'">');
                $.each(oData, function(key,value){
                    $('#ownername_select_'+survey_id).
                    append($("<option id='opt_"+value+"'></option>").
                    attr("value",value).
                    text(key));
                });
                $("#ownername_select_"+survey_id+ " option[id=opt_"+old_owner+"]").attr("selected","selected");
            }
            //else
        }).fail(function() {
            //$notifycontainer.notify("create", 'error-notify', { message:"An error was occured"});// To set in language or in extension (something like lsalert(text, type="default");
        });
    });

    $(document).on('click',".ownername_button", function(){
        var oldThis = this;
        var initial_text = $(this).attr('initial_text');
        var ownername_select_id = $(this).attr('id');
        var survey_id = ownername_select_id.slice(17);
        var newowner = $("#ownername_select_"+survey_id).val();
        var translate_to = $(this).attr('value');
        $.post( ownerediturl,{"newowner":newowner,"surveyid":survey_id},function( oData ) {
            if(typeof oData=="object")// To test json
            {
                var objToUpdate = $($(oldThis).parent());
                if (oData.record_count>0)
                    $(objToUpdate).html(oData.newowner);
                else
                    $(objToUpdate).html(old_owner);
                $(objToUpdate).html($(objToUpdate).html() + ' (<a id="ownername_edit_69173" translate_to='+translate_to+' class="ownername_edit" href="#">'+initial_text+'</a>)' );
            }
        }).fail(function() {
            //$notifycontainer.notify("create", 'error-notify', { message:"An error was occured"});// To set in language
        });
    });

    function returnColModel() {
        if($.cookie("detailedsurveycolumns")) {
            hidden=$.cookie("detailedsurveycolumns").split('|');
            for (i=0;i<hidden.length;i++)
                if(hidden[i]!="false") colModels[i]['hidden']=true;
        }
        return colModels;
    }
    jQuery("#displaysurveys").jqGrid({
        autoencode: false,// autoencode to false is really a bad idea. Need JS system for link and update
        direction: $('html').attr('dir'),
        recordtext: sRecordText,
        emptyrecords: sEmptyRecords,
        pgtext: sPageText,
        loadtext : sLoadText,
        align:"center",
        url: jsonUrl,
        editurl: editUrl,
        datatype: "json",
        mtype: "post",
        colNames : colNames,
        colModel: returnColModel(),
        toppager: true,
        height: "100%",
        autowidth:true,
        //width: $("#surveylist-wrapper").innerWidth(),
        shrinkToFit: true,
        ignoreCase: true,
        rowNum: 25,
        editable:true,
        scrollOffset:0,
        sortable : true,
        hidegrid : false,
        sortname: 'sid',
        sortorder: 'asc',
        viewrecords : true,
        rowList: [25,50,100,250,500,1000,2500,5000],
        multiselect: true,
        loadonce : true,
        pager: "#pager",
        caption: sCaption,
        beforeProcessing: function(data){
            $('#displaysurveys tbody').hide();
        },
        loadComplete: function(data){
            // Need this for vertical scrollbar
			$('#displaysurveys').setGridWidth($("#surveylist-wrapper").innerWidth());
            if (jQuery("#displaysurveys").jqGrid('getGridParam','datatype') === "json") {
                setTimeout(function(){
                    jQuery("#displaysurveys").trigger("reloadGrid");
					$('#displaysurveys tbody').show();
                },100);
            }
        }
    });

    // Inject the translations into jqGrid
    $.extend($.jgrid,{
        del:{
            msg:delmsg,
            bSubmit: sDelCaption,
            caption: sDelCaption,
            bCancel: sCancel
        },
        search : {
            odata : [ sOperator1, sOperator2, sOperator3, sOperator4, sOperator5, sOperator6, sOperator7, sOperator8, sOperator9, sOperator10, sOperator11, sOperator12, sOperator13, sOperator14, sOperator15, sOperator16 ],
            caption: sSearchCaption,
            Find : sFind,
            Reset: sReset,
        }
    });

    jQuery("#displaysurveys").jqGrid('navGrid','#pager',{
        deltitle: sDelTitle,
        searchtitle: sSearchTitle,
        refreshtitle: sRefreshTitle,
        alertcap: sWarningMsg,
        alerttext: sSelectRowMsg,
        add:false,
        del:true,
        edit:false,
        refresh: true,
        search: true
        },{},{},{
            width : 450,
            afterShowForm: function(form) {
                form.closest('div.ui-jqdialog').center();
            },
            beforeSubmit: function(postdata, formid) {
                var gridIdAsSelector = $.jgrid.jqID(this.id);
                $("#delmod" + gridIdAsSelector).hide();
                $("#load_" + gridIdAsSelector).show().center();
                return [true];
            },
            afterSubmit: function(response, postdata) {
                var gridIdAsSelector = $.jgrid.jqID(this.id);
                $("#load_" + gridIdAsSelector).hide();
                if (postdata.oper=='del')
                {
                    // Remove surveys from dropdown, too
                    aSurveyIDs=postdata.id.split(",");
                    $.each(aSurveyIDs,function(iIndex, iSurveyID){
                        $("#surveylist option[value='"+iSurveyID+"']").remove();
                    });
                };
                return [true];
            }
        },
        {
            width:600
    });
    jQuery("#displaysurveys").jqGrid('filterToolbar', {searchOnEnter : false,defaultSearch: 'cn'});
    jQuery("#displaysurveys").jqGrid('navButtonAdd','#pager',{
        buttonicon:"ui-icon-calculator",
        caption:"",
        title: sSelectColumns,
        onClickButton : function (){
            jQuery("#displaysurveys").jqGrid('columnChooser', {
                caption: sSelectColumns,
                bSubmit: sSubmit,
                bCancel: sCancel,
                done : function (perm) {
                    if (perm) {
                        this.jqGrid("remapColumns", perm, true);
                        var hidden = [];
                        $.each($("#displaysurveys").getGridParam("colModel"), function(key, val) {hidden.push( val['hidden'] );});
                        hidden.splice(0,1);
                        $.cookie("detailedsurveycolumns", hidden.join("|") );
                    }
                }
            });
        }
    });

    $(window).bind('resize', function() {
        $('#displaysurveys').setGridWidth($("#surveylist-wrapper").innerWidth());
    }).trigger('resize');

    /* Trigger the inline search when the status list changes */
    $('#gs_status_select').change(function() {
        $("#gs_status").val($('#gs_status_select').val());
        var e = jQuery.Event("keydown");
        $("#gs_status").trigger(e);
    });
    /* Trigger the inline search when the access list changes */
    $('#gs_access_select').change(function() {
        $("#gs_access").val($('#gs_access_select').val());
        var e = jQuery.Event("keydown");
        $("#gs_access").trigger(e);
    });
    /* Change the text search above "Status" icons to a dropdown */
    var parentDiv=$('#gs_status').parent();
    parentDiv.prepend($('#gs_status_select'));
    $('#gs_status_select').css("display", "");
    $('#gs_status').css("display", "none");
    /* Change the text search above "Access" to a dropdown */
    var parentADiv=$('#gs_access').parent();
    parentADiv.prepend($('#gs_access_select'));
    $('#gs_access_select').css("display", "");
    $('#gs_access').css("display", "none");


});
