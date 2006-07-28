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



    function getLanguageNameFromCode($codetosearch)
    {
        $detaillanguages = getLanguageData();
        if (isset($detaillanguages[$codetosearch]['description']))
        {
              return $detaillanguages[$codetosearch]['description'];
        }
          else
        // else return default en code
        return "English";
    }


    function getLanguageData() {
	    /* English */
        unset($supportedLanguages);
	    $supportedLanguages['en']['description'] = 'English';
	    $supportedLanguages['en']['nativedescription'] = 'English';

	    // German
	    $supportedLanguages['de']['description'] = 'German';
	    $supportedLanguages['de']['nativedescription'] = 'Deutsch (Sie)';

        // German informal
	    $supportedLanguages['de_informal']['description'] = 'German informal';
	    $supportedLanguages['de_informal']['nativedescription'] = 'Deutsch (Du)';


/*
	    // Afrikaans
 	    $supportedLanguages['af']['ZA']['description'] = 'Afrikaans';
	    $defaultCountry['af'] = 'ZA';

        // Czech
	    $supportedLanguages['cs']['CZ']['description'] = '&#x010c;esky';
	    $defaultCountry['cs'] = 'CZ';

	    // Danish
	    $supportedLanguages['da']['DK']['description'] = 'Dansk';
	    $defaultCountry['da'] = 'DK';

	    // Spanish
	    $supportedLanguages['es']['ES']['description'] = 'Espa&#241;ol';
	    $supportedLanguages['es']['MX']['description'] = 'Espa&#241;ol (MX)';
	    $supportedLanguages['es']['AR']['description'] = 'Espa&#241;ol (AR)';
	    $defaultCountry['es'] = 'ES';

	    // Basque
	    $supportedLanguages['eu']['ES']['description'] = 'Euskara';
	    $defaultCountry['eu'] = 'ES';

	    // French
	    $supportedLanguages['fr']['FR']['description'] = 'Fran&#231;ais';
	    $defaultCountry['fr'] = 'FR';

	    // Irish
	    $supportedLanguages['ga']['IE']['description'] = 'Gaeilge';
	    $defaultCountry['ga'] = 'IE';

	    // Greek
	    $supportedLanguages['el']['GR']['description'] = 'Greek';
	    $defaultCountry['el'] = 'GR';

	    // Icelandic
	    $supportedLanguages['is']['IS']['description'] = 'Icelandic';
	    $defaultCountry['is'] = 'IS';

	    // Italian
	    $supportedLanguages['it']['IT']['description'] = 'Italiano';
	    $defaultCountry['it'] = 'IT';

	    // Latvian
	    $supportedLanguages['lv']['LV']['description'] = 'Latvie&#353;u';
	    $defaultCountry['lv'] = 'LV';

	    // Lithuanian
	    $supportedLanguages['lt']['LT']['description'] = 'Lietuvi&#371;';
	    $defaultCountry['lt'] = 'LT';

	    // Hungarian
	    $supportedLanguages['hu']['HU']['description'] = 'Magyar';
	    $defaultCountry['hu'] = 'HU';

	    // Dutch
	    $supportedLanguages['nl']['NL']['description'] = 'Nederlands';
	    $defaultCountry['nl'] = 'NL';

	    // Norwegian
	    $supportedLanguages['no']['NO']['description'] = 'Norsk bokm&#229;l';
	    $defaultCountry['no'] = 'NO';

	    // Polish
	    $supportedLanguages['pl']['PL']['description'] = 'Polski';
	    $defaultCountry['pl'] = 'PL';

	    // Portuguese
	    $supportedLanguages['pt']['BR']['description'] = 'Portugu&#234;s Brasileiro';
	    $supportedLanguages['pt']['PT']['description'] = 'Portugu&#234;s';
	    $defaultCountry['pt'] = 'BR';

	    // Slovenian
	    $supportedLanguages['sl']['SI']['description'] = 'Sloven&#353;&#269;ina';
	    $defaultCountry['sl'] = 'SI';

	    // Serbian
	    $supportedLanguages['sr']['YU']['description'] = 'Srpski';
	    $defaultCountry['sr'] = 'YU';

	    // Finnish
	    $supportedLanguages['fi']['FI']['description'] = 'Suomi';
	    $defaultCountry['fi'] = 'FI';

	    // Swedish
	    $supportedLanguages['sv']['SE']['description'] = 'Svenska';
	    $defaultCountry['sv'] = 'SE';

	    // Thai
	    $supportedLanguages['th']['TH']['description'] = 'Thai';
	    $defaultCountry['th'] = 'TH';

	    // Vietnamese
	    $supportedLanguages['vi']['VN']['description'] = 'Ti&#7871;ng Vi&#7879;t';
	    $defaultCountry['vi'] = 'VN';

	    // Turkish
	    $supportedLanguages['tr']['TR']['description'] = 'T&#252;rk&#231;e';
	    $defaultCountry['tr'] = 'TR';

	    // Bulgarian
	    $supportedLanguages['bg']['BG']['description'] =
		'&#x0411;&#x044a;&#x043b;&#x0433;&#x0430;&#x0440;&#x0441;&#x043a;&#x0438;';
	    $defaultCountry['bg'] = 'BG';

	    // Russian
	    $supportedLanguages['ru']['RU']['description'] =
		'&#1056;&#1091;&#1089;&#1089;&#1082;&#1080;&#1081;';
	    $defaultCountry['ru'] = 'RU';

	    // Chinese
	    $supportedLanguages['zh']['CN']['description'] = '&#31616;&#20307;&#20013;&#25991;';
	    $supportedLanguages['zh']['TW']['description'] = '&#32321;&#39636;&#20013;&#25991;';
	    $defaultCountry['zh'] = 'CN';

	    // Japanese
	    $supportedLanguages['ja']['JP']['description'] = '&#x65e5;&#x672c;&#x8a9e;';
	    $defaultCountry['ja'] = 'JP';

	    // Arabic
	    $supportedLanguages['ar']['SA']['description'] =
		'&#1575;&#1604;&#1593;&#1585;&#1576;&#1610;&#1577;';
	    $supportedLanguages['ar']['SA']['right-to-left'] = true;
	    $defaultCountry['ar'] = 'SA';

	    // Hebrew
	    $supportedLanguages['he']['IL']['description'] = '&#1506;&#1489;&#1512;&#1497;&#1514;';
	    $supportedLanguages['he']['IL']['right-to-left'] = true;
	    $defaultCountry['he'] = 'IL';*/

	return $supportedLanguages;
    }

    /**
     * Return the list of languages that we support.
     * Return our language data
     *
     * @return array['language code']['country code'] =
     *              array('description', 'right-to-left'?)
     */
?>
