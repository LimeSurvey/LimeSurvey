<?php

namespace ls\tests\unit\api;

use ls\tests\TestBaseClass;
use LimeSurvey\Api\Authentication\AuthenticationTokenSimple;
use LimeSurvey\Api\Authentication\SessionUtil;
use LimeSurvey\Api\Transformer\Formatter\FormatterDateTimeToJson;


/**
 * @testdox Authentication Token Simple
 */
class AuthenticationTokenSimpleTest extends TestBaseClass
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

        $authTokenSimple = new AuthenticationTokenSimple(
            new SessionUtil,
            new FormatterDateTimeToJson
        );
        $result = $authTokenSimple->login(
            $username,
            $password
        );

        $this->assertNotEmpty($result);
        $this->assertIsString($result['token']);
        $this->assertIsString($result['expires']);
        $this->assertIsInt($result['userId']);
    }

    /**
     * @testdox checkKey() returns false on valid key.
     */
    public function testCheckKeySessionNotFound()
    {
        $authTokenSimple = new AuthenticationTokenSimple(
            new SessionUtil,
            new FormatterDateTimeToJson
        );
        $result = $authTokenSimple->isAuthenticated('invalid-key');
        $this->assertFalse($result);
    }
}
