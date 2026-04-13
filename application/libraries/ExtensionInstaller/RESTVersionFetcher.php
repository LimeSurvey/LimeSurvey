<?php

namespace LimeSurvey\ExtensionInstaller;

/**
 * @since 2018-09-26
 * @author LimeSurvey GmbH
 */
class RESTVersionFetcher extends VersionFetcher
{
    /**
     * Result from the curl fetch.
     * @var object
     */
    protected $curlResult = null;

    /**
     * @return string
     */
    public function getLatestVersion()
    {
        if (empty($this->curlResult)) {
            $this->fetchCurl();
        }

        if (empty($this->curlResult->version)) {
            throw new \Exception('Found no version field in curl result');
        }

        return $this->curlResult->version;
    }

    /**
     * @return string
     */
    public function getLatestSecurityVersion()
    {
        if (empty($this->curlResult)) {
            $this->fetchCurl();
        }

        if (empty($this->curlResult->last_security_version)) {
            //throw new \Exception('Found no last_security_version field in curl result');
            return '0.0.0';
        }

        return $this->curlResult->last_security_version;
    }

    /**
     * Contact remote server and fetch extension information.
     * @return void
     */
    protected function fetchCurl()
    {
        if (empty($this->source)) {
            throw new \Exception('Missing source');
        }

        if (empty($this->extensionName)) {
            throw new \Exception('Missing extension name');
        }

        if (empty($this->extensionType)) {
            throw new \Exception('Missing extension type');
        }

        // Simple CURL to source to fetch extension information.
        $url = $this->source;
        $url .= '&extension_name=' . $this->extensionName;
        $url .= '&extension_type=' . $this->extensionType;
        $ch = curl_init($url);
        if ($ch === false) {
            throw new \Exception('Could not open curl handle');
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $content = json_decode(curl_exec($ch));
        curl_close($ch);
        if ($content && count($content) === 1) {
            $this->curlResult = $content[0];
        } else {
            throw new \Exception('Could not fetch REST API information for extension ' . $this->extensionName);
        }
    }
}
