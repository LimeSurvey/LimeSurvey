<?php

namespace ls\tests\unit\api;

use ls\tests\TestBaseClass;
use LimeSurvey\Api\Authentication\AuthenticationTokenSimple;
use LimeSurvey\Api\Authentication\SessionUtil;
use LimeSurvey\Api\Transformer\Formatter\FormatterDateTimeToJson;


/**
 * @testdox Authentication Token Simple
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

    /**
     * @testdox login() with an API token opens a session limited to the token, which ends when the token is revoked.
     */
    public function testLoginWithApiToken()
    {
        \FailedLoginAttempt::model()->deleteAll();
        [$apiToken, $plainToken] = \UserApiToken::createForUser(1, 'REST test token', 30);
        try {
            $authTokenSimple = new AuthenticationTokenSimple(
                new SessionUtil,
                new FormatterDateTimeToJson
            );
            $result = $authTokenSimple->login('admin', $plainToken);
            $session = \Session::model()->findByPk($result['token']);
            $this->assertSame((int) $apiToken->id, (int) $session->api_token_id);

            $this->assertTrue($authTokenSimple->isAuthenticated($result['token']));
            $this->assertFalse(\Permission::model()->hasGlobalPermission('superadmin', 'read'));

            $refreshed = $authTokenSimple->refresh($result['token']);
            $this->assertSame((int) $apiToken->id, (int) \Session::model()->findByPk($refreshed['token'])->api_token_id);

            $apiToken->delete();
            $this->assertFalse($authTokenSimple->isAuthenticated($refreshed['token']));
        } finally {
            \UserApiToken::activate(null);
            if (\UserApiToken::model()->findByPk($apiToken->id)) {
                $apiToken->delete();
            }
        }
    }
}
