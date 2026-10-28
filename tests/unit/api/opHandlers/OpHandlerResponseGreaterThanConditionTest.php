<?php

namespace ls\tests\unit\api\opHandlers;

use InvalidArgumentException;
use LimeSurvey\Libraries\Api\Command\V1\SurveyResponses\conditions\GreaterThanConditionHandler;
use ls\tests\TestCondition;

class OpHandlerResponseGreaterThanConditionTest extends TestCondition
{
    public function testCanHandleGreaterThan(): void
    {
        $handler = new GreaterThanConditionHandler();
        $this->assertTrue($handler->canHandle('greaterThan'));
        $this->assertTrue($handler->canHandle('GREATERTHAN'));
        $this->assertFalse($handler->canHandle('range'));
        $this->assertFalse($handler->canHandle('equal'));
        $this->assertFalse($handler->canHandle(''));
    }

    public function testExecuteBuildsStrictComparisonWithoutCast(): void
    {
        $handler = new GreaterThanConditionHandler();

        $criteria = $handler->execute('id', '280');

        $this->assertInstanceOf(\CDbCriteria::class, $criteria);
        $this->assertStringNotContainsString('CAST', $criteria->condition);
        $this->assertFieldConditions($criteria->condition, '[0] > :idGreaterThan', ['id']);
        $this->assertSame([':idGreaterThan' => 280], $criteria->params);
    }

    public function testKeyIsSanitized(): void
    {
        $handler = new GreaterThanConditionHandler();

        $criteria = $handler->execute('id`; DROP TABLE  responses--', 1);

        $this->assertStringNotContainsString(';', $criteria->condition);
        $this->assertFieldConditions(
            $criteria->condition,
            '[0] > :idDROPTABLEresponsesGreaterThan',
            ['idDROPTABLEresponses--']
        );
        $this->assertArrayHasKey(':idDROPTABLEresponsesGreaterThan', $criteria->params);
    }

    public function testNonNumericValueThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        (new GreaterThanConditionHandler())->execute('id', 'abc');
    }

    public function testArrayValueThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        (new GreaterThanConditionHandler())->execute('id', ['1', '2']);
    }

    public function testMultipleKeysThrow(): void
    {
        $this->expectException(InvalidArgumentException::class);
        (new GreaterThanConditionHandler())->execute(['id', 'seed'], '1');
    }
}
