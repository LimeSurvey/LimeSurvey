<?php

namespace ls\tests;

class EmailPluginTest extends TestBaseClass
{
    protected static $plugin;

    /**
     * Activate plugin
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        require_once self::$dataFolder . '/plugins/EmailTestPlugin.php';

        $plugin = self::installAndActivatePlugin('EmailPlugin');

        // Get a handle to the plugin being tested
        self::$plugin = App()->getPluginManager()->loadPlugin('EmailPlugin', $plugin->id);
    }

    public function testIsCurrentEmailPlugin()
    {
        $isCurrentEmailPlugin = self::$plugin->IsTestCurrentEmailPlugin();
        $this->assertFalse($isCurrentEmailPlugin);

        //Set test plugin as the global email method
        \Yii::app()->setConfig('emailmethod', 'plugin');
        \Yii::app()->setConfig('emailplugin', 'EmailPlugin');

        $isCurrentEmailPlugin = self::$plugin->IsTestCurrentEmailPlugin();
        $this->assertTrue($isCurrentEmailPlugin, 'The test plugin is not the global email method.');
    }

    public function testInvalidCredentials()
    {
        // Empty credentials.
        $credentials = array();

        $valid = self::$plugin->validateTestPluginCredentials($credentials);
        $this->assertFalse($valid, 'An empty credentials array is not valid.');

        // Incomplete credentials.
        // The credentials array must have a clientId and a clientSecret.
        $credentials['clientId'] = 'TH1S1SAN1D';

        $valid = self::$plugin->validateTestPluginCredentials($credentials);
        $this->assertFalse($valid, 'An incomplete credentials array is not valid.');
    }

    public function testValidCredentials()
    {
        // Valid credentials.
        $credentials = array(
            'clientId' => 'TH1S1SAN1D',
            'clientSecret' => 'TH1S1S-TH3CL13NT-S3CR3T',
        );

        $valid = self::$plugin->validateTestPluginCredentials($credentials);
        $this->assertTrue($valid, $credentials . ' is a valid credentials array.');
    }

    public function testGetCredentials()
    {
        $credentials = self::$plugin->getTestPluginCredentials();
        $nullSettings = array(
            'clientId' => null,
            'clientSecret' => null,
        );

        $this->assertSame($nullSettings, $credentials, 'The initial settings shoul be null.');

        $settings = array(
            'clientId' => 'CL13NT1D',
            'clientSecret' => 'S3CR3T',
        );
        self::$plugin->saveTestPluginSettings($settings);

        $credentials = self::$plugin->getTestPluginCredentials();
        $this->assertSame($settings, $credentials, 'The settings were not initialized correctly.');
    }

    public function testCredentialsChange()
    {
        $currentCredentials = array(
            'clientId' => 'CL13NT1D',
            'clientSecret' => 'S3CR3T',
        );

        $change = self::$plugin->haveTestPluginCredentialsChanged($currentCredentials, $currentCredentials);
        $this->assertFalse($change, 'The credentials did not change.');

        $newCredentials = array(
            'clientId' => 'N3WCL13NT1D',
            'clientSecret' => 'N3W-CL13NT-S3CR3T',
        );

        $change = self::$plugin->haveTestPluginCredentialsChanged($currentCredentials, $newCredentials);
        $this->assertTrue($change, 'The credentials did change.');
    }

    public function testSaveRefreshToken()
    {
        $setToken = self::$plugin->getPluginProperty('refreshToken');
        $this->assertNull($token, 'No refresh token was set.');

        $setCredentials = self::$plugin->getPluginProperty('refreshTokenMetadata');
        $this->assertNull($credentials, 'No credentials were set.');

        $credentials = array(
            'clientId' => 'N3WCL13NT1D',
            'clientSecret' => 'N3W-CL13NT-S3CR3T',
        );

        self::$plugin->saveTestPluginRefreshToken('R3FRESH-T0K3N', $credentials);

        $setToken = self::$plugin->getPluginProperty('refreshToken');
        $this->assertSame('R3FRESH-T0K3N', $setToken, 'New refresh token was expected.');

        $setCredentials = self::$plugin->getPluginProperty('refreshTokenMetadata');
        $this->assertSame($credentials, $setCredentials, 'New credentials were expected.');
    }

    public function testSaveSettings()
    {
        $settings = array(
            'clientId' => '0THERCL13NT1D',
            'clientSecret' => '0TH3R-CL13NT-S3CR3T',
        );

        self::$plugin->saveTestPluginSettings($settings);

        $setToken = self::$plugin->getPluginProperty('refreshToken');
        $this->assertNull($token, 'The token was not cleared.');

        $setCredentials = self::$plugin->getPluginProperty('refreshTokenMetadata');
        $this->assertEmpty($credentials, 'The credentials were not cleared.');
    }

    public function testListPluginEmailsEvent()
    {
        $event = self::dispatchPluginEvent('EmailPlugin', 'listEmailPlugins', array());

        $this->assertInstanceOf(\PluginEvent::class, $event, 'The event object should be of \PluginEvent class.');
        $plugins = $event->get('plugins');
        $this->assertArrayHaskey('Test', $plugins, 'Test plugin was not registered, the event was not fired.');
        $testInfo = $plugins['Test'];
        $this->assertInstanceOf(\LimeSurvey\Datavalueobjects\EmailPluginInfo::class, $testInfo, 'testInfo should be an instance of LimeSurvey\Datavalueobjects\EmailPluginInfo.');
        $this->assertSame('Test Plugin', $testInfo->name, 'The plugin display name is not the one set.');
        $this->assertSame('EmailPlugin', $testInfo->class, 'The plugin class name is not the one set.');
    }
}
