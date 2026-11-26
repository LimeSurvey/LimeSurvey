<?php

namespace ls\tests;

/**
 * Tests for the dropdown options HTML of survey theme option definitions.
 *
 * @group templatemanifest
 */
class TemplateManifestDropdownOptionsTest extends TestBaseClass
{
    /**
     * The dropdown options HTML is put inside a select element, so it must only contain
     * the optgroup and option elements and not the dropdownoptions element of config.xml.
     *
     * @return void
     */
    public function testDropdownOptionsHtmlHasNoWrapperElement()
    {
        $themePath = \Yii::app()->getConfig('standardthemerootdir') . DIRECTORY_SEPARATOR . 'fruity_twentythree' . DIRECTORY_SEPARATOR;
        $optionAttributes = \TemplateManifest::getOptionAttributes($themePath);

        $this->assertIsArray($optionAttributes);
        foreach (['cssframework', 'font', 'checkicon'] as $optionName) {
            $optionsHtml = $optionAttributes['optionAttributes'][$optionName]['dropdownoptions'];
            $this->assertStringNotContainsString('dropdownoptions', $optionsHtml, $optionName);
            $this->assertStringContainsString('<option ', $optionsHtml, $optionName);
        }
    }

    /**
     * Themes extended from older core themes declare the logo and background image options as a dropdown, and
     * image dropdowns of the "Images" category have no dropdown options: They are read as "imagefile" options.
     * Other dropdowns keep their type.
     *
     * @return void
     */
    public function testLegacyImageDropdownsAreReadAsImagefile()
    {
        $themePath = \Yii::app()->getConfig('tempdir') . DIRECTORY_SEPARATOR . 'legacyimagetheme' . uniqid() . DIRECTORY_SEPARATOR;
        mkdir($themePath);
        file_put_contents(
            $themePath . 'config.xml',
            '<?xml version="1.0" encoding="UTF-8"?><config><options>'
            . '<brandlogofile type="dropdown" category="Images" parent="brandlogo">./files/logo.png</brandlogofile>'
            . '<backgroundimagefile category="Images" parent="backgroundimage">./files/pattern.png</backgroundimagefile>'
            . '<headerimagefile type="dropdown" category="Images">./files/header.png</headerimagefile>'
            . '<headerstyle type="dropdown" category="Images"><dropdownoptions><option value="wide">Wide</option></dropdownoptions></headerstyle>'
            . '<emptylist type="dropdown" category="Simple options">./files/other.png</emptylist>'
            . '<animation type="dropdown"><dropdownoptions><option value="fade">Fade</option></dropdownoptions></animation>'
            . '</options></config>'
        );
        try {
            $optionAttributes = \TemplateManifest::getOptionAttributes($themePath)['optionAttributes'];
        } finally {
            unlink($themePath . 'config.xml');
            rmdir($themePath);
        }

        $this->assertSame('imagefile', $optionAttributes['brandlogofile']['type']);
        $this->assertSame('imagefile', $optionAttributes['backgroundimagefile']['type']);
        $this->assertSame('imagefile', $optionAttributes['headerimagefile']['type']);
        $this->assertSame('dropdown', $optionAttributes['headerstyle']['type']);
        $this->assertSame('dropdown', $optionAttributes['emptylist']['type']);
        $this->assertSame('dropdown', $optionAttributes['animation']['type']);
    }

    /**
     * The core themes declare their image options as "imagefile" options.
     *
     * @return void
     */
    public function testCoreThemeImageOptionsAreImagefile()
    {
        $themePath = \Yii::app()->getConfig('standardthemerootdir') . DIRECTORY_SEPARATOR . 'fruity_twentythree' . DIRECTORY_SEPARATOR;
        $optionAttributes = \TemplateManifest::getOptionAttributes($themePath)['optionAttributes'];

        $this->assertSame('imagefile', $optionAttributes['brandlogofile']['type']);
        $this->assertSame('imagefile', $optionAttributes['backgroundimagefile']['type']);
    }
}
