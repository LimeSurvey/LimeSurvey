<?php
use LimeSurvey\PluginManager\AuthPluginBase;

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
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
 */

/**
 * Usermanagement Controller
 *
 * This controller is for the user management panel
 *
 * @package        LimeSurvey
 * @subpackage    Backend
 */
class UserManagement extends Survey_Common_Action
{
    /**
     * @inheritdoc
     */
    public function index()
    {
        $subAction = Yii::app()->request->getParam('sa', null);
        if ($subAction == null) {
            $this->getController()->redirect(App()->createUrl('admin/usermanagement/sa/view'));
            return;
        }
        throw new CHttpException(404, gT('The specified page cannot be found.'));
    }

    /**
     * Basic view method
     */
    public function view()
    {
        if (!Permission::model()->hasGlobalPermission('users', 'read')) {
            return $this->getController()->renderPartial(
                '/admin/usermanagement/partial/error',
                ['errors' => [gT("You do not have permission to access this page.")], 'noButton' => true]
            );
        }
        if (isset($_GET['pageSize'])) {
            Yii::app()->user->setState('pageSize', $this->api->getRequest()->getParam('pageSize'));
        }
        App()->getClientScript()->registerPackage('usermanagement');
        App()->getClientScript()->registerPackage('bootstrap-select2');

        $aData = [];
        $model = new User('search');
        $model->setAttributes(Yii::app()->getRequest()->getParam('User'), false);
        $aData['model'] = $model;

        $aData['columnDefinition'] = $model->managementColums;
        $aData['pageSize'] = Yii::app()->user->getState('pageSize', Yii::app()->params['defaultPageSize']);
        $aData['formUrl'] = App()->createUrl('admin/usermanagement/sa/view');

        $aData['massiveAction'] = App()->getController()->renderPartial(
            '/admin/usermanagement/massiveAction/_selector',
            [],
            true,
            false
        );

        $this->_renderWrappedTemplate('usermanagement', 'view', $aData);
    }

    /**
     * Open modal to edit, or create a new user
     *
     * @param integer $userid
     * @return string
     */
    public function editusermodal($userid = null)
    {

        if (
            ($userid === null && !Permission::model()->hasGlobalPermission('users', 'create'))
            || ($userid !== null && !Permission::model()->hasGlobalPermission('users', 'update'))
        ) {
            return $this->getController()->renderPartial(
                '/admin/usermanagement/partial/error',
                ['errors' => [gT("You do not have permission to access this page.")]]
            );
        }
        $oUser = $userid === null ? new User() : User::model()->findByPk($userid);
        $randomPassword = $this->getRandomPassword();
        return Yii::app()->getController()->renderPartial('/admin/usermanagement/partial/addedituser', ['oUser' => $oUser, 'randomPassword' => $randomPassword]);
    }

    /**
     * Stores changes to user, or triggers userCreateEvent
     *
     * @param integer $userid
     * @return string | JSON
     */
    public function applyedit()
    {
        if (!Permission::model()->hasGlobalPermission('users', 'update')) {
            return Yii::app()->getController()->renderPartial('/admin/usermanagement/partial/json.php', ["data" => [
                'success' => false,
                'error' => gT("You do not have permission to access this page."),
            ]]);
        }

        $aUser = Yii::app()->request->getParam('User');

        $paswordTest = Yii::app()->request->getParam('password_repeat', false);
        if (!empty($paswordTest)) {

            if ($paswordTest !== $aUser['password']) {
                return Yii::app()->getController()->renderPartial('/admin/usermanagement/partial/json', ["data" => [
                    'success' => false,
                    'error' => gT('Passwords do not match'),
                ]]);
            }

            $oPasswordTestEvent = new PluginEvent('checkPasswordRequirement');
            $oPasswordTestEvent->set('password', $paswordTest);
            $oPasswordTestEvent->set('passwordOk', true);
            $oPasswordTestEvent->set('passwordError', '');
            Yii::app()->getPluginManager()->dispatchEvent($oPasswordTestEvent);

            if (!$oPasswordTestEvent->get('passwordOk')) {
                return Yii::app()->getController()->renderPartial('/admin/usermanagement/partial/json', ["data" => [
                    'success' => false,
                    'error' => gT('Passwords does not fulfill minimum requirement:') . '<br/>' . $oPasswordTestEvent->get('passwordError'),
                ]]);
            }
        }

        if (!isset($aUser['uid']) || $aUser['uid'] == null) {
            $sendMail = (bool) Yii::app()->request->getPost('preset_password', false);
            $aUser = $this->_createNewUser($aUser);
            $sReturnMessage = '';
            $success = true;

            if ($sendMail && (isset($newUser['sendMail']) && $newUser['sendMail'] == true)) {
                if ($this->_sendAdminMail('registration', $aUser)) {
                    $sReturnMessage = gT("Success");
                    $sReturnMessage .= CHtml::tag("p", array(), sprintf(gT("Username : %s - Email : %s."), $aUser['users_name'], $aUser['email']));
                    $sReturnMessage .= CHtml::tag("p", array(), gT("An email with a generated password was sent to the user."));
                } else {
                    // has to be sent again or no other way
                    $sReturnMessage = gT("Warning");
                    $sReturnMessage .= CHtml::tag("p", array(), sprintf(gT("Email to %s (%s) failed."), "<strong>" . $aUser['users_name'] . "</strong>", $aUser['email']));
                    $sReturnMessage .= CHtml::tag("p", array('class' => 'alert alert-danger'), $mailer->getError());
                    $success = false;
                }
            }

            $display_user_password_in_html = Yii::app()->getConfig("display_user_password_in_html");
            $sReturnMessage .= $display_user_password_in_html ? CHtml::tag("p", array('class' => 'alert alert-danger'), 'New password set: <b>' . $new_pass . '</b>') : '';

            return Yii::app()->getController()->renderPartial('/admin/usermanagement/partial/json', ["data" => [
                'success' => $success,
                'html' => Yii::app()->getController()->renderPartial('/admin/usermanagement/partial/success', ['sMessage' => $sReturnMessage], true),
            ]]);
            Yii::app()->end();
        }

        $aUser = $this->_editUser($aUser);
        if ($aUser === false) {
            return Yii::app()->getController()->renderPartial('/admin/usermanagement/partial/json', ["data" => [
                'success' => false,
                'error' => print_r($oUser->getErrors(), true),
            ]]);
        }

        return Yii::app()->getController()->renderPartial('/admin/usermanagement/partial/json', ["data" => [
            'success' => true,
            'html' => Yii::app()->getController()->renderPartial('/admin/usermanagement/partial/success', $aReturnArray, true),
        ]]);

    }

