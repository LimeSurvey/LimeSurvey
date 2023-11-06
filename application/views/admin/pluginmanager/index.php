<?php

/**
 * Index view for plugin manager
 * @var $this AdminController
 *
 * @since 2015-10-02
 * @author LimeSurvey GmbH
 */

// DO NOT REMOVE This is for automated testing to validate we see that page
echo viewHelper::getViewTestTag('pluginManager');

$pageSize = intval(Yii::app()->user->getState('pageSize', Yii::app()->params['defaultPageSize']));

?>
<div class='container-fluid'>
    <div class='row'>
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
        </div>
    </div>

    <?php

    $sort = new CSort();
    $sort->attributes = [
        'name'        => [
            'asc'  => 'name',
            'desc' => 'name desc',
        ],
        'description' => [
            'asc'  => 'description',
            'desc' => 'description desc',
        ],
        'status'      => [
            'asc'     => 'active',
            'desc'    => 'active desc',
            'default' => 'desc',
        ],
    ];
    $sort->defaultOrder = [
        'name' => CSort::SORT_ASC,
    ];

    $providerOptions = [
        'pagination'        => [
            'pageSize' => $pageSize,
        ],
        'sort'              => $sort,
        'caseSensitiveSort' => false,
    ];

    $dataProvider = new CArrayDataProvider($plugins, $providerOptions);

    $gridColumns = [
        [
            'type'   => 'raw',
            'header' => gT('Action'),
            'name'   => 'action',
            'value'  => '$data->getActionButtons()'
        ],
        [
            'header' => gT('Status'),
            'type'   => 'html',
            'name'   => 'status',
            'value'  => '$data->getStatus()'
        ],
        [
            'header' => gT('Plugin'),
            'name'   => 'name',
            'type'   => 'html',
            'value'  => '$data->getName()'
        ],
        [
            'header' => gT('Description'),
            'name'   => 'description',
            'type'   => 'html',
            'value'  => '$data->getPossibleDescription()'
        ],
    ];

    $this->widget(
        'bootstrap.widgets.TbGridView',
        [
            'id'                       => 'plugins-grid',
            'dataProvider'             => $dataProvider,
            'htmlOptions'              => ['class' => 'table-responsive grid-view-ls'],
            'template'                 => "{items}\n<div id='pluginsListPager'><div class=\"col-sm-4\" id=\"massive-action-container\"></div><div class=\"col-sm-4 pager-container ls-ba \">{pager}</div><div class=\"col-sm-4 summary-container\">{summary}</div></div>",
            'summaryText'              => gT('Displaying {start}-{end} of {count} result(s).') . ' '
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
            'columns'                  => $gridColumns,
            'rowHtmlOptionsExpression' => 'array("data-id" => $data["id"])',
            'ajaxUpdate'               => 'plugins-grid'
        ]
    );

    $this->renderPartial('./pluginmanager/uploadModal', []);
    ?>
</div>

<script type="text/javascript">
    jQuery(function ($) {
        // To update rows per page via ajax
        $(document).on("change", '#pageSize', function () {
            $.fn.yiiGridView.update('plugins-grid', {data: {pageSize: $(this).val()}});
        });
    });
</script>
