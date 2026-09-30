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
     * Expression functions that turn encoded text back into HTML markup, and are not allowed in a filtered expression
     * @var string[]
     */
    const XSS_UNSAFE_EM_FUNCTIONS = array('html_entity_decode', 'htmlspecialchars_decode', 'quoted_printable_decode');

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
    /**
     * Filter attribute for allowDataUri (default is false)
     * @var boolean
     */
    public $allowDataUri = false;
    /**
     * Refuse the value (add a validation error) when the XSS filter had to change an expression.
     * Off by default : the filter is always applied, but only editors that show the validation errors
     * should refuse the save. Other callers (import, copy …) may ignore a failed save and lose the text.
     * @var boolean
     */
    public static $refuseChangedExpressions = false;
    /**
     * Errors about expressions changed by the last xssFilter call, only for the value as submitted
     * @var string[]
     */
    private $expressionErrors = array();
    /**
     * Notices about expressions disabled outside a refusing context (e.g. during import), for the current request.
     * Bulk callers such as the survey import read these to warn the user, since they can not show a validation error.
     * @var string[]
     */
    private static $disabledExpressionNotices = array();

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
            /* Unchanged content is grandfathered : only newly added or modified content is refused/disabled */
            $bModified = !($object instanceof LSActiveRecord) || $object->isAttributeModifiedFromStored($attribute);
            $object->$attribute = $this->xssFilter($object->$attribute, $bModified);
            if ($bModified && self::$refuseChangedExpressions) {
                foreach ($this->getExpressionErrors() as $sError) {
                    $this->addError($object, $attribute, '{attribute}: ' . $sError);
                }
            }
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
            $object->$attribute = self::languageCodeFilter($object->$attribute);
        }
        if ($this->isLanguageMulti) {
            $object->$attribute = self::multiLanguageCodeFilter($object->$attribute);
        }
        if (!$this->allowDataUri) {
            $object->$attribute = $this->dataUriFilter($object->$attribute);
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
     * The HTML outside and inside expressions is always purified, like before.
     * In addition, expressions that could bypass the HTML filter at render time (functions that decode entities,
     * sprintf with a non-literal or unsafe format, or an expression built inside a quoted string) are detected
     * and, when $neutralizeUnsafeExpressions is true, disabled by encoding their curly braces so they render as
     * inert text. The errors are collected in $expressionErrors so that editors can refuse the save instead.
     *
     * @param null|string $value
     * @param boolean $neutralizeUnsafeExpressions Whether to disable the unsafe expressions found (default true)
     * @return string
     */
    public function xssFilter($value, $neutralizeUnsafeExpressions = true)
    {
        $this->expressionErrors = array();
        /* No need to filter empty $value */
        if (empty($value)) {
            return strval($value);
        }
        $filter = LSYii_HtmlPurifier::getXssPurifier();
        Yii::import('application.helpers.expressions.em_core_helper', true); // Already imported in em_manager_helper.php ?
        $oExpressionManager = new ExpressionManager();
        /** Start to get complete filtered value with  url decode {QCODE} (bug #09300). This allow only question number in url, seems OK with XSS protection **/
        $sFiltered = $filter->purify($value);
        $sFiltered = preg_replace('#%7B([a-zA-Z0-9\.]*)%7D#', '{$1}', (string) $sFiltered);
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
                $bUnsafe = false;
                $sExpression = $this->filterExpression($oExpressionManager->Tokenize($sExpression, true), $filter, $bUnsafe);
                if ($bUnsafe && $neutralizeUnsafeExpressions) {
                    // Disable the whole expression : it renders as inert text and can not be evaluated again
                    $sNewValue .= str_replace(array('{', '}'), array('&#123;', '&#125;'), "{" . $sExpression . "}");
                    // Record it for bulk callers (import …) that can not show a validation error, unless an editor refuses it
                    if (!self::$refuseChangedExpressions) {
                        foreach ($this->expressionErrors as $sError) {
                            if (!in_array($sError, self::$disabledExpressionNotices, true)) {
                                self::$disabledExpressionNotices[] = $sError;
                            }
                        }
                    }
                } else {
                    $sNewValue .= "{" . $sExpression . "}";
                }
            }
        }
        gc_collect_cycles(); // To counter a high memory usage of HTML-Purifier
        return $sNewValue;
    }

    /**
     * Run a callback with $refuseChangedExpressions enabled, for editors that show the validation errors to the user
     *
     * @param callable $callback
     * @return mixed The return value of the callback
     */
    public static function refuseChangedExpressionsDuring(callable $callback)
    {
        $previous = self::$refuseChangedExpressions;
        self::$refuseChangedExpressions = true;
        try {
            return $callback();
        } finally {
            self::$refuseChangedExpressions = $previous;
        }
    }

    /**
     * Get the errors about unsafe expressions found by the last xssFilter call
     *
     * @return string[]
     */
    public function getExpressionErrors()
    {
        return $this->expressionErrors;
    }

    /**
     * Get the notices about expressions disabled outside a refusing context during the current request
     *
     * @return string[]
     */
    public static function getDisabledExpressionNotices()
    {
        return self::$disabledExpressionNotices;
    }

    /**
     * Clear the collected notices about disabled expressions (call before a bulk operation such as an import)
     *
     * @return void
     */
    public static function clearDisabledExpressionNotices()
    {
        self::$disabledExpressionNotices = array();
    }

    /**
     * Record an error about an unsafe expression (each distinct message only once)
     *
     * @param string $sError The translated error message
     * @return void
     */
    private function addExpressionError($sError)
    {
        if (!in_array($sError, $this->expressionErrors, true)) {
            $this->expressionErrors[] = $sError;
        }
    }

    /**
     * Rebuild an expression from its tokens, purifying the strings, and flag it when it contains an unsafe construct
     *
     * @param array $aTokens The tokens of the expression, from ExpressionManager::Tokenize with spaces
     * @param CHtmlPurifier $filter The XSS purifier
     * @param boolean $bUnsafe Set to true when the expression contains a construct that could bypass the HTML filter
     * @return string The rebuilt expression, without the surrounding curly braces
     */
    private function filterExpression(array $aTokens, $filter, &$bUnsafe)
    {
        $aTokens = array_values($aTokens);
        $sNewExpression = "";
        foreach ($aTokens as $key => $aToken) {
            if ($aToken[2] == 'DQ_STRING') {
                // This disallow complex HTML construction with XSS, quotes are escaped again as Tokenize removed the escaping
                $sNewExpression .= "\"" . str_replace("\"", "\\\"", $this->filterExpressionString($aTokens, $key, $filter, $bUnsafe)) . "\"";
            } elseif ($aToken[2] == 'SQ_STRING') {
                $sNewExpression .= "'" . str_replace("'", "\\'", $this->filterExpressionString($aTokens, $key, $filter, $bUnsafe)) . "'";
            } else {
                if ($aToken[2] == 'WORD' && !$this->isSafeExpressionFunction($aTokens, $key)) {
                    $bUnsafe = true;
                    if (strtolower((string) $aToken[0]) === 'sprintf') {
                        $this->addExpressionError(gT("The function sprintf() is only allowed with a fixed format text that does not use %c.", "unescaped"));
                    } else {
                        $this->addExpressionError(sprintf(gT("The function %s() is not allowed in expressions.", "unescaped"), strtolower((string) $aToken[0])));
                    }
                }
                $sNewExpression .= $aToken[0];
            }
        }
        return $sNewExpression;
    }

    /**
     * Purify a string of an expression and flag it when it contains an expression built inside the string.
     * EM evaluates the result of an expression again (up to 3 times), so a string that contains curly braces
     * could become a new expression that was never filtered. The pattern of regexMatch is kept as is : it is
     * never shown and needs the curly braces for quantifiers.
     *
     * @param array $aTokens The tokens of the expression, indexed from 0
     * @param integer $key The index of the string token
     * @param CHtmlPurifier $filter The XSS purifier
     * @param boolean $bUnsafe Set to true when the string contains an expression
     * @return string The purified string, without quotes
     */
    private function filterExpressionString(array $aTokens, $key, $filter, &$bUnsafe)
    {
        $sString = (string) $filter->purify($aTokens[$key][0]);
        $aPreviousTokens = self::getSignificantTokens(array_reverse(array_slice($aTokens, 0, $key)));
        $bIsRegexPattern = count($aPreviousTokens) >= 2
            && $aPreviousTokens[0][2] === 'LP'
            && $aPreviousTokens[1][2] === 'WORD'
            && strtolower((string) $aPreviousTokens[1][0]) === 'regexmatch';
        if ($bIsRegexPattern) {
            return $sString;
        }
        /* Any curly brace in a string could form a new expression at render time (also when split over several
           string arguments and joined), which would never have been filtered. regexMatch is the only exception. */
        if (strpos($sString, '{') !== false || strpos($sString, '}') !== false) {
            $bUnsafe = true;
            $this->addExpressionError(gT('Curly braces inside quoted text are not allowed. Join the text instead, for example "Hello " + NAME.', 'unescaped'));
        }
        return $sString;
    }

    /**
     * Remove the SPACE tokens and the empty tokens that Tokenize with spaces returns between the real tokens
     *
     * @param array $aTokens Expression tokens
     * @return array The remaining tokens, indexed from 0
     */
    private static function getSignificantTokens(array $aTokens)
    {
        $aSignificantTokens = array();
        foreach ($aTokens as $aToken) {
            if ($aToken[2] !== 'SPACE' && (string) $aToken[0] !== '') {
                $aSignificantTokens[] = $aToken;
            }
        }
        return $aSignificantTokens;
    }

    /**
     * Check if a WORD token in an expression can be kept.
     * Functions that decode entities are never allowed, sprintf is allowed only with a safe literal format.
     *
     * @param array $aTokens The tokens of the expression, indexed from 0
     * @param integer $key The index of the WORD token to check
     * @return boolean
     */
    private function isSafeExpressionFunction(array $aTokens, $key)
    {
        $sWord = strtolower((string) $aTokens[$key][0]);
        if (in_array($sWord, self::XSS_UNSAFE_EM_FUNCTIONS, true)) {
            return false;
        }
        if ($sWord !== 'sprintf') {
            return true;
        }
        /* The format must be the literal string directly after the opening parenthesis, alone as first argument */
        $aNextTokens = self::getSignificantTokens(array_slice($aTokens, $key + 1));
        if (count($aNextTokens) < 3 || $aNextTokens[0][2] !== 'LP') {
            return false;
        }
        if (!in_array($aNextTokens[1][2], array('DQ_STRING', 'SQ_STRING'), true)) {
            return false;
        }
        if (!in_array($aNextTokens[2][2], array('COMMA', 'RP'), true)) {
            return false;
        }
        return self::isSafeSprintfFormat((string) $aNextTokens[1][0]);
    }

    /**
     * Check that a sprintf format only contains conversions that can not create characters from numbers.
     * Every % must start a literal %% or a conversion with a safe specifier (no %c) ; the padding character
     * can not be a letter so the PHP and JavaScript implementations of sprintf read the format the same way.
     *
     * @param string $format The sprintf format
     * @return boolean
     */
    public static function isSafeSprintfFormat($format)
    {
        $sRemaining = preg_replace('/%%|%(?:\d+\$)?(?:[-+ 0]|\'[^a-zA-Z%])*\d*(?:\.\d+)?[bdeEfFgGhHosuxX]/', '', $format);
        return $sRemaining !== null && strpos($sRemaining, '%') === false;
    }

    /**
     * Function for backward compatibility - see languageCodeFilter()
     *
     * @param mixed $value The language string to filter. Can be any type, but only strings are processed.
     *
     * @return string The filtered language string containing only letters and hyphens.
     *                Returns an empty string if the input is empty or not a string.
     * @deprecated 7.0.0 Use languageCodeFilter() instead
     */
    public function languageFilter($value)
    {
        return self::languageCodeFilter($value);
    }

    /**
     * Filters a language string by removing invalid characters.
     *
     * This method validates and sanitizes a language code string by removing all characters
     * except letters (a-z) and hyphens (-). This ensures the value
     * conforms to standard language code formats (e.g., 'en', 'en-US', 'zh-Hans').
     *
     * Note: This function does NOT check if the language code is available in
     * the general or restricted  list of language codes in LimeSurvey
     *
     * @param mixed $value The language string to filter. Can be any type, but only strings are processed.
     *
     * @return string The filtered language string containing only letters and hyphens.
     *                Returns an empty string if the input is empty or not a string.
     */
    public static function languageCodeFilter($value)
    {
        /* No need to filter empty $value */
        if (!is_string($value) || empty(trim($value))) {
            return '';
        }
        // Maybe use the array of language ?
        return preg_replace('/[^a-z-]/i', '', $value);
    }


    /**
     * Function for backward compatibility - see multiLanguageCodeFilter()
     *
     * @param mixed $value The multi-language string to filter. Should be a space-separated list of language codes.
     *                      Can be any type, but only strings are processed.
     * @return string The filtered multi-language string containing only valid language codes separated by spaces.
     *                Duplicate codes are removed. Returns an empty string if the input is empty or not a string.
     * @deprecated 7.0.0 Use multiLanguageCodeFilter() instead
     */
    public function multiLanguageFilter($value)
    {
        return self::multiLanguageCodeFilter($value);
    }


    /**
     * Filters a multi-language string by removing invalid characters from each language code.
     *
     * This method processes a space-separated string of language codes, applying language code
     * filtering to each individual code. It removes duplicates and empty values, then rejoins
     * the filtered codes back into a space-separated string.
     *
     * Note: This function does NOT check if the language codes are available in
     * the general or restricted list of language codes in LimeSurvey.
     *
     * @param mixed $value The multi-language string to filter. Should be a space-separated list of language codes.
     *                      Can be any type, but only strings are processed.
     * @return string The filtered multi-language string containing only valid language codes separated by spaces.
     *                Duplicate codes are removed. Returns an empty string if the input is empty or not a string.
     */
    public static function multiLanguageCodeFilter($value)
    {
        /* No need to filter empty $value */
        if (!is_string($value) || empty(trim($value))) {
            return '';
        }
        $aValue = explode(" ", trim($value));
        $aValue = array_map([self::class, 'languageCodeFilter'], $aValue);
        // remove empty or duplicate values
        $aValue = array_filter(array_unique($aValue));
        // join back
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

    /**
     * Filters data URIs.
     * @param string $string
     * @return string
     */
    public static function dataUriFilter($value)
    {
        /* No need to filter empty $value */
        if (empty($value)) {
            return strval($value);
        }
        // Filter out data URIs (with regex)
        $filtered = preg_replace('/src\s*=\s*["\']data:[^\'"]+["\']/', '', $value);
        return $filtered;
    }
}
