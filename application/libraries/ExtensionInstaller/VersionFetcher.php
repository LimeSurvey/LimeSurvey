<?php

namespace LimeSurvey\ExtensionInstaller;

/**
 * @since 2018-09-26
 * @author Olle Haerstedt
 */
abstract class VersionFetcher
{
    /**
     * Set source to fetch version information. Can be URL to REST API, git repo, etc.
     * @param string $source
     */
    abstract public function setSource($source);

    /**
     * Get latest version for this extension.
     * @return string Semantic versioning string.
     */
    abstract public function getLatestVersion();

    /**
     * 
     */
    public function versionIsStable()
    {
        
    }

    /**
     * 
     */
    public function versionIsSecurityUpdate()
    {
        
    }
}
