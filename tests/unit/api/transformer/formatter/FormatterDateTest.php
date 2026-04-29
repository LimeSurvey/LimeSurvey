<?php

namespace ls\tests\unit\api;

use ls\tests\TestBaseClass;
use LimeSurvey\Api\Transformer\Formatter\FormatterDateTimeToJson;

/**
 * @testdox API Formatter Date
 */
class FormatterDateTimeToJsonTest extends TestBaseClass
{
    /**
     * @testdox Cast UTC datetime string to JSON datetime string
     */
    public function testApply()
    {
        $formatter = new FormatterDateTimeToJson();
        $config = $this->getConfig();
        $tr = $formatter->format('2024-02-01 18:17:16', $config);
        $this->assertEquals('2024-02-01T18:17:16.000Z', $tr);
    }

    /**
     * @testdox Cast JSON datetime string to UTC datetime string
     */
    public function testRevert()
    {
        $formatter = new FormatterDateTimeToJson();
        $config = $this->getConfig(true);
        $tr = $formatter->format('2024-02-01T18:17:16.000Z', $config);
        $this->assertEquals('2024-02-01 18:17:16', $tr);
    }

    private function getConfig($revert = false)
    {
        return ['revert' => $revert, 'inputTimezone' => 'UTC'];
    }
}
