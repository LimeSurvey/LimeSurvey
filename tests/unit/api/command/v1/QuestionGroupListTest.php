<?php

namespace ls\tests\unit\api\command\v1;

use ls\tests\TestBaseClass;
use ls\tests\unit\api\command\mixin\AssertResponse;
use LimeSurvey\Api\Command\V1\QuestionGroupList;
use LimeSurvey\Api\Command\Request\Request;

/**
 * @testdox API command v1 QuestionGroupList
 */
class QuestionGroupListTest extends TestBaseClass
{
    use AssertResponse;

    /**
     * @testdox Returns invalid session response (error unauthorised) if session key is not valid.
     */
    public function testQuestionGroupListInvalidSession()
    {
        $request = new Request(array(
            'sessionKey' => 'not-a-valid-session-id',
            'groupID' => 'groupID',
            'language' => 'language'
        ));
        $response = (new QuestionGroupList)->run($request);

        $this->assertResponseInvalidSession($response);
    }
}
