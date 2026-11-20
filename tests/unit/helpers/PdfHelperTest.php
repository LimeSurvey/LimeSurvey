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
}
