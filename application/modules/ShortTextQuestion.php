<?php
class ShortTextQuestion extends TextQuestion
{
    public function getAnswerHTML()
    {
        global $js_header_includes, $thissurvey;

        $clang = Yii::app()->lang;
        $googleMapsAPIKey = Yii::app()->getConfig("googleMapsAPIKey");
        $extraclass ="";
        $aQuestionAttributes = $this->getAttributeValues();

        if ($aQuestionAttributes['numbers_only']==1)
        {
            $sSeperator = getRadixPointData($thissurvey['surveyls_numberformat']);
            $sSeperator = $sSeperator['seperator'];
            $numbersonly = 'onkeypress="return goodchars(event,\'-0123456789'.$sSeperator.'\')"';
            $extraclass .=" numberonly";
            $checkconditionFunction = "fixnum_checkconditions";
        }
        else
        {
            $numbersonly = '';
            $checkconditionFunction = "checkconditions";
        }
        if (intval(trim($aQuestionAttributes['maximum_chars']))>0)
        {
            // Only maxlength attribute, use textarea[maxlength] jquery selector for textarea
            $maximum_chars= intval(trim($aQuestionAttributes['maximum_chars']));
            $maxlength= "maxlength='{$maximum_chars}' ";
            $extraclass .=" maxchars maxchars-".$maximum_chars;
        }
        else
        {
            $maxlength= "";
        }
        if (trim($aQuestionAttributes['text_input_width'])!='')
        {
            $tiwidth=$aQuestionAttributes['text_input_width'];
            $extraclass .=" inputwidth-".trim($aQuestionAttributes['text_input_width']);
        }
        else
        {
            $tiwidth=50;
        }
        if (trim($aQuestionAttributes['prefix'][$_SESSION['survey_'.$this->surveyid]['s_lang']])!='') {
            $prefix=$aQuestionAttributes['prefix'][$_SESSION['survey_'.$this->surveyid]['s_lang']];
            $extraclass .=" withprefix";
        }
        else
        {
            $prefix = '';
        }
        if (trim($aQuestionAttributes['suffix'][$_SESSION['survey_'.$this->surveyid]['s_lang']])!='') {
            $suffix=$aQuestionAttributes['suffix'][$_SESSION['survey_'.$this->surveyid]['s_lang']];
            $extraclass .=" withsuffix";
        }
        else
        {
            $suffix = '';
        }
        if ($thissurvey['nokeyboard']=='Y')
        {
            includeKeypad();
            $kpclass = "text-keypad";
            $extraclass .=" inputkeypad";
        }
        else
        {
            $kpclass = "";
        }
        if (trim($aQuestionAttributes['display_rows'])!='')
        {
            //question attribute "display_rows" is set -> we need a textarea to be able to show several rows
            $drows=$aQuestionAttributes['display_rows'];

            //if a textarea should be displayed we make it equal width to the long text question
            //this looks nicer and more continuous
            if($tiwidth == 50)
            {
                $tiwidth=40;
            }

            //NEW: textarea instead of input=text field

            // --> START NEW FEATURE - SAVE
            $answer ="<p class='question answer-item text-item {$extraclass}'><label for='answer{$this->fieldname}' class='hide label'>{$clang->gT('Answer')}</label>"
            . '<textarea class="textarea '.$kpclass.'" name="'.$this->fieldname.'" id="answer'.$this->fieldname.'" '
            .'rows="'.$drows.'" cols="'.$tiwidth.'" '.$maxlength.' onchange="'.$checkconditionFunction.'(this.value, this.name, this.type);" '.$numbersonly.'>';
            // --> END NEW FEATURE - SAVE

            if ($_SESSION['survey_'.$this->surveyid][$this->fieldname]) {
                $dispVal = str_replace("\\", "", $_SESSION['survey_'.$this->surveyid][$this->fieldname]);
                if ($aQuestionAttributes['numbers_only']==1)
                {
                    $dispVal = str_replace('.',$sSeperator,$dispVal);
                }
                $answer .= $dispVal;
            }

            $answer .= "</textarea></p>\n";
        }
        elseif((int)($aQuestionAttributes['location_mapservice'])!=0){
            $mapservice = $aQuestionAttributes['location_mapservice'];
            $currentLocation = $_SESSION['survey_'.$this->surveyid][$this->fieldname];
            $currentLatLong = null;

            $floatLat = 0;
            $floatLng = 0;

            // Get the latitude/longtitude for the point that needs to be displayed by default
            if (strlen($currentLocation) > 2){
                $currentLatLong = explode(';',$currentLocation);
                $currentLatLong = array($currentLatLong[0],$currentLatLong[1]);
            }
            else{
                if ((int)($aQuestionAttributes['location_nodefaultfromip'])==0)
                    $currentLatLong = getLatLongFromIp(getIPAddress());
                if (!isset($currentLatLong) || $currentLatLong==false){
                    $floatLat = 0;
                    $floatLng = 0;
                    $LatLong = explode(" ",trim($aQuestionAttributes['location_defaultcoordinates']));

                    if (isset($LatLong[0]) && isset($LatLong[1])){
                        $floatLat = $LatLong[0];
                        $floatLng = $LatLong[1];
                    }

                    $currentLatLong = array($floatLat,$floatLng);
                }
            }
            // 2 - city; 3 - state; 4 - country; 5 - postal
            $strBuild = "";
            if ($aQuestionAttributes['location_city'])
                $strBuild .= "2";
            if ($aQuestionAttributes['location_state'])
                $strBuild .= "3";
            if ($aQuestionAttributes['location_country'])
                $strBuild .= "4";
            if ($aQuestionAttributes['location_postal'])
                $strBuild .= "5";

            $currentLocation = $currentLatLong[0] . " " . $currentLatLong[1];
            $answer = "
            <script type=\"text/javascript\">
            zoom['$this->fieldname'] = {$aQuestionAttributes['location_mapzoom']};
            </script>
            <div class=\"question answer-item geoloc-item {$extraclass}\">
            <input type=\"hidden\" name=\"$this->fieldname\" id=\"answer$this->fieldname\" value=\"{$_SESSION['survey_'.$this->surveyid][$this->fieldname]}\">

            <input class=\"text location ".$kpclass."\" type=\"text\" size=\"20\" name=\"$this->fieldname_c\"
            id=\"answer$this->fieldname_c\" value=\"$currentLocation\"
            onchange=\"$checkconditionFunction(this.value, this.name, this.type)\" />

            <input type=\"hidden\" name=\"boycott_$this->fieldname\" id=\"boycott_$this->fieldname\"
            value = \"{$strBuild}\" >
            <input type=\"hidden\" name=\"mapservice_$this->fieldname\" id=\"mapservice_$this->fieldname\"
            class=\"mapservice\" value = \"{$aQuestionAttributes['location_mapservice']}\" >
            <div id=\"gmap_canvas_$this->fieldname_c\" style=\"width: {$aQuestionAttributes['location_mapwidth']}px; height: {$aQuestionAttributes['location_mapheight']}px\"></div>
            </div>";

            if ($aQuestionAttributes['location_mapservice']==1 && !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != "off")
                $js_header_includes[] = "https://maps.googleapis.com/maps/api/js?sensor=false";
            else if ($aQuestionAttributes['location_mapservice']==1)
                    $js_header_includes[] = "http://maps.googleapis.com/maps/api/js?sensor=false";
                elseif ($aQuestionAttributes['location_mapservice']==2)
                    $js_header_includes[] = "http://www.openlayers.org/api/OpenLayers.js";

                if (isset($aQuestionAttributes['hide_tip']) && $aQuestionAttributes['hide_tip']==0)
            {
                $answer .= "<div class=\"questionhelp\">"
                . $clang->gT('Drag and drop the pin to the desired location. You may also right click on the map to move the pin.').'</div>';
                $question_text['help'] = $clang->gT('Drag and drop the pin to the desired location. You may also right click on the map to move the pin.');
            }
        }
        else
        {
            //no question attribute set, use common input text field
            $answer = "<p class=\"question answer-item text-item {$extraclass}\">\n"
            ."<label for='answer{$this->fieldname}' class='hide label'>{$clang->gT('Answer')}</label>"
            ."$prefix\t<input class=\"text $kpclass\" type=\"text\" size=\"$tiwidth\" name=\"$this->fieldname\" id=\"answer$this->fieldname\"";

            $dispVal = $_SESSION['survey_'.$this->surveyid][$this->fieldname];
            if ($aQuestionAttributes['numbers_only']==1)
            {
                $dispVal = str_replace('.',$sSeperator,$dispVal);
            }
            $dispVal = htmlspecialchars($dispVal,ENT_QUOTES,'UTF-8');
            $answer .= " value=\"$dispVal\"";

            $answer .=" {$maxlength} onchange=\"$checkconditionFunction(this.value, this.name, this.type)\" $numbersonly />\n\t$suffix\n</p>\n";
        }

        if (trim($aQuestionAttributes['time_limit'])!='')
        {
            $js_header_includes[] = '/scripts/coookies.js';
            $answer .= return_timer_script($aQuestionAttributes, $this, "answer".$this->fieldname);
        }

        return $answer;
    }
    
