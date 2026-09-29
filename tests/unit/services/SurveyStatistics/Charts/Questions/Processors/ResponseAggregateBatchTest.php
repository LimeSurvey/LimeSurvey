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

    public function testEmptyValueDoesNotCountNullCells(): void
    {
        $batch = new ResponseAggregateBatch(1);
        $valueRequest = $this->getRequest($batch, $batch->countValue('Q12', ''));
        $jsonRequest = $this->getRequest($batch, $batch->countJsonArrayValue('Q13', 0, ''));

        $this->assertSame(1, $this->invoke($batch, 'aggregateValues', [[null, ''], $valueRequest]));
        $this->assertSame(1, $this->invoke($batch, 'aggregateValues', [[null, ''], $jsonRequest]));
    }

    public function testNumericEncryptedColumnIsNotTreatedAsJsonPosition(): void
    {
        $batch = new ResponseAggregateBatch(1);
        $this->setEncryptedFields($batch, ['123']);
        $alias = $batch->countValue('123', 'Y');
        $request = $this->getRequest($batch, $alias);

        $this->assertSame(['123'], $this->invoke($batch, 'requestFields', [$request]));
        $this->assertTrue($this->invoke($batch, 'requestUsesEncryptedField', [$request]));
    }

    public function testTextNumericAggregatesUseTheDatabaseNumericPattern(): void
    {
        $batch = new ResponseAggregateBatch(1);
        $textRequest = $this->getRequest($batch, $batch->countNumeric('Q12'));
        $numericRequest = $this->getRequest($batch, $batch->countNumeric('Q13', true));
        $values = ['1', '1e2', '-2.5', 'not a number'];

        $this->assertSame(2, $this->invoke($batch, 'aggregateValues', [$values, $textRequest]));
        $this->assertSame(3, $this->invoke($batch, 'aggregateValues', [$values, $numericRequest]));
    }

    public function testNumericTextPatternMatchesDatabaseDriverRules(): void
    {
        $batch = new ResponseAggregateBatch(1);
        $sqlServerPattern = $this->invoke($batch, 'numericPatternForDriver', ['sqlsrv']);
        $defaultPattern = $this->invoke($batch, 'numericPatternForDriver', ['pgsql']);

        $this->assertSame(1, preg_match('/' . $sqlServerPattern . '/', ' +1.5'));
        $this->assertSame(0, preg_match('/' . $defaultPattern . '/', ' +1.5'));
        $this->assertSame(1, preg_match('/' . $defaultPattern . '/', '-1.5'));
    }

    public function testEncryptedNumericAggregatesUseFourDecimalPrecision(): void
    {
        $batch = new ResponseAggregateBatch(1);
        $values = ['1.23456', '2.00004'];

        $sumRequest = $this->getRequest($batch, $batch->sumValues('Q12'));
        $squaresRequest = $this->getRequest($batch, $batch->sumSquares('Q12'));
        $minRequest = $this->getRequest($batch, $batch->minValue('Q12'));
        $maxRequest = $this->getRequest($batch, $batch->maxValue('Q12'));

        $this->assertEqualsWithDelta(3.2346, $this->invoke($batch, 'aggregateValues', [$values, $sumRequest]), 0.00001);
        $this->assertEqualsWithDelta(1.2346 ** 2 + 2 ** 2, $this->invoke($batch, 'aggregateValues', [$values, $squaresRequest]), 0.00001);
        $this->assertEqualsWithDelta(1.2346, $this->invoke($batch, 'aggregateValues', [$values, $minRequest]), 0.00001);
        $this->assertEqualsWithDelta(2, $this->invoke($batch, 'aggregateValues', [$values, $maxRequest]), 0.00001);
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
