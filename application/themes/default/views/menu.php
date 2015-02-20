<?php
	$menu = [
        array(
            array(
                'title' => gT('Default administration page'),
                'url' => ['surveys/index'],
                'icon' => 'home'
            ),
            array(
                'title' => gT('Manage survey administrators'),
                'url' => ['users/index'],
                'icon' => 'folder-open'
            ),
            array(
                'title' => gT('Create/edit user groups'),
                'url' => array('admin/usergroups', 'sa' => 'index'),
                'icon' => 'tags',
                'visible' => App()->user->checkAccess('usergroups')
            ),
            array(
                'title' => gT('Global settings'),
                'url' => array('admin/globalsettings'),
                'icon' => 'pencil',
                'visible' => App()->user->checkAccess('settings')
            ),
            array(
                'title' => gT('Check Data Integrity'),
                'url' => array('admin/checkintegrity'),
                'icon' => 'check',
                'visible' => App()->user->checkAccess('settings')
            ),
            array(
                'title' => gT('Backup Entire Database'),
                'url' => array("admin/dumpdb"),
                'icon' => 'download-alt',
                'visible' => App()->user->checkAccess('superadmin') && App()->db->driverName == 'mysql'
            ),
            array(
                'label' => gT('The database export is only available for MySQL databases. For other database types please use the according backup mechanism to create a database dump.'),
                'icon' => 'backup_disabled',
                'visible' => App()->user->checkAccess('superadmin') && App()->db->driverName != 'mysql'
            ),
            array(
                'title' => gT('Edit label sets'),
                'url' => array('admin/labels', 'sa' => 'view'),
                'icon' => 'list',
                'visible' => App()->user->checkAccess('labelsets')
            ),
            array(
                'title' => gT('Template Editor'),
                'url' => array('admin/templates/'),
                'icon' => 'eye-open',
                'visible' => App()->user->checkAccess('templates')
            ),
            array(
                'title' => gT('Central participant database/panel'),
                'url' => array('admin/participants'),
                'icon' => 'user',
                'visible' => App()->user->checkAccess('participantpanel')
            ),
            array(
                'title' => gT('Plugin manager'),
                'url' => array('/plugins'),
                'icon' => 'wrench',
                'visible' => App()->user->checkAccess('superadmin')
            ),
        ), [
//            array(
//                'title' => gT('Detailed list of surveys'),
//                'url' => array('admin/survey', 'sa' => 'index'),
//                'icon' => 'list'
//            ),
            [
                'label' => gT('Surveys'),
                'items' => array_map(function(Survey $survey) {
                    return [
                        'url' => App()->createUrl('admin/survey/sa/view', ['surveyid' => $survey->sid]),
                        'label' => $survey->localizedTitle . (($survey->isActive) ? ' (' . gT('inactive') .')' : '')
                    ];
                    
                }, Survey::model()->findAll())
            ], 
            [
                'title' => gT('Create, import, or copy a survey'),
                'url' => array('admin/survey', 'sa' => 'newsurvey'),
                'icon' => 'plus',
                'visible' => App()->user->checkAccess('surveys', ['crud' => 'create'])
            ],
            [
                'title' => gT('Logout') . ' ' . App()->user->name,
                'url' => ['users/logout'],
                'icon' => 'off'
            ], [
                'title' => gT('Preferences'),
                'url' => ['users/profile'],
                'icon' => 'edit'
            ],
            array(
                'title' => gT('LimeSurvey online manual'),
                'url' => "http://docs.limesurvey.org",
                'icon' => 'question-sign',
            ),
            
            
            
            ]
];
    
    $event = new PluginEvent('afterAdminMenuLoad', $this);
	$event->set('menu', $menu);
    $event->dispatch();
	return $event->get('menu');
?>