    /**
     * Show some user detail and statistics
     *
     * @return string
     */
    public function viewuser($userid)
    {
        if (!Permission::model()->hasGlobalPermission('users', 'read')) {
            return $this->getController()->renderPartial(
                '/admin/usermanagement/partial/error',
                ['errors' => [gT("You do not have permission to access this page.")], 'noButton' => true]
            );
        }
        $oUser = User::model()->findByPk($userid);

        $usergroups = array_map(function ($oUGMap) {
            return $oUGMap->group->name;
        }, UserInGroup::model()->findAllByAttributes(['uid' => $oUser->uid]));

        return $this->getController()->renderPartial('/admin/usermanagement/partial/showuser', ['usergroups' => $usergroups, 'oUser' => $oUser]);
    }

    /**
     * Takes ownership on user after confirmation
     *
     * @return void
     */
    public function takeownership()
    {
        if (!Permission::model()->hasGlobalPermission('users', 'update')) {
            return $this->getController()->renderPartial(
                '/admin/usermanagement/partial/error',
                ['errors' => [gT("You do not have permission to access this page.")], 'noButton' => true]
            );
        }
        $userId = Yii::app()->request->getPost('userid');
        $oUser = User::model()->findByPk($userId);
        $oUser->parent_id = Yii::app()->user->id;
        $oUser->save();
        App()->getController()->redirect(App()->createUrl('/admin/usermanagement'));
    }

    /**
     * Deletes a user after  confirmation
     *
     * @return void
     */
    public function deleteconfirm()
    {
        if (!Permission::model()->hasGlobalPermission('users', 'delete')) {
            return $this->getController()->renderPartial(
                '/admin/usermanagement/partial/error',
                ['errors' => [gT("You do not have permission to access this page.")], 'noButton' => true]
            );
        }
        $userId = Yii::app()->request->getPost('userid');
        $oUser = User::model()->findByPk($userId);
        $oUser->delete();
        App()->getController()->redirect(App()->createUrl('/admin/usermanagement'));
    }

    /**
     * Opens a modal to edit user template permissions
     *
     * @return string
     */
    public function usertemplatepermissions()
    {
        if (!Permission::model()->hasGlobalPermission('users', 'update')) {
            return $this->getController()->renderPartial(
                '/admin/usermanagement/partial/error',
                ['errors' => [gT("You do not have permission to access this page.")], 'noButton' => true]
            );
        }
        $aTemplateModels = Template::model()->findAll();
        $oRequest = Yii::app()->request;
        $userId = $oRequest->getParam('userid');
        $oUser = User::model()->findByPk($userId);

        $aTemplates = array_map(function ($oTemplate) use ($userId) {
            $oPermission = Permission::model()->findByAttributes(array('permission' => $oTemplate->folder, 'uid' => $userId, 'entity' => 'template'));
            $aTemplate = $oTemplate->attributes;
            $aTemplate['value'] = $oPermission == null ? 0 : $oPermission->read_p;
            return $aTemplate;
        }, $aTemplateModels);

        return $this->getController()->renderPartial(
            '/admin/usermanagement/partial/edittemplatepermissions',
            [
                "oUser" => $oUser,
                "aTemplates" => $aTemplates,
            ]
        );
    }

