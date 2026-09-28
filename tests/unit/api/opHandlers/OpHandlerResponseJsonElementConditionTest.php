<?php

namespace ls\tests\unit\api\opHandlers;

use InvalidArgumentException;
use LimeSurvey\Libraries\Api\Command\V1\SurveyResponses\conditions\JsonElementConditionHandler;
use ls\tests\TestCondition;

/**
 * Rankings keep the whole answer as one JSON array of item codes in rank
 * order, so filtering one means reading a single element of it.
 */
class OpHandlerResponseJsonElementConditionTest extends TestCondition
{
    public function testCanHandleJsonElement(): void
    {
        $handler = new JsonElementConditionHandler();
        $this->assertTrue($handler->canHandle('json-element'));
        $this->assertTrue($handler->canHandle('JSON-ELEMENT'));
        $this->assertFalse($handler->canHandle('equal'));
        $this->assertFalse($handler->canHandle(''));
    }

    public function testReadsTheGivenPositionAndBindsTheValue(): void
    {
        $handler = new JsonElementConditionHandler();

        $criteria = $handler->execute('Q140', ['position' => 1, 'value' => 'SQ006']);

        $paramName = array_key_first($criteria->params);
        $this->assertSame(['SQ006'], array_values($criteria->params));
        $this->assertStringContainsString('$[1]', $criteria->condition);
        $this->assertStringContainsString($paramName, $criteria->condition);
    }

    /** First place is element zero, and must not be mistaken for "no position". */
    public function testPositionZeroIsHonoured(): void
    {
        $handler = new JsonElementConditionHandler();

        $criteria = $handler->execute('Q140', ['position' => 0, 'value' => 'SQ001']);

        $this->assertStringContainsString('$[0]', $criteria->condition);
    }

    /**
     * The column may hold anything, so the read is guarded — a row whose value
     * is not valid JSON must be skipped rather than fail the whole query.
     */
    public function testTheReadIsGuardedAgainstNonJsonRows(): void
    {
        $handler = new JsonElementConditionHandler();

        $criteria = $handler->execute('Q140', ['position' => 0, 'value' => 'SQ001']);

        $this->assertStringContainsString('JSON_VALID', $criteria->condition);
    }

    /** The item code is bound, never written into the SQL. */
    public function testTheValueIsNeverInlined(): void
    {
        $handler = new JsonElementConditionHandler();

        $criteria = $handler->execute('Q140', ['position' => 0, 'value' => "SQ001' OR '1'='1"]);

        $this->assertStringNotContainsString('OR ', $criteria->condition);
        $this->assertSame(["SQ001' OR '1'='1"], array_values($criteria->params));
    }

    /**
     * Two ranking filters in one request must keep both codes — the same
     * collision that made every handler generate unique placeholder names.
     */
    public function testTwoMergedRankingFiltersKeepBothValues(): void
    {
        $handler = new JsonElementConditionHandler();

        $merged = new \CDbCriteria();
        $merged->mergeWith($handler->execute('Q140', ['position' => 0, 'value' => 'SQ001']));
        $merged->mergeWith($handler->execute('Q140', ['position' => 1, 'value' => 'SQ002']));

        $this->assertCount(2, $merged->params);
        $this->assertSame(['SQ001', 'SQ002'], array_values($merged->params));
    }

    public function testRejectsAValueWithoutAPosition(): void
    {
        $handler = new JsonElementConditionHandler();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('needs a position and a value');
        $handler->execute('Q140', ['value' => 'SQ001']);
    }

    public function testRejectsANegativePosition(): void
    {
        $handler = new JsonElementConditionHandler();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('cannot be negative');
        $handler->execute('Q140', ['position' => -1, 'value' => 'SQ001']);
    }

    public function testRejectsMultipleKeys(): void
    {
        $handler = new JsonElementConditionHandler();

        $this->expectException(InvalidArgumentException::class);
        $handler->execute(['Q140', 'Q141'], ['position' => 0, 'value' => 'SQ001']);
    }
}
