<?php

namespace ls\tests\unit\api\command;

use CHttpSession;
use LimeSurvey\Api\Command\Request\Request;
use LimeSurvey\Api\Command\Response\ResponseFactory;
use LimeSurvey\Api\Command\Response\Status\StatusErrorForbidden;
use LimeSurvey\Api\Command\Response\Status\StatusSuccess;
use LimeSurvey\Api\Command\V1\SurveyTemplate;
use ls\tests\TestBaseClass;
use ls\tests\unit\api\command\mixin\AssertResponse;
use Mockery;
use Permission;
use Survey;
use SurveyLanguageSetting;

/**
 * Tests for the access checks of the survey-template REST command.
 */
class SurveyTemplateTest extends TestBaseClass
{
    use AssertResponse;

    /**
     * An unknown survey id must give a 403 like a missing permission does,
     * so that users cannot probe which survey ids exist.
     */
    public function testUnknownSurveyGivesForbidden(): void
    {
        $survey = Mockery::mock(Survey::class)->makePartial();
        $survey->shouldNotReceive('findByPk');

        // Permission::hasSurveyPermission() returns false for unknown surveys
        $permission = Mockery::mock(Permission::class)->makePartial();
        $permission->shouldReceive('hasSurveyPermission')
            ->with(123, 'surveycontent', 'read', null)
            ->andReturn(false);

        $command = $this->makeCommand($survey, $permission);
        $response = $command->run(new Request(['_id' => 123]));

        $this->assertResponseStatus($response, new StatusErrorForbidden());
    }

    /**
     * A user without read permission on the survey content must get a 403.
     */
    public function testMissingPermissionGivesForbidden(): void
    {
        $survey = Mockery::mock(Survey::class)->makePartial();
        $survey->shouldReceive('findByPk')->with(123)->andReturn($survey);

        $permission = Mockery::mock(Permission::class)->makePartial();
        $permission->shouldReceive('hasSurveyPermission')
            ->with(123, 'surveycontent', 'read', null)
            ->andReturn(false);

        $command = $this->makeCommand($survey, $permission);
        $response = $command->run(new Request(['_id' => 123]));

        $this->assertResponseStatus($response, new StatusErrorForbidden());
    }

    /**
     * A user with read permission on the survey content gets the template.
     */
    public function testPermissionGivesSuccess(): void
    {
        $survey = Mockery::mock(Survey::class)->makePartial();
        $survey->shouldReceive('findByPk')->with(123)->andReturn($survey);
        $survey->language = 'en';

        $permission = Mockery::mock(Permission::class)->makePartial();
        $permission->shouldReceive('hasSurveyPermission')
            ->with(123, 'surveycontent', 'read', null)
            ->andReturn(true);

        $languageSetting = Mockery::mock(SurveyLanguageSetting::class);
        $languageSetting->shouldReceive('find')->andReturn(null);

        // The template is fetched over HTTP, so that step is stubbed
        $command = Mockery::mock(SurveyTemplate::class, [
            new ResponseFactory(),
            Mockery::mock(CHttpSession::class),
            $survey,
            $languageSetting
        ])->makePartial()->shouldAllowMockingProtectedMethods();
        $command->shouldReceive('getTemplateData')
            ->with(123, 'en')
            ->andReturn('<p>Survey</p>');
        $command->setPermissionModel($permission);

        $response = $command->run(new Request(['_id' => 123]));

        $this->assertResponseStatus($response, new StatusSuccess());
        $this->assertSame(['template' => '<p>Survey</p>'], $response->getData());
    }

    /**
     * Build the command with mocked collaborators
     *
     * @param Survey $survey
     * @param Permission $permission
     * @return SurveyTemplate
     */
    private function makeCommand(Survey $survey, Permission $permission): SurveyTemplate
    {
        $command = new SurveyTemplate(
            new ResponseFactory(),
            Mockery::mock(CHttpSession::class),
            $survey,
            Mockery::mock(SurveyLanguageSetting::class)
        );
        $command->setPermissionModel($permission);
        return $command;
    }
}
