<div class="side-body <?php echo getSideBodyClass(false); ?>">

<?php 
    $title = $subaction == "edit" ? sprintf(gT("Data entry, editing response (ID %s)"), $id) : sprintf(gT("Viewing response (ID %s)"), $id);
    $this->widget('ext.admin.survey.PageTitle.PageTitle', array(
            'title' => $title,
            'model' => $survey,
    ));
?>
        <div class="row">
            <div class="col-12 content-right">
        
                <?php echo CHtml::form(array("admin/dataentry/sa/update"), 'post', array('name'=>'editresponse', 'id'=>'editresponse'));?>
                   <table id='responsedetail' class="table" width='99%' align='center'>
