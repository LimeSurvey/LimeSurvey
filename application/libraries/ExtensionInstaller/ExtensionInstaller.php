<?php

namespace LimeSurvey\ExtensionInstaller;

/**
 * Base class for different extension installers.
 *
 * All extension have this in common:
 * - Upload ZIP file or grab files from web (e.g. git repo)
 * - Read config.xml
 * - If config.xml is valid and the extension compatible with current version of LimeSurvey, then
 * -- Copy files to correct folder (depends on extension type)
 * -- Install database row (depends on extension type)
 *
 * @since 2018-09-24
 * @author Olle Haerstedt
 */
abstract class ExtensionInstaller
{
    /**
     * @var FileFetcher
     */
    public $fileFetcher;

    /**
     * @param FileFetcher $fileFetcher
     * @return void
     */
    public function setFileFetcher(FileFetcher $fileFetcher)
    {
        $this->fileFetcher = $fileFetcher;
    }

    /**
     * @return array [boolean $result, string $errorMessage]
     */
    abstract public function fetchFiles();

    /**
     * @todo Should return wrapper class for XML.
     * @return SimpleXMLElement
     */
    abstract public function getConfig();

    /**
     * @return void
     */
    abstract public function install();

    /**
     * @return void
     */
    abstract public function abort();
}
