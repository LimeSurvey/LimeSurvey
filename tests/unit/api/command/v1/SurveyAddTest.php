<?php

namespace ls\tests\unit\api\command\v1;

use ls\tests\TestBaseClass;
use ls\tests\unit\api\command\mixin\AssertInvalidSession;
use LimeSurvey\Api\Command\V1\SurveyAdd;
use LimeSurvey\Api\Command\Request\Request;

/**
 * Tests for the API command v1 SurveyAdd.
 */
class SurveyAddTest extends TestBaseClass
{
    use AssertInvalidSession;

    public function testSurveyAddTestInvalidSession()
    {
        $request = new Request(array(
            'sessionKey' => 'not-a-valid-session-id',
            'surveyID' => 'surveyID',
            'surveyTitle' => 'surveyTitle',
            'surveyLanguage' => 'surveyLanguage',
            'format' => 'format'
        ));
        $response = (new SurveyAdd)->run($request);

        $this->assertInvalidSession($response);
    }
}
