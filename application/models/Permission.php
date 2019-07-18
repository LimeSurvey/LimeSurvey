<?php if (!defined('BASEPATH')) {
    die('No direct script access allowed');
}
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
 * @property integer $import_p
 * @property integer $export_p
 *
 *
 */
class Permission extends LSActiveRecord
{
    /* @var array[]|null The global base Permission LimeSurvey installation */
    protected static $aGlobalBasePermissions;

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
    public static function model($class = __CLASS__)
    {
        /** @var self $model */
        $model = parent::model($class);
        return $model;
    }

    /**
     * Returns the base permissions
     *
     * @access public
     * @static
     * @return array
     */
    public static function getSurveyBasePermissions()
    {
        $defaults = array(
            'create' => true,
            'read' => true,
            'update' => true,
            'delete' => true,
            'import' => true,
            'export' => true
        );
        $aPermissions = array(
            'assessments' => array(
                'import' => false,
                'export' => false,
                'title' => gT("Assessments"),
                'description' => gT("Permission to create/view/update/delete assessments rules for a survey"),
                'img' => ' fa fa-comment'
            ),
            'quotas' => array(
                'import' => false,
                'export' => false,
                'title' => gT("Quotas"),
                'description' => gT("Permission to create/view/update/delete quota rules for a survey"),
                'img' => ' fa fa-tasks'
            ),
            'responses' => array(
                'title' => gT("Responses"),
                'description' => gT("Permission to create(data entry)/view/update/delete/import/export responses"),
                'img' => ' icon-browse'
            ),
            'statistics' => array(
                'create' => false,
                'update' => false,
                'delete' => false,
                'import' => false,
                'export' => false,
                'title' => gT("Statistics"),
                'description' => gT("Permission to view statistics"),
                'img' => ' fa fa-bar-chart'
            ),
            'survey' => array(
                'create' => false,
                'update' => false,
                'import' => false,
                'export' => false,
                'title' => gT("Survey deletion"),
                'description' => gT("Permission to delete a survey"),
                'img' => ' fa fa-trash'
            ),
            'surveyactivation' => array(
                'create' => false,
                'read' => false,
                'delete' => false,
                'import' => false,
                'export' => false,
                'title' => gT("Survey activation"),
                'description' => gT("Permission to activate/deactivate a survey"),
                'img' => ' fa fa-play'
            ),
            'surveycontent' => array(
                'title' => gT("Survey content"),
                'description' => gT("Permission to create/view/update/delete/import/export the questions, groups, answers & conditions of a survey"),
                'img' => ' fa fa-file-text-o'
            ),
            'surveylocale' => array(
                'create' => false,
                'delete' => false,
                'import' => false,
                'export' => false,
                'title' => gT("Survey text elements"),
                'description' => gT("Permission to view/update the survey text elements, e.g. survey title, survey description, welcome and end message"),
                'img'=>' fa fa-edit'
            ),
            'surveysecurity' => array(
                'import' => false,
                'export' => false,
                'title' => gT("Survey security"),
                'description' => gT("Permission to modify survey security settings"),
                'img' => ' fa fa-shield'
            ),
            'surveysettings' => array(
                'create' => false,
                'delete' => false,
                'import' => false,
                'export' => false,
                'title' => gT("Survey settings"),
                'description' => gT("Permission to view/update the survey settings including survey participants table creation"),
                'img' => ' fa fa-gears'
            ),
            'tokens' => array(
                'title' => gT("Tokens"), 'description'=>gT("Permission to create/update/delete/import/export token entries"),
                'img' => ' fa fa-user'
            ),
            'translations' => array(
                'create' => false,
                'delete' => false,
                'import' => false,
                'export' => false,
                'title' => gT("Quick translation"),
                'description' => gT("Permission to view & update the translations using the quick-translation feature"),
                'img' => ' fa fa-language'
            )
        );
        uasort($aPermissions, array(__CLASS__, "comparePermissionTitle"));
        foreach ($aPermissions as &$permission) {
            $permission = array_merge($defaults, $permission);
        }
        return $aPermissions;
    }


