<?php

namespace ls\tests\unit\services\SurveyUpdater\GeneralSettings;

use ls\tests\TestBaseClass;

use Survey;
use Permission;
use LSYii_Application;
use Mockery;
use LimeSurvey\PluginManager\PluginManager;
use LimeSurvey\Models\Services\SurveyUpdater\{
    GeneralSettings,
    LanguageConsistency
};

class GeneralSettingsUpdateDatesTest extends TestBaseClass
{
    public function testUpdateStartDate()
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
        $survey->setAttributes([
            'sid' => 1,
            'startdate' => '2023-12-01 00:00:00',
        ]);

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

        $surveyUpdate = new GeneralSettings(
            $modelPermission,
            $modelSurvey,
            $yiiApp,
            $pluginManager,
            $languageConsistency
        );

        $surveyUpdate->update(1, [
            'startdate' => '01.01.2024 13:45'
        ]);

        $this->assertEquals('2024-01-01 13:45:00', $survey->startdate);
    }

    public function testUpdateExpiresDate()
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
        $survey->setAttributes([
            'sid' => 1,
            'expires' => '2023-12-01 00:00:00',
        ]);

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

        $surveyUpdate = new GeneralSettings(
            $modelPermission,
            $modelSurvey,
            $yiiApp,
            $pluginManager,
            $languageConsistency
        );

        $surveyUpdate->update(1, [
            'expires' => '01.01.2024 13:45'
        ]);

        $this->assertEquals('2024-01-01 13:45:00', $survey->expires);
    }
}
