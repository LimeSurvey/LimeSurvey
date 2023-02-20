<?php
// TODO: file seems to be unused
?>
<!-- List all notifications -->

<div class="container-fluid welcome full-page-wrapper">
    <div class="pagetitle h3"><?php eT('Notifications'); ?></div>
    <?php

$surveyGrid = $this->widget('application.extensions.admin.grid.CLSGridView', array(
    'dataProvider' => $model->search(),
    'id'           => 'notification-grid',
    'emptyText'    => gT('No notifications found'),
    'htmlOptions'  => ['class' => 'table-responsive grid-view-ls'],
    'ajaxUpdate'   => 'notification-grid',
    'columns'      => array(
        /*
        array(
            'id' => 'id'
        ),
         */
        array(
            'header' => 'ID',
            'name' => 'id'
        ),
        array(
            'header' => gT('Title'),
            'name' => 'title'
        ),
        array(
            'header' => gT('Message'),
            'name' => 'message'
        )
    )
));

    ?>
</div>
