<?php

namespace ls\tests\unit\services\ResponseFilters;

use InvalidArgumentException;
use LimeSurvey\Models\Services\ResponseFilters\QuestionColumnMap;
use PHPUnit\Framework\TestCase;

class QuestionColumnMapTest extends TestCase
{
    /**
     * A field map as getQuestionFieldMap() returns it: one entry per response
     * column, keyed by column name.
     *
     * Question 20 is a list radio with an "Other" option — the answer plus a
     * free-text column. Question 10 is multiple choice with comments, which has
     * no single answer column at all: one column per option, each with its
     * comment, plus the "Other" pair.
     */
    private function fieldMap(): array
    {
        return [
            '111X1X20' => $this->column(['qid' => 20, 'type' => 'L']),
            '111X1X20other' => $this->column(['qid' => 20, 'type' => 'L', 'aid' => 'other']),
            'Q10_S101' => $this->column(['qid' => 10, 'type' => 'P', 'aid' => 'SQ001', 'sqid' => 101]),
            'Q10_S101_Ccomment' => $this->column(['qid' => 10, 'type' => 'P', 'aid' => 'SQ001comment']),
            'Q10_S102' => $this->column(['qid' => 10, 'type' => 'P', 'aid' => 'SQ002', 'sqid' => 102]),
            'Q10_Cother' => $this->column(['qid' => 10, 'type' => 'P', 'aid' => 'other']),
        ];
    }

    private function column(array $values): array
    {
        return $values + ['aid' => null, 'sqid' => null, 'scaleid' => null];
    }

    private function map(): QuestionColumnMap
    {
        return QuestionColumnMap::fromQuestionFieldMap($this->fieldMap());
    }

    public function testGroupsColumnsByQuestion(): void
    {
        $map = $this->map();

        $this->assertTrue($map->has(20));
        $this->assertTrue($map->has(10));
        $this->assertFalse($map->has(999));
        $this->assertCount(2, $map->getColumns(20));
        $this->assertCount(4, $map->getColumns(10));
    }

    public function testReadsTheTypeFromTheColumns(): void
    {
        $this->assertSame('L', $this->map()->getType(20));
        $this->assertSame('P', $this->map()->getType(10));
    }

    /**
     * A qid from another survey (or a stale one) must not resolve to columns.
     */
    public function testAnUnknownQuestionThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Question 999 is not part of this survey.');
        $this->map()->getType(999);
    }

    /** The answer column, not the "Other" text that sits beside it. */
    public function testMainColumnSkipsTheExtras(): void
    {
        $this->assertSame('111X1X20', $this->map()->getMainColumn(20));
    }

    public function testThereIsNoMainColumnWhenEveryColumnIsAnOption(): void
    {
        $this->assertNull($this->map()->getMainColumn(10));
    }

    public function testFindsAnOptionColumnBySubquestionId(): void
    {
        $this->assertSame('Q10_S101', $this->map()->getColumnBySqid(10, 101));
        $this->assertSame('Q10_S102', $this->map()->getColumnBySqid(10, 102));
    }

    /**
     * The comment column repeats its option's aid but carries no sqid, so a
     * filter on the option can never land on the comment text instead.
     */
    public function testCommentColumnsAreNotMistakenForOptions(): void
    {
        $columns = array_column($this->map()->getColumns(10), 'fieldname');
        $this->assertContains('Q10_S101_Ccomment', $columns);

        $this->assertNotSame('Q10_S101_Ccomment', $this->map()->getColumnBySqid(10, 101));
    }

    public function testAnUnknownSubquestionHasNoColumn(): void
    {
        $this->assertNull($this->map()->getColumnBySqid(10, 555));
    }

    /**
     * Dual-scale columns share a subquestion and differ only by scale — and,
     * in their names, by the '#' suffix.
     */
    public function testFindsADualScaleColumnByItsScale(): void
    {
        $map = QuestionColumnMap::fromQuestionFieldMap([
            'Q40_S401#0' => $this->column(['qid' => 40, 'type' => '1', 'sqid' => 401, 'scaleid' => 0]),
            'Q40_S401#1' => $this->column(['qid' => 40, 'type' => '1', 'sqid' => 401, 'scaleid' => 1]),
        ]);

        $this->assertSame('Q40_S401#0', $map->getColumnBySqidAndScale(40, 401, 0));
        $this->assertSame('Q40_S401#1', $map->getColumnBySqidAndScale(40, 401, 1));
        $this->assertNull($map->getColumnBySqidAndScale(40, 401, 2));
        $this->assertNull($map->getColumnBySqidAndScale(40, 999, 0));
    }

    /**
     * createFieldMap() writes scale_id; the responses transformer renames it to
     * scaleid. Both have to work, so the statistics side can reuse this map
     * without an adapter of its own.
     */
    public function testAcceptsEitherSpellingOfTheScaleId(): void
    {
        $map = QuestionColumnMap::fromQuestionFieldMap([
            'Q40_S401#1' => [
                'fieldname' => 'Q40_S401#1',
                'qid' => 40,
                'type' => '1',
                'sqid' => 401,
                'scale_id' => 1,
            ],
        ]);

        $this->assertSame('Q40_S401#1', $map->getColumnBySqidAndScale(40, 401, 1));
    }

    /**
     * Grid cells store only their row's subquestion; the column's is in the
     * name. Matching it as an exact suffix keeps '_S5' off '_S15'.
     */
    public function testFindsAGridCellByRowAndColumn(): void
    {
        $map = QuestionColumnMap::fromQuestionFieldMap([
            'Q50_S501_S5' => $this->column(['qid' => 50, 'type' => ':', 'sqid' => 501]),
            'Q50_S501_S15' => $this->column(['qid' => 50, 'type' => ':', 'sqid' => 501]),
            'Q50_S502_S5' => $this->column(['qid' => 50, 'type' => ':', 'sqid' => 502]),
        ]);

        $this->assertSame('Q50_S501_S5', $map->getGridColumn(50, 501, 5));
        $this->assertSame('Q50_S501_S15', $map->getGridColumn(50, 501, 15));
        $this->assertSame('Q50_S502_S5', $map->getGridColumn(50, 502, 5));
        $this->assertNull($map->getGridColumn(50, 501, 999));
        $this->assertNull($map->getGridColumn(50, 999, 5));
    }

    /** System columns (id, submitdate, ...) carry no qid and are not questions. */
    public function testEntriesWithoutAQuestionAreIgnored(): void
    {
        $map = QuestionColumnMap::fromQuestionFieldMap([
            'id' => ['type' => 'int'],
            'submitdate' => ['qid' => 0, 'type' => ''],
            '111X1X20' => $this->column(['qid' => 20, 'type' => 'L']),
        ]);

        $this->assertTrue($map->has(20));
        $this->assertFalse($map->has(0));
    }

    /** The column name can be taken from the key when the entry omits it. */
    public function testFieldnameFallsBackToTheMapKey(): void
    {
        $map = QuestionColumnMap::fromQuestionFieldMap([
            '111X1X30' => ['qid' => 30, 'type' => 'S'],
        ]);

        $this->assertSame('111X1X30', $map->getMainColumn(30));
    }
}
