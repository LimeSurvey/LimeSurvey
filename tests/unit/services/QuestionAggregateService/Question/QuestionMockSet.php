<?php

namespace ls\tests\unit\services\QuestionAggregateService\Question;

use Question;
use QuestionL10n;
use Survey;
use Condition;
use LSYii_Application;

use LimeSurvey\Models\Services\QuestionAggregateService\L10nService;

use LimeSurvey\Models\Services\Proxy\{
    ProxySettingsUser,
    ProxyQuestion
};

class QuestionMockSet
{
    public Question $modelQuestion;
    public QuestionL10n $modelQuestionL10n;
    public Survey $modelSurvey;
    public Condition $modelCondition;
    public L10nService $l10nService;
    public ProxySettingsUser $proxySettingsUser;
    public ProxyQuestion $proxyQuestion;
    public LSYii_Application $yiiApp;
}
