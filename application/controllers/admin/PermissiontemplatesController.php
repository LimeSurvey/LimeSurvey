<?php

class PermissiontemplatesController extends Survey_Common_Action
{

    /**
     * Lists all models.
     */
    public function index()
    {
		$model = Permissiontemplates::model();
		$this->_renderWrappedTemplate(null, 'permissiontemplates/index', array(
			'model' => $model,
        ));
    }
    /**
     * Displays a particular model.
     * @param integer $id the ID of the model to be displayed
     */
    public function view($id)
    {
        $this->_renderWrappedTemplate(null, 'permissiontemplates/view', array(
            'model' => $this->loadModel($id),
        ));
    }

    /**
     * Creates a new model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     */
    public function create()
    {
        $model = new Permissiontemplates;

        // Uncomment the following line if AJAX validation is needed
        // $this->performAjaxValidation($model);

        if (isset($_POST['Permissiontemplates'])) {
            $model->attributes = $_POST['Permissiontemplates'];
            if ($model->save()) {
                $this->redirect(array('view', 'id' => $model->id));
            }

        }
		$this->_renderWrappedTemplate(null, 'permissiontemplates/create', array(
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

        if (isset($_POST['Permissiontemplates'])) {
            $model->attributes = $_POST['Permissiontemplates'];
            if ($model->save()) {
                $this->redirect(array('view', 'id' => $model->id));
            }

		}
		
		$this->_renderWrappedTemplate(null, 'permissiontemplates/update', array(
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
            $this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('admin'));
        }

    }

    /**
     * Manages all models.
     */
    public function actionAdmin()
    {
        $model = new Permissiontemplates('search');
        $model->unsetAttributes(); // clear any default values
        if (isset($_GET['Permissiontemplates'])) {
            $model->attributes = $_GET['Permissiontemplates'];
        }

        $this->render('admin', array(
            'model' => $model,
        ));
    }

    /**
     * Returns the data model based on the primary key given in the GET variable.
     * If the data model is not found, an HTTP exception will be raised.
     * @param integer $id the ID of the model to be loaded
     * @return Permissiontemplates the loaded model
     * @throws CHttpException
     */
    public function loadModel($id)
    {
        $model = Permissiontemplates::model()->findByPk($id);
        if ($model === null) {
            throw new CHttpException(404, 'The requested page does not exist.');
        }

        return $model;
    }

    /**
     * Performs the AJAX validation.
     * @param Permissiontemplates $model the model to be validated
     */
    protected function performAjaxValidation($model)
    {
        if (isset($_POST['ajax']) && $_POST['ajax'] === 'permissiontemplates-form') {
            echo CActiveForm::validate($model);
            Yii::app()->end();
        }
    }
}
