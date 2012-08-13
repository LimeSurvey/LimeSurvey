<?php

/** This file is part of KCFinder project
  *
  *      @desc Text processing helper class
  *   @package KCFinder
  *   @version 2.51
  *    @author Pavel Tzonkov <pavelc@users.sourceforge.net>
  * @copyright 2010, 2011 KCFinder Project
  *   @license http://www.opensource.org/licenses/gpl-2.0.php GPLv2
  *   @license http://www.opensource.org/licenses/lgpl-2.1.php LGPLv2
  *      @link http://kcfinder.sunhater.com
  */

class text {

  /** Replace repeated white spaces to single space
    * @param string $string
    * @return string */

    static function clearWhitespaces($string) {
        return trim(preg_replace('/\s+/s', " ", $string));
    }

  /** Normalize the string for HTML attribute value
    * @param string $string
    * @return string */

    static function htmlValue($string) {
        return
            str_replace('"', "&quot;",
            str_replace("'", '&#39;',
            str_replace('<', '&lt;',
            str_replace('&', "&amp;",
        $string))));
    }

  /** Normalize the string for JavaScript string value
    * @param string $string
    * @return string */

    static function jsValue($string) {
        return
            preg_replace('/\r?\n/', "\\n",
            str_replace('"', "\\\"",
            str_replace("'", "\\'",
            str_replace("\\", "\\\\",
        $string))));
    }

  /** Normalize the string for XML tag content data
    * @param string $string
    * @param bool $cdata */

    static function xmlData($string, $cdata=false) {
        $string = str_replace("]]>", "]]]]><![CDATA[>", $string);
        if (!$cdata)
            $string = "<![CDATA[$string]]>";
        return $string;
    }

  /** Returns compressed content of given CSS code
    * @param string $code
    * @return string */

    static function compressCSS($code) {
        $code = self::clearWhitespaces($code);
        $code = preg_replace('/ ?\{ ?/', "{", $code);
        $code = preg_replace('/ ?\} ?/', "}", $code);
        $code = preg_replace('/ ?\; ?/', ";", $code);
        $code = preg_replace('/ ?\> ?/', ">", $code);
        $code = preg_replace('/ ?\, ?/', ",", $code);
        $code = preg_replace('/ ?\: ?/', ":", $code);
        $code = str_replace(";}", "}", $code);
        return $code;
    }
}

?>