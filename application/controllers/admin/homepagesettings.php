<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class homepagesettings extends Survey_Common_Action
{
    public function __construct($controller, $id)
    {
        parent::__construct($controller, $id);
    }

    /**
     * Displays a particular model.
     * @param integer $id the ID of the model to be displayed
     */
    public function view($id)
    {

        $this->_renderWrappedTemplate('homepagesettings', 'view', array(
            'model'=>$this->loadModel($id),
        ));

    }

    /**
     * Creates a new model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     */
    public function create()
    {
        $model=new Boxes;

        // Uncomment the following line if AJAX validation is needed
        // $this->performAjaxValidation($model);

        if(isset($_POST['Boxes']))
        {
            $model->attributes=$_POST['Boxes'];
            if($model->save())
            {
                $this->getController()->redirect(array('admin/homepagesettings'));
            }
        }

        $this->_renderWrappedTemplate('homepagesettings', 'create', array(
            'model'=>$model,
        ));

    }

    /**
     * Updates a particular model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id the ID of the model to be updated
     */
    public function update($id)
    {
        $model=$this->loadModel($id);

        // Uncomment the following line if AJAX validation is needed
        // $this->performAjaxValidation($model);

        if(isset($_POST['Boxes']))
        {
            $model->attributes=$_POST['Boxes'];
            if($model->save())
            {
                if (isset($_POST['saveandclose']))
                {
                    $this->getController()->redirect(array('admin/homepagesettings','id'=>$model->id));
                }
            }
        }

        $this->_renderWrappedTemplate('homepagesettings', 'update', array(
            'model'=>$model,
        ));

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
        if(!isset($_GET['ajax']))
            $this->getController()->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('admin'));
    }

    /**
     * Lists all models.
     */
    public function index()
    {
        Yii::app()->loadHelper('surveytranslator');
        $dataProvider=new CActiveDataProvider('Boxes');
        $aData = array(
            'dataProvider'=>$dataProvider,
            'bShowLogo'=>(getGlobalSetting('show_logo')=="show"),
            'bShowLastSurveyAndQuestion'=>(getGlobalSetting('show_last_survey_and_question')=="show"),
            'iBoxesByRow'=>(int) getGlobalSetting('boxes_by_row'),
            'iBoxesOffset'=>(int) getGlobalSetting('boxes_offset'),
        );
        $this->_renderWrappedTemplate('homepagesettings', 'index', $aData);
    }

    /**
     * Manages all models.
     */
    public function admin()
    {
        $model=new Boxes('search');
        $model->unsetAttributes();  // clear any default values
        if(isset($_GET['Boxes']))
            $model->attributes=$_GET['Boxes'];

        $this->_renderWrappedTemplate('homepagesettings', 'admin', array(
            'model'=>$model,
        ));
    }

    /**
     * Returns the data model based on the primary key given in the GET variable.
     * If the data model is not found, an HTTP exception will be raised.
     * @param integer $id the ID of the model to be loaded
     * @return Boxes the loaded model
     * @throws CHttpException
     */
    public function loadModel($id)
    {
        $model=Boxes::model()->findByPk($id);
        if($model===null)
            throw new CHttpException(404,'The requested page does not exist.');
        return $model;
    }

    /**
     * Performs the AJAX validation.
     * @param Boxes $model the model to be validated
     */
    protected function performAjaxValidation($model)
    {
        if(isset($_POST['ajax']) && $_POST['ajax']==='boxes-form')
        {
            echo CActiveForm::validate($model);
            Yii::app()->end();
        }
    }

    /**
    * Renders template(s) wrapped in header and footer
    *
    * @param string $sAction Current action, the folder to fetch views from
    * @param string|array $aViewUrls View url(s)
    * @param array $aData Data to be passed on. Optional.
    */
    protected function _renderWrappedTemplate($sAction = '', $aViewUrls = array(), $aData = array())
    {
        parent::_renderWrappedTemplate($sAction, $aViewUrls, $aData);
    }

}
