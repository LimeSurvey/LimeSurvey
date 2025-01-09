<?php

class UserRoleController extends LSBaseController
{
    /**
     * Run filters
     *
     * @return array|void
     */
    public function filters()
    {
        return [
          'postOnly + delete, applyEdit, savePermissions, batchDelete'
        ];
    }

    /**
     * Renders the list of user roles.
     *
     * @throws CException
     */
    public function actionIndex()
    {
        if (!Permission::model()->hasGlobalPermission('superadmin', 'read')) {
            App()->session['flashmessage'] = gT('You have no access to the role management!');
            $this->redirect(array('/admin'));
        }

        App()->getClientScript()->registerPackage('permissionroles');
        $request = App()->request;

        $massiveAction = $this->renderPartial(
            'massiveAction/_selector',
            [],
            true
        );

        // Set page size
        $pageSize = $request->getParam('pageSize');
        if ($pageSize != null) {
            App()->user->setState('pageSize', (int)$pageSize);
        }

        $model = Permissiontemplates::model();

        //todo do we need that param here? seems not to be used anywhere
        $aPermissiontemplatesParam = $request->getParam('Permissiontemplates');
        if ($aPermissiontemplatesParam) {
            $model->setAttributes($aPermissiontemplatesParam, false);
        }

        $aData['topbar']['title'] = gT('User roles');
        $aData['topbar']['backLink'] = App()->createUrl('dashboard/view');

        $aData['topbar']['middleButtons'] = $this->renderPartial(
            'partials/topbarBtns/leftSideButtons',
            [],
            true
        );


        //this is really important, so we have the aData also before rendering the content
        $this->aData = $aData;

        $this->render(
            'index',
            [
                'model'         => $model,
                'massiveAction' => $massiveAction,
                'pageTitle'     => gT('User roles'),
            ]
        );
    }


    /**
     * Returns the modal view for adding/editing a user role
     *
     * @param int $ptid (optional)
     * @throws CException
     */
    public function actionEditRoleModal(?int $ptid = 0)
    {
        if (!Permission::model()->hasGlobalPermission('superadmin', 'read')) {
            Yii::app()->session['flashmessage'] = gT('You have no access to the role management!');
            $this->getController()->redirect(array('/admin'));
        }

        $model = $this->loadModel($ptid);
        $this->renderPartial('partials/_form', ['model' => $model]);
    }

    /**
     * Updates the role itself (name, description).
     * Renders a modal view with success/error message.
     *
     * @return string|string[]|null
     * @throws CException
     * @throws CHttpException
     */
    public function actionApplyEdit()
    {
        if (!Permission::model()->hasGlobalPermission('superadmin', 'read')) {
            Yii::app()->session['flashmessage'] = gT('You have no access to the role management!');
            $this->getController()->redirect(array('/admin'));
        }

        $aPermissiontemplate = Yii::app()->request->getPost('Permissiontemplates');
        $model = $this->loadModel($aPermissiontemplate['ptid']);

        // XSS filter
        $aPermissiontemplate['name'] = CHtml::encode($aPermissiontemplate['name']);
        $aPermissiontemplate['description'] = CHtml::encode($aPermissiontemplate['description']);

        $newAttributes = array_merge($model->attributes, $aPermissiontemplate);
        $model->attributes = $newAttributes;

        $success = $model->save();
        if ($success) {
            $message = gT('Role successfully saved');
        } else {
            $message = gT('Failed saving the role');
            $errors = $model->getErrors();
            $errorDiv = $this->renderErrors($errors);
        }
        return $this->renderPartial('/admin/super/_renderJson', [
            "data" => [
                'success' => $success,
                'message' => $message,
                'errors'  => $errorDiv ?? ''
            ]
        ]);
    }

    /**
     * Renders the modal for adding the permissions to the role.
     *
     * @return array|false|mixed|string|string[]|void|null
     * @throws CException
     */
    public function actionRenderModalPermissions()
    {
        if (!Permission::model()->hasGlobalPermission('superadmin', 'read')) {
            return $this->renderPartial(
                'partial/error',
                ['errors' => [gT("You do not have permission to access this page.")],'noButton' => true]
            );
        }

        $oRequest = Yii::app()->request;
        $ptid = $oRequest->getParam('ptid');
        $oPermissionTemplate = Permissiontemplates::model()->findByPk($ptid);

        // Check permissions
        $aBasePermissions = Permission::model()->getGlobalBasePermissions();

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

        return $this->renderPartial(
            'partials/_permissions',
            [
                "oModel" => $oPermissionTemplate,
                "aBasePermissions" => $aBasePermissions
            ]
        );
    }

