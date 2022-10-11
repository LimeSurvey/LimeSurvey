<?php

namespace ls\tests\unit\api\command\v1;

use ls\tests\TestBaseClass;
use ls\tests\unit\api\command\mixin\AssertResponse;
use LimeSurvey\Api\Command\V1\SurveyPropertiesGet;
use LimeSurvey\Api\Command\Request\Request;

/**
 * @testdox API command v1 SurveyPropertiesGetTest.
 */
class SurveyPropertiesGetTest extends TestBaseClass
{
    use AssertResponse;

    /**
     * @testdox Returns invalid session response (error unauthorised) if session key is not valid.
     */
    public function testSurveyDeleteTestInvalidSession()
    {
        $request = new Request(array(
            'sessionKey' => 'not-a-valid-session-id',
            'surveyID' => 'surveyID',
            'surveySettings' => array()
        ));
        $response = (new SurveyPropertiesGet)->run($request);

        $this->assertResponseInvalidSession($response);
    }
}
