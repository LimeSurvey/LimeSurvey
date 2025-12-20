<?php

namespace ls\tests\unit\helpers;

use ls\tests\TestBaseClass;

/**
 * Test sanitize_helper.php functions
 */
class SanitizeHelperTest extends TestBaseClass
{
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        \Yii::import('application.helpers.sanitize_helper', true);
    }

    /**
     * Test sanitize_languagecode with numeric characters (new in 7.0)
     */
    public function testSanitizeLanguagecodeWithNumbers()
    {
        // Test that numbers are now allowed in language codes
        $this->assertSame('zh-Hans', sanitize_languagecode('zh-Hans'), 'Should preserve valid language code.');
        $this->assertSame('sr-Latn', sanitize_languagecode('sr-Latn'), 'Should preserve language code with region.');
        
        // Test numeric characters are allowed
        $this->assertSame('test123', sanitize_languagecode('test123'), 'Should allow numeric characters.');
        $this->assertSame('en-US2', sanitize_languagecode('en-US2'), 'Should allow numeric characters in language codes.');
        
        // Test special characters are still filtered
        $this->assertSame('en', sanitize_languagecode('en@#$'), 'Should remove special characters.');
        $this->assertSame('fr-FR', sanitize_languagecode('fr_FR'), 'Should remove underscores.');
        
        // Test empty values
        $this->assertSame('', sanitize_languagecode(''), 'Should return empty string for empty input.');
        
        // Test edge cases
        $this->assertSame('123-456-789', sanitize_languagecode('123-456-789'), 'Should allow numbers and hyphens.');
        $this->assertSame('a1b2c3', sanitize_languagecode('a1!b2@c3#'), 'Should filter special chars but keep numbers.');
    }

    /**
     * Test sanitize_languagecode with various special characters
     */
    public function testSanitizeLanguagecodeSpecialCharacters()
    {
        // Test removal of various special characters
        $this->assertSame('en-US', sanitize_languagecode('en_US'), 'Should remove underscores.');
        $this->assertSame('en-US', sanitize_languagecode('en/US'), 'Should remove slashes.');
        $this->assertSame('en-US', sanitize_languagecode('en\\US'), 'Should remove backslashes.');
        $this->assertSame('en-US', sanitize_languagecode('en.US'), 'Should remove dots.');
        $this->assertSame('en-US', sanitize_languagecode('en,US'), 'Should remove commas.');
        $this->assertSame('en-US', sanitize_languagecode('en;US'), 'Should remove semicolons.');
        $this->assertSame('en-US', sanitize_languagecode('en:US'), 'Should remove colons.');
        $this->assertSame('en-US', sanitize_languagecode('en US'), 'Should remove spaces.');
        
        // Test with only special characters
        $this->assertSame('', sanitize_languagecode('!@#$%^&*()'), 'Should return empty for only special chars.');
        
        // Test with parentheses and brackets
        $this->assertSame('en', sanitize_languagecode('en()'), 'Should remove parentheses.');
        $this->assertSame('en', sanitize_languagecode('en[]'), 'Should remove brackets.');
        $this->assertSame('en', sanitize_languagecode('en{}'), 'Should remove braces.');
    }

    /**
     * Test sanitize_languagecode with mixed case
     */
    public function testSanitizeLanguagecodeCasePreservation()
    {
        // Test case preservation
        $this->assertSame('EN-us', sanitize_languagecode('EN-us'), 'Should preserve case.');
        $this->assertSame('MixedCase123', sanitize_languagecode('MixedCase123'), 'Should preserve mixed case and numbers.');
        $this->assertSame('de-DE', sanitize_languagecode('de-DE'), 'Should preserve uppercase region codes.');
        $this->assertSame('zh-Hans', sanitize_languagecode('zh-Hans'), 'Should preserve script case.');
    }

    /**
     * Test sanitize_languagecode with long strings
     */
    public function testSanitizeLanguagecodeLongStrings()
    {
        // Test with long valid string
        $longString = str_repeat('a', 100) . '123';
        $this->assertSame($longString, sanitize_languagecode($longString), 'Should handle long strings.');
        
        // Test with long string containing special chars
        $longStringWithSpecial = str_repeat('a', 50) . '@#$' . str_repeat('b', 50);
        $expected = str_repeat('a', 50) . str_repeat('b', 50);
        $this->assertSame($expected, sanitize_languagecode($longStringWithSpecial), 'Should filter special chars in long strings.');
    }

    /**
     * Test sanitize_languagecode with Unicode and accented characters
     */
    public function testSanitizeLanguagecodeUnicodeCharacters()
    {
        // Test with accented characters (should be removed as they're not ASCII a-z)
        $this->assertSame('test', sanitize_languagecode('tëst'), 'Should remove accented characters.');
        $this->assertSame('cafe', sanitize_languagecode('café'), 'Should remove accented e.');
        $this->assertSame('nahnah', sanitize_languagecode('ñañá'), 'Should remove Spanish ñ and á.');
        
        // Test with non-Latin characters
        $this->assertSame('', sanitize_languagecode('汉语'), 'Should remove Chinese characters.');
        $this->assertSame('', sanitize_languagecode('日本語'), 'Should remove Japanese characters.');
        $this->assertSame('', sanitize_languagecode('한글'), 'Should remove Korean characters.');
        $this->assertSame('', sanitize_languagecode('Русский'), 'Should remove Cyrillic characters.');
    }

    /**
     * Test sanitize_languagecodeS with multiple codes
     */
    public function testSanitizeLanguagecodeSMultipleCodes()
    {
        // Test multiple valid codes
        $this->assertSame('en de fr', sanitize_languagecodeS('en de fr'), 'Should preserve multiple valid codes.');
        $this->assertSame('en-US de-DE fr-FR', sanitize_languagecodeS('en-US de-DE fr-FR'), 'Should preserve codes with regions.');
        
        // Test with numbers in codes
        $this->assertSame('en1 de2 fr3', sanitize_languagecodeS('en1 de2 fr3'), 'Should preserve numbers in codes.');
        
        // Test filtering special characters from multiple codes
        $this->assertSame('en de fr', sanitize_languagecodeS('en@# de$% fr^&'), 'Should remove special characters from all codes.');
        
        // Test with mixed valid and invalid characters
        $this->assertSame('zh-Hans sr-Latn', sanitize_languagecodeS('zh-Hans sr_Latn'), 'Should filter underscores but preserve valid codes.');
    }

    /**
     * Test sanitize_languagecodeS with whitespace handling
     */
    public function testSanitizeLanguagecodeSWhitespace()
    {
        // Test empty and whitespace handling
        $this->assertSame('', sanitize_languagecodeS(''), 'Should handle empty string.');
        $this->assertSame('', sanitize_languagecodeS('   '), 'Should handle whitespace only.');
        
        // Test with extra whitespace
        $this->assertSame('en fr', sanitize_languagecodeS('  en   fr  '), 'Should handle extra whitespace.');
        $this->assertSame('en de fr', sanitize_languagecodeS('en     de     fr'), 'Should normalize multiple spaces.');
        
        // Test with leading/trailing spaces
        $this->assertSame('en de', sanitize_languagecodeS(' en de '), 'Should trim leading and trailing spaces.');
    }

    /**
     * Test sanitize_languagecodeS with complex scenarios
     */
    public function testSanitizeLanguagecodeSComplexScenarios()
    {
        // Test with single code
        $this->assertSame('en', sanitize_languagecodeS('en'), 'Should handle single code.');
        
        // Test with duplicate codes (preserves duplicates)
        $this->assertSame('en en de', sanitize_languagecodeS('en en de'), 'Should preserve duplicate codes.');
        
        // Test with many codes
        $manyCodes = 'en de fr es it pt nl pl cs sk';
        $this->assertSame($manyCodes, sanitize_languagecodeS($manyCodes), 'Should handle many codes.');
        
        // Test complex codes with numbers and hyphens
        $this->assertSame('en-US1 de-informal2 zh-Hans3', sanitize_languagecodeS('en-US1 de_informal2 zh-Hans3'), 'Should handle complex codes with numbers.');
    }

    /**
     * Test sanitize_languagecodeS with edge cases
     */
    public function testSanitizeLanguagecodeSEdgeCases()
    {
        // Test with codes that become empty after filtering
        $result = sanitize_languagecodeS('!@# $%^ &*(');
        // Each code becomes empty, and empty codes are preserved as empty strings joined by spaces
        $this->assertTrue(strlen(trim($result)) === 0 || $result === '  ', 'Should handle all-invalid input.');
        
        // Test with mix of valid and invalid codes
        $this->assertSame('en fr', sanitize_languagecodeS('en !@# fr'), 'Should preserve valid codes and filter invalid ones.');
        
        // Test with tabs and newlines (treated as separators by trim/explode)
        $result = sanitize_languagecodeS("en\tde");
        // Tab is removed by trim, so "en\tde" becomes "ende" after trim
        $this->assertSame('ende', $result, 'Should handle tabs within the string.');
    }

    /**
     * Test sanitize_languagecode with type coercion
     */
    public function testSanitizeLanguagecodeTypeCoercion()
    {
        // Test with numeric values
        $this->assertSame('123', sanitize_languagecode(123), 'Should handle integer input.');
        $this->assertSame('123', sanitize_languagecode('123'), 'Should handle numeric string.');
        $this->assertSame('0', sanitize_languagecode(0), 'Should handle zero.');
        
        // Test with boolean (will be cast to string)
        $this->assertSame('1', sanitize_languagecode(true), 'Should handle true (becomes "1").');
        $this->assertSame('', sanitize_languagecode(false), 'Should handle false (becomes "").');
    }

    /**
     * Test real-world language codes
     */
    public function testSanitizeLanguagecodeRealWorldCodes()
    {
        // Test common language codes
        $realCodes = [
            'en' => 'en',
            'en-US' => 'en-US',
            'en-GB' => 'en-GB',
            'de' => 'de',
            'de-DE' => 'de-DE',
            'de-CH' => 'de-CH',
            'fr' => 'fr',
            'fr-FR' => 'fr-FR',
            'fr-CA' => 'fr-CA',
            'es' => 'es',
            'es-ES' => 'es-ES',
            'es-MX' => 'es-MX',
            'zh-Hans' => 'zh-Hans',
            'zh-Hant' => 'zh-Hant',
            'sr-Latn' => 'sr-Latn',
            'sr-Cyrl' => 'sr-Cyrl',
            'pt-BR' => 'pt-BR',
            'pt-PT' => 'pt-PT',
        ];
        
        foreach ($realCodes as $input => $expected) {
            $this->assertSame($expected, sanitize_languagecode($input), "Should handle real-world code: $input");
        }
    }

    /**
     * Test sanitize_languagecodeS with real-world multi-language scenarios
     */
    public function testSanitizeLanguagecodeSRealWorldScenarios()
    {
        // Test multilingual survey scenarios
        $this->assertSame('en de fr', sanitize_languagecodeS('en de fr'), 'European languages');
        $this->assertSame('en-US en-GB en-AU', sanitize_languagecodeS('en-US en-GB en-AU'), 'English variants');
        $this->assertSame('zh-Hans zh-Hant', sanitize_languagecodeS('zh-Hans zh-Hant'), 'Chinese variants');
        $this->assertSame('es-ES es-MX es-AR', sanitize_languagecodeS('es-ES es-MX es-AR'), 'Spanish variants');
        
        // Test mixed informal/formal variants
        $this->assertSame('de-informal de-formal', sanitize_languagecodeS('de-informal de-formal'), 'German formal/informal');
        $this->assertSame('nl-informal nl-formal', sanitize_languagecodeS('nl-informal nl-formal'), 'Dutch formal/informal');
    }

    /**
     * Test that sanitize_languagecode and LSYii_Validators::languageFilter are consistent
     */
    public function testConsistencyWithLSYiiValidators()
    {
        $validator = new \LSYii_Validators();
        
        $testCases = [
            'en-US',
            'de-DE2',
            'zh-Hans',
            'fr@#$',
            'test_123',
            'a1-b2-c3',
            'MixedCase',
            '123',
            '',
        ];
        
        foreach ($testCases as $testCase) {
            $helperResult = sanitize_languagecode($testCase);
            $validatorResult = $validator->languageFilter($testCase);
            $this->assertSame($validatorResult, $helperResult, "Results should match for: $testCase");
        }
    }
}