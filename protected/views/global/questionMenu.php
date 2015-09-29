<?php
/* @var Controller $this*/
use ls\models\Question;

if (!isset($model) || !$model instanceof Question) {
    throw new Exception("ls\models\Question must be set for question menu.");
}
$menu = [[ // Left side
    [
        'title' => gT('Preview question'),
        'icon' => 'eye-open',
        'linkOptions' => [
            'target' => '_blank'
        ],
        'url' => ["questions/preview", 'id' => $model->primaryKey]
    ],

    [
        'title' => gT('Remove question'),
        'icon' => 'trash',
        'linkOptions' => [
            'confirm' => 'Are you sure?'
        ],
        'url' => ["questions/delete", 'id' => $model->primaryKey]
    ],
], [ // Right side
    [
    ], 
    
]];
    
    $event = new PluginEvent('afterQuestionMenuLoad', $this);
	$event->set('menu', $menu);
    $event->dispatch();
	return $event->get('menu');