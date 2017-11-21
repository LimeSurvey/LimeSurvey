<div class="side-body <?php echo getSideBodyClass(false); ?>">
    <h3>
    <?php eT("Data entry"); ?> -
    <?php
        if ($subaction == "edit") {
                echo sprintf(gT("Editing response (ID %s)"), $id);
        } else {
                echo sprintf(gT("Viewing response (ID %s)"), $id);
        }
    ?>
    </h3>
        <div class="row">
            <div class="col-lg-12 content-right">
        
                <?php echo CHtml::form(array("admin/dataentry/sa/update"), 'post', array('name'=>'editresponse', 'id'=>'editresponse'));?>
                   <table id='responsedetail' class="table" width='99%' align='center'>
