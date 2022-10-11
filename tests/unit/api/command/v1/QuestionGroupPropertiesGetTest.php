<?php

namespace ls\tests\unit\api\command\v1;

use ls\tests\TestBaseClass;
use ls\tests\unit\api\command\mixin\AssertResponse;
use LimeSurvey\Api\Command\V1\QuestionGroupPropertiesGet;
use LimeSurvey\Api\Command\Request\Request;

/**
 * Tests for the API command v1 QuestionGroupList.
 */
class QuestionGroupPropertiesGetTest extends TestBaseClass
{
    use AssertResponse;

    public function testQuestionGroupPropertiesGetInvalidSession()
    {
        $request = new Request(array(
            'sessionKey' => 'not-a-valid-session-id',
            'groupID' => 'groupID',
            'groupSettings' => 'groupSettings',
            'language' => 'language'
        ));
        $response = (new QuestionGroupPropertiesGet)->run($request);

        $this->assertResponseInvalidSession($response);
    }
}
