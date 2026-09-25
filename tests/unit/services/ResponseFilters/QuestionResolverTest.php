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
            'Q90_S901' => $this->column(['qid' => 90, 'type' => 'Q', 'aid' => 'SQ001', 'sqid' => 901]),
            'Q90_S902' => $this->column(['qid' => 90, 'type' => 'Q', 'aid' => 'SQ002', 'sqid' => 902]),
            'Q95_S951' => $this->column(['qid' => 95, 'type' => 'K', 'aid' => 'SQ001', 'sqid' => 951]),
            // Array: one column per row, holding the answer picked on the scale.
            'Q100_S1001' => $this->column(['qid' => 100, 'type' => 'F', 'sqid' => 1001]),
            'Q100_S1002' => $this->column(['qid' => 100, 'type' => 'F', 'sqid' => 1002]),
            // Dual scale: the same row answered twice, split by scale id.
            'Q110_S1101#0' => $this->column(['qid' => 110, 'type' => '1', 'sqid' => 1101, 'scaleid' => 0]),
            'Q110_S1101#1' => $this->column(['qid' => 110, 'type' => '1', 'sqid' => 1101, 'scaleid' => 1]),
            // Array (Numbers): a column per row/column pair, holding a number.
            // Column ids 5 and 15 are deliberate: '_S5' must not match '_S15'.
            'Q120_S1201_S5' => $this->column(['qid' => 120, 'type' => ':', 'sqid' => 1201]),
            'Q120_S1201_S15' => $this->column(['qid' => 120, 'type' => ':', 'sqid' => 1201]),
            'Q120_S1202_S5' => $this->column(['qid' => 120, 'type' => ':', 'sqid' => 1202]),
            // Array (Texts): same shape, holding text.
            'Q130_S1301_S1351' => $this->column(['qid' => 130, 'type' => ';', 'sqid' => 1301]),
            // Still unresolved kinds, and a display-only type.
            'Q140_S1401' => $this->column(['qid' => 140, 'type' => 'R', 'sqid' => 1401]),
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

    /**
     * Multiple short text: the value lands on the chosen box's column, not on
     * the question as a whole.
     */
    public function testASubTextQuestionResolvesToItsChosenBox(): void
    {
        $condition = $this->singleCondition([
            'qid' => 90,
            'subquestion' => 902,
            'text' => 'delivery',
        ]);

        $this->assertSame(['Q90_S902'], $condition->getKeys());
        $this->assertSame(ResolvedCondition::OPERATOR_CONTAIN, $condition->getOperator());
        $this->assertSame('delivery', $condition->getValue());
    }

    public function testASubNumberQuestionResolvesToARangeOnItsChosenBox(): void
    {
        $condition = $this->singleCondition([
            'qid' => 95,
            'subquestion' => 951,
            'numberMin' => 3,
        ]);

        $this->assertSame(['Q95_S951'], $condition->getKeys());
        $this->assertSame(ResolvedCondition::OPERATOR_RANGE, $condition->getOperator());
        $this->assertSame([3, ''], $condition->getValue());
    }

    /**
     * A value with nowhere to apply it is a bad request, not a no-op: dropping
     * it would return the rows the user was trying to exclude.
     */
    public function testAValueWithoutASubquestionThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Question 90 needs a subquestion to filter on.');
        $this->resolveOne(['qid' => 90, 'text' => 'delivery']);
    }

    public function testASubquestionFromAnotherQuestionThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Question 90 has no subquestion 951.');
        $this->resolveOne(['qid' => 90, 'subquestion' => 951, 'text' => 'delivery']);
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
        $this->expectExceptionMessage('No resolver for question kind: ranking.');
        $this->resolveOne(['qid' => 140, 'row' => 1, 'column' => '1401']);
    }

    /** One row of an array holds the answer picked on the scale. */
    public function testAnArrayQuestionResolvesToTheRowColumn(): void
    {
        $condition = $this->singleCondition([
            'qid' => 100,
            'row' => 1002,
            'column' => 'A2',
        ]);

        $this->assertSame(['Q100_S1002'], $condition->getKeys());
        $this->assertSame(ResolvedCondition::OPERATOR_EQUAL, $condition->getOperator());
        $this->assertSame('A2', $condition->getValue());
    }

    /**
     * Answer codes are not numbers, so they must survive the trip intact —
     * casting them to int would turn every code into 0.
     */
    public function testAnswerCodesAreNotTreatedAsNumbers(): void
    {
        $condition = $this->singleCondition(['qid' => 100, 'row' => 1001, 'column' => 'AAA']);

        $this->assertSame('AAA', $condition->getValue());
    }

    /** Each scale of a dual-scale row is its own column, split by '#'. */
    public function testADualScaleRowResolvesToTheScaleTheUserPicked(): void
    {
        $first = $this->singleCondition(['qid' => 110, 'row' => 1101, 'column' => 'A1']);
        $this->assertSame(['Q110_S1101#0'], $first->getKeys());

        $second = $this->singleCondition(['qid' => 110, 'row' => 1101, 'column2' => 'B1']);
        $this->assertSame(['Q110_S1101#1'], $second->getKeys());
    }

    /**
     * Answering on both scales means both must match, so the two conditions
     * AND — unlike a set of options picked from one list.
     */
    public function testBothDualScalesGiveTwoConditionsThatAnd(): void
    {
        $resolved = $this->resolveOne([
            'qid' => 110,
            'row' => 1101,
            'column' => 'A1',
            'column2' => 'B1',
        ]);

        $conditions = $resolved->getConditions();
        $this->assertCount(2, $conditions);
        $this->assertFalse($resolved->isInnerOr());

        $this->assertSame(['Q110_S1101#0'], $conditions[0]->getKeys());
        $this->assertSame('A1', $conditions[0]->getValue());
        $this->assertSame(['Q110_S1101#1'], $conditions[1]->getKeys());
        $this->assertSame('B1', $conditions[1]->getValue());
    }

    /** Array (Numbers): the cell holds a number, so it is a range. */
    public function testAnArrayNumbersCellResolvesToARange(): void
    {
        $condition = $this->singleCondition([
            'qid' => 120,
            'row' => 1201,
            'column' => '15',
            'numberMin' => 2,
            'numberMax' => 8,
        ]);

        $this->assertSame(['Q120_S1201_S15'], $condition->getKeys());
        $this->assertSame(ResolvedCondition::OPERATOR_RANGE, $condition->getOperator());
        $this->assertSame([2, 8], $condition->getValue());
    }

    /**
     * The column id is matched as an exact name suffix, so column 5 can never
     * resolve to column 15's cell.
     */
    public function testAGridColumnIsNotMatchedByAPrefixOfAnother(): void
    {
        $condition = $this->singleCondition([
            'qid' => 120,
            'row' => 1201,
            'column' => '5',
            'numberMin' => 1,
        ]);

        $this->assertSame(['Q120_S1201_S5'], $condition->getKeys());
    }

    public function testAGridCellIsPickedByRowAsWellAsColumn(): void
    {
        $condition = $this->singleCondition([
            'qid' => 120,
            'row' => 1202,
            'column' => '5',
            'numberMin' => 1,
        ]);

        $this->assertSame(['Q120_S1202_S5'], $condition->getKeys());
    }

    /** Array (Texts): the same cell shape, filtered by what was typed in it. */
    public function testAnArrayTextsCellResolvesToAContains(): void
    {
        $condition = $this->singleCondition([
            'qid' => 130,
            'row' => 1301,
            'column' => '1351',
            'text' => 'late',
        ]);

        $this->assertSame(['Q130_S1301_S1351'], $condition->getKeys());
        $this->assertSame(ResolvedCondition::OPERATOR_CONTAIN, $condition->getOperator());
        $this->assertSame('late', $condition->getValue());
    }

    public function testAnArrayValueWithoutARowThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Question 100 needs a row to filter on.');
        $this->resolveOne(['qid' => 100, 'column' => 'A1']);
    }

    public function testARowThatIsNotPartOfTheArrayThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Question 100 has no row 9999.');
        $this->resolveOne(['qid' => 100, 'row' => 9999, 'column' => 'A1']);
    }

    public function testADualScaleRowThatIsNotPartOfTheQuestionThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Question 110 has no row 9999 on scale 0.');
        $this->resolveOne(['qid' => 110, 'row' => 9999, 'column' => 'A1']);
    }

    public function testAGridValueWithoutAColumnThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Question 120 needs a column to filter on.');
        $this->resolveOne(['qid' => 120, 'row' => 1201, 'numberMin' => 1]);
    }

    public function testAGridCellThatDoesNotExistThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Question 120 has no cell 1201/999.');
        $this->resolveOne(['qid' => 120, 'row' => 1201, 'column' => '999', 'numberMin' => 1]);
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
            // The box alone asks for nothing, so there is nothing to reject.
            'no sub text' => [['qid' => 90, 'subquestion' => 901]],
            'no sub number bounds' => [['qid' => 95, 'subquestion' => 951]],
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