    /**
     * Opens a modal to edit user permissions
     *
     * @return string
     */
    public function userpermissions()
    {
        if (!Permission::model()->hasGlobalPermission('users', 'update')) {
            return $this->getController()->renderPartial(
                '/admin/usermanagement/partial/error',
                ['errors' => [gT("You do not have permission to access this page.")], 'noButton' => true]
            );
        }

        $oRequest = Yii::app()->request;
        $userId = $oRequest->getParam('userid');
        $oUser = User::model()->findByPk($userId);

        // Check permissions
        $aBasePermissions = Permission::model()->getGlobalBasePermissions();
        if (!Permission::model()->hasGlobalPermission('superadmin', 'read')) {
            // if not superadmin filter the available permissions as no admin may give more permissions than he owns
            Yii::app()->session['flashmessage'] = gT("Note: You can only give limited permissions to other users because your own permissions are limited, too.");
            $aFilteredPermissions = array();
            foreach ($aBasePermissions as $PermissionName => $aPermission) {
                foreach ($aPermission as $sPermissionKey => &$sPermissionValue) {
                    if ($sPermissionKey != 'title' && $sPermissionKey != 'img' && !Permission::model()->hasGlobalPermission($PermissionName, $sPermissionKey)) {
                        $sPermissionValue = false;
                    }
                }
                // Only show a row for that permission if there is at least one permission he may give to other users
                if ($aPermission['create'] || $aPermission['read'] || $aPermission['update'] || $aPermission['delete'] || $aPermission['import'] || $aPermission['export']) {
                    $aFilteredPermissions[$PermissionName] = $aPermission;
                }
            }
            $aBasePermissions = $aFilteredPermissions;
        }

        $aAllSurveys = Survey::model()->findAll();
        $aMySurveys = array_filter($aAllSurveys, function ($oSurvey) {
            if (Permission::model()->hasGlobalPermission('superadmin', 'read')) {
                return true;
            }
            if ($oSurvey->owner_id == App()->user->id) {
                return true;
            }
            return array_reduce($oSurvey->permissions, function ($coll, $oPermission) {
                if ($oPermission->permission == 'surveysecurity' && $oPermission->update_p == 1 && $oPermission->uid == App()->user->id) {
                    return true;
                }
                return $coll;
            }, false);
        });
        return $this->getController()->renderPartial(
            '/admin/usermanagement/partial/editpermissions',
            [
                "oUser" => $oUser,
                "aBasePermissions" => $aBasePermissions,
            ]
        );
    }

    /**
     * Stores the changed permissions
     *
     * @return string | JSON
     */
    public function applythemepermissions()
    {
        if (!(Permission::model()->hasGlobalPermission('users', 'update') && Permission::model()->hasGlobalPermission('templates', 'update'))) {
            return $this->getController()->renderPartial(
                '/admin/usermanagement/partial/error',
                ['errors' => [gT("You do not have permission to access this page.")], 'noButton' => true]
            );
        }

        $userId = Yii::app()->request->getPost('userid');
        $aPermissions = Yii::app()->request->getPost('Permission', []);

        $aTemplatePermissions = Yii::app()->request->getPost('TemplatePermissions', []);
        $aTemplates = Template::model()->findAll();

        // foreach ($aTemplates as $oTemplate) {
        //     $transferredOption = Yii::app()->request->getPost($oTemplate->folder, false);
        //     $aTemplatePermissions[$oTemplate->folder] = $transferredOption !== false ? $transferredOption : 0;
        // }
        $results = [];
        foreach ($aTemplatePermissions as $key => $value) {
            $oPermission = Permission::model()->findByAttributes(array('permission' => $key, 'uid' => $userId, 'entity' => 'template'));
            if (empty($oPermission)) {
                $oPermission = new Permission;
                $oPermission->uid = $userId;
                $oPermission->permission = $key;
                $oPermission->entity = 'template';
                $oPermission->entity_id = 0;
            }
            $oPermission->read_p = $value;
            $results[$key] = $oPermission->save();
        }

        return Yii::app()->getController()->renderPartial('/admin/usermanagement/partial/json', ["data" => [
            'success' => true,
            'html' => $this->getController()->renderPartial('/admin/usermanagement/partial/permissionsuccess', ['results' => $results], true),
        ]]);
    }
    /**
     * Stores the changed permissions
     *
     * @return string | JSON
     */
    public function saveuserpermissions()
    {
        if (!Permission::model()->hasGlobalPermission('users', 'update')) {
            return $this->getController()->renderPartial(
                '/admin/usermanagement/partial/error',
                ['errors' => [gT("You do not have permission to access this page.")], 'noButton' => true]
            );
        }
        $userId = Yii::app()->request->getPost('userid');
        $aPermissions = Yii::app()->request->getPost('Permission', []);
        $results = $this->applyPermissionFromArray($userId, $aPermissions);

        $oUser = User::model()->findByPk($userId);
        $oUser->modified = date('Y-m-d H:i:s');
        $save = $oUser->save();

        return Yii::app()->getController()->renderPartial('/admin/usermanagement/partial/json', ["data" => [
            'success' => true,
            'html' => $this->getController()->renderPartial('/admin/usermanagement/partial/permissionsuccess', ['results' => $results], true),
        ]]);
    }

