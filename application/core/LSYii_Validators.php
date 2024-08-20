<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}
/*
 * LimeSurvey
 * Copyright (C) 2007-2011 The LimeSurvey Project Team / Carsten Schmitz
 * All rights reserved.
 * License: GNU/GPL License v2 or later, see LICENSE.php
 * LimeSurvey is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 *
 */

class LSYii_Validators extends CValidator
{
    /**
     * Filter attribute for fixCKeditor
     * @var boolean
     */
    public $fixCKeditor = false;
    /**
     * Filter attribute for XSS
     * @var boolean
     */
    public $xssfilter = true;
    /**
     * Filter attribute for url
     * @var boolean
     */
    public $isUrl = false;
    /**
     * Filter attribute for isLanguage
     * @var boolean
     */
    public $isLanguage = false;
    /**
     * Filter attribute for isLanguageMulti (multi language string)
     * @var boolean
     */
    public $isLanguageMulti = false;

    public function __construct()
    {
        if (App()->getConfig('DBVersion') < 172) {
            // Permission::model exist only after 172 DB version
            return $this->xssfilter = ($this->xssfilter && App()->getConfig('filterxsshtml'));
        }
        // If run from console there is no user
        $this->xssfilter = (
            $this->xssfilter
            && ((defined('PHP_ENV') // phpunit test : don't check controller
                    && PHP_ENV == 'test'
                )
                || (($controller = App()->getController()) !== null // no controller
                    && (get_class($controller) !== 'ConsoleApplication') // ConsoleApplication
                )
            )
            && App()->user->isXssFiltered() // user
        );
        return;
    }

    protected function validateAttribute($object, $attribute)
    {
        if ($this->xssfilter) {
            $object->$attribute = $this->xssFilter($object->$attribute);
            if ($this->isUrl) {
                if (self::isXssUrl($object->$attribute)) {
                    $object->$attribute = "";
                }
            }
        }
        // Note that URL checking only checks basic URL properties. As a URL can contain EM expression there needs to be a lot of freedom.
        if ($this->isUrl) {
            if ($object->$attribute == 'http://' || $object->$attribute == 'https://') {
                $object->$attribute = "";
            }
        }
        if ($this->isLanguage) {
            $object->$attribute = $this->languageFilter($object->$attribute);
        }
        if ($this->isLanguageMulti) {
            $object->$attribute = $this->multiLanguageFilter($object->$attribute);
        }
    }

    /**
     * Remove some empty characters put by CK editor
     * Did we need to do if user don't use inline HTML editor ?
     *
     * @param string $value
     * @return string
     */
    public function fixCKeditor($value)
    {
        // Actually don't use it in model : model apply too when import : needed or not ?
        $value = str_replace('<br type="_moz" />', '', $value);
        if ($value == "<br />" || $value == " " || $value == "&nbsp;") {
            $value = "";
        }
        if (preg_match("/^[\s]+$/", $value)) {
            $value = '';
        }
        if ($value == "\n") {
            $value = "";
        }
        if (trim($value) == "&nbsp;" || trim($value) == '') {
            // chrome adds a single &nbsp; element to empty fckeditor fields
            $value = "";
        }
        return $value;
    }

