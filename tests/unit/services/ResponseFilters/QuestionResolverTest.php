<?php

namespace ls\tests\unit\services\ResponseFilters;

use InvalidArgumentException;
use LimeSurvey\Models\Services\ResponseFilters\QuestionColumnMap;
use LimeSurvey\Models\Services\ResponseFilters\QuestionResolver;
use LimeSurvey\Models\Services\ResponseFilters\ResolvedCondition;
use LimeSurvey\Models\Services\ResponseFilters\ResolvedFilter;
use LimeSurvey\Models\Services\ResponseFilters\ResponseFilterSet;
use PHPUnit\Framework\TestCase;

class QuestionResolverTest extends TestCase
{
    private QuestionResolver $resolver;

    /**
     * One question of each kind this commit resolves, plus the two shapes an
     * answer question can take: 40 stores the chosen code, 50 stores 'Y' per
     * ticked option.
     */
    protected function setUp(): void
    {
        $this->resolver = new QuestionResolver(QuestionColumnMap::fromQuestionFieldMap([
            '111X1X10' => $this->column(['qid' => 10, 'type' => 'N']),
            '111X1X20' => $this->column(['qid' => 20, 'type' => 'D']),
            '111X1X30' => $this->column(['qid' => 30, 'type' => 'S']),
            '111X1X40' => $this->column(['qid' => 40, 'type' => 'L']),
            '111X1X40other' => $this->column(['qid' => 40, 'type' => 'L', 'aid' => 'other']),
            'Q50_S501' => $this->column(['qid' => 50, 'type' => 'M', 'aid' => 'SQ001', 'sqid' => 501]),
            'Q50_S502' => $this->column(['qid' => 50, 'type' => 'M', 'aid' => 'SQ002', 'sqid' => 502]),
            'Q50_Cother' => $this->column(['qid' => 50, 'type' => 'M', 'aid' => 'other']),
            'Q60_S601' => $this->column(['qid' => 60, 'type' => 'M', 'aid' => 'SQ001', 'sqid' => 601]),
            '111X1X70' => $this->column(['qid' => 70, 'type' => 'F']),
            '111X1X80' => $this->column(['qid' => 80, 'type' => 'X']),
        ]));
    }

    private function column(array $values): array
    {
        return $values + ['aid' => null, 'sqid' => null, 'scaleid' => null];
    }

    private function resolveOne(array $entry): ResolvedFilter
    {
        $set = ResponseFilterSet::fromRequestValue([['source' => 'question'] + $entry]);
        return $this->resolver->resolve($set->all()[0]);
    }

    private function singleCondition(array $entry): ResolvedCondition
    {
        $resolved = $this->resolveOne($entry);
        $this->assertCount(1, $resolved->getConditions());
        return $resolved->getConditions()[0];
    }

    public function testANumericQuestionResolvesToARangeOnItsColumn(): void
    {
        $condition = $this->singleCondition([
            'qid' => 10,
            'numberMin' => 1,
            'numberMax' => 10,
        ]);

        $this->assertSame(['111X1X10'], $condition->getKeys());
        $this->assertSame(ResolvedCondition::OPERATOR_RANGE, $condition->getOperator());
        $this->assertSame([1, 10], $condition->getValue());
    }

    public function testADateQuestionResolvesToADateRange(): void
    {
        $condition = $this->singleCondition([
            'qid' => 20,
            'dateFrom' => '2024-01-01',
        ]);

        $this->assertSame(['111X1X20'], $condition->getKeys());
        $this->assertSame(ResolvedCondition::OPERATOR_DATE_RANGE, $condition->getOperator());
        $this->assertSame(['2024-01-01', ''], $condition->getValue());
    }

    public function testAFreeTextQuestionResolvesToAContains(): void
    {
        $condition = $this->singleCondition(['qid' => 30, 'text' => 'delivery']);

        $this->assertSame(['111X1X30'], $condition->getKeys());
        $this->assertSame(ResolvedCondition::OPERATOR_CONTAIN, $condition->getOperator());
        $this->assertSame('delivery', $condition->getValue());
    }

    /**
     * One column holds whichever code was picked, so several picks are an IN
     * list — and the row's conditions are OR'd, matching "any of these".
     */
    public function testASingleChoiceQuestionResolvesToAnInList(): void
    {
        $resolved = $this->resolveOne(['qid' => 40, 'answerCodes' => ['A1', 'A2']]);
        $condition = $resolved->getConditions()[0];

        $this->assertSame(['111X1X40'], $condition->getKeys());
        $this->assertSame(ResolvedCondition::OPERATOR_MULTI_SELECT, $condition->getOperator());
        $this->assertSame(['A1', 'A2'], $condition->getValue());
        $this->assertTrue($resolved->isInnerOr());
    }

