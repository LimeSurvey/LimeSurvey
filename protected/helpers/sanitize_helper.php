<?php


namespace ls\helpers;

define("PARANOID", 1);
//define("SQL", 2);
define("SYSTEM", 4);
define("HTML", 8);
define("INT", 16);
define("FLOAT", 32);
define("LDAP", 64);
define("UTF8", 128);

class Sanitize
{
    /**
     * Function: sanitize_filename
     * Returns a sanitized string, typically for URLs.
     *
     * Parameters:
     *     $string - The string to sanitize.
     *     $force_lowercase - Force the string to lowercase?
     *     $alphanumeric - If set to *true*, will remove all non-alphanumeric characters.
     */

    public static function filename($string, $force_lowercase = true, $alphanumeric = false)
    {
        $strip = array(
            "~",
            "`",
            "!",
            "@",
            "#",
            "$",
            "%",
            "^",
            "&",
            "*",
            "(",
            ")",
            "=",
            "+",
            "[",
            "{",
            "]",
            "}",
            "\\",
            "|",
            ";",
            ":",
            "\"",
            "'",
            "&#8216;",
            "&#8217;",
            "&#8220;",
            "&#8221;",
            "&#8211;",
            "&#8212;",
            "—",
            "–",
            ",",
            "<",
            ".",
            ">",
            "/",
            "?"
        );
        $lastdot = strrpos($string, ".");
        $clean = trim(str_replace($strip, "_", strip_tags($string)));
        $clean = preg_replace('/\s+/', "-", $clean);
        // remove the leading dot if any, this prevents the creation of hidden files on unix platforms
        $clean = preg_replace('/^\./', '', $clean);
        $clean = ($alphanumeric) ? preg_replace("/[^a-zA-Z0-9]/", "", $clean) : $clean;
        if ($lastdot !== false) {
            $clean = substr_replace($clean, '.', $lastdot, 1);
        }

        return ($force_lowercase) ?
            (function_exists('mb_strtolower')) ?
                mb_strtolower($clean, 'UTF-8') :
                strtolower($clean) :
            $clean;
    }


    /**
     * sanitizes a string that will be used as a directory name
     *
     * Parameters:
     *     $string - The string to sanitize.
     *     $force_lowercase - Force the string to lowercase?
     *     $alphanumeric - If set to *true*, will remove all non-alphanumeric characters.
     */

    public static function dirname($string, $force_lowercase = false, $alphanumeric = false)
    {
        $string = str_replace(".", "", $string);

        return self::filename($string, $force_lowercase, $alphanumeric);
    }


// paranoid sanitization -- only let the alphanumeric set through
    public static function paranoid_string($string, $min = '', $max = '')
    {
        if (isset($string)) {
            $string = preg_replace("/[^_.a-zA-Z0-9]/", "", $string);
            $len = strlen($string);
            if ((($min != '') && ($len < $min)) || (($max != '') && ($len > $max))) {
                return false;
            }

            return $string;
        }
    }

    public static function cquestions($string, $min = '', $max = '')
    {
        if (isset($string)) {
            $string = preg_replace("/[^_.a-zA-Z0-9+#]/", "", $string);
            $len = strlen($string);
            if ((($min != '') && ($len < $min)) || (($max != '') && ($len > $max))) {
                return false;
            }

            return $string;
        }
    }

// sanitize a string in prep for passing a single argument to system() (or similar)
    public static function system_string($string, $min = '', $max = '')
    {
        if (isset($string)) {
            $pattern = '/(;|\||`|>|<|&|^|"|' . "\n|\r|'" . '|{|}|[|]|\)|\()/i'; // no piping, passing possible environment variables ($),
            // separate commands, nested execution, file redirection,
            // background processing, special commands (backspace, etc.), quotes
            // newlines, or some other special characters
            $string = preg_replace($pattern, '', $string);
            $string = '"' . preg_replace('/\$/', '\\\$',
                    $string) . '"'; //make sure this is only interpretted as ONE argument
            $len = strlen($string);
            if ((($min != '') && ($len < $min)) || (($max != '') && ($len > $max))) {
                return false;
            }

            return $string;
        }
    }

