<?php

namespace api\opHandlers;

use LimeSurvey\Libraries\Api\Command\V1\SurveyResponses\conditions\EqualConditionHandler;
use PHPUnit\Framework\TestCase;


class OpHandlerResponseEqualConditionTest extends TestCase
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

        $this->assertStringContainsString('`status`', $criteria->condition);

        $this->assertSame('active', $criteria->params[':statusValue']);
    }

    public function testExecuteArrayKeysBuildsOrConditionAndSharedParam(): void
    {
        $handler = new EqualConditionHandler();

        $criteria = $handler->execute(['first_name', 'last_name'], 'Name');

        $this->assertStringContainsString('`first_name`', $criteria->condition);
        $this->assertStringContainsString(' OR ', $criteria->condition);
        $this->assertStringContainsString('`last_name`', $criteria->condition);

        // Single shared placeholder per the handler’s implementation
        $this->assertSame([':first_nameValue' => 'Name', ':last_nameValue' => 'Name'], $criteria->params);
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
        $this->assertStringContainsString('`nameDROPTABLEresponses--` = :nameDROPTABLEresponsesValue', $criteria->condition);
        $this->assertSame([':nameDROPTABLEresponsesValue' => 'ok'], $criteria->params);
    }

    /**
     * Regression: when array keys are provided, confirm only one param placeholder is used,
     * matching the handler’s current behavior (`:value` used for all OR’d columns).
     */
    public function testArrayKeysSinglePlaceholderRegression(): void
    {
        $handler = new EqualConditionHandler();

        $criteria = $handler->execute(['fieldA', 'filedB', 'fieldC'], 'sharedValue');

        $pattern = '/`fieldA` = :fieldAValue.*OR.*`filedB` = :filedBValue.*OR.*`fieldC` = :fieldCValue/s';
        $this->assertTrue(
            (bool)preg_match($pattern, $criteria->condition),
            "Failed asserting OR chain uses the same placeholder: {$criteria->condition}"
        );

        $this->assertSame([':fieldAValue' => 'sharedValue', ':filedBValue' => 'sharedValue', ':fieldCValue' => 'sharedValue'], $criteria->params);
    }
}
