<?php

namespace ls\tests\unit\api\command\v1;

use Eloquent\Phony\Phpunit\Phony;
use Permission;
use ls\tests\TestBaseClass;
use ls\tests\unit\api\command\mixin\AssertInvalidSession;
use LimeSurvey\Api\Command\V1\QuestionGroupAdd;
use LimeSurvey\Api\Command\Request\Request;
use LimeSurvey\Api\ApiSession;

/**
 * Tests for the API command v1 QuestionGroupAdd.
 */
class QuestionGroupAddTest extends TestBaseClass
{
    use AssertInvalidSession;

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

        $this->assertInvalidSession($response);
    }

    public function testQuestionGroupAddUnauthorised()
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

        $mockModelPermissionHandle = Phony::mock(Permission::class);
        $mockModelPermissionHandle->hasSurveyPermission
            ->returns(false);
        $mockModelPermission = $mockModelPermissionHandle->get();

        $command = new QuestionGroupAdd();
        $command->setApiSession($mockApiSession);
        $command->setPermissionModel($mockModelPermission);

        $response = $command->run($request);

        $this->assertInvalidSession($response);
    }
}
