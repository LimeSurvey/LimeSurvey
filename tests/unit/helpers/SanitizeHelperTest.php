<?php

namespace ls\tests\unit\helpers;

use ls\tests\TestBaseClass;

/**
 * Test sanitize_helper.php functions
 * @group helpers
 */
class SanitizeHelperTest extends TestBaseClass
{
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        
        // Load the helper file
        require_once(__DIR__ . '/../../../application/helpers/sanitize_helper.php');
    }

    /**
     * Test sanitize_languagecode with valid language codes
     */
    public function testSanitizeLanguagecodeWithValidCodes()
    {
        $this->assertSame('en', sanitize_languagecode('en'), 'Should accept simple language code');
        $this->assertSame('fr', sanitize_languagecode('fr'), 'Should accept French code');
        $this->assertSame('de', sanitize_languagecode('de'), 'Should accept German code');
        $this->assertSame('es', sanitize_languagecode('es'), 'Should accept Spanish code');
        $this->assertSame('it', sanitize_languagecode('it'), 'Should accept Italian code');
        $this->assertSame('pt', sanitize_languagecode('pt'), 'Should accept Portuguese code');
        $this->assertSame('ja', sanitize_languagecode('ja'), 'Should accept Japanese code');
        $this->assertSame('ko', sanitize_languagecode('ko'), 'Should accept Korean code');
        $this->assertSame('zh', sanitize_languagecode('zh'), 'Should accept Chinese code');
    }

    /**
     * Test sanitize_languagecode with compound language codes
     */
    public function testSanitizeLanguagecodeWithCompoundCodes()
    {
        $this->assertSame('en-US', sanitize_languagecode('en-US'), 'Should accept en-US');
        $this->assertSame('en-GB', sanitize_languagecode('en-GB'), 'Should accept en-GB');
        $this->assertSame('fr-FR', sanitize_languagecode('fr-FR'), 'Should accept fr-FR');
        $this->assertSame('de-DE', sanitize_languagecode('de-DE'), 'Should accept de-DE');
        $this->assertSame('pt-BR', sanitize_languagecode('pt-BR'), 'Should accept pt-BR');
        $this->assertSame('zh-Hans', sanitize_languagecode('zh-Hans'), 'Should accept zh-Hans');
        $this->assertSame('zh-Hant', sanitize_languagecode('zh-Hant'), 'Should accept zh-Hant');
        $this->assertSame('de-informal', sanitize_languagecode('de-informal'), 'Should accept custom variants');
        $this->assertSame('de-easy', sanitize_languagecode('de-easy'), 'Should accept easy variants');
    }

    /**
     * Test sanitize_languagecode with numeric characters (new behavior)
     */
    public function testSanitizeLanguagecodeWithNumericCharacters()
    {
        $this->assertSame('en1', sanitize_languagecode('en1'), 'Should allow numbers');
        $this->assertSame('fr2', sanitize_languagecode('fr2'), 'Should allow numbers');
        $this->assertSame('lang123', sanitize_languagecode('lang123'), 'Should allow multiple numbers');
        $this->assertSame('123', sanitize_languagecode('123'), 'Should allow pure numeric strings');
        $this->assertSame('0', sanitize_languagecode('0'), 'Should allow zero');
        $this->assertSame('en-US1', sanitize_languagecode('en-US1'), 'Should allow numbers in compound codes');
        $this->assertSame('custom123-variant456', sanitize_languagecode('custom123-variant456'), 'Should allow numbers throughout');
    }

    /**
     * Test sanitize_languagecode removes invalid characters
     */
    public function testSanitizeLanguagecodeRemovesInvalidCharacters()
    {
        // Special characters
        $this->assertSame('en', sanitize_languagecode('en!'), 'Should remove exclamation mark');
        $this->assertSame('en', sanitize_languagecode('en@'), 'Should remove at symbol');
        $this->assertSame('en', sanitize_languagecode('en#'), 'Should remove hash');
        $this->assertSame('en', sanitize_languagecode('en$'), 'Should remove dollar sign');
        $this->assertSame('en', sanitize_languagecode('en%'), 'Should remove percent');
        $this->assertSame('en', sanitize_languagecode('en^'), 'Should remove caret');
        $this->assertSame('en', sanitize_languagecode('en&'), 'Should remove ampersand');
        $this->assertSame('en', sanitize_languagecode('en*'), 'Should remove asterisk');
        $this->assertSame('en', sanitize_languagecode('en('), 'Should remove opening parenthesis');
        $this->assertSame('en', sanitize_languagecode('en)'), 'Should remove closing parenthesis');
        
        // Punctuation
        $this->assertSame('en', sanitize_languagecode('en_'), 'Should remove underscore');
        $this->assertSame('en', sanitize_languagecode('en='), 'Should remove equals');
        $this->assertSame('en', sanitize_languagecode('en+'), 'Should remove plus');
        $this->assertSame('en', sanitize_languagecode('en.'), 'Should remove period');
        $this->assertSame('en', sanitize_languagecode('en,'), 'Should remove comma');
        $this->assertSame('en', sanitize_languagecode('en:'), 'Should remove colon');
        $this->assertSame('en', sanitize_languagecode('en;'), 'Should remove semicolon');
        $this->assertSame('en', sanitize_languagecode('en/'), 'Should remove forward slash');
        $this->assertSame('en', sanitize_languagecode('en\\'), 'Should remove backslash');
        $this->assertSame('en', sanitize_languagecode('en?'), 'Should remove question mark');
        
        // Brackets and quotes
        $this->assertSame('en', sanitize_languagecode('en['), 'Should remove opening bracket');
        $this->assertSame('en', sanitize_languagecode('en]'), 'Should remove closing bracket');
        $this->assertSame('en', sanitize_languagecode('en{'), 'Should remove opening brace');
        $this->assertSame('en', sanitize_languagecode('en}'), 'Should remove closing brace');
        $this->assertSame('en', sanitize_languagecode('en<'), 'Should remove less than');
        $this->assertSame('en', sanitize_languagecode('en>'), 'Should remove greater than');
        $this->assertSame('en', sanitize_languagecode('en"'), 'Should remove double quote');
        $this->assertSame('en', sanitize_languagecode("en'"), 'Should remove single quote');
        $this->assertSame('en', sanitize_languagecode('en|'), 'Should remove pipe');
    }

    /**
     * Test sanitize_languagecode with whitespace
     */
    public function testSanitizeLanguagecodeWithWhitespace()
    {
        $this->assertSame('en', sanitize_languagecode('en '), 'Should remove trailing space');
        $this->assertSame('en', sanitize_languagecode(' en'), 'Should remove leading space');
        $this->assertSame('enfr', sanitize_languagecode('en fr'), 'Should remove internal space');
        $this->assertSame('en', sanitize_languagecode("en\t"), 'Should remove tab');
        $this->assertSame('en', sanitize_languagecode("en\n"), 'Should remove newline');
        $this->assertSame('en', sanitize_languagecode("en\r"), 'Should remove carriage return');
    }

    /**
     * Test sanitize_languagecode with Unicode characters
     */
    public function testSanitizeLanguagecodeWithUnicodeCharacters()
    {
        $this->assertSame('en', sanitize_languagecode('enñ'), 'Should remove n with tilde');
        $this->assertSame('fr', sanitize_languagecode('frá'), 'Should remove accented a');
        $this->assertSame('de', sanitize_languagecode('deü'), 'Should remove umlaut');
        $this->assertSame('', sanitize_languagecode('日本語'), 'Should remove Japanese characters');
        $this->assertSame('', sanitize_languagecode('中文'), 'Should remove Chinese characters');
        $this->assertSame('', sanitize_languagecode('한국어'), 'Should remove Korean characters');
        $this->assertSame('', sanitize_languagecode('العربية'), 'Should remove Arabic characters');
        $this->assertSame('', sanitize_languagecode('Русский'), 'Should remove Cyrillic characters');
    }

    /**
     * Test sanitize_languagecode with XSS attempts
     */
    public function testSanitizeLanguagecodeBlocksXSSAttempts()
    {
        $this->assertSame('scriptalertXSSscript', sanitize_languagecode('<script>alert("XSS")</script>'), 'Should remove script tags');
        $this->assertSame('imgscr', sanitize_languagecode('<img src=x>'), 'Should remove img tags');
        $this->assertSame('javascriptalert1', sanitize_languagecode('javascript:alert(1)'), 'Should remove javascript protocol');
        $this->assertSame('onerroralert1', sanitize_languagecode('onerror=alert(1)'), 'Should remove equals from event handlers');
    }

    /**
     * Test sanitize_languagecode with empty values
     */
    public function testSanitizeLanguagecodeWithEmptyValues()
    {
        $this->assertSame('', sanitize_languagecode(''), 'Empty string should return empty string');
        $this->assertSame('', sanitize_languagecode(null), 'Null should return empty string');
    }

    /**
     * Test sanitize_languagecode preserves case
     */
    public function testSanitizeLanguagecodePreservesCase()
    {
        $this->assertSame('EN', sanitize_languagecode('EN'), 'Should preserve uppercase');
        $this->assertSame('en', sanitize_languagecode('en'), 'Should preserve lowercase');
        $this->assertSame('En', sanitize_languagecode('En'), 'Should preserve mixed case');
        $this->assertSame('eN', sanitize_languagecode('eN'), 'Should preserve mixed case');
        $this->assertSame('en-US', sanitize_languagecode('en-US'), 'Should preserve case in compound codes');
    }

    /**
     * Test sanitize_languagecode with long strings
     */
    public function testSanitizeLanguagecodeWithLongStrings()
    {
        $longValid = str_repeat('a', 1000);
        $this->assertSame($longValid, sanitize_languagecode($longValid), 'Should handle long valid strings');
        
        $longInvalid = str_repeat('!', 1000);
        $this->assertSame('', sanitize_languagecode($longInvalid), 'Should remove all characters from long invalid string');
        
        $mixedLong = str_repeat('a!', 500);
        $this->assertSame(str_repeat('a', 500), sanitize_languagecode($mixedLong), 'Should filter invalid chars from long mixed string');
    }

    /**
     * Test sanitize_languagecodeS with multiple valid codes
     */
    public function testSanitizeLanguagecodeSWithMultipleCodes()
    {
        $this->assertSame('en fr', sanitize_languagecodeS('en fr'), 'Should accept multiple codes');
        $this->assertSame('en fr de', sanitize_languagecodeS('en fr de'), 'Should accept three codes');
        $this->assertSame('en fr de es it', sanitize_languagecodeS('en fr de es it'), 'Should accept five codes');
    }

    /**
     * Test sanitize_languagecodeS with numbers (new behavior)
     */
    public function testSanitizeLanguagecodeSWithNumbers()
    {
        $this->assertSame('en1 fr2', sanitize_languagecodeS('en1 fr2'), 'Should allow numbers');
        $this->assertSame('lang1 lang2 lang3', sanitize_languagecodeS('lang1 lang2 lang3'), 'Should allow multiple numbered codes');
        $this->assertSame('123 456', sanitize_languagecodeS('123 456'), 'Should allow pure numeric codes');
    }

    /**
     * Test sanitize_languagecodeS removes invalid characters
     */
    public function testSanitizeLanguagecodeSRemovesInvalidCharacters()
    {
        $this->assertSame('en fr', sanitize_languagecodeS('en! fr@'), 'Should remove special characters');
        $this->assertSame('en fr de', sanitize_languagecodeS('en# fr$ de%'), 'Should remove multiple special characters');
        $this->assertSame('en-US fr-FR', sanitize_languagecodeS('en-US! fr-FR@'), 'Should remove special characters from compound codes');
    }

    /**
     * Test sanitize_languagecodeS with extra spaces
     */
    public function testSanitizeLanguagecodeSHandlesExtraSpaces()
    {
        $result = sanitize_languagecodeS('en  fr');
        $this->assertStringContainsString('en', $result, 'Should contain en');
        $this->assertStringContainsString('fr', $result, 'Should contain fr');
        
        $result2 = sanitize_languagecodeS('en   fr   de');
        $this->assertStringContainsString('en', $result2, 'Should contain en');
        $this->assertStringContainsString('fr', $result2, 'Should contain fr');
        $this->assertStringContainsString('de', $result2, 'Should contain de');
        
        $result3 = sanitize_languagecodeS(' en fr ');
        $this->assertStringContainsString('en', $result3, 'Should contain en');
        $this->assertStringContainsString('fr', $result3, 'Should contain fr');
    }

    /**
     * Test sanitize_languagecodeS with empty values
     */
    public function testSanitizeLanguagecodeSWithEmptyValues()
    {
        $result = sanitize_languagecodeS('');
        $this->assertIsString($result, 'Should return string');
        
        $result2 = sanitize_languagecodeS(null);
        $this->assertIsString($result2, 'Should return string for null');
    }

    /**
     * Test sanitize_languagecodeS with single code
     */
    public function testSanitizeLanguagecodeSWithSingleCode()
    {
        $this->assertSame('en', sanitize_languagecodeS('en'), 'Should handle single code');
        $this->assertSame('en-US', sanitize_languagecodeS('en-US'), 'Should handle single compound code');
        $this->assertSame('custom1', sanitize_languagecodeS('custom1'), 'Should handle single custom code with number');
    }

    /**
     * Test sanitize_languagecodeS with many codes
     */
    public function testSanitizeLanguagecodeSWithManyCodes()
    {
        $input = 'en fr de es it pt ja ko zh ar ru';
        $result = sanitize_languagecodeS($input);
        $codes = explode(' ', trim($result));
        
        $this->assertGreaterThan(5, count($codes), 'Should handle many codes');
        $resultString = ' ' . $result . ' ';
        $this->assertStringContainsString(' en ', $resultString, 'Should contain en');
        $this->assertStringContainsString(' fr ', $resultString, 'Should contain fr');
        $this->assertStringContainsString(' de ', $resultString, 'Should contain de');
    }

    /**
     * Test sanitize_languagecodeS with XSS attempts
     */
    public function testSanitizeLanguagecodeSBlocksXSSAttempts()
    {
        $result = sanitize_languagecodeS('<script>alert("XSS")</script> en fr');
        $this->assertStringNotContainsString('<script>', $result, 'Should not contain script tags');
        $this->assertStringNotContainsString('</script>', $result, 'Should not contain closing script tags');
        $this->assertStringContainsString('en', $result, 'Should contain valid code en');
        $this->assertStringContainsString('fr', $result, 'Should contain valid code fr');
    }

    /**
     * Test sanitize_languagecodeS with Unicode
     */
    public function testSanitizeLanguagecodeSWithUnicode()
    {
        $this->assertSame('en fr', sanitize_languagecodeS('enñ frá'), 'Should remove Unicode from multi-language string');
        $result = sanitize_languagecodeS('deü esñ itò');
        $this->assertStringContainsString('de', $result, 'Should contain de');
        $this->assertStringContainsString('es', $result, 'Should contain es');
        $this->assertStringContainsString('it', $result, 'Should contain it');
        $this->assertStringNotContainsString('ü', $result, 'Should not contain umlaut');
        $this->assertStringNotContainsString('ñ', $result, 'Should not contain n with tilde');
        $this->assertStringNotContainsString('ò', $result, 'Should not contain o with grave');
    }

    /**
     * Test sanitize_languagecodeS preserves case
     */
    public function testSanitizeLanguagecodeSPreservesCase()
    {
        $this->assertSame('EN FR', sanitize_languagecodeS('EN FR'), 'Should preserve uppercase');
        $this->assertSame('en fr', sanitize_languagecodeS('en fr'), 'Should preserve lowercase');
        $this->assertSame('En Fr', sanitize_languagecodeS('En Fr'), 'Should preserve mixed case');
        $this->assertSame('en-US fr-FR', sanitize_languagecodeS('en-US fr-FR'), 'Should preserve case in compound codes');
    }
}