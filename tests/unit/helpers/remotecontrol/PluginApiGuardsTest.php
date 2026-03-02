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
    private const AUTHORIZED_SURVEY_USER = 'rc_api_guard_authorized_user';
    private const AUTHORIZED_SURVEY_PASSWORD = 'rc_api_guard_authorized_password';

    private static $originalRpcPluginApi = '0';
    private static $lowPermissionUserId = 0;
    private static $authorizedSurveyUserId = 0;

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

        $authorizedUser = self::createUserWithPermissions(
            [
                'users_name' => self::AUTHORIZED_SURVEY_USER,
                'full_name' => self::AUTHORIZED_SURVEY_USER,
                'email' => self::AUTHORIZED_SURVEY_USER . '@example.com',
                'lang' => 'en',
                'password' => self::AUTHORIZED_SURVEY_PASSWORD,
            ],
            [
                'auth_db' => [
                    'read' => 'on',
                ],
            ]
        );
        self::$authorizedSurveyUserId = (int) $authorizedUser->uid;
        self::grantSurveyReadPermission(self::$authorizedSurveyUserId, (int) self::$surveyId);
    }

    public static function tearDownAfterClass(): void
    {
        \SettingGlobal::setSetting('rpc_plugin_api', self::$originalRpcPluginApi);
        self::deActivatePlugin(self::TEST_PLUGIN);

        if (self::$lowPermissionUserId > 0) {
            \Permission::model()->deleteAllByAttributes(['uid' => self::$lowPermissionUserId]);
            \User::model()->deleteByPk(self::$lowPermissionUserId);
        }
        if (self::$authorizedSurveyUserId > 0) {
            \Permission::model()->deleteAllByAttributes(['uid' => self::$authorizedSurveyUserId]);
            \User::model()->deleteByPk(self::$authorizedSurveyUserId);
        }

        parent::tearDownAfterClass();
    }

    public function testListPluginApiIsBlockedWhenGlobalSwitchIsDisabled(): void
    {
        $result = $this->listPluginApiForUser($this->getUsername(), $this->getPassword(), false);
        $this->assertSame(['status' => 'Error: Plugin API disabled'], $result);
    }

    public function testCallPluginApiIsBlockedWhenGlobalSwitchIsDisabled(): void
    {
        $result = $this->callPluginApiForUser(
            $this->getUsername(),
            $this->getPassword(),
            false,
            'guard_global_action'
        );
        $this->assertSame(['status' => 'Error: Plugin API disabled'], $result);
    }

    public function testListPluginApiFiltersActionsByCallerPermissionAndMetadata(): void
    {
        $result = $this->listPluginApiForUser(
            self::LOW_PERMISSION_USER,
            self::LOW_PERMISSION_PASSWORD,
            true
        );
        $this->assertArrayHasKey('plugins', $result);
        $this->assertArrayNotHasKey(self::TEST_PLUGIN, $result['plugins']);
        $this->assertSame([], $result['plugins']);
    }

    public function testCallPluginApiRejectsInvalidPermissionMetadata(): void
    {
        $result = $this->callPluginApiForUser(
            $this->getUsername(),
            $this->getPassword(),
            true,
            'guard_invalid_permission_action'
        );
        $this->assertSame(['status' => 'Error: Invalid plugin API permission metadata'], $result);
    }

    public function testCallPluginApiRejectsGlobalActionForLowPermissionUser(): void
    {
        $result = $this->callPluginApiForUser(
            self::LOW_PERMISSION_USER,
            self::LOW_PERMISSION_PASSWORD,
            true,
            'guard_global_action'
        );
        $this->assertSame(['status' => 'No permission'], $result);
    }

    public function testCallPluginApiRejectsLegacyGlobalActionForLowPermissionUser(): void
    {
        $result = $this->callPluginApiForUser(
            self::LOW_PERMISSION_USER,
            self::LOW_PERMISSION_PASSWORD,
            true,
            'guard_legacy_global_action'
        );
        $this->assertSame(['status' => 'No permission'], $result);
    }

    /**
     * @dataProvider surveyReferenceProvider
     */
    public function testCallPluginApiRejectsSurveyActionForLowPermissionUser(string $referenceType): void
    {
        [$payload, $context] = $this->buildSurveyReference($referenceType);

        $result = $this->callPluginApiForUser(
            self::LOW_PERMISSION_USER,
            self::LOW_PERMISSION_PASSWORD,
            true,
            'guard_survey_action',
            $payload,
            $context
        );
        $this->assertSame(['status' => 'No permission'], $result);
    }

    public function testCallPluginApiRejectsLegacySurveyActionForLowPermissionUser(): void
    {
        $result = $this->callPluginApiForUser(
            self::LOW_PERMISSION_USER,
            self::LOW_PERMISSION_PASSWORD,
            true,
            'guard_legacy_survey_action',
            ['sid' => self::$surveyId]
        );
        $this->assertSame(['status' => 'No permission'], $result);
    }

    public function testCallPluginApiRequiresSurveyIdForSurveyScopedPermission(): void
    {
        $result = $this->callPluginApiForUser(
            self::AUTHORIZED_SURVEY_USER,
            self::AUTHORIZED_SURVEY_PASSWORD,
            true,
            'guard_survey_action'
        );
        $this->assertSame(['status' => 'Faulty parameters: payload.sid is required for permission check'], $result);
    }

    public function testCallPluginApiAllowsSurveyScopedActionWhenPermissionCheckPasses(): void
    {
        $result = $this->callPluginApiForUser(
            self::AUTHORIZED_SURVEY_USER,
            self::AUTHORIZED_SURVEY_PASSWORD,
            true,
            'guard_survey_action',
            ['sid' => self::$surveyId]
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

    /**
     * @return array
     */
    public function surveyReferenceProvider(): array
    {
        return [
            'payload sid' => ['payloadSid'],
            'payload surveyId' => ['payloadSurveyId'],
            'context sid' => ['contextSid'],
            'context surveyId' => ['contextSurveyId'],
        ];
    }

    private function setPluginApiEnabled(bool $enabled): void
    {
        \SettingGlobal::setSetting('rpc_plugin_api', $enabled ? '1' : '0');
    }

    /**
     * @param string $referenceType
     * @return array
     */
    private function buildSurveyReference(string $referenceType): array
    {
        switch ($referenceType) {
            case 'payloadSid':
                return [['sid' => self::$surveyId], []];
            case 'payloadSurveyId':
                return [['surveyId' => self::$surveyId], []];
            case 'contextSid':
                return [[], ['sid' => self::$surveyId]];
            case 'contextSurveyId':
                return [[], ['surveyId' => self::$surveyId]];
            default:
                $this->fail('Unsupported survey reference type: ' . $referenceType);
                return [[], []];
        }
    }

    /**
     * @param string $username
     * @param string $password
     * @param bool $enabled
     * @return array
     */
    private function listPluginApiForUser(string $username, string $password, bool $enabled): array
    {
        $this->setPluginApiEnabled($enabled);
        $sessionKey = $this->getValidSessionKey($username, $password);
        return $this->handler->list_plugin_api($sessionKey, self::TEST_PLUGIN);
    }

    /**
     * @param string $username
     * @param string $password
     * @param bool $enabled
     * @param string $action
     * @param array $payload
     * @param array $context
     * @return array
     */
    private function callPluginApiForUser(
        string $username,
        string $password,
        bool $enabled,
        string $action,
        array $payload = [],
        array $context = []
    ): array {
        $this->setPluginApiEnabled($enabled);
        $sessionKey = $this->getValidSessionKey($username, $password);
        return $this->handler->call_plugin_api($sessionKey, self::TEST_PLUGIN, $action, $payload, $context);
    }

    private static function grantSurveyReadPermission(int $userId, int $surveyId): void
    {
        $permission = new \Permission();
        $permission->entity = 'survey';
        $permission->entity_id = $surveyId;
        $permission->uid = $userId;
        $permission->permission = 'surveycontent';
        $permission->read_p = 1;
        $permission->create_p = 0;
        $permission->update_p = 0;
        $permission->delete_p = 0;
        $permission->import_p = 0;
        $permission->export_p = 0;
        if (!$permission->save()) {
            throw new \RuntimeException(
                'Failed to grant survey read permission: ' . json_encode($permission->getErrors())
            );
        }
    }
}
