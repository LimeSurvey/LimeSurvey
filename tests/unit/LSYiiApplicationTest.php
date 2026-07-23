<?php

namespace ls\tests;

use Yii;

/**
 * Test LSYii_Application class.
 */
class LSYiiApplicationTest extends TestBaseClass
{
    /**
     * @inheritdoc
     * Set the static var for resetting after
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        self::$testHelper::saveUrlSettings();
        self::$testHelper::setToExpectedDefault();
    }

    /**
     * Get public base url, previously set in publicurl config attribute.
     */
    public function testGetpublicBaseUrlFromConfig()
    {
        Yii::app()->setConfig('publicurl', 'http://config.example.com/');
        $url = Yii::app()->getPublicBaseUrl();

        $this->assertSame('http://config.example.com/', $url, 'Unexpected url. The url does not correspond to the one previously set.');

        self::$testHelper::setToExpectedDefault();
    }

    /**
     * Get absolute public base url, previously set in publicurl config attribute.
     */
    public function testGetAbsolutepublicBaseUrlFromConfig()
    {
        Yii::app()->setConfig('publicurl', 'http://absoluteConfig.example.com/');
        $url = Yii::app()->getPublicBaseUrl(true);
        $this->assertSame('http://absoluteConfig.example.com/', $url, 'Unexpected url. The url does not correspond to the one previously set.');
        self::$testHelper::setToExpectedDefault();
    }

    /**
     * Get public base url, previously set in baseUrl request attribute.
     */
    public function testGetpublicBaseUrlFromRequest()
    {
        Yii::app()->getRequest()->baseUrl = 'http://request.example.com/';
        $url = Yii::app()->getPublicBaseUrl();
        $this->assertSame('http://request.example.com/', $url, 'Unexpected url. The url does not correspond to the one previously set.');
        self::$testHelper::setToExpectedDefault();
    }

    /**
     * Get absolute public base url, previously set in baseUrl request attribute.
     */
    public function testGetAbsolutepublicBaseUrlFromRequest()
    {
        Yii::app()->getRequest()->baseUrl = '/absoluteRequest';
        $url = Yii::app()->getPublicBaseUrl(true);

        $this->assertSame('http://localhost/absoluteRequest', $url, 'Unexpected url. The url does not correspond to the one previously set.');
        self::$testHelper::setToExpectedDefault();
    }

    /**
     * Get public base url, previously set in baseUrl request attribute.
     * A public url is also set in the baseUrl config attribute,
     * getPublicBaseUrl must always return publicurl.
     */
    public function testGetpublicBaseUrlFromConfigNoSchemeInConfigPublicUrl()
    {
        // No scheme in url.
        Yii::app()->setConfig('publicurl', '//config.example.com/path?param=1');
        Yii::app()->getRequest()->baseUrl = 'http://request.example.com/';
        $url = Yii::app()->getPublicBaseUrl();

        $this->assertSame('http://request.example.com/', $url, 'Unexpected url. The url does not correspond to the one previously set.');

        self::$testHelper::setToExpectedDefault();
    }

    /**
     * Get public base url, previously set in baseUrl request attribute.
     * A public url is also set in the baseUrl config attribute,
     * but the one in the request attribute should be returned.
     */
    public function testGetpublicBaseUrlFromConfigNoHostInConfigPublicUrl()
    {
        Yii::app()->setConfig('publicurl', 'http://');
        Yii::app()->getRequest()->baseUrl = 'http://request.example.com/';
        $url = Yii::app()->getPublicBaseUrl();

        $this->assertSame('http://request.example.com/', $url, 'Unexpected url. The url does not correspond to the one previously set.');

        self::$testHelper::setToExpectedDefault();
    }

    /**
     * Create a public url with just a route.
     */
    public function testCreatePublicUrlWithARoute()
    {
        Yii::app()->setConfig('publicurl', 'http://www.example.com/');

        Yii::app()->getUrlManager()->urlFormat = \CUrlManager::PATH_FORMAT;
        Yii::app()->getUrlManager()->showScriptName = true;
        $url = Yii::app()->createPublicUrl('controller/action');
        $this->assertSame('http://www.example.com/index.php/controller/action', $url, 'Unexpected url. The url does not correspond with a public url and a route with showScriptName and urlformat to path.');

        Yii::app()->getUrlManager()->showScriptName = false;
        $url = Yii::app()->createPublicUrl('controller/action');
        $this->assertSame('http://www.example.com/controller/action', $url, 'Unexpected url. The url does not correspond with a public url and a route without showScriptName and urlformat to path.');
        /* Deactivate urlFormat => 'get', TODO fix it, work locally, not on github */
        Yii::app()->getUrlManager()->urlFormat = \CUrlManager::GET_FORMAT;
        Yii::app()->getUrlManager()->showScriptName = true;
        $url = Yii::app()->createPublicUrl('controller/action');
        $this->assertSame('http://www.example.com/index.php?r=controller/action', $url, 'Unexpected url. The url does not correspond with a public url and a route with showScriptName and urlformat to get.');

        Yii::app()->getUrlManager()->showScriptName = false;
        $url = Yii::app()->createPublicUrl('controller/action');
        $this->assertSame('http://www.example.com/?r=controller/action', $url, 'Unexpected url. The url does not correspond with a public url and a route without showScriptName and urlformat to get.');

        self::$testHelper::setToExpectedDefault();
    }

    /**
     * Create a public url with a route and two parameters.
     */
    public function testCreatePublicUrlWithParams()
    {
        Yii::app()->setConfig('publicurl', 'http://www.example.com/');
        $parameters = array('param_one' => 1, 'param_two' => 2);
        $url = Yii::app()->createPublicUrl('controller/action', $parameters);

        $this->assertSame('http://www.example.com/index.php/controller/action/param_one/1/param_two/2', $url, 'Unexpected url. The url does not correspond with a public url, a route and two parameters.');

        self::$testHelper::setToExpectedDefault();
    }

    /**
     * Create a public url with a specific schema.
     */
    public function testCreatePublicUrlWithASchema()
    {
        Yii::app()->setConfig('publicurl', 'http://www.example.com');
        Yii::app()->getRequest()->baseUrl = 'www.example.com';
        Yii::app()->getRequest()->hostInfo = '';

        $parameters = array('param_one' => 1, 'param_two' => 2);
        $url = Yii::app()->createPublicUrl('controller/action', $parameters, 'http');

        $this->assertSame('http://www.example.com/index.php/controller/action/param_one/1/param_two/2', $url, 'Unexpected url. The url does not correspond with a public url, a route and two parameters.');

        self::$testHelper::setToExpectedDefault();
    }

    /**
     * @inheritdoc
     * And reset request
     */
    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();
        self::$testHelper::resetUrlSettings();
        /* This set hostinfo to null (unsure needed) */
        self::$testHelper->resetHostInfo();
    }
}
