<?php
	$menu = [
        array(
            array(
                'title' => gT('Default administration page'),
                'url' => array('admin/survey'),
                'icon' => 'home'
            ),
            array(
                'title' => gT('Manage survey administrators'),
                'url' => array('admin/survey'),
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
        ), [array(
                'title' => gT('Detailed list of surveys'),
                'url' => array('admin/survey', 'sa' => 'index'),
                'icon' => 'list'
            ),
            array(
                'label' => gT('Surveys'),
                'items' => array_map(function(Survey $survey) {
                    return [
                        'url' => App()->createUrl('admin/survey/sa/view', ['surveyid' => $survey->sid]),
                        'label' => $survey->localizedTitle . (($survey->active != 'Y') ? ' (' . gT('inactive') .')' : '')
                    ];
                    
                }, Survey::model()->findAll())
//                run(function() {
//                    $result = array(
//                        array(
//                            'label' => gT('Active'),
//                            'items' => 
//                        ),
//                        array(
//                            'label' => gT('Inactive'),
//                            'items' => array()
//                        ),
//                        array(
//                            'label' => gT('Expired'),
//                            'items' => array()
//                        ),
//
//                    );
//                    foreach ($surveys as $survey)
//                    {
//                        $item = array(
//                            'label' => $survey->localizedTitle,
//                            'url' => array('admin/survey/sa/view', 'surveyid' => $survey->sid)
//
//                        );
//                        $result[] = $item;
//                        if ($survey->active != 'Y')
//                        {
//                            $result[1]['items'][] = $item;
//                        }
//                        elseif ($survey->isExpired())
//                        {
//                            $result[2]['items'][] = $item;
//                        }
//                        else
//                        {
//                            $result[0]['items'][] = $item;
//                        }


//                    }
//                    var_dump($result);
//                    return $result;

//                }),
            ), 
            [
                'title' => gT('Create, import, or copy a survey'),
                'url' => array('admin/survey', 'sa' => 'newsurvey'),
                'icon' => 'plus',
                'visible' => App()->user->checkAccess('surveys', ['crud' => 'create'])
            ],
            [
                'title' => gT('Logout') . ' ' . App()->user->name,
                'url' => ['admin/authentication/sa/logout'],
                'icon' => 'off'
            ], [
                'title' => gT('Preferences'),
                'url' => ['admin/user/sa/personalsettings'],
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

	return App()->getPluginManager()->dispatchEvent($event)->get('menu');
?>