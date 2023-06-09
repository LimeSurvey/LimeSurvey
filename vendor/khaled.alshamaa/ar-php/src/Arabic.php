<?php

namespace ArPHP\I18N;

/**
 * ----------------------------------------------------------------------
 *
 * Copyright (c) 2006-2023 Khaled Al-Sham'aa.
 *
 * http://www.ar-php.org
 *
 * PHP Version >= 5.6
 *
 * ----------------------------------------------------------------------
 *
 * LICENSE
 *
 * This program is open source product; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public License (LGPL)
 * as published by the Free Software Foundation; either version 3
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/lgpl.txt>.
 *
 * ----------------------------------------------------------------------
 * Class Name: PHP and Arabic Language
 *
 * Filename:   ar-php.php
 *
 * Original    Author(s): Khaled Al-Sham'aa <khaled@ar-php.org>
 *
 * Purpose:    Set of PHP classes developed to enhance Arabic web
 *             applications by providing set of tools includes stem-based searching,
 *             translitiration, soundex, Hijri calendar, charset detection and
 *             converter, spell numbers, keyboard language, Muslim prayer time,
 *             auto-summarization, and more...
 *
 * ----------------------------------------------------------------------
 *
 * @desc   Set of PHP classes developed to enhance Arabic web
 *         applications by providing set of tools includes translitiration,
 *         soundex, Hijri calendar, spell numbers, keyboard language, and more...
 *
 * @author    Khaled Al-Shamaa <khaled@ar-php.org>
 * @copyright 2006-2023 Khaled Al-Shamaa
 *
 * @license   LGPL <http://www.gnu.org/licenses/lgpl.txt>
 * @version   6.3.4 released in Apr 5, 2023
 * @link      http://www.ar-php.org
 */
 
class Arabic
{
    /** @var string */
    public $version = '6.3.4';
    
    /** @var array<string> */
    private $arStandardPatterns = array();

    /** @var array<string> */
    private $arStandardReplacements = array();
    
    /** @var array<string> */
    private $arFemaleNames = array();
    
    /** @var array<string> */
    private $arMaleNames = array();
    
    /** @var array<string> */
    private $strToTimeSearch = array();

    /** @var array<string> */
    private $strToTimeReplace = array();

    /** @var array<string> */
    private $hj = array();
    
    /** @var array<string> */
    private $strToTimePatterns = array();

    /** @var array<string> */
    private $strToTimeReplacements = array();
    
    /** @var string|false */
    private $umAlqoura;
    
    /** @var array<string> */
    private $arFinePatterns = array("/'+/u", "/([\- ])'/u", '/(.)#/u');

    /** @var array<string> */
    private $arFineReplacements = array("'", '\\1', "\\1'\\1");
    
    /** @var array<string> */
    private $diariticalSearch = array();

    /** @var array<string> */
    private $diariticalReplace = array();
    
    /** @var array<string> */
    private $en2arPregSearch = array();

    /** @var array<string> */
    private $en2arPregReplace = array();

    /** @var array<string> */
    private $en2arStrSearch = array();

    /** @var array<string> */
    private $en2arStrReplace = array();
    
    /** @var array<string> */
    private $ar2enPregSearch = array();

    /** @var array<string> */
    private $ar2enPregReplace = array();

    /** @var array<string> */
    private $ar2enStrSearch = array();

    /** @var array<string> */
    private $ar2enStrReplace = array();
    
    /** @var array<string> */
    private $iso233Search = array();

    /** @var array<string> */
    private $iso233Replace = array();
    
    /** @var array<string> */
    private $rjgcSearch = array();

    /** @var array<string> */
    private $rjgcReplace = array();
    
    /** @var array<string> */
    private $sesSearch = array();

    /** @var array<string> */
    private $sesReplace = array();
    
    /** @var int */
    private $arDateMode = 1;

    /** @var array<array<string|array<string>>> */
    private $arDateJSON = array();
    
    /** @var array<string|array<string|array<string>>> */
    private $arNumberIndividual = array();

    /** @var array<array<string>> */
    private $arNumberComplications = array();

    /** @var array<string> */
    private $arNumberArabicIndic = array();

    /** @var array<string> */
    private $arNumberOrdering = array();

    /** @var array<array<string|array<string>>> */
    private $arNumberCurrency = array();

    /** @var array<int> */
    private $arNumberSpell = array();

    /** @var int */
    private $arNumberFeminine = 1;

    /** @var int */
    private $arNumberFormat = 1;

    /** @var int */
    private $arNumberOrder = 1;

    /** @var array<array<string>> */
    private $arLogodd;

    /** @var array<array<string>> */
    private $enLogodd;

    /** @var array<string> */
    private $arKeyboard = array();

    /** @var array<string> */
    private $enKeyboard = array();

    /** @var array<string> */
    private $frKeyboard = array();
    
    /** @var array<string> */
    private $soundexTransliteration = array();

    /** @var array<string> */
    private $soundexMap = array();
    
    /** @var array<string> */
    private $arSoundexCode = array();

    /** @var array<string> */
    private $arPhonixCode = array();

    /** @var int */
    private $soundexLen = 4;

    /** @var string */
    private $soundexLang = 'en';

    /** @var string */
    private $soundexCode = 'soundex';

    /** @var array<array<string>> */
    private $arGlyphs = null;

    /** @var null|string */
    private $arGlyphsVowel = null;

    /** @var array<string> */
    private $arQueryFields = array();

    /** @var array<string> */
    private $arQueryLexPatterns = array();

    /** @var array<string> */
    private $arQueryLexReplacements = array();

    /** @var int */
    private $arQueryMode = 0;

    /** @var int */
    private $salatYear = 1975;

    /** @var int */
    private $salatMonth = 8;

    /** @var int */
    private $salatDay = 2;

    /** @var int */
    private $salatZone = 2;

    /** @var float */
    private $salatLong = 37.15861;

    /** @var float */
    private $salatLat = 36.20278;

    /** @var int */
    private $salatElevation = 0;

    /** @var float */
    private $salatAB2 = -0.833333;

    /** @var float */
    private $salatAG2 = -18;

    /** @var float */
    private $salatAJ2 = -18;

    /** @var string */
    private $salatSchool = 'Shafi';

    /** @var string */
    private $salatView   = 'Sunni';

    /** @var array<string> */
    private $arNormalizeAlef = array('أ','إ','آ');

    /** @var array<string> */
    private $arNormalizeDiacritics = array('َ','ً','ُ','ٌ','ِ','ٍ','ْ','ّ');

    /** @var array<string> */
    private $arSeparators = array('.',"\n",'،','؛','(','[','{',')',']','}',',',';');

    /** @var array<string> */
    private $arCommonChars = array('ة','ه','ي','ن','و','ت','ل','ا','س','م',
                                   'e', 't', 'a', 'o', 'i', 'n', 's');

    /** @var array<string> */
    private $arSummaryCommonWords = array();

    /** @var array<string> */
    private $arSummaryImportantWords = array();
    
    /** @var array<string> */
    private $arPluralsForms = array();
    
    /** @var array<string> */
    private $logOdd = array();

    /** @var array<string> */
    private $logOddStem = array();

    /** @var array<string> */
    private $allStems = array();
    
    /** @var string */
    private $rootDirectory;

    /** @var boolean */
    private $stripTatweel = true;
    
    /** @var boolean */
    private $stripTanween = true;
    
    /** @var boolean */
    private $stripShadda = true;
    
    /** @var boolean */
    private $stripLastHarakat = true;
    
    /** @var boolean */
    private $stripWordHarakat = true;
    
    /** @var boolean */
    private $normaliseLamAlef = true;

    /** @var boolean */
    private $normaliseAlef = true;

    /** @var boolean */
    private $normaliseHamza = true;

    /** @var boolean */
    private $normaliseTaa = true;

    /** @var array<string> */
    private $numeralHindu = array('٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩');

    /** @var array<string> */
    private $numeralPersian = array('۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹');

    /** @var array<string> */
    private $numeralArabic = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9');


    public function __construct()
    {
        mb_internal_encoding('UTF-8');
        
        $this->rootDirectory = dirname(__FILE__);
        $this->arFemaleNames = file($this->rootDirectory . '/data/ar_female.txt', FILE_IGNORE_NEW_LINES);
        $this->arMaleNames = file($this->rootDirectory . '/data/ar_male.txt', FILE_IGNORE_NEW_LINES);
        $this->umAlqoura  = file_get_contents($this->rootDirectory . '/data/um_alqoura.txt');
        $this->arDateJSON = json_decode((string)file_get_contents($this->rootDirectory . '/data/ar_date.json'), true);

        $json = json_decode(file_get_contents($this->rootDirectory . '/data/ar_plurals.json'), true);
        $this->arPluralsForms = $json['arPluralsForms'];

        $this->arStandardInit();
        $this->arStrToTimeInit();
        $this->arTransliterateInit();
        $this->arNumbersInit();
        $this->arKeySwapInit();
        $this->arSoundexInit();
        $this->arGlyphsInit();
        $this->arQueryInit();
        $this->arSummaryInit();
        $this->arSentimentInit();
    }
    
    /** @return void */
    private function arStandardInit()
    {
        $this->arStandardPatterns[] = '/\r\n/u';
        $this->arStandardPatterns[] = '/([^\@])\n([^\@])/u';
        $this->arStandardPatterns[] = '/\r/u';
        
        $this->arStandardReplacements[] = "\n@@@\n";
        $this->arStandardReplacements[] = "\\1\n&&&\n\\2";
        $this->arStandardReplacements[] = "\n###\n";
        
        /**
         * النقطة، الفاصلة، الفاصلة المنقوطة،
         * النقطتان، علامتي الاستفهام والتعجب،
         * النقاط الثلاث المتتالية
         * يترك فراغ واحد بعدها جميعا
         * دون أي فراغ قبلها
         */
        $this->arStandardPatterns[] = '/\s*([\.\،\؛\:\!\؟])\s*/u';
        $this->arStandardReplacements[] = '\\1 ';
        
        /**
         * النقاط المتتالية عددها 3 فقط
         * (ليست نقطتان وليست أربع أو أكثر)
         */
        $this->arStandardPatterns[] = '/(\. ){2,}/u';
        $this->arStandardReplacements[] = '...';
        
        /**
         * الأقواس ( ) [ ] { } يترك قبلها وبعدها فراغ
         * وحيد، فيما لا يوجد بينها وبين ما بداخلها
         * أي فراغ
         */
        $this->arStandardPatterns[] = '/\s*([\(\{\[])\s*/u';
        $this->arStandardPatterns[] = '/\s*([\)\}\]])\s*/u';
        
        $this->arStandardReplacements[] = ' \\1';
        $this->arStandardReplacements[] = '\\1 ';
        
        /**
         * علامات الاقتباس "..."
         * يترك قبلها وبعدها فراغ
         * وحيد، فيما لا يوجد بينها
         * وبين ما بداخلها أي فراغ
         */
        $this->arStandardPatterns[] = '/\s*\"\s*(.+)((?<!\s)\"|\s+\")\s*/u';
        $this->arStandardReplacements[] = ' "\\1" ';
        
        /**
         * علامات الإعتراض -...-
         * يترك قبلها وبعدها فراغ
         * وحيد، فيما لا يوجد بينها
         * وبين ما بداخلها أي فراغ
         */
        $this->arStandardPatterns[] = '/\s*\-\s*(.+)((?<!\s)\-|\s+\-)\s*/u';
        $this->arStandardReplacements[] = ' -\\1- ';
        
        /**
         * لا يترك فراغ بين حرف العطف الواو وبين
         * الكلمة التي تليه
         * إلا إن كانت تبدأ بحرف الواو
         */
        $this->arStandardPatterns[] = '/\sو\s+([^و])/u';
        $this->arStandardReplacements[] = ' و\\1';
        
        /**
         * الواحدات الإنجليزية توضع
         * على يمين الرقم مع ترك فراغ
         */
        $this->arStandardPatterns[] = '/\s+(\w+)\s*(\d+)\s+/';
        $this->arStandardPatterns[] = '/\s+(\d+)\s*(\w+)\s+/';
        
        $this->arStandardReplacements[] = ' <span dir="ltr">\\2 \\1</span> ';
        $this->arStandardReplacements[] = ' <span dir="ltr">\\1 \\2</span> ';
        
        /**
         * النسبة المؤية دائما إلى يسار الرقم
         * وبدون أي فراغ يفصل بينهما 40% مثلا
         */
        $this->arStandardPatterns[] = '/\s+(\d+)\s*\%\s+/u';
        $this->arStandardPatterns[] = '/\n?@@@\n?/u';
        $this->arStandardPatterns[] = '/\n?&&&\n?/u';
        $this->arStandardPatterns[] = '/\n?###\n?/u';
        
        $this->arStandardReplacements[] = ' %\\1 ';
        $this->arStandardReplacements[] = "\r\n";
        $this->arStandardReplacements[] = "\n";
        $this->arStandardReplacements[] = "\r";
    }
    
    /** @return void */
    private function arStrToTimeInit()
    {
        $this->strToTimeSearch = file($this->rootDirectory . '/data/strtotime_search.txt', FILE_IGNORE_NEW_LINES);
        $this->strToTimeReplace = file($this->rootDirectory . '/data/strtotime_replace.txt', FILE_IGNORE_NEW_LINES);
        
        foreach ($this->arDateJSON['ar_hj_month'] as $month) {
            $this->hj[] = (string)$month;
        }
        
        $this->strToTimePatterns[] = '/َ|ً|ُ|ٌ|ِ|ٍ|ْ|ّ/';
        $this->strToTimePatterns[] = '/\s*ال(\S{3,})\s+ال(\S{3,})/';
        $this->strToTimePatterns[] = '/\s*ال(\S{3,})/';
        
        $this->strToTimeReplacements[] = '';
        $this->strToTimeReplacements[] = ' \\2 \\1';
        $this->strToTimeReplacements[] = ' \\1';
    }
    
    /** @return void */
    private function arTransliterateInit()
    {
        $json = json_decode((string)file_get_contents($this->rootDirectory . '/data/ar_transliteration.json'), true);

        foreach ($json['preg_replace_en2ar'] as $item) {
            $this->en2arPregSearch[]  = $item['search'];
            $this->en2arPregReplace[] = $item['replace'];
        }

        foreach ($json['str_replace_en2ar'] as $item) {
            $this->en2arStrSearch[]  = $item['search'];
            $this->en2arStrReplace[] = $item['replace'];
        }

        foreach ($json['preg_replace_ar2en'] as $item) {
            $this->ar2enPregSearch[]  = $item['search'];
            $this->ar2enPregReplace[] = $item['replace'];
        }

        foreach ($json['str_replace_ar2en'] as $item) {
            $this->ar2enStrSearch[]  = $item['search'];
            $this->ar2enStrReplace[] = $item['replace'];
        }

        foreach ($json['str_replace_diaritical'] as $item) {
            $this->diariticalSearch[]  = $item['search'];
            $this->diariticalReplace[] = $item['replace'];
        }

        foreach ($json['str_replace_RJGC'] as $item) {
            $this->rjgcSearch[]  = $item['search'];
            $this->rjgcReplace[] = $item['replace'];
        }

        foreach ($json['str_replace_SES'] as $item) {
            $this->sesSearch[]  = $item['search'];
            $this->sesReplace[] = $item['replace'];
        }

        foreach ($json['str_replace_ISO233'] as $item) {
            $this->iso233Search[]  = $item['search'];
            $this->iso233Replace[] = $item['replace'];
        }
    }
    
    /** @return void */
    private function arNumbersInit()
    {
        $json = json_decode((string)file_get_contents($this->rootDirectory . '/data/ar_numbers.json'), true);
        
        foreach ($json['individual']['male'] as $num) {
            if (isset($num['grammar'])) {
                $grammar = $num['grammar'];
                $this->arNumberIndividual["{$num['value']}"][1]["$grammar"] = (string)$num['text'];
            } else {
                $this->arNumberIndividual["{$num['value']}"][1] = (string)$num['text'];
            }
        }
        
        foreach ($json['individual']['female'] as $num) {
            if (isset($num['grammar'])) {
                $grammar = $num['grammar'];
                $this->arNumberIndividual["{$num['value']}"][2]["$grammar"] = (string)$num['text'];
            } else {
                $this->arNumberIndividual["{$num['value']}"][2] = (string)$num['text'];
            }
        }
        
        foreach ($json['individual']['gt19'] as $num) {
            if (isset($num['grammar'])) {
                $grammar = $num['grammar'];
                $this->arNumberIndividual["{$num['value']}"]["$grammar"] = (string)$num['text'];
            } else {
                $this->arNumberIndividual["{$num['value']}"] = (string)$num['text'];
            }
        }

        foreach ($json['complications'] as $num) {
            $scale  = $num['scale'];
            $format = $num['format'];
            $this->arNumberComplications["$scale"]["$format"] = (string)$num['text'];
        }
        
        foreach ($json['arabicIndic'] as $html) {
            $value  = $html['value'];
            $this->arNumberArabicIndic["$value"] = $html['text'];
        }
        
        foreach ($json['order']['male'] as $num) {
            $this->arNumberOrdering["{$num['value']}"][1] = (string)$num['text'];
        }
        
        foreach ($json['order']['female'] as $num) {
            $this->arNumberOrdering["{$num['value']}"][2] = (string)$num['text'];
        }

        foreach ($json['individual']['male'] as $num) {
            if ($num['value'] < 11) {
                $str = strtr((string)$num['text'], array('أ' => 'ا', 'إ' => 'ا', 'آ' => 'ا'));
                $this->arNumberSpell[$str] = (int)$num['value'];
            }
        }
        
        foreach ($json['individual']['female'] as $num) {
            if ($num['value'] < 11) {
                $str = strtr((string)$num['text'], array('أ' => 'ا', 'إ' => 'ا', 'آ' => 'ا'));
                $this->arNumberSpell[$str] = (int)$num['value'];
            }
        }
        
        foreach ($json['individual']['gt19'] as $num) {
            $str = strtr((string)$num['text'], array('أ' => 'ا', 'إ' => 'ا', 'آ' => 'ا'));
            $this->arNumberSpell[$str] = (int)$num['value'];
        }
        
        foreach ($json['currency'] as $money) {
            $this->arNumberCurrency[$money['iso']]['ar']['basic']    = $money['ar_basic'];
            $this->arNumberCurrency[$money['iso']]['ar']['fraction'] = $money['ar_fraction'];
            $this->arNumberCurrency[$money['iso']]['en']['basic']    = $money['en_basic'];
            $this->arNumberCurrency[$money['iso']]['en']['fraction'] = $money['en_fraction'];
            
            $this->arNumberCurrency[$money['iso']]['decimals'] = $money['decimals'];
        }
    }
    
