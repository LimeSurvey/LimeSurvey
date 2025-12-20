<?php

namespace ls\tests\unit\controllers;

use ls\tests\TestBaseClass;
use LSYii_Validators;

/**
 * Test ExpressionValidate controller changes
 * @group controllers
 */
class ExpressionValidateControllerTest extends TestBaseClass
{
    /**
     * Test that LSYii_Validators languageFilter method is accessible as instance method
     */
    public function testLanguageFilterIsInstanceMethod()
    {
        $validator = new LSYii_Validators();
        
        $this->assertTrue(method_exists($validator, 'languageFilter'));
        
        // Test it can be called as instance method
        $result = $validator->languageFilter('en-US');
        $this->assertSame('en-US', $result);
    }

    /**
     * Test that validator can filter language codes with numbers
     */
    public function testValidatorLanguageFilterWithNumbers()
    {
        $validator = new LSYii_Validators();
        
        // The change allows numbers in language codes
        $result = $validator->languageFilter('zh-Hans1');
        $this->assertSame('zh-Hans1', $result);
        
        $result = $validator->languageFilter('test123');
        $this->assertSame('test123', $result);
    }

    /**
     * Test validator instantiation
     */
    public function testValidatorInstantiation()
    {
        $validator = new LSYii_Validators();
        
        $this->assertInstanceOf(LSYii_Validators::class, $validator);
        $this->assertInstanceOf(\CValidator::class, $validator);
    }

    /**
     * Test that language filter handles various inputs
     */
    public function testLanguageFilterHandlesVariousInputs()
    {
        $validator = new LSYii_Validators();
        
        // Empty input
        $this->assertSame('', $validator->languageFilter(''));
        
        // Valid codes
        $this->assertSame('en', $validator->languageFilter('en'));
        $this->assertSame('de-DE', $validator->languageFilter('de-DE'));
        
        // Invalid characters
        $this->assertSame('test', $validator->languageFilter('te@st'));
        $this->assertSame('test', $validator->languageFilter('te$st'));
        
        // With numbers (new behavior)
        $this->assertSame('test1', $validator->languageFilter('test1'));
        $this->assertSame('en2de3', $validator->languageFilter('en2de3'));
    }
}