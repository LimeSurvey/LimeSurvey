<?php

class QuestionThemesController extends Survey_Common_Action
{
    /**
     * @var string the default layout for the views.
     */
    public $layout = 'view.php';

    /**
     * Displays a particular model.
     *
     * @param integer $id the ID of the model to be displayed
     *
     * @throws CHttpException
     */
    public function actionView($id)
    {
        $this->_renderWrappedTemplate('view', array(
            'model' => $this->loadModel($id),
        ));
    }

    /**
     * Creates a new model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     *
     * @throws CHttpException
     */
    public function actionCreate()
    {
        $model = new QuestionTheme;

        // Uncomment the following line if AJAX validation is needed
        // $this->performAjaxValidation($model);

        if (isset($_POST['QuestionTheme'])) {
            $model->attributes = $_POST['QuestionTheme'];
            if ($model->save()) {
                $this->getController()->redirect(array('view', 'id' => $model->id));
            }
        }

        $this->_renderWrappedTemplate('questionThemes','create', array(
            'model' => $model,
        ));
    }

    /**
     * Updates a particular model.
     * If update is successful, the browser will be redirected to the 'view' page.
     *
     * @param integer $id the ID of the model to be updated
     *
     * @throws CHttpException
     */
    public function actionUpdate($id)
    {
        $model = $this->loadModel($id);

        // Uncomment the following line if AJAX validation is needed
        // $this->performAjaxValidation($model);

        if (isset($_POST['QuestionTheme'])) {
            $model->attributes = $_POST['QuestionTheme'];
            if ($model->save()) {
                $this->getController()->redirect(array('view', 'id' => $model->id));
            }
        }

        $this->_renderWrappedTemplate('update', array(
            'model' => $model,
        ));
    }

    /**
     * Deletes a particular model.
     * If deletion is successful, the browser will be redirected to the 'admin' page.
     *
     * @param integer $id the ID of the model to be deleted
     *
     * @throws CDbException
     * @throws CHttpException
     */
    public function actionDelete($id)
    {
        $this->loadModel($id)->delete();

        // if AJAX request (triggered by deletion via admin grid view), we should not redirect the browser
        if (!isset($_GET['ajax'])) {
            $this->getController()->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('admin'));
        }
    }

    /**
     * Lists all models.
     *
     * @throws CHttpException
     */
    public function actionIndex()
    {
        $dataProvider = new CActiveDataProvider('QuestionTheme');
        $this->_renderWrappedTemplate('index', array(
            'dataProvider' => $dataProvider,
        ));
    }

    /**
     * Manages all models.
     *
     * @throws CHttpException
     */
    public function actionAdmin()
    {
        $model = new QuestionTheme('search');
        $model->unsetAttributes();  // clear any default values
        if (isset($_GET['QuestionTheme'])) {
            $model->attributes = $_GET['QuestionTheme'];
        }

        $this->_renderWrappedTemplate('admin', array(
            'model' => $model,
        ));
    }

    /**
     * Returns the data model based on the primary key given in the GET variable.
     * If the data model is not found, an HTTP exception will be raised.
     *
     * @param integer $id the ID of the model to be loaded
     *
     * @return QuestionTheme|null the loaded model
     * @throws CHttpException
     */
    public function loadModel($id)
    {
        $model = QuestionTheme::model()->findByPk($id);
        if ($model === null) {
            throw new CHttpException(404, 'The requested page does not exist.');
        }
        return $model;
    }

    /**
     * @param string  $id
     *
     * @param boolean $visible
     */
    public function toggleVisibility($id)
    {
        if (!Permission::model()->hasGlobalPermission('templates', 'update')) {
            return;
        }
        $aQuestionThemes = QuestionTheme::model()->findAllByAttributes([], 'id = :id', ['id' => $id]);

        /** @var QuestionTheme $oQuestionTheme */
        foreach ($aQuestionThemes as $oQuestionTheme) {
            if ($oQuestionTheme->visible == 'Y'){
                $oQuestionTheme->setAttribute('visible', 'N');
            } else {
                $oQuestionTheme->setAttribute('visible', 'Y');
            }
            $oQuestionTheme->save();
        }
    }

    /**
     * Performs the AJAX validation.
     *
     * @param QuestionTheme $model the model to be validated
     */
    protected function performAjaxValidation($model)
    {
        if (isset($_POST['ajax']) && $_POST['ajax'] === 'question-themes-form') {
            echo CActiveForm::validate($model);
            Yii::app()->end();
        }
    }
}
