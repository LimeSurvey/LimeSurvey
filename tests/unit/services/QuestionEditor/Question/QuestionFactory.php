<?php

namespace ls\tests\unit\services\QuestionEditor\Question;

use LimeSurvey\Models\Services\QuestionEditor\QuestionEditorQuestion;

/**
 * Question Factory
 */
class QuestionFactory
{
    /**
     * @param ?QuestionMockSet $init
     */
    public function make(QuestionMockSet $mockSet = null): QuestionEditorQuestion
    {
        $mockSet = (new QuestionMockSetFactory())->make($mockSet);

        return new QuestionEditorQuestion(
            $mockSet->modelQuestion,
            $mockSet->modelSurvey,
            $mockSet->modelCondition,
            $mockSet->questionEditorL10n,
            $mockSet->proxySettingsUser,
            $mockSet->proxyQuestion,
            $mockSet->yiiApp,
        );
    }
}
