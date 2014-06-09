<?php

    class MenuWidget extends CWidget
    {
        public $menu = array();
        
        public function __construct($owner = null) {
            parent::__construct($owner);
            Yii::import('application.helpers.surveytranslator_helper', true);
        }
        public $defaults = array(
            'title' => '',
            'alt' => '',
            'type' => 'link'
        );
        
        public $surveyId = null;
        public $groupId = null;
        public $questionId = null;
        
        public function run()
        {
            $this->render('adminmenu', array('menu' => $this->menuMain()));
            if (isset($this->surveyId))
            {
                $this->render('adminmenu', array('menu' => $this->menuSurvey($this->surveyId)));
            }
            if (isset($this->groupId))
            {
                $this->render('adminmenu', array('menu' => $this->menuGroup($this->groupId)));
            }
            if (isset($this->questionId))
            {
                $this->render('adminmenu', array('menu' => $this->menuQuestion($this->questionId)));
            }
        }

        
        
        protected function menuMain()
        {
            $title = CHtml::tag('strong', array(), gT('Administration'));
            if(Yii::app()->session['loginID'])
            {
                $title .= ' -- ' . gT("Logged in as:");
                $text = ' ' . Yii::app()->session['user'] . ' ' . CHtml::image(Yii::app()->getConfig('adminimageurl'). 'profile_edit.png', gT("Edit your personal preferences"));
                $title .=  CHtml::tag('strong', array(), CHtml::link($text, array('admin/user', 'sa' => 'personalsettings')));
            }
            $menu['title'] = CHtml::tag('div', array('class'=>'menubar-title-left'), $title);
            $menu['role'] = 'main';
            $menu['imageUrl'] = App()->getConfig('adminimageurl');
            $menu['items']['left'][] = array(
                'href' => array('admin/survey'),
                'alt' => gT('Default administration page'),
                'image' => 'home.png',
            );
            $menu['items']['left'][] = 'separator';
            if(Permission::model()->hasGlobalPermission('users','read'))
            {
                $menu['items']['left'][] = array(
                    'href' => array('admin/user'),
                    'alt' => gT('Manage survey administrators'),
                    'image' => 'security.png',
                );
            }

            $menu['items']['left'][] = $this->userGroups();
            $menu['items']['left'][] = $this->globalSettings();
            $menu['items']['left'][] = 'separator';
            $menu['items']['left'][] = $this->checkIntegrity();
            $menu['items']['left'][] = $this->dumpDatabase();
            $menu['items']['left'][] = 'separator';
            $menu['items']['left'][] = $this->editLabels();
            $menu['items']['left'][] = 'separator';
            $menu['items']['left'][] = $this->editTemplates();
            $menu['items']['left'][] = 'separator';
            $menu['items']['left'][] = $this->participantDatabase();
            $menu['items']['left'][] = $this->pluginManager();

            $surveys = getSurveyList(true);
            $tmpList = array();
            $timeadjust = getGlobalSetting('timeadjust');
            foreach ($surveys as $survey)
            {
                if($survey['active']!='Y')
                {
                    $group = gT("Inactive");
                    $list = 'inactive';
                } elseif($survey['expires']!='' && $survey['expires'] < dateShift(date("Y-m-d H:i:s"), "Y-m-d H:i:s", $timeadjust))
                {
                    $group = gT("Expired");
                    $list = 'expired';
                } else
                {
                    $group = gt("Active");
                    $list = 'active';
                }        
                $tmpList[$list][] = array(
                    'id' => $survey['sid'],
                    'title' => $survey['surveyls_title'],
                    'group' => $group                
                );
            }
            $surveyList = array();
            if (array_key_exists('active', $tmpList)) {
                $surveyList = array_merge($surveyList, $tmpList['active']);
            }
            if (array_key_exists('expired', $tmpList)) {
                $surveyList = array_merge($surveyList, $tmpList['expired']);
            }
            if (array_key_exists('inactive', $tmpList)) {
                $surveyList = array_merge($surveyList, $tmpList['inactive']);
            }
            $menu['items']['right'][] = array(
                'title' => gT('Surveys:'),
                'type' => 'select',
                'name' => 'surveyid',
                'route' => 'admin/survey/sa/view',
                'param' => 'surveyid',
                'empty' => gT('No surveys available.'),
                'values' => $surveyList,
                'value' => $this->surveyId
            );
            $menu['items']['right'][] = array(
                'href' => array('admin/survey', 'sa' => 'index'),
                'alt' => gT('Detailed list of surveys'),
                'image' => 'surveylist.png'
            );
            
            $menu['items']['right'][] = $this->createSurvey();
            $menu['items']['right'][] = 'separator';

            
            $menu['items']['right'][] = array(
                'href' => array('admin/authentication', 'sa' => 'logout'),
                'alt' => gT('Logout'),
                'image' => 'logout.png'
            );
            
            $menu['items']['right'][] = array(
                'href' => "http://docs.limesurvey.org",
                'alt' => gT('LimeSurvey online manual'),
                'image' => 'showhelp.png'
            );

            $event = new PluginEvent('afterAdminMenuLoad', $this);
            $event->set('menu', $menu);
            
            $result = App()->getPluginManager()->dispatchEvent($event);
            
            $menu = $result->get('menu');
            return $menu;
        }

        protected function menuQuestion($questionId)
        {
            $question = Question::model()->findByPk($questionId);
            
            $menu['title'] = "Question {$question->code} (id: {$questionId})";
            $menu['role'] = 'question';
            $menu['imageUrl'] = App()->getConfig('adminimageurl');
            
            $menu['items']['left'][] = array(
                'alt' => gT('Preview this question'),
                'type' => 'link',
                'image' => 'preview.png',
                'target' => '_blank',
                'href' => array('questions/preview/', 'id' => $questionId)
            );
            $menu['items']['left'][] = 'separator';
            
            return $menu;
        }
        
        protected function menuSurvey($surveyId)
        {
            /**
             * @todo Remove direct session access.
             * @todo Remove admin specific setting; language is a property of any session.
             */
            $surveyInfo = getSurveyInfo($surveyId, Yii::app()->session['adminlang']);
            $menu['title'] = "Survey {$surveyInfo['surveyls_title']} (id: {$surveyId})";
            $menu['role'] = 'survey';
            $menu['imageUrl'] = App()->getConfig('adminimageurl');
            
            if ($surveyInfo['active'] == 'Y')
            {
                $menu['items']['left'][] = array(
                    'type' => 'image',
                    'image' => 'active.png',
                );
                /**
                 * @todo Get request changes state.
                 */
                $menu['items']['left'][] = array(
                    'type' => 'image',
                    'image' => 'deactivate.png',
                );
            }
            else
            {
                $menu['items']['left'][] = array(
                    'type' => 'image',
                    'image' => 'inactive.png',
                );
                $menu['items']['left'][] = array(
                    'href' => array('admin/survey', 'sa' => 'activate', 'surveyid' => $surveyId),
                    'image' => 'activate.png',
                );
                
            }
            $menu['items']['left'][] = 'separator';
            $languages = array($surveyInfo['language']);
            if (isset($surveyInfo['additional_languages']))
            {
                $languages = array_merge($languages, array_filter(explode(' ', $surveyInfo['additional_languages'])));
            }
            foreach ($languages as $language)
            {
                $subitems[] = array(
                    'type' => 'link',
                    'title' => getLanguageNameFromCode($language, false),
                    'image' => 'do_30.png',
                    'href' => array('survey/index', 'sid' => $surveyId, 'newtest' => 'Y', 'lang' => $language)
                );
            }
            $menu['items']['left'][] = array(
                'type' => 'sub',
                'href' => array('survey/index', 'sid' => $surveyId, 'newtest' => 'Y'),
                'image' => 'do.png',
                'items' => array(
                    array(
                        'type' => 'sub',
                        'items' => $subitems,
                        'href' => array('survey/index', 'sid' => $surveyId, 'newtest' => 'Y'),
                        'title' => gT('Test this survey'),
                        'image' => 'do_30.png'
                    )
                )
            );
            
            $menu['items']['left'][] = array(
                'type' => 'sub',
                'href' => array('surveys/view', 'id' => $surveyId),
                'image' => 'edit.png',
                'items' => array(
                    array(
                        'type' => 'link',
                        'title' => gT('Edit text elements'),
                        'image' => 'edit_30.png',
                        'href' => array('admin/survey', 'sa' => 'editlocalsettings', 'surveyid' => $surveyId)
                    ),
                    array(
                        'type' => 'link',
                        'title' => gT('General settings'),
                        'image' => 'survey_settings_30.png',
                        'href' => array('admin/survey', 'sa' => 'editsurveysettings', 'surveyid' => $surveyId)
                    ),
                    array(
                        'type' => 'link',
                        'title' => gT('Survey permissions'),
                        'image' => 'survey_security_30.png',
                        'href' => array('admin/surveypermission', 'sa' => 'view', 'surveyid' => $surveyId)
                    ),
                    array(
                        'type' => 'link',
                        'title' => gT('Quotas'),
                        'image' => 'quota_30.png',
                        'href' => array('admin/quotas', 'sa' => 'index', 'surveyid' => $surveyId)
                    ),
                    array(
                        'type' => 'link',
                        'title' => gT('Assessments'),
                        'image' => 'assessments_30.png',
                        'href' => array('admin/assessments', 'sa' => 'index', 'surveyid' => $surveyId)
                    ),
                    array(
                        'type' => 'link',
                        'title' => gT('Email templates'),
                        'image' => 'emailtemplates_30.png',
                        'href' => array('admin/emailtemplates', 'sa' => 'index', 'surveyid' => $surveyId)
                    ),
                    array(
                        'type' => 'link',
                        'title' => gT('Survey logic file'),
                        'image' => 'quality_assurance_30.png',
                        'href' => array('admin/expressions', 'sa' => 'survey_logic_file', 'sid' => $surveyId)
                    ),
                    
                )
            );
            $menu['items']['left'][] = array(
                'type' => 'sub',
                'href' => array('surveys/view', 'id' => $surveyId),
                'image' => 'tools.png',
                'items' => array(
                    array(
                        'type' => 'link',
                        'title' => gT('Delete survey'),
                        'image' => 'delete_30.png',
                        'href' => array('admin/survey', 'sa' => 'delete', 'surveyid' => $surveyId)
                    ),
                    array(
                        'type' => 'link',
                        'title' => gT('Quick-translation'),
                        'image' => 'translate_30.png',
                        'href' => array('admin/translate', 'sa' => 'index', 'surveyid' => $surveyId)
                    ),
                    array(
                        'type' => 'link',
                        'title' => gT('Expression manager'),
                        'image' => 'expressionmanager_30.png',
                        'href' => array('admin/expressions')
                    ),
                    array(
                        'type' => 'link',
                        'title' => gT('Reset conditions'),
                        'image' => 'resetsurveylogic_30.png'
                    ),
                    array(
                        'type' => 'sub',
                        'title' => gT('Regenerate question codes'),
                        'image' => 'resetsurveylogic_30.png',
                        'items' => array(
                            array(
                                'title' => gT('Straight'),
                                'image' => 'resetsurveylogic_30.png',
                                'href' => array('admin/survey', 'sa' => 'regenquestioncodes', 'surveyid' => $surveyId, 'subaction' => 'straight')
                            ),
                            array(
                                'title' => gT('By group'),
                                'image' => 'resetsurveylogic_30.png',
                                'href' => array('admin/survey', 'sa' => 'regenquestioncodes', 'surveyid' => $surveyId, 'subaction' => 'bygroup')
                            )
                        )
                    ),
                )
            );
            $menu['items']['right'][] = array(
                'title' => 'QuestionGroup:',
                'type' => 'select',
                'name' => 'id',
                'route' => 'groups/view',
                'values' => QuestionGroup::model()->findListByAttributes(array('sid' => $surveyId), 'group_name', 'gid'),
                'value' => $this->groupId
            );
            $menu['items']['right'][] = array(
                'alt' => gT('Add new group to survey'),
                'type' => 'link',
                'image' => 'add.png',
                'href' => array('admin/questiongroups', 'sa' =>  'add', 'surveyid' => $surveyId)
                
            );
            
            return $menu;
        }
        
        protected function menuGroup($groupId)
        {
            $group = QuestionGroup::model()->findByAttributes(array('gid' => $groupId));
            $menu['title'] = "Group {$group->group_name} (id: {$groupId})";
            $menu['role'] = 'group';
            $menu['imageUrl'] = App()->getConfig('adminimageurl');
            
            $menu['items']['left'][] = array(
                'alt' => gT('Preview this group'),
                'type' => 'link',
                'image' => 'preview.png',
                'target' => '_blank',
                'href' => array('groups/preview', 'id' => $groupId)
            );
            $menu['items']['left'][] = 'separator';
            $menu['items']['left'][] = array(
                'alt' => gT('Edit current question group'),
                'type' => 'link',
                'image' => 'edit.png',
                'href' => array('admin/questiongroups', 'sa' => 'edit', 'surveyid' => $group->sid, 'gid' => $groupId)
            );
            $menu['items']['left'][] = 'separator';
            
            $menu['items']['left'][] = 'separator';

            $menu['items']['right'][] = array(
                'type' => 'select',
                'title' => gT('Questions'),
                'name' => 'id',
                'route' => 'questions/update',
                'values' => Question::model()->findListByAttributes(array('sid' => $group->sid, 'gid' => $groupId), 'code', 'qid'),
                'value' => $this->questionId
            );
            $menu['items']['right'][] = array(
                'alt' => gT('Add new question to group'),
                'type' => 'link',
                'image' => 'add.png',
                'href' => array('questions/create', 'gid' => $groupId)
                
            );
            return $menu;
        }
        
        protected function renderItem($item, &$allowSeparator, $imageUrl, $level = 0)
        {
            $result = '';
            if (is_array($item))
            {
                $allowSeparator = true;
                if (isset($item['image']))
                {
                    $result .= CHtml::image($imageUrl . $item['image'], isset($item['alt']) ? $item['alt'] : '');
                }
                if (isset($item['title']))
                {
                    $result .= $item['title'];
                }
                
                if(isset($item['values']))
                {
                    
                    $result = $this->renderSelect($item);
                }
                
                if (isset($item['href']))
                {
                    $options = array();
                    if (isset($item['target']))
                    {
                        $options['target'] = $item['target'];
                    }
                    $result = CHtml::link($result, $item['href'], $options);
                }
                
                if(isset($item['items']))
                {
                    $result = $this->renderSub($item, $imageUrl, $level + 1);
                }
                
                
                
            }
            elseif (is_string($item) && $item == 'separator' && $allowSeparator)
            {
                $result = CHtml::image($imageUrl . 'separator.gif');
                $allowSeparator = false;
            }

            
            return CHtml::tag('li', array(), $result);
        }
        
        protected function renderMenu($menu)
        {
            foreach ($menu['items'] as $class => $menuItems)
            {
                echo CHtml::openTag('ol', array('class' => "menubar-$class level0"));
                $allowSeparator = false;
                foreach($menuItems as $item)
                {
                    echo $this->renderItem($item, $allowSeparator, $menu['imageUrl']);
                }
                echo CHtml::closeTag('ol');

            }
        }
        
        protected function renderSelect($item)
        {
            $result = CHtml::label($item['title'],  $item['name']);
            if (is_array(current($item['values'])))
            {
                $listData = CHtml::listData($item['values'], 'id', 'title', 'group');
            }
            else
            {
                $listData = $item['values'];
            }
            if (!empty($listData))
            {
                $htmlOptions = array(
                    'class' => 'select',
                    'id' => $item['name'],
                    'prompt' => ''//gT('Please choose...')
                );
                if (isset($item['route']))
                {
                    $htmlOptions['data-route'] = $item['route'];
                }
                $result .= $this->widget('ext.bootstrap.widgets.TbSelect2', array(
                    'name' => $item['name'],
                    'value' => $item['value'],
                    'data' => $listData,
                    'options' => array(
                        'minimumResultsForSearch' => 10,
                        'placeholder' => gT('Please choose...')
                    ),
                    'htmlOptions' => $htmlOptions
                ), true);
            }
            elseif (isset($item['empty']))
            {
                $result .= CHtml::tag('span', array(), ' '. $item['empty']);
            }
            return $result;
        }
        
        protected function renderSub($item, $imageUrl, $level)
        {
            $result = '';
            if (isset($item['image']))
            {
                $result .= CHtml::image($imageUrl . $item['image']);
            }
            if (isset($item['title']))
            {
                $result .= $item['title'];
            }
            if (isset($item['href']))    
            {
                $result = CHtml::link($result, $item['href']);
            }
            
            $result .= CHtml::openTag('ol', array('class' => "level$level"));
            
            foreach ($item['items'] as $subItem)
            {
                $allowSeparator = false;
                $result .= $this->renderItem($subItem, $allowSeparator, $imageUrl, $level);
            }
            $result .= CHtml::closeTag('ol');
            return $result;
        }
        
        protected function globalSettings()
        {
            if (Permission::model()->hasGlobalPermission('settings','read'))
            {
                return array(
                    'href' => array('admin/globalsettings'),
                    'image' => 'global.png',
                    'alt' => gT('Global settings')
                );
            }
        }

        protected function checkIntegrity()
        {
            if (Permission::model()->hasGlobalPermission('settings','read'))
            {
                return array(
                    'href' => array('admin/checkintegrity'),
                    'image' => 'checkdb.png',
                    'alt' => gT('Check Data Integrity')
                );
            }
        }

        
        protected function createSurvey()
        {
            if (Permission::model()->hasGlobalPermission('surveys','create'))
            {
                return array(
                    'href' => array('admin/survey', 'sa' => 'newsurvey'),
                    'image' => 'add.png',
                    'alt' => gT('Create, import, or copy a survey')
                );
            }
        }
        protected function dumpDatabase()
        {
            if (Permission::model()->hasGlobalPermission('superadmin','read'))
            {
                if (in_array(Yii::app()->db->getDriverName(), array('mysql', 'mysqli')) || Yii::app()->getConfig('demo_mode') == true)
                {
                    return array(
                        'image' => 'backup.png',
                        'href' => array("admin/dumpdb"),
                        'alt' => gT('Backup Entire Database')
                    );
                }
                else
                {
                    return array(
                        'image' => 'backup_disabled.png',
                        'alt' => gT('The database export is only available for MySQL databases. For other database types please use the according backup mechanism to create a database dump.'),
                        'type' => 'image'
                    );
                }
            }
        }

        protected function editLabels()
        {
            if (Permission::model()->hasGlobalPermission('labelsets','read'))
            {
                return array(
                    'href' => array('admin/labels', 'sa' => 'view'),
                    'image' => 'labels.png',
                    'alt' => gT('Edit label sets')
                );
            }
        }

        protected function editTemplates()
        {
            if (Permission::model()->hasGlobalPermission('templates','read'))
            {
                return array(
                    'href' => array('admin/templates/'),
                    'alt' => gT('Template Editor'),
                    'image' => 'templates.png'
                );
            }
        }
        
        protected function userGroups()
        {
            if(Permission::model()->hasGlobalPermission('usergroups','read'))
            {
                return array(
                    'href' => array('admin/usergroups', 'sa' => 'index'),
                    'alt' => gT('Create/edit user groups'),
                    'image' => 'usergroup.png'
                );
            }
        }

        protected function participantDatabase()
        {
            if (Permission::model()->hasGlobalPermission('participantpanel','read'))
            {
                return array(
                    'alt' => gT('Central participant database/panel'),
                    'href' => array('admin/participants'),
                    'image' => 'cpdb.png'
                 );
            }
        }
        
        protected function pluginManager()
        {
            if (Permission::model()->hasGlobalPermission('superadmin','read'))
            {
                return array(
                    'href' => array('/plugins'),
                    'alt' => gT('Plugin manager'),
                    'image' => 'plugin.png'
                );
            }
        }

    }

?>
