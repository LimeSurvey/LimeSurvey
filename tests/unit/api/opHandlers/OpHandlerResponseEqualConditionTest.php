<?php

namespace api\opHandlers;

use LimeSurvey\Libraries\Api\Command\V1\SurveyResponses\conditions\EqualConditionHandler;
use ls\tests\TestCondition;

class OpHandlerResponseEqualConditionTest extends TestCondition
{
    public function testCanHandleEqual(): void
    {
        $handler = new EqualConditionHandler();
        $this->assertTrue($handler->canHandle('equal'));
        $this->assertTrue($handler->canHandle('EQUAL'));
        $this->assertTrue($handler->canHandle('EqUaL'));
    }

    public function testCanHandleOther(): void
    {
        $handler = new EqualConditionHandler();
        $this->assertFalse($handler->canHandle('not_equal'));
        $this->assertFalse($handler->canHandle('contains'));
        $this->assertFalse($handler->canHandle(''));
    }

    public function testExecuteSingleKeyBuildsConditionAndParams(): void
    {
        $handler = new EqualConditionHandler();

        $criteria = $handler->execute('status', 'active');
        $paramName = array_key_first($criteria->params);
        $this->assertFieldConditions($criteria->condition, "[0] = $paramName", ['status']);
        $this->assertSame('active', $criteria->params[$paramName]);
    }

    public function testExecuteArrayKeysBuildsOrConditionAndSharedParam(): void
    {
        $handler = new EqualConditionHandler();
        $criteria = $handler->execute(['first_name', 'last_name'], 'Name');

        [$firstParam, $secondParam] = array_keys($criteria->params);
        $this->assertFieldConditions(
            $criteria->condition,
            "[0] = $firstParam OR [1] = $secondParam",
            ['first_name', 'last_name']
        );
        $this->assertSame([$firstParam => 'Name', $secondParam => 'Name'], $criteria->params);
    }

    /**
     * Regression: ensure dangerous characters are stripped from keys and not passed through to quoting.
     * Input key like "name; DROP TABLE" should become `nameDROPTABLE`.
     */
    public function testKeySanitizationRegression(): void
    {
        $handler = new EqualConditionHandler();

        $criteria = $handler->execute('name; DROP TABLE responses--', 'ok');

        $this->assertStringNotContainsString(';', $criteria->condition);

        // Expect the sanitized, quoted column name
        $paramName = array_key_first($criteria->params);
        $this->assertFieldConditions(
            $criteria->condition,
            "[0] = $paramName",
            ['nameDROPTABLEresponses--']
        );
        $this->assertSame([$paramName => 'ok'], $criteria->params);
    }

    /**
     * Regression: array keys produce one placeholder per column, every one
     * bound to the same value, OR'd together.
     */
    public function testArrayKeysBindSameValueToEveryColumn(): void
    {
        $handler = new EqualConditionHandler();

        $criteria = $handler->execute(['fieldA', 'filedB', 'fieldC'], 'sharedValue');

        $paramNames = array_keys($criteria->params);
        $this->assertCount(3, $paramNames);
        $this->assertStringContainsString('OR', $criteria->condition);
        foreach ($paramNames as $paramName) {
            $this->assertStringContainsString("= $paramName", $criteria->condition);
        }

        $this->assertSame(
            array_fill_keys($paramNames, 'sharedValue'),
            $criteria->params
        );
    }

    /**
     * Regression: dual-scale questions store one column per scale, separated by
     * '#'. Stripping it turned Q42_S101#0 into Q42_S1010 — a column that does
     * not exist — so the query failed and the filter was reported back as an
     * invalid key. Those questions could not be filtered at all.
     */
    public function testDualScaleColumnKeepsItsScaleSeparator(): void
    {
        $handler = new EqualConditionHandler();

        $criteria = $handler->execute('Q42_S101#1', 'A2');

        $paramName = array_key_first($criteria->params);
        $this->assertFieldConditions($criteria->condition, "[0] = $paramName", ['Q42_S101#1']);
        $this->assertSame([$paramName => 'A2'], $criteria->params);
    }

    /** The two scales of one row must stay distinct columns. */
    public function testTheTwoScalesOfARowResolveToDifferentColumns(): void
    {
        $handler = new EqualConditionHandler();

        $first = $handler->execute('Q42_S101#0', 'A1');
        $second = $handler->execute('Q42_S101#1', 'A2');

        $this->assertNotSame($first->condition, $second->condition);
    }

    /**
     * Regression: two filters on the same column merged into one criteria must
     * keep both bound values. Placeholder names used to be derived from the
     * column, so mergeWith()'s array_merge silently dropped the first value.
     */
    public function testTwoMergedFiltersOnSameColumnKeepBothValues(): void
    {
        $handler = new EqualConditionHandler();

        $merged = new \CDbCriteria();
        $merged->mergeWith($handler->execute('status', 'active'));
        $merged->mergeWith($handler->execute('status', 'closed'));

        $this->assertCount(2, $merged->params);
        $this->assertContains('active', $merged->params);
        $this->assertContains('closed', $merged->params);
    }
}
