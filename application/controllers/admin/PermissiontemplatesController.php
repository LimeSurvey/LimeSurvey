<?php

class PermissiontemplatesController extends Survey_Common_Action
{

    /**
     * Lists all models.
     */
    public function index()
    {
        if (!Permission::model()->hasGlobalPermission('superadmin', 'read')) {
            App()->session['flashmessage'] = gT('You have no access to the role management!');
            $this->getController()->redirect(array('/admin'));
        }

        App()->getClientScript()->registerPackage('permissionroles');
        $request = App()->request;

        $massiveAction = App()->getController()->renderPartial(
            '/admin/permissiontemplates/massiveAction/_selector',
            [],
            true,
            false
        );

        // Set page size
        $pageSize = $request->getParam('pageSize', null);
        if ($pageSize != null) {
            App()->user->setState('pageSize', (int)$pageSize);
        }

        $model = Permissiontemplates::model();
        $aPermissiontemplatesParam = $request->getParam('Permissiontemplates');
        if ($aPermissiontemplatesParam) {
            $model->setAttributes($aPermissiontemplatesParam, false);
        }
        $this->_renderWrappedTemplate(
            null,
            'permissiontemplates/index',
            array(
                'model'         => $model,
                'massiveAction' => $massiveAction
            )
        );
    }
    /**
     * Displays a particular model.
     * @param integer $ptid the ID of the model to be displayed
     */
    public function viewrole($ptid)
    {
        if(!Permission::model()->hasGlobalPermission('superadmin', 'read')) {
            Yii::app()->session['flashmessage'] = gT('You have no access to the role management!');
            $this->getController()->redirect(array('/admin'));
        }
        $oPermissionTemplate = Permissiontemplates::model()->findByPk($ptid);
        return $this->getController()->renderPartial(
            '/admin/permissiontemplates/partials/_view', 
            [
                "oModel" => $oPermissionTemplate, 
            ]
        );
    }

    public function editrolemodal($ptid=null)
    {
        if(!Permission::model()->hasGlobalPermission('superadmin', 'read')) {
            Yii::app()->session['flashmessage'] = gT('You have no access to the role management!');
            $this->getController()->redirect(array('/admin'));
        }

        $model = $this->loadModel($ptid);
        Yii::app()->getController()->renderPartial( 'permissiontemplates/partials/_form', ['model' => $model]);
    }

    /**
     * @return string|string[]|null
     * @throws CException
     * @throws CHttpException
     */
    public function applyedit()
    {
        $aPermissiontemplate = Yii::app()->request->getPost('Permissiontemplates');
        $model = $this->loadModel($aPermissiontemplate['ptid']);

        // XSS filter
        $aPermissiontemplate['name'] = CHtml::encode($aPermissiontemplate['name']);
        $aPermissiontemplate['description'] = CHtml::encode($aPermissiontemplate['description']);

        $newAttributes = array_merge($model->attributes, $aPermissiontemplate);
        $model->attributes = $newAttributes;

        if ($model->save()) {
            $success = true;
            $message = gT('Role successfully saved');
        } else {
            $success = false;
            $message = gT('Failed saving the role');
            $errors = $model->getErrors();

            $errorDiv = $this->renderErrors($errors);
        }
        return App()->getController()->renderPartial('/admin/super/_renderJson', [
            "data" => [
                'success' => $success,
                'message' => $message,
                'errors'  => $errorDiv ?? ''
            ]
        ]);
    }

    /**
     * @param array $errors
     *
     * @return string $errorDiv
     */
    private function renderErrors($errors)
    {
        $errorDiv = '<ul class="list-unstyled">';
        foreach ($errors as $key => $error) {
            foreach ($error as $errormessages) {
                $errorDiv .= '<li>' . print_r($errormessages, true) . '</li>';
            }
        }
        $errorDiv .= '</ul>';
        return (string)$errorDiv;
    }

