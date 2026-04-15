<?php

/*
 * LimeSurvey
 * Copyright (C) 2007-2020 The LimeSurvey Project Team / Carsten Schmitz
 * All rights reserved.
 * License: GNU/GPL License v2 or later, see LICENSE.php
 * LimeSurvey is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 *
 * Surveys Groups Permission Controller
 */

use LimeSurvey\Models\Services\PermissionManager;

/**
 * Class SurveysGroupsPermissionController
 */
class SurveysGroupsPermissionController extends LSBaseController
{
    /** By default : just view */
    public $defaultAction = 'index';

    /**
     * It's import to have the accessRules set (security issue).
     * Only logged in users should have access to actions. All other permissions
     * should be checked in the action itself.
     *
     * @return array
     */
    public function accessRules()
    {
        return [
            [
                'allow',
                'actions' => [],
                'users'   => ['*'], //everybody
            ],
            [
                'allow',
                'actions' => ['view'],
                'users'   => ['@'], //only login users
            ],
            ['deny'], //always deny all actions not mentioned above
        ];
    }

    public function filters()
    {
        return array(
            'postOnly + DeleteUser'
        );
    }

    /**
     * Shown permissions list, allow to add user and group,
     * No action done
     * @param integer $id SurveysGroups id
     * @return void
     * @throws CHttpException
     */
    public function actionIndex(int $id)
    {
        /** @var SurveysGroups $model */
        $model = $this->loadModel($id);
        $aData = array(
            'model' => $model
        );
        $aCruds = array('create', 'read', 'update', 'delete', 'import', 'export');
        $aSurveysGroupsPermissions = Permission::model()->getEntityBasePermissions('SurveysGroups');
        $aSurveysInGroupPermissions = Permission::model()->getEntityBasePermissions('SurveysInGroup');
        $aDefinitionPermissions = array_merge(
            $aSurveysGroupsPermissions,
            $aSurveysInGroupPermissions
        );
        foreach ($aDefinitionPermissions as $permissionKey => $aPermission) {
            $aDefinitionPermissions[$permissionKey]['maxCrud'] = count(array_filter(array_intersect_key($aPermission, array_flip($aCruds)))); // Used for mixed class
        }

        /* Find all current user list with rights */
        /* @todo : move this to : SurveysGroups ? Permission ? User ?*/
        $oCriteria = new CDbCriteria();
        $oCriteria->select = ['uid','entity','entity_id'];
        $oCriteria->group = 'uid, entity, entity_id';
        $oCriteria->compare("entity", "SurveysGroups"); // on SurveyGroup
        $oCriteria->compare("entity_id", $model->primaryKey); // on this SurveyGroup
        $oCriteria->compare("uid", "<>" . App()->user->getId()); // not self user
        $aExistingUsers = CHtml::listData(Permission::model()->findAll($oCriteria), 'uid', 'uid');
        $oExistingUsers = array();
        $aCurrentsUserRights = array();
        if (!empty($aExistingUsers)) {
            $oCriteria = new CDbCriteria();
            $oCriteria->order = "users_name";
            $oCriteria->addInCondition("uid", $aExistingUsers);
            if (Yii::app()->getConfig('usercontrolSameGroupPolicy') == true && !Permission::model()->hasGlobalPermission('superadmin')) {
                $authorizedUsersList = getUserList('onlyuidarray'); // Limit the user list for the samegrouppolicy
                $oCriteria->addInCondition("uid", $authorizedUsersList);
            }
            $oExistingUsers = User::model()->findAll($oCriteria);
            $aCurrentsUserRights = array();
            foreach ($oExistingUsers as $oUser) {
                foreach ($aSurveysGroupsPermissions as $sPermission => $aPermissions) {
                    $aCurrentsUserRights[$oUser->uid][$sPermission] = array();
                    foreach (array_intersect_key($aPermissions, array_flip($aCruds)) as $sCrud => $available) {
                        if ($available && $model->hasPermission($sPermission, $sCrud, $oUser->uid)) {
                            $aCurrentsUserRights[$oUser->uid][$sPermission][] = $sCrud;
                        }
                    }
                }
                foreach ($aSurveysInGroupPermissions as $sPermission => $aPermissions) {
                    $aCurrentsUserRights[$oUser->uid][$sPermission] = array();
                    foreach (array_intersect_key($aPermissions, array_flip($aCruds)) as $sCrud => $available) {
                        if ($available && $model->hasPermission($sPermission, $sCrud, $oUser->uid)) {
                            $aCurrentsUserRights[$oUser->uid][$sPermission][] = $sCrud;
                        }
                    }
                }
            }
        }

        $oAddUserList  = array();
        $oAddGroupList  = array();

        if ($model->hasPermission('permission', 'create')) {
            /* Search user withouth rights on SurveyGroup */
            /* @todo : move this to : SurveysGroups ? Permission ? User ?*/
            $oCriteria = new CDbCriteria();
            if (Yii::app()->getConfig('usercontrolSameGroupPolicy') == true && !Permission::model()->hasGlobalPermission('superadmin')) {
                $authorizedUsersList = getUserList('onlyuidarray'); // Limit the user list for the samegrouppolicy
                $oCriteria->addInCondition("uid", $authorizedUsersList);
            }
            $oCriteria->addNotInCondition("uid", $aExistingUsers);
            $oCriteria->compare("uid", "<>" . Permission::model()->getUserId());
            $oCriteria->order = "users_name";
            $oAddUserList = User::model()->findAll($oCriteria);
            /* User group according to rights */
            $oCriteria = new CDbCriteria();
            if (shouldFilterUserGroupList()) {
                $authorizedGroupsList = getUserGroupList();
                $oCriteria->addInCondition("ugid", $authorizedGroupsList);
            }
            $oCriteria->order = "name";
            $oAddGroupList = UserGroup::model()->findAll($oCriteria);
        }
        $aData['subview'] = 'viewCurrents';
        $aData['aPermissionData'] = array(
            'aDefinitionPermissions' => $aDefinitionPermissions,
            'oExistingUsers' => $oExistingUsers,
            'aCurrentsUserRights' => $aCurrentsUserRights,
            'oAddUserList' => $oAddUserList,
            'oAddGroupList' => $oAddGroupList,
            'model' => $model,
        );

        $aData['topbar']['title'] = gT('Permission for group: ') . CHtml::encode($model->title);
        $aData['topbar']['rightButtons'] = Yii::app()->getController()->renderPartial(
            '/layouts/partial_topbar/right_close_saveclose_save',
            [
                'isReturnBtn' => true,
                'returnUrl' => Yii::app()->createUrl("surveyAdministration/listsurveys#surveygroups"),
                'isCloseBtn' => false,
                'isSaveBtn' => false,
                'isSaveAndCloseBtn' => false,
            ],
            true
        );
        $this->aData = $aData;

        App()->getController()->render('/SurveysGroupsPermission/index', $aData);
    }

