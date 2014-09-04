<script type='text/javascript'>
    var strdeleteconfirm='<?php $clang->eT('Do you really want to delete this response?', 'js'); ?>';
    var strDeleteAllConfirm='<?php $clang->eT('Do you really want to delete all marked responses?', 'js'); ?>';
    var noFilesSelectedForDeletion = '<?php $clang->eT('Please select at least one file for deletion', 'js'); ?>';
    var noFilesSelectedForDnld = '<?php $clang->eT('Please select at least one file for download', 'js'); ?>';
</script>

<br />
<script type='text/javascript'>
    var getuserurl = '<?php echo $this->createUrl('admin/survey/ajaxgetusers'); ?>';
    var ownerediturl = '<?php echo $this->createUrl('admin/survey/ajaxowneredit'); ?>';
    var delmsg ='<?php $clang->eT("Are you sure you want to delete these surveys?",'js');?>';
    var sCaption ='<?php $clang->eT("Survey Respones",'js');?>';
    var sSelectColumns ='<?php $clang->eT("Select columns",'js');?>';
    var sRecordText = '<?php $clang->eT("View {0} - {1} of {2}",'js');?>';
    var sPageText = '<?php $clang->eT("Page {0} of {1}",'js');?>';
    var sLoadText = '<?php $clang->eT("Loading...",'js');?>';
    var sDelTitle = '<?php $clang->eT("Delete selected response(s)",'js');?>';
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
    var sFind= '<?php $clang->eT("Filter",'js');?>';
    var sReset= '<?php $clang->eT("Reset",'js');?>';
    var sSelectColumns= '<?php $clang->eT("Select columns",'js');?>';
    var sSubmit= '<?php $clang->eT("Save",'js');?>';

    var sCancel = '<?php $clang->eT("Cancel",'js');?>';
    var sSearchTitle ='<?php $clang->eT("Filter responses",'js');?>';
    var sRefreshTitle ='<?php $clang->eT("Reload responses list",'js');?>';
    var delBtnCaption ='<?php $clang->eT("Save",'js');?>';
    var sEmptyRecords ='<?php $clang->eT("There are currently no responses.",'js');?>';
    var jsonUrl = "<?php echo Yii::app()->getController()->createUrl('/admin/responses/getResponses_json/surveyid/'.$surveyid); ?>";
    //var sConfirmationExpireMessage='<?php $clang->eT("Are you sure you want to expire these surveys?",'js');?>';
    //var sConfirmationArchiveMessage='<?php $clang->eT("This function creates a ZIP archive of several survey archives and can take some time - please be patient! Do you want to continue?",'js');?>';
    // var editUrl = "<?php echo $this->createUrl('/admin/survey/editSurvey_json'); ?>";
    var colNames = <?php echo $column_names_txt; ?>;
    var colModels = <?php echo $column_model_txt; ?>;


</script>
<br/>

<table id="displayresponses"></table> <div id="pager"></div>

<select id='gs_completed_select' style='display: none'>
    <option value=''><?php $clang->eT("Any") ?></option>
    <option value='Y'><?php $clang->eT("Yes") ?></option>
    <option value='N'><?php $clang->eT("No") ?></option>
</select>

<div id='gs_no_filter'>&nbsp;</div>

<br />