    /**
     * Opens the modal to add dummy users
     *
     * @return string
     */
    public function addrole()
    {
        $userId = Yii::app()->request->getParam('userid');
        $oUser = User::model()->findByPk($userId);
        $aPermissiontemplates = Permissiontemplates::model()->findAll();
        $aPossibleRoles = [
            '' => gT('No role')
        ];
        array_walk(
            $aPermissiontemplates,
            function ($oPermissionRole) use (&$aPossibleRoles) {
                $aPossibleRoles[$oPermissionRole->ptid] = $oPermissionRole->name;
            }
        );
        $aCurrentRoles = array_map(function ($oRole) {return $oRole->ptid;}, $oUser->roles);

        return $this->getController()->renderPartial(
            '/admin/usermanagement/partial/addrole',
            [
                'oUser' => $oUser,
                'aPossibleRoles' => $aPossibleRoles,
                'aCurrentRoles' => $aCurrentRoles,
            ]
        );
    }
    /**
     * Apply role to user
     *
     *
     * @return string
     */
    public function applyaddrole()
    {
        if (!Permission::model()->hasGlobalPermission('users', 'update')) {
            return $this->getController()->renderPartial(
                '/admin/usermanagement/partial/error',
                ['errors' => [gT("You do not have permission to access this page.")], 'noButton' => true]
            );
        }
        $iUserId = Yii::app()->request->getPost('userid');
        $aUserRoleIds = Yii::app()->request->getPost('roleselector', []);
        $results = [];
        
        $results['clear'] = Permissiontemplates::model()->clearUser($iUserId);
        foreach($aUserRoleIds as $iUserRoleId) {
            if ($iUserRoleId == '' ) { continue; }
            $results[$iUserRoleId] = Permissiontemplates::model()->applyToUser($iUserId, $iUserRoleId);
        }

        return Yii::app()->getController()->renderPartial('/admin/usermanagement/partial/json', ["data" => [
            'success' => true,
            'html' => $this->getController()->renderPartial('/admin/usermanagement/partial/permissionsuccess', ['results' => $results], true),
        ]]);
    }
    /**
     * Stores the permission settings run via MassEdit
     *
     * @return string
     */
    public function batchPermissions()
    {
        if (!Permission::model()->hasGlobalPermission('users', 'update')) {
            return $this->getController()->renderPartial(
                '/admin/usermanagement/partial/error',
                ['errors' => [gT("You do not have permission to access this page.")], 'noButton' => true]
            );
        }
        $userIds = Yii::app()->request->getPost('userids', []);
        $aPermissions = Yii::app()->request->getPost('Permission', []);
        $results = [];
        foreach ($userIds as $iUserId) {
            $results[$iUserId] = $this->applyPermissionFromArray($iUserId, $aPermissions);
            $oUser = User::model()->findByPk($iUserId);
            $oUser->modified = date('Y-m-d H:i:s');
            $results[$iUserId]['save'] = $oUser->save();
        }

        return $this->getController()->renderPartial('/admin/usermanagement/partial/permissionsuccess', ['results' => $results, "noButton" => true]);

    }

    /**
     * Method to resend a password to selected surveyadministrators (MassAction)
     *
     * @return String
     */
    public function batchSendAndResetLoginData()
    {
        if (!Permission::model()->hasGlobalPermission('users', 'update')) {
            return $this->getController()->renderPartial(
                '/admin/usermanagement/partial/error',
                ['errors' => [gT("You do not have permission to access this page.")], 'noButton' => true]
            );

        }

        $userIds = json_decode(Yii::app()->request->getPost('sItems', "[]"));
        $entity_ids = Yii::app()->request->getPost('entity_ids', []);
        $permissionclass = Yii::app()->request->getPost('permissionclass');

        $results = [];
        foreach ($userIds as $userId) {
            $oUser = User::model()->findByPk($userId);
            $result[] = $this->resetLoginData($oUser, true);
            $oUser->modified = date('Y-m-d H:i:s');
            $result['saved'] = $oUser->save();
            $results[] = $result;
        }

        $success = array_reduce($results, function ($coll, $arr) {
            return $coll = $coll && $arr['saved'];
        }, true);

        if (!$success) {

        }

        return $this->getController()->renderPartial(
            '/admin/usermanagement/partial/success',
            [
                'sMessage' => gT('Emails successfully sent'),
                'sDebug' => json_encode($results, JSON_PRETTY_PRINT),
                'noButton' => true,
            ]
        );
    }
    /**
     * Mass edition delete user
     *
     *
     * @return string
     */
    public function batchDelete()
    {
        if (!Permission::model()->hasGlobalPermission('superadmin', 'read')) {
            return $this->getController()->renderPartial(
                '/admin/usermanagement/partial/error',
                ['errors' => [gT("You do not have permission to access this page.")], 'noButton' => true]
            );
        }
        $aItems = json_decode(Yii::app()->request->getPost('sItems', []));
        $results = [];
        foreach ($aItems as $sItem) {
            $oUser = User::model()->findByPk($sItem);
            $results[$oUser->uid] = $oUser->delete();
        }

        $this->getController()->renderPartial('/admin/usermanagement/partial/success', ['sMessage' => gT('Users successfully deleted'), 'sDebug' => json_encode($results, JSON_PRETTY_PRINT), 'noButton' => true]);
    }

    /**
     * Mass edition apply roles
     *
     *
     * @return string
     */
    public function batchAddGroup()
    {
        if (!Permission::model()->hasGlobalPermission('users', 'delete')) {
            return $this->getController()->renderPartial(
                '/admin/usermanagement/partial/error',
                ['errors' => [gT("You do not have permission to access this page.")], 'noButton' => true]
            );
        }
        $aItems = json_decode(Yii::app()->request->getPost('sItems', []));
        $iUserGroupId = Yii::app()->request->getPost('addtousergroup');
        $oUserGroup = UserGroup::model()->findByPk($iUserGroupId);
        $results = [];
        foreach ($aItems as $sItem) {
            if (!$oUserGroup->hasUser($sItem)) {
                $results[$sItem] = $oUserGroup->addUser($sItem);
            }
        }

        $this->getController()->renderPartial('/admin/usermanagement/partial/success', ['sMessage' => gT('Users successfully deleted'), 'sDebug' => json_encode($results, JSON_PRETTY_PRINT), 'noButton' => true]);
    }

