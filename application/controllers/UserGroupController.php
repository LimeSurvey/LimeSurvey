<?php

/**
 * class UserGroupController
 **/
class UserGroupController extends LSBaseController
{
    /**
     * Run filters
     *
     * @return array|void
     */
    public function filters()
    {
        return [
            'postOnly + deleteGroup, addUserToGroup, deleteUserFromGroup'
        ];
    }
    /**
     * @return array
     **/
    public function accessRules()
    {
        return array(
            array(
                'allow',
                'actions' => array(),
                'users' => array('*'), //everybody
            ),
            array(
                'allow',
                'actions' => array('index','edit', 'viewGroup', 'addGroup', 'addUserToGroup',
                    'deleteGroup', 'deleteUserFromGroup', 'mailToAllUsersInGroup'),
                'users' => array('@'), //only login users
            ),
            array('deny'),
        );
    }

    /**
     * this is part of renderWrappedTemplate implement in old usergroups.php
     *
     * @param string $view
     * @return bool
     */
    public function beforeRender($view)
    {
        App()->getClientScript()->registerPackage('jquery-tablesorter');
        App()->getClientScript()->registerScriptFile(App()->getConfig('adminscripts') . 'users.js');

        Yii::app()->loadHelper('database');

        $this->aData['imageurl'] = Yii::app()->getConfig("adminimageurl");

        $this->aData['menubar_pathname'] = '/userGroup/usergroupbar_view';

        return parent::beforeRender($view);
    }

    /**
     * Load main user group screen, showing all existing userGroups in a gridview.
     *
     * @return array
     */
    public function actionIndex()
    {
        if (!Permission::model()->hasGlobalPermission('usergroups', 'read')) {
            Yii::app()->session['flashmessage'] = gT('Access denied!');
            $this->redirect(App()->createUrl("/admin"));
        }

        $aData = [];

        $model = UserGroup::model();

        $aData['usergroupbar']['returnbutton']['url'] = 'admin/index';
        $aData['usergroupbar']['returnbutton']['text'] = gT('Back');

        $aData['pageTitle'] = gT('User group list');

        if (isset($_GET['pageSize'])) {
            Yii::app()->user->setState('pageSize', (int)$_GET['pageSize']);
        }

        $pageSize = Yii::app()->user->getState('pageSize', Yii::app()->params['defaultPageSize']);

        $this->aData = $aData;

        $this->render('usergroups_view', [
            'model' => $model,
            'pageSize' => $pageSize
        ]);
    }

