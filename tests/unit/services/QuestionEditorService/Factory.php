<?php

namespace ls\tests\unit\services\QuestionEditorService;

use LimeSurvey\Models\Services\QuestionEditorService;

/**
 * Question Editor Factory
 */
class Factory
{
    /**
     * @param ?QuestionEditorMockSet $init
     */
    public function make(MockSet $mockSet = null): QuestionEditorService
    {
        $mockSet = (new MockSetFactory())->make($mockSet);

        return new QuestionEditorService(
            $mockSet->questionService,
            $mockSet->l10nService,
            $mockSet->attributesService,
            $mockSet->answersService,
            $mockSet->subQuestionsService,
            $mockSet->modelQuestion,
            $mockSet->modelPermission,
            $mockSet->proxyExpressionManager,
            $mockSet->yiiDb
        );
    }
}
