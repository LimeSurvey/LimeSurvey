<?php

/*
 * LimeSurvey
 * Copyright (C) 2013 The LimeSurvey Project Team / Carsten Schmitz
 * All rights reserved.
 * License: GNU/GPL License v2 or later, see LICENSE.php
 * LimeSurvey is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 *
 */

/**
 * Class Permission
 *
 * @property integer $id
 * @property string $entity
 * @property integer $entity_id
 * @property integer $uid
 * @property string $permission
 * @property integer $create_p
 * @property integer $read_p
 * @property integer $update_p
 * @property integer $delete_p
 * @property integer $import_p
 * @property integer $export_p
 *
 *
 */
class Permission extends LSActiveRecord
{
    /* @var array[]|null The global base Permission LimeSurvey installation */
    protected static $aGlobalBasePermissions;

    /* @var array[] The already loaded survey permissions */
    protected static $aCachedSurveyPermissions = [];

    /** @inheritdoc */
    public function tableName()
    {
        return '{{permissions}}';
    }

    /**
     * Returns the static model of Settings table
     *
     * @static
     * @access public
     * @param string $class
     * @return Permission
     */
    public static function model($className = __CLASS__)
    {
        /** @var self $model */
        $model = parent::model($className);
        return $model;
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        return array(
            array('entity, entity_id, uid, permission', 'required'),
            array('entity', 'length', 'max' => 50),
            array('entity',  'LSYii_FilterValidator', 'filter' => 'strtolower', 'skipOnEmpty' => true),
            array('permission', 'length', 'max' => 100),
            array('create_p, read_p, update_p, delete_p, import_p, export_p', 'default', 'value' => 0),
            array('create_p, read_p, update_p, delete_p, import_p, export_p', 'numerical', 'integerOnly' => true),
        );
    }

    /**
     * @inheritdoc
     */
    public function relations()
    {
        return [
            'user' => array(self::BELONGS_TO, 'User', 'uid'),
        ];
    }

    /**
     * Returns the base permissions for survey
     * @see self::getEntityBasePermissions
     *
     * @return array
     */
    public static function getSurveyBasePermissions()
    {
        return self::getEntityBasePermissions('Survey');
    }

    /**
     * Return Permission for an object, using object::getPermissionData directly
     * @param string $sEntityName must be an existing object child of LSActiveRecord
     * @return array of permission : each permission with array of available crud
     */
    public static function getEntityBasePermissions($sEntityName)
    {
        /* @todo : check $sEntityName implement PermissionInterface */
        $defaults = array(
            'create' => true,
            'read' => true,
            'update' => true,
            'delete' => true,
            'import' => true,
            'export' => true,
        );

        $aPermissions = $sEntityName::getPermissionData();
        uasort($aPermissions, array(__CLASS__, "comparePermissionTitle"));
        foreach ($aPermissions as &$permission) {
            $permission = array_merge($defaults, $permission);
        }
        return $aPermissions;
    }

    /**
     * Return minimal permission name (for read value)
     * @param string $sEntityName must be an existing object child of LSActiveRecord
     * @return null|string
     */
    public static function getEntityMinimalPermissionRead($sEntityName)
    {
        /* @todo : check $sEntityName implement PermissionInterface */
        return $sEntityName::getMinimalPermissionRead();
    }

    /**
     * Returns the global permissions including description and title
     *
     * @return array of array of permission
     */
    public static function getGlobalBasePermissions()
    {
        if (self::$aGlobalBasePermissions) {
            return self::$aGlobalBasePermissions;
        }
        $defaults = array(
            'create' => true,
            'read' => true,
            'update' => true,
            'delete' => true,
            'import' => true,
            'export' => true,
        );
        $aPermissions = self::getGlobalPermissionData();

        uasort($aPermissions, array(__CLASS__, "comparePermissionTitle"));
        $aPermissions['superadmin'] = array(
            'create' => true, // Currently : is set/unset tis Permission to other user's
            'update' => false,
            'delete' => false,
            'import' => false,
            'export' => false,
            'title' => gT("Superadministrator"),
            'description' => gT("Unlimited administration permissions"),
            'warning' => gT("This setting allows an admin to perform all actions. Please make sure to assign this only to trusted persons."),
            'img' => 'ri-star-fill',
        );
        $aPermissions['auth_db'] = array(
            'create' => false,
            'update' => false,
            'delete' => false,
            'import' => false,
            'export' => false,
            'title' => gT("Database authentication"),
            'description' => gT("Database authentication"),
            'img' => 'ri-user-settings-fill',
        );

        /**
         * New event to allow plugin to add own global permission
         * Using $event->append('globalBasePermissions', $newGlobalBasePermissions);
         * $newGlobalBasePermissions=[
         *  permissionName=>[
         *       'create' : create (optional)
         *       'read' : read (optional)
         *       'update' : update (optional)
         *       'delete' : delete (optional)
         *       'import' : import (optional)
         *       'export' : export (optional)
         *       'title' : translated title/name
         *       'description' : translated description
         *       'img': icon name class
         *  ]
         */
        $event = new \LimeSurvey\PluginManager\PluginEvent('getGlobalBasePermissions');
        $result = App()->getPluginManager()->dispatchEvent($event);
        $aPluginPermissions = (array) $result->get('globalBasePermissions');
        $aPermissions = array_merge($aPermissions, $aPluginPermissions);

        foreach ($aPermissions as &$permission) {
            $permission = array_merge($defaults, $permission);
        }
        self::$aGlobalBasePermissions = $aPermissions;
        return self::$aGlobalBasePermissions;
    }