    /**
     * Renders a view for a particular group showing all users in group
     *
     * @param $ugid
     * @param bool $header
     */
    public function actionViewGroup($ugid, bool $header = false)
    {
        if (!Permission::model()->hasGlobalPermission('usergroups', 'read')) {
            Yii::app()->session['flashmessage'] = gT('Access denied!');
            $this->redirect(App()->createUrl("/admin"));
        }

        $aData = [];
        if (!empty($header)) {
            $aData['headercfg'] = $header;
        } else {
            $aData['headercfg'] = null;
        }

        if ($ugid != false) {
            $ugid = (int)$ugid;
            $userGroup = UserGroup::model()->findByPk($ugid);
            $uid = Yii::app()->user->id;
            if (
                $userGroup &&
                ($userGroup->hasUser($uid) || Permission::model()->hasGlobalPermission('superadmin', 'read'))
            ) {
                $aData['userGroup'] = $userGroup;
            }
        } else {
            $sFlashType = 'error';
            $sFlashMessage = gT('GroupId missing');
            Yii::app()->user->setFlash($sFlashType, $sFlashMessage);
            $this->redirect('index');
        }

        $aData['ugid'] = $ugid;
        if (Yii::app()->session['loginID']) {
            $aData["usergroupid"] = $ugid;
            $result = UserGroup::model()->requestViewGroup($ugid, Yii::app()->session["loginID"]);
            $crow = $result[0];
            if ($result) {
                $aData["groupfound"] = true;
                $aData["groupname"] = $crow['name'];
                if (!empty($crow['description'])) {
                    $aData["usergroupdescription"] = $crow['description'];
                } else {
                    $aData["usergroupdescription"] = "";
                }
            }

            $aUserInGroupsResult = UserGroup::model()->findByPk($ugid);

            $row = 1;
            $userloop = array();
            $bgcc = "oddrow";
            foreach ($aUserInGroupsResult->users as $oUser) {
                // @todo: Move the zebra striping to view
                if ($bgcc == "evenrow") {
                    $bgcc = "oddrow";
                } else {
                    $bgcc = "evenrow";
                }
                $userloop[$row]["userid"] = $oUser->uid;

                //  output users
                $userloop[$row]["rowclass"] = $bgcc;
                if (Permission::model()->hasGlobalPermission('usergroups', 'update') && $oUser->parent_id == Yii::app()->session['loginID']) {
                    $userloop[$row]["displayactions"] = true;
                } else {
                    $userloop[$row]["displayactions"] = false;
                }

                $userloop[$row]["username"] = $oUser->users_name;
                $userloop[$row]["email"] = $oUser->email;

                $row++;
            }
            $aData["userloop"] = $userloop;

            $aSearchCriteria = new CDbCriteria();
            $aSearchCriteria->compare("ugid", $ugid);
            if (!Permission::model()->hasGlobalPermission('superadmin', 'read')) {
                $aSearchCriteria->compare("owner_id", Yii::app()->session['loginID']);
            }
            $aFilteredUserGroups = UserGroup::model()->count($aSearchCriteria);

            if ($aFilteredUserGroups > 0) {
                $aData["useradddialog"] = true;

                $aUsers = User::model()->findAll(['join' => "LEFT JOIN (SELECT uid AS id FROM {{user_in_groups}} WHERE ugid = {$ugid}) AS b ON t.uid = b.id", 'condition' => "id IS NULL ORDER BY users_name"]);
                $aNewUserListData = CHtml::listData($aUsers, 'uid', function ($user) {
                    return \CHtml::encode($user->users_name) . " (" . \CHtml::encode($user->full_name) . ')';
                });
                // Remove group owner because an owner is automatically member of a group
                unset($aNewUserListData[$aUserInGroupsResult->owner_id]);
                $aData["addableUsers"] = array('-1' => gT("Please choose...")) + $aNewUserListData;
                $aData["useraddurl"] = "";
            }
        }

        $aData['usergroupbar']['edit'] = true;

        // Return Button
        $aData['usergroupbar']['returnbutton']['url'] = 'userGroup/index';
        $aData['usergroupbar']['returnbutton']['text'] = gT('Back');

        // Green Bar (SurveyManagerBar) Page Title
        $basePageTitle = gT('User group');
        $userGroupName = $aData['groupname'];
        $aData['pageTitle'] = $basePageTitle . ' : ' . $userGroupName;

        $this->aData = $aData;

        $this->render('viewUserGroup_view', [
            'ugid' => $aData['ugid'],
            'groupname' => $aData['groupname'],
            'groupfound' => $aData['groupfound'],
            'usergroupdescription' => $aData["usergroupdescription"],
            'headercfg' => $aData["headercfg"],
            'userloop' => $aData["userloop"],
            'useradddialog' => $aData["useradddialog"],
            'addableUsers' => $aData["addableUsers"]
        ]);
    }

    /**
     *
     * Load edit user group screen.
     *
     * @param int $ugid
     * @return void
     */
    public function actionEdit(int $ugid)
    {
        if (!Permission::model()->hasGlobalPermission('usergroups', 'update')) {
            Yii::app()->session['flashmessage'] = gT('Access denied!');
            $this->redirect(App()->createUrl("/admin"));
        }
        $ugid = (int) $ugid;

        $aData = [];
        $action = (isset($_POST['action'])) ? $_POST['action'] : '';
        if (Permission::model()->hasGlobalPermission('usergroups', 'update')) {
            if ($action == "editusergroupindb") {
                $ugid = (int) $_POST['ugid'];

                $groupName = flattenText($_POST['name'], false, true, 'UTF-8');
                $groupDescription = flattenText($_POST['description']);
                if (UserGroup::model()->updateGroup($groupName, $groupDescription, $ugid)) {
                    Yii::app()->session['flashmessage'] = gT("User group successfully saved!");
                    $aData['ugid'] = $ugid;
                    $this->redirect(array('userGroup/viewGroup/ugid/' . $ugid));
                } else {
                    Yii::app()->user->setFlash('error', gT("Failed to edit user group! Group already exists?"));
                    $this->redirect(array('userGroup/edit/ugid/' . $ugid));
                }
            } else {
                $result = UserGroup::model()->requestEditGroup($ugid, Yii::app()->session['loginID']);
                $aData['model'] = $result;
                $aData['ugid'] = $result->ugid;
            }
        } else {
            Yii::app()->session['flashmessage'] = gT("You don't have permission to edit a usergroup");
            $this->redirect(App()->createUrl("/admin"));
        }

        $aData['usergroupbar']['returnbutton']['url'] = 'userGroup/index';
        $aData['usergroupbar']['returnbutton']['text'] = gT('Back');
        $aData['usergroupbar']['savebutton']['form'] = 'usergroupform';
        $aData['usergroupbar']['savebutton']['text'] = gT("Save");

        // Green Bar (SurveyManagerBar) Page Title
        $aData['pageTitle'] = sprintf(gT("Editing user group (Owner: %s)"), Yii::app()->session['user']);
        $this->aData = $aData;

        $this->render('editUserGroup_view', [
            'ugid' => $aData['ugid'],
            'model' => $aData['model']
        ]);
    }

