<?php

namespace ls\tests\unit\services\QuestionEditor;

use Permission;
use Question;
use QuestionL10n;
use CDbConnection;

use LimeSurvey\Models\Services\QuestionEditor\{
    QuestionEditorQuestion,
    QuestionEditorL10n,
    QuestionEditorAttributes,
    QuestionEditorAnswers,
    QuestionEditorSubQuestions
};

use LimeSurvey\Models\Services\Proxy\ProxyExpressionManager;

class QuestionEditorMockSet
{
    public QuestionEditorQuestion $questionEditorQuestion;
    public QuestionEditorL10n $questionEditorL10n;
    public QuestionEditorAttributes $questionEditorAttributes;
    public QuestionEditorAnswers $questionEditorAnswers;
    public QuestionEditorSubQuestions $questionEditorSubQuestions;
    public Question $modelQuestion;
    public QuestionL10n $modelQuestionL10n;
    public Permission $modelPermission;
    public ProxyExpressionManager $proxyExpressionManager;
    public CDbConnection $yiiDb;
}