    /**
     * get current permissions list
     * Seems used in LimeSurvey\PluginManager\LimesurveyApi->getPermissionSet
     * @param integer $iUserID
     * @param integer $iEntityID
     * @param string $sEntityName
     * @return array
     */
    public static function getPermissions($iUserID, $iEntityID = null, $sEntityName = null)
    {
        $aBasePermissions = array();
        if (is_null($sEntityName)) {
            $oPermissions = Permission::model()->findAllByAttributes(array('uid' => $iUserID));
            $aBasePermissions = array();
            foreach ($oPermissions as $oPermission) {
                $aBasePermissions[$oPermission->id] = $oPermission->attributes;
            }
            return $aBasePermissions;
        }
        if ($sEntityName == 'global') {
            $aBasePermissions = Permission::model()->getGlobalBasePermissions();
        } else {
            $aBasePermissions = Permission::model()->getEntityBasePermissions($sEntityName);
        }

        foreach ($aBasePermissions as $sPermission => &$aPermissionDetail) {
            $oCurrentPermissions = Permission::model()->findByAttributes(array(
                'uid' => $iUserID,
                'entity' => $sEntityName,
                'entity_id' => $iEntityID,
                'permission' => $sPermission
            ));
            if ($aPermissionDetail['create']) {
                $aPermissionDetail['create'] = ($oCurrentPermissions ? (bool) $oCurrentPermissions->create_p : false);
            }
            if ($aPermissionDetail['read']) {
                $aPermissionDetail['read'] = ($oCurrentPermissions ? (bool) $oCurrentPermissions->read_p : false);
            }
            if ($aPermissionDetail['update']) {
                $aPermissionDetail['update'] = ($oCurrentPermissions ? (bool) $oCurrentPermissions->update_p : false);
            }
            if ($aPermissionDetail['delete']) {
                $aPermissionDetail['delete'] = ($oCurrentPermissions ? (bool) $oCurrentPermissions->delete_p : false);
            }
            if ($aPermissionDetail['import']) {
                $aPermissionDetail['import'] = ($oCurrentPermissions ? (bool) $oCurrentPermissions->import_p : false);
            }
            if ($aPermissionDetail['export']) {
                $aPermissionDetail['export'] = ($oCurrentPermissions ? (bool) $oCurrentPermissions->export_p : false);
            }
        }
        return $aBasePermissions;
    }

