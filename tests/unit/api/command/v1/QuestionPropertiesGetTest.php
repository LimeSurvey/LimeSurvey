<?php

namespace ls\tests\unit\api\command\v1;

use ls\tests\TestBaseClass;
use ls\tests\unit\api\command\mixin\AssertInvalidSession;
use LimeSurvey\Api\Command\V1\QuestionPropertiesGet;
use LimeSurvey\Api\Command\Request\Request;

/**
 * Tests for the API command v1 QuestionPropertiesGet.
 */
class QuestionPropertiesGetTest extends TestBaseClass
{
    use AssertInvalidSession;

    public function testQuestionListInvalidSession()
    {
        $request = new Request(array(
            'sessionKey' => 'not-a-valid-session-id',
            'questionID' => 'questionID',
            'questionSettings' => 'questionSettings',
            'language' => 'language'
        ));
        $response = (new QuestionPropertiesGet)->run($request);

        $this->assertInvalidSession($response);
    }
}