    /**
     * Returns the global permissions including description and title
     *
     * @access public
     * @static
     * @return array
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
            'export' => true
        );
        $aPermissions = array(
            'surveys' => array(
                'import' => false,
                'title' => gT("Surveys"),
                'description' => gT("Permission to create surveys (for which all permissions are automatically given) and view, update and delete surveys from other users"),
                'img'=>' icon-list'
            ),
            'users' => array(
                'import' => false,
                'export' => false,
                'title' => gT("Users"),
                'description' => gT("Permission to create, view, update and delete users"),
                'img' => ' fa fa-shield'
            ),
            'usergroups' => array(
                'import' => false,
                'export' => false,
                'title' => gT("User groups"),
                'description' => gT("Permission to create, view, update and delete user groups"),
                'img' => ' fa fa-users'
            ),
            'templates' => array(
                'title'=> gT("Templates"),
                'description' => gT("Permission to create, view, update, delete, export and import templates"),
                'img' => ' fa fa-paint-brush'
            ),
            'labelsets' => array(
                'title' => gT("Label sets"),
                'description' => gT("Permission to create, view, update, delete, export and import label sets/labels"),
                'img' => ' icon-defaultanswers'
            ),
            'settings' => array(
                'create' => false,
                'delete' => false,
                'export' => false,
                'title' => gT("Settings & Plugins"),
                'description' => gT("Permission to view and update global settings & plugins and to delete and import plugins"),
                'img' => 'fa fa-globe'
            ),
            'participantpanel' => array(
                'title' => gT("Central participant database"),
                'description' => gT("Permission to create participants in the central participants database (for which all permissions are automatically given) and view, update and delete participants from other users"),
                'img' => 'fa fa-user-circle-o'
            ),
        );
        uasort($aPermissions, array(__CLASS__, "comparePermissionTitle"));
        $aPermissions['superadmin'] = array(
            'create' => true, // Currently : is set/unset tis Permission to other user's
            'update' => false,
            'delete' => false,
            'import' => false,
            'export' => false,
            'title' => gT("Superadministrator"),
            'description' => gT("Unlimited administration permissions"),
            'img' => 'icon-superadmin'
        );
        $aPermissions['auth_db'] = array(
            'create' => false,
            'update' => false,
            'delete' => false,
            'import' => false,
            'export' => false,
            'title' => gT("Use internal database authentication"),
            'description' => gT("Use internal database authentication"),
            'img' => 'usergroup'
        );

        /**
         * New event to allow plugin to add own global permission
         * Using $event->append('globalBasePermissions', $newGlobalBasePermissions);
         * $newGlobalBasePermissions=[
         *  permissionName=>[
         *       'create' : create (optionnal)
         *       'read' : read (optionnal)
         *       'update' : update (optionnal)
         *       'delete' : delete (optionnal)
         *       'import' : import (optionnal)
         *       'export' : export (optionnal)
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
     * @param integer $iUserID
     * @param integer $iEntityID
     * @param string $sEntityName
     * @return array
     */
    public static function getPermissions($iUserID, $iEntityID = null, $sEntityName = null)
    {
        $aBasePermissions = array();
        if ($sEntityName == 'survey') {
            $aBasePermissions = Permission::model()->getSurveyBasePermissions();
        } elseif ($sEntityName == 'global') {
            $aBasePermissions = Permission::model()->getGlobalBasePermissions();
        }

        if (is_null($sEntityName)) {
            $oPermissions = Permission::model()->findAllByAttributes(array('uid'=>$iUserID));
            $aBasePermissions = array();
            foreach ($oPermissions as $oPermission) {
                $aBasePermissions[$oPermission->id] = $oPermission->attributes;
            }
        } else {
            foreach ($aBasePermissions as $sPermission=>&$aPermissionDetail) {
                $oCurrentPermissions = Permission::model()->findByAttributes(array('uid'=>$iUserID, 'entity_id'=>$iEntityID, 'permission'=>$sPermission));
                if ($aPermissionDetail['create']) {
                    $aPermissionDetail['create'] = ($oCurrentPermissions ? (boolean) $oCurrentPermissions->create_p : false);
                }
                if ($aPermissionDetail['read']) {
                    $aPermissionDetail['read'] = ($oCurrentPermissions ? (boolean) $oCurrentPermissions->read_p : false);
                }
                if ($aPermissionDetail['update']) {
                    $aPermissionDetail['update'] = ($oCurrentPermissions ? (boolean) $oCurrentPermissions->update_p : false);
                }
                if ($aPermissionDetail['delete']) {
                    $aPermissionDetail['delete'] = ($oCurrentPermissions ? (boolean) $oCurrentPermissions->delete_p : false);
                }
                if ($aPermissionDetail['import']) {
                    $aPermissionDetail['import'] = ($oCurrentPermissions ? (boolean) $oCurrentPermissions->import_p : false);
                }
                if ($aPermissionDetail['export']) {
                    $aPermissionDetail['export'] = ($oCurrentPermissions ? (boolean) $oCurrentPermissions->export_p : false);
                }
            }
        }
        return $aBasePermissions;
    }