    /**
     * Mass edition apply roles
     *
     *
     * @return string
     */
    public function batchApplyRoles()
    {
        if (!Permission::model()->hasGlobalPermission('users', 'update')) {
            return $this->getController()->renderPartial(
                '/admin/usermanagement/partial/error',
                ['errors' => [gT("You do not have permission to access this page.")], 'noButton' => true]
            );
        }
        $aItems = json_decode(Yii::app()->request->getPost('sItems', []));
        $aUserRoleIds = Yii::app()->request->getPost('roleselector');
        $results = [];
        foreach ($aItems as $sItem) {
            foreach($aUserRoleIds as $iUserRoleId) {
                $results[$sItem.'-'.$iUserRoleId] = Permissiontemplates::model()->applyToUser($sItem, $iUserRoleId);
            }
        }

        $this->getController()->renderPartial('/admin/usermanagement/partial/success', ['sMessage' => gT('Users successfully deleted'), 'sDebug' => json_encode($results, JSON_PRETTY_PRINT), 'noButton' => true]);
    }

    /**
     * Opens the modal to add dummy users
     *
     * @return string
     */
    public function adddummyuser()
    {
        return $this->getController()->renderPartial('/admin/usermanagement/partial/adddummyuser', []);
    }

    /**
     * Creates a batch of dummy users
     *
     *
     * @return string | JSON
     */
    public function runadddummyuser()
    {
        if (!Permission::model()->hasGlobalPermission('users', 'create')) {
            return $this->getController()->renderPartial(
                '/admin/usermanagement/partial/error',
                ['errors' => [gT("You do not have permission to access this page.")], 'noButton' => true]
            );
        }
        $times = App()->request->getParam('times', 5);
        $passwordsize = (int) App()->request->getParam('passwordsize', 5);
        $passwordsize = $passwordsize < 8 || is_nan($passwordsize) ? 8 : $passwordsize;
        $prefix = App()->request->getParam('prefix', 'randuser_');
        $email = App()->request->getParam('email', User::model()->findByPk(App()->user->id)->email);

        $randomUsers = [];

        for (; $times > 0; $times--) {
            $name = $this->getRandomUsername($prefix);
            $password = $this->getRandomPassword($passwordsize);
            $oUser = new User;
            $oUser->users_name = $name;
            $oUser->full_name = $name;
            $oUser->email = $email;
            $oUser->parent_id = App()->user->id;
            $oUser->created = date('Y-m-d H:i:s');
            $oUser->modified = date('Y-m-d H:i:s');
            $oUser->password = password_hash($password, PASSWORD_DEFAULT);
            $save = $oUser->save();
            $randomUsers[] = ['username' => $name, 'password' => $password, 'save' => $save];
            //Permission::model()->setGlobalPermission($oUser->uid, 'auth_db');
        }

        return Yii::app()->getController()->renderPartial('/admin/usermanagement/partial/json', ["data" => [
            'success' => true,
            'html' => $this->getController()->renderPartial('/admin/usermanagement/partial/createdrandoms', ['randomUsers' => $randomUsers, 'filename' => $prefix], true),
        ]]);
    }

    /**
     * Calls up a modal to import users via csv file
     *
     *
     * @return string
     */
    public function importuser()
    {
        if (!Permission::model()->hasGlobalPermission('users', 'create')) {
            return $this->getController()->renderPartial(
                '/admin/usermanagement/partial/error',
                ['errors' => [gT("You do not have permission to access this page.")], 'noButton' => true]
            );
        }
        return $this->getController()->renderPartial('/admin/usermanagement/partial/importuser', []);
    }

    /**
     * Creates users from an uploaded CSV file
     *
     *
     * @return string
     */
    public function importcsv()
    {
        if (!Permission::model()->hasGlobalPermission('users', 'create')) {
            return $this->getController()->renderPartial(
                '/admin/usermanagement/partial/error',
                ['errors' => [gT("You do not have permission to access this page.")], 'noButton' => true]
            );
        }
        $created = [];
        $aNewUsers = UserParser::getDataFromCSV($_FILES);
        foreach ($aNewUsers as $aNewUser) {
            if (isset($aNewUser['firstname']) && isset($aNewUser['surname'])) {
                $name = $this->filterSpecials("{$aNewUser['firstname']}.{$aNewUser['surname']}");
                $fullname = "{$aNewUser['firstname']} {$aNewUser['surname']}";
            } elseif (isset($aNewUser['full_name'])) {
                $fullname = $aNewUser['full_name'];
                $name = $this->filterSpecials($fullname);
            }

            $password = $this->getRandomPassword(8);
            if (User::model()->findByAttributes(['users_name' => $name]) !== null) {
                continue;
            }

            $save = $this->_createNewUser([
                'users_name' => $name,
                'full_name' => $fullname,
                'password' => $password,
                'email' => $aNewUser['email'],
            ], false);

            if ($save) {
                $created[] = [
                    'uid' => $oUser->uid,
                    'username' => $name,
                    'full_name' => $fullname,
                    'email' => $aNewUser['email'],
                    'password' => $password,
                    'save' => $save,
                ];
            }
        }
        return $this->getController()->renderPartial('userimported', ['created' => $created], true);
    }