    /**
     * Adds a user to usergroup if action is set to "saveusergroup"
     *
     */
    public function actionAddGroup()
    {
        $action = (isset($_POST['action'])) ? $_POST['action'] : '';
        $aData = array();

        if (Permission::model()->hasGlobalPermission('usergroups', 'create')) {
            if ($action == "saveusergroup") {
                //try to save the normal yii-way (validation rules must be implement in UserGroup()->rules(...)
                $model = new UserGroup();
                $model->name = flattenText($_POST['group_name'], false, true, 'UTF-8');
                $model->description = flattenText($_POST['group_description']);
                $model->owner_id = Yii::app()->user->id;

                if ($model->save()) {
                    //everythiong ok, go back to index
                    Yii::app()->user->setFlash('success', gT("User group successfully added!"));
                    $this->redirect(array('userGroup/index'));
                } else {
                    //show error msg
                    $errors = $model->getErrors();
                    //show only the first error, so the user could fix them one by one ...
                    foreach ($errors as $key => $value) {
                        $firstError = $key;
                        break;
                    }
                    Yii::app()->user->setFlash('error', $errors[$firstError][0]);
                }
            }
        } else {
            $this->redirect('index');
        }

        // Save Button
        $aData['usergroupbar']['savebutton']['form'] = 'usergroupform';
        $aData['usergroupbar']['savebutton']['text'] = gT('Save');

        // Back Button
        $aData['usergroupbar']['returnbutton']['text'] = gT('Back');
        $aData['usergroupbar']['returnbutton']['url'] = 'userGroup/index';

        // Add User Group Button
        $aData['usergroupbar']['add'] = 'admin/usergroups';

        # Green Bar (SurveyManagerBar) Page Title
        $aData['pageTitle'] = gT('Add user group');

        $this->aData = $aData;

        $this->render('addUserGroup_view');
    }

    /**
     *  Deletes a usergroup and all entries in UserInGroup related to that group
     *
     */
    public function actionDeleteGroup()
    {
        if (Permission::model()->hasGlobalPermission('usergroups', 'delete')) {
            $userGroupId = Yii::app()->request->getPost("ugid");

            if (Permission::model()->hasGlobalPermission('superadmin', 'read')) {
                //superadmin can delete
                $model = UserGroup::model()->findByAttributes(['ugid'     => (int)$userGroupId]);
            } else {
                //user is owner
                $model = UserGroup::model()->findByAttributes(['ugid'     => (int)$userGroupId,
                                                               'owner_id' => Yii::app()->user->id
                ]);
            }

            if ($model !== null && $model->delete()) {
                Yii::app()->user->setFlash("success", gT("Successfully deleted user group."));
            } else {
                Yii::app()->user->setFlash("error", gT("Could not delete user group."));
            }
        }

        $this->redirect(array('userGroup/index'));
    }

    /**
     * Adds a user to a group
     *
     */
    public function actionAddUserToGroup()
    {
        $uid = (int) Yii::app()->request->getPost('uid');
        $ugid = (int) Yii::app()->request->getPost('ugid');
        $checkPermissionsUserGroupExists = $this->checkBeforeAddDeleteUser($uid, $ugid);
        if (count($checkPermissionsUserGroupExists) > 0) {
            Yii::app()->user->setFlash('error', $checkPermissionsUserGroupExists['errorMsg']);
            $this->redirect(array($checkPermissionsUserGroupExists['redirectPath']));
        }

        //add user to group
        $newEntryUserInGroup = new UserInGroup();
        $newEntryUserInGroup->uid = $uid;
        $newEntryUserInGroup->ugid = $ugid;
        if ($newEntryUserInGroup->save()) {
            Yii::app()->user->setFlash('success', gT('User added.'));
        } else {
            Yii::app()->user->setFlash('error', gT('User could not be added.'));
        }
        $this->redirect(array('userGroup/viewGroup/ugid/' . $ugid));
    }

