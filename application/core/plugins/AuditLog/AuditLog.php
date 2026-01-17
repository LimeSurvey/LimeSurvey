<?php

class AuditLog extends \LimeSurvey\PluginManager\PluginBase
{
    protected $storage = 'DbStorage';
    protected static $description = 'Core: Create an audit log of changes';
    protected static $name = 'auditlog';

    /** @inheritdoc this plugin didn't have any public method */
    public $allowedPublicMethods = array();

    protected $settings = array(
        'AuditLog_Log_UserSave' => array(
            'type' => 'checkbox',
            'label' => 'Log if a user was modified or created',
            'default' => '1',
        ),
        'AuditLog_Log_UserLogin' => array(
            'type' => 'checkbox',
            'label' => 'Log if a user has logged in successfully',
            'default' => '1',
        ),
        'AuditLog_Log_UserLogout' => array(
            'type' => 'checkbox',
            'label' =>  'Log if user has logged out',
            'default' => '1',
        ),
        'AuditLog_Log_UserFailedLoginAttempt' => array(
            'type' => 'checkbox',
            'label' => 'Log if a user login has failed',
            'default' => '1',
        ),
        'AuditLog_Log_UserDelete' => array(
            'type' => 'checkbox',
            'label' => 'Log if a user was deleted',
            'default' => '1',
        ),
        'AuditLog_Log_DataEntryCreate' => array(
            'type' => 'checkbox',
            'label' => 'Log if a survey admin creates a response',
            'default' => '1',
        ),
        'AuditLog_Log_DataEntryUpdate' => array(
            'type' => 'checkbox',
            'label' => 'Log if a survey admin modifies a response',
            'default' => '1',
        ),
        'AuditLog_Log_DataEntryDelete' => array(
            'type' => 'checkbox',
            'label' => 'Log if a survey admin delete a response',
            'default' => '1',
        ),
        'AuditLog_Log_DataEntryImport' => array(
            'type' => 'checkbox',
            'label' => 'Log if a survey admin imports responses',
            'default' => '1',
        ),
        'AuditLog_Log_TokenSave' => array(
            'type' => 'checkbox',
            'label' => 'Log if a survey participant was modified or created',
            'default' => '1',
        ),
        'AuditLog_Log_TokenDelete' => array(
            'type' => 'checkbox',
            'label' => 'Log if a survey participant was deleted',
            'default' => '1',
        ),
        'AuditLog_Log_ParticipantSave' => array(
            'type' => 'checkbox',
            'label' => 'Log if a central database participant was modified or created',
            'default' => '1',
        ),
        'AuditLog_Log_ParticipantDelete' => array(
            'type' => 'checkbox',
            'label' => 'Log if a central database participant was deleted',
            'default' => '1',
        ),
        'AuditLog_Log_UserPermissionsChanged' => array(
            'type' => 'checkbox',
            'label' => 'Log if a user permissions changes',
            'default' => '1',
        ),
        'AuditLog_Log_SurveySettings' => array(
            'type' => 'checkbox',
            'label' => 'Log if a user changes survey settings',
            'default' => '1',
        ),
    );


    public function init()
    {
        $this->subscribe('beforeSurveySettings');
        $this->subscribe('newSurveySettings');
        $this->subscribe('beforeSurveySettingsSave');
        $this->subscribe('beforeActivate');
        $this->subscribe('beforeUserSave');
        $this->subscribe('beforeUserDelete');
        $this->subscribe('beforePermissionSetSave');
        $this->subscribe('beforeDataEntryCreate');
        $this->subscribe('beforeDataEntryUpdate');
        $this->subscribe('beforeDataEntryDelete');
        $this->subscribe('beforeDataEntryImport');
        $this->subscribe('beforeTokenSave');
        $this->subscribe('beforeTokenDelete');
        $this->subscribe('beforeTokenDeleteMany');
        $this->subscribe('beforeParticipantSave');
        $this->subscribe('beforeParticipantDelete');
        $this->subscribe('beforeLogout');
        $this->subscribe('afterSuccessfulLogin');
        $this->subscribe('afterFailedLoginAttempt');
    }

    /**
    * check for setting for a single operation event, login user, save or delete
    * @return boolean
    */
    private function checkSetting($settingName)
    {
        $pluginsettings = $this->getPluginSettings(true);
        // Logging will done if setted to true
        return $pluginsettings[$settingName]['current'] == 1;
    }


