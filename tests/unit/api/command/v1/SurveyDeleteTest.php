<?php

namespace ls\tests\unit\api\command\v1;

use Permission;
use ls\tests\TestBaseClass;
use ls\tests\unit\api\command\mixin\AssertResponse;
use LimeSurvey\Api\Command\V1\SurveyDelete;
use LimeSurvey\Api\Command\Request\Request;
use LimeSurvey\Api\Command\Response\Status\StatusErrorBadRequest;
use LimeSurvey\Api\Command\Response\Status\StatusErrorUnauthorised;
use LimeSurvey\Api\Command\Response\Status\StatusSuccess;
use LimeSurvey\Api\ApiSession;
use Survey;
use Mockery;

/**
 * @testdox API command v1 SurveyDelete.
 */
class SurveyDeleteTest extends TestBaseClass
{
    use AssertResponse;

    /**
     * @testdox Returns invalid session response (error unauthorised) if session key is not valid.
     */
    public function testSurveyDeleteTestInvalidSession()
    {
        $request = new Request(array(
            'sessionKey' => 'not-a-valid-session-id',
            'surveyID' => 'surveyID'
        ));
        $response = (new SurveyDelete())->run($request);

        $this->assertResponseInvalidSession($response);
    }

    /**
     * @testdox Returns error unauthorised if user does not have permission.
     */
    public function testSurveyDeleteNoPermission()
    {
        $request = new Request(array(
            'sessionKey' => 'mock',
            'surveyID' => 'surveyID'
        ));

        $mockApiSession= Mockery::mock(ApiSession::class);
        $mockApiSession
            ->allows()
            ->checkKey('mock')
            ->andReturns(true);

        $mockModelPermission= Mockery::mock(Permission::class);
        $mockModelPermission
            ->allows()
            ->hasSurveyPermission(0, 'survey', 'delete', null)
            ->andReturns(false);

        $mockSurveyModel= $this->createStub(Survey::class);

        $command= new SurveyDelete();
        $command->setApiSession($mockApiSession);
        $command->setPermissionModel($mockModelPermission);
        $command->setSurveyModel($mockSurveyModel);

        $response = $command->run($request);

        $this->assertResponseStatus(
            $response,
            new StatusErrorUnauthorised()
        );

        $this->assertResponseDataStatus(
            $response,
            'No permission'
        );
    }

    /**
     * @testdox Returns success with data status response OK.
     */
    public function testSurveyDeleteSuccessReturnsDataStatusOK()
    {
        $request = new Request(array(
            'sessionKey' => 'mock',
            'surveyID' => 'surveyID'
        ));

        $mockApiSession= Mockery::mock(ApiSession::class);
        $mockApiSession
            ->allows()
            ->checkKey('mock')
            ->andReturns(true);

        $mockModelPermission= Mockery::mock(Permission::class);
        $mockModelPermission
            ->allows()
            ->hasSurveyPermission(0, 'survey', 'delete', null)
            ->andReturns(true);

        $mockSurveyModel= $this->createStub(Survey::class);

        $command= new SurveyDelete();
        $command->setApiSession($mockApiSession);
        $command->setPermissionModel($mockModelPermission);

        $response = $command->run($request);

        $this->assertResponseStatus(
            $response,
            new StatusSuccess()
        );

        $this->assertResponseDataStatus(
            $response,
            'OK'
        );
    }
}
