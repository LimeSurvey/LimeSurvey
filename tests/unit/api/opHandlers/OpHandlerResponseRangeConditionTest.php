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

        // Condition should cast and use both Min and Max placeholders
        $this->assertFieldConditions($criteria->condition, 'CAST([0] AS UNSIGNED) BETWEEN :idMin AND :idMax', ['id']);
        $this->assertSame(
            [':idMin' => 10.0, ':idMax' => 25.0],
            $criteria->params
        );
    }

    public function testExecuteWithOnlyMinBuildsLowerBound(): void
    {
        $handler = new RangeConditionHandler();

        $criteria = $handler->execute('score', ['7', '']);

        $this->assertInstanceOf(\CDbCriteria::class, $criteria);
        $this->assertFieldConditions($criteria->condition, 'CAST([0] AS UNSIGNED) >= :scoreMin', ['score']);
        $this->assertSame([':scoreMin' => 7.0], $criteria->params);
    }

    public function testExecuteWithOnlyMaxBuildsUpperBound(): void
    {
        $handler = new RangeConditionHandler();

        $criteria = $handler->execute('score', ['', '42']);

        $this->assertInstanceOf(\CDbCriteria::class, $criteria);
        $this->assertFieldConditions($criteria->condition, 'CAST([0] AS UNSIGNED) <= :scoreMax', ['score']);
        $this->assertSame([':scoreMax' => 42.0], $criteria->params);
    }

    /**
     * Regression: ensure dangerous characters in key are stripped BEFORE quoting,
     * and that param names don't carry quoting/backticks or punctuation.
     *
     * Input like "id`; DROP TABLE" should sanitize to `idDROPTABLE`
     * and param names :idDROPTABLEMin / :idDROPTABLEMax.
     */
    public function testKeySanitizationForParamsAndQuoting(): void
    {
        $handler = new RangeConditionHandler();

        $criteria = $handler->execute('id`; DROP TABLE  responses--', ['1', '2']);

        $this->assertStringNotContainsString(';', $criteria->condition);
        $this->assertFieldConditions(
            $criteria->condition,
            'CAST([0] AS UNSIGNED) BETWEEN :idDROPTABLEresponsesMin AND :idDROPTABLEresponsesMax',
            ['idDROPTABLEresponses--']
        );

        $this->assertArrayHasKey(':idDROPTABLEresponsesMin', $criteria->params);
        $this->assertArrayHasKey(':idDROPTABLEresponsesMax', $criteria->params);
        $this->assertSame(1.0, $criteria->params[':idDROPTABLEresponsesMin']);
        $this->assertSame(2.0, $criteria->params[':idDROPTABLEresponsesMax']);
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
     * Ensure the condition always casts to UNSIGNED (as implemented).
     */
    public function testConditionUsesUnsignedCast(): void
    {
        $handler = new RangeConditionHandler();

        $criteria = $handler->execute('numeric_field', ['5', '15']);
        $this->assertFieldConditions(
            $criteria->condition,
            'CAST([0] AS UNSIGNED) BETWEEN :numeric_fieldMin AND :numeric_fieldMax',
            ['numeric_field']
        );
    }
}
