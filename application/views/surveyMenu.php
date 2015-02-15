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
    
]];
    
    $event = new PluginEvent('afterSurveyMenuLoad', $this);
	$event->set('menu', $menu);
    $event->dispatch();
	return $event->get('menu');