    /**
     * Sets permissions (global or survey-specific) for a survey administrator
     * Checks what permissions may be set and automatically filters invalid ones.
     * A permission may be invalid if the permission does not exist or that particular user may not give that permission
     * @deprecated : usage only for global Permission currently
     *
     * @param mixed $iUserID
     * @param mixed $iEntityID
     * @param string $sEntityName
     * @param mixed $aPermissions
     * @param boolean $bBypassCheck : by pass control of current permission for current user only for global permission
     * @throw Exception
     * @return null|boolean
     */
    public static function setPermissions($iUserID, $iEntityID, $sEntityName, $aPermissions, $bBypassCheck = false)
    {
        $iUserID = sanitize_int($iUserID);
        $aBasePermissions = array();
        // Filter global permissions on save
        if ($sEntityName == 'global') {
            $aBasePermissions = Permission::model()->getGlobalBasePermissions();
            // if not superadmin filter the available permissions as no admin may give more permissions than he owns
            if (!Permission::model()->hasGlobalPermission('superadmin', 'read') && !$bBypassCheck) {
                // Make sure that he owns the user he wants to give global permissions for
                $oUser = User::model()->findByAttributes(array('uid' => $iUserID, 'parent_id' => Yii::app()->session['loginID']));
                if (!$oUser) {
                    die('You are not allowed to set permisisons for this user');
                }
                $aFilteredPermissions = array();
                foreach ($aBasePermissions as $PermissionName => $aPermission) {
                    foreach ($aPermission as $sPermissionKey => &$sPermissionValue) {
                        if ($sPermissionKey != 'title' && $sPermissionKey != 'img' && !Permission::model()->hasGlobalPermission($PermissionName, $sPermissionKey)) {
                            $sPermissionValue = false;
                        }
                    }
                    // Only have a row for that permission if there is at least one permission he may give to other users
                    if ($aPermission['create'] || $aPermission['read'] || $aPermission['update'] || $aPermission['delete'] || $aPermission['import'] || $aPermission['export']) {
                        $aFilteredPermissions[$PermissionName] = $aPermission;
                    }
                }
                $aBasePermissions = $aFilteredPermissions;
            } elseif (!Permission::model()->hasGlobalPermission('superadmin', 'create')) {
                unset($aBasePermissions['superadmin']);
            }
        } else {
            if (in_array("PermissionInterface", class_implements($sEntityName))) {
                /* model implement \PermissionInterface */
                throw new Exception("Must use PermissionManager service");
            }
            $aBasePermissions = Permission::model()->getEntityBasePermissions($sEntityName);
        }

        $aFilteredPermissions = array();
        foreach ($aBasePermissions as $sPermissionname => $aPermission) {
            $aFilteredPermissions[$sPermissionname]['create'] = (isset($aPermissions[$sPermissionname]['create']) && $aPermissions[$sPermissionname]['create']);
            $aFilteredPermissions[$sPermissionname]['read'] = (isset($aPermissions[$sPermissionname]['read']) && $aPermissions[$sPermissionname]['read']);
            $aFilteredPermissions[$sPermissionname]['update'] = (isset($aPermissions[$sPermissionname]['update']) && $aPermissions[$sPermissionname]['update']);
            $aFilteredPermissions[$sPermissionname]['delete'] = (isset($aPermissions[$sPermissionname]['delete']) && $aPermissions[$sPermissionname]['delete']);
            $aFilteredPermissions[$sPermissionname]['import'] = (isset($aPermissions[$sPermissionname]['import']) && $aPermissions[$sPermissionname]['import']);
            $aFilteredPermissions[$sPermissionname]['export'] = (isset($aPermissions[$sPermissionname]['export']) && $aPermissions[$sPermissionname]['export']);
        }
        $condition = array(
            'entity' => $sEntityName,
            'entity_id' => $iEntityID,
            'uid' => $iUserID
        );
        $oEvent = new \LimeSurvey\PluginManager\PluginEvent('beforePermissionSetSave');
        $oEvent->set('aNewPermissions', $aFilteredPermissions);
        $oEvent->set('iSurveyID', $iEntityID);
        $oEvent->set('entity', $sEntityName); /* New in 4.4.X */
        $oEvent->set('entityId', $iEntityID); /* New in 4.4.X */
        $oEvent->set('iUserID', $iUserID);
        App()->getPluginManager()->dispatchEvent($oEvent);

        if (!Permission::model()->hasGlobalPermission('superadmin', 'create')) {
            Permission::model()->deleteAllByAttributes($condition, "permission <> 'superadmin'");
        } else {
            Permission::model()->deleteAllByAttributes($condition);
        }

        foreach ($aFilteredPermissions as $sPermissionname => $aPermission) {
            /* @todo : review this : any user with security update can delete or add any other permission, must be limited to own permission */
            /* @see https://bugs.limesurvey.org/view.php?id=14551 */
            /* Move to : search or create, and update after */
            if ($aPermission['create'] || $aPermission['read'] || $aPermission['update'] || $aPermission['delete'] || $aPermission['import'] || $aPermission['export']) {
                $data = array(
                    'entity_id' => $iEntityID,
                    'entity' => $sEntityName,
                    'uid' => $iUserID,
                    'permission' => $sPermissionname,
                    'create_p' => (int) $aPermission['create'],
                    'read_p' => (int) $aPermission['read'],
                    'update_p' => (int) $aPermission['update'],
                    'delete_p' => (int) $aPermission['delete'],
                    'import_p' => (int) $aPermission['import'],
                    'export_p' => (int) $aPermission['export'],
                );
                $permission = new self();
                foreach ($data as $k => $v) {
                    $permission->$k = $v;
                }
                $permission->save();
            }
        }
        if ($sEntityName != 'global') {
            self::setMinimalEntityPermission($iUserID, $iEntityID, $sEntityName);
        }
        return true;
    }