    /**
     * Add minimal permission (read) to user
     * Show result and allow to set other permissions
     * @param integer $id SurveysGroups id
     * @return void
     */
    public function actionAddUser(int $id)
    {
        $model = $this->loadModel($id);
        if (!$model->hasPermission('permission', 'create')) {
            throw new CHttpException(403, gT("You do not have permission to access this page."));
        }
        $uid = App()->getRequest()->getPost('uid');
        if (!$uid) {
            throw new CHttpException(400, gT("Invalid action"));
        }
        /* Check if logged user can see user */
        if (!in_array($uid, getUserList('onlyuidarray'))) {
            throw new CHttpException(403, gT("You do not have permission to this user."));
        }
        $aData = array(
            'model' => $model
        );
        $aData['subview'] = 'addUserResult';

        $aData['topbar']['title'] = gT('Permission for group: ') . CHtml::encode($model->title);
        $aData['topbar']['rightButtons'] = Yii::app()->getController()->renderPartial(
            '/layouts/partial_topbar/right_close_saveclose_save',
            [
                'backUrl' => App()->createUrl("SurveysGroupsPermission/index", ["id" => $id]),
                'isCloseBtn' => true,
                'isSaveBtn' => false,
                'isSaveAndCloseBtn' => false,
            ],
            true
        );
        $aData['aPermissionData'] = array(
            'result' => array(),
            'uid' => $uid,
            'model' => $model,
        );
        $result = array(
            'success' => false,
            'warning' => false,
            'error' => false,
        );
        $oPermission = Permission::setMinimalEntityPermission($uid, $id, 'SurveysGroups');
        if (!$oPermission->hasErrors()) {
            $result['success'] = gT("User added.");
        } else {
            $result['error'] = $oPermission;
        }

        $aData['aPermissionData']['result'] = $result;

        $this->aData = $aData;
        App()->getController()->render('/SurveysGroupsPermission/index', $aData);
    }


