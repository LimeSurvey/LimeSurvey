<?php

/*
 * LimeSurvey
 * Copyright (C) 2007-2011 The LimeSurvey Project Team / Carsten Schmitz
 * All rights reserved.
 * License: GNU/GPL License v2 or later, see LICENSE.php
 * LimeSurvey is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 *
 * Surveys Groups Controller
 */

class SurveysGroupsPermissionController extends LSBaseController
{
    /** By default : just view */
    public $defaultAction = 'index';

    /**
     * Shown permissions list, allow to add user and group,
     * No action done
     * @param integer$id SurveysGroups id
     */
    public function actionIndex($id)
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
        $oCriteria->group = 'uid';
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
                        if ($available && Permission::model()->hasSurveyGroupPermission($id, $sPermission, $sCrud, $oUser->uid)) {
                            $aCurrentsUserRights[$oUser->uid][$sPermission][] = $sCrud;
                        }
                    }
                }
                foreach ($aSurveysInGroupPermissions as $sPermission => $aPermissions) {
                    $aCurrentsUserRights[$oUser->uid][$sPermission] = array();
                    foreach (array_intersect_key($aPermissions, array_flip($aCruds)) as $sCrud => $available) {
                        if ($available && Permission::model()->hasSurveyGroupPermission($id, $sPermission, $sCrud, $oUser->uid)) {
                            $aCurrentsUserRights[$oUser->uid][$sPermission][] = $sCrud;
                        }
                    }
                }
            }
        }

        $oAddUserList  = array();
        $oAddGroupList  = array();
        if (Permission::model()->hasSurveyGroupPermission($id, 'permission', 'create')) {
            /* Search user withouth rights on SurveyGroup */
            /* @todo : move this to : SurveysGroups ? Permission ? User ?*/
            $oCriteria = new CDbCriteria();
            if (Yii::app()->getConfig('usercontrolSameGroupPolicy') == true && !Permission::model()->hasGlobalPermission('superadmin')) {
                $authorizedUsersList = getUserList('onlyuidarray'); // Limit the user list for the samegrouppolicy
                $oCriteria->addInCondition("uid", $authorizedUsersList);
            }
            $oCriteria->addNotInCondition("uid", $aExistingUsers);
            $oCriteria->compare("uid", "<>" . Permission::getUserId());
            $oCriteria->order = "users_name";
            $oAddUserList = User::model()->findAll($oCriteria);
            /* User group according to rights */
            $oCriteria = new CDbCriteria();
            if (Yii::app()->getConfig('usercontrolSameGroupPolicy') == true && !Permission::model()->hasGlobalPermission('superadmin')) {
                $authorizedGroupsList = getUserGroupList();
                $oCriteria->addInCondition("ugid", $authorizedGroupsList);
            }
            $oCriteria->order = "name";
            $oAddGroupList = UserGroup::model()->findAll($oCriteria);
        }
        $aData['subview'] = 'viewCurrents';
        $aData['buttons'] = array(
            'closebutton' => array(
                'url' => App()->createUrl('surveyAdministration/listsurveys', array('#' => 'surveygroups')),
            ),
        );
        $aData['aPermissionData'] = array(
            'aDefinitionPermissions' => $aDefinitionPermissions,
            'oExistingUsers' => $oExistingUsers,
            'aCurrentsUserRights' => $aCurrentsUserRights,
            'oAddUserList' => $oAddUserList,
            'oAddGroupList' => $oAddGroupList,
            'model' => $model,
        );
        App()->getController()->render('/SurveysGroupsPermission/index', $aData);
    }

    /**
     * Add minimal permission (read) to user
     * Show result and allow to set other permissions
     * @param integer $id SurveysGroups id
     * @return void
     */
    public function actionAddUser($id)
    {
        $model = $this->loadModel($id);
        if (!Permission::model()->hasSurveyGroupPermission($id, 'permission', 'create')) {
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
        $aData['buttons'] = array(
            'closebutton' => array(
                'url' => App()->createUrl("SurveysGroupsPermission/index", array("id" => $id)),
            ),
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
            $result['error'] = CHtml::errorSummary($oPermission);
        }

        $aData['aPermissionData']['result'] = $result;
        App()->getController()->render('/SurveysGroupsPermission/index', $aData);
    }


    /**
     * Add minimal permission (read) to all users in a group of user
     * Show result and allow to set other permissions
     * @param integer $id SurveysGroups id
     * @return void
     */
    public function actionAddUserGroup($id)
    {
        $model = $this->loadModel($id);
        if (!Permission::model()->hasSurveyGroupPermission($id, 'permission', 'create')) {
            throw new CHttpException(403, gT("You do not have permission to access this page."));
        }
        $ugid = App()->getRequest()->getPost('ugid');
        if (!$ugid) {
            throw new CHttpException(400, gT("Invalid action"));
        }
        /* Check if logged user can see user group */
        if (!in_array($ugid, getUserGroupList())) {
            throw new CHttpException(403, gT("You do not have permission to this user group."));
        }
        $aData = array(
            'model' => $model
        );
        $aData['subview'] = 'addUserGroupResult';
        $aData['buttons'] = array(
            'closebutton' => array(
                'url' => App()->createUrl("SurveysGroupsPermission/index", array("id" => $id)),
            ),
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
        App()->getController()->render('/SurveysGroupsPermission/index', $aData);
    }

    /**
     * Shown permissions list for user
     * @param integer $id SurveysGroups id
     * @param integer $to user id
     */
    public function actionViewUser($id, $to)
    {
        $oUser = User::model()->findByPk($to);
        if (empty($oUser)) {
            throw new CHttpException(401, gT("User not found"));
        }
        if (!in_array($to, getUserList('onlyuidarray'))) {
            throw new CHttpException(403, gT("You do not have permission to this user."));
        }
        if ($to == App()->user->getId()) {
            throw new CHttpException(403, gT("You do not set your own permission."));
        }
        $this->viewUserOrUserGroup($id, $to, 'user');
    }

    /**
     * Shown permissions list for user group
     * @param integer $id SurveysGroups id
     * @param integer $id group id
     */
    public function actionViewUserGroup($id, $to)
    {
        $oUserGroup = UserGroup::model()->findByPk($to);
        if (empty($oUserGroup)) {
            throw new CHttpException(401, gT("User group not found"));
        }
        /* Check if logged user can see user group */
        if (!in_array($to, getUserGroupList())) {
            throw new CHttpException(403, gT("You do not have permission to this user group."));
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
    public function actionSave($id)
    {
        $model = $this->loadModel($id);
        if (!Permission::model()->hasSurveyGroupPermission($id, 'permission', 'update')) {
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
                throw new CHttpException(403, gT("You do not have permission to this user."));
            }
            $uids = array($uid);
        }
        $set = App()->getRequest()->getPost('set');
        foreach ($set as $entity => $aPermissionSet) {
            foreach ($uids as $uid) {
                /* Permission::model()->setPermissions return true or break */
                Permission::model()->setPermissions(
                    $uid,
                    $id,
                    $entity,
                    $aPermissionSet
                );
            }
        }
        App()->setFlashMessage("Surveys groups permissions were successfully updated");
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
     */
    public function actionDeleteUser($id, $uid)
    {
        $model = $this->loadModel($id);
        if (!Permission::model()->hasSurveyGroupPermission($id, 'permission', 'delete')) {
            throw new CHttpException(403, gT("You do not have permission to access this page."));
        }
        $oUser = User::model()->findByPk($uid);
        if (empty($oUser)) {
            throw new CHttpException(401, gT("User not found"));
        }
        if (!in_array($uid, getUserList('onlyuidarray'))) {
            throw new CHttpException(403, gT("You do not have permission to this user."));
        }
        if ($uid == App()->user->getId()) {
            throw new CHttpException(403, gT("You do not delete your own permission."));
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
        $aData['buttons'] = array(
            'closebutton' => array(
                'url' => App()->createUrl("SurveysGroupsPermission/index", array("id" => $id)),
            ),
        );
        $aData['aPermissionData'] = array(
            'model' => $model,
            'oUser' => $oUser,
        );
        App()->getController()->render('/SurveysGroupsPermission/index', $aData);
    }

    /**
     * Shown permissions list for user or group
     * @param integer $id SurveysGroups id
     * @param integer $id user or group id
     * @param string $type user or group
     * @return void
     */
    private function viewUserOrUserGroup($id, $to, $type = 'user')
    {
        $model = $this->loadModel($id);
        if ($type == 'user') {
            $oUserGroup = null;
            $oUser = User::model()->findByPk($to);
            $userId = $to; // More clear after
        } else {
            $oUserGroup = UserGroup::model()->findByPk($to);
            $oUser = null;
        }
        $aSurveysGroupsPermissions = Permission::model()->getEntityBasePermissions('SurveysGroups');
        /* Set the current : @todo move to Permission::model ? Or an helper ?*/
        foreach (array_keys($aSurveysGroupsPermissions) as $sPermission) {
            $aSurveysGroupsPermissions[$sPermission]['current'] = array(
                'create' => array(
                    'checked' => false,
                    'disabled' => !Permission::model()->hasSurveyGroupPermission($id, $sPermission, 'create'),
                    'indeterminate' => false
                ),
                'read' => array(
                    'checked' => false,
                    'disabled' => !Permission::model()->hasSurveyGroupPermission($id, $sPermission, 'read'),
                    'indeterminate' => false
                ),
                'update' => array(
                    'checked' => false,
                    'disabled' => !Permission::model()->hasSurveyGroupPermission($id, $sPermission, 'update'),
                    'indeterminate' => false
                ),
                'delete' => array(
                    'checked' => false,
                    'disabled' => !Permission::model()->hasSurveyGroupPermission($id, $sPermission, 'delete'),
                    'indeterminate' => false
                ),
                'import' => array(
                    'checked' => false,
                    'disabled' => !Permission::model()->hasSurveyGroupPermission($id, $sPermission, 'import'),
                    'indeterminate' => false
                ),
                'export' => array(
                    'checked' => false,
                    'disabled' => !Permission::model()->hasSurveyGroupPermission($id, $sPermission, 'export'),
                    'indeterminate' => false
                ),
            );
            $aSurveysGroupsPermissions[$sPermission]['entity'] = 'SurveysGroups';
            if ($type == 'user') {
                $oCurrentPermissions = Permission::model()->find(
                    "entity = :entity AND entity_id = :entity_id AND uid = :uid AND permission = :permission",
                    array(
                        ":entity" => 'SurveysGroups',
                        ":entity_id" => $id,
                        ":uid" => $userId,
                        ":permission" => $sPermission
                    )
                );
                foreach (array_keys($aSurveysGroupsPermissions[$sPermission]['current']) as $sCrud) {
                    if ($aSurveysGroupsPermissions[$sPermission][$sCrud]) {
                        $havePermissionSet = !empty($oCurrentPermissions) && $oCurrentPermissions->getAttribute("{$sCrud}_p");
                        $aSurveysGroupsPermissions[$sPermission]['current'][$sCrud]['checked'] = $havePermissionSet;
                        $aSurveysGroupsPermissions[$sPermission]['current'][$sCrud]['indeterminate'] = !$havePermissionSet && Permission::model()->hasSurveyGroupPermission($id, $sPermission, $sCrud, $userId); // Set by global or owner
                    }
                }
            }
        }
        $aSurveysInGroupPermissions = Permission::model()->getEntityBasePermissions('SurveysInGroup');
        /* Set the current : @todo move to Permission::model ? Or an helper ?*/
        foreach (array_keys($aSurveysInGroupPermissions) as $sPermission) {
            $aSurveysInGroupPermissions[$sPermission]['current'] = array(
                'create' => array(
                    'checked' => false,
                    'disabled' => !Permission::model()->hasSurveyGroupPermission($id, $sPermission, 'create'),
                    'indeterminate' => false
                ),
                'read' => array(
                    'checked' => false,
                    'disabled' => !Permission::model()->hasSurveyGroupPermission($id, $sPermission, 'read'),
                    'indeterminate' => false
                ),
                'update' => array(
                    'checked' => false,
                    'disabled' => !Permission::model()->hasSurveyGroupPermission($id, $sPermission, 'update'),
                    'indeterminate' => false
                ),
                'delete' => array(
                    'checked' => false,
                    'disabled' => !Permission::model()->hasSurveyGroupPermission($id, $sPermission, 'delete'),
                    'indeterminate' => false
                ),
                'import' => array(
                    'checked' => false,
                    'disabled' => !Permission::model()->hasSurveyGroupPermission($id, $sPermission, 'import'),
                    'indeterminate' => false
                ),
                'export' => array(
                    'checked' => false,
                    'disabled' => !Permission::model()->hasSurveyGroupPermission($id, $sPermission, 'export'),
                    'indeterminate' => false
                )
            );
            $aSurveysInGroupPermissions[$sPermission]['entity'] = 'SurveysInGroup';
            if ($type == 'user') {
                $oCurrentPermissions = Permission::model()->find(
                    "entity = :entity AND entity_id = :entity_id AND uid = :uid AND permission = :permission",
                    array(
                        ":entity" => 'SurveysInGroup',
                        ":entity_id" => $id,
                        ":uid" => $userId,
                        ":permission" => $sPermission
                    )
                );
                foreach (array_keys($aSurveysInGroupPermissions[$sPermission]['current']) as $sCrud) {
                    if ($aSurveysInGroupPermissions[$sPermission][$sCrud]) {
                        $havePermissionSet = !empty($oCurrentPermissions) && $oCurrentPermissions->getAttribute("{$sCrud}_p");
                        $aSurveysInGroupPermissions[$sPermission]['current'][$sCrud]['checked'] = $havePermissionSet;
                        $aSurveysInGroupPermissions[$sPermission]['current'][$sCrud]['indeterminate'] = !$havePermissionSet && Permission::model()->hasSurveyGroupPermission($id, $sPermission, $sCrud, $userId); // Set by global or owner
                    }
                }
            }
        }
        $aPermissions = array_merge(
            $aSurveysGroupsPermissions,
            $aSurveysInGroupPermissions
        );
        $aData = array(
            'model' => $model,
            'subview' => 'setPermissionForm',
            'buttons' => array(
                'savebutton' => array(
                    'form' => 'permissionsSave'
                ),
                'saveandclosebutton' => array(
                    'form' => 'permissionsSave'
                ),
                'closebutton' => array(
                    'url' => App()->createUrl('surveyAdministration/listsurveys', array('#' => 'surveygroups')),
                ),
            ),
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
        if (!Permission::model()->hasSurveyGroupPermission($id, 'permission', 'read')) {
            throw new CHttpException(403, gT("You do not have permission to access this page."));
        }
        return $model;
    }
}
