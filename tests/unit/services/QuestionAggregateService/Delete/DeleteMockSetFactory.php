<?php

namespace ls\tests\unit\services\QuestionAggregateService\Delete;

use Question;
use QuestionL10n;
use Condition;
use Mockery;

use LimeSurvey\Models\Services\Proxy\ProxyExpressionManager;

/**
 * Delete Mock Set Factory
 */
class DeleteMockSetFactory
{
    /**
     * @param ?DeleteMockSet $init
     */
    public function make(DeleteMockSet $init = null): DeleteMockSet
    {
        $mockSet = new DeleteMockSet;

        $mockSet->modelQuestion = ($init && isset($init->modelQuestion))
            ? $init->modelQuestion
            : $this->getMockModelQuestion();

        $mockSet->modelQuestionL10n = ($init && isset($init->modelQuestionL10n))
            ? $init->modelQuestionL10n
            : $this->getMockModelQuestionL10n();

        $mockSet->modelCondition = ($init && isset($init->modelCondition))
            ? $init->modelCondition
            : $this->getMockModelCondition();

        $mockSet->proxyExpressionManager = ($init && isset($init->proxyExpressionManager))
            ? $init->proxyExpressionManager
            : $this->getMockProxyExpressionManager();

        return $mockSet;
    }

    private function getMockModelQuestion(): Question
    {
        $modelQuestion = Mockery::mock(Question::class)
            ->makePartial();
        $modelQuestion->shouldReceive('findByAttributes')
            ->andReturn(
                Mockery::mock(Question::class)->makePartial()
            );
        return $modelQuestion;
    }

    private function getMockModelQuestionL10n(): QuestionL10n
    {
        $modelQuestionL10n = Mockery::mock(
            QuestionL10n::class
        )->makePartial();
        $modelQuestionL10n->shouldReceive('deleteAllByAttributes');
        return $modelQuestionL10n;
    }

    private function getMockModelCondition(): Condition
    {
        $modelCondition = Mockery::mock(Condition::class)
            ->makePartial();
        $modelCondition->shouldReceive('findAllByAttributes')
            ->andReturn(
                []
            );
        return $modelCondition;
    }

    private function getMockProxyExpressionManager(): ProxyExpressionManager
    {
        $mock = Mockery::mock(ProxyExpressionManager::class)
            ->makePartial();
        $mock->shouldReceive('revertUpgradeConditionsToRelevance');
        return $mock;
    }
}
