<?php

namespace ls\tests;

use TemplateConfig;
use Yii;

class TemplateConfigTest extends TestBaseClass
{
    public function testConvertEmptyArrayOptionsToJson()
    {
        $jsonOptions = TemplateConfig::convertOptionsToJson(array());
        $this->assertSame('""', $jsonOptions, 'An empty json string must have been returned.');
    }

    public function testConvertXmlOptionsStringToJson()
    {
        $xmlStr =   '<options>
                        <!-- simple options -->
                        <container>on</container>
                    </options>';

        $xmlData = new \SimpleXMLElement($xmlStr);
        $jsonOptions = TemplateConfig::convertOptionsToJson($xmlData);

        $this->assertSame('{"container":"on"}', $jsonOptions, 'Unexpected result. The options were not encoded correctly.');
    }

    public function testXmlStringWithMultipleNodes()
    {
        $xmlStr =   '<options>
                        <hideprivacyinfo type="buttons" category="Simple options" width="4" title="Hide privacy info" options="on|off" optionlabels="Yes|No">off</hideprivacyinfo>
                        <cssframework type="dropdown" category="Simple options" title="Variations" parent="cssframework">
                                    <dropdownoptions>
                                        <option data-mode="replace" value="css/variations/basic.min.css">Basic Bootstrap</option>
                                        <option data-mode="replace" value="css/variations/cerulean.min.css">Cerulean</option>
                                    </dropdownoptions>
                        </cssframework>
                    </options>';

        $xmlData = new \SimpleXMLElement($xmlStr);
        $jsonOptions = TemplateConfig::convertOptionsToJson($xmlData);

        $this->assertSame('{"hideprivacyinfo":"off","cssframework":""}', $jsonOptions, 'Unexpected result. The options were not encoded correctly.');
    }
}
