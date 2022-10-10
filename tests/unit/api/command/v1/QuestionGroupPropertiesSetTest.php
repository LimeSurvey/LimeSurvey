<?php

namespace ls\tests\unit\api\command\v1;

use ls\tests\TestBaseClass;
use ls\tests\unit\api\command\mixin\AssertInvalidSession;
use LimeSurvey\Api\Command\V1\QuestionGroupPropertiesSet;
use LimeSurvey\Api\Command\Request\Request;

/**
 * Tests for the API command v1 QuestionGroupPropertiesSet.
 */
class QuestionGroupPropertiesSetTest extends TestBaseClass
{
    use AssertInvalidSession;

    public function testQuestionGroupPropertiesSetInvalidSession()
    {
        $request = new Request(array(
            'sessionKey' => 'not-a-valid-session-id',
            'groupID' => 'groupID',
            'groupSettings' => 'groupData'
        ));
        $response = (new QuestionGroupPropertiesSet)->run($request);

        $this->assertInvalidSession($response);
    }
}
