<?php

/**
 * Some extra quick-menu items to ease everyday usage
 *
 * @since 2016-04-22
 * @author Olle Härstedt
 */
class QuickMenu extends \ls\pluginmanager\PluginBase
{
    static protected $description = 'Add a quick-menu when the question explorer is collapsed';
    static protected $name = 'QuickMenu';

    protected $storage = 'DbStorage';
    protected $settings = array(
        'info' => array(
            'type' => 'info',
            'content' => '<div class="well col-sm-8"><span class="fa fa-info-circle"></span>&nbsp;&nbsp;Choose which buttons to show in the quick-menu. The buttons are visible to all back-end users. Some buttons will be hidden due to permissions.</div>'
        ),
        'activateSurvey' => array(
            'type' => 'checkbox',
            'label' => 'Activate survey&nbsp;<span class="glyphicon glyphicon-play"></span>',
            'default' => '0',
            'help' => 'Needed permission: Survey activation - Update'
        ),
        'deactivateSurvey' => array(
            'type' => 'checkbox',
            'label' => 'Deactivate survey&nbsp;<span class="glyphicon glyphicon-stop"></span>',
            'default' => '0',
            'help' => 'Needed permission: Survey activation - Update'
        ),
        'testSurvey' => array(
            'type' => 'checkbox',
            'label' => 'Test or execute survey&nbsp;<span class="glyphicon glyphicon-cog"></span>',
            'default' => '0',
            'help' => 'Available for everyone. Uses survey base language.'
        ),
        'listQuestions' => array(
            'type' => 'checkbox',
            'label' => 'List questions&nbsp;<span class="glyphicon glyphicon-list"></span>',
            'default' => '0',
            'help' => 'Needed permission: Survey content - View'
        ),
        'listQuestionGroups' => array(
            'type' => 'checkbox',
            'label' => 'List question groups&nbsp;<span class="glyphicon glyphicon-list"></span>',
            'default' => '0',
            'help' => 'Needed permission: Survey content - View'
        ),
        'surveySettings' => array(
            'type' => 'checkbox',
            'label' => 'Survey settings&nbsp;<span class="icon-edit"></span>',
            'default' => '0',
            'help' => 'Needed permission: Survey settings - View'
        ),
        'surveySecurity' => array(
            'type' => 'checkbox',
            'label' => 'Survey security&nbsp;<span class="icon-security"></span>',
            'default' => '0',
            'help' => 'Needed permission: Survey security - View'
        ),
        'quotas' => array(
            'type' => 'checkbox',
            'label' => 'Quotas&nbsp;<span class="icon-quota"></span>',
            'default' => '0',
            'help' => 'Needed permission: Quotas - View'
        ),
        'assessments' => array(
            'type' => 'checkbox',
            'label' => 'Assessments&nbsp;<span class="icon-assessments"></span>',
            'default' => '0',
            'help' => 'Needed permission: Assessments - View'
        ),
        'emailTemplates' => array(
            'type' => 'checkbox',
            'label' => 'E-mail templates&nbsp;<span class="icon-emailtemplates"></span>',
            'default' => '0',
            'help' => 'Needed permission: Locale - View'
        ),
        'surveyLogicFile' => array(
            'type' => 'checkbox',
            'label' => 'Survey logic file&nbsp;<span class="icon-expressionmanagercheck"></span>',
            'default' => '0',
            'help' => 'Needed permission: Survey content - View. Uses survey base language.'
        ),
        'tokenManagement' => array(
            'type' => 'checkbox',
            'label' => 'Survey participants&nbsp;<span class="glyphicon glyphicon-user"></span>',
            'default' => '0',
            'help' => 'Needed permission: Token - View'
        ),
        'cpdb' => array(
            'type' => 'checkbox',
            'label' => 'Central participant database&nbsp;<span class="fa fa-users"></span>',
            'default' => '0',
            'help' => 'Needed permission: ?'
        ),
        'responses' => array(
            'type' => 'checkbox',
            'label' => 'Responses&nbsp;<span class="icon-browse"></span>',
            'default' => '0',
            'help' => 'Needed permission: Responses - View'
        ),
        'statistics' => array(
            'type' => 'checkbox',
            'label' => 'Statistics&nbsp;<span class="glyphicon glyphicon-stats"></span>',
            'default' => '0',
            'help' => 'Needed permission: Statistics - View'
        ),
        'reorder' => array(
            'type' => 'checkbox',
            'label' => 'Reorder questions and question groups&nbsp;<span class="icon-organize"></span>',
            'default' => 0,
            'help' => 'Needed permission: Survey content - Update'
        )
    );

