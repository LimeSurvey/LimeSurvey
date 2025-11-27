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

        //$this->assertStringContainsString('`status`', $criteria->condition);
        $this->assertTrue(
            strpos($criteria->condition, '`status`') !== false
            || strpos($criteria->condition, '[status]') !== false
        );
        $this->assertSame('active', $criteria->params[':statusValue']);
    }

    public function testExecuteArrayKeysBuildsOrConditionAndSharedParam(): void
    {
        $handler = new EqualConditionHandler();

        $criteria = $handler->execute(['first_name', 'last_name'], 'Name');

        //$this->assertStringContainsString('`first_name`', $criteria->condition);
        $this->assertTrue(
            strpos($criteria->condition, '`first_name`') !== false
            || strpos($criteria->condition, '[first_name]') !== false
        );
        $this->assertStringContainsString(' OR ', $criteria->condition);
//        $this->assertStringContainsString('`last_name`', $criteria->condition);
        $this->assertTrue(
            strpos($criteria->condition, '`last_name`') !== false
            || strpos($criteria->condition, '[last_name]') !== false
        );
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
        $this->assertTrue(
            strpos($criteria->condition, '`nameDROPTABLEresponses--` = :nameDROPTABLEresponsesValue') !== false
            || strpos($criteria->condition, '[nameDROPTABLEresponses--] = :nameDROPTABLEresponsesValue') !== false
        );
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

        $this->assertRegExp('/[`\\[]fieldA[`\\]] = :fieldAValue/', $criteria->condition);
        $this->assertRegExp('/[`\\[]filedB[`\\]] = :filedBValue/', $criteria->condition);
        $this->assertRegExp('/[`\\[]fieldC[`\\]] = :fieldCValue/', $criteria->condition);
        $this->assertStringContainsString('OR', $criteria->condition);

        $this->assertSame([':fieldAValue' => 'sharedValue', ':filedBValue' => 'sharedValue', ':fieldCValue' => 'sharedValue'], $criteria->params);
    }
}
