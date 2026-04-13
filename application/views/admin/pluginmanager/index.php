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
<div class="row mb-3 mt-1">
    <div class="float-end">
        <?php /* Disabled for prototype 1.
            <a
                href=''
                class='btn btn-outline-secondary '
                data-bs-toggle='tooltip'
                title='<?php eT('Install plugins from the extension shop'); ?>'
            >
                <i class='ri-shopping-cart-fill'></i>&nbsp;
                <?php eT('Browse the shop'); ?>
            </a>
             */ ?>
        <?php foreach ($extraMenus as $menu) : ?>
            <a href='<?php echo $menu->getHref(); ?>' <?php if ($menu->getOnClick()) : ?> onclick='<?php echo $menu->getOnClick(); ?>' <?php endif; ?> <?php if ($menu->getTooltip()) : ?> data-bs-toggle='tooltip' data-title='<?php echo $menu->getTooltip(); ?>' <?php endif; ?> class='btn btn-outline-secondary'>
                <?php if ($menu->getIconClass()) : ?>
                    <i class='<?php echo $menu->getIconClass(); ?>'></i>&nbsp;
                <?php endif; ?>
                <?php echo $menu->getLabel(); ?>
            </a>
        <?php endforeach; ?>
    </div>
</div>

<?php

$sort               = new CSort();
$sort->attributes   = [
    'name' => [
        'asc' => 'name',
        'desc' => 'name desc',
    ],
    'description' => [
        'asc' => 'description',
        'desc' => 'description desc',
    ],
    'status' => [
        'asc' => 'active',
        'desc' => 'active desc',
        'default' => 'desc',
    ],
];
$sort->defaultOrder = [
    'name' => CSort::SORT_ASC,
];

$providerOptions = [
    'pagination' => [
        'pageSize' => $pageSize,
    ],
    'sort' => $sort,
    'caseSensitiveSort' => false,
];

$dataProvider = new CArrayDataProvider($plugins, $providerOptions);

$gridColumns = [
    [
        'header' => gT('Plugin'),
        'name' => 'name',
        'type' => 'html',
        'value' => '$data->getName()'
    ],
    [
        'header' => gT('Description'),
        'name' => 'description',
        'type' => 'html',
        'value' => '$data->getPossibleDescription()'
    ],
    [
        'header' => gT('Status'),
        'type' => 'html',
        'name' => 'status',
        'value' => '$data->getStatus()'
    ],
    [
        'header'            => gT('Action'),
        'name'              => 'actions',
        'value'             => '$data->buttons',
        'type'              => 'raw',
        'headerHtmlOptions' => ['class' => 'ls-sticky-column'],
        'htmlOptions'       => ['class' => 'text-center ls-sticky-column'],
    ]
];

$this->widget(
    'application.extensions.admin.grid.CLSGridView',
    [
        'id'                       => 'plugins-grid',
        'dataProvider'             => $dataProvider,
        'summaryText'              => gT('Displaying {start}-{end} of {count} result(s).') . ' '
            . sprintf(
                gT('%s rows per page'),
                CHtml::dropDownList(
                    'pageSize',
                    $pageSize,
                    Yii::app()->params['pageSizeOptions'],
                    [
                        'class' => 'changePageSize form-select',
                        'style' => 'display: inline; width: auto'
                    ]
                )
            ),
        'columns' => $gridColumns,
        'rowHtmlOptionsExpression' => 'array("data-id" => $data["id"])',
        'ajaxUpdate' => 'plugins-grid',
        'lsAfterAjaxUpdate'          => []
    ]
);

$this->renderPartial('./pluginmanager/uploadModal', []);
?>

<script type="text/javascript">
    jQuery(function($) {
        // To update rows per page via ajax
        $(document).on("change", '#pageSize', function() {
            $.fn.yiiGridView.update('plugins-grid', {
                data: {
                    pageSize: $(this).val()
                }
            });
        });
    });
</script>