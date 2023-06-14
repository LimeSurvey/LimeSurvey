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
    public static function make()
    {
        $modelPermission = Mockery::mock(Permission::class)
            ->makePartial();
        $modelPermission->shouldReceive('hasSurveyPermission')
            ->andReturn(true);
        $modelPermission->shouldReceive('hasGlobalPermission')
            ->andReturn(true);

        $survey = Mockery::mock(Survey::class)
            ->makePartial();
        $survey->shouldReceive('save')
            ->andReturn(true);
        $survey->shouldReceive('setAttributes')
            ->passthru();
        $survey->shouldReceive('getAttributes')
            ->passthru();
        $survey->setAttributes([]);

        $modelSurvey = Mockery::mock(Survey::class)
            ->makePartial();
        $modelSurvey->shouldReceive('findByPk')
            ->andReturn($survey);

        $yiiApp = Mockery::mock(LSYii_Application::class)
            ->makePartial();

        $pluginManager = Mockery::mock(PluginManager::class)
            ->makePartial();
        $pluginManager->shouldReceive('dispatchEvent')
            ->andReturn(null);

        $languageConsistency = Mockery::mock(LanguageConsistency::class)
            ->makePartial();

        return (object) [
            'modelPermission' => $modelPermission,
            'survey' => $survey,
            'modelSurvey' => $modelSurvey,
            'yiiApp' => $yiiApp,
            'pluginManager' => $pluginManager,
            'languageConsistency' => $languageConsistency
        ];
    }
}