    /** @return void */
    private function arKeySwapInit()
    {
        $json = json_decode((string)file_get_contents($this->rootDirectory . '/data/ar_keyswap.json'), true);
        
        foreach ($json['arabic'] as $key) {
            $index = (int)$key['id'];
            $this->arKeyboard[$index] = (string)$key['text'];
        }

        foreach ($json['english'] as $key) {
            $index = (int)$key['id'];
            $this->enKeyboard[$index] = (string)$key['text'];
        }
        
        foreach ($json['french'] as $key) {
            $index = (int)$key['id'];
            $this->frKeyboard[$index] = (string)$key['text'];
        }
        
        $this->arLogodd = unserialize(file_get_contents($this->rootDirectory . '/data/logodd_ar.txt'));
        $this->enLogodd = unserialize(file_get_contents($this->rootDirectory . '/data/logodd_en.txt'));
    }
    
    /** @return void */
    private function arSoundexInit()
    {
        $json = json_decode((string)file_get_contents($this->rootDirectory . '/data/ar_soundex.json'), true);
        
        foreach ($json['arSoundexCode'] as $item) {
            $index = $item['search'];
            $this->arSoundexCode["$index"] = (string)$item['replace'];
        }
        
        foreach ($json['arPhonixCode'] as $item) {
            $index = $item['search'];
            $this->arPhonixCode["$index"] = (string)$item['replace'];
        }
        
        foreach ($json['soundexTransliteration'] as $item) {
            $index = $item['search'];
            $this->soundexTransliteration["$index"] = (string)$item['replace'];
        }
        
        $this->soundexMap = $this->arSoundexCode;
    }
    
    /** @return void */
    private function arGlyphsInit()
    {
        $this->arGlyphsVowel     = 'ًٌٍَُِّْ';
        
        // Arabic Presentation Forms-B (https://en.wikipedia.org/wiki/Arabic_Presentation_Forms-B)
        // Contextual forms (https://en.wikipedia.org/wiki/Arabic_script_in_Unicode#Contextual_forms)
        // 0- ISOLATED FORM, 1- FINAL FORM, 2- INITIAL FORM, 3- MEDIAL FORM
        $this->arGlyphs = json_decode((string)file_get_contents($this->rootDirectory . '/data/ar_glyphs.json'), true);
    }

    /** @return void */
    private function arQueryInit()
    {
        $json = json_decode((string)file_get_contents($this->rootDirectory . '/data/ar_query.json'), true);

        foreach ($json['preg_replace'] as $pair) {
            $this->arQueryLexPatterns[] = (string)$pair['search'];
            $this->arQueryLexReplacements[] = (string)$pair['replace'];
        }
    }

    /** @return void */
    private function arSummaryInit()
    {
        // This common words used in cleanCommon method
        $words    = file($this->rootDirectory . '/data/ar_stopwords.txt', FILE_IGNORE_NEW_LINES);
        $en_words = file($this->rootDirectory . '/data/en_stopwords.txt', FILE_IGNORE_NEW_LINES);

        $words = array_merge($words, $en_words);
        
        $this->arSummaryCommonWords = $words;
        
        // This important words used in rankSentences method
        $words = file($this->rootDirectory . '/data/important_words.txt', FILE_IGNORE_NEW_LINES);

        $this->arSummaryImportantWords = $words;
    }

    
    /** @return void */
    private function arSentimentInit()
    {
        $this->allStems   = file($this->rootDirectory . '/data/stems.txt', FILE_IGNORE_NEW_LINES);
        $this->logOddStem = file($this->rootDirectory . '/data/logodd_stem.txt', FILE_IGNORE_NEW_LINES);
        $this->logOdd     = file($this->rootDirectory . '/data/logodd.txt', FILE_IGNORE_NEW_LINES);
    }
    
    /////////////////////////////////////// Standard //////////////////////////////////////////////

    /**
     * This function will standardize Arabic text to follow writing standards
     * (just like magazine/newspapers rules), for example spaces before and
     * after punctuations, brackets and units etc ...
     *
     * @param string $text Arabic text you would like to standardize
     *
     * @return String Standardized version of input Arabic text
     * @author Khaled Al-Sham'aa <khaled@ar-php.org>
     */
    public function standard($text)
    {
        $text = preg_replace($this->arStandardPatterns, $this->arStandardReplacements, $text);

        return $text;
    }


    /////////////////////////////////////// Gender ////////////////////////////////////////////////

    /**
     * Arabic Gender Guesser
     *
     * This function attempts to guess the gender of Arabic names.
     *
     * Arabic nouns are either masculine or feminine. Usually when referring to a male,
     * a masculine noun is usually used and when referring to a female, a feminine noun
     * is used. In most cases the feminine noun is formed by adding a special characters
     * to the end of the masculine noun. Its not just nouns referring to people that
     * have gender. Inanimate objects (doors, houses, cars, etc.) is either masculine or
     * feminine. Whether an inanimate noun is masculine or feminine is mostly arbitrary.
     *
     * @param string $str Arabic word you would like to check if it is feminine
     *
     * @return boolean Return true if input Arabic word is feminine
     * @author Khaled Al-Sham'aa <khaled@ar-php.org>
     */
    public function isFemale($str)
    {
        $female = false;
        $words  = explode(' ', $str);
        $str    = $words[0];

        $str = strtr($str, array('أ' => 'ا', 'إ' => 'ا', 'آ' => 'ا'));

        $last       = mb_substr($str, -1, 1);
        $beforeLast = mb_substr($str, -2, 1);

        if ($last == 'ا' || $last == 'ة' || $last == 'ه' || $last == 'ى' || ($last == 'ء' && $beforeLast == 'ا')) {
            $female = true;
        } elseif (preg_match("/^[اإ].{2}ا.$/u", $str) || preg_match("/^[إا].ت.ا.+$/u", $str)) {
            // الأسماء على وزن إفتعال و إفعال
            $female = true;
        } elseif (array_search($str, $this->arFemaleNames) > 0) {
            $female = true;
        }
        
        // إستثناء الأسماء المذكرة المؤنثة تأنيث لفظي
        if (array_search($str, $this->arMaleNames) > 0) {
            $female = false;
        }

        return $female;
    }

    /////////////////////////////////////// StrToTime /////////////////////////////////////////////

    /**
     * Arabic arStrToTime Function
     *
     * Function to parse about any Arabic textual datetime description into
     * a Unix timestamp.
     *
     * The function expects to be given a string containing an Arabic date format
     * and will try to parse that format into a Unix timestamp (the number of seconds
     * since January 1 1970 00:00:00 GMT), relative to the timestamp given in now, or
     * the current time if none is supplied.
     *
     * @param string  $text The string to parse, according to the GNU Date Input Formats syntax (in Arabic).
     * @param integer $now  The timestamp used to calculate the returned value.
     *
     * @return Integer Returns a timestamp on success, FALSE otherwise
     * @author Khaled Al-Sham'aa <khaled@ar-php.org>
     */
    public function strtotime($text, $now)
    {
        $int = 0;

        for ($i = 0; $i < 12; $i++) {
            if (strpos($text, $this->hj[$i]) > 0) {
                preg_match('/.*(\d{1,2}).*(\d{4}).*/', $text, $matches);

                $fix  = $this->mktimeCorrection($i + 1, (int)$matches[2]);
                $int  = $this->mktime(0, 0, 0, $i + 1, (int)$matches[1], (int)$matches[2], $fix);
                $temp = null;

                break;
            }
        }

        if ($int == 0) {
            $text = preg_replace($this->strToTimePatterns, $this->strToTimeReplacements, $text);
            $text = str_replace($this->strToTimeSearch, $this->strToTimeReplace, $text);

            $pattern = '[ابتثجحخدذرزسشصضطظعغفقكلمنهوي]';
            $text    = preg_replace("/$pattern/", '', $text);

            $int = strtotime($text, $now);
        }

        return $int;
    }

    /////////////////////////////////////// Mktime ////////////////////////////////////////////////

    /**
     * This will return current Unix timestamp
     * for given Hijri date (Islamic calendar)
     *
     * @param integer $hour       Time hour
     * @param integer $minute     Time minute
     * @param integer $second     Time second
     * @param integer $hj_month   Hijri month (Islamic calendar)
     * @param integer $hj_day     Hijri day   (Islamic calendar)
     * @param integer $hj_year    Hijri year  (Islamic calendar)
     * @param integer $correction To apply correction factor (+/- 1-2) to standard Hijri calendar
     *
     * @return integer Returns the current time measured in the number of
     *                 seconds since the Unix Epoch (January 1 1970 00:00:00 GMT)
     * @author Khaled Al-Sham'aa <khaled@ar-php.org>
     */
    public function mktime($hour, $minute, $second, $hj_month, $hj_day, $hj_year, $correction = 0)
    {
        list($year, $month, $day) = $this->arDateIslamicToGreg($hj_year, $hj_month, $hj_day);

        $unixTimeStamp = mktime($hour, $minute, $second, $month, $day, $year);
        $unixTimeStamp = $unixTimeStamp + 3600 * 24 * $correction;

        return $unixTimeStamp;
    }

    /**
     * This will convert given Hijri date (Islamic calendar) into Gregorian date
     *
     * @param integer $y Hijri year (Islamic calendar)
     * @param integer $m Hijri month (Islamic calendar)
     * @param integer $d Hijri day (Islamic calendar)
     *
     * @return array<int> Gregorian date [int Year, int Month, int Day]
     * @author Khaled Al-Sham'aa <khaled@ar-php.org>
     */
    private function arDateIslamicToGreg($y, $m, $d)
    {
        $str = jdtogregorian($this->arDateIslamicToJd($y, $m, $d));

        list($month, $day, $year) = explode('/', $str);

        return array((int)$year, (int)$month, (int)$day);
    }

    /**
     * Calculate Hijri calendar correction using Um-Al-Qura calendar information
     *
     * @param integer $m Hijri month (Islamic calendar)
     * @param integer $y Hijri year  (Islamic calendar), valid range [1420-1459]
     *
     * @return integer Correction factor to fix Hijri calendar calculation using Um-Al-Qura calendar information
     * @author Khaled Al-Sham'aa <khaled@ar-php.org>
     */
    public function mktimeCorrection($m, $y)
    {
        if ($y >= 1420 && $y < 1460) {
            $calc   = $this->mktime(0, 0, 0, $m, 1, $y);
            $offset = (($y - 1420) * 12 + $m) * 11;

            $d = substr($this->umAlqoura, $offset, 2);
            $m = substr($this->umAlqoura, $offset + 3, 2);
            $y = substr($this->umAlqoura, $offset + 6, 4);

            $real = mktime(0, 0, 0, (int)$m, (int)$d, (int)$y);
            $diff = (int)(($real - $calc) / (3600 * 24));
        } else {
            $diff = 0;
        }

        return $diff;
    }

    /**
     * Calculate how many days in a given Hijri month
     *
     * @param integer $m         Hijri month (Islamic calendar)
     * @param integer $y         Hijri year  (Islamic calendar), valid range[1320-1459]
     * @param boolean $umAlqoura Should we implement Um-Al-Qura calendar correction
     *                           in this calculation (default value is true)
     *
     * @return integer Days in a given Hijri month
     * @author Khaled Al-Sham'aa <khaled@ar-php.org>
     */
    public function hijriMonthDays($m, $y, $umAlqoura = true)
    {
        if ($y >= 1320 && $y < 1460) {
            $begin = $this->mktime(0, 0, 0, $m, 1, $y);

            if ($m == 12) {
                $m2 = 1;
                $y2 = $y + 1;
            } else {
                $m2 = $m + 1;
                $y2 = $y;
            }

            $end = $this->mktime(0, 0, 0, $m2, 1, $y2);

            if ($umAlqoura === true) {
                $c1 = $this->mktimeCorrection($m, $y);
                $c2 = $this->mktimeCorrection($m2, $y2);
            } else {
                $c1 = 0;
                $c2 = 0;
            }

            $days = ($end - $begin) / (3600 * 24);
            $days = $days - $c1 + $c2;
        } else {
            $days = false;
        }

        return $days;
    }


    /////////////////////////////////////// Transliteration ///////////////////////////////////////

    /**
     * Transliterate English string into Arabic by render them in the
     * orthography of the Arabic language
     *
     * @param string $string English string you want to transliterate
     * @param string $locale Locale information (e.g. 'en_GB' or 'de_DE')
     *
     * @return String Out of vocabulary English string in Arabic characters
     * @author Khaled Al-Sham'aa <khaled@ar-php.org>
     */
    public function en2ar($string, $locale = 'en_US')
    {
        setlocale(LC_ALL, $locale);

        $string = iconv("UTF-8", "ASCII//TRANSLIT", $string);
        $string = preg_replace('/[^\w\s]/', '', $string);
        $string = strtolower($string);
        $words  = explode(' ', $string);
        $string = '';

        foreach ($words as $word) {
            // if it is el or al don't add space after
            if ($word == 'el' || $word == 'al') {
                $space = '';
            } else {
                $space = ' ';
            }
            
            // skip translation if it has no a-z char (i.e., just add it to the string as is)
            if (preg_match('/[a-z]/i', $word)) {
                $word = preg_replace($this->en2arPregSearch, $this->en2arPregReplace, $word);
                $word = strtr($word, array_combine($this->en2arStrSearch, $this->en2arStrReplace));
            }

            $string .= $word . $space;
        }

        return trim($string);
    }

    /**
     * Transliterate Arabic string into English by render them in the
     * orthography of the English language
     *
     * @param string $string   Arabic string you want to transliterate
     * @param string $standard Transliteration standard, default is UNGEGN and possible values are
     *                         [UNGEGN, UNGEGN+, RJGC, SES, ISO233]
     *
     * @return String Out of vocabulary Arabic string in English characters
     * @author Khaled Al-Sham'aa <khaled@ar-php.org>
     */
    public function ar2en($string, $standard = 'UNGEGN')
    {
        //$string = strtr($string, array('ة ال' => 'tul'));
        $words  = explode(' ', $string);
        $string = '';

        for ($i = 0; $i < count($words) - 1; $i++) {
            $words[$i] = strtr($words[$i], 'ة', 'ت');
        }

        foreach ($words as $word) {
            $temp = $word;

            if ($standard == 'UNGEGN+') {
                $temp = strtr($temp, array_combine($this->diariticalSearch, $this->diariticalReplace));
            } elseif ($standard == 'RJGC') {
                $temp = strtr($temp, array_combine($this->diariticalSearch, $this->diariticalReplace));
                $temp = strtr($temp, array_combine($this->rjgcSearch, $this->rjgcReplace));
            } elseif ($standard == 'SES') {
                $temp = strtr($temp, array_combine($this->diariticalSearch, $this->diariticalReplace));
                $temp = strtr($temp, array_combine($this->sesSearch, $this->sesReplace));
            } elseif ($standard == 'ISO233') {
                $temp = strtr($temp, array_combine($this->iso233Search, $this->iso233Replace));
            }

            $temp = preg_replace($this->ar2enPregSearch, $this->ar2enPregReplace, $temp);
            $temp = strtr($temp, array_combine($this->ar2enStrSearch, $this->ar2enStrReplace));
            $temp = preg_replace($this->arFinePatterns, $this->arFineReplacements, $temp);

            if (preg_match('/[a-z]/', mb_substr($temp, 0, 1))) {
                $temp = ucwords($temp);
            }

            $pos  = strpos($temp, '-');

            if ($pos > 0) {
                if (preg_match('/[a-z]/', mb_substr($temp, $pos + 1, 1))) {
                    $temp2  = substr($temp, 0, $pos);
                    $temp2 .= '-' . strtoupper($temp[$pos + 1]);
                    $temp2 .= substr($temp, $pos + 2);
                } else {
                    $temp2 = $temp;
                }
            } else {
                $temp2 = $temp;
            }

            $string .= ' ' . $temp2;
        }

        return trim($string);
    }


    /////////////////////////////////////// Date //////////////////////////////////////////////////

    /**
     * Setting value for $_arDateMode scalar
     *
     * @param integer $mode Output mode of date function where:
     *                       1) Hijri format (Islamic calendar)
     *                       2) Arabic month names used in Middle East countries
     *                       3) Arabic Transliteration of Gregorian month names
     *                       4) Both of 2 and 3 formats together
     *                       5) Libya style
     *                       6) Algeria and Tunis style
     *                       7) Morocco style
     *                       8) Hijri format (Islamic calendar) in English
     *
     * @return object $this to build a fluent interface
     * @author Khaled Al-Sham'aa <khaled@ar-php.org>
     */
    public function setDateMode($mode = 1)
    {
        $mode = (int) $mode;

        if ($mode > 0 && $mode < 9) {
            $this->arDateMode = $mode;
        }

        return $this;
    }

    /**
     * Getting $mode value that refer to output mode format
     *               1) Hijri format (Islamic calendar)
     *               2) Arabic month names used in Middle East countries
     *               3) Arabic Transliteration of Gregorian month names
     *               4) Both of 2 and 3 formats together
     *               5) Libyan way
     *               6) Algeria and Tunis style
     *               7) Morocco style
     *               8) Hijri format (Islamic calendar) in English
     *
     * @return Integer Value of $mode properity
     * @author Khaled Al-Sham'aa <khaled@ar-php.org>
     */
    public function getDateMode()
    {
        return $this->arDateMode;
    }

    /**
     * Format a local time/date in Arabic string
     *
     * @param string  $format     Format string (same as PHP date function)
     * @param integer $timestamp  Unix timestamp
     * @param integer $correction To apply correction factor (+/- 1-2) to standard hijri calendar
     *
     * @return string Format Arabic date string according to given format string using the given integer timestamp
     *                or the current local time if no timestamp is given.
     * @author Khaled Al-Sham'aa <khaled@ar-php.org>
     */
    public function date($format, $timestamp, $correction = 0)
    {
        if ($this->arDateMode == 1 || $this->arDateMode == 8) {
            /** @var array<string> */
            $hj_txt_month = array();
            
            if ($this->arDateMode == 1) {
                foreach ($this->arDateJSON['ar_hj_month'] as $id => $month) {
                    $id++;
                    $hj_txt_month["$id"] = (string)$month;
                }
            }

            if ($this->arDateMode == 8) {
                foreach ($this->arDateJSON['en_hj_month'] as $id => $month) {
                    $id++;
                    $hj_txt_month["$id"] = (string)$month;
                }
            }

            $patterns     = array();
            $replacements = array();

            $patterns[] = 'Y';
            $patterns[] = 'y';
            $patterns[] = 'M';
            $patterns[] = 'F';
            $patterns[] = 'n';
            $patterns[] = 'm';
            $patterns[] = 'j';
            $patterns[] = 'd';

            $replacements[] = 'b1';
            $replacements[] = 'b2';
            $replacements[] = 'b3';
            $replacements[] = 'b3';
            $replacements[] = 'b4';
            $replacements[] = 'b5';
            $replacements[] = 'b6';
            $replacements[] = 'b7';

            if ($this->arDateMode == 8) {
                $patterns[] = 'S';
                $replacements[] = '';
            }

            $format = strtr($format, array_combine($patterns, $replacements));
            $str    = date($format, $timestamp);

            if ($this->arDateMode == 1) {
                $str = $this->arDateEn2ar($str);
            }

            list($y, $m, $d) = explode(' ', date('Y m d', $timestamp));
            list($hj_y, $hj_m, $hj_d) = $this->arDateGregToIslamic((int)$y, (int)$m, (int)$d);

            $hj_d += $correction;

            if ($hj_d <= 0) {
                $hj_d = $hj_d == 0 ? 30 : 29;
                // alter the $hj_m and $hj_y to refer to the previous month
                $hj_m = $hj_m == 1 ? 12 : $hj_m - 1;
                $hj_y = $hj_m == 12 ? $hj_y - 1 : $hj_y;
            } elseif ($hj_d > 30) {
                $hj_d = $hj_d == 31 ? 1 : 2;
                // alter the $hj_m and $hj_y to refer to the next month
                $hj_m = $hj_m == 12 ? 1 : $hj_m + 1;
                $hj_y = $hj_m == 1 ? $hj_y + 1 : $hj_y;
            }

            $patterns     = array();
            $replacements = array();

            $patterns[] = 'b1';
            $patterns[] = 'b2';
            $patterns[] = 'b3';
            $patterns[] = 'b4';
            $patterns[] = 'b5';
            $patterns[] = 'b6';
            $patterns[] = 'b7';
            
            $replacements[] = $hj_y;
            $replacements[] = substr((string)$hj_y, -2);
            $replacements[] = $hj_txt_month[$hj_m];
            $replacements[] = $hj_m;
            $replacements[] = sprintf('%02d', $hj_m);
            $replacements[] = $hj_d;
            $replacements[] = sprintf('%02d', $hj_d);

            $str = strtr($str, array_combine($patterns, $replacements));
        } elseif ($this->arDateMode == 5) {
            $year  = date('Y', $timestamp);
            $year -= 632;
            $yr    = substr("$year", -2);

            $format = strtr($format, array('Y' => (string)$year));
            $format = strtr($format, array('y' => $yr));

            $str = date($format, $timestamp);
            $str = $this->arDateEn2ar($str);
        } else {
            $str = date($format, $timestamp);
            $str = $this->arDateEn2ar($str);
        }
        return $str;
    }