    /**
     * Set global permissions to the user id
     *
     * @param int $iUserID the user id
     * @param mixed $iEntityID the entity id
     * @param string $sEntityName  the entity name (Object)
     * @return null|self::model()
     */
    public static function setMinimalEntityPermission($iUserID, $iEntityID, $sEntityName)
    {
        $sEntityName = strtolower($sEntityName);
        $sPermission = self::getEntityMinimalPermissionRead($sEntityName);
        if (!$sPermission) {
            return null;
        }
        $oPermission = Permission::model()->find(
            "uid= :uid AND entity = :entity AND entity_id = :entity_id AND permission = :permission",
            array(
                'uid' => $iUserID,
                'entity' => $sEntityName,
                'entity_id' => $iEntityID,
                'permission' => $sPermission,
            )
        );
        if (empty($oPermission)) {
            $oPermission = new Permission();
            $oPermission->uid = $iUserID;
            $oPermission->entity = $sEntityName;
            $oPermission->entity_id = $iEntityID;
            $oPermission->permission = $sPermission;
        }
        $oPermission->read_p = 1;
        $oPermission->save();
        return $oPermission;
    }

    /**
     * Set global permissions to the user id
     *
     * @param int $iNewUID
     * @param string[] $aPermissions
     * @param string $sPermType
     */
    public function setGlobalPermission($iNewUID, $sPermType, array $aPermissions = array('read_p'))
    {
        $aPerm = array(
            'entity_id' => 0,
            'entity' => 'global',
            'uid' => $iNewUID,
            'permission' => $sPermType,
            'create_p' => 0,
            'read_p' => 0,
            'update_p' => 0,
            'delete_p' => 0,
            'import_p' => 0,
            'export_p' => 0,
        );

        foreach ($aPermissions as $sPermType) {
            $aPerm[$sPermType] = 1;
        }

        $this->insertSomeRecords($aPerm);
    }

    /**
     * Give all permission of a specific user without permission control of current user
     * Used when create survey
     * @see mantis #16967: https://bugs.limesurvey.org/view.php?id=16967
     * @param integer $iUserID
     * @param integer $iSurveyID
     */
    public function giveAllSurveyPermissions($iUserID, $iSurveyID)
    {
        if ($iSurveyID == 0) {
            throw new InvalidArgumentException('Survey ID cannot be 0 (collides with superadmin permission entity id)');
        }
        $aPermissions = Survey::getPermissionData();
        $aCrud = array('create', 'read', 'update', 'delete', 'import', 'export');
        foreach ($aPermissions as $sPermissionName => $aPermissionDetails) {
            $oPermission = Permission::model()->findByAttributes(array(
                'entity' => 'survey',
                'entity_id' => $iSurveyID,
                'uid' => $iUserID,
                'permission' => $sPermissionName
            ));
            if (empty($oPermission)) {
                $oPermission = new Permission();
                $oPermission->entity = 'survey';
                $oPermission->entity_id = $iSurveyID;
                $oPermission->uid = $iUserID;
                $oPermission->permission = $sPermissionName;
            }
            foreach ($aCrud as $crud) {
                if (!isset($aPermissionDetails[$crud]) || $aPermissionDetails[$crud]) {
                    $oPermission->setAttribute($crud . "_p", 1);
                }
            }
            $oPermission->save();
        }
    }

    /**
     * @param array $data
     * @deprecated at 2018-01-29 use $model->attributes = $data && $model->save()
     */
    public function insertRecords($data)
    {
        foreach ($data as $item) {
            $this->insertSomeRecords($item);
        }
    }

    /**
     * @param array $data
     * @return bool
     */
    public function insertSomeRecords($data)
    {
        $permission = new self();
        foreach ($data as $k => $v) {
            $permission->$k = $v;
        }
        return $permission->save();
    }

    /**
     * @param integer $iSurveyIDSource
     * @param integer $iSurveyIDTarget
     */
    public function copySurveyPermissions($iSurveyIDSource, $iSurveyIDTarget)
    {
        $aRows = self::model()->findAll("entity_id=:sid AND entity='survey'", array(':sid' => $iSurveyIDSource));
        foreach ($aRows as $aRow) {
            $aRow = $aRow->getAttributes();
            $aRow['entity_id'] = $iSurveyIDTarget; // Set the new survey ID
            unset($aRow['id']); // To insert, we reset the id
            try {
                $this->insertSomeRecords($aRow);
            } catch (Exception $e) {
                //Ignore
            }
        }
    }

