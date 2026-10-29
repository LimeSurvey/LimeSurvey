<?php

namespace ls\tests\unit\api\opHandlers;

use LimeSurvey\Libraries\Api\Command\V1\SurveyResponses\conditions\MultiSelectConditionHandler;
use ls\tests\TestCondition;

class OpHandlerResponseMultiSelectConditionTest extends TestCondition
{
    public function testCanHandleRecognizesOperationCaseInsensitive(): void
    {
        $handler = new MultiSelectConditionHandler();

        $this->assertTrue($handler->canHandle('multi-select'));
        $this->assertTrue($handler->canHandle('MULTI-SELECT'));
        $this->assertTrue($handler->canHandle('Multi-Select'));
    }

    public function testCanHandleReturnsFalseForOtherOperations(): void
    {
        $handler = new MultiSelectConditionHandler();

        $this->assertFalse($handler->canHandle('equal'));
        $this->assertFalse($handler->canHandle('contains'));
        $this->assertFalse($handler->canHandle(''));
    }

    public function testExecuteWithSingleValueBuildsConditionAndParams(): void
    {
        $handler = new MultiSelectConditionHandler();

        $criteria = $handler->execute('status', 'active');

        $paramName = array_key_first($criteria->params);
        // Condition
        $this->assertFieldConditions($criteria->condition, "[0] IN ($paramName)", ['status']);
        // Params
        $this->assertSame([$paramName => 'active'], $criteria->params);
    }

    public function testExecuteWithMultipleValuesBuildsOrChain(): void
    {
        $handler = new MultiSelectConditionHandler();

        $criteria = $handler->execute('category', ['A', 'B', 'C']);
        $paramNames = array_keys($criteria->params);
        $this->assertFieldConditions(
            $criteria->condition,
            '[0] IN (' . implode(', ', $paramNames) . ')',
            ['category']
        );
        $this->assertSame(
            array_combine($paramNames, ['A', 'B', 'C']),
            $criteria->params
        );
    }

    public function testExecuteSanitizesKeyAndQuotesColumn(): void
    {
        $handler = new MultiSelectConditionHandler();

        $criteria = $handler->execute('sta`tus; DROP TABLE users--', 'ok');

        $paramName = array_key_first($criteria->params);
        $this->assertFieldConditions(
            $criteria->condition,
            "[0] IN ($paramName)",
            ['statusDROPTABLEusers--']
        );
        $this->assertSame([$paramName => 'ok'], $criteria->params);
    }

    /**
     * Regression: two multi-select filters merged into one criteria must keep
     * both bound values. Placeholder names used to be index-based (:value0),
     * so mergeWith()'s array_merge silently dropped the first value.
     */
    public function testTwoMergedFiltersKeepBothValues(): void
    {
        $handler = new MultiSelectConditionHandler();

        $merged = new \CDbCriteria();
        $merged->mergeWith($handler->execute('colour', ['red']));
        $merged->mergeWith($handler->execute('size', ['large']));

        $this->assertCount(2, $merged->params);
        $this->assertContains('red', $merged->params);
        $this->assertContains('large', $merged->params);
    }

    public function testExecuteWithEmptyArrayProducesNoCondition(): void
    {
        $handler = new MultiSelectConditionHandler();

        $criteria = $handler->execute('status', []);

        $this->assertSame('', $criteria->condition);
        $this->assertSame([], $criteria->params);
    }
}
