<?php
/* @var Controller $this*/
use ls\models\QuestionGroup;

/** @var QuestionGroup $model */
if (!isset($model) || !$model instanceof QuestionGroup) {
    throw new Exception("Group must be set for group menu.");
}
$menu = [[ // Left side
    [
        'title' => gT('Remove group'),
        'icon' => 'trash',
        'disabled' => $model->questionCount > 0,
        'linkOptions' => [
            'data-confirm' => 'Are you sure?',
            'data-method' => 'delete'
        ],
        'url' => ["groups/delete", 'id' => $model->primaryKey]
//
    ],
], [ // Right side
    [
        'title' => gT('Add question'),
        'icon' => 'plus',
        'disabled' => $model->survey->isActive,
        'url' => ["questions/create", 'groupId' => $model->primaryKey]
//
    ],
    [
//        'label' => gT('Questions'),
//        'items' => array_map(function(ls\models\Question $question) {
//            return [
//                'url' => App()->createUrl('questions/view', ['id' => $question->qid]),
//                'label' => $question->title
//            ];
//
//        }, ls\models\Question::model()->primary()->findAllByAttributes(['sid' => $this->survey->sid, 'language' => $this->survey->language]))
    ], 
    
]];
    
    $event = new PluginEvent('afterGroupMenuLoad', $this);
	$event->set('menu', $menu);
    $event->dispatch();
	return $event->get('menu');