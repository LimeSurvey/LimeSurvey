<?php

namespace ls\tests\unit\services\ResponseFilters;

use InvalidArgumentException;
use LimeSurvey\Models\Services\ResponseFilters\ParticipantResolver;
use LimeSurvey\Models\Services\ResponseFilters\ResolvedCondition;
use LimeSurvey\Models\Services\ResponseFilters\ResolvedFilter;
use LimeSurvey\Models\Services\ResponseFilters\ResponseFilter;
use LimeSurvey\Models\Services\ResponseFilters\ResponseFilterSqlBuilder;
use ls\tests\TestCondition;

/**
 * What the statistics emitter owns by itself. Whether it agrees with the
 * responses emitter is ResponseFilterBuilderEquivalenceTest's job.
 */
class ResponseFilterSqlBuilderTest extends TestCondition
{
    private const SURVEY_ID = 563242;

    private ResponseFilterSqlBuilder $builder;

    protected function setUp(): void
    {
        $this->builder = new ResponseFilterSqlBuilder(self::SURVEY_ID);
    }

    public function testNoFiltersProduceNoWhere(): void
    {
        $this->assertSame(
            ['condition' => '', 'params' => []],
            $this->builder->build([])
        );
    }

    public function testASingleConditionIsPassedThrough(): void
    {
        $sql = $this->builder->build([$this->row([$this->equals('gender', 'F')])]);

        $paramName = array_key_first($sql['params']);
        $this->assertFieldConditions($sql['condition'], "[0] = $paramName", ['gender']);
        $this->assertSame(['F'], array_values($sql['params']));
    }

    /** The participant table is read without joining it to the aggregates. */
    public function testParticipantFiltersBecomeASubquery(): void
    {
        $sql = $this->builder->build([
            $this->row([$this->participant('email', 'example.org')]),
        ]);

        $this->assertStringContainsString(
            'IN (SELECT',
            $sql['condition']
        );
        $this->assertStringContainsString('{{tokens_' . self::SURVEY_ID . '}}', $sql['condition']);
        $this->assertFieldConditions($sql['condition'], '[0] IN (SELECT', ['token']);
        $this->assertSame(['%example.org%'], array_values($sql['params']));
    }

    public function testAParticipantFilterNeedsTheSurvey(): void
    {
        $builder = new ResponseFilterSqlBuilder();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('A participant filter needs the survey it belongs to.');
        $builder->build([$this->row([$this->participant('email', 'example.org')])]);
    }

    public function testAnUnknownRelationThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('No SQL for filter relation: quotas.');
        $this->builder->build([
            $this->row([
                new ResolvedCondition(['limit'], ResolvedCondition::OPERATOR_EQUAL, '1', 'quotas'),
            ]),
        ]);
    }

    public function testAnUnknownOperatorThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('No handler for filter operator: invented.');
        $this->builder->build([
            $this->row([new ResolvedCondition(['col'], 'invented', 'x')]),
        ]);
    }

    public function testEmptyRowsAreDroppedWithTheirJoin(): void
    {
        $sql = $this->builder->build([
            $this->row([$this->equals('gender', 'F')]),
            $this->row([], ResponseFilter::JOIN_OR),
        ]);

        $this->assertStringNotContainsString(' OR ', $sql['condition']);
        $this->assertCount(1, $sql['params']);
    }

    /**
     * The clause is executed as a prepared statement, so a placeholder with no
     * bound value — or a value with no placeholder — fails the whole query.
     */
    public function testEveryPlaceholderIsBoundAndEveryBoundValueIsUsed(): void
    {
        $sql = $this->builder->build([
            $this->row([new ResolvedCondition(['id'], ResolvedCondition::OPERATOR_RANGE, [1, 10])]),
            $this->row(
                [new ResolvedCondition(['Q1'], ResolvedCondition::OPERATOR_MULTI_SELECT, ['A1', 'A2'])],
                ResponseFilter::JOIN_OR
            ),
            $this->row([$this->participant('email', 'example.org')]),
            $this->row([new ResolvedCondition(['submitdate'], ResolvedCondition::OPERATOR_NULL, 'true')]),
        ]);

        preg_match_all('/:[A-Za-z_][A-Za-z0-9_]*/', $sql['condition'], $matches);
        $used = array_unique($matches[0]);
        $bound = array_map(static fn(string $name): string => ':' . ltrim($name, ':'), array_keys($sql['params']));

        sort($used);
        sort($bound);
        $this->assertSame($bound, $used);
    }

    /**
     * Statistics run the same clause in several statements (the aggregate scan,
     * then one sub-select per median), and PDO cannot take the same placeholder
     * twice in a native prepare — so a rebuild must mint new names.
     */
    public function testRebuildingTheSameFilterMintsNewPlaceholders(): void
    {
        $filters = [$this->row([$this->equals('gender', 'F')])];

        $first = $this->builder->build($filters);
        $second = $this->builder->build($filters);

        $this->assertSame([], array_intersect_key($first['params'], $second['params']));
        $this->assertSame(array_values($first['params']), array_values($second['params']));
    }

    private function equals(string $key, string $value): ResolvedCondition
    {
        return new ResolvedCondition([$key], ResolvedCondition::OPERATOR_EQUAL, $value);
    }

    private function participant(string $attribute, string $value): ResolvedCondition
    {
        return new ResolvedCondition(
            [$attribute],
            ResolvedCondition::OPERATOR_CONTAIN,
            $value,
            ParticipantResolver::RELATION
        );
    }

    /** @param ResolvedCondition[] $conditions */
    private function row(
        array $conditions,
        string $join = ResponseFilter::JOIN_AND,
        string $innerJoin = ResponseFilter::JOIN_AND
    ): ResolvedFilter {
        return new ResolvedFilter($join, $conditions, $innerJoin);
    }
}
