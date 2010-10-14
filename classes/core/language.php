<?php
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
 * $Id$
 *


 Wrapper to use phpgettext as a class and omit having an english translation
 USAGE:
 require_once($rootdir.'classes/core/language.php');
 $locale = new limesurvey_lang('en'); // Char code
 print $locale->getTranslation("Hello World!");
 */

if (!isset($rootdir) || isset($_REQUEST['rootdir'])) {die("Cannot run this script directly");}

require_once($rootdir.'/classes/php-gettext/gettext.php');
require_once($rootdir.'/classes/php-gettext/streams.php');

class limesurvey_lang {

    var $gettextclass;
    var $langcode;

    function limesurvey_lang($langcode){
        global $rootdir;
        $langcode=sanitize_languagecode($langcode);
        $streamer = new FileReader($rootdir.'/locale/'.$langcode.'/LC_MESSAGES/'.$langcode.'.mo');
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
                    return html_escape($basestring);
                    break;
                case 'js':
                    return javascript_escape($basestring);
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
                    return html_escape($string);
                    break;
                case 'js':
                    return javascript_escape($string);
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
                    return html_escape($basestring);
                    break;
                case 'js':
                    return javascript_escape($basestring);
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
                    return html_escape($string);
                    break;
                case 'js':
                    return javascript_escape($string);
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

}

?>