    /**
     *  Checks permission to add/delete users to group and
     *  checks if group and user exists
     *
     *  todo: could be moved to model
     *
     * @param $uid   integer  userID
     * @param $userGroupId   integer userGroupID
     *
     * @return array if empty everything is ok, else
     *                  ['errorMsg']
     *                  ['redirectPath']
     */
    private function checkBeforeAddDeleteUser($uid, $userGroupId)
    {
        $aRet = [];

        if (!Permission::model()->hasGlobalPermission('usergroups', 'read')) {
            $aRet['errorMsg'] = gT('Access denied');
            $aRet['redirectPath'] = 'userGroup/viewGroup/ugid/' . $userGroupId;
            return $aRet;
        }

        if (Permission::model()->hasGlobalPermission('superadmin', 'read')) {
            $group = UserGroup::model()->findByAttributes(array('ugid' => $userGroupId));
        } else {
            $group = UserGroup::model()->findByAttributes(array('ugid'     => $userGroupId,
                                                                'owner_id' => Yii::app()->session['loginID']
            ));
        }

        if (empty($group)) {
            $aRet['errorMsg'] = gT('Group not found.');
            $aRet['redirectPath'] = 'userGroup/index';
            return $aRet;
        }

        if ($group->owner_id == $uid) {
            $aRet['errorMsg'] = gT('You can not add or remove the group owner from the group.');
            $aRet['redirectPath'] = 'userGroup/viewGroup/ugid/' . $userGroupId;
            return $aRet;
        }

        $userToAdd = User::model()->findByPk($uid);
        if ($userToAdd === null) {
            $aRet['errorMsg'] = gT('Unknown user. You have to select a user.');
            $aRet['redirectPath'] = 'userGroup/viewGroup/ugid/' . $userGroupId;
        }

        return $aRet;
    }

    /**
     * Deletes a user from group
     *
     * @param $ugid
     * @throws CDbException
     */
    public function actionDeleteUserFromGroup($ugid)
    {
        $uid = (int) Yii::app()->request->getPost('uid');
        $checkOK = $this->checkBeforeAddDeleteUser($uid, (int)$ugid);
        if (count($checkOK) > 0) {
            Yii::app()->user->setFlash('error', $checkOK['errorMsg']);
            $this->redirect(array($checkOK['redirectPath']));
        }

        //add user to group
        $deleteUser = UserInGroup::model()->findByAttributes(['uid' => $uid, 'ugid' => $ugid]);

        if ($deleteUser->delete()) {
            Yii::app()->user->setFlash('success', gT('User removed.'));
        } else {
            Yii::app()->user->setFlash('error', gT('Failed to remove user.'));
        }
        $this->redirect(array('userGroup/viewGroup/ugid/' . $ugid));
    }

    /**
     *  Sends email to all users in a group
     *
     * @param int $ugid
     */
    public function actionMailToAllUsersInGroup(int $ugid)
    {
        $ugid = sanitize_int($ugid);
        $action = Yii::app()->request->getPost("action");
        $aData = [];

        if ($action == "mailsendusergroup") {
            // user must be in user group or superadmin
            if ($ugid === null) {
                $ugid = (int) Yii::app()->request->getPost('ugid');
            }
            $result = UserInGroup::model()->findAllByPk(array('ugid' => (int)$ugid, 'uid' => Yii::app()->session['loginID']));
            if (count($result) > 0 || Permission::model()->hasGlobalPermission('superadmin', 'read')) {
                try {
                    $sendCopy = Yii::app()->getRequest()->getPost('copymail') == 1 ? 1 : 0;
                    $emailSendingResults = UserGroup::model()->sendUserEmails(
                        $ugid,
                        Yii::app()->getRequest()->getPost('subject'),
                        Yii::app()->getRequest()->getPost('body'),
                        $sendCopy
                    );

                    Yii::app()->user->setFlash('success', $emailSendingResults);
                    $this->redirect(array('userGroup/viewGroup/ugid/' . $ugid));
                } catch (Exception $e) {
                    // TODO: Show error message?
                    Yii::app()->user->setFlash('error', gT("Error: no email has been send."));
                    $this->redirect(array('userGroup/viewGroup/ugid/' . $ugid));
                }
            } else {
                Yii::app()->user->setFlash('error', gT("You do not have permission to send emails to all users. "));
                $this->redirect(array('userGroup/viewGroup/ugid/' . $ugid));
            }
        } else {
            $aData['ugid'] = $ugid;
        }

        // Back Button
        $aData['usergroupbar']['returnbutton']['url'] = 'userGroup/index';
        $aData['usergroupbar']['returnbutton']['text'] = gT('Back');

        // Send Button
        $aData['usergroupbar']['savebutton']['form'] = 'mailusergroup';
        $aData['usergroupbar']['savebutton']['text'] = gT('Send');

        // Reset Button
        $aData['usergroupbar']['resetbutton']['form'] = 'mailusergroup';
        $aData['usergroupbar']['resetbutton']['text'] = gT('Reset');

        // Green Bar (SurveyManagerBar) Page Title
        $aData['pageTitle'] = gT("Mail to all Members");

        $this->aData = $aData;

        $this->render('mailUserGroup_view', [
            'ugid' => $aData['ugid']
        ]);
    }
}
