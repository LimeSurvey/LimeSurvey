<?php
    if ( ! defined('BASEPATH')) exit('No direct script access allowed');
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
    function getDateFormatData($iDateFormat=0,$sLanguageCode='en')
    {
        $clang = new Limesurvey_lang($sLanguageCode);
        $aDateFormats= array(
        1=> array ('phpdate' => 'd.m.Y', 'jsdate' => 'dd.mm.yy', 'dateformat' => $clang->gT('dd.mm.yyyy')),
        2=> array ('phpdate' => 'd-m-Y', 'jsdate' => 'dd-mm-yy', 'dateformat' => $clang->gT('dd-mm-yyyy')),
        3=> array ('phpdate' => 'Y.m.d', 'jsdate' => 'yy.mm.dd', 'dateformat' => $clang->gT('yyyy.mm.dd')),
        4=> array ('phpdate' => 'j.n.Y', 'jsdate' => 'd.m.yy',   'dateformat' => $clang->gT('d.m.yyyy')),
        5=> array ('phpdate' => 'd/m/Y', 'jsdate' => 'dd/mm/yy', 'dateformat' => $clang->gT('dd/mm/yyyy')),
        6=> array ('phpdate' => 'Y-m-d', 'jsdate' => 'yy-mm-dd', 'dateformat' => $clang->gT('yyyy-mm-dd')),
        7=> array ('phpdate' => 'Y/m/d', 'jsdate' => 'yy/mm/dd', 'dateformat' => $clang->gT('yyyy/mm/dd')),
        8=> array ('phpdate' => 'j/n/Y', 'jsdate' => 'd/m/yy',   'dateformat' => $clang->gT('d/m/yyyy')),
        9=> array ('phpdate' => 'm-d-Y', 'jsdate' => 'mm-dd-yy', 'dateformat' => $clang->gT('mm-dd-yyyy')),
        10=>array ('phpdate' => 'm.d.Y', 'jsdate' => 'mm.dd.yy', 'dateformat' => $clang->gT('mm.dd.yyyy')),
        11=>array ('phpdate' => 'm/d/Y', 'jsdate' => 'mm/dd/yy', 'dateformat' => $clang->gT('mm/dd/yyyy')),
        12=>array ('phpdate' => 'j-n-Y', 'jsdate' => 'd-m-yy',   'dateformat' => $clang->gT('d-m-yyyy'))
        );

        if ($iDateFormat > 12 || $iDateFormat<0) {
            $iDateFormat = 11;   // TODO - what should default be?
        }
        if ($iDateFormat >0)
        {
            return $aDateFormats[$iDateFormat];
        }
        else
            return $aDateFormats;

    }

    function getLanguageData($bOrderByNative=false,$sLanguageCode='en') {

        $clang = new Limesurvey_lang($sLanguageCode);

        static $result = array();

        if (isset($result[$sLanguageCode][$bOrderByNative])) return $result[$sLanguageCode][$bOrderByNative];

        // Afrikaans
        $supportedLanguages['af']['description'] = $clang->gT('Afrikaans');
        $supportedLanguages['af']['nativedescription'] = 'Afrikaans';
        $supportedLanguages['af']['rtl'] = false;
        $supportedLanguages['af']['dateformat'] = 1;
        $supportedLanguages['af']['radixpoint'] = 1;

        // Albanian
        $supportedLanguages['sq']['description'] = $clang->gT('Albanian');
        $supportedLanguages['sq']['nativedescription'] = 'Shqipe';
        $supportedLanguages['sq']['rtl'] = false;
        $supportedLanguages['sq']['dateformat'] = 1;
        $supportedLanguages['sq']['radixpoint'] = 1;

        // Amharic
        $supportedLanguages['am']['description'] = $clang->gT('Amharic');
        $supportedLanguages['am']['nativedescription'] = '&#4768;&#4635;&#4653;&#4763;';
        $supportedLanguages['am']['rtl'] = false;
        $supportedLanguages['am']['dateformat'] = 2;
        $supportedLanguages['am']['radixpoint'] = 1;

        // Arabic
        $supportedLanguages['ar']['description'] = $clang->gT('Arabic');
        $supportedLanguages['ar']['nativedescription'] = '&#1593;&#1614;&#1585;&#1614;&#1576;&#1610;&#1618;';
        $supportedLanguages['ar']['rtl'] = true;
        $supportedLanguages['ar']['dateformat'] = 2;
        $supportedLanguages['ar']['radixpoint'] = 0;

        // Armenian
        $supportedLanguages['hy']['description'] = $clang->gT('Armenian');
        $supportedLanguages['hy']['nativedescription'] = '&#1392;&#1377;&#1397;&#1381;&#1408;&#1381;&#1398;';
        $supportedLanguages['hy']['rtl'] = false;
        $supportedLanguages['hy']['dateformat'] = 1;
        $supportedLanguages['hy']['radixpoint'] = 1;

        // Basque
        $supportedLanguages['eu']['description'] = $clang->gT('Basque');
        $supportedLanguages['eu']['nativedescription'] = 'Euskara';
        $supportedLanguages['eu']['rtl'] = false;
        $supportedLanguages['eu']['dateformat'] = 3;
        $supportedLanguages['eu']['radixpoint'] = 1;

        // Belarusian
        $supportedLanguages['be']['description'] = $clang->gT('Belarusian');
        $supportedLanguages['be']['nativedescription'] = '&#1041;&#1077;&#1083;&#1072;&#1088;&#1091;&#1089;&#1082;&#1110;';
        $supportedLanguages['be']['rtl'] = false;
        $supportedLanguages['be']['dateformat'] = 1;
        $supportedLanguages['be']['radixpoint'] = 1;

        // Bosnian
        $supportedLanguages['bs']['description'] = $clang->gT('Bosnian');
        $supportedLanguages['bs']['nativedescription'] = 'Bosanski';
        $supportedLanguages['bs']['rtl'] = false;
        $supportedLanguages['bs']['dateformat'] = 4;
        $supportedLanguages['bs']['radixpoint'] = 0;

        // Bulgarian
        $supportedLanguages['bg']['description'] = $clang->gT('Bulgarian');
        $supportedLanguages['bg']['nativedescription'] = '&#x0411;&#x044a;&#x043b;&#x0433;&#x0430;&#x0440;&#x0441;&#x043a;&#x0438;';
        $supportedLanguages['bg']['rtl'] = false;
        $supportedLanguages['bg']['dateformat'] = 1;
        $supportedLanguages['bg']['radixpoint'] = 0;

        // Catalan
        $supportedLanguages['ca-valencia']['description'] = $clang->gT('Catalan (Valencian)');
        $supportedLanguages['ca-valencia']['nativedescription'] = 'Catal&#224; (Valenci&#224;)';
        $supportedLanguages['ca-valencia']['rtl'] = false;
        $supportedLanguages['ca-valencia']['dateformat'] = 1;
        $supportedLanguages['ca-valencia']['radixpoint'] = 1;

        // Catalan
        $supportedLanguages['ca']['description'] = $clang->gT('Catalan');
        $supportedLanguages['ca']['nativedescription'] = 'Catal&#224;';
        $supportedLanguages['ca']['rtl'] = false;
        $supportedLanguages['ca']['dateformat'] = 1;
        $supportedLanguages['ca']['radixpoint'] = 1;

        // Welsh
        $supportedLanguages['cy']['description'] = $clang->gT('Welsh');
        $supportedLanguages['cy']['nativedescription'] = 'Cymraeg';
        $supportedLanguages['cy']['rtl'] = false;
        $supportedLanguages['cy']['dateformat'] = 5;
        $supportedLanguages['cy']['radixpoint'] = 0;

        // Chinese (Simplified)
        $supportedLanguages['zh-Hans']['description'] = $clang->gT('Chinese (Simplified)');
        $supportedLanguages['zh-Hans']['nativedescription'] = '&#31616;&#20307;&#20013;&#25991;';
        $supportedLanguages['zh-Hans']['rtl'] = false;
        $supportedLanguages['zh-Hans']['dateformat'] = 6;
        $supportedLanguages['zh-Hans']['radixpoint'] = 0;

        // Chinese (Traditional - Hong Kong)
        $supportedLanguages['zh-Hant-HK']['description'] = $clang->gT('Chinese (Traditional - Hong Kong)');
        $supportedLanguages['zh-Hant-HK']['nativedescription'] = '&#32321;&#39636;&#20013;&#25991;&#35486;&#31995;';
        $supportedLanguages['zh-Hant-HK']['rtl'] = false;
        $supportedLanguages['zh-Hant-HK']['dateformat'] = 6;
        $supportedLanguages['zh-Hant-HK']['radixpoint'] = 0;

        // Chinese (Traditional - Taiwan)
        $supportedLanguages['zh-Hant-TW']['description'] = $clang->gT('Chinese (Traditional - Taiwan)');
        $supportedLanguages['zh-Hant-TW']['nativedescription'] = '&#32321;&#39636;&#20013;&#25991;&#65288;&#21488;&#28771;&#65289;';
        $supportedLanguages['zh-Hant-TW']['rtl'] = false;
        $supportedLanguages['zh-Hant-TW']['dateformat'] = 6;
        $supportedLanguages['zh-Hant-TW']['radixpoint'] = 0;

        // Croatian
        $supportedLanguages['hr']['description'] = $clang->gT('Croatian');
        $supportedLanguages['hr']['nativedescription'] = 'Hrvatski';
        $supportedLanguages['hr']['rtl'] = false;
        $supportedLanguages['hr']['dateformat'] = 4;
        $supportedLanguages['hr']['radixpoint'] = 1;

        // Czech
        $supportedLanguages['cs']['description'] = $clang->gT('Czech');
        $supportedLanguages['cs']['nativedescription'] = '&#x010c;esky';
        $supportedLanguages['cs']['rtl'] = false;
        $supportedLanguages['cs']['dateformat'] = 4;
        $supportedLanguages['cs']['radixpoint'] = 1;

        // Czech informal
        $supportedLanguages['cs-informal']['description'] = $clang->gT('Czech (informal)');
        $supportedLanguages['cs-informal']['nativedescription'] = '&#x010c;esky neform&aacute;ln&iacute;';
        $supportedLanguages['cs-informal']['rtl'] = false;
        $supportedLanguages['cs-informal']['dateformat'] = 4;
        $supportedLanguages['cs-informal']['radixpoint'] = 1;
        
        
        // Danish
        $supportedLanguages['da']['description'] = $clang->gT('Danish');
        $supportedLanguages['da']['nativedescription'] = 'Dansk';
        $supportedLanguages['da']['rtl'] = false;
        $supportedLanguages['da']['dateformat'] =  2;
        $supportedLanguages['da']['radixpoint'] = 1;

        // Dari
        $supportedLanguages['prs']['description'] = $clang->gT('Dari');
        $supportedLanguages['prs']['nativedescription'] = '&#1583;&#1585;&#1740;';
        $supportedLanguages['prs']['rtl'] = true;
        $supportedLanguages['prs']['dateformat'] = 6;
        $supportedLanguages['prs']['radixpoint'] = 0;

        // Dutch
        $supportedLanguages['nl']['description'] = $clang->gT('Dutch');
        $supportedLanguages['nl']['nativedescription'] = 'Nederlands';
        $supportedLanguages['nl']['rtl'] = false;
        $supportedLanguages['nl']['dateformat'] = 2;
        $supportedLanguages['nl']['radixpoint'] = 1;

        // Dutch
        $supportedLanguages['nl-informal']['description'] = $clang->gT('Dutch (informal)');
        $supportedLanguages['nl-informal']['nativedescription'] = 'Nederlands (informeel)';
        $supportedLanguages['nl-informal']['rtl'] = false;
        $supportedLanguages['nl-informal']['dateformat'] = 2;
        $supportedLanguages['nl-informal']['radixpoint'] = 1;

        // English
        $supportedLanguages['en']['description'] = $clang->gT('English');
        $supportedLanguages['en']['nativedescription'] = 'English';
        $supportedLanguages['en']['rtl'] = false;
        $supportedLanguages['en']['dateformat'] = 9;
        $supportedLanguages['en']['radixpoint'] = 0;

        // Estonian
        $supportedLanguages['et']['description'] = $clang->gT('Estonian');
        $supportedLanguages['et']['nativedescription'] = 'Eesti';
        $supportedLanguages['et']['rtl'] = false;
        $supportedLanguages['et']['dateformat'] = 4;
        $supportedLanguages['et']['radixpoint'] = 1;

        // Finnish
        $supportedLanguages['fi']['description'] = $clang->gT('Finnish');
        $supportedLanguages['fi']['nativedescription'] = 'Suomi';
        $supportedLanguages['fi']['rtl'] = false;
        $supportedLanguages['fi']['dateformat'] = 4;
        $supportedLanguages['fi']['radixpoint'] = 1;

        // French
        $supportedLanguages['fr']['description'] = $clang->gT('French');
        $supportedLanguages['fr']['nativedescription'] = 'Fran&#231;ais';
        $supportedLanguages['fr']['rtl'] = false;
        $supportedLanguages['fr']['dateformat'] = 5;
        $supportedLanguages['fr']['radixpoint'] = 1;

        // Fula
        $supportedLanguages['ful']['description'] = $clang->gT('Fula');
        $supportedLanguages['ful']['nativedescription'] = 'Fulfulde';
        $supportedLanguages['ful']['rtl'] = false;
        $supportedLanguages['ful']['dateformat'] = 5;
        $supportedLanguages['ful']['radixpoint'] = 1;

        // Galician
        $supportedLanguages['gl']['description'] = $clang->gT('Galician');
        $supportedLanguages['gl']['nativedescription'] = 'Galego';
        $supportedLanguages['gl']['rtl'] = false;
        $supportedLanguages['gl']['dateformat'] = 5;
        $supportedLanguages['gl']['radixpoint'] = 1;

        // Georgian
        $supportedLanguages['ka']['description'] = $clang->gT('Georgian');
        $supportedLanguages['ka']['nativedescription'] = '&#4325;&#4304;&#4320;&#4311;&#4323;&#4314;&#4312; &#4308;&#4316;&#4304;';
        $supportedLanguages['ka']['rtl'] = false;
        $supportedLanguages['ka']['dateformat'] = 1;
        $supportedLanguages['ka']['radixpoint'] = 1;

        // German
        $supportedLanguages['de']['description'] = $clang->gT('German');
        $supportedLanguages['de']['nativedescription'] = 'Deutsch';
        $supportedLanguages['de']['rtl'] = false;
        $supportedLanguages['de']['dateformat'] = 1;
        $supportedLanguages['de']['radixpoint'] = 1;

        // German informal
        $supportedLanguages['de-informal']['description'] = $clang->gT('German (informal)');
        $supportedLanguages['de-informal']['nativedescription'] = 'Deutsch (Du)';
        $supportedLanguages['de-informal']['rtl'] = false;
        $supportedLanguages['de-informal']['dateformat'] = 1;
        $supportedLanguages['de-informal']['radixpoint'] = 1;
        
        // Gujarati
        $supportedLanguages['gu']['description'] = $clang->gT('Gujarati');
        $supportedLanguages['gu']['nativedescription'] = '&#2711;&#2753;&#2716;&#2736;&#2750;&#2724;&#2752;';
        $supportedLanguages['gu']['rtl'] = false;
        $supportedLanguages['gu']['dateformat'] = 2;
        $supportedLanguages['gu']['radixpoint'] = 0;

        // Greek
        $supportedLanguages['el']['description'] = $clang->gT('Greek');
        $supportedLanguages['el']['nativedescription'] = '&#949;&#955;&#955;&#951;&#957;&#953;&#954;&#940;';
        $supportedLanguages['el']['rtl'] = false;
        $supportedLanguages['el']['dateformat'] = 8;
        $supportedLanguages['el']['radixpoint'] = 1;

        // Hindi
        $supportedLanguages['hi']['description'] = $clang->gT('Hindi');
        $supportedLanguages['hi']['nativedescription'] = '&#2361;&#2367;&#2344;&#2381;&#2342;&#2368;';
        $supportedLanguages['hi']['rtl'] = false;
        $supportedLanguages['hi']['dateformat'] = 2;
        $supportedLanguages['hi']['radixpoint'] = 0;

        // Hebrew
        $supportedLanguages['he']['description'] = $clang->gT('Hebrew');
        $supportedLanguages['he']['nativedescription'] = ' &#1506;&#1489;&#1512;&#1497;&#1514;';
        $supportedLanguages['he']['rtl'] = true;
        $supportedLanguages['he']['dateformat'] = 5;
        $supportedLanguages['he']['radixpoint'] = 0;

        // Hungarian
        $supportedLanguages['hu']['description'] = $clang->gT('Hungarian');
        $supportedLanguages['hu']['nativedescription'] = 'Magyar';
        $supportedLanguages['hu']['rtl'] = false;
        $supportedLanguages['hu']['dateformat'] = 6;
        $supportedLanguages['hu']['radixpoint'] = 1;

        // Icelandic
        $supportedLanguages['is']['description'] = $clang->gT('Icelandic');
        $supportedLanguages['is']['nativedescription'] = '&#237;slenska';
        $supportedLanguages['is']['rtl'] = false;
        $supportedLanguages['is']['dateformat'] = 1;
        $supportedLanguages['is']['radixpoint'] = 1;

        // Indonesian
        $supportedLanguages['id']['description'] = $clang->gT('Indonesian');
        $supportedLanguages['id']['nativedescription'] = 'Bahasa Indonesia';
        $supportedLanguages['id']['rtl'] = false;
        $supportedLanguages['id']['dateformat'] = 5;
        $supportedLanguages['id']['radixpoint'] = 1;

        // Irish
        $supportedLanguages['ie']['description'] = $clang->gT('Irish');
        $supportedLanguages['ie']['nativedescription'] = 'Gaeilge';
        $supportedLanguages['ie']['rtl'] = false;
        $supportedLanguages['ie']['dateformat'] = 2;
        $supportedLanguages['ie']['radixpoint'] = 0;

        // Italian
        $supportedLanguages['it']['description'] = $clang->gT('Italian');
        $supportedLanguages['it']['nativedescription'] = 'Italiano';
        $supportedLanguages['it']['rtl'] = false;
        $supportedLanguages['it']['dateformat'] = 5;
        $supportedLanguages['it']['radixpoint'] = 1;

        // Italian informal
        $supportedLanguages['it-informal']['description'] = $clang->gT('Italian (informal)');
        $supportedLanguages['it-informal']['nativedescription'] = 'Italiano (informale)';
        $supportedLanguages['it-informal']['rtl'] = false;
        $supportedLanguages['it-informal']['dateformat'] = 5;
        $supportedLanguages['it-informal']['radixpoint'] = 1;

        // Japanese
        $supportedLanguages['ja']['description'] = $clang->gT('Japanese');
        $supportedLanguages['ja']['nativedescription'] = '&#x65e5;&#x672c;&#x8a9e;';
        $supportedLanguages['ja']['rtl'] = false;
        $supportedLanguages['ja']['dateformat'] = 6;
        $supportedLanguages['ja']['radixpoint'] = 0;

        // Kazakh
        $supportedLanguages['kk']['description'] = $clang->gT('Kazakh');
        $supportedLanguages['kk']['nativedescription'] = 'Qazaq&#351;a';
        $supportedLanguages['kk']['rtl'] = false;
        $supportedLanguages['kk']['dateformat'] = 1;
        $supportedLanguages['kk']['radixpoint'] = 1;

        // Kinyarwanda 
        $supportedLanguages['rw']['description'] = $clang->gT('Kinyarwanda');
        $supportedLanguages['rw']['nativedescription'] = 'Kinyarwanda';
        $supportedLanguages['rw']['rtl'] = false;
        $supportedLanguages['rw']['dateformat'] = 5;
        $supportedLanguages['rw']['radixpoint'] = 1;

        // Korean
        $supportedLanguages['ko']['description'] = $clang->gT('Korean');
        $supportedLanguages['ko']['nativedescription'] = '&#54620;&#44397;&#50612;';
        $supportedLanguages['ko']['rtl'] = false;
        $supportedLanguages['ko']['dateformat'] = 7;
        $supportedLanguages['ko']['radixpoint'] = 0;

        // Kurdish (Sorani)
        $supportedLanguages['ckb']['description'] = $clang->gT('Kurdish (Sorani)');
        $supportedLanguages['ckb']['nativedescription'] = '&#1705;&#1608;&#1585;&#1583;&#1740;&#1740; &#1606;&#1575;&#1608;&#1749;&#1606;&#1583;&#1740;';
        $supportedLanguages['ckb']['rtl'] = true;
        $supportedLanguages['ckb']['dateformat'] = 1;
        $supportedLanguages['ckb']['radixpoint'] = 1;
        
        
        // Lithuanian
        $supportedLanguages['lt']['description'] = $clang->gT('Lithuanian');
        $supportedLanguages['lt']['nativedescription'] = 'Lietuvi&#371;';
        $supportedLanguages['lt']['rtl'] = false;
        $supportedLanguages['lt']['dateformat'] = 6;
        $supportedLanguages['lt']['radixpoint'] = 1;

        // Latvian
        $supportedLanguages['lv']['description'] = $clang->gT('Latvian');
        $supportedLanguages['lv']['nativedescription'] = 'Latvie&#353;u';
        $supportedLanguages['lv']['rtl'] = false;
        $supportedLanguages['lv']['dateformat'] = 6;
        $supportedLanguages['lv']['radixpoint'] = 1;

        // Macedonian
        $supportedLanguages['mk']['description'] = $clang->gT('Macedonian');
        $supportedLanguages['mk']['nativedescription'] = '&#1052;&#1072;&#1082;&#1077;&#1076;&#1086;&#1085;&#1089;&#1082;&#1080;';
        $supportedLanguages['mk']['rtl'] = false;
        $supportedLanguages['mk']['dateformat'] = 1;
        $supportedLanguages['mk']['radixpoint'] = 1;

        // Mongolian
        $supportedLanguages['mn']['description'] = $clang->gT('Mongolian');
        $supportedLanguages['mn']['nativedescription'] = '&#1052;&#1086;&#1085;&#1075;&#1086;&#1083;';
        $supportedLanguages['mn']['rtl'] = false;
        $supportedLanguages['mn']['dateformat'] = 3;
        $supportedLanguages['mn']['radixpoint'] = 0;

        // Malay
        $supportedLanguages['ms']['description'] = $clang->gT('Malay');
        $supportedLanguages['ms']['nativedescription'] = 'Bahasa Melayu';
        $supportedLanguages['ms']['rtl'] = false;
        $supportedLanguages['ms']['dateformat'] = 1;
        $supportedLanguages['ms']['radixpoint'] = 0;

        // Maltese
        $supportedLanguages['mt']['description'] = $clang->gT('Maltese');
        $supportedLanguages['mt']['nativedescription'] = 'Malti';
        $supportedLanguages['mt']['rtl'] = false;
        $supportedLanguages['mt']['dateformat'] = 1;
        $supportedLanguages['mt']['radixpoint'] = 0;

        // Marathi
        $supportedLanguages['mr']['description'] = $clang->gT('Marathi');
        $supportedLanguages['mr']['nativedescription'] = '&#2350;&#2352;&#2366;&#2336;&#2368;';
        $supportedLanguages['mr']['rtl'] = false;
        $supportedLanguages['mr']['dateformat'] = 2;
        $supportedLanguages['mr']['radixpoint'] = 0;
        
        // Norwegian Bokmal
        $supportedLanguages['nb']['description'] = $clang->gT('Norwegian (Bokmal)');
        $supportedLanguages['nb']['nativedescription'] = 'Norsk Bokm&#229;l';
        $supportedLanguages['nb']['rtl'] = false;
        $supportedLanguages['nb']['dateformat'] = 4;
        $supportedLanguages['nb']['radixpoint'] = 1;

        // Norwegian Nynorsk
        $supportedLanguages['nn']['description'] = $clang->gT('Norwegian (Nynorsk)');
        $supportedLanguages['nn']['nativedescription'] = 'Norsk Nynorsk';
        $supportedLanguages['nn']['rtl'] = false;
        $supportedLanguages['nn']['dateformat'] = 4;
        $supportedLanguages['nn']['radixpoint'] = 1;

        // Occitan
        $supportedLanguages['oc']['description'] = $clang->gT('Occitan');
        $supportedLanguages['oc']['nativedescription'] = 'Lenga d&#39;&#242;c';
        $supportedLanguages['oc']['rtl'] = false;
        $supportedLanguages['oc']['dateformat'] = 5;
        $supportedLanguages['oc']['radixpoint'] = 1;

        // Pashto
        $supportedLanguages['ps']['description'] = $clang->gT('Pashto');
        $supportedLanguages['ps']['nativedescription'] = '&#1662;&#1690;&#1578;&#1608;';
        $supportedLanguages['ps']['rtl'] = true;
        $supportedLanguages['ps']['dateformat'] = 6;
        $supportedLanguages['ps']['radixpoint'] = 0;

        // Persian
        $supportedLanguages['fa']['description'] = $clang->gT('Persian');
        $supportedLanguages['fa']['nativedescription'] = '&#1601;&#1575;&#1585;&#1587;&#1740;';
        $supportedLanguages['fa']['rtl'] = true;
        $supportedLanguages['fa']['dateformat'] = 6;
        $supportedLanguages['fa']['radixpoint'] = 0;

        // Papiamento (Aruba)
        $supportedLanguages['pap-AW']['description'] = $clang->gT('Papiamento (Aruba)');
        $supportedLanguages['pap-AW']['nativedescription'] = 'Papiamento';
        $supportedLanguages['pap-AW']['rtl'] = false;
        $supportedLanguages['pap-AW']['dateformat'] = 2;
        $supportedLanguages['pap-AW']['radixpoint'] = 1;

        // Papiamento (Curaçao and Bonaire)
        $supportedLanguages['pap-CW']['description'] = $clang->gT('Papiamento (Curaçao and Bonaire)');
        $supportedLanguages['pap-CW']['nativedescription'] = 'Papiamentu';
        $supportedLanguages['pap-CW']['rtl'] = false;
        $supportedLanguages['pap-CW']['dateformat'] = 2;
        $supportedLanguages['pap-CW']['radixpoint'] = 1;

        // Polish
        $supportedLanguages['pl']['description'] = $clang->gT('Polish');
        $supportedLanguages['pl']['nativedescription'] = 'Polski';
        $supportedLanguages['pl']['rtl'] = false;
        $supportedLanguages['pl']['dateformat'] = 1;
        $supportedLanguages['pl']['radixpoint'] = 1;

        // Portuguese
        $supportedLanguages['pt']['description'] = $clang->gT('Portuguese');
        $supportedLanguages['pt']['nativedescription'] = 'Portugu&#234;s';
        $supportedLanguages['pt']['rtl'] = false;
        $supportedLanguages['pt']['dateformat'] = 5;
        $supportedLanguages['pt']['radixpoint'] = 1;

        // Brazilian Portuguese
        $supportedLanguages['pt-BR']['description'] = $clang->gT('Portuguese (Brazilian)');
        $supportedLanguages['pt-BR']['nativedescription'] = 'Portugu&#234;s do Brasil';
        $supportedLanguages['pt-BR']['rtl'] = false;
        $supportedLanguages['pt-BR']['dateformat'] = 5;
        $supportedLanguages['pt-BR']['radixpoint'] = 1;

        // Punjabi
        $supportedLanguages['pa']['description'] = $clang->gT('Punjabi');
        $supportedLanguages['pa']['nativedescription'] = '&#2602;&#2672;&#2588;&#2622;&#2604;&#2624;';
        $supportedLanguages['pa']['rtl'] = false;
        $supportedLanguages['pa']['dateformat'] = 2;
        $supportedLanguages['pa']['radixpoint'] = 0;

        // Russian
        $supportedLanguages['ru']['description'] = $clang->gT('Russian');
        $supportedLanguages['ru']['nativedescription'] = '&#1056;&#1091;&#1089;&#1089;&#1082;&#1080;&#1081;';
        $supportedLanguages['ru']['rtl'] = false;
        $supportedLanguages['ru']['dateformat'] = 1;
        $supportedLanguages['ru']['radixpoint'] = 1;

        // Romanian
        $supportedLanguages['ro']['description'] = $clang->gT('Romanian');
        $supportedLanguages['ro']['nativedescription'] = 'Rom&#226;na';
        $supportedLanguages['ro']['rtl'] = false;
        $supportedLanguages['ro']['dateformat'] = 1;
        $supportedLanguages['ro']['radixpoint'] = 1;

        // Slovak
        $supportedLanguages['sk']['description'] = $clang->gT('Slovak');
        $supportedLanguages['sk']['nativedescription'] = 'Sloven&#269;ina';
        $supportedLanguages['sk']['rtl'] = false;
        $supportedLanguages['sk']['dateformat'] = 4;
        $supportedLanguages['sk']['radixpoint'] = 1;

        // Sinhala
        $supportedLanguages['si']['description'] = $clang->gT('Sinhala');
        $supportedLanguages['si']['nativedescription'] = '&#3523;&#3538;&#3458;&#3524;&#3517;';
        $supportedLanguages['si']['rtl'] = false;
        $supportedLanguages['si']['dateformat'] = 5;
        $supportedLanguages['si']['radixpoint'] = 0;

        // Slovenian
        $supportedLanguages['sl']['description'] = $clang->gT('Slovenian');
        $supportedLanguages['sl']['nativedescription'] = 'Sloven&#353;&#269;ina';
        $supportedLanguages['sl']['rtl'] = false;
        $supportedLanguages['sl']['dateformat'] = 4;
        $supportedLanguages['sl']['radixpoint'] = 1;

        // Serbian
        $supportedLanguages['sr']['description'] = $clang->gT('Serbian (Cyrillic)');
        $supportedLanguages['sr']['nativedescription'] = '&#1057;&#1088;&#1087;&#1089;&#1082;&#1080;';
        $supportedLanguages['sr']['rtl'] = false;
        $supportedLanguages['sr']['dateformat'] = 4;
        $supportedLanguages['sr']['radixpoint'] = 1;

        // Serbian (Latin script)
        $supportedLanguages['sr-Latn']['description'] = $clang->gT('Serbian (Latin)');
        $supportedLanguages['sr-Latn']['nativedescription'] = 'Srpski';
        $supportedLanguages['sr-Latn']['rtl'] = false;
        $supportedLanguages['sr-Latn']['dateformat'] = 4;
        $supportedLanguages['sr-Latn']['radixpoint'] = 1;
        
        // Spanish
        $supportedLanguages['es']['description'] = $clang->gT('Spanish');
        $supportedLanguages['es']['nativedescription'] = 'Espa&#241;ol';
        $supportedLanguages['es']['rtl'] = false;
        $supportedLanguages['es']['dateformat'] = 5;
        $supportedLanguages['es']['radixpoint'] = 1;
        
        // Spanish (Argentina)
        $supportedLanguages['es-AR']['description'] = $clang->gT('Spanish (Argentina)');
        $supportedLanguages['es-AR']['nativedescription'] = 'Espa&#241;ol rioplatense';
        $supportedLanguages['es-AR']['rtl'] = false;
        $supportedLanguages['es-AR']['dateformat'] = 5;
        $supportedLanguages['es-AR']['radixpoint'] = 0;

        // Spanish (Argentina) (Informal)
        $supportedLanguages['es-AR-informal']['description'] = $clang->gT('Spanish (Argentina) (Informal)');
        $supportedLanguages['es-AR-informal']['nativedescription'] = 'Espa&#241;ol rioplatense informal';
        $supportedLanguages['es-AR-informal']['rtl'] = false;
        $supportedLanguages['es-AR-informal']['dateformat'] = 5;
        $supportedLanguages['es-AR-informal']['radixpoint'] = 0;

        // Spanish (Chile)
        $supportedLanguages['es-CL']['description'] = $clang->gT('Spanish (Chile)');
        $supportedLanguages['es-CL']['nativedescription'] = 'Espa&#241;ol chileno';
        $supportedLanguages['es-CL']['rtl'] = false;
        $supportedLanguages['es-CL']['dateformat'] = 5;
        $supportedLanguages['es-CL']['radixpoint'] = 0;

        // Spanish (Mexico)
        $supportedLanguages['es-MX']['description'] = $clang->gT('Spanish (Mexico)');
        $supportedLanguages['es-MX']['nativedescription'] = 'Espa&#241;ol mexicano';
        $supportedLanguages['es-MX']['rtl'] = false;
        $supportedLanguages['es-MX']['dateformat'] = 5;
        $supportedLanguages['es-MX']['radixpoint'] = 0;

        // Swahili
        $supportedLanguages['swh']['description'] = $clang->gT('Swahili');
        $supportedLanguages['swh']['nativedescription'] = 'Kiswahili';
        $supportedLanguages['swh']['rtl'] = false;
        $supportedLanguages['swh']['dateformat'] = 1;
        $supportedLanguages['swh']['radixpoint'] = 1;

        // Swedish
        $supportedLanguages['sv']['description'] = $clang->gT('Swedish');
        $supportedLanguages['sv']['nativedescription'] = 'Svenska';
        $supportedLanguages['sv']['rtl'] = false;
        $supportedLanguages['sv']['dateformat'] = 6;
        $supportedLanguages['sv']['radixpoint'] = 1;

        // Tamil
        $supportedLanguages['ta']['description'] = $clang->gT('Tamil');
        $supportedLanguages['ta']['nativedescription'] = '&#2980;&#2990;&#3007;&#2996;&#3021;';
        $supportedLanguages['ta']['rtl'] = false;
        $supportedLanguages['ta']['dateformat'] = 2;
        $supportedLanguages['ta']['radixpoint'] = 0;

        // Turkish
        $supportedLanguages['tr']['description'] = $clang->gT('Turkish');
        $supportedLanguages['tr']['nativedescription'] = 'T&#252;rk&#231;e';
        $supportedLanguages['tr']['rtl'] = false;
        $supportedLanguages['tr']['dateformat'] = 5;
        $supportedLanguages['tr']['radixpoint'] = 1;

        // Thai
        $supportedLanguages['th']['description'] = $clang->gT('Thai');
        $supportedLanguages['th']['nativedescription'] = '&#3616;&#3634;&#3625;&#3634;&#3652;&#3607;&#3618;';
        $supportedLanguages['th']['rtl'] = false;
        $supportedLanguages['th']['dateformat'] = 5;
        $supportedLanguages['th']['radixpoint'] = 0;


        //Urdu
        $supportedLanguages['ur']['description'] = $clang->gT('Urdu');
        $supportedLanguages['ur']['nativedescription'] = '&#1575;&#1585;&#1583;&#1608;';
        $supportedLanguages['ur']['rtl'] = true;
        $supportedLanguages['ur']['dateformat'] = 2;
        $supportedLanguages['ur']['radixpoint'] = 0;

        // Vietnamese
        $supportedLanguages['vi']['description'] = $clang->gT('Vietnamese');
        $supportedLanguages['vi']['nativedescription'] = 'Ti&#7871;ng Vi&#7879;t';
        $supportedLanguages['vi']['rtl'] = false;
        $supportedLanguages['vi']['dateformat'] = 5;
        $supportedLanguages['vi']['radixpoint'] = 1;

        // Zulu
        $supportedLanguages['zu']['description'] = $clang->gT('Zulu');
        $supportedLanguages['zu']['nativedescription'] = 'isiZulu';
        $supportedLanguages['zu']['rtl'] = false;
        $supportedLanguages['zu']['dateformat'] = 5;
        $supportedLanguages['zu']['radixpoint'] = 1;

        if ($bOrderByNative)
        {
            uasort($supportedLanguages,"userSortNative");
        }
        else
        {
            uasort($supportedLanguages,"userSort");
        }

        $result[$sLanguageCode][$bOrderByNative] = $supportedLanguages;

        Return $supportedLanguages;
    }


    /**
    *  Returns avaliable formats for Radix Points (Decimal Separators) or returns
    *  radix point info about a specific format.
    *
    *  @param int $format Format ID/Number [optional]
    */
    function getRadixPointData($format=-1)
    {
        $clang = Yii::app()->lang;
        $aRadixFormats = array (
        0=>array('separator'=> '.', 'desc'=> $clang->gT('Dot (.)')),
        1=>array('separator'=> ',', 'desc'=> $clang->gT('Comma (,)'))
        );

        // hack for fact that null sometimes sent to this function
        if (is_null($format)) {
            $format = 0;
        }

        if ($format >= 0)
            return $aRadixFormats[$format];
        else
            return $aRadixFormats;
    }


    /**
    * Convert a 'dateformat' format string to a 'phpdate' format.
    *
    * @param $sDateformat string
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
        // Without leading zero
        "d"    => "j",
        "m"    => "n",
        "yy"   => "y",
        "H"    => "G",
        "M"    => "i");

        // Extra allowed characters
        $aAllowed = array('-', '.', '/', ':', ' ');

        // Convert
        $tmp = array();
        foreach ($aAllowed as $k)
        {
            $tmp[$k] = true;
        }
        foreach (array_values($aFmts) as $k)
        {
            for ($i = 0; $i < strlen($k); $i++)
            {
                $tmp[$k[$i]] = true;
            }
        }
        $aAllowed = $tmp;

        $tmp = strtr($sDateformat, $aFmts);
        $sPhpdate = "";
        for ($i = 0; $i < strlen($tmp); $i++)
        {
            $c = $tmp[$i];
            if(isset($aAllowed[$c]))
            {
                $sPhpdate .= $c;
            }
        }

        return $sPhpdate;
    }


    /**
    * Convert a 'dateformat' format string to a 'jsdate' format.
    *
    * @param $sDateformat string
    * @returns string
    *
    */
    function getJSDateFromDateFormat($sDateformat)
    {
        // The only difference from dateformat is that Jsdate does not support truncated years
        return str_replace(array('yy'), array('y'), $sDateformat);
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
        if (isset($aQidAttributes['date_format']) && trim($aQidAttributes['date_format'])!='')
        {
            $aDateFormatDetails = array();
            $aDateFormatDetails['dateformat'] = trim($aQidAttributes['date_format']);
            $aDateFormatDetails['phpdate'] = getPHPDateFromDateFormat($aDateFormatDetails['dateformat']);
            $aDateFormatDetails['jsdate'] = getJSDateFromDateFormat($aDateFormatDetails['dateformat']);
        }
        else
        {
            if(!is_array($mThisSurvey))
            {
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
    function getDateFormatForSID($surveyid, $languagecode='')
    {
        if (!isset($languagecode) || $languagecode=='')
        {
            $languagecode=Survey::model()->findByPk($surveyid)->language;
        }
        $data = SurveyLanguageSetting::model()->getDateFormat($surveyid,$languagecode);

        if(empty($data))
        {
            $dateformat = 0;
        }
        else
        {
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
    function canShowDatePicker($dateformatdetails, $dateformats=null)
    {
        if(is_null($dateformats))
        {
            $dateformats = getDateFormatData();
        }
        $showpicker = false;
        foreach($dateformats as $format)
        {
            if($format['jsdate'] == $dateformatdetails['jsdate'])
            {
                $showpicker = true;
                break;
            }
        }
        return $showpicker;
    }


    function getLanguageCodefromLanguage($languagetosearch)
    {
        $detaillanguages = getLanguageData(false,Yii::app()->session['adminlang']);
        foreach ($detaillanguages as $key2=>$languagename)
        {
            if ($languagetosearch==$languagename['description'])
            {
                return $key2;
            }
        }
        // else return default en code
        return "en";
    }




    function getLanguageNameFromCode($codetosearch, $withnative=true, $sTranslationLanguage=null)
    {
        if (is_null($sTranslationLanguage))
        {
            $sTranslationLanguage=Yii::app()->session['adminlang'];
        }
        $detaillanguages = getLanguageData(false,$sTranslationLanguage);
        if (isset($detaillanguages[$codetosearch]['description']))
        {
            if ($withnative) {
                return array($detaillanguages[$codetosearch]['description'], $detaillanguages[$codetosearch]['nativedescription']);
            }
            else { return $detaillanguages[$codetosearch]['description'];}
        }
        else
            // else return default en code
            return false;
    }


    function getLanguageRTL($sLanguageCode)
    {
        $aLanguageData= getLanguageData(false,$sLanguageCode);
        if (isset($aLanguageData[$sLanguageCode]['rtl']))
        {
            return $aLanguageData[$sLanguageCode]['rtl'];
        }
        else
        {
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
        $detaillanguages = getLanguageData(false,Yii::app()->session['adminlang']);
        if (isset($detaillanguages[$codetosearch]))
        {
            return $detaillanguages[$codetosearch];
        }
        else
        {
            return $detaillanguages['en'];
        }
    }

    function getLanguageDataRestricted($bOrderByNative=false,$sLanguageCode='en') {
        $aLanguageData=getLanguageData($bOrderByNative, $sLanguageCode);

        if (trim(Yii::app()->getConfig('restrictToLanguages'))!='')
        {
            foreach(explode(' ',trim(Yii::app()->getConfig('restrictToLanguages'))) AS $key) {
                $aArray[$key] = $aLanguageData[$key];
            }
        }
        else
        {
            $aArray=$aLanguageData;
        }
        return $aArray;
    }


    function userSort($a, $b) {

        // smarts is all-important, so sort it first
        if($a['description'] >$b['description']) {
            return 1;
        }
        else {
            return -1;
        }
    }


    function userSortNative($a, $b) {

        // smarts is all-important, so sort it first
        if($a['nativedescription'] >$b['nativedescription']) {
            return 1;
        }
        else {
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
    function UTF8Strrev($str, $reverse_numbers=false) {
        preg_match_all('/./us', $str, $ar);
        if ($reverse_numbers)
            return join('',array_reverse($ar[0]));
        else {
            $temp = array();
            foreach ($ar[0] as $value) {
                if (is_numeric($value) && !empty($temp[0]) && is_numeric($temp[0])) {
                    foreach ($temp as $key => $value2) {
                        if (is_numeric($value2))
                            $pos = ($key + 1);
                        else
                            break;
                    }
                    $temp2 = array_splice($temp, $pos);
                    $temp = array_merge($temp, array($value), $temp2);
                } else
                    array_unshift($temp, $value);
            }
            return implode('', $temp);
        }
    }

?>
