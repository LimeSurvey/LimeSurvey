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
use LimeSurvey\Models\Services\Exception\{
    ExceptionPersistError,
    ExceptionNotFound,
    ExceptionPermissionDenied
};

class GeneralSettingsExceptionsTest extends TestBaseClass
{
    /**
     * @testdox update() throws ExceptionPermissionDenied
     */
    public function testThrowsExceptionPermissionDenied()
    {
        $this->expectException(
            ExceptionPermissionDenied::class
        );

        $modelPermission = Mockery::mock(Permission::class)
            ->makePartial();
        $modelPermission->shouldReceive('hasSurveyPermission')
            ->andReturn(false);

        $modelSurvey = Mockery::mock(Survey::class)
            ->makePartial();

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

        $surveyUpdate->update(1, []);
    }

    /**
     * @testdox update() throws ExceptionNotFound is survey not found
     */
    public function testThrowsExceptionNotFoundIfSurveyNotFound()
    {
        $this->expectException(
            ExceptionNotFound::class
        );

        $modelPermission = Mockery::mock(Permission::class)
            ->makePartial();
        $modelPermission->shouldReceive('hasSurveyPermission')
            ->andReturn(true);

        $modelSurvey = Mockery::mock(Survey::class)
            ->makePartial();
        $modelSurvey->shouldReceive('findByPk')
            ->andReturn(null);

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

        $surveyUpdate->update(1, []);
    }

    /**
     * @testdox update() throws ExceptionPersistError on save failure
     */
    public function testThrowsExceptionPersistErrorOnSaveFailure()
    {
        $this->expectException(
            ExceptionPersistError::class
        );

        $survey = Mockery::mock(Survey::class)->makePartial();
        $survey
            ->shouldReceive('save')
            ->andReturn(false);

        $modelPermission = Mockery::mock(Permission::class)
            ->makePartial();
        $modelPermission
            ->shouldReceive('hasSurveyPermission')
            ->andReturn(true);

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

        $surveyUpdate->update(1, ['owner_id' => 99999]);
    }
}
