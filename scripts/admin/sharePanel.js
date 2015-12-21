/*
This functions checks the current checkbox value using the name we have supplied in the each checkbox
Please Note: we are not using the normal jqgrid cell edit here.
*/
function ajaxSave(rowid,shareduid) {
    var state;
    var cbId="#check_"+rowid+"_"+shareduid;

    if($(cbId).is(':checked'))
    {
        state="true";
    }
    else
    {
        state ="false";
    }
    $.post(editurlshare, { participant_id: rowid, can_edit: state, shared_uid: shareduid } );
}

$(document).ready(function() {
    //script for jqGrid

    if(isadmin == 1)
    {
        var colNames = '["'+firstNameText+'", "'+lastNameText+'", "'+emailText+'", "'+sharedWithText+'", "'+sharedUidText+'", "'+ownerText+'", "'+dateAddedText+'", "'+canEditText+'"]';
        var CM = [ {name:'firstname',index:'firstname', width:60,align:"center",editable:false},
            {name:'lastname',index:'lastname', width:60,align:"center",editable:false},
            {name:'email',index:'email', width:130,align:"center",editable:false},
            {name:'sharedwith',index:'sharedwith', width:130,align:"center",editable:false,width:40},
            {name:'shareduid',index:'shareduid',hidden: true,editable:false},
            {name:'Owner',index:'Owner', width:130,align:"center",editable:false,width:40},
            {name:'dateadded',index:'dateadded', width:80,align:"center",editable:false},
            {name: 'canedit', width: 40, align: 'center', editable: true, formatter: checkboxFormatter, edittype: 'checkbox'}];
    }
    else
    {
        var colNames = '["'+firstNameText+'", "'+lastNameText+'", "'+emailText+'", "'+sharedWithText+'", "'+sharedUidText+'", "'+dateAddedText+'", "'+canEditText+'"]';
        var CM = [ {name:'firstname',index:'firstname', width:60,align:"center",editable:false},
            {name:'lastname',index:'lastname', width:60,align:"center",editable:false},
            {name:'email',index:'email', width:130,align:"center",editable:false},
            {name:'sharedwith',index:'sharedwith', width:130,align:"center",editable:false,width:40},
            {name:'shareduid',index:'shareduid',hidden: true,editable:false},
            {name:'dateadded',index:'dateadded', width:80,align:"center",editable:false},
            {name: 'canedit', width: 40, align: 'center', editable: true, formatter: checkboxFormatter, edittype: 'checkbox'}];
    }

    var lastSel;
    jQuery("#sharePanel").jqGrid({
        direction: $('html').attr('dir'),
        loadtext : sLoadText,
        align:"center",
        url: shareinfoUrl,
        editurl : editurlshare,
        datatype: "json",
        mtype: "post",
        editable : true,
        colNames: jQuery.parseJSON(colNames),
        colModel: CM,
        height: "100%",
        width: "100%",
        rowNum: 25,
        scrollOffset:0,
        autowidth: true,
        sortname : "firstname",
        rowList: [25,50,100,250,500,1000,2500,5000],
        multiselect: true,
        loadonce: true,
        pager: "#pager",
        pgtext: pageViewTxt

    });
    function checkboxFormatter(cellvalue, options, rowObject) {
        cellvalue = cellvalue + "";
        cellvalue = cellvalue.toLowerCase();
        var bchk = cellvalue.search(/(false|0|no|off|n)/i) < 0 ? " checked=\"checked\"" : "";
        /* Adding a name and the shared user id to the checkbox here with the format check_rowId_shared_uid
        * so that in the ajax save function we get the currect selected value instead of the one
        * provided by custom formatter so that the user can toggle on a single checkbox and still
        * the values are stored at the database. The only thing that can be unique accross the
        * share panel is the row id and share user id
        * */
        return "<input type='checkbox' name='check_"+options.rowId+"_"+rowObject[4]+"' id='check_"+options.rowId+"_"+rowObject[4]+"' onclick=\"ajaxSave('" + options.rowId + "','" + rowObject[4] +"');\" " + bchk + " value='" + cellvalue + "' offval='no' />";
    }
    jQuery('#sharePanel').jqGrid('navGrid',
        '#pager',
        {add:false,
            del:true,
            edit:false,
            alertcap: sWarningMsg,
            alerttext: sSelectRowMsg,
            deltitle: sDeleteShares, 
            searchtitle: sSearchMsg,
            refreshtitle: refreshListTxt},
        {closeAfterDel: true,
            reloadAfterSubmit: true
        },{},{
            caption: sDeleteShares,
            msg: sDeleteMsg,
            width: 500,
            afterShowForm: function($form) {
                /* This code sets the position of the delete dialog to just below the last selected item */
                /* Unless this would put the delete dialog off the page, in which case it will be pushed up a bit */
                var dialog = $form.closest('div.ui-jqdialog'),
                selRowId = jQuery("#sharePanel").jqGrid('getGridParam', 'selrow'),
                selRowCoordinates = $('#'+selRowId).offset();
                selRowCoordinates.top=selRowCoordinates.top+25;
                selRowCoordinates.left=50;
                if(selRowCoordinates.top+325 > jQuery(window).height()) {
                    selRowCoordinates.top=selRowCoordinates.top-325;
                }
                dialog.offset(selRowCoordinates);
            }
            
        },
        {   Find: sFindButtonCaption,
            Reset: sResetButtonCaption,
            width:600,
            caption: sSearchTitle,
            odata : [ sOperator1, sOperator2, sOperator3, sOperator4, sOperator5, sOperator6, sOperator7, sOperator8, sOperator9, sOperator10, sOperator11, sOperator12, sOperator13, sOperator14 ],
            groupOps: [    { op: "AND", text: sOptionAnd },    { op: "OR",  text: sOptionOr }    ],

            closeAfterSearch: true,
            closeAfterReset: true
        } //Default settings for search                             
    );
    $.extend(jQuery.jgrid.edit,{closeAfterAdd: true,reloadAfterSubmit: true,closeOnEspace:true});



});