    /**
     * Mass import from a well-formed json string
     *
     * @return void
     */
    public function importfromjson()
    {
        if (!Permission::model()->hasGlobalPermission('users', 'create')) {
            return $this->getController()->renderPartial(
                '/admin/usermanagement/partial/error',
                ['errors' => [gT("You do not have permission to access this page.")], 'noButton' => true]
            );
        }

        $sJsonUserString = Yii::app()->request->getPost('jsonstring', '{}');

        $result = [];

        if ($sJsonUserString != '{}') {
            $aJsonUsers = json_decode($sJsonUserString, true);
            $result = ['created' => [], 'updated' => []];
            foreach ($aJsonUsers as $aUserData) {
                $storeTo = 'updated';
                $oUser = User::model()->findByAttributes(['email' => $aUserData['email']]);
                if ($oUser == null) {
                    $storeTo = 'created';
                    $aUser = $this->_createNewUser($aUserData, false);
                    $oUser = new User;
                    $oUser->users_name = $aUserData['username'];
                    $oUser->full_name = $aUserData['full_name'];
                    $oUser->email = $aUserData['email'];
                    $oUser->parent_id = App()->user->id;
                    $oUser->created = date('Y-m-d H:i:s');
                    $oUser->modified = date('Y-m-d H:i:s');
                }
                $oUser->password = password_hash($aUserData['password'], PASSWORD_DEFAULT);
                $save = $oUser->save();

                $result[$storeTo][] = [
                    'uid' => $oUser->uid,
                    'username' => $oUser->users_name,
                    'full_name' => $oUser->full_name,
                    'email' => $oUser->email,
                    'password' => $aUserData['password'],
                    'save' => $save,
                ];
            }
        }
        $this->_renderWrappedTemplate('usermanagement', 'importfromjson', ['result' => $result]);
    }

    ##### PRIVATE METHODS #####

    /**
     * Resets the password for one user
     *
     * @param User $oUser User model
     * @param boolean $sendMail Send a mail to the user
     * @return array [success, uid, username, password]
     */
    private function resetLoginData(&$oUser, $sendMail = false)
    {
        $newPassword = $this->getRandomPassword(8);
        $oUser->setPassword($newPassword);
        $success = true;
        if ($sendMail == true) {
            $aUser = $oUser->attributes;
            $aUser['rawPassword'] = $newPassword;
            $success = $this->_sendAdminMail('resetPassword', $aUser, $newPassword);
        }
        return [
            'success' => $success, 'uid' => $oUser->uid, 'username' => $oUser->users_name, 'password' => $newPassword,
        ];
    }

    private function _createNewUser($aUser, $sendMail = true)
    {
        if (!Permission::model()->hasGlobalPermission('users', 'create')) {
            return Yii::app()->getController()->renderPartial('/admin/usermanagement/partial/json', ["data" => [
                'success' => false,
                'error' => gT("You do not have permissionfor this action."),
            ]]);
        }

        $aUser['users_name'] = flattenText($aUser['users_name']);

        if (empty($aUser['users_name'])) {
            return Yii::app()->getController()->renderPartial('/admin/usermanagement/partial/json', ["data" => [
                'success' => false,
                'error' => gT("A username was not supplied or the username is invalid."),
            ]]);
        }

        if (User::model()->find("users_name=:users_name", array(':users_name' => $aUser['users_name']))) {
            return Yii::app()->getController()->renderPartial('/admin/usermanagement/partial/json', ["data" => [
                'success' => false,
                'error' => gT("A user with this username already exists."),
            ]]);
        }

        $event = new PluginEvent('createNewUser');
        $event->set('errorCode', AuthPluginBase::ERROR_NOT_ADDED);
        $event->set('errorMessageTitle', gT("Failed to add user"));
        $event->set('errorMessageBody', gT("Plugin is not active"));
        $event->set('preCollectedUserArray', $aUser);

        Yii::app()->getPluginManager()->dispatchEvent($event);

        if ($event->get('errorCode') != AuthPluginBase::ERROR_NONE) {
            return Yii::app()->getController()->renderPartial('/admin/usermanagement/partial/json', ["data" => [
                'success' => false,
                'error' => $event->get('errorMessageTitle') . '<br/>' . $event->get('errorMessageBody'),
                'debug' => ['title' => $event->get('errorMessageTitle'), 'body' => $event->get('errorMessageBody'), 'code' => $event->get('errorCode'), 'event' => $event],
            ]]);
        }

        $success = true;
        $new_user = $aUser['users_name'];
        $iNewUID = $event->get('newUserID');
        $new_pass = $event->get('newPassword');
        $new_email = $event->get('newEmail');
        $new_full_name = $event->get('newFullName');

        // add default template to template rights for user
        Permission::model()->insertSomeRecords(array('uid' => $iNewUID, 'permission' => getGlobalSetting('defaulttheme'), 'entity' => 'template', 'read_p' => 1, 'entity_id' => 0));
        // add default usersettings to the user
        SettingsUser::applyBaseSettings($iNewUID);

        return User::model()->findByPk($iNewUID)->attributes;
    }

