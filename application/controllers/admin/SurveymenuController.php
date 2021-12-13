<?php

/**
 * Class SurveymenuController
 */
class SurveymenuController extends SurveyCommonAction
{
    /**
     * @return string[] action filters
     */
    public function filters()
    {
        return array(
            'accessControl', // perform access control for CRUD operations
            'postOnly + delete', // we only allow deletion via POST request
        );
    }

    /**
     * Specifies the access control rules.
     * This method is used by the 'accessControl' filter.
     * @return array access control rules
     */
    public function accessRules()
    {
        return array(
            array('allow', // allow all users to perform 'index' and 'view' actions
                'actions' => array('index', 'view'),
                'users' => array('*'),
            ),
            array('allow', // allow authenticated user to perform 'create' and 'update' actions
                'actions' => array('create', 'update'),
                'users' => array('@'),
            ),
            array('allow', // allow admin user to perform 'admin' and 'delete' actions
                'actions' => array('admin', 'delete'),
                'users' => array('admin'),
            ),
            array('deny', // deny all users
                'users' => array('*'),
            ),
        );
    }

    /**
     * Updates a particular model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id the ID of the model to be updated
     */
    public function update($id = 0)
    {
        if (!Permission::model()->hasGlobalPermission('settings', 'update')) {
            Yii::app()->user->setFlash('error', gT("Access denied"));
            $this->getController()->redirect(Yii::app()->createUrl('/admin'));
        }

        if ($id != 0) {
                    $model = $this->loadModel($id);
        } else {
                    $model = new Surveymenu();
        }
        // Uncomment the following line if AJAX validation is needed
        // $this->performAjaxValidation($model);

        $success = false;
        if (Yii::app()->request->isPostRequest) {
            $aSurveymenu = Yii::app()->request->getPost('Surveymenu', []);
            // Sanitize title and description to prevent XSS attack
            if (isset($aSurveymenu['title'])) {
                $aSurveymenu['title'] = flattenText($aSurveymenu['title'], false, true);
            }
            if (isset($aSurveymenu['description'])) {
                $aSurveymenu['description'] = flattenText($aSurveymenu['description'], false, true);
            }
            if ($aSurveymenu['id'] == '') {
                unset($aSurveymenu['id']);
                $aSurveymenu['created_at'] = date('Y-m-d H:i:s');
                $aSurveymenu['parent_id'] = (int) $aSurveymenu['parent_id'];
                if ($aSurveymenu['parent_id'] > 0) {
                    $aSurveymenu['level'] = ((Surveymenu::model()->findByPk($aSurveymenu['parent_id'])->level) + 1);
                }
            }

            $model->setAttributes($aSurveymenu);
            if ($model->save()) {
                $model->id = $model->getPrimaryKey();
                $success = true;
            }
        }

        $debug = App()->getConfig('debug');
        $returnData = array(
            'data' => [
                'success' => $success,
                'redirect' => $this->getController()->createUrl('admin/menus/sa/view'),
                'settings' => array(
                    'extrasettings' => false,
                    'parseHTML' => false,
                ),
                'message' =>  ($success ? gT("Default survey menus restored.") : gT("Something went wrong!"))
            ]
        );

        if ($debug > 0) {
            $returnData['data']['debug'] = [$model, $_POST];
            $returnData['data']['debugErrors'] = $model->getErrors();
        }

        return Yii::app()->getController()->renderPartial(
            '/admin/super/_renderJson',
            $returnData,
            false,
            false
        );
    }

