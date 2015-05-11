<?php
/* @var Controller $this*/
if (!isset($this->question)) {
    throw new Exception("Question must be set for question menu.");
}
$menu = [[ // Left side
    [
        'title' => gT('Remove question'),
        'icon' => 'trash',
        'linkOptions' => [
            'confirm' => 'Are you sure?'
        ],
        'url' => ["questions/delete", 'id' => $this->question->primaryKey]
//
    ],
], [ // Right side
    [
    ], 
    
]];
    
    $event = new PluginEvent('afterQuestionMenuLoad', $this);
	$event->set('menu', $menu);
    $event->dispatch();
	return $event->get('menu');