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
        $formatter = new FormatterIntToBool();
        $config = $this->getConfig();
        $this->assertTrue($formatter->format(true, $config));
        $this->assertTrue($formatter->format(1, $config));
        $this->assertTrue($formatter->format('1', $config));
        $this->assertTrue($formatter->format('truey', $config));
    }

    public function testConvertsFalseyToFalse()
    {
        $formatter = new FormatterIntToBool();
        $config = $this->getConfig();
        $this->assertFalse($formatter->format(false, $config));
        $this->assertFalse($formatter->format(0, $config));
        $this->assertFalse($formatter->format(-1, $config));
        $this->assertFalse($formatter->format('0', $config));
        $this->assertFalse($formatter->format('-1', $config));
    }

    public function testConvertsEmptyStringToNull()
    {
        $formatter = new FormatterIntToBool();
        $config = $this->getConfig();
        $this->assertNull($formatter->format('', $config));
    }

    public function testPassesNullUnchanged()
    {
        $formatter = new FormatterIntToBool();
        $config = $this->getConfig();
        $this->assertNull($formatter->format(null, $config));
    }

    public function testRevertsTrueyToInt()
    {
        $formatter = new FormatterIntToBool();
        $config = $this->getConfig(true);
        $this->assertEquals(1, $formatter->format(true, $config));
        $this->assertEquals(1, $formatter->format(1, $config));
        $this->assertEquals(1, $formatter->format('Y', $config));
        $this->assertEquals(1, $formatter->format('truey', $config));
    }

    public function testRevertsFalseyToInt()
    {
        $formatter = new FormatterIntToBool();
        $config = $this->getConfig(true);
        $this->assertEquals(0, $formatter->format(false, $config));
        $this->assertEquals(0, $formatter->format(0, $config));
        $this->assertEquals(0, $formatter->format(-1, $config));
        $this->assertEquals(0, $formatter->format('0', $config));
        $this->assertEquals(0, $formatter->format('-1', $config));
    }

    private function getConfig($revert = false)
    {
        $options = $revert ? ['revert' => true] : true;
        return [
            'formatter' => ['intToBool' => $options]
        ];
    }
}
