<?php
/*
 * $RCSfile: SurveyTranslator.class,v $
 *
 * PHPSurveyor
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or (at
 * your option) any later version.
 *
 * This program is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street - Fifth Floor, Boston, MA  02110-1301, USA.
 *
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
        unset($supportedLanguages);

	    // Bulgarian
	    $supportedLanguages['bg']['description'] = 'Bulgarian';
	    $supportedLanguages['bg']['nativedescription'] = '&#x0411;&#x044a;&#x043b;&#x0433;&#x0430;&#x0440;&#x0441;&#x043a;&#x0438;';

        // Chinese (Simplified)
	    $supportedLanguages['cnt']['description'] = 'Chinese (Traditional)';
	    $supportedLanguages['cnt']['nativedescription'] = '&#31616;&#20307;&#20013;&#25991;';

        // Chinese (Traditional)
	    $supportedLanguages['cns']['description'] = 'Chinese (Simplified)';
	    $supportedLanguages['cns']['nativedescription'] = '&#31616;&#20307;&#20013;&#25991;';

        // Croatian
	    $supportedLanguages['hr']['description'] = 'Croatian';
	    $supportedLanguages['hr']['nativedescription'] = 'Croatian';

	    // Danish
	    $supportedLanguages['da']['description'] = 'Danish';
	    $supportedLanguages['da']['nativedescription'] = 'Dansk';

	    // Dutch
	    $supportedLanguages['nl']['description'] = 'Dutch';
	    $supportedLanguages['nl']['nativedescription'] = 'Nederlands';

	    /* English */
	    $supportedLanguages['en']['description'] = 'English';
	    $supportedLanguages['en']['nativedescription'] = 'English';

	    // Finnish
	    $supportedLanguages['fi']['description'] = 'Finnish';
	    $supportedLanguages['fi']['nativedescription'] = 'Suomi';


	    // French
	    $supportedLanguages['fr']['description'] = 'French';
	    $supportedLanguages['fr']['nativedescription'] = 'Fran&#231;ais';

   	    // German
	    $supportedLanguages['de']['description'] = 'German';
	    $supportedLanguages['de']['nativedescription'] = 'Deutsch (Sie)';

        // German informal
	    $supportedLanguages['de_informal']['description'] = 'German informal';
	    $supportedLanguages['de_informal']['nativedescription'] = 'Deutsch (Du)';

	    // Greek
	    $supportedLanguages['gr']['description'] = 'Greek';
	    $supportedLanguages['gr']['nativedescription'] = '&#949;&#955;&#955;&#951;&#957;&#953;&#954;&#940;';

	    // Hebrew
	    $supportedLanguages['he']['description'] = 'Hebrew';
	    $supportedLanguages['he']['nativedescription'] = ' &#1506;&#1489;&#1512;&#1497;&#1514;';

	    // Hungarian
	    $supportedLanguages['hu']['description'] = 'Hungarian';
	    $supportedLanguages['hu']['nativedescription'] = 'Magyar';

	    // Italian
	    $supportedLanguages['it']['description'] = 'Italian';
	    $supportedLanguages['it']['nativedescription'] = 'Italiano';

	    // Japanese
	    $supportedLanguages['jp']['description'] = 'Japanese';
	    $supportedLanguages['jp']['nativedescription'] = '&#x65e5;&#x672c;&#x8a9e;';

	    // Lithuanian
	    $supportedLanguages['lt']['description'] = 'Lithuanian';
	    $supportedLanguages['lt']['nativedescription'] = 'Lietuvi&#371;';
	    
	    // Norwegian
	    $supportedLanguages['no']['description'] = 'Norwegian';
	    $supportedLanguages['no']['nativedescription'] = 'Norsk bokm&#229;l';

	    // Portuguese
	    $supportedLanguages['pt']['description'] = 'Portuguese';
	    $supportedLanguages['pt']['nativedescription'] = 'Portugu&#234;s';

	    // Brazilian Portuguese
	    $supportedLanguages['pt_br']['description'] = 'Portuguese (Brazilian)';
	    $supportedLanguages['pt_br']['nativedescription'] = 'Portugu&#234;s do Brasil';


	    // Russian
	    $supportedLanguages['ru']['description'] = 'Russian';
	    $supportedLanguages['ru']['nativedescription'] = '&#1056;&#1091;&#1089;&#1089;&#1082;&#1080;&#1081;';

	    // Romanian
	    $supportedLanguages['ro']['description'] = 'Romanian';
	    $supportedLanguages['ro']['nativedescription'] = 'Romanian?';

	    // Slovenian
	    $supportedLanguages['si']['description'] = 'Slovenian';
	    $supportedLanguages['si']['nativedescription'] = 'Sloven&#353;&#269;ina';

	    // Spanish
	    $supportedLanguages['es']['description'] = 'Spanish';
	    $supportedLanguages['es']['nativedescription'] = 'Espa&#241;ol';

	    // Swedish
	    $supportedLanguages['se']['description'] = 'Swedish';
	    $supportedLanguages['se']['nativedescription'] = 'Svenska';

	    // Vietnamese
	    $supportedLanguages['vn']['description'] = 'Vietnamese';
	    $supportedLanguages['vn']['nativedescription'] = 'Ti&#7871;ng Vi&#7879;t';


        Return $supportedLanguages;
    }
/*    // future languages

	    // Afrikaans
 	    $supportedLanguages['za']['description'] = 'Afrikaans';

        // Czech
	    $supportedLanguages['cz']['description'] = '&#x010c;esky';

	    // Irish
	    $supportedLanguages['ie']['description'] = 'Gaeilge';

	    // Icelandic
	    $supportedLanguages['is']['description'] = 'Icelandic';

	    // Latvian
	    $supportedLanguages['lv']['description'] = 'Latvie&#353;u';

	    // Polish
	    $supportedLanguages['pl']['description'] = 'Polski';

	    // Serbian
	    $supportedLanguages['yu']['description'] = 'Srpski';

	    // Thai
	    $supportedLanguages['th']['description'] = 'Thai';

	    // Turkish
	    $supportedLanguages['tr']['description'] = 'T&#252;rk&#231;e';

	    // Arabic
	    $supportedLanguages['sa']['description'] = '&#1575;&#1604;&#1593;&#1585;&#1576;&#1610;&#1577;';
	    $supportedLanguages['sa']['right-to-left'] = true;

	    // Hebrew
	    $supportedLanguages['il']['description'] = '&#1506;&#1489;&#1512;&#1497;&#1514;';
	    $supportedLanguages['il']['right-to-left'] = true;

     */
?>