    public function showImportXML() {
        if(!Permission::model()->hasGlobalPermission('superadmin', 'read')) {
            Yii::app()->session['flashmessage'] = gT('You have no access to the role management!');
            $this->getController()->redirect(array('/admin'));
        }

        Yii::app()->getController()->renderPartial( 'permissiontemplates/partials/_import', []);
    }

    public function importXML() {
        
        $sRandomFileName = randomChars(20);
        $sFilePath = Yii::app()->getConfig('tempdir').DIRECTORY_SEPARATOR.$sRandomFileName;
        $aPathinfo = pathinfo($_FILES['the_file']['name']);
        $sExtension = $aPathinfo['extension'];
        $bMoveFileResult = false;
        
 
        if ($_FILES['the_file']['error'] == 1 || $_FILES['the_file']['error'] == 2) {
            Yii::app()->setFlashMessage(sprintf(gT("Sorry, this file is too large. Only files up to %01.2f MB are allowed."), getMaximumFileUploadSize() / 1024 / 1024), 'error');
            Yii::app()->getController()->redirect(array('/admin/roles'));
            Yii::app()->end();
        } elseif (strtolower($sExtension) == 'xml' ||1==1) {
            $bMoveFileResult = @move_uploaded_file($_FILES['the_file']['tmp_name'], $sFilePath);
        } else {
            Yii::app()->setFlashMessage(gT("This is not a .xml file."). 'It is a '.$sExtension, 'error');
            Yii::app()->getController()->redirect(array('/admin/roles'));
            Yii::app()->end();
        }

        if ($bMoveFileResult === false) {
            Yii::app()->setFlashMessage(gT("An error occurred uploading your file. This may be caused by incorrect permissions for the application /tmp folder."), 'error');
            Yii::app()->getController()->redirect(array('/admin/roles'));
            Yii::app()->end();
            return;
        }

        libxml_disable_entity_loader(false);
        $oRoleDefinition = simplexml_load_file(realpath($sFilePath));
        libxml_disable_entity_loader(true);
        
        $oNewRole = Permissiontemplates::model()->createFromXML($oRoleDefinition);
        if($oNewRole == false ) {

            Yii::app()->setFlashMessage(gT("Error creating role"), 'error');
            Yii::app()->getController()->redirect(array('/admin/roles'));
            Yii::app()->end();
            return;
        }

        $applyPermissions = $this->applyPermissionFromXML($oNewRole->ptid, $oRoleDefinition->permissions);
        
        Yii::app()->setFlashMessage(gT("Role was successfully imported."), 'success');
        Yii::app()->getController()->redirect(array('/admin/roles'));
        Yii::app()->end();
        return;

    }

    public function setpermissions() {
        if(!Permission::model()->hasGlobalPermission('superadmin', 'read')) {
            return $this->getController()->renderPartial(
                '/admin/permissiontemplates/partial/error',
                ['errors' => [gT("You do not have permission to access this page.")],'noButton' => true]
          );
        }

        $oRequest = Yii::app()->request;
        $ptid = $oRequest->getParam('ptid');
        $oPermissionTemplate = Permissiontemplates::model()->findByPk($ptid);

        // Check permissions
        $aBasePermissions = Permission::model()->getGlobalBasePermissions();
        if (!Permission::model()->hasGlobalPermission('superadmin', 'read')) {
            // if not superadmin filter the available permissions as no admin may give more permissions than he owns
            Yii::app()->session['flashmessage'] = gT("Note: You can only give limited permissions to other users because your own permissions are limited, too.");
            $aFilteredPermissions = array();
            foreach ($aBasePermissions as $PermissionName=>$aPermission) {
                foreach ($aPermission as $sPermissionKey=>&$sPermissionValue) {
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
                if ($oPermission->permission=='surveysecurity' && $oPermission->update_p == 1 && $oPermission->uid == App()->user->id) {
                    return true;
                }
                return $coll;
            }, false);
        });

