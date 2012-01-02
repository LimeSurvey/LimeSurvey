<br />
<script type='text/javascript'>
var getuserurl = '<?php echo $this->createUrl('admin/survey/ajaxgetusers'); ?>';
var ownerediturl = '<?php echo $this->createUrl('admin/survey/ajaxowneredit'); ?>';
var delmsg ='<?php $clang->eT("Are you sure you want to delete these surveys?",'js');?>';
var sConfirmationExpireMessage='<?php $clang->eT("Are you sure you want to expire these surveys?",'js');?>';
var sConfirmationArchiveMessage='<?php $clang->eT("This function creates a ZIP archive of several survey archives and can take some time - please be patient! Do you want to continue?",'js');?>';
var jsonUrl = "<?php echo Yii::app()->getController()->createUrl('admin/survey/getSurveys_json'); ?>";
var editUrl = "<?php echo $this->createUrl('admin/survey/surveyactions'); ?>";
var colNames = ["<?php $clang->eT("Status") ?>","<?php $clang->eT("SID") ?>","<?php $clang->eT("Survey") ?>","<?php $clang->eT("Date created") ?>","<?php $clang->eT("Owner") ?>","<?php $clang->eT("Access") ?>","<?php $clang->eT("Anonymized responses") ?>","<?php $clang->eT("Full") ?>","<?php $clang->eT("Partial") ?>","<?php $clang->eT("Total") ?>","<?php $clang->eT("Tokens avaliable") ?>","<?php $clang->eT("Response rate") ?>"];
var colModels = [{ "name":"status", "index":"status", "width":15, "align":"center", "sorttype":"string", "sortable": true, "editable":false},
{ "name":"sid", "index":"sid", "sorttype":"int", "sortable": true, "width":25, "align":"center", "editable":false},
{ "name":"survey", "index":"survey", "sorttype":"string", "sortable": true, "width":60, "align":"left", "editable":true},
{ "name":"date_created", "index":"date_created", "sorttype":"string", "sortable": true,"width":40, "align":"center", "editable":false},
{ "name":"owner", "index":"owner","align":"center","width":40, "sorttype":"string", "sortable": true, "editable":true},
{ "name":"access", "index":"access","align":"center","width":25,"sorttype":"string", "sortable": true, "editable":true, "edittype":"checkbox", "editoptions":{ "value":"Y:N"}},
{ "name":"anonymous", "index":"anonymous","align":"center", "sorttype":"string", "sortable": true,"width":25,"editable":true, "edittype":"checkbox", "editoptions":{ "value":"Y:N"}},
{ "name":"full", "index":"full","align":"center", "sorttype":"int", "sortable": true,"width":25,"editable":false},
{ "name":"partial", "index":"partial","align":"center", "sorttype":"int", "sortable": true,"width":25,"editable":false},
{ "name":"total", "index":"total","align":"center", "sorttype":"int", "sortable": true,"width":25,"editable":false},
{ "name":"available", "index":"available","align":"center", "sorttype":"int", "sortable": true,"width":25,"editable":false},
{ "name":"rate", "index":"rate","align":"center", "sorttype":"int", "sortable": true,"width":25,"editable":false}];
</script>
<br/>
<table id="displaysurveys"></table> <div id="pager"></div>
<br />
