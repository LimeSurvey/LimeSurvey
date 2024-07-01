<?php

namespace ls\tests;

use Yii;

class RemoteControlTest extends TestBaseClassWeb
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

        if (self::$tmpRPCType === 'off') {
            \SettingGlobal::setSetting('RPCInterface', 'json');
            $RPCType = 'json';
        } else {
            $RPCType = self::$tmpRPCType;
        }

        if ($RPCType == 'xml') {
            $cur_path = get_include_path();
            set_include_path($cur_path . PATH_SEPARATOR . APPPATH . 'helpers');
            require_once('Zend/XmlRpc/Client.php');

            self::$client = new \Zend_XmlRpc_Client($serverUrl);
        } elseif ($RPCType == 'json') {
            Yii::app()->loadLibrary('jsonRPCClient');
            self::$client = new \jsonRPCClient($serverUrl);
        }
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
        $sessionKey = self::$client->call('get_session_key', ['admin', 'password']);
        $this->assertIsString($sessionKey);

        self::$client->call('release_session_key', [$sessionKey]);
    }

    public function testCredentialsError()
    {
        $sessionKey = self::$client->call('get_session_key', ['user', 'pass']);
        $this->assertIsArray($sessionKey);
        $this->assertSame("Invalid user name or password", $sessionKey['status']);

        self::$client->call('release_session_key', [$sessionKey]);
    }
}
