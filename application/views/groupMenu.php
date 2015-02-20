<?php
/* @var Controller $this*/
if (!isset($this->group)) {
    throw new Exception("Group must be set for group menu.");
}
$menu = [[ // Left side
  
], [ // Right side
    [
        'label' => gT('Questions'),
        'items' => array_map(function(Question $question) {
            return [
                'url' => App()->createUrl('questions/view', ['id' => $question->qid]),
                'label' => $question->title
            ];

        }, Question::model()->primary()->findAllByAttributes(['sid' => $this->survey->sid, 'language' => $this->survey->language]))
    ], 
    
]];
    
    $event = new PluginEvent('afterGroupMenuLoad', $this);
	$event->set('menu', $menu);
    $event->dispatch();
	return $event->get('menu');