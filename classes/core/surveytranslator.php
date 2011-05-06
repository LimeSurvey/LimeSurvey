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
 * $Id: surveytranslator.php 9719 2011-01-26 21:44:40Z c_schmitz $
 */


/*
 * Internationalization and Localization utilities
 *
 * @package Classes
 * @subpackage Core
 */


/**
 * Returns all available dateformats in a structured aray
 * If $format is given only the particual dateformat will be returned
 *
 * @param $format integer
 * @returns array
 *
 */
function getDateFormatData($format=0)
{
    $dateformats= array(
        1=> array ('phpdate' => 'd.m.Y', 'jsdate' => 'dd.mm.yy', 'dateformat' => 'dd.mm.yyyy'),
        2=> array ('phpdate' => 'd-m-Y', 'jsdate' => 'dd-mm-yy', 'dateformat' => 'dd-mm-yyyy'),
        5=> array ('phpdate' => 'd/m/Y', 'jsdate' => 'dd/mm/yy', 'dateformat' => 'dd/mm/yyyy'),
        3=> array ('phpdate' => 'Y.m.d', 'jsdate' => 'yy.mm.dd', 'dateformat' => 'yyyy.mm.dd'),
        6=> array ('phpdate' => 'Y-m-d', 'jsdate' => 'yy-mm-dd', 'dateformat' => 'yyyy-mm-dd'),
        7=> array ('phpdate' => 'Y/m/d', 'jsdate' => 'yy/mm/dd', 'dateformat' => 'yyyy/mm/dd'),
        4=> array ('phpdate' => 'j.n.Y', 'jsdate' => 'd.m.yy', 'dateformat' => 'd.m.yyyy'),
        12=>array ('phpdate' => 'j-n-Y', 'jsdate' => 'd-m-yy',    'dateformat' => 'd-m-yyyy'),
        8=> array ('phpdate' => 'j/n/Y', 'jsdate' => 'd/m/yy', 'dateformat' => 'd/m/yyyy'),
        9=> array ('phpdate' => 'm-d-Y', 'jsdate' => 'mm-dd-yy', 'dateformat' => 'mm-dd-yyyy'),
        10=>array ('phpdate' => 'm.d.Y', 'jsdate' => 'mm.dd.yy',  'dateformat' => 'mm.dd.yyyy'),
        11=>array ('phpdate' => 'm/d/Y', 'jsdate' => 'mm/dd/yy',  'dateformat' => 'mm/dd/yyyy')
    );

    if ($format >0)
    {
        return $dateformats[$format];
    }
    else
    return $dateformats;

}

/**
 *  Returns avaliable formats for Radix Points (Decimal Seperators) or returns
 *  radix point info about a specific format.
 *
 *  @param int $format Format ID/Number [optional]
 */

function getRadixPointData($format=-1)
{
    global $clang;      
    $aRadixFormats = array (
            0=>array('seperator'=> '.', 'desc'=> $clang->gT('Dot (.)')),
            1=>array('seperator'=> ',', 'desc'=> $clang->gT('Comma (,)'))
     );

    if ($format >= 0)
        return $aRadixFormats[$format];
    else
        return $aRadixFormats;
}


