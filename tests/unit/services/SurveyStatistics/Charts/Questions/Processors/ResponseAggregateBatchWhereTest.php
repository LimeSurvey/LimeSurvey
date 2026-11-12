<?php

namespace ls\tests\unit\services\SurveyStatistics\Charts\Questions\Processors;

use LimeSurvey\Models\Services\ResponseFilters\ResolvedCondition;
use LimeSurvey\Models\Services\ResponseFilters\ResolvedFilter;
use LimeSurvey\Models\Services\ResponseFilters\ResponseFilter;
use LimeSurvey\Models\Services\SurveyStatistics\Charts\Questions\Processors\ResponseAggregateBatch;
use LimeSurvey\Models\Services\SurveyStatistics\StatisticsResponseFilters;
use CDbCommand;
use CDbConnection;
use ls\tests\TestCondition;
use ReflectionMethod;

/**
 * The WHERE the aggregate scan runs under. execute() itself needs a database,
 * so the clause is read straight off the builder.
 */
class ResponseAggregateBatchWhereTest extends TestCondition
{
    private const SURVEY_ID = 563242;

    public function testNoFiltersScanEverything(): void
    {
        [$where, $params] = $this->buildWhere(new ResponseAggregateBatch(self::SURVEY_ID));

        $this->assertSame('', $where);
        $this->assertSame([], $params);
    }

    /** The sidebar's filters inline their values, so they bind nothing. */
    public function testLegacyFiltersStayInlined(): void
    {
        $legacy = (new StatisticsResponseFilters())->setMinId(5)->setCompleted(true);

        [$where, $params] = $this->buildWhere(
            new ResponseAggregateBatch(self::SURVEY_ID, $legacy)
        );

        $this->assertSame(' WHERE submitdate IS NOT NULL AND id >= 5', $where);
        $this->assertSame([], $params);
    }

    public function testAResolvedFilterIsBoundRatherThanInlined(): void
    {
        [$where, $params] = $this->buildWhere(
            new ResponseAggregateBatch(self::SURVEY_ID, null, [$this->row('gender', 'F')])
        );

        $paramName = array_key_first($params);
        $this->assertStringStartsWith(' WHERE (', $where);
        $this->assertFieldConditions($where, "[0] = $paramName", ['gender']);
        $this->assertSame(['F'], array_values($params));
    }

    /** Both params can be sent together, and each keeps its meaning. */
    public function testTheTwoFilterSetsCombineWithAnd(): void
    {
        $legacy = (new StatisticsResponseFilters())->setMaxId(100);

        [$where, $params] = $this->buildWhere(
            new ResponseAggregateBatch(self::SURVEY_ID, $legacy, [$this->row('gender', 'F')])
        );

        $this->assertStringStartsWith(' WHERE id <= 100 AND (', $where);
        $this->assertCount(1, $params);
    }

    /**
     * The median query repeats this clause once per field inside a single
     * UNION ALL, where PDO cannot take the same placeholder twice.
     */
    public function testEachBuildMintsNewPlaceholders(): void
    {
        $batch = new ResponseAggregateBatch(self::SURVEY_ID, null, [$this->row('gender', 'F')]);

        [, $first] = $this->buildWhere($batch);
        [, $second] = $this->buildWhere($batch);

        $this->assertSame([], array_intersect_key($first, $second));
        $this->assertSame(array_values($first), array_values($second));
    }

    /**
     * Two date questions under a filter share one UNION ALL, so each part
     * needs placeholders of its own (SQL Server can't repeat one).
     */
    public function testDayCountsBindEachPlaceholderOnce(): void
    {
        $batch = new ResponseAggregateBatch(self::SURVEY_ID, null, [$this->row('gender', 'F')]);
        $batch->countByDay('Q1');
        $batch->countByDay('Q2');

        $queries = [];
        $db = $this->createMock(CDbConnection::class);
        $db->method('quoteColumnName')->willReturnArgument(0);
        $db->method('quoteValue')->willReturnCallback(static fn($value) => "'$value'");
        $db->method('createCommand')->willReturnCallback(
            function (string $sql) use (&$queries) {
                $command = $this->createMock(CDbCommand::class);
                $command->method('queryAll')->willReturnCallback(
                    static function ($fetchAssociative, array $params) use ($sql, &$queries) {
                        $queries[] = [$sql, $params];
                        return [];
                    }
                );
                return $command;
            }
        );

        $method = new ReflectionMethod(ResponseAggregateBatch::class, 'executeDayCounts');
        $method->setAccessible(true);
        $method->invoke($batch, $db, 'responses');

        $this->assertCount(1, $queries);
        [$sql, $params] = $queries[0];
        $this->assertCount(2, $params);
        foreach (array_keys($params) as $name) {
            $this->assertSame(1, preg_match_all('/' . preg_quote($name, '/') . '(?!\\w)/', $sql), $name);
        }
    }

    /**
     * @return array{0: string, 1: array<string,mixed>}
     */
    private function buildWhere(ResponseAggregateBatch $batch): array
    {
        $method = new ReflectionMethod(ResponseAggregateBatch::class, 'buildWhere');
        $method->setAccessible(true);

        return $method->invoke($batch);
    }

    private function row(string $key, string $value): ResolvedFilter
    {
        return new ResolvedFilter(
            ResponseFilter::JOIN_AND,
            [new ResolvedCondition([$key], ResolvedCondition::OPERATOR_EQUAL, $value)]
        );
    }
}
