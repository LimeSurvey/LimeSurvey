<?php
/** @var \ls\controllers\Controller $this */
use ls\models\Survey;

/* @var Survey $model */
if (!isset($model) || !$model instanceof Survey) {
    throw new Exception("Survey must be set for survey menu.");
}
$menu = [[ // Left side
    [
        'title' => gT('Activate survey'),
        'url' => $model->isActive && $model->isExpired ? ["surveys/unexpire", 'id' => $model->sid] : ["surveys/activate", 'id' => $model->sid],
        'icon' => 'play',
        'disabled' => $model->isActive && !$model->isExpired,
    ], [
        'title' => gT('Expire survey'),
        'url' => $model->isExpired ? '#' : ["surveys/expire", 'id' => $model->sid],
        'icon' => 'pause',
        'disabled' => $model->isExpired || !$model->isActive

    ], [
        'title' => gT('Deactivate survey'),
        'url' => ["surveys/deactivate", 'id' => $model->sid],
        'icon' => 'stop',
        'disabled' => !$model->isActive
    ], [
        'title' => gT('Execute survey'),
        'icon' => 'certificate',
        'disabled' => !$model->isActive || $model->isExpired || $model->bool_usetokens,
        'linkOptions' => ['target' => '_blank'],
        'url' => !$model->isActive || $model->isExpired ? '#' : ["surveys/start", 'id' => $model->sid]
    ], [
        'title' => gT('Preview survey'),
        'icon' => 'eye-open',
        'linkOptions' => [
            'target' => '_blank'
        ],
        'url' => ["surveys/preview", 'id' => $model->primaryKey]
    ],[
        'title' => gT('Survey settings'),
        'icon' => 'wrench',
//        'disabled' => $model->responseCount == 0,
        'url' => ["surveys/update", 'id' => $model->sid]
    ], [
        'title' => gT('Responses'),
        'icon' => 'inbox',
        'disabled' => $model->responseCount == 0,
        'items' => [
            [
                'label' => gT('Show responses'),
                'icon' => 'eye-open',
                'disabled' => $model->responseCount == 0,
                'url' => ["responses/index", 'id' => $model->sid],2
            ], [
                'label' => gT('Export'),
                'icon' => 'download',
                'disabled' => $model->responseCount == 0,
                'url' => ["responses/export", 'id' => $model->sid],
            ]

        ]
    ], [
        'title' => gT('Tokens'),
        'icon' => 'bullhorn',
        'disabled' => !$model->bool_usetokens,
        'url' => ["tokens/index", 'surveyId' => $model->sid]
    ],
], [ // Right side
    [
        'title' => gT('Delete survey'),
        'icon' => 'trash',
        'linkOptions' => [
            'data-method' => 'delete',
            'data-confirm' => gT('Are you sure?')


        ] ,
        'url' => ["surveys/delete", 'id' => $model->sid]

    ],
    [
        'title' => gT('Export survey'),
        'icon' => 'download',
        'url' => ["surveys/export", 'id' => $model->sid]
    ], [
        'title' => gT('Add group'),
        'icon' => 'plus',
        'disabled' => $model->isActive,
        'url' => ["groups/create", 'surveyId' => $model->primaryKey]
    ],
    
]];
    
    $event = new PluginEvent('afterSurveyMenuLoad', $this);
	$event->set('menu', $menu);
    $event->dispatch();
	return $event->get('menu');