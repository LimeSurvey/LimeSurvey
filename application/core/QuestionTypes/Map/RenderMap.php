<?php

/**
 * RenderClass for Map Question
 *  * The ia Array contains the following
 *  0 => string qid
 *  1 => string sgqa
 *  2 => string questioncode
 *  3 => string question
 *  4 => string type
 *  5 => string gid
 *  6 => string mandatory,
 *  7 => string conditionsexist,
 *  8 => string usedinconditions
 *  0 => string used in group.php for question count
 * 10 => string new group id for question in randomization group (GroupbyGroup Mode)
 *
 */
class RenderMap extends QuestionBaseRenderer
{
    public function getMainView()
    {
        return '/survey/questions/answer/map/location_mapservice';
    }

    public function getRows()
    {
        return;
    }

    public function render($sCoreClasses = '')
    {
        $coreClass = 'ls-answers map-item geoloc-item ' . $sCoreClasses;
        $iMapService = (int) ($this->getQuestionAttribute('location_mapservice') ?? 100);
        $currentLocation = $this->mSessionValue !== '' ? $this->mSessionValue : null;

        if ($iMapService === 1 && empty(sanitize_googleapikey(App()->getConfig('googleMapsAPIKey')))) {
            $iMapService = 100;
        }

        if ($iMapService == 1) {
            $answer = $this->renderGoogleMap($coreClass, $currentLocation, $iMapService);
        } else {
            $answer = $this->renderOpenStreetMap($coreClass, $currentLocation, $iMapService);
        }

        $this->registerAssets();
        return array($answer, [$this->sSGQA]);
    }

    private function renderGoogleMap($coreClass, $currentLocation, $iMapService)
    {
        $currentLatLong = $this->resolveGoogleLatLong($currentLocation);
        $strBuild = $this->buildLocationSaveFlags();
        $currentLocation = $currentLatLong[0] . ' ' . $currentLatLong[1];

        $sGoogleMapsAPIKey = sanitize_googleapikey(App()->getConfig('googleMapsAPIKey'));
        if (!empty($sGoogleMapsAPIKey)) {
            $this->aScriptFiles[] = [
                'path' => "//maps.googleapis.com/maps/api/js?sensor=false&key={$sGoogleMapsAPIKey}",
                'position' => LSYii_ClientScript::POS_BEGIN,
            ];
        }
        $this->aScriptFiles[] = [
            'path' => Yii::app()->getConfig('generalscripts') . 'map.js',
            'position' => LSYii_ClientScript::POS_END,
        ];

        $hideTip = $this->getQuestionAttribute('hide_tip');
        $questionHelp = false;
        $sQuestionHelpText = '';
        if ($hideTip !== null && $hideTip == 0) {
            $questionHelp = true;
            $sQuestionHelpText = gT('Drag and drop the pin to the desired location. You may also right click on the map to move the pin.');
        }

        return Yii::app()->twigRenderer->renderQuestion(
            $this->getMainView() . '/item',
            array(
                'extraclass'             => '',
                'coreClass'              => $coreClass,
                'freeTextId'             => 'answer' . $this->sSGQA,
                'name'                   => $this->sSGQA,
                'qid'                    => $this->oQuestion->qid,
                'basename'               => $this->sSGQA,
                'value'                  => $this->mSessionValue !== '' ? $this->mSessionValue : null,
                'kpclass'                => '',
                'currentLocation'        => $currentLocation,
                'strBuild'               => $strBuild,
                'location_mapservice'    => $iMapService,
                'location_mapzoom'       => $this->getQuestionAttribute('location_mapzoom') ?? 11,
                'location_mapheight'     => $this->getQuestionAttribute('location_mapheight') ?? 300,
                'questionHelp'           => $questionHelp,
                'question_text_help'     => $sQuestionHelpText,
                'inputsize'              => null,
                'placeholder'            => '',
                'withColumn'             => false,
            ),
            true
        );
    }

    private function renderOpenStreetMap($coreClass, $currentLocation, $iMapService)
    {
        [$currentCenter, $currentLatLong] = $this->resolveOsmLatLong($currentLocation);

        $this->aPackages = ['leaflet', 'devbridge-autocomplete'];
        $this->addScript(
            'sGlobalMapScriptVar',
            'LSmap=' . ls_json_encode([
                'geonameUser' => Yii::app()->getConfig('GeoNamesUsername'),
                'geonameLang' => Yii::app()->language,
            ]) . ";\nLSmaps=[];"
        );
        $this->addScript(
            'sThisMapScriptVar' . $this->sSGQA,
            "LSmaps['{$this->sSGQA}']=" . ls_json_encode([
                'zoomLevel' => $this->getQuestionAttribute('location_mapzoom') ?? 11,
                'latitude' => $currentCenter[0],
                'longitude' => $currentCenter[1],
            ]) . ';'
        );
        $this->aScriptFiles[] = [
            'path' => Yii::app()->getConfig('generalscripts') . 'map.js',
            'position' => LSYii_ClientScript::POS_END,
        ];
        Yii::app()->getClientScript()->registerCssFile(Yii::app()->getConfig('publicstyleurl') . 'map.css');

        $hideTip = $this->getQuestionAttribute('hide_tip');
        $questionHelp = false;
        $sQuestionHelpText = '';
        if ($hideTip !== null && $hideTip == 0) {
            $questionHelp = true;
            $sQuestionHelpText = gT('Click to set the location or drag and drop the pin. You may may also enter coordinates');
        }

        return Yii::app()->twigRenderer->renderQuestion(
            $this->getMainView() . '/item_100',
            array(
                'extraclass' => '',
                'coreClass' => $coreClass,
                'name' => $this->sSGQA,
                'qid' => $this->oQuestion->qid,
                'basename' => $this->sSGQA,
                'value' => $this->mSessionValue !== '' ? $this->mSessionValue : null,
                'strBuild' => '',
                'location_mapservice' => $iMapService,
                'location_mapzoom' => $this->getQuestionAttribute('location_mapzoom') ?? 11,
                'location_mapheight' => $this->getQuestionAttribute('location_mapheight') ?? 300,
                'questionHelp' => $questionHelp,
                'question_text_help' => $sQuestionHelpText,
                'location_value' => $currentLatLong[0] . ' ' . $currentLatLong[1],
                'currentLat' => $currentLatLong[0],
                'currentLong' => $currentLatLong[1],
                'inputsize' => null,
                'placeholder' => '',
                'withColumn' => false,
            ),
            true
        );
    }

