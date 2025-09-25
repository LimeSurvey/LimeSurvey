<?php

namespace ls\tests;

use Yii;

/**
 * Test LSYii_Application class.
 */
class LSYiiApplicationTest extends TestBaseClass
{
    /**
     * Get public base url, previously set in publicurl config attribute.
     */
    public function testGetpublicBaseUrlFromConfig()
    {
        $tmpPublicUrl = Yii::app()->getConfig('publicurl');

        Yii::app()->setConfig('publicurl', 'http://config.example.com/');
        $url = Yii::app()->getPublicBaseUrl();

        $this->assertSame($url, 'http://config.example.com/', 'Unexpected url. The url does not correspond to the one previously set.');

        // Reset original value.
        Yii::app()->setConfig('publicurl', $tmpPublicUrl);
    }

    /**
     * Get absolute public base url, previously set in publicurl config attribute.
     */
    public function testGetAbsolutepublicBaseUrlFromConfig()
    {
        $tmpPublicUrl = Yii::app()->getConfig('publicurl');

        Yii::app()->setConfig('publicurl', 'http://absoluteConfig.example.com/');
        $url = Yii::app()->getPublicBaseUrl(true);

        $this->assertSame($url, 'http://absoluteConfig.example.com/', 'Unexpected url. The url does not correspond to the one previously set.');

        // Reset original value.
        Yii::app()->setConfig('publicurl', $tmpPublicUrl);
    }

    /**
     * Get public base url, previously set in baseUrl request attribute.
     */
    public function testGetpublicBaseUrlFromRequest()
    {
        if (getenv('LOCAL_TEST')) {
            App()->setConfig('publicurl', 'http://request.example.com/');
        }
        $tmpPublicUrl = Yii::app()->getRequest()->getBaseUrl();

        Yii::app()->getRequest()->baseUrl = 'http://request.example.com/';
        $url = Yii::app()->getPublicBaseUrl();

        $this->assertSame($url, 'http://request.example.com/', 'Unexpected url. The url does not correspond to the one previously set.');

        // Reset original value.
        Yii::app()->getRequest()->baseUrl = $tmpPublicUrl;
    }

    /**
     * Get absolute public base url, previously set in baseUrl request attribute.
     */
    public function testGetAbsolutepublicBaseUrlFromRequest()
    {
        if (getenv('LOCAL_TEST')) {
            App()->setConfig('publicurl', 'http://absoluteRequest.example.com/');
        }
        $tmpPublicUrl = Yii::app()->getRequest()->getBaseUrl();

        Yii::app()->getRequest()->baseUrl = 'absoluteRequest.example.com/';
        $url = Yii::app()->getPublicBaseUrl(true);

        $this->assertSame($url, 'http://absoluteRequest.example.com/', 'Unexpected url. The url does not correspond to the one previously set.');

        // Reset original value.
        Yii::app()->getRequest()->baseUrl = $tmpPublicUrl;
    }

    /**
     * Get public base url, previously set in baseUrl request attribute.
     * A public url is also set in the baseUrl config attribute,
     * but the one in the request attribute should be returned.
     */
    public function testGetpublicBaseUrlFromConfigNoSchemeInConfigPublicUrl()
    {
        $tmpConfigPublicUrl = Yii::app()->getConfig('publicurl');
        $tmpRequestPublicUrl = Yii::app()->getRequest()->getBaseUrl();

        // No scheme in url.
        Yii::app()->setConfig('publicurl', '//config.example.com/path?param=1');
        Yii::app()->getRequest()->baseUrl = 'http://request.example.com/';
        $url = Yii::app()->getPublicBaseUrl();

        $this->assertSame($url, 'http://request.example.com/', 'Unexpected url. The url does not correspond to the one previously set.');

        // Restore original values.
        Yii::app()->setConfig('publicurl', $tmpConfigPublicUrl);
        Yii::app()->getRequest()->baseUrl = $tmpRequestPublicUrl;
    }

    /**
     * Get public base url, previously set in baseUrl request attribute.
     * A public url is also set in the baseUrl config attribute,
     * but the one in the request attribute should be returned.
     */
    public function testGetpublicBaseUrlFromConfigNoHostInConfigPublicUrl()
    {
        $tmpConfigPublicUrl = Yii::app()->getConfig('publicurl');
        $tmpRequestPublicUrl = Yii::app()->getRequest()->getBaseUrl();

        Yii::app()->setConfig('publicurl', 'http://');
        Yii::app()->getRequest()->baseUrl = 'http://request.example.com/';
        $url = Yii::app()->getPublicBaseUrl();

        $this->assertSame($url, 'http://request.example.com/', 'Unexpected url. The url does not correspond to the one previously set.');

        // Restore original values.
        Yii::app()->setConfig('publicurl', $tmpConfigPublicUrl);
        Yii::app()->getRequest()->baseUrl = $tmpRequestPublicUrl;
    }

    /**
     * Create a public url with just a route.
     */
    public function testCreatePublicUrlWithARoute()
    {
        $tmpPublicUrl = Yii::app()->getConfig('publicurl');

        Yii::app()->setConfig('publicurl', 'http://www.example.com/');
        $url = Yii::app()->createPublicUrl('controller/action');

        $expectedRelativeUrl = Yii::app()->createUrl('controller/action');
        $this->assertSame($url, 'http://www.example.com' . $expectedRelativeUrl, 'Unexpected url. The url does not correspond with a public url and a route.');

        // Restore original values.
        Yii::app()->setConfig('publicurl', $tmpPublicUrl);
    }

    /**
     * Create a public url with a route and two parameters.
     */
    public function testCreatePublicUrlWithParams()
    {
        $tmpPublicUrl = Yii::app()->getConfig('publicurl');

        Yii::app()->setConfig('publicurl', 'http://www.example.com/');
        $parameters = array('param_one' => 1, 'param_two' => 2);
        $url = Yii::app()->createPublicUrl('controller/action', $parameters);

        $expectedRelativeUrl = Yii::app()->createUrl('controller/action', $parameters);
        $this->assertSame($url, 'http://www.example.com' . $expectedRelativeUrl, 'Unexpected url. The url does not correspond with a public url, a route and two parameters.');

        // Restore original values.
        Yii::app()->setConfig('publicurl', $tmpPublicUrl);
    }

    /**
     * Create a public url with a specific schema.
     */
    public function testCreatePublicUrlWithASchema()
    {
        $tmpConfigPublicUrl = Yii::app()->getConfig('publicurl');
        $tmpRequestPublicUrl = Yii::app()->getRequest()->getBaseUrl();

        Yii::app()->setConfig('publicurl', 'http://www.example.com');
        Yii::app()->getRequest()->baseUrl = 'www.example.com';
        Yii::app()->getRequest()->hostInfo = '';

        $parameters = array('param_one' => 1, 'param_two' => 2);
        $url = Yii::app()->createPublicUrl('controller/action', $parameters, 'http');

        $expectedRelativeUrl = Yii::app()->createUrl('controller/action', $parameters);
        $this->assertSame($url, 'http://www.example.com' . $expectedRelativeUrl, 'Unexpected url. The url does not correspond with a public url, a route and two parameters.');

        // Restore original values.
        Yii::app()->setConfig('publicurl', $tmpConfigPublicUrl);
        Yii::app()->getRequest()->baseUrl = $tmpRequestPublicUrl;
        self::$testHelper->resetHostInfo();
    }
}