    /**
     * Add minimal permission (read) to all users in a group of user
     * Show result and allow to set other permissions
     * @param integer $id SurveysGroups id
     * @return void
     */
    public function actionAddUserGroup(int $id)
    {
        $model = $this->loadModel($id);
        if (!$model->hasPermission('permission', 'create')) {
            throw new CHttpException(403, gT("You do not have permission to access this page."));
        }
        $ugid = App()->getRequest()->getPost('ugid');
        if (!$ugid) {
            throw new CHttpException(400, gT("Invalid action"));
        }
        /* Check if logged user can see user group */
        if (shouldFilterUserGroupList() && !in_array($ugid, getUserGroupList())) {
            throw new CHttpException(403, gT("You do not have permission to this user group."));
        }
        $aData = array(
            'model' => $model
        );
        $aData['subview'] = 'addUserGroupResult';

        $aData['topbar']['title'] = gT('Permission for group: ') . CHtml::encode($model->title);
        $aData['topbar']['rightButtons'] = Yii::app()->getController()->renderPartial(
            '/layouts/partial_topbar/right_close_saveclose_save',
            [
                'backUrl' => App()->createUrl("SurveysGroupsPermission/index", ["id" => $id]),
                'isCloseBtn' => true,
                'isSaveBtn' => false,
                'isSaveAndCloseBtn' => false,
            ],
            true
        );
        $aData['aPermissionData'] = array(
            'result' => array(),
            'ugid' => $ugid,
            'model' => $model,
        );
        $result = array(
            'success' => false,
            'warning' => false,
            'error' => false,
        );
        /* All seems OK */
        /** @var UserInGroup[] */
        $oUserInGroups = UserInGroup::model()->findAll(
            'ugid = :ugid AND uid <> :currentUserId AND uid <> :surveysgroupOwnerId',
            array(
                ':ugid' => $ugid,
                ':currentUserId' => Permission::model()->getUserId(), // Don't need to set to current user
                ':surveysgroupOwnerId' => $model->getOwnerId(), // Don't need to set to owner (?) , get from surveyspermission
            )
        );
        foreach ($oUserInGroups as $oUserInGroup) {
            Permission::setMinimalEntityPermission($oUserInGroup->uid, $id, 'SurveysGroups');
        }
        // Did we check something ? Some warning if group is empty for example ?
        $result['success'] = gT("User group added.");
        $aData['aPermissionData']['result'] = $result;
        $this->aData = $aData;
        App()->getController()->render('/SurveysGroupsPermission/index', $aData);
    }

    /**
     * Shown permissions list for user
     * @param integer $id SurveysGroups id
     * @param integer $to user id
     * @return void
     */
    public function actionViewUser(int $id, int $to)
    {
        $oUser = User::model()->findByPk($to);
        if (empty($oUser)) {
            throw new CHttpException(401, gT("User not found"));
        }
        if (!in_array($to, getUserList('onlyuidarray'))) {
            throw new CHttpException(403, gT("You do not have permission to this user."));
        }
        if ($to == App()->user->getId()) {
            throw new CHttpException(403, gT("You cannot modify your own permissions."));
        }
        $this->viewUserOrUserGroup($id, $to, 'user');
    }

    /**
     * Shown permissions list for user group
     * @param integer $id SurveysGroups id
     * @param integer $to group id
     * @return void
     */
    public function actionViewUserGroup(int $id, int $to)
    {
        $oUserGroup = UserGroup::model()->findByPk($to);
        if (empty($oUserGroup)) {
            throw new CHttpException(401, gT("User group not found"));
        }
        /* Check if logged user can see user group */
        if (shouldFilterUserGroupList() && !in_array($to, getUserGroupList())) {
            throw new CHttpException(403, gT("You do not have permission to access this user group."));
        }
        $this->viewUserOrUserGroup($id, $to, 'group');
    }

