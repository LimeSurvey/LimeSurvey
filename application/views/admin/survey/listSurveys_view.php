
<script type='text/javascript'>
    var getuserurl = '<?php echo $this->createUrl('admin/survey/sa/ajaxgetusers'); ?>';
    var ownerediturl = '<?php echo $this->createUrl('admin/survey/sa/ajaxowneredit'); ?>';
    var delmsg ='<?php $clang->eT("Are you sure you want to delete these surveys?",'js');?>';
    var sWarningMsg = "<?php $clang->eT("Warning", 'js') ?>";
    var sCaption ='<?php $clang->eT("Surveys",'js');?>';
    var sSelectColumns ='<?php $clang->eT("Select columns",'js');?>';
    var sRecordText = '<?php $clang->eT("View {0} - {1} of {2}",'js');?>';
    var sPageText = '<?php $clang->eT("Page {0} of {1}",'js');?>';
    var sSelectRowMsg = "<?php $clang->eT("Select at least one survey.", 'js') ?>";
    var sLoadText = '<?php $clang->eT("Loading...",'js');?>';
    var sDelTitle = '<?php $clang->eT("Delete selected survey(s)",'js');?>';
    var sDelCaption = '<?php $clang->eT("Delete",'js');?>';
    var sSearchCaption = '<?php $clang->eT("Filter...",'js');?>';
    var sOperator1= '<?php $clang->eT("equal",'js');?>';
    var sOperator2= '<?php $clang->eT("not equal",'js');?>';
    var sOperator3= '<?php $clang->eT("less",'js');?>';
    var sOperator4= '<?php $clang->eT("less or equal",'js');?>';
    var sOperator5= '<?php $clang->eT("greater",'js');?>';
    var sOperator6= '<?php $clang->eT("greater or equal",'js');?>';
    var sOperator7= '<?php $clang->eT("begins with",'js');?>';
    var sOperator8= '<?php $clang->eT("does not begin with",'js');?>';
    var sOperator9= '<?php $clang->eT("is in",'js');?>';
    var sOperator10= '<?php $clang->eT("is not in",'js');?>';
    var sOperator11= '<?php $clang->eT("ends with",'js');?>';
    var sOperator12= '<?php $clang->eT("does not end with",'js');?>';
    var sOperator13= '<?php $clang->eT("contains",'js');?>';
    var sOperator14= '<?php $clang->eT("does not contain",'js');?>';
    var sOperator15= '<?php $clang->eT("is null",'js');?>';
    var sOperator16= '<?php $clang->eT("is not null",'js');?>';
    var sFind= '<?php $clang->eT("Filter",'js');?>';
    var sReset= '<?php $clang->eT("Reset",'js');?>';
    var sSelectColumns= '<?php $clang->eT("Select columns",'js');?>';                          
    var sSubmit= '<?php $clang->eT("Save",'js');?>';
    
    var sCancel = '<?php $clang->eT("Cancel",'js');?>';
    var sSearchTitle ='<?php $clang->eT("Filter surveys",'js');?>';
    var sRefreshTitle ='<?php $clang->eT("Reload survey list",'js');?>';
    var delBtnCaption ='<?php $clang->eT("Save",'js');?>';
    var sEmptyRecords ='<?php $clang->eT("There are currently no surveys.",'js');?>';
    var sConfirmationExpireMessage='<?php $clang->eT("Are you sure you want to expire these surveys?",'js');?>';
    var sConfirmationArchiveMessage='<?php $clang->eT("This function creates a ZIP archive of several survey archives and can take some time - please be patient! Do you want to continue?",'js');?>';
    var jsonUrl = "<?php echo Yii::app()->getController()->createUrl('admin/survey/sa/getSurveys_json'); ?>";
    var editUrl = "<?php echo $this->createUrl('admin/survey/sa/editSurvey_json'); ?>";
    var colNames = ["<?php $clang->eT("Status") ?>","<?php $clang->eT("SID") ?>","<?php $clang->eT("Survey") ?>","<?php $clang->eT("Date created") ?>","<?php $clang->eT("Owner") ?>","<?php $clang->eT("Access") ?>","<?php $clang->eT("Anonymized responses") ?>","<?php $clang->eT("Full") ?>","<?php $clang->eT("Partial") ?>","<?php $clang->eT("Total") ?>","<?php $clang->eT("Tokens available") ?>","<?php $clang->eT("Response rate") ?>"];
    var colModels = [{ "name":"status", "index":"status", "width":25, "align":"center", "sorttype":"string", "sortable": true, "editable":false},
    { "name":"sid", "index":"sid", "sorttype":"int", "sortable": true, "width":15, "align":"center", "editable":false},
    { "name":"survey", "index":"survey", "sorttype":stripLinkSort, "sortable": true, "width":100, "align":"left", "editable":true},
    { "name":"date_created", "index":"date_created", "sorttype":"string", "sortable": true,"width":25, "align":"center", "editable":false},
    { "name":"owner", "index":"owner","align":"center","width":40, "sorttype":"string", "sortable": true, "editable":true},
    { "name":"access", "index":"access","align":"center","width":25,"sorttype":"string", "sortable": true, "editable":true, "edittype":"checkbox", "editoptions":{ "value":"Y:N"}},
    { "name":"anonymous", "index":"anonymous","align":"center", "sorttype":"string", "sortable": true,"width":25,"editable":true, "edittype":"checkbox", "editoptions":{ "value":"Y:N"}},
    { "name":"full", "index":"full","align":"center", "sorttype":"int", "sortable": true,"width":25,"editable":false},
    { "name":"partial", "index":"partial","align":"center", "sorttype":"int", "sortable": true,"width":25,"editable":false},
    { "name":"total", "index":"total","align":"center", "sorttype":"int", "sortable": true,"width":25,"editable":false},
    { "name":"available", "index":"available","align":"center", "sorttype":"int", "sortable": true,"width":25,"editable":false},
    { "name":"rate", "index":"rate","align":"center", "sorttype":"int", "sortable": true,"width":25,"editable":false}];
    function stripLinkSort(cell) {
        var cellText = $(cell).text().toLowerCase();
        return cellText;
    }
</script>
<table id="displaysurveys"></table> <div id="pager"></div>
<select id='gs_status_select' style='display: none'>
    <option value=''><?php $clang->eT("Any") ?></option>
    <option value='--a--'><?php $clang->eT("Expired") ?></option>
    <option value='--e--'><?php $clang->eT("Inactive") ?></option>
    <option value='--c--'><?php $clang->eT("Active") ?></option>
</select>
<select id='gs_access_select' style='display: none'>
    <option value=''><?php $clang->eT("Any") ?></option>
    <option><?php $clang->eT("Open") ?></option>
    <option><?php $clang->eT("Closed") ?></option>
</select>
<br />
