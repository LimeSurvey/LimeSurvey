<?php
/* @var Survey $survey */
if (!isset($this->survey)) {
    throw new Exception("Survey must be set for survey menu.");
}
$menu = [[ // Left side
    [
        'title' => gT('This survey is currently active.'),
        'url' => ["admin/survey", 'sa' => 'deactivate', 'surveyid' => $this->survey->sid],
        'icon' => 'stop',
        'visible' => $this->survey->isActive
    ], [
        'title' => gT('This survey is currently not active'),
        'url' => ["admin/survey", 'sa' => 'activate', 'surveyid' => $this->survey->sid],
        'icon' => 'play',
        'visible' => !$this->survey->isActive
    ],
], [ // Right side
    [
        'label' => gT('Groups'),
        'items' => array_map(function(QuestionGroup $group) {
            return [
                'url' => App()->createUrl('admin/survey/sa/view', ['surveyid' => $group->sid, 'gid' => $group->gid]),
                'label' => $group->title
            ];

        }, QuestionGroup::model()->findAllByAttributes(['sid' => $this->survey->sid, 'language' => $this->survey->language]))
    ], 
    
]];
    
    $event = new PluginEvent('afterSurveyMenuLoad', $this);
	$event->set('menu', $menu);
    $event->dispatch();
	return $event->get('menu');