    public static function xss_string($string)
    {
        if (isset($string)) {
            $bad = array('*', '^', '&', ';', '\"', '(', ')', '%', '$', '?');

            return str_replace($bad, '', $string);
        }
    }


// sanitize a string for SQL input (simple slash out quotes and slashes)
    public static function ldap_string($string, $min = '', $max = '')
    {
        $pattern = '/(\)|\(|\||&)/';
        $len = strlen($string);
        if ((($min != '') && ($len < $min)) || (($max != '') && ($len > $max))) {
            return false;
        }

        return preg_replace($pattern, '', $string);
    }


// sanitize a string for HTML (make sure nothing gets interpretted!)
    public static function html_string($string)
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

        return preg_replace($pattern, $replacement, $string);
    }

// make int int!
    public static function int($integer, $min = '', $max = '')
    {
        $int = preg_replace("#[^0-9]#", "", $integer);
        if ((($min != '') && ($int < $min)) || (($max != '') && ($int > $max))) {
            return false;
        }
        if ($int == '') {
            return null;
        }

        return $int;
    }

// sanitize a username
// TODO: define the exact format of the username
// allow for instance 0-9a-zA-Z@_-.
    public static function user($string)
    {
        $username_length = 64;
        $string = mb_substr($string, 0, $username_length);

        return $string;
    }

// sanitize a username
// TODO: define the exact format of the username
// allow for instance 0-9a-zA-Z@_-.
    public static function userfullname($string)
    {
        $username_length = 50;
        $string = mb_substr($string, 0, $username_length);

        return $string;
    }

    public static function labelname($string)
    {
        $labelname_length = 100;
        $string = mb_substr($string, 0, $labelname_length);

        return $string;
    }

// make float float!
    public static function float($float, $min = '', $max = '')
    {
        $float = str_replace(',', '.', $float);
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
    public static function all($input, $flags, $min = '', $max = '')
    {
        if ($flags & PARANOID) {
            $input = \ls\helpers\Sanitize::paranoid_string($input, $min, $max);
        }
        if ($flags & INT) {
            $input = \ls\helpers\Sanitize::int($input, $min, $max);
        }
        if ($flags & FLOAT) {
            $input = sanitize_float($input, $min, $max);
        }
        if ($flags & HTML) {
            $input = sanitize_html_string($input, $min, $max);
        }
        if ($flags & LDAP) {
            $input = sanitize_ldap_string($input, $min, $max);
        }
        if ($flags & SYSTEM) {
            $input = sanitize_system_string($input, $min, $max);
        }

        return $input;
    }


    public static function check_int($input, $min = '', $max = '')
    {
        if ($input != self::int($input, $min, $max)) {
            return false;
        }

        return true;
    }

    public static function check_float($input, $min = '', $max = '')
    {
        if ($input != sanitize_float($input, $min, $max)) {
            return false;
        }

        return true;
    }

    public static function check_html_string($input, $min = '', $max = '')
    {
        if ($input != sanitize_html_string($input, $min, $max)) {
            return false;
        }

        return true;
    }


    public static function check_ldap_string($input, $min = '', $max = '')
    {
        if ($input != sanitize_string($input, $min, $max)) {
            return false;
        }

        return true;
    }


// glue together all the other functions
    public static function check($input, $flags, $min = '', $max = '')
    {
        $oldput = $input;
        if ($flags & UTF8) {
            $input = my_utf8_decode($input);
        }
        if ($flags & PARANOID) {
            $input = \ls\helpers\Sanitize::paranoid_string($input, $min, $max);
        }
        if ($flags & INT) {
            $input = \ls\helpers\Sanitize::int($input, $min, $max);
        }
        if ($flags & FLOAT) {
            $input = sanitize_float($input, $min, $max);
        }
        if ($flags & HTML) {
            $input = sanitize_html_string($input, $min, $max);
        }
        if ($flags & LDAP) {
            $input = sanitize_ldap_string($input, $min, $max);
        }
        if ($flags & SYSTEM) {
            $input = sanitize_system_string($input, $min, $max, true);
        }
        if ($input != $oldput) {
            return false;
        }

        return true;
    }

    public static function languagecode($codetosanitize)
    {
        return preg_replace('/[^a-z0-9-]/i', '', $codetosanitize);
    }

    public static function languagecodeS($codestringtosanitize)
    {
        $codearray = explode(" ", trim($codestringtosanitize));
        $codearray = array_map([self, 'languagecode'], $codearray);

        return implode(" ", $codearray);
    }

}