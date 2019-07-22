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
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class themeoptions  extends Survey_Common_Action
{

    /**
     * @param string $controller
     */
    public function __construct($controller = null, $id = null)
    {
        parent::__construct($controller, $id);
    }

    /**
     * Displays a particular model.
     * @param integer $id the ID of the model to be displayed
     */
    public function view($id)
    {
        if (Permission::model()->hasGlobalPermission('templates', 'read')) {
            $this->_renderWrappedTemplate('themeoptions', 'read', array(
                'model'=>$model,
            ));
            return;
        }
        Yii::app()->setFlashMessage(gT("We are sorry but you don't have permissions to do this."), 'error');
        $this->getController()->redirect(App()->createUrl("/admin"));
    }

    /**
     * Creates a new model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     */
    public function create()
    {
        if (Permission::model()->hasGlobalPermission('templates', 'update')) {
            $model = new TemplateOptions;

            if (isset($_POST['TemplateOptions'])) {
                $model->attributes = $_POST['TemplateOptions'];
                if ($model->save()) {
                    $this->getController()->redirect(array('admin/themeoptions/sa/update/id/'.$model->id));
                }
            }

            $this->render('create', array(
                'model'=>$model,
            ));
        } else {
            Yii::app()->setFlashMessage(gT("We are sorry but you don't have permissions to do this."), 'error');
            $this->getController()->redirect(array("admin/themeoptions"));
        }
    }

    /**
     * Reset all selected themes from massive action
     * @return void 
     */

    public function resetMultiple()
    {   
        $aTemplates = json_decode(Yii::app()->request->getPost('sItems'));
        $aResults = array();

        if (Permission::model()->hasGlobalPermission('templates', 'update')) {

            foreach($aTemplates as $template){
                $model = $this->loadModel($template);
                $templatename = $model->template_name;
                $aResults[$template]['title'] = $templatename; 
                $aResults[$template]['result'] = TemplateConfiguration::uninstall($templatename);
                TemplateManifest::importManifest($templatename);
            }
            //set Modal table labels
            $tableLabels = array(gT('Template id'),gT('Template name') ,gT('Status'));

            Yii::app()->getController()->renderPartial(
                'ext.admin.survey.ListSurveysWidget.views.massive_actions._action_results', 
                array
                (
                    'aResults'     => $aResults,
                    'successLabel' => gT('Has been reset'),
                    'tableLabels'  => $tableLabels
                    
                ));
        } else {

            Yii::app()->setFlashMessage(gT("We are sorry but you don't have permissions to do this."), 'error');
        }

    }

    /**
     * Uninstall all selected themes from massive action 
     *@return void 
     */

    public function uninstallMultiple()
    {
        $aTemplates = json_decode(Yii::app()->request->getPost('sItems'));
        $aResults = array();

        if (Permission::model()->hasGlobalPermission('templates', 'update')) {

            foreach($aTemplates as $template){
                $model = $this->loadModel($template);
                $templatename = $model->template_name;
                $aResults[$template]['title'] = $templatename;  

                if (!Template::hasInheritance($templatename)) {   
                    if ($templatename != getGlobalSetting('defaulttheme')){
                        $aResults[$template]['result'] = TemplateConfiguration::uninstall($templatename);
                    }else{
                        $aResults[$template]['result'] = false;
                        $aResults[$template]['error'] = gT('Error!! You cannot uninstall the default template');
                    }
                  
                } else {   
                    $aResults[$template]['result'] = false;
                    $aResults[$template]['error'] = gT('Error!! Some templates inherit from it');
                }
            }
            //set Modal table labels
            $tableLabels= array(gT('Template id'),gT('Template name') ,gT('Status'));

            Yii::app()->getController()->renderPartial(
                'ext.admin.survey.ListSurveysWidget.views.massive_actions._action_results', 
                array
                (
                    'aResults'     => $aResults,
                    'successLabel' => gT('Uninstalled'),
                    'tableLabels'  => $tableLabels
                ));
            
        } else {

            Yii::app()->setFlashMessage(gT("We are sorry but you don't have permissions to do this."), 'error');
        }
    }

    
     /**
     * render selected items for massive action modal
     * @return void
     */

    public function renderSelectedItems()
    {
        $aTemplates = json_decode(Yii::app()->request->getPost('$oCheckedItems'));   
        $aResults = array();
        foreach($aTemplates as $template){

            $model = $this->loadModel($template);
            $aResults[$template]['title'] = $model->template_name;
            $aResults[$template]['result'] = gT('Selected');
        }
        //set Modal table labels
        $tableLabels= array(gT('Template id'),gT('Template name') ,gT('Status'));

        Yii::app()->getController()->renderPartial(
            'ext.admin.grid.MassiveActionsWidget.views._selected_items',
            array(
                'aResults'     => $aResults,
                'successLabel' => gT('Seleted'),
                'tableLabels'  => $tableLabels,
            )
        );        
    }


    /**
     * Updates a particular model (globally)
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id the ID of the model to be updated
     */
    public function update($id)
    {
        $model = $this->loadModel($id);
        if (Permission::model()->hasTemplatePermission($model->template_name,'update')) {
            if (isset($_POST['TemplateConfiguration'])) {
                $model->attributes = $_POST['TemplateConfiguration'];
                if ($model->save()) {
                    Yii::app()->user->setFlash('success', gT('Theme options saved.'));
                    $this->getController()->redirect(array('admin/themeoptions/sa/update/id/'.$model->id));
                }
            }
            $this->_updateCommon($model);
        } else {
            Yii::app()->setFlashMessage(gT("We are sorry but you don't have permissions to do this."), 'error');
            $this->getController()->redirect(Yii::app()->getController()->createUrl("/admin/themeoptions"));
        }
    }

    /**
     * Updates a particular model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $sid the ID of the model to be updated
     */
    public function updatesurvey($sid)
    {
        if (Permission::model()->hasGlobalPermission('templates', 'update') || Permission::model()->hasSurveyPermission($sid,'surveysettings','update') ) {
            // Did we really need hasGlobalPermission template ? We are inside survey : hasSurveyPermission only seem better
            $model = TemplateConfiguration::getInstance(null, null, $sid);
            if (isset($_POST['TemplateConfiguration'])) {
                $model->attributes = $_POST['TemplateConfiguration'];
                if ($model->save()) {
                    Yii::app()->user->setFlash('success', gT('Theme options saved.'));
                    $this->getController()->redirect(Yii::app()->getController()->createUrl("/admin/themeoptions/sa/updatesurvey", ['surveyid'=>$sid, 'sid'=>$sid]));
                }
            }
            $this->_updateCommon($model, $sid);
        } else {
            Yii::app()->setFlashMessage(gT("We are sorry but you don't have permissions to do this."), 'error');
            $this->getController()->redirect(array('admin/survey/sa/view/surveyid/'.$sid));
        }
    }

    /**
     * Updates a particular model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id the ID of the model to be updated
     */
    public function updatesurveygroup($id = null, $gsid, $l = null)
    {
        if (Permission::model()->hasGlobalPermission('templates', 'update')) {
            // @todo : review permission : template permission or group permission ?
            $sTemplateName = $id !== null ? TemplateConfiguration::model()->findByPk($id)->template_name : null;
            $model = TemplateConfiguration::getInstance($sTemplateName, $gsid);

            if ($model->bJustCreated === true && $l === null) {
                $this->getController()->redirect(Yii::app()->getController()->createUrl("/admin/themeoptions/sa/updatesurveygroup/", ['id'=>$id, 'gsid'=>$gsid, 'l'=>1]));
            }

            if (isset($_POST['TemplateConfiguration'])) {
                $model = TemplateConfiguration::getInstance($_POST['TemplateConfiguration']['template_name'], $gsid);
                $model->attributes = $_POST['TemplateConfiguration'];
                if ($model->save()) {
                    Yii::app()->user->setFlash('success', gT('Theme options saved.'));
                    $this->getController()->redirect(Yii::app()->getController()->createUrl("/admin/surveysgroups/sa/update/", ['id'=>$gsid]));
                }
            }

            $this->_updateCommon($model);
        } else {
            Yii::app()->setFlashMessage(gT("We are sorry but you don't have permissions to do this."), 'error');
            $this->getController()->redirect(Yii::app()->getController()->createUrl("/admin/surveysgroups/sa/update/", ['id'=>$gsid]));
        }
    }

    public function setAdminTheme($sAdminThemeName)
    {
        if (!Permission::model()->hasGlobalPermission('settings', 'update')) {
            Yii::app()->setFlashMessage(gT("We are sorry but you don't have permissions to do this."), 'error');
            $this->getController()->redirect(array('/admin'));
        }

        $sAdmintheme = sanitize_paranoid_string($sAdminThemeName);
        SettingGlobal::setSetting('admintheme', $sAdmintheme);
        $this->getController()->redirect(Yii::app()->getController()->createUrl("admin/themeoptions#adminthemes"));
    }

    /**
     * Lists all models.
     */
    public function index()
    {
        if (Permission::model()->hasGlobalPermission('templates', 'read')) {
            $aData = array();
            $oSurveyTheme = new TemplateConfiguration();
            $aData['oAdminTheme']  = new AdminTheme();


            $canImport = true;
            $importErrorMessage = null;

            if(!is_writable(Yii::app()->getConfig('tempdir'))) {
                $canImport = false;
                $importErrorMessage = gT("The template upload directory doesn't exist or is not writable.");
            }
            else if (!is_writable(Yii::app()->getConfig('userthemerootdir'))) {
                $canImport = false;
                $importErrorMessage = gT("Some directories are not writable. Please change the folder permissions for /tmp and /upload/themes in order to enable this option.");
            }
            else if (!function_exists("zip_open")) {
                $canImport = false;
                $importErrorMessage = gT("You do not have the required ZIP library installed in PHP.");
            }

            /// FOR GRID View
            $filterForm = Yii::app()->request->getPost('TemplateConfiguration', false);
            if ($filterForm) {
                $oSurveyTheme->setAttributes($filterForm, false);
                if (array_key_exists('template_description', $filterForm)){
                    $oSurveyTheme->template_description = $filterForm['template_description'];
                }
                if (array_key_exists('template_type', $filterForm)){
                    $oSurveyTheme->template_type = $filterForm['template_type'];
                }
                if (array_key_exists('template_extends', $filterForm)){
                    $oSurveyTheme->template_extends = $filterForm['template_extends'];
                }
            }

            // Page size
            if (Yii::app()->request->getParam('pageSize')) {
                Yii::app()->user->setState('pageSizeTemplateView', (int) Yii::app()->request->getParam('pageSize'));
            }

            $aData['oSurveyTheme'] = $oSurveyTheme;
            $aData['canImport']  = $canImport;
            $aData['importErrorMessage']  = $importErrorMessage;
            $aData['pageSize'] = Yii::app()->user->getState('pageSizeTemplateView', Yii::app()->params['defaultPageSize']); // Page size

            $this->_renderWrappedTemplate('themeoptions', 'index', $aData);
        } else {
            Yii::app()->setFlashMessage(gT("We are sorry but you don't have permissions to do this."), 'error');
            $this->getController()->redirect(App()->createUrl("/admin"));
        }
    }

    /**
     * Manages all models.
     */
    public function admin()
    {
        if (Permission::model()->hasGlobalPermission('templates', 'read')) {
            $model = new TemplateOptions('search');
            $model->unsetAttributes(); // clear any default values
            if (isset($_GET['TemplateOptions'])) {
                $model->attributes = $_GET['TemplateOptions'];
            }

            $this->render('admin', array(
                'model'=>$model,
            ));
        } else {
            Yii::app()->setFlashMessage(gT("We are sorry but you don't have permissions to do this."), 'error');
            $this->getController()->redirect(App()->createUrl("/admin"));
        }
    }

    /**
     * Returns the data model based on the primary key given in the GET variable.
     * If the data model is not found, an HTTP exception will be raised.
     * @param integer $id the ID of the model to be loaded
     * @return TemplateConfiguration the loaded model
     * @throws CHttpException
     */
    public function loadModel($id)
    {
        $model = TemplateConfiguration::model()->findByPk($id);
        if ($model === null) {
            throw new CHttpException(404, 'The requested page does not exist.');
        }

        return $model;
    }


    public function importManifest()
    {
        $templatename = Yii::app()->request->getPost('templatename');
        if (Permission::model()->hasGlobalPermission('templates', 'update')) {
            TemplateManifest::importManifest($templatename);
            $this->getController()->redirect(array("admin/themeoptions"));
        } else {
            Yii::app()->setFlashMessage(gT("We are sorry but you don't have permissions to do this."), 'error');
            $this->getController()->redirect(array("admin/themeoptions"));
        }

    }

    public function uninstall()
    {
        $templatename = Yii::app()->request->getPost('templatename');
        if (Permission::model()->hasGlobalPermission('templates', 'update')) {
            if (!Template::hasInheritance($templatename)) {
                TemplateConfiguration::uninstall($templatename);
            } else {
                Yii::app()->setFlashMessage(sprintf(gT("You can't uninstall template '%s' because some templates inherit from it."), $templatename), 'error');
            }
        } else {
            Yii::app()->setFlashMessage(gT("We are sorry but you don't have permissions to do this."), 'error');
        }

        $this->getController()->redirect(array("admin/themeoptions"));
    }

    public function reset($gsid)
    {
        $templatename = Yii::app()->request->getPost('templatename');
        if (Permission::model()->hasGlobalPermission('templates', 'update')) {
            TemplateConfiguration::uninstall($templatename);
            TemplateManifest::importManifest($templatename);
            Yii::app()->setFlashMessage(sprintf(gT("The theme '%s' has been reset."), $templatename), 'success');
            $this->getController()->redirect(array("admin/themeoptions"));
        } else {
            Yii::app()->setFlashMessage(gT("We are sorry but you don't have permissions to do this."), 'error');
            $this->getController()->redirect(Yii::app()->getController()->createUrl("/admin/surveysgroups/sa/update/", ['id'=>$gsid]));            
        }
    }

    /**
     * Performs the AJAX validation.
     * @param TemplateOptions $model the model to be validated
     */
    protected function performAjaxValidation($model)
    {
        if (isset($_POST['ajax']) && $_POST['ajax'] === 'template-options-form') {
            echo CActiveForm::validate($model);
            Yii::app()->end();
        }
    }

    public function getPreviewTag()
    {
        $templatename = Yii::app()->request->getPost('templatename');
        $oTemplate = TemplateConfiguration::getInstanceFromTemplateName($templatename);
        $previewTag = $oTemplate->getPreview();
        return Yii::app()->getController()->renderPartial(
            '/admin/super/_renderJson',
            ['data' => ['image' =>  $previewTag]],
            false,
            false
        );
    }

    /**
     * Renders the template options form.
     *
     * @param TemplateConfiguration $model
     * @param int $sid : survey id
     * @param int $gsid : survey group id
     * @return void
     */
    private function _updateCommon(TemplateConfiguration $model, $sid = null,$gsid = null)
    {
        /* init the template to current one if option use some twig function (imageSrc for example) mantis #14363 */
        Template::model()->getInstance($model->template_name,$sid,$gsid);

        $oModelWithInheritReplacement = TemplateConfiguration::model()->findByPk($model->id);
        $templateOptionPage           = $oModelWithInheritReplacement->optionPage;
        $aOptionAttributes            = TemplateManifest::getOptionAttributes($oModelWithInheritReplacement->path);
        $aTemplateConfiguration = $oModelWithInheritReplacement->getOptionPageAttributes();
        Yii::app()->clientScript->registerPackage('bootstrap-switch', LSYii_ClientScript::POS_BEGIN);

        $oSimpleInheritance = Template::getInstance($oModelWithInheritReplacement->sTemplateName, $sid, $gsid, null, true);
        $oSimpleInheritance->options = 'inherit';
        $oSimpleInheritanceTemplate = $oSimpleInheritance->prepareTemplateRendering($oModelWithInheritReplacement->sTemplateName);
        $oParentOptions = (array) $oSimpleInheritanceTemplate->oOptions;
        $oParentOptions = TemplateConfiguration::translateOptionLabels($oParentOptions);

        $aData = array(
            'model'=>$model,
            'templateOptionPage' => $templateOptionPage,
            'optionInheritedValues' => $oModelWithInheritReplacement->oOptions,
            'optionCssFiles' => $oModelWithInheritReplacement->files_css,
            'optionCssFramework' => $oModelWithInheritReplacement->cssframework_css,
            'aTemplateConfiguration' => $aTemplateConfiguration,
            'aOptionAttributes' => $aOptionAttributes,
            'sid' => $sid,
            'oParentOptions' => $oParentOptions,
            'sPackagesToLoad' => $oModelWithInheritReplacement->packages_to_load
        );

        if ($sid !== null) {
            $aData['surveybar']['buttons']['view'] = true;
            $aData['surveybar']['savebutton']['form'] = true;
            $aData['surveyid'] = $sid;
            $aData['title_bar']['title'] = gT("Survey template options");
            $aData['subaction'] = gT("Survey template options");
        }

        $this->_renderWrappedTemplate('themeoptions', 'update', $aData);
    }
}