    private $buttons = array();

    public function init()
    {
        $this->subscribe('afterQuickMenuLoad');
        $this->subscribe('beforeActivate');
        $this->subscribe('beforeDeactivate');
        $this->subscribe('newDirectRequest');
    }

    /**
     * At activation, create database for
     * quick-menu items sort order
     */
    public function beforeActivate()
    {
        // Create sort order table if it doesn't exist
        if (!$this->api->tableExists($this, 'sortorder'))
        {
            try {
                $this->createSortorderTable();
            }
            catch(Exception $e)
            {
                $event = $this->getEvent();
                $event->set('success', false);
                $event->set(
                    'message',
                    gT('An non-recoverable error happened during the update. Error details:')
                    . "<p>"
                    . htmlspecialchars($e->getMessage())
                    . "</p>"
                );
            }
        }
    }

    /**
     * Create the table to store quick-menu sort order
     *
     * @return void
     */
    protected function createSortorderTable()
    {
        $aFields = array(
            'uid' => 'integer NOT NULL',
            'button_name' => 'string(64)',
            'sort_order' => 'integer',
            'PRIMARY KEY (button_name, uid)'
        );
        $this->api->createTable($this, 'sortorder', $aFields);
    }

    /**
     * Remove database tables at deactivation
     */
    public function beforeDeactivate()
    {
        /*
        // Remove table
        $oDB = Yii::app()->getDb();
        $oDB->schemaCachingDuration=0; // Deactivate schema caching
        $oTransaction = $oDB->beginTransaction();
        try
        {
            $oDB->createCommand()->dropTable('{{plugin_extraquickmenuitems_sortorder}}');
            $oTransaction->commit();
        }
        catch(Exception $e)
        {
            $oTransaction->rollback();
            // Activate schema caching
            $oDB->schemaCachingDuration = 3600;
            // Load all tables of the application in the schema
            $oDB->schema->getTables();
            // Clear the cache of all loaded tables
            $oDB->schema->refresh();
            $event = $this->getEvent();
            $event->set(
                'message',
                gT('An non-recoverable error happened during the update. Error details:')
                . "<p>"
                . htmlspecialchars($e->getMessage())
                . '</p>'
            );
            return;
        }
        */
    }


