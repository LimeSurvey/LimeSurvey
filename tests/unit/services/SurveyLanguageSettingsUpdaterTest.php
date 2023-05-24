<?php

namespace ls\tests;

use ls\tests\TestBaseClass;

use Survey;
use Permission;
use SurveyLanguageSetting;
use Mockery;
use LimeSurvey\Models\Services\SurveyLanguageSettingsUpdater;
use LimeSurvey\Models\Services\Exception\{
    ExceptionPersistError,
    ExceptionNotFound,
    ExceptionPermissionDenied
};

class SurveyLanguageSettingsUpdaterTest extends TestBaseClass
{
    /**
     * @testdox update() throws ExceptionPermissionDenied if no survey update permission
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

        $surveyUpdate = new SurveyLanguageSettingsUpdater;
        $surveyUpdate->setModelPermission($modelPermission);

        $surveyUpdate->update(1, []);
    }

    /**
     * @testdox update() throws ExceptionNotFound if survey not found
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

        $surveyUpdate = new SurveyLanguageSettingsUpdater;
        $surveyUpdate->setModelPermission($modelPermission);
        $surveyUpdate->setModelSurvey($modelSurvey);

        $surveyUpdate->update(1, []);
    }

    /**
     * @testdox update() throws ExceptionNotFound if language setting not found
     */
    public function testThrowsExceptionNotFoundIfLanguageSettingNotFound()
    {
        $this->expectException(
            ExceptionNotFound::class
        );

        $survey = Mockery::mock(Survey::class)->makePartial();
        $survey->sid = 1;
        $survey->language = 'en';
        // return empty array for additionalLanguages relation
        $survey->shouldReceive('getRelated')
            ->andReturn([]);

        $modelPermission = Mockery::mock(Permission::class)
            ->makePartial();
        $modelPermission
            ->shouldReceive('hasSurveyPermission')
            ->andReturn(true);

        $modelSurvey = Mockery::mock(Survey::class)
            ->makePartial();
        $modelSurvey->shouldReceive('findByPk')
            ->andReturn($survey);

        $modelSurveyLanguageSetting = Mockery::mock(
            SurveyLanguageSetting::class
            )->makePartial();
        $modelSurveyLanguageSetting
            ->shouldReceive('findByPk')
            ->andReturn(null);

        $surveyUpdate = new SurveyLanguageSettingsUpdater;
        $surveyUpdate->setModelPermission($modelPermission);
        $surveyUpdate->setModelSurvey($modelSurvey);
        $surveyUpdate->setModelSurveyLanguageSetting(
            $modelSurveyLanguageSetting
        );

        $surveyUpdate->update(1, [
            'en' => ['url_description' => 'test']
        ]);
    }
}
