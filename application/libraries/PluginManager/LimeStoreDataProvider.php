<?php

namespace LimeSurvey\PluginManager;

use CArrayDataProvider;

class LimeStoreDataProvider extends CArrayDataProvider
{
    public function getData($refresh = false)
    {
        $url = 'https://comfortupdate.limesurvey.org/index.php?r=limestorerest&extension_type=p';
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type:application/json']);
        $result = json_decode(curl_exec($ch));
        curl_close($ch);

        return $result;
    }

    protected function fetchKeys()
    {
        return ['extension_name'];
    }
}
