<?php

namespace ls\tests\unit\api\opHandlers;

use LimeSurvey\Libraries\Api\Command\V1\SurveyResponses\conditions\ContainConditionHandler;
use ls\tests\TestCondition;

class OpHandlerResponseContainConditionTest extends TestCondition
{
    public function testCanHandleContain(): void
    {
        $handler = new ContainConditionHandler();
        // Expect case-insensitive handling (mirrors EqualConditionHandler test style)
        $this->assertTrue($handler->canHandle('contain'));
        $this->assertTrue($handler->canHandle('CONTAIN'));
        $this->assertTrue($handler->canHandle('CoNtAiN'));
    }

    public function testCanHandleOther(): void
    {
        $handler = new ContainConditionHandler();
        $this->assertFalse($handler->canHandle('not_contain'));
        $this->assertFalse($handler->canHandle('equal'));
        $this->assertFalse($handler->canHandle(''));
    }

    public function testExecuteSingleKeyBuildsConditionAndParams(): void
    {
        $handler = new ContainConditionHandler();

        // Includes whitespace to verify value trimming inside execute()
        $criteria = $handler->execute('status', '  active  ');


        $this->assertInstanceOf(\CDbCriteria::class, $criteria);
        $paramName = array_key_first($criteria->params);
        $this->assertFieldConditions($criteria->condition, "[0] LIKE $paramName", ['status']);
        $this->assertSame([$paramName => '%active%'], $criteria->params);
    }

    public function testExecuteArrayKeysBuildsOrConditionAndIndexedParams(): void
    {
        $handler = new ContainConditionHandler();

        $criteria = $handler->execute(['first_name', 'last_name'], 'Name');

        $this->assertInstanceOf(\CDbCriteria::class, $criteria);

        // Expect OR chain with unique placeholders
        $paramNames = array_keys($criteria->params);
        $this->assertCount(2, $paramNames);
        $this->assertNotSame($paramNames[0], $paramNames[1]);
        $this->assertFieldConditions(
            $criteria->condition,
            "[0] LIKE {$paramNames[0]} OR [1] LIKE {$paramNames[1]}",
            ['first_name', 'last_name']
        );

        $this->assertSame(
            [
                $paramNames[0] => '%Name%',
                $paramNames[1] => '%Name%',
            ],
            $criteria->params
        );
    }

    /**
     * Regression: ensure dangerous characters are stripped from keys and not passed through to quoting.
     * Input key like "name; DROP TABLE" should become `nameDROPTABLE`.
     */
    public function testKeySanitizationRegression(): void
    {
        $handler = new ContainConditionHandler();

        $criteria = $handler->execute('name; DROP TABLE responses--', 'ok');

        $this->assertStringNotContainsString(';', $criteria->condition, 'Semicolon should be removed from condition.');

        // Expect the sanitized, quoted column name and LIKE placeholder
        $paramName = array_key_first($criteria->params);
        $this->assertFieldConditions(
            $criteria->condition,
            "[0] LIKE $paramName",
            ['nameDROPTABLEresponses--']
        );
        $this->assertSame([$paramName => '%ok%'], $criteria->params);
    }

    /**
     * Regression: when array keys are provided, confirm unique param placeholders are used
     * and each receives the same %value% payload.
     */
    public function testArrayKeysUniquePlaceholdersRegression(): void
    {
        $handler = new ContainConditionHandler();

        $criteria = $handler->execute(['fieldA', 'fieldB', 'fieldC'], 'shared');
        $paramNames = array_keys($criteria->params);
        $this->assertCount(3, $paramNames);
        $this->assertSame($paramNames, array_unique($paramNames));
        $pattern = '/(?:"|`|\[)?fieldA(?:"|`|\])?\s+LIKE\s+' . preg_quote($paramNames[0], '/')
            . '.*?\s+OR\s+(?:"|`|\[)?fieldB(?:"|`|\])?\s+LIKE\s+' . preg_quote($paramNames[1], '/')
            . '.*?\s+OR\s+(?:"|`|\[)?fieldC(?:"|`|\])?\s+LIKE\s+' . preg_quote($paramNames[2], '/') . '/s';
        $this->assertTrue(
            (bool)preg_match($pattern, $criteria->condition),
            "Failed asserting OR chain uses unique placeholders: {$criteria->condition}"
        );

        $this->assertSame(
            [
                $paramNames[0] => '%shared%',
                $paramNames[1] => '%shared%',
                $paramNames[2] => '%shared%',
            ],
            $criteria->params
        );
    }
}
