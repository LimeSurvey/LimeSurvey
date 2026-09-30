<?php

namespace ls\tests\unit\api\opHandlers;

use LimeSurvey\Libraries\Api\Command\V1\SurveyResponses\conditions\RangeConditionHandler;
use ls\tests\TestCondition;

class OpHandlerResponseRangeConditionTest extends TestCondition
{
    public function testCanHandleRange(): void
    {
        $handler = new RangeConditionHandler();
        $this->assertTrue($handler->canHandle('range'));
        $this->assertTrue($handler->canHandle('RANGE'));
        $this->assertTrue($handler->canHandle('RaNgE'));
    }

    public function testCanHandleOther(): void
    {
        $handler = new RangeConditionHandler();
        $this->assertFalse($handler->canHandle('between'));
        $this->assertFalse($handler->canHandle('equal'));
        $this->assertFalse($handler->canHandle(''));
    }

    public function testExecuteWithMinAndMaxBuildsConditionAndParams(): void
    {
        $handler = new RangeConditionHandler();

        $criteria = $handler->execute('id', ['10', '25']);

        $this->assertInstanceOf(\CDbCriteria::class, $criteria);

        [$minParam, $maxParam] = array_keys($criteria->params);
        // The column is read as a number, and both bounds are placeholders.
        $this->assertFieldConditions($criteria->condition, '[0]', ['id']);
        $this->assertStringContainsString("BETWEEN $minParam AND $maxParam", $criteria->condition);
        $this->assertSame(
            [$minParam => 10.0, $maxParam => 25.0],
            $criteria->params
        );
    }

    public function testExecuteWithOnlyMinBuildsLowerBound(): void
    {
        $handler = new RangeConditionHandler();

        $criteria = $handler->execute('score', ['7', '']);

        $this->assertInstanceOf(\CDbCriteria::class, $criteria);
        $minParam = array_key_first($criteria->params);
        $this->assertFieldConditions($criteria->condition, ">= $minParam", ['score']);
        $this->assertSame([$minParam => 7.0], $criteria->params);
    }

    public function testExecuteWithOnlyMaxBuildsUpperBound(): void
    {
        $handler = new RangeConditionHandler();

        $criteria = $handler->execute('score', ['', '42']);

        $this->assertInstanceOf(\CDbCriteria::class, $criteria);
        $maxParam = array_key_first($criteria->params);
        $this->assertFieldConditions($criteria->condition, "<= $maxParam", ['score']);
        $this->assertSame([$maxParam => 42.0], $criteria->params);
    }

    /**
     * Regression: ensure dangerous characters in key are stripped BEFORE quoting,
     * and that param names don't carry quoting/backticks or punctuation.
     *
     * Input like "id`; DROP TABLE" should sanitize to `idDROPTABLE`.
     */
    public function testKeySanitizationForParamsAndQuoting(): void
    {
        $handler = new RangeConditionHandler();

        $criteria = $handler->execute('id`; DROP TABLE  responses--', ['1', '2']);

        $this->assertStringNotContainsString(';', $criteria->condition);

        [$minParam, $maxParam] = array_keys($criteria->params);
        $this->assertFieldConditions(
            $criteria->condition,
            "BETWEEN $minParam AND $maxParam",
            ['idDROPTABLEresponses--']
        );

        $this->assertSame(1.0, $criteria->params[$minParam]);
        $this->assertSame(2.0, $criteria->params[$maxParam]);
    }

    /**
     * Regression: two range filters on the same column merged into one criteria
     * must keep all four bounds. Placeholder names used to be derived from the
     * column, so mergeWith()'s array_merge silently dropped the first pair.
     */
    public function testTwoMergedRangesOnSameColumnKeepAllBounds(): void
    {
        $handler = new RangeConditionHandler();

        $merged = new \CDbCriteria();
        $merged->mergeWith($handler->execute('id', ['1', '5']));
        $merged->mergeWith($handler->execute('id', ['10', '20']));

        $this->assertCount(4, $merged->params);
        $this->assertSame([1.0, 5.0, 10.0, 20.0], array_values($merged->params));
    }

    /**
     * When both ends are empty / missing, parseRange should throw.
     * We capture any Throwable (to avoid requiring ext-http’s InvalidArgumentException type).
     */
    public function testInvalidRangeBothEmptyThrows(): void
    {
        $handler = new RangeConditionHandler();

        $threw = false;
        try {
            $handler->execute('id', ['', '']);
        } catch (\Throwable $e) {
            $threw = true;
            $this->assertStringContainsString('Missing min and max', $e->getMessage());
        }
        $this->assertTrue($threw, 'Expected an exception when both min and max are empty.');
    }

    /**
     * When more than 2 elements are provided, parseRange should throw.
     */
    public function testInvalidRangeTooManyElementsThrows(): void
    {
        $handler = new RangeConditionHandler();

        $threw = false;
        try {
            $handler->execute('id', ['1', '2', '3']);
        } catch (\Throwable $e) {
            $threw = true;
            $this->assertStringContainsString('Invalid range', $e->getMessage());
        }
        $this->assertTrue($threw, 'Expected an exception when more than two range values are provided.');
    }

    /**
     * An unsigned integer cast truncated 2.5 to 2 and wrapped -3 to
     * 18446744073709551613, so answers were compared against a number nobody
     * asked for. The cast has to be exact and signed.
     */
    public function testConditionCastsToAnExactSignedDecimal(): void
    {
        $handler = new RangeConditionHandler();

        $criteria = $handler->execute('numeric_field', ['5', '15']);

        $this->assertStringContainsString('DECIMAL(30,10)', $criteria->condition);
        $this->assertStringNotContainsString('UNSIGNED', $criteria->condition);
    }

    /**
     * A cell that is not a number has to drop out of the range. MySQL reads
     * such a cell as 0, which otherwise pulls every blank and every piece of
     * junk into any range spanning zero.
     */
    public function testNonNumericCellsAreExcluded(): void
    {
        $handler = new RangeConditionHandler();

        $criteria = $handler->execute('numeric_field', ['-1', '1']);

        $this->assertStringContainsString('CASE WHEN', $criteria->condition);
        $this->assertStringContainsString('REGEXP', $criteria->condition);
    }

    /**
     * Postgres will not read text as a number on its own and fails the whole
     * query on a cell it cannot convert, so the test happens inside CASE and
     * the column is read as text before it is matched.
     */
    public function testPostgresGuardsTheCastInsideCase(): void
    {
        $criteria = $this->handlerFor('pgsql')->execute('numeric_field', ['5', '15']);

        $this->assertStringContainsString('CASE WHEN CAST', $criteria->condition);
        $this->assertStringContainsString('AS TEXT) ~ ', $criteria->condition);
        $this->assertStringContainsString('DECIMAL(30,10)', $criteria->condition);
        $this->assertStringNotContainsString('REGEXP', $criteria->condition);
    }

    /** MSSQL answers with NULL by itself, so it needs no guard of its own. */
    public function testMssqlUsesTryCast(): void
    {
        $criteria = $this->handlerFor('sqlsrv')->execute('numeric_field', ['5', '15']);

        $this->assertStringContainsString('TRY_CAST', $criteria->condition);
        $this->assertStringNotContainsString('CASE WHEN', $criteria->condition);
    }

    /** The per-driver branches are otherwise only reachable on those drivers. */
    private function handlerFor(string $driver): RangeConditionHandler
    {
        return new class ($driver) extends RangeConditionHandler {
            private string $driver;

            public function __construct(string $driver)
            {
                $this->driver = $driver;
            }

            protected function driverName(): string
            {
                return $this->driver;
            }
        };
    }
}
