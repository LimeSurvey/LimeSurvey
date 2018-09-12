<div class='side-body <?php echo getSideBodyClass(false); ?>'>
    <h3><?php echo sprintf(gT("View response ID %d"), $id); ?></h3>
        <div class="row">
            <div class="col-lg-12 content-right">
<?php echo CHtml::form(array("admin/responses/sa/browse/surveyid/{$surveyid}/"), 'post', array('id'=>'resulttableform')); ?>
    <input id='downloadfile' name='downloadfile' value='' type='hidden'>
    <input id='sid' name='sid' value='<?php echo $surveyid; ?>' type='hidden'>
    <input id='subaction' name='subaction' value='all' type='hidden'>
</form>

<table class='detailbrowsetable table table-striped'>
