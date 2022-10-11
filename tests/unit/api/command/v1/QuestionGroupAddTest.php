<?php

namespace ls\tests\unit\api\command\v1;

use Eloquent\Phony\Phpunit\Phony;
use Permission;
use ls\tests\TestBaseClass;
use ls\tests\unit\api\command\mixin\AssertResponse;
use LimeSurvey\Api\Command\V1\QuestionGroupAdd;
use LimeSurvey\Api\Command\Request\Request;
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
            new StatusErrorUnauthorised
        );
    }
}
