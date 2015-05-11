<?php
/* @var Controller $this*/
/** @var QuestionGroup $this->group */
if (!isset($this->group)) {
    throw new Exception("Group must be set for group menu.");
}
$menu = [[ // Left side
    [
        'title' => gT('Remove group'),
        'icon' => 'trash',
        'disabled' => $this->group->questionCount > 0,
        'linkOptions' => [
            'confirm' => 'Are you sure?'
        ],
        'url' => ["groups/delete", 'id' => $this->group->primaryKey]
//
    ],
], [ // Right side
    [
        'title' => gT('Add question'),
        'icon' => 'plus',
        'disabled' => $this->survey->isActive,
        'url' => ["questions/create", 'groupId' => $this->group->primaryKey]
//
    ],
    [
//        'label' => gT('Questions'),
//        'items' => array_map(function(Question $question) {
//            return [
//                'url' => App()->createUrl('questions/view', ['id' => $question->qid]),
//                'label' => $question->title
//            ];
//
//        }, Question::model()->primary()->findAllByAttributes(['sid' => $this->survey->sid, 'language' => $this->survey->language]))
    ], 
    
]];
    
    $event = new PluginEvent('afterGroupMenuLoad', $this);
	$event->set('menu', $menu);
    $event->dispatch();
	return $event->get('menu');