    /**
     * Save Permissions
     *
     * @return array|false|mixed|string|string[]|void|null
     * @throws CException
     */
    public function actionSavePermissions()
    {
        if (!Permission::model()->hasGlobalPermission('superadmin', 'read')) {
            return $this->renderPartial(
                'partial/error',
                ['errors' => [gT("You do not have permission to access this page.")],'noButton' => true]
            );
        }

        $oRequest = Yii::app()->request;
        $ptid = $oRequest->getPost('ptid');
        $aPermissions = $oRequest->getPost('Permission', []);
        $oPermissionTemplate = Permissiontemplates::model()->findByPk((int)$ptid);
        $results = $this->applyPermissionFromArray($ptid, $aPermissions);

        $oPermissionTemplate->renewed_last = date('Y-m-d H:i:s');
        $oPermissionTemplate->save();

        $html = $this->renderPartial('/userManagement/partial/permissionsuccess', ['results' => $results], true);
        return $this->renderPartial('/userManagement/partial/json', ["data" => [
            'success' => true,
            'html' => $html
        ]]);
    }

    /**
     * Displays a particular role.
     *
     * @param int $ptid
     * @return array|false|mixed|string|string[]|void|null
     * @throws CException
     */
    public function actionViewRole(int $ptid)
    {
        if (!Permission::model()->hasGlobalPermission('superadmin', 'read')) {
            Yii::app()->session['flashmessage'] = gT('You have no access to the role management!');
            $this->redirect(array('/admin'));
        }
        $oPermissionTemplate = Permissiontemplates::model()->findByPk($ptid);
        return $this->renderPartial(
            'partials/_view',
            [
                "oModel" => $oPermissionTemplate,
            ]
        );
    }

    /**
     * Creates an xml content/file to export. Opens dialog to save
     * the xml file.
     *
     * @param int $ptid
     */
    public function actionRunExport($ptid)
    {
        if (!Permission::model()->hasGlobalPermission('superadmin', 'read')) {
            Yii::app()->session['flashmessage'] = gT('You have no access to the role management!');
            $this->redirect(['/admin']);
        }
        $oModel = $this->loadModel($ptid);
        $oXML = $oModel->compileExportXML();
        $filename = preg_replace("/[^a-zA-Z0-9-_]*/", '', (string) $oModel->name);

        header('Content-type: application/xml');
        header('Content-Disposition: attachment; filename="' . $filename . '.xml"');
        print($oXML->asXML());
        Yii::app()->end();
    }

    /**
     * Deletes a particular model.
     * If deletion is successful, the browser will be redirected to the 'admin' page.
     *
     */
    public function actionDelete()
    {
        if (!Permission::model()->hasGlobalPermission('superadmin', 'read')) {
            Yii::app()->session['flashmessage'] = gT('You have no access to the role management!');
            $this->getController()->redirect(array('/admin'));
        }
        $ptid = Yii::app()->request->getPost('ptid', 0);
        try {
            $this->loadModel((int)$ptid)->delete();
            Yii::app()->setFlashMessage(gT("Role was successfully deleted."), 'success');
        } catch (Exception $e) {
            Yii::app()->setFlashMessage(gT("Role could not be deleted."), 'error');
        }

        // if AJAX request (triggered by deletion via admin grid view), we should not redirect the browser
        if (!isset($_GET['ajax'])) {
            $this->redirect($_POST['returnUrl'] ?? ['index']);
        }
    }

    /**
     * Opens modal to import role (xml-file).
     *
     * @throws CException
     */
    public function actionShowImportXML()
    {
        if (!Permission::model()->hasGlobalPermission('superadmin', 'read')) {
            Yii::app()->session['flashmessage'] = gT('You have no access to the role management!');
            $this->redirect(array('/admin'));
        }

        $this->renderPartial('partials/_import');
    }