    /**
    * User logout to the audit log
    * @return unknown_type
    */
    public function beforeLogout()
    {
        if (!$this->checkSetting('AuditLog_Log_UserLogout')) {
            return;
        }
        $oUser = $this->api->getCurrentUser();
        if ($oUser != false) {
            $iUserID = $oUser->uid;
            $oAutoLog = $this->api->newModel($this, 'log');
            $oAutoLog->uid = $iUserID;
            $oAutoLog->entity = 'user';
            $oAutoLog->entityid = $iUserID;
            $oAutoLog->action = 'beforeLogout';
            $oAutoLog->save();
        }
    }

    /**
    * Successfull login to the audit log
    * @return unknown_type
    */
    public function afterSuccessfulLogin()
    {
        if (!$this->checkSetting('AuditLog_Log_UserLogin')) {
            return;
        }

        $iUserID = $this->api->getCurrentUser()->uid;
        $oAutoLog = $this->api->newModel($this, 'log');
        $oAutoLog->uid = $iUserID;
        $oAutoLog->entity = 'user';
        $oAutoLog->entityid = $iUserID;
        $oAutoLog->action = 'afterSuccessfulLogin';
        $oAutoLog->save();
    }

    /**
    * Failed login attempt to the audit log
    * @return unknown_type
    */
    public function afterFailedLoginAttempt()
    {
        if (!$this->checkSetting('AuditLog_Log_UserFailedLoginAttempt')) {
            return;
        }
        $event = $this->getEvent();
        $identity = $event->get('identity');
        $oAutoLog = $this->api->newModel($this, 'log');
        $oAutoLog->entity = 'user';
        $oAutoLog->action = 'afterFailedLoginAttempt';
        $aUsername['username'] = $identity->username;
        $oAutoLog->newvalues = json_encode($aUsername);
        $oAutoLog->save();
    }

    /**
    * Saves permissions changes to the audit log
    */
    public function beforePermissionSetSave()
    {

        if (!$this->checkSetting('AuditLog_Log_UserPermissionsChanged')) {
            return;
        }

        $event = $this->getEvent();
        $aNewPermissions = $event->get('aNewPermissions');
        $iSurveyID = $event->get('iSurveyID');
        $iUserID = $event->get('iUserID');
        $oCurrentUser = $this->api->getCurrentUser();
        $oOldPermission = $this->api->getPermissionSet($iUserID, $iSurveyID, 'Survey');
        $sAction = 'update';   // Permissions are in general only updated (either you have a permission or you don't)

        if (count(array_diff_assoc_recursive($aNewPermissions, $oOldPermission))) {
            $oAutoLog = $this->api->newModel($this, 'log');
            $oAutoLog->uid = $oCurrentUser->uid;
            $oAutoLog->entity = 'permission';
            $oAutoLog->entityid = $iSurveyID;
            $oAutoLog->action = $sAction;
            $oAutoLog->oldvalues = json_encode(array_diff_assoc_recursive($oOldPermission, $aNewPermissions));
            $oAutoLog->newvalues = json_encode(array_diff_assoc_recursive($aNewPermissions, $oOldPermission));
            $oAutoLog->fields = implode(',', array_keys(array_diff_assoc_recursive($aNewPermissions, $oOldPermission)));
            $oAutoLog->save();
        }
    }

    /**
    * Function catches if a response was created
    * @return unknown_type
    */
    public function beforeDataEntryCreate()
    {
        $event = $this->getEvent();
        $iSurveyID = $event->get('iSurveyID');
        if (!$this->checkSetting('AuditLog_Log_DataEntryCreate') || !$this->get('auditing', 'Survey', $iSurveyID, true)) {
            return;
        }

        $oCurrentUser = $this->api->getCurrentUser();
        $currentUID = $oCurrentUser ? $oCurrentUser->uid : null;

        $aValues = $event->get('oModel')->getAttributes();
        if (count($aValues)) {
            $oAutoLog = $this->api->newModel($this, 'log');
            $oAutoLog->uid = $currentUID;
            $oAutoLog->entity = 'responses_' . $iSurveyID;
            $oAutoLog->action = "create";
            $oAutoLog->newvalues = json_encode($aValues);
            $oAutoLog->save();
        }
    }

