<!-- Warning if user has a token table created -->
<?php if (isset($hasTokenTable) && $hasTokenTable): ?>
    <p class='alert alert-warning'>
        <span class='fa fa-exclamation-circle'></span>
        &nbsp;
        <?php echo $plugin->gT('Please delete your token participant table to be able to use Cint Link.'); ?>
    </p>
<?php endif; ?>

<!-- Some info -->
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

<!-- Cint widget button (not visible from global dashboard) -->
<?php if (!empty($surveyId)): // Widget is not visible on global dashboard ?>
    <button
        class='btn btn-default'
        onclick='LS.plugin.cintlink.showWidget();'
        <?php if ($hasTokenTable): ?> disabled='disabled' <?php endif; ?>
    >
        <span class='fa fa-bars'></span>
        &nbsp;
        <?php echo $plugin->gT('Choose participants'); ?>
    </button>
    <p class='help-block'><?php echo $plugin->gT('Use the Cint widget to buy participants'); ?></p>
<?php endif; ?>

<h4>Orders</h4>
<div id='cintlink-gridview'>
<?php 
    $columns = array();
    $columns[] = array(
        'name' => 'url',
        'header' => 'ID',
        'value' => 'substr($data->url, 47)'
    );
    $columns[] = array(
        'name' => 'created',
        'header' => $plugin->gT('Created'),
        'value' => '$data->formattedCreatedDate'
    );

    // Only needed on global dashboard
    if (empty($surveyId))
    {
        $columns[] = array(
            'name' => 'sid',
            'header' => $plugin->gT('Survey ID'),
            'value' => '$data->surveyIdLink',
            'type' => 'raw'
        );
    }

    $columns[] = array(
        'name' => 'ordered_by',
        'header' => $plugin->gT('Ordered by'),
        'value' => '$data->user->full_name'
    );
    $columns[] = array(
        'name' => 'country',
        'header' => $plugin->gT('Country'),
        'value' => '$data->country'
    );
    $columns[] = array(
        'name' => 'target-group',
        'header' => $plugin->gT('Target group'),
        'value' => '(strlen($data->targetGroup) > 50
            ? CHtml::tag("span", array("title" => $data->targetGroup, "data-toggle" => "tooltip"), ellipsize($data->targetGroup, 50))
            : $data->targetGroup)',
        'type' => 'raw'
    );
    $columns[] = array(
        'name' => 'age',
        'header' => $plugin->gT('Age'),
        'value' => '$data->age',
        'htmlOptions' => array(
            'class' => 'cint-age'
        )
    );
    $columns[] = array(
        'name' => 'price',
        'header' => $plugin->gT('Price'),
        'value' => '$data->price',
        'type' => 'raw',
        'htmlOptions' => array(
            'class' => 'cint-price'
        )
    );
    $columns[] = array(
        'name' => 'completes',
        'header' => $plugin->gT('Completes'),
        'value' => '$data->completes',
    );
    $columns[] = array(
        'name' => '__completedCheck',
        'header' => '',
        'value' => '$data->completedCheck',
        'type' => 'raw'
    );
    $columns[] = array(
        'name' => 'status',
        'header' => $plugin->gT('Status'),
        'value' => '$data->styledStatus',
        'type' => 'raw'
    );
    $columns[] = array(
        'name' => 'buttons',
        'header' => '',
        'value' => '$data->buttons',
        'type' => 'raw',
        'htmlOptions' => array(
            'class' => 'cint-buttons'
        )
    );

    $widget = $this->widget('bootstrap.widgets.TbGridView', array(
        'dataProvider' => $model->search($surveyId),
        'id' => 'url',
        'itemsCssClass' =>'table-striped',
        'emptyText' => $plugin->gT('No order made yet'),
        'afterAjaxUpdate' => 'doToolTip',
        'ajaxUpdate' => true,
        'columns' => $columns
    ));
?>
</div>

<!-- Hack to not publish jQuery twice -->
<?php $plugin->renderClientScripts(); ?>
