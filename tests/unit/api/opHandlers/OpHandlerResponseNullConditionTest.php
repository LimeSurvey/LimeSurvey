<?php

namespace ls\tests\unit\api\opHandlers;

use LimeSurvey\Libraries\Api\Command\V1\SurveyResponses\conditions\ContainConditionHandler;
use LimeSurvey\Libraries\Api\Command\V1\SurveyResponses\conditions\NullConditionHandler;
use PHPUnit\Framework\TestCase;


class OpHandlerResponseNullConditionTest extends TestCase
{
    public function testExecuteSingleTrueBuildsNotNullCondition(): void
    {
        $handler = new NullConditionHandler();

        $criteria = $handler->execute('deleted_at', 'true');

        $this->assertInstanceOf(\CDbCriteria::class, $criteria);
        $this->assertSame('(deleted_at IS NOT NULL)', $criteria->condition);
        $this->assertSame([], $criteria->params);
    }

    public function testExecuteSingleFalseBuildsNullCondition(): void
    {
        $handler = new NullConditionHandler();

        $criteria = $handler->execute('deleted_at', 'false');

        $this->assertInstanceOf(\CDbCriteria::class, $criteria);
        $this->assertSame('(deleted_at IS NULL)', $criteria->condition);
        $this->assertSame([], $criteria->params);
    }

    public function testExecuteArrayTrueOrFalseBuildsOrCondition(): void
    {
        $handler = new NullConditionHandler();

        $criteria = $handler->execute('archived_at', ['true', 'false']);

        $this->assertInstanceOf(\CDbCriteria::class, $criteria);
        $this->assertSame('(archived_at IS NOT NULL OR archived_at IS NULL)', $criteria->condition);
        $this->assertSame([], $criteria->params);
    }

    public function testExecuteIgnoresInvalidStringsAndEmptyResultsProduceNoCondition(): void
    {
        $handler = new NullConditionHandler();

        $criteria = $handler->execute('some_field', ['TRUE', 'False', 'maybe', '']);

        // None of these equal the literal 'true' or 'false' after string cast
        $this->assertInstanceOf(\CDbCriteria::class, $criteria);
        $this->assertEmpty($criteria->condition, 'Condition should remain null when no valid items are provided.');
        $this->assertSame([], $criteria->params);
    }

    public function testExecuteCastsScalarsAndFiltersProperly(): void
    {
        $handler = new NullConditionHandler();

        // Mixed types: only the literal strings 'true' and 'false' should be used
        $criteria = $handler->execute('x', [true, false, 'true', 'no', 1, 0, 'false']);

        // After (string) cast: '1', '' (for false), 'true', 'no', '1', '0', 'false'
        // Only 'true' and 'false' should be used.
        $this->assertSame('(x IS NOT NULL OR x IS NULL)', $criteria->condition);
        $this->assertSame([], $criteria->params);
    }

    public function testExecuteWrapsNonArrayValue(): void
    {
        $handler = new NullConditionHandler();

        // Non-array should be wrapped into an array internally
        $criteria = $handler->execute('flag', 'false');

        $this->assertSame('(flag IS NULL)', $criteria->condition);
    }
}
