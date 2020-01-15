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
* Home Page boxes can respect user permissions.
* To that goal, create user groups corresponding (like 'administrator', 'publisher', 'templateeditor') related to the permissions
* Inspired by ACL pattern, see : https://en.wikipedia.org/wiki/Access_control_list
*/

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

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
        if (!Permission::model()->hasGlobalPermission('settings', 'read')) {
            Yii::app()->session['flashmessage'] = gT('Access denied!');
            $this->getController()->redirect(App()->createUrl("/admin"));
        }
        $this->_renderWrappedTemplate('homepagesettings', 'read', array(
            'model'=>$this->loadModel($id),
        ));
    }

    /**
     * Creates a new model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     */
    public function create()
    {
        if (!Permission::model()->hasGlobalPermission('settings', 'update')) {
            Yii::app()->session['flashmessage'] = gT('Access denied!');
            $this->getController()->redirect($this->createUrl("/admin/homepagesettings"));
        }

        $model = new Box;
        if (isset($_POST['Box'])) {
            if (Yii::app()->getConfig('demoMode')) {
                Yii::app()->setFlashMessage(gT('This setting cannot be changed because demo mode is active.'), 'error');
                $this->getController()->redirect(Yii::app()->getController()->createUrl("/admin/homepagesettings"));
            }
            $model->attributes = $_POST['Box'];
            if ($model->save()) {
                Yii::app()->user->setFlash('success', gT('New box created'));
                if (isset($_POST['saveandclose'])) {
                    $this->getController()->redirect(array('admin/homepagesettings'));
                } else {
                    $this->getController()->redirect(array('admin/homepagesettings/sa/update/id/'.$model->id));
                }
            } else {
                Yii::app()->user->setFlash('error', gT('Could not create new box'));
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
        if (!Permission::model()->hasGlobalPermission('settings', 'update')) {
            Yii::app()->setFlashMessage(gT('Access denied!'), 'error');
            $this->getController()->redirect(Yii::app()->getController()->createUrl("/admin/homepagesettings"));
        }
        if (Yii::app()->getConfig('demoMode')) {
            Yii::app()->setFlashMessage(gT('This setting cannot be changed because demo mode is active.'), 'error');
            $this->getController()->redirect(Yii::app()->getController()->createUrl("/admin/homepagesettings"));
        }

        $model = $this->loadModel($id);

        // Uncomment the following line if AJAX validation is needed
        // $this->performAjaxValidation($model);

        if (isset($_POST['Box'])) {
            $model->attributes = $_POST['Box'];
            if ($model->save()) {
                Yii::app()->user->setFlash('success', gT('Box updated'));

                if (isset($_POST['saveandclose'])) {
                    $this->getController()->redirect(array('admin/homepagesettings', 'id'=>$model->id));
                }
            } else {
                Yii::app()->user->setFlash('error', gT('Could not update box'));
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
    public function delete($id=null)
    {
        $id = App()->request->getPost('id',$id);
        if (!Permission::model()->hasGlobalPermission('settings', 'update')) {
            Yii::app()->session['flashmessage'] = gT('Access denied!');
            $this->getController()->redirect($this->createUrl("/admin/homepagesettings"));
        }
        if (Yii::app()->getConfig('demoMode')) {
            Yii::app()->setFlashMessage(gT('This setting cannot be changed because demo mode is active.'), 'error');
            $this->getController()->redirect(Yii::app()->getController()->createUrl("/admin/homepagesettings"));
        }


        $this->loadModel($id)->delete();
        Yii::app()->user->setFlash('success', gT('Box deleted'));

        // if AJAX request (triggered by deletion via admin grid view), we should not redirect the browser
        if (!isset($_GET['ajax'])) {
            $this->getController()->redirect(array('admin/homepagesettings'));
        }
    }

    /**
     * Lists all models.
     */
    public function index()
    {
        if (!Permission::model()->hasGlobalPermission('settings', 'read')) {
            Yii::app()->session['flashmessage'] = gT('Access denied!');
            $this->getController()->redirect(App()->createUrl("/admin"));
        }

        $dataProvider = new CActiveDataProvider('Box');
        $aData = array(
            'dataProvider'=>$dataProvider,
            'bShowLogo'=>(getGlobalSetting('show_logo') == "show"),
            'bShowLastSurveyAndQuestion'=>(getGlobalSetting('show_last_survey_and_question') == "show"),
            'bShowSurveyList'=>(getGlobalSetting('show_survey_list') == "show"),
            'bShowSurveyListSearch'=>(getGlobalSetting('show_survey_list_search') == "show"),
            'bBoxesInContainer'=>(getGlobalSetting('boxes_in_container') == "yes"),
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
        $model = new Box('search');
        $model->unsetAttributes(); // clear any default values
        if (isset($_GET['Box'])) {
                    $model->attributes = $_GET['Box'];
        }

        $this->_renderWrappedTemplate('homepagesettings', 'admin', array(
            'model'=>$model,
        ));
    }

    /**
     * Returns the data model based on the primary key given in the GET variable.
     * If the data model is not found, an HTTP exception will be raised.
     * @param integer $id the ID of the model to be loaded
     * @return Box the loaded model
     * @throws CHttpException
     */
    public function loadModel($id)
    {
        $model = Box::model()->findByPk($id);
        if ($model === null) {
                    throw new CHttpException(404, 'The requested page does not exist.');
        }
        return $model;
    }

    /**
     * Performs the AJAX toggle of show_logo setting
     */
    public function toggleShowLogoStatus()
    {
        if (Yii::app()->getConfig('demoMode')) {
            Yii::app()->setFlashMessage(gT('This setting cannot be changed because demo mode is active.'), 'error');
            $this->getController()->redirect(Yii::app()->getController()->createUrl("/admin/homepagesettings"));
        }
        if (Permission::model()->hasGlobalPermission('settings', 'update')) {
            $bNewShowLogo = (getGlobalSetting('show_logo') == "show") ? "hide" : "show";
            SettingGlobal::setSetting('show_logo', $bNewShowLogo);
            echo $bNewShowLogo;
        }
    }

    /**
     * Performs the AJAX toggle of show_last_survey_and_question setting
     */
    public function toggleShowLastSurveyAndQuestion()
    {
        if (Yii::app()->getConfig('demoMode')) {
            Yii::app()->setFlashMessage(gT('This setting cannot be changed because demo mode is active.'), 'error');
            $this->getController()->redirect(Yii::app()->getController()->createUrl("/admin/homepagesettings"));
        }
        if (Permission::model()->hasGlobalPermission('settings', 'update')) {
            $bNewShowLastSurveyAndQuestion = (getGlobalSetting('show_last_survey_and_question') == "show") ? "hide" : "show";
            SettingGlobal::setSetting('show_last_survey_and_question', $bNewShowLastSurveyAndQuestion);
            echo $bNewShowLastSurveyAndQuestion;
        }
    }


    /**
     * Performs the AJAX toggle of show_survey_list
     */
    public function toggleShowSurveyList()
    {
        if (Yii::app()->getConfig('demoMode')) {
            Yii::app()->setFlashMessage(gT('This setting cannot be changed because demo mode is active.'), 'error');
            $this->getController()->redirect(Yii::app()->getController()->createUrl("/admin/homepagesettings"));
        }

        if (Permission::model()->hasGlobalPermission('settings', 'update')) {
            $bShowSurveyList = (getGlobalSetting('show_survey_list') == "show") ? "hide" : "show";
            SettingGlobal::setSetting('show_survey_list', $bShowSurveyList);
            echo $bShowSurveyList;
        }
    }

    /**
     * Performs the AJAX toggle of show_survey_list_search
     */
    public function toggleShowSurveyListSearch()
    {
        if (Yii::app()->getConfig('demoMode')) {
            Yii::app()->setFlashMessage(gT('This setting cannot be changed because demo mode is active.'), 'error');
            $this->getController()->redirect(Yii::app()->getController()->createUrl("/admin/homepagesettings"));
        }

        if (Permission::model()->hasGlobalPermission('settings', 'update')) {
            $bShowSurveyListSearch = (getGlobalSetting('show_survey_list_search') == "show") ? "hide" : "show";
            SettingGlobal::setSetting('show_survey_list_search', $bShowSurveyListSearch);
            echo $bShowSurveyListSearch;
        }
    }

    /**
     * Performs the AJAX toggle of show_survey_list_search
     */
    public function changeBoxesInContainer()
    {
        if (Yii::app()->getConfig('demoMode')) {
            Yii::app()->setFlashMessage(gT('This setting cannot be changed because demo mode is active.'), 'error');
            $this->getController()->redirect(Yii::app()->getController()->createUrl("/admin/homepagesettings"));
        }

        if (Permission::model()->hasGlobalPermission('settings', 'update')) {
            $changeBoxesInContainer = (getGlobalSetting('boxes_in_container') == "yes") ? "no" : "yes";
            SettingGlobal::setSetting('boxes_in_container', $changeBoxesInContainer);
            echo $changeBoxesInContainer;
        }
    }

    /**
     * Performs the AJAX update of box setting
     */
    public function setBoxesSettings($boxesbyrow, $boxesoffset)
    {
        if (Yii::app()->getConfig('demoMode')) {
            Yii::app()->setFlashMessage(gT('This setting cannot be changed because demo mode is active.'), 'error');
            $this->getController()->redirect(Yii::app()->getController()->createUrl("/admin/homepagesettings"));
        }
        if (Permission::model()->hasGlobalPermission('settings', 'update')) {
            SettingGlobal::setSetting('boxes_by_row', $boxesbyrow);
            SettingGlobal::setSetting('boxes_offset', $boxesoffset);
            return true;
        }
    }

    public function resetall()
    {
        if (Permission::model()->hasGlobalPermission('settings', 'update')) {
            // We delete all the old boxes, and reinsert new ones
            Box::model()->deleteAll();
            Box::model()->restoreDefaults();
        }
        $this->getController()->redirect(array('admin/homepagesettings'));
    }

    /**
     * Performs the AJAX validation.
     * @param Box $model the model to be validated
     */
    protected function performAjaxValidation($model)
    {
        if (isset($_POST['ajax']) && $_POST['ajax'] === 'boxes-form') {
            echo CActiveForm::validate($model);
            Yii::app()->end();
        }
    }

    /**
     * Renders template(s) wrapped in header and footer
     *
     * @param string $sAction Current action, the folder to fetch views from
     * @param string $aViewUrls View url(s)
     * @param array $aData Data to be passed on. Optional.
     */
    protected function _renderWrappedTemplate($sAction = '', $aViewUrls = array(), $aData = array(), $sRenderFile = false)
    {
        App()->getClientScript()->registerScriptFile(App()->getConfig('adminscripts').'homepagesettings.js');
        parent::_renderWrappedTemplate($sAction, $aViewUrls, $aData, $sRenderFile);
    }

}
