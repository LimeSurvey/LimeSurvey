<?php

namespace ls\tests\unit\services\QuestionGroup;

use LimeSurvey\Models\Services\Proxy\ProxyExpressionManager;
use LimeSurvey\Models\Services\Proxy\ProxyQuestionGroup;
use Permission;
use Survey;
use Question;
use QuestionGroup;
use QuestionGroupL10n;
use LSYii_Application;

class QuestionGroupMockSet
{
    public Permission $modelPermission;
    public Survey $survey;
    public Survey $modelSurvey;
    public Question $modelQuestion;
    public QuestionGroup $modelQuestionGroup;
    public QuestionGroup $questionGroup;
    public QuestionGroupL10n $modelQuestionGroupL10n;
    public QuestionGroupL10n $questionGroupL10n;
    public ProxyExpressionManager $proxyExpressionManager;
    public ProxyQuestionGroup $proxyQuestionGroup;
    public LSYii_Application $yiiApp;
}