<?php

namespace ls\tests\unit\services\QuestionEditor;

use LimeSurvey\Models\Services\QuestionEditor;

/**
 * Question Editor Factory
 */
class QuestionEditorFactory
{
    /**
     * @param ?QuestionEditorMockSet $init
     */
    public function make(QuestionEditorMockSet $mockSet = null): QuestionEditor
    {
        $mockSet = (new QuestionEditorMockSetFactory())->make($mockSet);

        return new QuestionEditor(
            $mockSet->questionEditorQuestion,
            $mockSet->questionEditorL10n,
            $mockSet->questionEditorAttributes,
            $mockSet->questionEditorAnswers,
            $mockSet->questionEditorSubQuestions,
            $mockSet->modelQuestion,
            $mockSet->modelPermission,
            $mockSet->proxyExpressionManager,
            $mockSet->yiiDb
        );
    }
}
