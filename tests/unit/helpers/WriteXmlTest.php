<?php

namespace ls\tests;

use Yii;
use LimeExpressionManager;

class WriteXmlTest extends TestBaseClass
{
    public function testBasic()
    {
        Yii::import('application.helpers.export_helper', true);

        $data = json_decode(<<<JSON
{"themes":{"theme":[{"id":null,"sid":946185,"template_name":"bootswatch","config":{"options":{"general_inherit":null,"font":"inherit","cssframework":"inherit","brandlogofile":"inherit","container":"inherit","showpopups":"inherit","showclearall":"inherit","questionhelptextposition":"inherit","fixnumauto":"inherit","hideprivacyinfo":"on","brandlogo":"inherit","generalInherit":null}}},{"id":null,"sid":946185,"template_name":"extends_fruity","config":{"options":"inherit"}},{"id":null,"sid":946185,"template_name":"fruity","config":{"options":"inherit"}},{"id":null,"sid":946185,"template_name":"fruity_twentythree","config":{"options":"inherit"}},{"id":null,"sid":946185,"template_name":"vanilla","config":{"options":"inherit"}}]}}
JSON);
        $xml = $this->getMockBuilder(XmlWriter::class)->getMock();
        \writeXmlFromArray($xml, $data);
    }
}
