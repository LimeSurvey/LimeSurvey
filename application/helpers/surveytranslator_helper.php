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

//@todo this should be a table with values from getLanguageData, then using ONLY the model to get values/arrays from it

    /*
    * Internationalization and Localization utilities
    *
    * @package LimeSurvey
    * @subpackage Helpers
    */


    /**
     * Returns all available dateformats in a structured aray
     * If $iDateFormat is given only the particual dateformat will be returned
     *
     * @param $iDateFormat integer
     * @param $sLanguageCode string
     * @returns array
     *
     */
function getDateFormatData($iDateFormat = 0, $sLanguageCode = 'en')
{
    // Bootstrap DateTimePicker uses capital letters, but
    // we still need small jsdate letters for dropdown client side.
    $aDateFormats = array(
        1 => array('phpdate' => 'd.m.Y', 'jsdate' => 'DD.MM.YYYY', 'dateformat' => gT('dd.mm.yyyy')),
        2 => array('phpdate' => 'd-m-Y', 'jsdate' => 'DD-MM-YYYY', 'dateformat' => gT('dd-mm-yyyy')),
        3 => array('phpdate' => 'Y.m.d', 'jsdate' => 'YYYY.MM.DD', 'dateformat' => gT('yyyy.mm.dd')),
        4 => array('phpdate' => 'j.n.Y', 'jsdate' => 'D.M.YYYY', 'dateformat' => gT('d.m.yyyy')),
        5 => array('phpdate' => 'd/m/Y', 'jsdate' => 'DD/MM/YYYY', 'dateformat' => gT('dd/mm/yyyy')),
        6 => array('phpdate' => 'Y-m-d', 'jsdate' => 'YYYY-MM-DD', 'dateformat' => gT('yyyy-mm-dd')),
        7 => array('phpdate' => 'Y/m/d', 'jsdate' => 'YYYY/MM/DD', 'dateformat' => gT('yyyy/mm/dd')),
        8 => array('phpdate' => 'j/n/Y', 'jsdate' => 'D/M/YYYY', 'dateformat' => gT('d/m/yyyy')),
        9 => array('phpdate' => 'm-d-Y', 'jsdate' => 'MM-DD-YYYY', 'dateformat' => gT('mm-dd-yyyy')),
        10 => array('phpdate' => 'm.d.Y', 'jsdate' => 'MM.DD.YYYY', 'dateformat' => gT('mm.dd.yyyy')),
        11 => array('phpdate' => 'm/d/Y', 'jsdate' => 'MM/DD/YYYY', 'dateformat' => gT('mm/dd/yyyy')),
        12 => array('phpdate' => 'j-n-Y', 'jsdate' => 'D-M-YYYY', 'dateformat' => gT('d-m-yyyy'))
    );

    if ($iDateFormat > 12 || $iDateFormat < 0) {
        $iDateFormat = 6;
    }
    if ($iDateFormat > 0) {
        return $aDateFormats[$iDateFormat];
    } else {
        return $aDateFormats;
    }
}

/**
 * @param boolean $bOrderByNative
 * @param string $sLanguageCode
 * @return mixed
 */
