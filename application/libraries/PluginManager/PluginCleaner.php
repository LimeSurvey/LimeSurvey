<?php

namespace LimeSurvey\PluginManager;

/**
 * Helper class used to clean up the list of installed plugins
 */
class PluginCleaner
{
    /** @var array<string,string[]> list of removed plugin names, grouped by reason of removal */
    protected $removedPlugins = [];

    /** @var array<string,string> texts to use on flash messages depending on the reason of removal */
    protected $messages = [
        'incompatible' => "Plugin '%s' was uninstalled because it is not compatible with your LimeSurvey version.",
        'missing' => "Plugin '%s' was uninstalled because it was missing.",
    ];

    /**
     * Uninstalls incompatible or missing plugins
     * @return int the amount of plugins uninstalled
     */
    public function clean()
    {
        $this->removedPlugins = [];
        $plugins = \Plugin::model()->findAll(array('order' => 'name'));
        foreach ($plugins as $plugin) {
            // If plugin is missing or is not compatible, it will be uninstalled
            if (!$plugin->isCompatible()) {
                $plugin->delete();
                if ($plugin->dirExists()) {
                    $this->addRemovedPlugin($plugin->name, 'incompatible');
                } else {
                    $this->addRemovedPlugin($plugin->name, 'missing');
                }
            }
        }
        return $this->getRemovedPluginsCount();
    }

    /**
     * Adds a plugin to the list of removed plugins
     * @param string $pluginName the name of the removed plugin
     * @param string $reason the reason why the plugin was removed
     */
    protected function addRemovedPlugin($pluginName, $reason)
    {
        $this->removedPlugins[$reason][] = $pluginName;
    }

    /**
     * Returns the count of removed plugins
     * @return int
     */
    public function getRemovedPluginsCount()
    {
        $count = 0;
        foreach ($this->removedPlugins as $pluginNames) {
            $count += count($pluginNames);
        }
        return $count;
    }

    /**
     * Returns the list of removed plugin names, grouped by reason of removal.
     * @return array<string,string[]>
     */
    public function getRemovedPlugins()
    {
        return $this->removedPlugins;
    }

    /**
     * Sets the flash messages corresponding to each removed plugin.
     */
    public function showFlashMessages()
    {
        foreach ($this->removedPlugins as $reason => $pluginNames) {
            $baseMessage = array_key_exists($reason, $this->messages) ? $this->messages[$reason] : "Plugin '%s' was uninstalled.";
            foreach ($pluginNames as $pluginName) {
                \Yii::app()->setFlashMessage(sprintf(gT($baseMessage), $pluginName));
            }
        }
    }
}