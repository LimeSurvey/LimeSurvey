<?php

namespace LimeSurvey\ExtensionInstaller;

/**
 * @since 2018-09-24
 * @author Olle Haerstedt
 */
class PluginInstaller extends ExtensionInstaller
{
    /**
     * @var FileFetcher
     */
    public $fileFetcher;

    /**
     * Core, upload or user.
     * @var string
     */
    protected $pluginType;

    /**
     * @return void
     */
    public function fetchFiles()
    {
        if (empty($this->fileFetcher)) {
            throw new \InvalidArgumentException(gT('fileFetcher is not set'));
        }

        $this->fileFetcher->fetch();
    }

    /**
     * Install unzipped package into correct folder.
     * @return void
     */
    public function install()
    {
        if (empty($this->fileFetcher)) {
            throw new \InvalidArgumentException(gT('fileFetcher is not set'));
        }

        $config = $this->getConfig();
        $pluginManager = App()->getPluginManager();
        $destdir = $pluginManager->getPluginFolder($config, $this->pluginType);

        if ($this->fileFetcher->move($destdir)) {
            list($result, $errorMessage) = $pluginManager->installUploadedPlugin($destdir);
            if ($result) {
                // Do nothing.
            } else {
                throw new \Exception($errorMessage);
            }
        } else {
            throw new \Exception('Could not move files.');
        }
    }

    /**
     * @todo
     */
    public function uninstall()
    {
        throw new \Exception('Not implemented');
    }

    /**
     * @return SimpleXMLElement
     */
    public function getConfig()
    {
        if ($this->fileFetcher) {
            return $this->fileFetcher->getConfig();
        } else {
            return null;
        }
    }

    /**
     * @return void
     */
    public function abort()
    {
        if ($this->fileFetcher) {
            $this->fileFetcher->abort();
        }
    }

    /**
     * @param string $pluginType
     * @return void
     */
    public function setPluginType($pluginType)
    {
        $this->pluginType = $pluginType;
    }
}