    /**
     * Save current permissions for user (or group)
     * use POST value for user id or group id
     * redirect (in all cas) to surveysgroups permission or to user set permission (permissionsSet function)
     * @param integer $id SurveysGroups id
     * @return void
     */
    public function actionSave(int $id)
    {
        $model = $this->loadModel($id);
        $uid = null;
        if (!$model->hasPermission('permission', 'update')) {
            throw new CHttpException(403, gT("You do not have permission to access this page."));
        }
        $type = App()->getRequest()->getPost('type', 'user');
        if ($type == 'group') {
            $ugid = App()->getRequest()->getPost('ugid');
            if (empty($ugid)) {
                throw new CHttpException(400, gT("Invalid action"));
            }
            $oUserInGroups = UserInGroup::model()->findAll(
                'ugid = :ugid AND uid <> :currentUserId AND uid <> :surveygroupsOwnerId',
                array(
                    ':ugid' => $ugid,
                    ':currentUserId' => Permission::model()->getUserId(), // Don't need to set to current user
                    ':surveygroupsOwnerId' => $model->getOwnerId(), // Don't need to set to owner (?) , get from surveyspermission
                )
            );
            $uids = CHtml::listData($oUserInGroups, 'uid', 'uid');
        } else {
            $uid = App()->getRequest()->getPost('uid');
            if (empty($uid)) {
                throw new CHttpException(400, gT("Invalid action"));
            }
            if (!in_array($uid, getUserList('onlyuidarray'))) {
                throw new CHttpException(403, gT("You do not have permission to access this user."));
            }
            $uids = array($uid);
        }
        $set = App()->getRequest()->getPost('set', array());
        $user = App()->user;
        $request = App()->request;
        $success = true;
        foreach ($set as $entityName => $aPermissionSet) {
            /* Must get SurveysIngroup for SurveysIngroup entity */
            $entity = $entityName::model()->findByPk($id);
            $PermissionManagerService = new PermissionManager(
                $request,
                $user,
                $entity,
                App()
            );
            foreach ($uids as $uid) {
                /* Permission::model()->setPermissions return true or break */
                $success = $success && $PermissionManagerService->setPermissions($uid);
            }
        }
        if ($success) {
            App()->setFlashMessage(gT("Survey group permissions were successfully updated."));
        } else {
            App()->setFlashMessage(gT("An error happened while updating survey group permissions."), 'danger');
        }
        if ($type == 'group') {
            App()->request->redirect(App()->getController()->createUrl('surveysGroupsPermission/index', array('id' => $id)));
        }
        if (App()->getRequest()->getParam('saveandclose')) {
            App()->request->redirect(App()->getController()->createUrl('surveysGroupsPermission/index', array('id' => $id)));
        }
        App()->request->redirect(App()->getController()->createUrl('surveysGroupsPermission/viewUser', array('id' => $id, 'to' => $uid)));
    }

