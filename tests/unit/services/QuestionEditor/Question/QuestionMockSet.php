<?php

namespace ls\tests\unit\services\QuestionEditor\Question;

use Question;
use Survey;
use Condition;
use LSYii_Application;

use LimeSurvey\Models\Services\QuestionEditor\QuestionEditorL10n;

use LimeSurvey\Models\Services\Proxy\{
    ProxySettingsUser,
    ProxyQuestion
};

class QuestionMockSet
{
    public Question $modelQuestion;
    public Survey $modelSurvey;
    public Condition $modelCondition;
    public QuestionEditorL10n $questionEditorL10n;
    public ProxySettingsUser $proxySettingsUser;
    public ProxyQuestion $proxyQuestion;
    public LSYii_Application $yiiApp;
}
