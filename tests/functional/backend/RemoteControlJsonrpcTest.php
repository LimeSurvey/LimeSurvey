<?php

namespace ls\tests;

use Yii;

class RemoteControlJsonrpcTest extends TestBaseClassWeb
{
    private static $tmpBaseUrl;
    private static $tmpRPCType;
    private static $serverUrl;

    private static $client;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        $urlMan = Yii::app()->urlManager;
        self::$tmpBaseUrl = $urlMan->getBaseUrl();
        $urlMan->setBaseUrl('http://' . self::$domain . '/index.php');
        //$serverUrl = App()->createAbsoluteUrl('/admin/remotecontrol');
        $serverUrl = $urlMan->createUrl('/admin/remotecontrol');
        self::$serverUrl = $serverUrl;

        self::$tmpRPCType = Yii::app()->getConfig('RPCInterface');

        if (self::$tmpRPCType !== 'json') {
            \SettingGlobal::setSetting('RPCInterface', 'json');
            $RPCType = 'json';
        }
        Yii::app()->loadLibrary('jsonRPCClient');
        self::$client = new \jsonRPCClient($serverUrl);
    }

    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();

        $urlMan = Yii::app()->urlManager;
        $urlMan->setBaseUrl(self::$tmpBaseUrl);

        \SettingGlobal::setSetting('RPCInterface', self::$tmpRPCType);
    }

    public function testGetSessionKey()
    {
        $username = getenv('ADMINUSERNAME');
        if (!$username) {
            $username = 'admin';
        }

        $password = getenv('PASSWORD');
        if (!$password) {
            $password = 'password';
        }
        $sessionKey = self::$client->call('get_session_key', [$username, $password]);
        $this->assertIsString($sessionKey);

        self::$client->call('release_session_key', [$sessionKey]);
    }

    public function testCredentialsError()
    {
        /* generate a random string to get an invalid user, no restriction on users_name */
        $sessionKey = self::$client->call('get_session_key', [Yii::app()->securityManager->generateRandomString(64), Yii::app()->securityManager->generateRandomString(64)]);
        $this->assertIsArray($sessionKey);
        $this->assertSame("Invalid user name or password", $sessionKey['status']);

        self::$client->call('release_session_key', [$sessionKey]);
    }

    public function testPluginApiMethodsAreBlockedWhenRpcInterfaceIsOff()
    {
        $originalPluginApi = (string) Yii::app()->getConfig('rpc_plugin_api', '0');
        \SettingGlobal::setSetting('rpc_plugin_api', '1');

        try {
            \SettingGlobal::setSetting('RPCInterface', 'json');

            $enabledList = $this->sendJsonRpcRequest('list_plugin_api', ['invalid-session-key', 'RemoteControlApiTestPlugin']);
            $this->assertArrayHasKey('result', $enabledList);
            $this->assertSame('Invalid session key', $enabledList['result']['status']);

            $enabledCall = $this->sendJsonRpcRequest(
                'call_plugin_api',
                ['invalid-session-key', 'RemoteControlApiTestPlugin', 'guard_survey_action', ['sid' => 1], []]
            );
            $this->assertArrayHasKey('result', $enabledCall);
            $this->assertSame('Invalid session key', $enabledCall['result']['status']);

            \SettingGlobal::setSetting('RPCInterface', 'off');

            $blockedListBody = $this->sendRawJsonRpcRequest('list_plugin_api', ['invalid-session-key', 'RemoteControlApiTestPlugin']);
            $this->assertSame('', trim($blockedListBody));

            $blockedCallBody = $this->sendRawJsonRpcRequest(
                'call_plugin_api',
                ['invalid-session-key', 'RemoteControlApiTestPlugin', 'guard_survey_action', ['sid' => 1], []]
            );
            $this->assertSame('', trim($blockedCallBody));
        } finally {
            \SettingGlobal::setSetting('rpc_plugin_api', $originalPluginApi);
            \SettingGlobal::setSetting('RPCInterface', 'json');
        }
    }

    private function sendJsonRpcRequest(string $method, array $params): array
    {
        $responseBody = $this->sendRawJsonRpcRequest($method, $params);
        $this->assertNotSame('', trim($responseBody), 'Expected JSON-RPC response body.');

        $decoded = json_decode($responseBody, true);
        $this->assertIsArray($decoded, 'Expected valid JSON-RPC response.');
        return $decoded;
    }

    private function sendRawJsonRpcRequest(string $method, array $params): string
    {
        $requestBody = json_encode([
            'method' => $method,
            'params' => $params,
            'id' => 999,
        ]);

        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => 'Content-Type: application/json',
                'content' => $requestBody,
                'ignore_errors' => true,
            ],
        ]);

        $responseBody = file_get_contents(self::$serverUrl, false, $context);
        if ($responseBody === false) {
            return '';
        }

        return $responseBody;
    }
}
