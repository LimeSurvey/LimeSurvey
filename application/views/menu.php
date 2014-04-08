<?php
	$menu = array(
		array(
			'label' => gT('Default administration page'),
			'url' => array('admin/survey'),
			'icon' => 'home'
		),
		array(
			'label' => gT('Manage survey administrators'),
			'url' => array('admin/survey'),
			'icon' => 'security'
		),
		array(
			'label' => gT('Create/edit user groups'),
			'url' => array('admin/usergroups', 'sa' => 'index'),
			'icon' => 'usergroup',
			'visible' => Permission::model()->hasGlobalPermission('usergroups', 'read')
		),
		array(
			'label' => gT('Global settings'),
			'url' => array('admin/globalsettings'),
			'icon' => 'global',
			'visible' => Permission::model()->hasGlobalPermission('settings', 'read')
		),
		array(
			'label' => gT('Check Data Integrity'),
			'url' => array('admin/checkintegrity'),
			'icon' => 'checkdb',
			'visible' => Permission::model()->hasGlobalPermission('settings','read')
		),
		array(
			'label' => gT('Backup Entire Database'),
			'url' => array("admin/dumpdb"),
			'icon' => 'backup',
			'visible' => Permission::model()->hasGlobalPermission('superadmin','read') && App()->db->driverName == 'mysql'
		),
		array(
			'label' => gT('The database export is only available for MySQL databases. For other database types please use the according backup mechanism to create a database dump.'),
			'icon' => 'backup_disabled',
			'visible' => Permission::model()->hasGlobalPermission('superadmin','read') && App()->db->driverName != 'mysql'
		),
		array(
			'label' => gT('Edit label sets'),
			'url' => array('admin/labels', 'sa' => 'view'),
			'icon' => 'labels',
			'visible' => Permission::model()->hasGlobalPermission('labelsets','read')
		),
	    array(
			'label' => gT('Template Editor'),
			'url' => array('admin/templates/'),
			'icon' => 'templates',
			'visible' => Permission::model()->hasGlobalPermission('templates','read')
		),
		array(
			'label' => gT('Central participant database/panel'),
			'url' => array('admin/participants'),
			'icon' => 'cpdb',
			'visible' => Permission::model()->hasGlobalPermission('participantpanel','read')
		),
		array(
			'label' => gT('Plugin manager'),
			'url' => array('/plugins'),
			'icon' => 'plugin',
			'visible' => Permission::model()->hasGlobalPermission('superadmin','read')
		),
		// Right side: note that these are listed in reverse order since they float right.
		array(
			'label' => gT('Logout'),
			'itemOptions' => array(
				'class' => 'right'
			),
			'url' => array('admin/authentication/sa/logout'),
			'icon' => 'logout'
			
		),
		array(
			'label' => gT('Preferences'),
			'itemOptions' => array(
				'class' => 'right'
			),
			'url' => array('admin/user/sa/personalsettings'),
			'icon' => 'edit'

		),
		array(
			'label' => gT('LimeSurvey online manual'),
			'itemOptions' => array(
				'class' => 'right'
			),
			'url' => "http://docs.limesurvey.org",
			'icon' => 'showhelp',
		),
		array(
			'label' => gT('Create, import, or copy a survey'),
			'itemOptions' => array(
				'class' => 'right'
			),
			'url' => array('admin/survey', 'sa' => 'newsurvey'),
			'icon' => 'add',
			'visible' => Permission::model()->hasGlobalPermission('surveys','create')
		),
		array(
			'label' => gT('Detailed list of surveys'),
			'itemOptions' => array(
				'class' => 'right'
			),
			'url' => array('admin/survey', 'sa' => 'index'),
			'icon' => 'surveylist'
		),
		array(
			'label' => gT('Surveys'),
			'itemOptions' => array(
				'class' => 'right'
			),
			'items' => run(function() {
				$surveys = Survey::model()->active()->findAll();
				$result = array(
					array(
						'label' => gT('Active'),
						'items' => array()
					),
					array(
						'label' => gT('Inactive'),
						'items' => array()
					),
					array(
						'label' => gT('Expired'),
						'items' => array()
					),

				);
				foreach ($surveys as $survey)
				{
					$item = array(
						'label' => $survey->localizedTitle,
						'url' => array('admin/survey/sa/view', 'surveyid' => $survey->sid)

					);

					if ($survey->active != 'Y')
					{
						$result[1]['items'][] = $item;
					}
					elseif ($survey->isExpired())
					{
						$result[2]['items'][] = $item;
					}
					else
					{
						$result[0]['items'][] = $item;
					}

					
				}
				return $result;

			})
		)
		
		
	);

	$event = new PluginEvent('afterAdminMenuLoad', $this);
	$event->set('menu', $menu);

	return App()->getPluginManager()->dispatchEvent($event)->get('menu');
?>