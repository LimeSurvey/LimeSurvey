<?php

namespace ls\tests\unit\services\SurveyStatistics\Charts\Questions\Processors;

use LimeSurvey\Models\Services\SurveyStatistics\Charts\Questions\Processors\ResponseAggregateBatch;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use ReflectionProperty;

class ResponseAggregateBatchTest extends TestCase
{
    public function testEncryptedValueRequestIsAggregatedAfterDecryption(): void
    {
        $batch = new ResponseAggregateBatch(1);
        $this->setEncryptedFields($batch, ['Q12']);
        $alias = $batch->countValue('Q12', 'Y');
        $request = $this->getRequest($batch, $alias);

        $this->assertTrue($this->invoke($batch, 'requestUsesEncryptedField', [$request]));
        $this->assertSame(2, $this->invoke($batch, 'aggregateValues', [['Y', 'N', 'Y', ''], $request]));
    }

    public function testEncryptedRankingJsonIsAggregatedByPosition(): void
    {
        $batch = new ResponseAggregateBatch(1);
        $this->setEncryptedFields($batch, ['Q10']);
        $alias = $batch->countJsonArrayValue('Q10', 1, 'SQ1');
        $request = $this->getRequest($batch, $alias);

        $rows = [
            ['Q10' => '["SQ2","SQ1"]'],
            ['Q10' => '["SQ1","SQ2"]'],
            ['Q10' => ''],
        ];
        $values = array_map(
            fn(array $row) => $this->invoke($batch, 'valueForRequest', [$row, $request]),
            $rows
        );

        $this->assertSame(1, $this->invoke($batch, 'aggregateValues', [$values, $request]));
    }

    private function setEncryptedFields(ResponseAggregateBatch $batch, array $fields): void
    {
        $property = new ReflectionProperty(ResponseAggregateBatch::class, 'encryptedFields');
        $property->setValue($batch, array_fill_keys($fields, true));
    }

    private function getRequest(ResponseAggregateBatch $batch, string $alias): array
    {
        $property = new ReflectionProperty(ResponseAggregateBatch::class, 'requests');
        return $property->getValue($batch)[$alias];
    }

    private function invoke(ResponseAggregateBatch $batch, string $method, array $arguments)
    {
        $reflection = new ReflectionMethod(ResponseAggregateBatch::class, $method);
        return $reflection->invokeArgs($batch, $arguments);
    }
}
