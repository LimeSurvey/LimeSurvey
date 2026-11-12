<?php

namespace ls\tests;

use Yii;

/**
 * @group api
 */
class RemoteControlJsonrpcTest extends TestBaseClassWeb
{
    private static $tmpBaseUrl;
    private static $tmpRPCType;

    private static $client;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        $urlMan = Yii::app()->urlManager;
        self::$tmpBaseUrl = $urlMan->getBaseUrl();
        $urlMan->setBaseUrl('http://' . self::$domain . '/index.php');
        $serverUrl = App()->createAbsoluteUrl('/admin/remotecontrol');

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

        // $sessionKey is an error array, not a valid key — do not attempt to release it
        // self::$client->call('release_session_key', [$sessionKey]);
    }

    /**
     * Request ids that are falsy in PHP but still identify a request.
     *
     * @return array
     */
    public static function falsyRequestIdProvider(): array
    {
        return [
            'integer zero' => [0],
            'string zero' => ['0'],
        ];
    }

    /**
     * A request with an id of 0 or "0" must get a response, not an empty body.
     *
     * @dataProvider falsyRequestIdProvider
     * @param int|string $requestId
     * @return void
     */
    public function testFalsyRequestIdGetsResponse($requestId)
    {
        $response = $this->postRawRequest([
            'jsonrpc' => '2.0',
            'id' => $requestId,
            'method' => 'release_session_key',
            'params' => [Yii::app()->securityManager->generateRandomString(32)],
        ]);
        $this->assertNotSame('', trim($response), 'The response body must not be empty.');
        $decoded = json_decode($response, true);
        $this->assertIsArray($decoded);
        $this->assertSame($requestId, $decoded['id']);
        $this->assertSame('OK', $decoded['result']);
        $this->assertNull($decoded['error']);
    }

    /**
     * A notification (null id) still gets no response.
     *
     * @return void
     */
    public function testNotificationGetsNoResponse()
    {
        $response = $this->postRawRequest([
            'id' => null,
            'method' => 'release_session_key',
            'params' => [Yii::app()->securityManager->generateRandomString(32)],
        ]);
        $this->assertSame('', trim($response));
    }

    /**
     * Post a JSON-RPC request to the RemoteControl endpoint and return the raw response body.
     *
     * @param array $request
     * @return string
     */
    private function postRawRequest(array $request): string
    {
        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => 'Content-type: application/json',
                'content' => json_encode($request),
            ],
        ]);
        $response = file_get_contents(App()->createAbsoluteUrl('/admin/remotecontrol'), false, $context);
        $this->assertNotFalse($response, 'Unable to connect to the RemoteControl endpoint.');
        return $response;
    }
}
