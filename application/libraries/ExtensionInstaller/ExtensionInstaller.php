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
class ExtensionInstaller
{
    /**
     * @var FileFetcher
     */
    public $fileFetcher;

    /**
     * 
     */
    public function setFileFetcher(FileFetcher $fileFetcher)
    {
        $this->fileFetcher = $fileFetcher;
    }
}
