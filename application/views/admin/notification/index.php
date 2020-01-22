<!-- List all notifications -->

<div class="container-fluid welcome full-page-wrapper">
    <div class="pagetitle h3"><?php eT('Notifications'); ?></div>
    <?php

$surveyGrid = $this->widget('bootstrap.widgets.TbGridView', array(
    'dataProvider' => $model->search(),
    'id' => 'notification-grid',
    'emptyText' => gT('No notifications found'),
    'itemsCssClass' =>'table-striped',
    'ajaxUpdate' => 'notification-grid',
    'columns' => array(
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
