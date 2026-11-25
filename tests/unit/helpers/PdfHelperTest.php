<?php

namespace ls\tests\unit\helpers;

use ls\tests\TestBaseClass;

/**
 * Tests for the PDF font settings of languages the default PDF font can not print.
 *
 * @see https://bugs.limesurvey.org/view.php?id=7158
 */
class PdfHelperTest extends TestBaseClass
{
    /**
     * Loads the PDF library and the PDF helper.
     *
     * @return void
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        \Yii::import('application.libraries.admin.pdf', true);
        \Yii::import('application.helpers.pdfHelper', true);
    }

    /**
     * Languages the default font can not print get a font that contains their letters.
     *
     * @dataProvider alternateFontLanguageProvider
     * @param string $language Language code
     * @param string $sampleLetter A letter of the script of the language
     * @return void
     */
    public function testAlternateFontContainsLettersOfLanguage($language, $sampleLetter)
    {
        $settings = \pdfHelper::getPdfLanguageSettings($language);

        $pdf = new \pdf();
        $pdf->setLanguageArray($settings['lg']);
        $pdf->setFont($settings['pdffont']);

        $this->assertTrue(
            $pdf->isCharDefined($sampleLetter),
            'The PDF font ' . $settings['pdffont'] . ' can not print the language ' . $language
        );
    }

    /**
     * Bold text falls back to a font file that contains the letters of the language.
     *
     * @dataProvider alternateFontLanguageProvider
     * @param string $language Language code
     * @param string $sampleLetter A letter of the script of the language
     * @return void
     */
    public function testStyledTextStaysPrintable($language, $sampleLetter)
    {
        $settings = \pdfHelper::getPdfLanguageSettings($language);

        $pdf = new \pdf();
        $pdf->setLanguageArray($settings['lg']);
        foreach (['B', 'I', 'BI'] as $style) {
            $pdf->setFont($settings['pdffont'], $style);
            $this->assertTrue(
                $pdf->isCharDefined($sampleLetter),
                'The PDF font ' . $settings['pdffont'] . ' can not print the language ' . $language . ' in style ' . $style
            );
        }
    }

    /**
     * Provides the languages with an alternate PDF font.
     *
     * @return array<string, array{string, string}> Language code and a letter of its script, by language name
     */
    public static function alternateFontLanguageProvider()
    {
        return [
            'Amharic' => ['am', 'አ'],
            'Bengali' => ['bn', 'ক'],
            'Gujarati' => ['gu', 'ક'],
            'Hebrew' => ['he', 'א'],
            'Hindi' => ['hi', 'क'],
            'Malayalam' => ['ml', 'മ'],
            'Marathi' => ['mr', 'म'],
            'Nepali' => ['ne', 'न'],
            'Odia' => ['ory', 'କ'],
            'Punjabi' => ['pa', 'ਕ'],
            'Sinhala' => ['si', 'ස'],
            'Tamil' => ['ta', 'க'],
            'Thai' => ['th', 'ก'],
            'Tigrinya' => ['ti', 'ት'],
            'Uyghur' => ['ug', 'ۇ'],
        ];
    }

    /**
     * Only the styles the font of the language can not print are removed.
     *
     * @dataProvider fontStyleProvider
     * @param string|null $language Language code
     * @param string $family Font family
     * @param string $style Requested font style
     * @param string $expected Expected font style
     * @return void
     */
    public function testRemoveUnsupportedFontStyles($language, $family, $style, $expected)
    {
        $this->assertSame($expected, \pdfHelper::removeUnsupportedFontStyles($language, $family, $style));
    }

    /**
     * Provides font style requests and the style that must be used for them.
     *
     * @return array<string, array{string|null, string, string, string}> Language code, font family, requested style and expected style, by case
     */
    public static function fontStyleProvider()
    {
        return [
            'Bold removed' => ['am', 'freeserif', 'B', ''],
            'Bold and italic removed' => ['ta', 'freeserif', 'BI', ''],
            'Underline kept' => ['am', 'freeserif', 'BU', 'U'],
            'Only bold removed' => ['bn', 'freesans', 'BI', 'I'],
            'Only italic removed' => ['hi', 'freesans', 'BI', 'B'],
            'Lowercase style' => ['gu', 'freeserif', 'b', ''],
            'Font family is not case sensitive' => ['gu', 'FreeSerif', 'B', ''],
            'Language with all styles' => ['th', 'freeserif', 'BI', 'BI'],
            'Language without alternate font' => ['en', 'dejavusans', 'BI', 'BI'],
            'Font not used as alternate font of the language' => ['bn', 'freeserif', 'B', ''],
            'Font with all styles' => ['am', 'customfont', 'BI', 'BI'],
            'No language' => [null, 'freeserif', 'B', 'B'],
            'Regular style' => ['am', 'freeserif', '', ''],
        ];
    }

    /**
     * The icons of the print answers views are replaced by text, for every survey theme.
     *
     * @dataProvider iconProvider
     * @param string $html HTML of an answer
     * @param string $expected Expected HTML after the replacement
     * @return void
     */
    public function testReplaceIconsWithText($html, $expected)
    {
        $this->assertSame($expected, \pdfHelper::replaceIconsWithText($html));
    }

    /**
     * Provides HTML with icons and the HTML that must be printed in the PDF.
     *
     * @return array<string, array{string, string}> HTML and expected HTML, by case
     */
    public static function iconProvider()
    {
        return [
            'Remix checked' => ['<i class="ri-checkbox-line"></i>', '[X]'],
            'Remix no' => ['No <i class="ri-close-fill"></i>', 'No [X]'],
            'Remix uncertain' => ['<i class="ri-checkbox-indeterminate-line"></i>', '[-]'],
            'Remix unchecked' => ['<i class="ri-checkbox-blank-line"></i>', '[ ]'],
            'Remix increase' => ['<i class="ri-add-line"></i>', '+'],
            'Remix same' => ['<i class="ri-checkbox-blank-circle-fill"></i>', '|'],
            'Remix decrease' => ['<i class="ri-subtract-fill"></i>', '-'],
            'FontAwesome checked' => ['<i class="fa fa-check-square-o"></i>', '[X]'],
            'FontAwesome no' => ['No <i class="fa fa-close"></i>', 'No [X]'],
            'FontAwesome uncertain' => ['<i class="fa fa-minus-square-o"></i>', '[-]'],
            'FontAwesome unchecked' => ['<i class="fa fa-square-o"></i>', '[ ]'],
            'FontAwesome increase' => ['<i class="fa fa-plus"></i>', '+'],
            'FontAwesome same' => ['<i class="fa fa-circle"></i>', '|'],
            'FontAwesome decrease' => ['<i class="fa fa-minus"></i>', '-'],
            'Extra attribute' => ['<i class="fa fa-check-square-o" aria-hidden="true"></i>', '[X]'],
            'Several icons' => ['<b><i class="ri-checkbox-line"></i></b> A <i class="fa fa-square-o"></i> B', '<b>[X]</b> A [ ] B'],
            'Unknown icon kept' => ['Male <i class="fa fa-mars"></i>', 'Male <i class="fa fa-mars"></i>'],
            'Italic text kept' => ['<i class="note">fa-plus</i>', '<i class="note">fa-plus</i>'],
            'No icon' => ['<p>Plain answer</p>', '<p>Plain answer</p>'],
        ];
    }
}
