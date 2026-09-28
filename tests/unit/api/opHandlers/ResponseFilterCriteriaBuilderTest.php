<?php

namespace ls\tests\unit\api\opHandlers;

use InvalidArgumentException;
use LimeSurvey\Libraries\Api\Command\V1\SurveyResponses\ResponseFilterCriteriaBuilder;
use LimeSurvey\Models\Services\ResponseFilters\ParticipantResolver;
use LimeSurvey\Models\Services\ResponseFilters\ResolvedCondition;
use LimeSurvey\Models\Services\ResponseFilters\ResolvedFilter;
use LimeSurvey\Models\Services\ResponseFilters\ResponseFilter;
use ls\tests\TestCondition;

class ResponseFilterCriteriaBuilderTest extends TestCondition
{
    private ResponseFilterCriteriaBuilder $builder;

    protected function setUp(): void
    {
        $this->builder = new ResponseFilterCriteriaBuilder();
    }

    private function equals(string $key, string $value, ?string $relation = null): ResolvedCondition
    {
        return new ResolvedCondition([$key], ResolvedCondition::OPERATOR_EQUAL, $value, $relation);
    }

    private function row(array $conditions, string $join = 'and', string $innerJoin = 'and'): ResolvedFilter
    {
        return new ResolvedFilter($join, $conditions, $innerJoin);
    }

    public function testNoFiltersLeaveTheQueryUntouched(): void
    {
        $criteria = $this->builder->build([]);

        $this->assertSame('', $criteria->condition);
        $this->assertSame([], $criteria->params);
    }

    public function testASingleConditionIsPassedThrough(): void
    {
        $criteria = $this->builder->build([$this->row([$this->equals('gender', 'F')])]);

        $paramName = array_key_first($criteria->params);
        $this->assertFieldConditions($criteria->condition, "[0] = $paramName", ['gender']);
        $this->assertSame(['F'], array_values($criteria->params));
    }

    /** Two rows with the default join must both hold. */
    public function testRowsJoinedByAnd(): void
    {
        $criteria = $this->builder->build([
            $this->row([$this->equals('gender', 'F')]),
            $this->row([$this->equals('country', 'DE')]),
        ]);

        $this->assertStringContainsString(') AND (', $criteria->condition);
        $this->assertSame(['F', 'DE'], array_values($criteria->params));
    }

    public function testRowsJoinedByOr(): void
    {
        $criteria = $this->builder->build([
            $this->row([$this->equals('gender', 'F')]),
            $this->row([$this->equals('country', 'DE')], ResponseFilter::JOIN_OR),
        ]);

        $this->assertStringContainsString(') OR (', $criteria->condition);
    }

    /**
     * The modal is a list read top to bottom, so "A OR B AND C" means
     * "(A OR B) AND C". SQL's own precedence would group it the other way and
     * return different rows for the same filter.
     */
    public function testRowsFoldLeftToRightRatherThanBySqlPrecedence(): void
    {
        $criteria = $this->builder->build([
            $this->row([$this->equals('a', '1')]),
            $this->row([$this->equals('b', '2')], ResponseFilter::JOIN_OR),
            $this->row([$this->equals('c', '3')]),
        ]);

        // ((a OR b) AND c) — the OR is nested inside, not trailing.
        $orPosition = strpos($criteria->condition, ' OR ');
        $andPosition = strpos($criteria->condition, ' AND ');
        $this->assertNotFalse($orPosition);
        $this->assertNotFalse($andPosition);
        $this->assertLessThan($andPosition, $orPosition, 'The OR should be grouped before the AND applies.');
    }

    /** Parts of one answer must all hold. */
    public function testConditionsInsideARowAndByDefault(): void
    {
        $criteria = $this->builder->build([
            $this->row([$this->equals('Q1_S1#0', 'A1'), $this->equals('Q1_S1#1', 'B1')]),
        ]);

        $this->assertStringContainsString(') AND (', $criteria->condition);
        $this->assertSame(['A1', 'B1'], array_values($criteria->params));
    }

    /** Options picked from one list mean "any of these". */
    public function testConditionsInsideARowCanOr(): void
    {
        $criteria = $this->builder->build([
            $this->row(
                [$this->equals('Q1_S1', 'Y'), $this->equals('Q1_Cother', 'x')],
                ResponseFilter::JOIN_AND,
                ResponseFilter::JOIN_OR
            ),
        ]);

        $this->assertStringContainsString(') OR (', $criteria->condition);
    }

    /**
     * An unfilled row is dropped with its join, so it cannot widen an OR back
     * to matching everything.
     */
    public function testEmptyRowsAreDroppedWithTheirJoin(): void
    {
        $criteria = $this->builder->build([
            $this->row([$this->equals('gender', 'F')]),
            $this->row([], ResponseFilter::JOIN_OR),
        ]);

        $paramName = array_key_first($criteria->params);
        $this->assertFieldConditions($criteria->condition, "[0] = $paramName", ['gender']);
        $this->assertStringNotContainsString(' OR ', $criteria->condition);
    }