    /**
     * @param array $data
     * @return void
     */
    private function initialiseButtons(array $data)
    {

        $surveyId = $data['surveyid'];
        $activated = $data['activated'];
        $survey = $data['oSurvey'];
        $baselang = $survey->language;

        $this->buttons = array(
            'activateSurvey' => new QuickMenuButton(array(
                'name' => 'activateSurvey',
                'href' => Yii::app()->getController()->createUrl("admin/survey/sa/activate/surveyid/$surveyId"),
                'tooltip' => gT('Activate survey'),
                'iconClass' => 'glyphicon glyphicon-play navbar-brand',
                'showOnlyWhenSurveyIsDeactivated' => true,
                'neededPermission' => array('surveyactivation', 'update')
            )),
            'deactivateSurvey' => new QuickMenuButton(array(
                'name' => 'deactivateSurvey',
                'href' => Yii::app()->getController()->createUrl("admin/survey/sa/deactivate/surveyid/$surveyId"),
                'tooltip' => gT('Stop this survey'),
                'iconClass' => 'glyphicon glyphicon-stop navbar-brand',
                'showOnlyWhenSurveyIsActivated' => true,
                'neededPermission' => array('surveyactivation', 'update')
            )),
            'testSurvey' => new QuickMenuButton(array(
                'name' => 'testSurvey',
                'openInNewTab' => true,
                'href' => Yii::app()->getController()->createUrl("survey/index/sid/$surveyId/newtest/Y/lang/$baselang"),
                'tooltip' => $activated ? gT('Execute survey') : gT('Test survey'),
                'iconClass' => 'glyphicon glyphicon-cog navbar-brand'
            )),
            'listQuestions' => new QuickMenuButton(array(
                'name' => 'listQuestions',
                'href' => Yii::app()->createUrl("admin/survey/sa/listquestions/surveyid/$surveyId"),
                'tooltip' => gT('List questions'),
                'iconClass' => 'glyphicon glyphicon-list navbar-brand',
                'neededPermission' => array('surveycontent', 'read')
            )),
            'listQuestionGroups' => new QuickMenuButton(array(
                'name' => 'listQuestionGroups',
                'href' => Yii::app()->createUrl("admin/survey/sa/listquestiongroups/surveyid/$surveyId"),
                'tooltip' => gT('List question groups'),
                'iconClass' => 'glyphicon glyphicon-list navbar-brand',
                'neededPermission' => array('surveycontent', 'read')
            )),
            'surveySettings' => new QuickMenuButton(array(
                'name' => 'surveySettings',
                'href' => Yii::app()->getController()->createUrl("admin/survey/sa/editlocalsettings/surveyid/$surveyId"),
                'tooltip' => gT('General settings & texts'),
                'iconClass' => 'icon-edit navbar-brand',
                'neededPermission' => array('surveysettings', 'read')
            )),
            'surveySecurity' => new QuickMenuButton(array(
                'name' => 'surveySecurity',
                'href' => Yii::app()->getController()->createUrl("admin/surveypermission/sa/view/surveyid/$surveyId"),
                'tooltip' => gT('Survey permissions'),
                'iconClass' => 'icon-security navbar-brand',
                'neededPermission' => array('surveysecurity', 'read')
            )),
            'quotas' => new QuickMenuButton(array(
                'name' => 'quotas',
                'href' => Yii::app()->getController()->createUrl("admin/quotas/sa/view/surveyid/$surveyId"),
                'tooltip' => gT('Quotas'),
                'iconClass' => 'icon-quota navbar-brand',
                'neededPermission' => array('quotas', 'read')
            )),
            'assessments' => new QuickMenuButton(array(
                'name' => 'assessments',
                'href' => Yii::app()->getController()->createUrl("admin/assessments/sa/view/surveyid/$surveyId"),
                'tooltip' => gT('Assessments'),
                'iconClass' => 'icon-assessments navbar-brand',
                'neededPermission' => array('assessments', 'read')
            )),
            'emailTemplates' => new QuickMenuButton(array(
                'name' => 'emailTemplates',
                'href' => Yii::app()->getController()->createUrl("admin/emailtemplates/sa/view/surveyid/$surveyId"),
                'tooltip' => gT('E-mail templates'),
                'iconClass' => 'icon-emailtemplates navbar-brand',
                'neededPermission' => array('surveylocale', 'read')
            )),
            'surveyLogicFile' => new QuickMenuButton(array(
                'name' => 'surveyLogicFile',
                'href' => Yii::app()->getController()->createUrl("admin/expressions/sa/survey_logic_file/sid/$surveyId/"),
                'tooltip' => gT('Survey logic file'),
                'iconClass' => 'icon-expressionmanagercheck navbar-brand',
                'neededPermission' => array('surveycontent', 'read')
            )),
            'tokenManagement' => new QuickMenuButton(array(
                'name' => 'tokenManagement',
                'href' => Yii::app()->getController()->createUrl("admin/tokens/sa/index/surveyid/$surveyId"),
                'tooltip' => gT('Survey participants'),
                'iconClass' => 'glyphicon glyphicon-user navbar-brand',
                'neededPermission' => array('tokens', 'read')
            )),
            'cpdb' => new QuickMenuButton(array(
                'name' => 'cpdb',
                'href' => Yii::app()->getController()->createUrl("admin/participants/sa/displayParticipants"),
                'tooltip' => gT('Central participant database'),
                'iconClass' => 'fa fa-users navbar-brand',
                'neededPermission' => array('tokens', 'read')
            )),
            'responses' => new QuickMenuButton(array(
                'name' => 'responses',
                'href' => Yii::app()->getController()->createUrl("admin/responses/sa/browse/surveyid/$surveyId/"),
                'tooltip' => gT('Responses'),
                'iconClass' => 'icon-browse navbar-brand',
                'showOnlyWhenSurveyIsActivated' => true,
                'neededPermission' => array('responses', 'read')
            )),
            'statistics' => new QuickMenuButton(array(
                'name' => 'statistics',
                'href' => Yii::app()->getController()->createUrl("admin/responses/sa/browse/surveyid/$surveyId/"),
                'href' => Yii::app()->getController()->createUrl("admin/statistics/sa/index/surveyid/$surveyId"),
                'tooltip' => gT('Statistics'),
                'iconClass' => 'glyphicon glyphicon-stats navbar-brand',
                'showOnlyWhenSurveyIsActivated' => true,
                'neededPermission' => array('statistics', 'read')
            )),
            'reorder' => new QuickMenuButton(array(
                'name' => 'reorder',
                'href' =>Yii::app()->getController()->createUrl("admin/survey/sa/organize/surveyid/$surveyId"),
                'tooltip' => gT('Reorder questions/question groups'),
                'iconClass' => 'icon-organize',
                'showOnlyWhenSurveyIsDeactivated' => true,
                'neededPermission' => array('surveycontent', 'update')
            ))
        );

        // Central participant database
        /*
        $buttons[] = array(
            'openInNewTab' => false,
            'href' => Yii::app()->getController()->createUrl("admin/participants/sa/displayParticipants"),
            'tooltip' => gT('Central participant database'),
            'iconClass' => 'glyphicon TODO: Icon navbar-brand'
        );
         */
    }

