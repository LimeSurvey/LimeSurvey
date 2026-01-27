<?php

/**
 * Class SurveymenuEntryController
 */
class SurveymenuEntryController extends SurveyCommonAction
{
    /**
     * SurveymenuEntryController constructor.
     * @param $controller
     * @param $id
     */
    public function __construct($controller, $id)
    {
        parent::__construct($controller, $id);

        if (!Permission::model()->hasGlobalPermission('settings', 'read')) {
            Yii::app()->setFlashMessage(gT("You do not have permission to access this page."), 'error');
            $this->getController()->redirect($this->getController()->createUrl("/admin/"));
        }
    }

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
     * Index
     * @access public
     * @return void
     */
    public function index()
    {
        $this->getController()->redirect(array('admin/menuentries/sa/view'));
    }

    /**
     * View
     * @throws CHttpException
     */
    public function view()
    {
        $data = array();
        $filterAndSearch = Yii::app()->request->getPost('SurveymenuEntries', []);
        $data['model'] = SurveymenuEntries::model();
        $data['model']->setAttributes($filterAndSearch);
        $data['user'] = Yii::app()->session['loginID'];

        if (Yii::app()->request->getParam('pageSize')) {
            Yii::app()->user->setState('pageSize', (int) Yii::app()->request->getParam('pageSize'));
        }
        $data['pageSize'] = Yii::app()->user->getState('pageSize', (int) Yii::app()->params['defaultPageSize']);
        $data['pageTitle'] = gT('Menu entries');
        $data['fullpagebar'] = [
            'menus' => [
                'buttons' => [
                    'addMenuEntry' => true,
                    'reset'        => true,
                    'reorder'      => true,
                ],
            ],
        ];
        App()->getClientScript()->registerPackage('surveymenufunctions');
        $this->renderWrappedTemplate(null, array('surveymenu_entries/index'), $data);
    }

    public function getsurveymenuentryform($menuentryid = null)
    {
        $menuentryid = Yii::app()->request->getParam('menuentryid', null);
        if ($menuentryid != null) {
            $model = SurveymenuEntries::model()->findByPk(((int) $menuentryid));
            if (empty($model)) {
                throw new CHttpException(404, gT("Invalid menu entry."));
            }
        } else {
            $model = new SurveymenuEntries();
        }
        $user = Yii::app()->session['loginID'];
        if (App()->request->getIsAjaxRequest()) {
            App()->getController()->renderPartial('/admin/surveymenu_entries/_form', array('model' => $model, 'user' => $user), false, false);
        } else {
            $this->renderWrappedTemplate(null, array('surveymenu_entries/_form'), array('model' => $model, 'user' => $user, 'ajax' => false));
        }
    }


    /**
     * Creates a new model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     */
    public function create()
    {
        $model = new SurveymenuEntries();

        // Uncomment the following line if AJAX validation is needed
        // $this->performAjaxValidation($model);

        if (isset($_POST['SurveymenuEntries'])) {
            $model->attributes = $_POST['SurveymenuEntries'];
            if ($model->save()) {
                $this->redirect(array('view', 'id' => $model->id));
            }
        }

        $this->render('create', array(
            'model' => $model,
        ));
    }