    public function testOnlyEmptyRowsLeaveTheQueryUntouched(): void
    {
        $criteria = $this->builder->build([$this->row([]), $this->row([], ResponseFilter::JOIN_OR)]);

        $this->assertSame('', $criteria->condition);
    }

    /** Several keys on one condition reach the handler together, to be OR'd. */
    public function testAConditionWithSeveralKeysIsPassedAsAnArray(): void
    {
        $criteria = $this->builder->build([
            $this->row([
                new ResolvedCondition(['Q1_S1', 'Q1_S2'], ResolvedCondition::OPERATOR_EQUAL, 'Y'),
            ]),
        ]);

        $this->assertStringContainsString(' OR ', $criteria->condition);
        $this->assertSame(['Y', 'Y'], array_values($criteria->params));
    }

    /**
     * Participant columns are in a joined table, so they carry its name and
     * quote as two identifiers rather than one strange one.
     */
    public function testParticipantColumnsAreQualifiedByTheirTable(): void
    {
        $criteria = $this->builder->build([
            $this->row([
                new ResolvedCondition(
                    ['email'],
                    ResolvedCondition::OPERATOR_CONTAIN,
                    'example.org',
                    ParticipantResolver::RELATION
                ),
            ]),
        ]);

        $this->assertStringContainsString('tokens', $criteria->condition);
        $this->assertStringNotContainsString('tokens.email', $criteria->condition);
    }

    /** The caller is told what to join, and told it once. */
    public function testRelationsAreReportedForTheCallerToJoin(): void
    {
        $participant = static function (string $attribute): ResolvedCondition {
            return new ResolvedCondition(
                [$attribute],
                ResolvedCondition::OPERATOR_CONTAIN,
                'x',
                ParticipantResolver::RELATION
            );
        };

        $criteria = $this->builder->build([
            $this->row([$participant('email')]),
            $this->row([$participant('lastname')]),
        ]);

        $this->assertNotSame('', $criteria->condition);
        $this->assertSame([ParticipantResolver::RELATION], $this->builder->getRelations());
    }

    public function testNothingIsJoinedWhenNoFilterNeedsIt(): void
    {
        $this->builder->build([$this->row([$this->equals('gender', 'F')])]);

        $this->assertSame([], $this->builder->getRelations());
    }

    /** Relations do not leak from one build into the next. */
    public function testRelationsAreResetBetweenBuilds(): void
    {
        $this->builder->build([
            $this->row([
                new ResolvedCondition(
                    ['email'],
                    ResolvedCondition::OPERATOR_CONTAIN,
                    'x',
                    ParticipantResolver::RELATION
                ),
            ]),
        ]);
        $this->builder->build([$this->row([$this->equals('gender', 'F')])]);

        $this->assertSame([], $this->builder->getRelations());
    }

    /**
     * Every operator the resolvers emit must have a handler, or a filter the
     * user built would silently do nothing.
     */
    public function testEveryResolvedOperatorHasAHandler(): void
    {
        $operators = [
            ResolvedCondition::OPERATOR_EQUAL => 'Y',
            ResolvedCondition::OPERATOR_CONTAIN => 'text',
            ResolvedCondition::OPERATOR_RANGE => [1, 10],
            ResolvedCondition::OPERATOR_DATE_RANGE => ['2024-01-01', '2024-12-31'],
            ResolvedCondition::OPERATOR_MULTI_SELECT => ['A1', 'A2'],
            ResolvedCondition::OPERATOR_NOT_EMPTY => null,
            ResolvedCondition::OPERATOR_EMPTY => null,
            ResolvedCondition::OPERATOR_JSON_ELEMENT => ['position' => 0, 'value' => 'SQ001'],
        ];

        foreach ($operators as $operator => $value) {
            $criteria = $this->builder->build([
                $this->row([new ResolvedCondition(['col'], $operator, $value)]),
            ]);

            $this->assertNotSame('', $criteria->condition, "No SQL built for operator: $operator");
        }
    }

    public function testAnUnknownOperatorThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('No handler for filter operator: invented.');
        $this->builder->build([$this->row([new ResolvedCondition(['col'], 'invented', 'x')])]);
    }

    /**
     * Every condition binds its own placeholders, so filters in one request
     * cannot overwrite each other's values.
     */
    public function testManyFiltersKeepEveryBoundValue(): void
    {
        $criteria = $this->builder->build([
            $this->row([$this->equals('a', '1')]),
            $this->row([$this->equals('b', '2')], ResponseFilter::JOIN_OR),
            $this->row([$this->equals('a', '3')]),
        ]);

        $this->assertCount(3, $criteria->params);
        $this->assertSame(['1', '2', '3'], array_values($criteria->params));
    }
}
