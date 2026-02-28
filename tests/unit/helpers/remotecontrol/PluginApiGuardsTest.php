<?php

namespace ls\tests;

/**
 * Guard coverage for RemoteControl plugin API.
 */
class PluginApiGuardsTest extends BaseTest
{
    private const TEST_PLUGIN = 'RemoteControlApiTestPlugin';
    private const LOW_PERMISSION_USER = 'rc_api_guard_user';
    private const LOW_PERMISSION_PASSWORD = 'rc_api_guard_password';

    private static $originalRpcPluginApi = '0';
    private static $lowPermissionUserId = 0;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        self::$originalRpcPluginApi = (string) App()->getConfig('rpc_plugin_api', '0');
        \SettingGlobal::setSetting('rpc_plugin_api', '0');

        self::loadTestPlugin(self::TEST_PLUGIN);
        self::importSurvey(self::$surveysFolder . '/limesurvey_survey_666368.lss');

        $user = self::createUserWithPermissions(
            [
                'users_name' => self::LOW_PERMISSION_USER,
                'full_name' => self::LOW_PERMISSION_USER,
                'email' => self::LOW_PERMISSION_USER . '@example.com',
                'lang' => 'en',
                'password' => self::LOW_PERMISSION_PASSWORD,
            ],
            [
                'auth_db' => [
                    'read' => 'on',
                ],
            ]
        );
        self::$lowPermissionUserId = (int) $user->uid;
    }

    public static function tearDownAfterClass(): void
    {
        \SettingGlobal::setSetting('rpc_plugin_api', self::$originalRpcPluginApi);
        self::deActivatePlugin(self::TEST_PLUGIN);

        if (self::$lowPermissionUserId > 0) {
            \Permission::model()->deleteAllByAttributes(['uid' => self::$lowPermissionUserId]);
            \User::model()->deleteByPk(self::$lowPermissionUserId);
        }

        parent::tearDownAfterClass();
    }

    public function testListPluginApiIsBlockedWhenGlobalSwitchIsDisabled(): void
    {
        $this->setPluginApiEnabled(false);
        $sessionKey = $this->getValidSessionKey($this->getUsername(), $this->getPassword());

        $result = $this->handler->list_plugin_api($sessionKey, self::TEST_PLUGIN);
        $this->assertSame(['status' => 'Error: Plugin API disabled'], $result);
    }

    public function testCallPluginApiIsBlockedWhenGlobalSwitchIsDisabled(): void
    {
        $this->setPluginApiEnabled(false);
        $sessionKey = $this->getValidSessionKey($this->getUsername(), $this->getPassword());

        $result = $this->handler->call_plugin_api($sessionKey, self::TEST_PLUGIN, 'guard_global_action', [], []);
        $this->assertSame(['status' => 'Error: Plugin API disabled'], $result);
    }

    public function testListPluginApiFiltersActionsByCallerPermissionAndMetadata(): void
    {
        $this->setPluginApiEnabled(true);
        $sessionKey = $this->getValidSessionKey(self::LOW_PERMISSION_USER, self::LOW_PERMISSION_PASSWORD);

        $result = $this->handler->list_plugin_api($sessionKey, self::TEST_PLUGIN);
        $this->assertArrayHasKey('plugins', $result);
        $this->assertArrayHasKey(self::TEST_PLUGIN, $result['plugins']);
        $this->assertArrayHasKey('actions', $result['plugins'][self::TEST_PLUGIN]);

        $actions = $result['plugins'][self::TEST_PLUGIN]['actions'];
        $this->assertArrayHasKey('guard_survey_action', $actions);
        $this->assertArrayNotHasKey('guard_global_action', $actions);
        $this->assertArrayNotHasKey('guard_invalid_permission_action', $actions);
    }

    public function testCallPluginApiRejectsInvalidPermissionMetadata(): void
    {
        $this->setPluginApiEnabled(true);
        $sessionKey = $this->getValidSessionKey($this->getUsername(), $this->getPassword());

        $result = $this->handler->call_plugin_api($sessionKey, self::TEST_PLUGIN, 'guard_invalid_permission_action', [], []);
        $this->assertSame(['status' => 'Error: Invalid plugin API permission metadata'], $result);
    }

    public function testCallPluginApiRejectsGlobalActionForLowPermissionUser(): void
    {
        $this->setPluginApiEnabled(true);
        $sessionKey = $this->getValidSessionKey(self::LOW_PERMISSION_USER, self::LOW_PERMISSION_PASSWORD);

        $result = $this->handler->call_plugin_api($sessionKey, self::TEST_PLUGIN, 'guard_global_action', [], []);
        $this->assertSame(['status' => 'No permission'], $result);
    }

    public function testCallPluginApiRequiresSurveyIdForSurveyScopedPermission(): void
    {
        $this->setPluginApiEnabled(true);
        $sessionKey = $this->getValidSessionKey($this->getUsername(), $this->getPassword());

        $result = $this->handler->call_plugin_api($sessionKey, self::TEST_PLUGIN, 'guard_survey_action', [], []);
        $this->assertSame(['status' => 'Faulty parameters: payload.sid is required for permission check'], $result);
    }

    public function testCallPluginApiAllowsSurveyScopedActionWhenPermissionCheckPasses(): void
    {
        $this->setPluginApiEnabled(true);
        $sessionKey = $this->getValidSessionKey($this->getUsername(), $this->getPassword());

        $result = $this->handler->call_plugin_api(
            $sessionKey,
            self::TEST_PLUGIN,
            'guard_survey_action',
            ['sid' => self::$surveyId],
            []
        );

        $this->assertArrayHasKey('ok', $result);
        $this->assertTrue($result['ok']);
        $this->assertSame('guard_survey_action', $result['action']);
        $this->assertSame((int) self::$surveyId, $result['sid']);
    }

    private function getValidSessionKey(string $username, string $password): string
    {
        $sessionKey = $this->handler->get_session_key($username, $password);
        $this->assertIsString($sessionKey, 'Failed to create session key for test user');
        return $sessionKey;
    }

    private function setPluginApiEnabled(bool $enabled): void
    {
        \SettingGlobal::setSetting('rpc_plugin_api', $enabled ? '1' : '0');
    }
}
