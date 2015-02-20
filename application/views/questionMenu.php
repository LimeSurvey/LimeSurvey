<?php
/* @var Controller $this*/
if (!isset($this->question)) {
    throw new Exception("Question must be set for question menu.");
}
$menu = [[ // Left side
    [
        'test'
    ]
], [ // Right side
    [
    ], 
    
]];
    
    $event = new PluginEvent('afterQuestionMenuLoad', $this);
	$event->set('menu', $menu);
    $event->dispatch();
	return $event->get('menu');