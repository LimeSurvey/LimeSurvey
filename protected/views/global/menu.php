<?php
use ls\models\Survey;

$menu = [
        [
            [
                'title' => gT('Default administration page'),
                'url' => ['surveys/index'],
                'icon' => 'home'
            ],
            [
                'title' => gT('Manage survey administrators'),
                'url' => ['users/index'],
                'icon' => 'folder-open'
            ],
            [
                'title' => gT('Create/edit user groups'),
                'url' => ['admin/usergroups', 'sa' => 'index'],
                'icon' => 'tags',
                'visible' => false && App()->user->checkAccess('usergroups')
            ],
            [
                'title' => gT('Global settings'),
                'url' => ['settings/index'],
                'icon' => 'pencil',
                'visible' => App()->user->checkAccess('settings')
            ],
            [
                'title' => gT('Check Data Integrity'),
                'url' => ['admin/checkintegrity'],
                'icon' => 'check',
                'visible' => App()->user->checkAccess('settings')
            ],
            [
                'title' => gT('Backup Entire Database'),
                'url' => ["admin/dumpdb"],
                'icon' => 'download-alt',
                'visible' => App()->user->checkAccess('superadmin') && App()->db->driverName == 'mysql'
            ],
            [
                'label' => gT('The database export is only available for MySQL databases. For other database types please use the according backup mechanism to create a database dump.'),
                'icon' => 'backup_disabled',
                'visible' => App()->user->checkAccess('superadmin') && App()->db->driverName != 'mysql'
            ],
            [
                'title' => gT('Edit label sets'),
                'url' => ['labels/index'],
                'icon' => 'list',
                'visible' => App()->user->checkAccess('labelsets')
            ],
            [
                'title' => gT('ls\models\Template Editor'),
                'url' => ['templates/index'],
                'icon' => 'eye-open',
                'visible' => App()->user->checkAccess('templates')
            ],
            [
                'title' => gT('Central participant database/panel'),
                'url' => ['participants/index'],
                'icon' => 'user',
                'visible' => App()->user->checkAccess('participantpanel')
            ],
            [
                'title' => gT('Plugin manager'),
                'url' => ['/plugins'],
                'icon' => 'wrench',
                'visible' => App()->user->checkAccess('superadmin')
            ],
        ], [
//            array(
//                'title' => gT('Detailed list of surveys'),
//                'url' => array('admin/survey', 'sa' => 'index'),
//                'icon' => 'list'
//            ),
            [
                'label' => gT('Surveys'),
                'url' => ['surveys/index'],
                'items' => array_map(function (Survey $survey) {
                    return [
                        'url' => App()->createUrl('surveys/update', ['id' => $survey->sid]),
                        'label' => $survey->localizedTitle . ((!$survey->isActive) ? ' (' . gT('inactive') .')' : '')
                    ];
                    
                }, Survey::model()->findAll()),
                'visible' => App()->user->checkAccess('surveys', ['crud' => 'read'])
            ], 
            [
                'title' => gT('Create, import, or copy a survey'),
                'url' => ['surveys/create'],
                'icon' => 'plus',
                'visible' => App()->user->checkAccess('surveys', ['crud' => 'create'])
            ],
            [
                'title' => gT('Logout') . ' ' . App()->user->name,
                'label' => App()->user->name,
                'url' => ['users/logout'],
                'icon' => 'log-out',
                'visible' => !App()->user->isGuest

            ],
            [
                'title' => gT('Login'),
//                'label' => App()->user->name,
                'url' => ['users/login'],
                'icon' => 'log-in',
                'visible' => App()->user->isGuest

            ],
            [
                'title' => gT('Preferences'),
                'url' => ['users/profile'],
                'icon' => 'edit',
                'visible' => !App()->user->isGuest
            ],
            [
                'title' => gT('LimeSurvey online manual'),
                'url' => "http://docs.limesurvey.org",
                'icon' => 'question-sign',
            ],
            
            
            
            ]
];
    
    $event = new PluginEvent('afterAdminMenuLoad', $this);
	$event->set('menu', $menu);
    $event->dispatch();
	return $event->get('menu');
?>