    /**
     * Imports a role (and the permissions) from a xml-file.
     * Shows error message in case of
     *  - file to large
     *  - wrong file extension
     *  - error while parsing xml to db
     * Shows success message if role could be imported.
     * Redirects to index in any case.
     *
     */
    public function actionImportXML()
    {
        if (!Permission::model()->hasGlobalPermission('superadmin', 'read')) {
            throw new CHttpException(403, gT("You do not have permission to access this page."));
        }
        $sRandomFileName = randomChars(20);
        $sFilePath = Yii::app()->getConfig('tempdir') . DIRECTORY_SEPARATOR . $sRandomFileName;
        $aPathinfo = pathinfo((string) $_FILES['the_file']['name']);
        $sExtension = $aPathinfo['extension'];
        $bMoveFileResult = false;

        if ($_FILES['the_file']['error'] == 1 || $_FILES['the_file']['error'] == 2) {
            Yii::app()->setFlashMessage(sprintf(gT("Sorry, this file is too large. Only files up to %01.2f MB are allowed."), getMaximumFileUploadSize() / 1024 / 1024), 'error');
            $this->redirect(array('userRole/index'));
            Yii::app()->end(); //todo: is this necessary? after redirect this line will never be reached?!?
        } elseif (strtolower($sExtension) !== 'xml') {
            Yii::app()->setFlashMessage(gT("This is not a .xml file.") . 'It is a ' . $sExtension, 'error');
            $this->redirect(array('userRole/index'));
            Yii::app()->end();
        } else {
            $bMoveFileResult = @move_uploaded_file($_FILES['the_file']['tmp_name'], $sFilePath);
        }

        if ($bMoveFileResult === false) {
            Yii::app()->setFlashMessage(gT("An error occurred uploading your file. This may be caused by incorrect permissions for the application /tmp folder."), 'error');
            $this->redirect(array('userRole/index'));
            Yii::app()->end();
            return;
        }

        if (\PHP_VERSION_ID < 80000) {
            libxml_disable_entity_loader(false);
        }

        $oRoleDefinition = simplexml_load_file(realpath($sFilePath));
        if (\PHP_VERSION_ID < 80000) {
            libxml_disable_entity_loader(true);
        }

        $oNewRole = Permissiontemplates::model()->createFromXML($oRoleDefinition);
        if ($oNewRole == false) {
            Yii::app()->setFlashMessage(gT("Error creating role"), 'error');
            Yii::app()->getController()->redirect(array('userRole/index'));
            Yii::app()->end();
            return;
        }

        $applyPermissions = $this->applyPermissionFromXML($oNewRole->ptid, $oRoleDefinition->permissions);

        Yii::app()->setFlashMessage(gT("Role was successfully imported."), 'success');
        Yii::app()->getController()->redirect(array('userRole/index'));
        Yii::app()->end();
    }

