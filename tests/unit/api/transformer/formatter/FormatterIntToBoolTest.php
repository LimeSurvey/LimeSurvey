<?php

namespace ls\tests\unit\api;

use ls\tests\TestBaseClass;
use LimeSurvey\Api\Transformer\Formatter\FormatterIntToBool;

/**
 * @testdox API Formatter Int to Boolean
 */
class FormatterIntToBoolTest extends TestBaseClass
{
    public function testConvertsTrueyToTrue()
    {
        $transformer = new FormatterIntToBool;
        $this->assertTrue($transformer->format(1));
        $this->assertTrue($transformer->format('1'));
        $this->assertTrue($transformer->format('truey'));
    }

    public function testConvertsFalseyToFalse()
    {
        $transformer = new FormatterIntToBool;
        $this->assertFalse($transformer->format(0));
        $this->assertFalse($transformer->format(-1));
        $this->assertFalse($transformer->format('0'));
        $this->assertFalse($transformer->format('-1'));
    }

    public function testConvertsEmptyStringToNull()
    {
        $transformer = new FormatterIntToBool;
        $this->assertNull($transformer->format(''));
    }

    public function testPassesNullUnchanged()
    {
        $transformer = new FormatterIntToBool;
        $this->assertNull($transformer->format(null));
    }

    public function testRevertsTrueyToInt()
    {
        $transformer = new FormatterIntToBool(true);
        $this->assertEquals(1, $transformer->format(1));
        $this->assertEquals(1, $transformer->format('Y'));
        $this->assertEquals(1, $transformer->format('truey'));
    }

    public function testRevertsFalseyToInt()
    {
        $transformer = new FormatterIntToBool(true);
        $this->assertEquals(0, $transformer->format(0));
        $this->assertEquals(0, $transformer->format(-1));
        $this->assertEquals(0, $transformer->format('0'));
        $this->assertEquals(0, $transformer->format('-1'));
    }
}
