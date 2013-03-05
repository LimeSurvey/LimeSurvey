// $Id: listsurvey.js 9692 2011-01-15 21:31:10Z c_schmitz $
$(document).ready(function(){

    var old_owner = '';

    $(".ownername_edit").live('click',function(){
        var oldThis = this;
        var ownername_edit_id = $(this).attr('id');
        var survey_id = ownername_edit_id.slice(15);
        var translate_to = $(this).attr('translate_to');
        var initial_text = $(this).html();
        $.getJSON(getuserurl,'',function(oData)
        {
            old_owner =  $($(oldThis).parent()).html();

            old_owner = (old_owner.split("("))[0];
            $($(oldThis).parent()).html('<select class="ownername_select" id="ownername_select_'+survey_id+'"></select>'
            + '<input class="ownername_button" id="ownername_button_'+survey_id+'" type="button" initial_text="'+initial_text+'" value="'+delBtnCaption+'">');
            $(oData).each(function(key,value){
                $('#ownername_select_'+survey_id).
                append($("<option id='opt_"+value[1]+"'></option>").
                attr("value",value[0]).
                text(value[1]));
            });
            $("#ownername_select_"+survey_id+ " option[id=opt_"+old_owner+"]").attr("selected","selected");
        });
    });

    $(".ownername_button").live('click',function(){
        var oldThis = this;
        var initial_text = $(this).attr('initial_text');
        var ownername_select_id = $(this).attr('id');
        var survey_id = ownername_select_id.slice(17);
        var newowner = $("#ownername_select_"+survey_id).val();
        var translate_to = $(this).attr('value');

        $.getJSON(ownerediturl+'/newowner/' + newowner + '/surveyid/' + survey_id,'', function (data){

            var objToUpdate = $($(oldThis).parent());

            if (data.record_count>0)
                $(objToUpdate).html(data.newowner);
            else
                $(objToUpdate).html(old_owner);

            $(objToUpdate).html($(objToUpdate).html() + ' (<a id="ownername_edit_69173" translate_to='+translate_to+' class="ownername_edit" href="#">'+initial_text+'</a>)' );
        });
    });

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
    function returnColModel() {
        if($.cookie("detailedsurveycolumns")) {
            hidden=$.cookie("detailedsurveycolumns").split('|');
            for (i=0;i<hidden.length;i++)
                if(hidden[i]!="false") colModels[i]['hidden']=true;
        }
        return colModels;
    }
    jQuery("#displaysurveys").jqGrid({
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
        width: $(window).width()-4,
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
        loadComplete: function(data){
            // Need this for vertical scrollbar 
			$('#displaysurveys').setGridWidth($(window).width()-4);
            $('.wrapper').width($('#displaysurveys').width()+4);
            $('.footer').outerWidth($('#displaysurveys').outerWidth()+4).css({ 'margin':'0 auto' });
        }
    });
    jQuery("#displaysurveys").jqGrid('navGrid','#pager',{ deltitle: sDelTitle, 
                                                          searchtitle: sSearchTitle,
                                                          refreshtitle: sRefreshTitle,
                                                          add:false,
                                                          del:true,
                                                          edit:false,
                                                          refresh: true,
                                                          search: true
                                                        },{},{},{ msg:delmsg, 
                                                                  bSubmit: sDelCaption,
                                                                  caption: sDelCaption,
                                                                  bCancel: sCancel,
                                                                  width : 450,
                                                                  afterShowForm: function(form) {
                                                                    form.closest('div.ui-jqdialog').center();
                                                                  },
                                                          afterSubmit: function(response, postdata) {
                                                              if (postdata.oper=='del')
                                                              {
                                                                  // Remove surveys from dropdown, too
                                                                    aSurveyIDs=postdata.id.split(",");
                                                                    $.each(aSurveyIDs,function(iIndex, iSurveyID){
                                                                        $("#surveylist option[value='"+iSurveyID+"']").remove();   
                                                                    })
                                                              };
                                                              return [true];
                                                          }
                                                                },
                                                                {
                                                                      caption: sSearchCaption,
                                                                      Find : sFind,
                                                                      odata : [ sOperator1, sOperator2, sOperator3, sOperator4, sOperator5, sOperator6, sOperator7, sOperator8, sOperator9, sOperator10, sOperator11, sOperator12, sOperator13, sOperator14 ],
                                                                      Reset: sReset
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

	$('.wrapper').width($('#displaysurveys').width()+4);
	$('.footer').outerWidth($('#displaysurveys').outerWidth()+4).css({ 'margin':'0 auto' });
	
    $(window).bind('resize', function() {
        $('#displaysurveys').setGridWidth($(window).width()-4);
        $('.wrapper').width($('#displaysurveys').width()+4);
        $('.footer').outerWidth($('#displaysurveys').outerWidth()+4).css({ 'margin':'0 auto' });
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
