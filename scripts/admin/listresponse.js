//$Id: listsurvey.js 9692 2012-12-10 21:31:10Z pradesh $
//V1.1 Pradesh - Copied from listsurvey.js

/*
* Scroll the pager and the footer when scrolling horizontally
* Maybe for token table too
*/
$(window).scroll(function(){
    $('.ui-jqgrid-toppager').css({
        'left': $(this).scrollLeft()
    });
    $('.ui-jqgrid-pager').css({
        'left': $(this).scrollLeft()
    });
});

$(function() {
    /* We don't use it actually ? */
    $("#addbutton").click(function() {
                        id = 2;
                        html = "<tr name='joincondition_"
                                + id
                                + "' id='joincondition_"
                                + id
                                + "'><td><select name='join_"
                                + id
                                + "' id='join_"
                                + id
                                + "'><option value='and'>AND</option><option value='or'>OR</option></td><td></td></tr><tr><td><select name='field_"
                                + id
                                + "' id='field_"
                                + id
                                + "'>\n\
            <option value='completed'>"
                                + colNames[2]
                                + "</option>\n\
        <option value='id'>"
                                + colNames[3]
                                + "</option>\n\
        <option value='startlanguage'>"
                                + colNames[4]
                                + "</option>\n\
        </select>\n\</td>\n\<td>\n\
        <select name='condition_"
                                + id
                                + "' id='condition_"
                                + id
                                + "'>\n\
        <option value='equal'>"
                                + searchtypes[0]
                                + "</option>\n\
        <option value='contains'>"
                                + searchtypes[1]
                                + "</option>\n\
        <option value='notequal'>"
                                + searchtypes[2]
                                + "</option>\n\
        <option value='notcontains'>"
                                + searchtypes[3]
                                + "</option>\n\
        <option value='greaterthan'>"
                                + searchtypes[4]
                                + "</option>\n\
        <option value='lessthan'>"
                                + searchtypes[5]
                                + "</option>\n\
        </select></td>\n\<td><input type='text' id='conditiontext_"
                                + id
                                + "' style='margin-left:10px;' /></td>\n\
        <td><img src="
                                + minusbutton
                                + " onClick= $(this).parent().parent().remove();$('#joincondition_"
                                + id
                                + "').remove() id='removebutton'"
                                + id
                                + ">\n\
        <img src="
                                + addbutton
                                + " id='addbutton'  onclick='addcondition();' style='margin-bottom:4px'></td></tr><tr></tr>";
        $('#searchtable tr:last').after(html);
    });
    /* For advanced search button ? */
    var searchconditions = {};
    var field;
    $('#searchbutton').click(function() {
        // Must be done
    });

    var lastSel, lastSel2; /* not used */
    function returnColModel() {
        if ($.cookie("detailedresponsecolumns")) {
            hidden = $.cookie("detailedresponsecolumns").split(
                    '|');
            for (i = 0; i < hidden.length; i++)
                if (hidden[i] != "false")
                    colModels[i]['hidden'] = true;
        }
        return colModels;
    }
    /* Launch jqgrid */
    jQuery("#displayresponses").jqGrid({
        recordtext : sRecordText,
        emptyrecords : sEmptyRecords,
        pgtext : sPageText,
        loadtext : sLoadText,
        align : "center",
        url : jsonUrl,
        // editurl : editUrl,
        datatype : "json",
        mtype : "POST",
        colNames : colNames,
        colModel : returnColModel(),
        toppager : true,
        height : "100%",
        shrinkToFit : false,
        ignoreCase : true,
        rowNum : 25,
        editable : false,
        scrollOffset : 0,
        sortable : true,
        hidegrid : false,
        sortname : 'id',
        sortorder : 'asc',
        viewrecords : true,
        rowList : [ 25, 50, 100, 250, 500, 1000 ],
        multiselect : true,
        loadonce : false, // use ajax request
        pager : "#pager",
        caption : sCaption,
        beforeRequest: function(){
            /* activate tooltip on header */
            for (i = 0; i < colModels.length; i++) {
                var col=i+1;
                $("tr.ui-jqgrid-labels th:eq("+col+") .questiontext").attr('title',colModels[i]['title']);
            }
            $(".ui-jqgrid-labels").tooltip();
        },
        loadComplete: function(){
            /* activate tooltip on answers : must be limited ? */
            $("#displayresponses").tooltip({ tooltipClass: "tooltip-text" });
        }
    });
    /* Add navgrid */
    jQuery("#displayresponses").jqGrid(
        'navGrid',
        '#pager',
        {
            deltitle : sDelTitle,
            searchtitle : sSearchTitle,
            refreshtitle : sRefreshTitle,
            add : false,
            del : false,
            edit : false,
            refresh : true,
            search : true
        },
        {},
        {},
        {
            msg : delmsg,
            bSubmit : sDelCaption,
            caption : sDelCaption,
            bCancel : sCancel,
            width : 700
        },
        {
            caption : sSearchCaption,
            Find : sFind,
            odata : [ sOperator1, sOperator2, sOperator3,
                    sOperator4, sOperator5, sOperator6,
                    sOperator7, sOperator8, sOperator9,
                    sOperator10, sOperator11, sOperator12,
                    sOperator13, sOperator14 ],
            Reset : sReset
        }
    );
    /* quick search toolbar */
    jQuery("#displayresponses").jqGrid('filterToolbar', {
        searchOnEnter : false,
        defaultSearch : 'cn'
    });
    /* navButton ? */
    jQuery("#displayresponses").jqGrid(
        'navButtonAdd',
        '#pager',
        {
            buttonicon : "ui-icon-calculator",
            caption : "",
            title : sSelectColumns,
            onClickButton : function() {
                jQuery("#displayresponses").jqGrid(
                                'columnChooser',
                                {
                                    caption : sSelectColumns,
                                    bSubmit : sSubmit,
                                    bCancel : sCancel,
                                    done : function(
                                            perm) {
                                        if (perm) {
                                            this
                                                    .jqGrid(
                                                            "remapColumns",
                                                            perm,
                                                            true);
                                            var hidden = [];
                                            $
                                                    .each(
                                                            $(
                                                                    "#displayresponses")
                                                                    .getGridParam(
                                                                            "colModel"),
                                                            function(
                                                                    key,
                                                                    val) {
                                                                hidden
                                                                        .push(val['hidden']);
                                                            });
                                            hidden
                                                    .splice(
                                                            0,
                                                            1);
                                            $
                                                    .cookie(
                                                            "detailedresponsecolumns",
                                                            hidden
                                                                    .join("|"));
                                        }
                                    }
                                });
            }
        }
    );
    /* Grid resize : needed ? */
    jQuery("#displayresponses").jqGrid('gridResize', {
        minWidth : 1400,
        minHeight : 100
    });

    /* Trigger the inline search when the access list changes */
    $(document).on('change','#gs_completed_select',function() {
        $("#gs_completed").val($('#gs_completed_select').val());
        $("#gs_completed").trigger("keydown");
    });

    /* Change the text search above "Status" icons to a dropdown */
    var parentDiv = $('#gs_completed').parent();
    parentDiv.prepend($('#gs_completed_select'));
    $('#gs_completed_select').css("display", "");
    $('#gs_completed').css("display", "none");

    /* Disable search on the action column */
    var parentDiv = $('#gs_actions').parent();
    parentDiv.prepend($('#gs_no_filter'));
    $('#gs_no_filter').css("display", "");
    $('#gs_Actions').css("display", "none");

});