    /**
     * Single choice stores "Other" as the code -oth- in the same column, so it
     * needs no special handling — unlike multiple choice below.
     */
    public function testSingleChoiceOtherIsJustAnotherCode(): void
    {
        $condition = $this->singleCondition(['qid' => 40, 'answerCodes' => ['A1', '-oth-']]);

        $this->assertSame(['111X1X40'], $condition->getKeys());
        $this->assertSame(['A1', '-oth-'], $condition->getValue());
    }

    /**
     * Multiple choice has one column per option. The picks arrive as
     * subquestion ids and become one comparison over several columns, which the
     * equal handler already ORs.
     */
    public function testMultipleChoiceResolvesToTheTickedOptionColumns(): void
    {
        $condition = $this->singleCondition(['qid' => 50, 'answerCodes' => [501, 502]]);

        $this->assertSame(['Q50_S501', 'Q50_S502'], $condition->getKeys());
        $this->assertSame(ResolvedCondition::OPERATOR_EQUAL, $condition->getOperator());
        $this->assertSame('Y', $condition->getValue());
    }

    /**
     * Ticking "Other" on a multiple choice stores free text in its own column
     * and nothing anywhere else, so the tick can only be found by that text
     * being present. It ORs with the ticked options, not ANDs: the user asked
     * for "option 1 or Other".
     */
    public function testMultipleChoiceOtherResolvesToItsFreeTextColumn(): void
    {
        $resolved = $this->resolveOne(['qid' => 50, 'answerCodes' => [501, '-oth-']]);
        $conditions = $resolved->getConditions();

        $this->assertCount(2, $conditions);
        $this->assertTrue($resolved->isInnerOr());

        $this->assertSame(['Q50_S501'], $conditions[0]->getKeys());
        $this->assertSame(ResolvedCondition::OPERATOR_EQUAL, $conditions[0]->getOperator());

        $this->assertSame(['Q50_Cother'], $conditions[1]->getKeys());
        $this->assertSame(ResolvedCondition::OPERATOR_NOT_EMPTY, $conditions[1]->getOperator());
    }

    public function testOtherOnAMultipleChoiceThatHasNoneThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Question 60 has no 'Other' option.");
        $this->resolveOne(['qid' => 60, 'answerCodes' => ['-oth-']]);
    }

    public function testAnOptionThatIsNotPartOfTheQuestionThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Question 50 has no option '999'.");
        $this->resolveOne(['qid' => 50, 'answerCodes' => [999]]);
    }

    public function testAQuestionFromAnotherSurveyThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Question 777 is not part of this survey.');
        $this->resolveOne(['qid' => 777, 'answerCodes' => ['A1']]);
    }

    /** Display text holds no answer, so a filter on it is a bad request. */
    public function testADisplayOnlyQuestionThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Question 80 holds no answers to filter on.');
        $this->resolveOne(['qid' => 80, 'text' => 'anything']);
    }

    /**
     * Kinds still to be built must say so rather than resolve to nothing: a
     * filter that quietly matches every row shows data the user excluded.
     */
    public function testAKindWithoutAResolverThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('No resolver for question kind: arrayScale.');
        $this->resolveOne(['qid' => 70, 'row' => 1, 'column' => 2]);
    }

    /**
     * @dataProvider valuelessRows
     */
    public function testAValuelessRowResolvesToNoConditions(array $entry): void
    {
        $this->assertTrue($this->resolveOne($entry)->isEmpty());
    }

    public function valuelessRows(): array
    {
        return [
            'no number bounds' => [['qid' => 10]],
            'blank number bounds' => [['qid' => 10, 'numberMin' => '', 'numberMax' => '']],
            'no dates' => [['qid' => 20]],
            'no text' => [['qid' => 30]],
            'whitespace only text' => [['qid' => 30, 'text' => '   ']],
            'no answer codes' => [['qid' => 40, 'answerCodes' => []]],
        ];
    }

    /** Parts of one answer AND together; only picked-from-a-list ORs. */
    public function testConditionsInsideANonAnswerRowAnd(): void
    {
        $this->assertFalse($this->resolveOne(['qid' => 30, 'text' => 'x'])->isInnerOr());
        $this->assertFalse($this->resolveOne(['qid' => 10, 'numberMin' => 1])->isInnerOr());
    }

    public function testTheRowsJoinIsCarriedThrough(): void
    {
        $set = ResponseFilterSet::fromRequestValue([
            ['source' => 'question', 'qid' => 30, 'text' => 'a'],
            ['source' => 'question', 'qid' => 30, 'text' => 'b', 'join' => 'or'],
        ]);

        $this->assertFalse($this->resolver->resolve($set->all()[0])->isOr());
        $this->assertTrue($this->resolver->resolve($set->all()[1])->isOr());
    }

    public function testRejectsAFilterFromAnotherSource(): void
    {
        $set = ResponseFilterSet::fromRequestValue([
            ['source' => 'surveyData', 'field' => 'id', 'numberMin' => 1],
        ]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('only resolves question filters');
        $this->resolver->resolve($set->all()[0]);
    }
}