function getLanguageData($bOrderByNative = false, $sLanguageCode = 'en')
{

    static $result = array();

    if (isset($result[$sLanguageCode][$bOrderByNative])) {
        return $result[$sLanguageCode][$bOrderByNative];
    }

    // Afrikaans
    $supportedLanguages['af']['description'] = gT('Afrikaans');
    $supportedLanguages['af']['nativedescription'] = 'Afrikaans';
    $supportedLanguages['af']['rtl'] = false;
    $supportedLanguages['af']['dateformat'] = 1;
    $supportedLanguages['af']['radixpoint'] = 1;
    $supportedLanguages['af']['momentjs'] = 'af';

    // Albanian
    $supportedLanguages['sq']['description'] = gT('Albanian');
    $supportedLanguages['sq']['nativedescription'] = 'Shqipe';
    $supportedLanguages['sq']['rtl'] = false;
    $supportedLanguages['sq']['dateformat'] = 1;
    $supportedLanguages['sq']['radixpoint'] = 1;
    $supportedLanguages['sq']['momentjs'] = 'sq';

    // Amharic
    $supportedLanguages['am']['description'] = gT('Amharic');
    $supportedLanguages['am']['nativedescription'] = '&#4768;&#4635;&#4653;&#4763;';
    $supportedLanguages['am']['rtl'] = false;
    $supportedLanguages['am']['dateformat'] = 2;
    $supportedLanguages['am']['radixpoint'] = 1;
    $supportedLanguages['am']['momentjs'] = null;

    // Arabic
    $supportedLanguages['ar']['description'] = gT('Arabic');
    $supportedLanguages['ar']['nativedescription'] = '&#1593;&#1614;&#1585;&#1614;&#1576;&#1610;&#1618;';
    $supportedLanguages['ar']['rtl'] = true;
    $supportedLanguages['ar']['dateformat'] = 2;
    $supportedLanguages['ar']['radixpoint'] = 0;
    $supportedLanguages['ar']['momentjs'] = 'ar';

    // Armenian
    $supportedLanguages['hy']['description'] = gT('Armenian');
    $supportedLanguages['hy']['nativedescription'] = '&#1392;&#1377;&#1397;&#1381;&#1408;&#1381;&#1398;';
    $supportedLanguages['hy']['rtl'] = false;
    $supportedLanguages['hy']['dateformat'] = 1;
    $supportedLanguages['hy']['radixpoint'] = 1;
    $supportedLanguages['hy']['momentjs'] = 'hy-am';

    // Azerbaijani
    $supportedLanguages['az']['description'] = gT('Azerbaijani');
    $supportedLanguages['az']['nativedescription'] = 'Azerbaycanca';
    $supportedLanguages['az']['rtl'] = false;
    $supportedLanguages['az']['dateformat'] = 5;
    $supportedLanguages['az']['radixpoint'] = 1;
    $supportedLanguages['az']['momentjs'] = 'az';

    // Basque
    $supportedLanguages['eu']['description'] = gT('Basque');
    $supportedLanguages['eu']['nativedescription'] = 'Euskara';
    $supportedLanguages['eu']['rtl'] = false;
    $supportedLanguages['eu']['dateformat'] = 3;
    $supportedLanguages['eu']['radixpoint'] = 1;
    $supportedLanguages['eu']['momentjs'] = 'eu';

    // Belarusian
    $supportedLanguages['be']['description'] = gT('Belarusian');
    $supportedLanguages['be']['nativedescription'] = '&#1041;&#1077;&#1083;&#1072;&#1088;&#1091;&#1089;&#1082;&#1110;';
    $supportedLanguages['be']['rtl'] = false;
    $supportedLanguages['be']['dateformat'] = 1;
    $supportedLanguages['be']['radixpoint'] = 1;
    $supportedLanguages['be']['momentjs'] = 'be';

    // Bengali
    $supportedLanguages['bn']['description'] = gT('Bengali');
    $supportedLanguages['bn']['nativedescription'] = '&#2476;&#2494;&#2434;&#2482;&#2494;';
    $supportedLanguages['bn']['rtl'] = false;
    $supportedLanguages['bn']['dateformat'] = 2;
    $supportedLanguages['bn']['radixpoint'] = 0;
    $supportedLanguages['bn']['momentjs'] = 'bn';

    // Bosnian
    $supportedLanguages['bs']['description'] = gT('Bosnian');
    $supportedLanguages['bs']['nativedescription'] = 'Bosanski';
    $supportedLanguages['bs']['rtl'] = false;
    $supportedLanguages['bs']['dateformat'] = 4;
    $supportedLanguages['bs']['radixpoint'] = 0;
    $supportedLanguages['bs']['momentjs'] = 'bs';

    // Bulgarian
    $supportedLanguages['bg']['description'] = gT('Bulgarian');
    $supportedLanguages['bg']['nativedescription'] = '&#x0411;&#x044a;&#x043b;&#x0433;&#x0430;&#x0440;&#x0441;&#x043a;&#x0438;';
    $supportedLanguages['bg']['rtl'] = false;
    $supportedLanguages['bg']['dateformat'] = 1;
    $supportedLanguages['bg']['radixpoint'] = 0;
    $supportedLanguages['bg']['momentjs'] = 'bg';

    // Catalan
    $supportedLanguages['ca-valencia']['description'] = gT('Valencian');
    $supportedLanguages['ca-valencia']['nativedescription'] = 'Valenci&#224;';
    $supportedLanguages['ca-valencia']['rtl'] = false;
    $supportedLanguages['ca-valencia']['dateformat'] = 1;
    $supportedLanguages['ca-valencia']['radixpoint'] = 1;
    $supportedLanguages['ca-valencia']['cldr'] = 'ca';
    $supportedLanguages['ca-valencia']['momentjs'] = 'ca';

    // Catalan
    $supportedLanguages['ca']['description'] = gT('Catalan');
    $supportedLanguages['ca']['nativedescription'] = 'Catal&#224;';
    $supportedLanguages['ca']['rtl'] = false;
    $supportedLanguages['ca']['dateformat'] = 1;
    $supportedLanguages['ca']['radixpoint'] = 1;
    $supportedLanguages['ca']['momentjs'] = 'ca';

    // Cebuano
    $supportedLanguages['ceb']['description'] = gT('Cebuano');
    $supportedLanguages['ceb']['nativedescription'] = 'Cebuano';
    $supportedLanguages['ceb']['rtl'] = false;
    $supportedLanguages['ceb']['dateformat'] = 1;
    $supportedLanguages['ceb']['radixpoint'] = 1;

    // Chichewa
    $supportedLanguages['ny']['description'] = gT('Chichewa');
    $supportedLanguages['ny']['nativedescription'] = 'Chichewa';
    $supportedLanguages['ny']['rtl'] = false;
    $supportedLanguages['ny']['dateformat'] = 5;
    $supportedLanguages['ny']['radixpoint'] = 0;

    // Chinese (Simplified)
    $supportedLanguages['zh-Hans']['description'] = gT('Chinese (Simplified)');
    $supportedLanguages['zh-Hans']['nativedescription'] = '&#31616;&#20307;&#20013;&#25991;';
    $supportedLanguages['zh-Hans']['rtl'] = false;
    $supportedLanguages['zh-Hans']['dateformat'] = 6;
    $supportedLanguages['zh-Hans']['radixpoint'] = 0;
    $supportedLanguages['zh-Hans']['momentjs'] = 'zh-cn';

    // Creole (Haiti)
    $supportedLanguages['hat']['description'] = gT('Creole (Haitian)');
    $supportedLanguages['hat']['nativedescription'] = 'Kreyòl ayisyen';
    $supportedLanguages['hat']['rtl'] = false;
    $supportedLanguages['hat']['dateformat'] = 6;
    $supportedLanguages['hat']['radixpoint'] = 1;
    $supportedLanguages['hat']['cldr'] = 'fr_ht';

    // Chinese (Traditional - Hong Kong)
    $supportedLanguages['zh-Hant-HK']['description'] = gT('Chinese (Traditional - Hong Kong)');
    $supportedLanguages['zh-Hant-HK']['nativedescription'] = '&#32321;&#39636;&#20013;&#25991;&#35486;&#31995;';
    $supportedLanguages['zh-Hant-HK']['rtl'] = false;
    $supportedLanguages['zh-Hant-HK']['dateformat'] = 6;
    $supportedLanguages['zh-Hant-HK']['radixpoint'] = 0;
    $supportedLanguages['zh-Hant-HK']['momentjs'] = 'zh-hk';

    // Chinese (Traditional - Taiwan)
    $supportedLanguages['zh-Hant-TW']['description'] = gT('Chinese (Traditional - Taiwan)');
    $supportedLanguages['zh-Hant-TW']['nativedescription'] = '&#32321;&#39636;&#20013;&#25991;&#65288;&#21488;&#28771;&#65289;';
    $supportedLanguages['zh-Hant-TW']['rtl'] = false;
    $supportedLanguages['zh-Hant-TW']['dateformat'] = 6;
    $supportedLanguages['zh-Hant-TW']['radixpoint'] = 0;
    $supportedLanguages['zh-Hant-TW']['momentjs'] = 'zh-tw';

    // Croatian
    $supportedLanguages['hr']['description'] = gT('Croatian');
    $supportedLanguages['hr']['nativedescription'] = 'Hrvatski';
    $supportedLanguages['hr']['rtl'] = false;
    $supportedLanguages['hr']['dateformat'] = 4;
    $supportedLanguages['hr']['radixpoint'] = 1;
    $supportedLanguages['hr']['momentjs'] = 'hr';

    // Czech
    $supportedLanguages['cs']['description'] = gT('Czech');
    $supportedLanguages['cs']['nativedescription'] = '&#x010c;esky';
    $supportedLanguages['cs']['rtl'] = false;
    $supportedLanguages['cs']['dateformat'] = 4;
    $supportedLanguages['cs']['radixpoint'] = 1;
    $supportedLanguages['cs']['momentjs'] = 'cs';

    // Czech informal
    $supportedLanguages['cs-informal']['description'] = gT('Czech (informal)');
    $supportedLanguages['cs-informal']['nativedescription'] = '&#x010c;esky neform&aacute;ln&iacute;';
    $supportedLanguages['cs-informal']['rtl'] = false;
    $supportedLanguages['cs-informal']['dateformat'] = 4;
    $supportedLanguages['cs-informal']['radixpoint'] = 1;
    $supportedLanguages['cs-informal']['cldr'] = 'cs';
    $supportedLanguages['cs-informal']['momentjs'] = 'cs';

    // Danish
    $supportedLanguages['da']['description'] = gT('Danish');
    $supportedLanguages['da']['nativedescription'] = 'Dansk';
    $supportedLanguages['da']['rtl'] = false;
    $supportedLanguages['da']['dateformat'] = 2;
    $supportedLanguages['da']['radixpoint'] = 1;
    $supportedLanguages['da']['momentjs'] = 'da';

    // Dari
    $supportedLanguages['prs']['description'] = gT('Dari');
    $supportedLanguages['prs']['nativedescription'] = '&#1583;&#1585;&#1740;';
    $supportedLanguages['prs']['rtl'] = true;
    $supportedLanguages['prs']['dateformat'] = 6;
    $supportedLanguages['prs']['radixpoint'] = 0;
    $supportedLanguages['prs']['cldr'] = 'fa_af';
    $supportedLanguages['prs']['momentjs'] = null;

    // Dutch
    $supportedLanguages['nl']['description'] = gT('Dutch');
    $supportedLanguages['nl']['nativedescription'] = 'Nederlands';
    $supportedLanguages['nl']['rtl'] = false;
    $supportedLanguages['nl']['dateformat'] = 2;
    $supportedLanguages['nl']['radixpoint'] = 1;
    $supportedLanguages['nl']['momentjs'] = 'nl';

    // Dutch
    $supportedLanguages['nl-informal']['description'] = gT('Dutch (informal)');
    $supportedLanguages['nl-informal']['nativedescription'] = 'Nederlands (informeel)';
    $supportedLanguages['nl-informal']['rtl'] = false;
    $supportedLanguages['nl-informal']['dateformat'] = 2;
    $supportedLanguages['nl-informal']['radixpoint'] = 1;
    $supportedLanguages['nl-informal']['cldr'] = 'nl';
    $supportedLanguages['nl-informal']['momentjs'] = 'nl';

    // English
    $supportedLanguages['en']['description'] = gT('English');
    $supportedLanguages['en']['nativedescription'] = 'English';
    $supportedLanguages['en']['rtl'] = false;
    $supportedLanguages['en']['dateformat'] = 9;
    $supportedLanguages['en']['radixpoint'] = 0;
    $supportedLanguages['en']['momentjs'] = 'en';

    // Estonian
    $supportedLanguages['et']['description'] = gT('Estonian');
    $supportedLanguages['et']['nativedescription'] = 'Eesti';
    $supportedLanguages['et']['rtl'] = false;
    $supportedLanguages['et']['dateformat'] = 4;
    $supportedLanguages['et']['radixpoint'] = 1;
    $supportedLanguages['et']['momentjs'] = 'et';

    // Filipino - Tagalog
    $supportedLanguages['fil']['description'] = gT('Filipino');
    $supportedLanguages['fil']['nativedescription'] = 'Wikang Filipino';
    $supportedLanguages['fil']['rtl'] = false;
    $supportedLanguages['fil']['dateformat'] = 1;
    $supportedLanguages['fil']['radixpoint'] = 1;
    $supportedLanguages['fil']['momentjs'] = 'fil';

    // Finnish
    $supportedLanguages['fi']['description'] = gT('Finnish');
    $supportedLanguages['fi']['nativedescription'] = 'Suomi';
    $supportedLanguages['fi']['rtl'] = false;
    $supportedLanguages['fi']['dateformat'] = 4;
    $supportedLanguages['fi']['radixpoint'] = 1;
    $supportedLanguages['fi']['momentjs'] = 'fi';

    // French
    $supportedLanguages['fr']['description'] = gT('French');
    $supportedLanguages['fr']['nativedescription'] = 'Fran&#231;ais';
    $supportedLanguages['fr']['rtl'] = false;
    $supportedLanguages['fr']['dateformat'] = 5;
    $supportedLanguages['fr']['radixpoint'] = 1;
    $supportedLanguages['fr']['momentjs'] = 'fr';

    // Fula
    $supportedLanguages['ful']['description'] = gT('Fula');
    $supportedLanguages['ful']['nativedescription'] = 'Fulfulde';
    $supportedLanguages['ful']['rtl'] = false;
    $supportedLanguages['ful']['dateformat'] = 5;
    $supportedLanguages['ful']['radixpoint'] = 1;
    $supportedLanguages['ful']['cldr'] = 'ff';
    $supportedLanguages['ful']['momentjs'] = null;

    // Galician
    $supportedLanguages['gl']['description'] = gT('Galician');
    $supportedLanguages['gl']['nativedescription'] = 'Galego';
    $supportedLanguages['gl']['rtl'] = false;
    $supportedLanguages['gl']['dateformat'] = 5;
    $supportedLanguages['gl']['radixpoint'] = 1;
    $supportedLanguages['gl']['momentjs'] = 'gl';

    // Georgian
    $supportedLanguages['ka']['description'] = gT('Georgian');
    $supportedLanguages['ka']['nativedescription'] = '&#4325;&#4304;&#4320;&#4311;&#4323;&#4314;&#4312; &#4308;&#4316;&#4304;';
    $supportedLanguages['ka']['rtl'] = false;
    $supportedLanguages['ka']['dateformat'] = 1;
    $supportedLanguages['ka']['radixpoint'] = 1;
    $supportedLanguages['ka']['momentjs'] = 'ka';

    // German
    $supportedLanguages['de']['description'] = gT('German');
    $supportedLanguages['de']['nativedescription'] = 'Deutsch';
    $supportedLanguages['de']['rtl'] = false;
    $supportedLanguages['de']['dateformat'] = 1;
    $supportedLanguages['de']['radixpoint'] = 1;
    $supportedLanguages['de']['momentjs'] = 'de';

    // German easy
    $supportedLanguages['de-easy']['description'] = gT('German (easy)');
    $supportedLanguages['de-easy']['nativedescription'] = 'Deutsch - Leichte Sprache';
    $supportedLanguages['de-easy']['rtl'] = false;
    $supportedLanguages['de-easy']['dateformat'] = 1;
    $supportedLanguages['de-easy']['radixpoint'] = 1;
    $supportedLanguages['de-easy']['cldr'] = 'de';
    $supportedLanguages['de-easy']['momentjs'] = 'de';

    // German informal
    $supportedLanguages['de-informal']['description'] = gT('German (informal)');
    $supportedLanguages['de-informal']['nativedescription'] = 'Deutsch (Du)';
    $supportedLanguages['de-informal']['rtl'] = false;
    $supportedLanguages['de-informal']['dateformat'] = 1;
    $supportedLanguages['de-informal']['radixpoint'] = 1;
    $supportedLanguages['de-informal']['cldr'] = 'de';
    $supportedLanguages['de-informal']['momentjs'] = 'de';

    // Gujarati
    $supportedLanguages['gu']['description'] = gT('Gujarati');
    $supportedLanguages['gu']['nativedescription'] = '&#2711;&#2753;&#2716;&#2736;&#2750;&#2724;&#2752;';
    $supportedLanguages['gu']['rtl'] = false;
    $supportedLanguages['gu']['dateformat'] = 2;
    $supportedLanguages['gu']['radixpoint'] = 0;
    $supportedLanguages['gu']['momentjs'] = 'gu';

    // Greek
    $supportedLanguages['el']['description'] = gT('Greek');
    $supportedLanguages['el']['nativedescription'] = '&#917;&#955;&#955;&#951;&#957;&#953;&#954;&#940;';
    $supportedLanguages['el']['rtl'] = false;
    $supportedLanguages['el']['dateformat'] = 8;
    $supportedLanguages['el']['radixpoint'] = 1;
    $supportedLanguages['el']['momentjs'] = 'el';

    // Greenlandic
    $supportedLanguages['kal']['description'] = gT('Greenlandic');
    $supportedLanguages['kal']['nativedescription'] = 'Kalaallisut';
    $supportedLanguages['kal']['rtl'] = false;
    $supportedLanguages['kal']['dateformat'] = 2;
    $supportedLanguages['kal']['radixpoint'] = 1;
    $supportedLanguages['kal']['momentjs'] = null;

    // Hazaragi
    $supportedLanguages['haz']['description'] = gT('Hazaragi');
    $supportedLanguages['haz']['nativedescription'] = '&#x622;&#x632;&#x631;&#x6AF;&#x6CC;';
    $supportedLanguages['haz']['rtl'] = true;
    $supportedLanguages['haz']['dateformat'] = 2;
    $supportedLanguages['haz']['radixpoint'] = 0;
    $supportedLanguages['haz']['momentjs'] = 'fa';
    $supportedLanguages['haz']['cldr'] = 'fa';

    // Hausa
    $supportedLanguages['ha']['description'] = gT('Hausa');
    $supportedLanguages['ha']['nativedescription'] = 'Hausa';
    $supportedLanguages['ha']['rtl'] = false;
    $supportedLanguages['ha']['dateformat'] = 1;
    $supportedLanguages['ha']['radixpoint'] = 1;
    
    // Hebrew
    $supportedLanguages['he']['description'] = gT('Hebrew');
    $supportedLanguages['he']['nativedescription'] = ' &#1506;&#1489;&#1512;&#1497;&#1514;';
    $supportedLanguages['he']['rtl'] = true;
    $supportedLanguages['he']['dateformat'] = 5;
    $supportedLanguages['he']['radixpoint'] = 0;
    $supportedLanguages['he']['momentjs'] = 'he';

    // Hindi
    $supportedLanguages['hi']['description'] = gT('Hindi');
    $supportedLanguages['hi']['nativedescription'] = '&#2361;&#2367;&#2344;&#2381;&#2342;&#2368;';
    $supportedLanguages['hi']['rtl'] = false;
    $supportedLanguages['hi']['dateformat'] = 2;
    $supportedLanguages['hi']['radixpoint'] = 0;
    $supportedLanguages['hi']['momentjs'] = 'hi';

    // Hiligaynon
    $supportedLanguages['hil']['description'] = gT('Hiligaynon');
    $supportedLanguages['hil']['nativedescription'] = 'Ilonggo';
    $supportedLanguages['hil']['rtl'] = false;
    $supportedLanguages['hil']['dateformat'] = 1;
    $supportedLanguages['hil']['radixpoint'] = 1;

    // Hungarian
    $supportedLanguages['hu']['description'] = gT('Hungarian');
    $supportedLanguages['hu']['nativedescription'] = 'Magyar';
    $supportedLanguages['hu']['rtl'] = false;
    $supportedLanguages['hu']['dateformat'] = 6;
    $supportedLanguages['hu']['radixpoint'] = 1;
    $supportedLanguages['hu']['momentjs'] = 'hu';

    // Hungarian informal
    $supportedLanguages['hu-informal']['description'] = gT('Hungarian (informal)');
    $supportedLanguages['hu-informal']['nativedescription'] = 'Magyar (tegez&#337;d&#337;)';
    $supportedLanguages['hu-informal']['rtl'] = false;
    $supportedLanguages['hu-informal']['dateformat'] = 6;
    $supportedLanguages['hu-informal']['radixpoint'] = 1;
    $supportedLanguages['hu-informal']['cldr'] = 'hu';
    $supportedLanguages['hu-informal']['momentjs'] = 'hu';

    // Icelandic
    $supportedLanguages['is']['description'] = gT('Icelandic');
    $supportedLanguages['is']['nativedescription'] = '&#237;slenska';
    $supportedLanguages['is']['rtl'] = false;
    $supportedLanguages['is']['dateformat'] = 1;
    $supportedLanguages['is']['radixpoint'] = 1;
    $supportedLanguages['is']['momentjs'] = 'is';

    // Indonesian
    $supportedLanguages['id']['description'] = gT('Indonesian');
    $supportedLanguages['id']['nativedescription'] = 'Bahasa Indonesia';
    $supportedLanguages['id']['rtl'] = false;
    $supportedLanguages['id']['dateformat'] = 5;
    $supportedLanguages['id']['radixpoint'] = 1;
    $supportedLanguages['id']['momentjs'] = 'id';

    // Inuktitut
    $supportedLanguages['ike']['description'] = gT('Inuktitut');
    $supportedLanguages['ike']['nativedescription'] = '&#x1403;&#x14C4;&#x1483;&#x144E;&#x1450;&#x1466;';
    $supportedLanguages['ike']['rtl'] = false;
    $supportedLanguages['ike']['dateformat'] = 5;
    $supportedLanguages['ike']['radixpoint'] = 1;
    $supportedLanguages['ike']['momentjs'] = null;

    // Irish
    $supportedLanguages['ie']['description'] = gT('Irish');
    $supportedLanguages['ie']['nativedescription'] = 'Gaeilge';
    $supportedLanguages['ie']['rtl'] = false;
    $supportedLanguages['ie']['dateformat'] = 2;
    $supportedLanguages['ie']['radixpoint'] = 0;
    $supportedLanguages['ie']['cldr'] = 'ga';
    $supportedLanguages['ie']['momentjs'] = 'ga';

    // Hiligaynon
    $supportedLanguages['ilo']['description'] = gT('Ilocano');
    $supportedLanguages['ilo']['nativedescription'] = 'Ilokano';
    $supportedLanguages['ilo']['rtl'] = false;
    $supportedLanguages['ilo']['dateformat'] = 1;
    $supportedLanguages['ilo']['radixpoint'] = 1;

    // Italian
    $supportedLanguages['it']['description'] = gT('Italian');
    $supportedLanguages['it']['nativedescription'] = 'Italiano';
    $supportedLanguages['it']['rtl'] = false;
    $supportedLanguages['it']['dateformat'] = 5;
    $supportedLanguages['it']['radixpoint'] = 1;
    $supportedLanguages['it']['momentjs'] = 'it';

    // Italian informal
    $supportedLanguages['it-informal']['description'] = gT('Italian (informal)');
    $supportedLanguages['it-informal']['nativedescription'] = 'Italiano (informale)';
    $supportedLanguages['it-informal']['rtl'] = false;
    $supportedLanguages['it-informal']['dateformat'] = 5;
    $supportedLanguages['it-informal']['radixpoint'] = 1;
    $supportedLanguages['it-informal']['cldr'] = 'it';
    $supportedLanguages['it-informal']['momentjs'] = 'it';

    // Japanese
    $supportedLanguages['ja']['description'] = gT('Japanese');
    $supportedLanguages['ja']['nativedescription'] = '&#x65e5;&#x672c;&#x8a9e;';
    $supportedLanguages['ja']['rtl'] = false;
    $supportedLanguages['ja']['dateformat'] = 6;
    $supportedLanguages['ja']['radixpoint'] = 0;
    $supportedLanguages['ja']['momentjs'] = 'ja';

    // Kannada
    $supportedLanguages['kn']['description'] = gT('Kannada');
    $supportedLanguages['kn']['nativedescription'] = '&#xC95;&#xCA8;&#xCCD;&#xCA8;&#xCA1;';
    $supportedLanguages['kn']['rtl'] = false;
    $supportedLanguages['kn']['dateformat'] = 2;
    $supportedLanguages['kn']['radixpoint'] = 0;
    $supportedLanguages['kn']['momentjs'] = 'kn';

    // Kazakh
    $supportedLanguages['kk']['description'] = gT('Kazakh');
    $supportedLanguages['kk']['nativedescription'] = 'Qazaq&#351;a';
    $supportedLanguages['kk']['rtl'] = false;
    $supportedLanguages['kk']['dateformat'] = 1;
    $supportedLanguages['kk']['radixpoint'] = 1;
    $supportedLanguages['kk']['momentjs'] = 'kk';

    // Kinyarwanda
    $supportedLanguages['rw']['description'] = gT('Kinyarwanda');
    $supportedLanguages['rw']['nativedescription'] = 'Kinyarwanda';
    $supportedLanguages['rw']['rtl'] = false;
    $supportedLanguages['rw']['dateformat'] = 5;
    $supportedLanguages['rw']['radixpoint'] = 1;
    $supportedLanguages['rw']['momentjs'] = null;

    // Korean
    $supportedLanguages['ko']['description'] = gT('Korean');
    $supportedLanguages['ko']['nativedescription'] = '&#54620;&#44397;&#50612;';
    $supportedLanguages['ko']['rtl'] = false;
    $supportedLanguages['ko']['dateformat'] = 7;
    $supportedLanguages['ko']['radixpoint'] = 0;
    $supportedLanguages['ko']['momentjs'] = 'ko';

    // Khmer
    $supportedLanguages['km']['description'] = gT('Khmer');
    $supportedLanguages['km']['nativedescription'] = 'Khmer';
    $supportedLanguages['km']['rtl'] = false;
    $supportedLanguages['km']['dateformat'] = 5;
    $supportedLanguages['km']['radixpoint'] = 1;
    $supportedLanguages['km']['cldr'] = 'km';
    $supportedLanguages['km']['momentjs'] = null;

    // Kirundi
    $supportedLanguages['run']['description'] = gT('Kirundi');
    $supportedLanguages['run']['nativedescription'] = 'Ikirundi';
    $supportedLanguages['run']['rtl'] = false;
    $supportedLanguages['run']['dateformat'] = 1;
    $supportedLanguages['run']['radixpoint'] = 1;
    $supportedLanguages['run']['cldr'] = 'rn';
    $supportedLanguages['run']['momentjs'] = null;

    // Kurdish (Sorani)
    $supportedLanguages['ckb']['description'] = gT('Kurdish (Sorani)');
    $supportedLanguages['ckb']['nativedescription'] = '&#1705;&#1608;&#1585;&#1583;&#1740;&#1740; &#1606;&#1575;&#1608;&#1749;&#1606;&#1583;&#1740;';
    $supportedLanguages['ckb']['rtl'] = true;
    $supportedLanguages['ckb']['dateformat'] = 1;
    $supportedLanguages['ckb']['radixpoint'] = 1;
    $supportedLanguages['ckb']['cldr'] = 'ku';
    $supportedLanguages['ckb']['momentjs'] = 'ku';

    // Kurdish (Kurmanji)
    $supportedLanguages['kmr']['description'] = gT('Kurdish (Kurmanji)');
    $supportedLanguages['kmr']['nativedescription'] = 'Kurmanc&#xEE;';
    $supportedLanguages['kmr']['rtl'] = false;
    $supportedLanguages['kmr']['dateformat'] = 1;
    $supportedLanguages['kmr']['radixpoint'] = 1;
    $supportedLanguages['kmr']['cldr'] = 'ku';
    $supportedLanguages['kmr']['momentjs'] = 'ku';

    // Kyrgyz
    $supportedLanguages['ky']['description'] = gT('Kyrgyz');
    $supportedLanguages['ky']['nativedescription'] = '&#1050;&#1099;&#1088;&#1075;&#1099;&#1079;&#1095;&#1072;';
    $supportedLanguages['ky']['rtl'] = false;
    $supportedLanguages['ky']['dateformat'] = 1;
    $supportedLanguages['ky']['radixpoint'] = 1;
    $supportedLanguages['ky']['momentjs'] = 'ky';

    // Latvian
    $supportedLanguages['lv']['description'] = gT('Latvian');
    $supportedLanguages['lv']['nativedescription'] = 'Latvie&#353;u';
    $supportedLanguages['lv']['rtl'] = false;
    $supportedLanguages['lv']['dateformat'] = 6;
    $supportedLanguages['lv']['radixpoint'] = 1;
    $supportedLanguages['lv']['momentjs'] = 'lv';

    // Lithuanian
    $supportedLanguages['lt']['description'] = gT('Lithuanian');
    $supportedLanguages['lt']['nativedescription'] = 'Lietuvi&#371;';
    $supportedLanguages['lt']['rtl'] = false;
    $supportedLanguages['lt']['dateformat'] = 6;
    $supportedLanguages['lt']['radixpoint'] = 1;
    $supportedLanguages['lt']['momentjs'] = 'lt';

    // Luxembourgish
    $supportedLanguages['lb']['description'] = gT('Luxembourgish');
    $supportedLanguages['lb']['nativedescription'] = 'L&#235;tzebuergesch';
    $supportedLanguages['lb']['rtl'] = false;
    $supportedLanguages['lb']['dateformat'] = 1;
    $supportedLanguages['lb']['radixpoint'] = 1;
    $supportedLanguages['lb']['cldr'] = 'fr_lu';
    $supportedLanguages['lb']['momentjs'] = 'lb';

    // Macedonian
    $supportedLanguages['mk']['description'] = gT('Macedonian');
    $supportedLanguages['mk']['nativedescription'] = '&#1052;&#1072;&#1082;&#1077;&#1076;&#1086;&#1085;&#1089;&#1082;&#1080;';
    $supportedLanguages['mk']['rtl'] = false;
    $supportedLanguages['mk']['dateformat'] = 1;
    $supportedLanguages['mk']['radixpoint'] = 1;
    $supportedLanguages['mk']['momentjs'] = 'mk';

    // Malay
    $supportedLanguages['ms']['description'] = gT('Malay');
    $supportedLanguages['ms']['nativedescription'] = 'Bahasa Melayu';
    $supportedLanguages['ms']['rtl'] = false;
    $supportedLanguages['ms']['dateformat'] = 1;
    $supportedLanguages['ms']['radixpoint'] = 0;
    $supportedLanguages['ms']['momentjs'] = 'ms';

    // Malayalam
    $supportedLanguages['ml']['description'] =  gT('Malayalam');
    $supportedLanguages['ml']['nativedescription'] = 'Malay&#257;&#7735;a&#7745;';
    $supportedLanguages['ml']['rtl'] = false;
    $supportedLanguages['ml']['dateformat'] = 2;
    $supportedLanguages['ml']['radixpoint'] = 0;
    $supportedLanguages['ml']['momentjs'] = 'ml';

    // Maltese
    $supportedLanguages['mt']['description'] = gT('Maltese');
    $supportedLanguages['mt']['nativedescription'] = 'Malti';
    $supportedLanguages['mt']['rtl'] = false;
    $supportedLanguages['mt']['dateformat'] = 1;
    $supportedLanguages['mt']['radixpoint'] = 0;
    $supportedLanguages['mt']['momentjs'] = 'mt';

    // Marathi
    $supportedLanguages['mr']['description'] = gT('Marathi');
    $supportedLanguages['mr']['nativedescription'] = '&#2350;&#2352;&#2366;&#2336;&#2368;';
    $supportedLanguages['mr']['rtl'] = false;
    $supportedLanguages['mr']['dateformat'] = 2;
    $supportedLanguages['mr']['radixpoint'] = 0;
    $supportedLanguages['mr']['momentjs'] = 'mr';

    // Mongolian
    $supportedLanguages['mn']['description'] = gT('Mongolian');
    $supportedLanguages['mn']['nativedescription'] = '&#1052;&#1086;&#1085;&#1075;&#1086;&#1083;';
    $supportedLanguages['mn']['rtl'] = false;
    $supportedLanguages['mn']['dateformat'] = 3;
    $supportedLanguages['mn']['radixpoint'] = 0;
    $supportedLanguages['mn']['momentjs'] = 'mn';

    // Montenegrin
    $supportedLanguages['cnr']['description'] = gT('Montenegrin');
    $supportedLanguages['cnr']['nativedescription'] = 'Crnogorski';
    $supportedLanguages['cnr']['rtl'] = false;
    $supportedLanguages['cnr']['dateformat'] = 4;
    $supportedLanguages['cnr']['radixpoint'] = 1;
    $supportedLanguages['cnr']['cldr'] = 'sr_Latn_ME';
    $supportedLanguages['cnr']['momentjs'] = 'me';

    // Myanmar / Burmese
    $supportedLanguages['mya']['description'] = gT('Myanmar (Burmese)');
    $supportedLanguages['mya']['nativedescription'] = '&#4121;&#4156;&#4116;&#4154;&#4121;&#4140;&#4120;&#4140;&#4126;&#4140;';
    $supportedLanguages['mya']['rtl'] = false;
    $supportedLanguages['mya']['dateformat'] = 1;
    $supportedLanguages['mya']['radixpoint'] = 1;
    $supportedLanguages['mya']['momentjs'] = 'my';

    // Norwegian Bokmal
    $supportedLanguages['nb']['description'] = gT('Norwegian (Bokmal)');
    $supportedLanguages['nb']['nativedescription'] = 'Norsk Bokm&#229;l';
    $supportedLanguages['nb']['rtl'] = false;
    $supportedLanguages['nb']['dateformat'] = 4;
    $supportedLanguages['nb']['radixpoint'] = 1;
    $supportedLanguages['nb']['momentjs'] = 'nb';

    // Nepali
    $supportedLanguages['ne']['description'] = gT('Nepali');
    $supportedLanguages['ne']['nativedescription'] = 'Nepali';
    $supportedLanguages['ne']['rtl'] = false;
    $supportedLanguages['ne']['dateformat'] = 6;
    $supportedLanguages['ne']['radixpoint'] = 0;
    $supportedLanguages['ne']['momentjs'] = 'ne';

    // Norwegian Nynorsk
    $supportedLanguages['nn']['description'] = gT('Norwegian (Nynorsk)');
    $supportedLanguages['nn']['nativedescription'] = 'Norsk Nynorsk';
    $supportedLanguages['nn']['rtl'] = false;
    $supportedLanguages['nn']['dateformat'] = 4;
    $supportedLanguages['nn']['radixpoint'] = 1;
    $supportedLanguages['nn']['momentjs'] = 'nn';

    // Occitan
    $supportedLanguages['oc']['description'] = gT('Occitan');
    $supportedLanguages['oc']['nativedescription'] = "Lenga d'&#242;c";
    $supportedLanguages['oc']['rtl'] = false;
    $supportedLanguages['oc']['dateformat'] = 5;
    $supportedLanguages['oc']['radixpoint'] = 1;
    $supportedLanguages['oc']['momentjs'] = 'oc-lnc';

    // Odia
    $supportedLanguages['ory']['description'] = gT('Odia');
    $supportedLanguages['ory']['nativedescription'] = "&#xB13;&#xB21;&#xB3C;&#xB3F;&#xB06;";
    $supportedLanguages['ory']['rtl'] = false;
    $supportedLanguages['ory']['dateformat'] = 5;
    $supportedLanguages['ory']['radixpoint'] = 1;

    // Pashto
    $supportedLanguages['ps']['description'] = gT('Pashto');
    $supportedLanguages['ps']['nativedescription'] = '&#1662;&#1690;&#1578;&#1608;';
    $supportedLanguages['ps']['rtl'] = true;
    $supportedLanguages['ps']['dateformat'] = 6;
    $supportedLanguages['ps']['radixpoint'] = 0;
    $supportedLanguages['ps']['momentjs'] = null;

    // Persian
    $supportedLanguages['fa']['description'] = gT('Persian');
    $supportedLanguages['fa']['nativedescription'] = '&#1601;&#1575;&#1585;&#1587;&#1740;';
    $supportedLanguages['fa']['rtl'] = true;
    $supportedLanguages['fa']['dateformat'] = 6;
    $supportedLanguages['fa']['radixpoint'] = 0;
    $supportedLanguages['fa']['momentjs'] = 'fa';

    // Papiamento (Curacao and Bonaire)
    $supportedLanguages['pap-CW']['description'] = gT('Papiamento (Curaçao & Bonaire)');
    $supportedLanguages['pap-CW']['nativedescription'] = 'Papiamentu';
    $supportedLanguages['pap-CW']['rtl'] = false;
    $supportedLanguages['pap-CW']['dateformat'] = 2;
    $supportedLanguages['pap-CW']['radixpoint'] = 1;
    $supportedLanguages['pap-CW']['cldr'] = 'en'; // Fix me - Yii does not provide Papiamento support, yet
    $supportedLanguages['pap-CW']['momentjs'] = null;

    // Polish
    $supportedLanguages['pl']['description'] = gT('Polish');
    $supportedLanguages['pl']['nativedescription'] = 'Polski';
    $supportedLanguages['pl']['rtl'] = false;
    $supportedLanguages['pl']['dateformat'] = 1;
    $supportedLanguages['pl']['radixpoint'] = 1;
    $supportedLanguages['pl']['momentjs'] = 'pl';

    // Polish
    $supportedLanguages['pl-informal']['description'] = gT('Polish (Informal)');
    $supportedLanguages['pl-informal']['nativedescription'] = 'Polski (nieformalny)';
    $supportedLanguages['pl-informal']['rtl'] = false;
    $supportedLanguages['pl-informal']['dateformat'] = 1;
    $supportedLanguages['pl-informal']['radixpoint'] = 1;
    $supportedLanguages['pl-informal']['cldr'] = 'pl';
    $supportedLanguages['pl-informal']['momentjs'] = 'pl';

    // Portuguese
    $supportedLanguages['pt']['description'] = gT('Portuguese');
    $supportedLanguages['pt']['nativedescription'] = 'Portugu&#234;s';
    $supportedLanguages['pt']['rtl'] = false;
    $supportedLanguages['pt']['dateformat'] = 5;
    $supportedLanguages['pt']['radixpoint'] = 1;
    $supportedLanguages['pt']['momentjs'] = 'pt';

    // Brazilian Portuguese
    $supportedLanguages['pt-BR']['description'] = gT('Portuguese (Brazilian)');
    $supportedLanguages['pt-BR']['nativedescription'] = 'Portugu&#234;s do Brasil';
    $supportedLanguages['pt-BR']['rtl'] = false;
    $supportedLanguages['pt-BR']['dateformat'] = 5;
    $supportedLanguages['pt-BR']['radixpoint'] = 1;
    $supportedLanguages['pt-BR']['momentjs'] = 'pt-br';

    // Punjabi
    $supportedLanguages['pa']['description'] = gT('Punjabi');
    $supportedLanguages['pa']['nativedescription'] = '&#2602;&#2672;&#2588;&#2622;&#2604;&#2624;';
    $supportedLanguages['pa']['rtl'] = false;
    $supportedLanguages['pa']['dateformat'] = 2;
    $supportedLanguages['pa']['radixpoint'] = 0;
    $supportedLanguages['pa']['momentjs'] = 'pa-in';

    // Romanian
    $supportedLanguages['ro']['description'] = gT('Romanian');
    $supportedLanguages['ro']['nativedescription'] = 'Rom&#226;na';
    $supportedLanguages['ro']['rtl'] = false;
    $supportedLanguages['ro']['dateformat'] = 1;
    $supportedLanguages['ro']['radixpoint'] = 1;
    $supportedLanguages['ro']['momentjs'] = 'ro';

    // Romansh
    $supportedLanguages['roh']['description'] = gT('Romansh');
    $supportedLanguages['roh']['nativedescription'] = 'Rumantsch';
    $supportedLanguages['roh']['rtl'] = false;
    $supportedLanguages['roh']['dateformat'] = 1;
    $supportedLanguages['roh']['radixpoint'] = 1;
    $supportedLanguages['roh']['momentjs'] = null;

    // Russian
    $supportedLanguages['ru']['description'] = gT('Russian');
    $supportedLanguages['ru']['nativedescription'] = '&#1056;&#1091;&#1089;&#1089;&#1082;&#1080;&#1081;';
    $supportedLanguages['ru']['rtl'] = false;
    $supportedLanguages['ru']['dateformat'] = 1;
    $supportedLanguages['ru']['radixpoint'] = 1;
    $supportedLanguages['ru']['momentjs'] = 'ru';

    // Sami
    $supportedLanguages['smi']['description'] = gT('Sami (Northern)');
    $supportedLanguages['smi']['nativedescription'] = 'Davvisámegiella';
    $supportedLanguages['smi']['rtl'] = false;
    $supportedLanguages['smi']['dateformat'] = 4;
    $supportedLanguages['smi']['radixpoint'] = 1;
    $supportedLanguages['smi']['momentjs'] = 'sme';

    // Serbian
    $supportedLanguages['sr']['description'] = gT('Serbian (Cyrillic)');
    $supportedLanguages['sr']['nativedescription'] = '&#1057;&#1088;&#1087;&#1089;&#1082;&#1080;';
    $supportedLanguages['sr']['rtl'] = false;
    $supportedLanguages['sr']['dateformat'] = 4;
    $supportedLanguages['sr']['radixpoint'] = 1;
    $supportedLanguages['sr']['momentjs'] = 'sr-cyrl';

    // Serbian (Latin script)
    $supportedLanguages['sr-Latn']['description'] = gT('Serbian (Latin)');
    $supportedLanguages['sr-Latn']['nativedescription'] = 'Srpski';
    $supportedLanguages['sr-Latn']['rtl'] = false;
    $supportedLanguages['sr-Latn']['dateformat'] = 4;
    $supportedLanguages['sr-Latn']['radixpoint'] = 1;
    $supportedLanguages['sr-Latn']['momentjs'] = 'sr';

    // Sinhala
    $supportedLanguages['si']['description'] = gT('Sinhala');
    $supportedLanguages['si']['nativedescription'] = '&#3523;&#3538;&#3458;&#3524;&#3517;';
    $supportedLanguages['si']['rtl'] = false;
    $supportedLanguages['si']['dateformat'] = 5;
    $supportedLanguages['si']['radixpoint'] = 0;
    $supportedLanguages['si']['momentjs'] = 'si';

    // Slovak
    $supportedLanguages['sk']['description'] = gT('Slovak');
    $supportedLanguages['sk']['nativedescription'] = 'Sloven&#269;ina';
    $supportedLanguages['sk']['rtl'] = false;
    $supportedLanguages['sk']['dateformat'] = 4;
    $supportedLanguages['sk']['radixpoint'] = 1;
    $supportedLanguages['sk']['momentjs'] = 'sk';

    // Slovenian
    $supportedLanguages['sl']['description'] = gT('Slovenian');
    $supportedLanguages['sl']['nativedescription'] = 'Sloven&#353;&#269;ina';
    $supportedLanguages['sl']['rtl'] = false;
    $supportedLanguages['sl']['dateformat'] = 4;
    $supportedLanguages['sl']['radixpoint'] = 1;
    $supportedLanguages['sl']['momentjs'] = 'sl';

    // Somali
    $supportedLanguages['so']['description'] = gT('Somali');
    $supportedLanguages['so']['nativedescription'] = 'Af-Soomaali';
    $supportedLanguages['so']['rtl'] = false;
    $supportedLanguages['so']['dateformat'] = 9;
    $supportedLanguages['so']['radixpoint'] = 1;
    $supportedLanguages['so']['momentjs'] = null;

    // Spanish
    $supportedLanguages['es']['description'] = gT('Spanish');
    $supportedLanguages['es']['nativedescription'] = 'Espa&#241;ol';
    $supportedLanguages['es']['rtl'] = false;
    $supportedLanguages['es']['dateformat'] = 5;
    $supportedLanguages['es']['radixpoint'] = 1;
    $supportedLanguages['es']['momentjs'] = 'es';

    // Spanish (informal)
    $supportedLanguages['es-informal']['description'] = gT('Spanish (informal)');
    $supportedLanguages['es-informal']['nativedescription'] = 'Espa&#241;ol (informal)';
    $supportedLanguages['es-informal']['rtl'] = false;
    $supportedLanguages['es-informal']['dateformat'] = 5;
    $supportedLanguages['es-informal']['radixpoint'] = 1;
    $supportedLanguages['es-informal']['cldr'] = 'es';
    $supportedLanguages['es-informal']['momentjs'] = 'es';


    // Spanish (Argentina)
    $supportedLanguages['es-AR']['description'] = gT('Spanish (Argentina)');
    $supportedLanguages['es-AR']['nativedescription'] = 'Espa&#241;ol rioplatense';
    $supportedLanguages['es-AR']['rtl'] = false;
    $supportedLanguages['es-AR']['dateformat'] = 5;
    $supportedLanguages['es-AR']['radixpoint'] = 0;
    $supportedLanguages['es-AR']['momentjs'] = 'es';

    // Spanish (Argentina) (Informal)
    $supportedLanguages['es-AR-informal']['description'] = gT('Spanish (Argentina) (Informal)');
    $supportedLanguages['es-AR-informal']['nativedescription'] = 'Espa&#241;ol rioplatense informal';
    $supportedLanguages['es-AR-informal']['rtl'] = false;
    $supportedLanguages['es-AR-informal']['dateformat'] = 5;
    $supportedLanguages['es-AR-informal']['radixpoint'] = 0;
    $supportedLanguages['es-AR-informal']['cldr'] = 'es-AR';
    $supportedLanguages['es-AR-informal']['momentjs'] = 'es';

    // Spanish (Chile)
    $supportedLanguages['es-CL']['description'] = gT('Spanish (Chile)');
    $supportedLanguages['es-CL']['nativedescription'] = 'Espa&#241;ol chileno';
    $supportedLanguages['es-CL']['rtl'] = false;
    $supportedLanguages['es-CL']['dateformat'] = 5;
    $supportedLanguages['es-CL']['radixpoint'] = 0;
    $supportedLanguages['es-CL']['momentjs'] = 'es';

    // Spanish (Colombia)
    $supportedLanguages['es-CO']['description'] = gT('Spanish (Colombia)');
    $supportedLanguages['es-CO']['nativedescription'] = 'Espa&#241;ol colombiano';
    $supportedLanguages['es-CO']['rtl'] = false;
    $supportedLanguages['es-CO']['dateformat'] = 5;
    $supportedLanguages['es-CO']['radixpoint'] = 0;
    $supportedLanguages['es-CO']['momentjs'] = 'es';

    // Spanish (Mexico)
    $supportedLanguages['es-MX']['description'] = gT('Spanish (Mexico)');
    $supportedLanguages['es-MX']['nativedescription'] = 'Espa&#241;ol mexicano';
    $supportedLanguages['es-MX']['rtl'] = false;
    $supportedLanguages['es-MX']['dateformat'] = 5;
    $supportedLanguages['es-MX']['radixpoint'] = 0;
    $supportedLanguages['es-MX']['momentjs'] = 'es';

    // Swahili
    $supportedLanguages['swh']['description'] = gT('Swahili');
    $supportedLanguages['swh']['nativedescription'] = 'Kiswahili';
    $supportedLanguages['swh']['rtl'] = false;
    $supportedLanguages['swh']['dateformat'] = 1;
    $supportedLanguages['swh']['radixpoint'] = 1;
    $supportedLanguages['swh']['cldr'] = 'sw';
    $supportedLanguages['swh']['momentjs'] = 'sw';

    // Swedish
    $supportedLanguages['sv']['description'] = gT('Swedish');
    $supportedLanguages['sv']['nativedescription'] = 'Svenska';
    $supportedLanguages['sv']['rtl'] = false;
    $supportedLanguages['sv']['dateformat'] = 6;
    $supportedLanguages['sv']['radixpoint'] = 1;
    $supportedLanguages['sv']['momentjs'] = 'sv';

    // Tagalog
    $supportedLanguages['tl']['description'] = gT('Tagalog');
    $supportedLanguages['tl']['nativedescription'] = 'Tagalog';
    $supportedLanguages['tl']['rtl'] = false;
    $supportedLanguages['tl']['dateformat'] = 1;
    $supportedLanguages['tl']['radixpoint'] = 1;
    $supportedLanguages['tl']['momentjs'] = 'tl-ph';

    // Tajik
    $supportedLanguages['tg']['description'] = gT('Tajik');
    $supportedLanguages['tg']['nativedescription'] = '&#x422;&#x43E;&#x4B7;&#x438;&#x43A;&#x4E3;';
    $supportedLanguages['tg']['rtl'] = false;
    $supportedLanguages['tg']['dateformat'] = 6;
    $supportedLanguages['tg']['radixpoint'] = 0;
    $supportedLanguages['tg']['momentjs'] = 'tg';

    // Tamil
    $supportedLanguages['ta']['description'] = gT('Tamil');
    $supportedLanguages['ta']['nativedescription'] = '&#2980;&#2990;&#3007;&#2996;&#3021;';
    $supportedLanguages['ta']['rtl'] = false;
    $supportedLanguages['ta']['dateformat'] = 2;
    $supportedLanguages['ta']['radixpoint'] = 0;
    $supportedLanguages['ta']['momentjs'] = 'ta';

    // Telugu
    $supportedLanguages['te']['description'] = gT('Telugu');
    $supportedLanguages['te']['nativedescription'] = '&#xC24;&#xC46;&#xC32;&#xC41;&#xC17;&#xC41;';
    $supportedLanguages['te']['rtl'] = false;
    $supportedLanguages['te']['dateformat'] = 2;
    $supportedLanguages['te']['radixpoint'] = 0;
    $supportedLanguages['te']['momentjs'] = 'te';

    // Thai
    $supportedLanguages['th']['description'] = gT('Thai');
    $supportedLanguages['th']['nativedescription'] = '&#3616;&#3634;&#3625;&#3634;&#3652;&#3607;&#3618;';
    $supportedLanguages['th']['rtl'] = false;
    $supportedLanguages['th']['dateformat'] = 5;
    $supportedLanguages['th']['radixpoint'] = 0;
    $supportedLanguages['th']['momentjs'] = 'th';

    // Thai - Tigrinya
    $supportedLanguages['ti']['description'] = gT('Tigrinya');
    $supportedLanguages['ti']['nativedescription'] = '&#x1275;&#x130d;&#x122d;&#x129b;';
    $supportedLanguages['ti']['rtl'] = false;
    $supportedLanguages['ti']['dateformat'] = 9;
    $supportedLanguages['ti']['radixpoint'] = 0;
    $supportedLanguages['ti']['momentjs'] = null;

    // Turkish
    $supportedLanguages['tr']['description'] = gT('Turkish');
    $supportedLanguages['tr']['nativedescription'] = 'T&#252;rk&#231;e';
    $supportedLanguages['tr']['rtl'] = false;
    $supportedLanguages['tr']['dateformat'] = 5;
    $supportedLanguages['tr']['radixpoint'] = 1;
    $supportedLanguages['tr']['momentjs'] = 'tr';

    //Ukrainian
    $supportedLanguages['uk']['description'] = gT('Ukrainian');
    $supportedLanguages['uk']['nativedescription'] = '&#x423;&#x43A;&#x440;&#x430;&#x457;&#x43D;&#x441;&#x44C;&#x43A;&#x430;';
    $supportedLanguages['uk']['rtl'] = false;
    $supportedLanguages['uk']['dateformat'] = 1;
    $supportedLanguages['uk']['radixpoint'] = 1;
    $supportedLanguages['uk']['momentjs'] = 'uk';

    //Urdu
    $supportedLanguages['ur']['description'] = gT('Urdu');
    $supportedLanguages['ur']['nativedescription'] = '&#1575;&#1585;&#1583;&#1608;';
    $supportedLanguages['ur']['rtl'] = true;
    $supportedLanguages['ur']['dateformat'] = 2;
    $supportedLanguages['ur']['radixpoint'] = 0;
    $supportedLanguages['ur']['momentjs'] = 'ur';

    //Uzbek
    $supportedLanguages['uz']['description'] = gT('Uzbek');
    $supportedLanguages['uz']['nativedescription'] = "O'zbek";
    $supportedLanguages['uz']['rtl'] = false;
    $supportedLanguages['uz']['dateformat'] = 1;
    $supportedLanguages['uz']['radixpoint'] = 1;
    $supportedLanguages['uz']['momentjs'] = 'uz';

    //Uyghur
    $supportedLanguages['ug']['description'] = gT('Uyghur');
    $supportedLanguages['ug']['nativedescription'] = 'ئۇيغۇرچە';
    $supportedLanguages['ug']['rtl'] = true;
    $supportedLanguages['ug']['dateformat'] = 6;
    $supportedLanguages['ug']['radixpoint'] = 0;
    $supportedLanguages['ug']['momentjs'] = 'ug-cn';

    // Vietnamese
    $supportedLanguages['vi']['description'] = gT('Vietnamese');
    $supportedLanguages['vi']['nativedescription'] = 'Ti&#7871;ng Vi&#7879;t';
    $supportedLanguages['vi']['rtl'] = false;
    $supportedLanguages['vi']['dateformat'] = 5;
    $supportedLanguages['vi']['radixpoint'] = 1;
    $supportedLanguages['vi']['momentjs'] = 'vi';

    // Welsh
    $supportedLanguages['cy']['description'] = gT('Welsh');
    $supportedLanguages['cy']['nativedescription'] = 'Cymraeg';
    $supportedLanguages['cy']['rtl'] = false;
    $supportedLanguages['cy']['dateformat'] = 5;
    $supportedLanguages['cy']['radixpoint'] = 0;
    $supportedLanguages['cy']['momentjs'] = 'cy';

    // Xhosa
    $supportedLanguages['xho']['description'] = gT('Xhosa');
    $supportedLanguages['xho']['nativedescription'] = 'isiXhosa';
    $supportedLanguages['xho']['rtl'] = false;
    $supportedLanguages['xho']['dateformat'] = 5;
    $supportedLanguages['xho']['radixpoint'] = 1;
    $supportedLanguages['xho']['momentjs'] = 'null';

    // Yakut
    $supportedLanguages['sah']['description'] = gT('Yakut');
    $supportedLanguages['sah']['nativedescription'] = '&#x421;&#x430;&#x445;&#x430; &#x442;&#x44B;&#x43B;&#x430;';
    $supportedLanguages['sah']['rtl'] = false;
    $supportedLanguages['sah']['dateformat'] = 5;
    $supportedLanguages['sah']['radixpoint'] = 1;
    $supportedLanguages['sah']['momentjs'] = 'null';

    // Zulu
    $supportedLanguages['yor']['description'] = gT('Yoruba');
    $supportedLanguages['yor']['nativedescription'] = '&#xC8;d&#xE8; Yor&#xF9;b&#xE1;';
    $supportedLanguages['yor']['rtl'] = false;
    $supportedLanguages['yor']['dateformat'] = 5;
    $supportedLanguages['yor']['radixpoint'] = 1;
    $supportedLanguages['yor']['momentjs'] = null;

    // Zulu
    $supportedLanguages['zu']['description'] = gT('Zulu');
    $supportedLanguages['zu']['nativedescription'] = 'isiZulu';
    $supportedLanguages['zu']['rtl'] = false;
    $supportedLanguages['zu']['dateformat'] = 5;
    $supportedLanguages['zu']['radixpoint'] = 1;
    $supportedLanguages['zu']['momentjs'] = null;

    if ($bOrderByNative) {
        uasort($supportedLanguages, "userSortNative");
    } else {
        uasort($supportedLanguages, "userSort");
    }

    $result[$sLanguageCode][$bOrderByNative] = $supportedLanguages;

    return $supportedLanguages;
}


    /**
     *  Returns avaliable formats for Radix Points (Decimal Separators) or returns
     *  radix point info about a specific format.
     *
     *  @param int $format Format ID/Number [optional]
     *
     * @return integer|array
     */