    /**
     * private method to store a change in a user
     * return json to return to js frontend
     *
     * @param array $aUser
     * @param integer $userid
     * @return string
     */
    private function _editUser($aUser)
    {
        $oUser = User::model()->findByPk($aUser['uid']);
        $aReturnArray = ["sMessage" => gT('User successfully updated')];
        if (isset($aUser['password'])) {
            $rawPassword = $aUser['password'];
            $aUser['password'] = password_hash($rawPassword, PASSWORD_DEFAULT);
            $display_user_password_in_html = Yii::app()->getConfig("display_user_password_in_html");
            $aReturnArray["sMessage"] .= $display_user_password_in_html ? "<br/>New password set: " . $rawPassword : '';
        }

        $oUser->setAttributes($aUser);
        $oUser->modified = date('Y-m-d H:i:s');

        $aUser = [];
        if ($oUser->save()) {
            return $oUser->attributes();
        }
        return false;
    }

    /**
     * Send the registration email to a new survey administrator
     * @TODO: make this user configurable by TWIG, or similar
     *
     * @param array $aUser
     * @return boolean if send is successfull
     */
    public function _sendAdminMail($type = 'registration', $aUser, $newPassword = null)
    {
        switch ($type) {
            case "resetPassword":
                $renderArray = [
                    'surveyapplicationname' => Yii::app()->getConfig("sitename"),
                    'emailMessage' => sprintf(gT("Hello %s,"), $aUser['full_name']) . "<br />"
                    . sprintf(gT("this is an automated email to notify that your login credentials for '%s' have been reset."), Yii::app()->getConfig("sitename")),
                    'credentialsText' => gT("Here are you're new credentials."),
                    'siteadminemail' => Yii::app()->getConfig("siteadminemail"),
                    'linkToAdminpanel' => $this->getController()->createAbsoluteUrl("/admin"),
                    'username' => $aUser['users_name'],
                    'password' => $aUser['rawPassword'],
                    'mainLogoFile' => LOGO_URL,
                    'showPasswordSection' => Yii::app()->getConfig("auth_webserver") === false && Permission::model()->hasGlobalPermission('auth_db', 'read', $aUser['uid']),
                    'showPassword' => (Yii::app()->getConfig("display_user_password_in_email") === true),
                ];
                $subject = "[" . Yii::app()->getConfig("sitename") . "] " . gT("Your login credentials have been reset");
                $emailType = "addadminuser";
                break;
            case 'registration':
            default:
                $renderArray = [
                    'surveyapplicationname' => Yii::app()->getConfig("sitename"),
                    'emailMessage' => sprintf(gT("Hello %s,"), $aUser['full_name']) . "<br />"
                    . sprintf(gT("this is an automated email to notify that a user has been created for you on the site '%s'.."), Yii::app()->getConfig("sitename")),
                    'credentialsText' => gT("You can use now the following credentials to log into the site:"),
                    'siteadminemail' => Yii::app()->getConfig("siteadminemail"),
                    'linkToAdminpanel' => $this->getController()->createAbsoluteUrl("/admin"),
                    'username' => $aUser['users_name'],
                    'password' => $aUser['rawPassword'],
                    'mainLogoFile' => LOGO_URL,
                    'showPasswordSection' => Yii::app()->getConfig("auth_webserver") === false && Permission::model()->hasGlobalPermission('auth_db', 'read', $aUser['uid']),
                    'showPassword' => (Yii::app()->getConfig("display_user_password_in_email") === true),
                ];
                $subject = "[" . Yii::app()->getConfig("sitename") . "] " . gT("An account has been created for you");
                $emailType = "addadminuser";
                break;
        }

        $body = Yii::app()->getController()->renderPartial('/admin/usermanagement/partial/usernotificationemail', $renderArray, true);

        $oCurrentlyLoggedInUser = User::model()->findByPk(Yii::app()->user->id);

        $mailer = new LimeMailer;
        $mailer->addAddress($aUser['email'], $aUser['full_name']);
        $mailer->Subject = $subject;
        $mailer->setFrom($oCurrentlyLoggedInUser->email, $oCurrentlyLoggedInUser->users_name);
        $mailer->Body = $body;
        $mailer->isHtml(true);
        $mailer->emailType = $emailType;
        return $mailer->sendMessage();

    }

    /**
     * Filters special characters to simple ones
     *
     * @param string $in String that needs to be changed
     * @return string
     */
    private function filterSpecials($in)
    {
        $was = array("ä", "ö", "ü", "Ä", "Ö", "Ü", "ß", "é", "â", " ");
        $wie = array("ae", "oe", "ue", "Ae", "Oe", "Ue", "ss", 'e', "a", ".");
        $clean = str_replace($was, $wie, $in);
        return preg_replace("/[^a-zA-Z-.]/", "", $clean);
    }

    /**
     * Creates a random string
     *
     * @return string
     */
    private function getRandomString()
    {
        if (is_callable('openssl_random_pseudo_bytes')) {
            $uiq = openssl_random_pseudo_bytes(128);
        } else {
            $uiq = decbin(rand(1000000, 9999999) * (rand(100, 999) . rand(100, 999) . rand(100, 999) . rand(100, 999)));
        }
        return hash('sha256', bin2hex($uiq));
    }