    /**
     * Translate English date/time terms into Arabic langauge
     *
     * @param string $str Date/time string using English terms
     *
     * @return string Date/time string using Arabic terms
     * @author Khaled Al-Sham'aa <khaled@ar-php.org>
     */
    private function arDateEn2ar($str)
    {
        $patterns     = array();
        $replacements = array();

        $str = strtolower($str);

        foreach ($this->arDateJSON['en_day']['mode_full'] as $day) {
            $patterns[] = (string)$day;
        }

        foreach ($this->arDateJSON['ar_day'] as $day) {
            $replacements[] = (string)$day;
        }

        foreach ($this->arDateJSON['en_month']['mode_full'] as $month) {
            $patterns[] = (string)$month;
        }

        $replacements = array_merge($replacements, $this->arDateArabicMonths($this->arDateMode));

        foreach ($this->arDateJSON['en_day']['mode_short'] as $day) {
            $patterns[] = (string)$day;
        }

        foreach ($this->arDateJSON['ar_day'] as $day) {
            $replacements[] = (string)$day;
        }

        foreach ($this->arDateJSON['en_month']['mode_short'] as $m) {
            $patterns[] = (string)$m;
        }

        $replacements = array_merge($replacements, $this->arDateArabicMonths($this->arDateMode));

        foreach ($this->arDateJSON['preg_replace_en2ar'] as $p) {
            $patterns[] = (string)$p['search'];
            $replacements[] = (string)$p['replace'];
        }

        $str = strtr($str, array_combine($patterns, $replacements));

        return $str;
    }

    /**
     * Add Arabic month names to the replacement array
     *
     * @param integer $mode Naming mode of months in Arabic where:
     *                       2) Arabic month names used in Middle East countries
     *                       3) Arabic Transliteration of Gregorian month names
     *                       4) Both of 2 and 3 formats together
     *                       5) Libya style
     *                       6) Algeria and Tunis style
     *                       7) Morocco style
     *
     * @return array<string> Arabic month names in selected style
     * @author Khaled Al-Sham'aa <khaled@ar-php.org>
     */
    private function arDateArabicMonths($mode)
    {
        $replacements = array();

        foreach ($this->arDateJSON['ar_month']["mode_$mode"] as $month) {
            $replacements[] = (string)$month;
        }

        return $replacements;
    }

    /**
     * Convert given Gregorian date into Hijri date
     *
     * @param integer $y Year Gregorian year
     * @param integer $m Month Gregorian month
     * @param integer $d Day Gregorian day
     *
     * @return array<int> Hijri date [int Year, int Month, int Day](Islamic calendar)
     * @author Khaled Al-Sham'aa <khaled@ar-php.org>
     */
    private function arDateGregToIslamic($y, $m, $d)
    {
        $jd = gregoriantojd($m, $d, $y);

        list($year, $month, $day) = $this->arDateJdToIslamic($jd);

        return array($year, $month, $day);
    }

    /**
     * Convert given Julian day into Hijri date
     *
     * @param integer $jd Julian day
     *
     * @return array<int> Hijri date [int Year, int Month, int Day](Islamic calendar)
     * @author Khaled Al-Sham'aa <khaled@ar-php.org>
     */
    private function arDateJdToIslamic($jd)
    {
        $l = (int)$jd - 1948440 + 10632;
        $n = (int)(($l - 1) / 10631);

        $l = $l - 10631 * $n + 354;
        $j = (int)((10985 - $l) / 5316) * (int)((50 * $l) / 17719) + (int)($l / 5670) * (int)((43 * $l) / 15238);

        $l = $l - (int)((30 - $j) / 15) * (int)((17719 * $j) / 50) - (int)($j / 16) * (int)((15238 * $j) / 43) + 29;
        $m = (int)((24 * $l) / 709);
        $d = $l - (int)((709 * $m) / 24);
        $y = (int)(30 * $n + $j - 30);

        return array($y, $m, $d);
    }

    /**
     * Convert given Hijri date into Julian day
     *
     * @param integer $y Year Hijri year
     * @param integer $m Month Hijri month
     * @param integer $d Day Hijri day
     *
     * @return integer Julian day
     * @author Khaled Al-Sham'aa <khaled@ar-php.org>
     */
    private function arDateIslamicToJd($y, $m, $d)
    {
        $jd = (int)((11 * $y + 3) / 30) + (int)(354 * $y) + (int)(30 * $m) - (int)(($m - 1) / 2) + $d + 1948440 - 385;

        return $jd;
    }

    /**
     * Calculate Hijri calendar correction using Um-Al-Qura calendar information
     *
     * @param integer $time Unix timestamp
     *
     * @return integer Correction factor to fix Hijri calendar calculation using Um-Al-Qura calendar information
     * @author Khaled Al-Sham'aa <khaled@ar-php.org>
     */
    public function dateCorrection($time)
    {
        $calc = $time - (int)$this->date('j', $time) * 3600 * 24;

        $y      = $this->date('Y', $time);
        $m      = $this->date('n', $time);
        $offset = (((int)$y - 1420) * 12 + (int)$m) * 11;

        $d = substr($this->umAlqoura, $offset, 2);
        $m = substr($this->umAlqoura, $offset + 3, 2);
        $y = substr($this->umAlqoura, $offset + 6, 4);

        $real = mktime(0, 0, 0, (int)$m, (int)$d, (int)$y);
        $diff = (int)(($calc - $real) / (3600 * 24));

        return $diff;
    }


    /////////////////////////////////////// Numbers ///////////////////////////////////////////////

    /**
     * Set feminine flag of the counted object
     *
     * @param integer $value Counted object feminine (1 for masculine & 2 for feminine)
     *
     * @return object $this to build a fluent interface
     * @author Khaled Al-Sham'aa <khaled@ar-php.org>
     */
    public function setNumberFeminine($value)
    {
        if ($value == 1 || $value == 2) {
            $this->arNumberFeminine = $value;
        }

        return $this;
    }

    /**
     * Set the grammar position flag of the counted object
     *
     * @param integer $value Grammar position of counted object (1 if Marfoua & 2 if Mansoub or Majrour)
     *
     * @return object $this to build a fluent interface
     * @author Khaled Al-Sham'aa <khaled@ar-php.org>
     */
    public function setNumberFormat($value)
    {
        if ($value == 1 || $value == 2) {
            $this->arNumberFormat = $value;
        }

        return $this;
    }

    /**
     * Set the ordering flag, is it normal number or ordering number
     *
     * @param integer $value Is it an ordering number? default is 1 (use 1 if no and 2 if yes)
     *
     * @return object $this to build a fluent interface
     * @author Khaled Al-Sham'aa <khaled@ar-php.org>
     */
    public function setNumberOrder($value)
    {
        if ($value == 1 || $value == 2) {
            $this->arNumberOrder = $value;
        }

        return $this;
    }

    /**
     * Get the feminine flag of counted object
     *
     * @return integer return current setting of counted object feminine flag
     * @author Khaled Al-Sham'aa <khaled@ar-php.org>
     */
    public function getNumberFeminine()
    {
        return $this->arNumberFeminine;
    }

    /**
     * Get the grammer position flag of counted object
     *
     * @return integer return current setting of counted object grammer position flag
     * @author Khaled Al-Sham'aa <khaled@ar-php.org>
     */
    public function getNumberFormat()
    {
        return $this->arNumberFormat;
    }

    /**
     * Get the ordering flag value
     *
     * @return integer return current setting of ordering flag value
     * @author Khaled Al-Sham'aa <khaled@ar-php.org>
     */
    public function getNumberOrder()
    {
        return $this->arNumberOrder;
    }

    /**
     * Spell integer number in Arabic idiom
     *
     * @param integer $number The number you want to spell in Arabic idiom
     *
     * @return string The Arabic idiom that spells inserted number
     * @author Khaled Al-Sham'aa <khaled@ar-php.org>
     */
    public function int2str($number)
    {
        if ($number == 1 && $this->arNumberOrder == 2) {
            if ($this->arNumberFeminine == 1) {
                $string = 'الأول';
            } else {
                $string = 'الأولى';
            }
        } else {
            if ($number < 0) {
                $string = 'سالب ';
                $number = (string) (-1 * $number);
            } else {
                $string = '';
            }

            $temp = explode('.', (string)$number);

            $string .= $this->arNumbersSubStr($temp[0]);

            if (!empty($temp[1])) {
                $dec     = $this->arNumbersSubStr($temp[1]);
                $string .= ' فاصلة ' . $dec;
            }
        }

        return $string;
    }
    
    /**
     * Spell integer number in Arabic idiom followed by plural form of the counted item
     *
     * @param integer $count The number you want to spell in Arabic idiom
     * @param string  $word  The counted item
     *
     * @return string The Arabic idiom that spells inserted number followed by plural form of the counted item.
     * @author Khaled Al-Sham'aa <khaled@ar-php.org>
     */
    public function int2strItem($count, $word)
    {
        $feminine = $this->isFemale($word) ? 2 : 1;
        
        $this->setNumberFeminine($feminine);
        
        $str1 = $this->int2str($count);
        $str2 = $this->arPlural($word, $count);
        
        $string = strtr($str2, array('%d' => $str1));

        return $string;
    }

    /**
     * Spell number in Arabic idiom as money
     *
     * @param integer $number The number you want to spell in Arabic idiom as money
     * @param string  $iso    The three-letter Arabic country code defined in ISO 3166 standard
     * @param string  $lang   The two-letter language code in ISO 639-1 standard [ar|en]
     *
     * @return string The Arabic idiom that spells inserted number as money
     * @author Khaled Al-Sham'aa <khaled@ar-php.org>
     */
    public function money2str($number, $iso = 'SYP', $lang = 'ar')
    {
        $iso  = strtoupper($iso);
        $lang = strtolower($lang);

        $number = sprintf("%01.{$this->arNumberCurrency[$iso]['decimals']}f", $number);
        $temp   = explode('.', $number);
        $string = '';

        if ($temp[0] != 0) {
            if ($lang == 'ar') {
                $string .= $this->int2strItem((int)$temp[0], $this->arNumberCurrency[$iso][$lang]['basic']);
            } else {
                $string .= $temp[0] . ' ' . $this->arNumberCurrency[$iso][$lang]['basic'];
            }
        }

        if (!empty($temp[1]) && $temp[1] != 0) {
            if ($string != '') {
                if ($lang == 'ar') {
                    $string .= ' و';
                } else {
                    $string .= ' and ';
                }
            }

            if ($lang == 'ar') {
                $string .= $this->int2strItem((int)$temp[1], $this->arNumberCurrency[$iso][$lang]['fraction']);
            } else {
                $string .= $temp[1] . ' ' . $this->arNumberCurrency[$iso][$lang]['fraction'];
            }
        }
        
        return $string;
    }

    /**
     * Convert Arabic idiom number string into Integer
     *
     * @param string $str The Arabic idiom that spells input number
     *
     * @return integer The number you spell it in the Arabic idiom
     * @author Khaled Al-Sham'aa <khaled@ar-php.org>
     */
    public function str2int($str)
    {
        // Normalization phase
        $str = strtr($str, array('أ' => 'ا', 'إ' => 'ا', 'آ' => 'ا'));
        $str = strtr($str, array('ه' => 'ة'));
        $str = preg_replace('/\s+/', ' ', $str);
        $ptr = array('ـ' => '', 'َ' => '', 'ً' => '', 'ُ' => '', 'ٌ' => '', 'ِ' => '', 'ٍ' => '', 'ْ' => '', 'ّ' => '');
        $str = strtr($str, $ptr);
        $str = strtr($str, array('مائة' => 'مئة'));
        $ptr = array('/احدى\s/u','/احد\s/u');
        $str = preg_replace($ptr, 'واحد ', $str);
        $ptr = array('/اثنا\s/u','/اثني\s/u','/اثنتا\s/u', '/اثنتي\s/u','/اثنين\s/u','/اثنتان\s/u', '/اثنتين\s/u');
        $str = preg_replace($ptr, 'اثنان ', $str);

        $str = trim($str);

        if (strpos($str, 'ناقص') === false && strpos($str, 'سالب') === false) {
            $negative = false;
        } else {
            $negative = true;
        }

        // Complications process
        $segment = array();
        $max     = count($this->arNumberComplications);

        for ($scale = $max; $scale > 0; $scale--) {
            $key = pow(1000, $scale);

            $pattern = array('أ' => 'ا', 'إ' => 'ا', 'آ' => 'ا');
            $format1 = strtr($this->arNumberComplications[$scale][1], $pattern);
            $format2 = strtr($this->arNumberComplications[$scale][2], $pattern);
            $format3 = strtr($this->arNumberComplications[$scale][3], $pattern);
            $format4 = strtr($this->arNumberComplications[$scale][4], $pattern);

            if (strpos($str, $format1) !== false) {
                list($temp, $str) = explode($format1, $str);
                $segment[$key]    = 'اثنان';
            } elseif (strpos($str, $format2) !== false) {
                list($temp, $str) = explode($format2, $str);
                $segment[$key]    = 'اثنان';
            } elseif (strpos($str, $format3) !== false) {
                list($segment[$key], $str) = explode($format3, $str);
            } elseif (strpos($str, $format4) !== false) {
                list($segment[$key], $str) = explode($format4, $str);
                if ($segment[$key] == '') {
                    $segment[$key] = 'واحد';
                }
            }

            if (isset($segment[$key])) {
                $segment[$key] = trim($segment[$key]);
            }
        }

        $segment[1] = trim($str);
        // Individual process

        $total    = 0;
        $subTotal = 0;

        foreach ($segment as $scale => $str) {
            $str = " $str ";
            foreach ($this->arNumberSpell as $word => $value) {
                if (strpos($str, "$word ") !== false) {
                    $str = strtr($str, array("$word " => ' '));
                    $subTotal += $value;
                }
            }

            $total   += $subTotal * $scale;
            $subTotal = 0;
        }

        if ($negative) {
            $total = -1 * $total;
        }

        return $total;
    }

    /**
     * Spell integer number in Arabic idiom
     *
     * @param string  $number The number you want to spell in Arabic idiom
     * @param boolean $zero   Present leading zero if true [default is true]
     *
     * @return string The Arabic idiom that spells inserted number
     * @author Khaled Al-Sham'aa <khaled@ar-php.org>
     */
    private function arNumbersSubStr($number, $zero = true)
    {
        $blocks = array();
        $items  = array();
        $zeros  = '';
        $string = '';
        $number = ($zero != false) ? trim((string)$number) : trim((string)(float)$number);

        if ($number > 0) {
            //--- by Jnom: handle left zero
            // http://www.itkane.com
            // jnom23@gmail.com
            if ($zero != false) {
                $fulnum = $number;
                while (($fulnum[0]) == '0') {
                    $zeros = 'صفر ' . $zeros;
                    $fulnum = substr($fulnum, 1, strlen($fulnum));
                };
                $zeros = trim($zeros);
            };
            //---/

            while (strlen($number) > 3) {
                $blocks[] = substr($number, -3);
                $number = substr($number, 0, strlen($number) - 3);
            }

            $blocks[] = $number;
            $blocks_num = count($blocks) - 1;

            for ($i = $blocks_num; $i >= 0; $i--) {
                $number = floor((float)$blocks[$i]);
                $text   = $this->arNumberWrittenBlock((int)$number);

                if ($text) {
                    if ($number == 1 && $i != 0) {
                        $text = $this->arNumberComplications[$i][4];
                    } elseif ($number == 2 && $i != 0) {
                        $text = $this->arNumberComplications[$i][$this->arNumberFormat];
                    } elseif ($number > 2 && $number < 11 && $i != 0) {
                        $text .= ' ' . $this->arNumberComplications[$i][3];
                    } elseif ($i != 0) {
                        $text .= ' ' . $this->arNumberComplications[$i][4];
                    }
                    
                    if ($this->arNumberOrder == 2 && ($number > 1 && $number < 11)) {
                        $text = 'ال' . $text;
                    }

                    //--- by Jnom: handle left zero
                    if ($text != '' && $zeros != '' && $zero != false) {
                        $text  = $zeros . ' ' . $text;
                        $zeros = '';
                    };
                    //---/

                    $items[] = $text;
                }
            }

            $string = implode(' و', $items);
        } else {
            $string = 'صفر';
        }

        return $string;
    }

