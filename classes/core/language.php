<?php
/* Wrapper to use phpgettext as a class and omit having an english translation
USAGE:
require_once(dirname(__FILE__).'classes/core/language.php');
$locale = new phpsurveyor_lang('en'); // Char code
print $locale->getTranslation("Hello World!");
*/

require_once(dirname(__FILE__).'/../php-gettext/gettext.php');
require_once(dirname(__FILE__).'/../php-gettext/streams.php');

class phpsurveyor_lang {

    var $gettextclass;
   
    function phpsurveyor_lang($langcode){
        if ( $langcode != "en" ) {
            $streamer = new FileReader(dirname(__FILE__).'/locale/'.$langcode.'/LC_MESSAGES/'.$langcode.'.mo');
            $this->gettextclass = new gettext_reader($streamer);
        } else {
            $this->gettextclass = false;
        }
    }
   
    function getTranslation($string)
    {
        if ($this->gettextclass)
        {
            return $this->gettextclass->translate($string);
        } else {
            return $string;
        }
    }

}

?>