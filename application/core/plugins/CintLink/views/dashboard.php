<!-- Cint widget button (not visible from global dashboard) -->
<?php if (empty($surveyId)): ?>
    <p class='alert alert-info'>
        <span class='fa fa-info-circle'></span>
        &nbsp;
        <?php echo $plugin->gT('To order participants, please go to the survey specific CintLink view.'); ?>
    </p>
<?php elseif (!empty($survey) && $survey->active != 'Y'):  ?>
    <p class='alert alert-info'>
        <span class='fa fa-info-circle'></span>
        &nbsp;
        <?php echo $plugin->gT('Please make sure the survey is activated before paying your Cint order.'); ?>
    </p>
<?php endif; ?>

<?php if (!empty($surveyId)): // Widget is not visible on global dashboard ?>
    <button class='btn btn-default' onclick='LS.plugin.cintlink.showWidget();'>
        <span class='fa fa-bars'></span>
        &nbsp;
        <?php echo $plugin->gT('Choose participants'); ?>
    </button>
    <p class='help-block'><?php echo $plugin->gT('Use the Cint widget to buy participants'); ?></p>
<?php endif; ?>

<h4>Orders</h4>
<div id='cintlink-gridview'>
<?php 
    $widget = $this->widget('bootstrap.widgets.TbGridView', array(
        'dataProvider' => $model->search($surveyId),
        'id' => 'url',
        'itemsCssClass' =>'table-striped',
        'emptyText' => $plugin->gT('No order made yet'),
        'afterAjaxUpdate' => 'doToolTip',
        'ajaxUpdate' => true,
        'columns' => array(
            array(
                'name' => 'url',
                'header' => 'ID',
                'value' => 'substr($data->url, 47)'
            ),
            array(
                'name' => 'created',
                'header' => $plugin->gT('Created'),
                'value' => '$data->formattedCreatedDate'
            ),
            array(
                'name' => 'sid',
                'header' => $plugin->gT('Survey ID'),
                'value' => '$data->surveyIdLink',
                'type' => 'raw'
            ),
            array(
                'name' => 'ordered_by',
                'header' => $plugin->gT('Ordered by'),
                'value' => '$data->user->full_name'
            ),
            array(
                'name' => '__completedCheck',
                'header' => '',
                'value' => '$data->completedCheck',
                'type' => 'raw'
            ),
            array(
                'name' => 'status',
                'header' => $plugin->gT('Status'),
                'value' => '$data->styledStatus',
                'type' => 'raw'
            ),
            array(
                'name' => 'buttons',
                'header' => '',
                'value' => '$data->buttons',
                'type' => 'raw'
            )
        )
    ));
?>
</div>

<!-- Hack to not publish jQuery twice -->
<?php $plugin->renderClientScripts(); ?>
