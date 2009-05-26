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
        if (isset($detaillanguages[$codetosearch]['rtl']))
        {
              return $detaillanguages[$codetosearch];
        }
        else
            {
                return $detaillanguages['en'];
            }
    }
    
    function getLanguageData() {
        global $clang;

	    // Albanian
	    $supportedLanguages['sq']['description'] = $clang->gT('Albanian');
	    $supportedLanguages['sq']['nativedescription'] = 'Shqipe';
	    $supportedLanguages['sq']['rtl'] = false;
        $supportedLanguages['sq']['dateformatphp'] = 'd.m.Y';
        $supportedLanguages['sq']['dateformat'] = 'dd.mm.yyyy';

	    // Arabic
	    $supportedLanguages['ar']['description'] = $clang->gT('Arabic');
	    $supportedLanguages['ar']['nativedescription'] = '&#1593;&#1614;&#1585;&#1614;&#1576;&#1610;&#1618;';
	    $supportedLanguages['ar']['rtl'] = true;
        $supportedLanguages['ar']['dateformatphp'] = 'dd-mm-yyyy';
        $supportedLanguages['ar']['dateformat'] = 'dd-mm-yyyy';

	    // Basque
	    $supportedLanguages['eu']['description'] = $clang->gT('Basque');
	    $supportedLanguages['eu']['nativedescription'] = 'Euskara';
	    $supportedLanguages['eu']['rtl'] = false;
        $supportedLanguages['eu']['dateformatphp'] = 'Y.m.d';
        $supportedLanguages['eu']['dateformat'] = 'yyyy.mm.dd';

	    // Bosnian
	    $supportedLanguages['bs']['description'] = $clang->gT('Bosnian');
	    $supportedLanguages['bs']['nativedescription'] = 'Bosanski';
	    $supportedLanguages['bs']['rtl'] = false;
        $supportedLanguages['bs']['dateformatphp'] = 'j.n.Y';
        $supportedLanguages['bs']['dateformat'] = 'd.m.yyyy';        

	    // Bulgarian
	    $supportedLanguages['bg']['description'] = $clang->gT('Bulgarian');
	    $supportedLanguages['bg']['nativedescription'] = '&#x0411;&#x044a;&#x043b;&#x0433;&#x0430;&#x0440;&#x0441;&#x043a;&#x0438;';
	    $supportedLanguages['bg']['rtl'] = false;
        $supportedLanguages['bg']['dateformatphp'] = 'd.m.Y';
        $supportedLanguages['bg']['dateformat'] = 'dd.mm.yyyy';
        
        
        // Catalan
		$supportedLanguages['ca']['description'] = $clang->gT('Catalan');
	    $supportedLanguages['ca']['nativedescription'] = 'Catal&#940;';
	    $supportedLanguages['ca']['rtl'] = false;
        $supportedLanguages['ca']['dateformatphp'] = 'd.m.Y';
        $supportedLanguages['ca']['dateformat'] = 'dd.mm.yyyy';

        // Welsh
		$supportedLanguages['cy']['description'] = $clang->gT('Welsh');
	    $supportedLanguages['cy']['nativedescription'] = 'Cymraeg';
	    $supportedLanguages['cy']['rtl'] = false;
        $supportedLanguages['cy']['dateformatphp'] = 'd/m/Y';
        $supportedLanguages['cy']['dateformat'] = 'dd/mm/yyyy';

        // Chinese (Simplified)
	    $supportedLanguages['zh-Hans']['description'] = $clang->gT('Chinese (Simplified)');
	    $supportedLanguages['zh-Hans']['nativedescription'] = '&#31616;&#20307;&#20013;&#25991;';
	    $supportedLanguages['zh-Hans']['rtl'] = false;
        $supportedLanguages['zh-Hans']['dateformatphp'] = 'Y-m-d';
        $supportedLanguages['zh-Hans']['dateformat'] = 'yyyy-mm-dd';

        // Chinese (Traditional - Hong Kong)
        $supportedLanguages['zh-Hant-HK']['description'] = $clang->gT('Chinese (Traditional - Hong Kong)');
        $supportedLanguages['zh-Hant-HK']['nativedescription'] = '&#32321;&#39636;&#20013;&#25991;&#35486;&#31995;';
	    $supportedLanguages['zh-Hant-HK']['rtl'] = false;
        $supportedLanguages['zh-Hant-HK']['dateformatphp'] = 'Y-m-d';
        $supportedLanguages['zh-Hant-HK']['dateformat'] = 'yyyy-mm-dd';

        // Chinese (Traditional - Taiwan)
        $supportedLanguages['zh-Hant-TW']['description'] = $clang->gT('Chinese (Traditional - Taiwan)');
        $supportedLanguages['zh-Hant-TW']['nativedescription'] = 'Chinese (Traditional - Taiwan)';
	    $supportedLanguages['zh-Hant-TW']['rtl'] = false;
        $supportedLanguages['zh-Hant-TW']['dateformatphp'] = 'Y-m-d';
        $supportedLanguages['zh-Hant-TW']['dateformat'] = 'yyyy-mm-dd';

        // Croatian
	    $supportedLanguages['hr']['description'] = $clang->gT('Croatian');
	    $supportedLanguages['hr']['nativedescription'] = 'Hrvatski';
	    $supportedLanguages['hr']['rtl'] = false;
        $supportedLanguages['hr']['dateformatphp'] = 'j.n.Y';
        $supportedLanguages['hr']['dateformat'] = 'd.m.yyyy';        

        // Czech
        $supportedLanguages['cs']['description'] = $clang->gT('Czech');
        $supportedLanguages['cs']['nativedescription'] = '&#x010c;esky';
	    $supportedLanguages['cs']['rtl'] = false;
        $supportedLanguages['cs']['dateformatphp'] = 'j.n.Y';
        $supportedLanguages['cs']['dateformat'] = 'd.m.yyyy';        
        
        // Danish
	    $supportedLanguages['da']['description'] = $clang->gT('Danish');
	    $supportedLanguages['da']['nativedescription'] = 'Dansk';
	    $supportedLanguages['da']['rtl'] = false;
        $supportedLanguages['da']['dateformatphp'] = 'd-m-Y';
        $supportedLanguages['da']['dateformat'] = 'dd-mm-yyyy';

	    // Dutch
	    $supportedLanguages['nl']['description'] = $clang->gT('Dutch');
	    $supportedLanguages['nl']['nativedescription'] = 'Nederlands';
	    $supportedLanguages['nl']['rtl'] = false;
        $supportedLanguages['nl']['dateformatphp'] = 'd-m-Y';
        $supportedLanguages['nl']['dateformat'] = 'dd-mm-yyyy';

	    // English
	    $supportedLanguages['en']['description'] = $clang->gT('English');
	    $supportedLanguages['en']['nativedescription'] = 'English';
	    $supportedLanguages['en']['rtl'] = false;
        $supportedLanguages['en']['dateformatphp'] = 'm-d-Y';
        $supportedLanguages['en']['dateformat'] = 'mm-dd-yyyy';

        // Estonian
        $supportedLanguages['et']['description'] = $clang->gT('Estonian');
        $supportedLanguages['et']['nativedescription'] = 'Eesti';
	    $supportedLanguages['et']['rtl'] = false;
        $supportedLanguages['et']['dateformatphp'] = 'j.n.Y';
        $supportedLanguages['et']['dateformat'] = 'd.m.yyyy';        

	    // Finnish
	    $supportedLanguages['fi']['description'] = $clang->gT('Finnish');
	    $supportedLanguages['fi']['nativedescription'] = 'Suomi';
	    $supportedLanguages['fi']['rtl'] = false;
        $supportedLanguages['fi']['dateformatphp'] = 'j.n.Y';
        $supportedLanguages['fi']['dateformat'] = 'd.m.yyyy';        

	    // French
	    $supportedLanguages['fr']['description'] = $clang->gT('French');
	    $supportedLanguages['fr']['nativedescription'] = 'Fran&#231;ais';
	    $supportedLanguages['fr']['rtl'] = false;
        $supportedLanguages['fr']['dateformatphp'] = 'd-m-Y';
        $supportedLanguages['fr']['dateformat'] = 'dd-mm-yyyy';
        

        // Galician
        $supportedLanguages['gl']['description'] = $clang->gT('Galician');
        $supportedLanguages['gl']['nativedescription'] = 'Galego';
	    $supportedLanguages['gl']['rtl'] = false;
        $supportedLanguages['gl']['dateformatphp'] = 'd/m/Y';
        $supportedLanguages['gl']['dateformat'] = 'dd/mm/yyyy';
        

   	    // German
	    $supportedLanguages['de']['description'] = $clang->gT('German');
	    $supportedLanguages['de']['nativedescription'] = 'Deutsch (Sie)';
	    $supportedLanguages['de']['rtl'] = false;
        $supportedLanguages['de']['dateformatphp'] = 'd.m.Y';
        $supportedLanguages['de']['dateformat'] = 'tt.mm.jjjj';

        // German informal
	    $supportedLanguages['de-informal']['description'] = $clang->gT('German informal');
	    $supportedLanguages['de-informal']['nativedescription'] = 'Deutsch (Du)';
	    $supportedLanguages['de-informal']['rtl'] = false;
        $supportedLanguages['de-informal']['dateformatphp'] = 'd.m.Y';
        $supportedLanguages['de-informal']['dateformat'] = 'tt.mm.jjjj';

	    // Greek
	    $supportedLanguages['el']['description'] = $clang->gT('Greek');
	    $supportedLanguages['el']['nativedescription'] = '&#949;&#955;&#955;&#951;&#957;&#953;&#954;&#940;';
	    $supportedLanguages['el']['rtl'] = false;
        $supportedLanguages['el']['dateformatphp'] = 'j/n/Y';
        $supportedLanguages['el']['dateformat'] = 'd/m/yyyy';    
        
  	    // Hindi
	    $supportedLanguages['hi']['description'] = $clang->gT('Hindi');
	    $supportedLanguages['hi']['nativedescription'] = '&#2361;&#2367;&#2344;&#2381;&#2342;&#2368;';
	    $supportedLanguages['hi']['rtl'] = false;
        $supportedLanguages['hi']['dateformatphp'] = 'd-m-Y';
        $supportedLanguages['hi']['dateformat'] = 'dd-mm-yyyy';
                     
	    // Hebrew
	    $supportedLanguages['he']['description'] = $clang->gT('Hebrew');
	    $supportedLanguages['he']['nativedescription'] = ' &#1506;&#1489;&#1512;&#1497;&#1514;';
	    $supportedLanguages['he']['rtl'] = true;
        $supportedLanguages['he']['dateformatphp'] = 'd/m/Y';
        $supportedLanguages['he']['dateformat'] = 'dd/mm/yyyy';

	    // Hungarian
	    $supportedLanguages['hu']['description'] = $clang->gT('Hungarian');
	    $supportedLanguages['hu']['nativedescription'] = 'Magyar';
	    $supportedLanguages['hu']['rtl'] = false;
        $supportedLanguages['hu']['dateformatphp'] = 'Y-m-d';
        $supportedLanguages['hu']['dateformat'] = 'yyyy-mm-dd';
        

	    // Icelandic
	    $supportedLanguages['is']['description'] = $clang->gT('Icelandic');
	    $supportedLanguages['is']['nativedescription'] = '&#237;slenska';
	    $supportedLanguages['is']['rtl'] = false;
        $supportedLanguages['is']['dateformatphp'] = 'd.m.Y';
        $supportedLanguages['is']['dateformat'] = 'dd.mm.yyyy';

        
	    // Indonesian
	    $supportedLanguages['id']['description'] = $clang->gT('Indonesian');
	    $supportedLanguages['id']['nativedescription'] = 'Bahasa Indonesia';
	    $supportedLanguages['id']['rtl'] = false;
        $supportedLanguages['id']['dateformatphp'] = 'd/m/Y';
        $supportedLanguages['id']['dateformat'] = 'dd/mm/yyyy';
        

	    // Italian
	    $supportedLanguages['it']['description'] = $clang->gT('Italian');
	    $supportedLanguages['it']['nativedescription'] = 'Italiano';
	    $supportedLanguages['it']['rtl'] = false;
        $supportedLanguages['it']['dateformatphp'] = 'd/m/Y';
        $supportedLanguages['it']['dateformat'] = 'dd/mm/yyyy';

	    // Japanese
	    $supportedLanguages['ja']['description'] = $clang->gT('Japanese');
	    $supportedLanguages['ja']['nativedescription'] = '&#x65e5;&#x672c;&#x8a9e;';
	    $supportedLanguages['ja']['rtl'] = false;
        $supportedLanguages['ja']['dateformatphp'] = 'Y-m-d';
        $supportedLanguages['ja']['dateformat'] = 'yyyy-mm-dd';

	    // Korean
	    $supportedLanguages['ko']['description'] = $clang->gT('Korean');
	    $supportedLanguages['ko']['nativedescription'] = '&#54620;&#44397;&#50612;';
	    $supportedLanguages['ko']['rtl'] = false;
        $supportedLanguages['ko']['dateformatphp'] = 'Y/m/d';
        $supportedLanguages['ko']['dateformat'] = 'yyyy/mm/dd';        

	    // Lithuanian
	    $supportedLanguages['lt']['description'] = $clang->gT('Lithuanian');
	    $supportedLanguages['lt']['nativedescription'] = 'Lietuvi&#371;';
	    $supportedLanguages['lt']['rtl'] = false;
        $supportedLanguages['lt']['dateformatphp'] = 'Y-m-d';
        $supportedLanguages['lt']['dateformat'] = 'yyyy-mm-dd';        

        // Latvian
        $supportedLanguages['lv']['description'] = $clang->gT('Latvian');
        $supportedLanguages['lv']['nativedescription'] = 'Latvie&#353;u';
        $supportedLanguages['lv']['rtl'] = false;	    
        $supportedLanguages['lv']['dateformatphp'] = 'Y-m-d';
        $supportedLanguages['lv']['dateformat'] = 'yyyy-mm-dd';        

	    // Macedonian
	    $supportedLanguages['mk']['description'] = $clang->gT('Macedonian');
	    $supportedLanguages['mk']['nativedescription'] = '&#1052;&#1072;&#1082;&#1077;&#1076;&#1086;&#1085;&#1089;&#1082;&#1080;';
	    $supportedLanguages['mk']['rtl'] = false;
        $supportedLanguages['mk']['dateformatphp'] = 'd.m.Y';
        $supportedLanguages['mk']['dateformat'] = 'dd.mm.yyyy';
        
   
	    // Norwegian Bokmal
	    $supportedLanguages['nb']['description'] = $clang->gT('Norwegian (Bokmal)');
	    $supportedLanguages['nb']['nativedescription'] = 'Norsk Bokm&#229;l';
	    $supportedLanguages['nb']['rtl'] = false;
        $supportedLanguages['nb']['dateformatphp'] = 'j.n.Y';
        $supportedLanguages['nb']['dateformat'] = 'd.m.yyyy';        
        

	    // Norwegian Nynorsk 
	    $supportedLanguages['nn']['description'] = $clang->gT('Norwegian (Nynorsk)');
	    $supportedLanguages['nn']['nativedescription'] = 'Norsk Nynorsk';
	    $supportedLanguages['nn']['rtl'] = false;
        $supportedLanguages['nn']['dateformatphp'] = 'j.n.Y';
        $supportedLanguages['nn']['dateformat'] = 'd.m.yyyy';        

	    // Persian
	    $supportedLanguages['fa']['description'] = $clang->gT('Persian');
	    $supportedLanguages['fa']['nativedescription'] = '&#1601;&#1575;&#1585;&#1587;&#1740;';
	    $supportedLanguages['fa']['rtl'] = true;
        $supportedLanguages['fa']['dateformatphp'] = 'Y-m-d';
        $supportedLanguages['fa']['dateformat'] = 'yyyy-mm-dd';

        // Polish
        $supportedLanguages['pl']['description'] = $clang->gT('Polish');
        $supportedLanguages['pl']['nativedescription'] = 'Polski';
	    $supportedLanguages['pl']['rtl'] = false;
        $supportedLanguages['pl']['dateformatphp'] = 'd.m.Y';
        $supportedLanguages['pl']['dateformat'] = 'dd.mm.yyyy';
 
 	    // Portuguese
	    $supportedLanguages['pt']['description'] = $clang->gT('Portuguese');
	    $supportedLanguages['pt']['nativedescription'] = 'Portugu&#234;s';
	    $supportedLanguages['pt']['rtl'] = false;
        $supportedLanguages['pt']['dateformatphp'] = 'd/m/Y';
        $supportedLanguages['pt']['dateformat'] = 'dd/mm/yyyy';

	    // Brazilian Portuguese
	    $supportedLanguages['pt-BR']['description'] = $clang->gT('Portuguese (Brazilian)');
	    $supportedLanguages['pt-BR']['nativedescription'] = 'Portugu&#234;s do Brasil';
	    $supportedLanguages['pt-BR']['rtl'] = false;
        $supportedLanguages['pt-BR']['dateformatphp'] = 'd/m/Y';
        $supportedLanguages['pt-BR']['dateformat'] = 'dd/mm/yyyy';

	    // Russian
	    $supportedLanguages['ru']['description'] = $clang->gT('Russian');
	    $supportedLanguages['ru']['nativedescription'] = '&#1056;&#1091;&#1089;&#1089;&#1082;&#1080;&#1081;';
	    $supportedLanguages['ru']['rtl'] = false;
        $supportedLanguages['ru']['dateformatphp'] = 'd.m.Y';
        $supportedLanguages['ru']['dateformat'] = 'dd.mm.yyyy';

	    // Romanian
	    $supportedLanguages['ro']['description'] = $clang->gT('Romanian');
	    $supportedLanguages['ro']['nativedescription'] = 'Rom&#226;nesc';
	    $supportedLanguages['ro']['rtl'] = false;
        $supportedLanguages['ro']['dateformatphp'] = 'd.m.Y';
        $supportedLanguages['ro']['dateformat'] = 'dd.mm.yyyy';
 
 	    // Slovak
	    $supportedLanguages['sk']['description'] = $clang->gT('Slovak');
	    $supportedLanguages['sk']['nativedescription'] = 'Slov&aacute;k';
	    $supportedLanguages['sk']['rtl'] = false;
        $supportedLanguages['sk']['dateformatphp'] = 'j.n.Y';
        $supportedLanguages['sk']['dateformat'] = 'd.m.yyyy';

 	    // Sinhala
	    $supportedLanguages['si']['description'] = $clang->gT('Sinhala');
	    $supportedLanguages['si']['nativedescription'] = '&#3523;&#3538;&#3458;&#3524;&#3517;';
	    $supportedLanguages['si']['rtl'] = false;
        $supportedLanguages['si']['dateformatphp'] = 'd/m/Y';
        $supportedLanguages['si']['dateformat'] = 'dd/mm/yyyy';

	    // Slovenian
	    $supportedLanguages['sl']['description'] = $clang->gT('Slovenian');
	    $supportedLanguages['sl']['nativedescription'] = 'Sloven&#353;&#269;ina';
	    $supportedLanguages['sl']['rtl'] = false;
        $supportedLanguages['sl']['dateformatphp'] = 'j.n.Y';
        $supportedLanguages['sl']['dateformat'] = 'd.m.yyyy';

        // Serbian
        $supportedLanguages['sr']['description'] = $clang->gT('Serbian');
        $supportedLanguages['sr']['nativedescription'] = 'Srpski';
	    $supportedLanguages['sr']['rtl'] = false;
        $supportedLanguages['sr']['dateformatphp'] = 'j.n.Y';
        $supportedLanguages['sr']['dateformat'] = 'd.m.yyyy';

	    // Spanish
	    $supportedLanguages['es']['description'] = $clang->gT('Spanish');
	    $supportedLanguages['es']['nativedescription'] = 'Espa&#241;ol';
	    $supportedLanguages['es']['rtl'] = false;
        $supportedLanguages['es']['dateformatphp'] = 'd/m/Y';
        $supportedLanguages['es']['dateformat'] = 'dd/mm/yyyy';

	    // Spanish (Mexico)
	    $supportedLanguages['es-MX']['description'] = $clang->gT('Spanish (Mexico)');
	    $supportedLanguages['es-MX']['nativedescription'] = 'Espa&#241;ol Mejicano';
	    $supportedLanguages['es-MX']['rtl'] = false;
        $supportedLanguages['es-MX']['dateformatphp'] = 'd/m/Y';
        $supportedLanguages['es-MX']['dateformat'] = 'dd/mm/yyyy';

	    // Swedish
	    $supportedLanguages['sv']['description'] = $clang->gT('Swedish');
	    $supportedLanguages['sv']['nativedescription'] = 'Svenska';
	    $supportedLanguages['sv']['rtl'] = false;
        $supportedLanguages['sv']['dateformatphp'] = 'Y-m-d';
        $supportedLanguages['sv']['dateformat'] = 'yyyy-mm-dd';        

	    // Turkish
	    $supportedLanguages['tr']['description'] = $clang->gT('Turkish');
	    $supportedLanguages['tr']['nativedescription'] = 'T&#252;rk&#231;e';
	    $supportedLanguages['tr']['rtl'] = false;
        $supportedLanguages['tr']['dateformatphp'] = 'd/m/Y';
        $supportedLanguages['tr']['dateformat'] = 'dd/mm/yyyy';

	    // Thai
	    $supportedLanguages['th']['description'] = $clang->gT('Thai');
	    $supportedLanguages['th']['nativedescription'] = '&#3616;&#3634;&#3625;&#3634;&#3652;&#3607;&#3618;';
	    $supportedLanguages['th']['rtl'] = false;
        $supportedLanguages['th']['dateformatphp'] = 'd/m/Y';
        $supportedLanguages['th']['dateformat'] = 'dd/mm/yyyy';


	    // Vietnamese
	    $supportedLanguages['vi']['description'] = $clang->gT('Vietnamese');
	    $supportedLanguages['vi']['nativedescription'] = 'Ti&#7871;ng Vi&#7879;t';
	    $supportedLanguages['vi']['rtl'] = false;
        $supportedLanguages['vi']['dateformatphp'] = 'd/m/Y';
        $supportedLanguages['vi']['dateformat'] = 'dd/mm/yyyy';

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
                                                                            
        // Serbian
	    $supportedLanguages['yu']['nativedescription'] = 'Srpski';


*/
?>
