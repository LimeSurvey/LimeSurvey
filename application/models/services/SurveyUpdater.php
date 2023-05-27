<?php

namespace LimeSurvey\Models\Services;

use Survey;
use Permission;
use SurveyURLParameter;
use SurveyLanguageSetting;
use LSYii_Application;
use LimeSurvey\PluginManager\PluginManager;
use LimeSurvey\Models\Services\Exception\{
    ExceptionPersistError,
    ExceptionNotFound,
    ExceptionPermissionDenied
};

/**
 * Service SurveyUpdater
 *
 * Service class for update survey settings.
 *
 * Dependencies are injected to enable mocking.
 */
class SurveyUpdater
{
    private ?Permission $modelPermission = null;
    private ?Survey $modelSurvey = null;
    private ?SurveyURLParameter $modelSurveyUrlParameter = null;
    private ?SurveyLanguageSetting $modelSurveyLanguageSetting = null;
    private ?LSYii_Application $yiiApp = null;
    private ?PluginManager $yiiPluginManager = null;

    public function __construct()
    {
        $this->modelPermission = Permission::model();
        $this->modelSurvey = Survey::model();
        $this->modelSurveyUrlParameter = SurveyURLParameter::model();
        $this->modelSurveyLanguageSetting = SurveyLanguageSetting::model();
        $this->yiiApp = App();
        $this->yiiPluginManager = App()->getPluginManager();
    }

    /**
     * Update
     *
     * @param int $surveyId
     * @param array $input
     * @throws ExceptionPersistError
     * @throws ExceptionNotFound
     * @throws ExceptionPermissionDenied
     * @return boolean
     */
    public function update($surveyId, array $input)
    {
        $surveyUpdaterLanguageSettings = new SurveyUpdaterLanguageSettings;
        $surveyUpdaterLanguageSettings->setModelPermission(
            $this->modelPermission
        );
        $surveyUpdaterLanguageSettings->setModelSurvey(
            $this->modelSurvey
        );
        $surveyUpdaterLanguageSettings->setModelSurveyLanguageSetting(
            $this->modelSurveyLanguageSetting
        );
        $surveyUpdaterLanguageSettings->update(
            $surveyId,
            $input
        );

        $surveyUpdaterGeneralSettings = new SurveyUpdaterGeneralSettings;
        $surveyUpdaterGeneralSettings->setModelPermission(
            $this->modelPermission
        );
        $surveyUpdaterGeneralSettings->setModelSurvey(
            $this->modelSurvey
        );
        $surveyUpdaterGeneralSettings->setYiiApp(
            $this->yiiApp
        );
        $surveyUpdaterGeneralSettings->setYiiPluginManager(
            $this->yiiPluginManager
        );
        $surveyUpdaterGeneralSettings->update(
            $surveyId,
            $input
        );

        if (!empty($input['url_params'])) {
            $surveyUpdaterUrlParams = new SurveyUpdaterUrlParams;
            $surveyUpdaterUrlParams->setModelSurveyUrlParameter(
                $this->modelSurveyUrlParameter
            );
            $surveyUpdaterUrlParams->update(
                $surveyId,
                $input['url_params']
            );
        }

        return true;
    }

    /**
     * Set model Permission
     *
     * Dependency injection of Permission::model().
     *
     * @param Permission $model
     * @return void
     */
    public function setModelPermission(Permission $model)
    {
        $this->modelPermission = $model;
    }

    /**
     * Set model Survey
     *
     * Dependency injection of Survey::model().
     *
     * @param Survey $model
     * @return void
     */
    public function setModelSurvey(Survey $model)
    {
        $this->modelSurvey = $model;
    }

    /**
     * Set model SurveyURLParameter
     *
     * Dependency injection of SurveyURLParameter::model().
     *
     * @param SurveyURLParameter $model
     * @return void
     */
    public function setModelSurveyUrlParameter(SurveyURLParameter $model)
    {
        $this->modelSurveyUrlParameter = $model;
    }

    /**
     * Set model SurveyLanguageSetting
     *
     * Dependency injection of SurveyLanguageSetting::model().
     *
     * @param SurveyLanguageSetting $model
     * @return void
     */
    public function setModelSurveyLanguageSetting(SurveyLanguageSetting $model)
    {
        $this->modelSurveyLanguageSetting = $model;
    }

    /**
     * Set Yii App
     *
     * Dependency injection of LSYii_Application.
     *
     * @param SurveyURLParameter $model
     * @return void
     */
    public function setYiiApp(LSYii_Application $app)
    {
        $this->yiiApp = $app;
    }

    /**
     * Set Yii PluginManager
     *
     * Dependency injection of PluginManager.
     *
     * @param PluginManager $model
     * @return void
     */
    public function setYiiPluginManager(PluginManager $pluginManager)
    {
        $this->yiiPluginManager = $pluginManager;
    }
}
