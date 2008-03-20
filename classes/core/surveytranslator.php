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


    function getLanguageData() {
        global $clang;
        unset($supportedLanguages);

	    // Albanian
	    $supportedLanguages['sq']['description'] = $clang->gT('Albanian');
	    $supportedLanguages['sq']['nativedescription'] = 'Shqipe';

	    // Basque
	    $supportedLanguages['eu']['description'] = $clang->gT('Basque');
	    $supportedLanguages['eu']['nativedescription'] = 'Euskara';

	    // Bosnian
	    $supportedLanguages['bs']['description'] = $clang->gT('Bosnian');
	    $supportedLanguages['bs']['nativedescription'] = '&#x0411;&#x044a;&#x043b;&#x0433;&#x0430;&#x0440;&#x0441;&#x043a;&#x0438;';

	    // Bulgarian
	    $supportedLanguages['bg']['description'] = $clang->gT('Bulgarian');
	    $supportedLanguages['bg']['nativedescription'] = '&#x0411;&#x044a;&#x043b;&#x0433;&#x0430;&#x0440;&#x0441;&#x043a;&#x0438;';
        
        // Catalan
		$supportedLanguages['ca']['description'] = $clang->gT('Catalan');
	    $supportedLanguages['ca']['nativedescription'] = 'Catal&#940;';

        // Chinese (Simplified)
	    $supportedLanguages['zh-Hans']['description'] = $clang->gT('Chinese (Simplified)');
	    $supportedLanguages['zh-Hans']['nativedescription'] = '&#31616;&#20307;&#20013;&#25991;';

        // Chinese (Traditional - Hong Kong)
        $supportedLanguages['zh-Hant-HK']['description'] = $clang->gT('Chinese (Traditional - Hong Kong)');
        $supportedLanguages['zh-Hant-HK']['nativedescription'] = '&#32321;&#39636;&#20013;&#25991;&#35486;&#31995;';

        // Chinese (Traditional - Taiwan)
        $supportedLanguages['zh-Hant-TW']['description'] = $clang->gT('Chinese (Traditional - Taiwan)');
        $supportedLanguages['zh-Hant-TW']['nativedescription'] = 'Chinese (Traditional - Taiwan)';

        // Croatian
	    $supportedLanguages['hr']['description'] = $clang->gT('Croatian');
	    $supportedLanguages['hr']['nativedescription'] = 'Hrvatski';

        // Czech
        $supportedLanguages['cs']['description'] = $clang->gT('Czech');
        $supportedLanguages['cs']['nativedescription'] = '&#x010c;esky';
        
        // Danish
	    $supportedLanguages['da']['description'] = $clang->gT('Danish');
	    $supportedLanguages['da']['nativedescription'] = 'Dansk';

	    // Dutch
	    $supportedLanguages['nl']['description'] = $clang->gT('Dutch');
	    $supportedLanguages['nl']['nativedescription'] = 'Nederlands';

	    // English
	    $supportedLanguages['en']['description'] = $clang->gT('English');
	    $supportedLanguages['en']['nativedescription'] = 'English';

        // Estonian
        $supportedLanguages['et']['description'] = $clang->gT('Estonian');
        $supportedLanguages['et']['nativedescription'] = 'Eesti';

	    // Finnish
	    $supportedLanguages['fi']['description'] = $clang->gT('Finnish');
	    $supportedLanguages['fi']['nativedescription'] = 'Suomi';


	    // French
	    $supportedLanguages['fr']['description'] = $clang->gT('French');
	    $supportedLanguages['fr']['nativedescription'] = 'Fran&#231;ais';

        // Galician
        $supportedLanguages['gl']['description'] = $clang->gT('Galician');
        $supportedLanguages['gl']['nativedescription'] = 'Galego';

   	    // German
	    $supportedLanguages['de']['description'] = $clang->gT('German');
	    $supportedLanguages['de']['nativedescription'] = 'Deutsch (Sie)';

        // German informal
	    $supportedLanguages['de-informal']['description'] = $clang->gT('German informal');
	    $supportedLanguages['de-informal']['nativedescription'] = 'Deutsch (Du)';

	    // Greek
	    $supportedLanguages['el']['description'] = $clang->gT('Greek');
	    $supportedLanguages['el']['nativedescription'] = '&#949;&#955;&#955;&#951;&#957;&#953;&#954;&#940;';

	    // Hebrew
	    $supportedLanguages['he']['description'] = $clang->gT('Hebrew');
	    $supportedLanguages['he']['nativedescription'] = ' &#1506;&#1489;&#1512;&#1497;&#1514;';

	    // Hungarian
	    $supportedLanguages['hu']['description'] = $clang->gT('Hungarian');
	    $supportedLanguages['hu']['nativedescription'] = 'Magyar';

	    // Indonesian
	    $supportedLanguages['id']['description'] = $clang->gT('Indonesian');
	    $supportedLanguages['id']['nativedescription'] = 'Bahasa Indonesia';

	    // Italian
	    $supportedLanguages['it']['description'] = $clang->gT('Italian');
	    $supportedLanguages['it']['nativedescription'] = 'Italiano';

	    // Japanese
	    $supportedLanguages['ja']['description'] = $clang->gT('Japanese');
	    $supportedLanguages['ja']['nativedescription'] = '&#x65e5;&#x672c;&#x8a9e;';

	    // Lithuanian
	    $supportedLanguages['lt']['description'] = $clang->gT('Lithuanian');
	    $supportedLanguages['lt']['nativedescription'] = 'Lietuvi&#371;';
	    
	    // Macedonian
	    $supportedLanguages['mk']['description'] = $clang->gT('Macedonian');
	    $supportedLanguages['mk']['nativedescription'] = '&#1052;&#1072;&#1082;&#1077;&#1076;&#1086;&#1085;&#1089;&#1082;&#1080;';
   
	    // Norwegian Bokml
	    $supportedLanguages['nb']['description'] = $clang->gT('Norwegian (Bokmal)');
	    $supportedLanguages['nb']['nativedescription'] = 'Norsk Bokm&#229;l';

	    // Norwegian Nynorsk 
	    $supportedLanguages['nn']['description'] = $clang->gT('Norwegian (Nynorsk)');
	    $supportedLanguages['nn']['nativedescription'] = 'Norsk Nynorsk';

        // Polish
        $supportedLanguages['pl']['description'] = $clang->gT('Polish');
        $supportedLanguages['pl']['nativedescription'] = 'Polski';
 
 	    // Portuguese
	    $supportedLanguages['pt']['description'] = $clang->gT('Portuguese');
	    $supportedLanguages['pt']['nativedescription'] = 'Portugu&#234;s';

	    // Brazilian Portuguese
	    $supportedLanguages['pt-BR']['description'] = $clang->gT('Portuguese (Brazilian)');
	    $supportedLanguages['pt-BR']['nativedescription'] = 'Portugu&#234;s do Brasil';


	    // Russian
	    $supportedLanguages['ru']['description'] = $clang->gT('Russian');
	    $supportedLanguages['ru']['nativedescription'] = '&#1056;&#1091;&#1089;&#1089;&#1082;&#1080;&#1081;';

	    // Romanian
	    $supportedLanguages['ro']['description'] = $clang->gT('Romanian');
	    $supportedLanguages['ro']['nativedescription'] = 'Rom&#226;nesc';
 
 	    // Slovak
	    $supportedLanguages['sk']['description'] = $clang->gT('Slovak');
	    $supportedLanguages['sk']['nativedescription'] = 'Slov&aacute;k';

	    // Slovenian
	    $supportedLanguages['sl']['description'] = $clang->gT('Slovenian');
	    $supportedLanguages['sl']['nativedescription'] = 'Sloven&#353;&#269;ina';

        // Serbian
        $supportedLanguages['sr']['description'] = $clang->gT('Serbian');
        $supportedLanguages['sr']['nativedescription'] = 'Srpski';

	    // Spanish
	    $supportedLanguages['es']['description'] = $clang->gT('Spanish');
	    $supportedLanguages['es']['nativedescription'] = 'Espa&#241;ol';

	    // Spanish (Mexico)
	    $supportedLanguages['es-MX']['description'] = $clang->gT('Spanish (Mexico)');
	    $supportedLanguages['es-MX']['nativedescription'] = 'Espa&#241;ol Mejicano';

	    // Swedish
	    $supportedLanguages['sv']['description'] = $clang->gT('Swedish');
	    $supportedLanguages['sv']['nativedescription'] = 'Svenska';

	    // Turkish
	    $supportedLanguages['tr']['description'] = $clang->gT('Turkish');
	    $supportedLanguages['tr']['nativedescription'] = 'T&#252;rk&#231;e';

	    // Thai
	    $supportedLanguages['th']['description'] = $clang->gT('Thai');
	    $supportedLanguages['th']['nativedescription'] = '&#3616;&#3634;&#3625;&#3634;&#3652;&#3607;&#3618;';


	    // Vietnamese
	    $supportedLanguages['vi']['description'] = $clang->gT('Vietnamese');
	    $supportedLanguages['vi']['nativedescription'] = 'Ti&#7871;ng Vi&#7879;t';

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
