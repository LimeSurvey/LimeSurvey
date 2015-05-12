<?php
/* @var Survey $survey */
if (!isset($this->survey)) {
    throw new Exception("Survey must be set for survey menu.");
}
$menu = [[ // Left side
    [
        'title' => gT('Activate survey'),
        'url' => $this->survey->isActive && $this->survey->isExpired ? ["surveys/unexpire", 'id' => $this->survey->sid] : ["surveys/activate", 'id' => $this->survey->sid],
        'icon' => 'play',
        'disabled' => $this->survey->isActive && !$this->survey->isExpired,
    ], [
        'title' => gT('Expire survey'),
        'url' => $this->survey->isExpired ? '#' : ["surveys/expire", 'id' => $this->survey->sid],
        'icon' => 'pause',
        'disabled' => $this->survey->isExpired || !$this->survey->isActive

    ], [
        'title' => gT('Deactivate survey'),
        'url' => ["surveys/deactivate", 'id' => $this->survey->sid],
        'icon' => 'stop',
        'disabled' => !$this->survey->isActive
    ], [
        'title' => gT('Execute survey.'),
        'icon' => 'certificate',
        'disabled' => !$this->survey->isActive || $this->survey->isExpired,
        'linkOptions' => ['target' => '_blank'],
        'url' => !$this->survey->isActive || $this->survey->isExpired ? '#' : ["surveys/start", 'id' => $this->survey->sid]
    ], [
        'title' => gT('Survey settings'),
        'icon' => 'wrench',
//        'disabled' => $this->survey->responseCount == 0,
        'url' => ["surveys/update", 'id' => $this->survey->sid]
    ], [
        'title' => gT('Responses'),
        'icon' => 'inbox',
        'disabled' => $this->survey->responseCount == 0,
        'url' => ["responses/index", 'id' => $this->survey->sid]
    ], [
        'title' => gT('Tokens'),
        'icon' => 'bullhorn',
        'disabled' => !$this->survey->bool_usetokens,
        'url' => ["tokens/index", 'surveyId' => $this->survey->sid]
    ]
], [ // Right side

    [
        'title' => gT('Add group'),
        'icon' => 'plus',
        'disabled' => $this->survey->isActive,
        'url' => ["groups/create", 'surveyId' => $this->survey->primaryKey]
    ],
    
]];
    
    $event = new PluginEvent('afterSurveyMenuLoad', $this);
	$event->set('menu', $menu);
    $event->dispatch();
	return $event->get('menu');