    /**
     * Spell sub block number of three digits max in Arabic idiom
     *
     * @param integer $number Sub block number of three digits max you want to spell in Arabic idiom
     *
     * @return string The Arabic idiom that spells inserted sub block
     * @author Khaled Al-Sham'aa <khaled@ar-php.org>
     */
    private function arNumberWrittenBlock($number)
    {
        $items  = array();
        $string = '';

        if ($number > 99) {
            $hundred = floor($number / 100) * 100;
            $number  = $number % 100;

            if ($this->arNumberOrder == 2) {
                $pre = 'ال';
            } else {
                $pre = '';
            }

            if ($hundred == 200) {
                $items[] = $pre . $this->arNumberIndividual[$hundred][$this->arNumberFormat];
            } else {
                $items[] = $pre . $this->arNumberIndividual[$hundred];
            }
        }

        if ($number != 0) {
            if ($this->arNumberOrder == 2) {
                if ($number <= 10) {
                    $items[] = $this->arNumberOrdering[$number][$this->arNumberFeminine];
                } elseif ($number < 20) {
                    $number -= 10;
                    $item    = 'ال' . $this->arNumberOrdering[$number][$this->arNumberFeminine];

                    if ($this->arNumberFeminine == 1) {
                        $item .= ' عشر';
                    } else {
                        $item .= ' عشرة';
                    }

                    $items[] = $item;
                } else {
                    $ones = $number % 10;
                    $tens = floor($number / 10) * 10;

                    if ($ones > 0) {
                        $items[] = 'ال' . $this->arNumberOrdering[$ones][$this->arNumberFeminine];
                    }
                    $items[] = 'ال' . $this->arNumberIndividual[$tens][$this->arNumberFormat];
                }
            } else {
                if ($number == 2 || $number == 12) {
                    $items[] = $this->arNumberIndividual[$number][$this->arNumberFeminine][$this->arNumberFormat];
                } elseif ($number < 20) {
                    $items[] = $this->arNumberIndividual[$number][$this->arNumberFeminine];
                } else {
                    $ones = $number % 10;
                    $tens = floor($number / 10) * 10;

                    if ($ones == 2) {
                        $items[] = $this->arNumberIndividual[2][$this->arNumberFeminine][$this->arNumberFormat];
                    } elseif ($ones > 0) {
                        $items[] = $this->arNumberIndividual[$ones][$this->arNumberFeminine];
                    }

                    $items[] = $this->arNumberIndividual[$tens][$this->arNumberFormat];
                }
            }
        }

        $items  = array_diff($items, array(''));
        $string = implode(' و', $items);

        return $string;
    }

    /**
     * Represent integer number in Arabic-Indic digits using HTML entities
     *
     * @param integer $number The number you want to present in Arabic-Indic digits using HTML entities
     *
     * @return string The Arabic-Indic digits represent inserted integer number using HTML entities
     * @author Khaled Al-Sham'aa <khaled@ar-php.org>
     */
    public function int2indic($number)
    {
        $str = strtr("$number", $this->arNumberArabicIndic);

        return $str;
    }


    /////////////////////////////////////// KeySwap ///////////////////////////////////////////////

    /**
     * Make conversion to swap that odd Arabic text by original English sentence
     * you meant when you type on your keyboard (if keyboard language was incorrect)
     *
     * @param string $text Odd Arabic string
     *
     * @return string Normal English string
     * @author Khaled Al-Sham'aa <khaled@ar-php.org>
     */
    public function swapAe($text)
    {
        $pieces = explode('لا', $text);
        
        $max = count($pieces);

        for ($i = 0; $i < $max; $i++) {
            $pieces[$i] = $this->swapCore($pieces[$i], 'ar', 'en');
        }
        
        if ($max > 1) {
            for ($i = 1; $i < $max; $i++) {
                $first_next = mb_substr($pieces[$i], 0, 1);
                $last_prev  = mb_substr($pieces[$i - 1], -1);
                
                $rank_b  = (float)$this->enLogodd[$last_prev]['b'] + (float)$this->enLogodd['b'][$first_next];
                $rank_gh = (float)$this->enLogodd[$last_prev]['g'] + (float)$this->enLogodd['h'][$first_next];
                
                if ($rank_b > $rank_gh) {
                    $pieces[$i] = 'b' . $pieces[$i];
                } else {
                    $pieces[$i] = 'gh' . $pieces[$i];
                }
            }
        }
        
        $output = implode('', $pieces);

        return $output;
    }

    /**
     * Make conversion to swap that odd English text by original Arabic sentence
     * you meant when you type on your keyboard (if keyboard language was incorrect)
     *
     * @param string $text Odd English string
     *
     * @return string Normal Arabic string
     * @author Khaled Al-Sham'aa <khaled@ar-php.org>
     */
    public function swapEa($text)
    {
        $output = $this->swapCore($text, 'en', 'ar');

        return $output;
    }

    /**
     * Make conversion to swap that odd Arabic text by original French sentence
     * you meant when you type on your keyboard (if keyboard language was incorrect)
     *
     * @param string $text Odd Arabic string
     *
     * @return string Normal French string
     * @author Khaled Al-Sham'aa <khaled@ar-php.org>
     */
    public function swapAf($text)
    {
        $output = $this->swapCore($text, 'ar', 'fr');

        return $output;
    }

    /**
     * Make conversion to swap that odd French text by original Arabic sentence
     * you meant when you type on your keyboard (if keyboard language was incorrect)
     *
     * @param string $text Odd French string
     *
     * @return string Normal Arabic string
     * @author Khaled Al-Sham'aa <khaled@ar-php.org>
     */
    public function swapFa($text)
    {
        $output = $this->swapCore($text, 'fr', 'ar');

        return $output;
    }

    /**
     * Make conversion between different keyboard maps to swap odd text in
     * one language by original meaningful text in another language that
     * you meant when you type on your keyboard (if keyboard language was incorrect)
     *
     * @param string $text Odd string
     * @param string $in   Input language [ar|en|fr]
     * @param string $out  Output language [ar|en|fr]
     *
     * @return string Normal string
     * @author Khaled Al-Sham'aa <khaled@ar-php.org>
     */
    private function swapCore($text, $in, $out)
    {
        $output = '';
        $text   = stripslashes($text);
        $max    = mb_strlen($text);
        
        $inputMap  = array();
        $outputMap = array();

        switch ($in) {
            case 'ar':
                $inputMap = $this->arKeyboard;
                break;
            case 'en':
                $inputMap = $this->enKeyboard;
                break;
            case 'fr':
                $inputMap = $this->frKeyboard;
                break;
        }

        switch ($out) {
            case 'ar':
                $outputMap = $this->arKeyboard;
                break;
            case 'en':
                $outputMap = $this->enKeyboard;
                break;
            case 'fr':
                $outputMap = $this->frKeyboard;
                break;
        }

        for ($i = 0; $i < $max; $i++) {
            $chr = mb_substr($text, $i, 1);
            $key = array_search($chr, $inputMap);

            if ($key === false) {
                $output .= $chr;
            } else {
                $output .= $outputMap[$key];
            }
        }

        return $output;
    }

    /**
     * Calculate the log odd probability that inserted string from keyboard is in English language
     *
     * @param string $str Inserted string from the keyboard
     *
     * @return float Calculated score for input string as English language
     * @author Khaled Al-Sham'aa <khaled@ar-php.org>
     */
    private function checkEn($str)
    {
        $str  = mb_strtolower($str);
        $max  = mb_strlen($str);
        $rank = 0;

        for ($i = 1; $i < $max; $i++) {
            $first  = mb_substr($str, $i - 1, 1);
            $second = mb_substr($str, $i, 1);

            if (isset($this->enLogodd["$first"]["$second"])) {
                $rank += $this->enLogodd["$first"]["$second"];
            } else {
                $rank -= 10;
            }
        }

        return $rank;
    }

    /**
     * Calculate the log odd probability that inserted string from keyboard is in Arabic language
     *
     * @param string $str Inserted string from the keyboard
     *
     * @return float Calculated score for input string as Arabic language
     * @author Khaled Al-Sham'aa <khaled@ar-php.org>
     */
    private function checkAr($str)
    {
        $max  = mb_strlen($str);
        $rank = 0;

        for ($i = 1; $i < $max; $i++) {
            $first  = mb_substr($str, $i - 1, 1);
            $second = mb_substr($str, $i, 1);

            if (isset($this->arLogodd["$first"]["$second"])) {
                $rank += $this->arLogodd["$first"]["$second"];
            } else {
                $rank -= 10;
            }
        }

        return $rank;
    }

    /**
     * This method will automatically detect the language of content supplied
     * in the input string. It will return the suggestion of correct inserted text.
     * The accuracy of the automatic language detection increases with the amount
     * of text entered.
     *
     * @param string $str Inserted string from the keyboard
     *
     * @return string Fixed string language and letter case to the better guess
     * @author Khaled Al-Sham'aa <khaled@ar-php.org>
     */
    public function fixKeyboardLang($str)
    {
        preg_match_all("/([\x{0600}-\x{06FF}])/u", $str, $matches);

        $arNum    = count($matches[0]);
        $nonArNum = mb_strlen(strtr($str, array(' ' => ''))) - $arNum;

        $capital = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
        $small   = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

        $strCaps   = strtr($str, $capital, $small);
        $arStrCaps = $this->swapEa($strCaps);

        if ($arNum > $nonArNum) {
            $arStr = $str;
            $enStr = $this->swapAe($str);
            $isAr  = true;

            $enRank = $this->checkEn($enStr);
            $arRank = $this->checkAr($arStr);

            $arCapsRank = $arRank;
        } else {
            $arStr = $this->swapEa($str);
            $enStr = $str;

            $isAr = false;

            $enRank = $this->checkEn($enStr);
            $arRank = $this->checkAr($arStr);

            $arCapsRank = $this->checkAr($arStrCaps);
        }

        if ($enRank > $arRank && $enRank > $arCapsRank) {
            if ($isAr) {
                $fix = $enStr;
            } else {
                preg_match_all("/([A-Z])/u", $enStr, $matches);
                $capsNum = count($matches[0]);

                preg_match_all("/([a-z])/u", $enStr, $matches);
                $nonCapsNum = count($matches[0]);

                if ($capsNum > $nonCapsNum && $nonCapsNum > 0) {
                    $enCapsStr = strtr($enStr, $capital, $small);
                    $fix       = $enCapsStr;
                } else {
                    $fix = '';
                }
            }
        } else {
            if ($arCapsRank > $arRank) {
                $arStr  = $arStrCaps;
                $arRank = $arCapsRank;
            }
            if (!$isAr) {
                $fix = $arStr;
            } else {
                $fix = '';
            }
        }

        return $fix;
    }


    /////////////////////////////////////// Soundex ///////////////////////////////////////////////

    /**
     * Set the length of soundex key (default value is 4)
     *
     * @param integer $integer Soundex key length
     *
     * @return object $this to build a fluent interface
     * @author Khaled Al-Sham'aa <khaled@ar-php.org>
     */
    public function setSoundexLen($integer)
    {
        $this->soundexLen = (int)$integer;

        return $this;
    }

    /**
     * Set the language of the soundex key (default value is "en")
     *
     * @param string $str Soundex key language [ar|en]
     *
     * @return object $this to build a fluent interface
     * @author Khaled Al-Sham'aa <khaled@ar-php.org>
     */
    public function setSoundexLang($str)
    {
        $str = strtolower($str);

        if ($str == 'ar' || $str == 'en') {
            $this->soundexLang = $str;
        }

        return $this;
    }

    /**
     * Set the mapping code of the soundex key (default value is "soundex")
     *
     * @param string $str Soundex key mapping code [soundex|phonix]
     *
     * @return object $this to build a fluent interface
     * @author Khaled Al-Sham'aa <khaled@ar-php.org>
     */
    public function setSoundexCode($str)
    {
        $str = strtolower($str);

        if ($str == 'soundex' || $str == 'phonix') {
            $this->soundexCode = $str;

            if ($str == 'phonix') {
                $this->soundexMap = $this->arPhonixCode;
            } else {
                $this->soundexMap = $this->arSoundexCode;
            }
        }

        return $this;
    }

    /**
     * Get the soundex key length used now
     *
     * @return integer return current setting for soundex key length
     * @author Khaled Al-Sham'aa <khaled@ar-php.org>
     */
    public function getSoundexLen()
    {
        return $this->soundexLen;
    }

    /**
     * Get the soundex key language used now
     *
     * @return string return current setting for soundex key language
     * @author Khaled Al-Sham'aa <khaled@ar-php.org>
     */
    public function getSoundexLang()
    {
        return $this->soundexLang;
    }

    /**
     * Get the soundex key calculation method used now
     *
     * @return string return current setting for soundex key calculation method
     * @author Khaled Al-Sham'aa <khaled@ar-php.org>
     */
    public function getSoundexCode()
    {
        return $this->soundexCode;
    }

    /**
     * Methode to get soundex/phonix numric code for given word
     *
     * @param string $word The word that we want to encode it
     *
     * @return string The calculated soundex/phonix numeric code
     * @author Khaled Al-Sham'aa <khaled@ar-php.org>
     */
    private function arSoundexMapCode($word)
    {
        $encodedWord = '';
        $max         = mb_strlen($word);

        for ($i = 0; $i < $max; $i++) {
            $char = mb_substr($word, $i, 1);

            if (isset($this->soundexMap["$char"])) {
                $encodedWord .= $this->soundexMap["$char"];
            } else {
                $encodedWord .= '0';
            }
        }

        return $encodedWord;
    }

    /**
     * Remove any characters replicates
     *
     * @param string $word Arabic word you want to check if it is feminine
     *
     * @return string Same word without any duplicate chracters
     * @author Khaled Al-Sham'aa <khaled@ar-php.org>
     */
    private function arSoundexTrimRep($word)
    {
        $lastChar  = null;
        $cleanWord = null;
        $max       = mb_strlen($word);

        for ($i = 0; $i < $max; $i++) {
            $char = mb_substr($word, $i, 1);

            if ($char != $lastChar) {
                $cleanWord .= $char;
            }

            $lastChar = $char;
        }

        return $cleanWord;
    }

    /**
     * Arabic soundex algorithm takes Arabic word as an input and produces a
     * character string which identifies a set words that are (roughly)
     * phonetically alike.
     *
     * @param string $word Arabic word you want to calculate its soundex
     *
     * @return string Soundex value for a given Arabic word
     * @author Khaled Al-Sham'aa <khaled@ar-php.org>
     */
    public function soundex($word)
    {
        $soundex = mb_substr($word, 0, 1);
        $rest    = mb_substr($word, 1, mb_strlen($word));

        if ($this->soundexLang == 'en') {
            $soundex = $this->soundexTransliteration[$soundex];
        }

        $encodedRest      = $this->arSoundexMapCode($rest);
        $cleanEncodedRest = $this->arSoundexTrimRep($encodedRest);

        $soundex .= $cleanEncodedRest;
        $soundex  = strtr($soundex, array('0' => ''));
        $totalLen = mb_strlen($soundex);

        if ($totalLen > $this->soundexLen) {
            $soundex = mb_substr($soundex, 0, $this->soundexLen);
        } else {
            $soundex .= str_repeat('0', $this->soundexLen - $totalLen);
        }

        return $soundex;
    }


    /////////////////////////////////////// Glyphs ////////////////////////////////////////////////

    /**
     * Add extra glyphs
     *
     * @param string  $char     Char to be added
     * @param string  $hex      String of 16 hexadecimals digits refers to the letter unicode
     *                          in the following order
     *                          ISOLATED FORM, FINAL FORM, INITIAL FORM, MEDIAL FORM
     *                          (e.g. for Arabic letter HEH 'FEE9FEEAFEEBFEEC')
     * @param boolean $prevLink If TRUE (default), when this letter be previous, then next will be linked to it
     * @param boolean $nextLink If TRUE (default), when this letter be next, then previous will be linked to it
     *
     * @return void
     * @author Khaled Al-Sham'aa <khaled@ar-php.org>
     */
    public function addGlyphs($char, $hex, $prevLink = true, $nextLink = true)
    {
        $this->arGlyphs[$char][0] = substr($hex, 0, 4);
        $this->arGlyphs[$char][1] = substr($hex, 4, 4);
        $this->arGlyphs[$char][2] = substr($hex, 8, 4);
        $this->arGlyphs[$char][3] = substr($hex, 12, 4);
        
        $this->arGlyphs[$char]["prevLink"] = $prevLink;
        $this->arGlyphs[$char]["nextLink"] = $nextLink;
    }
    
    /**
     * Convert Arabic string into glyph joining in UTF-8 hexadecimals stream
     *
     * @param string $str Arabic string in UTF-8 charset
     *
     * @return string Arabic glyph joining in UTF-8 hexadecimals stream
     * @author Khaled Al-Sham'aa <khaled@ar-php.org>
     */
    private function arGlyphsPreConvert($str)
    {
        $crntChar = null;
        $prevChar = null;
        $nextChar = null;
        $output   = '';
        $number   = '';
        $chars    = array();

        $open_range  = ')]>}';
        $close_range = '([<{';

        $_temp = mb_strlen($str);

        // split the given string to an array of chars
        for ($i = 0; $i < $_temp; $i++) {
            $chars[] = mb_substr($str, $i, 1);
        }

        $max = count($chars);

        // scan the array of chars backward to flip the sequence of Arabic chars in the string
        for ($i = $max - 1; $i >= 0; $i--) {
            $crntChar = $chars[$i];

            // by default assume the letter form is isolated
            $form = 0;

            // set the prevChar by ignore tashkeel (max of two harakat), let it be space if we process the last char
            if ($i > 0) {
                $prevChar = $chars[$i - 1];
                if (mb_strpos($this->arGlyphsVowel, $prevChar) !== false && $i > 1) {
                    $prevChar = $chars[$i - 2];

                    if (mb_strpos($this->arGlyphsVowel, $prevChar) !== false && $i > 2) {
                        $prevChar = $chars[$i - 3];
                    }
                }
            } else {
                $prevChar = ' ';
            }

            // if it is a digit, then keep adding it to the number in the correct order from left to right
            // once finish, push it to the output array as a whole number then reset the number value to empty
            if (is_numeric($crntChar)) {
                $number = $crntChar . $number;
                continue;
            } elseif (strlen($number) > 0) {
                $output .= $number;
                $number  = '';
            }

            // handle the case of open and close brackets (flip them)
            if (mb_strpos($open_range . $close_range, $crntChar) !== false) {
                $output .= ($close_range . $open_range)[mb_strpos($open_range . $close_range, $crntChar)];
                continue;
            }

            // if it is an English char, then show it as it is
            if (ord($crntChar) < 128) {
                $output  .= $crntChar;
                $nextChar = $crntChar;
                continue;
            }

            // if the current char is LAM followed by ALEF, use ALEF-LAM character, then step to the next char
            if (
                $crntChar == 'ل' && isset($nextChar)
                && (mb_strpos('آأإا', $nextChar) !== false)
            ) {
                $output = substr($output, 0, strlen($output) - 8);
                if (isset($this->arGlyphs[$prevChar]['prevLink']) && $this->arGlyphs[$prevChar]['prevLink'] == true) {
                    $output .= '&#x' . $this->arGlyphs[$crntChar . $nextChar][1] . ';';
                } else {
                    $output .= '&#x' . $this->arGlyphs[$crntChar . $nextChar][0] . ';';
                }
                if ($prevChar == 'ل') {
                    $tmp_form = (isset($this->arGlyphs[$chars[$i - 2]]['prevLink']) &&
                                 $this->arGlyphs[$chars[$i - 2]]['prevLink'] == true) ? 3 : 2;
                    $output .= '&#x' . $this->arGlyphs[$prevChar][$tmp_form] . ';';
                    $i--;
                }
                continue;
            }

            // handle the case of HARAKAT
            if (mb_strpos($this->arGlyphsVowel, $crntChar) !== false) {
                if ($crntChar == 'ّ') {
                    if (mb_strpos($this->arGlyphsVowel, $chars[$i - 1]) !== false) {
                        // check if the SHADDA & HARAKA in the middle of connected letters (form 3)
                        if (
                            ($prevChar && $this->arGlyphs[$prevChar]['prevLink'] == true) &&
                            ($nextChar && $this->arGlyphs[$nextChar]['nextLink'] == true)
                        ) {
                            $form = 3;
                        }

                        // handle the case of HARAKAT after SHADDA
                        switch ($chars[$i - 1]) {
                            case 'ً':
                                $output .= '&#x0651;&#x064B;';
                                break;
                            case 'ٌ':
                                $output .= '&#xFC5E;';
                                break;
                            case 'ٍ':
                                $output .= '&#xFC5F;';
                                break;
                            case 'َ':
                                $output .= ($form == 3) ? '&#xFCF2;' : '&#xFC60;';
                                break;
                            case 'ُ':
                                $output .= ($form == 3) ? '&#xFCF3;' : '&#xFC61;';
                                break;
                            case 'ِ':
                                $output .= ($form == 3) ? '&#xFCF4;' : '&#xFC62;';
                                break;
                        }
                    } else {
                        $output .= '&#x0651;';
                    }
                // else show HARAKAT if it is not combined with SHADDA (which processed above)
                } elseif (!isset($chars[$i + 1]) || $chars[$i + 1] != 'ّ') {
                    switch ($crntChar) {
                        case 'ً':
                            $output .= '&#x064B;';
                            break;
                        case 'ٌ':
                            $output .= '&#x064C;';
                            break;
                        case 'ٍ':
                            $output .= '&#x064D;';
                            break;
                        case 'َ':
                            $output .= '&#x064E;';
                            break;
                        case 'ُ':
                            $output .= '&#x064F;';
                            break;
                        case 'ِ':
                            $output .= '&#x0650;';
                            break;
                        case 'ْ':
                            $output .= '&#x0652;';
                            break;
                    }
                }
                continue;
            }

            // check if it should connect to the prev char, then adjust the form value accordingly
            if ($prevChar && isset($this->arGlyphs[$prevChar]) && $this->arGlyphs[$prevChar]['prevLink'] == true) {
                $form++;
            }

            // check if it should connect to the next char, the adjust the form value accordingly
            if ($nextChar && isset($this->arGlyphs[$nextChar]) && $this->arGlyphs[$nextChar]['nextLink'] == true) {
                $form += 2;
            }

            // add the current char UTF-8 code to the output string
            $output  .= '&#x' . $this->arGlyphs[$crntChar][$form] . ';';
            
            // next char will be the current one before loop (we are going backword to manage right-to-left presenting)
            $nextChar = $crntChar;
        }

        // from Arabic Presentation Forms-B, Range: FE70-FEFF,
        // file "UFE70.pdf" (in reversed order)
        // into Arabic Presentation Forms-A, Range: FB50-FDFF, file "UFB50.pdf"
        // Example: $output = strtr($output, array('&#xFEA0;&#xFEDF;' => '&#xFCC9;'));
        // Lam Jeem
        $output = $this->arGlyphsDecodeEntities($output, $exclude = array('&'));

        return $output;
    }

