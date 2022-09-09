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
                'actions' => ['index'],
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
        if (!Permission::model()->hasSurveyPermission($surveyid, 'surveysecurity', 'read')) {
            Yii::app()->user->setFlash('error', gT("No permission or survey does not exist."));
            $this->redirect(Yii::app()->request->urlReferrer);
        }
        App()->getClientScript()->registerPackage('jquery-tablesorter');
        App()->getClientScript()->registerScriptFile(App()->getConfig('adminscripts') . 'surveypermissions.js');
        $oSurvey = Survey::model()->findByPk($surveyid);
        $aData['surveyid'] = $surveyid;
        $aData['sidemenu']['state'] = false;

        $aData['title_bar']['title'] = $oSurvey->currentLanguageSettings->surveyls_title . " (" . gT("ID") . ":" . $surveyid . ")";
        $aData['subaction'] = gT("Survey permissions");

        $aData['surveybar']['closebutton']['url'] = 'surveyAdministration/view/surveyid/' . $surveyid; // Close button

        $this->aData = $aData;
        $aBaseSurveyPermissions = Permission::model()->getSurveyBasePermissions();
        //function get table content as array
        //structure should be
        /*
         *
         */
        return $this->render('index', [
            'basePermissions' => $aBaseSurveyPermissions,
            'userCreatePermission' => Permission::model()->hasSurveyPermission($surveyid, 'surveysecurity', 'create'),
            'surveyid' => $surveyid
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
        $surveyPermissions = new \LimeSurvey\Models\Services\SurveyPermissions($oSurvey);
        $userAdded = $surveyPermissions->addUserToSurveyPermission($userId);
        if ($userAdded) {
            Yii::app()->user->setFlash('success', gT("User added."));
            $this->redirect(array(
                    'surveyPermissions/settingsPermissions',
                    'surveyid' => $surveyid,
                    'action' => 'user',
                    'id' => $userId
                ));
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
        if (!Permission::model()->hasSurveyPermission($surveyid, 'surveysecurity', 'create')) {
            Yii::app()->user->setFlash('error', gT("No permission or survey does not exist."));
            $this->redirect(Yii::app()->request->urlReferrer);
        }
        // 2.  add the user in permission table
        $userId = (int)Yii::app()->request->getPost('ugid');
        // 3.  redirect to index if failed, or redirect to 'add permission page'; giving flash message in both cases
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
        if (!Permission::model()->hasSurveyPermission($surveyid, 'surveysecurity', 'create')) {
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
                Yii::app()->user->setFlash('error', gT("Unknown usergroup."));
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
        $aData['topBar']['showSaveButton'] = true;
        $aData['title_bar']['title'] = $oSurvey->currentLanguageSettings->surveyls_title . " (" . gT("ID") . ":" . $surveyid . ")";
        $aData['surveybar']['savebutton']['form'] = 'frmeditgroup';
        $this->aData = $aData;
        return $this->render(
            'settingsPermission',
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
     * Save permissions for a user or a usergroup
     *
     * @param $surveyid
     * @return void
     */
    public function actionSavePermissions($surveyid)
    {
        if (!Permission::model()->hasSurveyPermission($surveyid, 'surveysecurity', 'create')) {
            Yii::app()->user->setFlash('error', gT("No permission or survey does not exist."));
            $this->redirect(Yii::app()->request->urlReferrer);
        }
        //get post-params
        $action = Yii::app()->request->getPost('action'); //the action could be 'user' or 'usergroup'
        $userId = Yii::app()->request->getPost('uid');
        $userGroupId = Yii::app()->request->getPost('ugid');
        // 1. save the permissions
        // 2. redirect to overview (index)

        $this->redirect(array('surveyPermissions/index', 'surveyid' => $surveyid));
    }


    public function actionDeleteUserPermissions()
    {
        $surveyid = (int)Yii::app()->request->getPost('surveyid');
        if (!Permission::model()->hasSurveyPermission($surveyid, 'surveysecurity', 'delete')) {
            Yii::app()->user->setFlash('error', gT("No permission to delete survey permissions from user."));
            $this->redirect(Yii::app()->request->urlReferrer);
        }

        // get POST data userid
        //remove all permissions for that specific user (except for yourself or admin users)
        // display a flash message
    }
}
