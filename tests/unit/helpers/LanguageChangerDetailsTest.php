<?php

namespace ls\tests\unit\helpers;

use ls\tests\TestBaseClass;
use Yii;

/**
 * Test getLanguageChangerDetails() and getLanguageChangerLabel(): the native name of each language
 * must be marked with its own language in the language changer (WCAG 3.1.2).
 */
class LanguageChangerDetailsTest extends TestBaseClass
{
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        Yii::import('application.helpers.frontend_helper', true);
        Yii::import('application.helpers.surveytranslator_helper', true);
    }

    /**
     * The native name and the translated name are returned separately, with the language of the native name.
     * Fixed descriptions are used, as getLanguageData() translates them into the current language.
     */
    public function testNativeAndTranslatedNameAreSeparate()
    {
        $aLanguage = array('description' => 'French', 'nativedescription' => 'Fran&ccedil;ais');
        $aDetails = getLanguageChangerDetails('fr', $aLanguage);
        $this->assertSame('Français', $aDetails['nativeName']);
        $this->assertSame('French', $aDetails['name']);
        $this->assertSame('Français - French', $aDetails['label']);
        $this->assertSame('fr', $aDetails['lang']);
        $this->assertSame('ltr', $aDetails['dir']);
        $this->assertSame('Français - French', getLanguageChangerLabel($aLanguage));
    }

    /**
     * The translated name is empty when it is the same as the native name, so it is not shown twice.
     */
    public function testTranslatedNameIsEmptyWhenSameAsNativeName()
    {
        $aLanguage = array('description' => 'English', 'nativedescription' => 'English');
        $aDetails = getLanguageChangerDetails('en', $aLanguage);
        $this->assertSame('', $aDetails['name']);
        $this->assertSame('English', $aDetails['label']);
        $this->assertSame('English', getLanguageChangerLabel($aLanguage));
    }

    /**
     * Internal language codes are not valid lang values: the BCP 47 tag is used instead,
     * the same as for the html lang attribute.
     */
    public function testInternalCodeUsesBcp47Tag()
    {
        $aDetails = getLanguageChangerDetails('de-informal', getLanguageData(true)['de-informal']);
        $this->assertSame('de', $aDetails['lang']);
        $this->assertSame(getHtmlLangAttributeValue('de-informal'), $aDetails['lang']);
        $this->assertSame('Deutsch (Du)', $aDetails['nativeName']);
    }

    /**
     * Right-to-left languages get dir rtl.
     */
    public function testRightToLeftLanguage()
    {
        $aDetails = getLanguageChangerDetails('ar', getLanguageData(true)['ar']);
        $this->assertSame('ar', $aDetails['lang']);
        $this->assertSame('rtl', $aDetails['dir']);
    }
}
