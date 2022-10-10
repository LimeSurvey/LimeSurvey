<?php

namespace ls\tests\unit\api\command\v1;

use ls\tests\TestBaseClass;
use ls\tests\unit\api\command\mixin\AssertInvalidSession;
use LimeSurvey\Api\Command\V1\SurveyDelete;
use LimeSurvey\Api\Command\Request\Request;

/**
 * Tests for the API command v1 SurveyDelete.
 */
class SurveyDeleteTest extends TestBaseClass
{
    use AssertInvalidSession;

    public function testSurveyDeleteTestInvalidSession()
    {
        $request = new Request(array(
            'sessionKey' => 'not-a-valid-session-id',
            'surveyID' => 'surveyID'
        ));
        $response = (new SurveyDelete)->run($request);

        $this->assertInvalidSession($response);
    }
}
