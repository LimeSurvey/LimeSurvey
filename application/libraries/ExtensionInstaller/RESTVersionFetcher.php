<?php

namespace LimeSurvey\ExtensionInstaller;

/**
 * @since 2018-09-26
 * @author Olle Haerstedt
 */
class RESTVersionFetcher extends VersionFetcher
{
    /**
     * @param string $extensionName
     * @return ExtensionUpdateInfo
     */
    public function getLatestVersion()
    {
        // curl into source for this extension name.
        die('here');
    }
}
