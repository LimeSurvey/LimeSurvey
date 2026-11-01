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
}
