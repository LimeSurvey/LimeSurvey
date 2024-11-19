<?php

namespace LimeSurvey\ExtensionInstaller;

use Exception;
use Throwable;
use ExtensionConfig;
use QuestionTheme;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

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
     * Installs new question theme.
     * Assumes it has been checked that is not already installed. If it is, use update() instead.
     *
     * @return void
     */
    public function install()
    {
        $extConfig = $this->getConfig();
        $destdir = App()->getConfig('userquestionthemerootdir') . DIRECTORY_SEPARATOR . $extConfig->getName();

        if ($this->fileFetcher->move($destdir)) {
            $questionTheme = new QuestionTheme();
            $xmlFolder = $this->getXmlFolder($destdir);
            if (empty($xmlFolder)) {
                throw new Exception('Found no xml folder for question theme');
            }
            $questionTheme->importManifest($xmlFolder, false, true);
            $this->fileFetcher->abort();
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
                gT("An error occurred while generating the Question theme"),
                'error'
            );
            $this->getController()->redirect(array("themeOptions/index#questionthemes"));
        }
        if (count($importErrors) > 0) {
            Yii::app()->setFlashMessage(gT("Some of the themes couldn't be imported."), 'error');
        }
         */
    }

    /**
     * Update an existing question theme.
     *
     * @throws Exception
     */
    public function update()
    {
        $extConfig = $this->getConfig();
        $destdir = App()->getConfig('userquestionthemerootdir') . DIRECTORY_SEPARATOR . $extConfig->getName();

        if ($this->fileFetcher->move($destdir)) {
            $questionTheme =  QuestionTheme::model()->findByAttributes(['name' => $extConfig->getName()]);
            if (empty($questionTheme)) {
                throw new Exception('Tried to update question theme but found no theme with name ' . $extConfig->getName());
            }
            $xmlFolder = $this->getXmlFolder($destdir);
            if (empty($xmlFolder)) {
                throw new Exception('Found no xml folder for question theme');
            }
            $questionTheme->importManifest($xmlFolder, false, true);
            $this->fileFetcher->abort();
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
     * Returns absolute path of folder inside $destdir that has config.xml in it.
     *
     * @param string $dir Root dir of question theme
     * @return string|null Folder as string if config.xml is found; otherwise null
     */
    protected function getXmlFolder($dir)
    {
        $it = new RecursiveDirectoryIterator($dir);
        // @see https://stackoverflow.com/questions/1860393/recursive-file-search-php
        foreach (new RecursiveIteratorIterator($it) as $key => $file) {
            // @see https://stackoverflow.com/questions/619610/whats-the-most-efficient-test-of-whether-a-php-string-ends-with-another-string?lq=1
            if (stripos(strrev((string) $file), strrev('config.xml')) === 0) {
                return dirname((string) $key);
            }
        }
        return null;
    }
}
