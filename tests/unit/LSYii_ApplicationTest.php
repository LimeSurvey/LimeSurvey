<?php

namespace ls\tests;

/**
 * Test LSYii_Application class language handling
 * @group core
 */
class LSYii_ApplicationTest extends TestBaseClass
{
    /**
     * Test that language codes are properly filtered when set
     */
    public function testSetLanguageFiltersCode()
    {
        $originalLanguage = \Yii::app()->getLanguage();
        
        // Test setting valid language
        \Yii::app()->setLanguage('en');
        $this->assertSame('en', \Yii::app()->getLanguage());
        
        // Test setting language with hyphen
        \Yii::app()->setLanguage('en-US');
        $this->assertSame('en-US', \Yii::app()->getLanguage());
        
        // Test setting language with numbers (new behavior)
        \Yii::app()->setLanguage('zh-Hans1');
        $this->assertSame('zh-Hans1', \Yii::app()->getLanguage());
        
        // Test that invalid characters are filtered
        \Yii::app()->setLanguage('te@st');
        $this->assertSame('test', \Yii::app()->getLanguage());
        
        // Restore original language
        \Yii::app()->setLanguage($originalLanguage);
    }

    /**
     * Test language session storage
     */
    public function testLanguageStoredInSession()
    {
        $originalLanguage = \Yii::app()->getLanguage();
        
        // Set a language
        \Yii::app()->setLanguage('de');
        
        // Check it's stored in session
        $this->assertSame('de', \Yii::app()->session['_lang']);
        
        // Set another language
        \Yii::app()->setLanguage('fr');
        $this->assertSame('fr', \Yii::app()->session['_lang']);
        
        // Restore
        \Yii::app()->setLanguage($originalLanguage);
    }

    /**
     * Test language filtering removes invalid characters
     */
    public function testLanguageFilteringRemovesInvalidCharacters()
    {
        $originalLanguage = \Yii::app()->getLanguage();
        
        // Test various invalid characters
        \Yii::app()->setLanguage('en$%^');
        $currentLang = \Yii::app()->getLanguage();
        $this->assertStringNotContainsString('$', $currentLang);
        $this->assertStringNotContainsString('%', $currentLang);
        $this->assertStringNotContainsString('^', $currentLang);
        
        // Test with special characters
        \Yii::app()->setLanguage('te/st');
        $currentLang = \Yii::app()->getLanguage();
        $this->assertStringNotContainsString('/', $currentLang);
        
        // Restore
        \Yii::app()->setLanguage($originalLanguage);
    }

    /**
     * Test language codes with numbers are preserved
     */
    public function testLanguageCodesWithNumbersPreserved()
    {
        $originalLanguage = \Yii::app()->getLanguage();
        
        // Numbers should be allowed now
        \Yii::app()->setLanguage('lang1');
        $this->assertSame('lang1', \Yii::app()->getLanguage());
        
        \Yii::app()->setLanguage('test123');
        $this->assertSame('test123', \Yii::app()->getLanguage());
        
        \Yii::app()->setLanguage('zh-Hans2');
        $this->assertSame('zh-Hans2', \Yii::app()->getLanguage());
        
        // Restore
        \Yii::app()->setLanguage($originalLanguage);
    }

    /**
     * Test empty language code handling
     */
    public function testEmptyLanguageCodeHandling()
    {
        $originalLanguage = \Yii::app()->getLanguage();
        
        // Set empty string
        \Yii::app()->setLanguage('');
        $currentLang = \Yii::app()->getLanguage();
        $this->assertIsString($currentLang);
        
        // Restore
        \Yii::app()->setLanguage($originalLanguage);
    }
}