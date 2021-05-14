<?php

namespace LimeSurvey\ExtensionInstaller;

use Exception;
use ExtensionConfig;

/**
 */
class QuestionThemeInstaller extends ExtensionInstaller
{
    /**
     * @return ExtensionConfig
     * @todo Move to parent class?
     */
    public function getConfig()
    {
        assert(!empty($this->fileFetcher), 'File fetcher must be set');

        return $this->fileFetcher->getConfig();
    }

    /**
     * @return void
     */
    public function install()
    {
        //$config = $this->getConfig();
        // todo
    }

    public function update()
    {
        throw new Exception('Not implemented');
    }

    /**
     * @todo
     */
    public function uninstall()
    {
        throw new Exception('Not implemented');
    }
}
