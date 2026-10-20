<?php

namespace ls\tests\unit\helpers\remotecontrol;

use FailedLoginAttempt;
use LSUserIdentity;
use Permission;
use Session;
use Survey;
use User;
use UserApiToken;
use UserApiTokenPermission;

/**
 * Login to RemoteControl with personal API tokens, and the scope of the tokens
 */
class ApiTokenTest extends BaseTest
{
    /** @var int[] Two test surveys */
    private static $surveyIds = [];

    /**
     * Import two test surveys
     *
     * @return void
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        \Yii::app()->session['loginID'] = 1;
        foreach ([0, 1] as $index) {
            Survey::model()->resetCache();
            $result = \importSurveyFile(self::$surveysFolder . '/limesurvey_survey_666368.lss', false, null, null);
            self::$surveyIds[$index] = (int) $result['newsid'];
        }
    }

    /**
     * Delete the test surveys and the API tokens of the admin
     *
     * @return void
     */
    public static function tearDownAfterClass(): void
    {
        UserApiToken::activate(null);
        \Yii::app()->session['loginID'] = 1;
        foreach (UserApiToken::model()->findAllByAttributes(['uid' => 1]) as $apiToken) {
            $apiToken->delete();
        }
        foreach (self::$surveyIds as $surveyId) {
            $survey = Survey::model()->findByPk($surveyId);
            if ($survey) {
                $survey->delete();
            }
        }
        parent::tearDownAfterClass();
    }

    /**
     * Reset the login lockout, failed logins are part of the tests
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        FailedLoginAttempt::model()->deleteAll();
    }

    /**
     * Remove the token scope and log in the admin again
     *
     * @return void
     */
    protected function tearDown(): void
    {
        UserApiToken::activate(null);
        \Yii::app()->session['loginID'] = 1;
        \Yii::app()->user->setId(1);
        parent::tearDown();
    }

    /**
     * Create an API token for the admin
     *
     * @param array $permissions Permissions of the token, see UserApiToken::setPermissions()
     * @param int $lifetime Lifetime in days, 0 for no expiration
     * @return array{0: UserApiToken, 1: string} The token record and the plain token
     */
    private function createAdminToken(array $permissions, $lifetime = 30)
    {
        [$apiToken, $plainToken] = UserApiToken::createForUser(1, 'Test token', $lifetime);
        $apiToken->setPermissions($permissions);
        return [$apiToken, $plainToken];
    }

    public function testSessionKeyWithApiToken()
    {
        [$apiToken, $plainToken] = $this->createAdminToken([]);

        $sessionKey = $this->handler->get_session_key($this->getUsername(), $plainToken);

        $this->assertIsString($sessionKey);
        $this->assertSame(32, strlen($sessionKey));
        $this->assertSame((int) $apiToken->id, (int) Session::model()->findByPk($sessionKey)->api_token_id);
        $this->assertNotEmpty(UserApiToken::model()->findByPk($apiToken->id)->last_used);
    }

    public function testSessionKeyWithPasswordIsNotLinkedToToken()
    {
        $sessionKey = $this->handler->get_session_key($this->getUsername(), $this->getPassword());

        $this->assertIsString($sessionKey);
        $this->assertEmpty(Session::model()->findByPk($sessionKey)->api_token_id);
    }

    public function testSessionKeyWithUnknownApiToken()
    {
        $result = $this->handler->get_session_key($this->getUsername(), UserApiToken::TOKEN_PREFIX . str_repeat('0', 48));

        $this->assertIsArray($result);
        $this->assertSame('Invalid user name or password', $result['status']);
    }

    public function testSessionKeyWithApiTokenOfOtherUser()
    {
        [, $plainToken] = $this->createAdminToken([]);

        $result = $this->handler->get_session_key('someoneelse', $plainToken);

        $this->assertIsArray($result);
        $this->assertSame('Invalid user name or password', $result['status']);
    }

    public function testSessionKeyWithExpiredApiToken()
    {
        [$apiToken, $plainToken] = $this->createAdminToken([]);
        $apiToken->expires = gmdate('Y-m-d H:i:s', time() - 60);
        $apiToken->save();

        $result = $this->handler->get_session_key($this->getUsername(), $plainToken);

        $this->assertIsArray($result);
        $this->assertSame('Invalid user name or password', $result['status']);
    }

    public function testApiTokenIsNotAcceptedOutsideOfTheApi()
    {
        [, $plainToken] = $this->createAdminToken([]);

        $identity = new LSUserIdentity($this->getUsername(), $plainToken);
        $identity->setPlugin('Authdb');

        $this->assertFalse($identity->authenticate());
        $this->assertNull($identity->apiTokenId);
    }

