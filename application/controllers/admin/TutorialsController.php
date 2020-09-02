<?php

/**
 * This tutorial controller is the first step to have user generated tutorials.
 * Current project stance is only to serve pregenerated tutorials bay LimeSurvey Company.
 * @TODO: Make this user editable
 */
class TutorialsController extends Survey_Common_Action
{
    /**
     * @return string[] action filters
     */
    public function filters()
    {
        return array(
            'accessControl', // perform access control for CRUD operations
            'postOnly + delete', // we only allow deletion via POST request
            'postOnly + triggerfinished', // we only allow triggerfinished via POST request
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
            array(
                'allow', // allow all users to perform 'index' and 'view' actions
                'actions'=>array('index', 'view'),
                'users'=>array('*'),
            ),
            array(
                'allow', // allow authenticated user to perform 'create' and 'update' actions
                'actions'=>array('create', 'update'),
                'users'=>array('@'),
            ),
            array(
                'allow', // allow admin user to perform 'admin' and 'delete' actions
                'actions'=>array('admin', 'delete'),
                'users'=>array('admin'),
            ),
            array(
                'deny', // deny all users
                'users'=>array('*'),
            ),
        );
    }

    /**
     * 
     */
    public function servertutorial()
    {
        $ajax = Yii::app()->request->getParam('ajax', false);
        if ($ajax == false) {
            $this->getController()->redirect(['/admin']);
        }
        $tutorialname = Yii::app()->request->getParam('tutorialname', '');
        $model = Tutorial::model()->findByName($tutorialname);
        $aTutorialArray = $model->getTutorialDataArray($tutorialname);
        return Yii::app()->getController()->renderPartial(
            '/admin/super/_renderJson',
            array(
                'data' => [
                    'tutorial'=> $aTutorialArray,
                ]
            ),
            false,
            false
        );
    }

    /**
     * Creates a new model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     */
    public function create()
    {
        $model = new Tutorial;

        // Uncomment the following line if AJAX validation is needed
        // $this->performAjaxValidation($model);

        if (isset($_POST['Tutorial'])) {
            $model->attributes = $_POST['Tutorial'];
            if ($model->save()) {
                $this->redirect(array('view', 'id'=>$model->tid));
            }
        }

        $this->render(
            'create',
            array(
                'model'=>$model,
            )
        );
    }

    /**
     * Updates a particular model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id the ID of the model to be updated
     */
    public function update($id)
    {
        $model = $this->loadModel($id);

        // Uncomment the following line if AJAX validation is needed
        // $this->performAjaxValidation($model);

        if (isset($_POST['Tutorial'])) {
            $model->attributes = $_POST['Tutorial'];
            if ($model->save()) {
                $this->redirect(array('view', 'id'=>$model->tid));
            }
        }

        $this->render(
            'update',
            array(
                'model'=>$model,
            )
        );
    }

    /**
     * Deletes a particular model.
     * If deletion is successful, the browser will be redirected to the 'admin' page.
     * @param integer $id the ID of the model to be deleted
     */
    public function delete($id)
    {
        $this->loadModel($id)->delete();

        // if AJAX request (triggered by deletion via admin grid view), we should not redirect the browser
        if (!isset($_GET['ajax'])) {
            $this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('admin'));
        }
    }

    public function triggerfinished($tid)
    {
        $oTutorial = Tutorial::model()->find('name=:name',array(':name'=>$tid));
        $oTutorial->setFinished(App()->user->id);
        echo '{"success": true}';
    }
    
    public function index()
    {
        $this->getController()->redirect(array('admin/tutorials/sa/view'));
    }

    public function view()
    {
        //$this->checkPermission();

        $data = array();
        $data['model'] = Tutorial::model();
        //App()->getClientScript()->registerPackage('surveymenufunctions');
        $this->_renderWrappedTemplate(null, array('tutorials/index'), $data);
    }

    /**
     * Manages all models.
     */
    public function admin()
    {
        $model = new Tutorial('search');
        $model->unsetAttributes(); // clear any default values
        if (isset($_GET['Tutorial'])) {
            $model->attributes = $_GET['Tutorial'];
        }

        $this->render(
            'admin',
            array(
                'model'=>$model,
            )
        );
    }

    /**
     * Returns the data model based on the primary key given in the GET variable.
     * If the data model is not found, an HTTP exception will be raised.
     * @param integer $id the ID of the model to be loaded
     * @return Tutorial the loaded model
     * @throws CHttpException
     */
    public function loadModel($id)
    {
        $model = Tutorial::model()->findByPk($id);
        if ($model === null) {
            throw new CHttpException(404, 'The requested page does not exist.');
        }
        return $model;
    }

    /**
     * Performs the AJAX validation.
     * @param Tutorial $model the model to be validated
     */
    protected function performAjaxValidation($model)
    {
        if (isset($_POST['ajax']) && $_POST['ajax'] === 'tutorials-form') {
            echo CActiveForm::validate($model);
            Yii::app()->end();
        }
    }
}
