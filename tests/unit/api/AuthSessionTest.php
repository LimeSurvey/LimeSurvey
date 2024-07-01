<?php

namespace ls\tests\unit\api;

use ls\tests\TestBaseClass;
use LimeSurvey\Api\Auth\AuthSession;

/**
 * @testdox Auth Session
 */
class AuthSessionTest extends TestBaseClass
{
    public static function tearDownAfterClass(): void
    {
        $_SESSION = [];
        $_POST    = [];
    }

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

        $authSession = new AuthSession();
        $result = $authSession->doLogin(
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
        $authSession = new AuthSession();
        $result = $authSession->checkKey('invalid-key');
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

        $authSession = new AuthSession();
        $result = $authSession->jumpStartSession($username);
        $this->assertTrue($result);
    }
}
