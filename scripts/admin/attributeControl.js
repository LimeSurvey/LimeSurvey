$(document).ready(function() {
    var CM = [
        {name: 'actions', width: 75, align: 'center', fixed: true, sortable: false, resize: false, formatter: 'actions', search: false},
        {name: 'attribute_name', index: 'attribute_name', width: 250, align:"center", editable: true, editrules: {"required":true}},
        {name: 'attribute_type', index: 'attribute_type', width: 250, align:"center", editable: true, edittype:"select", editoptions:{value:attributeTypeSelections}, stype: 'select', searchoptions: {sopt: ['eq', 'ne'], value:attributeTypeSearch}},
        {name: 'visible', index: 'visible', width: 250, align: 'center', editable: true, formatter: checkboxFormatter, edittype: 'checkbox', edittype: "checkbox", editoptions: {value: "TRUE"}, stype: 'select', searchoptions: {sopt: ['eq', 'ne'], value: "TRUE:Yes;FALSE:No"}}
    ];

    $("#flashinfo").css("opacity", 0); //Make sure the flash message doesn't display in IE

    jQuery("#attributeControl").jqGrid({
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
        rowList: [25,50,100,250,500,1000,5000],
        multiselect: true,
        pager: "#pager",
    });

    jQuery.extend($.fn.fmatter , {
        rowactions : function(rid,gid,act, pos) {
            var delOptions = {
                caption: deleteCaption,
                msg: deleteMsg,
                reloadAfterSubmit: true,
                width: 400
            };
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
    });

    jQuery('#attributeControl').jqGrid('navGrid', '#pager',
                                       { add:true,
                                         edit:false,
                                         del:true},
                                       {}, //Default settings for edit
                                       { addCaption: addCaption,
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
                                            width: 500
                                           }, //Default settings for delete
                                       { multipleSearch:true,
                                         width:600,
                                         closeAfterSearch: true,
                                         closeAfterReset: true}, //Default settings for search
                                       {closeAfterAdd:true}
                                      );

});

function checkboxFormatter(cellvalue, options) {
    cellvalue = cellvalue + "";
    var bchk = cellvalue.toLowerCase() == 'true' ? " checked=\"checked\"" : "";
    return "<input type='checkbox' name='visible_"+options.rowId+"' id='visible_"+options.rowId+"' onclick=\"ajaxSave('" + options.rowId + "');\" " + bchk + " value='" + cellvalue + "' />"
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