    /**
    * Function catches if a response was modified
    * @return unknown_type
    */
    public function beforeDataEntryUpdate()
    {
        $event = $this->getEvent();
        $iSurveyID = $event->get('iSurveyID');
        if (!$this->checkSetting('AuditLog_Log_DataEntryUpdate') || !$this->get('auditing', 'Survey', $iSurveyID, true)) {
            return;
        }

        $oCurrentUser = $this->api->getCurrentUser();
        $currentUID = $oCurrentUser ? $oCurrentUser->uid : null;
        $oldvalues = $this->api->getResponse($iSurveyID, $event->get('iResponseID'), false);

        $aDiffOld = array();
        $aDiffNew = array();
        foreach ($oldvalues->attributes as $aFieldName => $sValue) {
            $oldValue = $sValue;
            $newValue = App()->request->getPost($aFieldName);
            if ($oldValue != $newValue) {
                $aDiffOld[$aFieldName] = $oldValue;
                $aDiffNew[$aFieldName] = $newValue;
            }
        }

        if (count($aDiffOld)) {
            $oAutoLog = $this->api->newModel($this, 'log');
            $oAutoLog->uid = $currentUID;
            $oAutoLog->entity = 'responses_' . $iSurveyID;
            $oAutoLog->action = "update";
            $oAutoLog->entityid = $event->get('iResponseID');
            $oAutoLog->oldvalues = json_encode($aDiffOld);
            $oAutoLog->newvalues = json_encode($aDiffNew);
            $oAutoLog->fields = implode(',', array_keys($aDiffOld));
            $oAutoLog->save();
        }
    }

    /**
    * Function catches if a response was deleted
    * @return unknown_type
    */
    public function beforeDataEntryDelete()
    {
        $event = $this->getEvent();
        $iSurveyID = $event->get('iSurveyID');
        if (!$this->checkSetting('AuditLog_Log_DataEntryDelete') || !$this->get('auditing', 'Survey', $iSurveyID, true)) {
            return;
        }

        $oCurrentUser = $this->api->getCurrentUser();
        $currentUID = $oCurrentUser ? $oCurrentUser->uid : null;
        $oldvalues = $this->api->getResponse($iSurveyID, $event->get('iResponseID'), true);

        $oAutoLog = $this->api->newModel($this, 'log');
        $oAutoLog->uid = $currentUID;
        $oAutoLog->entity = 'responses_' . $iSurveyID;
        $oAutoLog->action = "delete";
        $oAutoLog->entityid = $event->get('iResponseID');
        $oAutoLog->oldvalues = json_encode($oldvalues);
        $oAutoLog->save();
    }

    /**
    * Log import responses
    * @return unknown_type
    */
    public function beforeDataEntryImport()
    {
        $event = $this->getEvent();
        $iSurveyID = $event->get('iSurveyID');
        if (!$this->checkSetting('AuditLog_Log_DataEntryImport') || !$this->get('auditing', 'Survey', $iSurveyID, true)) {
            return;
        }

        $oCurrentUser = $this->api->getCurrentUser();
        $currentUID = $oCurrentUser ? $oCurrentUser->uid : null;

        $oModel = $this->getEvent()->get('oModel');
        $aValues = $oModel->getAttributes();
        if (count($aValues)) {
            $oAutoLog = $this->api->newModel($this, 'log');
            $oAutoLog->uid = $currentUID;
            $oAutoLog->entity = 'responses_' . $iSurveyID;
            $oAutoLog->action = "import";
            $oAutoLog->newvalues = json_encode($aValues);
            $oAutoLog->fields = implode(',', array_keys($aValues));
            $oAutoLog->save();
        }
    }