    /**
     * Check if user has permission to show this button
     *
     * @param int $surveyId
     * @param QuickMenuButton $button
     * @return bool
     */
    private function hasPermission($surveyId, $button)
    {
        // Check for permission to show button
        if ($button['neededPermission'] !== null)
        {
            $hasPermission = Permission::model()->hasSurveyPermission(
                $surveyId,
                $button['neededPermission'][0],
                $button['neededPermission'][1]
            );

            return $hasPermission;
        }

        return true;
    }

    /**
     * Return list of buttons that will be shown for this page load
     *
     * @param int $surveyId
     * @param bool $activated - True if survey is activated
     * @param array $settings - Plugin settings
     * @return array<QuickMenuButton>
     */
    private function getButtonsToShow($surveyId, $activated, $settings)
    {
        $buttonsToShow = array();

        // Loop through all buttons and check settings and activation
        foreach ($this->buttons as $buttonName => $button)
        {
            if ($settings[$buttonName]['current'] === '1')
            {
                if (!$this->hasPermission($surveyId, $button))
                {
                    continue;
                }

                // Check if survey is active and whether or not to show button
                if ($button['showOnlyWhenSurveyIsActivated'] && $activated)
                {
                    $buttonsToShow[$buttonName] = $button;
                }
                elseif ($button['showOnlyWhenSurveyIsDeactivated'] && !$activated)
                {
                    $buttonsToShow[$buttonName] = $button;
                }
                elseif (!$button['showOnlyWhenSurveyIsActivated'] &&
                        !$button['showOnlyWhenSurveyIsDeactivated'])
                {
                    $buttonsToShow[$buttonName] = $button;
                }
            }
        }

        return $buttonsToShow;
    }