    /**
     * Updates a particular model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id the ID of the model to be updated
     */
    public function update($id)
    {
        if (!(Permission::model()->hasGlobalPermission('settings', 'update')) || Yii::app()->getConfig('demoMode')) {
            Yii::app()->user->setFlash('error', gT("Access denied!"));
            $this->getController()->redirect(Yii::app()->createUrl('/admin'));
        }
        //Update or create
        $id = intval($id);
        if ($id != 0) {
            $model = SurveymenuEntries::model()->findByPk($id);
        } else {
            $model = new SurveymenuEntries();
        }
        if (empty($model)) {
            throw new CHttpException(404, gT("Invalid menu entry."));
        }
        //Don't update  main menu entries when not superadmin
        if (($model->menu_id == 1 || $model->menu_id == 2) && !Permission::model()->hasGlobalPermission('superadmin', 'read')) {
            Yii::app()->user->setFlash('error', gT("Access denied!"));
            $this->getController()->redirect(Yii::app()->createUrl('/admin'));
        }

        $success = false;
        if (Yii::app()->request->isPostRequest) {
            $aSurveymenuEntry = Yii::app()->request->getPost('SurveymenuEntries', []);

            $aSurveymenuEntry['changed_at'] = date('Y-m-d H:i:s');
            $aSurveymenuEntry['created_at'] = date('Y-m-d H:i:s');
            $aSurveymenuEntry['menu_id'] = (int) $aSurveymenuEntry['menu_id'];
            $model->setAttributes($aSurveymenuEntry);
            if ($model->save()) {
                $model->id = $model->getPrimaryKey();
                $success = true;
                SurveymenuEntries::reorderMenu($model->menu_id);
            }
        }

        return Yii::app()->getController()->renderPartial(
            '/admin/super/_renderJson',
            array(
                'data' => [
                    'success' => $success,
                    'redirect' => $this->getController()->createUrl('admin/menus/sa/view'),
                    'debug' => [$model, $aSurveymenuEntry, $_POST],
                    'debugErrors' => $model->getErrors(),
                    'settings' => array(
                        'extrasettings' => false,
                        'parseHTML' => false,
                    )
                ]
            ),
            false,
            false
        );
    }