function getRadixPointData($format = -1)
{
    $aRadixFormats = array(
    0 => array('separator' => '.', 'desc' => gT('Dot (.)')),
    1 => array('separator' => ',', 'desc' => gT('Comma (,)'))
    );

    // hack for fact that null sometimes sent to this function
    //todo then change the hack ...
    if (is_null($format)) {
        $format = 0;
    }

    if ($format >= 0) {
        return $aRadixFormats[$format];
    } else {
        return $aRadixFormats;
    }
}


    /**
     * Convert a 'dateformat' format string to a 'phpdate' format.
     *
     * @param string $sDateformat string
     * @returns string
     *
     */
function getPHPDateFromDateFormat($sDateformat)
{
    // Note that order is relevant (longer strings first)
    $aFmts = array(
        // With leading zero
        "dd"   => "d",
        "mm"   => "m",
        "yyyy" => "Y",
        "HH"   => "H",
        "MM"   => "i",
        "hh"   => "h",
        // Without leading zero
        "d"    => "j",
        "m"    => "n",
        "yy"   => "y",
        "H"    => "G",
        "M"    => "i",
        "h"    => "g",
        // AP/PM
        "A"    => "A",
        "a"    => "a",
    );

    // Extra allowed characters
    $aAllowed = array('-', '.', '/', ':', ' ');

    // Convert
    $tmp = array();
    foreach ($aAllowed as $k) {
        $tmp[$k] = true;
    }
    foreach (array_values($aFmts) as $k) {
        for ($i = 0; $i < strlen($k); $i++) {
            $tmp[$k[$i]] = true;
        }
    }
    $aAllowed = $tmp;

    $tmp = strtr($sDateformat, $aFmts);
    $sPhpdate = "";
    for ($i = 0; $i < strlen($tmp); $i++) {
        $c = $tmp[$i];
        if (isset($aAllowed[$c])) {
            $sPhpdate .= $c;
        }
    }

    return $sPhpdate;
}


    /**
     * Convert a 'dateformat' format string to a 'jsdate' format.
     * For Bootstrap, that means using capital letters, e.g.
     * MM/DD/YYYY instead of mm/dd/yyyy and mm instead of MM for minutes.
     *
     * @param $sDateformat string
     * @returns string
     *
     */