        return $this->getController()->renderPartial(
            '/admin/permissiontemplates/partials/_permissions', 
            [
                "oModel" => $oPermissionTemplate, 
                "aBasePermissions" => $aBasePermissions
            ]
        );
    }

    public function savepermissions()
    {
        if(!Permission::model()->hasGlobalPermission('superadmin', 'read')) {
            return $this->getController()->renderPartial(
                '/admin/permissiontemplates/partial/error', 
                ['errors' => [gT("You do not have permission to access this page.")],'noButton' => true]
          );
        }

        $oRequest = Yii::app()->request;
        $ptid = $oRequest->getParam('ptid');
        $aPermissions = Yii::app()->request->getPost('Permission',[]);
        $oPermissionTemplate = Permissiontemplates::model()->findByPk($ptid);
        $results = $this->applyPermissionFromArray($ptid, $aPermissions);
        
        $oPermissionTemplate->renewed_last = date('Y-m-d H:i:s');
        $save = $oPermissionTemplate->save();
        
        $html = $this->getController()->renderPartial('/userManagement/partial/permissionsuccess', ['results' => $results], true);
        return Yii::app()->getController()->renderPartial('/userManagement/partial/json', ["data"=>[
            'success' => true,
            'html' => $html
        ]]);
    }

    /**
     * Creates a new model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     */
    public function edit($ptid=null)
    {
        if(!Permission::model()->hasGlobalPermission('superadmin', 'read')) {
            Yii::app()->session['flashmessage'] = gT('You have no access to the role management!');
            $this->getController()->redirect(array('/admin'));
        }
        $model = $this->loadModel($ptid);

        // Uncomment the following line if AJAX validation is needed
        // $this->performAjaxValidation($model);

        if (isset($_POST['Permissiontemplates'])) {
            $model->attributes = $_POST['Permissiontemplates'];
            if ($model->save()) {
                $this->redirect(array('view', 'id' => $model->id));
            }

        }

        $this->_renderWrappedTemplate(
            null, 
            'permissiontemplates/edit', 
            array(
                'model' => $model,
            )
        );
    }

    public function batchDelete() 
    {
        if(!Permission::model()->hasGlobalPermission('superadmin', 'read')) {
            Yii::app()->session['flashmessage'] = gT('You have no access to the role management!');
            $this->getController()->redirect(array('/admin'));
        }
        $sPtids = Yii::app()->request->getPost('sItems', []);
        $aPtids = json_decode($sPtids, true);
        $success = [];
        foreach ($aPtids as $ptid) {
            $success[$ptid] = $this->loadModel($ptid)->delete();
        }

        $this->getController()->renderPartial(
            '/userManagement/partial/success',
            [
                'sMessage' => gT('Roles successfully deleted'), 
                'sDebug' => json_encode($success, JSON_PRETTY_PRINT), 
                'noButton' => true
            ]
        );

    }

    /**
     * Deletes a particular model.
     * If deletion is successful, the browser will be redirected to the 'admin' page.
     * @param integer $ptid the ID of the model to be deleted
     */
    public function delete()
    {
        if(!Permission::model()->hasGlobalPermission('superadmin', 'read')) {
            Yii::app()->session['flashmessage'] = gT('You have no access to the role management!');
            $this->getController()->redirect(array('/admin'));
        }
        $ptid = Yii::app()->request->getPost('ptid', 0);
        $this->loadModel($ptid)->delete();

        // if AJAX request (triggered by deletion via admin grid view), we should not redirect the browser
        if (!isset($_GET['ajax'])) {
            $this->getController()->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('/admin/roles'));
        }

    }

    public function runexport($ptid) {
        $oModel = $this->loadModel($ptid);
        $oXML = $oModel->compileExportXML();
        $filename = preg_replace("/[^a-zA-Z0-9-_]*/",'',$oModel->name);

        header('Content-type: application/xml');
        header('Content-Disposition: attachment; filename="'.$filename.'.xml"');
        print($oXML->asXML());
        Yii::app()->end();
    }
    
    public function batchExport() {
        if(!Permission::model()->hasGlobalPermission('superadmin', 'read')) {
            Yii::app()->session['flashmessage'] = gT('You have no access to the role management!');
            $this->getController()->redirect(array('/admin'));
        }
        $sPtids = Yii::app()->request->getParam('sItems', '');
        $aPtids = explode(',',$sPtids);
        $sRandomFolderName = randomChars(20);
        $sRandomFileName = "RoleExport-".randomChars(5).'-'.time();
        
        $tempdir = Yii::app()->getConfig('tempdir');
        $zipfile = "$tempdir/$sRandomFileName.zip";
        Yii::app()->loadLibrary('admin.pclzip');

        $zip = new PclZip($zipfile);
        $sFilePath = $tempdir.DIRECTORY_SEPARATOR.$sRandomFolderName;      
        
        mkdir($sFilePath);
        $filesInArchive = [];
        
        foreach ($aPtids as $iPtid) {
            $oModel = $this->loadModel($iPtid);
            $oXML = $oModel->compileExportXML();
            $filename = preg_replace("/[^a-zA-Z0-9-_]*/",'',$oModel->name).'.xml';

            file_put_contents($sFilePath.DIRECTORY_SEPARATOR.$filename, $oXML->asXML());
            $filesInArchive[] = $sFilePath.DIRECTORY_SEPARATOR.$filename;
        }

        $zip->create($filesInArchive, PCLZIP_OPT_REMOVE_ALL_PATH);

        if (is_file($zipfile)) {
            // Send the file for download!
            header("Expires: 0");
            header("Cache-Control: must-revalidate");
            header("Content-Type: application/force-download");
            header("Content-Disposition: attachment; filename=$sRandomFileName.zip");
            header("Content-Description: File Transfer");

            @readfile($zipfile);

            // Delete the temporary file
            array_map('unlink', glob("$sFilePath/*.*"));
            rmdir($sFilePath);
            unlink($zipfile);
            return;
        }

        $this->getController()->redirect('/admin/roles');
    }

    /**
     * Returns the data model based on the primary key given in the GET variable.
     * If the data model is not found, an HTTP exception will be raised.
     * @param integer $ptid the ID of the model to be loaded
     * @return Permissiontemplates the loaded model
     * @throws CHttpException
     */
    public function loadModel($ptid)
    {
        $model = Permissiontemplates::model()->findByPk($ptid);
        if ($model === null) {
            $model = new Permissiontemplates();
        }

        return $model;
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
        foreach($aPermissionArray as $sPermissionKey => $aPermissionSettings) {
            $oPermission = new Permission();
            $oPermission->entity = 'role';
            $oPermission->entity_id = $iRoleId;
            $oPermission->uid = 0;
            $oPermission->permission = $sPermissionKey;

            foreach($aPermissionSettings as $sSettingKey => $sSettingValue) {
                $oPermissionDBSettingKey = $sSettingKey.'_p';
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

    private function applyPermissionFromXML($iRoleId, $oPermissionObject)
    {
        $oCriteria = new CDbCriteria();
        $oCriteria->compare('entity_id', $iRoleId);
        $oCriteria->compare('entity', 'role');
        //Kill all Permissions of that role.
        $aPermissionsCurrently = Permission::model()->deleteAll($oCriteria);
        $results = [];
        //Apply the permission array
        $aCleanPermissionObject = json_decode(json_encode($oPermissionObject), true);
        foreach($aCleanPermissionObject as $sPermissionKey => $aPermissionSettings) {
            $oPermission = new Permission();
            $oPermission->entity = 'role';
            $oPermission->entity_id = $iRoleId;
            $oPermission->uid = 0;
            $oPermission->permission = $sPermissionKey;

            foreach($aPermissionSettings as $sSettingKey => $sSettingValue) {
                $oPermissionDBSettingKey = $sSettingKey.'_p';
                if(isset($oPermission->$oPermissionDBSettingKey)) {
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

}