    /**
     * Erstellt einen zufälligen Benutzernamen mit Präfix
     *
     * Prüft ob der Name einzigartig ist
     *
     * @param string $prefix der Präfix
     * @return string
     */
    private function getRandomUsername($prefix)
    {
        do {
            $rand = $this->getRandomString();
            $username = $prefix . '_' . substr($rand, rand(0, strlen($rand) - 6), 4);
            $oUser = User::model()->findByAttributes(['users_name' => $username]);
        } while ($oUser != null);
        return $username;
    }

    /**
     * Creates a random password through the core plugin
     *
     * @param integer $length Length of the password
     * @return string
     */
    private function getRandomPassword($length = 8)
    {
        $oGetPasswordEvent = new PluginEvent('createRandomPassword');
        $oGetPasswordEvent->set('targetSize', $length);
        Yii::app()->getPluginManager()->dispatchEvent($oGetPasswordEvent);
        $pw = $oGetPasswordEvent->get('password');
        return $pw;
    }

    /**
     * Adds permission to a users
     * Needs an array in the form of [PERMISSIONID][PERMISSION]
     *
     * @param int $iUserId
     * @param array $aPermissionArray
     * @return array
     */
    private function applyPermissionFromArray($iUserId, $aPermissionArray)
    {
        $oCriteria = new CDbCriteria();
        $oCriteria->compare('uid', $iUserId);
        $oCriteria->compare('entity_id', 0);
        //Kill all Permissions without entity.
        $aPermissionsCurrently = Permission::model()->deleteAll($oCriteria);
        $results = [];
        //Apply the permission array
        foreach ($aPermissionArray as $sPermissionKey => $aPermissionSettings) {
            $oPermission = new Permission();
            $oPermission->entity = 'global';
            $oPermission->entity_id = 0;
            $oPermission->uid = $iUserId;
            $oPermission->permission = $sPermissionKey;

            foreach ($aPermissionSettings as $sSettingKey => $sSettingValue) {
                $oPermissionDBSettingKey = $sSettingKey . '_p';
                $oPermission->$oPermissionDBSettingKey = $sSettingValue == 'on' ? 1 : 0;
            }

            $results[$sPermissionKey] = [
                'success' => $oPermission->save(),
                'storedValue' => $oPermission->attributes,
            ];
        }
        return $results;
    }

    /**
     * CURRENTLY UNUSED
     * Add a tenplated permission to a users
     *
     * @param User $oUser
     * @param string $permissionclass
     * @param array $entity_ids
     * @return array
     */
    private function applyPermissionTemplate($oUser, $permissionclass, $entity_ids = [])
    {
        if ($permissionclass == 'Gruppenmanager' && empty($entity_ids)) {
            return [
                "success" => false,
                "error" => "Keine Umfrage für Berechtigung ausgewählt",
            ];
        }
        $oCriteria = new CDbCriteria();
        $oCriteria->compare('uid', $oUser->uid);
        //Kill all Permissions
        $aPermissionsCurrently = Permission::model()->deleteAll($oCriteria);

        //Allow Login again
        Permission::model()->setGlobalPermission($oUser->uid, 'auth_db');

        $result = false;
        if (in_array($permissionclass, ['Befragungsmanager', 'Wissenschaftler', 'combo'])) {
            $result = $this->applyGlobalPermissionTemplate($oUser, $permissionclass);
            $this->applyCorrectUsergroup($oUser->uid, ($permissionclass == 'combo' ? ['Befragungsmanager', 'Wissenschaftler'] : [$permissionclass]));
        } elseif ($permissionclass == 'Gruppenmanager') {
            $result = $this->applySurveyPermissionTemplate($oUser, $permissionclass, $entity_ids);
            $this->applyCorrectUsergroup($oUser->uid, [$permissionclass]);
        }
        return $result;
    }

    /**
     * CURRENTLY UNUSED
     * Apply global permission from template
     *
     * @param User $oUser
     * @param string $permissionclass
     * @return array
     */
    private function applyGlobalPermissionTemplate($oUser, $permissionclass)
    {
        $permissionTemplate = []; //PermissionTemplates::getPermissionTemplateBlock($permissionclass, $oUser->uid);
        $check = [];
        foreach ($permissionTemplate as $permission) {
            $oPermission = new Permission();
            array_walk($permission, function ($val, $key) use (&$oPermission) {
                $oPermission->$key = $val;
            });
            $check[$permission['permission']] = $oPermission->save(false);
        }
        return $check;
    }

    /**
     * CURRENTLY UNUSED
     * Add survey specific permissions by template
     *
     * @param User $oUser
     * @param string $permissionclass
     * @param array $entity_ids
     * @return array
     */
    private function applySurveyPermissionTemplate($oUser, $permissionclass, $entity_ids)
    {
        $permissionTemplate = []; //PermissionTemplates::getPermissionTemplateBlock($permissionclass, $oUser->uid);
        $check = [];
        foreach ($permissionTemplate as $permission) {
            array_walk($entity_ids, function ($entity_id) use ($permission, &$check) {
                $oPermission = new Permission();
                $permission['entity_id'] = $entity_id;
                array_walk($permission, function ($val, $key) use (&$oPermission) {
                    $oPermission->$key = $val;
                });
                $check[$permission['permission'] . '/' . $entity_id] = $oPermission->save(false);
            });
        }
        return $check;
    }
}
