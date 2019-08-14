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
        1=> array('phpdate' => 'd.m.Y', 'jsdate' => 'DD.MM.YYYY', 'jsdate_original' => 'dd.mm.yyyy', 'dateformat' => gT('dd.mm.yyyy')),
        2=> array('phpdate' => 'd-m-Y', 'jsdate' => 'DD-MM-YYYY', 'jsdate_original' => 'dd-mm-yyyy', 'dateformat' => gT('dd-mm-yyyy')),
        3=> array('phpdate' => 'Y.m.d', 'jsdate' => 'YYYY.MM.DD', 'jsdate_original' => 'yyyy.mm.dd', 'dateformat' => gT('yyyy.mm.dd')),
        4=> array('phpdate' => 'j.n.Y', 'jsdate' => 'D.M.YYYY', 'jsdate_original' => 'd.m.yyyy', 'dateformat' => gT('d.m.yyyy')),
        5=> array('phpdate' => 'd/m/Y', 'jsdate' => 'DD/MM/YYYY', 'jsdate_original' => 'dd/mm/yyyy', 'dateformat' => gT('dd/mm/yyyy')),
        6=> array('phpdate' => 'Y-m-d', 'jsdate' => 'YYYY-MM-DD', 'jsdate_original' => 'yyyy-mm-dd', 'dateformat' => gT('yyyy-mm-dd')),
        7=> array('phpdate' => 'Y/m/d', 'jsdate' => 'YYYY/MM/DD', 'jsdate_original' => 'yyyy/mm/dd', 'dateformat' => gT('yyyy/mm/dd')),
        8=> array('phpdate' => 'j/n/Y', 'jsdate' => 'D/M/YYYY', 'jsdate_original' => 'd/m/yyyy', 'dateformat' => gT('d/m/yyyy')),
        9=> array('phpdate' => 'm-d-Y', 'jsdate' => 'MM-DD-YYYY', 'jsdate_original' => 'mm-dd-yyyy', 'dateformat' => gT('mm-dd-yyyy')),
        10=>array('phpdate' => 'm.d.Y', 'jsdate' => 'MM.DD.YYYY', 'jsdate_original' => 'mm.dd.yyyy', 'dateformat' => gT('mm.dd.yyyy')),
        11=>array('phpdate' => 'm/d/Y', 'jsdate' => 'MM/DD/YYYY', 'jsdate_original' => 'mm/dd/yyyy', 'dateformat' => gT('mm/dd/yyyy')),
        12=>array('phpdate' => 'j-n-Y', 'jsdate' => 'D-M-YYYY', 'jsdate_original' => 'd-m-yyyy', 'dateformat' => gT('d-m-yyyy'))
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

    // Albanian
    $supportedLanguages['sq']['description'] = gT('Albanian');
    $supportedLanguages['sq']['nativedescription'] = 'Shqipe';
    $supportedLanguages['sq']['rtl'] = false;
    $supportedLanguages['sq']['dateformat'] = 1;
    $supportedLanguages['sq']['radixpoint'] = 1;

    // Amharic
    $supportedLanguages['am']['description'] = gT('Amharic');
    $supportedLanguages['am']['nativedescription'] = '&#4768;&#4635;&#4653;&#4763;';
    $supportedLanguages['am']['rtl'] = false;
    $supportedLanguages['am']['dateformat'] = 2;
    $supportedLanguages['am']['radixpoint'] = 1;

    // Arabic
    $supportedLanguages['ar']['description'] = gT('Arabic');
    $supportedLanguages['ar']['nativedescription'] = '&#1593;&#1614;&#1585;&#1614;&#1576;&#1610;&#1618;';
    $supportedLanguages['ar']['rtl'] = true;
    $supportedLanguages['ar']['dateformat'] = 2;
    $supportedLanguages['ar']['radixpoint'] = 0;

    // Armenian
    $supportedLanguages['hy']['description'] = gT('Armenian');
    $supportedLanguages['hy']['nativedescription'] = '&#1392;&#1377;&#1397;&#1381;&#1408;&#1381;&#1398;';
    $supportedLanguages['hy']['rtl'] = false;
    $supportedLanguages['hy']['dateformat'] = 1;
    $supportedLanguages['hy']['radixpoint'] = 1;

    // Basque
    $supportedLanguages['eu']['description'] = gT('Basque');
    $supportedLanguages['eu']['nativedescription'] = 'Euskara';
    $supportedLanguages['eu']['rtl'] = false;
    $supportedLanguages['eu']['dateformat'] = 3;
    $supportedLanguages['eu']['radixpoint'] = 1;

    // Belarusian
    $supportedLanguages['be']['description'] = gT('Belarusian');
    $supportedLanguages['be']['nativedescription'] = '&#1041;&#1077;&#1083;&#1072;&#1088;&#1091;&#1089;&#1082;&#1110;';
    $supportedLanguages['be']['rtl'] = false;
    $supportedLanguages['be']['dateformat'] = 1;
    $supportedLanguages['be']['radixpoint'] = 1;

    // Bengali
    $supportedLanguages['bn']['description'] = gT('Bengali');
    $supportedLanguages['bn']['nativedescription'] = '&#2476;&#2494;&#2434;&#2482;&#2494;';
    $supportedLanguages['bn']['rtl'] = false;
    $supportedLanguages['bn']['dateformat'] = 2;
    $supportedLanguages['bn']['radixpoint'] = 0;

    // Bosnian
    $supportedLanguages['bs']['description'] = gT('Bosnian');
    $supportedLanguages['bs']['nativedescription'] = 'Bosanski';
    $supportedLanguages['bs']['rtl'] = false;
    $supportedLanguages['bs']['dateformat'] = 4;
    $supportedLanguages['bs']['radixpoint'] = 0;

    // Bulgarian
    $supportedLanguages['bg']['description'] = gT('Bulgarian');
    $supportedLanguages['bg']['nativedescription'] = '&#x0411;&#x044a;&#x043b;&#x0433;&#x0430;&#x0440;&#x0441;&#x043a;&#x0438;';
    $supportedLanguages['bg']['rtl'] = false;
    $supportedLanguages['bg']['dateformat'] = 1;
    $supportedLanguages['bg']['radixpoint'] = 0;

    // Catalan
    $supportedLanguages['ca-valencia']['description'] = gT('Catalan (Valencian)');
    $supportedLanguages['ca-valencia']['nativedescription'] = 'Catal&#224; (Valenci&#224;)';
    $supportedLanguages['ca-valencia']['rtl'] = false;
    $supportedLanguages['ca-valencia']['dateformat'] = 1;
    $supportedLanguages['ca-valencia']['radixpoint'] = 1;
    $supportedLanguages['ca-valencia']['cldr'] = 'ca';

    // Catalan
    $supportedLanguages['ca']['description'] = gT('Catalan');
    $supportedLanguages['ca']['nativedescription'] = 'Catal&#224;';
    $supportedLanguages['ca']['rtl'] = false;
    $supportedLanguages['ca']['dateformat'] = 1;
    $supportedLanguages['ca']['radixpoint'] = 1;

    // Welsh
    $supportedLanguages['cy']['description'] = gT('Welsh');
    $supportedLanguages['cy']['nativedescription'] = 'Cymraeg';
    $supportedLanguages['cy']['rtl'] = false;
    $supportedLanguages['cy']['dateformat'] = 5;
    $supportedLanguages['cy']['radixpoint'] = 0;

    // Chinese (Simplified)
    $supportedLanguages['zh-Hans']['description'] = gT('Chinese (Simplified)');
    $supportedLanguages['zh-Hans']['nativedescription'] = '&#31616;&#20307;&#20013;&#25991;';
    $supportedLanguages['zh-Hans']['rtl'] = false;
    $supportedLanguages['zh-Hans']['dateformat'] = 6;
    $supportedLanguages['zh-Hans']['radixpoint'] = 0;

    // Chinese (Traditional - Hong Kong)
    $supportedLanguages['zh-Hant-HK']['description'] = gT('Chinese (Traditional - Hong Kong)');
    $supportedLanguages['zh-Hant-HK']['nativedescription'] = '&#32321;&#39636;&#20013;&#25991;&#35486;&#31995;';
    $supportedLanguages['zh-Hant-HK']['rtl'] = false;
    $supportedLanguages['zh-Hant-HK']['dateformat'] = 6;
    $supportedLanguages['zh-Hant-HK']['radixpoint'] = 0;

    // Chinese (Traditional - Taiwan)
    $supportedLanguages['zh-Hant-TW']['description'] = gT('Chinese (Traditional - Taiwan)');
    $supportedLanguages['zh-Hant-TW']['nativedescription'] = '&#32321;&#39636;&#20013;&#25991;&#65288;&#21488;&#28771;&#65289;';
    $supportedLanguages['zh-Hant-TW']['rtl'] = false;
    $supportedLanguages['zh-Hant-TW']['dateformat'] = 6;
    $supportedLanguages['zh-Hant-TW']['radixpoint'] = 0;

    // Croatian
    $supportedLanguages['hr']['description'] = gT('Croatian');
    $supportedLanguages['hr']['nativedescription'] = 'Hrvatski';
    $supportedLanguages['hr']['rtl'] = false;
    $supportedLanguages['hr']['dateformat'] = 4;
    $supportedLanguages['hr']['radixpoint'] = 1;

    // Czech
    $supportedLanguages['cs']['description'] = gT('Czech');
    $supportedLanguages['cs']['nativedescription'] = '&#x010c;esky';
    $supportedLanguages['cs']['rtl'] = false;
    $supportedLanguages['cs']['dateformat'] = 4;
    $supportedLanguages['cs']['radixpoint'] = 1;

    // Czech informal
    $supportedLanguages['cs-informal']['description'] = gT('Czech (informal)');
    $supportedLanguages['cs-informal']['nativedescription'] = '&#x010c;esky neform&aacute;ln&iacute;';
    $supportedLanguages['cs-informal']['rtl'] = false;
    $supportedLanguages['cs-informal']['dateformat'] = 4;
    $supportedLanguages['cs-informal']['radixpoint'] = 1;
    $supportedLanguages['cs-informal']['cldr'] = 'cs';


    // Danish
    $supportedLanguages['da']['description'] = gT('Danish');
    $supportedLanguages['da']['nativedescription'] = 'Dansk';
    $supportedLanguages['da']['rtl'] = false;
    $supportedLanguages['da']['dateformat'] = 2;
    $supportedLanguages['da']['radixpoint'] = 1;

    // Dari
    $supportedLanguages['prs']['description'] = gT('Dari');
    $supportedLanguages['prs']['nativedescription'] = '&#1583;&#1585;&#1740;';
    $supportedLanguages['prs']['rtl'] = true;
    $supportedLanguages['prs']['dateformat'] = 6;
    $supportedLanguages['prs']['radixpoint'] = 0;
    $supportedLanguages['prs']['cldr'] = 'fa_af';

    // Dutch
    $supportedLanguages['nl']['description'] = gT('Dutch');
    $supportedLanguages['nl']['nativedescription'] = 'Nederlands';
    $supportedLanguages['nl']['rtl'] = false;
    $supportedLanguages['nl']['dateformat'] = 2;
    $supportedLanguages['nl']['radixpoint'] = 1;

    // Dutch
    $supportedLanguages['nl-informal']['description'] = gT('Dutch (informal)');
    $supportedLanguages['nl-informal']['nativedescription'] = 'Nederlands (informeel)';
    $supportedLanguages['nl-informal']['rtl'] = false;
    $supportedLanguages['nl-informal']['dateformat'] = 2;
    $supportedLanguages['nl-informal']['radixpoint'] = 1;
    $supportedLanguages['nl-informal']['cldr'] = 'nl';

    // English
    $supportedLanguages['en']['description'] = gT('English');
    $supportedLanguages['en']['nativedescription'] = 'English';
    $supportedLanguages['en']['rtl'] = false;
    $supportedLanguages['en']['dateformat'] = 9;
    $supportedLanguages['en']['radixpoint'] = 0;

    // Estonian
    $supportedLanguages['et']['description'] = gT('Estonian');
    $supportedLanguages['et']['nativedescription'] = 'Eesti';
    $supportedLanguages['et']['rtl'] = false;
    $supportedLanguages['et']['dateformat'] = 4;
    $supportedLanguages['et']['radixpoint'] = 1;

    // Finnish
    $supportedLanguages['fi']['description'] = gT('Finnish');
    $supportedLanguages['fi']['nativedescription'] = 'Suomi';
    $supportedLanguages['fi']['rtl'] = false;
    $supportedLanguages['fi']['dateformat'] = 4;
    $supportedLanguages['fi']['radixpoint'] = 1;

    // French
    $supportedLanguages['fr']['description'] = gT('French');
    $supportedLanguages['fr']['nativedescription'] = 'Fran&#231;ais';
    $supportedLanguages['fr']['rtl'] = false;
    $supportedLanguages['fr']['dateformat'] = 5;
    $supportedLanguages['fr']['radixpoint'] = 1;

    // Fula
    $supportedLanguages['ful']['description'] = gT('Fula');
    $supportedLanguages['ful']['nativedescription'] = 'Fulfulde';
    $supportedLanguages['ful']['rtl'] = false;
    $supportedLanguages['ful']['dateformat'] = 5;
    $supportedLanguages['ful']['radixpoint'] = 1;
    $supportedLanguages['ful']['cldr'] = 'ff';

    // Galician
    $supportedLanguages['gl']['description'] = gT('Galician');
    $supportedLanguages['gl']['nativedescription'] = 'Galego';
    $supportedLanguages['gl']['rtl'] = false;
    $supportedLanguages['gl']['dateformat'] = 5;
    $supportedLanguages['gl']['radixpoint'] = 1;

    // Georgian
    $supportedLanguages['ka']['description'] = gT('Georgian');
    $supportedLanguages['ka']['nativedescription'] = '&#4325;&#4304;&#4320;&#4311;&#4323;&#4314;&#4312; &#4308;&#4316;&#4304;';
    $supportedLanguages['ka']['rtl'] = false;
    $supportedLanguages['ka']['dateformat'] = 1;
    $supportedLanguages['ka']['radixpoint'] = 1;

    // German
    $supportedLanguages['de']['description'] = gT('German');
    $supportedLanguages['de']['nativedescription'] = 'Deutsch';
    $supportedLanguages['de']['rtl'] = false;
    $supportedLanguages['de']['dateformat'] = 1;
    $supportedLanguages['de']['radixpoint'] = 1;

    // German informal
    $supportedLanguages['de-informal']['description'] = gT('German (informal)');
    $supportedLanguages['de-informal']['nativedescription'] = 'Deutsch (Du)';
    $supportedLanguages['de-informal']['rtl'] = false;
    $supportedLanguages['de-informal']['dateformat'] = 1;
    $supportedLanguages['de-informal']['radixpoint'] = 1;
    $supportedLanguages['de-informal']['cldr'] = 'de';

    // Gujarati
    $supportedLanguages['gu']['description'] = gT('Gujarati');
    $supportedLanguages['gu']['nativedescription'] = '&#2711;&#2753;&#2716;&#2736;&#2750;&#2724;&#2752;';
    $supportedLanguages['gu']['rtl'] = false;
    $supportedLanguages['gu']['dateformat'] = 2;
    $supportedLanguages['gu']['radixpoint'] = 0;

    // Greek
    $supportedLanguages['el']['description'] = gT('Greek');
    $supportedLanguages['el']['nativedescription'] = '&#917;&#955;&#955;&#951;&#957;&#953;&#954;&#940;';
    $supportedLanguages['el']['rtl'] = false;
    $supportedLanguages['el']['dateformat'] = 8;
    $supportedLanguages['el']['radixpoint'] = 1;

    // Hindi
    $supportedLanguages['hi']['description'] = gT('Hindi');
    $supportedLanguages['hi']['nativedescription'] = '&#2361;&#2367;&#2344;&#2381;&#2342;&#2368;';
    $supportedLanguages['hi']['rtl'] = false;
    $supportedLanguages['hi']['dateformat'] = 2;
    $supportedLanguages['hi']['radixpoint'] = 0;

    // Hebrew
    $supportedLanguages['he']['description'] = gT('Hebrew');
    $supportedLanguages['he']['nativedescription'] = ' &#1506;&#1489;&#1512;&#1497;&#1514;';
    $supportedLanguages['he']['rtl'] = true;
    $supportedLanguages['he']['dateformat'] = 5;
    $supportedLanguages['he']['radixpoint'] = 0;

    // Hungarian
    $supportedLanguages['hu']['description'] = gT('Hungarian');
    $supportedLanguages['hu']['nativedescription'] = 'Magyar';
    $supportedLanguages['hu']['rtl'] = false;
    $supportedLanguages['hu']['dateformat'] = 6;
    $supportedLanguages['hu']['radixpoint'] = 1;

    // Icelandic
    $supportedLanguages['is']['description'] = gT('Icelandic');
    $supportedLanguages['is']['nativedescription'] = '&#237;slenska';
    $supportedLanguages['is']['rtl'] = false;
    $supportedLanguages['is']['dateformat'] = 1;
    $supportedLanguages['is']['radixpoint'] = 1;

    // Indonesian
    $supportedLanguages['id']['description'] = gT('Indonesian');
    $supportedLanguages['id']['nativedescription'] = 'Bahasa Indonesia';
    $supportedLanguages['id']['rtl'] = false;
    $supportedLanguages['id']['dateformat'] = 5;
    $supportedLanguages['id']['radixpoint'] = 1;

    // Irish
    $supportedLanguages['ie']['description'] = gT('Irish');
    $supportedLanguages['ie']['nativedescription'] = 'Gaeilge';
    $supportedLanguages['ie']['rtl'] = false;
    $supportedLanguages['ie']['dateformat'] = 2;
    $supportedLanguages['ie']['radixpoint'] = 0;
    $supportedLanguages['ie']['cldr'] = 'ga';

    // Italian
    $supportedLanguages['it']['description'] = gT('Italian');
    $supportedLanguages['it']['nativedescription'] = 'Italiano';
    $supportedLanguages['it']['rtl'] = false;
    $supportedLanguages['it']['dateformat'] = 5;
    $supportedLanguages['it']['radixpoint'] = 1;

    // Italian informal
    $supportedLanguages['it-informal']['description'] = gT('Italian (informal)');
    $supportedLanguages['it-informal']['nativedescription'] = 'Italiano (informale)';
    $supportedLanguages['it-informal']['rtl'] = false;
    $supportedLanguages['it-informal']['dateformat'] = 5;
    $supportedLanguages['it-informal']['radixpoint'] = 1;
    $supportedLanguages['it-informal']['cldr'] = 'it';

    // Japanese
    $supportedLanguages['ja']['description'] = gT('Japanese');
    $supportedLanguages['ja']['nativedescription'] = '&#x65e5;&#x672c;&#x8a9e;';
    $supportedLanguages['ja']['rtl'] = false;
    $supportedLanguages['ja']['dateformat'] = 6;
    $supportedLanguages['ja']['radixpoint'] = 0;

    // Kinyarwanda
    $supportedLanguages['rw']['description'] = gT('Kinyarwanda');
    $supportedLanguages['rw']['nativedescription'] = 'Kinyarwanda';
    $supportedLanguages['rw']['rtl'] = false;
    $supportedLanguages['rw']['dateformat'] = 5;
    $supportedLanguages['rw']['radixpoint'] = 1;

    // Korean
    $supportedLanguages['ko']['description'] = gT('Korean');
    $supportedLanguages['ko']['nativedescription'] = '&#54620;&#44397;&#50612;';
    $supportedLanguages['ko']['rtl'] = false;
    $supportedLanguages['ko']['dateformat'] = 7;
    $supportedLanguages['ko']['radixpoint'] = 0;

    // Kirundi
    $supportedLanguages['run']['description'] = gT('Kirundi');
    $supportedLanguages['run']['nativedescription'] = 'Ikirundi';
    $supportedLanguages['run']['rtl'] = false;
    $supportedLanguages['run']['dateformat'] = 1;
    $supportedLanguages['run']['radixpoint'] = 1;
    
    // Kurdish (Sorani)
    $supportedLanguages['ckb']['description'] = gT('Kurdish (Sorani)');
    $supportedLanguages['ckb']['nativedescription'] = '&#1705;&#1608;&#1585;&#1583;&#1740;&#1740; &#1606;&#1575;&#1608;&#1749;&#1606;&#1583;&#1740;';
    $supportedLanguages['ckb']['rtl'] = true;
    $supportedLanguages['ckb']['dateformat'] = 1;
    $supportedLanguages['ckb']['radixpoint'] = 1;
    $supportedLanguages['ckb']['cldr'] = 'ku';

    // Kyrgyz
    $supportedLanguages['ky']['description'] = gT('Kyrgyz');
    $supportedLanguages['ky']['nativedescription'] = '&#1050;&#1099;&#1088;&#1075;&#1099;&#1079;&#1095;&#1072;';
    $supportedLanguages['ky']['rtl'] = false;
    $supportedLanguages['ky']['dateformat'] = 1;
    $supportedLanguages['ky']['radixpoint'] = 1;

    // Luxembourgish
    $supportedLanguages['lb']['description'] = gT('Luxembourgish');
    $supportedLanguages['lb']['nativedescription'] = 'L&#235;tzebuergesch';
    $supportedLanguages['lb']['rtl'] = false;
    $supportedLanguages['lb']['dateformat'] = 1;
    $supportedLanguages['lb']['radixpoint'] = 1;

    // Lithuanian
    $supportedLanguages['lt']['description'] = gT('Lithuanian');
    $supportedLanguages['lt']['nativedescription'] = 'Lietuvi&#371;';
    $supportedLanguages['lt']['rtl'] = false;
    $supportedLanguages['lt']['dateformat'] = 6;
    $supportedLanguages['lt']['radixpoint'] = 1;

    // Latvian
    $supportedLanguages['lv']['description'] = gT('Latvian');
    $supportedLanguages['lv']['nativedescription'] = 'Latvie&#353;u';
    $supportedLanguages['lv']['rtl'] = false;
    $supportedLanguages['lv']['dateformat'] = 6;
    $supportedLanguages['lv']['radixpoint'] = 1;

    // Macedonian
    $supportedLanguages['mk']['description'] = gT('Macedonian');
    $supportedLanguages['mk']['nativedescription'] = '&#1052;&#1072;&#1082;&#1077;&#1076;&#1086;&#1085;&#1089;&#1082;&#1080;';
    $supportedLanguages['mk']['rtl'] = false;
    $supportedLanguages['mk']['dateformat'] = 1;
    $supportedLanguages['mk']['radixpoint'] = 1;

    // Mongolian
    $supportedLanguages['mn']['description'] = gT('Mongolian');
    $supportedLanguages['mn']['nativedescription'] = '&#1052;&#1086;&#1085;&#1075;&#1086;&#1083;';
    $supportedLanguages['mn']['rtl'] = false;
    $supportedLanguages['mn']['dateformat'] = 3;
    $supportedLanguages['mn']['radixpoint'] = 0;

    // Malay
    $supportedLanguages['ms']['description'] = gT('Malay');
    $supportedLanguages['ms']['nativedescription'] = 'Bahasa Melayu';
    $supportedLanguages['ms']['rtl'] = false;
    $supportedLanguages['ms']['dateformat'] = 1;
    $supportedLanguages['ms']['radixpoint'] = 0;    
    
    // Malayalam
    $supportedLanguages['ml']['description'] =  gT('Malayalam');
    $supportedLanguages['ml']['nativedescription'] = 'Malay&#257;&#7735;a&#7745;';
    $supportedLanguages['ml']['rtl'] = false;
    $supportedLanguages['ml']['dateformat'] = 2;
    $supportedLanguages['ml']['radixpoint'] = 0;

    
    // Maltese
    $supportedLanguages['mt']['description'] = gT('Maltese');
    $supportedLanguages['mt']['nativedescription'] = 'Malti';
    $supportedLanguages['mt']['rtl'] = false;
    $supportedLanguages['mt']['dateformat'] = 1;
    $supportedLanguages['mt']['radixpoint'] = 0;
    
    // Maltese
    $supportedLanguages['mt']['description'] = gT('Maltese');
    $supportedLanguages['mt']['nativedescription'] = 'Malti';
    $supportedLanguages['mt']['rtl'] = false;
    $supportedLanguages['mt']['dateformat'] = 1;
    $supportedLanguages['mt']['radixpoint'] = 0;

    // Marathi
    $supportedLanguages['mr']['description'] = gT('Marathi');
    $supportedLanguages['mr']['nativedescription'] = '&#2350;&#2352;&#2366;&#2336;&#2368;';
    $supportedLanguages['mr']['rtl'] = false;
    $supportedLanguages['mr']['dateformat'] = 2;
    $supportedLanguages['mr']['radixpoint'] = 0;
    
    // Montenegrin
    $supportedLanguages['cnr']['description'] = gT('Montenegrin');
    $supportedLanguages['cnr']['nativedescription'] = 'Crnogorski';
    $supportedLanguages['cnr']['rtl'] = false;
    $supportedLanguages['cnr']['dateformat'] = 4;
    $supportedLanguages['cnr']['radixpoint'] = 1;
    $supportedLanguages['cnr']['cldr'] ='sr_Latn_ME';
    

    // Myanmar / Burmese
    $supportedLanguages['mya']['description'] = gT('Myanmar (Burmese)');
    $supportedLanguages['mya']['nativedescription'] = '&#4121;&#4156;&#4116;&#4154;&#4121;&#4140;&#4120;&#4140;&#4126;&#4140;';
    $supportedLanguages['mya']['rtl'] = false;
    $supportedLanguages['mya']['dateformat'] = 1;
    $supportedLanguages['mya']['radixpoint'] = 1;

    // Norwegian Bokmal
    $supportedLanguages['nb']['description'] = gT('Norwegian (Bokmal)');
    $supportedLanguages['nb']['nativedescription'] = 'Norsk Bokm&#229;l';
    $supportedLanguages['nb']['rtl'] = false;
    $supportedLanguages['nb']['dateformat'] = 4;
    $supportedLanguages['nb']['radixpoint'] = 1;

    // Norwegian Nynorsk
    $supportedLanguages['nn']['description'] = gT('Norwegian (Nynorsk)');
    $supportedLanguages['nn']['nativedescription'] = 'Norsk Nynorsk';
    $supportedLanguages['nn']['rtl'] = false;
    $supportedLanguages['nn']['dateformat'] = 4;
    $supportedLanguages['nn']['radixpoint'] = 1;

    // Occitan
    $supportedLanguages['oc']['description'] = gT('Occitan');
    $supportedLanguages['oc']['nativedescription'] = "Lenga d'&#242;c";
    $supportedLanguages['oc']['rtl'] = false;
    $supportedLanguages['oc']['dateformat'] = 5;
    $supportedLanguages['oc']['radixpoint'] = 1;

    // Pashto
    $supportedLanguages['ps']['description'] = gT('Pashto');
    $supportedLanguages['ps']['nativedescription'] = '&#1662;&#1690;&#1578;&#1608;';
    $supportedLanguages['ps']['rtl'] = true;
    $supportedLanguages['ps']['dateformat'] = 6;
    $supportedLanguages['ps']['radixpoint'] = 0;

    // Persian
    $supportedLanguages['fa']['description'] = gT('Persian');
    $supportedLanguages['fa']['nativedescription'] = '&#1601;&#1575;&#1585;&#1587;&#1740;';
    $supportedLanguages['fa']['rtl'] = true;
    $supportedLanguages['fa']['dateformat'] = 6;                                                                        
    $supportedLanguages['fa']['radixpoint'] = 0;

    // Papiamento (Curacao and Bonaire)
    $supportedLanguages['pap-CW']['description'] = gT('Papiamento (Curaçao & Bonaire)');
    $supportedLanguages['pap-CW']['nativedescription'] = 'Papiamentu';
    $supportedLanguages['pap-CW']['rtl'] = false;
    $supportedLanguages['pap-CW']['dateformat'] = 2;
    $supportedLanguages['pap-CW']['radixpoint'] = 1;
    $supportedLanguages['pap-CW']['cldr'] = 'en'; // Fix me - Yii does not provide Papiamento support, yet

    // Polish
    $supportedLanguages['pl']['description'] = gT('Polish');
    $supportedLanguages['pl']['nativedescription'] = 'Polski';
    $supportedLanguages['pl']['rtl'] = false;
    $supportedLanguages['pl']['dateformat'] = 1;
    $supportedLanguages['pl']['radixpoint'] = 1;

    // Polish
    $supportedLanguages['pl-informal']['description'] = gT('Polish (Informal)');
    $supportedLanguages['pl-informal']['nativedescription'] = 'Polski (nieformalny)';
    $supportedLanguages['pl-informal']['rtl'] = false;
    $supportedLanguages['pl-informal']['dateformat'] = 1;
    $supportedLanguages['pl-informal']['radixpoint'] = 1;
    $supportedLanguages['pl-informal']['cldr'] = 'pl';

    // Portuguese
    $supportedLanguages['pt']['description'] = gT('Portuguese');
    $supportedLanguages['pt']['nativedescription'] = 'Portugu&#234;s';
    $supportedLanguages['pt']['rtl'] = false;
    $supportedLanguages['pt']['dateformat'] = 5;
    $supportedLanguages['pt']['radixpoint'] = 1;

    // Brazilian Portuguese
    $supportedLanguages['pt-BR']['description'] = gT('Portuguese (Brazilian)');
    $supportedLanguages['pt-BR']['nativedescription'] = 'Portugu&#234;s do Brasil';
    $supportedLanguages['pt-BR']['rtl'] = false;
    $supportedLanguages['pt-BR']['dateformat'] = 5;
    $supportedLanguages['pt-BR']['radixpoint'] = 1;

    // Punjabi
    $supportedLanguages['pa']['description'] = gT('Punjabi');
    $supportedLanguages['pa']['nativedescription'] = '&#2602;&#2672;&#2588;&#2622;&#2604;&#2624;';
    $supportedLanguages['pa']['rtl'] = false;
    $supportedLanguages['pa']['dateformat'] = 2;
    $supportedLanguages['pa']['radixpoint'] = 0;

    // Romanian
    $supportedLanguages['ro']['description'] = gT('Romanian');
    $supportedLanguages['ro']['nativedescription'] = 'Rom&#226;na';
    $supportedLanguages['ro']['rtl'] = false;
    $supportedLanguages['ro']['dateformat'] = 1;
    $supportedLanguages['ro']['radixpoint'] = 1;

    // Romansh
    /*
    $supportedLanguages['roh']['description'] = gT('Romansh');
    $supportedLanguages['roh']['nativedescription'] = 'Rumantsch';
    $supportedLanguages['roh']['rtl'] = false;
    $supportedLanguages['roh']['dateformat'] = 1;
    $supportedLanguages['roh']['radixpoint'] = 1;    
    */
    
    // Russian
    $supportedLanguages['ru']['description'] = gT('Russian');
    $supportedLanguages['ru']['nativedescription'] = '&#1056;&#1091;&#1089;&#1089;&#1082;&#1080;&#1081;';
    $supportedLanguages['ru']['rtl'] = false;
    $supportedLanguages['ru']['dateformat'] = 1;
    $supportedLanguages['ru']['radixpoint'] = 1;
    
    // Sinhala
    $supportedLanguages['si']['description'] = gT('Sinhala');
    $supportedLanguages['si']['nativedescription'] = '&#3523;&#3538;&#3458;&#3524;&#3517;';
    $supportedLanguages['si']['rtl'] = false;
    $supportedLanguages['si']['dateformat'] = 5;
    $supportedLanguages['si']['radixpoint'] = 0;

    // Slovak
    $supportedLanguages['sk']['description'] = gT('Slovak');
    $supportedLanguages['sk']['nativedescription'] = 'Sloven&#269;ina';
    $supportedLanguages['sk']['rtl'] = false;
    $supportedLanguages['sk']['dateformat'] = 4;
    $supportedLanguages['sk']['radixpoint'] = 1;

    // Slovenian
    $supportedLanguages['sl']['description'] = gT('Slovenian');
    $supportedLanguages['sl']['nativedescription'] = 'Sloven&#353;&#269;ina';
    $supportedLanguages['sl']['rtl'] = false;
    $supportedLanguages['sl']['dateformat'] = 4;
    $supportedLanguages['sl']['radixpoint'] = 1;

    // Serbian
    $supportedLanguages['sr']['description'] = gT('Serbian (Cyrillic)');
    $supportedLanguages['sr']['nativedescription'] = '&#1057;&#1088;&#1087;&#1089;&#1082;&#1080;';
    $supportedLanguages['sr']['rtl'] = false;
    $supportedLanguages['sr']['dateformat'] = 4;
    $supportedLanguages['sr']['radixpoint'] = 1;

    // Serbian (Latin script)
    $supportedLanguages['sr-Latn']['description'] = gT('Serbian (Latin)');
    $supportedLanguages['sr-Latn']['nativedescription'] = 'Srpski';
    $supportedLanguages['sr-Latn']['rtl'] = false;
    $supportedLanguages['sr-Latn']['dateformat'] = 4;
    $supportedLanguages['sr-Latn']['radixpoint'] = 1;

    // Spanish
    $supportedLanguages['es']['description'] = gT('Spanish');
    $supportedLanguages['es']['nativedescription'] = 'Espa&#241;ol';
    $supportedLanguages['es']['rtl'] = false;
    $supportedLanguages['es']['dateformat'] = 5;
    $supportedLanguages['es']['radixpoint'] = 1;

    // Spanish (Argentina)
    $supportedLanguages['es-AR']['description'] = gT('Spanish (Argentina)');
    $supportedLanguages['es-AR']['nativedescription'] = 'Espa&#241;ol rioplatense';
    $supportedLanguages['es-AR']['rtl'] = false;
    $supportedLanguages['es-AR']['dateformat'] = 5;
    $supportedLanguages['es-AR']['radixpoint'] = 0;

    // Spanish (Argentina) (Informal)
    $supportedLanguages['es-AR-informal']['description'] = gT('Spanish (Argentina) (Informal)');
    $supportedLanguages['es-AR-informal']['nativedescription'] = 'Espa&#241;ol rioplatense informal';
    $supportedLanguages['es-AR-informal']['rtl'] = false;
    $supportedLanguages['es-AR-informal']['dateformat'] = 5;
    $supportedLanguages['es-AR-informal']['radixpoint'] = 0;
    $supportedLanguages['es-AR-informal']['cldr'] = 'es-AR';

    // Spanish (Chile)
    $supportedLanguages['es-CL']['description'] = gT('Spanish (Chile)');
    $supportedLanguages['es-CL']['nativedescription'] = 'Espa&#241;ol chileno';
    $supportedLanguages['es-CL']['rtl'] = false;
    $supportedLanguages['es-CL']['dateformat'] = 5;
    $supportedLanguages['es-CL']['radixpoint'] = 0;

    // Spanish (Mexico)
    $supportedLanguages['es-MX']['description'] = gT('Spanish (Mexico)');
    $supportedLanguages['es-MX']['nativedescription'] = 'Espa&#241;ol mexicano';
    $supportedLanguages['es-MX']['rtl'] = false;
    $supportedLanguages['es-MX']['dateformat'] = 5;
    $supportedLanguages['es-MX']['radixpoint'] = 0;

    // Swahili
    $supportedLanguages['swh']['description'] = gT('Swahili');
    $supportedLanguages['swh']['nativedescription'] = 'Kiswahili';
    $supportedLanguages['swh']['rtl'] = false;
    $supportedLanguages['swh']['dateformat'] = 1;
    $supportedLanguages['swh']['radixpoint'] = 1;
    $supportedLanguages['swh']['cldr'] = 'sw';

    // Swedish
    $supportedLanguages['sv']['description'] = gT('Swedish');
    $supportedLanguages['sv']['nativedescription'] = 'Svenska';
    $supportedLanguages['sv']['rtl'] = false;
    $supportedLanguages['sv']['dateformat'] = 6;
    $supportedLanguages['sv']['radixpoint'] = 1;
    
    // Tagalog
    $supportedLanguages['tl']['description'] = gT('Tagalog');
    $supportedLanguages['tl']['nativedescription'] = 'Tagalog';
    $supportedLanguages['tl']['rtl'] = false;
    $supportedLanguages['tl']['dateformat'] = 1;
    $supportedLanguages['tl']['radixpoint'] = 1;
    
    // Tajik
    $supportedLanguages['tg']['description'] = gT('Tajik');
    $supportedLanguages['tg']['nativedescription'] = '&#x422;&#x43E;&#x4B7;&#x438;&#x43A;&#x4E3;';
    $supportedLanguages['tg']['rtl'] = false;
    $supportedLanguages['tg']['dateformat'] = 6;
    $supportedLanguages['tg']['radixpoint'] = 0;

    // Tamil
    $supportedLanguages['ta']['description'] = gT('Tamil');
    $supportedLanguages['ta']['nativedescription'] = '&#2980;&#2990;&#3007;&#2996;&#3021;';
    $supportedLanguages['ta']['rtl'] = false;
    $supportedLanguages['ta']['dateformat'] = 2;
    $supportedLanguages['ta']['radixpoint'] = 0;

    // Turkish
    $supportedLanguages['tr']['description'] = gT('Turkish');
    $supportedLanguages['tr']['nativedescription'] = 'T&#252;rk&#231;e';
    $supportedLanguages['tr']['rtl'] = false;
    $supportedLanguages['tr']['dateformat'] = 5;
    $supportedLanguages['tr']['radixpoint'] = 1;

    // Thai
    $supportedLanguages['th']['description'] = gT('Thai');
    $supportedLanguages['th']['nativedescription'] = '&#3616;&#3634;&#3625;&#3634;&#3652;&#3607;&#3618;';
    $supportedLanguages['th']['rtl'] = false;
    $supportedLanguages['th']['dateformat'] = 5;
    $supportedLanguages['th']['radixpoint'] = 0;

    //Ukrainian
    $supportedLanguages['uk']['description'] = gT('Ukrainian');
    $supportedLanguages['uk']['nativedescription'] = '&#x423;&#x43A;&#x440;&#x430;&#x457;&#x43D;&#x441;&#x44C;&#x43A;&#x430;';
    $supportedLanguages['uk']['rtl'] = false;
    $supportedLanguages['uk']['dateformat'] = 1;
    $supportedLanguages['uk']['radixpoint'] = 1;

    //Urdu
    $supportedLanguages['ur']['description'] = gT('Urdu');
    $supportedLanguages['ur']['nativedescription'] = '&#1575;&#1585;&#1583;&#1608;';
    $supportedLanguages['ur']['rtl'] = true;
    $supportedLanguages['ur']['dateformat'] = 2;
    $supportedLanguages['ur']['radixpoint'] = 0;
    
    //Uyghur
    $supportedLanguages['ug']['description'] = gT('Uyghur');
    $supportedLanguages['ug']['nativedescription'] = 'ئۇيغۇرچە';
    $supportedLanguages['ug']['rtl'] = true;
    $supportedLanguages['ug']['dateformat'] = 6;
    $supportedLanguages['ug']['radixpoint'] = 0;    

    // Vietnamese
    $supportedLanguages['vi']['description'] = gT('Vietnamese');
    $supportedLanguages['vi']['nativedescription'] = 'Ti&#7871;ng Vi&#7879;t';
    $supportedLanguages['vi']['rtl'] = false;
    $supportedLanguages['vi']['dateformat'] = 5;
    $supportedLanguages['vi']['radixpoint'] = 1;

    // Zulu
    $supportedLanguages['zu']['description'] = gT('Zulu');
    $supportedLanguages['zu']['nativedescription'] = 'isiZulu';
    $supportedLanguages['zu']['rtl'] = false;
    $supportedLanguages['zu']['dateformat'] = 5;
    $supportedLanguages['zu']['radixpoint'] = 1;

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
     */