    /**
     * Batch Edit.
     **/
    public function batchEdit()
    {
        if (!Permission::model()->hasGlobalPermission('settings', 'update')) {
            Yii::app()->user->setFlash('error', gT("Access denied"));
            $this->getController()->redirect(Yii::app()->createUrl('/admin'));
        }

        $aSurveyMenuIds = json_decode(Yii::app()->request->getPost('sItems'));
        $aResults = array();
        $oBaseModel = Surveymenu::model();
        if (Permission::model()->hasGlobalPermission('settings', 'update')) {
            // First we create the array of fields to update
            $aData = array();
            $aResults['global']['result'] = true;

            // Core Fields
            $aCoreTokenFields = array('position', 'parent', 'survey', 'user');

            foreach ($aCoreTokenFields as $sCoreTokenField) {
                if (trim(Yii::app()->request->getPost($sCoreTokenField, 'lskeep')) != 'lskeep') {
                    $aData[$sCoreTokenField] = flattenText(Yii::app()->request->getPost($sCoreTokenField));
                }
            }

            if (count($aData) > 0) {
                foreach ($aSurveyMenuIds as $iSurveyMenuId) {
                    $iSurveyMenuId = (int) $iSurveyMenuId;
                    $oSurveyMenu = Surveymenu::model()->findByPk($iSurveyMenuId);

                    foreach ($aData as $k => $v) {
                        $oSurveyMenu->$k = $v;
                    }

                    $bUpdateSuccess = $oSurveyMenu->update();
                    if ($bUpdateSuccess) {
                        $aResults[$iSurveyMenuId]['status']    = true;
                        $aResults[$iSurveyMenuId]['message']   = gT('Updated');
                    } else {
                        $aResults[$iSurveyMenuId]['status']    = false;
                        $aResults[$iSurveyMenuId]['message']   = $iSurveyMenuId->error;
                    }
                }
            } else {
                $aResults['global']['result']  = false;
                $aResults['global']['message'] = gT('Nothing to update');
            }
        } else {
            $aResults['global']['result'] = false;
            $aResults['global']['message'] = gT("We are sorry but you don't have permissions to do this.");
        }


        Yii::app()->getController()->renderPartial('/admin/surveymenu/massive_action/_update_results', array('aResults' => $aResults));
    }

    /**
     * Deletes an array of models.
     */
    public function massDelete()
    {
        if (!Permission::model()->hasGlobalPermission('settings', 'delete')) {
            Yii::app()->user->setFlash('error', gT("Access denied"));
            $this->getController()->redirect(Yii::app()->createUrl('/admin'));
        }

        if (Yii::app()->request->isPostRequest) {
            $aSurveyMenuIds = json_decode(Yii::app()->request->getPost('sItems'));
            $success = [];
            foreach ($aSurveyMenuIds as $menuid) {
                $model = $this->loadModel($menuid);
                $success[$menuid] = $model->delete();
            }

            $debug = isset($userConfig['config']['debug']) ? $userConfig['config']['debug'] : 0;
            $returnData = array(
                'data' => [
                    'success' => $success,
                    'redirect' => $this->getController()->createUrl('admin/menus/sa/view'),
                    'settings' => array(
                        'extrasettings' => false,
                        'parseHTML' => false,
                    )
                ]
            );

            if ($debug > 0) {
                $returnData['data']['debug'] = [$model, $_POST];
                $returnData['data']['debugErrors'] = $model->getErrors();
            }

            return Yii::app()->getController()->renderPartial(
                '/admin/super/_renderJson',
                $returnData,
                false,
                false
            );
        }
    }

    /**
     * Deletes a particular model.
     * If deletion is successful, the browser will be redirected to the 'admin' page.
     */
    public function delete()
    {
        if (!Permission::model()->hasGlobalPermission('settings', 'delete')) {
            Yii::app()->user->setFlash('error', gT("Access denied"));
            $this->getController()->redirect(Yii::app()->createUrl('/admin'));
        }

        if (Yii::app()->request->isPostRequest) {
            $menuid = Yii::app()->request->getPost('menuid', 0);
            $success = false;
            $model = $this->loadModel($menuid);
            $success = $model->delete();
            $debug = isset($userConfig['config']['debug']) ? $userConfig['config']['debug'] : 0;
            $returnData = array(
                'data' => [
                    'success' => $success,
                    'redirect' => $this->getController()->createUrl('admin/menus/sa/view'),
                    'settings' => array(
                        'extrasettings' => false,
                        'parseHTML' => false,
                    )
                ]
            );

            if ($debug > 0) {
                $returnData['data']['debug'] = [$model, $_POST];
                $returnData['data']['debugErrors'] = $model->getErrors();
            }

            return Yii::app()->getController()->renderPartial(
                '/admin/super/_renderJson',
                $returnData,
                false,
                false
            );
        }
    }

