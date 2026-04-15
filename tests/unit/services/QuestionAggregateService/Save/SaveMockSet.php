<?php

namespace ls\tests\unit\services\QuestionAggregateService\Save;

use LimeSurvey\Models\Services\QuestionAggregateService\{
    QuestionService,
    L10nService,
    AttributesService,
    AnswersService,
    SubQuestionsService
};

use LimeSurvey\Models\Services\Proxy\ProxyExpressionManager;

class SaveMockSet
{
    public QuestionService $questionService;
    public L10nService $l10nService;
    public AttributesService $attributesService;
    public AnswersService $answersService;
    public SubQuestionsService $subQuestionsService;
    public ProxyExpressionManager $proxyExpressionManager;
}
