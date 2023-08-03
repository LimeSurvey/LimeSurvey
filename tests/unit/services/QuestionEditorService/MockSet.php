<?php

namespace ls\tests\unit\services\QuestionEditorService;

use Permission;
use Question;
use QuestionL10n;
use CDbConnection;

use LimeSurvey\Models\Services\QuestionEditorService\{
    QuestionService,
    L10nService,
    AttributesService,
    AnswersService,
    SubQuestionsService
};

use LimeSurvey\Models\Services\Proxy\ProxyExpressionManager;

class MockSet
{
    public QuestionService $questionService;
    public L10nService $l10nService;
    public AttributesService $attributesService;
    public AnswersService $answersService;
    public SubQuestionsService $subQuestionsService;
    public Question $modelQuestion;
    public QuestionL10n $modelQuestionL10n;
    public Permission $modelPermission;
    public ProxyExpressionManager $proxyExpressionManager;
    public CDbConnection $yiiDb;
}
