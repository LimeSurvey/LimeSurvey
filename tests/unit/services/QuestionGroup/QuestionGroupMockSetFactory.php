<?php

namespace ls\tests\unit\services\QuestionGroup;

use LimeSurvey\Models\Services\Proxy\ProxyExpressionManager;
use LimeSurvey\Models\Services\Proxy\ProxyQuestionGroup;
use LSYii_Application;
use Mockery;
use Permission;
use Question;
use QuestionGroup;
use QuestionGroupL10n;
use Survey;

class QuestionGroupMockSetFactory
{
    /**
     * @param QuestionGroupMockSet|null $init
     * @return QuestionGroupMockSet
     */
    public function make(QuestionGroupMockSet $init = null)
    {
        $mockSet = new QuestionGroupMockSet();

        $mockSet->modelPermission = ($init && isset($init->modelPermission))
            ? $init->modelPermission
            : $this->getMockModelPermission();

        $mockSet->survey = ($init && isset($init->survey))
            ? $init->survey
            : $this->getMockSurvey();

        $mockSet->modelSurvey = ($init && isset($init->modelSurvey))
            ? $init->modelSurvey
            : $this->getMockModelSurvey($mockSet->survey);

        $mockSet->modelQuestion = ($init && isset($init->modelQuestion))
            ? $init->modelQuestion
            : $this->getMockModelQuestion();

        $mockSet->questionGroup = ($init && isset($init->questionGroup))
            ? $init->questionGroup
            : $this->getMockQuestionGroup();

        $mockSet->modelQuestionGroup = ($init && isset($init->modelQuestionGroup))
            ? $init->modelQuestionGroup
            : $this->getMockModelQuestionGroup($mockSet->questionGroup);

        $mockSet->questionGroupL10n = ($init && isset($init->questionGroupL10n))
            ? $init->questionGroupL10n
            : $this->getMockQuestionGroupL10n();

        $mockSet->modelQuestionGroupL10n = ($init && isset($init->modelQuestionGroupL10n))
            ? $init->modelQuestionGroupL10n
            : $this->getMockModelQuestionGroupL10n($mockSet->questionGroupL10n);

        $mockSet->proxyExpressionManager = ($init && isset($init->proxyExpressionManager))
            ? $init->proxyExpressionManager
            : $this->getMockProxyExpressionManager();

        $mockSet->proxyQuestionGroup = ($init && isset($init->proxyQuestionGroup))
            ? $init->proxyQuestionGroup
            : $this->getMockProxyQuestionGroup();

        $mockSet->yiiApp = ($init && isset($init->yiiApp))
            ? $init->yiiApp
            : $this->getMockYiiApp();

        return $mockSet;
    }

    public function getMockModelNoSurveyPermission(): Permission
    {
        $modelPermission = Mockery::mock(Permission::class)
            ->makePartial();
        $modelPermission->shouldReceive('hasSurveyPermission')
            ->andReturn(false);

        return $modelPermission;
    }

    public function getMockModelForSurveyNotFound(): Survey
    {
        $modelSurvey = Mockery::mock(Survey::class)
            ->makePartial();
        $modelSurvey->shouldReceive('findByPk')
            ->andReturn(null);

        return $modelSurvey;
    }

    public function getMockModelForQuestionGroupNotFound(): QuestionGroup
    {
        $modelQuestionGroup = Mockery::mock(QuestionGroup::class)
            ->makePartial();
        $modelQuestionGroup->shouldReceive('findByPk')
            ->andReturn(null);

        return $modelQuestionGroup;
    }

    public function getMockModelForQuestionGroupPersistError(): QuestionGroup
    {
        $questionGroup = Mockery::mock(QuestionGroup::class)
            ->makePartial();
        $questionGroup->shouldReceive('save')
            ->andReturn(false);
        $modelQuestionGroup = Mockery::mock(QuestionGroup::class)
            ->makePartial();
        $modelQuestionGroup->shouldReceive('findByPk')
            ->andReturn($questionGroup);
        $modelQuestionGroup->shouldReceive('save')
            ->andReturn(false);

        return $modelQuestionGroup;
    }

