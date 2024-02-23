<?php

namespace ls\tests\unit\api;

use ls\tests\TestBaseClass;
use LimeSurvey\Api\Transformer\Formatter\FormatterMandatory;

/**
 * @testdox API Formatter Mandatory
 */
class FormatterMandatoryTest extends TestBaseClass
{
    /**
     * @testdox formats null to S
     */
    public function testConvertsNullToS()
    {
        $formatter = new FormatterMandatory();
        $config = $this->getConfig();
        $this->assertEquals('S', $formatter->format(null, $config));
    }

    /**
     * @testdox formats true to Y
     */
    public function testConvertsTrueToY()
    {
        $formatter = new FormatterMandatory();
        $config = $this->getConfig();
        $this->assertEquals('Y', $formatter->format(true, $config));
    }

    /**
     * @testdox formats false to N
     */
    public function testConvertsFalseToN()
    {
        $formatter = new FormatterMandatory();
        $config = $this->getConfig();
        $this->assertEquals('N', $formatter->format(false, $config));
    }

    private function getConfig()
    {
        return [
            'formatter' => ['mandatory' => true]
        ];
    }
}
