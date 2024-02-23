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
        $transformer = new FormatterDateTimeToJson();
        $tr = $transformer->format('2024-02-01 18:17:16');
        $this->assertEquals('2024-02-01T18:17:16.000Z', $tr);
    }

    /**
     * @testdox Cast JSON datetime string to UTC datetime string
     */
    public function testRevert()
    {
        $transformer = new FormatterDateTimeToJson(true);
        $tr = $transformer->format('2024-02-01T18:17:16.000Z');
        $this->assertEquals('2024-02-01T18:17:16+00:00', $tr);
    }

}
