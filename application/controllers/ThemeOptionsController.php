<?php

/**
 * @class ThemeOptionsController
 */
class ThemeOptionsController extends LSBaseController
{
    /**
     * It's import to have the accessRules set (security issue).
     * Only logged in users should have access to actions. All other permissions
     * should be checked in the action itself.
     *
     * @return array
     */
    public function accessRules()
    {
        return [
            [
                'allow',
                'actions' => [],
                'users'   => ['*'], //everybody
            ],
            [
                'allow',
                'actions' => ['view'],
                'users'   => ['@'], //only login users
            ],
            ['deny'], //always deny all actions not mentioned above
        ];
    }

    /**
     * This part comes from renderWrappedTemplate
     *
     * @param string $view Name of View
     *
     * @return bool
     */
    protected function beforeRender($view)
    {
        if (isset($this->aData['surveyid'])) {
            $this->aData['oSurvey'] = $this->aData['oSurvey'] ?? Survey::model()->findByPk($this->aData['surveyid']);

            // Needed to evaluate EM expressions in question summary
            // See bug #11845
            LimeExpressionManager::SetSurveyId($this->aData['surveyid']);
            LimeExpressionManager::StartProcessingPage(false, true);

            $this->layout = 'layout_questioneditor';
        }
        return parent::beforeRender($view);
    }

    /**
     * Displayed a particular Model.
     *
     * @param int $id ID of model.
     *
     * @return void
     */
    public function actionViewModel(int $id)
    {
        if (Permission::model()->hasGlobalPermission('templates', 'read')) {
            $this->render(
                'themeoptions'
            );
            return;
        }
        App()->setFlashMessage(
            gT("We are sorry but you don't have permissions to do this"),
            'error'
        );
        $this->redirect(App()->createUrl("/admin"));
    }

    /**
     * Create a new model.
     * If creation is sucessful, the browser will be redirected to the 'view' page.
     *
     * todo: function not in use (  new TemplateOptions(); there is no model class like this ..)
     *
     * @return void
     */
    /*
    public function actionCreate()
    {
        if (Permission::model()->hasGlobalPermission('template', 'update')) {
            $model = new TemplateOptions();

            if (isset($_POST['TemplateOptions'])) {
                $model->attributes = $_POST['TemplateOptions'];

                if ($model->save()) {
                    $this->redirect(
                        array('themeOptions/update/id/', $model->id)
                    );
                }

                $this->render(
                    'create',
                    array(
                        'model' => $model,
                    )
                );
            } else {
                App()->setFlashMessage(
                    gT("We are sorry but you don't have permissions to do this."),
                    'error'
                );
                $this->redirect(array("themeOptions"));
            }
        }
    }*/

    /**
     * Resets all selected themes from massive action.
     *
     * @return void
     * @throws CException
     */
    /*
    public function actionResetMultiple()
    {
        $aTemplates = json_decode(App()->request->getPost('sItems'));
        $gridid = App()->request->getPost('grididvalue');
        $aResults = array();

        if (Permission::model()->hasGlobalPermission('template', 'update')) {
            foreach ($aTemplates as $template) {
                if ($gridid === 'questionthemes-grid') {
                    /** @var QuestionTheme|null
                    $questionTheme = QuestionTheme::model()->findByPk($template);
                    $templatename = $questionTheme->name;
                    $templatefolder = $questionTheme->xml_path;
                    $aResults[$template]['title'] = $templatename;
                    $sQuestionThemeName = $questionTheme->importManifest($templatefolder);
                    $aResults[$template]['result'] = isset($sQuestionThemeName) ? true : false;
                } elseif ($gridid === 'themeoptions-grid') {
                    $model = TemplateConfiguration::model()->findByPk($template);
                    $templatename = $model->template_name;
                    $aResults[$template]['title'] = $templatename;
                    $aResults[$template]['result'] = TemplateConfiguration::uninstall($templatename);
                    TemplateManifest::importManifest($templatename);
                }
            }

            //set Modal table labels
            $tableLabels = array(gT('Theme ID'),gT('Theme name') ,gT('Status'));

            $this->renderPartial(
                'ext.admin.survey.ListSurveysWidget.views.massive_actions._action_results',
                array
                (
                    'aResults'     => $aResults,
                    'successLabel' => gT('Has been reset'),
                    'tableLabels'  => $tableLabels
                )
            );
        } else {
            //todo: this message gets never visible for the user ...
            App()->setFlashMessage(gT("We are sorry but you don't have permissions to do this."), 'error');
        }
    }*/