    /**
     * Shown permissions list for user (or group)
     * @param integer $id SurveysGroups id
     * @param integer $uid user id
     * @return void
     */
    public function actionDeleteUser(int $id, int $uid)
    {
        $model = $this->loadModel($id);
        if (!$model->hasPermission('permission', 'delete')) {
            throw new CHttpException(403, gT("You do not have permission to access this page."));
        }
        $oUser = User::model()->findByPk($uid);
        if (empty($oUser)) {
            throw new CHttpException(401, gT("User not found"));
        }
        if (!in_array($uid, getUserList('onlyuidarray'))) {
            throw new CHttpException(403, gT("You do not have permission to access this user."));
        }
        if ($uid == App()->user->getId()) {
            throw new CHttpException(403, gT("You cannot delete your own user."));
        }
        Permission::model()->deleteAll("uid = :uid AND entity_id = :sid AND entity = :entity", array(
            ':uid' => $uid,
            ':sid' => $id,
            ':entity' => 'SurveysGroups'
        ));
        Permission::model()->deleteAll("uid = :uid AND entity_id = :sid AND entity = :entity", array(
            ':uid' => $uid,
            ':sid' => $id,
            ':entity' => 'SurveysInGroup'
        ));
        $aData = array(
            'model' => $model
        );
        $aData['subview'] = 'deleteUserResult';

        $aData['topbar']['title'] = gT('Permission for group: ') . CHtml::encode($model->title);
        $aData['topbar']['rightButtons'] = Yii::app()->getController()->renderPartial(
            '/layouts/partial_topbar/right_close_saveclose_save',
            [
                'backUrl' => App()->createUrl("SurveysGroupsPermission/index", ["id" => $id]),
                'isCloseBtn' => true,
                'isSaveBtn' => false,
                'isSaveAndCloseBtn' => false,
            ],
            true
        );
        $aData['aPermissionData'] = array(
            'model' => $model,
            'oUser' => $oUser,
        );
        $this->aData = $aData;
        App()->getController()->render('/SurveysGroupsPermission/index', $aData);
    }

    /**
     * Shown permissions list for user or group
     * @param integer $id SurveysGroups id
     * @param integer $to user or group id
     * @param string $type user or group
     * @return void
     */
    private function viewUserOrUserGroup($id, $to, $type = 'user')
    {
        $model = $this->loadModel($id);
        $userId = null;
        if ($type == 'user') {
            $oUserGroup = null;
            $oUser = User::model()->findByPk($to);
            $userId = $to; // More clear after
        } else {
            $oUserGroup = UserGroup::model()->findByPk($to);
            $oUser = null;
        }
        $user = App()->user;
        $request = App()->request;
        $PermissionManagerService = new PermissionManager(
            $request,
            $user,
            $model,
            App()
        );
        $aSurveysGroupsPermissions = $PermissionManagerService->getPermissionData($userId);
        $PermissionManagerService = new PermissionManager(
            $request,
            $user,
            /** @scrutinizer ignore-type : we alreadty check SurveysGroup then we have it*/ SurveysInGroup::model()->findByPk($id),
            App()
        );
        $aSurveysInGroupPermissions = $PermissionManagerService->getPermissionData($userId);
        $aPermissions = array_merge(
            $aSurveysGroupsPermissions,
            $aSurveysInGroupPermissions
        );
        $aData = array(
            'model' => $model,
            'subview' => 'setPermissionForm',
        );

        $hasUpdatePermission = $model->hasPermission('permission', 'update');
        $aData['topbar']['title'] = gT('Permission for group: ') . CHtml::encode($model->title);
        $aData['topbar']['rightButtons'] = Yii::app()->getController()->renderPartial(
            '/layouts/partial_topbar/right_close_saveclose_save',
            [
                'backUrl' => App()->createUrl("SurveysGroupsPermission/index", ["id" => $id]),
                'isCloseBtn' => true,
                'isSaveBtn' => $hasUpdatePermission,
                'formIdSave' => 'permissionsSave',
                'isSaveAndCloseBtn' => $hasUpdatePermission,
                'formIdSaveClose' => 'permissionsSave',
            ],
            true
        );

        $aData['aPermissionData'] = array(
            'aPermissions' => $aPermissions,
            'model' => $model,
            'uid' => ($type == 'user') ? $to : null,
            'to' => $to,
            'type' => $type,
            'oUser' => $oUser,
            'oUserGroup' => $oUserGroup,
        );
        $this->aData = $aData;
        App()->getController()->render('/SurveysGroupsPermission/index', $aData);
    }

    /**
     * Returns the data model based on the primary key given
     * If the data model is not found, an HTTP exception will be raised.
     * Check minimal permission to see
     * @param integer $id the ID of the model to be loaded
     * @return SurveysGroups the loaded model
     * @throws CHttpException
     */
    private function loadModel($id)
    {
        $model = SurveysGroups::model()->findByPk($id);
        if ($model === null) {
            throw new CHttpException(404, 'The requested page does not exist.');
        }
        if (!$model->hasPermission('permission', 'read')) {
            throw new CHttpException(403, gT("You do not have permission to access this page."));
        }
        return $model;
    }
}
