<?php

namespace ls\tests\unit\api\command\v1;

use Eloquent\Phony\Phpunit\Phony;
use ls\tests\TestBaseClass;
use ls\tests\unit\api\command\mixin\AssertResponse;
use LimeSurvey\Api\Command\V1\QuestionGroupDelete;
use LimeSurvey\Api\Command\Request\Request;
use LimeSurvey\Api\Command\Response\Status\StatusErrorNotFound;
use LimeSurvey\Api\ApiSession;

/**
 * Tests for the API command v1 QuestionGroupDelete.
 */
class QuestionGroupDeleteTest extends TestBaseClass
{
    use AssertResponse;

    public function testQuestionGroupDeleteInvalidSession()
    {
        $request = new Request(array(
            'sessionKey' => 'not-a-valid-session-id',
            'groupID' => 'groupID'
        ));
        $response = (new QuestionGroupDelete)->run($request);

        $this->assertResponseInvalidSession($response);
    }

    public function testQuestionGroupDeleteInvalidGroupId()
    {
        $request = new Request(array(
            'sessionKey' => 'mocked',
            'groupID' => '99999999999999999999'
        ));

        $mockApiSessionHandle = Phony::mock(ApiSession::class);
        $mockApiSessionHandle
            ->checkKey
            ->returns(true);
        $mockApiSession = $mockApiSessionHandle->get();

        $command = new QuestionGroupDelete();
        $command->setApiSession($mockApiSession);

        $response = $command->run($request);

        $this->assertResponseStatus($response, new StatusErrorNotFound);
    }
}