    /**
     * Restores the default surveymenus
     */
    public function restore()
    {
        if (!(Permission::model()->hasGlobalPermission('settings', 'delete') && Permission::model()->hasGlobalPermission('settings', 'update'))) {
            Yii::app()->user->setFlash('error', gT("Access denied"));
            $this->getController()->redirect(Yii::app()->createUrl('/admin'));
        }

        if (Yii::app()->request->isPostRequest) {
            //Check for permission!
            if (!Permission::model()->hasGlobalPermission('superadmin', 'read')) {
                $debug = isset($userConfig['config']['debug']) ? $userConfig['config']['debug'] : 0;
                $returnData = array(
                    'data' => [
                        'success' => $success,
                        'redirect' => false,
                        'settings' => array(
                            'extrasettings' => false,
                            'parseHTML' => false,
                        ),
                        'message' => gT("You don't have the right to restore the settings to default")
                    ]
                );

                if ($debug > 0) {
                    $returnData['data']['debug'] = [$model, $_POST];
                    $returnData['data']['debugErrors'] = $model->getErrors();
                }

                return Yii::app()->getController()->renderPartial(
                    '/admin/super/_renderJson',
                    $returnData,
                    false,
                    false
                );
            }
            //get model to do the work
            $model = Surveymenu::model();
            $success = $model->restoreDefaults();
            $debug = isset($userConfig['config']['debug']) ? $userConfig['config']['debug'] : 0;
            $returnData = array(
                'data' => [
                    'success' => $success,
                    'redirect' => false,
                    'settings' => array(
                        'extrasettings' => false,
                        'parseHTML' => false,
                    ),
                    'message' =>  ($success ? gT("Default survey menus restored.") : gT("Something went wrong!"))
                ]
            );

            if ($debug > 0) {
                $returnData['data']['debug'] = [$model, $_POST];
                $returnData['data']['debugErrors'] = $model->getErrors();
            }

            return Yii::app()->getController()->renderPartial(
                '/admin/super/_renderJson',
                $returnData,
                false,
                false
            );
        }
    }

    /**
     * Returns the data model based on the primary key given in the GET variable.
     * If the data model is not found, an HTTP exception will be raised.
     * @param integer $id the ID of the model to be loaded
     * @return Surveymenu the loaded model
     * @throws CHttpException
     */
    public function loadModel($id)
    {
        $model = Surveymenu::model()->findByPk($id);
        if ($model === null) {
                    throw new CHttpException(404, 'The requested page does not exist.');
        }
        return $model;
    }

    /**
     * Performs the AJAX validation.
     * @param Surveymenu $model the model to be validated
     */
    protected function performAjaxValidation($model)
    {
        if (isset($_POST['ajax']) && $_POST['ajax'] === 'surveymenu-form') {
            echo CActiveForm::validate($model);
            Yii::app()->end();
        }
    }

    /**
     * Index
     **/
    public function index()
    {
        $this->getController()->redirect(array('admin/menus/sa/view'));
    }

    /**
     * View.
     * @throws CHttpException
     */
    public function view()
    {
        $aData = array();
        $aData['model'] = Surveymenu::model();

        // Survey Menu Entries Data
        $filterAndSearch = Yii::app()->request->getPost('SurveymenuEntries', []);
        $aData['entries_model'] = SurveymenuEntries::model();
        $aData['entries_model']->setAttributes($filterAndSearch);

        if (Yii::app()->request->getParam('pageSize')) {
            Yii::app()->user->setState('pageSize', (int) Yii::app()->request->getParam('pageSize'));
        }
        $aData['pageSize'] = Yii::app()->user->getState('pageSize', (int) Yii::app()->params['defaultPageSize']);

        // Page Title Green Bar
        $aData['pageTitle'] = gT('Survey menus');

        // White Bar
        $aData['fullpagebar'] = [
            'menus' => [
                'buttons' => [
                    'addMenu' => true,
                    'addMenuEntry' => true,
                    'reset' => Permission::model()->hasGlobalPermission('superadmin', 'read'),
                    'reorder' => true,
                ],
            ],
            'returnbutton' => [
                'text' => gT('Back'),
                'url' => 'admin/index',
            ],
        ];

        App()->getClientScript()->registerPackage('surveymenufunctions');
        $this->renderWrappedTemplate(null, array('surveymenu/index'), $aData);
    }

    /**
     * Get SurveyMenuForm
     * @param $menuid
     **/
    public function getsurveymenuform($menuid = null)
    {
        $menuid = Yii::app()->request->getParam('menuid', null);
        if ($menuid != null) {
            $model = Surveymenu::model()->findByPk(((int) $menuid));
        } else {
            $model = new Surveymenu();
        }
        $user = Yii::app()->session['loginID'];
        return Yii::app()->getController()->renderPartial('/admin/surveymenu/_form', array('model' => $model, 'user' => $user));
    }
}
