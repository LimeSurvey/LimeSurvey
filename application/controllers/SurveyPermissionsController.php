<?php

use LimeSurvey\Models\Services\PermissionManager;

class SurveyPermissionsController extends LSBaseController
{
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
                'users' => ['*'], //everybody
            ],
            [
                'allow',
                'actions' => [
                    'index',
                    'addUser',
                    'addUserGroups',
                    'deleteUserPermissions',
                    'savePermissions',
                    'SettingsPermissions'
                ],
                'users' => ['@'], //only login users
            ],
            ['deny'], //always deny all actions not mentioned above
        ];
    }

    /**
     * Here we have to use the correct layout (NOT main.php)
     *
     * @param string $view
     * @return bool
     */
    protected function beforeRender($view)
    {
        $this->layout = 'layout_questioneditor';
        LimeExpressionManager::SetSurveyId($this->aData['surveyid']);
        LimeExpressionManager::StartProcessingPage(false, true);

        return parent::beforeRender($view);
    }

    /**
     * @param $surveyid
     * @return array|mixed|string|string[]|null
     */
    public function actionIndex($surveyid)
    {
        $surveyid = sanitize_int($surveyid);
        if (!Permission::model()->hasSurveyPermission($surveyid, 'surveysecurity', 'read')) {
            Yii::app()->user->setFlash('error', gT("No permission or survey does not exist."));
            $this->redirect(Yii::app()->request->urlReferrer);
        }
        App()->getClientScript()->registerPackage('jquery-tablesorter');
        App()->getClientScript()->registerPackage('usermanagement');
        App()->getClientScript()->registerPackage('select2-bootstrap');


        App()->getClientScript()->registerScriptFile(App()->getConfig('adminscripts') . 'surveypermissions.js');
        $oSurvey = Survey::model()->findByPk($surveyid);
        $aData['surveyid'] = $surveyid;
        $aData['sidemenu']['state'] = false;

        $aData['title_bar']['title'] = $oSurvey->currentLanguageSettings->surveyls_title . " (" . gT("ID") . ":" . $surveyid . ")";
        $topbarData = TopbarConfiguration::getSurveyTopbarData($surveyid);
        $aData['topbar']['middleButtons'] = $this->renderPartial(
            '/surveyAdministration/partial/topbar/surveyTopbarLeft_view',
            $topbarData,
            true
        );

        $aData['subaction'] = gT("Survey permissions");

        $aData['surveybar']['closebutton']['url'] = 'surveyAdministration/view/surveyid/' . $surveyid; // Close button

        $this->aData = $aData;
        $aBaseSurveyPermissions = Permission::model()->getSurveyBasePermissions();
        $oSurveyPermissions = new \LimeSurvey\Models\Services\SurveyPermissions(
            $oSurvey,
            Yii::app()->getConfig('usercontrolSameGroupPolicy')
        );
        return $this->render('index', [
            'basePermissions' => $aBaseSurveyPermissions,
            'userCreatePermission' => Permission::model()->hasSurveyPermission($surveyid, 'surveysecurity', 'create'),
            'surveyid' => $surveyid,
            'userList' => $oSurveyPermissions->getSurveyUserList(),
            'userGroupList' => $oSurveyPermissions->getSurveyUserGroupList(),
            'tableContent' => $oSurveyPermissions->getUsersSurveyPermissions(),
            'oSurveyPermissions' => $oSurveyPermissions,
            // newly added property
            'dataProvider' => $oSurveyPermissions->getUsersSurveyPermissionsDataProvider(),
        ]);
    }

    /**
     * Add a user to permission table for this survey.
     * Opens the permission settings site if user could be added.
     *
     * @param $surveyid
     * @return void
     */
    public function actionAddUser($surveyid)
    {
        $surveyid = sanitize_int($surveyid);
        if (!Permission::model()->hasSurveyPermission($surveyid, 'surveysecurity', 'create')) {
            Yii::app()->user->setFlash('error', gT("No permission or survey does not exist."));
            $this->redirect(Yii::app()->request->urlReferrer);
        }
        // 2.  add the user in permission table
        $userId = (int)Yii::app()->request->getPost('uid');
        $oSurvey = Survey::model()->findByPk($surveyid);
        $surveyPermissions = new \LimeSurvey\Models\Services\SurveyPermissions(
            $oSurvey,
            Yii::app()->getConfig('usercontrolSameGroupPolicy')
        );
        $userAdded = $surveyPermissions->addUserToSurveyPermission($userId);
        if ($userAdded) {
            Yii::app()->user->setFlash('success', gT("User added."));
            $this->redirect(['surveyPermissions/index', 'surveyid' => $surveyid]);
        } else {
            Yii::app()->user->setFlash('error', gT("User could not be added to survey permissions."));
            $this->redirect(['surveyPermissions/index', 'surveyid' => $surveyid]);
        }
    }

    /**
     * Add group users to permission table for this survey.
     * and redirects to settings permission page
     *
     * @param $surveyid
     * @return void
     */
    public function actionAddUserGroup($surveyid)
    {
        $surveyid = sanitize_int($surveyid);
        if (!Permission::model()->hasSurveyPermission($surveyid, 'surveysecurity', 'create')) {
            Yii::app()->user->setFlash('error', gT("No permission or survey does not exist."));
            $this->redirect(Yii::app()->request->urlReferrer);
        }
        $oSurvey = Survey::model()->findByPk($surveyid);
        $userGroupId = (int)Yii::app()->request->getPost('ugid');
        $surveyPermissions = new \LimeSurvey\Models\Services\SurveyPermissions(
            $oSurvey,
            Yii::app()->getConfig('usercontrolSameGroupPolicy')
        );
        $amountUsersAdded = $surveyPermissions->addUserGroupToSurveyPermissions($userGroupId);
        if ($amountUsersAdded == 0) {
            Yii::app()->user->setFlash('error', gT("No users from group could be added."));
            $this->redirect(['surveyPermissions/index', 'surveyid' => $surveyid]);
        } else {
            Yii::app()->user->setFlash('success', sprintf(gT("%s users from group were added."), $amountUsersAdded));
            $this->redirect(array(
                'surveyPermissions/settingsPermissions',
                'surveyid' => $surveyid,
                'action' => 'usergroup',
                'id' => $userGroupId
            ));
        }
    }

    /**
     * Open settings permission page
     *
     * @param $surveyid int
     * @param $action string the action could be 'user' or 'usergroup'
     * @param $id int userid or groupid depending on the action
     *
     * @return void
     */
    public function actionSettingsPermissions($surveyid, $action, $id)
    {
        $surveyid = sanitize_int($surveyid);
        $id = sanitize_int($id);
        if (!Permission::model()->hasSurveyPermission($surveyid, 'surveysecurity', 'update')) {
            Yii::app()->user->setFlash('error', gT("No permission or survey does not exist."));
            $this->redirect(Yii::app()->request->urlReferrer);
        }
        if (!in_array($action, ['user', 'usergroup'])) {
            Yii::app()->user->setFlash('error', gT("Unknown action."));
            $this->redirect(Yii::app()->request->urlReferrer);
        }
        $oSurvey = Survey::model()->findByPk($surveyid);
        $PermissionManagerService = new PermissionManager(
            App()->request,
            App()->user,
            $oSurvey,
            App()
        );
        $isUserGroup = $action === 'usergroup';
        if ($isUserGroup) {
            $oUserGroup = UserGroup::model()->findByPk($id);
            if (!isset($oUserGroup)) {
                Yii::app()->user->setFlash('error', gT("Unknown user group."));
                $this->redirect(Yii::app()->request->urlReferrer);
            }
            $name = $oUserGroup->name;
            $aPermissions = $PermissionManagerService->getPermissionData();
        } else {
            $oUser = User::model()->findByPk($id);
            if (!isset($oUser)) {
                Yii::app()->user->setFlash('error', gT("Unknown user."));
                $this->redirect(Yii::app()->request->urlReferrer);
            }
            $name = $oUser->full_name;
            $aPermissions = $PermissionManagerService->getPermissionData($id);
        }
        $aData['surveyid'] = $surveyid;
        $aData['sidemenu']['state'] = false;
        //$aData['topBar']['showSaveButton'] = true;
        $aData['title_bar']['title'] = $oSurvey->currentLanguageSettings->surveyls_title . " (" . gT("ID") . ":" . $surveyid . ")";

        $this->aData = $aData;
        return $this->renderPartial(
            'partial/editpermission',
            [
                'surveyid' => $surveyid,
                'aPermissions' => $aPermissions,
                'isUserGroup' => $isUserGroup,
                'id' => $id,
                'name' => $name,
            ]
        );
    }

    /**
     * Save permissions for a user or a user group
     *
     * @param $surveyid
     * @return void
     * @throws CHttpException
     */
    public function actionSavePermissions($surveyid)
    {
        $surveyid = sanitize_int($surveyid);
        //todo: or update permission ?!?
        if (!Permission::model()->hasSurveyPermission($surveyid, 'surveysecurity', 'create')) {
            Yii::app()->user->setFlash('error', gT("No permission or survey does not exist."));
            $this->redirect(Yii::app()->request->urlReferrer);
        }
        //get post-params
        $oSurvey = Survey::model()->findByPk($surveyid);
        $action = Yii::app()->request->getPost('action'); //the action could be 'user' or 'usergroup'
        $setOfPermissions = Yii::app()->request->getPost('set');
        $oSurveyPermissions = new \LimeSurvey\Models\Services\SurveyPermissions(
            $oSurvey,
            Yii::app()->getConfig('usercontrolSameGroupPolicy')
        );
        switch ($action) {
            case 'user':
                $userId = sanitize_int(Yii::app()->request->getPost('uid'));
                $success = $oSurveyPermissions->saveUserPermissions($userId, $setOfPermissions['Survey']);
                if ($success) {
                    Yii::app()->user->setFlash('success', gT("Successfully saved permissions for user."));
                } else {
                    Yii::app()->user->setFlash('error', gT("Error saving permissions for user."));
                }
                break;
            case 'usergroup':
                $userGroupId = sanitize_int(Yii::app()->request->getPost('ugid'));
                if (shouldFilterUserGroupList() && !in_array($userGroupId, getUserGroupList())) {
                    throw new CHttpException(403, gT("You do not have permission to this user group."));
                }
                $success = $oSurveyPermissions->saveUserGroupPermissions($userGroupId, $setOfPermissions['Survey']);
                if ($success) {
                    Yii::app()->user->setFlash('success', gT("Successfully saved permissions for user group."));
                } else {
                    Yii::app()->user->setFlash('error', gT("Error saving permissions for user group."));
                }
                break;
            default: //error here unknown action
                Yii::app()->user->setFlash('error', gT("Unknown action. Error saving permissions."));
        }

        $this->redirect(array('surveyPermissions/index', 'surveyid' => $surveyid));
    }

    /**
     * Deletes all survey permissions the user has.
     *
     * @return void
     */
    public function actionDeleteUserPermissions()
    {
        $surveyid = sanitize_int(Yii::app()->request->getPost('surveyid'));
        if (!Permission::model()->hasSurveyPermission($surveyid, 'surveysecurity', 'delete')) {
            Yii::app()->user->setFlash('error', gT("No permission to delete survey permissions from user."));
            $this->redirect(Yii::app()->request->urlReferrer);
        }

        $userid = sanitize_int(Yii::app()->request->getPost('userid'));
        $oSurvey = Survey::model()->findByPk($surveyid);
        $oSurveyPermission = new \LimeSurvey\Models\Services\SurveyPermissions($oSurvey, Yii::app()->getConfig('usercontrolSameGroupPolicy'));

        $result = $oSurveyPermission->deleteUserPermissions($userid);
        if ($result === 0) {
            Yii::app()->user->setFlash('error', gT("No survey permissions deleted."));
        } else {
            Yii::app()->user->setFlash('success', gT("Survey permissions deleted."));
        }

        $this->redirect(array('surveyPermissions/index', 'surveyid' => $surveyid));
    }
}
