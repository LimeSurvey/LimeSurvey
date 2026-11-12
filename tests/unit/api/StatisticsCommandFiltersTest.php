<?php

namespace ls\tests\unit\api;

use LimeSurvey\Api\Command\Request\Request;
use LimeSurvey\Api\Rest\Endpoint\EndpointFactory;
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

    public function testNullAndEmptyRangeBoundsAreOpen(): void
    {
        $conditions = [
            ['key' => 'Q43', 'filterMethod' => 'range', 'value' => [null, 35]],
            ['key' => 'Q44', 'filterMethod' => 'range', 'value' => ['', '2026-01-31']],
        ];
        $filters = $this->makeCommand()->getFilters(new Request(['conditions' => $conditions]));
        $this->assertSame(['conditions' => $conditions], $filters->getFilters());
    }

    /**
     * @dataProvider invalidConditionProvider
     */
    public function testMalformedConditionsAreBadRequests(array $condition): void
    {
        $this->expectException(\InvalidArgumentException::class);
        (new StatisticsResponseFilters())->setConditions([$condition]);
    }

    public function invalidConditionProvider(): array
    {
        return [
            'associative range' => [['key' => 'Q1', 'filterMethod' => 'range', 'value' => ['max' => 50, 'min' => 10]]],
            'range of three' => [['key' => 'Q1', 'filterMethod' => 'range', 'value' => [1, 2, 3]]],
            'range of one' => [['key' => 'Q1', 'filterMethod' => 'range', 'value' => [1]]],
            'null equal' => [['key' => 'Q1', 'filterMethod' => 'equal', 'value' => null]],
            'null in multi-select' => [['key' => 'Q1', 'filterMethod' => 'multi-select', 'value' => [null]]],
            'boolean equal' => [['key' => 'quotaExit', 'filterMethod' => 'equal', 'value' => false]],
            'nested value' => [['key' => 'Q1', 'filterMethod' => 'equal', 'value' => [['x']]]],
            'ipaddr' => [['key' => 'ipaddr', 'filterMethod' => 'equal', 'value' => '1.2.3.4']],
            'refurl' => [['key' => 'refurl', 'filterMethod' => 'equal', 'value' => 'x']],
            'seed' => [['key' => 'seed', 'filterMethod' => 'equal', 'value' => '1']],
            'startlanguage' => [['key' => 'startlanguage', 'filterMethod' => 'equal', 'value' => 'en']],
            'sql in key' => [['key' => 'id; DROP TABLE x', 'filterMethod' => 'equal', 'value' => '1']],
        ];
    }

    public function testAllowedColumnsPass(): void
    {
        $conditions = [
            ['key' => '12345X1X2', 'filterMethod' => 'equal', 'value' => 'A'],
            ['key' => 'Q42_S1', 'filterMethod' => 'equal', 'value' => 'A'],
            ['key' => 'quotaExit', 'filterMethod' => 'equal', 'value' => 1],
            ['key' => 'id', 'filterMethod' => 'range', 'value' => [1, 5]],
            ['key' => 'submitdate', 'filterMethod' => 'range', 'value' => ['2026-01-01', null]],
            ['key' => 'lastpage', 'filterMethod' => 'equal', 'value' => 3],
        ];
        $filters = (new StatisticsResponseFilters())->setConditions($conditions);
        $this->assertSame(['conditions' => $conditions], $filters->getFilters());
    }

    public function testTokenConditionNeedsPermission(): void
    {
        $condition = [['key' => 'Token', 'filterMethod' => 'equal', 'value' => 'abc']];
        $this->assertCount(1, (new StatisticsResponseFilters())->setConditions($condition, true)->getFilters());

        $this->expectException(\LimeSurvey\Models\Services\Exception\PermissionDeniedException::class);
        (new StatisticsResponseFilters())->setConditions($condition);
    }

    public function testTokenConditionWithoutPermissionIsRefusedByTheCommand(): void
    {
        $command = $this->makeCommand();
        $permission = $this->createMock(\Permission::class);
        $permission->method('hasSurveyPermission')->willReturn(false);
        $property = new \ReflectionProperty(Statistics::class, 'permission');
        $property->setAccessible(true);
        $property->setValue($command, $permission);

        $this->expectException(\LimeSurvey\Models\Services\Exception\PermissionDeniedException::class);
        $command->getFilters(new Request([
            '_id' => 1,
            'conditions' => [['key' => 'token', 'filterMethod' => 'equal', 'value' => 'abc']],
        ]));
    }

    public function testInvalidCompletionIsRejected(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->makeCommand()->getFilters(new Request(['completion' => 'COMPLETED']));
    }

    public function testRestEndpointForwardsCompletionAndConditions(): void
    {
        $rest = require __DIR__ . '/../../../application/config/rest/v1/statistics.php';
        $endpoint = $rest['v1/statistics/$id']['GET'];

        $conditions = [['key' => 'Q42', 'filterMethod' => 'equal', 'value' => 'x']];
        $request = new class ($conditions) extends \CHttpRequest {
            private array $conditions;

            public function __construct(array $conditions)
            {
                $this->conditions = $conditions;
            }

            public function getParam($name, $defaultValue = null)
            {
                $values = ['_id' => 7, 'completion' => 'completed', 'conditions' => $this->conditions];
                return $values[$name] ?? $defaultValue;
            }

            public function getRestParams()
            {
                return [];
            }
        };
        $factory = new class extends EndpointFactory {
            public function __construct()
            {
            }

            public function getAuthToken()
            {
                return 'token';
            }
        };

        $params = $factory->getCommandParams($endpoint, $request);
        $this->assertSame('completed', $params['completion']);
        $this->assertSame($conditions, $params['conditions']);

        $filters = $this->makeCommand()->getFilters(new Request($params));
        $this->assertSame(
            ['completion' => 'completed', 'conditions' => $conditions],
            $filters->getFilters()
        );
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