    /**
     * Sets permissions (global or survey-specific) for a survey administrator
     * Checks what permissions may be set and automatically filters invalid ones.
     * A permission may be invalid if the permission does not exist or that particular user may not give that permission
     *
     * @param mixed $iUserID
     * @param mixed $iEntityID
     * @param string $sEntityName
     * @param mixed $aPermissions
     * @param boolean $bBypassCheck
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
                foreach ($aBasePermissions as $PermissionName=>$aPermission) {
                    foreach ($aPermission as $sPermissionKey=>&$sPermissionValue) {
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
        } elseif ($sEntityName == 'survey') {
            $aBasePermissions = Permission::model()->getSurveyBasePermissions();
        }

        $aFilteredPermissions = array();
        foreach ($aBasePermissions as $sPermissionname=>$aPermission) {
            $aFilteredPermissions[$sPermissionname]['create'] = (isset($aPermissions[$sPermissionname]['create']) && $aPermissions[$sPermissionname]['create']);
            $aFilteredPermissions[$sPermissionname]['read'] = (isset($aPermissions[$sPermissionname]['read']) && $aPermissions[$sPermissionname]['read']);
            $aFilteredPermissions[$sPermissionname]['update'] = (isset($aPermissions[$sPermissionname]['update']) && $aPermissions[$sPermissionname]['update']);
            $aFilteredPermissions[$sPermissionname]['delete'] = (isset($aPermissions[$sPermissionname]['delete']) && $aPermissions[$sPermissionname]['delete']);
            $aFilteredPermissions[$sPermissionname]['import'] = (isset($aPermissions[$sPermissionname]['import']) && $aPermissions[$sPermissionname]['import']);
            $aFilteredPermissions[$sPermissionname]['export'] = (isset($aPermissions[$sPermissionname]['export']) && $aPermissions[$sPermissionname]['export']);
        }

        $condition = array('entity_id' => $iEntityID, 'uid' => $iUserID);
        $oEvent = new \LimeSurvey\PluginManager\PluginEvent('beforePermissionSetSave');
        $oEvent->set('aNewPermissions', $aFilteredPermissions);
        $oEvent->set('iSurveyID', $iEntityID);
        $oEvent->set('iUserID', $iUserID);

        if (!Permission::model()->hasGlobalPermission('superadmin', 'create')) {
            Permission::model()->deleteAllByAttributes($condition, "permission <> 'superadmin' AND entity <> 'template'");
        } else {
            Permission::model()->deleteAllByAttributes($condition, "entity <> 'template'");
        }

        foreach ($aFilteredPermissions as $sPermissionname=>$aPermission) {
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
                    'export_p' => (int) $aPermission['export']
                );

                $permission = new self;
                foreach ($data as $k => $v) {
                                    $permission->$k = $v;
                }
                $permission->save();
            }
        }
        return true;
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
            'export_p' => 0
        );

        foreach ($aPermissions as $sPermType) {
            $aPerm[$sPermType] = 1;
        }

        $this->insertSomeRecords($aPerm);
    }

    /**
     * @param integer $iUserID
     * @param integer $iSurveyID
     */
    public function giveAllSurveyPermissions($iUserID, $iSurveyID)
    {
        if ($iSurveyID == 0) {
            throw new InvalidArgumentException('Survey ID cannot be 0 (collides with superadmin permission entity id)');
        }

        $aPermissions = $this->getSurveyBasePermissions();
        $aPermissionsToSet = array();
        foreach ($aPermissions as $sPermissionName=>$aPermissionDetails) {
            foreach ($aPermissionDetails as $sPermissionDetailKey=>$sPermissionDetailValue) {
                if (in_array($sPermissionDetailKey, array('create', 'read', 'update', 'delete', 'import', 'export')) && $sPermissionDetailValue == true) {
                    $aPermissionsToSet[$sPermissionName][$sPermissionDetailKey] = 1;
                }
            }
        }

        $this->setPermissions($iUserID, $iSurveyID, 'survey', $aPermissionsToSet);
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
        $permission = new self;
        foreach ($data as $k => $v) {
                    $permission->$k = $v;
        }
        return $permission->save();
    }

