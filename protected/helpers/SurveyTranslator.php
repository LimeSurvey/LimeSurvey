<?php


namespace ls\helpers;

use \Yii;
class SurveyTranslator
{

    public static function getDateFormatData($iDateFormat = 0)
    {
        $aDateFormats = array(
            1 => array('phpdate' => 'd.m.Y', 'jsdate' => 'dd.mm.yy', 'dateformat' => gT('dd.mm.yyyy')),
            2 => array('phpdate' => 'd-m-Y', 'jsdate' => 'dd-mm-yy', 'dateformat' => gT('dd-mm-yyyy')),
            3 => array('phpdate' => 'Y.m.d', 'jsdate' => 'yy.mm.dd', 'dateformat' => gT('yyyy.mm.dd')),
            4 => array('phpdate' => 'j.n.Y', 'jsdate' => 'd.m.yy', 'dateformat' => gT('d.m.yyyy')),
            5 => array('phpdate' => 'd/m/Y', 'jsdate' => 'dd/mm/yy', 'dateformat' => gT('dd/mm/yyyy')),
            6 => array('phpdate' => 'Y-m-d', 'jsdate' => 'yy-mm-dd', 'dateformat' => gT('yyyy-mm-dd')),
            7 => array('phpdate' => 'Y/m/d', 'jsdate' => 'yy/mm/dd', 'dateformat' => gT('yyyy/mm/dd')),
            8 => array('phpdate' => 'j/n/Y', 'jsdate' => 'd/m/yy', 'dateformat' => gT('d/m/yyyy')),
            9 => array('phpdate' => 'm-d-Y', 'jsdate' => 'mm-dd-yy', 'dateformat' => gT('mm-dd-yyyy')),
            10 => array('phpdate' => 'm.d.Y', 'jsdate' => 'mm.dd.yy', 'dateformat' => gT('mm.dd.yyyy')),
            11 => array('phpdate' => 'm/d/Y', 'jsdate' => 'mm/dd/yy', 'dateformat' => gT('mm/dd/yyyy')),
            12 => array('phpdate' => 'j-n-Y', 'jsdate' => 'd-m-yy', 'dateformat' => gT('d-m-yyyy'))
        );

        if ($iDateFormat > 12 || $iDateFormat < 0) {
            $iDateFormat = 11;   // TODO - what should default be?
        }
        if ($iDateFormat > 0) {
            return $aDateFormats[$iDateFormat];
        } else {
            return $aDateFormats;
        }

    }

    public static function getLanguageData($bOrderByNative = false, $sLanguageCode = 'en')
    {
        $supportedLanguages = App()->getLocale()->data();

        if ($bOrderByNative) {
            $supportedLanguages = \Cake\Utility\Hash::sort($supportedLanguages, 'nativedescription');
        } else {
            $supportedLanguages = \Cake\Utility\Hash::sort($supportedLanguages, 'description');
        }

        return $supportedLanguages;
    }


    /**
     *  Returns avaliable formats for Radix Points (Decimal Separators) or returns
     *  radix point info about a specific format.
     *
     * @param int $format Format ID/Number [optional]
     */
    public static function getRadixPointData($format = -1)
    {
        $aRadixFormats = array(
            0 => array('separator' => '.', 'desc' => gT('Dot (.)')),
            1 => array('separator' => ',', 'desc' => gT('Comma (,)'))
        );

        // hack for fact that null sometimes sent to this function
        if (is_null($format)) {
            $format = 0;
        }

        if ($format >= 0) {
            return $aRadixFormats[$format];
        } else {
            return $aRadixFormats;
        }
    }


    /**
     * Convert a 'dateformat' format string to a 'phpdate' format.
     *
     * @param $sDateformat string
     * @returns string
     *
     */
    public static function getPHPDateFromDateFormat($sDateformat)
    {
        // Note that order is relevant (longer strings first)
        $aFmts = array(
            // With leading zero
            "dd" => "d",
            "mm" => "m",
            "yyyy" => "Y",
            "HH" => "H",
            "MM" => "i",
            // Without leading zero
            "d" => "j",
            "m" => "n",
            "yy" => "y",
            "H" => "G",
            "M" => "i"
        );

        // Extra allowed characters
        $aAllowed = array('-', '.', '/', ':', ' ');

        // Convert
        $tmp = [];
        foreach ($aAllowed as $k) {
            $tmp[$k] = true;
        }
        foreach (array_values($aFmts) as $k) {
            for ($i = 0; $i < strlen($k); $i++) {
                $tmp[$k[$i]] = true;
            }
        }
        $aAllowed = $tmp;

        $tmp = strtr($sDateformat, $aFmts);
        $sPhpdate = "";
        for ($i = 0; $i < strlen($tmp); $i++) {
            $c = $tmp[$i];
            if (isset($aAllowed[$c])) {
                $sPhpdate .= $c;
            }
        }

        return $sPhpdate;
    }


    /**
     * Convert a 'dateformat' format string to a 'jsdate' format.
     *
     * @param $sDateformat string
     * @returns string
     *
     */
    public static function getJSDateFromDateFormat($sDateformat)
    {
        // The only difference from dateformat is that Jsdate does not support truncated years
        return str_replace(array('yy'), array('y'), $sDateformat);
    }


