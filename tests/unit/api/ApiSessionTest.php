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
     * @testdox doLogin() Returns boolean true if login successful.
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

        $apiSession = new ApiSession();
        $result = $apiSession->doLogin(
            $username,
            $password
        );

        $this->assertTrue($result);
    }

    /**
     * @testdox checkKey() returns false on valid key.
     */
    public function testCheckKeySessionNotFound()
    {
        $apiSession = new ApiSession();
        $result = $apiSession->checkKey('invalid-key');
        $this->assertFalse($result);
    }

    /**
     * @testdox jumpStartSession() init session from key.
     */
    public function testJumpStartSessionInitSessionFromKey()
    {
        $username = getenv('ADMINUSERNAME');
        if (!$username) {
            $username = 'admin';
        }

        $apiSession = new ApiSession();
        $result = $apiSession->jumpStartSession($username);
        $this->assertTrue($result);
    }
}