    /**
     * Convert Arabic string into glyph joining in UTF-8
     * hexadecimals stream (take care of whole the document including English
     * sections as well as numbers and arcs etc...)
     *
     * @param string  $text      Arabic string
     * @param integer $max_chars Max number of chars you can fit in one line
     * @param boolean $hindo     If true use Hindo digits else use Arabic digits
     * @param boolean $forcertl  If true forces RTL in the bidi algorithm
     *
     * @return string Arabic glyph joining in UTF-8 hexadecimals stream (take
     *                care of whole document including English sections as well
     *                as numbers and arcs etc...)
     * @author Khaled Al-Sham'aa <khaled@ar-php.org>
     */
    public function utf8Glyphs($text, $max_chars = 50, $hindo = true, $forcertl = false)
    {
        $lines = array();
        $pairs = array();

        $harakat = array('َ', 'ً', 'ُ', 'ٌ', 'ِ', 'ٍ');
        foreach ($harakat as $haraka) {
            $pairs["ّ$haraka"] = "{$haraka}ّ";
        }

        $text = strtr($text, $pairs);
        
        // process by line required for bidi in RTL case
        $userLines = explode("\n", $text);

        foreach ($userLines as $line) {
            // wrape long lines for bidi in RTL case
            while (mb_strlen($line) > $max_chars) {
                // find the last space before hit the max line length
                $last = mb_strrpos(mb_substr($line, 0, $max_chars), ' ');
                
                // add it as a new line in the lines array
                $lines[] = mb_substr($line, 0, $last);
                
                // the rest of the line will be our new line now to iterate
                $line = mb_substr($line, $last + 1, mb_strlen($line) - $last);
            }
        
            $lines[] = $line;
        }
        
        $outLines = array();
        
        foreach ($lines as $str) {
            // identify Arabic fragments in the line for glyphs
            $p = $this->arIdentify($str);

            // check if current line has any Arabic fragment
            if (count($p) > 0) {
                // rtl if the current line starts by Arabic or the whole text is forced to be rtl
                if ($forcertl == true || $p[0] == 0) {
                    $rtl = true;
                } else {
                    $rtl = false;
                }
                
                // block structure to save processed fragments
                $block = array();
                
                // if line does not start by Arabic, then save first non-Arabic fragment in block structure
                if ($p[0] != 0) {
                    $block[] = substr($str, 0, $p[0]);
                }
                
                // get the last Arabic fragment identifier
                $max = count($p);
                
                // if the bidi logic is rtl
                if ($rtl == true) {
                    // check the start for each Arabic fragment
                    for ($i = 0; $i < $max; $i += 2) {
                        // alter start position to include the prev. close bracket if exist
                        $p[$i] = strlen(preg_replace('/\)\s*$/', '', substr($str, 0, $p[$i])));
                    }
                }
                
                // for each Arabic fragment
                for ($i = 0; $i < $max; $i += 2) {
                    // do glyphs pre-processing and save the result in the block structure
                    $block[] = $this->arGlyphsPreConvert(substr($str, $p[$i], $p[$i + 1] - $p[$i]));
                    
                    // if we still have another Arabic fragment
                    if ($i + 2 < $max) {
                        // get the in-between non-Arabic fragment as is and save it in the block structure
                        $block[] = substr($str, $p[$i + 1], $p[$i + 2] - $p[$i + 1]);
                    } elseif ($p[$i + 1] != strlen($str)) {
                        // else, the whole fragment starts after the last Arabic fragment
                        // until the end of the string will be save as is (non-Arabic) in the block structure
                        $block[] = substr($str, $p[$i + 1], strlen($str) - $p[$i + 1]);
                    }
                }
                
                // if the logic is rtl, then reverse the blocks order before concatenate
                if ($rtl == true) {
                    $block = array_reverse($block);
                }

                // concatenate the whole string blocks
                $str = implode('', $block);
            }
            
            // add the processed string to the output lines array
            $outLines[] = $str;
        }
        
        // concatenate the whole text lines using \n
        $output = implode("\n", $outLines);
        
        // convert to Hindu numerals if requested
        if ($hindo == true) {
            $output = strtr($output, array_combine($this->numeralArabic, $this->numeralHindu));
        }

        return $output;
    }

    /**
     * Decode all HTML entities (including numerical ones) to regular UTF-8 bytes.
     * Double-escaped entities will only be decoded once
     * ("&amp;lt;" becomes "&lt;", not "<").
     *
     * @param string        $text    The text to decode entities in.
     * @param array<string> $exclude An array of characters which should not be decoded.
     *                               For example, array('<', '&', '"'). This affects
     *                               both named and numerical entities.
     *
     * @return string
     * @author Khaled Al-Sham'aa <khaled@ar-php.org>
     */
    private function arGlyphsDecodeEntities($text, $exclude = array())
    {
        // Get all named HTML entities.
        $table = array_flip(get_html_translation_table(HTML_ENTITIES, ENT_COMPAT, 'UTF-8'));
        
        // Add apostrophe (XML)
        $table['&apos;'] = "'";

        $newtable = array_diff($table, $exclude);

        // Use a regexp to select all entities in one pass, to avoid decoding double-escaped entities twice.
        $text = preg_replace_callback('/&(#x?)?([A-Fa-f0-9]+);/u', function ($matches) use ($newtable, $exclude) {
            return $this->arGlyphsDecodeEntities2($matches[1], $matches[2], $matches[0], $newtable, $exclude);
        }, $text);

        return $text;
    }

    /**
     * Helper function for decodeEntities
     *
     * @param string        $prefix    Prefix
     * @param string        $codepoint Codepoint
     * @param string        $original  Original
     * @param array<string> $table     Store named entities in a table
     * @param array<string> $exclude   An array of characters which should not be decoded
     *
     * @return string
     * @author Khaled Al-Sham'aa <khaled@ar-php.org>
     */
    private function arGlyphsDecodeEntities2($prefix, $codepoint, $original, &$table, &$exclude)
    {
        // Named entity
        if (!$prefix) {
            if (isset($table[$original])) {
                return $table[$original];
            } else {
                return $original;
            }
        }

        // Hexadecimal numerical entity
        if ($prefix == '#x') {
            $codepoint = base_convert($codepoint, 16, 10);
        }

        $str = '';
        
        // Encode codepoint as UTF-8 bytes
        if ($codepoint < 0x80) {
            $str = chr((int)$codepoint);
        } elseif ($codepoint < 0x800) {
            $str = chr(0xC0 | ((int)$codepoint >> 6)) . chr(0x80 | ((int)$codepoint & 0x3F));
        } elseif ($codepoint < 0x10000) {
            $str = chr(0xE0 | ((int)$codepoint >> 12)) . chr(0x80 | (((int)$codepoint >> 6) & 0x3F)) .
                   chr(0x80 | ((int)$codepoint & 0x3F));
        } elseif ($codepoint < 0x200000) {
            $str = chr(0xF0 | ((int)$codepoint >> 18)) . chr(0x80 | (((int)$codepoint >> 12) & 0x3F)) .
                   chr(0x80 | (((int)$codepoint >> 6) & 0x3F)) . chr(0x80 | ((int)$codepoint & 0x3F));
        }

        // Check for excluded characters
        if (in_array($str, $exclude, true)) {
            return $original;
        } else {
            return $str;
        }
    }


    /////////////////////////////////////// Query /////////////////////////////////////////////////

    /**
     * Setting value for $_fields array
     *
     * @param array<string> $arrConfig Name of the fields that SQL statement will search
     *                                 them (in array format where items are those fields names)
     *
     * @return object $this to build a fluent interface
     * @author Khaled Al-Sham'aa <khaled@ar-php.org>
     */
    public function setQueryArrFields($arrConfig)
    {
        if (is_array($arrConfig)) {
            // Get arQueryFields array
            $this->arQueryFields = $arrConfig;
        }

        return $this;
    }

    /**
     * Setting value for $_fields array
     *
     * @param string $strConfig Name of the fields that SQL statement will search
     *                          them (in string format using comma as delimated)
     *
     * @return object $this to build a fluent interface
     * @author Khaled Al-Sham'aa <khaled@ar-php.org>
     */
    public function setQueryStrFields($strConfig)
    {
        if (is_string($strConfig)) {
            // Get arQueryFields array
            $this->arQueryFields = explode(',', $strConfig);
        }

        return $this;
    }

    /**
     * Setting $mode propority value that refer to search mode [0 for OR logic | 1 for AND logic]
     *
     * @param integer $mode Setting value to be saved in the $mode propority [0 for OR logic | 1 for AND logic]
     *
     * @return object $this to build a fluent interface
     * @author Khaled Al-Sham'aa <khaled@ar-php.org>
     */
    public function setQueryMode($mode)
    {
        if (in_array($mode, array('0', '1'))) {
            // Set search mode [0 for OR logic | 1 for AND logic]
            $this->arQueryMode = $mode;
        }

        return $this;
    }

    /**
     * Getting $mode propority value that refer to search mode [0 for OR logic | 1 for AND logic]
     *
     * @return integer Value of $mode properity
     * @author Khaled Al-Sham'aa <khaled@ar-php.org>
     */
    public function getQueryMode()
    {
        // Get search mode value [0 for OR logic | 1 for AND logic]
        return $this->arQueryMode;
    }

    /**
     * Getting values of $_fields Array in array format
     *
     * @return array<string> Value of $_fields array in Array format
     * @author Khaled Al-Sham'aa <khaled@ar-php.org>
     */
    public function getQueryArrFields()
    {
        $fields = $this->arQueryFields;

        return $fields;
    }

    /**
     * Getting values of $_fields array in String format (comma delimated)
     *
     * @return string Values of $_fields array in String format (comma delimated)
     * @author Khaled Al-Sham'aa <khaled@ar-php.org>
     */
    public function getQueryStrFields()
    {
        $fields = implode(',', $this->arQueryFields);

        return $fields;
    }

    /**
     * Build WHERE section of the SQL statement using defind lex's rules, search
     * mode [AND | OR], and handle also phrases (inclosed by "") using normal
     * LIKE condition to match it as it is.
     *
     * @param string $arg String that user search for in the database table
     *
     * @return string The WHERE section in SQL statement (MySQL database engine format)
     * @author Khaled Al-Sham'aa <khaled@ar-php.org>
     */
    public function arQueryWhereCondition($arg)
    {
        $sql = '';

        //$arg   = mysql_real_escape_string($arg);
        $search  = array("\\",  "\x00", "\n",  "\r",  "'",  '"', "\x1a");
        $replace = array("\\\\","\\0","\\n", "\\r", "\'", '\"', "\\Z");
        $arg     = strtr($arg, array_combine($search, $replace));

        // Check if there are phrases in $arg should handle as it is
        $phrase = explode("\"", $arg);

        if (count($phrase) > 2) {
            // Re-init $arg variable
            // (It will contain the rest of $arg except phrases).
            $arg = '';

            for ($i = 0; $i < count($phrase); $i++) {
                $subPhrase = $phrase[$i];
                if ($i % 2 == 0 && $subPhrase != '') {
                    // Re-build $arg variable after restricting phrases
                    $arg .= $subPhrase;
                } elseif ($i % 2 == 1 && $subPhrase != '') {
                    // Handle phrases using reqular LIKE matching in MySQL
                    $wordCondition[] = $this->getWordLike($subPhrase);
                }
            }
        }

        // Handle normal $arg using lex's and regular expresion
        $words = preg_split('/\s+/', trim($arg));

        foreach ($words as $word) {
            //if (is_numeric($word) || strlen($word) > 2) {
                // Take off all the punctuation
                //$word = preg_replace("/\p{P}/", '', $word);
                $exclude = array('(', ')', '[', ']', '{', '}', ',', ';', ':', '?', '!', '،', '؛', '؟');
                $word    = strtr($word, array_fill_keys($exclude, ''));

                $wordCondition[] = $this->getWordRegExp($word);
            //}
        }

        if (!empty($wordCondition)) {
            if ($this->arQueryMode == 0) {
                $sql = '(' . implode(') OR (', $wordCondition) . ')';
            } elseif ($this->arQueryMode == 1) {
                $sql = '(' . implode(') AND (', $wordCondition) . ')';
            }
        }

        return $sql;
    }

    /**
     * Search condition in SQL format for one word in all defind fields using
     * REGEXP clause and lex's rules
     *
     * @param string $arg String (one word) that you want to build a condition for
     *
     * @return string sub SQL condition (for internal use)
     * @author Khaled Al-Sham'aa <khaled@ar-php.org>
     */
    private function getWordRegExp($arg)
    {
        $arg = $this->arQueryLex($arg);
        //$sql = implode(" REGEXP '$arg' OR ", $this->_fields) . " REGEXP '$arg'";
        $sql = ' REPLACE(' . implode(", 'ـ', '') REGEXP '$arg' OR REPLACE(", $this->arQueryFields) .
               ", 'ـ', '') REGEXP '$arg'";

        return $sql;
    }

    /**
     * Search condition in SQL format for one word in all defind fields using
     * normal LIKE clause
     *
     * @param string $arg String (one word) that you want to build a condition for
     *
     * @return string sub SQL condition (for internal use)
     * @author Khaled Al-Sham'aa <khaled@ar-php.org>
     */
    private function getWordLike($arg)
    {
        $sql = implode(" LIKE '$arg' OR ", $this->arQueryFields) . " LIKE '$arg'";

        return $sql;
    }

    /**
     * Get more relevant order by section related to the user search keywords
     *
     * @param string $arg String that user search for in the database table
     *
     * @return string sub SQL ORDER BY section
     * @author Saleh AlMatrafe <saleh@saleh.cc>
     * @author Khaled Al-Sham'aa <khaled@ar-php.org>
     */
    public function arQueryOrderBy($arg)
    {
        $wordOrder = array();
        
        // Check if there are phrases in $arg should handle as it is
        $phrase = explode("\"", $arg);
        
        if (count($phrase) > 2) {
            // Re-init $arg variable (It will contain the rest of $arg except phrases).
            $arg = '';
            for ($i = 0; $i < count($phrase); $i++) {
                if ($i % 2 == 0 && isset($phrase[$i])) {
                    // Re-build $arg variable after restricting phrases
                    $arg .= $phrase[$i];
                } elseif ($i % 2 == 1 && isset($phrase[$i])) {
                    // Handle phrases using reqular LIKE matching in MySQL
                    $wordOrder[] = $this->getWordLike($phrase[$i]);
                }
            }
        }

        // Handle normal $arg using lex's and regular expresion
        $words = explode(' ', $arg);
        foreach ($words as $word) {
            if ($word != '') {
                $wordOrder[] = 'CASE WHEN ' . $this->getWordRegExp($word) . ' THEN 1 ELSE 0 END';
            }
        }

        $order = '((' . implode(') + (', $wordOrder) . ')) DESC';

        return $order;
    }

    /**
     * This method will implement various regular expressin rules based on
     * pre-defined Arabic lexical rules
     *
     * @param string $arg String of one word user want to search for
     *
     * @return string Regular Expression format to be used in MySQL query statement
     * @author Khaled Al-Sham'aa <khaled@ar-php.org>
     */
    private function arQueryLex($arg)
    {
        $arg = preg_replace($this->arQueryLexPatterns, $this->arQueryLexReplacements, $arg);

        return $arg;
    }