    /**
     * Checks if a user has a certain permission
     *
     * @param $iEntityID integer The entity ID
     * @param string $sEntityName string The entity name
     * @param $sPermission string Name of the permission
     * @param $sCRUD string The permission detail you want to check on: 'create','read','update','delete','import' or 'export'
     * @param $iUserID integer User ID - if empty : use the current user
     * @return bool True if user has the permission
     */
    public function hasPermission($iEntityID, $sEntityName, $sPermission, $sCRUD = 'read', $iUserID = null)
    {
        // TODO: in entry script, if CConsoleApplication, set user as superadmin
        if (is_null($iUserID) && Yii::app() instanceof CConsoleApplication) {
            return true;
        }
        static $aPermissionStatic;

        /* Allow plugin to set own permission */
        // TODO: plugin should not be able to override the permission system (security issue),
        //      they should read permissions via the model
        //      and they should add row in permission table  (entity = plugin, etc)
        $sEntityName = strtolower($sEntityName);
        $oEvent = new \LimeSurvey\PluginManager\PluginEvent('beforeHasPermission');
        $oEvent->set('iEntityID', $iEntityID);
        $oEvent->set('sEntityName', $sEntityName);
        $oEvent->set('sPermission', $sPermission);
        $oEvent->set('sCRUD', $sCRUD);
        $oEvent->set('iUserID', $iUserID);
        App()->getPluginManager()->dispatchEvent($oEvent);
        $pluginbPermission = $oEvent->get('bPermission');

        if (isset($pluginbPermission)) {
            return $pluginbPermission;
        }

        /* Always return true for CConsoleApplication (before or after plugin ? All other seems better after plugin) */
        // TODO: see above about entry script and superadmin
        if (empty($iUserID) && Yii::app() instanceof CConsoleApplication) {
            return true;
        }

        /* Always return false for unknow sCRUD */
        // TODO: should not be necessary
        if (!in_array($sCRUD, array('create', 'read', 'update', 'delete', 'import', 'export'))) {
            return false;
        }
        $sCRUD = $sCRUD . '_p';

        /* Be sure to have an user id */
        $iUserID = $this->getUserId($iUserID);
        /* Always return false for guests */
        if (empty($iUserID)) {
            return false;
        }

        /* Always return true if user are the owner of entity*/
        if ($iUserID == $this->getEntityOwnerId($iEntityID, $sEntityName)) {
            return true;
        }

        /* Check if superadmin and static it */
        if (!isset($aPermissionStatic[0]['global'][$iUserID]['superadmin']['read_p'])) {
            $aPermission = $this->findByAttributes(array("entity_id" => 0, 'entity' => 'global', "uid" => $iUserID, "permission" => 'superadmin'));
            $bPermission = is_null($aPermission) ? array() : $aPermission->attributes;
            $aPermissionStatic[0]['global'][$iUserID]['superadmin'] = array_merge(
                array(
                    'create_p' => false,
                    'read_p' => false,
                    'update_p' => false,
                    'delete_p' => false,
                    'import_p' => false,
                    'export_p' => false,
                ),
                $bPermission
            );
            /* get it by roles if exist */
            $aRolesList = CHtml::listData(self::getUserRole($iUserID), 'ptid', 'ptid');
            if ($aRolesList) {
                /* Do it only for read and create : roles can remove permission */
                $aPermissionStatic[0]['global'][$iUserID]['superadmin']['read_p'] = self::getPermissionByRoles($aRolesList, 'superadmin', 'read');
                $aPermissionStatic[0]['global'][$iUserID]['superadmin']['create_p'] = self::getPermissionByRoles($aRolesList, 'superadmin', 'create');
            }
        }
        /* If it's a superadmin Permission : get and return */
        if ($sPermission == 'superadmin') {
            return self::isForcedSuperAdmin($iUserID) || $aPermissionStatic[0]['global'][$iUserID][$sPermission][$sCRUD];
        }
        if (self::isForcedSuperAdmin($iUserID) || $aPermissionStatic[0]['global'][$iUserID]['superadmin']['read_p']) {
            return true;
        }

        /* Find the roles the user is part of and return those permissions */
        /* roles are only for global permission */
        // @TODO add surveypermission to roles
        if ($sEntityName == 'global') {
            $aRoles = self::getUserRole($iUserID);
            if (safecount($aRoles) > 0) {
                $allowed = false;
                foreach ($aRoles as $role) {
                    $allowed = $allowed || $this->roleHasPermission($role['ptid'], $sPermission, substr($sCRUD, 0, -2));
                }
                /* Can return false ? Even if user have the specific right … */
                return $allowed;
            }
        }

        /* Check in permission DB and static it */
        if (!isset($aPermissionStatic[$iEntityID][$sEntityName][$iUserID][$sPermission][$sCRUD])) {
            $query = $this->findByAttributes(array("entity_id" => $iEntityID, "uid" => $iUserID, "entity" => $sEntityName, "permission" => $sPermission));
            $bPermission = is_null($query) ? array() : $query->attributes;
            if (!isset($bPermission[$sCRUD]) || $bPermission[$sCRUD] == 0) {
                $bPermission = false;
            } else {
                $bPermission = true;
            }
            $aPermissionStatic[$iEntityID][$sEntityName][$iUserID][$sPermission][$sCRUD] = $bPermission;
        }
        return $aPermissionStatic[$iEntityID][$sEntityName][$iUserID][$sPermission][$sCRUD];
    }

