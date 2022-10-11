<?php

namespace ls\tests\unit\api;

use ls\tests\TestBaseClass;
use LimeSurvey\Api\ApiSession;

/**
 * @testdox API Session
 */
class ApiSessionTest extends TestBaseClass
{
    /**
     * @testdox Returns boolean true if logic successful.
     */
    public function testDoLogin()
    {
        $username = getenv('ADMINUSERNAME');
        if (!$username) {
            $username = 'admin';
        }

        $password = getenv('PASSWORD');
        if (!$password) {
            $password = 'password';
        }

        $apiSession = new ApiSession;
        $result = $apiSession->doLogin(
            $username,
            $password
        );

        $this->assertTrue($result);
    }
}
