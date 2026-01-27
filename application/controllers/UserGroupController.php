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
        App()->getClientScript()->registerScriptFile(App()->getConfig('adminscripts') . 'users.js');

        Yii::app()->loadHelper('database');

        $this->aData['imageurl'] = Yii::app()->getConfig("adminimageurl");

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

        $aData['topbar']['title'] = gT('User group list');
        $aData['topbar']['backLink'] = App()->createUrl('dashboard/view');

        $aData['topbar']['middleButtons'] = $this->renderPartial('partial/topbarBtns/leftSideButtons', [], true);
        $aData['topbar']['rightButtons'] = $this->renderPartial('partial/topbarBtns/rightSideButtons', [
            'addGroupSave' => false
        ], true);


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
        $ugid = (int)$ugid;
        if (empty($ugid)) {
            throw new CHttpException(400, gT('GroupId missing'));
        }

        $userGroup = UserGroup::model()->findByPk($ugid);
        if (empty($userGroup)) {
            throw new CHttpException(404, gT("User group not found."));
        }
        /* Check Permssion to view */
        if (
            !(
                Permission::model()->hasGlobalPermission('superadmin', 'read') // superadmin
                ||  $userGroup->owner_id == Yii::app()->user->id // owner
                || ($userGroup->hasUser(Yii::app()->user->id) && Permission::model()->hasGlobalPermission('usergroups', 'read')) // inside group and have global UserGroup view
            )
        ) {
            throw new CHttpException(403, gT("You do not have permission to view this user group."));
        }

        $aData = [];
        if (!empty($header)) {
            $aData['headercfg'] = $header;
        } else {
            $aData['headercfg'] = null;
        }
        $aData['userGroup'] = $userGroup;
        $aData['ugid'] = $ugid;
        $aData["usergroupid"] = $ugid;
        $aData["groupfound"] = true;
        $aData["groupname"] = $userGroup->name;
        $aData["usergroupdescription"] = $userGroup->description;

        $aSearchCriteria = new CDbCriteria();
        $aSearchCriteria->compare("ugid", $ugid);
        if (!Permission::model()->hasGlobalPermission('superadmin', 'read')) {
            $aSearchCriteria->compare("owner_id", Yii::app()->session['loginID']);
        }
        $aFilteredUserGroups = UserGroup::model()->count($aSearchCriteria);

        $aData["useradddialog"] = false;
        $aData["addableUsers"] = [];
        if ($aFilteredUserGroups > 0) {
            $aData["useradddialog"] = true;

            $aUsers = User::model()->findAll(['join' => "LEFT JOIN (SELECT uid AS id FROM {{user_in_groups}} WHERE ugid = {$ugid}) AS b ON t.uid = b.id", 'condition' => "id IS NULL ORDER BY users_name"]);
            $aNewUserListData = CHtml::listData($aUsers, 'uid', function ($user) {
                return \CHtml::encode($user->users_name) . " (" . \CHtml::encode($user->full_name) . ')';
            });
            // Remove group owner because an owner is automatically member of a group
            // TODO: Is this still right on 6.0?
            unset($aNewUserListData[$userGroup->owner_id]);
            $aData["addableUsers"] = array('-1' => gT("Please choose...")) + $aNewUserListData;
            $aData["useraddurl"] = "";
        }

        $aData['topbar']['title'] = gT('User group') . ': ' . $userGroup->name;
        $aData['topbar']['backLink'] = App()->createUrl('userGroup/index');


        $aData['topbar']['middleButtons'] = $this->renderPartial(
            'partial/topbarBtns_manageGroup/leftSideButtons',
            [
                'userGroupId' => $userGroup->ugid,
                'hasPermission' => (
                    Permission::model()->hasGlobalPermission('superadmin', 'read')
                    || App()->getCurrentUserId() == $userGroup->owner_id
                )
            ],
            true
        );


        if (isset($_GET['pageSize'])) {
            Yii::app()->user->setState('pageSize', (int)$_GET['pageSize']);
        }
        $model = User::model();
        $filterForm = Yii::app()->request->getPost('User', false);
        if ($filterForm) {
            $model->setAttributes($filterForm, false);
        }

        $this->aData = $aData;

        $this->render('viewUserGroup_view', [
            'ugid' => $aData['ugid'],
            'groupfound' => $aData['groupfound'],
            'usergroupdescription' => $aData["usergroupdescription"],
            'headercfg' => $aData["headercfg"],
            'useradddialog' => $aData["useradddialog"],
            'addableUsers' => $aData["addableUsers"],
            'model' => $model
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
        $action = $_POST['action'] ?? '';
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
                if ($result !== null) {
                    $aData['model'] = $result;
                    $aData['ugid'] = $result->ugid;
                } else {
                    Yii::app()->session['flashmessage'] = gT("You don't have permission to edit this user group.");
                    $this->redirect(App()->createUrl("/admin"));
                }
            }
        } else {
            Yii::app()->session['flashmessage'] = gT("You don't have permission to edit a user group");
            $this->redirect(App()->createUrl("/admin"));
        }

        $aData['topbar']['title'] = sprintf(gT("Editing user group (Owner: %s)"), Yii::app()->session['user']);
        $aData['topbar']['rightButtons'] = $this->renderPartial(
            'partial/topbarBtns/rightSideButtons',
            [
                'backUrl' => Yii::app()->createUrl('userGroup/index'),
                'addGroupSave' => true
            ],
            true
        );

        $this->aData = $aData;

        $this->render('editUserGroup_view', [
            'ugid' => $aData['ugid'],
            'model' => $aData['model']
        ]);
    }

    /**
     * Adds a user to user group if action is set to "saveusergroup"
     *
     */
    public function actionAddGroup()
    {
        $action = $_POST['action'] ?? '';
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

        $aData['topbar']['title'] = gT('Add user group');
        $aData['topbar']['middleButtons'] = $this->renderPartial(
            'partial/topbarBtns/leftSideButtons',
            [],
            true
        );
        $aData['topbar']['rightButtons'] = $this->renderPartial(
            '/layouts/partial_topbar/right_close_saveclose_save',
            [
                'isCloseBtn' => true,
                'isSaveAndCloseBtn' => false,
                'isSaveBtn' => true,
                'backUrl' => Yii::app()->createUrl('userGroup/index'),
                'formIdSaveClose' => '',
                'formIdSave' => 'usergroupform'
            ],
            true
        );

        $this->aData = $aData;

        $this->render('addUserGroup_view');
    }

    /**
     *  Deletes a user group and all entries in UserInGroup related to that group
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
            $aRet['errorMsg'] = gT('Access denied!');
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
     * @throws CDbException
     */
    public function actionDeleteUserFromGroup()
    {
        $ugid = (int) Yii::app()->request->getPost('ugid');
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
        $currentUserId = App()->getCurrentUserId();
        $userGroup = UserGroup::model()->findByPk($ugid);
        if (empty($userGroup)) {
            throw new CHttpException(404, gT("User group not found."));
        }
        if (
            !Permission::model()->hasGlobalPermission('superadmin', 'read') // User is not a superadmin
            && $userGroup->owner_id != $currentUserId // User is not owner
        ) {
            throw new CHttpException(403, gT("You do not have permission to send emails to all users."));
        }
        $redirectUrl = App()->createUrl("userGroup/viewGroup", ['ugid' => $ugid]);
        $aData = [];
        $aData['ugid'] = $ugid;
        if ($action == "mailsendusergroup") {
            try {
                $sendCopy = Yii::app()->getRequest()->getPost('copymail') == 1 ? 1 : 0;
                $emailSendingResults = UserGroup::model()->sendUserEmails(
                    $ugid,
                    Yii::app()->getRequest()->getPost('subject'),
                    Yii::app()->getRequest()->getPost('body'),
                    $sendCopy
                );
                App()->user->setFlash('success', $emailSendingResults);
            } catch (Exception $e) {
                // TODO: Show error message?
                App()->user->setFlash('error', gT("Error: no email has been send."));
            }
            $this->redirect($redirectUrl);
            App()->end(); // redirect end : add it here for clarity
        }

        $aData['topbar']['title'] = gT('Mail to all Members');
        $aData['topbar']['backLink'] = App()->createUrl('userGroup/index');
        $aData['topbar']['rightButtons'] = $this->renderPartial(
            'partial/topbarBtns_mail/rightSideButtons',
            [],
            true
        );

        $this->aData = $aData;
        $this->render('mailUserGroup_view', $aData);
    }
}
