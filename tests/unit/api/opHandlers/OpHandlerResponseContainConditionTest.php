<?php

namespace ls\tests\unit\api\opHandlers;

use LimeSurvey\Libraries\Api\Command\V1\SurveyResponses\conditions\ContainConditionHandler;
use PHPUnit\Framework\TestCase;


class OpHandlerResponseContainConditionTest extends TestCase
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
        //$this->assertStringContainsString('`status` LIKE :match', $criteria->condition);
        $this->assertTrue(
            strpos($criteria->condition, '`status` LIKE :match') !== false
            || strpos($criteria->condition, '[status] LIKE :match') !== false
            || strpos($criteria->condition, '"status" LIKE :match') !== false
        );
        $this->assertSame([':match' => '%active%'], $criteria->params);
    }

    public function testExecuteArrayKeysBuildsOrConditionAndIndexedParams(): void
    {
        $handler = new ContainConditionHandler();

        $criteria = $handler->execute(['first_name', 'last_name'], 'Name');

        $this->assertInstanceOf(\CDbCriteria::class, $criteria);

        // Expect OR chain with uniquely indexed placeholders
        //$this->assertStringContainsString('`first_name` LIKE :match0', $criteria->condition);
        $this->assertTrue(
            strpos($criteria->condition, '`first_name` LIKE :match0') !== false
            || strpos($criteria->condition, '[first_name] LIKE :match0') !== false
            || strpos($criteria->condition, '"first_name" LIKE :match0') !== false
        );
        $this->assertStringContainsString(' OR ', $criteria->condition);
        $this->assertTrue(
            strpos($criteria->condition, '`last_name` LIKE :match1') !== false
            || strpos($criteria->condition, '[last_name] LIKE :match1') !== false
            || strpos($criteria->condition, '"last_name" LIKE :match1') !== false
        );

        $this->assertSame(
            [
                ':match0' => '%Name%',
                ':match1' => '%Name%',
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
        //$this->assertStringContainsString('`nameDROPTABLEresponses--` LIKE :match', $criteria->condition);
        $this->assertTrue(
            strpos($criteria->condition, '`nameDROPTABLEresponses--` LIKE :match') !== false
            || strpos($criteria->condition, '[nameDROPTABLEresponses--] LIKE :match') !== false
            || strpos($criteria->condition, '"nameDROPTABLEresponses--" LIKE :match') !== false
        );
        $this->assertSame([':match' => '%ok%'], $criteria->params);
    }

    /**
     * Regression: when array keys are provided, confirm unique param placeholders are used
     * (:match0, :match1, ...) and each receives the same %value% payload.
     */
    public function testArrayKeysUniquePlaceholdersRegression(): void
    {
        $handler = new ContainConditionHandler();

        $criteria = $handler->execute(['fieldA', 'fieldB', 'fieldC'], 'shared');

        //$pattern = '/`fieldA` LIKE :match0.*OR.*`fieldB` LIKE :match1.*OR.*`fieldC` LIKE :match2/s';
        $pattern = '/(?:"|`|\[)?fieldA(?:"|`|\])?\s+LIKE\s+:match0.*?\s+OR\s+(?:"|`|\[)?fieldB(?:"|`|\])?\s+LIKE\s+:match1.*?\s+OR\s+(?:"|`|\[)?fieldC(?:"|`|\])?\s+LIKE\s+:match2/s';
        $this->assertTrue(
            (bool)preg_match($pattern, $criteria->condition),
            "Failed asserting OR chain uses unique indexed placeholders: {$criteria->condition}"
        );

        $this->assertSame(
            [
                ':match0' => '%shared%',
                ':match1' => '%shared%',
                ':match2' => '%shared%',
            ],
            $criteria->params
        );
    }
}
