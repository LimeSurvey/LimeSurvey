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
}
