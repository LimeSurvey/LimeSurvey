<?php

namespace ls\tests\unit\services\QuestionAggregateService\Delete;

use Question;
use QuestionL10n;
use Condition;

use LimeSurvey\Models\Services\Proxy\ProxyExpressionManager;

class DeleteMockSet
{
    public Question $modelQuestion;
    public QuestionL10n $modelQuestionL10n;
    public Condition $modelCondition;
    public ProxyExpressionManager $proxyExpressionManager;
}
