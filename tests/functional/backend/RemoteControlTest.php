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
            Yii::app()->setConfig('RPCInterface', 'json');
        }

        $RPCType = Yii::app()->getConfig('RPCInterface');

        if ($RPCType == 'xml') {
            $cur_path = get_include_path();
            set_include_path($cur_path . PATH_SEPARATOR . APPPATH . 'helpers');
            require_once('Zend/XmlRpc/Client.php');

            self::$client = new \Zend_XmlRpc_Client($serverUrl);
        } elseif ($RPCType == 'json') {
            Yii::app()->loadLibrary('jsonRPCClient');
            self::$client = new \jsonRPCClient($serverUrl, true);
        } else {
            die('RPC interface not activated in global settings');
        }
    }

    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();

        $urlMan = Yii::app()->urlManager;
        $urlMan->setBaseUrl(self::$tmpBaseUrl);

        Yii::app()->setConfig('RPCInterface', self::$tmpRPCType);
    }

    public function testGetSessionKey()
    {
        $sSessionKey = self::$client->call('get_session_key', array('admin', 'password'));

        $this->assertTrue(true);
    }
}
