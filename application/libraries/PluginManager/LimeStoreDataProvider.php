<?php

namespace LimeSurvey\PluginManager;

use CArrayDataProvider;

class LimeStoreDataProvider extends CArrayDataProvider
{
    public function __construct($rawData, $config = [])
    {
        error_log('constr');
        $url = 'https://comfortupdate.limesurvey.org/index.php?r=limestorerest&extension_type=p';
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type:application/json']);
        curl_setopt($ch, CURLOPT_ENCODING, 'gzip');
        $result = json_decode(curl_exec($ch));
        curl_close($ch);

        $this->rawData = $result;
    }

    protected function fetchKeys()
    {
        return ['extension_name'];
    }
}