function getJSDateFromDateFormat($sDateformat)
{
    return strtr($sDateformat, "dmyM", "DMYm");
}


    /**
     * Get the date format details for a specific question.
     *
     * @param $aQidAttributes array Question attributes
     * @param $mThisSurvey mixed Array of Survey attributes or surveyid
     * @returns array
     *
     */
function getDateFormatDataForQID($aQidAttributes, $mThisSurvey, $language = '')
{
    if (isset($aQidAttributes['date_format']) && trim((string) $aQidAttributes['date_format']) != '') {
        $aDateFormatDetails = array();
        $aDateFormatDetails['dateformat'] = trim((string) $aQidAttributes['date_format']);
        $aDateFormatDetails['phpdate'] = getPHPDateFromDateFormat($aDateFormatDetails['dateformat']);
        $aDateFormatDetails['jsdate'] = getJSDateFromDateFormat($aDateFormatDetails['dateformat']);
    } else {
        if (!is_array($mThisSurvey)) {
            $mThisSurvey = array('surveyls_dateformat' => getDateFormatForSID($mThisSurvey, $language));
        }
        $aDateFormatDetails = getDateFormatData($mThisSurvey['surveyls_dateformat']);
    }
    return $aDateFormatDetails;
}


    /**
     * Get the date format for a specified survey
     *
     * @param $surveyid integer Survey id
     * @param $languagecode string Survey language code (optional)
     * @returns integer
     *
     */
