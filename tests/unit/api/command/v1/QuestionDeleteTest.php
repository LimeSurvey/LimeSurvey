<?php

namespace ls\tests\unit\api\command\v1;

use ls\tests\TestBaseClass;
use ls\tests\unit\api\command\mixin\AssertInvalidSession;
use LimeSurvey\Api\Command\V1\QuestionDelete;
use LimeSurvey\Api\Command\Request\Request;

/**
 * Tests for the API command v1 QuestionDelete.
 */
class QuestionDeleteTest extends TestBaseClass
{
    use AssertInvalidSession;

    public function testQuestionGroupAddInvalidSession()
    {
        $request = new Request(array(
            'sessionKey' => 'not-a-valid-session-id',
            'questionID' => 'questionID'
        ));
        $response = (new QuestionDelete)->run($request);

        $this->assertInvalidSession($response);
    }
}
