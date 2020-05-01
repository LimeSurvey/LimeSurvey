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
     * @param null $id
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
     *
     * @return void
     * @throws Exception
     */

    public function resetMultiple()
    {   
        $aTemplates = json_decode(App()->request->getPost('sItems'));
        $gridid = App()->request->getPost('grididvalue');
        $aResults = array();

        if (Permission::model()->hasGlobalPermission('templates', 'update')) {

            foreach($aTemplates as $template){
                $model = $this->loadModel($template, $gridid);
                if ($gridid == 'questionthemes-grid') {
                    $templatename = $model->name;
                    $templatefolder = $model->xml_path;
                    $aResults[$template]['title'] = $templatename;
                    $sQuestionThemeName = $model->importManifest($templatefolder);
                    $aResults[$template]['result'] = isset($sQuestionThemeName) ? true : false;
                } elseif ($gridid == 'themeoptions-grid') {
                    $templatename = $model->template_name;
                    $aResults[$template]['title'] = $templatename;
                    $aResults[$template]['result'] = TemplateConfiguration::uninstall($templatename);
                    TemplateManifest::importManifest($templatename);
                }
            }
            //set Modal table labels
            $tableLabels = array(gT('Template id'),gT('Template name') ,gT('Status'));

            App()->getController()->renderPartial(
                'ext.admin.survey.ListSurveysWidget.views.massive_actions._action_results', 
                array
                (
                    'aResults'     => $aResults,
                    'successLabel' => gT('Has been reset'),
                    'tableLabels'  => $tableLabels
                    
                ));
        } else {

            App()->setFlashMessage(gT("We are sorry but you don't have permissions to do this."), 'error');
        }

    }

    /**
     * Uninstall all selected themes from massive action
     *
     * @return void
     * @throws CException
     */
    public function uninstallMultiple()
    {
        $aTemplates = json_decode(App()->request->getPost('sItems'));
        $gridid = App()->request->getPost('grididvalue');
        $aResults = array();

        if (Permission::model()->hasGlobalPermission('templates', 'update')) {

            foreach ($aTemplates as $template) {
                $model = $this->loadModel($template, $gridid);

                if ($gridid == 'questionthemes-grid') {
                    $aResults[$template]['title'] = $model->name;
                    $templatename = $model->name;
                    $aResults[$template]['title'] = $templatename;
                    $aUninstallResult = QuestionTheme::uninstall($model);
                    $aResults[$template]['result'] = isset($aUninstallResult['result']) ? $aUninstallResult['result'] : false;
                    $aResults[$template]['error'] = isset($aUninstallResult['error']) ? $aUninstallResult['error'] : null;

                } elseif ($gridid == 'themeoptions-grid') {
                    $aResults[$template]['title'] = $model->template_name;
                    $templatename = $model->template_name;
                    $aResults[$template]['title'] = $templatename;
                    if (!Template::hasInheritance($templatename)) {
                        if ($templatename != getGlobalSetting('defaulttheme')) {
                            $aResults[$template]['result'] = TemplateConfiguration::uninstall($templatename);
                        } else {
                            $aResults[$template]['result'] = false;
                            $aResults[$template]['error'] = gT('Error! You cannot uninstall the default template.');
                        }

                    } else {
                        $aResults[$template]['result'] = false;
                        $aResults[$template]['error'] = gT('Error! Some theme(s) inherit from this theme');
                    }
                }
            }
            //set Modal table labels
            $tableLabels= array(gT('Template id'),gT('Template name') ,gT('Status'));

            App()->getController()->renderPartial(
                'ext.admin.survey.ListSurveysWidget.views.massive_actions._action_results',
                array
                (
                    'aResults'     => $aResults,
                    'successLabel' => gT('Uninstalled'),
                    'tableLabels'  => $tableLabels
                ));
            
        } else {

            App()->setFlashMessage(gT("We are sorry but you don't have permissions to do this."), 'error');
        }
    }


    /**
     * render selected items for massive action modal
     *
     * @return void
     * @throws CHttpException
     * @throws CException
     */

    public function renderSelectedItems()
    {
        $aTemplates = json_decode(App()->request->getPost('$oCheckedItems'));
        $aResults = [];
        $gridid = App()->request->getParam('$grididvalue');

        foreach($aTemplates as $template){
            $aResults[$template]['title'] = '';
            $model = $this->loadModel($template, $gridid);

            if ($gridid == 'questionthemes-grid'){
                $aResults[$template]['title'] = $model->name;
            } elseif ($gridid == 'themeoptions-grid'){
                $aResults[$template]['title'] = $model->template_name;
            }

            $aResults[$template]['result'] = gT('Selected');
        }
        //set Modal table labels
        $tableLabels= array(gT('Template id'),gT('Template name') ,gT('Status'));

        App()->getController()->renderPartial(
            'ext.admin.grid.MassiveActionsWidget.views._selected_items',
            array(
                'aResults'     => $aResults,
                'successLabel' => gT('Selected'),
                'tableLabels'  => $tableLabels,
            )
        );        
    }

    /**
     * Updates a particular model (globally)
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id the ID of the model to be updated
     * @throws CException
     * @throws CHttpException
     */
    public function update($id)
    {
        $model = $this->loadModel($id);
        if (Permission::model()->hasTemplatePermission($model->template_name,'update')) {

            // Turn Ajax off as default save it after.
            $model = $this->turnAjaxmodeOffAsDefault($model);
            $model->save();

            if (isset($_POST['TemplateConfiguration'])) {
                $model->attributes = $_POST['TemplateConfiguration'];
                if ($model->save()) {
                    Yii::app()->user->setFlash('success', gT('Theme options saved.'));
                    $this->getController()->redirect(array('admin/themeoptions/sa/update/id/'.$model->id));
                }
            }
            $this->updateCommon($model);
        } else {
            Yii::app()->setFlashMessage(gT("We are sorry but you don't have permissions to do this."), 'error');
            $this->getController()->redirect(Yii::app()->getController()->createUrl("/admin/themeoptions"));
        }
    }

    /**
     * This method turn ajaxmode off as default.
     *
     * @param TemplateConfiguration $templateConfiguration Configuration of Template
     * @return TemplateConfiguration
     */
    public function turnAjaxmodeOffAsDefault(TemplateConfiguration $templateConfiguration)
    {
        $attributes = $templateConfiguration->getAttributes();
        $hasOptions = isset($attributes['options']);
        if ($hasOptions) {
            $options = $attributes['options'];
            $optionsJSON = json_decode($options, true);

            if ($options !== 'inherit' && $optionsJSON !== null) {
                $ajaxModeOn  = (!empty($optionsJSON['ajaxmode']) && $optionsJSON['ajaxmode'] == 'on');
                if ($ajaxModeOn) {
                    $optionsJSON['ajaxmode'] = 'off';
                    $options = json_encode($optionsJSON);
                    $templateConfiguration->setAttribute('options', $options);
                }
            } else {
                // todo: If its inherited do something else and set pageOptions to '' cause this is rendering string and this is not good. wee need the
                // todo: json
            }
        }
        return $templateConfiguration;
    }

    /**
     * Updates a particular model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $sid the ID of the model to be updated
     */
    public function updatesurvey($sid)
    {
        if (Permission::model()->hasGlobalPermission('templates', 'update') ||
            Permission::model()->hasSurveyPermission($sid,'surveysettings','update') ) {
            // Did we really need hasGlobalPermission template ? We are inside survey : hasSurveyPermission only seem better
            $model = TemplateConfiguration::getInstance(null, null, $sid);

            // turn ajaxmode off as default behavior
            $model = $this->turnAjaxmodeOffAsDefault($model);
            $model->save();

            if (isset($_POST['TemplateConfiguration'])) {
                $model->attributes = $_POST['TemplateConfiguration'];
                if ($model->save()) {
                    Yii::app()->user->setFlash('success', gT('Theme options saved.'));
                    $this->getController()->redirect(Yii::app()->getController()->createUrl("/admin/themeoptions/sa/updatesurvey", ['surveyid'=>$sid, 'sid'=>$sid]));
                }
            }
            $this->updateCommon($model, $sid);
        } else {
            Yii::app()->setFlashMessage(gT("We are sorry but you don't have permissions to do this."), 'error');
            $this->getController()->redirect(array('admin/survey/sa/view/surveyid/'.$sid));
        }
    }

    /**
     * Updates a particular model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id the ID of the model to be updated
     * @param $gsid
     * @param null $l
     */
    public function updatesurveygroup($id = null, $gsid, $l = null)
    {
        if (Permission::model()->hasGlobalPermission('templates', 'update')) {
            // @todo : review permission : template permission or group permission ?
            $sTemplateName = $id !== null ? TemplateConfiguration::model()->findByPk($id)->template_name : null;
            $model = TemplateConfiguration::getInstance($sTemplateName, $gsid);

            if ($model->bJustCreated === true && $l === null) {
                $this->getController()->redirect(App()->getController()->createUrl("/admin/themeoptions/sa/updatesurveygroup/", ['id'=>$id, 'gsid'=>$gsid, 'l'=>1]));
            }

            if (isset($_POST['TemplateConfiguration'])) {
                $model = TemplateConfiguration::getInstance($_POST['TemplateConfiguration']['template_name'], $gsid);
                $model->attributes = $_POST['TemplateConfiguration'];
                if ($model->save()) {
                    App()->user->setFlash('success', gT('Theme options saved.'));
                    $this->getController()->redirect(App()->getController()->createUrl("/admin/surveysgroups/sa/update/", ['id'=>$gsid]));
                }
            }

            $this->updateCommon($model);
        } else {
            App()->setFlashMessage(gT("We are sorry but you don't have permissions to do this."), 'error');
            $this->getController()->redirect(App()->getController()->createUrl("/admin/surveysgroups/sa/update/", ['id'=>$gsid]));
        }
    }

    /**
     * @param string $sAdminThemeName
     */
    public function setAdminTheme($sAdminThemeName)
    {
        if (!Permission::model()->hasGlobalPermission('settings', 'update')) {
            App()->setFlashMessage(gT("We are sorry but you don't have permissions to do this."), 'error');
            $this->getController()->redirect(array('/admin'));
        }

        $sAdmintheme = sanitize_paranoid_string($sAdminThemeName);
        SettingGlobal::setSetting('admintheme', $sAdmintheme);
        $this->getController()->redirect(App()->getController()->createUrl("admin/themeoptions#adminthemes"));
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
            $aData['oQuestionTheme'] =  new QuestionTheme;
            $canImport = true;
            $importErrorMessage = null;

            if(!is_writable(App()->getConfig('tempdir'))) {
                $canImport = false;
                $importErrorMessage = gT("The template upload directory doesn't exist or is not writable.");
            }
            else if (!is_writable(App()->getConfig('userthemerootdir'))) {
                $canImport = false;
                $importErrorMessage = gT("Some directories are not writable. Please change the folder permissions for /tmp and /upload/themes in order to enable this option.");
            }
            else if (!function_exists("zip_open")) {
                $canImport = false;
                $importErrorMessage = gT("You do not have the required ZIP library installed in PHP.");
            }

            /// FOR GRID View
            $filterForm = App()->request->getPost('TemplateConfiguration', false);
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

            $filterForm = App()->request->getPost('QuestionTheme', false);
            if ($filterForm) {
                $aData['oQuestionTheme']->setAttributes($filterForm, false);
                if (array_key_exists('description', $filterForm)){
                    $aData['oQuestionTheme']->description = $filterForm['description'];
                }
                if (array_key_exists('core_theme', $filterForm)){
                    $aData['oQuestionTheme']->core_theme = $filterForm['core_theme'] == '1' || $filterForm['core_theme'] == '0' ? intval($filterForm['core_theme']) : '';
                }
                if (array_key_exists('extends', $filterForm)){
                    $aData['oQuestionTheme']->extends = $filterForm['extends'];
                }
            }

            // Page size
            if (App()->request->getParam('pageSize')) {
                App()->user->setState('pageSizeTemplateView', (int) App()->request->getParam('pageSize'));
            }

            $aData['oSurveyTheme'] = $oSurveyTheme;
            $aData['canImport']  = $canImport;
            $aData['importErrorMessage']  = $importErrorMessage;
            $aData['pageSize'] = App()->user->getState('pageSizeTemplateView', App()->params['defaultPageSize']); // Page size

            $this->_renderWrappedTemplate('themeoptions', 'index', $aData);
        } else {
            App()->setFlashMessage(gT("We are sorry but you don't have permissions to do this."), 'error');
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
            App()->setFlashMessage(gT("We are sorry but you don't have permissions to do this."), 'error');
            $this->getController()->redirect(App()->createUrl("/admin"));
        }
    }

    /**
     * Returns the data model based on the primary key given in the GET variable.
     * If the data model is not found, an HTTP exception will be raised.
     *
     * @param integer $id the ID of the model to be loaded
     * @param null    $gridid
     *
     * @return QuestionTheme|TemplateConfiguration|null
     * @throws CHttpException
     */
    public function loadModel($id, $gridid = null)
    {
        if ( $gridid == 'questionthemes-grid') {
            $model = QuestionTheme::model()->findByPk($id);
        } else {
            $model = TemplateConfiguration::model()->findByPk($id);
        }
        if ($model === null) {
            throw new CHttpException(404, 'The requested page does not exist.');
        }

        return $model;
    }


    /**
     * Import or install the Theme Condigurations into the database
     *
     * @throws Exception
     */
    public function importManifest()
    {
        $templatename = App()->request->getPost('templatename');
        $theme = App()->request->getPost('theme');
        if (Permission::model()->hasGlobalPermission('templates', 'update')) {
            if ($theme == 'questiontheme') {
                $templateFolder = App()->request->getPost('templatefolder');
                $questionTheme = new QuestionTheme();
                $themeName = $questionTheme->importManifest($templateFolder);
                if (isset($themeName)){
                    App()->setFlashMessage(sprintf(gT('The Question theme "%s" has been sucessfully installed'), "$themeName"), 'success');
                } else {
                    App()->setFlashMessage(sprintf(gT('The Question theme "%s" could not be installed'), $themeName), 'error');
                }
                $this->getController()->redirect(array("admin/themeoptions#questionthemes"));
            } else {
                TemplateManifest::importManifest($templatename);
                $this->getController()->redirect(array("admin/themeoptions#surveythemes"));
            }
        } else {
            App()->setFlashMessage(gT("We are sorry but you don't have permissions to do this."), 'error');
            $this->getController()->redirect(array("admin/themeoptions"));
        }

    }

    public function uninstall()
    {
        $templatename = App()->request->getPost('templatename');
        if (Permission::model()->hasGlobalPermission('templates', 'update')) {
            if (!Template::hasInheritance($templatename)) {
                TemplateConfiguration::uninstall($templatename);
            } else {
                App()->setFlashMessage(
                    sprintf(
                        gT("You can't uninstall template '%s' because some templates inherit from it."),
                        $templatename
                    ),
                    'error'
                );
            }
        } else {
            App()->setFlashMessage(gT("We are sorry but you don't have permissions to do this."), 'error');
        }

        $this->getController()->redirect(array("admin/themeoptions"));
    }

    /**
     * @param integer $gsid
     * @throws Exception
     */
    public function reset($gsid)
    {
        $templatename = App()->request->getPost('templatename');
        if (Permission::model()->hasGlobalPermission('templates', 'update')) {
            TemplateConfiguration::uninstall($templatename);
            TemplateManifest::importManifest($templatename);
            App()->setFlashMessage(sprintf(gT("The theme '%s' has been reset."), $templatename), 'success');
            $this->getController()->redirect(array("admin/themeoptions"));
        } else {
            App()->setFlashMessage(gT("We are sorry but you don't have permissions to do this."), 'error');
            $this->getController()->redirect(
                App()->getController()->createUrl("/admin/surveysgroups/sa/update/", ['id'=>$gsid])
            );
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
            App()->end();
        }
    }

    /**
     * @return string|string[]|null
     * @throws CException
     */
    public function getPreviewTag()
    {
        $templatename = App()->request->getPost('templatename');
        $oTemplate = TemplateConfiguration::getInstanceFromTemplateName($templatename);
        $previewTag = $oTemplate->getPreview();
        return App()->getController()->renderPartial(
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
    private function updateCommon(TemplateConfiguration $model, $sid = null, $gsid = null)
    {
        /* init the template to current one if option use some twig function (imageSrc for example) mantis #14363 */
        $oTemplate = Template::model()->getInstance($model->template_name, $sid, $gsid);

        $oModelWithInheritReplacement = TemplateConfiguration::model()->findByPk($model->id);
        $aOptionAttributes            = TemplateManifest::getOptionAttributes($oTemplate->path);
        $aTemplateConfiguration = $oModelWithInheritReplacement->getOptionPageAttributes();
        App()->clientScript->registerPackage('bootstrap-switch', LSYii_ClientScript::POS_BEGIN);
        
        if ($aOptionAttributes['optionsPage'] == 'core') {
            App()->clientScript->registerPackage('themeoptions-core');
            $templateOptionPage = '';
        } else {
            $templateOptionPage = $oModelWithInheritReplacement->optionPage;
        }

        $oSimpleInheritance = Template::getInstance(
            $oModelWithInheritReplacement->sTemplateName,
            $sid,
            $gsid,
            null,
            true
        );
        $oSimpleInheritance->options = 'inherit';
        $oSimpleInheritanceTemplate = $oSimpleInheritance->prepareTemplateRendering(
            $oModelWithInheritReplacement->sTemplateName
        );
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
            $aData['topBar']['showSaveButton'] = true;
            $aData['surveybar']['buttons']['view'] = true;
            $aData['surveybar']['savebutton']['form'] = true;
            $aData['surveyid'] = $sid;
            $aData['title_bar']['title'] = gT("Survey theme options");
            $aData['subaction'] = gT("Survey theme options");
        }

        $this->_renderWrappedTemplate('themeoptions', 'update', $aData);
    }
}