function getDateFormatForSID($surveyid, $languagecode = '')
{
    if (!isset($languagecode) || $languagecode == '') {
        $languagecode = Survey::model()->findByPk($surveyid)->language;
    }
    $data = SurveyLanguageSetting::model()->getDateFormat($surveyid, $languagecode);

    if (empty($data)) {
        $dateformat = 0;
    } else {
        $dateformat = (int) $data;
    }
    return (int) $dateformat;
}


    /**
     * Check whether we can show the JS date picker with the current format
     *
     * @param $dateformatdetails array Date format details for the question
     * @param $dateformats array Available date formats
     * @returns integer
     *
     */
function canShowDatePicker($dateformatdetails, $dateformats = null)
{
    if (is_null($dateformats)) {
        $dateformats = getDateFormatData();
    }
    $showpicker = false;
    foreach ($dateformats as $format) {
        if ($format['jsdate'] == $dateformatdetails['jsdate']) {
            $showpicker = true;
            break;
        }
    }
    return $showpicker;
}

/**
 * Returns a language code from the name
 *
 * @param string $languagetosearch this is the name of the language (e.g. 'English' see array in getLanguageData())
 * @return int|string
 */
function getLanguageCodefromLanguage($languagetosearch)
{
    $detaillanguages = getLanguageData(false, Yii::app()->session['adminlang']);
    foreach ($detaillanguages as $key2 => $languagename) {
        if ($languagetosearch == $languagename['description']) {
            return $key2;
        }
    }
    // else return default en code
    return "en";
}



