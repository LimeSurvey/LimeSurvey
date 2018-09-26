<?php

namespace LimeSurvey\ExtensionInstaller;

/**
 * @since 2018-09-26
 * @author Olle Haerstedt
 */
abstract class ExtensionUpdater
{
    /**
     * @var VersionFetcher
     */
    protected $versionFetcher;

    /**
     * @param VersionFetcher $vf
     * @return void
     */
    public function setVersionFetcher(VersionFetcher $vf)
    {
        $this->versionFetcher = $vf;
    }

    /**
     * Use the version fetcher to get info about available updates for 
     * this extension.
     * @return ?
     */
    public function getAvailableUpdates()
    {
        
    }
}
