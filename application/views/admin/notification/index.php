<?php
// TODO: file seems to be unused
?>
<!-- List all notifications -->

<div class="welcome full-page-wrapper">
    <div class="pagetitle h3"><?php eT('Notifications'); ?></div>
    <?php
    $surveyGrid = $this->widget('application.extensions.admin.grid.CLSGridView', [
        'dataProvider' => $model->search(),
        'id'           => 'notification-grid',
        'emptyText'    => gT('No notifications found'),
        'htmlOptions'  => ['class' => 'table-responsive grid-view-ls'],
        'ajaxUpdate'   => 'notification-grid',
        'columns'      => [
            /*
            array(
                'id' => 'id'
            ),
             */
            [
                'header' => 'ID',
                'name'   => 'id'
            ],
            [
                'header' => gT('Title'),
                'name'   => 'title'
            ],
            [
                'header' => gT('Message'),
                'name'   => 'message'
            ]
        ]
    ]);

    ?>
</div>