    /**
    * Function catches if a participant of a particular survey was modified or created
    * All data is saved - only the password hash is anonymized for security reasons
    */
    public function beforeTokenSave()
    {
        $event = $this->getEvent();
        $iSurveyID = $event->get('iSurveyID');
        if (!$this->checkSetting('AuditLog_Log_TokenSave') || !$this->get('auditing', 'Survey', $iSurveyID, true)) {
            return;
        }

        $oNewParticipant = $this->getEvent()->get('model');
        $oCurrentUser = $this->api->getCurrentUser();
        $currentUID = $oCurrentUser ? $oCurrentUser->uid : null;
        if ($oNewParticipant->isNewRecord) {
            $sAction = 'create';
            $oldvalues = array();
        } else {
            $sAction = 'update';
            $oldvalues = $this->api->getTokenById($iSurveyID, $oNewParticipant->tid)->getAttributes();
        }

        $newValues = $oNewParticipant->getAttributes();

        if (count(array_diff_assoc($newValues, $oldvalues))) {
            $oAutoLog = $this->api->newModel($this, 'log');
            $oAutoLog->uid = $currentUID;
            $oAutoLog->entity = 'token_' . $iSurveyID;
            $oAutoLog->action = $sAction;
            $oAutoLog->entityid = $newValues['tid'];
            $oAutoLog->oldvalues = json_encode(array_diff_assoc($oldvalues, $newValues));
            $oAutoLog->newvalues = json_encode(array_diff_assoc($newValues, $oldvalues));
            $oAutoLog->fields = implode(',', array_keys(array_diff_assoc($newValues, $oldvalues)));
            $oAutoLog->save();
        }
    }

    /**
    * Function catches if a participant of a particular survey was deleted
    * All data is saved
    */
    public function beforeTokenDelete()
    {
        $event = $this->getEvent();
        $iSurveyID = $event->get('iSurveyID');
        if (!$this->checkSetting('AuditLog_Log_TokenDelete') || !$this->get('auditing', 'Survey', $iSurveyID, true)) {
            return;
        }

        // beforeTokenDelete mutated through time.
        // At the very begining, the event was dispatched with an sTokenIds parameter.
        // Then, dynamic model events were introduced, and this event mutated its interface.
        // The code below accepts both kinds of interface.
        $sTokenIds = $event->get('sTokenIds');
        if (!empty($sTokenIds)) {
            $aTokenIds = explode(',', (string) $sTokenIds);
        } else {
            // If sTokenIds is empty, assume we're dealing with a dynamic model event.
            // In this case, the dynamicId parameter contains the token ID.
            $aTokenIds = [$event->get('dynamicId')];
        }
        if (empty($aTokenIds)) {
            return;
        }
        $oCurrentUser = $this->api->getCurrentUser();

        foreach ($aTokenIds as $tokenId) {
            $token = Token::model($iSurveyID)->find('tid=' . $tokenId);

            if (!is_null($token)) {
                $aValues = $token->getAttributes();
                $oAutoLog = $this->api->newModel($this, 'log');
                $oAutoLog->uid = $oCurrentUser->uid;
                $oAutoLog->entity = 'token';
                $oAutoLog->action = 'delete';
                $oAutoLog->entityid = $aValues['tid'];
                $oAutoLog->oldvalues = json_encode($aValues);
                $oAutoLog->fields = implode(',', array_keys($aValues));
                $oAutoLog->save();
            }
        }
    }

    /**
    * Function catches if multiple participants of a particular survey were deleted
    * All data is saved
    */
    public function beforeTokenDeleteMany()
    {
        $event = $this->getEvent();
        $surveyId = $event->get('iSurveyID');
        if (!$this->checkSetting('AuditLog_Log_TokenDelete') || !$this->get('auditing', 'Survey', $surveyId, true)) {
            return;
        }

        $filterCriteria = $event->get('filterCriteria');

        // We need to "fix" (update) the criteria given by parameter.
        // - SELECT queries are built with the table alias.
        // - DELETE queries are not.
        // We are given a DELETE query criteria and need to use it on a SELECT query,
        // so we replace the table name with the alias.
        $tokenModel = Token::model($surveyId);
        $selectCriteria = clone $filterCriteria;
        $tableName = $tokenModel->getTableSchema()->rawName;
        $alias = $tokenModel->getTableAlias(true);
        // Replace the table name with the alias
        $selectCriteria->condition = str_replace($tableName, $alias, $selectCriteria->condition);

        $tokens = $tokenModel->findAll($selectCriteria);

        $oCurrentUser = $this->api->getCurrentUser();

        foreach ($tokens as $token) {
            $aValues = $token->getAttributes();
            $oAutoLog = $this->api->newModel($this, 'log');
            $oAutoLog->uid = $oCurrentUser->uid;
            $oAutoLog->entity = 'token';
            $oAutoLog->action = 'delete';
            $oAutoLog->entityid = $aValues['tid'];
            $oAutoLog->oldvalues = json_encode($aValues);
            $oAutoLog->fields = implode(',', array_keys($aValues));
            $oAutoLog->save();
        }
    }

