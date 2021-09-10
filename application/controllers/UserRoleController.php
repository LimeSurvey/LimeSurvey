<?php

class UserRoleController extends LSBaseController
{

    /**
     * Renders the list of user roles.
     *
     * @throws CException
     */
    public function actionIndex()
    {
        if (!Permission::model()->hasGlobalPermission('superadmin', 'read')) {
            App()->session['flashmessage'] = gT('You have no access to the role management!');
            $this->getController()->redirect(array('/admin'));
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
        $aPermissiontemplatesParam = $request->getParam('Permissiontemplates');
        if ($aPermissiontemplatesParam) {
            $model->setAttributes($aPermissiontemplatesParam, false);
        }

        // Green Bar (SurveyManagerBar) Page Title
        $aData['pageTitle'] = gT('User roles');

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
     * @param null $ptid
     * @throws CException
     */
    public function actionEditRoleModal($ptid = null)
    {
        if (!Permission::model()->hasGlobalPermission('superadmin', 'read')) {
            Yii::app()->session['flashmessage'] = gT('You have no access to the role management!');
            $this->getController()->redirect(array('/admin'));
        }

        $model = $this->loadModel($ptid);
        $this->renderPartial('partials/_form', ['model' => $model]);
    }

    /**
     * @return string|string[]|null
     * @throws CException
     * @throws CHttpException
     */
    public function actionApplyEdit()
    {
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

        /**
         * REFACTORED
         *
         * this part could never be reached at any time, because same if-clause above is already
         * returning with an error ...
         *
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
         */

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
        $ptid = $oRequest->getParam('ptid');
        $aPermissions = Yii::app()->request->getPost('Permission', []);
        $oPermissionTemplate = Permissiontemplates::model()->findByPk($ptid);
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

    /**                                       THIS FUNCTIONS DO NOT BELONG TO CONTROLLERS                         ** */

    /**
     * Returns the data model based on the id. If no entry exists with the given id, then a
     * new model for Permissiontemplates is returned.
     *
     * @param integer|null $ptid the ID of the model to be loaded
     * @return Permissiontemplates the loaded model
     */
    private function loadModel($ptid):Permissiontemplates
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
    private function renderErrors(array $errors):string
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
