<?php

namespace ls\tests\unit\services\QuestionEditor\Question;

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

use LimeSurvey\Models\Services\QuestionEditor\QuestionEditorL10n;

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

        $mockSet->questionEditorL10n = ($init && isset($init->questionEditorL10n))
            ? $init->questionEditorL10n
            : $this->getMockQuestionEditorL10n();

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

    private function getMockQuestionEditorL10n(): QuestionEditorL10n
    {
        return Mockery::mock(QuestionEditorL10n::class)
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
