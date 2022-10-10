<?php

namespace ls\tests\unit\api\command\v1;

use ls\tests\TestBaseClass;
use ls\tests\unit\api\command\mixin\AssertInvalidSession;
use LimeSurvey\Api\Command\V1\QuestionList;
use LimeSurvey\Api\Command\Request\Request;

/**
 * Tests for the API command v1 SiteSettingsGet.
 */
class SiteSettingsGetTest extends TestBaseClass
{
    use AssertInvalidSession;

    public function testSiteSettingsGetTestInvalidSession()
    {
        $request = new Request(array(
            'sessionKey' => 'not-a-valid-session-id',
            'settingName' => 'settingName'
        ));
        $response = (new QuestionList)->run($request);

        $this->assertInvalidSession($response);
    }
}
