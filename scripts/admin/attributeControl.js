$(document).ready(function() {
    var CM = [
        {name: 'actions', width: 75, align: 'center', fixed: true, sortable: false, resize: false, formatter: 'actions', search: false},
        {name: 'attribute_name', index: 'attribute_name', width: 250, align:"center", editable: true, editrules: {"required":true}},
        {name: 'attribute_type', index: 'attribute_type', width: 250, align:"center", editable: true, edittype:"select", editoptions:{value:attributeTypeSelections}, stype: 'select', searchoptions: {sopt: ['eq', 'ne'], value:attributeTypeSearch}},
        {name: 'visible', index: 'visible', width: 250, align: 'center', editable: true, formatter: checkboxFormatter, edittype: 'checkbox', edittype: "checkbox", editoptions: {value: "TRUE"}, stype: 'select', searchoptions: {sopt: ['eq', 'ne'], value: "TRUE:Yes;FALSE:No"}}
    ];

    $("#flashinfo").css("opacity", 0); //Make sure the flash message doesn't display in IE
    
    // Set some custom messages
    $.jgrid.edit.msg.required = sRequired;

    $("#attributeControl").jqGrid({
        direction: $('html').attr('dir'),
        loadtext : sLoadText,
        align:"center",
        url: attributeInfoUrl,
        editurl : editAttributeUrl,
        datatype: "json",
        mtype: "post",
        editable : true,
        colNames: jQuery.parseJSON(attributeControlCols),
        colModel: CM,
        height: "100%",
        width: "100%",
        rowNum: 25,
        scrollOffset:0,
        autowidth: true,
        loadonce: true,
        sortname : "attribute_name",
        rowList: [25,50,100,250,500,1000,2500,5000],
        multiselect: true,
        pager: "#pager",
        pgtext: pagerMsg,
        emptyrecords: emptyRecordsTxt,
		gridComplete: function() {
			// Disable "Add" button if more than 59 attributes
			if($('#attributeControl').jqGrid('getGridParam', 'records') > 59) {
				var newCell = $('#add_attributeControl').clone();
				$('#add_attributeControl').hide().before(newCell);
				$('#add_attributeControl:eq(0)').attr('id', 'add_attributeControl_new').attr('title', addDisabledCaption);
				$('#add_attributeControl_new .ui-icon').addClass('ui-state-disabled');
			}
			else {
				$('#add_attributeControl').show();
				$('#add_attributeControl_new').remove();
			}
		},
        recordtext: viewRecordTxt
    });

    $.extend($.fn.fmatter.rowactions = function(act) {
            var delOptions = {
                bCancel: sCancel,
                bSubmit: sDeleteButtonCaption,                
                caption: deleteCaption,
                msg: deleteMsg,
                reloadAfterSubmit: true,
                width: 400
            };
            var $tr = $(this).closest("tr.jqgrow");
            rid = $tr.attr("id");
            gid = $(this).closest("table.ui-jqgrid-btable").attr('id').replace(/_frozen([^_]*)$/,'$1');
            switch(act)
            {
                case 'edit' :
                    window.open(attributeEditUrl + '/' + rid, '_top');
                    break;
                case 'del':
                    $('#'+gid).jqGrid('delGridRow', rid, delOptions);
                    break;
            }
        }
    );

    $('#attributeControl').jqGrid('navGrid', '#pager',
        { add:true,
            edit:false,
            del:true,
            alertcap: sWarningMsg,
            alerttext: sSelectRowMsg,
            addtitle: addCaption,
            deltitle: deleteCaption,
            edittitle: sEditAttributeMsg,
            searchtitle: searchMsg,
            refreshtitle: refreshMsg},
        {
            edittitle: sEditAttributeMsg,
        }, //Default settings for edit
        { addCaption: addCaption,
            bCancel: sCancel,
            bSubmit: sSaveButtonCaption,                
            closeAfterAdd: true,
            width: 400,
            afterSubmit: function () {
                $(this).jqGrid('setGridParam', {datatype: 'json'});
                return [true,'',false]; //no error and no new rowid
            },
        }, //default settings for add
        {    reloadAfterSubmit: true,

            caption: deleteCaption,
            msg: deleteMsg,
            width: 500,
            afterShowForm: function($form) {
                /* This code sets the position of the delete dialog to just below the last selected item */
                /* Unless this would put the delete dialog off the page, in which case it will be pushed up a bit */
                var dialog = $form.closest('div.ui-jqdialog'),
                selRowId = jQuery("#attributeControl").jqGrid('getGridParam', 'selrow'),
                selRowCoordinates = $('#'+selRowId).offset();
                selRowCoordinates.top=selRowCoordinates.top+25;
                selRowCoordinates.left=50;
                if(selRowCoordinates.top+325 > jQuery(window).height()) {
                    selRowCoordinates.top=selRowCoordinates.top-325;
                }
                dialog.offset(selRowCoordinates);
            }
        }, //Default settings for delete
        { multipleSearch:true,
            Find: sFindButtonCaption,
            Reset: sResetButtonCaption,
            width:600,
            caption: sSearchTitle,
            odata : [ sOperator1, sOperator2, sOperator3, sOperator4, sOperator5, sOperator6, sOperator7, sOperator8, sOperator9, sOperator10, sOperator11, sOperator12, sOperator13, sOperator14 ],
            groupOps: [    { op: "AND", text: sOptionAnd },    { op: "OR",  text: sOptionOr }    ],
            
            closeAfterSearch: true,
            closeAfterReset: true
        }, //Default settings for search
        {closeAfterAdd:true}
    );


});

function checkboxFormatter(cellvalue, options) {
    cellvalue = cellvalue + "";
    var bchk = cellvalue.toLowerCase() == 'true' ? " checked=\"checked\"" : "";
    return "<input type='checkbox' name='visible_"+options.rowId+"' id='visible_"+options.rowId+"' onclick=\"ajaxSave('" + options.rowId + "');\" " + bchk + " value='" + cellvalue + "' />";
}

function ajaxSave(rowid) {
    var state;

    if($('#visible_'+rowid).is(':checked') == true)
    {
        state = "TRUE";
    }
    else
    {
        state = "FALSE";
    }
    $.post(editAttributeUrl, {
        id: rowid,
        visible: state,
        oper : 'edit'
        },
        function (data) {
            $("p#flashmessagetext").html(data);
            $("#flashinfo").css("display", "");
            $("#flashinfo").css("opacity", 0);
            $("#flashinfo").animate({opacity: 1.0}, 1500).fadeOut("slow");
        }
    );
}