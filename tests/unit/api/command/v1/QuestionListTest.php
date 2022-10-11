<?php

namespace ls\tests\unit\api\command\v1;

use ls\tests\TestBaseClass;
use ls\tests\unit\api\command\mixin\AssertResponse;
use LimeSurvey\Api\Command\V1\QuestionList;
use LimeSurvey\Api\Command\Request\Request;

/**
 * @testdox API command v1 QuestionList.
 */
class QuestionListTest extends TestBaseClass
{
    use AssertResponse;

    /**
     * @testdox Returns invalid session response (error unauthorised) if session key is not valid.
     */
    public function testQuestionListInvalidSession()
    {
        $request = new Request(array(
            'sessionKey' => 'not-a-valid-session-id',
            'surveyID' => 'surveyID',
            'groupID' => 'groupID',
            'language' => 'language'
        ));
        $response = (new QuestionList)->run($request);

        $this->assertResponseInvalidSession($response);
    }
}
