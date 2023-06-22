<?php

namespace ls\tests\unit\services\SurveyUpdater\GeneralSettings;

use ls\tests\TestBaseClass;

use Mockery;
use Survey;
use Permission;
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

        $mockSet = (new GeneralSettingsMockSetFactory)->make();
        $mockSet->modelPermission = $modelPermission;

        $generalSettings = (new GeneralSettingsFactory)->make($mockSet);

        $generalSettings->update(1, []);
    }

    /**
     * @testdox update() throws ExceptionNotFound is survey not found
     */
    public function testThrowsExceptionNotFoundIfSurveyNotFound()
    {
        $this->expectException(
            ExceptionNotFound::class
        );

        $modelSurvey = Mockery::mock(Survey::class)
            ->makePartial();
        $modelSurvey->shouldReceive('findByPk')
            ->andReturn(null);

        $mockSet = (new GeneralSettingsMockSetFactory)->make();
        $mockSet->modelSurvey = $modelSurvey;

        $generalSettings = (new GeneralSettingsFactory)->make($mockSet);

        $generalSettings->update(1, []);
    }

    /**
     * @testdox update() throws ExceptionPersistError on save failure
     */
    public function testThrowsExceptionPersistErrorOnSaveFailure()
    {
        $this->expectException(
            ExceptionPersistError::class
        );

        $survey = Mockery::mock(Survey::class)
            ->makePartial();
        $survey->shouldReceive('save')
            ->andReturn(false);

        $mockSetInit = new GeneralSettingsMockSet();
        $mockSetInit->survey = $survey;

        $mockSet = (new GeneralSettingsMockSetFactory)->make($mockSetInit);

        $generalSettings = (new GeneralSettingsFactory)->make($mockSet);

        $generalSettings->update(1, ['owner_id' => 99999]);
    }
}