    /**
     * Remove any script or dangerous HTML
     *
     * @param null|string $value
     * @return string
     */
    public function xssFilter($value)
    {
        /* No need to filter empty $value */
        if (empty($value)) {
            return strval($value);
        }
        $filter = LSYii_HtmlPurifier::getXssPurifier();

        /** Start to get complete filtered value with  url decode {QCODE} (bug #09300). This allow only question number in url, seems OK with XSS protection **/
        $sFiltered = $filter->purify($value);
        $sFiltered = preg_replace('#%7B([a-zA-Z0-9\.]*)%7D#', '{$1}', (string) $sFiltered);
        Yii::import('application.helpers.expressions.em_core_helper', true); // Already imported in em_manager_helper.php ?
        $oExpressionManager = new ExpressionManager();
        /**  We get 2 array : one filtered, other unfiltered **/
        $aValues = $oExpressionManager->asSplitStringOnExpressions($value); // Return array of array : 0=>the string,1=>string length,2=>string type (STRING or EXPRESSION)
        $aFilteredValues = $oExpressionManager->asSplitStringOnExpressions($sFiltered); // Same but for the filtered string
        $bCountIsOk = count($aValues) == count($aFilteredValues);
        /** Construction of new string with unfiltered EM and filtered HTML **/
        $sNewValue = "";
        foreach ($aValues as $key => $aValue) {
            if ($aValue[2] == "STRING") {
                $sNewValue .= $bCountIsOk ? $aFilteredValues[$key][0] : $filter->purify($aValue[0]); // If EM is broken : can throw invalid $key
            } else {
                $sExpression = trim((string) $aValue[0], '{}');
                $sNewValue .= "{";
                $aParsedExpressions = $oExpressionManager->Tokenize($sExpression, true);
                foreach ($aParsedExpressions as $aParsedExpression) {
                    if ($aParsedExpression[2] == 'DQ_STRING') {
                        $sNewValue .= "\"" . (string) $filter->purify($aParsedExpression[0]) . "\""; // This disallow complex HTML construction with XSS
                    } elseif ($aParsedExpression[2] == 'SQ_STRING') {
                        $sNewValue .= "'" . (string) $filter->purify($aParsedExpression[0]) . "'";
                    } elseif ($aParsedExpression[2] == 'WORD') {
                        $sNewValue .= str_replace("html_entity_decode", "", (string) $aParsedExpression[0]);
                    } else {
                        $sNewValue .= $aParsedExpression[0];
                    }
                }
                $sNewValue .= "}";
            }
        }
        gc_collect_cycles(); // To counter a high memory usage of HTML-Purifier
        return $sNewValue;
    }

    /**
     * Defines the customs validation rule for language string
     *
     * @param mixed $value
     * @return string
     */
    public function languageFilter($value)
    {
        /* No need to filter empty $value */
        if (empty($value)) {
            return strval($value);
        }
        // Maybe use the array of language ?
        return preg_replace('/[^a-z0-9-]/i', '', (string) $value);
    }

    /**
     * Defines the customs validation rule for multi language string
     *
     * @param mixed $value
     * @return string
     */
    public function multiLanguageFilter($value)
    {
        /* No need to filter empty $value */
        if (empty($value)) {
            return strval($value);
        }
        $aValue = explode(" ", trim((string) $value));
        $aValue = array_map("sanitize_languagecode", $aValue);
        return implode(" ", $aValue);
    }

    /**
     * Checks whether an URL seems unsafe in terms of XSS.
     * @param string $url
     * @return boolean Returns true if the URL is unsafe.
     */
    public static function isXssUrl($url)
    {
        /* No need to filter empty $value */
        if (empty($url)) {
            return false;
        }
        $decodedUrl = self::treatSpecialChars($url);
        $clean = self::removeInvisibleChars($decodedUrl);

        // Remove javascript:
        if (self::hasUnsafeScheme($clean)) {
            return true;
        }
        return false;
    }

    /**
     * Removes invisible characters from a string.
     * @param string $string
     * @return string
     */
    public static function removeInvisibleChars($string)
    {
        // Remove invisible characters
        $prevString = '';
        while ($prevString != $string) {
            $prevString = $string;
            $string = preg_replace('/\p{C}/u', '', $string);
        };

        return $string;
    }

    /**
     * Checks if URL contains an unsafe scheme.
     * It currently checks for "javascript:" only.
     * Note: URL should be previously decoded.
     * @param string $url
     * @return boolean
     */
    public static function hasUnsafeScheme($url)
    {
        // TODO: Check for other schemes? FTP? vbscript?
        return stripos($url, "javascript:") !== false;
    }

    /**
     * Decodes URL encoded characters and html entities.
     * @param string $string
     * @return string
     */
    public static function treatSpecialChars($string)
    {
        // TODO: Recurse?
        return urldecode(html_entity_decode($string));
    }
}