    /**
    * Function catches if a central database participant was modified or created
    * All data is saved - only the password hash is anonymized for security reasons
    */
    public function beforeParticipantSave()
    {
        if (!$this->checkSetting('AuditLog_Log_ParticipantSave')) {
            return;
        }
        $oNewParticipant = $this->getEvent()->get('model');
        if ($oNewParticipant->isNewRecord) {
            $sAction = 'create';
            $aOldValues = array();
        } else {
            $sAction = 'update';
            $aOldValues = $this->api->getParticipant($oNewParticipant->participant_id)->getAttributes();
        }
        $oCurrentUser = $this->api->getCurrentUser();
        $aNewValues = $oNewParticipant->getAttributes();
        if (count(array_diff_assoc($aNewValues, $aOldValues))) {
            $oAutoLog = $this->api->newModel($this, 'log');
            $oAutoLog->uid = $oCurrentUser->uid;
            $oAutoLog->entity = 'participant';
            $oAutoLog->action = $sAction;
            $oAutoLog->entityid = $aNewValues['participant_id'];
            $oAutoLog->oldvalues = json_encode(array_diff_assoc($aOldValues, $aNewValues));
            $oAutoLog->newvalues = json_encode(array_diff_assoc($aNewValues, $aOldValues));
            $oAutoLog->fields = implode(',', array_keys(array_diff_assoc($aNewValues, $aOldValues)));
            $oAutoLog->save();
        }
    }

    /**
    * Function catches if a central database participant was modified or created
    * All data is saved - only the password hash is anonymized for security reasons
    */
    public function beforeParticipantDelete()
    {
        if (!$this->checkSetting('AuditLog_Log_ParticipantDelete')) {
            return;
        }
        $oNewParticipant = $this->getEvent()->get('model');
        $oCurrentUser = $this->api->getCurrentUser();

        $aValues = $oNewParticipant->getAttributes();

        $oAutoLog = $this->api->newModel($this, 'log');
        $oAutoLog->uid = $oCurrentUser->uid;
        $oAutoLog->entity = 'participant';
        $oAutoLog->action = 'delete';
        $oAutoLog->entityid = $aValues['participant_id'];
        $oAutoLog->oldvalues = json_encode($aValues);
        $oAutoLog->fields = implode(',', array_keys($aValues));
        $oAutoLog->save();
    }

    /**
    * Function catches if a user was modified or created
    * All data is saved - only the password hash is anonymized for security reasons
    */
    public function beforeUserSave()
    {

        if (!$this->checkSetting('AuditLog_Log_UserSave')) {
            return;
        }
        $oUserData = $this->getEvent()->get('model');

        $oCurrentUser = $this->api->getCurrentUser();

        $aNewValues = $oUserData->getAttributes();
        if (!isset($oUserData->uid)) {
            $sAction = 'create';
            $aOldValues = array();
            // Indicate the password has changed but assign fake hash
            $aNewValues['password'] = '*MASKED*PASSWORD*';
        } else {
            $oOldUser = $this->api->getUser($oUserData->uid);
            $sAction = 'update';
            $aOldValues = $oOldUser->getAttributes();

            // Postgres delivers bytea fields as streams
            if (gettype($aOldValues['password']) == 'resource') {
                $aOldValues['password'] = stream_get_contents($aOldValues['password']);
            }
            // If the password has changed then indicate that it has changed but assign fake hashes
            if ($aNewValues['password'] != $aOldValues['password']) {
                $aOldValues['password'] = '*MASKED*OLD*PASSWORD*';
                $aNewValues['password'] = '*MASKED*NEW*PASSWORD*';
            }
        }

        if (count(array_diff_assoc($aNewValues, $aOldValues))) {
            $oAutoLog = $this->api->newModel($this, 'log');
            if ($oCurrentUser) {
                $oAutoLog->uid = $oCurrentUser->uid;
            } else {
                $oAutoLog->uid = 'Automatic creation';
            }
            $oAutoLog->entity = 'user';
            if ($sAction == 'update') {
                $oAutoLog->entityid = $oOldUser['uid'];
            }
            $oAutoLog->action = $sAction;
            $oAutoLog->oldvalues = json_encode(array_diff_assoc($aOldValues, $aNewValues));
            $oAutoLog->newvalues = json_encode(array_diff_assoc($aNewValues, $aOldValues));
            $oAutoLog->fields = implode(',', array_keys(array_diff_assoc($aNewValues, $aOldValues)));
            $oAutoLog->save();
        }
    }

