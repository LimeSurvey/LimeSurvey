<?php

namespace ls\tests;

/**
 * Tests for the font dropdown options of survey theme option definitions.
 *
 * @group templatemanifest
 */
class TemplateManifestFontOptionsTest extends TestBaseClass
{
    /** @var string Package name of the temporary user font */
    private const USER_FONT_PACKAGE = 'font-lsunittestfont';

    /**
     * Removes the temporary user font package.
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset(\Yii::app()->getClientScript()->packages[self::USER_FONT_PACKAGE]);
        parent::tearDown();
    }

    /**
     * Font options from core and user fonts must both be added to the font dropdown,
     * also when a font title contains XML special characters.
     *
     * @return void
     */
    public function testCoreAndUserFontsAreAddedToFontDropdown()
    {
        \Yii::app()->getClientScript()->packages[self::USER_FONT_PACKAGE] = [
            'title' => 'Unit & Test Font',
            'type' => 'user',
            'css' => [],
        ];

        $themePath = \Yii::app()->getConfig('standardthemerootdir') . DIRECTORY_SEPARATOR . 'fruity_twentythree' . DIRECTORY_SEPARATOR;
        $optionAttributes = \TemplateManifest::getOptionAttributes($themePath);

        $this->assertIsArray($optionAttributes);
        $fontOptions = $optionAttributes['optionAttributes']['font']['dropdownoptionsArray']['optgroup'];
        $fontValues = [];
        foreach ($fontOptions as $optgroup) {
            foreach ($optgroup['option'] as $option) {
                $fontValues[$option['attributes']['value']] = $option['value'];
            }
        }
        // Core font
        $this->assertArrayHasKey('noto', $fontValues);
        // User font
        $this->assertArrayHasKey('lsunittestfont', $fontValues);
        $this->assertSame('Unit & Test Font', $fontValues['lsunittestfont']);
        // Option defined in the theme's config.xml
        $this->assertArrayHasKey('arial', $fontValues);
    }
}
