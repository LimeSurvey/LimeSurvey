<?php

use LimeSurvey\PluginManager\LimeStoreDataProvider;

$this->widget(
    'bootstrap.widgets.TbGridView',
    [
        'id'                       => 'limestore-grid',
        'dataProvider'             => new LimeStoreDataProvider([]),
        'htmlOptions'              => ['class' => 'table-responsive grid-view-ls'],
        //{ ["extension_type"]=> string(1) "p" ["extension_name"]=> string(10) "MassAction" ["status"]=> string(8) "disabled" ["version"]=> string(5) "1.0.5" ["last_security_version"]=> NULL ["created"]=> string(19) "2018-12-12 17:13:33" ["owner"]=> object(stdClass)#2071 (3) { ["id"]=> string(5) "49797" ["name"]=> string(14) "Olle Haerstedt" ["username"]=> string(7) "ollehar" } } 
        'columns'                  => [
            [
                'header' => gT('Action'),
                'type'   => 'raw',
                'name'   => 'action',
                'value'  => function () { return '<button class="btn btn-primary">Install</button>'; }
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
        ]
        //'rowHtmlOptionsExpression' => 'array("data-id" => $data["id"])',
        //'ajaxUpdate'               => 'plugins-grid'
    ]
); ?>