    /**
     * Get most possible Arabic lexical forms for a given word
     *
     * @param string $word String that user search for
     *
     * @return array<string> list of most possible Arabic lexical forms for a given word
     * @author Khaled Al-Sham'aa <khaled@ar-php.org>
     */
    private function arQueryAllWordForms($word)
    {
        $wordForms = array($word);

        $postfix1 = array('كم', 'كن', 'نا', 'ها', 'هم', 'هن');
        $postfix2 = array('ين', 'ون', 'ان', 'ات', 'وا');

        if (mb_substr($word, 0, 2) == 'ال') {
            $word = mb_substr($word, 2, mb_strlen($word));
        }

        $len = mb_strlen($word);
        
        $wordForms[] = $word;

        $str1 = mb_substr($word, 0, -1);
        $str2 = mb_substr($word, 0, -2);
        $str3 = mb_substr($word, 0, -3);

        $last1 = mb_substr($word, -1, $len);
        $last2 = mb_substr($word, -2, $len);
        $last3 = mb_substr($word, -3, $len);

        if ($len >= 6 && $last3 == 'تين') {
            $wordForms[] = $str3;
            $wordForms[] = $str3 . 'ة';
            $wordForms[] = $word . 'ة';
        }

        if ($len >= 6 && ($last3 == 'كما' || $last3 == 'هما')) {
            $wordForms[] = $str3;
            $wordForms[] = $str3 . 'كما';
            $wordForms[] = $str3 . 'هما';
        }

        if ($len >= 5 && in_array($last2, $postfix2)) {
            $wordForms[] = $str2;
            $wordForms[] = $str2 . 'ة';
            $wordForms[] = $str2 . 'تين';

            foreach ($postfix2 as $postfix) {
                $wordForms[] = $str2 . $postfix;
            }
        }

        if ($len >= 5 && in_array($last2, $postfix1)) {
            $wordForms[] = $str2;
            $wordForms[] = $str2 . 'ي';
            $wordForms[] = $str2 . 'ك';
            $wordForms[] = $str2 . 'كما';
            $wordForms[] = $str2 . 'هما';

            foreach ($postfix1 as $postfix) {
                $wordForms[] = $str2 . $postfix;
            }
        }

        if ($len >= 5 && $last2 == 'ية') {
            $wordForms[] = $str1;
            $wordForms[] = $str2;
        }

        if (
            ($len >= 4 && ($last1 == 'ة' || $last1 == 'ه' || $last1 == 'ت'))
            || ($len >= 5 && $last2 == 'ات')
        ) {
            $wordForms[] = $str1;
            $wordForms[] = $str1 . 'ة';
            $wordForms[] = $str1 . 'ه';
            $wordForms[] = $str1 . 'ت';
            $wordForms[] = $str1 . 'ات';
        }

        if ($len >= 4 && $last1 == 'ى') {
            $wordForms[] = $str1 . 'ا';
        }
        
        if (preg_match("/(\\S{1,})ئ(\\S{1,})/", $word) != false) {
            foreach ($wordForms as $form) {
                $wordForms[] = preg_replace("/(\\S{1,})ئ(\\S{1,})/", "\\1ي\\2", $form);
            }
        }

        $trans = array('أ' => 'ا', 'إ' => 'ا', 'آ' => 'ا');
        foreach ($wordForms as $form) {
            $normWord = strtr($form, $trans);
            if ($normWord != $form) {
                $wordForms[] = $normWord;
            }
        }

        $wordForms = array_unique($wordForms);

        return $wordForms;
    }

    /**
     * Get most possible Arabic lexical forms of user search keywords
     *
     * @param string $arg String that user search for
     *
     * @return string list of most possible Arabic lexical forms for given keywords
     * @author Khaled Al-Sham'aa <khaled@ar-php.org>
     */
    public function arQueryAllForms($arg)
    {
        $wordForms = array();
        $words     = explode(' ', $arg);

        foreach ($words as $word) {
            $wordForms = array_merge($wordForms, $this->arQueryAllWordForms($word));
        }

        $str = implode(' ', $wordForms);

        return $str;
    }


    /////////////////////////////////////// Salat /////////////////////////////////////////////////

    /**
     * Setting date of day for Salat calculation
     *
     * @param integer $m Month of date you want to calculate Salat in
     * @param integer $d Day of date you want to calculate Salat in
     * @param integer $y Year (four digits) of date you want to calculate Salat in
     *
     * @return object $this to build a fluent interface
     * @author Khaled Al-Sham'aa <khaled@ar-php.org>
     */
    public function setSalatDate($m = 8, $d = 2, $y = 1975)
    {
        if (is_numeric($y) && $y > 0 && $y < 3000) {
            $this->salatYear = (int)floor($y);
        }

        if (is_numeric($m) && $m >= 1 && $m <= 12) {
            $this->salatMonth = (int)floor($m);
        }

        if (is_numeric($d) && $d >= 1 && $d <= 31) {
            $this->salatDay = (int)floor($d);
        }

        return $this;
    }

    /**
     * Setting location information for Salat calculation
     *
     * @param float   $l1 Latitude of location you want to calculate Salat time in
     * @param float   $l2 Longitude of location you want to calculate Salat time in
     * @param integer $z  Time Zone, offset from UTC (see also Greenwich Mean Time)
     * @param integer $e  Elevation, it is the observer's height in meters.
     *
     * @return object $this to build a fluent interface
     * @author Khaled Al-Sham'aa <khaled@ar-php.org>
     */
    public function setSalatLocation($l1 = 36.20278, $l2 = 37.15861, $z = 2, $e = 0)
    {
        if (is_numeric($l1) && $l1 >= -180 && $l1 <= 180) {
            $this->salatLat = $l1;
        }

        if (is_numeric($l2) && $l2 >= -180 && $l2 <= 180) {
            $this->salatLong = $l2;
        }

        if (is_numeric($z) && $z >= -12 && $z <= 12) {
            $this->salatZone = (int)floor($z);
        }

        if (is_numeric($e)) {
            $this->salatElevation = $e;
        }

        return $this;
    }

    /**
     * Setting rest of Salat calculation configuration
     *
     * Convention [Fajr Angle, Isha Angle]
     * - Muslim World League [-18, -17]
     * - Islamic Society of North America (ISNA) [-15, -15]
     * - Egyptian General Authority of Survey [-19.5, -17.5]
     * - Umm al-Qura University, Makkah [-18.5, Isha 90  min after Maghrib, 120 min during Ramadan]
     * - University of Islamic Sciences, Karachi [-18, -18]
     * - Institute of Geophysics, University of Tehran [-17.7, -14*]
     * - Shia Ithna Ashari, Leva Research Institute, Qum [-16, -14]
     *
     * (*) Isha angle is not explicitly defined in Tehran method
     * Fajr Angle = $fajrArc, Isha Angle = $ishaArc
     *
     * @param string $sch        [Shafi|Hanafi] to define Muslims Salat calculation method (affect Asr time)
     * @param float  $sunriseArc Sun rise arc (default value is -0.833333)
     * @param float  $ishaArc    Isha arc (default value is -18)
     * @param float  $fajrArc    Fajr arc (default value is -18)
     * @param string $view       [Sunni|Shia] to define Muslims Salat calculation method
     *                            (affect Maghrib and Midnight time)
     *
     * @return object $this to build a fluent interface
     * @author Khaled Al-Sham'aa <khaled@ar-php.org>
     */
    public function setSalatConf(
        $sch = 'Shafi',
        $sunriseArc = -0.833333,
        $ishaArc = -17.5,
        $fajrArc = -19.5,
        $view = 'Sunni'
    ) {
        $sch = ucfirst($sch);

        if ($sch == 'Shafi' || $sch == 'Hanafi') {
            $this->salatSchool = $sch;
        }

        if (is_numeric($sunriseArc) && $sunriseArc >= -180 && $sunriseArc <= 180) {
            $this->salatAB2 = $sunriseArc;
        }

        if (is_numeric($ishaArc) && $ishaArc >= -180 && $ishaArc <= 180) {
            $this->salatAG2 = $ishaArc;
        }

        if (is_numeric($fajrArc) && $fajrArc >= -180 && $fajrArc <= 180) {
            $this->salatAJ2 = $fajrArc;
        }

        if ($view == 'Sunni' || $view == 'Shia') {
            $this->salatView = $view;
        }

        return $this;
    }

    /**
     * Calculate Salat times for the date set in setSalatDate methode, and
     * location set in setSalatLocation.
     *
     * @return array<string> of Salat times + sun rise in the following format
     *                       hh:mm where hh is the hour in local format and 24 mode
     *                       mm is minutes with leading zero to be 2 digits always
     *                       array items is [$Fajr, $Sunrise, $Dhuhr, $Asr, $Maghrib,
     *                       $Isha, $Sunset, $Midnight, $Imsak, array $timestamps]
     * @author Khaled Al-Sham'aa <khaled@ar-php.org>
     * @author Hamid Zarrabi-Zadeh <zarrabi@scs.carleton.ca>
     * @source http://praytimes.org/calculation
     */
    public function getPrayTime()
    {
        $unixtimestamp = mktime(0, 0, 0, $this->salatMonth, $this->salatDay, $this->salatYear);

        // Calculate Julian date
        if ($this->salatMonth <= 2) {
            $year  = $this->salatYear - 1;
            $month = $this->salatMonth + 12;
        } else {
            $year  = $this->salatYear;
            $month = $this->salatMonth;
        }

        $A = floor($year / 100);
        $B = 2 - $A + floor($A / 4);

        $jd = floor(365.25 * ($year + 4716)) + floor(30.6001 * ($month + 1)) + $this->salatDay + $B - 1524.5;

        // The following algorithm from U.S. Naval Observatory computes the
        // Sun's angular coordinates to an accuracy of about 1 arcminute within
        // two centuries of 2000.
        $d = $jd - 2451545.0;  // jd is the given Julian date

        // The following algorithm from U.S. Naval Observatory computes the Sun's
        // angular coordinates to an accuracy of about 1 arcminute within two
        // centuries of 2000
        // http://aa.usno.navy.mil/faq/docs/SunApprox.php
        // Note: mod % in PHP ignore decimels!
        $g = 357.529 + 0.98560028 * $d;
        $g = $g % 360 + ($g - ceil($g) + 1);

        $q = 280.459 + 0.98564736 * $d;
        $q = $q % 360 + ($q - ceil($q) + 1);

        $L = $q + 1.915 * sin(deg2rad($g)) + 0.020 * sin(deg2rad(2 * $g));
        $L = $L % 360 + ($L - ceil($L) + 1);

        $R = 1.00014 - 0.01671 * cos(deg2rad($g)) - 0.00014 * cos(deg2rad(2 * $g));
        $e = 23.439 - 0.00000036 * $d;

        $RA = rad2deg(atan2(cos(deg2rad($e)) * sin(deg2rad($L)), cos(deg2rad($L)))) / 15;

        if ($RA < 0) {
            $RA = 24 + $RA;
        }

        // The declination of the Sun is the angle between the rays of the sun and
        // the plane of the earth equator. The declination of the Sun changes
        // continuously throughout the year. This is a consequence of the Earth's
        // tilt, i.e. the difference in its rotational and revolutionary axes.
        // declination of the Sun
        $D = rad2deg(asin(sin(deg2rad($e)) * sin(deg2rad($L))));

        // The equation of time is the difference between time as read from sundial
        // and a clock. It results from an apparent irregular movement of the Sun
        // caused by a combination of the obliquity of the Earth's rotation axis
        // and the eccentricity of its orbit. The sundial can be ahead (fast) by
        // as much as 16 min 33 s (around November 3) or fall behind by as much as
        // 14 min 6 s (around February 12), as shown in the following graph:
        // http://en.wikipedia.org/wiki/File:Equation_of_time.png
        $EqT = ($q / 15) - $RA;  // equation of time

        // Dhuhr
        // When the Sun begins to decline after reaching its highest point in the sky
        $Dhuhr = 12 + $this->salatZone - ($this->salatLong / 15) - $EqT;
        if ($Dhuhr < 0) {
            $Dhuhr = 24 + $Dhuhr;
        }

        // Sunrise & Sunset
        // If the observer's location is higher than the surrounding terrain, we
        // can consider this elevation into consideration by increasing the above
        // constant 0.833 by 0.0347 × sqrt(elevation), where elevation is the
        // observer's height in meters.
        $alpha = -1 * $this->salatAB2 + 0.0347 * sqrt($this->salatElevation);
        $n     = -1 * sin(deg2rad($alpha)) - sin(deg2rad($this->salatLat)) * sin(deg2rad($D));
        $d     = cos(deg2rad($this->salatLat)) * cos(deg2rad($D));

        // date_sun_info Returns an array with information about sunset/sunrise
        // and twilight begin/end
        $Sunrise = $Dhuhr - (1 / 15) * rad2deg(acos($n / $d));
        $Sunset  = $Dhuhr + (1 / 15) * rad2deg(acos($n / $d));

        // Fajr & Isha
        // Imsak: The time to stop eating Sahur (for fasting), slightly before Fajr.
        // Fajr:  When the sky begins to lighten (dawn).
        // Isha:  The time at which darkness falls and there is no scattered light
        //        in the sky.
        $n     = -1 * sin(deg2rad(abs($this->salatAJ2))) - sin(deg2rad($this->salatLat)) * sin(deg2rad($D));
        $Fajr  = $Dhuhr - (1 / 15) * rad2deg(acos($n / $d));
        $Imsak = $Fajr - (10 / 60);

        $n     = -1 * sin(deg2rad(abs($this->salatAG2))) - sin(deg2rad($this->salatLat)) * sin(deg2rad($D));
        $Isha  = $Dhuhr + (1 / 15) * rad2deg(acos($n / $d));

        // Asr
        // The following formula computes the time difference between the mid-day
        // and the time at which the object's shadow equals t times the length of
        // the object itself plus the length of that object's shadow at noon
        if ($this->salatSchool == 'Shafi') {
            $n = sin(atan(1 / (1 + tan(deg2rad($this->salatLat - $D))))) -
                 sin(deg2rad($this->salatLat)) * sin(deg2rad($D));
        } else {
            $n = sin(atan(1 / (2 + tan(deg2rad($this->salatLat - $D))))) -
                 sin(deg2rad($this->salatLat)) * sin(deg2rad($D));
        }

        $Asr = $Dhuhr + (1 / 15) * rad2deg(acos($n / $d));

        // Maghrib
        if ($this->salatView == 'Sunni') {
            // In the Sunni's point of view, the time for Maghrib prayer begins once
            // the Sun has completely set beneath the horizon, that is, Maghrib = Sunset
            // (some calculators suggest 1 to 3 minutes after Sunset for precaution)
            $Maghrib = $Sunset + 2 / 60;
        } else {
            // In the Shia's view, however, the dominant opinion is that as long as
            // the redness in the eastern sky appearing after sunset has not passed
            // overhead, Maghrib prayer should not be performed.
            $n       = -1 * sin(deg2rad(4)) - sin(deg2rad($this->salatLat)) * sin(deg2rad($D));
            $Maghrib = $Dhuhr + (1 / 15) * rad2deg(acos($n / $d));
        }

        // Midnight
        if ($this->salatView == 'Sunni') {
            // Midnight is generally calculated as the mean time from Sunset to Sunrise
            $Midnight = $Sunset + 0.5 * ($Sunrise - $Sunset);
        } else {
            // In Shia point of view, the juridical midnight (the ending time for
            // performing Isha prayer) is the mean time from Sunset to Fajr
            $Midnight = $Sunset + 0.5 * ($Fajr - $Sunset);
        }

        if ($Midnight > 12) {
            $Midnight = $Midnight - 12;
        } else {
            $Midnight = $Midnight + 12;
        }

        // Result.ThlthAkhir:= Result.Fajr - (24 - Result.Maghrib + Result.Fajr) / 3;
        // Result.Doha      := Result.Sunrise + (15 / 60);
        // if isRamadan then (Um-Al-Qura calendar)
        // Result.Isha      := Result.Maghrib + 2
        // else Result.Isha := Result.Maghrib + 1.5;

        $times = array($Fajr, $Sunrise, $Dhuhr, $Asr, $Maghrib, $Isha, $Sunset, $Midnight, $Imsak);

        // Convert number after the decimal point into minutes
        foreach ($times as $index => $time) {
            $hours   = (int)floor($time);
            $minutes = round(($time - $hours) * 60);

            if ($minutes < 10) {
                $minutes = "0$minutes";
            }

            $times[$index] = "$hours:$minutes";

            $times[9][$index] = $unixtimestamp + 3600 * $hours + 60 * $minutes;

            if ($index == 7 && $hours < 6) {
                $times[9][$index] += 24 * 3600;
            }
        }

        return $times;
    }

    /**
     * Determine Qibla direction using basic spherical trigonometric formula
     *
     * @return float Qibla Direction (from the north direction) in degrees
     * @author Khaled Al-Sham'aa <khaled@ar-php.org>
     * @author S. Kamal Abdali <k.abdali@acm.org>
     * @source http://www.patriot.net/users/abdali/ftp/qibla.pdf
     */
    public function getQibla()
    {
        // The geographical coordinates of the Ka'ba
        $K_latitude  = 21.423333;
        $K_longitude = 39.823333;

        $latitude  = $this->salatLat;
        $longitude = $this->salatLong;

        $numerator   = sin(deg2rad($K_longitude - $longitude));
        $denominator = (cos(deg2rad($latitude)) * tan(deg2rad($K_latitude))) -
                       (sin(deg2rad($latitude)) * cos(deg2rad($K_longitude - $longitude)));

        $q = atan($numerator / $denominator);
        $q = rad2deg($q);

        if ($this->salatLat > 21.423333) {
            $q += 180;
        }

        return $q;
    }

    /**
     * Convert coordinates presented in degrees, minutes and seconds
     * (e.g. 12°34'56"S formula) into usual float number in degree unit scale
     * (e.g. -12.5822 value)
     *
     * @param string $value Coordinate presented in degrees, minutes and seconds (e.g. 12°34'56"S formula)
     *
     * @return float Equivalent float number in degree unit scale (e.g. -12.5822 value)
     * @author Khaled Al-Sham'aa <khaled@ar-php.org>
     */
    public function dms2dd($value)
    {
        $pattern = "/(\d{1,2})°((\d{1,2})')?((\d{1,2})\")?([NSEW])/i";

        preg_match($pattern, $value, $matches);

        $degree = (int)$matches[1] + ((int)$matches[3] / 60) + ((float)$matches[5] / 3600);

        $direction = strtoupper($matches[6]);

        if ($direction == 'S' || $direction == 'W') {
            $degree = -1 * $degree;
        }

        return $degree;
    }

    /**
     * Convert coordinates presented in float number in degree unit scale
     * (e.g. -12.5822 value) into degrees, minutes and seconds (e.g. -12°34'56" formula)
     *
     * @param float $value Coordinate presented in float number in degree unit scale (e.g. -12.5822 value)
     *
     * @return string Equivalent coordinate presented in degrees, minutes and seconds (e.g. -12°34'56" formula)
     * @author Khaled Al-Sham'aa <khaled@ar-php.org>
     */
    public function dd2dms($value)
    {
        if ($value < 0) {
            $value = abs($value);
            $dd    = '-';
        } else {
            $dd = '';
        }
        
        $degrees = (int)$value;
        $minutes = (int)(($value - $degrees) * 60);
        $seconds = round(((($value - $degrees) * 60) - $minutes) * 60, 4);

        if ($degrees > 0) {
            $dd .= $degrees . '°';
        }
        
        if ($minutes >= 10) {
            $dd .= $minutes . '\'';
        } else {
            $dd .= '0' . $minutes . '\'';
        }
        
        if ($seconds >= 10) {
            $dd .= $seconds . '"';
        } else {
            $dd .= '0' . $seconds . '"';
        }

        return $dd;
    }


