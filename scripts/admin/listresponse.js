/*
* JavaScript functions for LimeSurvey response browse
*
*/

// @license magnet:?xt=urn:btih:cf05388f2679ee054f2beb29a391d25f4e673ac3&dn=gpl-2.0.txt  GNU/GPL License v2 or later


/**
 * Scroll the pager and the footer when scrolling horizontally
 */
$(document).ready(function(){
    $('#displayResponsesContainer').scroll(function(){
        $('#pager').css({
            'left': $(this).scrollLeft() ,
        });
    });
});

$(document).on("click","[data-delete]",function(event){
    event.preventDefault();
    var responseid=$(this).data("delete");
    var url=$(this).attr("href"); // Or replace responseid  by post if needed
    var buttons = {};
    buttons[sDelCaption] = function(){
        $.ajax({
            url : url,
            type : "POST"
        })
        .done(function() {
            jQuery("#displayresponses").delRowData(responseid);
        });
        $( this ).dialog( "close" );
    };
    buttons[sCancel] = function(){ $( this ).dialog( "close" ); };
    var dialog=$("<p>"+strdeleteconfirm+"</p>").dialog({
        modal:true,
        buttons: buttons
    });
});
$(function() {

    /* Launch jqgrid */

    jQuery("#displayresponses").jqGrid({
        altRows : true,
        recordtext : sRecordText,
        emptyrecords : sEmptyRecords,
        pgtext : sPageText,
        loadtext : sLoadText,
        align : "center",
        url : jsonUrl,
        editurl : jsonActionUrl,
        datatype : "json",
        mtype : "POST",
        colNames : colNames,
        colModel : colModels,
        toppager : false,
        height : "100%",
        //shrinkToFit : false,
        ignoreCase : true,
        rowNum : 10,
        editable : false,
        scrollOffset : 0,
        sortable : true,
        hidegrid : false,
        sortname : 'id',
        sortorder : 'desc',
        viewrecords : true,
        rowList : [ 10, 25, 50, 100, 250, 500, 1000 ],
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
        },
        beforeSelectRow: function(rowid, event) {
            /* deactivate row select on tools */
            if($(event.target).is("a") || $(event.target).closest("a").length )
                return false;
            return true;
        },
        gridComplete: function() {
            $("#displayresponses_cb").css("width","35px");
            $("#displayresponses tbody tr").children().first("td").css("width","35px");
            $("#displayresponses tbody tr td").css("text-align","center");
         }
    });

    /* Add navgrid */
    jQuery("#displayresponses").jqGrid( 'navGrid', '#pager',
        {
            add: false,
            edit: false,
            del: true,
            alertcap: sWarningMsg,
            alerttext: sSelectRowMsg,
            searchtitle : sSearchTitle,
            refreshtitle : sRefreshTitle,
            deltitle : sDelTitle,
            search: true,
            refresh: true,
            view: false,
            position: "left"
        },
        {}, // edit options
        {}, // add options
        {
            msg : strDeleteAllConfirm,
            bSubmit : sDelCaption,
            caption : sDelCaption,
            bCancel : sCancel,
            width : 700,
            afterShowForm: function($form) {
                var dialog = $form.closest('div.ui-jqdialog'),
                selRowId = jQuery("#displayresponses").jqGrid('getGridParam', 'selrow'),
                selRowCoordinates = $('#'+selRowId).offset();
                dialog.offset(selRowCoordinates);
                $(document).scrollTop(selRowCoordinates.top);
            },
        },
        { // Search options
            caption : sSearchCaption,
            Find : sFind,
            multipleSearch: true,
            Reset : sReset,
            width: 700
        }

    );
    $.jgrid.search.odata= [ sOperator1, sOperator2, sOperator3, sOperator4, sOperator5, sOperator6, sOperator7, sOperator8, sOperator9, sOperator10, sOperator11, sOperator12, sOperator13, sOperator14, sOperator15, sOperator16  ],

    /* quick search toolbar */
    jQuery("#displayresponses").jqGrid('filterToolbar', {
        searchOnEnter : false,
        defaultSearch : 'cn'
    });
    /* Column button */
    jQuery("#displayresponses").jqGrid(
        'navButtonAdd',
        '#pager',
        {
            buttonicon : "fa fa-columns",
            caption : "",
            title : sSelectColumns,
            onClickButton : function() {
                jQuery("#displayresponses").jqGrid(
                    'columnChooser',
                    {
                        caption : sSelectColumns,
                        bSubmit : sSubmit,
                        bCancel : sCancel,
                        done : function(perm) {
                            if (perm) {
                                this.jqGrid("remapColumns",perm,true);
                                var hidden = [];
                                $.each($("#displayresponses").getGridParam("colModel"),
                                    function(i,obj) {
                                        if(obj.hasOwnProperty('index') && obj.hidden){
                                            hidden.push(obj.index);
                                        }
                                });
                                $.post( jsonBaseUrl+"&sa=setHiddenColumns", { aHiddenFields: hidden.join("|") } );
                            }
                        }
                });
            }
        }
    );
    if(typeof sDownLoad!=="undefined")
    {
        jQuery("#displayresponses").navButtonAdd('#pager',{
            //caption:sDownLoad, // Remove it ? no it's more clear ;)
            caption:'',
            title:sDownLoad, // Todo dynamically update download selected , download all
            buttonicon:"fa fa-download",
            onClickButton: function(){
                selectedlist=jQuery("#displayresponses").getGridParam('selarrrow').join(",");//  Or send like an array ?
                if(selectedlist!="")
                {
                    sendPost(jsonActionUrl,null,["oper","id"],["downloadzip",selectedlist]);
                }
                else
                {
                    if(confirm(sConfirmationArchiveMessage))
                    {
                        sendPost(jsonActionUrl,null,["oper"],["downloadzip"]);
                    }
                }
            },
            position:"last",
        });
    }

    /* Grid resize : only heigth ? */
    jQuery("#displayresponses").jqGrid('gridResize', {
        handles: "n, s",
        minHeight : 100
    });
});
