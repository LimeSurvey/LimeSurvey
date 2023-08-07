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
        return Mockery::mock(Question::class)
            ->makePartial();
    }

    private function getMockModelQuestionL10n(): QuestionL10n
    {
        return Mockery::mock(
            QuestionL10n::class
        )->makePartial();
    }

    private function getMockModelCondition(): Condition
    {
        return Mockery::mock(
            Condition::class
        )->makePartial();
    }

    private function getMockProxyExpressionManager(): ProxyExpressionManager
    {
        return Mockery::mock(ProxyExpressionManager::class)
            ->makePartial();
    }
}
