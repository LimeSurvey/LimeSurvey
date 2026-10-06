<div class="side-body">
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
            <div class="col-12 content-right">
        
                <?php echo CHtml::form(
                    ["admin/dataentry/sa/update"],
                    'post',
                    [
                        'name' => 'editresponse',
                        'id' => 'editresponse',
                        /**
                         * Validation is actually handled by the submit handler, but we need data-trigger-validation
                         * so that the SaveController doesn't show the spinner when the form is already invalid (from
                         * a previous submit attempt).
                         */
                        'data-trigger-validation' => 'true',
                    ]
                ); ?>
                   <table id='responsedetail' class="table" width='99%' align='center'>
