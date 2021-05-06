<?php

namespace LimeSurvey\ExtensionInstaller;

/**
 */
class QuestionThemeInstaller extends ExtensionInstaller
{
    /**
     * @return void
     * @todo Code duplication?
     */
    public function fetchFiles()
    {
        if (empty($this->fileFetcher)) {
            throw new \InvalidArgumentException('fileFetcher is not set');
        }

        $this->fileFetcher->fetch();
    }

    public function install()
    {
        $config = $this->getConfig();
    }

}
