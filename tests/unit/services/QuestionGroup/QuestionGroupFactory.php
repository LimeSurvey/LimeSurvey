<?php

namespace ls\tests\unit\services\QuestionGroup;

use LimeSurvey\Models\Services\QuestionGroupService;

class QuestionGroupFactory
{
    public function make(QuestionGroupMockSet $mockSet = null): QuestionGroupService
    {
        $mockSet = (new QuestionGroupMockSetFactory())->make($mockSet);

        return new QuestionGroupService(
            $mockSet->modelPermission,
            $mockSet->modelSurvey,
            $mockSet->modelQuestion,
            $mockSet->modelQuestionGroup,
            $mockSet->modelQuestionGroupL10n,
            $mockSet->proxyExpressionManager,
            $mockSet->proxyQuestionGroup,
            $mockSet->yiiApp
        );
    }
}