    /////////////////////////////////////// Summary ///////////////////////////////////////////////

    /**
     * Load enhanced Arabic stop words list
     *
     * @return void
     * @author Khaled Al-Sham'aa <khaled@ar-php.org>
     */
    public function arSummaryLoadExtra()
    {
        $extra_words = file($this->rootDirectory . '/data/ar_stopwords_extra.txt');
        $extra_words = array_map('trim', $extra_words);

        $this->arSummaryCommonWords = array_merge($this->arSummaryCommonWords, $extra_words);
    }

    /**
     * Core summarize function that implement required steps in the algorithm
     *
     * @param string  $str      Input Arabic document as a string
     * @param string  $keywords List of keywords higlited by search process
     * @param integer $int      Sentences value (see $mode effect also)
     * @param integer $mode     Mode of sentences count [1|2] for "number" and "rate" modes respectively
     * @param integer $output   Output mode [1|2] for "summary" and "highlight" modes respectively
     *
     * @return string Output summary requested
     * @author Khaled Al-Sham'aa <khaled@ar-php.org>
     */
    public function arSummary($str, $keywords, $int, $mode, $output)
    {
        preg_match_all("/[^\.\n\،\؛\,\;](.+?)[\.\n\،\؛\,\;]/u", $str, $sentences);
        $_sentences = $sentences[0];

        if ($mode == 1) {
            $str            = preg_replace("/\s{2,}/u", ' ', $str);
            $totalChars     = mb_strlen($str);
            $totalSentences = count($_sentences);

            $maxChars = round($int * $totalChars / 100);
            $int      = round($int * $totalSentences / 100);
        } else {
            $maxChars = 99999;
        }

        $summary = '';

        $str           = strip_tags($str);
        $normalizedStr = $this->arNormalize($str);
        $cleanedStr    = $this->arCleanCommon($normalizedStr);
        $stemStr       = $this->arDraftStem($cleanedStr);

        preg_match_all("/[^\.\n\،\؛\,\;](.+?)[\.\n\،\؛\,\;]/u", $stemStr, $sentences);
        $_stemmedSentences = $sentences[0];

        $wordRanks = $this->arSummaryRankWords($stemStr);

        if ($keywords) {
            $keywords = $this->arNormalize($keywords);
            $keywords = $this->arDraftStem($keywords);
            $words    = explode(' ', $keywords);

            foreach ($words as $word) {
                $wordRanks[$word] = 1000;
            }
        }

        $sentencesRanks          = $this->arSummaryRankSentences($_sentences, $_stemmedSentences, $wordRanks);
        list($sentences, $ranks) = $sentencesRanks;
        $minRank                 = $this->arSummaryMinAcceptedRank($sentences, $ranks, $int, $maxChars);
        $totalSentences          = count($ranks);

        for ($i = 0; $i < $totalSentences; $i++) {
            if ($sentencesRanks[1][$i] >= $minRank) {
                if ($output == 1) {
                    $summary .= ' ' . $sentencesRanks[0][$i];
                } else {
                    $summary .= '<mark>' . $sentencesRanks[0][$i] . '</mark>';
                }
            } else {
                if ($output == 2) {
                    $summary .= $sentencesRanks[0][$i];
                }
            }
        }

        if ($output == 2) {
            $summary = strtr($summary, array("\n" => '<br />'));
        }

        return $summary;
    }

    /**
     * Extract keywords from a given Arabic string (document content)
     *
     * @param string  $str Input Arabic document as a string
     * @param integer $int Number of keywords required to be extracting from input string (document content)
     *
     * @return string List of the keywords extracting from input Arabic string (document content)
     * @author Khaled Al-Sham'aa <khaled@ar-php.org>
     */
    public function arSummaryKeywords($str, $int)
    {
        $patterns     = array();
        $replacements = array();
        $metaKeywords = '';

        $patterns[]     = '/\.|\n|\،|\؛|\(|\[|\{|\)|\]|\}|\,|\;/u';
        $replacements[] = ' ';
        
        $str = preg_replace($patterns, $replacements, $str);

        $normalizedStr = $this->arNormalize($str);
        $cleanedStr    = $this->arCleanCommon($normalizedStr);

        $str = preg_replace('/(\W)ال(\w{3,})/u', '\\1\\2', $cleanedStr);
        $str = preg_replace('/(\W)وال(\w{3,})/u', '\\1\\2', $str);
        $str = preg_replace('/(\w{3,})هما(\W)/u', '\\1\\2', $str);
        $str = preg_replace('/(\w{3,})كما(\W)/u', '\\1\\2', $str);
        $str = preg_replace('/(\w{3,})تين(\W)/u', '\\1\\2', $str);
        $str = preg_replace('/(\w{3,})هم(\W)/u', '\\1\\2', $str);
        $str = preg_replace('/(\w{3,})هن(\W)/u', '\\1\\2', $str);
        $str = preg_replace('/(\w{3,})ها(\W)/u', '\\1\\2', $str);
        $str = preg_replace('/(\w{3,})نا(\W)/u', '\\1\\2', $str);
        $str = preg_replace('/(\w{3,})ني(\W)/u', '\\1\\2', $str);
        $str = preg_replace('/(\w{3,})كم(\W)/u', '\\1\\2', $str);
        $str = preg_replace('/(\w{3,})تم(\W)/u', '\\1\\2', $str);
        $str = preg_replace('/(\w{3,})كن(\W)/u', '\\1\\2', $str);
        $str = preg_replace('/(\w{3,})ات(\W)/u', '\\1\\2', $str);
        $str = preg_replace('/(\w{3,})ين(\W)/u', '\\1\\2', $str);
        $str = preg_replace('/(\w{3,})تن(\W)/u', '\\1\\2', $str);
        $str = preg_replace('/(\w{3,})ون(\W)/u', '\\1\\2', $str);
        $str = preg_replace('/(\w{3,})ان(\W)/u', '\\1\\2', $str);
        $str = preg_replace('/(\w{3,})تا(\W)/u', '\\1\\2', $str);
        $str = preg_replace('/(\w{3,})وا(\W)/u', '\\1\\2', $str);
        $str = preg_replace('/(\w{3,})ة(\W)/u', '\\1\\2', $str);

        $stemStr = preg_replace('/(\W)\w{1,3}(\W)/u', '\\2', $str);

        $wordRanks = $this->arSummaryRankWords($stemStr);

        arsort($wordRanks, SORT_NUMERIC);

        $i = 1;
        foreach ($wordRanks as $key => $value) {
            if ($this->arSummaryAcceptedWord($key)) {
                $metaKeywords .= $key . '، ';
                $i++;
            }
            if ($i > $int) {
                break;
            }
        }

        $metaKeywords = mb_substr($metaKeywords, 0, -2);

        return $metaKeywords;
    }

