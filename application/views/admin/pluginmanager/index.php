<?php

/**
 * Index view for plugin manager
 * @var $this AdminController
 *
 * @since 2015-10-02
 * @author Olle Haerstedt
 */

// DO NOT REMOVE This is for automated testing to validate we see that page
echo viewHelper::getViewTestTag('pluginManager');

$pageSize = intval(Yii::app()->user->getState('pageSize', Yii::app()->params['defaultPageSize']));

?>

<div class='col-sm-12'>
    <div>  <!-- Extra funny div -->
        <div class="pagetitle h3"><?php eT('Plugin manager'); ?></div>
        <div class='col-sm-12'>
            <div class='pull-right'>
                <?php /* Disabled for prototype 1.
                <a
                    href=''
                    class='btn btn-default '
                    data-tooltip='true'
                    title='<?php eT('Install plugins from the extension shop'); ?>'
                >
                    <i class='fa fa-shopping-cart'></i>&nbsp;
                    <?php eT('Browse the shop'); ?>
                </a>
                 */ ?>
                <?php foreach ($extraMenus as $menu): ?>
                    <a
                        href='<?php echo $menu->getHref(); ?>'
                        <?php if ($menu->getOnClick()): ?>
                            onclick='<?php echo $menu->getOnClick(); ?>'
                        <?php endif; ?>
                        <?php if ($menu->getTooltip()): ?>
                            data-toggle='tooltip'
                            data-title='<?php echo $menu->getTooltip(); ?>'
                        <?php endif; ?>
                        class='btn btn-default'
                    >
                        <?php if ($menu->getIconClass()): ?>
                            <i class='<?php echo $menu->getIconClass(); ?>'></i>&nbsp;
                        <?php endif; ?>
                        <?php echo $menu->getLabel(); ?>
                    </a>
                <?php endforeach; ?>
                <?php if (!Yii::app()->getConfig('demoMode')): ?>
                    <a
                        href=''
                        class='btn btn-success '
                        data-toggle='modal'
                        data-target='#installPluginZipModal'
                        data-tooltip='true'
                        title='<?php eT('Install plugin by ZIP archive'); ?>'
                    >
                        <i class='fa fa-file-zip-o'></i>&nbsp;
                        <?php eT('Install ZIP'); ?>
                    </a>
                <?php endif; ?>
                <a 
                    href='<?php echo $scanFilesUrl; ?>'
                    class='btn btn-default'
                    data-toggle='tooltip'
                    title='<?php eT('Scan files for available plugins'); ?>'
                >
                    <i class='fa fa-file '></i>
                    <i class='fa fa-search '></i>&nbsp;
                    <?php eT('Scan files'); ?>
                </a>
                &nbsp;
            </div>
        </div>

    <?php

    $sort = new CSort();
    $sort->attributes = array(
        'name'=>array(
            'asc'=> 'name',
            'desc'=> 'name desc',
        ),
        'description'=>array(
            'asc'=> 'description',
            'desc'=> 'description desc',
        ),
        'status'=>array(
            'asc'=> 'active',
            'desc'=> 'active desc',
            'default'=> 'desc',
        ),
    );
    $sort->defaultOrder = array(
        'name' => CSort::SORT_ASC,
    );

    $providerOptions = array(
        'pagination' => array(
            'pageSize' => $pageSize,
        ),
        'sort' => $sort,
        'caseSensitiveSort' => false,
    );

    $dataProvider = new CArrayDataProvider($plugins, $providerOptions);

    $gridColumns = [
        [
            'header' => gT('Status'),
            'type' => 'html',
            'name' => 'status',
            'value' => '$data->getStatus()'
        ],
        [
            'header' => gT('Plugin'),
            'name' => 'name',
            'type' => 'html',
            'value' => '$data->getName()'
        ],
        [
            'header' => gT('Description'),
            'name' => 'description'
        ],
        [
            'type' => 'raw',
            'header' => gT('Action'),
            'name' => 'action',
            'value' => '$data->getActionButtons()'
        ],
    ];

    $this->widget(
        'bootstrap.widgets.TbGridView',
        [
            'dataProvider' => $dataProvider,
            'id'           => 'plugins-grid',
            'summaryText'  => gT('Displaying {start}-{end} of {count} result(s).') .' '
                . sprintf(
                    gT('%s rows per page'),
                    CHtml::dropDownList(
                        'pageSize',
                        $pageSize,
                        Yii::app()->params['pageSizeOptions'],
                        [
                            'class' => 'changePageSize form-control',
                            'style' => 'display: inline; width: auto'
                        ]
                    )
                ),
            'columns' => $gridColumns,
            'rowHtmlOptionsExpression' => 'array("data-id" => $data["id"])',
            'ajaxUpdate' => 'plugins-grid'
        ]
    );

    $this->renderPartial('./pluginmanager/uploadModal', []);
?>

<script type="text/javascript">
jQuery(function($) {
    // To update rows per page via ajax
    $(document).on("change", '#pageSize', function() {
        $.fn.yiiGridView.update('plugins-grid',{ data:{ pageSize: $(this).val() }});
    });
});
</script>
