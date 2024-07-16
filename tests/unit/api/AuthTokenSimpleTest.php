<?php

namespace ls\tests\unit\api;

use ls\tests\TestBaseClass;
use LimeSurvey\Api\Auth\AuthTokenSimple;

/**
 * @testdox Auth Session
 */
class AuthTokenSimpleTest extends TestBaseClass
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

        $authTokenSimple = new AuthTokenSimple();
        $result = $authTokenSimple->login(
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
        $authTokenSimple = new AuthTokenSimple();
        $result = $authTokenSimple->isAuthenticated('invalid-key');
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

        $authTokenSimple = new AuthTokenSimple();
        $result = $authTokenSimple->jumpStartSession($username);
        $this->assertTrue($result);
    }
}
