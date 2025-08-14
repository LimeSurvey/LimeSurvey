<?php

namespace ls\tests\unit\api\opHandlers;

use LimeSurvey\Libraries\Api\Command\V1\SurveyResponses\conditions\MultiSelectConditionHandler;
use PHPUnit\Framework\TestCase;

class OpHandlerResponseMultiSelectConditionTest extends TestCase
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

        // Condition
        $this->assertSame('(`status` = :value0)', $criteria->condition);
        // Params
        $this->assertSame([':value0' => 'active'], $criteria->params);
    }

    public function testExecuteWithMultipleValuesBuildsOrChain(): void
    {
        $handler = new MultiSelectConditionHandler();

        $criteria = $handler->execute('category', ['A', 'B', 'C']);

        $this->assertSame(
            '(`category` = :value0 OR `category` = :value1 OR `category` = :value2)',
            $criteria->condition
        );
        $this->assertSame(
            [':value0' => 'A', ':value1' => 'B', ':value2' => 'C'],
            $criteria->params
        );
    }

    public function testExecuteSanitizesKeyAndQuotesColumn(): void
    {
        $handler = new MultiSelectConditionHandler();

        $criteria = $handler->execute('sta`tus; DROP TABLE users--', 'ok');

        $this->assertSame('(`statusDROPTABLEusers--` = :value0)', $criteria->condition);
        $this->assertSame([':value0' => 'ok'], $criteria->params);
    }

    public function testExecuteWithEmptyArrayProducesNoCondition(): void
    {
        $handler = new MultiSelectConditionHandler();

        $criteria = $handler->execute('status', []);

        $this->assertSame('', $criteria->condition);
        $this->assertSame([], $criteria->params);
    }
}
