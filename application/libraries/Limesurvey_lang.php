<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
 * LimeSurvey
 * Copyright (C) 2007 The LimeSurvey Project Team / Carsten Schmitz
 * All rights reserved.
 * License: http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * LimeSurvey is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 *
 * $Id: language.php 9648 2011-01-07 13:06:39Z c_schmitz $
 *
 * 
 * 


 Wrapper to use phpgettext as a class and omit having an english translation
 USAGE:
 require_once($rootdir.'classes/core/language.php');
 $locale = new limesurvey_lang('en'); // Char code
 print $locale->getTranslation("Hello World!");
 */


class Limesurvey_lang {

    var $CI;
    var $gettextclass;
    var $langcode;

    function limesurvey_lang($params = array()){
		if(empty($params))
			trigger_error('langcode param is undefined ', E_USER_WARNING);
		$langcode=reset($params);	
		
		$CI =& get_instance();
        $CI->load->helper('sanitize');
        
        $langcode[0]=sanitize_languagecode($langcode);
        
        $streamer = new FileReader(getcwd().'/locale/'.$langcode.'/LC_MESSAGES/'.$langcode.'.mo');

        $this->gettextclass = new gettext_reader($streamer);
        
        $this->langcode = $langcode;
    }

    function getlangcode()
    {
        return $this->langcode;
    }

    function gTview($string, $escapemode = 'html')
    {
        global $addTitleToLinks;
        if ( $addTitleToLinks === true)
        {
            return $this->gT($string, $escapemode = 'html');
        }
        else
        {
            return '';
        }
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
                    return $this->html_escape($basestring);
                    break;
                case 'js':
                    return $this->javascript_escape($basestring);
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
                    return $this->html_escape($string);
                    break;
                case 'js':
                    return $this->javascript_escape($string);
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
    * This function translates strings to their according language
    * 
    * @param string $string The string to translate
    * @param mixed $escapemode Different uses require the string to be escaped accordinlgy. Possible values are 'html'(default),'js' and 'unescaped'
    * @return string Translated string
    */
    function gT($string, $escapemode = 'html')
    {
        if ($this->gettextclass)
        {
            $basestring=str_replace('&lsquo;','\'',$this->gettextclass->translate($string));
            
            switch ($escapemode)
            {
                case 'html':
                    return $this->html_escape($basestring);
                    break;
                case 'js':
                    return $this->javascript_escape($basestring);
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
                    return $this->html_escape($string);
                    break;
                case 'js':
                    return $this->javascript_escape($string);
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
    
    function html_escape($str) {
    // escape newline characters, too, in case we put a value from
    // a TEXTAREA  into an <input type="hidden"> value attribute.
    return str_replace(array("\x0A","\x0D"),array("&#10;","&#13;"),
    htmlspecialchars( $str, ENT_QUOTES ));
    }

    // make a string safe to include in a JavaScript String parameter.
    function javascript_escape($str, $strip_tags=false, $htmldecode=false) {
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
