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
/*
  *
 * Copyright (c) 2002,2003 Free Software Foundation
 * developed under the custody of the
 * Open Web Application Security Project
 * (http://www.owasp.org)
 *
 * This file is part of the PHP Filters.
 * PHP Filters is free software; you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * PHP Filters is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU General Public License for more details.
 *
 * If you are not able to view the LICENSE, which should
 * always be possible within a valid and working PHP Filters release,
 * please write to the Free Software Foundation, Inc.,
 * 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 * to get a copy of the GNU General Public License or to report a
 * possible license violation.
 */
///////////////////////////////////////
// sanitize.inc.php
// Sanitization functions for PHP
// by: Gavin Zuchlinski, Jamie Pratt, Hokkaido
// webpage: http://libox.net
// Last modified: December 21, 2003
//
// Many thanks to those on the webappsec list for helping me improve these functions
///////////////////////////////////////
// Function list:
// sanitize_paranoid_string($string) -- input string, returns string stripped of all non
//           alphanumeric
// sanitize_system_string($string) -- input string, returns string stripped of special
//           characters
// sanitize_html_string($string) -- input string, returns string with html replacements
//           for special characters
// sanitize_int($integer) -- input integer, returns ONLY the integer (no extraneous
//           characters
// sanitize_float($float) -- input float, returns ONLY the float (no extraneous
//           characters)
// sanitize($input, $flags) -- input any variable, performs sanitization
//           functions specified in flags. flags can be bitwise
//           combination of PARANOID, SQL, SYSTEM, HTML, INT, FLOAT, LDAP,
//           UTF8
// sanitize_user($string) -- total length check (and more ??)
// sanitize_userfullname($string) -- total length check (and more ??)
//
//
///////////////////////////////////////
//
// 20031121 jp - added defines for magic_quotes and register_globals, added ; to replacements
//               in sanitize_sql_string() function, created rudimentary testing pages
// 20031221 gz - added nice_addslashes and changed sanitize_sql_string to use it
// 20070213 lemeur - marked sanitize_sql_string as obsolete, should use db_quote instead
// 20071032 lemeur - added sanitize_user and sanitize_userfullname
//
/////////////////////////////////////////

define("PARANOID", 1);
//define("SQL", 2);
define("SYSTEM", 4);
define("HTML", 8);
define("INT", 16);
define("FLOAT", 32);
define("LDAP", 64);
define("UTF8", 128);

// get magic_quotes_gpc ini setting - jp
$magic_quotes = (bool) @ini_get('magic_quotes_gpc');
if ($magic_quotes == true) {
    define("MAGIC_QUOTES", 1);
} else {
    define("MAGIC_QUOTES", 0);
}

// addslashes wrapper to check for gpc_magic_quotes - gz
function nice_addslashes($string)
{
    // if magic quotes is on the string is already quoted, just return it
    if (MAGIC_QUOTES) {
        return $string;
    } else {
        return addslashes((string) $string);
    }
}


/**
 * Function: sanitize_filename
 * Returns a sanitized string, typically for URLs.
 *
 * Parameters:
 *     $string - The string to sanitize.
 *     $force_lowercase - Force the string to lowercase?
 *     $alphanumeric - If set to *true*, will remove all non-alphanumeric characters.
 */
