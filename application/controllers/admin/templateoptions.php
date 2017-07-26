<?php
/*
* LimeSurvey
* Copyright (C) 2007-2011 The LimeSurvey Project Team / Carsten Schmitz
* All rights reserved.
* License: GNU/GPL License v2 or later, see LICENSE.php
* LimeSurvey is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*
* Template Options controller
*/

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class templateoptions  extends Survey_Common_Action
{

    public function __construct($controller=null, $id=null)
    {
        parent::__construct($controller, $id);
    }

    /**
     * Displays a particular model.
     * @param integer $id the ID of the model to be displayed
     */
    public function view($id)
    {
        if (!Permission::model()->hasGlobalPermission('templates','read')){
            Yii::app()->session['flashmessage'] =gT('Access denied!');
            $this->getController()->redirect(App()->createUrl("/admin"));
        }
        $this->_renderWrappedTemplate('templateoptions', 'read', array(
            'model'=>$this->loadModel($id),
        ));
    }

    /**
     * Creates a new model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     */
    public function create()
    {
        if (!Permission::model()->hasGlobalPermission('templates','update')){
            die('No permission');
        }

        $model=new TemplateOptions;

        // Uncomment the following line if AJAX validation is needed
        // $this->performAjaxValidation($model);

        if(isset($_POST['TemplateOptions']))
        {
            $model->attributes=$_POST['TemplateOptions'];
            if($model->save())
                $this->getController()->redirect(array('admin/templateoptions/sa/update/id/'.$model->id));
        }

        $this->render('create',array(
            'model'=>$model,
        ));
    }


    private function _updateCommon($model,$sid=null){

        $templateOptionPage = $model->optionPage;

        // Uncomment the following line if AJAX validation is needed
        // $this->performAjaxValidation($model);

        yii::app()->clientScript->registerPackage('bootstrap-switch');
        $aData = array(
            'model'=>$model, 
            'templateOptionPage' => $templateOptionPage
        );
        if($sid !== null){
            $aData['surveyid'] = $sid;
            $aData['title_bar']['title'] = gT("Survey template options");
            $aData['subaction'] = gT("Survey template options");
        }

        // TODO: twig file from template folder
        $this->_renderWrappedTemplate('templateoptions', 'update', $aData);
    }

    /**
     * Updates a particular model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id the ID of the model to be updated
     */
    public function update($id)
    {
        if (! Permission::model()->hasGlobalPermission('templates', 'update')){
            Yii::app()->setFlashMessage(gT('Access denied!'),'error');
            $this->getController()->redirect(Yii::app()->getController()->createUrl("/admin/templateoptions"));
        }
        $model = $this->loadModel($id);

        if(isset($_POST['TemplateConfiguration'])){
            $model->attributes=$_POST['TemplateConfiguration'];
            if($model->save())
                $this->getController()->redirect(array('admin/templateoptions/sa/update/id/'.$model->id));
        }

        $this->_updateCommon($model);

    }

    /**
     * Updates a particular model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id the ID of the model to be updated
     */
    public function updatesurvey($sid)
    {
        if (! Permission::model()->hasGlobalPermission('templates', 'update')){
            Yii::app()->setFlashMessage(gT('Access denied!'),'error');
            $this->getController()->redirect(Yii::app()->getController()->createUrl("/admin/templateoptions/sa/updatesurvey",['surveyid'=>$sid,'sid'=>$sid]));
        }

        $model = Template::getTemplateConfiguration(null, $sid);

        if(isset($_POST['TemplateConfiguration'])){
            $model->attributes=$_POST['TemplateConfiguration'];
            if($model->save())
                $this->getController()->redirect(Yii::app()->getController()->createUrl("/admin/templateoptions/sa/updatesurvey",['surveyid'=>$sid,'sid'=>$sid]));
        }

        $this->_updateCommon($model, $sid);
    }

    /**
     * Lists all models.
     */
    public function index()
    {
        if (! Permission::model()->hasGlobalPermission('templates', 'read') ){
            Yii::app()->session['flashmessage'] =gT('Access denied!');
            $this->getController()->redirect(App()->createUrl("/admin"));
        }

        $aData = array();

        $model = new TemplateConfiguration('search');
        $model->sid = $model->gsid = $model->uid = null;
        $aData['model'] = $model;
        $this->_renderWrappedTemplate('templateoptions', 'index', $aData);
    }

    /**
     * Manages all models.
     */
    public function admin()
    {
        $model=new TemplateOptions('search');
        $model->unsetAttributes();  // clear any default values
        if(isset($_GET['TemplateOptions']))
            $model->attributes=$_GET['TemplateOptions'];

        $this->render('admin',array(
            'model'=>$model,
        ));
    }

    /**
     * Returns the data model based on the primary key given in the GET variable.
     * If the data model is not found, an HTTP exception will be raised.
     * @param integer $id the ID of the model to be loaded
     * @return TemplateOptions the loaded model
     * @throws CHttpException
     */
    public function loadModel($id)
    {
        $model=TemplateConfiguration::model()->findByPk($id);
        if($model===null)
            throw new CHttpException(404,'The requested page does not exist.');
        return $model;
    }

    /**
     * Performs the AJAX validation.
     * @param TemplateOptions $model the model to be validated
     */
    protected function performAjaxValidation($model)
    {
        if(isset($_POST['ajax']) && $_POST['ajax']==='template-options-form')
        {
            echo CActiveForm::validate($model);
            Yii::app()->end();
        }
    }
}
