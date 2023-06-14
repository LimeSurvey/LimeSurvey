<?php

namespace ls\tests\SurveyUpdater;

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

class GeneralSettingsUpdateBooleanTest extends TestBaseClass
{
    public function testUpdateAllowRegisterSetTrue()
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
            'allowregister' => 'N',
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
            'allowregister' => '1'
        ]);

        $this->assertEquals('Y', $survey->allowregister);
    }

    public function testUpdateAllowRegisterSetFalse()
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
            'allowregister' => 'Y',
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
            'allowregister' => '0'
        ]);

        $this->assertEquals('N', $survey->allowregister);
    }

    public function testUpdateAllowRegisterSetInherit()
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
            'allowregister' => 'Y',
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
            'allowregister' => 'I'
        ]);

        $this->assertEquals('I', $survey->allowregister);
    }
}
