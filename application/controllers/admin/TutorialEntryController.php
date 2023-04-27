<?php

class TutorialEntryController extends SurveyCommonAction
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

    public function index()
    {
        $this->getController()->redirect(array('admin/tutorialentries/sa/view'));
    }

    public function view()
    {
        //$this->checkPermission();
        $data = array();
        $data['model'] = TutorialEntry::model();
        //App()->getClientScript()->registerPackage('surveymenufunctions');
        $this->renderWrappedTemplate(null, array('tutorialentries/index'), $data);
    }

    /**
     * Displays a particular model.
     * @param integer $id the ID of the model to be displayed
     */
    public function actionView($id)
    {
        $this->render('view', array(
            'model' => $this->loadModel($id),
        ));
    }

    /**
     * Creates a new model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     */
    public function actionCreate()
    {
        $model = new TutorialEntry();

        // Uncomment the following line if AJAX validation is needed
        // $this->performAjaxValidation($model);

        if (isset($_POST['TutorialEntry'])) {
            $model->attributes = $_POST['TutorialEntry'];
            if ($model->save()) {
                            $this->redirect(array('view', 'id' => $model->teid));
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
    public function actionUpdate($id)
    {
        $model = $this->loadModel($id);

        // Uncomment the following line if AJAX validation is needed
        // $this->performAjaxValidation($model);

        if (isset($_POST['TutorialEntry'])) {
            $model->attributes = $_POST['TutorialEntry'];
            if ($model->save()) {
                            $this->redirect(array('view', 'id' => $model->teid));
            }
        }

        $this->render('update', array(
            'model' => $model,
        ));
    }

    /**
     * Deletes a particular model.
     * If deletion is successful, the browser will be redirected to the 'admin' page.
     * @param integer $id the ID of the model to be deleted
     */
    public function actionDelete($id)
    {
        $this->loadModel($id)->delete();

        // if AJAX request (triggered by deletion via admin grid view), we should not redirect the browser
        if (!isset($_GET['ajax'])) {
                    $this->redirect($_POST['returnUrl'] ?? array('admin'));
        }
    }

    /**
     * Manages all models.
     */
    public function actionAdmin()
    {
        $model = new TutorialEntry('search');
        $model->unsetAttributes(); // clear any default values
        if (isset($_GET['TutorialEntry'])) {
                    $model->attributes = $_GET['TutorialEntry'];
        }

        $this->render('admin', array(
            'model' => $model,
        ));
    }

    /**
     * Returns the data model based on the primary key given in the GET variable.
     * If the data model is not found, an HTTP exception will be raised.
     * @param integer $id the ID of the model to be loaded
     * @return TutorialEntry the loaded model
     * @throws CHttpException
     */
    public function loadModel($id)
    {
        $model = TutorialEntry::model()->findByPk($id);
        if ($model === null) {
                    throw new CHttpException(404, 'The requested page does not exist.');
        }
        return $model;
    }

    /**
     * Performs the AJAX validation.
     * @param TutorialEntry $model the model to be validated
     */
    protected function performAjaxValidation($model)
    {
        if (isset($_POST['ajax']) && $_POST['ajax'] === 'tutorial-entry-form') {
            echo CActiveForm::validate($model);
            Yii::app()->end();
        }
    }
}
