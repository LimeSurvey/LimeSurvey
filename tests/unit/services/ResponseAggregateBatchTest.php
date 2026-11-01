<?php

namespace ls\tests\unit\services;

use LimeSurvey\Models\Services\SurveyStatistics\Charts\Questions\Processors\ResponseAggregateBatch;
use PHPUnit\Framework\TestCase;

class ResponseAggregateBatchTest extends TestCase
{
    public function testMedianChunksStayWithinParameterBudget(): void
    {
        $method = new \ReflectionMethod(ResponseAggregateBatch::class, 'medianChunkSize');
        $method->setAccessible(true);

        foreach ([0 => 100, 1 => 100, 20 => 100, 21 => 95, 1000 => 2] as $paramsPerSelect => $expected) {
            $size = $method->invoke(null, $paramsPerSelect);
            $this->assertSame($expected, $size);
            $this->assertLessThanOrEqual(2000, $size * $paramsPerSelect);
        }
    }
}