    /**
     * Returns true if user is a forced superadmin (can not disable superadmin rights)
     * @var int
     * @return boolean
     */
    public static function isForcedSuperAdmin($iUserID)
    {
        return in_array($iUserID, App()->getConfig('forcedsuperadmin'));
    }
    /**
     * Returns true if a user has global permission for a certain action.
     * @param string $sPermission string Name of the permission - see function getGlobalPermissions
     * @param $sCRUD string The permission detailsyou want to check on: 'create','read','update','delete','import' or 'export'
     * @param $iUserID integer User ID - if not given the one of the current user is used
     * @return bool True if user has the permission
     */
    public function hasGlobalPermission($sPermission, $sCRUD = 'read', $iUserID = null)
    {
        return $this->hasPermission(0, 'global', $sPermission, $sCRUD, $iUserID);
    }

    public function getButtons(): string
    {
        $setPermissionsUrl = App()->getController()->createUrl(
            'surveyPermissions/settingsPermissions',
            ['id' => $this->uid, 'action' => 'user','surveyid' => $this->entity_id,]
        );

        $dropdownItems = [];

        $dropdownItems[] = [
            'title'            => gT('Edit permissions'),
            'iconClass'        => "ri-pencil-fill",
            'linkClass'        => "UserManagement--action--openmodal UserManagement--action--permissions",
            'linkAttributes'   => [
                'data-href'      => $setPermissionsUrl,
                'data-modalsize' => 'modal-lg',
            ],
            'enabledCondition' => Permission::model()->hasSurveyPermission($this->entity_id, 'surveysecurity', 'update')
        ];

        $dropdownItems[] = [
            'title'            => gT('Delete'),
            'url'              => App()->createUrl("surveyPermissions/deleteUserPermissions/"),
            'iconClass'        => 'ri-delete-bin-fill text-danger',
            'enabledCondition' => Permission::model()->hasSurveyPermission($this->entity_id, 'surveysecurity', 'delete'),
            'linkAttributes'   => [
                'data-bs-toggle'  => 'modal',
                'data-bs-target'  => '#confirmation-modal',
                'data-btnclass'   => 'btn-danger',
                'type'            => 'submit',
                'data-btntext'    => gT("Delete"),
                'data-title'      => gT('Delete user survey permissions'),
                'data-message'    => gT("Are you sure you want to delete this entry?"),
                'data-post-url'   => App()->createUrl("surveyPermissions/deleteUserPermissions/"),
                'data-post-datas' => json_encode(['surveyid' => $this->entity_id, 'userid' => $this->uid]),
            ],
        ];

        return App()->getController()->widget(
            'ext.admin.grid.GridActionsWidget.GridActionsWidget',
            ['dropdownItems' => $dropdownItems],
            true
        );
    }

