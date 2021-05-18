<?php

namespace LimeSurvey\ExtensionInstaller;

use Exception;
use ExtensionConfig;
use QuestionTheme;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

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
        $extConfig = $this->getConfig();
        $destdir = App()->getConfig('userquestionthemerootdir') . DIRECTORY_SEPARATOR . $extConfig->getName();

        if ($this->fileFetcher->move($destdir)) {
            $nrOfImportedThemes = 0;
            $directory = new RecursiveDirectoryIterator($destdir);
            $iterator = new RecursiveIteratorIterator($directory);
            $importErrors = [];
            foreach ($iterator as $info) {
                if ($info->isFile() && $info->getBasename() == 'config.xml') {
                    $questionConfigFilePath = dirname($info->getPathname());
                    $questionThemeTitle = null;
                    try {
                        $questionTheme = new QuestionTheme();
                        $questionThemeTitle = $questionTheme->importManifest($questionConfigFilePath, false, true);
                    } catch (Throwable $t) {
                        $sThemeDirectoryName = $questionTheme->getThemeDirectoryPath($questionConfigFilePath . "/config.xml");
                        $importErrors[$sThemeDirectoryName] = $t->getMessage();
                    }
                    if (!empty($questionThemeTitle)) {
                        $nrOfImportedThemes++;
                    }
                }
            }
        } else {
            throw new Exception('Could not move files.');
        }

        // Question themes that apply to more than one question type, are technically different themes but can be distributed
        // in the same ZIP. So we must try to install all the available themes in the folder.
        /*
        $nrOfImportedThemes = 0;
        $directory = new RecursiveDirectoryIterator($destdir);
        $iterator = new RecursiveIteratorIterator($directory);
        $importErrors = [];
        foreach ($iterator as $info) {
            if ($info->isFile() && $info->getBasename() == 'config.xml') {
                $questionConfigFilePath = dirname($info->getPathname());
                $sQuestionThemeTitle = null;
                try {
                    $questionTheme = new QuestionTheme();
                    $sQuestionThemeTitle = $questionTheme->importManifest($questionConfigFilePath, false, true);
                } catch (Throwable $t) {
                    $sThemeDirectoryName = $questionTheme->getThemeDirectoryPath($questionConfigFilePath . "/config.xml");
                    $importErrors[$sThemeDirectoryName] = $t->getMessage();
                }
                if (!empty($sQuestionThemeTitle)) {
                    $nrOfImportedThemes++;
                }
            }
        }
        if ($nrOfImportedThemes == 0) {
            rmdirr($destdir);
            App()->setFlashMessage(
                gT("An error occured while generating the Question theme"),
                'error'
            );
            $this->getController()->redirect(array("themeOptions/index#questionthemes"));
        }
        if (count($importErrors) > 0) {
            Yii::app()->setFlashMessage(gT("Some of the themes couldn't be imported."), 'error');
        }
         */
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
