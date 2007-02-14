<?php
/* Wrapper to use phpgettext as a class and omit having an english translation
USAGE:
require_once(dirname(__FILE__).'classes/core/language.php');
$locale = new phpsurveyor_lang('en'); // Char code
print $locale->getTranslation("Hello World!");
*/


require_once($rootdir.'/classes/php-gettext/gettext.php');
require_once($rootdir.'/classes/php-gettext/streams.php');

class phpsurveyor_lang {

    var $gettextclass;
    
    function phpsurveyor_lang($langcode){
        if ( $langcode != "en" ) {
        	global $rootdir;
            $streamer = new FileReader($rootdir.'/locale/'.$langcode.'/LC_MESSAGES/'.$langcode.'.mo');
            $this->gettextclass = new gettext_reader($streamer);
        } else {
            $this->gettextclass = false;
        }
    }
   
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
