<?php

namespace LimeSurvey\ExtensionInstaller;

use Exception;
use InvalidArgumentException;

/**
 * @since 2018-09-24
 * @author LimeSurvey GmbH
 */
class PluginInstaller extends ExtensionInstaller
{
    /**
     * Core, upload or user.
     * @var string
     */
    protected $pluginType;

    /**
     * Install unzipped package into correct folder.
     * Assumes file fetcher and config is set.
     * @return void
     * @throws Exception
     */
    public function install()
    {
        if (empty($this->fileFetcher)) {
            throw new InvalidArgumentException('fileFetcher is not set');
        }

        if (!$this->isWhitelisted()) {
            throw new Exception('The plugin is not in the plugin allowlist.');
        }

        $config = $this->getConfig();
        $pluginManager = App()->getPluginManager();
        $destdir = $pluginManager->getPluginFolder($config, $this->pluginType);

        if ($this->fileFetcher->move($destdir)) {
            list($result, $errorMessage) = $pluginManager->installUploadedPlugin($destdir);
            if ($result) {
                // Do nothing.
            } else {
                throw new Exception($errorMessage);
            }
        } else {
            throw new Exception('Could not move files.');
        }
    }

    /**
     * Update the plugin.
     * Assumes file fetcher and config is set.
     * @return void
     * @throws Exception
     */
    public function update()
    {
        if (empty($this->fileFetcher)) {
            throw new InvalidArgumentException('fileFetcher is not set');
        }

        if (!$this->isWhitelisted()) {
            throw new Exception('The plugin is not in the plugin allowlist.');
        }

        $config = $this->getConfig();
        $plugin = \Plugin::model()->find('name = :name', [':name' => $config->getName()]);

        if (empty($plugin)) {
            throw new Exception('Plugin is not installed, cannot update.');
        }

        $pluginManager = App()->getPluginManager();
        $destdir = $pluginManager->getPluginFolder($config, $this->pluginType);

        if ($this->fileFetcher->move($destdir)) {
            $plugin->version = $config->getVersion();
            $plugin->update();
        } else {
            throw new Exception('Could not move files.');
        }
    }

    /**
     * @todo
     */
    public function uninstall()
    {
        throw new Exception('Not implemented');
    }

    /**
     * @param string $pluginType
     * @return void
     */
    public function setPluginType($pluginType)
    {
        $this->pluginType = $pluginType;
    }

    /**
     * Returns true if the plugin name is allowlisted or the allowlist is disabled.
     * @return boolean
     */
    public function isWhitelisted()
    {
        if (empty($this->fileFetcher)) {
            throw new InvalidArgumentException('fileFetcher is not set');
        }

        $config = $this->getConfig();
        $pluginName = $config->getName();
        $pluginManager = App()->getPluginManager();

        return $pluginManager->isWhitelisted($pluginName);
    }
}
