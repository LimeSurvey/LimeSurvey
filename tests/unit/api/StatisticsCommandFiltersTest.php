<?php

namespace ls\tests\unit\api;

use LimeSurvey\Api\Command\Request\Request;
use LimeSurvey\Libraries\Api\Command\V1\Statistics;
use LimeSurvey\Models\Services\SurveyStatistics\StatisticsResponseFilters;
use PHPUnit\Framework\TestCase;

/**
 * The statistics command reads its filters from the command request,
 * so REST (query strings) and MCP (JSON values) callers behave the same.
 */
class StatisticsCommandFiltersTest extends TestCase
{
    public function testFiltersFromRestQueryStrings(): void
    {
        $filters = $this->makeCommand()->getFilters(new Request([
            'minId' => '5',
            'maxId' => '10',
            'completed' => 'true',
            'search' => ['late'],
        ]));

        $this->assertSame(
            ['minId' => 5, 'maxId' => 10, 'completed' => true, 'search' => ['late']],
            $filters->getFilters()
        );
    }

    public function testFiltersFromJsonValues(): void
    {
        $filters = $this->makeCommand()->getFilters(new Request([
            'completed' => false,
            'search' => 'single term',
        ]));

        $this->assertSame(['completed' => false, 'search' => ['single term']], $filters->getFilters());
    }

    public function testNoFiltersWhenParamsAreMissing(): void
    {
        $filters = $this->makeCommand()->getFilters(new Request([]));

        $this->assertSame(0, $filters->count());
    }

    public function testResponseColumnConditionsAreValidatedAndForwarded(): void
    {
        $conditions = [
            ['key' => 'Q42', 'filterMethod' => 'equal', 'value' => 'v1'],
            ['key' => 'quotaExit', 'filterMethod' => 'multi-select', 'value' => [1, 2]],
            ['key' => 'Q43', 'filterMethod' => 'range', 'value' => [20, 35]],
        ];
        $filters = $this->makeCommand()->getFilters(new Request(['conditions' => $conditions]));
        $this->assertSame(['conditions' => $conditions], $filters->getFilters());

        $this->expectException(\InvalidArgumentException::class);
        $filters->setConditions([['key' => 'Q42', 'filterMethod' => 'range', 'value' => [1]]]);
    }

    public function testRejectsUnsupportedConditionMethods(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->makeCommand()->getFilters(new Request(['conditions' => [
            ['key' => 'Q42', 'filterMethod' => 'raw_sql', 'value' => '1=1'],
        ]]));
    }

    public function testRejectsNonArrayConditions(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('conditions must be an array');

        $this->makeCommand()->getFilters(new Request(['conditions' => 'x']));
    }

    public function testCompletionStateIsForwarded(): void
    {
        $filters = $this->makeCommand()->getFilters(new Request(['completion' => 'screened_out']));
        $this->assertSame(['completion' => 'screened_out'], $filters->getFilters());
    }

    public function testRejectsNonStringCompletionBeforeCheckingConflict(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('completion must be a string');

        $this->makeCommand()->getFilters(new Request([
            'completed' => true,
            'completion' => ['screened_out'],
        ]));
    }

    public function testCompletedAndNonAllCompletionAreRejected(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Use either completed or completion, not both.');

        $this->makeCommand()->getFilters(new Request([
            'completed' => false,
            'completion' => 'screened_out',
        ]));
    }

    public function testCompletedAndAllCompletionAreAllowed(): void
    {
        $filters = $this->makeCommand()->getFilters(new Request([
            'completed' => false,
            'completion' => 'all',
        ]));

        $this->assertSame(['completed' => false], $filters->getFilters());
    }

    public function testEachCompletionFilterIsAllowedAlone(): void
    {
        $completed = $this->makeCommand()->getFilters(new Request(['completed' => true]));
        $completion = $this->makeCommand()->getFilters(new Request(['completion' => 'incomplete']));

        $this->assertSame(['completed' => true], $completed->getFilters());
        $this->assertSame(['completion' => 'incomplete'], $completion->getFilters());
    }

    private function makeCommand(): Statistics
    {
        $reflection = new \ReflectionClass(Statistics::class);
        /** @var Statistics $command */
        $command = $reflection->newInstanceWithoutConstructor();
        $property = $reflection->getProperty('filters');
        $property->setAccessible(true);
        $property->setValue($command, new StatisticsResponseFilters());

        return $command;
    }
}
