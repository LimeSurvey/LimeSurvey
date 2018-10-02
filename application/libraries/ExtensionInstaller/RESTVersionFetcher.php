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
        // Simple CURL to source to fetch extension information.
        $url = $this->source;
        $url .= '&extension_name=' . $this->extensionName;
        $url .= '&extension_type=' . $this->extensionType;
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $content = json_decode(curl_exec($ch));
        curl_close($ch);

        if ($content && count($content) === 1) {
            return $content[0]->version;
        } else {
            return null;
        }
    }
}
