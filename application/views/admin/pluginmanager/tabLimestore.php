<?php

use LimeSurvey\PluginManager\LimeStoreDataProvider;

$installLimestorePluginUrl = Yii::app()->getController()->createUrl(
    'admin/pluginmanager',
    ['sa' => 'installLimestorePlugin']
);

$this->widget(
    'bootstrap.widgets.TbGridView',
    [
        'id'                       => 'limestore-grid',
        'dataProvider'             => new LimeStoreDataProvider([]),
        'template'                 => "{items}\n<div id='pluginsListPager'><div class=\"col-sm-4\" id=\"massive-action-container\"></div><div class=\"col-sm-4 pager-container ls-ba \">{pager}</div><div class=\"col-sm-4 summary-container\">{summary}</div></div>",
        'htmlOptions'              => ['class' => 'table-responsive grid-view-ls'],
        'columns'                  => [
            [
                'header' => gT('Action'),
                'type'   => 'raw',
                'name'   => 'action',
                'value'  => function ($data) use ($installLimestorePluginUrl) {
                    return sprintf(
                        '<a href="%s" class="btn btn-primary">%s</a>',
                        $installLimestorePluginUrl . '&id=' . $data->id,
                        gT('Install')
                    );
                }
            ],
            [
                'header' => gT('Plugin'),
                'name'   => 'extension_name',
                'type'   => 'html',
                'value'  => '$data->extension_name'
            ],
            [
                'header' => gT('Author'),
                'name'   => 'name',
                'type'   => 'html',
                'value'  => '$data->owner->name'
            ],
            [
                'header' => gT('Status'),
                'name'   => 'name',
                'type'   => 'html',
                'value'  => '$data->status'
            ],
            [
                'header' => gT('Created'),
                'name'   => 'name',
                'type'   => 'html',
                'value'  => '$data->created'
            ]
        ],
        //'rowHtmlOptionsExpression' => 'array("data-id" => $data->id)',
        'ajaxUpdate'               => 'plugins-grid'
    ]
); ?>