    private function getMockModelPermission(): Permission
    {
        $modelPermission = Mockery::mock(Permission::class)
            ->makePartial();
        $modelPermission->shouldReceive('hasSurveyPermission')
            ->andReturn(true);

        return $modelPermission;
    }

    private function getMockSurvey(): Survey
    {
        $survey = Mockery::mock(Survey::class)
            ->makePartial();
        $survey->shouldReceive('save')
            ->andReturn(true);
        $survey->shouldReceive('setAttributes')
            ->passthru();
        $survey->setAttributes([]);
        $survey->shouldReceive('getAttributes')
            ->passthru();
        $survey->getAttributes([]);

        return $survey;
    }

    private function getMockModelSurvey(Survey $survey): Survey
    {
        $modelSurvey = Mockery::mock(Survey::class)
            ->makePartial();
        $modelSurvey->shouldReceive('findByPk')
            ->andReturn($survey);

        return $modelSurvey;
    }

    private function getMockModelQuestion(): Question
    {
        $modelQuestion = Mockery::mock(Question::class)
            ->makePartial();
        $modelQuestion->shouldReceive('findAll')
            ->andReturn([]);

        return $modelQuestion;
    }

    private function getMockModelQuestionGroup($questionGroup): QuestionGroup
    {
        $modelQuestionGroup = Mockery::mock(QuestionGroup::class)
            ->makePartial();

        $modelQuestionGroup->shouldReceive('findByPk')
            ->andReturn($questionGroup);
        $modelQuestionGroup->shouldReceive('findAll')
            ->andReturn($questionGroup);
        $modelQuestionGroup->shouldReceive('setAttributes')
            ->passthru();
        $modelQuestionGroup->shouldReceive('save')
            ->andReturn(true);
        $modelQuestionGroup->shouldReceive('cleanOrder')
            ->andReturn(null);

        return $modelQuestionGroup;
    }

    private function getMockQuestionGroup(): QuestionGroup
    {
        $questionGroup = Mockery::mock(QuestionGroup::class)
            ->makePartial();
        $questionGroup->shouldReceive('save')
            ->andReturn(true);
        $questionGroup->shouldReceive('setAttributes')
            ->passthru();
        $questionGroup->setAttributes([]);
        $questionGroup->shouldReceive('getAttributes')
            ->passthru();
        $questionGroup->getAttributes([]);

        return $questionGroup;
    }

    private function getMockModelQuestionGroupL10n(QuestionGroupL10n $questionGroupL10n): QuestionGroupL10n
    {
        $modelQuestionGroupL10n = Mockery::mock(QuestionGroupL10n::class)
            ->makePartial();
        $modelQuestionGroupL10n->shouldReceive('findByAttributes')
            ->andReturn($questionGroupL10n);
        $modelQuestionGroupL10n->shouldReceive('save')
            ->andReturn(true);
        $modelQuestionGroupL10n->shouldReceive('setAttributes')
            ->passthru();
        $modelQuestionGroupL10n->setAttributes([]);

        return $modelQuestionGroupL10n;
    }

    private function getMockQuestionGroupL10n(): QuestionGroupL10n
    {
        $questionGroupL10n = Mockery::mock(QuestionGroupL10n::class)
            ->makePartial();
        $questionGroupL10n->shouldReceive('save')
            ->andReturn(true);
        $questionGroupL10n->shouldReceive('setAttributes')
            ->passthru();
        $questionGroupL10n->setAttributes([]);
        $questionGroupL10n->shouldReceive('getAttributes')
            ->passthru();
        $questionGroupL10n->getAttributes([]);

        return $questionGroupL10n;
    }

    private function getMockProxyExpressionManager(): ProxyExpressionManager
    {
        $proxyExpressionManager = Mockery::mock(ProxyExpressionManager::class)
            ->makePartial();

        return $proxyExpressionManager;
    }

    private function getMockProxyQuestionGroup(): ProxyQuestionGroup
    {
        $proxyQuestionGroup = Mockery::mock(ProxyQuestionGroup::class)
            ->makePartial();

        return $proxyQuestionGroup;
    }

    private function getMockYiiApp(): LSYii_Application
    {
        return Mockery::mock(LSYii_Application::class)
            ->makePartial();
    }
}