<?php

namespace ls\tests;

use ls\tests\TestBaseClass;

use Survey;
use Permission;
use Mockery;
use LimeSurvey\PluginManager\PluginManager;
use LimeSurvey\Models\Services\SurveyUpdaterGeneralSettings;
use LimeSurvey\Models\Services\Exception\{
    ExceptionPersistError,
    ExceptionNotFound,
    ExceptionPermissionDenied
};

class SurveyUpdaterGeneralSettingsTest extends TestBaseClass
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

        $pluginManager = Mockery::mock(PluginManager::class)
            ->makePartial();
        $pluginManager->shouldReceive('dispatchEvent')
            ->andReturn(null);

        $surveyUpdate = new SurveyUpdaterGeneralSettings;
        $surveyUpdate->setModelPermission($modelPermission);
        $surveyUpdate->setYiiPluginManager($pluginManager);

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

        $pluginManager = Mockery::mock(PluginManager::class)
            ->makePartial();
        $pluginManager->shouldReceive('dispatchEvent')
            ->andReturn(null);

        $surveyUpdate = new SurveyUpdaterGeneralSettings;
        $surveyUpdate->setModelPermission($modelPermission);
        $surveyUpdate->setModelSurvey($modelSurvey);
        $surveyUpdate->setYiiPluginManager($pluginManager);

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

        $pluginManager = Mockery::mock(PluginManager::class)
            ->makePartial();
        $pluginManager->shouldReceive('dispatchEvent')
            ->andReturn(null);

        $surveyUpdate = new SurveyUpdaterGeneralSettings;
        $surveyUpdate->setModelPermission($modelPermission);
        $surveyUpdate->setModelSurvey($modelSurvey);
        $surveyUpdate->setYiiPluginManager($pluginManager);

        $surveyUpdate->update(1, ['owner_id' => 99999]);
    }
}
