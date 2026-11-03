<?php

namespace ls\tests\unit\api\opHandlers;

use LimeSurvey\Libraries\Api\Command\V1\SurveyResponses\conditions\EmptyConditionHandler;
use LimeSurvey\Libraries\Api\Command\V1\SurveyResponses\conditions\NotEmptyConditionHandler;
use ls\tests\TestCondition;

/**
 * The two presence handlers. A column nobody filled can hold NULL or '', so
 * both halves of each test matter: either one alone misses responses.
 */
class OpHandlerResponsePresenceConditionTest extends TestCondition
{
    public function testCanHandleNotEmpty(): void
    {
        $handler = new NotEmptyConditionHandler();
        $this->assertTrue($handler->canHandle('not-empty'));
        $this->assertTrue($handler->canHandle('NOT-EMPTY'));
        $this->assertFalse($handler->canHandle('empty'));
        $this->assertFalse($handler->canHandle(''));
    }

    public function testCanHandleEmpty(): void
    {
        $handler = new EmptyConditionHandler();
        $this->assertTrue($handler->canHandle('empty'));
        $this->assertFalse($handler->canHandle('not-empty'));
    }

    public function testNotEmptyTestsBothNullAndBlank(): void
    {
        $handler = new NotEmptyConditionHandler();

        $criteria = $handler->execute('Q50_Cother', null);

        $this->assertFieldConditions(
            $criteria->condition,
            "([0] IS NOT NULL AND [0] <> '')",
            ['Q50_Cother']
        );
        $this->assertSame([], $criteria->params);
    }

    public function testEmptyTestsBothNullAndBlank(): void
    {
        $handler = new EmptyConditionHandler();

        $criteria = $handler->execute('Q150_Cfilecount', null);

        $this->assertFieldConditions(
            $criteria->condition,
            "([0] IS NULL OR [0] = '')",
            ['Q150_Cfilecount']
        );
        $this->assertSame([], $criteria->params);
    }

    /** Answered means any of the question's columns holds something. */
    public function testNotEmptyAcrossSeveralKeysOrs(): void
    {
        $handler = new NotEmptyConditionHandler();

        $criteria = $handler->execute(['first', 'second'], null);

        $this->assertStringContainsString(' OR ', $criteria->condition);
        $this->assertStringNotContainsString(' AND (', $criteria->condition);
    }

    /** Unanswered means every one of them is blank. */
    public function testEmptyAcrossSeveralKeysAnds(): void
    {
        $handler = new EmptyConditionHandler();

        $criteria = $handler->execute(['first', 'second'], null);

        $this->assertStringContainsString(' AND ', $criteria->condition);
    }

    /** Neither handler binds anything, so neither can collide on merge. */
    public function testMergingPresenceConditionsKeepsBoth(): void
    {
        $merged = new \CDbCriteria();
        $merged->mergeWith((new NotEmptyConditionHandler())->execute('a', null));
        $merged->mergeWith((new EmptyConditionHandler())->execute('b', null));

        $this->assertSame([], $merged->params);
        $this->assertStringContainsString('IS NOT NULL', $merged->condition);
        $this->assertStringContainsString('IS NULL', $merged->condition);
    }

    public function testKeysAreSanitized(): void
    {
        $handler = new NotEmptyConditionHandler();

        $criteria = $handler->execute('name; DROP TABLE responses', null);

        $this->assertStringNotContainsString(';', $criteria->condition);
    }

    /** Dual-scale columns keep their '#', as everywhere else. */
    public function testDualScaleColumnsSurvive(): void
    {
        $handler = new NotEmptyConditionHandler();

        $criteria = $handler->execute('Q42_S101#1', null);

        $this->assertStringContainsString('#1', $criteria->condition);
    }
}
