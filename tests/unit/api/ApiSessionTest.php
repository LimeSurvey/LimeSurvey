<?php

namespace ls\tests\api;

use ls\tests\TestBaseClass;
use LimeSurvey\Api\ApiSession;

/**
 * Tests for the API Session.
 */
class ApiSessionTest extends TestBaseClass
{
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
