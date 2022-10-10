<?php

namespace ls\tests\unit\api\command\v1;

use ls\tests\TestBaseClass;
use ls\tests\unit\api\command\mixin\AssertInvalidSession;
use LimeSurvey\Api\Command\V1\QuestionGroupList;
use LimeSurvey\Api\Command\Request\Request;

/**
 * Tests for the API command v1 QuestionGroupList.
 */
class QuestionGroupListTest extends TestBaseClass
{
    use AssertInvalidSession;

    public function testQuestionGroupListInvalidSession()
    {
        $request = new Request(array(
            'sessionKey' => 'not-a-valid-session-id',
            'groupID' => 'groupID',
            'language' => 'language'
        ));
        $response = (new QuestionGroupList)->run($request);

        $this->assertInvalidSession($response);
    }
}
