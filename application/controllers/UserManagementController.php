<?php

//LSYii_Controller
/**
 * Class UserManagementController
 */
class UserManagementController extends LSBaseController
{
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
                'actions' => array(
                    'index', 'addEditUser', 'applyEdit', 'addDummyUser',
                    'runAddDummyUser', 'addRole', 'batchAddGroup', 'batchApplyRoles', 'batchPermissions',
                    'batchSendAndResetLoginData', 'deleteConfirm',  'deleteMultiple', 'exportUser', 'importUser',
                    'renderSelectedItems', 'renderUserImport', 'runAddDummyUser', 'saveRole', 'saveThemePermissions',
                    'takeOwnership', 'userPermissions', 'userTemplatePermissions', 'viewUser'
                ),
                'users' => array('@'), //only login users
            ),
            array('deny'),
        );
    }

    /**
     * @return string|string[]|null
     * @throws CException
     */
    public function actionIndex()
    {
        if (!Permission::model()->hasGlobalPermission('users', 'read')) {
            return $this->renderPartial(
                'partial/error',
                ['errors' => [gT("You do not have permission to access this page.")], 'noButton' => true]
            );
        }
        if (isset($_GET['pageSize'])) {
            Yii::app()->user->setState('pageSize', Yii::app()->request->getParam('pageSize'));
        }
        App()->getClientScript()->registerPackage('usermanagement');
        App()->getClientScript()->registerPackage('bootstrap-select2');

        $aData = [];
        $model = new User('search');
        $model->setAttributes(Yii::app()->getRequest()->getParam('User'), false);
        $aData['model'] = $model;
        $aData['columnDefinition'] = $model->getManagementColums();
        $aData['pageSize'] = Yii::app()->user->getState('pageSize', Yii::app()->params['defaultPageSize']);
        $aData['formUrl'] = $this->createUrl('userManagement/index');

        $aData['massiveAction'] = $this->renderPartial(
            'massiveAction/_selector',
            ['userid' => $model->uid],
            true,
            false
        );

        // Green Bar (SurveyManagerBar) Page Title
        $aData['pageTitle'] = gT('User management');

        //this is really important, so we have the aData also before rendering the content
        $this->aData = $aData;

        return $this->render('index', [
            'model' => $aData['model'],
            'columnDefinition' => $aData['columnDefinition'],
            'pageSize' => $aData['pageSize'],
            'formUrl' => $aData['formUrl'],
            'massiveAction' => $aData['massiveAction'],
        ]);
    }

    /**
     * Open modal to edit, or create a new user
     *
     * @param int|null $userid
     * @throws CException
     */
    public function actionAddEditUser($userid = null)
    {
        if (
            ($userid === null && !Permission::model()->hasGlobalPermission('users', 'create'))
            || ($userid !== null && !Permission::model()->hasGlobalPermission('users', 'update'))
        ) {
            return $this->renderPartial(
                'partial/error',
                ['errors' => [gT("You do not have permission to access this page.")]]
            );
        }
        if ($userid === null) {
            $oUser = new User();
        } else {
            $oUser = User::model()->findByPk((int)$userid);
            if ($oUser === null) {
                App()->user->setFlash('error', gT("User does not exist"));
                $this->redirect(App()->request->urlReferrer);
            }
        }
        $randomPassword = \LimeSurvey\Models\Services\PasswordManagement::getRandomPassword();
        return $this->renderPartial('partial/addedituser', ['oUser' => $oUser, 'randomPassword' => $randomPassword]);
    }

    /**
     * Stores changes to user, or triggers userCreateEvent
     *
     * @return string | JSON
     * @throws CException
     * @throws \PHPMailer\PHPMailer\Exception
     */
    public function actionApplyEdit()
    {
        if (!Permission::model()->hasGlobalPermission('users', 'update')) {
            return Yii::app()->getController()->renderPartial('/admin/super/_renderJson', ["data" => [
                'success' => false,
                'errors' => gT("You do not have permission to access this page."),
            ]]);
        }

        $aUser = Yii::app()->request->getParam('User');
        // Sanitize full name to prevent XSS attack
        if (isset($aUser['full_name'])) {
            $aUser['full_name'] = flattenText($aUser['full_name'], false, true);
        }

        $passwordTest = Yii::app()->request->getParam('password_repeat', false);

        if (!empty($passwordTest)) {
            if ($passwordTest !== $aUser['password']) {
                return Yii::app()->getController()->renderPartial('/admin/super/_renderJson', ["data" => [
                    'success' => false,
                    'errors' => gT('Passwords do not match'),
                ]]);
            }

            $oPasswordTestEvent = new PluginEvent('checkPasswordRequirement');
            $oPasswordTestEvent->set('password', $passwordTest);
            $oPasswordTestEvent->set('passwordOk', true);
            $oPasswordTestEvent->set('passwordError', '');
            Yii::app()->getPluginManager()->dispatchEvent($oPasswordTestEvent);

            if (!$oPasswordTestEvent->get('passwordOk')) {
                return Yii::app()->getController()->renderPartial('/admin/super/_renderJson', ["data" => [
                    'success' => false,
                    'errors' => gT('Passwords does not fulfill minimum requirement:') . '<br/>' . $oPasswordTestEvent->get('passwordError'),
                ]]);
            }
        }

        if (isset($aUser['uid']) && $aUser['uid']) {
            $oUser = $this->updateAdminUser($aUser);
            if ($oUser->hasErrors()) {
                return App()->getController()->renderPartial('/admin/super/_renderJson', [
                    "data" => [
                        'success' => false,
                        'errors'  => $this->renderErrors($oUser->getErrors()) ?? ''
                    ]
                ]);
            }
            return App()->getController()->renderPartial('/admin/super/_renderJson', [
                'data' => [
                    'success' => true,
                    'message' => gT('User successfully updated')
                ]
            ]);
        } else {
            //generate random password when password is empty
            if (empty($aUser['password'])) {
                $newPassword = \LimeSurvey\Models\Services\PasswordManagement::getRandomPassword();
                $aUser['password'] =  $newPassword;
            }

            //retrive the raw password
            $aUser['rawPassword'] = $aUser['password'];

            $passwordSetByUser = Yii::app()->request->getParam('preset_password');
            if ($passwordSetByUser == 0) { //in this case admin has not set a password, email with link will be sent
                $data = $this->createAdminUser($aUser);
            } else {
                //in this case admin has set a password, no email will be send ..just create user with given credentials
                $data = $this->createAdminUser($aUser, false);
            }

            return App()->getController()->renderPartial('/admin/super/_renderJson', [
                "data" => $data
            ]);
        }
    }

    /**
     * Opens the modal to add dummy users
     *
     * @throws CException
     */
    public function actionAddDummyUser()
    {
        return $this->renderPartial('partial/adddummyuser', []);
    }

    /**
     * Creates a batch of dummy users
     *
     * @return string | JSON
     * @throws CException
     */
    public function actionRunAddDummyUser()
    {
        if (!Permission::model()->hasGlobalPermission('users', 'create')) {
            return $this->renderPartial(
                'partial/error',
                ['errors' => [gT("You do not have permission to access this page.")], 'noButton' => true]
            );
        }
        if (!App()->request->isPostRequest) {
            //it has to be post request when inserting data to DB
            return $this->renderPartial(
                'partial/error',
                ['errors' => [gT("Access denied.")], 'noButton' => true]
            );
        }
        $times = App()->request->getPost('times', 5);
        $minPwLength = \LimeSurvey\Models\Services\PasswordManagement::MIN_PASSWORD_LENGTH;
        $passwordSize = (int) App()->request->getPost('passwordsize', $minPwLength);
        $prefix = flattenText(App()->request->getPost('prefix', 'randuser_'));
        $email = App()->request->getPost('email', User::model()->findByPk(App()->user->id)->email);

        $randomUsers = [];

        for (; $times > 0; $times--) {
            $name = $this->getRandomUsername($prefix);
            $password = \LimeSurvey\Models\Services\PasswordManagement::getRandomPassword($passwordSize);
            $oUser = new User();
            $oUser->users_name = $name;
            $oUser->full_name = $name;
            $oUser->email = $email;
            $oUser->parent_id = App()->user->id;
            $oUser->created = date('Y-m-d H:i:s');
            $oUser->modified = date('Y-m-d H:i:s');
            $oUser->password = password_hash($password, PASSWORD_DEFAULT);
            $save = $oUser->save();
            $randomUsers[] = ['username' => $name, 'password' => $password, 'save' => $save];
        }

        return Yii::app()->getController()->renderPartial('/admin/super/_renderJson', ["data" => [
            'success' => true,
            'html' => $this->renderPartial('partial/createdrandoms', ['randomUsers' => $randomUsers, 'filename' => $prefix], true),
        ]]);
    }


    /**
     * Deletes a user after  confirmation
     *
     * @return void|string
     * @throws CException
     */
    public function actionDeleteUser()
    {
        if (!Permission::model()->hasGlobalPermission('users', 'delete')) {
            return $this->renderPartial(
                'partial/error',
                ['errors' => [gT("You do not have permission to access this page.")], 'noButton' => true]
            );
        }
        $userId = Yii::app()->request->getPost('userid');
        if ($userId == Yii::app()->user->id) {
            return App()->getController()->renderPartial('/admin/super/_renderJson', [
                'data' => [
                    'success' => false,
                    'message' => gT("You cannot delete yourself.")
                ]
            ]);
        }

        $message = '';
        $transferTo = Yii::app()->request->getPost('transfer_surveys_to');

        if (empty($transferTo)) {
            // If $transferTo is empty, check if user owns a survey.
            // If so, render the "transfer to" selection screen
            $aOwnedSurveys = Survey::model()->findAllByAttributes(array('owner_id' => $userId));
            if (count($aOwnedSurveys)) {
                $postuser = flattenText(Yii::app()->request->getPost("user"));
                $aUsers = User::model()->findAll();
                return Yii::app()->getController()->renderPartial(
                    '/admin/super/_renderJson',
                    [
                        "data" => [
                            'success' => true,
                            'html' => $this->renderPartial(
                                'partial/transfersurveys',
                                [
                                    'postuserid' => $userId,
                                    'postuser' => $postuser,
                                    'current_user' => Yii::app()->user->id,
                                    'users' => $aUsers,
                                ],
                                true
                            ),
                        ]
                    ]
                );
            }
        } else {
            // If $transferTo is not null, transfer the surveys
            $iSurveysTransferred = Survey::model()->updateAll(array('owner_id' => $transferTo), 'owner_id=' . $userId);
            if ($iSurveysTransferred) {
                $sTransferredTo = User::model()->findByPk($transferTo)->users_name;
                $message = sprintf(gT("All of the user's surveys were transferred to %s."), $sTransferredTo) . " ";
            }
        }

        $oUser = User::model()->findByPk($userId);
        //todo REFACTORING user permissions should be deleted also ... (in table permissions)
        $oUser->delete();
        $message .= gT("User successfully deleted.");
        return App()->getController()->renderPartial('/admin/super/_renderJson', [
            'data' => [
                'success' => true,
                'message' => $message,
            ]
        ]);
    }

    /**
     * Show user delete confirmation
     *
     * @return void|string
     * @throws CException
     */
    public function actionDeleteConfirm()
    {
        if (!Permission::model()->hasGlobalPermission('users', 'delete')) {
            return $this->renderPartial(
                'partial/error',
                ['errors' => [gT("You do not have permission to access this page.")], 'noButton' => true]
            );
        }
        $userId = Yii::app()->request->getParam('userid');
        $sUserName = flattenText(Yii::app()->request->getParam("user"));

        $aData['userId'] = $userId;
        $aData['sUserName'] = $sUserName;

        return $this->renderPartial('partial/confirmuserdelete', $aData);
    }

    /**
     * Show some user detail and statistics
     *
     * @param int $userid
     * @return string|null
     * @throws CException
     */
    public function actionViewUser(int $userid): ?string
    {
        if (!Permission::model()->hasGlobalPermission('users', 'read')) {
            return $this->renderPartial(
                'partial/error',
                ['errors' => [gT("You do not have permission to access this page.")], 'noButton' => true]
            );
        }
        $oUser = User::model()->findByPk($userid);

        $userGroups = array_map(function ($oUGMap) {
            return $oUGMap->group->name;
        }, UserInGroup::model()->findAllByAttributes(['uid' => $oUser->uid]));

        return $this->renderPartial('partial/showuser', ['usergroups' => $userGroups, 'oUser' => $oUser]);
    }

    /**
     * Opens a modal to edit user permissions
     *
     * @return string
     * @throws CException
     */
    public function actionUserPermissions()
    {
        if (!Permission::model()->hasGlobalPermission('users', 'update')) {
            return $this->renderPartial(
                'partial/error',
                ['errors' => [gT("You do not have permission to access this page.")], 'noButton' => true]
            );
        }

        $userId = Yii::app()->request->getParam('userid');
        $userId = sanitize_int($userId);
        $oUser = User::model()->findByPk($userId);

        // Check permissions
        $aBasePermissions = Permission::model()->getGlobalBasePermissions();
        if (!Permission::model()->hasGlobalPermission('superadmin', 'read')) {
            // if not superadmin filter the available permissions as no admin may give more permissions than he owns
            Yii::app()->session['flashmessage'] = gT("Note: You can only give limited permissions to other users because your own permissions are limited, too.");
            $aFilteredPermissions = array();
            foreach ($aBasePermissions as $PermissionName => $aPermission) {
                foreach ($aPermission as $sPermissionKey => &$sPermissionValue) {
                    if (
                        $sPermissionKey != 'title' && $sPermissionKey != 'img' &&
                        !Permission::model()->hasGlobalPermission($PermissionName, $sPermissionKey)
                    ) {
                        $sPermissionValue = false;
                    }
                }
                // Only show a row for that permission if there is at least one permission he may give to other users
                if (
                    $aPermission['create'] || $aPermission['read'] || $aPermission['update']
                    || $aPermission['delete'] || $aPermission['import'] || $aPermission['export']
                ) {
                    $aFilteredPermissions[$PermissionName] = $aPermission;
                }
            }
            $aBasePermissions = $aFilteredPermissions;
        }

        return $this->renderPartial(
            'partial/editpermissions',
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
     * @throws CException
     */
    public function actionSaveUserPermissions(): string
    {
        if (!Permission::model()->hasGlobalPermission('users', 'update')) {
            return $this->renderPartial(
                'partial/error',
                ['errors' => [gT("You do not have permission to access this page.")], 'noButton' => true]
            );
        }
        $userId = Yii::app()->request->getPost('userid');
        $aPermissions = Yii::app()->request->getPost('Permission', []);

        Permissiontemplates::model()->clearUser($userId);

        $results = $this->applyPermissionFromArray($userId, $aPermissions);

        $oUser = User::model()->findByPk($userId);
        $oUser->modified = date('Y-m-d H:i:s');
        $oUser->save();

        return Yii::app()->getController()->renderPartial('/admin/super/_renderJson', [
            "data" => [
                'success' => true,
                'html'    => $this->renderPartial('partial/permissionsuccess', ['results' => $results], true),
            ]
        ]);
    }

    /**
     * Opens a modal to edit user template permissions
     *
     * @return string|null
     * @throws CException
     */
    public function actionUserTemplatePermissions(): ?string
    {
        if (!Permission::model()->hasGlobalPermission('users', 'update')) {
            return $this->renderPartial(
                'partial/error',
                ['errors' => [gT("You do not have permission to access this page.")], 'noButton' => true]
            );
        }
        $aTemplateModels = Template::model()->findAll();
        $userId = Yii::app()->request->getParam('userid');
        $oUser = User::model()->findByPk((int)$userId);

        $aTemplates = array_map(function ($oTemplate) use ($userId) {
            $oPermission = Permission::model()->findByAttributes(array('permission' => $oTemplate->folder, 'uid' => $userId, 'entity' => 'template'));
            $aTemplate = $oTemplate->attributes;
            $aTemplate['value'] = $oPermission == null ? 0 : $oPermission->read_p;
            return $aTemplate;
        }, $aTemplateModels);

        return $this->renderPartial(
            'partial/edittemplatepermissions',
            [
                "oUser" => $oUser,
                "aTemplates" => $aTemplates,
            ]
        );
    }

    /**
     * Stores the changed permissions
     *
     * @return string | JSON
     * @throws CException
     */
    public function actionSaveThemePermissions(): string
    {
        if (
            !(Permission::model()->hasGlobalPermission('users', 'update') &&
            Permission::model()->hasGlobalPermission('templates', 'update'))
        ) {
            return $this->renderPartial(
                'partial/error',
                ['errors' => [gT("You do not have permission to access this page.")], 'noButton' => true]
            );
        }

        $userId = Yii::app()->request->getPost('userid');
        $aTemplatePermissions = Yii::app()->request->getPost('TemplatePermissions', []);

        $results = Permission::editThemePermissionsUser($userId, $aTemplatePermissions);

        return Yii::app()->getController()->renderPartial('/admin/super/_renderJson', [
            "data" => [
                'success' => true,
                'html'    => $this->renderPartial('partial/permissionsuccess', ['results' => $results], true),
            ]
        ]);
    }

    /**
     * Opens the modal to add dummy users
     *
     * @return string|null
     * @throws CException
     */
    public function actionAddRole(): ?string
    {
        //Permission check user should have permission to add/edit new user ('create' or 'update')
        if (
            !(Permission::model()->hasGlobalPermission('users', 'create') ||
            Permission::model()->hasGlobalPermission('users', 'update'))
        ) {
            return $this->renderPartial(
                'partial/error',
                ['errors' => [gT("You do not have permission to access this page.")], 'noButton' => true]
            );
        }

        $userId = Yii::app()->request->getParam('userid');
        $oUser = User::model()->findByPk($userId);
        $aPermissionTemplates = Permissiontemplates::model()->findAll();
        $aPossibleRoles = [];
        array_walk(
            $aPermissionTemplates,
            function ($oPermissionRole) use (&$aPossibleRoles) {
                $aPossibleRoles[$oPermissionRole->ptid] = $oPermissionRole->name;
            }
        );
        $aCurrentRoles = array_map(function ($oRole) {
            return $oRole->ptid;
        }, $oUser->roles);

        return $this->renderPartial(
            'partial/addrole',
            [
                'oUser' => $oUser,
                'aPossibleRoles' => $aPossibleRoles,
                'aCurrentRoles' => $aCurrentRoles,
            ]
        );
    }

    /**
     * Save role of user
     *
     * @return string|null
     * @throws CException
     */
    public function actionSaveRole(): ?string
    {
        if (!Permission::model()->hasGlobalPermission('users', 'update')) {
            return $this->renderPartial(
                'partial/error',
                ['errors' => [gT("You do not have permission to access this page.")], 'noButton' => true]
            );
        }
        $iUserId = Yii::app()->request->getPost('userid');
        $aUserRoleIds = Yii::app()->request->getPost('roleselector', []);
        $results = [];

        $clearUser = Permissiontemplates::model()->clearUser($iUserId);
        foreach ($aUserRoleIds as $iUserRoleId) {
            if ($iUserRoleId == '') {
                continue;
            }
            $results[$iUserRoleId] = Permissiontemplates::model()->applyToUser($iUserId, $iUserRoleId);
        }
        if (empty($aUserRoleIds)) {
            $results['clear'] = $clearUser;
        }
        return $this->renderPartial('partial/json', [
            "data" => [
                'success' => true,
                'html'    => $this->renderPartial('partial/permissionsuccess', ['results' => $results], true),
            ]
        ]);
    }

    /**
     * Calls up a modal to import users via csv/json file
     *
     * @param string $importFormat - Importformat (csv/json) to render
     * @return string
     * @throws CException
     */
    public function actionRenderUserImport(string $importFormat = 'csv')
    {
        if (!Permission::model()->hasGlobalPermission('users', 'create')) {
            return $this->renderPartial(
                'partial/error',
                ['errors' => [gT("You do not have permission to access this page.")], 'noButton' => true]
            );
        }

        $importNote = sprintf(gT("Please make sure that your CSV contains the fields '%s', '%s', '%s', '%s', and '%s'"), '<b>users_name</b>', '<b>full_name</b>', '<b>email</b>', '<b>lang</b>', '<b>password</b>');
        $allowFileType = ".csv";

        if ($importFormat == 'json') {
            $importNote = sprintf(gT("Please make sure that your JSON arrays contain the fields '%s', '%s', '%s', '%s', and '%s'"), '<b>users_name</b>', '<b>full_name</b>', '<b>email</b>', '<b>lang</b>', '<b>password</b>');
            $allowFileType = ".json,application/json";
        }

        return $this->renderPartial('partial/importuser', [
            "note"         => $importNote,
            "importFormat" => $importFormat,
            "allowFile"    => $allowFileType
        ]);
    }

    /**
     * Creates users from an uploaded CSV / JSON file
     *
     * @param string $importFormat - format of the imported file - Choice between csv / json
     * @return string
     * @throws CException
     */
    public function actionImportUsers(string $importFormat = 'csv'): string
    {
        if (!Permission::model()->hasGlobalPermission('users', 'create')) {
            return $this->renderPartial(
                'partial/error',
                ['errors' => [gT("You do not have permission to access this page.")], 'noButton' => true]
            );
        }

        $overwriteUsers = boolval(App()->getRequest()->getPost('overwrite'));

        switch ($importFormat) {
            case "json":
                $aNewUsers = UserParser::getDataFromJSON($_FILES);
                break;
            case "csv":
            default:
                $aNewUsers = UserParser::getDataFromCSV($_FILES); //importFormat default is csv ...
        }
        if (empty($aNewUsers)) {
            Yii::app()->setFlashMessage(gT("No user definition found in file."), 'error');
            $this->redirect(['userManagement/index']);
            App()->end();
        }
        $created = [];
        $updated = [];

        foreach ($aNewUsers as $aNewUser) {
            $oUser = User::model()->findByAttributes(['users_name' => $aNewUser['users_name']]);

            if ($oUser  !== null) {
                if ($overwriteUsers) {
                    $oUser->full_name = $aNewUser['full_name'];
                    $oUser->email = $aNewUser['email'];
                    $oUser->parent_id = App()->user->id;
                    $oUser->modified = date('Y-m-d H:i:s');
                    if ($aNewUser['password'] != ' ') {
                        $oUser->password = password_hash($aNewUser['password'], PASSWORD_DEFAULT);
                    }

                    $save = $oUser->save();
                    if ($save) {
                        $updated[] = [
                            'username' => $aNewUser['users_name'],
                            'full_name' => $aNewUser['full_name'],
                            'email' => $aNewUser['email'],
                        ];
                    }
                }
            } else {
                $password = \LimeSurvey\Models\Services\PasswordManagement::getRandomPassword();
                $passwordText = $password;
                if ($aNewUser['password'] != ' ') {
                    $password = password_hash($aNewUser['password'], PASSWORD_DEFAULT);
                }

                $save = $this->createNewUser([
                    'users_name' => $aNewUser['users_name'],
                    'full_name' => $aNewUser['full_name'],
                    'password' => $password,
                    'email' => $aNewUser['email'],
                    'lang' => $aNewUser['lang'],
                ]);

                if ($save) {
                    $created[] = [
                        'username' => $aNewUser['users_name'],
                        'full_name' => $aNewUser['full_name'],
                        'email' => $aNewUser['email'],
                        'password' => $passwordText,
                    ];
                }
            }
        }

        Yii::app()->setFlashMessage(gT("Users imported successfully."), 'success');
        $this->redirect(['userManagement/index']);
    }


    /**
     * Export users with specific format (json or csv)
     *
     * @param string $outputFormat json or csv
     * @param int $uid userId   if 0, all users will be exported
     * @return mixed
     * @throws CException
     */
    public function actionExportUser(string $outputFormat, int $uid = 0)
    {
        //Check if user has permissions to export users
        if (!Permission::model()->hasGlobalPermission('users', 'export')) {
            return $this->renderPartial(
                'partial/error',
                ['errors' => [gT("You do not have permission to access this page.")], 'noButton' => true]
            );
        }

        if ($uid > 0) {
            $oUsers = User::model()->findByPk($uid);
        } else {
            $oUsers = User::model()->findAll();
        }

        //test GET PARAM $ouputFormat
        switch ($outputFormat) {
            case 'csv':
            case 'json':  //all good, both cases are ok
                break;
            default:
                $outputFormat = 'csv';
        }

        $aUsers = array();
        $sTempDir = Yii::app()->getConfig("tempdir");
        $exportFile = $sTempDir . DIRECTORY_SEPARATOR . 'users_export.' . $outputFormat;

        foreach ($oUsers as $user) {
            $exportUser['uid'] = $user->attributes['uid'];
            $exportUser['users_name'] = $user->attributes['users_name'];
            $exportUser['full_name'] = $user->attributes['full_name'];
            $exportUser['email'] = $user->attributes['email'];
            $exportUser['lang'] = $user->attributes['lang'];
            $exportUser['password'] = '';
            array_push($aUsers, $exportUser);
        }

        switch ($outputFormat) {
            case "json":
                $json = json_encode($aUsers, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
                $fp = fopen($exportFile, 'w');
                fwrite($fp, $json);
                fclose($fp);
                header('Content-Encoding: UTF-8');
                header("Content-Type:application/json; charset=UTF-8");
                break;

            case "csv":
                $fp = fopen($exportFile, 'w');

                //Add utf-8 encoding
                fprintf($fp, chr(0xEF) . chr(0xBB) . chr(0xBF));
                $header = array('uid', 'users_name', 'full_name', 'email', 'lang', 'password');
                //Add csv header
                fputcsv($fp, $header, ';');

                //add csv row datas
                foreach ($aUsers as $fields) {
                    fputcsv($fp, $fields, ';');
                }
                fclose($fp);
                header('Content-Encoding: UTF-8');
                header("Content-type: text/csv; charset=UTF-8");
                break;
        }
        //end file to download
        header("Content-Disposition: attachment; filename=userExport." . $outputFormat);
        header("Pragma: no-cache");
        header("Expires: 0");
        @readfile($exportFile);
        unlink($exportFile);
    }

    /**
     * Delete multiple users selected by massive action
     *
     * @return void|string
     * @throws CException
     * @throws CHttpException
     */
    public function actionDeleteMultiple()
    {
        if (!Permission::model()->hasGlobalPermission('users', 'delete')) {
            return $this->renderPartial(
                'partial/error',
                ['errors' => [gT("You do not have permission to access this page.")], 'noButton' => true]
            );
        }

        $aUsers = json_decode(App()->request->getPost('sItems'));
        $aResults = [];

        foreach ($aUsers as $user) {
            $aResults[$user]['title'] = '';
            $model = $this->loadModel($user);
            $aResults[$user]['title'] = $model->users_name;
            $aResults[$user]['result'] = $this->deleteUser($user);
            if (!$aResults[$user]['result'] && $user == Yii::app()->user->id) {
                $aResults[$user]['error'] = gT("You cannot delete yourself or a protected user.");
            }
        }

        $tableLabels = array(gT('User ID'), gT('Username'), gT('Status'));

        Yii::app()->getController()->renderPartial(
            'ext.admin.survey.ListSurveysWidget.views.massive_actions._action_results',
            array(
                'aResults'     => $aResults,
                'successLabel' => gT('Deleted'),
                'tableLabels' =>  $tableLabels
            )
        );
    }

    /**
     * render selected items for massive action modal
     *
     * @return void
     * @throws CHttpException
     * @throws CException
     */
    public function actionRenderSelectedItems()
    {
        $aUsers = json_decode(App()->request->getPost('$oCheckedItems'));
        $aResults = [];
        $gridid = App()->request->getParam('$grididvalue');

        foreach ($aUsers as $user) {
            $aResults[$user]['title'] = '';
            $model = $this->loadModel($user);

            if ($gridid == 'usermanagement--identity-gridPanel') {
                $aResults[$user]['title'] = $model->users_name;
            }

            $aResults[$user]['result'] = gT('Selected');
        }
        //set Modal table labels
        $tableLabels = array(gT('User ID'), gT('Username'), gT('Status'));

        App()->getController()->renderPartial(
            'ext.admin.grid.MassiveActionsWidget.views._selected_items',
            array(
                'aResults'     => $aResults,
                'successLabel' => gT('Selected'),
                'tableLabels'  => $tableLabels,
            )
        );
    }

    /**
     * Method to resend a password to selected surveyadministrators (MassAction)
     *
     * @return String
     * @throws CException
     * @throws \PHPMailer\PHPMailer\Exception
     */
    public function actionBatchSendAndResetLoginData()
    {
        if (!Permission::model()->hasGlobalPermission('users', 'update')) {
            return $this->renderPartial(
                'partial/error',
                ['errors' => [gT("You do not have permission to access this page.")], 'noButton' => true]
            );
        }

        $aUsers = json_decode(App()->request->getPost('sItems', "[]"));

        $aResults = [];
        foreach ($aUsers as $user) {
            $oUser = $this->loadModel($user);
            $aResults[$user]['result'] = false;
            $aResults[$user]['title'] = $oUser->users_name;
            //User should not reset and resend  email to himself throw massive action
            if ($oUser->uid == Yii::app()->user->id) {
                $aResults[$user]['error'] = gT("Error! Please change your password from your profile settings.");
            } else {
                //not modify original superuser
                if ($oUser->uid == 1) {
                    $aResults[$user]['error'] = gT("Error! You do not have the permission to edit this user.");
                } else {
                    $passwordManagement = new \LimeSurvey\Models\Services\PasswordManagement($oUser);
                    $successData = $passwordManagement->sendPasswordLinkViaEmail(\LimeSurvey\Models\Services\PasswordManagement::EMAIL_TYPE_RESET_PW);
                    $success = $successData['success'];
                    if (!$success) {
                        $aResults[$user]['error'] = sprintf(gT("Error: New password could not be sent to %s"), $oUser->email);
                    }
                    $aResults[$user]['result'] = $success;
                }
            }
        }

        $tableLabels = array(gT('User ID'), gT('Username'), gT('Status'));

        Yii::app()->getController()->renderPartial(
            'ext.admin.survey.ListSurveysWidget.views.massive_actions._action_results',
            array(
                'aResults'     => $aResults,
                'successLabel' => gT('Email successfully sent.'),
                'tableLabels' =>  $tableLabels
            )
        );
    }

    /**
     * Stores the permission settings run via MassEdit
     *
     * @return string
     * @throws CException
     */
    public function actionBatchPermissions()
    {
        if (!Permission::model()->hasGlobalPermission('users', 'update')) {
            return $this->renderPartial(
                'partial/error',
                ['errors' => [gT("You do not have permission to access this page.")], 'noButton' => true]
            );
        }

        $userIds = json_decode(Yii::app()->request->getPost('sItems', "[]"));
        $aPermissions = Yii::app()->request->getPost('Permission', []);
        $results = [];
        foreach ($userIds as $iUserId) {
            $aPermissionsResults = $this->applyPermissionFromArray($iUserId, $aPermissions);
            $oUser = User::model()->findByPk($iUserId);
            $oUser->modified = date('Y-m-d H:i:s');
            $results[$iUserId]['result'] = $oUser->save();
            $results[$iUserId]['title'] = $oUser->users_name;
            foreach ($aPermissionsResults as $aPermissionsResult) {
                if (!$aPermissionsResult['success']) {
                    $results[$iUserId]['result'] = false;
                    break;
                }
            }
            if (!$results[$iUserId]['result']) {
                $results[$iUserId]['error'] = gT('Error');
            }
        }

        $tableLabels = array(gT('User ID'), gT('Username'), gT('Status'));

        Yii::app()->getController()->renderPartial(
            'ext.admin.survey.ListSurveysWidget.views.massive_actions._action_results',
            array(
                'aResults'     => $results,
                'successLabel' => gT('Saved successfully'),
                'tableLabels' =>  $tableLabels
            )
        );
    }

    /**
     * Mass edition apply roles
     *
     * @return string|null|void
     * @throws CException
     * @throws CHttpException
     */
    public function actionBatchAddGroup()
    {
        if (!Permission::model()->hasGlobalPermission('users', 'update')) {
            return $this->renderPartial(
                'partial/error',
                ['errors' => [gT("You do not have permission to access this page.")], 'noButton' => true]
            );
        }
        $aItems = json_decode(Yii::app()->request->getPost('sItems', []));
        $iUserGroupId = Yii::app()->request->getPost('addtousergroup');

        if ($iUserGroupId) {
            $oUserGroup = UserGroup::model()->findByPk($iUserGroupId);
            $aResults = [];

            foreach ($aItems as $sItem) {
                $aResults[$sItem]['title'] = '';
                $model = $this->loadModel($sItem);
                $aResults[$sItem]['title'] = $model->users_name;
                if (!$oUserGroup->hasUser($sItem)) {
                    $aResults[$sItem]['result'] = $oUserGroup->addUser($sItem);
                } else {
                    $aResults[$sItem]['result'] = false;
                    $aResults[$sItem]['error'] = gT('User is already a member of the group.');
                }
            }
        } else {
            foreach ($aItems as $sItem) {
                $aResults[$sItem]['title'] = '';
                $model = $this->loadModel($sItem);
                $aResults[$sItem]['title'] = $model->users_name;
                $aResults[$sItem]['result'] = false;
                $aResults[$sItem]['error'] = gT('No user group selected.');
            }
        }

        $tableLabels = array(gT('User ID'), gT('Username'), gT('Status'));

        Yii::app()->getController()->renderPartial(
            'ext.admin.survey.ListSurveysWidget.views.massive_actions._action_results',
            array(
                'aResults'     => $aResults,
                'successLabel' => gT('Usergroup updated'),
                'tableLabels' =>  $tableLabels
            )
        );
    }

    /**
     * Mass edition apply roles
     *
     * @return string
     * @throws CException
     */
    public function actionBatchApplyRoles()
    {
        if (!Permission::model()->hasGlobalPermission('users', 'update')) {
            return $this->renderPartial(
                'partial/error',
                ['errors' => [gT("You do not have permission to access this page.")], 'noButton' => true]
            );
        }
        $aItems = json_decode(Yii::app()->request->getPost('sItems', []));
        $aUserRoleIds = Yii::app()->request->getPost('roleselector');
        $aResults = [];

        foreach ($aItems as $sItem) {
            $aResults[$sItem]['title'] = '';
            $model = $this->loadModel($sItem);
            $aResults[$sItem]['title'] = $model->users_name;

            //check if user is admin otherwhise change the role
            if (intval($sItem) == 1) {  //todo REFACTORING is admin id always 1?? is there another possibility to check for admin user?
                $aResults[$sItem]['result'] = false;
                $aResults[$sItem]['error'] = gT('The superadmin role cannot be changed.');
            } else {
                foreach ($aUserRoleIds as $iUserRoleId) {
                    $aResults[$sItem]['result'] = Permissiontemplates::model()->applyToUser($sItem, $iUserRoleId);
                }
            }
        }

        $tableLabels = array(gT('User ID'), gT('Username'), gT('Status'));

        Yii::app()->getController()->renderPartial(
            'ext.admin.survey.ListSurveysWidget.views.massive_actions._action_results',
            array(
                'aResults'     => $aResults,
                'successLabel' => gT('Role updated'),
                'tableLabels' =>  $tableLabels
            )
        );
    }

    /**
     * Takes ownership on user after confirmation
     *
     * @return void | string
     * @throws CException
     */
    public function actionTakeOwnership()
    {
        if (!Permission::model()->hasGlobalPermission('users', 'update')) {
            return $this->renderPartial(
                'partial/error',
                ['errors' => [gT("You do not have permission to access this page.")], 'noButton' => true]
            );
        }
        $userId = Yii::app()->request->getPost('userid');
        $oUser = User::model()->findByPk($userId);
        $oUser->parent_id = Yii::app()->user->id;
        $oUser->save();
        $this->redirect(Yii::app()->createUrl("userManagement/index"));
    }

    /**
     * Deletes a user
     *
     * @param int $uid
     * @return boolean
     * @throws CException
     */
    public function deleteUser(int $uid): bool
    {
        if (!Permission::model()->hasGlobalPermission('users', 'delete')) {
            return $this->renderPartial(
                'partial/error',
                ['errors' => [gT("You do not have permission to access this page.")], 'noButton' => true]
            );
        }

        if ($uid == Yii::app()->user->id) {
            return false;
        }
        if (Permission::isForcedSuperAdmin($uid)) {
            return false;
        }

        $oUser = User::model()->findByPk($uid);
        return $oUser->delete();
    }

    /**
     * Returns the data model based on the primary key given in the GET variable.
     * If the data model is not found, an HTTP exception will be raised.
     *
     * @param int $id the ID of the model to be loaded
     *
     * @return User|null  object
     * @throws CHttpException
     */
    public function loadModel(int $id): User
    {
        $model = User::model()->findByPk($id);

        if ($model === null) {
            throw new CHttpException(404, 'The requested page does not exist.');
        }

        return $model;
    }

    /**
     * Update admin-user
     *
     * REFACTORED (in UserManagementController)
     *
     * @param array $aUser array with user details
     * @return object user - updated user object
     * @throws CException
     */
    public function updateAdminUser(array $aUser): User
    {
        $oUser = User::model()->findByPk($aUser['uid']);
        //If the user id of the post is spoofed somehow it would be possible to edit superadmin users
        //Therefore we need to make sure no non-superadmin can modify superadmin accounts
        //Since this should NEVER be the case without hacking the software, this will silently just do nothing.
        if (
            !Permission::model()->hasGlobalPermission('superadmin', 'read', Yii::app()->user->id)
            && Permission::model()->hasGlobalPermission('superadmin', 'read', $oUser->uid)
        ) {
            throw new CException("This action is not allowed, and should never happen", 500);
        }

        $aUser['full_name'] = flattenText($aUser['full_name']); //to prevent xss ...
        $oUser->setAttributes($aUser);

        if (isset($aUser['password']) && $aUser['password']) {
            $oUser->password = password_hash($aUser['password'], PASSWORD_DEFAULT);
        }
        $oUser->modified = date('Y-m-d H:i:s');
        $oUser->save();

        return  $oUser;
    }

    /**
     * This method creates a new admin user and returns success or error message
     *
     * @param array $aUser array with attributes from user model
     * @param boolean $sendEmail true if email should be send, false otherwise
     *
     * @return array
     *
     * @throws CException
     * @throws \PHPMailer\PHPMailer\Exception
     */
    private function createAdminUser(array $aUser, bool $sendEmail = true): array
    {
        if (!isset($aUser['uid']) || $aUser['uid'] == null) {
            $newUser = $this->createNewUser($aUser);
            $success = true;
            $sReturnMessage = gT('User successfully created');

            if (Yii::app()->getConfig("sendadmincreationemail") && $sendEmail) {
                $user = User::model()->findByPk($newUser['uid']);
                $passwordManagement = new \LimeSurvey\Models\Services\PasswordManagement($user);
                $successData = $passwordManagement->sendPasswordLinkViaEmail(\LimeSurvey\Models\Services\PasswordManagement::EMAIL_TYPE_REGISTRATION);

                $sReturnMessage = $successData['sReturnMessage'];
                $success = $successData['success'];
            }

            if ($success) {
                $data = [
                    'success' => $success,
                    'message' => $sReturnMessage,
                    'href' => Yii::app()->getController()->createUrl('userManagement/userPermissions', ['userid' => $newUser['uid']]),
                    'modalsize' => 'modal-lg',
                ];
            } else {
                $data = [
                    'success' => $success,
                    'errors' => $sReturnMessage
                ];
            }

            return $data;
        }

        return [
            'success' => false,
            'errors' => CHtml::tag("p", array(), gT("Error: User was not created"))
        ];
    }

    /**
     * Create new user
     *
     * @param array $aUser array with user details
     * @return array returns all attributes from model user as an array
     * @throws CException
     */
    public function createNewUser(array $aUser): array
    {
        if (!Permission::model()->hasGlobalPermission('users', 'create')) {
            return Yii::app()->getController()->renderPartial('/admin/super/_renderJson', [
                "data" => [
                    'success' => false,
                    'errors'  => gT("You do not have permission for this action."),
                ]
            ]);
        }

        $aUser['users_name'] = flattenText($aUser['users_name']);

        if (empty($aUser['users_name'])) {
            return Yii::app()->getController()->renderPartial('/admin/super/_renderJson', [
                "data" => [
                    'success' => false,
                    'errors'  => gT("A username was not supplied or the username is invalid."),
                ]
            ]);
        }

        if (User::model()->find("users_name=:users_name", array(':users_name' => $aUser['users_name']))) {
            return Yii::app()->getController()->renderPartial('/admin/super/_renderJson', [
                "data" => [
                    'success' => false,
                    'errors'  => gT("A user with this username already exists."),
                ]
            ]);
        }

        $event = new PluginEvent('createNewUser');
        $event->set('errorCode', AuthPluginBase::ERROR_NOT_ADDED);
        $event->set('errorMessageTitle', gT("Failed to add user"));
        $event->set('errorMessageBody', gT("Plugin is not active"));
        $event->set('preCollectedUserArray', $aUser);

        Yii::app()->getPluginManager()->dispatchEvent($event);

        if ($event->get('errorCode') != AuthPluginBase::ERROR_NONE) {
            return Yii::app()->getController()->renderPartial('/admin/super/_renderJson', [
                "data" => [
                    'success' => false,
                    'errors'  => $event->get('errorMessageTitle')
                                    . '<br/>'
                                    . $event->get('errorMessageBody'),
                    'debug'   => [
                        'title' => $event->get('errorMessageTitle'),
                        'body'  => $event->get('errorMessageBody'),
                        'code'  => $event->get('errorCode'),
                        'event' => $event
                    ],
                ]
            ]);
        }
        $iNewUID = $event->get('newUserID');
        // add default template to template rights for user
        Permission::model()->insertSomeRecords(
            array(
                'uid'         => $iNewUID,
                'permission'  => App()->getConfig('defaulttheme'),
                'entity'      => 'template',
                'read_p'      => 1,
                'entity_id'   => 0
            )
        );
        // add default usersettings to the user
        SettingsUser::applyBaseSettings($iNewUID);

        return User::model()->findByPk($iNewUID)->attributes;
    }

    /**
     * todo this should not be in a controller, find a better place for it (view)
     *
     *
     * @param array $errors
     *
     * @return string $errorDiv
     */
    private function renderErrors(array $errors): string
    {
        $errorDiv = '<ul class="list-unstyled">';
        foreach ($errors as $key => $error) {
            foreach ($error as $errorMessages) {
                $errorDiv .= '<li>' . print_r($errorMessages, true) . '</li>';
            }
        }
        $errorDiv .= '</ul>';
        return (string) $errorDiv;
    }

    /**
     * Creates a random unique username using prefix
     *
     * todo this should be moved to model user ...
     *
     * @param string $prefix the prefix to be used
     * @return string
     */
    protected function getRandomUsername(string $prefix): string
    {
        do {
            $rand = $this->getRandomString();
            $username = $prefix . '_' . substr($rand, rand(0, strlen($rand) - 6), 4);
            $oUser = User::model()->findByAttributes(['users_name' => $username]);
        } while ($oUser != null);
        return $username;
    }

    /**
     * Creates a random string
     *
     * todo REFACTORING this should be moved to model user ...see getRandomUsername
     *
     * @return string
     */
    protected function getRandomString(): string
    {
        if (is_callable('openssl_random_pseudo_bytes')) {
            $uiq = openssl_random_pseudo_bytes(128);
        } else {
            $uiq = decbin(rand(1000000, 9999999) * (rand(100, 999) . rand(100, 999) . rand(100, 999) . rand(100, 999)));
        }
        return hash('sha256', bin2hex($uiq));
    }

    /**
     * Adds permission to a users
     * Needs an array in the form of [PERMISSIONID][PERMISSION]
     *
     * todo REFACTORING this should be moved to model (user or permission)
     *
     * @param int $iUserId
     * @param array $aPermissionArray
     * @return array
     */
    protected function applyPermissionFromArray(int $iUserId, array $aPermissionArray): array
    {
        //Delete all current Permissions
        $oCriteria = new CDbCriteria();
        $oCriteria->compare('uid', $iUserId);
        // without entity
        $oCriteria->compare('entity_id', 0);
        // except for template entity (no entity_id is set here)
        $oCriteria->compare('entity', "<>template");
        Permission::model()->deleteAll($oCriteria);

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

            $aPermissionData = Permission::getGlobalPermissionData($sPermissionKey);

            $results[$sPermissionKey] = [
                'descriptionData' => $aPermissionData,
                'success' => $oPermission->save(),
                'storedValue' => $oPermission->attributes
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
    /*
    protected function applyPermissionTemplate($oUser, $permissionclass, $entity_ids = [])
    {
        if ($permissionclass == 'Group manager' && empty($entity_ids)) {
            return [
                "success" => false,
                "error" => "No survey selected for permissions",
            ];
        }
        $oCriteria = new CDbCriteria();
        $oCriteria->compare('uid', $oUser->uid);
        //Kill all Permissions
        $aPermissionsCurrently = Permission::model()->deleteAll($oCriteria);

        //Allow Login again
        Permission::model()->setGlobalPermission($oUser->uid, 'auth_db');

        $result = false;
        if (in_array($permissionclass, ['Survey manager', 'Scientist', 'combo'])) {
            $result = $this->applyGlobalPermissionTemplate($oUser, $permissionclass);
            $this->applyCorrectUsergroup($oUser->uid, ($permissionclass == 'combo' ? ['Survey manager', 'Scientist'] : [$permissionclass]));
        } elseif ($permissionclass == 'Group manager') {
            $result = $this->applySurveyPermissionTemplate($oUser, $permissionclass, $entity_ids);
            $this->applyCorrectUsergroup($oUser->uid, [$permissionclass]);
        }
        return $result;
    }*/

    /**
     * CURRENTLY UNUSED
     * Apply global permission from template
     *
     * @param User $oUser
     * @param string $permissionclass
     * @return array
     */
    /*
    protected function applyGlobalPermissionTemplate($oUser, $permissionclass)
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
    }*/

    /**
     * CURRENTLY UNUSED
     * Add survey specific permissions by template
     *
     * @param User $oUser
     * @param string $permissionclass
     * @param array $entity_ids
     * @return array
     */
    /*
    protected function applySurveyPermissionTemplate($oUser, $permissionclass, $entity_ids)
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
    }*/
}
