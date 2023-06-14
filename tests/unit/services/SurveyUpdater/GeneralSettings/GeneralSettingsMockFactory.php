<?php

namespace ls\tests\unit\services\SurveyUpdater\GeneralSettings;

use Survey;
use Permission;
use LSYii_Application;
use Mockery;
use LimeSurvey\PluginManager\PluginManager;
use LimeSurvey\Models\Services\SurveyUpdater\LanguageConsistency;

class GeneralSettingsMockFactory
{
    /**
     * @param ?GeneralSettingsMockSet $init
     */
    public function make(GeneralSettingsMockSet $init = null): GeneralSettingsMockSet
    {
        $mockSet = new GeneralSettingsMockSet;

        $mockSet->modelPermission = ($init && isset($init->modelPermission))
            ? $init->modelPermission
            : $this->getMockModelPermission();

        $mockSet->survey = ($init && isset($init->survey))
            ? $init->survey
            : $this->getMockSurvey();

        $mockSet->modelSurvey = ($init && isset($init->modelSurvey))
            ? $init->modelSurvey
            : $this->getMockModelSurvey($mockSet->survey);

        $mockSet->yiiApp = ($init && isset($init->yiiApp))
            ? $init->yiiApp
            : $this->getMockYiiApp();

        $mockSet->pluginManager = ($init && isset($init->pluginManager))
            ? $init->pluginManager
            : $this->getMockPluginManager();

        $mockSet->languageConsistency = ($init && isset($init->languageConsistency))
            ? $init->languageConsistency
            : $this->getMockLanguageConsistency();

        return $mockSet;
    }

    private function getMockModelPermission(): Permission
    {
        $modelPermission = Mockery::mock(Permission::class)
            ->makePartial();
        $modelPermission->shouldReceive('hasSurveyPermission')
            ->andReturn(true);
        $modelPermission->shouldReceive('hasGlobalPermission')
            ->andReturn(true);

        return $modelPermission;
    }

    private function getMockSurvey(): Survey
    {
        $survey = Mockery::mock(Survey::class)
            ->makePartial();
        $survey->shouldReceive('save')
            ->andReturn(true);
        $survey->shouldReceive('setAttributes')
            ->passthru();
        $survey->shouldReceive('getAttributes')
            ->passthru();
        $survey->setAttributes([]);

        return $survey;
    }

    private function getMockModelSurvey($survey): Survey
    {
        $modelSurvey = Mockery::mock(Survey::class)
            ->makePartial();
        $modelSurvey->shouldReceive('findByPk')
            ->andReturn($survey);

        return $modelSurvey;
    }

    private function getMockYiiApp(): LSYii_Application
    {
        $yiiApp = Mockery::mock(LSYii_Application::class)
            ->makePartial();

        return $yiiApp;
    }

    private function getMockPluginManager(): PluginManager
    {
        $pluginManager = Mockery::mock(PluginManager::class)
            ->makePartial();
        $pluginManager->shouldReceive('dispatchEvent')
            ->andReturn(null);

        return $pluginManager;
    }

    private function getMockLanguageConsistency(): LanguageConsistency
    {
        $languageConsistency = Mockery::mock(
            LanguageConsistency::class
        )->makePartial();

        return $languageConsistency;
    }
}