/**
* Returns a language name from the code
*
* @param string  $codetosearch
* @param boolean $withnative
* @param string  $sTranslationLanguage
* @return string|array
* @todo Should not give back different data types
*/
function getLanguageNameFromCode($codetosearch, $withnative = true, $sTranslationLanguage = null)
{
    if (is_null($sTranslationLanguage)) {
        $sTranslationLanguage = Yii::app()->session['adminlang'];
    }
    $detaillanguages = getLanguageData(false, $sTranslationLanguage);
    if (isset($detaillanguages[$codetosearch]['description'])) {
        if ($withnative) {
            return array($detaillanguages[$codetosearch]['description'], $detaillanguages[$codetosearch]['nativedescription']);
        } else {
            return $detaillanguages[$codetosearch]['description'];
        }
    }
// else return code
    return $codetosearch;
}


function getLanguageRTL($sLanguageCode)
{
    $aLanguageData = getLanguageData(false, $sLanguageCode);
    if (isset($aLanguageData[$sLanguageCode]) && isset($aLanguageData[$sLanguageCode]['rtl'])) {
        return $aLanguageData[$sLanguageCode]['rtl'];
    } else {
        return false;
    }
}

    /**
     * Returns the locale settings for a certain language code
     *
     * @param string $codetosearch
     * @return array Array with locale details
     *
     */
