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
        $questionThemeName = $extConfig->getName();
        if (!$this->validateQuestionThemeName($questionThemeName)) {
            throw new Exception(gT('Invalid question theme name in config.xml'));
        }
        $destdir = App()->getConfig('userquestionthemerootdir') . DIRECTORY_SEPARATOR . $questionThemeName;

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
        $questionThemeName = $extConfig->getName();
        if (!$this->validateQuestionThemeName($questionThemeName)) {
            throw new Exception(gT('Invalid question theme name in config.xml'));
        }
        $destdir = App()->getConfig('userquestionthemerootdir') . DIRECTORY_SEPARATOR . $questionThemeName;

        if ($this->fileFetcher->move($destdir)) {
            $questionTheme =  QuestionTheme::model()->findByAttributes(['name' => $questionThemeName]);
            if (empty($questionTheme)) {
                throw new Exception('Tried to update question theme but found no theme with name ' . $questionThemeName);
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

    /**
     * Validate that a question theme name is safe to use for installation.
     *
     * @param string $questionThemeName
     * @return bool
     */
    public function validateQuestionThemeName($questionThemeName)
    {
        // TODO: Should this be stricter, e.g. only allow alphanumeric characters plus hyphens and underscores?

        // Reject path traversal and other unsafe path component values.
        \Yii::import('application.helpers.sanitize_helper', true);
        if (!validate_path_component($questionThemeName)) {
            return false;
        }

        // Match the QuestionTheme model validation limit for the name field.
        if (mb_strlen((string) $questionThemeName, 'UTF-8') > 150) {
            return false;
        }

        return true;
    }
}
