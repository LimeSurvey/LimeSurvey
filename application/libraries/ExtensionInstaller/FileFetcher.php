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
     */
    abstract public function setSource($source);

    /**
     * Move files from source to tmp/ folder.
     * @tood
     */
    abstract public function fetch();

    /**
     * @todo Should return wrapper class for XML.
     * @return SimpleXMLElement|null
     * @throws Exception if config cannot be parsed.
     */
    abstract public function getExtensionConfig();
}
