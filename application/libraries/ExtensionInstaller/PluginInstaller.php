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
            throw new \InvalidArgumentException('fileFetcher is not set');
        }

        $this->fileFetcher->fetch();
    }

    /**
     * 
     */
    public function install()
    {
        
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
