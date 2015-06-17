
<script type='text/javascript'>
    var getuserurl = '<?php echo $this->createUrl('admin/survey/sa/ajaxgetusers'); ?>';
    var ownerediturl = '<?php echo $this->createUrl('admin/survey/sa/ajaxowneredit'); ?>';
    var delmsg ='<?php eT("Are you sure you want to delete these surveys?",'js');?>';
    var sWarningMsg = "<?php eT("Warning", 'js') ?>";
    var sCaption ='<?php eT("Surveys",'js');?>';
    var sSelectColumns ='<?php eT("Select columns",'js');?>';
    var sRecordText = '<?php eT("View {0} - {1} of {2}",'js');?>';
    var sPageText = '<?php eT("Page {0} of {1}",'js');?>';
    var sSelectRowMsg = "<?php eT("Select at least one survey.", 'js') ?>";
    var sLoadText = '<?php eT("Loading...",'js');?>';
    var sDelTitle = '<?php eT("Delete selected survey(s)",'js');?>';
    var sDelCaption = '<?php eT("Delete",'js');?>';
    var sSearchCaption = '<?php eT("Filter...",'js');?>';
    var sOperator1= '<?php eT("equal",'js');?>';
    var sOperator2= '<?php eT("not equal",'js');?>';
    var sOperator3= '<?php eT("less",'js');?>';
    var sOperator4= '<?php eT("less or equal",'js');?>';
    var sOperator5= '<?php eT("greater",'js');?>';
    var sOperator6= '<?php eT("greater or equal",'js');?>';
    var sOperator7= '<?php eT("begins with",'js');?>';
    var sOperator8= '<?php eT("does not begin with",'js');?>';
    var sOperator9= '<?php eT("is in",'js');?>';
    var sOperator10= '<?php eT("is not in",'js');?>';
    var sOperator11= '<?php eT("ends with",'js');?>';
    var sOperator12= '<?php eT("does not end with",'js');?>';
    var sOperator13= '<?php eT("contains",'js');?>';
    var sOperator14= '<?php eT("does not contain",'js');?>';
    var sOperator15= '<?php eT("is null",'js');?>';
    var sOperator16= '<?php eT("is not null",'js');?>';
    var sFind= '<?php eT("Filter",'js');?>';
    var sReset= '<?php eT("Reset",'js');?>';
    var sSelectColumns= '<?php eT("Select columns",'js');?>';
    var sSubmit= '<?php eT("Save",'js');?>';

    var sCancel = '<?php eT("Cancel",'js');?>';
    var sSearchTitle ='<?php eT("Filter surveys",'js');?>';
    var sRefreshTitle ='<?php eT("Reload survey list",'js');?>';
    var delBtnCaption ='<?php eT("Save",'js');?>';
    var sEmptyRecords ='<?php eT("There are currently no surveys.",'js');?>';
    var sConfirmationExpireMessage='<?php eT("Are you sure you want to expire these surveys?",'js');?>';
    var sConfirmationArchiveMessage='<?php eT("This function creates a ZIP archive of several survey archives and can take some time - please be patient! Do you want to continue?",'js');?>';
    var jsonUrl = "<?php echo Yii::app()->getController()->createUrl('admin/survey/sa/getSurveys_json'); ?>";
    var editUrl = "<?php echo $this->createUrl('admin/survey/sa/editSurvey_json'); ?>";
    var colNames = ["<?php eT("Status") ?>","<?php eT("SID") ?>","<?php eT("Survey") ?>","<?php eT("Date created") ?>","<?php eT("Owner") ?>","<?php eT("Access") ?>","<?php eT("Anonymized responses") ?>","<?php eT("Full") ?>","<?php eT("Partial") ?>","<?php eT("Total") ?>","<?php eT("Tokens available") ?>","<?php eT("Response rate") ?>"];
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
<div id="surveylist-wrapper" class="grid-wrapper">
    <table id="displaysurveys"></table> <div id="pager"></div>
</div>
<select id='gs_status_select' style='display: none'>
    <option value=''><?php eT("Any") ?></option>
    <option value='--a--'><?php eT("Expired") ?></option>
    <option value='--e--'><?php eT("Inactive") ?></option>
    <option value='--c--'><?php eT("Active") ?></option>
</select>
<select id='gs_access_select' style='display: none'>
    <option value=''><?php eT("Any") ?></option>
    <option><?php eT("Open") ?></option>
    <option><?php eT("Closed") ?></option>
</select>
<br />
