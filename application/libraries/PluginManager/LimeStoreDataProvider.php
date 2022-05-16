<?php

namespace LimeSurvey\PluginManager;

use CArrayDataProvider;

class LimeStoreDataProvider extends CArrayDataProvider
{
    public function __construct($rawData, $config = [])
    {
        $this->rawData = $this->getLimestoreExtensions();
    }

    /**
     * @return array[]
     */
    public function getLimestoreExtensions()
    {
        $url = 'https://comfortupdate.limesurvey.org/index.php?r=limestorerest&extension_type=p';
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type:application/json', 'Cache-Control: no-cache']);
        curl_setopt($ch, CURLOPT_ENCODING, 'gzip');
        curl_setopt($ch, CURLOPT_ENCODING, 'gzip');
        curl_setopt($ch, CURLOPT_FRESH_CONNECT, TRUE);
        $result = json_decode(curl_exec($ch));
        curl_close($ch);
        return $result;
    }

    protected function fetchKeys()
    {
        return ['extension_name'];
    }
}
