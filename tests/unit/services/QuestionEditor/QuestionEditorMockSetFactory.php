<?php

namespace ls\tests\unit\services\QuestionEditor;

use ls\tests\unit\services\QuestionEditor\Question\QuestionFactory;

use Permission;
use Question;
use QuestionAttribute;
use CDbConnection;
use Mockery;

use LimeSurvey\Models\Services\Proxy\ProxyExpressionManager;

use LimeSurvey\Models\Services\QuestionEditor\{
    QuestionEditorL10n,
    QuestionEditorAttributes,
    QuestionEditorAnswers,
    QuestionEditorSubQuestions
};

/**
 * Question Editor Mock Factory
 *
 * Reusable initialisation of mock dependencies for use in QuestionEditor tests.
 */
class QuestionEditorMockSetFactory
{
    /**
     * @param ?QuestionEditorMockSet $init
     */
    public function make(QuestionEditorMockSet $init = null): QuestionEditorMockSet
    {
        $mockSet = new QuestionEditorMockSet;

        $mockSet->questionEditorQuestion = ($init && isset($init->questionEditorQuestion))
            ? $init->questionEditorQuestion
            : (new QuestionFactory)->make();

        $mockSet->questionEditorL10n = ($init && isset($init->questionEditorL10n))
            ? $init->questionEditorL10n
            : $this->getMockQuestionEditorL10n();

        $mockSet->questionEditorAttributes = ($init && isset($init->questionEditorAttributes))
            ? $init->questionEditorAttributes
            : $this->getMockQuestionEditorAttributes();

        $mockSet->questionEditorAnswers = ($init && isset($init->questionEditorAnswers))
            ? $init->questionEditorAnswers
            : $this->getMockQuestionEditorAnswers();

        $mockSet->questionEditorSubQuestions = ($init && isset($init->questionEditorSubQuestions))
            ? $init->questionEditorSubQuestions
            : $this->getMockQuestionEditorSubQuestions();

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

    private function getMockQuestionEditorL10n(): QuestionEditorL10n
    {
        return Mockery::mock(QuestionEditorL10n::class)
            ->makePartial();
    }

    private function getMockQuestionEditorAttributes(): QuestionEditorAttributes
    {
        return Mockery::mock(
            QuestionEditorAttributes::class,
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

    private function getMockQuestionEditorAnswers(): QuestionEditorAnswers
    {
        return Mockery::mock(QuestionEditorAnswers::class)
            ->makePartial();
    }

    private function getMockQuestionEditorSubQuestions(): QuestionEditorSubQuestions
    {
        return  Mockery::mock(QuestionEditorSubQuestions::class)
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
