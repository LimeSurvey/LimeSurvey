<?php

namespace ls\tests;

/**
 * The plugin type shown in the plugin list, read from the "category" element of the plugin's config.xml.
 */
class PluginCategoryTest extends TestBaseClass
{
    /**
     * Every core plugin declares a known category
     */
    public function testCorePluginsDeclareKnownCategory()
    {
        $labels = \Plugin::getCategoryLabels();
        $pluginDirs = glob(\Yii::app()->getBasePath() . '/core/plugins/*/config.xml');
        $this->assertNotEmpty($pluginDirs);
        foreach ($pluginDirs as $configFile) {
            $config = new \ExtensionConfig(simplexml_load_file($configFile));
            $this->assertArrayHasKey($config->getCategory(), $labels, basename(dirname($configFile)) . ' has no known category');
        }
    }

    /**
     * Known categories are translated, unknown ones shown as given, a missing one is empty
     */
    public function testExtensionConfigCategory()
    {
        $config = new \ExtensionConfig(simplexml_load_string('<config><metadata><name>A</name><category> captcha </category></metadata></config>'));
        $this->assertSame('captcha', $config->getCategory());
        $config = new \ExtensionConfig(simplexml_load_string('<config><metadata><name>A</name></metadata></config>'));
        $this->assertSame('', $config->getCategory());
    }

    /**
     * The plugin list label: translated category, or "Other" when the config.xml has none or cannot be read
     */
    public function testCategoryLabel()
    {
        $plugin = new \Plugin();
        $plugin->name = 'Authdb';
        $plugin->plugin_type = 'core';
        $this->assertSame(gT('Authentication'), $plugin->getCategoryLabel());
        $this->assertSame(gT('Authentication'), $plugin->categoryLabel, 'The plugin list sorts by this property');

        $plugin = new \Plugin();
        $plugin->name = 'NoSuchPluginForCategoryTest';
        $plugin->plugin_type = 'core';
        $this->assertSame(gT('Other'), $plugin->getCategoryLabel());
    }
}