    /**
     * Checks if a user has a certain permission in the given survey
     *
     * @param $iSurveyID integer The survey ID
     * @param $sPermission string Name of the permission
     * @param $sCRUD string The permission detail you want to check on: 'create','read','update','delete','import' or 'export'
     * @param $iUserID integer User ID - if not given the one of the current user is used
     * @return bool True if user has the permission
     */
    public function hasSurveyPermission($iSurveyID, $sPermission, $sCRUD = 'read', $iUserID = null)
    {
        if (isset(self::$aCachedSurveyPermissions[$iSurveyID][$sPermission][$sCRUD][$iUserID])) {
            return self::$aCachedSurveyPermissions[$iSurveyID][$sPermission][$sCRUD][$iUserID];
        }
        if (!isset(self::$aCachedSurveyPermissions[$iSurveyID])) {
            self::$aCachedSurveyPermissions[$iSurveyID] = [];
        }
        if (!isset(self::$aCachedSurveyPermissions[$iSurveyID][$sPermission])) {
            self::$aCachedSurveyPermissions[$iSurveyID][$sPermission] = [];
        }
        if (!isset(self::$aCachedSurveyPermissions[$iSurveyID][$sPermission][$sCRUD])) {
            self::$aCachedSurveyPermissions[$iSurveyID][$sPermission][$iUserID] = [];
        }
        $oSurvey = Survey::Model()->findByPk($iSurveyID);
        return self::$aCachedSurveyPermissions[$iSurveyID][$sPermission][$sCRUD][$iUserID] = ($oSurvey ? $oSurvey->hasPermission($sPermission, $sCRUD, $iUserID) : false);
    }

    /**
     * Returns true if a role has permission for crud
     * @param integer $roleId
     * @param string $sPermission
     * @param string $sCRUD The permission detailsyou want to check on: 'create','read','update','delete','import' or 'export'
     * @return bool allowed permssion
     */
    public function roleHasPermission($iRoleId, $sPermission, $sCRUD = 'read')
    {
        if (!in_array($sCRUD, array('create', 'read', 'update', 'delete', 'import', 'export'))) {
            return false;
        }
        $rolePermission = $this->findByAttributes(array(
            "entity_id" => $iRoleId,
            "entity" => "role",
            "permission" => $sPermission
        ));
        if (empty($rolePermission)) {
            return false;
        }
        return (bool) $rolePermission->getAttribute("{$sCRUD}_p");
    }

    /**
     * Returns true if a user has permission to read/create/update a certain template
     * @param string $sTemplateName
     * @param $sCRUD string The permission detailsyou want to check on: 'create','read','update','delete','import' or 'export'
     * @param integer $iUserID integer User ID - if not given the one of the current user is used
     * @return bool True if user has the permission
     */
    public function hasTemplatePermission($sTemplateName, $sCRUD = 'read', $iUserID = null)
    {
        return $this->hasPermission(0, 'global', 'templates', $sCRUD, $iUserID) || $this->hasPermission(0, 'template', $sTemplateName, $sCRUD, $iUserID);
    }

    /**
     * function used to order Permission by language string
     * @param array $aApermission The first permission information
     * @param array $aBpermission The second permission information
     * @return integer
     */
    private static function comparePermissionTitle($aApermission, $aBpermission)
    {
        return strcmp((string) $aApermission['title'], (string) $aBpermission['title']);
    }

    /**
     * Get the default/fixed $iUserID for Permission only
     * Use App()->getCurrentUserId() for all other purpose
     * @todo move to private function
     * @param integer|null $iUserID optional user id
     * @return int user id
     * @throws Exception
     */
    public function getUserId($iUserID = null)
    {
        if (empty($iUserID)) {
            if (Yii::app() instanceof CConsoleApplication) {
                /* Alt : return 1st forcedAdmin ? */
                throw new Exception('Permission must not be tested with console application.');
            }
            return App()->getCurrentUserId();
        }
        return $iUserID;
    }
    /**
     * get the connected user role
     * @param integer $iUserID user id
     * @return int roleId
     * @throws Exception
     */
    public static function getUserRole($iUserID)
    {
        return UserInPermissionrole::model()->getRoleForUser($iUserID);
    }

    /**
     * get permission by user roles
     * @param integer[] $rolesIds array of roles id
     * @param string $permission;
     * @param string $crud
     * @return boolean
     */
    public static function getPermissionByRoles($rolesIds, $permission, $crud = 'read')
    {
        $criteria = new CDbCriteria();
        $criteria->compare("entity", "role");
        $criteria->compare("permission", $permission);
        $criteria->addInCondition('entity_id', $rolesIds);
        $criteria->compare($crud . '_p', 1);
        return boolval(self::model()->count($criteria));
    }

    /**
     * get the owner if of an entity if exist
     * @param integer $iEntityID the entity id
     * @param string $sEntityName string name (model)
     * @return integer|null user id if exist
     */
    protected function getEntityOwnerId($iEntityID, $sEntityName)
    {
        /* know invalid entity */
        if (in_array($sEntityName, array('global', 'template', 'role'))) {
            return null;
        }
        /* allow to get it dynamically from any model */
        $oEntity = $this->getEntity($sEntityName, $iEntityID);
        if (empty($oEntity)) {
            return null;
        }
        if (!method_exists($oEntity, 'getOwnerId')) {
            return null;
        }
        return $oEntity->getOwnerId();
    }

