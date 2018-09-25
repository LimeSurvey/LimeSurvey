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

        $this->fileFetcher->move($destdir);

        $pluginManager = App()->getPluginManager();
        list($result, $errorMessage) = $pluginManager->installUploadedPlugin($destdir);
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
     * 
     */
    public function abort()
    {
        if ($this->fileFetcher) {
            $this->fileFetcher->abort();
        }
    }
}
