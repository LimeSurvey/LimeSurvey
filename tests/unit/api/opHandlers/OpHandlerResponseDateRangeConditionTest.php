<?php

namespace ls\tests\unit\api\opHandlers;

use LimeSurvey\Libraries\Api\Command\V1\SurveyResponses\conditions\DateRangeConditionHandler;
use ls\tests\TestCondition;


class OpHandlerResponseDateRangeConditionTest extends TestCondition
{
    public function testCanHandleDateRange(): void
    {
        $handler = new DateRangeConditionHandler();
        $this->assertTrue($handler->canHandle('date-range'));
        $this->assertTrue($handler->canHandle('DATE-RANGE'));
        $this->assertTrue($handler->canHandle('DaTe-RaNgE'));
    }

    public function testCanHandleOther(): void
    {
        $handler = new DateRangeConditionHandler();
        $this->assertFalse($handler->canHandle('range'));
        $this->assertFalse($handler->canHandle('equal'));
        $this->assertFalse($handler->canHandle(''));
    }

    public function testExecuteMinAndMaxBuildsBetweenConditionWithNormalizedTimes(): void
    {
        $handler = new DateRangeConditionHandler();

        $criteria = $handler->execute('created_at', ['2024-07-01', '2024-07-31']);

        $this->assertInstanceOf(\CDbCriteria::class, $criteria);
        [$minParam, $maxParam] = array_keys($criteria->params);
        $this->assertFieldConditions(
            $criteria->condition,
            "[0] BETWEEN $minParam AND $maxParam",
            ['created_at']
        );
        $this->assertSame(
            [
                $minParam => '2024-07-01 00:00:00',
                $maxParam => '2024-07-31 23:59:59',
            ],
            $criteria->params
        );
    }

    public function testExecuteOnlyMinBuildsLowerBoundWithStartOfDay(): void
    {
        $handler = new DateRangeConditionHandler();

        $criteria = $handler->execute('updated_at', ['2023-01-15', '']);

        $this->assertInstanceOf(\CDbCriteria::class, $criteria);
        $minParam = array_key_first($criteria->params);
        $this->assertFieldConditions($criteria->condition, "[0] >= $minParam", ['updated_at']);
        $this->assertSame([$minParam => '2023-01-15 00:00:00'], $criteria->params);
    }

    public function testExecuteOnlyMaxBuildsUpperBoundWithEndOfDay(): void
    {
        $handler = new DateRangeConditionHandler();

        $criteria = $handler->execute('updated_at', ['', '2023-01-31']);

        $this->assertInstanceOf(\CDbCriteria::class, $criteria);
        $maxParam = array_key_first($criteria->params);
        $this->assertFieldConditions($criteria->condition, "[0] <= $maxParam", ['updated_at']);
        $this->assertSame([$maxParam => '2023-01-31 23:59:59'], $criteria->params);
    }

    /**
     * If a date has the wrong format, validateDate() returns false and the handler
     * should fall back to one-sided condition accordingly.
     */
    public function testInvalidMinDateFallsBackToUpperBoundOnly(): void
    {
        $handler = new DateRangeConditionHandler();

        // Bad min date; good max date
        $criteria = $handler->execute('ts', ['07/01/2024', '2024-07-10']);
        $maxParam = array_key_first($criteria->params);
        $this->assertFieldConditions($criteria->condition, "[0] <= $maxParam", ['ts']);
        $this->assertSame([$maxParam => '2024-07-10 23:59:59'], $criteria->params);
    }

    public function testInvalidMaxDateFallsBackToLowerBoundOnly(): void
    {
        $handler = new DateRangeConditionHandler();

        // Good min date; bad max date
        $criteria = $handler->execute('ts', ['2024-07-01', '07-31-2024']);
        $minParam = array_key_first($criteria->params);
        $this->assertFieldConditions($criteria->condition, "[0] >= $minParam", ['ts']);
        $this->assertSame([$minParam => '2024-07-01 00:00:00'], $criteria->params);
    }

    /**
     * Too many values should throw.
     */
    public function testInvalidRangeTooManyElementsThrows(): void
    {
        $handler = new DateRangeConditionHandler();

        $this->expectException(\Throwable::class);
        $this->expectExceptionMessage('Invalid date range sent.');
        $handler->execute('date_field', ['2024-01-01', '2024-01-02', '2024-01-03']);
    }

    /**
     * Both ends missing should throw.
     */
    public function testInvalidRangeBothEmptyThrows(): void
    {
        $handler = new DateRangeConditionHandler();

        $this->expectException(\Throwable::class);
        $this->expectExceptionMessage('Missing min and max array values.');
        $handler->execute('date_field', ['', '']);
    }

    /**
     * Regression: ensure dangerous characters are stripped from keys BEFORE quoting.
     * Input like "created_at`; DROP" should sanitize to `created_atDROP`.
     */
    public function testKeySanitizationRegression(): void
    {
        $handler = new DateRangeConditionHandler();

        $criteria = $handler->execute('created_at`; DROP  table--', ['2024-06-01', '2024-06-02']);

        $this->assertStringNotContainsString(';', $criteria->condition);

        [$minParam, $maxParam] = array_keys($criteria->params);
        $this->assertFieldConditions(
            $criteria->condition,
            "[0] BETWEEN $minParam AND $maxParam",
            ['created_atDROPtable--']
        );
        $this->assertSame(
            [
                $minParam => '2024-06-01 00:00:00',
                $maxParam => '2024-06-02 23:59:59',
            ],
            $criteria->params
        );
    }

    /**
     * Regression: two date-range filters on the same column merged into one
     * criteria must keep all four bounds. Placeholder names used to be derived
     * from the column, so mergeWith()'s array_merge dropped the first pair.
     */
    public function testTwoMergedDateRangesOnSameColumnKeepAllBounds(): void
    {
        $handler = new DateRangeConditionHandler();

        $merged = new \CDbCriteria();
        $merged->mergeWith($handler->execute('created_at', ['2024-01-01', '2024-01-31']));
        $merged->mergeWith($handler->execute('created_at', ['2024-06-01', '2024-06-30']));

        $this->assertCount(4, $merged->params);
        $this->assertSame(
            [
                '2024-01-01 00:00:00',
                '2024-01-31 23:59:59',
                '2024-06-01 00:00:00',
                '2024-06-30 23:59:59',
            ],
            array_values($merged->params)
        );
    }
}