    /**
     * Uninstalls all selected themes from massive action.
     *
     * @return void
     * @throws Exception
     */
    public function actionUninstallMultiple()
    {
        $aTemplates = json_decode(App()->request->getPost('sItems', '')); //array of ids

        //can be 'themeoptions-grid' (for survey themes) or 'questionthemes-grid'
        $gridid = App()->request->getPost('grididvalue');
        $aResults = array();

        if (Permission::model()->hasGlobalPermission('templates', 'update')) {
            foreach ($aTemplates as $template) {
                $templateID = (int) $template;
                $model = $this->loadModel($templateID, $gridid); //model is TemplateConfiguration or QuestionTheme

                if ($gridid === 'questionthemes-grid') {
                    $aResults[$template]['title'] = $model->name;
                    $templatename = $model->name;
                    $aResults[$template]['title'] = $templatename;
                    $aUninstallResult = QuestionTheme::uninstall($model);
                    $aResults[$template]['result'] = $aUninstallResult['result'] ?? false;
                    $aResults[$template]['error'] = $aUninstallResult['error'] ?? null;
                } elseif ($gridid === 'themeoptions-grid') {
                    $aResults[$template]['title'] = $model->template_name;
                    $templatename = $model->template_name;
                    $aResults[$template]['title'] = $templatename;
                    if (!Template::hasInheritance($templatename)) {
                        if ($templatename != App()->getConfig('defaulttheme')) {
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
            $tableLabels = array(gT('Theme ID'),gT('Theme name') ,gT('Status'));

            $this->renderPartial(
                'ext.admin.survey.ListSurveysWidget.views.massive_actions._action_results',
                array
                (
                    'aResults'     => $aResults,
                    'successLabel' => gT('Uninstalled'),
                    'tableLabels'  => $tableLabels
                )
            );
        } else {
            App()->setFlashMessage(gT("We are sorry but you don't have permissions to do this."), 'error');
        }
    }

    /**
     * Renders selected Items for massive action modal.
     *
     * @return void
     * @throws CException
     * @throws CHttpException
     */
    public function actionSelectedItems()
    {
        $aTemplates = json_decode(App()->request->getPost('$oCheckedItems', ''));
        $aResults = [];
        $gridid = App()->request->getParam('$grididvalue');

        foreach ($aTemplates as $template) {
            $aResults[$template]['title'] = '';
            $model = $this->loadModel((int)$template, $gridid);

            if ($gridid === 'questionthemes-grid') {
                $aResults[$template]['title'] = $model->name;
            } elseif ($gridid === 'themeoptions-grid') {
                $aResults[$template]['title'] = $model->template_name;
            }

            $aResults[$template]['result'] = gT('Selected');
        }
        //set Modal table labels
        $tableLabels = array(gT('Theme ID'),gT('Theme name') ,gT('Status'));

        $this->renderPartial(
            'ext.admin.grid.MassiveActionsWidget.views._selected_items',
            array(
                'aResults'     => $aResults,
                'successLabel' => gT('Selected'),
                'tableLabels'  => $tableLabels,
            )
        );
    }

    /**
     * Updates a particular model (globally).
     * If update is successful, the browser will be redirected to the 'view' page.
     *
     * @param integer $id ID of the model
     *
     * @return void
     * @throws CException
     * @throws CHttpException
     */
    public function actionUpdate(int $id)
    {
        $model = $this->loadModel($id);

        if (Permission::model()->hasTemplatePermission($model->template_name, 'update')) {
            if (isset($_POST['TemplateConfiguration'])) {
                $model->attributes = $_POST['TemplateConfiguration'];
                if ($model->save()) {
                    App()->user->setFlash('success', gT('Theme options saved.'));
                    $this->redirect(array('themeOptions/update/id/' . $model->id));
                }
            }
            $this->updateCommon($model);
        } else {
            App()->setFlashMessage(gT("We are sorry but you don't have permissions to do this."), 'error');
            $this->redirect(array("themeOptions/index"));
        }
    }

    /**
     * Updates a particular model.
     * If update is successful, the browser will be redirected to the 'view' page.
     *
     * @return void
     */
    public function actionUpdateSurvey()
    {
        $sid = $this->getSurveyIdFromGetRequest();
        $gsid = $this->getSurveyGroupIdFromGetRequest();
        if (
            !Permission::model()->hasGlobalPermission('templates', 'update')
            && !Permission::model()->hasSurveyPermission($sid, 'surveysettings', 'update')
        ) {
            throw new CHttpException(403, gT("You do not have permission to access this page."));
        }
        // Did we really need hasGlobalPermission template ? We are inside survey : hasSurveyPermission only seem better
        $model = TemplateConfiguration::getInstance(null, null, $sid);

        if (isset($_POST['TemplateConfiguration'])) {
            $model->attributes = $_POST['TemplateConfiguration'];
            if ($model->save()) {
                App()->user->setFlash('success', gT('Theme options saved.'));
            }
        }
        $this->updateCommon($model, $sid, $gsid);
    }

    /**
     * Updates particular model.
     * If update is successful, the browser will be redirected to the 'view' page.
     *
     * @param integer $id   ID of model.
     * @param integer $gsid id of survey group
     * @param null    $l    ?
     *
     * @return void
     */
    public function actionUpdateSurveyGroup(int $id = null, int $gsid, $l = null)
    {
        if (!Permission::model()->hasGlobalPermission('templates', 'update')) {
            if (empty($gsid)) {
                throw new CHttpException(403, gT("You do not have permission to access this page."));
            }
            $oSurveysGroups = SurveysGroups::model()->findByPk($gsid);
            if (empty($oSurveysGroups) || !$oSurveysGroups->hasPermission('surveysettings', 'update')) {
                throw new CHttpException(403, gT("You do not have permission to access this page."));
            }
        }
        $sTemplateName = $id !== null ? TemplateConfiguration::model()->findByPk($id)->template_name : null;
        $model = TemplateConfiguration::getInstance($sTemplateName, $gsid);

        if ($model->bJustCreated === true && $l === null) {
            $this->redirect(array("themeOptions/updateSurveyGroup/", 'id' => $id, 'gsid' => $gsid, 'l' => 1));
        }

        if (isset($_POST['TemplateConfiguration'])) {
            $model = TemplateConfiguration::getInstance($_POST['TemplateConfiguration']['template_name'], $gsid);
            $model->attributes = $_POST['TemplateConfiguration'];
            if ($model->save()) {
                App()->user->setFlash('success', gT('Theme options saved.'));
            }
        }

        $this->updateCommon($model, null, $gsid);
    }

    /**
     * Sets admin theme.
     *
     * @param string $sAdminThemeName Admin theme Name
     *
     * @return void
     */
    public function actionSetAdminTheme(string $sAdminThemeName)
    {
        if (!Permission::model()->hasGlobalPermission('settings', 'update')) {
            throw new CHttpException(403, gT("You do not have permission to access this page."));
        }

        $sAdmintheme = sanitize_paranoid_string($sAdminThemeName);
        SettingGlobal::setSetting('admintheme', $sAdmintheme);
        $this->redirect(array("themeOptions/index","#" => "adminthemes"));
    }

    /**
     * Lists all models.
     *
     * @return void
     */
    public function actionIndex()
    {
        if (!Permission::model()->hasGlobalPermission('templates', 'read')) {
            throw new CHttpException(403, gT("You do not have permission to access this page."));
        }
        $aData = array();
        $oSurveyTheme = new TemplateConfiguration();
        $aData['aAdminThemes']  = AdminTheme::getAdminThemeList();
        $aData['oQuestionTheme'] = new QuestionTheme();
        $canImport = true;
        $importErrorMessage = null;

        if (!is_writable(App()->getConfig('tempdir'))) {
            $canImport = false;
            $importErrorMessage = gT("The template upload directory doesn't exist or is not writable.");
        } elseif (!is_writable(App()->getConfig('userthemerootdir'))) {
            $canImport = false;
            $importErrorMessage = gT("Some directories are not writable. Please change the folder permissions for /tmp and /upload/themes in order to enable this option.");
        } elseif (!class_exists('ZipArchive')) {
            $canImport = false;
            $importErrorMessage = gT("You do not have the required ZIP library installed in PHP.");
        }

        /// FOR GRID View
        $filterForm = App()->request->getPost('TemplateConfiguration', false);
        if ($filterForm) {
            $oSurveyTheme->setAttributes($filterForm, false);
            if (array_key_exists('template_description', $filterForm)) {
                $oSurveyTheme->template_description = $filterForm['template_description'];
            }
            if (array_key_exists('template_type', $filterForm)) {
                $oSurveyTheme->template_type = $filterForm['template_type'];
            }
            if (array_key_exists('template_extends', $filterForm)) {
                $oSurveyTheme->template_extends = $filterForm['template_extends'];
            }
        }

        $filterForm = App()->request->getPost('QuestionTheme', false);
        if ($filterForm) {
            $aData['oQuestionTheme']->setAttributes($filterForm, false);
            if (array_key_exists('description', $filterForm)) {
                $aData['oQuestionTheme']->description = $filterForm['description'];
            }
            if (array_key_exists('core_theme', $filterForm)) {
                $aData['oQuestionTheme']->core_theme = $filterForm['core_theme'] == '1' || $filterForm['core_theme'] == '0' ? intval($filterForm['core_theme']) : '';
            }
            if (array_key_exists('extends', $filterForm)) {
                $aData['oQuestionTheme']->extends = $filterForm['extends'];
            }
        }

        // Page size
        if (App()->request->getParam('pageSize')) {
            App()->user->setState('pageSizeTemplateView', (int) App()->request->getParam('pageSize'));
        }

        $aData['oSurveyTheme'] = $oSurveyTheme;
        $aData['aTemplatesWithoutDB'] = TemplateConfig::getTemplatesWithNoDb();

        $aData['canImport']  = $canImport;
        $aData['importErrorMessage']  = $importErrorMessage;
        $aData['pageSize'] = App()->user->getState('pageSizeTemplateView', App()->params['defaultPageSize']); // Page size

        $aData['topbar']['title'] = gT('Themes');
        $aData['topbar']['backLink'] = App()->createUrl('admin/index');

        if (Permission::model()->hasGlobalPermission('templates', 'import')) {
            //only show upload&install button if user has the permission ...
            $aData['topbar']['middleButtons'] = $this->renderPartial(
                'partial/topbarBtns/leftSideButtons',
                ['canImport' => $canImport, 'importErrorMessage' => $importErrorMessage ],
                true
            );
        }
        $this->aData = $aData;

        $this->render('index', $aData);
    }

    /**
     * Manages all models.
     *
     * todo: this actions is not in use (TemplateOptions does not exist)
     *
     * @return void
     */
    /*
    public function actionAdmin()
    {
        if (Permission::model()->hasGlobalPermission('templates', 'read')) {
            $model = new TemplateOptions('search');
            $model->unsetAttributes(); // clear any default values
            if (isset($_GET['TemplateOptions'])) {
                $model->attributes = $_GET['TemplateOptions'];
            }

            $this->render(
                'admin',
                array(
                    'model' => $model,
                )
            );
        } else {
            App()->setFlashMessage(gT("We are sorry but you don't have permissions to do this."), 'error');
            $this->redirect(array("/admin"));
        }
    }
    */

    /**
     * Returns the data model based on the primary key given in the GET variable.
     * If the data model is not found, and HTTP exception will be raised.
     *
     * @param int $id ID
     * @param int|string $gridid Grid ID
     *
     * @return QuestionTheme | TemplateConfiguration | null
     * @throws CHttpException
     */
    public function loadModel(int $id, $gridid = null)
    {
        if ($gridid === 'questionthemes-grid') {
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
     * Import or install the Theme Configuration into the database.
     * for survey theme and question theme
     *
     * @throws Exception
     * @return void
     */
    public function actionImportManifest()
    {
        $templatename = App()->request->getPost('templatename');
        $theme = App()->request->getPost('theme');
        if (Permission::model()->hasGlobalPermission('templates', 'update')) {
            if ($theme === 'questiontheme') {
                $templateFolder = App()->request->getPost('templatefolder');
                if (strpos($templateFolder, "..") !== false) {
                    throw new CHttpException(eT("Unsafe path"));
                }
                //$themeType is being sanitized inside getAbsolutePathForType
                $themeType = App()->request->getPost('theme_type');
                $fullTemplateFolder = QuestionTheme::getAbsolutePathForType($templateFolder, $themeType);
                $questionTheme = new QuestionTheme();
                //skip convertion LS3ToLS4 (this should have been happen BEFORE theme was moved to the uninstalled themes
                $themeName = $questionTheme->importManifest($fullTemplateFolder, true);
                if (isset($themeName)) {
                    App()->setFlashMessage(sprintf(gT('The Question theme "%s" has been successfully installed'), "$themeName"), 'success');
                } else {
                    App()->setFlashMessage(sprintf(gT('The Question theme "%s" could not be installed'), $themeName), 'error');
                }
                $this->redirect(array("themeOptions/index#questionthemes"));
            } else {
                TemplateManifest::importManifest($templatename);
                $this->redirect(array('themeOptions/index#surveythemes'));
            }
        } else {
            App()->setFlashMessage(gT("We are sorry but you don't have permissions to do this."), 'error');
            $this->redirect(array("themeOptions/index"));
        }
    }

    /**
     * Uninstalls the theme.
     *
     * @return void
     */
    public function actionUninstall()
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

        $this->redirect(array("themeOptions/index"));
    }

    /**
     * Resets the theme.
     *
     * @param integer $gsid ID
     *
     * @return void
     *
     * @throws Exception
     */
    public function actionReset(int $gsid)
    {
        if (!Permission::model()->hasGlobalPermission('templates', 'update')) {
            if (empty($gsid)) {
                throw new CHttpException(403, gT("You do not have permission to access this page."));
            }
            $oSurveysGroups = SurveysGroups::model()->findByPk($gsid);
            if (empty($oSurveysGroups) || !$oSurveysGroups->hasPermission('surveysettings', 'update')) {
                throw new CHttpException(403, gT("You do not have permission to access this page."));
            }
        }
        $templatename = App()->request->getPost('templatename');

        if ($gsid) {
            $oTemplateConfiguration = TemplateConfiguration::model()->find(
                "gsid = :gsid AND template_name = :templatename",
                array(":gsid" => $gsid, ":templatename" => $templatename)
            );
            if (empty($oTemplateConfiguration)) {
                throw new CHttpException(401, gT("Invalid theme configuration for this group."));
            }
            $oTemplateConfiguration->setToInherit();
            if ($oTemplateConfiguration->save()) {
                App()->setFlashMessage(sprintf(gT("The theme '%s' has been reset."), $templatename), 'success');
            }
            $this->redirect(array("admin/surveysgroups/sa/update", 'id' => $gsid, "#" => "templateSettingsFortThisGroup"));
        }
        TemplateConfiguration::uninstall($templatename);
        TemplateManifest::importManifest($templatename);
        App()->setFlashMessage(sprintf(gT("The theme '%s' has been reset."), $templatename), 'success');
        $this->redirect(array("themeOptions/index"));
    }

    /**
     * Performs the AJAX validation.
     *
     * todo: this function is not in use (there is no class TemplateOptions ...)
     *
     * @param TemplateOptions $model Model to be validated.
     *
     * @return void
     */
    /*
    public function actionPerformAjaxValidation(TemplateOptions $model)
    {
        if (isset($_POST['ajax']) && $_POST['ajax'] === 'template-options-form') {
            echo CActiveForm::validate($model);
            App()->end();
        }
    }*/

    /**
     * Preview Tag.
     *
     * todo: maybe this action should be moved to surveyAdministrationController (it's used in 'General settings')
     *
     * @return string | string[] | null
     * @throws CException
     */
    public function actionGetPreviewTag()
    {
        $templatename = App()->request->getPost('templatename');
        $oTemplate = TemplateConfiguration::getInstanceFromTemplateName($templatename);
        $previewTag = $oTemplate->getPreview();
        return $this->renderPartial(
            '/admin/super/_renderJson',
            ['data' => ['image' =>  $previewTag]],
            false,
            false
        );
    }

    /**
     * Updates Common.
     *
     * @param TemplateConfiguration $model Template Configuration
     * @param int|null $sid Survey ID
     * @param int|null $gsid Survey Group ID
     *
     * @return void
     */
    private function updateCommon(TemplateConfiguration $model, int $sid = null, int $gsid = null)
    {
        /* init the template to current one if option use some twig function (imageSrc for example) mantis #14363 */
        $oTemplate = Template::model()->getInstance($model->template_name, $sid, $gsid);

        $oModelWithInheritReplacement = TemplateConfiguration::model()->findByPk($model->id);
        $aOptionAttributes            = TemplateManifest::getOptionAttributes($oTemplate->path);

        $oTemplate = $oModelWithInheritReplacement->prepareTemplateRendering($oModelWithInheritReplacement->template->name); // Fix empty file lists
        $aTemplateConfiguration = $oTemplate->getOptionPageAttributes();
        App()->clientScript->registerPackage('bootstrap-switch', LSYii_ClientScript::POS_BEGIN);

        if ($aOptionAttributes['optionsPage'] == 'core') {
            App()->clientScript->registerPackage('themeoptions-core');
            $templateOptionPage = '';
        } else {
             $templateOptionPage = $oModelWithInheritReplacement->getOptionPage();
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

        $aData = array(
            'model'              => $model,
            'templateOptionPage' => $templateOptionPage,
            'optionInheritedValues' => $oModelWithInheritReplacement->oOptions,
            'optionCssFiles'        => $oModelWithInheritReplacement->files_css,
            'optionCssFramework'    => $oModelWithInheritReplacement->cssframework_css,
            'aTemplateConfiguration' => $aTemplateConfiguration,
            'aOptionAttributes'      => $aOptionAttributes,
            'oParentOptions'  => $oParentOptions,
            'sPackagesToLoad' => $oModelWithInheritReplacement->packages_to_load,
            'sid' => $sid,
            'gsid' => $gsid
        );

        if ($sid !== null) {
            $aData['surveyid'] = $sid;
            $aData['title_bar']['title'] = gT("Survey theme options");
            $aData['subaction'] = gT("Survey theme options");
            $aData['sidemenu']['landOnSideMenuTab'] = 'settings';
            //buttons in topbar
            $aData['topBar']['showSaveButton'] = true;
            $topbarData = TopbarConfiguration::getSurveyTopbarData($sid);
            $topbarData = array_merge($topbarData, $aData['topBar']);
            $aData['topbar']['middleButtons'] = $this->renderPartial(
                '/surveyAdministration/partial/topbar/surveyTopbarLeft_view',
                $topbarData,
                true
            );
            $aData['topbar']['rightButtons'] = $this->renderPartial(
                '/layouts/partial_topbar/right_close_saveclose_save',
                [
                    'isCloseBtn' => false,
                    'backUrl' => Yii::app()->createUrl('themeOptions'),
                    'isSaveBtn' => true,
                    'isSaveAndCloseBtn' => false,
                    'formIdSave' => 'template-options-form'
                ],
                true
            );
        } else {
            // Title concatenation
            $templateName = $model->template_name;
            $basePageTitle = sprintf('Survey options for theme %s', $templateName);

            if (!is_null($sid)) {
                $addictionalSubtitle = gT(" for survey ID: $sid");
            } elseif (!is_null($gsid)) {
                $addictionalSubtitle = gT(" for survey group id: $gsid");
            } else {
                $addictionalSubtitle = gT(" global level");
            }

            $pageTitle = $basePageTitle . " (" . $addictionalSubtitle . " )";

            $aData['topbar']['title'] = $pageTitle;
            $aData['topbar']['rightButtons'] = $this->renderPartial(
                '/layouts/partial_topbar/right_close_saveclose_save',
                [
                    'isCloseBtn' => true,
                    'backUrl' => Yii::app()->createUrl('themeOptions'),
                    'isSaveBtn' => true,
                    'isSaveAndCloseBtn' => false,
                    'formIdSave' => 'template-options-form'
                ],
                true
            );
        }
        $actionBaseUrl = 'themeOptions/update/';
        $actionUrlArray = ['id' => $model->id];

        if ($model->sid) {
            $actionBaseUrl = 'themeOptions/updateSurvey/';
            unset($actionUrlArray['id']);
            $actionUrlArray['surveyid'] = $model->sid;
            $actionUrlArray['gsid'] = $model->gsid ?  $model->gsid : $gsid;
        }
        if ($model->gsid) {
            $actionBaseUrl = 'themeOptions/updateSurveyGroup/';
            unset($actionUrlArray['id']);
            $actionUrlArray['gsid'] = $model->gsid;
            $actionUrlArray['id'] = $model->id;
        }

        $aData['actionUrl'] = $this->createUrl($actionBaseUrl, $actionUrlArray);

        $this->aData = $aData;
        // here, render update //
        $this->render('update', $aData);
    }

    /**
     * Try to get the get-parameter from request.
     * At the moment there are three namings for a survey ID:
     * 'sid'
     * 'surveyid'
     * 'iSurveyID'
     *
     * Returns the id as integer or null if not exists any of them.
     *
     * @return int | null
     *
     * @todo While refactoring (at some point) this function should be removed and only one unique identifier should be used
     */
    private function getSurveyIdFromGetRequest()
    {
        $surveyId = Yii::app()->request->getParam('sid');
        if ($surveyId === null) {
            $surveyId = Yii::app()->request->getParam('surveyid');
        }
        if ($surveyId === null) {
            $surveyId = Yii::app()->request->getParam('iSurveyID');
        }

        return (int) $surveyId;
    }

    private function getSurveyGroupIdFromGetRequest()
    {
        $surveyGroupId = Yii::app()->request->getParam('gsid');
        if ($surveyGroupId === null) {
            $surveyGroupId = Yii::app()->request->getParam('surveyGroupId');
        }
        return (int) $surveyGroupId;
    }
}