    /**
     * Get the date format for a specified survey
     *
     * @param $surveyid integer ls\models\Survey id
     * @param $languagecode string ls\models\Survey language code (optional)
     * @returns integer
     *
     */
    public static function getDateFormatForSID($surveyid, $languagecode = '')
    {
        if (!isset($languagecode) || $languagecode == '') {
            $languagecode = Survey::model()->findByPk($surveyid)->language;
        }
        $data = SurveyLanguageSetting::model()->getDateFormat($surveyid, $languagecode);

        if (empty($data)) {
            $dateformat = 0;
        } else {
            $dateformat = (int)$data;
        }

        return (int)$dateformat;
    }


    /**
     * Check whether we can show the JS date picker with the current format
     *
     * @param $dateformatdetails array Date format details for the question
     * @param $dateformats array Available date formats
     * @returns integer
     *
     */
    public static function canShowDatePicker($dateformatdetails, $dateformats = null)
    {
        if (is_null($dateformats)) {
            $dateformats = getDateFormatData();
        }
        $showpicker = false;
        foreach ($dateformats as $format) {
            if ($format['jsdate'] == $dateformatdetails['jsdate']) {
                $showpicker = true;
                break;
            }
        }

        return $showpicker;
    }


    public static function getLanguageCodefromLanguage($languagetosearch)
    {
        $detaillanguages = self::getLanguageData(false, Yii::app()->session['adminlang']);
        foreach ($detaillanguages as $key2 => $languagename) {
            if ($languagetosearch == $languagename['description']) {
                return $key2;
            }
        }

        // else return default en code
        return "en";
    }


    public static function getLanguageNameFromCode($codetosearch, $withnative = true, $sTranslationLanguage = null)
    {
        if (is_null($sTranslationLanguage)) {
            $sTranslationLanguage = Yii::app()->session['adminlang'];
        }
        $detaillanguages = self::getLanguageData(false, $sTranslationLanguage);
        if (isset($detaillanguages[$codetosearch]['description'])) {
            if ($withnative) {
                return array(
                    $detaillanguages[$codetosearch]['description'],
                    $detaillanguages[$codetosearch]['nativedescription']
                );
            } else {
                return $detaillanguages[$codetosearch]['description'];
            }
        } else // else return default en code
        {
            return false;
        }
    }


    public static function getLanguageRTL($sLanguageCode)
    {
        $aLanguageData = self::getLanguageData(false, $sLanguageCode);
        if (isset($aLanguageData[$sLanguageCode]['rtl'])) {
            return $aLanguageData[$sLanguageCode]['rtl'];
        } else {
            return false;
        }
    }

    /**
     * Returns the locale settings for a certain language code
     *
     * @param string $codetosearch
     * @return array Array with locale details
     *
     */
    public static function getLanguageDetails($codetosearch)
    {
        $detaillanguages = self::getLanguageData(false, Yii::app()->session['adminlang']);
        if (isset($detaillanguages[$codetosearch])) {
            return $detaillanguages[$codetosearch];
        } else {
            return $detaillanguages['en'];
        }
    }

    /**
     * This functions translates LimeSurvey specific locale code to match a Yii locale
     *
     * @param mixed $sLocale LimeSurvey locale code
     */
    public static function translateLStoYiiLocale($sLocale)
    {
        // Strip informal string
        $sLocale = str_replace('-informal', '', $sLocale);

        return $sLocale;
    }

    public static function getLanguageDataRestricted($bOrderByNative = false, $sLanguageCode = 'en')
    {
        $aLanguageData = self::getLanguageData($bOrderByNative, $sLanguageCode);
        $result = [];

        $activeLanguages = SettingGlobal::get('restrictToLanguages');

        if (!empty($activeLanguages)) {
            foreach ($activeLanguages as $language) {
                $result[$language] = $aLanguageData[$language];
            }
        } else {
            $result = $aLanguageData;
        }

        return $result;
    }


    public static function userSort($a, $b)
    {

        // smarts is all-important, so sort it first
        if ($a['description'] > $b['description']) {
            return 1;
        } else {
            return -1;
        }
    }


    public static function userSortNative($a, $b)
    {

        // smarts is all-important, so sort it first
        if ($a['nativedescription'] > $b['nativedescription']) {
            return 1;
        } else {
            return -1;
        }
    }


    /**
     * This public static function  support the ability NOT to reverse numbers (for example when you output
     * a phrase as a parameter for a SWF file that can't handle RTL languages itself, but
     * obviously any numbers should remain the same as in the original phrase).
     * Note that it can be used just as well for UTF-8 usages if you want the numbers to remain intact
     *
     * @param string $str
     * @param boolean $reverse_numbers
     * @return string
     */
    public static function UTF8Strrev($str, $reverse_numbers = false)
    {
        preg_match_all('/./us', $str, $ar);
        if ($reverse_numbers) {
            return join('', array_reverse($ar[0]));
        } else {
            $temp = [];
            foreach ($ar[0] as $value) {
                if (is_numeric($value) && !empty($temp[0]) && is_numeric($temp[0])) {
                    foreach ($temp as $key => $value2) {
                        if (is_numeric($value2)) {
                            $pos = ($key + 1);
                        } else {
                            break;
                        }
                    }
                    $temp2 = array_splice($temp, $pos);
                    $temp = array_merge($temp, array($value), $temp2);
                } else {
                    array_unshift($temp, $value);
                }
            }

            return implode('', $temp);
        }
    }

}
