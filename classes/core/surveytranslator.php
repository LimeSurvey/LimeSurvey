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
*/


/*
 * Internationalization and Localization utilities
 *
 * @package Classes
 * @subpackage Core
 */


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
    
    
    function getLanguageData() {
        global $clang;
        unset($supportedLanguages);

	    // Albanian
	    $supportedLanguages['sq']['description'] = $clang->gT('Albanian');
	    $supportedLanguages['sq']['nativedescription'] = 'Shqipe';
	    $supportedLanguages['sq']['rtl'] = false;

	    // Arabic
	    $supportedLanguages['ar']['description'] = $clang->gT('Arabic');
	    $supportedLanguages['ar']['nativedescription'] = '&#1593;&#1614;&#1585;&#1614;&#1576;&#1610;&#1618;';
	    $supportedLanguages['ar']['rtl'] = true;

	    // Basque
	    $supportedLanguages['eu']['description'] = $clang->gT('Basque');
	    $supportedLanguages['eu']['nativedescription'] = 'Euskara';
	    $supportedLanguages['eu']['rtl'] = false;

	    // Bosnian
	    $supportedLanguages['bs']['description'] = $clang->gT('Bosnian');
	    $supportedLanguages['bs']['nativedescription'] = 'Bosanski';
	    $supportedLanguages['bs']['rtl'] = false;

	    // Bulgarian
	    $supportedLanguages['bg']['description'] = $clang->gT('Bulgarian');
	    $supportedLanguages['bg']['nativedescription'] = '&#x0411;&#x044a;&#x043b;&#x0433;&#x0430;&#x0440;&#x0441;&#x043a;&#x0438;';
	    $supportedLanguages['bg']['rtl'] = false;
        
        // Catalan
		$supportedLanguages['ca']['description'] = $clang->gT('Catalan');
	    $supportedLanguages['ca']['nativedescription'] = 'Catal&#940;';
	    $supportedLanguages['ca']['rtl'] = false;

        // Welsh
		$supportedLanguages['cy']['description'] = $clang->gT('Welsh');
	    $supportedLanguages['cy']['nativedescription'] = 'Cymraeg';
	    $supportedLanguages['cy']['rtl'] = false;

        // Chinese (Simplified)
	    $supportedLanguages['zh-Hans']['description'] = $clang->gT('Chinese (Simplified)');
	    $supportedLanguages['zh-Hans']['nativedescription'] = '&#31616;&#20307;&#20013;&#25991;';
	    $supportedLanguages['zh-Hans']['rtl'] = false;

        // Chinese (Traditional - Hong Kong)
        $supportedLanguages['zh-Hant-HK']['description'] = $clang->gT('Chinese (Traditional - Hong Kong)');
        $supportedLanguages['zh-Hant-HK']['nativedescription'] = '&#32321;&#39636;&#20013;&#25991;&#35486;&#31995;';
	    $supportedLanguages['zh-Hant-HK']['rtl'] = false;

        // Chinese (Traditional - Taiwan)
        $supportedLanguages['zh-Hant-TW']['description'] = $clang->gT('Chinese (Traditional - Taiwan)');
        $supportedLanguages['zh-Hant-TW']['nativedescription'] = 'Chinese (Traditional - Taiwan)';
	    $supportedLanguages['zh-Hant-TW']['rtl'] = false;

        // Croatian
	    $supportedLanguages['hr']['description'] = $clang->gT('Croatian');
	    $supportedLanguages['hr']['nativedescription'] = 'Hrvatski';
	    $supportedLanguages['hr']['rtl'] = false;

        // Czech
        $supportedLanguages['cs']['description'] = $clang->gT('Czech');
        $supportedLanguages['cs']['nativedescription'] = '&#x010c;esky';
	    $supportedLanguages['cs']['rtl'] = false;
        
        // Danish
	    $supportedLanguages['da']['description'] = $clang->gT('Danish');
	    $supportedLanguages['da']['nativedescription'] = 'Dansk';
	    $supportedLanguages['da']['rtl'] = false;

	    // Dutch
	    $supportedLanguages['nl']['description'] = $clang->gT('Dutch');
	    $supportedLanguages['nl']['nativedescription'] = 'Nederlands';
	    $supportedLanguages['nl']['rtl'] = false;

	    // English
	    $supportedLanguages['en']['description'] = $clang->gT('English');
	    $supportedLanguages['en']['nativedescription'] = 'English';
	    $supportedLanguages['en']['rtl'] = false;

        // Estonian
        $supportedLanguages['et']['description'] = $clang->gT('Estonian');
        $supportedLanguages['et']['nativedescription'] = 'Eesti';
	    $supportedLanguages['et']['rtl'] = false;

	    // Finnish
	    $supportedLanguages['fi']['description'] = $clang->gT('Finnish');
	    $supportedLanguages['fi']['nativedescription'] = 'Suomi';
	    $supportedLanguages['fi']['rtl'] = false;


	    // French
	    $supportedLanguages['fr']['description'] = $clang->gT('French');
	    $supportedLanguages['fr']['nativedescription'] = 'Fran&#231;ais';
	    $supportedLanguages['fr']['rtl'] = false;

        // Galician
        $supportedLanguages['gl']['description'] = $clang->gT('Galician');
        $supportedLanguages['gl']['nativedescription'] = 'Galego';
	    $supportedLanguages['gl']['rtl'] = false;

   	    // German
	    $supportedLanguages['de']['description'] = $clang->gT('German');
	    $supportedLanguages['de']['nativedescription'] = 'Deutsch (Sie)';
	    $supportedLanguages['de']['rtl'] = false;

        // German informal
	    $supportedLanguages['de-informal']['description'] = $clang->gT('German informal');
	    $supportedLanguages['de-informal']['nativedescription'] = 'Deutsch (Du)';
	    $supportedLanguages['de-informal']['rtl'] = false;

	    // Greek
	    $supportedLanguages['el']['description'] = $clang->gT('Greek');
	    $supportedLanguages['el']['nativedescription'] = '&#949;&#955;&#955;&#951;&#957;&#953;&#954;&#940;';
	    $supportedLanguages['el']['rtl'] = false;

  	    // Hindi
	    $supportedLanguages['hi']['description'] = $clang->gT('Hindi');
	    $supportedLanguages['hi']['nativedescription'] = '&#2361;&#2367;&#2344;&#2381;&#2342;&#2368;';
	    $supportedLanguages['hi']['rtl'] = false;

	    // Hebrew
	    $supportedLanguages['he']['description'] = $clang->gT('Hebrew');
	    $supportedLanguages['he']['nativedescription'] = ' &#1506;&#1489;&#1512;&#1497;&#1514;';
	    $supportedLanguages['he']['rtl'] = true;

	    // Hungarian
	    $supportedLanguages['hu']['description'] = $clang->gT('Hungarian');
	    $supportedLanguages['hu']['nativedescription'] = 'Magyar';
	    $supportedLanguages['hu']['rtl'] = false;

	    // Icelandic
	    $supportedLanguages['is']['description'] = $clang->gT('Icelandic');
	    $supportedLanguages['is']['nativedescription'] = '&#237;slenska';
	    $supportedLanguages['is']['rtl'] = false;

	    // Indonesian
	    $supportedLanguages['id']['description'] = $clang->gT('Indonesian');
	    $supportedLanguages['id']['nativedescription'] = 'Bahasa Indonesia';
	    $supportedLanguages['id']['rtl'] = false;

	    // Italian
	    $supportedLanguages['it']['description'] = $clang->gT('Italian');
	    $supportedLanguages['it']['nativedescription'] = 'Italiano';
	    $supportedLanguages['it']['rtl'] = false;

	    // Japanese
	    $supportedLanguages['ja']['description'] = $clang->gT('Japanese');
	    $supportedLanguages['ja']['nativedescription'] = '&#x65e5;&#x672c;&#x8a9e;';
	    $supportedLanguages['ja']['rtl'] = false;

	    // Korean
	    $supportedLanguages['ko']['description'] = $clang->gT('Korean');
	    $supportedLanguages['ko']['nativedescription'] = '&#54620;&#44397;&#50612;';
	    $supportedLanguages['ko']['rtl'] = false;

	    // Lithuanian
	    $supportedLanguages['lt']['description'] = $clang->gT('Lithuanian');
	    $supportedLanguages['lt']['nativedescription'] = 'Lietuvi&#371;';
	    $supportedLanguages['lt']['rtl'] = false;

        // Latvian
        $supportedLanguages['lv']['description'] = $clang->gT('Latvian');
        $supportedLanguages['lv']['nativedescription'] = 'Latvie&#353;u';
        $supportedLanguages['lv']['rtl'] = false;	    

	    // Macedonian
	    $supportedLanguages['mk']['description'] = $clang->gT('Macedonian');
	    $supportedLanguages['mk']['nativedescription'] = '&#1052;&#1072;&#1082;&#1077;&#1076;&#1086;&#1085;&#1089;&#1082;&#1080;';
	    $supportedLanguages['mk']['rtl'] = false;
   
	    // Norwegian Bokml
	    $supportedLanguages['nb']['description'] = $clang->gT('Norwegian (Bokmal)');
	    $supportedLanguages['nb']['nativedescription'] = 'Norsk Bokm&#229;l';
	    $supportedLanguages['nb']['rtl'] = false;

	    // Norwegian Nynorsk 
	    $supportedLanguages['nn']['description'] = $clang->gT('Norwegian (Nynorsk)');
	    $supportedLanguages['nn']['nativedescription'] = 'Norsk Nynorsk';
	    $supportedLanguages['nn']['rtl'] = false;

	    // Persian
	    $supportedLanguages['fa']['description'] = $clang->gT('Persian');
	    $supportedLanguages['fa']['nativedescription'] = '&#1601;&#1575;&#1585;&#1587;&#1740;';
	    $supportedLanguages['fa']['rtl'] = true;

        // Polish
        $supportedLanguages['pl']['description'] = $clang->gT('Polish');
        $supportedLanguages['pl']['nativedescription'] = 'Polski';
	    $supportedLanguages['pl']['rtl'] = false;
 
 	    // Portuguese
	    $supportedLanguages['pt']['description'] = $clang->gT('Portuguese');
	    $supportedLanguages['pt']['nativedescription'] = 'Portugu&#234;s';
	    $supportedLanguages['pt']['rtl'] = false;

	    // Brazilian Portuguese
	    $supportedLanguages['pt-BR']['description'] = $clang->gT('Portuguese (Brazilian)');
	    $supportedLanguages['pt-BR']['nativedescription'] = 'Portugu&#234;s do Brasil';
	    $supportedLanguages['pt-BR']['rtl'] = false;


	    // Russian
	    $supportedLanguages['ru']['description'] = $clang->gT('Russian');
	    $supportedLanguages['ru']['nativedescription'] = '&#1056;&#1091;&#1089;&#1089;&#1082;&#1080;&#1081;';
	    $supportedLanguages['ru']['rtl'] = false;

	    // Romanian
	    $supportedLanguages['ro']['description'] = $clang->gT('Romanian');
	    $supportedLanguages['ro']['nativedescription'] = 'Rom&#226;nesc';
	    $supportedLanguages['ro']['rtl'] = false;
 
 	    // Slovak
	    $supportedLanguages['sk']['description'] = $clang->gT('Slovak');
	    $supportedLanguages['sk']['nativedescription'] = 'Slov&aacute;k';
	    $supportedLanguages['sk']['rtl'] = false;

 	    // Sinhaka
	    $supportedLanguages['si']['description'] = $clang->gT('Sinhala');
	    $supportedLanguages['si']['nativedescription'] = '&#3523;&#3538;&#3458;&#3524;&#3517;';
	    $supportedLanguages['si']['rtl'] = false;

	    // Slovenian
	    $supportedLanguages['sl']['description'] = $clang->gT('Slovenian');
	    $supportedLanguages['sl']['nativedescription'] = 'Sloven&#353;&#269;ina';
	    $supportedLanguages['sl']['rtl'] = false;

        // Serbian
        $supportedLanguages['sr']['description'] = $clang->gT('Serbian');
        $supportedLanguages['sr']['nativedescription'] = 'Srpski';
	    $supportedLanguages['sr']['rtl'] = false;

	    // Spanish
	    $supportedLanguages['es']['description'] = $clang->gT('Spanish');
	    $supportedLanguages['es']['nativedescription'] = 'Espa&#241;ol';
	    $supportedLanguages['es']['rtl'] = false;

	    // Spanish (Mexico)
	    $supportedLanguages['es-MX']['description'] = $clang->gT('Spanish (Mexico)');
	    $supportedLanguages['es-MX']['nativedescription'] = 'Espa&#241;ol Mejicano';
	    $supportedLanguages['es-MX']['rtl'] = false;

	    // Swedish
	    $supportedLanguages['sv']['description'] = $clang->gT('Swedish');
	    $supportedLanguages['sv']['nativedescription'] = 'Svenska';
	    $supportedLanguages['sv']['rtl'] = false;

	    // Turkish
	    $supportedLanguages['tr']['description'] = $clang->gT('Turkish');
	    $supportedLanguages['tr']['nativedescription'] = 'T&#252;rk&#231;e';
	    $supportedLanguages['tr']['rtl'] = false;

	    // Thai
	    $supportedLanguages['th']['description'] = $clang->gT('Thai');
	    $supportedLanguages['th']['nativedescription'] = '&#3616;&#3634;&#3625;&#3634;&#3652;&#3607;&#3618;';
	    $supportedLanguages['th']['rtl'] = false;


	    // Vietnamese
	    $supportedLanguages['vi']['description'] = $clang->gT('Vietnamese');
	    $supportedLanguages['vi']['nativedescription'] = 'Ti&#7871;ng Vi&#7879;t';
	    $supportedLanguages['vi']['rtl'] = false;

        uasort($supportedLanguages,"user_sort");
        
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
    
    
/*    // future languages

	    // Afrikaans
 	    $supportedLanguages['za']['nativedescription'] = 'Afrikaans';

	    // Irish
	    $supportedLanguages['ie']['nativedescription'] = 'Gaeilge';

	    // Icelandic
	    $supportedLanguages['is']['nativedescription'] = 'Icelandic';

	    // Latvian
	    $supportedLanguages['lv']['nativedescription'] = 'Latvie&#353;u';

	    // Serbian
	    $supportedLanguages['yu']['nativedescription'] = 'Srpski';

	    // Arabic
	    $supportedLanguages['sa']['nativedescription'] = '&#1575;&#1604;&#1593;&#1585;&#1576;&#1610;&#1577;';
	    $supportedLanguages['sa']['right-to-left'] = true;

	    $supportedLanguages['he']['right-to-left'] = true;

     */
?>
