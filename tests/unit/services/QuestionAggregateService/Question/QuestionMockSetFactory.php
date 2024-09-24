<?php

namespace ls\tests\unit\services\QuestionAggregateService\Question;

use Question;
use QuestionL10n;
use Survey;
use Condition;
use LSYii_Application;
use Mockery;

use LimeSurvey\Models\Services\Proxy\{
    ProxySettingsUser,
    ProxyQuestion
};

use LimeSurvey\Models\Services\QuestionAggregateService\L10nService;

/**
 * Question Editor Mock Factory
 *
 * Reusable initialisation of mock dependencies for use in QuestionEditor tests.
 */
class QuestionMockSetFactory
{
    /**
     * @param ?QuestionMockSet $init
     */
    public function make(QuestionMockSet $init = null): QuestionMockSet
    {
        $mockSet = new QuestionMockSet;

        $mockSet->modelQuestion = ($init && isset($init->modelQuestion))
            ? $init->modelQuestion
            : $this->getMockModelQuestion();

        $mockSet->modelSurvey = ($init && isset($init->modelSurvey))
            ? $init->modelSurvey
            : $this->getMockModelSurvey();

        $mockSet->modelCondition = ($init && isset($init->modelCondition))
            ? $init->modelCondition
            : $this->getMockModelCondition();

        $mockSet->modelQuestionL10n = ($init && isset($init->modelQuestionL10n))
            ? $init->modelQuestionL10n
            : $this->getMockQuestionL10n();

        $mockSet->l10nService = ($init && isset($init->l10nService))
            ? $init->l10nService
            : $this->getMockL10nService();

        $mockSet->proxySettingsUser = ($init && isset($init->proxySettingsUser))
            ? $init->proxySettingsUser
            : $this->getMockProxySettingsUser();

        $mockSet->proxyQuestion = ($init && isset($init->proxyQuestion))
            ? $init->proxyQuestion
            : $this->getMockProxyQuestion();

        $mockSet->yiiApp = ($init && isset($init->yiiApp))
            ? $init->yiiApp
            : $this->getMockYiiApp();

        return $mockSet;
    }

    private function getMockQuestionL10n(): QuestionL10n
    {
        return Mockery::mock(QuestionL10n::class)
            ->makePartial();
    }

    private function getMockL10nService(): L10nService
    {
        return Mockery::mock(L10nService::class)
            ->makePartial();
    }

    private function getMockModelCondition(): Condition
    {
        return Mockery::mock(Condition::class)
            ->makePartial();
    }

    private function getMockModelQuestion(): Question
    {
        return Mockery::mock(Question::class)
            ->makePartial();
    }

    private function getMockModelSurvey(): Survey
    {
        return Mockery::mock(Survey::class)
            ->makePartial();
    }

    private function getMockProxySettingsUser(): ProxySettingsUser
    {
        return Mockery::mock(ProxySettingsUser::class)
            ->makePartial();
    }

    private function getMockProxyQuestion(): ProxyQuestion
    {
        return Mockery::mock(ProxyQuestion::class)
            ->makePartial();
    }

    private function getMockYiiApp(): LSYii_Application
    {
        return Mockery::mock(LSYii_Application::class)
            ->makePartial();
    }
}
