<?php

namespace ls\tests\unit\api\command\v1;

use Permission;
use Survey;
use ls\tests\TestBaseClass;
use ls\tests\unit\api\command\mixin\AssertResponse;
use LimeSurvey\Api\Command\V1\QuestionGroupAdd;
use LimeSurvey\Api\Command\Request\Request;
use LimeSurvey\Api\Command\Response\Status\StatusErrorBadRequest;
use LimeSurvey\Api\Command\Response\Status\StatusErrorUnauthorised;
use LimeSurvey\Api\Auth\AuthSession;
use Mockery;

/**
 * @testdox API command v1 QuestionGroupAdd
 */
class QuestionGroupAddTest extends TestBaseClass
{
    use AssertResponse;

    /**
     * @testdox Returns invalid session response (error unauthorised) if session key is not valid.
     */
    public function testQuestionGroupAddInvalidSession()
    {
        $request = new Request(array(
            'sessionKey' => 'not-a-valid-session-id',
            'surveyID' => 'surveyID',
            'groupTitle' => 'groupTitle',
            'groupDescription' => 'groupDescription',
        ));

        $mockAuthSession = Mockery::mock(AuthSession::class);
        $mockAuthSession
            ->allows()
            ->checkKey('not-a-valid-session-id')
            ->andReturns(false);

        $command = new QuestionGroupAdd();
        $command->setAuthSession($mockAuthSession);

        $response = $command->run($request);

        $this->assertResponseInvalidSession($response);
    }

    /**
     * @testdox Returns invalid session response (error unauthorised) users does not have permission.
     */
    public function testQuestionGroupAddNoPermission()
    {
        $request = new Request(array(
            'sessionKey' => 'mocked',
            'surveyID' => 'surveyID',
            'groupTitle' => 'groupTitle',
            'groupDescription' => 'groupDescription',
        ));

        $mockAuthSession= Mockery::mock(AuthSession::class);
        $mockAuthSession
            ->allows()
            ->checkKey('mocked')
            ->andReturns(true);

        $mockModelPermission= Mockery::mock(Permission::class);
        $mockModelPermission
            ->allows()
            ->hasSurveyPermission(0, 'survey', 'update', null)
            ->andReturns(false);

        $command = new QuestionGroupAdd();
        $command->setAuthSession($mockAuthSession);
        $command->setPermissionModel($mockModelPermission);

        $response = $command->run($request);

        $this->assertResponseStatus(
            $response,
            new StatusErrorUnauthorised()
        );
    }

    /**
     * @testdox Returns error bad-request if survey id is not valid.
     */
    public function testQuestionGroupAddInvalidSurveyId()
    {
        $request = new Request(array(
            'sessionKey' => 'mock',
            'surveyID' => 'surveyID',
            'groupTitle' => 'groupTitle',
            'groupDescription' => 'groupDescription',
        ));

        $mockAuthSession= Mockery::mock(AuthSession::class);
        $mockAuthSession
            ->allows()
            ->checkKey('mock')
            ->andReturns(true);

        $mockModelPermission= Mockery::mock(Permission::class);
        $mockModelPermission
            ->allows()
            ->hasSurveyPermission(0, 'survey', 'update', null)
            ->andReturns(true);

        $command = new QuestionGroupAdd();
        $command->setAuthSession($mockAuthSession);
        $command->setPermissionModel($mockModelPermission);

        $response = $command->run($request);

        $this->assertResponseStatus(
            $response,
            new StatusErrorBadRequest()
        );

        $this->assertResponseDataStatus(
            $response,
            'Error: Invalid survey ID'
        );
    }

    /**
     * @testdox Returns error bad-request if survey is active.
     */
    public function testQuestionGroupAddSurveyActive()
    {
        $request = new Request(array(
            'sessionKey' => 'mock',
            'surveyID' => 'surveyID',
            'groupTitle' => 'groupTitle',
            'groupDescription' => 'groupDescription',
        ));

        $mockAuthSession= Mockery::mock(AuthSession::class);
        $mockAuthSession
            ->allows()
            ->checkKey('mock')
            ->andReturns(true);

        $mockModelPermission= Mockery::mock(Permission::class);
        $mockModelPermission
            ->allows()
            ->hasSurveyPermission(0, 'survey', 'update', null)
            ->andReturns(true);

        $survey = new Survey();
        $survey->setAttributes(array('active' => 'Y'), false);

        $command = new QuestionGroupAdd();
        $command->setAuthSession($mockAuthSession);
        $command->setPermissionModel($mockModelPermission);
        $command->setSurveyModel($survey);

        $response = $command->run($request);

        $this->assertResponseStatus(
            $response,
            new StatusErrorBadRequest()
        );

        $this->assertResponseDataStatus(
            $response,
            'Error: Survey is active and not editable'
        );
    }
}
