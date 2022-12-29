<?php

namespace ls\tests\unit\api;

use ls\tests\TestBaseClass;
use LimeSurvey\Api\Auth\AuthSession;

/**
 * @testdox Auth Session
 */
class AuthSessionTest extends TestBaseClass
{
    /**
     * @testdox doLogin() Returns boolean token if login successful.
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

        $AuthSession = new AuthSession();
        $result = $AuthSession->doLogin(
            $username,
            $password
        );

        $this->assertNotEmpty($result);
        $this->assertIsString($result);
    }

    /**
     * @testdox checkKey() returns false on valid key.
     */
    public function testCheckKeySessionNotFound()
    {
        $AuthSession = new AuthSession();
        $result = $AuthSession->checkKey('invalid-key');
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

        $AuthSession = new AuthSession();
        $result = $AuthSession->jumpStartSession($username);
        $this->assertTrue($result);
    }
}