function getLanguageDetails($codetosearch)
{
    $detaillanguages = getLanguageData(false, Yii::app()->session['adminlang']);
    if (isset($detaillanguages[$codetosearch])) {
        return $detaillanguages[$codetosearch];
    } else {
        return $detaillanguages['en'];
    }
}

    /**
     * This functions translates LimeSurvey specific locale code to a matching datetimepicker locale
     *
     * @param string $sLocale LimeSurvey locale code
     */
function convertLStoDateTimePickerLocale($sLocale)
{
    $languageData = getLanguageData(false, $sLocale);
    if (empty($languageData[$sLocale]['momentjs'])) {
        return 'en';
    }
    return $languageData[$sLocale]['momentjs'];
}

function getLanguageDataRestricted($bOrderByNative = false, $sDetail = 'full')
{
    $aLanguageData = getLanguageData($bOrderByNative);

    if (trim((string) Yii::app()->getConfig('restrictToLanguages')) != '') {
        foreach (explode(' ', trim((string) Yii::app()->getConfig('restrictToLanguages'))) as $key) {
            $aResult[$key] = $aLanguageData[$key];
        }
    } else {
        $aResult = $aLanguageData;
    }
    if ($sDetail == 'short') {
        foreach ($aResult as $sKey => $aLanguageData) {
            $aNewArray[$sKey] = $aLanguageData['description'];
        }
        $aResult = $aNewArray;
    }
    return $aResult;
}


