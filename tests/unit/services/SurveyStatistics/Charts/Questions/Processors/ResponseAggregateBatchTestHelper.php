<?php

namespace ls\tests\unit\services\SurveyStatistics\Charts\Questions\Processors;

use LimeSurvey\Models\Services\SurveyStatistics\Charts\Questions\Processors\ResponseAggregateBatch;
use ReflectionProperty;

/**
 * Processors only ever see the alias strings ResponseAggregateBatch hands
 * back from its registration methods (countValue, sumValues, medianValue,
 * etc - pure, no DB) and resolve them later through value($alias). This
 * lets tests use a real
 * batch instance - re-registering the same request from the test to
 * recover the alias the processor used - and inject the "as if executed"
 * results directly, without ever touching the DB-bound execute().
 */
trait ResponseAggregateBatchTestHelper
{
    private function injectBatchResults(ResponseAggregateBatch $batch, array $results): void
    {
        $property = new ReflectionProperty(ResponseAggregateBatch::class, 'results');
        $property->setValue($batch, $results);
    }

    /**
     * @param callable[] $values
     * @return array resolved values, in the same shape/order as $values
     */
    private function resolveAll(array $values): array
    {
        return array_map(static fn($value) => is_callable($value) ? $value() : $value, $values);
    }
}