    private function resolveGoogleLatLong($currentLocation)
    {
        $currentLatLong = $this->parseStoredLatLong($currentLocation);
        if ($currentLatLong !== null) {
            return $currentLatLong;
        }

        if ((int) ($this->getQuestionAttribute('location_nodefaultfromip') ?? 0) === 0) {
            // The IP lookup warns when the remote API is unreachable
            $currentLatLong = @$this->getLatLongFromIp(getIPAddress());
        }

        if (!empty($currentLatLong)) {
            return $currentLatLong;
        }

        $floatLat = '';
        $floatLng = '';
        $sDefaultcoordinates = trim(LimeExpressionManager::ProcessString(
            $this->getQuestionAttribute('location_defaultcoordinates') ?? '',
            $this->oQuestion->qid,
            array(),
            3,
            1,
            false,
            false,
            true
        ));
        if (strlen($sDefaultcoordinates) > 2 && strpos($sDefaultcoordinates, ' ')) {
            $LatLong = explode(' ', $sDefaultcoordinates);
            if (isset($LatLong[0]) && isset($LatLong[1])) {
                $floatLat = $LatLong[0];
                $floatLng = $LatLong[1];
            }
        }

        return array($floatLat, $floatLng);
    }

    private function resolveOsmLatLong($currentLocation)
    {
        $currentLatLong = $this->parseStoredLatLong($currentLocation);
        $currentCenter = $currentLatLong;

        if ($currentLatLong === null && (int) ($this->getQuestionAttribute('location_nodefaultfromip') ?? 0) === 0) {
            // The IP lookup warns when the remote API is unreachable
            $currentCenter = $currentLatLong = @$this->getLatLongFromIp(getIPAddress());
        }

        if (!$currentLatLong) {
            $currentLatLong = array('', '');
            $sDefaultcoordinates = trim(LimeExpressionManager::ProcessString(
                $this->getQuestionAttribute('location_defaultcoordinates') ?? '',
                $this->oQuestion->qid,
                array(),
                3,
                1,
                false,
                false,
                true
            ));
            $currentCenter = explode(' ', $sDefaultcoordinates);
            if (count($currentCenter) != 2) {
                $currentCenter = array('', '');
            }
        }

        return [$currentCenter, $currentLatLong];
    }

    private function parseStoredLatLong($currentLocation)
    {
        if (strlen((string) $currentLocation) > 2 && strpos((string) $currentLocation, ';')) {
            $currentLatLong = explode(';', (string) $currentLocation);
            return array($currentLatLong[0], $currentLatLong[1]);
        }

        return null;
    }

    private function buildLocationSaveFlags()
    {
        $strBuild = '';
        if (!empty($this->getQuestionAttribute('location_city'))) {
            $strBuild .= '2';
        }
        if (!empty($this->getQuestionAttribute('location_state'))) {
            $strBuild .= '3';
        }
        if (!empty($this->getQuestionAttribute('location_country'))) {
            $strBuild .= '4';
        }
        if (!empty($this->getQuestionAttribute('location_postal'))) {
            $strBuild .= '5';
        }

        return $strBuild;
    }

    /**
     * Looks up the geographic position of an IP address via ipinfodb.com (only if an API key is configured).
     *
     * @param string $sIPAddress IP address to look up
     * @return array{0: float, 1: float}|false|null [latitude, longitude], false if the lookup failed, null if no API key is set
     */
    private function getLatLongFromIp($sIPAddress)
    {
        $ipInfoDbAPIKey = Yii::app()->getConfig("ipInfoDbAPIKey");
        if ($ipInfoDbAPIKey) {
            // ipinfodb.com needs a key
            $context = stream_context_create(['http' => ['timeout' => 3]]);
            $sXML = file_get_contents("http://api.ipinfodb.com/v3/ip-city/?key=$ipInfoDbAPIKey&ip=$sIPAddress&format=xml", false, $context);
            $oXML = $sXML !== false ? simplexml_load_string($sXML) : false;
            if ($oXML !== false && $oXML->{'statusCode'} == "OK") {
                $lat = (float) $oXML->{'latitude'};
                $lng = (float) $oXML->{'longitude'};

                return (array($lat, $lng));
            } else {
                return false;
            }
        }
    }
}