    public function testSurveyPermissionsLimitTheSession()
    {
        [$surveyId, $otherSurveyId] = self::$surveyIds;
        [, $plainToken] = $this->createAdminToken([
            'survey' => [
                $surveyId => [
                    'survey' => ['read' => 1],
                    'responses' => ['read' => 1],
                ],
            ],
        ]);
        $sessionKey = $this->handler->get_session_key($this->getUsername(), $plainToken);

        $surveys = $this->handler->list_surveys($sessionKey);

        $this->assertSame([$surveyId], array_map('intval', array_column($surveys, 'sid')));
        $this->assertTrue(Permission::model()->hasSurveyPermission($surveyId, 'responses', 'read'));
        $this->assertFalse(Permission::model()->hasSurveyPermission($surveyId, 'responses', 'update'));
        $this->assertFalse(Permission::model()->hasSurveyPermission($surveyId, 'surveycontent', 'read'));
        $this->assertFalse(Permission::model()->hasSurveyPermission($otherSurveyId, 'survey', 'read'));
        $this->assertFalse(Permission::model()->hasGlobalPermission('superadmin', 'read'));
    }

    public function testGlobalSurveysPermissionCoversAllSurveys()
    {
        [$surveyId, $otherSurveyId] = self::$surveyIds;
        [, $plainToken] = $this->createAdminToken([
            'global' => [0 => ['surveys' => ['read' => 1]]],
        ]);
        $sessionKey = $this->handler->get_session_key($this->getUsername(), $plainToken);

        $surveys = $this->handler->list_surveys($sessionKey);

        $surveyIds = array_map('intval', array_column($surveys, 'sid'));
        $this->assertContains($surveyId, $surveyIds);
        $this->assertContains($otherSurveyId, $surveyIds);
        $this->assertTrue(Permission::model()->hasSurveyPermission($otherSurveyId, 'responses', 'read'));
        $this->assertFalse(Permission::model()->hasSurveyPermission($otherSurveyId, 'responses', 'update'));
        $this->assertFalse(Permission::model()->hasGlobalPermission('surveys', 'create'));
    }

    public function testReadOnlyTokenCannotCreateSurveys()
    {
        [, $plainToken] = $this->createAdminToken([
            'global' => [0 => ['surveys' => ['read' => 1]]],
        ]);
        $sessionKey = $this->handler->get_session_key($this->getUsername(), $plainToken);

        $result = $this->handler->add_survey($sessionKey, 0, 'Not allowed', 'en');

        $this->assertIsArray($result);
        $this->assertSame('No permission', $result['status']);
    }

    public function testRevokedTokenEndsTheSession()
    {
        [$apiToken, $plainToken] = $this->createAdminToken([
            'global' => [0 => ['surveys' => ['read' => 1]]],
        ]);
        $sessionKey = $this->handler->get_session_key($this->getUsername(), $plainToken);
        $this->assertIsArray($this->handler->list_surveys($sessionKey));

        $apiToken->delete();

        $result = $this->handler->list_surveys($sessionKey);
        $this->assertSame(\remotecontrol_handle::INVALID_SESSION_KEY, $result['status']);
        $this->assertNull(Session::model()->findByPk($sessionKey));
        $this->assertSame(0, (int) UserApiTokenPermission::model()->countByAttributes(['token_id' => $apiToken->id]));
    }

    public function testExpiredTokenEndsTheSession()
    {
        [$apiToken, $plainToken] = $this->createAdminToken([
            'global' => [0 => ['surveys' => ['read' => 1]]],
        ]);
        $sessionKey = $this->handler->get_session_key($this->getUsername(), $plainToken);

        $apiToken->expires = gmdate('Y-m-d H:i:s', time() - 60);
        $apiToken->save();

        $result = $this->handler->list_surveys($sessionKey);
        $this->assertSame(\remotecontrol_handle::INVALID_SESSION_KEY, $result['status']);
    }

    public function testTokenPermissionsAreLimitedToTheOwner()
    {
        $user = self::createUserWithPermissions(
            ['users_name' => 'apitokenowner', 'full_name' => 'API token owner', 'email' => 'apitokenowner@example.org'],
            ['surveys' => ['read' => 'on']]
        );
        try {
            [$apiToken] = UserApiToken::createForUser($user->uid, 'Test token', 30);
            $apiToken->setPermissions([
                'global' => [0 => [
                    'surveys' => ['read' => 1, 'update' => 1],
                    'users' => ['create' => 1],
                    'auth_db' => ['read' => 1],
                ]],
            ]);

            $this->assertTrue($apiToken->hasTokenPermission('global', 0, 'surveys', 'read'));
            $this->assertFalse($apiToken->hasTokenPermission('global', 0, 'surveys', 'update'));
            $this->assertFalse($apiToken->hasTokenPermission('global', 0, 'users', 'create'));
            $this->assertFalse($apiToken->hasTokenPermission('global', 0, 'auth_db', 'read'));
            $this->assertSame(['surveys'], array_keys($apiToken->getPermissionGrid('global')));
        } finally {
            $user->delete();
        }
        $this->assertSame(0, (int) UserApiToken::model()->countByAttributes(['uid' => $user->uid]));
    }

    public function testTokenFormat()
    {
        [, $plainToken] = $this->createAdminToken([]);

        $this->assertTrue(UserApiToken::isTokenFormat($plainToken));
        $this->assertFalse(UserApiToken::isTokenFormat($this->getPassword()));
        $this->assertFalse(UserApiToken::isTokenFormat($plainToken . '0'));
        $this->assertFalse(UserApiToken::isTokenFormat(strtoupper($plainToken)));
    }
}
