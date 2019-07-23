<?php

class PermissiontemplatesController extends Survey_Common_Action
{

    /**
     * Lists all models.
     */
    public function index()
    {
        if(!Permission::model()->hasGlobalPermission('superadmin', 'read')) {
            Yii::app()->session['flashmessage'] = gT('You have no access to the role management!');
            $this->getController()->redirect(array('/admin'));
        }

        Yii::app()->getClientScript()->registerPackage('permissionroles');

        $model = Permissiontemplates::model();
        $this->_renderWrappedTemplate(
            null, 
            'permissiontemplates/index', 
            array(
                'model' => $model,
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

    public function applyedit() {
        $aPermissiontemplate = Yii::app()->request->getPost('Permissiontemplates');
        $model = $this->loadModel($aPermissiontemplate['ptid']); 
        
        $newAttributes = array_merge($model->attributes, $aPermissiontemplate);
        $model->attributes = $newAttributes;

        if ($model->save()) {
            return Yii::app()->getController()->renderPartial('/admin/super/_renderJson', [
                "data"=>[
                'success' => true,
                'html' => Yii::app()->getController()->renderPartial(
                    '/admin/permissiontemplates/partials/success', 
                    ['sMessage' => gT('Role successfully saved') ], 
                    true
                )
            ]]);
            return;
        }

        return Yii::app()->getController()->renderPartial('/admin/super/_renderJson', [
            "data"=>[
            'success' => false,
            'html' => Yii::app()->getController()->renderPartial(
                '/admin/permissiontemplates/partials/error',
                [
                    'sMessage' => gT('Failed saving the role'),
                    'errors' => $model->getErrors()
                ], 
                true
            )
        ]]);

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

        return Yii::app()->getController()->renderPartial('/admin/usermanagement/partial/json', ["data"=>[
            'success' => true,
            'html' => $this->getController()->renderPartial('/admin/usermanagement/partial/permissionsuccess', ['results' => $results], true)
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

    /**
     * Deletes a particular model.
     * If deletion is successful, the browser will be redirected to the 'admin' page.
     * @param integer $ptid the ID of the model to be deleted
     */
    public function delete($ptid)
    {
        if(!Permission::model()->hasGlobalPermission('superadmin', 'read')) {
            Yii::app()->session['flashmessage'] = gT('You have no access to the role management!');
            $this->getController()->redirect(array('/admin'));
        }
        $this->loadModel($ptid)->delete();

        // if AJAX request (triggered by deletion via admin grid view), we should not redirect the browser
        if (!isset($_GET['ajax'])) {
            $this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('admin'));
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

            $results[$sPermissionKey] = [
                'success' => $oPermission->save(),
                'storedValue' => $oPermission->attributes
            ];
        }
        return $results;
    }

}
