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
 * -- Insert database row (depends on extension type)
 *
 * @since 2018-09-24
 * @author Olle Haerstedt
 */
abstract class ExtensionInstaller
{
    /**
     * Class responsible for fetching files from source.
     * @var FileFetcher
     */
    protected $fileFetcher;

    /**
     * @param FileFetcher $fileFetcher
     * @return void
     */
    public function setFileFetcher(FileFetcher $fileFetcher)
    {
        $this->fileFetcher = $fileFetcher;
    }

    /**
     * Order the file fetcher to fetch files.
     * @return void
     * @throws Exception
     */
    abstract public function fetchFiles();

    /**
     * Get the configuration from temp dir.
     * Before an extension is installed, we need to read the config
     * file. That's why the extension if fetched into a temp folder
     * first.
     * @return ExtensionConfig
     */
    abstract public function getConfig();

    /**
     * Install extension, which includes moving files
     * from temp dir to final dir, and creating the necessary
     * database changes.
     * @return void
     */
    abstract public function install();

    /**
     * Update extension.
     * @return void
     */
    abstract public function update();

    /**
     * Uninstall the extension.
     * @return void
     */
    abstract public function uninstall();

    /**
     * Installation procedure was not completed, abort changes.
     * @return void
     */
    abstract public function abort();
}
