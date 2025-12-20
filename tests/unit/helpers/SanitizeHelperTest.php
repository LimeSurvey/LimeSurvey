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
        // Load the sanitize helper
        \Yii::app()->loadHelper('sanitize');
    }

    /**
     * Test sanitize_languagecode with valid inputs
     */
    public function testSanitizeLanguagecodeWithValidInputs()
    {
        // Test basic language codes
        $this->assertSame('en', sanitize_languagecode('en'));
        $this->assertSame('de', sanitize_languagecode('de'));
        $this->assertSame('fr', sanitize_languagecode('fr'));
        
        // Test language codes with hyphens
        $this->assertSame('en-US', sanitize_languagecode('en-US'));
        $this->assertSame('de-easy', sanitize_languagecode('de-easy'));
        $this->assertSame('zh-Hans', sanitize_languagecode('zh-Hans'));
        
        // Test language codes with numbers (new behavior)
        $this->assertSame('zh-Hans1', sanitize_languagecode('zh-Hans1'));
        $this->assertSame('test123', sanitize_languagecode('test123'));
        $this->assertSame('lang2', sanitize_languagecode('lang2'));
    }

    /**
     * Test sanitize_languagecode removes invalid characters
     */
    public function testSanitizeLanguagecodeRemovesInvalidCharacters()
    {
        // Test removal of special characters
        $this->assertSame('en', sanitize_languagecode('en@#$'));
        $this->assertSame('de', sanitize_languagecode('de!%^'));
        $this->assertSame('fr', sanitize_languagecode('fr*&()'));
        
        // Test removal of accented characters
        $this->assertSame('en', sanitize_languagecode('enǵ'));
        $this->assertSame('es', sanitize_languagecode('esñ'));
        $this->assertSame('fr', sanitize_languagecode('frá'));
        
        // Test removal of spaces
        $this->assertSame('enus', sanitize_languagecode('en us'));
        $this->assertSame('en-US', sanitize_languagecode('en-US'));
        
        // Test removal of slashes and other path characters
        $this->assertSame('test', sanitize_languagecode('te/st'));
        $this->assertSame('test', sanitize_languagecode('te\\st'));
        $this->assertSame('test', sanitize_languagecode('te.st'));
    }

    /**
     * Test sanitize_languagecode with edge cases
     */
    public function testSanitizeLanguagecodeEdgeCases()
    {
        // Empty string
        $this->assertSame('', sanitize_languagecode(''));
        
        // Only invalid characters
        $this->assertSame('', sanitize_languagecode('@#$%^&*()'));
        $this->assertSame('', sanitize_languagecode('ñáéíóú'));
        
        // Mixed valid and invalid
        $this->assertSame('abc123xyz', sanitize_languagecode('abc@123#xyz'));
        $this->assertSame('test-lang', sanitize_languagecode('test!-lang'));
        
        // Case preservation
        $this->assertSame('EnUs', sanitize_languagecode('EnUs'));
        $this->assertSame('DE-EASY', sanitize_languagecode('DE-EASY'));
    }

    /**
     * Test sanitize_languagecode with various data types
     */
    public function testSanitizeLanguagecodeWithDifferentTypes()
    {
        // String with whitespace
        $this->assertSame('en', sanitize_languagecode(' en '));
        $this->assertSame('en-US', sanitize_languagecode('  en-US  '));
        
        // Numeric strings
        $this->assertSame('123', sanitize_languagecode('123'));
        $this->assertSame('0', sanitize_languagecode('0'));
        
        // Complex patterns
        $this->assertSame('zh-Hans-CN', sanitize_languagecode('zh-Hans-CN'));
        $this->assertSame('sr-Latn-RS', sanitize_languagecode('sr-Latn-RS'));
    }

    /**
     * Test sanitize_languagecodeS (multiple language codes)
     */
    public function testSanitizeLanguagecodeSWithValidInputs()
    {
        // Single language
        $this->assertSame('en', sanitize_languagecodeS('en'));
        
        // Multiple languages
        $this->assertSame('en de fr', sanitize_languagecodeS('en de fr'));
        $this->assertSame('en-US de-easy', sanitize_languagecodeS('en-US de-easy'));
        
        // With numbers
        $this->assertSame('en de1 fr2', sanitize_languagecodeS('en de1 fr2'));
    }

    /**
     * Test sanitize_languagecodeS removes invalid characters
     */
    public function testSanitizeLanguagecodeSRemovesInvalidCharacters()
    {
        // Remove accents
        $this->assertSame('es fr', sanitize_languagecodeS('esñ frá'));
        $this->assertSame('es fr it', sanitize_languagecodeS('esñ frá it'));
        
        // Remove special characters
        $this->assertSame('en de', sanitize_languagecodeS('en@#$ de!%^'));
        
        // Extra spaces
        $this->assertSame('en de fr', sanitize_languagecodeS('en  de   fr'));
        $this->assertSame('en de', sanitize_languagecodeS('  en   de  '));
    }

    /**
     * Test sanitize_languagecodeS edge cases
     */
    public function testSanitizeLanguagecodeSEdgeCases()
    {
        // Empty string
        $this->assertSame('', sanitize_languagecodeS(''));
        
        // Only spaces
        $this->assertSame('', sanitize_languagecodeS('   '));
        
        // Only invalid characters
        $this->assertSame('', sanitize_languagecodeS('@#$ %^& *()'));
        
        // Duplicates should be preserved (no deduplication)
        $result = sanitize_languagecodeS('en en de');
        $this->assertStringContainsString('en', $result);
        $this->assertStringContainsString('de', $result);
    }

    /**
     * Test sanitize_filename basic functionality
     */
    public function testSanitizeFilename()
    {
        // Basic filename
        $this->assertSame('testfile.txt', sanitize_filename('testfile.txt'));
        $this->assertSame('myfile.pdf', sanitize_filename('myfile.pdf'));
        
        // With spaces
        $this->assertSame('test-file.txt', sanitize_filename('test file.txt'));
        
        // With special characters
        $result = sanitize_filename('test@#$file.txt');
        $this->assertStringContainsString('test', $result);
        $this->assertStringContainsString('file', $result);
        
        // Case handling
        $this->assertSame('testfile.txt', sanitize_filename('TestFile.txt', true));
        $this->assertSame('TestFile.txt', sanitize_filename('TestFile.txt', false));
    }

    /**
     * Test sanitize_paranoid_string
     */
    public function testSanitizeParanoidString()
    {
        // Alphanumeric only
        $this->assertSame('abc123', sanitize_paranoid_string('abc123'));
        $this->assertSame('TEST', sanitize_paranoid_string('TEST'));
        
        // Remove all special characters
        $this->assertSame('test', sanitize_paranoid_string('test@#$%'));
        $this->assertSame('hello123', sanitize_paranoid_string('hello!@#123'));
        
        // Empty result for all invalid
        $this->assertSame('', sanitize_paranoid_string('@#$%^&*()'));
    }

    /**
     * Test sanitize_int
     */
    public function testSanitizeInt()
    {
        // Valid integers
        $this->assertSame(123, sanitize_int('123'));
        $this->assertSame(0, sanitize_int('0'));
        $this->assertSame(-456, sanitize_int('-456'));
        
        // Invalid inputs become 0
        $this->assertSame(0, sanitize_int('abc'));
        $this->assertSame(0, sanitize_int('12.34'));
        
        // Min/max validation
        $this->assertFalse(sanitize_int('5', 10, 20));
        $this->assertSame(15, sanitize_int('15', 10, 20));
        $this->assertFalse(sanitize_int('25', 10, 20));
    }

    /**
     * Test sanitize_float
     */
    public function testSanitizeFloat()
    {
        // Valid floats
        $this->assertSame(12.34, sanitize_float('12.34'));
        $this->assertSame(0.5, sanitize_float('0.5'));
        $this->assertSame(-3.14, sanitize_float('-3.14'));
        
        // Invalid inputs become 0
        $this->assertSame(0.0, sanitize_float('abc'));
        
        // Min/max validation
        $this->assertFalse(sanitize_float('0.5', 1.0, 10.0));
        $this->assertSame(5.5, sanitize_float('5.5', 1.0, 10.0));
        $this->assertFalse(sanitize_float('15.0', 1.0, 10.0));
    }
}

    /**
     * Test sanitize_languagecode with security considerations
     */
    public function testSanitizeLanguagecodeSecurityFiltering()
    {
        // Test path traversal attempts
        $this->assertSame('', sanitize_languagecode('../../../etc/passwd'));
        $this->assertSame('test', sanitize_languagecode('..\\..\\test'));
        
        // Test null byte injection
        $this->assertSame('test', sanitize_languagecode("test\0injection"));
        
        // Test SQL injection patterns
        $this->assertSame('test', sanitize_languagecode("test'; DROP TABLE--"));
        $this->assertSame('test', sanitize_languagecode("test' OR '1'='1"));
        
        // Test XSS patterns
        $this->assertSame('scriptalertXSSscript', sanitize_languagecode('<script>alert("XSS")</script>'));
        $this->assertSame('test', sanitize_languagecode('test<>'));
    }

    /**
     * Test sanitize_languagecodeS with security considerations
     */
    public function testSanitizeLanguagecodeSSecurityFiltering()
    {
        // Test multiple malicious codes
        $this->assertSame('en de', sanitize_languagecodeS('en../../../de'));
        $this->assertSame('', sanitize_languagecodeS("'; DROP TABLE users; --"));
        
        // Test with embedded scripts
        $result = sanitize_languagecodeS('<script> alert test</script>');
        $this->assertStringNotContainsString('<', $result);
        $this->assertStringNotContainsString('>', $result);
    }

    /**
     * Test sanitize_filename with directory parameter
     */
    public function testSanitizeFilenameWithDirectoryParameter()
    {
        // Test with directory = false (default)
        $result = sanitize_filename('test/file.txt', true, false, true, false);
        $this->assertStringNotContainsString('/', $result);
        
        // Basic file names
        $this->assertSame('document.pdf', sanitize_filename('document.pdf'));
        $this->assertSame('my-file.txt', sanitize_filename('my file.txt'));
    }

    /**
     * Test sanitize_int with boundary values
     */
    public function testSanitizeIntBoundaryValues()
    {
        // Test with very large numbers
        $this->assertSame(2147483647, sanitize_int('2147483647'));
        $this->assertSame(-2147483648, sanitize_int('-2147483648'));
        
        // Test with overflow
        $result = sanitize_int('99999999999999999999');
        $this->assertIsInt($result);
        
        // Test zero
        $this->assertSame(0, sanitize_int('0'));
        $this->assertSame(0, sanitize_int('-0'));
    }

    /**
     * Test sanitize_float with scientific notation
     */
    public function testSanitizeFloatWithScientificNotation()
    {
        // Scientific notation should work
        $this->assertSame(1.23e10, sanitize_float('1.23e10'));
        $this->assertSame(1.5e-5, sanitize_float('1.5e-5'));
        
        // Very small numbers
        $this->assertSame(0.0001, sanitize_float('0.0001'));
        $this->assertSame(0.00000001, sanitize_float('0.00000001'));
    }

    /**
     * Test sanitize_paranoid_string with min/max length
     */
    public function testSanitizeParanoidStringWithLength()
    {
        // Test minimum length
        $this->assertFalse(sanitize_paranoid_string('ab', 3));
        $this->assertSame('abc', sanitize_paranoid_string('abc', 3));
        
        // Test maximum length
        $this->assertSame('abc', sanitize_paranoid_string('abc', '', 3));
        $this->assertFalse(sanitize_paranoid_string('abcd', '', 3));
        
        // Test range
        $this->assertFalse(sanitize_paranoid_string('ab', 3, 10));
        $this->assertSame('abc', sanitize_paranoid_string('abc', 3, 10));
        $this->assertSame('abcdefghij', sanitize_paranoid_string('abcdefghij', 3, 10));
        $this->assertFalse(sanitize_paranoid_string('abcdefghijk', 3, 10));
    }

    /**
     * Test sanitize functions with unicode characters
     */
    public function testSanitizeFunctionsWithUnicode()
    {
        // Language codes should handle unicode properly
        $this->assertSame('', sanitize_languagecode('日本語'));
        $this->assertSame('', sanitize_languagecode('한국어'));
        $this->assertSame('', sanitize_languagecode('中文'));
        
        // Mixed ASCII and unicode
        $this->assertSame('test', sanitize_languagecode('test日本'));
        $this->assertSame('en', sanitize_languagecode('en한국'));
    }

    /**
     * Test sanitize_filename beautification
     */
    public function testSanitizeFilenameBeautification()
    {
        // With beautify = true (default)
        $result = sanitize_filename('test___file.txt', true, false, true);
        $this->assertStringNotContainsString('___', $result);
        
        // Multiple dashes should be reduced
        $result = sanitize_filename('test---file.txt', true, false, true);
        $this->assertStringNotContainsString('---', $result);
        
        // Spaces to dashes
        $this->assertSame('my-awesome-file.txt', sanitize_filename('my awesome file.txt'));
    }

    /**
     * Test check functions return boolean correctly
     */
    public function testCheckFunctionsReturnBoolean()
    {
        // check_int
        $this->assertTrue(check_int('123'));
        $this->assertFalse(check_int('abc'));
        
        // check_float
        $this->assertTrue(check_float('12.34'));
        $this->assertFalse(check_float('abc'));
        
        // check_paranoid_string
        $this->assertTrue(check_paranoid_string('abc123'));
        $this->assertFalse(check_paranoid_string('abc@123'));
    }
}