    /**
     * Batch Delete
     * Massive action for deleting multiple roles at once.
     *
     * Renders a modal with deleting results for all roles that should be deleted.
     *
     * @throws CDbException
     * @throws CException
     */
    public function actionBatchDelete()
    {
        if (!Permission::model()->hasGlobalPermission('superadmin', 'read')) {
            Yii::app()->session['flashmessage'] = gT('You have no access to the role management!');
            $this->redirect(array('/admin'));
        }
        $sPtids = Yii::app()->request->getPost('sItems', []);
        $aPtids = json_decode((string) $sPtids, true);
        $aResults = [];
        foreach ($aPtids as $ptid) {
            $model = $this->loadModel($ptid);
            $aResults[$ptid]['title'] = $model->name;
            $aResults[$ptid]['result'] = $model->delete();
        }

        $tableLabels = array(gT('Role ID'), gT('Name'), gT('Status'));

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
     * Batch Export
     * Massive action to export multiple roles.
     *
     * Redirects to index.
     */
    public function actionBatchExport()
    {
        if (!Permission::model()->hasGlobalPermission('superadmin', 'read')) {
            Yii::app()->session['flashmessage'] = gT('You have no access to the role management!');
            $this->redirect(array('/admin'));
        }
        $sPtids = Yii::app()->request->getParam('sItems', '');
        $aPtids = explode(',', (string) $sPtids);
        $sRandomFileName = "RoleExport-" . randomChars(5) . '-' . time();

        $tempdir = Yii::app()->getConfig('tempdir');
        $zipfile = "$tempdir/$sRandomFileName.zip";
        $zip = new LimeSurvey\Zip();
        $zip->open($zipfile, ZipArchive::CREATE);

        foreach ($aPtids as $iPtid) {
            $oModel = $this->loadModel($iPtid);
            $oXML = $oModel->compileExportXML();
            $filename = preg_replace("/[^a-zA-Z0-9-_]*/", '', (string) $oModel->name) . '.xml';

            $zip->addFromString($filename, $oXML->asXML());
        }

        $zip->close();

        if (is_file($zipfile)) {
            // Send the file for download!
            header("Expires: 0");
            header("Cache-Control: must-revalidate");
            header("Content-Type: application/force-download");
            header("Content-Disposition: attachment; filename=$sRandomFileName.zip");
            header("Content-Description: File Transfer");

            @readfile($zipfile);

            // Delete the temporary file
            unlink($zipfile);
            return;
        }

        $this->redirect('index');
    }

    /**                                       THIS FUNCTIONS DO NOT BELONG TO CONTROLLERS                         ** */

    /**
     * Apply Permission from XML.
     *
     * @param int   $iRoleId           Role ID
     * @param array $oPermissionObject Permission
     * @return array
     */
    private function applyPermissionFromXML($iRoleId, $oPermissionObject)
    {
        $oCriteria = new CDbCriteria();
        $oCriteria->compare('entity_id', $iRoleId);
        $oCriteria->compare('entity', 'role');
        //Kill all Permissions of that role.
        $aPermissionsCurrently = Permission::model()->deleteAll($oCriteria); //todo: why delete here???
        $results = [];
        //Apply the permission array
        $aCleanPermissionObject = json_decode(json_encode($oPermissionObject), true);
        foreach ($aCleanPermissionObject as $sPermissionKey => $aPermissionSettings) {
            $oPermission = new Permission();
            $oPermission->entity = 'role';
            $oPermission->entity_id = $iRoleId;
            $oPermission->uid = 0;
            $oPermission->permission = $sPermissionKey;

            foreach ($aPermissionSettings as $sSettingKey => $sSettingValue) {
                $oPermissionDBSettingKey = $sSettingKey . '_p';
                if (isset($oPermission->$oPermissionDBSettingKey)) {
                    $oPermission->$oPermissionDBSettingKey = $sSettingValue;
                }
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
     * Returns the data model based on the id. If no entry exists with the given id, then a
     * new model for Permissiontemplates is returned.
     *
     * @param integer|null $ptid the ID of the model to be loaded
     * @return Permissiontemplates the loaded model
     */
    private function loadModel($ptid): Permissiontemplates
    {
        $model = Permissiontemplates::model()->findByPk($ptid);
        if ($model === null) {
            $model = new Permissiontemplates();
        }

        return $model;
    }

    /**
     * Returns HTML fragment of errors
     *
     * @param array $errors
     *
     * @return string $errorDiv
     */
    private function renderErrors(array $errors): string
    {
        $errorDiv = '<ul class="list-unstyled">';
        foreach ($errors as $key => $error) {
            foreach ($error as $errormessages) {
                $errorDiv .= '<li>' . print_r($errormessages, true) . '</li>';
            }
        }
        $errorDiv .= '</ul>';
        return $errorDiv;
    }

    /**
     * Adds permission to a role
     * Needs an array in the form of [PERMISSIONID][PERMISSION]
     *
     * @param int $iRoleId
     * @param array $aPermissionArray
     * @return array
     */
    private function applyPermissionFromArray($iRoleId, $aPermissionArray)
    {
        $oCriteria = new CDbCriteria();
        $oCriteria->compare('entity_id', $iRoleId);
        $oCriteria->compare('entity', 'role');
        //Kill all Permissions of that role.
        $aPermissionsCurrently = Permission::model()->deleteAll($oCriteria);
        $results = [];
        //Apply the permission array
        foreach ($aPermissionArray as $sPermissionKey => $aPermissionSettings) {
            $oPermission = new Permission();
            $oPermission->entity = 'role';
            $oPermission->entity_id = $iRoleId;
            $oPermission->uid = 0;
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
}
