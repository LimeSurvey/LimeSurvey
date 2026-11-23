<?php

namespace ls\tests\unit\api\command;

use CHttpSession;
use LimeSurvey\Api\Command\Request\Request;
use LimeSurvey\Api\Command\Response\ResponseFactory;
use LimeSurvey\Api\Command\Response\Status\StatusErrorForbidden;
use LimeSurvey\Api\Command\Response\Status\StatusSuccess;
use LimeSurvey\Api\Command\V1\SurveyArchive;
use ls\tests\TestBaseClass;
use ls\tests\unit\api\command\mixin\AssertResponse;
use Mockery;
use Permission;
use Survey;

/**
 * Tests for the access checks of the survey-archive REST command.
 */
class SurveyArchiveTest extends TestBaseClass
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
     * A user with read permission on the survey content gets the archive list.
     */
    public function testPermissionGivesSuccess(): void
    {
        $survey = Mockery::mock(Survey::class)->makePartial();
        $survey->shouldReceive('findByPk')->with(123)->andReturn($survey);
        $survey->shouldReceive('getIsActive')->andReturn(false);

        $permission = Mockery::mock(Permission::class)->makePartial();
        $permission->shouldReceive('hasSurveyPermission')
            ->with(123, 'surveycontent', 'read', null)
            ->andReturn(true);

        $command = $this->makeCommand($survey, $permission);
        $response = $command->run(new Request(['_id' => 123]));

        $this->assertResponseStatus($response, new StatusSuccess());
        $this->assertIsArray($response->getData());
    }

    /**
     * Build the command with mocked collaborators
     *
     * @param Survey $survey
     * @param Permission $permission
     * @return SurveyArchive
     */
    private function makeCommand(Survey $survey, Permission $permission): SurveyArchive
    {
        $command = new SurveyArchive(
            Mockery::mock(CHttpSession::class),
            new ResponseFactory(),
            $survey
        );
        $command->setPermissionModel($permission);
        return $command;
    }
}
