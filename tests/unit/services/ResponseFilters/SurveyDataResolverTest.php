<?php

namespace ls\tests\unit\services\ResponseFilters;

use InvalidArgumentException;
use LimeSurvey\Models\Services\ResponseFilters\ResolvedCondition;
use LimeSurvey\Models\Services\ResponseFilters\ResolvedFilter;
use LimeSurvey\Models\Services\ResponseFilters\ResponseFilterSet;
use LimeSurvey\Models\Services\ResponseFilters\SurveyDataResolver;
use PHPUnit\Framework\TestCase;

class SurveyDataResolverTest extends TestCase
{
    private SurveyDataResolver $resolver;

    protected function setUp(): void
    {
        $this->resolver = new SurveyDataResolver();
    }

    /** Resolve a single row, built through the real request-parsing path. */
    private function resolveOne(array $entry): ResolvedFilter
    {
        $set = ResponseFilterSet::fromRequestValue([$entry]);
        return $this->resolver->resolve($set->all()[0]);
    }

    private function singleCondition(array $entry): ResolvedCondition
    {
        $resolved = $this->resolveOne($entry);
        $this->assertCount(1, $resolved->getConditions());
        return $resolved->getConditions()[0];
    }

    public function testResponseIdResolvesToANumberRange(): void
    {
        $condition = $this->singleCondition([
            'source' => 'surveyData',
            'field' => 'id',
            'numberMin' => '5',
            'numberMax' => '10',
        ]);

        $this->assertSame(['id'], $condition->getKeys());
        $this->assertSame(ResolvedCondition::OPERATOR_RANGE, $condition->getOperator());
        $this->assertSame([5, 10], $condition->getValue());
    }

    /**
     * An open-ended range keeps its empty slot: the range handler reads
     * position 0 as min and 1 as max, and treats '' as "no bound".
     */
    public function testAnOpenEndedNumberRangeKeepsItsEmptySlot(): void
    {
        $lowerBound = $this->singleCondition([
            'source' => 'surveyData',
            'field' => 'id',
            'numberMin' => 5,
        ]);
        $this->assertSame([5, ''], $lowerBound->getValue());

        $upperBound = $this->singleCondition([
            'source' => 'surveyData',
            'field' => 'id',
            'numberMax' => 10,
        ]);
        $this->assertSame(['', 10], $upperBound->getValue());
    }

    public function testSeedResolvesToANumberRangeOnItsOwnColumn(): void
    {
        $condition = $this->singleCondition([
            'source' => 'surveyData',
            'field' => 'seed',
            'numberMin' => 1,
            'numberMax' => 2,
        ]);

        $this->assertSame(['seed'], $condition->getKeys());
        $this->assertSame(ResolvedCondition::OPERATOR_RANGE, $condition->getOperator());
    }

    public function testSubmitDateResolvesToADateRange(): void
    {
        $condition = $this->singleCondition([
            'source' => 'surveyData',
            'field' => 'submitdate',
            'dateFrom' => '2024-01-01',
            'dateTo' => '2024-12-31',
        ]);

        $this->assertSame(['submitdate'], $condition->getKeys());
        $this->assertSame(ResolvedCondition::OPERATOR_DATE_RANGE, $condition->getOperator());
        $this->assertSame(['2024-01-01', '2024-12-31'], $condition->getValue());
    }

    public function testLastActionResolvesToADateRangeOnDatestamp(): void
    {
        $condition = $this->singleCondition([
            'source' => 'surveyData',
            'field' => 'datestamp',
            'dateTo' => '2024-12-31',
        ]);

        $this->assertSame(['datestamp'], $condition->getKeys());
        $this->assertSame(['', '2024-12-31'], $condition->getValue());
    }

    /**
     * "Completed" is derived, not stored: a response is complete when it has a
     * submit date, so the condition lands on submitdate.
     */
    public function testCompleteResolvesToSubmitDateIsNotNull(): void
    {
        $condition = $this->singleCondition([
            'source' => 'surveyData',
            'field' => 'completed',
            'included' => 'complete',
        ]);

        $this->assertSame(['submitdate'], $condition->getKeys());
        $this->assertSame(ResolvedCondition::OPERATOR_NULL, $condition->getOperator());
        $this->assertSame('true', $condition->getValue());
    }

    public function testIncompleteResolvesToSubmitDateIsNull(): void
    {
        $condition = $this->singleCondition([
            'source' => 'surveyData',
            'field' => 'completed',
            'included' => 'incomplete',
        ]);

        $this->assertSame('false', $condition->getValue());
    }

    public function testLanguagesResolveToAMultiSelect(): void
    {
        $condition = $this->singleCondition([
            'source' => 'surveyData',
            'field' => 'startlanguage',
            'languages' => ['en', 'de'],
        ]);

        $this->assertSame(['startlanguage'], $condition->getKeys());
        $this->assertSame(ResolvedCondition::OPERATOR_MULTI_SELECT, $condition->getOperator());
        $this->assertSame(['en', 'de'], $condition->getValue());
    }

    public function testTheRowsJoinIsCarriedThrough(): void
    {
        $set = ResponseFilterSet::fromRequestValue([
            ['source' => 'surveyData', 'field' => 'id', 'numberMin' => 1],
            ['source' => 'surveyData', 'field' => 'seed', 'numberMin' => 2, 'join' => 'or'],
        ]);

        $this->assertFalse($this->resolver->resolve($set->all()[0])->isOr());
        $this->assertTrue($this->resolver->resolve($set->all()[1])->isOr());
    }

    /**
     * The modal lets a user pick a field and apply without filling in a value.
     * That row must filter nothing — not match everything, and not blow up.
     *
     * @dataProvider valuelessRows
     */
    public function testAValuelessRowResolvesToNoConditions(array $entry): void
    {
        $resolved = $this->resolveOne($entry);

        $this->assertTrue($resolved->isEmpty());
        $this->assertSame([], $resolved->getConditions());
    }

    public function valuelessRows(): array
    {
        return [
            'no number bounds' => [['source' => 'surveyData', 'field' => 'id']],
            'blank number bounds' => [
                ['source' => 'surveyData', 'field' => 'seed', 'numberMin' => '', 'numberMax' => ''],
            ],
            'no dates' => [['source' => 'surveyData', 'field' => 'submitdate']],
            'included defaults to all' => [['source' => 'surveyData', 'field' => 'completed']],
            'included explicitly all' => [
                ['source' => 'surveyData', 'field' => 'completed', 'included' => 'all'],
            ],
            'no languages' => [['source' => 'surveyData', 'field' => 'startlanguage']],
            'empty language list' => [
                ['source' => 'surveyData', 'field' => 'startlanguage', 'languages' => []],
            ],
        ];
    }

    public function testRejectsAFilterFromAnotherSource(): void
    {
        $set = ResponseFilterSet::fromRequestValue([
            ['source' => 'question', 'qid' => 1, 'answerCodes' => ['Y']],
        ]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('only resolves surveyData filters');
        $this->resolver->resolve($set->all()[0]);
    }
}
