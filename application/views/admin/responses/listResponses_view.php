<script type='text/javascript'>
    var strdeleteconfirm='<?php eT('Do you really want to delete this response?', 'js'); ?>';
    var strDeleteAllConfirm='<?php eT('Do you really want to delete all marked responses?', 'js'); ?>';
    var noFilesSelectedForDeletion = '<?php eT('Please select at least one file for deletion', 'js'); ?>';
    var noFilesSelectedForDnld = '<?php eT('Please select at least one file for download', 'js'); ?>';
</script>

<br />
<script type='text/javascript'>
    var getuserurl = '<?php echo $this->createUrl('admin/survey/ajaxgetusers'); ?>';
    var ownerediturl = '<?php echo $this->createUrl('admin/survey/ajaxowneredit'); ?>';
    var delmsg ='<?php eT("Are you sure you want to delete these surveys?",'js');?>';
    var sCaption ='<?php eT("Survey Respones",'js');?>';
    var sSelectColumns ='<?php eT("Select columns",'js');?>';
    var sRecordText = '<?php eT("View {0} - {1} of {2}",'js');?>';
    var sPageText = '<?php eT("Page {0} of {1}",'js');?>';
    var sLoadText = '<?php eT("Loading...",'js');?>';
    var sDelTitle = '<?php eT("Delete selected response(s)",'js');?>';
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
    var sFind= '<?php eT("Filter",'js');?>';
    var sReset= '<?php eT("Reset",'js');?>';
    var sSelectColumns= '<?php eT("Select columns",'js');?>';
    var sSubmit= '<?php eT("Save",'js');?>';

    var sCancel = '<?php eT("Cancel",'js');?>';
    var sSearchTitle ='<?php eT("Filter responses",'js');?>';
    var sRefreshTitle ='<?php eT("Reload responses list",'js');?>';
    var delBtnCaption ='<?php eT("Save",'js');?>';
    var sEmptyRecords ='<?php eT("There are currently no responses.",'js');?>';
    var jsonUrl = "<?php echo Yii::app()->getController()->createUrl('/admin/responses/getResponses_json/surveyid/'.$surveyid); ?>";
    //var sConfirmationExpireMessage='<?php eT("Are you sure you want to expire these surveys?",'js');?>';
    //var sConfirmationArchiveMessage='<?php eT("This function creates a ZIP archive of several survey archives and can take some time - please be patient! Do you want to continue?",'js');?>';
    // var editUrl = "<?php echo $this->createUrl('/admin/survey/editSurvey_json'); ?>";
    var colNames = <?php echo $column_names_txt; ?>;
    var colModels = <?php echo $column_model_txt; ?>;


</script>
<br/>

<table id="displayresponses"></table> <div id="pager"></div>

<select id='gs_completed_select' style='display: none'>
    <option value=''><?php eT("Any") ?></option>
    <option value='Y'><?php eT("Yes") ?></option>
    <option value='N'><?php eT("No") ?></option>
</select>

<div id='gs_no_filter'>&nbsp;</div>

<br />