    /**
    * Function catches if a user was deleted
    * All data is saved - only the password hash is anonymized for security reasons
    */
    public function beforeUserDelete()
    {
        if (!$this->checkSetting('AuditLog_Log_UserDelete')) {
            return;
        }

        $oUserData = $this->getEvent()->get('model');
        $oCurrentUser = $this->api->getCurrentUser();
        $oOldUser = $this->api->getUser($oUserData->uid);
        if ($oOldUser) {
            $aOldValues = $oOldUser->getAttributes();
            unset($aOldValues['password']);
            $oAutoLog = $this->api->newModel($this, 'log');
            $oAutoLog->uid = $oCurrentUser->uid;
            $oAutoLog->entity = 'user';
            $oAutoLog->entityid = $oOldUser['uid'];
            $oAutoLog->action = 'delete';
            $oAutoLog->oldvalues = json_encode($aOldValues);
            $oAutoLog->fields = implode(',', array_keys($aOldValues));
            $oAutoLog->save();
        }
    }

    public function beforeActivate()
    {
        if (!$this->api->tableExists($this, 'log')) {
            $options = '';
            if (Yii::app()->db->driverName == 'mysqli' || Yii::app()->db->driverName == 'mysql') {
                $options .= sprintf(" ENGINE = %s ", Yii::app()->getConfig('mysqlEngine'));
            }
            $this->api->createTable($this, 'log', array('id' => 'pk',
                'created' => 'datetime',
                'uid' => 'string',
                'entity' => 'string',
                'entityid' => 'string',
                'action' => 'string',
                'fields' => 'text',
                'oldvalues' => 'text',
                'newvalues' => 'text'), $options);
        }
    }

    /**
    * This event is fired by the administration panel to gather extra settings
    * available for a survey.
    * The plugin should return setting meta data.
    */
    public function beforeSurveySettings()
    {
        $pluginsettings = $this->getPluginSettings(true);

        $event = $this->getEvent();
        $event->set("surveysettings.{$this->id}", array(
            'name' => get_class($this),
            'settings' => array(
                'auditing' => array(
                    'type' => 'select',
                    'options' => array(0 => 'No',
                        1 => 'Yes'),
                    'default' => 1,
                    'tab' => 'notification', // @todo: Setting no used yet
                    'category' => 'Auditing for person-related data', // @todo: Setting no used yet
                    'label' => 'Audit log for this survey:',
                    'current' => $this->get('auditing', 'Survey', $event->get('survey'))
                )
            )
        ));
    }

    public function newSurveySettings()
    {
        $event = $this->getEvent();
        foreach ($event->get('settings') as $name => $value) {
                $this->set($name, $value, 'Survey', $event->get('survey'));
        }
    }

    public function beforeSurveySettingsSave()
    {
        $event = $this->getEvent();
        $oModifiedSurvey = $event->get('modifiedSurvey');
        $iSurveyID = $oModifiedSurvey->sid;
        if (!$this->checkSetting('AuditLog_Log_SurveySettings') || !$this->get('auditing', 'Survey', $iSurveyID, true)) {
            return;
        }

        $oCurrentUser = $this->api->getCurrentUser();
        if (!is_null($oModifiedSurvey)) {
            $newAttributes = $oModifiedSurvey->getAttributes();
            $oldSurvey = Survey::model()->find('sid = :sid', array(':sid' => $iSurveyID));

            $oldAttributes = $oldSurvey->getAttributes();
            $diff = array_diff_assoc($newAttributes, $oldAttributes);
            if (count($diff) > 0) {
                $oAutoLog = $this->api->newModel($this, 'log');
                $oAutoLog->uid = $oCurrentUser->uid;
                $oAutoLog->entity = 'survey';
                $oAutoLog->entityid = $iSurveyID;
                $oAutoLog->action = 'update';
                $oAutoLog->oldvalues = json_encode(array_diff_assoc($oldAttributes, $newAttributes));
                $oAutoLog->newvalues = json_encode($diff);
                #$oAutoLog->fields=json_encode($diff);
                $oAutoLog->fields = implode(',', array_keys($diff));
                $oAutoLog->save();
            }
        }
    }
}
