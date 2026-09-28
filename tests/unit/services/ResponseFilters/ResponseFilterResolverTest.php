<?php

namespace ls\tests\unit\services\ResponseFilters;

use InvalidArgumentException;
use LimeSurvey\Models\Services\ResponseFilters\ParticipantResolver;
use LimeSurvey\Models\Services\ResponseFilters\QuestionColumnMap;
use LimeSurvey\Models\Services\ResponseFilters\ResolvedCondition;
use LimeSurvey\Models\Services\ResponseFilters\ResponseFilterResolver;
use LimeSurvey\Models\Services\ResponseFilters\ResponseFilterSet;
use PHPUnit\Framework\TestCase;

class ResponseFilterResolverTest extends TestCase
{
    private ResponseFilterResolver $resolver;

    protected function setUp(): void
    {
        $this->resolver = new ResponseFilterResolver(
            QuestionColumnMap::fromQuestionFieldMap([
                '111X1X10' => ['fieldname' => '111X1X10', 'qid' => 10, 'type' => 'S'],
            ]),
            new ParticipantResolver(['email', 'lastname'])
        );
    }

    /** @param array<int,array> $entries */
    private function resolve(array $entries): array
    {
        return $this->resolver->resolve(ResponseFilterSet::fromRequestValue($entries));
    }

    /** Each row goes to the resolver for its own source. */
    public function testEachSourceIsSentToItsResolver(): void
    {
        $resolved = $this->resolve([
            ['source' => 'question', 'qid' => 10, 'text' => 'refund'],
            ['source' => 'surveyData', 'field' => 'id', 'numberMin' => 5],
            ['source' => 'participant', 'attribute' => '{TOKEN:EMAIL}', 'value' => 'example.org'],
        ]);

        $this->assertCount(3, $resolved);
        $this->assertSame(['111X1X10'], $resolved[0]->getConditions()[0]->getKeys());
        $this->assertSame(['id'], $resolved[1]->getConditions()[0]->getKeys());
        $this->assertSame(['email'], $resolved[2]->getConditions()[0]->getKeys());
    }

    /** Only the participant one names a table to join. */
    public function testOnlyParticipantConditionsCarryARelation(): void
    {
        $resolved = $this->resolve([
            ['source' => 'surveyData', 'field' => 'id', 'numberMin' => 5],
            ['source' => 'participant', 'attribute' => 'lastname', 'value' => 'Smith'],
        ]);

        $this->assertNull($resolved[0]->getConditions()[0]->getRelation());
        $this->assertSame(
            ParticipantResolver::RELATION,
            $resolved[1]->getConditions()[0]->getRelation()
        );
    }

    /**
     * Order is kept, because the joins between rows fold left to right and
     * reordering them would change which responses come back.
     */
    public function testOrderAndJoinsAreKept(): void
    {
        $resolved = $this->resolve([
            ['source' => 'surveyData', 'field' => 'id', 'numberMin' => 1],
            ['source' => 'surveyData', 'field' => 'seed', 'numberMin' => 2, 'join' => 'or'],
            ['source' => 'surveyData', 'field' => 'submitdate', 'dateFrom' => '2024-01-01'],
        ]);

        $this->assertFalse($resolved[0]->isOr());
        $this->assertTrue($resolved[1]->isOr());
        $this->assertFalse($resolved[2]->isOr());

        $this->assertSame(['id'], $resolved[0]->getConditions()[0]->getKeys());
        $this->assertSame(['seed'], $resolved[1]->getConditions()[0]->getKeys());
        $this->assertSame(['submitdate'], $resolved[2]->getConditions()[0]->getKeys());
    }

    /** An empty set resolves to nothing rather than to a filter of everything. */
    public function testAnEmptySetResolvesToNoFilters(): void
    {
        $this->assertSame([], $this->resolver->resolve(ResponseFilterSet::fromRequestValue(null)));
    }

    /**
     * Unfilled rows stay in the list; dropping them is the builder's job, and
     * it drops their joins with them.
     */
    public function testUnfilledRowsSurviveAsEmptyFilters(): void
    {
        $resolved = $this->resolve([['source' => 'surveyData', 'field' => 'id']]);

        $this->assertCount(1, $resolved);
        $this->assertTrue($resolved[0]->isEmpty());
    }

    /** A problem in any row fails the request, naming the row. */
    public function testAnUnresolvableRowFailsTheWholeSet(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Question 999 is not part of this survey.');
        $this->resolve([
            ['source' => 'surveyData', 'field' => 'id', 'numberMin' => 1],
            ['source' => 'question', 'qid' => 999, 'text' => 'x'],
        ]);
    }

    public function testAParticipantFilterOnASurveyWithoutParticipantsFails(): void
    {
        $resolver = new ResponseFilterResolver(
            QuestionColumnMap::fromQuestionFieldMap([]),
            new ParticipantResolver([])
        );

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('no participant table');
        $resolver->resolve(ResponseFilterSet::fromRequestValue([
            ['source' => 'participant', 'attribute' => 'email', 'value' => 'x'],
        ]));
    }

    /** The resolved shape is what the criteria builder consumes. */
    public function testResolvesToConditionsTheBuilderUnderstands(): void
    {
        $condition = $this->resolve([
            ['source' => 'question', 'qid' => 10, 'text' => 'refund'],
        ])[0]->getConditions()[0];

        $this->assertSame(ResolvedCondition::OPERATOR_CONTAIN, $condition->getOperator());
        $this->assertSame('refund', $condition->getValue());
    }
}
