<?php

namespace ls\tests\SurveyAggregateService;

use ls\tests\TestBaseClass;

use Survey;
use Permission;
use SurveyLanguageSetting;
use Mockery;
use LimeSurvey\Models\Services\SurveyAggregateService\LanguageSettings;
use LimeSurvey\Models\Services\Exception\{
    PersistErrorException,
    NotFoundException,
    PermissionDeniedException
};

/**
 * @group services
 */
class LanguageSettingsExceptionsTest extends TestBaseClass
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

        $modelSurvey = Mockery::mock(Survey::class)
            ->makePartial();

        $modelSurveyLanguageSetting = Mockery::mock(
            SurveyLanguageSetting::class
        )->makePartial();

        $surveyUpdater = new LanguageSettings(
            $modelPermission,
            $modelSurvey,
            $modelSurveyLanguageSetting
        );

        $surveyUpdater->update(1, []);
    }

    /**
     * @testdox update() throws NotFoundException if survey not found
     */
    public function testThrowsExceptionNotFoundIfSurveyNotFound()
    {
        $this->expectException(
            NotFoundException::class
        );

        $modelPermission = Mockery::mock(Permission::class)
            ->makePartial();
        $modelPermission->shouldReceive('hasSurveyPermission')
            ->andReturn(true);

        $modelSurvey = Mockery::mock(Survey::class)
            ->makePartial();
        $modelSurvey->shouldReceive('findByPk')
            ->andReturn(null);

        $modelSurveyLanguageSetting = Mockery::mock(
            SurveyLanguageSetting::class
        )->makePartial();

        $surveyUpdater = new LanguageSettings(
            $modelPermission,
            $modelSurvey,
            $modelSurveyLanguageSetting
        );

        $surveyUpdater->update(1, []);
    }

    /**
     * @testdox update() throws NotFoundException if language setting not found
     */
    public function testThrowsExceptionNotFoundIfLanguageSettingNotFound()
    {
        $this->expectException(
            NotFoundException::class
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

        $surveyUpdater = new LanguageSettings(
            $modelPermission,
            $modelSurvey,
            $modelSurveyLanguageSetting
        );

        $surveyUpdater->update(1, [
            'en' => ['surveyls_urldescription' => 'test']
        ]);
    }

    /**
     * @testdox update() throws PersistErrorException on save failure
     */
    public function testThrowsExceptionPersistErrorOnSaveFailure()
    {
        $this->expectException(
            PersistErrorException::class
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

        $surveyLanguageSetting = Mockery::mock(
                SurveyLanguageSetting::class
            )->makePartial();
        $surveyLanguageSetting->shouldReceive('save')
            ->andReturn(false);

        $modelSurveyLanguageSetting = Mockery::mock(
            SurveyLanguageSetting::class
            )->makePartial();
        $modelSurveyLanguageSetting
            ->shouldReceive('findByPk')
            ->andReturn($surveyLanguageSetting);

        $surveyUpdater = new LanguageSettings(
            $modelPermission,
            $modelSurvey,
            $modelSurveyLanguageSetting
        );

        $surveyUpdater->update(1, [
            'en' => ['surveyls_urldescription' => 'test']
        ]);
    }

    /**
     * @testdox update()
     */
    public function testUpdate()
    {
        $survey = Mockery::mock(Survey::class)->makePartial();
        $survey->sid = 1;
        $survey->language = 'en';
        // return empty array for additionalLanguages relation
        $survey->shouldReceive('getRelated')
            ->andReturn(['de']);

        $modelPermission = Mockery::mock(Permission::class)
            ->makePartial();
        $modelPermission
            ->shouldReceive('hasSurveyPermission')
            ->andReturn(true);

        $modelSurvey = Mockery::mock(Survey::class)
            ->makePartial();
        $modelSurvey->shouldReceive('findByPk')
            ->andReturn($survey);

        $surveyLanguageSetting = Mockery::mock(
                SurveyLanguageSetting::class
            )->makePartial();
        $surveyLanguageSetting->shouldReceive('save')
            ->andReturn(true);

        $modelSurveyLanguageSetting = Mockery::mock(
            SurveyLanguageSetting::class
            )->makePartial();
        $modelSurveyLanguageSetting
            ->shouldReceive('findByPk')
            ->andReturn($surveyLanguageSetting);

        $surveyUpdater = new LanguageSettings(
            $modelPermission,
            $modelSurvey,
            $modelSurveyLanguageSetting
        );

        $result = $surveyUpdater->update(1, [
            'en' => ['surveyls_urldescription' => 'test'],
            'de' => ['surveyls_urldescription' => 'test'],
        ]);

        $this->assertEquals(true, $result);
    }
}
