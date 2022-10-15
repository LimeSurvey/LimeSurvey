<?php

namespace ls\tests\unit\api\command\v1;

use Eloquent\Phony\Phpunit\Phony;
use Permission;
use Survey;
use ls\tests\TestBaseClass;
use ls\tests\unit\api\command\mixin\AssertResponse;
use LimeSurvey\Api\Command\V1\QuestionGroupAdd;
use LimeSurvey\Api\Command\Request\Request;
use LimeSurvey\Api\Command\Response\Status\StatusErrorBadRequest;
use LimeSurvey\Api\Command\Response\Status\StatusErrorUnauthorised;
use LimeSurvey\Api\ApiSession;

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

        $mockApiSessionHandle = Phony::mock(ApiSession::class);
        $mockApiSessionHandle
            ->checkKey
            ->returns(false);
        $mockApiSession = $mockApiSessionHandle->get();

        $command = new QuestionGroupAdd();
        $command->setApiSession($mockApiSession);

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

        $mockApiSessionHandle = Phony::mock(ApiSession::class);
        $mockApiSessionHandle
            ->checkKey
            ->returns(true);
        $mockApiSession = $mockApiSessionHandle->get();

        $mockModelPermissionHandle = Phony::mock(Permission::class);
        $mockModelPermissionHandle->hasSurveyPermission
            ->returns(false);
        $mockModelPermission = $mockModelPermissionHandle->get();

        $command = new QuestionGroupAdd();
        $command->setApiSession($mockApiSession);
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

        $mockApiSessionHandle = Phony::mock(ApiSession::class);
        $mockApiSessionHandle
            ->checkKey
            ->returns(true);
        $mockApiSession = $mockApiSessionHandle->get();

        $mockModelPermissionHandle = Phony::mock(Permission::class);
        $mockModelPermissionHandle->hasSurveyPermission
            ->returns(true);
        $mockModelPermission = $mockModelPermissionHandle->get();

        $command = new QuestionGroupAdd();
        $command->setApiSession($mockApiSession);
        $command->setPermissionModel($mockModelPermission);

        $response = $command->run($request);

        $this->assertResponseStatus(
            $response,
            new StatusErrorBadRequest
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

        $mockApiSessionHandle = Phony::mock(ApiSession::class);
        $mockApiSessionHandle
            ->checkKey
            ->returns(true);
        $mockApiSession = $mockApiSessionHandle->get();

        $mockModelPermissionHandle = Phony::mock(Permission::class);
        $mockModelPermissionHandle->hasSurveyPermission
            ->returns(true);
        $mockModelPermission = $mockModelPermissionHandle->get();

        $survey = new Survey();
        $survey->setAttributes(array('active' => 'Y'), false);

        $command = new QuestionGroupAdd();
        $command->setApiSession($mockApiSession);
        $command->setPermissionModel($mockModelPermission);
        $command->setSurveyModel( $survey);

        $response = $command->run($request);

        $this->assertResponseStatus(
            $response,
            new StatusErrorBadRequest
        );

        $this->assertResponseDataStatus(
            $response,
            'Error: Survey is active and not editable'
        );
    }
}
