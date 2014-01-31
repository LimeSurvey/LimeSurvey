<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
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
       *


    Wrapper to use phpgettext as a class and omit having an english translation
    USAGE:
    require_once($rootdir.'classes/core/language.php');
    $locale = new limesurvey_lang('en'); // Char code
    print $locale->getTranslation("Hello World!");
    */


    class Limesurvey_lang {

        var $gettextclass;
        var $langcode;

        function  __construct($sLanguageCode, $bForceRefresh=false){
            if(empty($sLanguageCode))
                trigger_error('langcode param is undefined ', E_USER_WARNING);

            static $aClassCache=array();
            Yii::app()->loadHelper('sanitize');
            $sLanguageCode=sanitize_languagecode($sLanguageCode);
            if (isset($aClassCache[$sLanguageCode]) && !$bForceRefresh)
            {
                $this->gettextclass = $aClassCache[$sLanguageCode];
            }
            else
            {
                $streamer = new FileReader(getcwd().DIRECTORY_SEPARATOR.'locale'.DIRECTORY_SEPARATOR.$sLanguageCode.DIRECTORY_SEPARATOR.'LC_MESSAGES'.DIRECTORY_SEPARATOR.$sLanguageCode.'.mo');
                $this->gettextclass = $aClassCache[$sLanguageCode] = new gettext_reader($streamer);
            }
            $this->langcode = $sLanguageCode;
        }
        
        function getlangcode()
        {
            return $this->langcode;
        }

        /**
        * This function translates plural strings to their according language
        *
        * @param $single $string The single form of the string to translate
        * @param $plural $string The plural form to translate
        * @param $number $integer Depending on the number of items the right plural form is taken
        * @param mixed $escapemode Different uses require the string to be escaped accordinlgy. Possible values are 'html'(default),'js' and 'unescaped'
        * @return string Translated string
        */
        function ngT($single, $plural, $number, $escapemode = 'html')
        {
            if ($this->gettextclass)
            {
                $basestring=str_replace('&lsquo;','\'',$this->gettextclass->ngettext($single, $plural, $number));
                switch ($escapemode)
                {
                    case 'html':
                        return $this->HTMLEscape($basestring);
                        break;
                    case 'js':
                        return $this->javascriptEscape($basestring);
                        break;
                    case 'unescaped':
                        return $basestring;
                        break;
                    default:
                        return "Unsupported EscapeMode in gT method";
                        break;
                }
            } else {
                switch ($escapemode)
                {
                    case 'html':
                        return $this->HTMLEscape($string);
                        break;
                    case 'js':
                        return $this->javascriptEscape($string);
                        break;
                    case 'unescaped':
                        return $string;
                        break;
                    default:
                        return "Unsupported EscapeMode in gT method";
                        break;
                }
            }
        }

        /**
        * This function does the same as gT but instead of returning it outputs the string right away
        *
        * @param string $string The string to translate
        * @param mixed $escapemode Different uses require the string to be escaped accordinlgy. Possible values are 'html'(default),'js' and 'unescaped'
        */
        function eT($string, $escapemode = 'html')
        {
            echo $this->gT($string,$escapemode);
        }



        /**
        * This function translates strings to their according language
        *
        * @param string $string The string to translate
        * @param mixed $escapemode Different uses require the string to be escaped accordinlgy. Possible values are 'html'(default),'js' and 'unescaped'
        * @return string Translated string
        */
        function gT($sText, $sEscapeMode = 'html')
        {
            if ($this->gettextclass)
            {
                $basestring=$this->gettextclass->translate($sText);

                switch ($sEscapeMode)
                {
                    case 'html':
                        return $this->HTMLEscape($basestring);
                        break;
                    case 'js':
                        return $this->javascriptEscape($basestring);
                        break;
                    case 'unescaped':
                        return $basestring;
                        break;
                    default:
                        return "Unsupported EscapeMode in gT method";
                        break;
                }
            } else {
                switch ($sEscapeMode)
                {
                    case 'html':
                        return $this->HTMLEscape($sText);
                        break;
                    case 'js':
                        return $this->javascriptEscape($sText);
                        break;
                    case 'unescaped':
                        return $sText;
                        break;
                    default:
                        return "Unsupported EscapeMode in gT method";
                        break;
                }
            }
        }

        function HTMLEscape($str) {
            // escape newline characters, too, in case we put a value from
            // a TEXTAREA  into an <input type="hidden"> value attribute.
            return str_replace(array("\x0A","\x0D"),array("&#10;","&#13;"),
            htmlspecialchars( $str, ENT_QUOTES ));
        }

        // make a string safe to include in a JavaScript String parameter.
        function javascriptEscape($str, $strip_tags=false, $htmldecode=false) {
            $new_str ='';

            if ($htmldecode==true) {
                $str=html_entity_decode($str,ENT_QUOTES,'UTF-8');
            }
            if ($strip_tags==true)
            {
                $str=strip_tags($str);
            }
            return str_replace(array('\'','"', "\n"),
            array("\\'",'\u0022', "\\n"),
            $str);
        }

    }

    require_once(APPPATH.'third_party/php-gettext/streams.php');
    require_once(APPPATH.'third_party/php-gettext/gettext.php');

?>
