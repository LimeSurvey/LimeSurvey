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
        $transformer = new FormatterMandatory();
        $this->assertEquals('S', $transformer->format(null));
    }

    /**
     * @testdox formats true to Y
     */
    public function testConvertsTrueToY()
    {
        $transformer = new FormatterMandatory();
        $this->assertEquals('Y', $transformer->format(true));
    }

    /**
     * @testdox formats false to N
     */
    public function testConvertsFalseToN()
    {
        $transformer = new FormatterMandatory();
        $this->assertEquals('N', $transformer->format(false));
    }
}
