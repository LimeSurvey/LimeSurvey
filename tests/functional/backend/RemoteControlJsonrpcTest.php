<?php

namespace ls\tests;

use Yii;

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
        //$serverUrl = App()->createAbsoluteUrl('/admin/remotecontrol');
        $serverUrl = $urlMan->createUrl('/admin/remotecontrol');

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
}