function sanitize_filename($filename, $force_lowercase = true, $alphanumeric = false, $beautify = true, $directory = false)
{
    // sanitize filename
    $filename = mb_ereg_replace(
        '[<>:"\\|?*]|
        [\x00-\x1F]|
        [\x7F\xA0\xAD]|
        [#\[\]@!$&\'()+,;=]|
        [{}^\~`]',
        '-',
        (string) $filename
    );
    // Removes smart quotes
    $filename = str_replace(array("\xe2\x80\x98", "\xe2\x80\x99", "\xe2\x80\x9c", "\xe2\x80\x9d", "\xe2\x80\x93", "\xe2\x80\x94", "\xe2\x80\xa6"), array('','', '', '', '-', '--','...'), $filename);
    // avoids ".", ".." or ".hiddenFiles"
    $filename = ltrim($filename, '.-');
    // optional beautification
    if ($beautify) {
        $filename = beautify_filename($filename);
    }
    // maximise filename length to 255 bytes http://serverfault.com/a/9548/44086
    $ext = pathinfo((string) $filename, PATHINFO_EXTENSION);
    $filename_info = $directory ? $filename : pathinfo((string) $filename, PATHINFO_FILENAME);
    $filename = mb_strcut($filename_info, 0, 255 - ($ext ? strlen($ext) + 1 : 0), mb_detect_encoding((string) $filename)) . ($ext ? '.' . $ext : '');
    $filename = ($alphanumeric) ? mb_ereg_replace("[^a-zA-Z0-9]", "", $filename) : $filename;

    if ($force_lowercase) {
        $filename = mb_strtolower($filename, 'UTF-8');
    }
    // At the end of the process there are sometimes question marks left from non-UTF-8 characters
    $filename = str_replace('?', '', $filename);
    return $filename;
}

/**
 * @param string $filename
 */
function beautify_filename($filename)
{
    // reduce consecutive characters
    $filename = preg_replace(array(
        // "file   name.zip" becomes "file-name.zip"
        '/ +/',
        // "file___name.zip" becomes "file-name.zip"
        '/_+/',
        // "file---name.zip" becomes "file-name.zip"
        '/-+/'
    ), '-', $filename);
    $filename = preg_replace(array(
        // "file--.--.-.--name.zip" becomes "file.name.zip"
        '/-*\.-*/',
        // "file...name..zip" becomes "file.name.zip"
        '/\.{2,}/'
    ), '.', $filename);
    // ".file-name.-" becomes "file-name"
    $filename = trim($filename, '.-');
    return $filename;
}



/**
 * Function: sanitize_dirname
 * sanitizes a string that will be used as a directory name
 *
 * Parameters:
 *     $string - The string to sanitize.
 *     $force_lowercase - Force the string to lowercase?
 *     $alphanumeric - If set to *true*, will remove all non-alphanumeric characters.
 */

function sanitize_dirname($string, $force_lowercase = false, $alphanumeric = false)
{
    $string = str_replace(".", "", (string) $string);
    return sanitize_filename($string, $force_lowercase, $alphanumeric, false, true);
}


// paranoid sanitization -- only let the alphanumeric set through
function sanitize_paranoid_string($string, $min = '', $max = '')
{
    if (isset($string)) {
        $string = preg_replace("/[^_.a-zA-Z0-9]/", "", (string) $string);
        $len = strlen($string);
        if ((($min != '') && ($len < $min)) || (($max != '') && ($len > $max))) {
                return false;
        }
        return $string;
    }
}

function sanitize_cquestions($string, $min = '', $max = '')
{
    if (isset($string)) {
        $string = preg_replace("/[^_.a-zA-Z0-9+#]/", "", (string) $string);
        $len = strlen($string);
        if ((($min != '') && ($len < $min)) || (($max != '') && ($len > $max))) {
                return false;
        }
        return $string;
    }
}

// sanitize a string in prep for passing a single argument to system() (or similar)
function sanitize_system_string($string, $min = '', $max = '')
{
    if (isset($string)) {
        $pattern = '/(;|\||`|>|<|&|^|"|' . "\n|\r|'" . '|{|}|[|]|\)|\()/i'; // no piping, passing possible environment variables ($),
        // separate commands, nested execution, file redirection,
        // background processing, special commands (backspace, etc.), quotes
        // newlines, or some other special characters
        $string = preg_replace($pattern, '', (string) $string);
        $string = '"' . preg_replace('/\$/', '\\\$', $string) . '"'; //make sure this is only interpretted as ONE argument
        $len = strlen($string);
        if ((($min != '') && ($len < $min)) || (($max != '') && ($len > $max))) {
            return false;
        }
        return $string;
    }
}

function sanitize_xss_string($string)
{
    if (isset($string)) {
        $bad = array('*', '^', '&', ';', '\"', '(', ')', '%', '$', '?');
        return str_replace($bad, '', (string) $string);
    }
}



// sanitize a string for SQL input (simple slash out quotes and slashes)
function sanitize_sql_db_tablename($string)
{
    $bad = array('*', '^', '&', '\'', '-', ';', '\"', '(', ')', '%', '$', '?');
    return str_replace($bad, "", (string) $string);
}

// sanitize a string for SQL input (simple slash out quotes and slashes)
function sanitize_ldap_string($string, $min = '', $max = '')
{
    $pattern = '/(\)|\(|\||&)/';
    $len = strlen((string) $string);
    if ((($min != '') && ($len < $min)) || (($max != '') && ($len > $max))) {
        return false;
    }
    return preg_replace($pattern, '', (string) $string);
}


// sanitize a string for HTML (make sure nothing gets interpretted!)
function sanitize_html_string($string)
{
    $pattern[0] = '/\&/';
    $pattern[1] = '/</';
    $pattern[2] = "/>/";
    $pattern[3] = '/\n/';
    $pattern[4] = '/"/';
    $pattern[5] = "/'/";
    $pattern[6] = "/%/";
    $pattern[7] = '/\(/';
    $pattern[8] = '/\)/';
    $pattern[9] = '/\+/';
    $pattern[10] = '/-/';
    $replacement[0] = '&amp;';
    $replacement[1] = '&lt;';
    $replacement[2] = '&gt;';
    $replacement[3] = '<br />';
    $replacement[4] = '&quot;';
    $replacement[5] = '&#39;';
    $replacement[6] = '&#37;';
    $replacement[7] = '&#40;';
    $replacement[8] = '&#41;';
    $replacement[9] = '&#43;';
    $replacement[10] = '&#45;';
    return preg_replace($pattern, $replacement, (string) $string);
}

// make int int!
function sanitize_int($integer, $min = '', $max = '')
{
    $int = preg_replace("#[^0-9]#", "", (string) $integer);
    if ((($min != '') && ($int < $min)) || (($max != '') && ($int > $max))) {
        return false;
    }
    if ($int == '') {
        return null;
    }
    return (int) $int;
}

// sanitize a username
// TODO: define the exact format of the username
// allow for instance 0-9a-zA-Z@_-.
/**
 * @param string $string
 */
function sanitize_user($string)
{
    $username_length = 64;
    $string = mb_substr($string, 0, $username_length);
    return $string;
}

// sanitize a username
// TODO: define the exact format of the username
// allow for instance 0-9a-zA-Z@_-.
function sanitize_userfullname($string)
{
    $username_length = 50;
    $string = mb_substr((string) $string, 0, $username_length);
    return $string;
}

function sanitize_labelname($string)
{
    $labelname_length = 100;
    $string = mb_substr((string) $string, 0, $labelname_length);
    return $string;
}

// make float float!
function sanitize_float($float, $min = '', $max = '')
{
    $float = str_replace(',', '.', (string) $float);
    // GMP library allows for high precision and high value numbers
    if (function_exists('gmp_init') && defined('GMP_VERSION') && version_compare(GMP_VERSION, '4.3.2') == 1) {
        $gNumber = gmp_init($float);
        if (($min != '' && gmp_cmp($gNumber, $min) < 0) || ($max != '' && gmp_cmp($gNumber, $max) > 0)) {
            return false;
        } else {
            return gmp_strval($gNumber);
        }
    } else {
        $fNumber = str_replace(',', '.', $float);
        $fNumber = floatval($fNumber);
        if ((($min != '') && ($fNumber < $min)) || (($max != '') && ($fNumber > $max))) {
                    return false;
        }
        return $fNumber;
    }
}


// glue together all the other functions
function sanitize($input, $flags, $min = '', $max = '')
{
    if ($flags & PARANOID) {
        $input = sanitize_paranoid_string($input, $min, $max);
    }
    if ($flags & INT) {
        $input = sanitize_int($input, $min, $max);
    }
    if ($flags & FLOAT) {
        $input = sanitize_float($input, $min, $max);
    }
    if ($flags & HTML) {
        $input = sanitize_html_string($input);
    }
    if ($flags & LDAP) {
        $input = sanitize_ldap_string($input, $min, $max);
    }
    if ($flags & SYSTEM) {
        $input = sanitize_system_string($input, $min, $max);
    }
    return $input;
}

function check_paranoid_string($input, $min = '', $max = '')
{
    if ($input != sanitize_paranoid_string($input, $min, $max)) {
        return false;
    }
    return true;
}

function check_int($input, $min = '', $max = '')
{
    if ($input != sanitize_int($input, $min, $max)) {
        return false;
    }
    return true;
}

function check_float($input, $min = '', $max = '')
{
    if ($input != sanitize_float($input, $min, $max)) {
        return false;
    }
    return true;
}

function check_html_string($input, $min = '', $max = '')
{
    if ($input != sanitize_html_string($input)) {
            return false;
    }
    return true;
}


function check_system_string($input, $min = '', $max = '')
{
    if ($input != sanitize_system_string($input, $min, $max)) {
            return false;
    }
    return true;
}

// glue together all the other functions
/**
 * @param $input
 * @param $flags
 * @param string $min
 * @param string $max
 * @return bool
 * @throws Exception
 */
function check($input, $flags, $min = '', $max = '')
{
    $oldput = $input;
    if ($flags & UTF8) {
        // This case used before function my_utf8_decode, which doesn't exist.
        throw new Exception('UTF8 not supported');
    }
    if ($flags & PARANOID) {
        $input = sanitize_paranoid_string($input, $min, $max);
    }
    if ($flags & INT) {
        $input = sanitize_int($input, $min, $max);
    }
    if ($flags & FLOAT) {
        $input = sanitize_float($input, $min, $max);
    }
    if ($flags & HTML) {
        $input = sanitize_html_string($input);
    }
    if ($flags & LDAP) {
        $input = sanitize_ldap_string($input, $min, $max);
    }
    if ($flags & SYSTEM) {
        $input = sanitize_system_string($input, $min, $max);
    }
    if ($input != $oldput) {
        return false;
    }
    return true;
}

/**
 * Sanitizes a language code by removing all non-alphanumeric and non-dash characters.
 *
 * This function removes any characters that are not letters (a-z), numbers (0-9),
 * or hyphens (-) from the input string. It is case-insensitive in its matching.
 * @deprecated 7.0 Use LSYii_Validations::languageCodeFilter
 *
 * @param string $codetosanitize The language code string to sanitize.
 * @return string The sanitized language code containing only alphanumeric characters and hyphens.
 */
function sanitize_languagecode($codetosanitize)
{
    return preg_replace('/[^a-z0-9-]/i', '', (string) $codetosanitize);
}


/**
 * Sanitizes a space-separated string of language codes.
 *
 * This function takes a space-separated string of language codes, splits them into an array,
 * sanitizes each individual language code by removing all non-alphanumeric and non-dash characters,
 * and then rejoins them back into a space-separated string.
 * @deprecated 7.0 Use LSYii_Validations::multiLanguageCodeFilter
 *
 * @param string $codestringtosanitize A space-separated string of language codes to sanitize.
 * @return string A space-separated string of sanitized language codes containing only alphanumeric characters and hyphens.
 */
function sanitize_languagecodeS($codestringtosanitize)
{
    $codearray = explode(" ", trim($codestringtosanitize));
    $codearray = array_map("sanitize_languagecode", $codearray);
    return implode(" ", $codearray);
}


function sanitize_signedint($integer, $min = '', $max = '')
{
    $int = (int) $integer;

    if ((($min != '') && ($int < $min)) || (($max != '') && ($int > $max))) {
        return false; // Oops! Outside limits.
    }

    return $int;
};

/**
 * Checks the validity of IP address $ip
 *
 * @param string $ip to check
 *
 * @return boolean true if the $ip is a valid IP address
 */
function check_ip_address($ip)
{
    // Leave the wrapper in case we need to enhance the checks later
    return filter_var($ip, FILTER_VALIDATE_IP);
}

/**
 * Returns true if the argument is an absolute URL (either starting with schema+domain or just "/").
 * @param string $string
 * @return boolean
 */
function check_absolute_url($string)
{
    // Regular expression based on Symfony's UrlValidator (https://github.com/symfony/symfony/blob/6.3/src/Symfony/Component/Validator/Constraints/UrlValidator.php)
    // Modified to allow absolute URLs without the schema and domain.
    $pattern = '~^
        (http|https)://                                 # protocol
        (((?:[\_\.\pL\pN-]|%%[0-9A-Fa-f]{2})+:)?((?:[\_\.\pL\pN-]|%%[0-9A-Fa-f]{2})+)@)?  # basic auth
        (
            (?:
                (?:xn--[a-z0-9-]++\.)*+xn--[a-z0-9-]++            # a domain name using punycode
                    |
                (?:[\pL\pN\pS\pM\-\_]++\.)+[\pL\pN\pM]++          # a multi-level domain name
                    |
                [a-z0-9\-\_]++                                    # a single-level domain name
            )\.?
                |                                                 # or
            \d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}                    # an IP address
                |                                                 # or
            \[
                (?:(?:(?:(?:(?:(?:(?:[0-9a-f]{1,4})):){6})(?:(?:(?:(?:(?:[0-9a-f]{1,4})):(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:::(?:(?:(?:[0-9a-f]{1,4})):){5})(?:(?:(?:(?:(?:[0-9a-f]{1,4})):(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:(?:(?:(?:[0-9a-f]{1,4})))?::(?:(?:(?:[0-9a-f]{1,4})):){4})(?:(?:(?:(?:(?:[0-9a-f]{1,4})):(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:(?:(?:(?:(?:[0-9a-f]{1,4})):){0,1}(?:(?:[0-9a-f]{1,4})))?::(?:(?:(?:[0-9a-f]{1,4})):){3})(?:(?:(?:(?:(?:[0-9a-f]{1,4})):(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:(?:(?:(?:(?:[0-9a-f]{1,4})):){0,2}(?:(?:[0-9a-f]{1,4})))?::(?:(?:(?:[0-9a-f]{1,4})):){2})(?:(?:(?:(?:(?:[0-9a-f]{1,4})):(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:(?:(?:(?:(?:[0-9a-f]{1,4})):){0,3}(?:(?:[0-9a-f]{1,4})))?::(?:(?:[0-9a-f]{1,4})):)(?:(?:(?:(?:(?:[0-9a-f]{1,4})):(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:(?:(?:(?:(?:[0-9a-f]{1,4})):){0,4}(?:(?:[0-9a-f]{1,4})))?::)(?:(?:(?:(?:(?:[0-9a-f]{1,4})):(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:(?:(?:(?:(?:[0-9a-f]{1,4})):){0,5}(?:(?:[0-9a-f]{1,4})))?::)(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:(?:[0-9a-f]{1,4})):){0,6}(?:(?:[0-9a-f]{1,4})))?::))))
            \]  # an IPv6 address
        )
        (:[0-9]+)?                              # a port (optional)
        (?:/ (?:[\pL\pN\-._\~!$&\'()*+,;=:@]|%%[0-9A-Fa-f]{2})* )*          # a path
        (?:\? (?:[\pL\pN\-._\~!$&\'\[\]()*+,;=:@/?]|%%[0-9A-Fa-f]{2})* )?   # a query (optional)
        (?:\# (?:[\pL\pN\-._\~!$&\'()*+,;=:@/?]|%%[0-9A-Fa-f]{2})* )?       # a fragment (optional)

        |^(/\S+)$   # Simple absolute URL (without domain part).

    $~ixu';

    return preg_match($pattern, $string) == 1;
}

/**
 * Remove all chars from $value that are not alphanumeric or dash or underscore
 *
 * @param string $value
 * @return string
 */
function sanitize_alphanumeric($value)
{
    return preg_replace("/[^a-zA-Z0-9\-\_]/", "", $value);
}