    /**
     * Normalized Arabic document
     *
     * @param string $str Input Arabic document as a string
     *
     * @return string Normalized Arabic document
     * @author Khaled Al-Sham'aa <khaled@ar-php.org>
     */
    private function arNormalize($str)
    {
        $str = strtr($str, array_fill_keys($this->arNormalizeAlef, 'ا'));
        $str = strtr($str, array_fill_keys($this->arNormalizeDiacritics, ''));
        $str = strtr($str, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', 'abcdefghijklmnopqrstuvwxyz');

        return $str;
    }

    /**
     * Extracting common Arabic words (roughly)
     * from input Arabic string (document content)
     *
     * @param string $str Input normalized Arabic document as a string
     *
     * @return string Arabic document as a string free of common words (roughly)
     * @author Khaled Al-Sham'aa <khaled@ar-php.org>
     */
    private function arCleanCommon($str)
    {
        $str = str_replace(' ', '  ', $str);
        $str = strtr(" $str", array_fill_keys($this->arSummaryCommonWords, ' '));
        $str = str_replace('  ', ' ', $str);

        return trim($str);
    }

    /**
     * Remove less significant Arabic letter from given string (document content).
     * Please note that output will not be human readable.
     *
     * @param string $str Input Arabic document as a string
     *
     * @return string Output string after removing less significant Arabic letter (not human readable output)
     * @author Khaled Al-Sham'aa <khaled@ar-php.org>
     */
    private function arDraftStem($str)
    {
        $str = strtr($str, array_fill_keys($this->arCommonChars, ''));

        return $str;
    }

    /**
     * Ranks words in a given Arabic string (document content). That rank refers
     * to the frequency of that word appears in that given document.
     *
     * @param string $str Input Arabic document as a string
     *
     * @return array<int> Associated array where document words referred by index and
     *                    those words ranks referred by values of those array items.
     * @author Khaled Al-Sham'aa <khaled@ar-php.org>
     */
    private function arSummaryRankWords($str)
    {
        $wordsRanks = array();

        $str   = strtr($str, array_fill_keys($this->arSeparators, ' '));
        $words = preg_split("/[\s,]+/u", $str);

        foreach ($words as $word) {
            if (isset($wordsRanks[$word])) {
                $wordsRanks[$word]++;
            } else {
                $wordsRanks[$word] = 1;
            }
        }

        return $wordsRanks;
    }

    /**
     * Ranks sentences in a given Arabic string (document content).
     *
     * @param array<string> $sentences        Sentences of the input Arabic document as an array
     * @param array<string> $stemmedSentences Stemmed sentences of the input Arabic document as an array
     * @param array<int>    $arr              Words ranks array (word as an index and value refer to the word frequency)
     *
     * @return array<int, array<int, float|int|string>> Two dimension array, first item is an array of document
     *                                        sentences, second item is an array of ranks of document sentences.
     * @author Khaled Al-Sham'aa <khaled@ar-php.org>
     */
    private function arSummaryRankSentences($sentences, $stemmedSentences, $arr)
    {
        $sentenceArr = array();
        $rankArr     = array();
        $importent   = implode('|', $this->arSummaryImportantWords);

        $max = count($sentences);

        for ($i = 0; $i < $max; $i++) {
            $sentence = $sentences[$i];

            $w     = 0;
            $first = mb_substr($sentence, 0, 1);
            $last  = mb_substr($sentence, -1, 1);

            if ($first == "\n") {
                $w += 3;
            } elseif (in_array($first, $this->arSeparators, true)) {
                $w += 2;
            } else {
                $w += 1;
            }

            if ($last == "\n") {
                $w += 3;
            } elseif (in_array($last, $this->arSeparators, true)) {
                $w += 2;
            } else {
                $w += 1;
            }

            preg_match_all('/(' . $importent . ')/', $sentence, $out);
            $w += count($out[0]);

            $_sentence = mb_substr($sentence, 0, -1);
            $sentence  = mb_substr($_sentence, 1, mb_strlen($_sentence));

            if (!in_array($first, $this->arSeparators, true)) {
                $sentence = $first . $sentence;
            }

            $stemStr = $stemmedSentences[$i];
            $stemStr = mb_substr($stemStr, 0, -1);

            $words = preg_split("/[\s,]+/u", $stemStr);

            $totalWords = count($words);
            if ($totalWords > 4) {
                $totalWordsRank = 0;

                foreach ($words as $word) {
                    if (isset($arr[$word])) {
                        $totalWordsRank += $arr[$word];
                    }
                }

                $wordsRank     = $totalWordsRank / $totalWords;
                $sentenceRanks = $w * $wordsRank;

                $sentenceArr[] = $sentence . $last;
                $rankArr[]     = $sentenceRanks;
            }
        }

        $sentencesRanks = array($sentenceArr, $rankArr);

        return $sentencesRanks;
    }

    /**
     * Calculate minimum rank for sentences which will be including in the summary
     *
     * @param array<string> $str Document sentences
     * @param array<int>    $arr Sentences ranks
     * @param integer $int  Number of sentences you need to include in your summary
     * @param integer $max  Maximum number of characters accepted in your summary
     *
     * @return integer Minimum accepted sentence rank (sentences with rank more
     *                 than this will be listed in the document summary)
     * @author Khaled Al-Sham'aa <khaled@ar-php.org>
     */
    private function arSummaryMinAcceptedRank($str, $arr, $int, $max)
    {
        $len = array();

        foreach ($str as $line) {
            $len[] = mb_strlen($line);
        }

        rsort($arr, SORT_NUMERIC);

        $totalChars = 0;
        $minRank = 0;

        for ($i = 0; $i <= $int; $i++) {
            if (!isset($arr[$i])) {
                $minRank = 0;
                break;
            }

            $totalChars += $len[$i];

            if ($totalChars >= $max) {
                $minRank = $arr[$i];
                break;
            }

            $minRank = $arr[$i];
        }

        return $minRank;
    }

    /**
     * Check some conditions to know if a given string is a formal valid word or not
     *
     * @param string $word String to be checked if it is a valid word or not
     *
     * @return boolean True if passed string is accepted as a valid word else it will return False
     * @author Khaled Al-Sham'aa <khaled@ar-php.org>
     */
    private function arSummaryAcceptedWord($word)
    {
        $accept = true;

        if (mb_strlen($word) < 3) {
            $accept = false;
        }

        return $accept;
    }

    /////////////////////////////////////// Identifier ///////////////////////////////////////////////

    /**
     * Identify Arabic text in a given UTF-8 multi language string
     *
     * @param string  $str  UTF-8 multi language string
     * @param boolean $html If True, then ignore the HTML tags (default is TRUE)
     *
     * @return array<int> Offset of the beginning and end of each Arabic segment in
     *                    sequence in the given UTF-8 multi language string
     * @author Khaled Al-Sham'aa <khaled@ar-php.org>
     */
    public function arIdentify($str, $html = true)
    {
        // https://utf8-chartable.de/unicode-utf8-table.pl?start=1536&number=128&utf8=dec
        $minAr    = 55424;
        $maxAr    = 55743;
        
        $probAr   = false;
        $arFlag   = false;
        $htmlFlag = false;
        $arRef    = array();
        $max      = strlen($str);
        $ascii    = unpack('C*', $str);

        $i = -1;
        while (++$i < $max) {
            $cDec = $ascii[$i + 1];

            if ($html == true) {
                if ($cDec == 60 && $ascii[$i + 2] != 32) {
                    $htmlFlag = true;
                } elseif ($htmlFlag == true && $cDec == 62) {
                    $htmlFlag = false;
                } elseif ($htmlFlag == true) {
                    continue;
                }
            }

            // ignore ! " # $ % & ' ( ) * + , - . / 0 1 2 3 4 5 6 7 8 9 :
            // If it come in the Arabic context
            if ($cDec >= 33 && $cDec <= 58) {
                continue;
            }
            
            if (!$probAr && ($cDec == 216 || $cDec == 217)) {
                $probAr = true;
                continue;
            }

            if ($i > 0) {
                $pDec = $ascii[$i];
            } else {
                $pDec = null;
            }

            if ($probAr) {
                $utfDecCode = ($pDec << 8) + $cDec;
                if ($utfDecCode >= $minAr && $utfDecCode <= $maxAr) {
                    if (!$arFlag) {
                        $arFlag  = true;
                        // include the previous open bracket ( if it is exists
                        $sp = strlen(rtrim(substr($str, 0, $i - 1))) - 1;
                        if ($str[$sp] == '(') {
                            $arRef[] = $sp;
                        } else {
                            $arRef[] = $i - 1;
                        }
                    }
                } else {
                    if ($arFlag) {
                        $arFlag  = false;
                        $arRef[] = $i - 1;
                    }
                }
                
                $probAr = false;
                continue;
            }
            
            if ($arFlag && !preg_match("/^\s$/", $str[$i])) {
                $arFlag  = false;
                // tag out the trailer spaces
                $sp = $i - strlen(rtrim(substr($str, 0, $i)));
                $arRef[] = $i - $sp;
            }
        }

        if ($arFlag) {
            $arRef[] = $i;
        }

        return $arRef;
    }
    
    /**
     * Find out if given string is Arabic text or not
     *
     * @param string $str String
     *
     * @return boolean True if given string is UTF-8 Arabic, else will return False
     * @author Khaled Al-Sham'aa <khaled@ar-php.org>
     */
    public function isArabic($str)
    {
        $val = false;
        $arr = $this->arIdentify($str);

        if (count($arr) == 2 && $arr[0] == 0 && $arr[1] == strlen($str)) {
            $val = true;
        }
        
        return $val;
    }
    
    /**
     * Encode a location coordinates (latitude and longitude in WGS84) into Open Location Code
     * Ref: https://github.com/google/open-location-code/blob/master/docs/specification.md
     *
     * @param float   $latitude   Coordinate presented in float number in degree unit scale (e.g. 34.67175)
     * @param float   $longitude  Coordinate presented in float number in degree unit scale (e.g. 36.263625)
     * @param integer $codeLength Code length, default value is 10 (this provides an area that is
     *                            1/8000 x 1/8000 degree in size, roughly 14x14 meters)
     *
     * @return string Open Location Code string (e.g. 8G6RM7C7+PF)
     * @author Khaled Al-Sham'aa <khaled@ar-php.org>
     */
    public function dd2olc($latitude, $longitude, $codeLength = 10)
    {
        $codeLength = $codeLength / 2;
        $validChars = '23456789CFGHJMPQRVWX';
        
        $latitude  = $latitude + 90;
        $longitude = $longitude + 180;
        
        $latitude  = round($latitude * pow(20, $codeLength - 2), 0);
        $longitude = round($longitude * pow(20, $codeLength - 2), 0);
        
        $olc = '';
        
        for ($i = 1; $i <= $codeLength; $i++) {
            $x = $longitude % 20;
            $y = $latitude % 20;
            
            $longitude = floor($longitude / 20);
            $latitude  = floor($latitude / 20);
            
            $olc = substr($validChars, $y, 1) . substr($validChars, $x, 1) . $olc;
            
            if ($i == 1) {
                $olc = '+' . $olc;
            }
        }
        
        return $olc;
    }
    
    /**
     * Decode an Open Location Code string into its location coordinates in decimal degrees.
     * Ref: https://github.com/google/open-location-code/blob/master/docs/specification.md
     *
     * @param string  $olc        Open Location Code string (e.g. 8G6RM7C7+PF)
     * @param integer $codeLength Code length, default value is 10 (this provides an area that is
     *                            1/8000 x 1/8000 degree in size, roughly 14x14 meters)
     *
     * @return array<null|float>  Location coordinates in decimal degrees [latitude, longitude] in WGS84
     * @author Khaled Al-Sham'aa <khaled@ar-php.org>
     */
    public function olc2dd($olc, $codeLength = 10)
    {
        $coordinates = array();
        
        if ($this->volc($olc, $codeLength)) {
            $codeLength = $codeLength / 2;
            $validChars = '23456789CFGHJMPQRVWX';
            
            $olc = strtoupper(strtr($olc, array('+' => '')));

            $latitude  = 0;
            $longitude = 0;

            for ($i = 1; $i <= $codeLength; $i++) {
                $latitude  = $latitude + strpos($validChars, substr($olc, 2 * $i - 2, 1)) * pow(20, 2 - $i);
                $longitude = $longitude + strpos($validChars, substr($olc, 2 * $i - 1, 1)) * pow(20, 2 - $i);
            }

            $coordinates[] = $latitude - 90;
            $coordinates[] = $longitude - 180;
        } else {
            $coordinates[] = null;
            $coordinates[] = null;
        }
        
        return $coordinates;
    }
    
    /**
     * Determine if an Open Location Code is valid.
     * Ref: https://github.com/google/open-location-code/blob/master/docs/specification.md
     *
     * @param string  $olc        Open Location Code string (e.g. 8G6RM7C7+PF)
     * @param integer $codeLength Code length, default value is 10 (this provides an area that is
     *                            1/8000 x 1/8000 degree in size, roughly 14x14 meters)
     *
     * @return boolean String represents a valid Open Location Code.
     * @author Khaled Al-Sham'aa <khaled@ar-php.org>
     */
    public function volc($olc, $codeLength = 10)
    {
        if (strlen($olc) != $codeLength + 1) {
            $isValid = false;
        } elseif (substr($olc, -3, 1) != '+') {
            $isValid = false;
        } elseif (preg_match('/[^2-9CFGHJMPQRVWX+]/', strtoupper($olc))) {
            $isValid = false;
        } else {
            $isValid = true;
        }
        
        return $isValid;
    }
    
    /**
     * Get proper Arabic plural form
     * There are 4 plural forms in Arabic language:
     * - Form for 2
     * - Form for numbers that end with a number between 3 and 10 (like: 103, 1405, 23409)
     * - Form for numbers that end with a number between 11 and 99 (like: 1099, 278)
     * - Form for numbers above 100 ending with 0, 1 or 2 (like: 100, 232, 3001)
     *
     * @param string  $singular Singular word (e.g., عنصر).
     * @param integer $count    The number (e.g. item count) to determine the proper plural form.
     * @param string  $plural2  Plural form 2 (e.g., عنصران). If NULL [default] retrive from internal JSON dataset.
     * @param string  $plural3  Plural form 3 (e.g., عناصر). If NULL [default] retrive from internal JSON dataset.
     * @param string  $plural4  Plural form 4 (e.g., عنصرا). If NULL [default] retrive from internal JSON dataset.
     *
     * @return string Proper plural form of the given singular form
     * @author Khaled Al-Sham'aa <khaled@ar-php.org>
     */
    public function arPlural($singular, $count, $plural2 = null, $plural3 = null, $plural4 = null)
    {
        if ($count == 0) {
            $plural = is_null($plural2) ? $this->arPluralsForms[$singular][0] : "لا $plural3";
        } elseif ($count == 1 && $this->isFemale($singular)) {
            $plural = is_null($plural2) ? $this->arPluralsForms[$singular][1] : "$singular واحدة";
        } elseif ($count == 1 && !$this->isFemale($singular)) {
            $plural = is_null($plural2) ? $this->arPluralsForms[$singular][1] : "$singular واحد";
        } elseif ($count == 2) {
            $plural = is_null($plural2) ? $this->arPluralsForms[$singular][2] : $plural2;
        } elseif ($count % 100 >= 3 && $count % 100 <= 10) {
            $plural = is_null($plural2) ? $this->arPluralsForms[$singular][3] : "%d $plural3";
        } elseif ($count % 100 >= 11) {
            $plural = is_null($plural2) ? $this->arPluralsForms[$singular][4] : "%d $plural4";
        } else {
            $plural = is_null($plural2) ? $this->arPluralsForms[$singular][5] : "%d $singular";
        }
        
        return $plural;
    }
    
    /**
     * Strip Harakat
     *
     * @param string  $text    Arabic text you would like to strip Harakat from it.
     * @param boolean $tatweel Strip Tatweel (default is TRUE).
     * @param boolean $tanwen  Strip Tanwen (default is TRUE).
     * @param boolean $shadda  Strip Shadda (default is TRUE).
     * @param boolean $last    Strip last Harakat (default is TRUE).
     * @param boolean $harakat Strip in word Harakat (default is TRUE).
     *
     * @return string Arabic string clean from selected Harakat
     * @author Khaled Al-Sham'aa <khaled@ar-php.org>
     */
    public function stripHarakat($text, $tatweel = true, $tanwen = true, $shadda = true, $last = true, $harakat = true)
    {
        $text = $this->setNorm('stripTatweel', $tatweel)
                     ->setNorm('stripTanween', $tanwen)
                     ->setNorm('stripShadda', $shadda)
                     ->setNorm('stripLastHarakat', $last)
                     ->setNorm('stripWordHarakat', $harakat)
                     ->setNorm('normaliseLamAlef', true)
                     ->setNorm('normaliseAlef', false)
                     ->setNorm('normaliseHamza', false)
                     ->setNorm('normaliseTaa', false)
                     ->arNormalizeText($text);

        return $text;
    }
    
    /**
     * Arabic Sentiment Analysis
     *
     * @param string $text Arabic review string
     *
     * @return array<boolean|float> of 2 elements: boolean isPositive (negative if false),
     *                              and float probability (range from 0 to 1)
     * @author Khaled Al-Sham'aa <khaled@ar-php.org>
     */
    public function arSentiment($text)
    {
        # remove mentions
        $text = preg_replace('/@\\S+/u', '', $text);

        # remove hashtags
        $text = preg_replace('/#\\S+/u', '', $text);

        # normalise Alef, Hamza, and Taa
        $text = $this->setNorm('normaliseAlef', true)
                     ->setNorm('normaliseHamza', true)
                     ->setNorm('normaliseTaa', true)
                     ->arNormalizeText($text);

        # filter only Arabic text (white list)
        $text = preg_replace('/[^ ءابتثجحخدذرزسشصضطظعغفقكلمنهوي]+/u', ' ', $text);

        # exclude one letter words
        $text = preg_replace('/\\b\\S{1}\\b/u', ' ', $text);

        # remove extra spaces
        $text = preg_replace('/\\s{2,}/u', ' ', $text);
        $text = preg_replace('/^\\s+/u', '', $text);
        $text = preg_replace('/\\s+$/u', '', $text);

        # split string to words
        $words = preg_split('/\s+/u', $text, -1, PREG_SPLIT_NO_EMPTY);

        # set initial scores
        $score = 0;

        # add a simple rule-based mechanism to handle the negation words
        $negationWords = array('لا', 'ليس', 'غير', 'ما', 'لم', 'لن',
                               'لست', 'ليست', 'ليسا', 'ليستا', 'لستما',
                               'لسنا', 'لستم', 'ليسوا', 'لسن', 'لستن');
        $negationFlag  = false;

        # for each word
        foreach ($words as $word) {
            # split word to letters
            $letters = preg_split('//u', $word, -1, PREG_SPLIT_NO_EMPTY);
            
            $stems = array();

            $n = count($letters);

            # get all possible 2 letters stems of current word
            for ($i = 0; $i < $n - 1; $i++) {
                for ($j = $i + 1; $j < $n; $j++) {
                    # get stem key
                    $stems[] = array_search($letters[$i] . $letters[$j], $this->allStems);
                }
            }

            $log_odds = array();
            
            # get log odd for all word stems
            foreach ($stems as $key) {
                $log_odds[] = $this->logOddStem[$key];
            }
            
            # select the most probable stem for current word
            $sel_stem = $stems[array_search(min($log_odds), $log_odds)];

            if ($negationFlag) {
                // switch positive/negative sentiment because of negation word effect
                $score += -1 * (float)$this->logOdd[$sel_stem];
                
                $negationFlag = false;
            } else {
                # retrive the positive and negative log odd scores and accumulate them
                $score += $this->logOdd[$sel_stem];
            }

            if (in_array($word, $negationWords)) {
                $negationFlag = true;
            }
        }
        
        if ($score > 0) {
            $isPositive  = true;
        } else {
            $isPositive = false;
        }
        
        $probability = exp(abs($score)) / (1 + exp(abs($score)));

        return array('isPositive' => $isPositive, 'probability' => $probability);
    }
    
    /**
     * Strip Dots and Hamzat
     *
     * @param string $text Arabic text you would like to strip Dots and Hamzat from it.
     *
     * @return string Arabic text written using letters without dots and Hamzat
     * @author Khaled Al-Sham'aa <khaled@ar-php.org>
     */
    public function noDots($text)
    {
        $text = preg_replace('/ن(\b)/u', 'ں$1', $text);
        $text = preg_replace('/ك(\b)/u', 'ک$1', $text);
        
        $text = strtr($text, array('ب' => 'ٮ', 'ت' => 'ٮ', 'ث' => 'ٮ', 'ن' => 'ٮ',
                                   'ي' => 'ى', 'ف' => 'ڡ', 'ق' => 'ٯ', 'ش' => 'س',
                                   'غ' => 'ع', 'ذ' => 'د', 'ز' => 'ر', 'ض' => 'ص',
                                   'ظ' => 'ط', 'ة' => 'ه', 'ج' => 'ح', 'خ' => 'ح',
                                   'أ' => 'ا', 'إ' => 'ا', 'آ' => 'ا', 'ؤ' => 'و',
                                   'ئ' => 'ى'));
        
        return $text;
    }

    /////////////////////////////////////// Normalize ///////////////////////////////////////////////

    /**
     * Set given normalization form status.
     *
     * @param string  $form   One of the normalization forms ['stripTatweel', 'stripTanween', 'stripShadda',
     *                        'stripLastHarakat', 'stripWordHarakat', 'normaliseLamAlef',
     *                        'normaliseAlef', 'normaliseHamza', 'normaliseTaa', 'all']
     * @param boolean $status Normalization form status [true|false]
     *
     * @return object $this to build a fluent interface.
     * @author Khaled Al-Sham'aa <khaled@ar-php.org>
     */
    public function setNorm($form, $status)
    {
        if ($status == true) {
            $status = true;
        } else {
            $status = false;
        }
        
        switch ($form) {
            case 'stripTatweel':
                $this->stripTatweel = $status;
                break;
            case 'stripTanween':
                $this->stripTanween = $status;
                break;
            case 'stripShadda':
                $this->stripShadda = $status;
                break;
            case 'stripLastHarakat':
                $this->stripLastHarakat = $status;
                break;
            case 'stripWordHarakat':
                $this->stripWordHarakat = $status;
                break;
            case 'normaliseLamAlef':
                $this->normaliseLamAlef = $status;
                break;
            case 'normaliseAlef':
                $this->normaliseAlef = $status;
                break;
            case 'normaliseHamza':
                $this->normaliseHamza = $status;
                break;
            case 'normaliseTaa':
                $this->normaliseTaa = $status;
                break;
            case 'all':
                $this->stripTatweel     = $status;
                $this->stripTanween     = $status;
                $this->stripShadda      = $status;
                $this->stripLastHarakat = $status;
                $this->stripWordHarakat = $status;
                $this->normaliseLamAlef = $status;
                $this->normaliseAlef    = $status;
                $this->normaliseHamza   = $status;
                $this->normaliseTaa     = $status;
                break;
        }

        return $this;
    }
    
    /**
     * Get given normalization form status.
     *
     * @param string  $form One of the normalization forms ['stripTatweel', 'stripTanween', 'stripShadda',
     *                      'stripLastHarakat', 'stripWordHarakat', 'normaliseLamAlef',
     *                      'normaliseAlef', 'normaliseHamza', 'normaliseTaa']
     *
     * @return boolean Selected normalization form status.
     * @author Khaled Al-Sham'aa <khaled@ar-php.org>
     */
    public function getNorm($form)
    {
        switch ($form) {
            case 'stripTatweel':
                $value = $this->stripTatweel;
                break;
            case 'stripTanween':
                $value = $this->stripTanween;
                break;
            case 'stripShadda':
                $value = $this->stripShadda;
                break;
            case 'stripLastHarakat':
                $value = $this->stripLastHarakat;
                break;
            case 'stripWordHarakat':
                $value = $this->stripWordHarakat;
                break;
            case 'normaliseLamAlef':
                $value = $this->normaliseLamAlef;
                break;
            case 'normaliseAlef':
                $value = $this->normaliseAlef;
                break;
            case 'normaliseHamza':
                $value = $this->normaliseHamza;
                break;
            case 'normaliseTaa':
                $value = $this->normaliseTaa;
                break;
            default:
                $value = false;
        }
        
        return $value;
    }

    /**
     * Normalizes the input provided and returns the normalized string.
     *
     * @param string $text    The input string to normalize.
     * @param string $numeral Symbols used to represent numerical digits [Arabic, Hindu, or Persian]
     *                        default is null (i.e., will not normalize digits in the given string).
     *
     * @return string The normalized string.
     * @author Khaled Al-Sham'aa <khaled@ar-php.org>
     */
    public function arNormalizeText($text, $numeral = null)
    {
        if ($this->stripWordHarakat) {
            $bodyHarakat = array('/َ(\S)/u', '/ُ(\S)/u', '/ِ(\S)/u', '/ْ(\S)/u');
            $text = preg_replace($bodyHarakat, '\\1', $text);
        }

        if ($this->stripLastHarakat) {
            $lastHarakat = array('/َ(\s)/u', '/ُ(\s)/u', '/ِ(\s)/u', '/ْ(\s)/u', '/[َُِْ]$/u');
            $text = preg_replace($lastHarakat, '\\1', $text);
        }

        if ($this->stripTatweel) {
            $text = strtr($text, array('ـ' => ''));
        }

        if ($this->stripTanween) {
            $allTanwen = array('ً' => '', 'ٍ' => '', 'ٌ' => '');
            $text = strtr($text, $allTanwen);
        }

        if ($this->stripShadda) {
            $text = strtr($text, array('ّ' => ''));
        }

        if ($this->normaliseLamAlef) {
            $search  = array('لا', 'لآ', 'لأ', 'لإ');
            $replace = array('لا', 'لآ', 'لأ', 'لإ');
            $text    = str_replace($search, $replace, $text);
        }

        if ($this->normaliseAlef) {
            $text = strtr($text, array('أ' => 'ا', 'إ' => 'ا', 'آ' => 'ا', 'ى' => 'ا'));
        }

        if ($this->normaliseHamza) {
            $text = strtr($text, array('ؤ' => 'ء', 'ئ' => 'ء'));
        }

        if ($this->normaliseTaa) {
            $text = strtr($text, array('ة' => 'ه'));
        }
        
        if ($numeral == 'Hindu') {
            $text = strtr($text, array_combine($this->numeralPersian, $this->numeralHindu));
            $text = strtr($text, array_combine($this->numeralArabic, $this->numeralHindu));
        } elseif ($numeral == 'Persian') {
            $text = strtr($text, array_combine($this->numeralHindu, $this->numeralPersian));
            $text = strtr($text, array_combine($this->numeralArabic, $this->numeralPersian));
        } elseif ($numeral == 'Arabic') {
            $text = strtr($text, array_combine($this->numeralHindu, $this->numeralArabic));
            $text = strtr($text, array_combine($this->numeralPersian, $this->numeralArabic));
        }

        return($text);
    }

    /**
     * Get the difference in a human readable format.
     *
     * @param int      $time   the timestamp that is being compared.
     * @param int|null $others if null passed, now will be used as comparison reference;
     *                         if integer value, it will be used as reference timestamp.
     *                         (default value is null).
     * @param int      $parts  maximum number of parts to display (default value is 2).
     * @param bool     $floor  logic for rounding last part, if true then use floor, else use ceiling.
     *
     * @return string the difference in a human readable format.
     * @author Khaled Al-Sham'aa <khaled@ar-php.org>
     */
    public function diffForHumans($time, $others = null, $parts = 2, $floor = true)
    {
        $diff = $others == null ? $time - time() : $time - $others;

        if ($diff < 0) {
            $when = $others == null ? 'منذ' : 'قبل';
        } else {
            $when = $others == null ? 'باقي' : 'بعد';
        }

        $diff = abs($diff);

        $string = '';
        $minute = 60;
        $hour   = 60 * $minute;
        $day    = 24 * $hour;
        $week   = 7 * $day;
        $month  = 30 * $day;
        $year   = 365 * $day;

        while ($parts > 0) {
            if ($diff >= $year) {
                if ($parts > 1 || $floor === true) {
                    $value = floor($diff / $year);
                } else {
                    $value = ceil($diff / $year);
                }

                $text = $this->arPlural('سنة', (int)$value);
                $text = str_replace('%d', (string)$value, $text);

                $string = $string == '' ? $text : $string . ' و ' . $text;

                $diff  = $diff % $year;
                $parts = --$parts;
            } elseif ($diff >= $month) {
                if ($parts > 1 || $floor === true) {
                    $value = floor($diff / $month);
                } else {
                    $value = ceil($diff / $month);
                }

                $text = $this->arPlural('شهر', (int)$value);
                $text = str_replace('%d', (string)$value, $text);

                $string = $string == '' ? $text : $string . ' و ' . $text;

                $diff  = $diff % $month;
                $parts = --$parts;
            } elseif ($diff >= $week) {
                if ($parts > 1 || $floor === true) {
                    $value = floor($diff / $week);
                } else {
                    $value = ceil($diff / $week);
                }

                $text = $this->arPlural('إسبوع', (int)$value);
                $text = str_replace('%d', (string)$value, $text);

                $string = $string == '' ? $text : $string . ' و ' . $text;

                $diff  = $diff % $week;
                $parts = --$parts;
            } elseif ($diff >= $day) {
                if ($parts > 1 || $floor === true) {
                    $value = floor($diff / $day);
                } else {
                    $value = ceil($diff / $day);
                }

                $text = $this->arPlural('يوم', (int)$value);
                $text = str_replace('%d', (string)$value, $text);

                $string = $string == '' ? $text : $string . ' و ' . $text;

                $diff  = $diff % $day;
                $parts = --$parts;
            } elseif ($diff >= $hour) {
                if ($parts > 1 || $floor === true) {
                    $value = floor($diff / $hour);
                } else {
                    $value = ceil($diff / $hour);
                }

                $text = $this->arPlural('ساعة', (int)$value);
                $text = str_replace('%d', (string)$value, $text);

                $string = $string == '' ? $text : $string . ' و ' . $text;

                $diff  = $diff % $hour;
                $parts = --$parts;
            } elseif ($diff >= $minute) {
                if ($parts > 1 || $floor === true) {
                    $value = floor($diff / $minute);
                } else {
                    $value = ceil($diff / $minute);
                }

                $text = $this->arPlural('دقيقة', (int)$value);
                $text = str_replace('%d', (string)$value, $text);

                $string = $string == '' ? $text : $string . ' و ' . $text;

                $diff  = $diff % $minute;
                $parts = --$parts;
            } else {
                if ($diff > 0) {
                    $text = $this->arPlural('ثانية', (int)$diff);
                    $text = str_replace('%d', (string)$diff, $text);

                    $string = $string == '' ? $text : $string . ' و ' . $text;
                }

                $parts = 0;
            }
        }
        
        $string = $when . ' ' . $string;
        
        return $string;
    }
}
