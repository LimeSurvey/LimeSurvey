<?php

namespace ls\tests\unit\api\command\v1;

use Permission;
use QuestionGroup;
use Survey;
use ls\tests\TestBaseClass;
use ls\tests\unit\api\command\mixin\AssertResponse;
use LimeSurvey\Api\Command\V1\QuestionGroupDelete;
use LimeSurvey\Api\Command\Request\Request;
use LimeSurvey\Api\Command\Response\Status\StatusErrorNotFound;
use LimeSurvey\Api\Command\Response\Status\StatusErrorUnauthorised;
use LimeSurvey\Api\AuthSession;
use Mockery;

/**
 * @testdox API command v1 QuestionGroupDelete
 */
class QuestionGroupDeleteTest extends TestBaseClass
{
    use AssertResponse;

    /**
     * @testdox Returns invalid session response (error unauthorised) if session key is not valid.
     */
    public function testQuestionGroupDeleteInvalidSession()
    {
        $request = new Request(array(
            'sessionKey' => 'not-a-valid-session-id',
            'groupID' => 'groupID'
        ));
        $response = (new QuestionGroupDelete())->run($request);

        $this->assertResponseInvalidSession($response);
    }

    /**
     * @testdox Returns error not-found if group id is not valid.
     */
    public function testQuestionGroupDeleteInvalidGroupId()
    {
        $request = new Request(array(
            'sessionKey' => 'mocked',
            'groupID' => '99999999999999999999'
        ));

        $mockAuthSession= Mockery::mock(AuthSession::class);
        $mockAuthSession
            ->allows()
            ->checkKey('mocked')
            ->andReturns(true);

        $command = new QuestionGroupDelete();
        $command->setAuthSession($mockAuthSession);

        $response = $command->run($request);

        $this->assertResponseStatus(
            $response,
            new StatusErrorNotFound()
        );

        $this->assertResponseDataStatus(
            $response,
            'Error:Invalid group ID'
        );
    }

    /**
     * @testdox Returns invalid session response (error unauthorised) users does not have permission.
     */
    public function testQuestionGroupDeleteNoPermission()
    {
        $request = new Request(array(
            'sessionKey' => 'mocked',
            'groupID' => '1'
        ));

        $mockAuthSession= $this->getMockBuilder(AuthSession::class)
            ->setMethods(['checkKey'])
            ->getMock();
        $mockAuthSession->method('checkKey')->willReturn(true);

        $mockModelGroup  = $this->createStub(QuestionGroup::class);
        $mockModelSurvey = $this->createStub(Survey::class);

        $mockModelPermission = $this->createStub(Permission::class);
        $mockModelPermission
            ->method('hasSurveyPermission')
            ->willReturn(false);

        $command = new QuestionGroupDelete();
        $command->setQuestionGroupModel($mockModelGroup);
        $command->setSurveyModel($mockModelSurvey);
        $command->setAuthSession($mockAuthSession);
        $command->setPermissionModel($mockModelPermission);

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
}
