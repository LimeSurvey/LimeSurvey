<?php

namespace LimeSurvey\ExtensionInstaller;

/**
 * @since 2018-09-26
 * @author LimeSurvey GmbH
 */
class GitVersionFetcher extends VersionFetcher
{
    /**
     * @inherit
     */
    #[\Override]
    public function getLatestVersion()
    {
        return 'todo';
    }

    /**
     * @inherit
     */
    #[\Override]
    public function getLatestSecurityVersion()
    {
        return 'todo';
    }
}
