<?php

namespace ls\tests\unit\api;

use ls\tests\TestBaseClass;
use LimeSurvey\Api\Transformer\Formatter\FormatterYnToBool;
use LimeSurvey\Api\Transformer\Formatter\FormatterIntToBool;

/**
 * @testdox API Formatter Y/N to Boolean
 */
class FormatterYnToBoolTest extends TestBaseClass
{
    public function testConvertsYToTrue()
    {
        $transformer = new FormatterYnToBool;
        $this->assertTrue($transformer->format('Y'));
        $this->assertTrue($transformer->format('y'));
    }

    public function testConvertsNToFalse()
    {
        $transformer = new FormatterYnToBool;
        $this->assertFalse($transformer->format('N'));
        $this->assertFalse($transformer->format('n'));
    }

    public function testConvertsEmptyStringToNull()
    {
        $transformer = new FormatterYnToBool;
        $this->assertNull($transformer->format(''));
    }

    public function testPassesNullUnchanged()
    {
        $transformer = new FormatterYnToBool;
        $this->assertNull($transformer->format(null));
    }

    public function testRevertsTrueToY()
    {
        $transformer = new FormatterYnToBool(true);
        $this->assertEquals('Y', $transformer->format(true));
    }

    public function testRevertsFalseToN()
    {
        $transformer = new FormatterYnToBool(true);
        $this->assertEquals('N', $transformer->format(false));
    }
}