    public function availableAttributes($attr = false)
    {
        $attrs=array("display_rows","em_validation_q","em_validation_q_tip","em_validation_sq","em_validation_sq_tip","location_city","location_state","location_postal","location_country","statistics_showmap","statistics_showgraph","statistics_graphtype","location_mapservice","location_mapwidth","location_mapheight","location_nodefaultfromip","location_defaultcoordinates","location_mapzoom","hide_tip","hidden","maximum_chars","numbers_only","page_break","prefix","suffix","text_input_width","time_limit","time_limit_action","time_limit_disable_next","time_limit_disable_prev","time_limit_countdown_message","time_limit_timer_style","time_limit_message_delay","time_limit_message","time_limit_message_style","time_limit_warning","time_limit_warning_display_time","time_limit_warning_message","time_limit_warning_style","time_limit_warning_2","time_limit_warning_2_display_time","time_limit_warning_2_message","time_limit_warning_2_style","random_group");
        return $attr?array_key_exists($attr,$attrs):$attrs;
    }

    public function questionProperties($prop = false)
    {
        $clang=Yii::app()->lang;
        $props=array('description' => $clang->gT("Short Free Text"),'group' => $clang->gT("Text questions"),'subquestions' => 0,'class' => 'text-short','hasdefaultvalues' => 1,'assessable' => 0,'answerscales' => 0);
        return $prop?$props[$prop]:$props;
    }
}
?>