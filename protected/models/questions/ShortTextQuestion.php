<?php
namespace ls\models\questions;

use ls\interfaces\ResponseInterface;

class ShortTextQuestion extends TextQuestion
{
    /**
     * This function renders the object.
     * It MUST NOT produce any output.
     * It should return a string or an object that can be converted to string.
     * @param \ls\interfaces\Response $response
     * @param \ls\components\SurveySession $session
     * @return \ls\components\RenderedQuestion
     */
    public function render(ResponseInterface$response, \ls\components\SurveySession $session)
    {
        $result = parent::render($response, $session);
        // ---------------------------------------------------------------


        $sGoogleMapsAPIKey = \ls\models\SettingGlobal::get("googleMapsAPIKey");
        if ($sGoogleMapsAPIKey!='')
        {
            $sGoogleMapsAPIKey='&key='.$sGoogleMapsAPIKey;
        }
        $extraclass ="";

        if ($this->numbers_only == 1)
        {
            $sSeparator = \ls\helpers\SurveyTranslator::getRadixPointData($this->survey->localizedNumberFormat)['separator'];
            $extraclass .=" numberonly";
            $checkconditionFunction = "fixnum_checkconditions";
        }
        if (intval(trim($this->maximum_chars))>0)
        {
            // Only maxlength attribute, use textarea[maxlength] jquery selector for textarea
            $maximum_chars= intval(trim($this->maximum_chars));
            $maxlength= "maxlength='{$maximum_chars}' ";
            $extraclass .=" maxchars maxchars-".$maximum_chars;
        }
        else
        {
            $maxlength= "";
        }
        if (trim($this->text_input_width)!='')
        {
            $tiwidth=$this->text_input_width;
            $extraclass .=" inputwidth-".trim($this->text_input_width);
        }
        else
        {
            $tiwidth=50;
        }
        if (trim($this->prefix)!='') {
            $prefix = $this->prefix;
            $extraclass .=" withprefix";
        }
        else
        {
            $prefix = '';
        }
        if (trim($this->suffix) != '') {
            $suffix = $this->suffix;
            $extraclass .=" withsuffix";
        }
        else
        {
            $suffix = '';
        }
        if ($this->survey->bool_nokeyboard)
        {
            includeKeypad();
            $kpclass = "text-keypad";
            $extraclass .=" inputkeypad";
        }
        else
        {
            $kpclass = "";
        }
        if (trim($this->display_rows)!='')
        {
            //question attribute "display_rows" is set -> we need a textarea to be able to show several rows
            $drows=$this->display_rows;

            //if a textarea should be displayed we make it equal width to the long text question
            //this looks nicer and more continuous
            if($tiwidth == 50)
            {
                $tiwidth=40;
            }

            //NEW: textarea instead of input=text field

            // --> START NEW FEATURE - SAVE
            $html ="<p class='question answer-item text-item {$extraclass}'><label for='answer{$this->sgqa}' class='hide label'>".gT('Your answer')."</label>"
                . '<textarea class="textarea '.$kpclass.'" name="'.$this->sgqa.'" id="answer'.$this->sgqa.'" '
                .'rows="'.$drows.'" cols="'.$tiwidth.'" '.$maxlength.'>';
            // --> END NEW FEATURE - SAVE

            if ($response->{$this->sgqa}) {
                $dispVal = str_replace("\\", "", $response->{$this->sgqa});
                if ($this->numbers_only==1)
                {
                    $dispVal = str_replace('.',$sSeparator,$dispVal);
                }
                $html .= $dispVal;
            }

            $html .= "</textarea></p>\n";
        }
        elseif((int)($this->location_mapservice)==1){
            $mapservice = $this->location_mapservice;
            $currentLocation = $response->{$this->sgqa};
            $currentLatLong = null;

            $floatLat = 0;
            $floatLng = 0;

            // Get the latitude/longtitude for the point that needs to be displayed by default
            if (strlen($currentLocation) > 2){
                $currentLatLong = explode(';',$currentLocation);
                $currentLatLong = array($currentLatLong[0],$currentLatLong[1]);
            }
            else{
                if ((int)($this->location_nodefaultfromip)==0)
                    $currentLatLong = getLatLongFromIp(getIPAddress());
                if (!isset($currentLatLong) || $currentLatLong==false){
                    $floatLat = 0;
                    $floatLng = 0;
                    $LatLong = explode(" ",trim($this->location_defaultcoordinates));

                    if (isset($LatLong[0]) && isset($LatLong[1])){
                        $floatLat = $LatLong[0];
                        $floatLng = $LatLong[1];
                    }

                    $currentLatLong = array($floatLat,$floatLng);
                }
            }
            // 2 - city; 3 - state; 4 - country; 5 - postal
            $strBuild = "";
            if ($this->location_city)
                $strBuild .= "2";
            if ($this->location_state)
                $strBuild .= "3";
            if ($this->location_country)
                $strBuild .= "4";
            if ($this->location_postal)
                $strBuild .= "5";

            $currentLocation = $currentLatLong[0] . " " . $currentLatLong[1];
            $html = "
            <script type=\"text/javascript\">
            zoom['$this->sgqa'] = {$this->location_mapzoom};
            </script>

            <div class=\"question answer-item geoloc-item {$extraclass}\">
            <input type=\"hidden\" name=\"$this->sgqa\" id=\"answer$this->sgqa\" value=\"{$response->{$this->sgqa}}\">

            <input class=\"text location ".$kpclass."\" type=\"text\" size=\"20\" name=\"$this->sgqa_c\"
            id=\"answer$this->sgqa_c\" value=\"$currentLocation\"/>

            <input type=\"hidden\" name=\"boycott_$this->sgqa\" id=\"boycott_$this->sgqa\"
            value = \"{$strBuild}\" >

            <input type=\"hidden\" name=\"mapservice_$this->sgqa\" id=\"mapservice_$this->sgqa\"
            class=\"mapservice\" value = \"{$this->location_mapservice}\" >
            <div id=\"gmap_canvas_$this->sgqa_c\" style=\"width: {$this->location_mapwidth}px; height: {$this->location_mapheight}px\"></div>
            </div>";

            Yii::app()->getClientScript()->registerScriptFile(Yii::app()->getConfig('generalscripts')."map.js");
            if ($this->location_mapservice==1 && !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != "off")
                Yii::app()->getClientScript()->registerScriptFile("https://maps.googleapis.com/maps/api/js?sensor=false$sGoogleMapsAPIKey");
            else if ($this->location_mapservice==1)
                Yii::app()->getClientScript()->registerScriptFile("http://maps.googleapis.com/maps/api/js?sensor=false$sGoogleMapsAPIKey");
            elseif ($this->location_mapservice==2)
                Yii::app()->getClientScript()->registerScriptFile("http://www.openlayers.org/api/OpenLayers.js");

            if (isset($this->hide_tip) && $this->hide_tip==0)
            {
                $html .= "<div class=\"questionhelp\">"
                    . gT('Drag and drop the pin to the desired location. You may also right click on the map to move the pin.').'</div>';
                $this_text['help'] = gT('Drag and drop the pin to the desired location. You may also right click on the map to move the pin.');
            }
        }
        elseif((int)($this->location_mapservice)==100)
        {

            $currentLocation = $response->{$this->sgqa};
            $currentCenter = $currentLatLong = null;

            // Get the latitude/longtitude for the point that needs to be displayed by default
            if (strlen($currentLocation) > 2 && strpos($currentLocation,";"))
            {
                $currentLatLong = explode(';',$currentLocation);
                $currentCenter = $currentLatLong = array($currentLatLong[0],$currentLatLong[1]);
            }
            elseif ((int)($this->location_nodefaultfromip)==0)
            {
                $currentCenter = $currentLatLong = getLatLongFromIp(getIPAddress());
            }

            // If it's not set : set the center to the default position, but don't set the marker
            if (!$currentLatLong)
            {
                $currentLatLong = array("","");
                $currentCenter = explode(" ",trim($this->location_defaultcoordinates));
                if(count($currentCenter)!=2)
                {
                    $currentCenter = array("","");
                }
            }
            // 2 - city; 3 - state; 4 - country; 5 - postal
            // TODO : move it to aThisMapScriptVar and use geoname reverse geocoding (http://www.geonames.org/export/reverse-geocoding.html)
            $strBuild = "";
            /*if ($this->location_city)
                $strBuild .= "2";
            if ($this->location_state)
                $strBuild .= "3";
            if ($this->location_country)
                $strBuild .= "4";
            if ($this->location_postal)
                $strBuild .= "5";*/

            $aGlobalMapScriptVar= array(
                'geonameUser'=> \ls\models\SettingGlobal::get('GeoNamesUsername'),// Did we need to urlencode ?
                'geonameLang'=>Yii::app()->language,
            );
            $aThisMapScriptVar=array(
                'zoomLevel'=>$this->location_mapzoom,
                'latitude'=>$currentCenter[0],
                'longitude'=>$currentCenter[1],

            );
            $clientScript = App()->getClientScript();
            $clientScript->registerPackage('leaflet');
            $clientScript->registerScript('sGlobalMapScriptVar',"LSmap=".ls_json_encode($aGlobalMapScriptVar).";\nLSmaps= new Array();",CClientScript::POS_HEAD);
            $clientScript->registerScript('sThisMapScriptVar'.$this->sgqa,"LSmaps['{$this->sgqa}']=".ls_json_encode($aThisMapScriptVar),CClientScript::POS_HEAD);
            $clientScript->registerScriptFile(Yii::app()->getConfig('generalscripts')."map.js");
            $clientScript->registerCssFile(App()->publicUrl . '/styles-public/' . 'map.css');

            $html = "
            <div class=\"question answer-item geoloc-item {$extraclass}\">
                <input type=\"hidden\"  name=\"$this->sgqa\" id=\"answer$this->sgqa\" value=\"{$response->{$this->sgqa}}\"><!-- No javascript need a way to answer -->
                <input type=\"hidden\" class=\"location\" name=\"$this->sgqa_c\" id=\"answer$this->sgqa_c\" value=\"{$currentLatLong[0]} {$currentLatLong[1]}\" />

                <ul class=\"coordinates-list\">
                    <li class=\"coordinate-item\">".gt("Latitude:")."<input class=\"coords text\" type=\"text\" name=\"$this->sgqa_c1\" id=\"answer_lat$this->sgqa_c\"  value=\"{$currentLatLong[0]}\" /></li>
                    <li class=\"coordinate-item\">".gt("Longitude:")."<input class=\"coords text\" type=\"text\" name=\"$this->sgqa_c2\" id=\"answer_lng$this->sgqa_c\" value=\"{$currentLatLong[1]}\" /></li>
                </ul>

                <input type=\"hidden\" name=\"boycott_$this->sgqa\" id=\"boycott_$this->sgqa\" value = \"{$strBuild}\" >
                <input type=\"hidden\" name=\"mapservice_$this->sgqa\" id=\"mapservice_$this->sgqa\" class=\"mapservice\" value = \"{$this->location_mapservice}\" >

                <div>
                    <div class=\"geoname_restrict\">
                        <input type=\"checkbox\" id=\"restrictToExtent_{$this->sgqa}\"> <label for=\"restrictToExtent_{$this->sgqa}\">".gt("Restrict search place to map extent")."</label>
                    </div>
                    <div class=\"geoname_search\" >
                        <input id=\"searchbox_{$this->sgqa}\" placeholder=\"".gt("Search")."\" width=\"15\">
                    </div>
                </div>
                <div id=\"map_{$this->sgqa}\" style=\"width: 100%; height: {$this->location_mapheight}px;\">
            </div>
            ";


            if (isset($this->hide_tip) && $this->hide_tip==0)
            {
                $html .= "<div class=\"questionhelp\">"
                    . gT('Click to set the location or drag and drop the pin. You may may also enter coordinates').'</div>';
                $this_text['help'] = gT('Click to set the location or drag and drop the pin. You may may also enter coordinates');
            }
        }
        else
        {
            //no question attribute set, use common input text field
            $inputOptions = [
                'size' => $tiwidth,
                'id' => "answer$this->sgqa",
                'class' => 'text',
                'data-validation-expression' => $this->getExpressionManager($response)->getJavascript(implode(' and ', array_keys($this->getValidationExpressions())))
            ];
            $html = "<p class=\"question answer-item text-item {$extraclass}\">\n"
                ."<label for='answer{$this->sgqa}' class='hide label'>".gT('Your answer')."</label>"
                ."$prefix\t"
                . \CHtml::textField(
                    $this->sgqa, $response->{$this->sgqa}, $inputOptions);
            $html .="\n\t$suffix\n</p>\n";
        }

        $result->setHtml($html);
        return $result;

    }

    /**
     * Return the classes to be added to the question wrapper.
     * @return []
     */
    public function getClasses()
    {
        $result = parent::getClasses();
        $result[] = 'text-short';
        return $result;

    }

    /**
     * @todo Move individual cases to subclasses.
     * @return array|mixed
     * @throws Exception
     */
    public function getColumns()
    {
        return [$this->sgqa => "string"];
    }


}