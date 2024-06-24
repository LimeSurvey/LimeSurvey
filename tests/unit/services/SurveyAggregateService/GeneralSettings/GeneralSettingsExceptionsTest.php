<?php

namespace ls\tests\unit\services\SurveyAggregateService\GeneralSettings;

use ls\tests\TestBaseClass;

use Mockery;
use Survey;
use Permission;
use LimeSurvey\Models\Services\Exception\{
    PersistErrorException,
    NotFoundException,
    PermissionDeniedException
};

/**
 * @group services
 */
class GeneralSettingsExceptionsTest extends TestBaseClass
{
    /**
     * @testdox update() throws PermissionDeniedException
     */
    public function testThrowsExceptionPermissionDenied()
    {
        $this->expectException(
            PermissionDeniedException::class
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
     * @testdox update() throws NotFoundException is survey not found
     */
    public function testThrowsExceptionNotFoundIfSurveyNotFound()
    {
        $this->expectException(
            NotFoundException::class
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
     * @testdox update() throws PersistErrorException on save failure
     */
    public function testThrowsExceptionPersistErrorOnSaveFailure()
    {
        $this->expectException(
            PersistErrorException::class
        );

        $survey = Mockery::mock(Survey::class)
            ->makePartial();
        $survey->shouldReceive('save')
            ->andReturn(false);

        $mockSetInit = new GeneralSettingsMockSet();
        $mockSetInit->survey = $survey;

        $mockSet = (new GeneralSettingsMockSetFactory)->make($mockSetInit);

        $generalSettings = (new GeneralSettingsFactory)->make($mockSet);

        // Set at least one value so $survey->save() will be called
        $generalSettings->update(1, ['admin' => 'admin']);
    }
}
