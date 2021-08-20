<?php

namespace LimeSurvey\PluginManager;

use LimeSurvey\Datavalueobjects\RemovedPlugin;

/**
 * Helper class used to clean up the list of installed plugins
 */
class PluginCleaner
{
    const REASON_INCOMPATIBLE = 'incompatible';
    const REASON_MISSING = 'missing';
    const REASON_OTHER = 'other';

    /** @var LimeSurvey\Datavalueobjects\RemovedPlugin[] list of removed plugins */
    protected $removedPlugins = [];

    /** @var array<string,string> texts to use on flash messages depending on the reason of removal */
    protected $messages = [];

    public function __construct()
    {
        $this->messages = [
            self::REASON_INCOMPATIBLE => gT("Plugin '%s' was uninstalled because it is not compatible with your LimeSurvey version."),
            self::REASON_MISSING => gT("Plugin '%s' was uninstalled because it was missing."),
            self::REASON_OTHER => gT("Plugin '%s' was uninstalled: %s"),
        ];
    }

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
                try {
                    if ($plugin->dirExists()) {
                        $this->addRemovedPlugin($plugin->name, self::REASON_INCOMPATIBLE);
                    } else {
                        $this->addRemovedPlugin($plugin->name, self::REASON_MISSING);
                    }
                } catch (\Exception $ex) {
                    $this->addRemovedPlugin($plugin->name, self::REASON_OTHER, $ex->getMessage());
                }
            }
        }
        return $this->getRemovedPluginsCount();
    }

    /**
     * Adds a plugin to the list of removed plugins
     * @param string $pluginName the name of the removed plugin
     * @param string $reason the reason why the plugin was removed
     * @param string|null $exceptionMessage
     */
    protected function addRemovedPlugin($pluginName, $reason, $exceptionMessage = null)
    {
        $this->removedPlugins[] = new RemovedPlugin($pluginName, $reason, $exceptionMessage);
    }

    /**
     * Returns the count of removed plugins
     * @return int
     */
    public function getRemovedPluginsCount()
    {
        return count($this->removedPlugins);
    }

    /**
     * Returns the list of removed plugin names
     * @return LimeSurvey\Datavalueobjects\RemovedPlugin[]
     */
    public function getRemovedPlugins()
    {
        return $this->removedPlugins;
    }

    /**
     * Get the messages corresponding to each removed plugin.
     */
    public function getMessages()
    {
        $messages = [];
        foreach ($this->removedPlugins as $removedPlugin) {
            if (array_key_exists($removedPlugin->reason, $this->messages)) {
                $baseMessage = $this->messages[$removedPlugin->reason];
            } else {
                $baseMessage = gT("Plugin '%s' was uninstalled.");
            }
            if ($removedPlugin->reason == self::REASON_OTHER) {
                $messages[] = sprintf($baseMessage, $removedPlugin->name, $removedPlugin->extraInfo);
            } else {
                $messages[] = sprintf($baseMessage, $removedPlugin->name);
            }
        }
        return $messages;
    }
}