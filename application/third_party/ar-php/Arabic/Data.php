<?php 
class I18N_Arabic_Data
{
    private $xml = null;
    
    /**
     * Loads initialize values
     *
     * @ignore
     */
    public function __construct()
    {
        // load XML file
        $this->xml = simplexml_load_file(dirname(__FILE__).'/data/arab_countries.xml') or die ('Unable to load XML file!');
    }
    
    public function getCountriesData($xPath='*')
    {
        // SimpleXML object
        return $this->xml->xpath($xPath);
    }
    
    public function getCountryList($lang='ar', $longName=false, $xPath='*')
    {
        $list = array();
        $xml  = $this->xml->xpath($xPath);
        
        foreach ($xml as $country) {
            if ($lang == 'ar') {
                if ($longName) {
                    $name = $country->longname->arabic;
                } else {
                    $name = $country->name->arabic;
                }
            } else {
                if ($longName) {
                    $name = $country->longname->english;
                } else {
                    $name = $country->name->english;
                }
            }
            
            $id = $country->iso3166->a3;
            
            $list["$id"] = (string)$name;
        }

        return $list;
    }
    
    public function getCurrencyList($lang='ar', $xPath='*')
    {
        $list = array();
        $xml  = $this->xml->xpath($xPath);
        
        foreach ($xml as $country) {
            if ($lang == 'ar') {
                $name = $country->currency->arabic;
            } else {
                $name = $country->currency->english;
            }
            
            $id = $country->currency->iso;
            
            $list["$id"] = (string)$name;
        }

        return $list;
    }
    
    private function _countrySearch($country='Syria')
    {
        $country = mb_strtolower($country);

        $xPath  = "/countries/country[";
        $xPath .= "translate(iso3166/a2, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', 'abcdefghijklmnopqrstuvwxyz') = '$country' or ";
        $xPath .= "translate(iso3166/a3, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', 'abcdefghijklmnopqrstuvwxyz') = '$country' or ";
        $xPath .= "translate(name/english, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', 'abcdefghijklmnopqrstuvwxyz') = '$country' or ";
        $xPath .= "name/arabic = '$country']";
        echo "$xPath<br>";
        return $this->xml->xpath($xPath);
    }
    
    // arabic, english, iso a2, iso a3 names
    public function getCapital($country='syria')
    {
        $xml = $this->_countrySearch($country);
        
        $capital = array();
        
        $capital['arabic']    = $xml->country->capital->arabic;
        $capital['english']   = $xml->country->capital->english;
        $capital['latitude']  = $xml->country->capital->latitude;
        $capital['longitude'] = $xml->country->capital->longitude;
        $capital['elevation'] = $xml->country->capital->elevation;
        
        return $capital;
    }
    
    public function getTime($time=null, $country='syria')
    {
        if ($time == null) {
            $time = time();
        }

        $xml = $this->_countrySearch($country);
        
        $timezone = $xml->country->timezone;

        if ($xml->country->summertime['used'] == 'true') {
            $start = strtotime($xml->country->summertime->start);
            $end   = strtotime($xml->country->summertime->end);
            if (time() > $start && time() < $end) {
                $timezone = $timezone + 1;
                $timezone = '+' . $timezone;
            }
        }
        
        // convert current time to GMT based on time zone offset
        $gmtime    = $time - (int)substr(date('O'),0,3)*60*60; 
        $timestamp = $gmtime + $timezone * 3600;

        return $timestamp;
    }
    
}