<?php

namespace ls\tests;

use LimeSurvey\PluginManager\PluginManager;
use LimeSurvey\PluginManager\PluginManagerShutdownFunction;

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

    /**
     * A plugin that is not installed and fails to load when scanning the plugin folders
     * must be listed with its error, but must not be saved to the database.
     * Otherwise it stays marked as faulty after its files were fixed.
     * @return void
     */
    public function testScanPluginsDoesNotSaveLoadErrorOfNotInstalledPlugin()
    {
        $pluginName = 'ScanLoadErrorTestPlugin';
        $pluginDir = \Yii::getPathOfAlias('uploaddir.plugins') . DIRECTORY_SEPARATOR . $pluginName;
        mkdir($pluginDir);
        file_put_contents(
            $pluginDir . DIRECTORY_SEPARATOR . 'config.xml',
            '<?xml version="1.0" encoding="UTF-8"?>'
            . '<config><metadata><name>' . $pluginName . '</name><type>plugin</type>'
            . '<version>1.0.0</version><description>Test</description></metadata>'
            . '<compatibility><version>7</version></compatibility></config>'
        );
        file_put_contents(
            $pluginDir . DIRECTORY_SEPARATOR . $pluginName . '.php',
            '<?php throw new \Exception("Broken test plugin");'
        );
        // Load errors are only handled (instead of thrown) without debug mode.
        $debug = \Yii::app()->getConfig('debug');
        \Yii::app()->setConfig('debug', 0);

        try {
            $result = \Yii::app()->getPluginManager()->scanPlugins();
        } finally {
            \Yii::app()->setConfig('debug', $debug);
            unlink($pluginDir . DIRECTORY_SEPARATOR . 'config.xml');
            unlink($pluginDir . DIRECTORY_SEPARATOR . $pluginName . '.php');
            rmdir($pluginDir);
        }

        $this->assertArrayHasKey($pluginName, $result);
        $this->assertSame(1, $result[$pluginName]['load_error']);
        $this->assertSame('upload', $result[$pluginName]['pluginType']);
        $this->assertStringContainsString('Broken test plugin', $result[$pluginName]['load_error_message']);
        $this->assertNull(\Plugin::model()->findByAttributes(['name' => $pluginName]));
    }

    /**
     * A fatal error of a plugin that is not installed (caught by the shutdown function
     * while scanning the plugin folders) must not be saved to the database.
     * @return void
     */
    public function testShutdownFunctionDoesNotSaveNotInstalledPlugin()
    {
        $pluginName = 'NotInstalledShutdownTestPlugin';
        $shutdownFunction = new PluginManagerShutdownFunction();
        $shutdownFunction->enable();
        $shutdownFunction->setPluginName($pluginName);

        ob_start();
        try {
            $shutdownFunction();
        } finally {
            $output = ob_get_clean();
        }

        $this->assertStringContainsString('This plugin is not installed.', $output);
        $this->assertNull(\Plugin::model()->findByAttributes(['name' => $pluginName]));
    }
}