    /**
     * Return the global permission list as array
     * @param string $key the specific permission
     * @return array of crud if $key is set, array of permissio array by crud …
     * @todo Use data value object instead of array.
     */
    public static function getGlobalPermissionData($key = null)
    {
        $aPermissions = array(
            'surveys' => array(
                'import' => false,
                'title' => gT("Surveys"),
                'description' => gT("Permission to create surveys (for which all permissions are automatically given) and view, update and delete surveys from other users"),
                'img' => ' ri-list-unordered',
            ),
            'surveysgroups' => array(
                'create' => true,
                'read' => true,
                'delete' => true,
                'import' => false,
                'export' => false,
                'title' => gT("Survey groups"),
                'description' => gT("Permission to create survey groups (for which all permissions are automatically given) and view, update and delete survey groups from other users."),
                'img' => ' ri-indent-increase',
            ),
            'users' => array(
                'import' => false,
                'export' => false,
                'title' => gT("Users"),
                'description' => gT("Permission to create, view, update and delete users"),
                'img' => ' ri-shield-check-fill',
            ),
            'usergroups' => array(
                'import' => false,
                'export' => false,
                'title' => gT("User groups"),
                'description' => gT("Permission to create, view, update and delete user groups"),
                'img' => ' ri-group-fill',
            ),
            'templates' => array(
                'title' => gT("Themes"),
                'description' => gT("Permission to create, view, update, delete, export and import themes"),
                'warning' => gT("Update/import theme allows an admin to potentially use cross-site scripting using JavaScript. Please make sure to assign this only to trusted persons."),
                'img' => ' ri-brush-fill',
            ),
            'labelsets' => array(
                'title' => gT("Label sets"),
                'description' => gT("Permission to create, view, update, delete, export and import label sets/labels"),
                'img' => ' ri-grid-line',
            ),
            'settings' => array(
                'create' => false,
                'delete' => false,
                'export' => false,
                'title' => gT("Settings & Plugins"),
                'description' => gT("Permission to view and update global settings & plugins and to delete and import plugins"),
                'warning' => gT("This permission allows an admin to change security relevant settings. Please make sure to assign this only to trusted persons."),
                'img' => 'ri-earth-fill',
            ),
            'participantpanel' => array(
                'title' => gT("Central participant database"),
                'description' => gT("Permission to create participants in the central participants database (for which all permissions are automatically given) and view, update and delete participants from other users"),
                'img' => 'ri-user-fill',
            ),
        );
        return $key == null ? $aPermissions : ($aPermissions[$key] ?? $key);
    }
    /**
     * Used in application/views/admin/surveymenu_entries/_form.php
     * @return array
     */
    public static function getPermissionList()
    {
        $aPermissions = array_merge(self::getSurveyBasePermissions(), self::getGlobalBasePermissions());
        return array_map(function ($aPermission) {
            return $aPermission['title'];
        }, $aPermissions);
    }

    /**
     * Get the translation of each CRUD
     * @return array crud=>translation
     */
    public static function getPermissionGradeList()
    {
        return [
            'create' => gT("Create"),
            'read' => gT("View/read"),
            'update' => gT("Update"),
            'delete' => gT("Delete"),
            'import' => gT("Import"),
            'export' => gT("Export"),
        ];
    }

    /**
     * Saves the updated values of a users themepermissions.
     *
     * @param $userId   integer  -- this user themepermission values should be updated
     * @param $aTemplatePermissions array -- permissions to be set
     * @return array
     */
    public static function editThemePermissionsUser(int $userId, $aTemplatePermissions)
    {
        $results = [];
        foreach ($aTemplatePermissions as $key => $value) {
            $oPermission = Permission::model()->findByAttributes(array('permission' => $key, 'uid' => $userId, 'entity' => 'template'));
            if (empty($oPermission)) {
                $oPermission = new Permission();
                $oPermission->uid = $userId;
                $oPermission->permission = $key; // maybe this one should be checked before
                $oPermission->entity = 'template';
                $oPermission->entity_id = 0;
            }
            $oPermission->read_p = $value;
            $results[$key] = $oPermission->save();
        }

        return $results;
    }

    /**
     * Get object with $iEntityID of type $sEntityName
     * NB: This method needs to be public so that it can be mocked.
     *
     * @param string $sEntityName
     * @param int $iEntityID
     * @return Object
     */
    public function getEntity($sEntityName, $iEntityID)
    {
        return $sEntityName::model()->findByPk($iEntityID);
    }
}
