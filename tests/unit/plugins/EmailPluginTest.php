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

        $plugin = \Plugin::model()->findByAttributes(array('name' => 'EmailPlugin'));

        if (!$plugin) {
            $plugin = new \Plugin();
            $plugin->name = 'EmailPlugin';
            $plugin->active = 1;
            $plugin->save();
        } else {
            $plugin->active = 1;
            $plugin->save();
        }

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
        $this->assertTrue($isCurrentEmailPlugin);
    }

    public function testSaveSettings()
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
}
