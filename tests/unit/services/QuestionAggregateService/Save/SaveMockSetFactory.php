<?php

namespace ls\tests\unit\services\QuestionAggregateService\Save;

use LimeSurvey\Models\Services\QuestionAttributeHelper;
use ls\tests\unit\services\QuestionAggregateService\Question\QuestionFactory;

use QuestionAttribute;
use Mockery;

use LimeSurvey\Models\Services\Proxy\ProxyExpressionManager;

use LimeSurvey\Models\Services\QuestionAggregateService\{
    L10nService,
    AttributesService,
    AnswersService,
    SubQuestionsService
};

/**
 * Save Mock Factory
 */
class SaveMockSetFactory
{
    /**
     * @param ?SaveMockSet $init
     */
    public function make(SaveMockSet $init = null): SaveMockSet
    {
        $mockSet = new SaveMockSet;

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

        $mockSet->proxyExpressionManager = ($init && isset($init->proxyExpressionManager))
            ? $init->proxyExpressionManager
            : $this->getMockProxyExpressionManager();

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
                $this->getMockModelQuestionAttribute(),
                $this->getQuestionAttributeHelper(),
                $this->getMockSurveyModel()
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

    private function getMockProxyExpressionManager(): ProxyExpressionManager
    {
        return Mockery::mock(ProxyExpressionManager::class)
            ->makePartial();
    }

    private function getQuestionAttributeHelper()
    {
        return Mockery::mock(QuestionAttributeHelper::class)
            ->makePartial();
    }

    private function getMockSurveyModel()
    {
        return Mockery::mock(\Survey::class)
            ->makePartial();
    }
}
