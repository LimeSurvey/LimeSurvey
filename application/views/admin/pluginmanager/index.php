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

// Remember which grid page the admin was on, the same way pageSize is
// remembered, so returning from a plugin's detail page (e.g. via "Close")
// lands back on that page instead of always resetting to page 1.
// Yii omits the "page" param entirely for page 1 (it's the implicit
// default), so an explicit pager click to page 1 looks identical to a
// plain page reload unless we also check for the grid's own ajax marker,
// which is present on every real pager interaction (including to page 1)
// but absent on a fresh page load.
$requestedPage = Yii::app()->request->getParam('page');
$isPluginsGridAjaxRequest = Yii::app()->request->getParam('ajax') === 'plugins-grid';
if ($requestedPage !== null) {
    Yii::app()->user->setState('pluginListPage', intval($requestedPage));
} elseif ($isPluginsGridAjaxRequest) {
    Yii::app()->user->setState('pluginListPage', 1);
}

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
// Only set an explicit currentPage when this is neither an explicit page
// request nor a pager click to page 1 (recognized via the ajax marker);
// otherwise let CPagination's normal GET-based page navigation behave
// exactly as before.
if ($requestedPage === null && !$isPluginsGridAjaxRequest) {
    $providerOptions['pagination']['currentPage'] = max(0, intval(Yii::app()->user->getState('pluginListPage', 1)) - 1);
}

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
        'value' => '$data->getPossibleDescription()',
        'htmlOptions' => ['class' => 'can-contain-link'],
    ],
    [
        'header' => gT('Status'),
        'type' => 'raw',
        'name' => 'status',
        'value' => '$data->getStatus(false, "fs-3")',
        'headerHtmlOptions' => ['class' => 'text-center'],
        'htmlOptions' => ['class' => 'text-center'],
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
        'lsCaption'                  => gT('Plugins'),
        'dataProvider'             => $dataProvider,
        'lsPageSizeCurrentValue'     => $pageSize,
        'columns' => $gridColumns,
        'rowHtmlOptionsExpression' => 'array("data-id" => $data["id"])',
        'ajaxUpdate' => 'plugins-grid',
        'lsAfterAjaxUpdate'          => []
    ]
);

$this->renderPartial('./pluginmanager/uploadModal', []);
?>