function getRadixPointData($format = -1)
{
    $aRadixFormats = array(
    0=>array('separator'=> '.', 'desc'=> gT('Dot (.)')),
    1=>array('separator'=> ',', 'desc'=> gT('Comma (,)'))
    );

    // hack for fact that null sometimes sent to this function
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
function getDateFormatDataForQID($aQidAttributes, $mThisSurvey)
{
    if (isset($aQidAttributes['date_format']) && trim($aQidAttributes['date_format']) != '') {
        $aDateFormatDetails = array();
        $aDateFormatDetails['dateformat'] = trim($aQidAttributes['date_format']);
        $aDateFormatDetails['phpdate'] = getPHPDateFromDateFormat($aDateFormatDetails['dateformat']);
        $aDateFormatDetails['jsdate'] = getJSDateFromDateFormat($aDateFormatDetails['dateformat']);
        $aDateFormatDetails['jsdate_original'] = $aDateFormatDetails['dateformat']; // In dropdown, this is fed to Date in Javascript, not Bootstrap
    } else {
        if (!is_array($mThisSurvey)) {
            $mThisSurvey = array('surveyls_dateformat' => getDateFormatForSID($mThisSurvey));
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
* @param string $codetosearch
* @param boolean $withnative
* @param string $sTranslationLanguage
* @returns string|array
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
    // Strip informal string always for easier matching
    $sLocale = str_replace('-informal', '', $sLocale);
    $aConversions = array('ca-valencia'=>'ca',
                        'hy'=>'hy-am',
                        'zh-Hans'=>'zh-cn',
                        'zh-Hant-HK'=>'zh-cn',
                        'zh-Hant-TW'=>'zh-tw',
                        'prs'=>'fa',
                        'pa'=>'pa-in',
                        'sr'=>'sr-cyrl',
                        'es-AR'=>'es',
                        'es-CL'=>'es',
                        'es-MX'=>'es',
                        'swh'=>'sw'
                        );
    if (isset($aConversions[$sLocale])) {
        $sLocale = $aConversions[$sLocale];
    }
    return strtolower($sLocale);
}

function getLanguageDataRestricted($bOrderByNative = false, $sDetail = 'full')
{
    $aLanguageData = getLanguageData($bOrderByNative);

    if (trim(Yii::app()->getConfig('restrictToLanguages')) != '') {
        foreach (explode(' ', trim(Yii::app()->getConfig('restrictToLanguages'))) as $key) {
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