function getLanguageCodefromLanguage($languagetosearch)
{
    $detaillanguages = getLanguageData();
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




function getLanguageNameFromCode($codetosearch, $withnative=true)
{
    $detaillanguages = getLanguageData();
    if (isset($detaillanguages[$codetosearch]['description']))
    {
        if ($withnative) {
            return $detaillanguages[$codetosearch]['description'].' - '.$detaillanguages[$codetosearch]['nativedescription'];
        }
        else { return $detaillanguages[$codetosearch]['description'];}
    }
    else
    // else return default en code
    return false;
}


function getLanguageRTL($codetosearch)
{
    $detaillanguages = getLanguageData();
    if (isset($detaillanguages[$codetosearch]['rtl']))
    {
        return $detaillanguages[$codetosearch]['rtl'];
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
    $detaillanguages = getLanguageData();
    if (isset($detaillanguages[$codetosearch]))
    {
        return $detaillanguages[$codetosearch];
    }
    else
    {
        return $detaillanguages['en'];
    }
}

function getLanguageData($orderbynative=false) {
    global $clang;
    static $supportedLanguages;
    static $result = array();

    if (isset($result[$orderbynative])) return $result[$orderbynative];

    if (!isset($supportedLanguages)) {
    // Albanian
    $supportedLanguages['sq']['description'] = $clang->gT('Albanian');
    $supportedLanguages['sq']['nativedescription'] = 'Shqipe';
    $supportedLanguages['sq']['rtl'] = false;
    $supportedLanguages['sq']['dateformat'] = 1;
    $supportedLanguages['sq']['radixpoint'] = 1;

    // Arabic
    $supportedLanguages['ar']['description'] = $clang->gT('Arabic');
    $supportedLanguages['ar']['nativedescription'] = '&#1593;&#1614;&#1585;&#1614;&#1576;&#1610;&#1618;';
    $supportedLanguages['ar']['rtl'] = true;
    $supportedLanguages['ar']['dateformat'] = 2;
    $supportedLanguages['ar']['radixpoint'] = 0;

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
    $supportedLanguages['ca']['description'] = $clang->gT('Catalan');
    $supportedLanguages['ca']['nativedescription'] = 'Catal&#940;';
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
    $supportedLanguages['zh-Hant-TW']['nativedescription'] = 'Chinese (Traditional - Taiwan)';
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

    // Danish
    $supportedLanguages['da']['description'] = $clang->gT('Danish');
    $supportedLanguages['da']['nativedescription'] = 'Dansk';
    $supportedLanguages['da']['rtl'] = false;
    $supportedLanguages['da']['dateformat'] =  2;
    $supportedLanguages['da']['radixpoint'] = 1;

    // Dutch
    $supportedLanguages['nl']['description'] = $clang->gT('Dutch');
    $supportedLanguages['nl']['nativedescription'] = 'Nederlands';
    $supportedLanguages['nl']['rtl'] = false;
    $supportedLanguages['nl']['dateformat'] = 2;
    $supportedLanguages['nl']['radixpoint'] = 1;

    // Dutch
    $supportedLanguages['nl-informal']['description'] = $clang->gT('Dutch Informal');
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
    $supportedLanguages['fr']['dateformat'] = 2;
    $supportedLanguages['fr']['radixpoint'] = 1;

    // Galician
    $supportedLanguages['gl']['description'] = $clang->gT('Galician');
    $supportedLanguages['gl']['nativedescription'] = 'Galego';
    $supportedLanguages['gl']['rtl'] = false;
    $supportedLanguages['gl']['dateformat'] = 5;
    $supportedLanguages['gl']['radixpoint'] = 1;

    // German
    $supportedLanguages['de']['description'] = $clang->gT('German');
    $supportedLanguages['de']['nativedescription'] = 'Deutsch';
    $supportedLanguages['de']['rtl'] = false;
    $supportedLanguages['de']['dateformat'] = 1;
    $supportedLanguages['de']['radixpoint'] = 1;

    // German informal
    $supportedLanguages['de-informal']['description'] = $clang->gT('German informal');
    $supportedLanguages['de-informal']['nativedescription'] = 'Deutsch (Du)';
    $supportedLanguages['de-informal']['rtl'] = false;
    $supportedLanguages['de-informal']['dateformat'] = 1;
    $supportedLanguages['de-informal']['radixpoint'] = 1;

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

    // Italian-Formal
    $supportedLanguages['it-formal']['description'] = $clang->gT('Italian (formal)');
    $supportedLanguages['it-formal']['nativedescription'] = 'Formale Italiano';
    $supportedLanguages['it-formal']['rtl'] = false;
    $supportedLanguages['it-formal']['dateformat'] = 5;
    $supportedLanguages['it-formal']['radixpoint'] = 1;

    // Japanese
    $supportedLanguages['ja']['description'] = $clang->gT('Japanese');
    $supportedLanguages['ja']['nativedescription'] = '&#x65e5;&#x672c;&#x8a9e;';
    $supportedLanguages['ja']['rtl'] = false;
    $supportedLanguages['ja']['dateformat'] = 6;
    $supportedLanguages['ja']['radixpoint'] = 0;

    // Korean
    $supportedLanguages['ko']['description'] = $clang->gT('Korean');
    $supportedLanguages['ko']['nativedescription'] = '&#54620;&#44397;&#50612;';
    $supportedLanguages['ko']['rtl'] = false;
    $supportedLanguages['ko']['dateformat'] = 7;
    $supportedLanguages['ko']['radixpoint'] = 0;

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

    // Persian
    $supportedLanguages['fa']['description'] = $clang->gT('Persian');
    $supportedLanguages['fa']['nativedescription'] = '&#1601;&#1575;&#1585;&#1587;&#1740;';
    $supportedLanguages['fa']['rtl'] = true;
    $supportedLanguages['fa']['dateformat'] = 6;
    $supportedLanguages['fa']['radixpoint'] = 0;

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
    $supportedLanguages['ro']['nativedescription'] = 'Rom&#226;nesc';
    $supportedLanguages['ro']['rtl'] = false;
    $supportedLanguages['ro']['dateformat'] = 1;
    $supportedLanguages['ro']['radixpoint'] = 1;

    // Slovak
    $supportedLanguages['sk']['description'] = $clang->gT('Slovak');
    $supportedLanguages['sk']['nativedescription'] = 'Slov&aacute;k';
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
    $supportedLanguages['sr']['description'] = $clang->gT('Serbian');
    $supportedLanguages['sr']['nativedescription'] = 'Srpski';
    $supportedLanguages['sr']['rtl'] = false;
    $supportedLanguages['sr']['dateformat'] = 4;
    $supportedLanguages['sr']['radixpoint'] = 1;

    // Spanish
    $supportedLanguages['es']['description'] = $clang->gT('Spanish');
    $supportedLanguages['es']['nativedescription'] = 'Espa&#241;ol';
    $supportedLanguages['es']['rtl'] = false;
    $supportedLanguages['es']['dateformat'] = 5;
    $supportedLanguages['es']['radixpoint'] = 1;

    // Spanish (Mexico)
    $supportedLanguages['es-MX']['description'] = $clang->gT('Spanish (Mexico)');
    $supportedLanguages['es-MX']['nativedescription'] = 'Espa&#241;ol Mejicano';
    $supportedLanguages['es-MX']['rtl'] = false;
    $supportedLanguages['es-MX']['dateformat'] = 5;
    $supportedLanguages['es-MX']['radixpoint'] = 0;

    // Swedish
    $supportedLanguages['sv']['description'] = $clang->gT('Swedish');
    $supportedLanguages['sv']['nativedescription'] = 'Svenska';
    $supportedLanguages['sv']['rtl'] = false;
    $supportedLanguages['sv']['dateformat'] = 6;
    $supportedLanguages['sv']['radixpoint'] = 1;

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
    }

    if ($orderbynative)
    {
        uasort($supportedLanguages,"user_sort_native");
    }
    else
    {
        uasort($supportedLanguages,"user_sort");
    }

    $result[$orderbynative] = $supportedLanguages;

    Return $supportedLanguages;
}

function user_sort($a, $b) {
    
    // smarts is all-important, so sort it first
    if($a['description'] >$b['description']) {
        return 1;
    }
    else {
        return -1;
    }
}

function user_sort_native($a, $b) {

    // smarts is all-important, so sort it first
    if($a['nativedescription'] >$b['nativedescription']) {
        return 1;
    }
    else {
        return -1;
    }
}


/*    // future languages
 // Afrikaans
 $supportedLanguages['za']['nativedescription'] = 'Afrikaans';
 // Irish
 $supportedLanguages['ie']['nativedescription'] = 'Gaeilge';
 // Serbian
 $supportedLanguages['yu']['nativedescription'] = 'Srpski';
 */


/**
 * This function  support the ability NOT to reverse numbers (for example when you output
 * a phrase as a parameter for a SWF file that can't handle RTL languages itself, but
 * obviously any numbers should remain the same as in the original phrase).
 *  Note that it can be used just as well for UTF-8 usages if you want the numbers to remain intact
 *
 * @param string $str
 * @param boolean $reverse_numbers
 * @return string
 */
function utf8_strrev($str, $reverse_numbers=false) {
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