    public function batchEdit()
    {
        $aSurveyMenuEntryIds = json_decode(Yii::app()->request->getPost('sItems', '')) ?? [];
        $aResults = array();
        $oBaseModel = SurveymenuEntries::model();
        if (Permission::model()->hasGlobalPermission('settings', 'update')) {
            // First we create the array of fields to update
            $aData = array();
            $aResults['global']['result'] = true;

            // Core Fields
            $aCoreTokenFields = array('menu_id', 'menu_class', 'permission', 'permission_grade', 'language');

            foreach ($aCoreTokenFields as $sCoreTokenField) {
                if (trim((string) Yii::app()->request->getPost($sCoreTokenField, 'lskeep')) != 'lskeep') {
                    $aData[$sCoreTokenField] = flattenText(Yii::app()->request->getPost($sCoreTokenField));
                }
            }

            if (count($aData) > 0) {
                foreach ($aSurveyMenuEntryIds as $iSurveyMenuEntryId) {
                    $iSurveyMenuEntryId = (int) $iSurveyMenuEntryId;
                    $oSurveyMenuEntry = SurveymenuEntries::model()->findByPk($iSurveyMenuEntryId);

                    foreach ($aData as $k => $v) {
                        $oSurveyMenuEntry->$k = $v;
                    }

                    $bUpdateSuccess = $oSurveyMenuEntry->update();
                    if ($bUpdateSuccess) {
                        $aResults[$iSurveyMenuEntryId]['status']    = true;
                        $aResults[$iSurveyMenuEntryId]['message']   = gT('Updated');
                    } else {
                        $aResults[$iSurveyMenuEntryId]['status']    = false;
                        $aResults[$iSurveyMenuEntryId]['message']   = $oSurveyMenuEntry->error;
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

        $oBaseModel->reorder();

        Yii::app()->getController()->renderPartial('/admin/surveymenu_entries/massive_action/_update_results', array('aResults' => $aResults));
    }
    /**
     * Restores the default surveymenu entries
     */
    public function restore()
    {
        if (!(Permission::model()->hasGlobalPermission('settings', 'delete') && Permission::model()->hasGlobalPermission('settings', 'update'))) {
            Yii::app()->user->setFlash('error', gT("Access denied!"));
            $this->getController()->redirect(Yii::app()->createUrl('/admin'));
        }

        //get model to do the work
        $model = SurveymenuEntries::model();

        if (Yii::app()->request->isPostRequest) {
            //Check for permission!
            if (!Permission::model()->hasGlobalPermission('superadmin', 'read')) {
                return Yii::app()->getController()->renderPartial(
                    '/admin/super/_renderJson',
                    array(
                        'data' => [
                            'success' => false,
                            'redirect' => false,
                            'debug' => [$model, $_POST],
                            'debugErrors' => $model->getErrors(),
                            'message' => gT("You don't have the right to restore the settings to default")
                        ]
                    ),
                    false,
                    false
                );
            }
            $success = $model->restoreDefaults();
            return Yii::app()->getController()->renderPartial(
                '/admin/super/_renderJson',
                array(
                    'data' => [
                        'success' => $success,
                        'redirect' => false,
                        'debug' => [$model, $_POST],
                        'debugErrors' => $model->getErrors(),
                        'message' =>  ($success ? gT("Default survey menu entries restored.") : gT("Something went wrong! Are the survey menus properly restored?"))
                    ]
                ),
                false,
                false
            );
        }
    }

    /**
     * Deletes an array of models.
     */
    public function massDelete()
    {
        if (!(Permission::model()->hasGlobalPermission('settings', 'delete'))) {
            Yii::app()->user->setFlash('error', gT("Access denied!"));
            $this->getController()->redirect(Yii::app()->createUrl('/admin'));
        }

        if (Yii::app()->request->isPostRequest) {
            $aSurveyMenuEntryIds = json_decode(Yii::app()->request->getPost('sItems', '')) ?? [];
            $success = [];
            foreach ($aSurveyMenuEntryIds as $menuEntryid) {
                $model = SurveymenuEntries::model()->findByPk((int)$menuEntryid);
                $success[$menuEntryid] = false;
                if ($model !== null) {
                    $model->delete();
                    $success[$menuEntryid] = true;
                }
            }

            $debug = App()->getConfig('debug');
            $returnData = array(
                'data' => [
                    'success' => $success,
                    'redirect' => $this->getController()->createUrl('admin/menuentries/sa/view'),
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
        if (!(Permission::model()->hasGlobalPermission('settings', 'delete'))) {
            Yii::app()->user->setFlash('error', gT("Access denied!"));
            $this->getController()->redirect(Yii::app()->createUrl('/admin'));
        }

        if (Yii::app()->request->isPostRequest) {
            $menuEntryid = Yii::app()->request->getPost('menuEntryid', 0);
            $success = false;
            $model = SurveymenuEntries::model()->findByPk((int)$menuEntryid);
            //Don't delete  main menu entries when not superadmin
            if (($model->menu_id == 1 || $model->menu_id == 2) && !Permission::model()->hasGlobalPermission('superadmin', 'read')) {
                Yii::app()->user->setFlash('error', gT("Access denied!"));
                $this->getController()->redirect(Yii::app()->createUrl('/admin'));
            }
            $debug = App()->getConfig('debug');
            if ($model !== null) {
                $success = $model->delete();
            }

            $returnData = array(
                'data' => [
                    'success' => $success,
                    'redirect' => $this->getController()->createUrl('admin/menuentries/sa/view'),
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
     * Reorders the entries
     */
    public function reorder()
    {
        if (!(Permission::model()->hasGlobalPermission('settings', 'update'))) {
            Yii::app()->user->setFlash('error', gT("Access denied!"));
            $this->getController()->redirect(Yii::app()->createUrl('/admin'));
        }

        if (Yii::app()->request->isPostRequest) {
            $model = SurveymenuEntries::model();
            $success = $model->reorder();
            $debug = App()->getConfig('debug');

            $returnData = array(
                'data' => [
                    'success' => $success,
                    'redirect' => $this->getController()->createUrl('admin/menuentries/sa/view'),
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
     * Returns the data model based on the primary key given in the GET variable.
     * If the data model is not found, an HTTP exception will be raised.
     * @param integer $id the ID of the model to be loaded
     * @return SurveymenuEntries the loaded model
     * @throws CHttpException
     * * @deprecated do not use this function in future
     */
    public function loadModel($id)
    {
        $model = SurveymenuEntries::model()->findByPk($id);
        if ($model === null) {
                    throw new CHttpException(404, 'The requested page does not exist.');
        }
        return $model;
    }

    /**
     * Performs the AJAX validation.
     * @param SurveymenuEntries $model the model to be validated
     */
    protected function performAjaxValidation($model)
    {
        if (isset($_POST['ajax']) && $_POST['ajax'] === 'surveymenu-entries-form') {
            echo CActiveForm::validate($model);
            Yii::app()->end();
        }
    }
}
