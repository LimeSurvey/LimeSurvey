<!-- List all notifications -->

<div class="container-fluid welcome full-page-wrapper">
    <h3 class="pagetitle"><?php eT('Notifications'); ?></h3>
    <?php

$surveyGrid = $this->widget('bootstrap.widgets.TbGridView', array(
    'dataProvider' => $model->search(),
    'id' => 'notification-grid',
    'emptyText' => gT('No notifications found'),
    'itemsCssClass' =>'table-striped',
    'ajaxUpdate' => true,
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
