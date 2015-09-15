<?php
//$this->widget(FileManagerWidget::class, [
//    'context' => 'survey',
//    'key' => $survey->primaryKey
//]);

$this->widget(\FileManagerWidget::class, [
    'id' => 'fileBrowser',
    'key' => $survey->primaryKey,
    'context' => 'survey',
    'htmlOptions' => [
//        'style' => 'height: 500px;'
    ],
    'clientOptions' => [
        'height' => 400,
//        'defaultView' => 'list',
//        'resizable' => false,
        'uiOptions' => [
            'cwd' => [
                'listView' => [
                    'columns' => ['date', 'size'],
                ],
            ]
        ],
        'handlers' => [
            // Set height after init. Needed to correctly calculate height from fixed parent.
//            'init' => new CJavaScriptExpression('function() { $("#fileBrowser").css("height", "").trigger("resize"); }')
        ]
    ],
//    'disabledCommands' => [
//        'archive',
//        'download',
//        'quicklook',
//        'open',
//        'edit'
//    ],
]);