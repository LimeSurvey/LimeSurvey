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
     * @testdox keeps S
     */
    public function testKeepsS()
    {
        $formatter = new FormatterMandatory();
        $config = $this->getConfig();
        $this->assertEquals('S', $formatter->format('S', $config));
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