    /**
     * @param integer $surveyid
     * @return array
     */
    public function getUserDetails($surveyid)
    {
        $sQuery = "SELECT p.entity_id, p.uid, u.users_name, u.full_name FROM {{permissions}} AS p INNER JOIN {{users}}  AS u ON p.uid = u.uid
            WHERE p.entity_id = :surveyid AND u.uid != :userid and p.entity='survey'
            GROUP BY p.entity_id, p.uid, u.users_name, u.full_name
            ORDER BY u.users_name";
        $iUserID = Yii::app()->user->getId();
        return Yii::app()->db->createCommand($sQuery)->bindParam(":userid", $iUserID, PDO::PARAM_INT)->bindParam("surveyid", $surveyid, PDO::PARAM_INT)->query()->readAll(); //Checked
    }


    /**
     * @param integer $iSurveyIDSource
     * @param integer $iSurveyIDTarget
     */
    public function copySurveyPermissions($iSurveyIDSource, $iSurveyIDTarget)
    {
        $aRows = self::model()->findAll("entity_id=:sid AND entity='survey'", array(':sid'=>$iSurveyIDSource));
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
     * @param $iUserID integer User ID - if not given the one of the current user is used
     * @return bool True if user has the permission
     */
    public function hasPermission($iEntityID, $sEntityName, $sPermission, $sCRUD = 'read', $iUserID = null, $iPermissionRoleId = null)
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

        $oEvent = new \LimeSurvey\PluginManager\PluginEvent('beforeHasPermission');
        $oEvent->set('iEntityID', $iEntityID);
        $oEvent->set('sEntityName', $sEntityName);
        $oEvent->set('sPermission', $sPermission);
        $oEvent->set('sCRUD', $sCRUD);
        $oEvent->set('iUserID', $iUserID);
        $oEvent->set('iPermissionRoleId', $iPermissionRoleId);
        App()->getPluginManager()->dispatchEvent($oEvent);
        $pluginbPermission = $oEvent->get('bPermission');

        if (isset($pluginbPermission)) {
            return $pluginbPermission;
        }

        /* Always return true for CConsoleApplication (before or after plugin ? All other seems better after plugin) */
        // TODO: see above about entry script and superadmin
        if (is_null($iUserID) && Yii::app() instanceof CConsoleApplication) {
            return true;
        }

        /* Always return false for unknow sCRUD */
        // TODO: should not be necessary
        if (!in_array($sCRUD, array('create', 'read', 'update', 'delete', 'import', 'export'))) {
            return false;
        }
        $sCRUD = $sCRUD.'_p';

        /* Always return false for guests */
        // TODO: should not be necessary
        $iUserID = self::getUserId($iUserID);
        if (!$iUserID && $iUserID!==0) {
            return false;
        }

        /* Always return true if you are the owner : this can be done in core plugin ? */
        // TODO: give the rights to owner adding line in permissions table, so it will return true with the normal way
        if ($iUserID == $this->getOwnerId($iEntityID, $sEntityName) && $sEntityName != 'role') {
            return true;
        }

        /* Check if superadmin and static it */
        if (!isset($aPermissionStatic[0]['global'][$iUserID]['superadmin']['read_p'])) {
            $aPermission = $this->findByAttributes(array("entity_id"=>0, 'entity'=>'global', "uid"=> $iUserID, "permission"=>'superadmin'));
            $bPermission = is_null($aPermission) ? array() : $aPermission->attributes;
            $aPermissionStatic[0]['global'][$iUserID]['superadmin'] = array_merge(
                array(
                    'create_p'=>false,
                    'read_p'=>false,
                    'update_p'=>false,
                    'delete_p'=>false,
                    'import_p'=>false,
                    'export_p'=>false,
                ),
                $bPermission
            );
        }
        /* If it's a superadmin Permission : get and return */
        if ($sPermission == 'superadmin') {
            return self::isForcedSuperAdmin($iUserID) || $aPermissionStatic[0]['global'][$iUserID][$sPermission][$sCRUD];
        }
        if (self::isForcedSuperAdmin($iUserID) || $aPermissionStatic[0]['global'][$iUserID]['superadmin']['read_p']) {
            return true;
        }

        /* Find the roles the user is part of and return thoese permissions */
        /* Ignore roles for surveypermissions */
        // @TODO add surveypermission to roles
        $aRoles = self::getUserRole($iUserID);
        if(safecount($aRoles)>0 && $sEntityName != 'survey') {
            $allowed = false;
            foreach ($aRoles as $role) {
                $allowed = $allowed || $this->hasRolePermission($role['ptid'], $sPermission, substr($sCRUD, 0, -2));
            }
            return $allowed;
        }

        /* Check in permission DB and static it */
        // TODO: that should be the only way to get the permission,
        // and it should be accessible from any object with relations :
        // $obj->permissions->read or $obj->permissions->write, etc.
        // relation :
        // 'permissions' => array(self::HAS_ONE, 'Permission', array(), 'condition'=> 'entity_id='.{ENTITYID}.' && uid='.Yii::app()->user->id.' && entity="{ENTITY}" && permission="{PERMISSIONS}"', 'together' => true ),
        if (!isset($aPermissionStatic[$iEntityID][$sEntityName][$iUserID][$sPermission][$sCRUD])) {
            $query = $this->findByAttributes(array("entity_id"=> $iEntityID, "uid"=> $iUserID, "entity"=>$sEntityName, "permission"=>$sPermission));
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
    public function hasGlobalPermission($sPermission, $sCRUD = 'read', $iUserID = null, $iPermissionRoleId = null)
    {
        return $this->hasPermission(0, 'global', $sPermission, $sCRUD, $iUserID, $iPermissionRoleId);
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
        $oSurvey = Survey::Model()->findByPk($iSurveyID);
        if (!$oSurvey) {
            return false;
        }
        // Get global correspondance for surveys rigth
        $sGlobalCRUD = $sCRUD;
        if(($sCRUD == 'create' || $sCRUD == 'import')) {// Create and import (token, reponse , question content …) need only allow update surveys
            $sGlobalCRUD = 'update';
        }
        if(($sCRUD == 'delete' && $sPermission != 'survey')) {// Delete (token, reponse , question content …) need only allow update surveys
            $sGlobalCRUD = 'update';
        }
        return $this->hasGlobalPermission('surveys', $sGlobalCRUD, $iUserID) || $this->hasPermission($iSurveyID, 'survey', $sPermission, $sCRUD, $iUserID);
    }

    /**
     * Returns true if a role has permission to read/create/update a certain template
     * @param string $roleId
     * @param $sCRUD string The permission detailsyou want to check on: 'create','read','update','delete','import' or 'export'
     * @param integer $iUserID integer User ID - if not given the one of the current user is used
     * @return bool True if user has the permission
     */
    public function hasRolePermission($iRoleId, $sPermission, $sCRUD = 'read')
    {
        return $this->hasPermission($iRoleId, 'role', $sPermission, $sCRUD, 0);
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
        return strcmp($aApermission['title'], $aBpermission['title']);
    }

    /**
     * get the default/fixed $iUserID
     * @param integer $iUserID optional user id
     * @return int user id
     * @throws Exception
     */
    public static function getUserId($iUserID = null)
    {
        if (is_null($iUserID) && $iUserID!==0) {
            if (Yii::app() instanceof CConsoleApplication) {
                throw new Exception('Permission must not be tested with console application.');
            }
            $iUserID = Yii::app()->session['loginID'];
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
     * get the owner if of an entity if exist
     * @param integer $iEntityID the entity id
     * @param string $sEntityName string name (model)
     * @return integer|null user id if exist
     */
    protected function getOwnerId($iEntityID, $sEntityName)
    {
        if ($sEntityName == 'survey') {
            return $sEntityName::Model()->findByPk($iEntityID)->owner_id; // ALternative : if owner_id exist in $sEntityName::model()->findByPk($iEntityID), but unsure actually $sEntityName have always a model
        }
        return null;
    }

    public static function getPermissionList(){
        $aPermissions = array_merge(self::getSurveyBasePermissions(),self::getGlobalBasePermissions());
        return array_map(function($aPermission){
            return $aPermission['title'];
        }, $aPermissions);
    }
    public static function getPermissionGradeList(){
        return [
            'create' => gT("Create"),
            'read' => gT("View/read"),
            'update' => gT("Update"),
            'delete' => gT("Delete"),
            'import' => gT("Import"),
            'export' => gT("Export"),
        ];
    }
}