function userSort($a, $b)
{

    // smarts is all-important, so sort it first
    if ($a['description'] > $b['description']) {
        return 1;
    } else {
        return -1;
    }
}


function userSortNative($a, $b)
{

    // smarts is all-important, so sort it first
    if ($a['nativedescription'] > $b['nativedescription']) {
        return 1;
    } else {
        return -1;
    }
}


    /**
     * This function  support the ability NOT to reverse numbers (for example when you output
     * a phrase as a parameter for a SWF file that can't handle RTL languages itself, but
     * obviously any numbers should remain the same as in the original phrase).
     * Note that it can be used just as well for UTF-8 usages if you want the numbers to remain intact
     *
     * @param string $str
     * @param boolean $reverse_numbers
     * @return string
     */
function UTF8Strrev($str, $reverse_numbers = false)
{
    preg_match_all('/./us', $str, $ar);
    if ($reverse_numbers) {
        return join('', array_reverse($ar[0]));
    } else {
        $temp = array();
        foreach ($ar[0] as $value) {
            if (is_numeric($value) && !empty($temp[0]) && is_numeric($temp[0])) {
                foreach ($temp as $key => $value2) {
                    if (is_numeric($value2)) {
                        $pos = ($key + 1);
                    } else {
                        break;
                    }
                }
                $temp2 = array_splice($temp, $pos);
                $temp = array_merge($temp, array($value), $temp2);
            } else {
                array_unshift($temp, $value);
            }
        }
        return implode('', $temp);
    }
}
