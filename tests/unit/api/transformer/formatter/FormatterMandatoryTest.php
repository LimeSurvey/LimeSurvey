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
        $this->assertEquals('S', $formatter->format('S', ['revert' => true]));
    }

    /**
     * @testdox formats true to Y
     */
    public function testConvertsTrueToY()
    {
        $formatter = new FormatterMandatory();
        $this->assertEquals('Y', $formatter->format(true, ['revert' => true]));
    }

    /**
     * @testdox formats false to N
     */
    public function testConvertsFalseToN()
    {
        $formatter = new FormatterMandatory();
        $this->assertEquals('N', $formatter->format(false, ['revert' => true]));
    }
}
