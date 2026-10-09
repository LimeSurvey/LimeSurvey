<?php

namespace ls\tests;

use LimeSurvey\PluginManager\PluginManager;

class PluginManagerTest extends TestBaseClass
{
    public function testValidatePluginNameAllowsFlatClassNames()
    {
        $pluginManager = new PluginManager();

        $this->assertTrue($pluginManager->validatePluginName('Authdb'));
        $this->assertTrue($pluginManager->validatePluginName('Foo_Bar'));
        $this->assertTrue($pluginManager->validatePluginName('Plugin123'));
    }

    public function testValidatePluginNameRejectsUnsafeOrUnsupportedNames()
    {
        $pluginManager = new PluginManager();

        $cases = [
            '',
            '1Plugin',
            'Foo-Bar',
            'Foo.Bar',
            'Vendor\\Plugin',
            'foo/bar',
            '../twig/extensions/PoC',
        ];

        foreach ($cases as $case) {
            $this->assertFalse(
                $pluginManager->validatePluginName($case),
                'Expected invalid plugin name to be rejected: ' . json_encode($case)
            );
        }
    }

    /**
     * Getting the plugin manager from the application while the plugins are loaded
     * (e.g. in afterPluginLoad) must return the plugin manager being initialized,
     * not create a new one that loads the plugins again.
     * @return void
     */
    public function testPluginManagerIsAvailableDuringPluginLoad()
    {
        require_once self::$dataFolder . '/plugins/AfterPluginLoadTestPlugin.php';
        self::installAndActivatePlugin('AfterPluginLoadTestPlugin');
        \AfterPluginLoadTestPlugin::$afterPluginLoadCount = 0;
        \AfterPluginLoadTestPlugin::$applicationPluginManager = null;

        try {
            // Drop the current instance, so the next call creates and initializes a new plugin manager.
            \Yii::app()->setComponent('pluginManager', null);
            $pluginManager = \Yii::app()->getPluginManager();
        } finally {
            self::deActivatePlugin('AfterPluginLoadTestPlugin');
        }

        $this->assertSame(1, \AfterPluginLoadTestPlugin::$afterPluginLoadCount, 'Plugins were loaded more than once.');
        $this->assertSame($pluginManager, \AfterPluginLoadTestPlugin::$applicationPluginManager);
        $this->assertSame($pluginManager, \Yii::app()->getPluginManager());
    }
}
