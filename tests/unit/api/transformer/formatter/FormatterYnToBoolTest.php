<?php

namespace ls\tests\unit\api;

use ls\tests\TestBaseClass;
use LimeSurvey\Api\Transformer\Formatter\FormatterYnToBool;

/**
 * @testdox API Formatter Y/N to Boolean
 */
class FormatterYnToBoolTest extends TestBaseClass
{
    public function testConvertsYToTrue()
    {
        $formatter = new FormatterYnToBool();
        $config = $this->getConfig();
        $this->assertTrue($formatter->format('Y', $config));
        $this->assertTrue($formatter->format('y', $config));
    }

    public function testConvertsNToFalse()
    {
        $formatter = new FormatterYnToBool();
        $config = $this->getConfig();
        $this->assertFalse($formatter->format('N', $config));
        $this->assertFalse($formatter->format('n', $config));
    }

    public function testConvertsEmptyStringToNull()
    {
        $formatter = new FormatterYnToBool();
        $config = $this->getConfig();
        $this->assertNull($formatter->format('', $config));
    }

    public function testPassesNullUnchanged()
    {
        $formatter = new FormatterYnToBool();
        $config = $this->getConfig();
        $this->assertNull($formatter->format(null, $config));
    }

    public function testRevertsTrueToY()
    {
        $formatter = new FormatterYnToBool();
        $config = $this->getConfig(true);
        $this->assertEquals('Y', $formatter->format(true, $config));
    }

    public function testRevertsFalseToN()
    {
        $formatter = new FormatterYnToBool();
        $config = $this->getConfig(true);
        $this->assertEquals('N', $formatter->format(false, $config));
    }

    private function getConfig($revert = false)
    {
        return $revert ? ['revert' => true] : [];
    }
}
