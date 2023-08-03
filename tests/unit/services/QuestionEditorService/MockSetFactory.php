<?php

namespace ls\tests\unit\services\QuestionEditorService;

use ls\tests\unit\services\QuestionEditorService\Question\QuestionFactory;

use Permission;
use Question;
use QuestionAttribute;
use CDbConnection;
use Mockery;

use LimeSurvey\Models\Services\Proxy\ProxyExpressionManager;

use LimeSurvey\Models\Services\QuestionEditorService\{
    L10nService,
    AttributesService,
    AnswersService,
    SubQuestionsService
};

/**
 * Question Editor Mock Factory
 *
 * Reusable initialisation of mock dependencies for use in QuestionEditor tests.
 */
class MockSetFactory
{
    /**
     * @param ?QuestionEditorMockSet $init
     */
    public function make(MockSet $init = null): MockSet
    {
        $mockSet = new MockSet;

        $mockSet->questionService = ($init && isset($init->questionService))
            ? $init->questionService
            : (new QuestionFactory)->make();

        $mockSet->l10nService = ($init && isset($init->l10nService))
            ? $init->l10nService
            : $this->getMockL10nService();

        $mockSet->attributesService = ($init && isset($init->attributesService))
            ? $init->attributesService
            : $this->getMockAttributesService();

        $mockSet->answersService = ($init && isset($init->answersService))
            ? $init->answersService
            : $this->getMockAnswersService();

        $mockSet->subQuestionsService = ($init && isset($init->subQuestionsService))
            ? $init->subQuestionsService
            : $this->getMockSubQuestionsService();

        $mockSet->modelQuestion = ($init && isset($init->modelQuestion))
            ? $init->modelQuestion
            : $this->getMockModelQuestion();

        $mockSet->modelPermission = ($init && isset($init->modelPermission))
            ? $init->modelPermission
            : $this->getMockModelPermission();

        $mockSet->proxyExpressionManager = ($init && isset($init->proxyExpressionManager))
            ? $init->proxyExpressionManager
            : $this->getMockProxyExpressionManager();

        $mockSet->yiiDb = ($init && isset($init->yiiDb))
            ? $init->yiiDb
            : $this->getMockYiiDb();

        return $mockSet;
    }

    private function getMockL10nService(): L10nService
    {
        return Mockery::mock(L10nService::class)
            ->makePartial();
    }

    private function getMockAttributesService(): AttributesService
    {
        return Mockery::mock(
            AttributesService::class,
            [
                $this->getMockModelQuestionAttribute()
            ]
        )->makePartial();
    }

    private function getMockModelQuestionAttribute(): QuestionAttribute
    {
        return Mockery::mock(
            QuestionAttribute::class
        )->makePartial();
    }

    private function getMockAnswersService(): AnswersService
    {
        return Mockery::mock(AnswersService::class)
            ->makePartial();
    }

    private function getMockSubQuestionsService(): SubQuestionsService
    {
        return  Mockery::mock(SubQuestionsService::class)
            ->makePartial();
    }

    private function getMockModelPermission(): Permission
    {
        return Mockery::mock(Permission::class)
            ->makePartial();
    }

    private function getMockModelQuestion(): Question
    {
        return Mockery::mock(Question::class)
            ->makePartial();
    }

    private function getMockProxyExpressionManager(): ProxyExpressionManager
    {
        return Mockery::mock(ProxyExpressionManager::class)
            ->makePartial();
    }

    private function getMockYiiDb(): CDbConnection
    {
        return Mockery::mock(CDbConnection::class)
            ->makePartial();
    }
}
