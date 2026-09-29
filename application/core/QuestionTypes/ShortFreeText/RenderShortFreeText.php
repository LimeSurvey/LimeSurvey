<?php

/**
 * RenderClass for Short Free Text Question (including the map/location variants)
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
class RenderShortFreeText extends QuestionBaseRenderer
{
    /**
     * Returns the twig view used to render the default (single text input) variant of the question.
     *
     * @return string
     */
    public function getMainView()
    {
        return '/survey/questions/answer/shortfreetext/text/item';
    }

    /**
     * Short free text questions have no rows.
     *
     * @return void
     */
    public function getRows()
    {
        return;
    }

    /**
     * Renders the short free text question as a text input, a textarea (display_rows set)
     * or a map (location_mapservice set), followed by the timer if time_limit is set.
     *
     * @param string $sCoreClasses Unused, kept for signature compatibility
     * @return array{0: string, 1: string[]} Rendered answer HTML and the list of input names
     */
    public function render($sCoreClasses = '')
    {
        global $thissurvey;

        $coreClass  = "ls-answers answer-item text-item";
        $extraclass = "";
        $sLanguage  = $this->getSessionLanguage();

        if ($this->getQuestionAttribute('numbers_only') == 1) {
            $sSeparator  = getRadixPointData($thissurvey['surveyls_numberformat'] ?? null);
            $sSeparator  = $sSeparator['separator'];
            $extraclass .= " numberonly";
            $coreClass  .= " numeric-item";
            $numberonly  = true;
        } else {
            $sSeparator = '';
            $numberonly = false;
        }

        if (intval(trim((string) $this->getQuestionAttribute('maximum_chars'))) > 0) {
            // Only maxlength attribute, use textarea[maxlength] jquery selector for textarea
            $maxlength   = intval(trim((string) $this->getQuestionAttribute('maximum_chars')));
            $extraclass .= " ls-input-maxchars";
        } else {
            $maxlength = "";
        }

        if (trim((string) $this->getQuestionAttribute('text_input_width')) != '' && intval(trim((string) $this->getQuestionAttribute('location_mapservice'))) == 0) {
            $col         = ($this->getQuestionAttribute('text_input_width') <= 12) ? $this->getQuestionAttribute('text_input_width') : 12;
            $extraclass .= " col-md-" . trim((string) $col);
            $withColumn  = true;
        } else {
            $withColumn = false;
        }

        if (ctype_digit(trim((string) $this->getQuestionAttribute('input_size')))) {
            $inputsize   = trim((string) $this->getQuestionAttribute('input_size'));
            $extraclass .= " ls-input-sized";
        } else {
            $inputsize = null;
        }

        if (trim((string) $this->getQuestionAttribute('prefix', $sLanguage)) != '') {
            $prefix      = $this->getQuestionAttribute('prefix', $sLanguage);
            $extraclass .= " withprefix";
        } else {
            $prefix = '';
        }

        if (trim((string) $this->getQuestionAttribute('suffix', $sLanguage)) != '') {
            $suffix      = $this->getQuestionAttribute('suffix', $sLanguage);
            $extraclass .= " withsuffix";
        } else {
            $suffix = '';
        }

        if (trim((string) $this->getQuestionAttribute('placeholder', $sLanguage)) != '') {
            $placeholder = $this->getQuestionAttribute('placeholder', $sLanguage);
        } else {
            $placeholder = '';
        }

        // Shared by all variants: the rendering helpers add their own specific values
        $aCommonData = array(
            'extraclass'  => $extraclass,
            'coreClass'   => $coreClass,
            'prefix'      => $prefix,
            'suffix'      => $suffix,
            'maxlength'   => $maxlength,
            'numberonly'  => $numberonly,
            'sSeparator'  => $sSeparator,
            'inputsize'   => $inputsize,
            'placeholder' => $placeholder,
            'withColumn'  => $withColumn,
        );

        if (trim((string) $this->getQuestionAttribute('display_rows')) != '') {
            $answer = $this->renderTextarea($aCommonData);
        } elseif ((int) ($this->getQuestionAttribute('location_mapservice')) == 1) {
            $answer = $this->renderMapService($aCommonData);
        } elseif ((int) ($this->getQuestionAttribute('location_mapservice')) == 100) {
            $answer = $this->renderLeafletMap($aCommonData);
        } else {
            $answer = $this->renderTextInput($aCommonData);
        }

        if (trim((string) $this->getQuestionAttribute('time_limit')) != '') {
            // The legacy renderer ran under the @ operator, keep suppressing the notices
            // return_timer_script() raises for missing (translated) timer attributes.
            $answer .= @return_timer_script($this->aQuestionAttributes, $this->aFieldArray, "answer" . $this->sSGQA);
        }

        $this->registerAssets();

        $inputnames = [];
        $inputnames[] = $this->sSGQA;
        return array($answer, $inputnames);
    }

    /**
     * Renders the textarea variant, used when the display_rows attribute is set.
     *
     * @param array $aCommonData Values shared by all variants, as built in render()
     * @return string Rendered HTML
     */
    private function renderTextarea(array $aCommonData)
    {
        //question attribute "display_rows" is set -> we need a textarea to be able to show several rows
        $drows = $this->getQuestionAttribute('display_rows');

        $dispVal = "";
        $mSessionValue = $this->getLegacySessionValue($this->sSGQA);
        if ($mSessionValue) {
            $dispVal = str_replace("\\", "", (string) $mSessionValue);

            if ($this->getQuestionAttribute('numbers_only') == 1) {
                $dispVal = str_replace('.', $aCommonData['sSeparator'], $dispVal);
            }
            $dispVal = htmlspecialchars($dispVal);
        }

        return Yii::app()->twigRenderer->renderQuestion('/survey/questions/answer/shortfreetext/textarea/item', array(
            'extraclass'             => $aCommonData['extraclass'],
            'coreClass'              => $aCommonData['coreClass'],
            'freeTextId'             => 'answer' . $this->sSGQA,
            'name'                   => $this->sSGQA,
            'basename'               => $this->sSGQA,
            'drows'                  => $drows,
            'dispVal'                => $dispVal,
            'maxlength'              => $aCommonData['maxlength'],
            'kpclass'                => '', // Old class of removed keypad functionality, kept for question theme backward compatibility
            'prefix'                 => $aCommonData['prefix'],
            'suffix'                 => $aCommonData['suffix'],
            'inputsize'              => $aCommonData['inputsize'],
            'placeholder'            => $aCommonData['placeholder'],
            'withColumn'             => $aCommonData['withColumn'],
            'numberonly'             => $aCommonData['numberonly'],
        ), true);
    }

    /**
     * Renders the map variant using Google Maps or OpenLayers (location_mapservice = 1)
     * and registers the needed map scripts.
     *
     * @param array $aCommonData Values shared by all variants, as built in render()
     * @return string Rendered HTML
     */
    private function renderMapService(array $aCommonData)
    {
        $coreClass         = "ls-answers map-item geoloc-item";
        $sQuestionHelpText = '';
        $currentLocation   = $this->getLegacySessionValue($this->sSGQA);
        $currentLatLong    = null;
        // Get the latitude/longitude for the point that needs to be displayed by default
        if (strlen((string) $currentLocation) > 2 && strpos((string) $currentLocation, ";")) { // Quick check if current location is OK
            $currentLatLong = explode(';', (string) $currentLocation);
            $currentLatLong = array($currentLatLong[0], $currentLatLong[1]);
        } else {
            if ((int) ($this->getQuestionAttribute('location_nodefaultfromip')) == 0) {
                // Keep the legacy @: the IP lookup warns when the remote API is unreachable
                $currentLatLong = @$this->getLatLongFromIp(getIPAddress());
            }

            if (empty($currentLatLong)) {
                $floatLat = "";
                $floatLng = "";
                $sDefaultcoordinates = trim(LimeExpressionManager::ProcessString($this->getQuestionAttribute('location_defaultcoordinates'), $this->oQuestion->qid, array(), 3, 1, false, false, true));/* static var is the last one */
                if (strlen($sDefaultcoordinates) > 2 && strpos($sDefaultcoordinates, " ")) {
                    $LatLong = explode(" ", $sDefaultcoordinates);
                    if (isset($LatLong[0]) && isset($LatLong[1])) {
                        $floatLat = $LatLong[0];
                        $floatLng = $LatLong[1];
                    }
                }
                $currentLatLong = array($floatLat, $floatLng);
            }
        }
        // 2 - city; 3 - state; 4 - country; 5 - postal
        $strBuild = "";
        if ($this->getQuestionAttribute('location_city')) {
            $strBuild .= "2";
        }
        if ($this->getQuestionAttribute('location_state')) {
            $strBuild .= "3";
        }
        if ($this->getQuestionAttribute('location_country')) {
            $strBuild .= "4";
        }
        if ($this->getQuestionAttribute('location_postal')) {
            $strBuild .= "5";
        }

        $currentLocation = ($currentLatLong[0] ?? null) . " " . ($currentLatLong[1] ?? null);

        Yii::app()->getClientScript()->registerScriptFile(Yii::app()->getConfig('generalscripts') . "map.js", LSYii_ClientScript::POS_END);
        $sGoogleMapsAPIKey = sanitize_googleapikey(App()->getConfig("googleMapsAPIKey"));
        // Always true for == 1 when reached from render() (the elseif is kept as in the legacy renderer)
        if ($this->getQuestionAttribute('location_mapservice') == 1 && !empty($sGoogleMapsAPIKey)) {
            Yii::app()->getClientScript()->registerScriptFile("//maps.googleapis.com/maps/api/js?sensor=false&key={$sGoogleMapsAPIKey}", LSYii_ClientScript::POS_BEGIN);
        } elseif ($this->getQuestionAttribute('location_mapservice') == 2) {
            /* 2019-04-01 : openlayers auto redirect to https (on firefox) , but always good to use automatic protocol */
            Yii::app()->getClientScript()->registerScriptFile("//www.openlayers.org/api/OpenLayers.js", LSYii_ClientScript::POS_BEGIN);
        }

        $questionHelp = false;
        if ($this->getQuestionAttribute('hide_tip') !== null && $this->getQuestionAttribute('hide_tip') == 0) {
            $questionHelp = true;
            $sQuestionHelpText = gT('Drag and drop the pin to the desired location. You may also right click on the map to move the pin.');
        }

        return Yii::app()->twigRenderer->renderQuestion('/survey/questions/answer/shortfreetext/location_mapservice/item', array(
            'extraclass'             => $aCommonData['extraclass'],
            'coreClass'              => $coreClass,
            'freeTextId'             => 'answer' . $this->sSGQA,
            'name'                   => $this->sSGQA,
            'qid'                    => $this->oQuestion->qid,
            'basename'               => $this->sSGQA,
            'value'                  => $this->getLegacySessionValue($this->sSGQA),
            'kpclass'                => '', // Old class of removed keypad functionality, kept for question theme backward compatibility
            'currentLocation'        => $currentLocation,
            'strBuild'               => $strBuild,
            'location_mapservice'    => $this->getQuestionAttribute('location_mapservice'),
            'location_mapzoom'       => $this->getQuestionAttribute('location_mapzoom'),
            'location_mapheight'     => $this->getQuestionAttribute('location_mapheight'),
            'questionHelp'           => $questionHelp,
            'question_text_help'     => $sQuestionHelpText,
            'inputsize'              => $aCommonData['inputsize'],
            'placeholder'            => $aCommonData['placeholder'],
            'withColumn'             => $aCommonData['withColumn']
        ), true);
    }

    /**
     * Renders the Leaflet/OpenStreetMap variant (location_mapservice = 100)
     * and registers the needed map packages, scripts and styles.
     *
     * @param array $aCommonData Values shared by all variants, as built in render()
     * @return string Rendered HTML
     */
    private function renderLeafletMap(array $aCommonData)
    {
        $coreClass         = "ls-answers map-item geoloc-item";
        $sQuestionHelpText = '';
        $currentLocation   = $this->getLegacySessionValue($this->sSGQA);
        $currentCenter     = $currentLatLong = null;
        // Get the latitude/longitude for the point that needs to be displayed by default
        if (strlen((string) $currentLocation) > 2 && strpos((string) $currentLocation, ";")) {
            $currentLatLong = explode(';', (string) $currentLocation);
            $currentCenter  = $currentLatLong = array($currentLatLong[0], $currentLatLong[1]);
        } elseif ((int) ($this->getQuestionAttribute('location_nodefaultfromip')) == 0) {
            // Keep the legacy @: the IP lookup warns when the remote API is unreachable
            $currentCenter = $currentLatLong = @$this->getLatLongFromIp(getIPAddress());
        }

        // If it's not set : set the center to the default position, but don't set the marker
        if (!$currentLatLong) {
            $currentLatLong = array("", "");
            $sDefaultcoordinates = trim(LimeExpressionManager::ProcessString($this->getQuestionAttribute('location_defaultcoordinates'), $this->oQuestion->qid, array(), 3, 1, false, false, true));/* static var is the last one */
            $currentCenter = explode(" ", $sDefaultcoordinates);
            if (count($currentCenter) != 2) {
                $currentCenter = array("", "");
            }
        }
        $strBuild = "";

        $aGlobalMapScriptVar = array(
            'geonameUser' => Yii::app()->getConfig('GeoNamesUsername'), // Did we need to urlencode ?
            'geonameLang' => Yii::app()->language,
        );
        $aThisMapScriptVar = array(
            'zoomLevel' => $this->getQuestionAttribute('location_mapzoom'),
            'latitude' => $currentCenter[0] ?? null,
            'longitude' => $currentCenter[1] ?? null,

        );
        App()->getClientScript()->registerPackage('leaflet');
        App()->getClientScript()->registerPackage('devbridge-autocomplete'); /* for autocomplete */
        Yii::app()->getClientScript()->registerScript('sGlobalMapScriptVar', "LSmap=" . ls_json_encode($aGlobalMapScriptVar) . ";\nLSmaps=[];", CClientScript::POS_BEGIN);
        Yii::app()->getClientScript()->registerScript('sThisMapScriptVar' . $this->sSGQA, "LSmaps['{$this->sSGQA}']=" . ls_json_encode($aThisMapScriptVar) . ";", CClientScript::POS_BEGIN);
        Yii::app()->getClientScript()->registerScriptFile(Yii::app()->getConfig('generalscripts') . "map.js", CClientScript::POS_END);
        Yii::app()->getClientScript()->registerCssFile(Yii::app()->getConfig('publicstyleurl') . 'map.css');

        if ($this->getQuestionAttribute('hide_tip') !== null && $this->getQuestionAttribute('hide_tip') == 0) {
            $questionHelp = true;
            $sQuestionHelpText = gT('Click to set the location or drag and drop the pin. You may may also enter coordinates');
        }

        $itemDatas = array(
            'extraclass' => $aCommonData['extraclass'],
            'coreClass' => $coreClass,
            'name' => $this->sSGQA,
            'qid' => $this->oQuestion->qid,
            'basename'               => $this->sSGQA,
            'value' => $this->getLegacySessionValue($this->sSGQA),
            'strBuild' => $strBuild,
            'location_mapservice' => $this->getQuestionAttribute('location_mapservice'),
            'location_mapzoom' => $this->getQuestionAttribute('location_mapzoom'),
            'location_mapheight' => $this->getQuestionAttribute('location_mapheight'),
            // Legacy quirk: '' (not false) when the tip is hidden
            'questionHelp' => $questionHelp ?? '',
            'question_text_help' => $sQuestionHelpText,
            'location_value' => ($currentLatLong[0] ?? null) . ' ' . ($currentLatLong[1] ?? null),
            'currentLat' => $currentLatLong[0] ?? null,
            'currentLong' => $currentLatLong[1] ?? null,
            'inputsize'              => $aCommonData['inputsize'],
            'placeholder'            => $aCommonData['placeholder'],
            'withColumn'             => $aCommonData['withColumn']
        );
        return Yii::app()->twigRenderer->renderQuestion('/survey/questions/answer/shortfreetext/location_mapservice/item_100', $itemDatas, true);
    }

    /**
     * Renders the default single text input variant.
     *
     * @param array $aCommonData Values shared by all variants, as built in render()
     * @return string Rendered HTML
     */
    private function renderTextInput(array $aCommonData)
    {
        //no question attribute set, use common input text field
        $dispVal = $this->getLegacySessionValue($this->sSGQA);
        if ($this->getQuestionAttribute('numbers_only') == 1) {
            $dispVal = str_replace('.', $aCommonData['sSeparator'], (string) $dispVal);
        }
        $dispVal = htmlspecialchars((string) $dispVal, ENT_QUOTES, 'UTF-8');
        $itemDatas = array(
            'extraclass' => $aCommonData['extraclass'],
            'coreClass' => $aCommonData['coreClass'],
            'name' => $this->sSGQA,
            'basename'               => $this->sSGQA,
            'prefix' => $aCommonData['prefix'],
            'suffix' => $aCommonData['suffix'],
            'kpclass' => '', // Old class of removed keypad functionality, kept for question theme backward compatibility
            'dispVal' => $dispVal,
            'maxlength' => $aCommonData['maxlength'],
            'numberonly' => $aCommonData['numberonly'],
            'inputsize'              => $aCommonData['inputsize'],
            'placeholder'            => $aCommonData['placeholder'],
            'withColumn'             => $aCommonData['withColumn']
        );
        return Yii::app()->twigRenderer->renderQuestion($this->getMainView(), $itemDatas, true);
    }

    /**
     * Returns the language of the running survey session, looked up the same way the
     * legacy renderer did (session of the survey set in the 'surveyID' config).
     * An empty string is returned when unset, which is the array key PHP used for the
     * legacy null lookup.
     *
     * @return string
     */
    private function getSessionLanguage()
    {
        return $_SESSION['responses_' . Yii::app()->getConfig('surveyID')]['s_lang'] ?? '';
    }

    /**
     * Returns a value from the session of the survey set in the 'surveyID' config,
     * as the legacy renderer did.
     *
     * @param string $sIndex Session key (e.g. the SGQA)
     * @return mixed|null The stored value, or null if it is not set
     */
    private function getLegacySessionValue($sIndex)
    {
        return $_SESSION['responses_' . Yii::app()->getConfig('surveyID')][$sIndex] ?? null;
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
            $oXML = simplexml_load_file("http://api.ipinfodb.com/v3/ip-city/?key=$ipInfoDbAPIKey&ip=$sIPAddress&format=xml");
            if ($oXML->{'statusCode'} == "OK") {
                $lat = (float) $oXML->{'latitude'};
                $lng = (float) $oXML->{'longitude'};

                return(array($lat, $lng));
            } else {
                return false;
            }
        }
    }
}