    public function afterQuickMenuLoad()
    {
        $event = $this->getEvent();
        $settings = $this->getPluginSettings(true);

        $data = $event->get('aData');
        $activated = $data['activated'];
        $surveyId = $data['surveyid'];

        $this->initialiseButtons($data);
        $buttonsToShow = $this->getButtonsToShow($surveyId, $activated, $settings);

        $userId = Yii::app()->user->getId();

        $buttonOrders = self::getOrder($userId);

        foreach ($buttonsToShow as $button)
        {
            if (isset($buttonOrders[$button['name']]))
            {
                $button->setOrder($buttonOrders[$button['name']]);
            }
        }

        $event->append('quickMenuItems', $buttonsToShow);
    }

    /**
     *  Save order after drag-n-drop sorting
     *
     *  @param LSHttpRequest $request
     *  @return void
     */
    public function saveOrder(LSHttpRequest $request)
    {
        $buttons = $request->getParam('buttons');

        $userId = Yii::app()->user->getId();

        try
        {
            $this->deleteOldSortings($userId);
            $this->insertNewSortings($userId, $buttons);
        }
        catch(Exception $ex)
        {
            // Any error is sent as JSON to client
            return json_encode(array(
                'result' => 'error',
                'message' => $ex->getMessage()
            ));
        }

        return json_encode(array('result' => 'success'));
    }

    /**
     * Delete all old button sortings for the user
     *
     * @param int $userId
     * @return void
     */
    protected function deleteOldSortings($userId)
    {
        // Delete all old sortings
        $tableName = '{{quickmenu_sortorder}}';  // TODO: Should not be hard-coded
        $db = Yii::app()->db;
        $db->createCommand()
            ->delete(
                $tableName,
                'uid=:uid',
                array(':uid' => $userId)
            );

    }

    /**
     * Insert new button sortings
     *
     * @param int $userId
     * @param array<string, int> $buttons - button name => sorting index
     */
    protected function insertNewSortings($userId, $buttons)
    {
        $db = Yii::app()->db;
        $tableName = '{{quickmenu_sortorder}}';  // TODO: Should not be hard-coded
        foreach ($buttons as $buttonName => $buttonIndex)
        {
            $db->createCommand()->insert(
                $tableName,
                array(
                    'uid' => $userId,
                    'button_name' => $buttonName,
                    'sort_order' => $buttonIndex)
            );
        }

    }

    /**
     * Get sort order of buttons from database
     *
     * @param int $userId
     * @return array
     */
    public static function getOrder($userId)
    {
        $tableName = '{{quickmenu_sortorder}}';  // TODO: Should not be hard-coded
        $db = Yii::app()->db;

        $tableSchema = $db->schema->getTable($tableName);

        // TODO: Should be handled by plugin version system
        if ($tableSchema === null)
        {
            Yii::app()->user->setFlash('error', 'Quick-menu plugin has been updated. Please deactivate and activate it again.');
            return;
        }

        $orders = $db->createCommand()
            ->select(array('button_name', 'sort_order'))
            ->from($tableName)
            ->where('uid=:uid', array(':uid' => $userId))
            ->order('sort_order')
            ->queryAll();
        $result = array();
        foreach ($orders as $ordering)
        {
            $result[$ordering['button_name']] = $ordering['sort_order'];
        }
        return $result;
    }

    public function newDirectRequest()
    {
        $user = $this->api->getCurrentUser();

        if ($user === false || $user === null)
        {
            throw new CException("Invalid request: user is not logged in or does not exist");
        }

        $event = $this->event;
        if ($event->get('target') == "QuickMenu")
        {
            // you can get other params from the request object
            $request = $event->get('request');

            $functionToCall = $event->get('function');

            if ($functionToCall == 'saveOrder')
            {
                echo $this->saveOrder($request);
            }
            else
            {
                throw new \CException("Invalid request: not supported method: " . $functionToCall);
            }
        }
    }
}
