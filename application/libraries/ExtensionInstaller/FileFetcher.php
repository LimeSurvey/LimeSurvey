<?php

namespace LimeSurvey\ExtensionInstaller;

/**
 * Fetch files for the ExtensionInstaller to install.
 * Fetching files can happen in different ways:
 * - Upload ZIP
 * - wget ZIP from a URL
 * - Enter git repo
 * - Etc.
 * Each method is its own subclass of this class.
 * Each extension type can support a number of different file fetch methods.
 *
 * @since 2018-09-24
 * @author Olle Haerstedt
 */
abstract class FileFetcher
{
    /**
     * Set source for this file fetcher.
     * Can be ZIP file name, git repo URL, folder name, etc.
     * @param string $source
     * @return void
     */
    abstract public function setSource($source);

    /**
     * Move files from source to tmp/ folder.
     * @return void
     */
    abstract public function fetch();

    /**
     * Move files from tmp/ folder to final destination.
     * @param string $destdir
     * @return boolean
     */
    abstract public function move($destdir);

    /**
     * @return ExtensionConfig
     * @throws Exception if config cannot be parsed.
     */
    abstract public function getConfig();

    /**
     * Abort procedure, remove temporary files.
     * @return void
     */
    abstract public function abort();
}
