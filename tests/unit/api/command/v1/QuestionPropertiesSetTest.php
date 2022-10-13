<?php

namespace ls\tests\unit\api\command\v1;

use ls\tests\TestBaseClass;
use ls\tests\unit\api\command\mixin\AssertResponse;
use LimeSurvey\Api\Command\V1\QuestionPropertiesSet;
use LimeSurvey\Api\Command\Request\Request;

/**
 * @testdox API command v1 QuestionPropertiesSet.
 */
class QuestionPropertiesSetTest extends TestBaseClass
{
    use AssertResponse;

    /**
     * @testdox Returns invalid session response (error unauthorised) if session key is not valid.
     */
    public function testQuestionListInvalidSession()
    {
        $request = new Request(array(
            'sessionKey' => 'not-a-valid-session-id',
            'questionID' => 'questionID',
            'questionData' => 'questionData',
            'language' => 'language'
        ));
        $response = (new QuestionPropertiesSet)->run($request);

        $this->assertResponseInvalidSession($response);
    }
}
